<?php

namespace App\Services;

use App\Models\WorkReport;
use App\Models\WorkReportEvent;
use App\Models\BalanceMovement;
use App\Models\User;
use App\Models\Client;
use App\Services\BalanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Servicio para gestionar partes de trabajo (work_reports) y su cronómetro.
 *
 * Reglas de negocio core:
 * - Un técnico solo puede tener 1 parte en in_progress (los demás deben estar paused)
 * - El tiempo se mide en segundos
 * - Las pausas NO suman al total_seconds
 * - El tiempo se acumula mediante eventos start/pause/resume/finish
 * - Idempotencia: no permitir operaciones inválidas según estado actual
 */
class WorkReportService
{
    private BalanceService $balanceService;
    private ?AuditService $auditService;

    public function __construct(BalanceService $balanceService, ?AuditService $auditService = null)
    {
        $this->balanceService = $balanceService;
        $this->auditService = $auditService;
    }
    /**
     * Crea un nuevo parte de trabajo.
     *
     * @param Client|int $client Cliente o ID del cliente
     * @param User|int $technician Técnico o ID del técnico
     * @param string|null $title Título del parte (opcional)
     * @param string|null $description Descripción inicial (opcional)
     * @return WorkReport Parte creado
     * @throws InvalidArgumentException Si los parámetros son inválidos
     */
    public function create(
        Client|int $client,
        User|int $technician,
        ?string $title = null,
        ?string $description = null
    ): WorkReport {
        // Obtener cliente si se pasó un ID
        if (is_int($client)) {
            $client = Client::findOrFail($client);
        }

        // Obtener técnico si se pasó un ID
        if (is_int($technician)) {
            $technician = User::findOrFail($technician);
        }

        return WorkReport::create([
            'client_id' => $client->id,
            'technician_id' => $technician->id,
            'title' => $title,
            'description' => $description,
            'status' => WorkReport::STATUS_PAUSED, // Inicialmente pausado (no activo)
            'total_seconds' => 0,
        ]);
    }

    /**
     * Inicia el cronómetro del parte (start).
     *
     * Regla core: verifica que el técnico no tenga otro parte en in_progress.
     * Si ya está in_progress, lanza excepción (idempotencia).
     *
     * @param WorkReport|int $workReport Parte o ID del parte
     * @param User|int|null $createdBy Usuario que inicia (opcional)
     * @return WorkReport Parte actualizado
     * @throws RuntimeException Si el técnico ya tiene otro parte activo
     * @throws InvalidArgumentException Si el parte no está en estado válido
     */
    public function start(WorkReport|int $workReport, User|int|null $createdBy = null): WorkReport
    {
        // Obtener parte si se pasó un ID
        if (is_int($workReport)) {
            $workReport = WorkReport::findOrFail($workReport);
        }

        // Obtener usuario si se pasó un ID
        $createdById = null;
        if ($createdBy instanceof User) {
            $createdById = $createdBy->id;
        } elseif (is_int($createdBy)) {
            $createdById = $createdBy;
        }

        // Idempotencia: si ya está in_progress, no hacer nada
        if ($workReport->isInProgress()) {
            return $workReport;
        }

        // Validar estado: solo se puede iniciar si está paused
        if (!$workReport->isPaused()) {
            throw new InvalidArgumentException(
                "No se puede iniciar un parte que está en estado '{$workReport->status}'. Solo se puede iniciar desde 'paused'."
            );
        }

        // Regla core: verificar que el técnico no tenga otro parte en in_progress
        $activeReport = WorkReport::where('technician_id', $workReport->technician_id)
            ->where('status', WorkReport::STATUS_IN_PROGRESS)
            ->where('id', '!=', $workReport->id)
            ->first();

        if ($activeReport) {
            throw new RuntimeException(
                "El técnico ya tiene un parte activo (ID: {$activeReport->id}). " .
                "Solo puede haber 1 parte en 'in_progress' por técnico. " .
                "Debe pausar o finalizar el parte activo antes de iniciar otro."
            );
        }

        // Ejecutar en transacción para mantener consistencia
        return DB::transaction(function () use ($workReport, $createdById) {
            $now = now();

            // Actualizar estado y activar cronómetro
            $workReport->update([
                'status' => WorkReport::STATUS_IN_PROGRESS,
                'active_started_at' => $now,
            ]);

            // Crear evento de inicio
            $this->createEvent(
                $workReport,
                WorkReportEvent::TYPE_START,
                $now,
                $workReport->total_seconds, // elapsed_seconds_after = total actual (sin cambios aún)
                $createdById
            );

            Log::info('Parte iniciado', [
                'work_report_id' => $workReport->id,
                'technician_id' => $workReport->technician_id,
            ]);

            return $workReport->fresh();
        });
    }

    /**
     * Pausa el cronómetro del parte (pause).
     *
     * Calcula el delta de tiempo desde active_started_at y lo suma a total_seconds.
     * Las pausas NO suman tiempo, pero se registran para trazabilidad.
     *
     * @param WorkReport|int $workReport Parte o ID del parte
     * @param User|int|null $createdBy Usuario que pausa (opcional)
     * @param array|null $metadata Información adicional (ej: motivo de pausa)
     * @return WorkReport Parte actualizado
     * @throws InvalidArgumentException Si el parte no está en estado válido
     */
    public function pause(WorkReport|int $workReport, User|int|null $createdBy = null, ?array $metadata = null): WorkReport
    {
        // Obtener parte si se pasó un ID
        if (is_int($workReport)) {
            $workReport = WorkReport::findOrFail($workReport);
        }

        // Obtener usuario si se pasó un ID
        $createdById = null;
        if ($createdBy instanceof User) {
            $createdById = $createdBy->id;
        } elseif (is_int($createdBy)) {
            $createdById = $createdBy;
        }

        // Idempotencia: si ya está paused, no hacer nada
        if ($workReport->isPaused()) {
            return $workReport;
        }

        // Validar estado: solo se puede pausar si está in_progress
        if (!$workReport->isInProgress()) {
            throw new InvalidArgumentException(
                "No se puede pausar un parte que está en estado '{$workReport->status}'. Solo se puede pausar desde 'in_progress'."
            );
        }

        // Ejecutar en transacción para mantener consistencia
        return DB::transaction(function () use ($workReport, $createdById, $metadata) {
            // Recargar el parte para obtener el valor actualizado de active_started_at
            $workReport->refresh();
            $now = now();

            // Calcular delta de tiempo desde active_started_at
            // NOTE: El tiempo en pausa NO suma, pero el tiempo activo sí
            $deltaSeconds = 0;
            if ($workReport->active_started_at) {
                // Calcular diferencia en segundos usando timestamps
                $startTimestamp = $workReport->active_started_at->timestamp;
                $nowTimestamp = $now->timestamp;
                $deltaSeconds = max(0, $nowTimestamp - $startTimestamp);
            }

            // Actualizar total_seconds sumando el delta
            $newTotalSeconds = $workReport->total_seconds + $deltaSeconds;

            // Actualizar estado y limpiar active_started_at
            $workReport->update([
                'status' => WorkReport::STATUS_PAUSED,
                'total_seconds' => $newTotalSeconds,
                'active_started_at' => null, // Limpiar tramo activo
            ]);

            // Crear evento de pausa
            $this->createEvent(
                $workReport,
                WorkReportEvent::TYPE_PAUSE,
                $now,
                $newTotalSeconds, // elapsed_seconds_after = nuevo total
                $createdById,
                $metadata
            );

            Log::info('Parte pausado', [
                'work_report_id' => $workReport->id,
                'delta_seconds' => $deltaSeconds,
                'total_seconds' => $newTotalSeconds,
            ]);

            return $workReport->fresh();
        });
    }

    /**
     * Reanuda el cronómetro del parte (resume).
     *
     * Regla core: verifica que el técnico no tenga otro parte en in_progress.
     *
     * @param WorkReport|int $workReport Parte o ID del parte
     * @param User|int|null $createdBy Usuario que reanuda (opcional)
     * @return WorkReport Parte actualizado
     * @throws RuntimeException Si el técnico ya tiene otro parte activo
     * @throws InvalidArgumentException Si el parte no está en estado válido
     */
    public function resume(WorkReport|int $workReport, User|int|null $createdBy = null): WorkReport
    {
        // Obtener parte si se pasó un ID
        if (is_int($workReport)) {
            $workReport = WorkReport::findOrFail($workReport);
        }

        // Obtener usuario si se pasó un ID
        $createdById = null;
        if ($createdBy instanceof User) {
            $createdById = $createdBy->id;
        } elseif (is_int($createdBy)) {
            $createdById = $createdBy;
        }

        // Idempotencia: si ya está in_progress, no hacer nada
        if ($workReport->isInProgress()) {
            return $workReport;
        }

        // Validar estado: solo se puede reanudar si está paused
        if (!$workReport->isPaused()) {
            throw new InvalidArgumentException(
                "No se puede reanudar un parte que está en estado '{$workReport->status}'. Solo se puede reanudar desde 'paused'."
            );
        }

        // Regla core: verificar que el técnico no tenga otro parte en in_progress
        $activeReport = WorkReport::where('technician_id', $workReport->technician_id)
            ->where('status', WorkReport::STATUS_IN_PROGRESS)
            ->where('id', '!=', $workReport->id)
            ->first();

        if ($activeReport) {
            throw new RuntimeException(
                "El técnico ya tiene un parte activo (ID: {$activeReport->id}). " .
                "Solo puede haber 1 parte en 'in_progress' por técnico. " .
                "Debe pausar o finalizar el parte activo antes de reanudar otro."
            );
        }

        // Ejecutar en transacción para mantener consistencia
        return DB::transaction(function () use ($workReport, $createdById) {
            $now = now();

            // Actualizar estado y reactivar cronómetro
            $workReport->update([
                'status' => WorkReport::STATUS_IN_PROGRESS,
                'active_started_at' => $now, // Iniciar nuevo tramo activo
            ]);

            // Crear evento de reanudación
            $this->createEvent(
                $workReport,
                WorkReportEvent::TYPE_RESUME,
                $now,
                $workReport->total_seconds, // elapsed_seconds_after = total actual (sin cambios aún)
                $createdById
            );

            Log::info('Parte reanudado', [
                'work_report_id' => $workReport->id,
                'technician_id' => $workReport->technician_id,
            ]);

            return $workReport->fresh();
        });
    }

    /**
     * Finaliza el parte (finish).
     *
     * Cierra el tramo activo si existía (suma delta a total_seconds),
     * cambia estado a finished y registra finished_at.
     *
     * @param WorkReport|int $workReport Parte o ID del parte
     * @param string|null $summary Resumen de lo realizado (opcional)
     * @param User|int|null $createdBy Usuario que finaliza (opcional)
     * @return WorkReport Parte actualizado
     * @throws InvalidArgumentException Si el parte no está en estado válido
     */
    public function finish(WorkReport|int $workReport, ?string $summary = null, User|int|null $createdBy = null): WorkReport
    {
        // Obtener parte si se pasó un ID
        if (is_int($workReport)) {
            $workReport = WorkReport::findOrFail($workReport);
        }

        // Obtener usuario si se pasó un ID
        $createdById = null;
        if ($createdBy instanceof User) {
            $createdById = $createdBy->id;
        } elseif (is_int($createdBy)) {
            $createdById = $createdBy;
        }

        // Validar estado: solo se puede finalizar si está in_progress o paused
        if (!$workReport->isInProgress() && !$workReport->isPaused()) {
            throw new InvalidArgumentException(
                "No se puede finalizar un parte que está en estado '{$workReport->status}'. " .
                "Solo se puede finalizar desde 'in_progress' o 'paused'."
            );
        }

        // Ejecutar en transacción para mantener consistencia
        return DB::transaction(function () use ($workReport, $summary, $createdById) {
            // Recargar el parte para obtener el valor actualizado
            $workReport->refresh();
            $now = now();
            $newTotalSeconds = $workReport->total_seconds;

            // Si estaba activo, cerrar el tramo y sumar delta
            if ($workReport->isInProgress() && $workReport->active_started_at) {
                // Calcular diferencia en segundos usando timestamps
                $startTimestamp = $workReport->active_started_at->timestamp;
                $nowTimestamp = $now->timestamp;
                $deltaSeconds = max(0, $nowTimestamp - $startTimestamp);
                $newTotalSeconds = $workReport->total_seconds + $deltaSeconds;
            }

            // Actualizar estado y limpiar active_started_at
            $workReport->update([
                'status' => WorkReport::STATUS_FINISHED,
                'total_seconds' => $newTotalSeconds,
                'active_started_at' => null, // Limpiar tramo activo
                'finished_at' => $now,
                'summary' => $summary,
            ]);

            // Crear evento de finalización
            $this->createEvent(
                $workReport,
                WorkReportEvent::TYPE_FINISH,
                $now,
                $newTotalSeconds, // elapsed_seconds_after = nuevo total
                $createdById
            );

            Log::info('Parte finalizado', [
                'work_report_id' => $workReport->id,
                'total_seconds' => $newTotalSeconds,
            ]);

            return $workReport->fresh();
        });
    }

    /**
     * Valida el parte (validate).
     *
     * Cambia estado a validated, registra validated_at y validated_by,
     * y descuenta el saldo del cliente usando BalanceService::debit().
     *
     * Reglas de negocio:
     * - El descuento se realiza al validar (no al finalizar)
     * - El descuento usa work_reports.total_seconds (segundos)
     * - Si no hay saldo suficiente, NO valida y NO crea movimiento
     * - Idempotencia fuerte: si ya está validated o ya existe movimiento, no duplica
     * - Reparación: si está validated pero sin movimiento, permite crear el movimiento sin exigir estado finished
     * - Concurrencia: maneja duplicate key como idempotencia (no falla)
     * - Todo ejecuta en transacción: estado + evento + movimiento de saldo atómicos
     *
     * @param WorkReport|int $workReport Parte o ID del parte
     * @param User|int $validatedBy Usuario que valida (obligatorio)
     * @return WorkReport Parte actualizado
     * @throws InvalidArgumentException Si el parte no está en estado válido
     * @throws RuntimeException Si no hay saldo suficiente para validar
     */
    public function validate(WorkReport|int $workReport, User|int $validatedBy): WorkReport
    {
        // Obtener parte si se pasó un ID
        if (is_int($workReport)) {
            $workReport = WorkReport::findOrFail($workReport);
        }

        // Obtener usuario si se pasó un ID
        $validatedById = null;
        if ($validatedBy instanceof User) {
            $validatedById = $validatedBy->id;
        } elseif (is_int($validatedBy)) {
            $validatedById = $validatedBy;
        } else {
            throw new InvalidArgumentException('El validador es obligatorio.');
        }

        // Verificar si ya existe un movimiento de saldo para este parte
        $existingMovement = $this->findExistingBalanceMovement($workReport);

        // Detectar caso de reparación: parte validated pero sin movimiento
        // NOTE: En reparación, permitimos continuar sin exigir estado finished
        $isRepair = $workReport->isValidated() && !$existingMovement;

        // Idempotencia fuerte: si ya está validated y tiene movimiento, retornar sin hacer nada
        if ($workReport->isValidated() && $existingMovement) {
            return $workReport;
        }

        // Validar estado: solo se puede validar si está finished (excepto en reparación)
        if (!$isRepair && !$workReport->isFinished()) {
            throw new InvalidArgumentException(
                "No se puede validar un parte que está en estado '{$workReport->status}'. " .
                "Solo se puede validar desde 'finished'."
            );
        }

        // Validar que el parte tenga tiempo trabajado
        if ($workReport->total_seconds <= 0) {
            throw new InvalidArgumentException(
                "No se puede validar un parte sin tiempo trabajado (total_seconds: {$workReport->total_seconds})."
            );
        }

        // Ejecutar en transacción para mantener consistencia
        // NOTE: La transacción protege: cambio de estado + evento + movimiento de saldo
        // Si falla el descuento (saldo insuficiente), se revierte todo
        return DB::transaction(function () use ($workReport, $validatedById, $isRepair) {
            $now = now();

            // Obtener el cliente del parte
            $client = $workReport->client;

            // Descontar saldo usando BalanceService::debit()
            // Manejar concurrencia: si se produce duplicate key, tratarlo como idempotencia
            try {
                $this->balanceService->debit(
                    $client,
                    $workReport->total_seconds, // Descontar el total de segundos trabajados
                    'validation_work_report', // Reason para trazabilidad
                    'WorkReport', // reference_type
                    $workReport->id, // reference_id
                    $validatedById, // created_by (quién valida)
                    [ // metadata con información adicional
                        'work_report_id' => $workReport->id,
                        'work_report_title' => $workReport->title,
                        'total_seconds' => $workReport->total_seconds,
                    ]
                );
            } catch (QueryException $e) {
                // Manejar concurrencia: si se produce duplicate key (índice único violado),
                // tratarlo como idempotencia y continuar
                // NOTE: El constraint único balance_movements_reference_unique previene doble cargo
                if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'balance_movements_reference_unique')) {
                    // Recargar el parte desde BD para obtener estado actualizado
                    $workReport->refresh();

                    // Verificar que el movimiento existe ahora
                    $existingMovement = $this->findExistingBalanceMovement($workReport);
                    if ($existingMovement) {
                        // Movimiento creado por otra transacción concurrente, continuar como idempotente
                        Log::info('Validación concurrente detectada, tratada como idempotente', [
                            'work_report_id' => $workReport->id,
                            'movement_id' => $existingMovement->id,
                        ]);
                    } else {
                        // Si no existe el movimiento, re-lanzar la excepción (error real)
                        throw $e;
                    }
                } else {
                    // Otra excepción de BD, re-lanzar
                    throw $e;
                }
            }

            // Actualizar estado y registrar validación (solo si no está ya validated)
            // En reparación, mantener validated_at y validated_by existentes si ya existen
            if (!$workReport->isValidated()) {
                $workReport->update([
                    'status' => WorkReport::STATUS_VALIDATED,
                    'validated_at' => $now,
                    'validated_by' => $validatedById,
                ]);
            } elseif ($isRepair) {
                // En reparación, asegurar que validated_at y validated_by estén seteados
                $updateData = [];
                if (!$workReport->validated_at) {
                    $updateData['validated_at'] = $now;
                }
                if (!$workReport->validated_by) {
                    $updateData['validated_by'] = $validatedById;
                }
                if (!empty($updateData)) {
                    $workReport->update($updateData);
                }
            }

            // Crear evento de validación solo si no existe ya (no duplicar eventos)
            $existingEvent = WorkReportEvent::where('work_report_id', $workReport->id)
                ->where('type', WorkReportEvent::TYPE_VALIDATE)
                ->first();

            if (!$existingEvent) {
                $this->createEvent(
                    $workReport,
                    WorkReportEvent::TYPE_VALIDATE,
                    $now,
                    $workReport->total_seconds, // elapsed_seconds_after = total actual
                    $validatedById
                );
            }

            Log::info('Parte validado y saldo descontado', [
                'work_report_id' => $workReport->id,
                'client_id' => $client->id,
                'validated_by' => $validatedById,
                'total_seconds' => $workReport->total_seconds,
                'balance_debited' => $workReport->total_seconds,
                'is_repair' => $isRepair,
            ]);

            // Registrar auditoría (no debe interrumpir el flujo si falla)
            // NOTE: El AuditService ya captura excepciones internamente, pero añadimos try-catch
            // adicional por si acaso para garantizar que nunca interrumpa el flujo principal
            if ($this->auditService) {
                try {
                    $this->auditService->log(
                        'work_report_validated',
                        $validatedById,
                        'WorkReport',
                        $workReport->id,
                        [
                            'client_id' => $client->id,
                            'total_seconds' => $workReport->total_seconds,
                            'is_repair' => $isRepair,
                        ]
                    );
                } catch (\Exception $e) {
                    // Si falla la auditoría, registrar warning pero no interrumpir
                    Log::warning('Error al registrar auditoría de validación', [
                        'error' => $e->getMessage(),
                        'work_report_id' => $workReport->id,
                    ]);
                }
            }

            return $workReport->fresh();
        });
    }

    /**
     * Busca si ya existe un movimiento de saldo para este parte.
     *
     * Método auxiliar para verificar idempotencia antes de crear un nuevo movimiento.
     *
     * @param WorkReport $workReport Parte
     * @return BalanceMovement|null Movimiento existente o null
     */
    private function findExistingBalanceMovement(WorkReport $workReport): ?BalanceMovement
    {
        return BalanceMovement::where('client_id', $workReport->client_id)
            ->where('reference_type', 'WorkReport')
            ->where('reference_id', $workReport->id)
            ->where('reason', 'validation_work_report')
            ->first();
    }

    /**
     * Actualiza los detalles de un parte (title, description, summary).
     *
     * Reglas de negocio:
     * - No se permite editar tiempos ni estados (total_seconds, active_started_at, status, etc.)
     * - validated: BLOQUEADO para todos (por defecto, más seguro)
     * - in_progress/paused: se permite editar title y description (summary NO hasta finished)
     * - finished: se permite editar summary (y opcionalmente title/description)
     * - Cada update crea evento `edit` con metadata diff
     * - Se registra auditoría (no bloqueante)
     * - Todo ejecuta en transacción: actualización + evento + auditoría
     *
     * @param WorkReport|int $workReport Parte o ID del parte
     * @param array $data Datos a actualizar (title, description, summary)
     * @param User|int $actor Usuario que realiza la edición (obligatorio)
     * @return WorkReport Parte actualizado
     * @throws InvalidArgumentException Si el parte está validated o los campos no son editables según estado
     */
    public function updateDetails(WorkReport|int $workReport, array $data, User|int $actor): WorkReport
    {
        // Obtener parte si se pasó un ID
        if (is_int($workReport)) {
            $workReport = WorkReport::findOrFail($workReport);
        }

        // Obtener usuario si se pasó un ID
        $actorId = null;
        if ($actor instanceof User) {
            $actorId = $actor->id;
        } elseif (is_int($actor)) {
            $actorId = $actor;
        } else {
            throw new InvalidArgumentException('El usuario que edita es obligatorio.');
        }

        // Regla: validated está BLOQUEADO para todos (por defecto, más seguro)
        if ($workReport->isValidated()) {
            throw new InvalidArgumentException(
                "No se puede editar un parte que está en estado 'validated'. " .
                "Los partes validados no se pueden modificar."
            );
        }

        // Filtrar solo campos editables (title, description, summary)
        // Regla: NO se permite cambiar tiempos ni estados
        $allowedFields = ['title', 'description', 'summary'];
        $dataToUpdate = array_intersect_key($data, array_flip($allowedFields));

        // Validar campos según estado
        $status = $workReport->status;
        if (in_array($status, [WorkReport::STATUS_IN_PROGRESS, WorkReport::STATUS_PAUSED])) {
            // in_progress/paused: solo title y description (summary NO hasta finished)
            if (isset($dataToUpdate['summary'])) {
                throw new InvalidArgumentException(
                    "No se puede editar 'summary' en un parte con estado '{$status}'. " .
                    "El resumen solo se puede editar cuando el parte está 'finished'."
                );
            }
        }
        // finished: se permite editar summary, title y description (sin restricciones adicionales)

        // Si no hay cambios, retornar sin hacer nada
        if (empty($dataToUpdate)) {
            return $workReport;
        }

        // Ejecutar en transacción para mantener consistencia
        // NOTE: La transacción protege: actualización + evento + auditoría
        return DB::transaction(function () use ($workReport, $dataToUpdate, $actorId) {
            // Recargar el parte para obtener valores actuales
            $workReport->refresh();
            $now = now();

            // Calcular diff de cambios para metadata del evento
            $diff = [];
            foreach ($dataToUpdate as $field => $newValue) {
                $oldValue = $workReport->$field;
                if ($oldValue !== $newValue) {
                    $diff[$field] = [
                        'from' => $oldValue,
                        'to' => $newValue,
                    ];
                }
            }

            // Si no hay cambios reales, retornar sin hacer nada
            if (empty($diff)) {
                return $workReport;
            }

            // Actualizar work_report
            $workReport->update($dataToUpdate);

            // Crear evento `edit` con metadata diff
            $this->createEvent(
                $workReport,
                WorkReportEvent::TYPE_EDIT,
                $now,
                $workReport->total_seconds, // elapsed_seconds_after = total actual
                $actorId,
                ['diff' => $diff] // metadata con diff de cambios
            );

            Log::info('Parte editado', [
                'work_report_id' => $workReport->id,
                'fields_edited' => array_keys($diff),
                'actor_id' => $actorId,
            ]);

            // Registrar auditoría (no debe interrumpir el flujo si falla)
            if ($this->auditService) {
                try {
                    $this->auditService->log(
                        'work_report_edited',
                        $actorId,
                        'WorkReport',
                        $workReport->id,
                        [
                            'fields_edited' => array_keys($diff),
                            'diff' => $diff,
                            'status' => $workReport->status,
                        ]
                    );
                } catch (\Exception $e) {
                    // Si falla la auditoría, registrar warning pero no interrumpir
                    Log::warning('Error al registrar auditoría de edición', [
                        'error' => $e->getMessage(),
                        'work_report_id' => $workReport->id,
                    ]);
                }
            }

            return $workReport->fresh();
        });
    }

    /**
     * Crea un evento en el historial del parte.
     *
     * Método auxiliar para registrar eventos de forma consistente.
     *
     * @param WorkReport $workReport Parte
     * @param string $type Tipo de evento
     * @param \DateTimeInterface $occurredAt Momento en que ocurrió
     * @param int $elapsedSecondsAfter Segundos acumulados tras el evento
     * @param int|null $createdById ID del usuario que crea el evento
     * @param array|null $metadata Información adicional
     * @return WorkReportEvent Evento creado
     */
    private function createEvent(
        WorkReport $workReport,
        string $type,
        \DateTimeInterface $occurredAt,
        int $elapsedSecondsAfter,
        ?int $createdById = null,
        ?array $metadata = null
    ): WorkReportEvent {
        return WorkReportEvent::create([
            'work_report_id' => $workReport->id,
            'type' => $type,
            'occurred_at' => $occurredAt,
            'elapsed_seconds_after' => $elapsedSecondsAfter,
            'metadata' => $metadata,
            'created_by' => $createdById,
        ]);
    }
}
