<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\BalanceMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

/**
 * Servicio para gestionar el saldo de clientes mediante ledger (balance_movements).
 *
 * Reglas de negocio:
 * - El saldo se mide en segundos
 * - La fuente de verdad es balance_movements (suma de amount_seconds)
 * - client_profiles.balance_seconds es un agregado de rendimiento que se mantiene en la misma transacción
 * - Los movimientos son inmutables (no se editan, se compensan con otro movimiento)
 * - No se permite saldo negativo al realizar un débito
 */
class BalanceService
{
    private ?AuditService $auditService;

    public function __construct(?AuditService $auditService = null)
    {
        $this->auditService = $auditService;
    }
    /**
     * Añade crédito (saldo positivo) al cliente.
     *
     * Crea un movimiento positivo en balance_movements y actualiza el agregado
     * en client_profiles.balance_seconds dentro de una transacción.
     *
     * @param Client|int $client Cliente o ID del cliente
     * @param int $seconds Cantidad de segundos a añadir (debe ser positivo)
     * @param string $reason Motivo del crédito (obligatorio para trazabilidad)
     * @param string|null $referenceType Tipo de entidad referenciada (opcional)
     * @param int|null $referenceId ID de la entidad referenciada (opcional)
     * @param int|null $createdBy ID del usuario que crea el movimiento (opcional)
     * @param array|null $metadata Información adicional en formato array (opcional)
     * @return BalanceMovement Movimiento creado
     * @throws InvalidArgumentException Si los parámetros son inválidos
     * @throws RuntimeException Si falla la transacción
     */
    public function credit(
        Client|int $client,
        int $seconds,
        string $reason,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $createdBy = null,
        ?array $metadata = null
    ): BalanceMovement {
        // Validación: los segundos deben ser positivos
        if ($seconds <= 0) {
            throw new InvalidArgumentException('Los segundos deben ser positivos para un crédito.');
        }

        // Validación: reason es obligatorio
        if (empty($reason)) {
            throw new InvalidArgumentException('El motivo (reason) es obligatorio para trazabilidad.');
        }

        // Obtener el cliente si se pasó un ID
        if (is_int($client)) {
            $client = Client::findOrFail($client);
        }

        // Ejecutar en transacción para mantener consistencia entre ledger y agregado
        return DB::transaction(function () use ($client, $seconds, $reason, $referenceType, $referenceId, $createdBy, $metadata) {
            // Crear el movimiento en el ledger (fuente de verdad)
            $movement = BalanceMovement::create([
                'client_id' => $client->id,
                'amount_seconds' => $seconds, // Positivo para crédito
                'type' => 'credit',
                'reason' => $reason,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $createdBy,
                'metadata' => $metadata,
            ]);

            // Actualizar el agregado en client_profiles (optimización de rendimiento)
            // Se actualiza en la misma transacción para mantener consistencia
            $profile = $this->getOrCreateProfile($client);
            $profile->increment('balance_seconds', $seconds);

            Log::info('Crédito añadido', [
                'client_id' => $client->id,
                'amount_seconds' => $seconds,
                'reason' => $reason,
                'movement_id' => $movement->id,
            ]);

            // Registrar auditoría (no debe interrumpir el flujo si falla)
            // NOTE: El AuditService ya captura excepciones internamente, pero añadimos try-catch
            // adicional por si acaso para garantizar que nunca interrumpa el flujo principal
            if ($this->auditService) {
                try {
                    $this->auditService->log(
                        'saldo_change',
                        $createdBy,
                        'BalanceMovement',
                        $movement->id,
                        [
                            'type' => 'credit',
                            'client_id' => $client->id,
                            'amount_seconds' => $seconds,
                            'reason' => $reason,
                            'balance_after' => $profile->fresh()->balance_seconds,
                        ]
                    );
                } catch (\Exception $e) {
                    // Si falla la auditoría, registrar warning pero no interrumpir
                    Log::warning('Error al registrar auditoría de crédito', [
                        'error' => $e->getMessage(),
                        'movement_id' => $movement->id,
                    ]);
                }
            }

            return $movement;
        });
    }

    /**
     * Realiza un débito (descuento de saldo) al cliente.
     *
     * Valida que haya saldo suficiente, crea un movimiento negativo en balance_movements
     * y actualiza el agregado en client_profiles.balance_seconds dentro de una transacción.
     *
     * @param Client|int $client Cliente o ID del cliente
     * @param int $seconds Cantidad de segundos a descontar (debe ser positivo)
     * @param string $reason Motivo del débito (obligatorio para trazabilidad)
     * @param string|null $referenceType Tipo de entidad referenciada (opcional)
     * @param int|null $referenceId ID de la entidad referenciada (opcional)
     * @param int|null $createdBy ID del usuario que crea el movimiento (opcional)
     * @param array|null $metadata Información adicional en formato array (opcional)
     * @return BalanceMovement Movimiento creado
     * @throws InvalidArgumentException Si los parámetros son inválidos
     * @throws RuntimeException Si no hay saldo suficiente o falla la transacción
     */
    public function debit(
        Client|int $client,
        int $seconds,
        string $reason,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $createdBy = null,
        ?array $metadata = null
    ): BalanceMovement {
        // Validación: los segundos deben ser positivos
        if ($seconds <= 0) {
            throw new InvalidArgumentException('Los segundos deben ser positivos para un débito.');
        }

        // Validación: reason es obligatorio
        if (empty($reason)) {
            throw new InvalidArgumentException('El motivo (reason) es obligatorio para trazabilidad.');
        }

        // Obtener el cliente si se pasó un ID
        if (is_int($client)) {
            $client = Client::findOrFail($client);
        }

        // Ejecutar en transacción para mantener consistencia entre ledger y agregado
        return DB::transaction(function () use ($client, $seconds, $reason, $referenceType, $referenceId, $createdBy, $metadata) {
            // Obtener saldo actual desde el ledger (fuente de verdad)
            $currentBalance = $this->getBalanceSeconds($client);

            // Validar que hay saldo suficiente (regla: no permitir saldo negativo)
            if ($currentBalance < $seconds) {
                throw new RuntimeException(
                    "Saldo insuficiente. Saldo actual: {$currentBalance} segundos, intento de débito: {$seconds} segundos."
                );
            }

            // Crear el movimiento en el ledger (fuente de verdad)
            $movement = BalanceMovement::create([
                'client_id' => $client->id,
                'amount_seconds' => -$seconds, // Negativo para débito
                'type' => 'debit',
                'reason' => $reason,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $createdBy,
                'metadata' => $metadata,
            ]);

            // Actualizar el agregado en client_profiles (optimización de rendimiento)
            // Se actualiza en la misma transacción para mantener consistencia
            $profile = $this->getOrCreateProfile($client);
            $profile->decrement('balance_seconds', $seconds);

            Log::info('Débito realizado', [
                'client_id' => $client->id,
                'amount_seconds' => $seconds,
                'reason' => $reason,
                'movement_id' => $movement->id,
                'balance_before' => $currentBalance,
                'balance_after' => $currentBalance - $seconds,
            ]);

            // Registrar auditoría (no debe interrumpir el flujo si falla)
            // NOTE: El AuditService ya captura excepciones internamente, pero añadimos try-catch
            // adicional por si acaso para garantizar que nunca interrumpa el flujo principal
            if ($this->auditService) {
                try {
                    $this->auditService->log(
                        'saldo_change',
                        $createdBy,
                        'BalanceMovement',
                        $movement->id,
                        [
                            'type' => 'debit',
                            'client_id' => $client->id,
                            'amount_seconds' => $seconds,
                            'reason' => $reason,
                            'balance_before' => $currentBalance,
                            'balance_after' => $currentBalance - $seconds,
                        ]
                    );
                } catch (\Exception $e) {
                    // Si falla la auditoría, registrar warning pero no interrumpir
                    Log::warning('Error al registrar auditoría de débito', [
                        'error' => $e->getMessage(),
                        'movement_id' => $movement->id,
                    ]);
                }
            }

            return $movement;
        });
    }

    /**
     * Obtiene el saldo actual del cliente calculado desde el ledger (fuente de verdad).
     *
     * Suma todos los amount_seconds de balance_movements para el cliente.
     * Este es el cálculo canónico del saldo.
     *
     * @param Client|int $client Cliente o ID del cliente
     * @return int Saldo en segundos (puede ser negativo si hay movimientos negativos previos)
     */
    public function getBalanceSeconds(Client|int $client): int
    {
        // Obtener el cliente si se pasó un ID
        if (is_int($client)) {
            $client = Client::findOrFail($client);
        }

        // Calcular saldo desde el ledger (fuente de verdad)
        // Suma todos los amount_seconds (positivos y negativos)
        return (int) BalanceMovement::where('client_id', $client->id)
            ->sum('amount_seconds');
    }

    /**
     * Obtiene o crea el perfil del cliente.
     *
     * Garantiza que existe un ClientProfile para el cliente.
     * Se usa internamente para actualizar el agregado balance_seconds.
     *
     * @param Client $client Cliente
     * @return ClientProfile Perfil del cliente
     */
    private function getOrCreateProfile(Client $client): ClientProfile
    {
        // Usar firstOrCreate para evitar race conditions y duplicados
        return ClientProfile::firstOrCreate(
            ['client_id' => $client->id],
            ['balance_seconds' => 0]
        );
    }
}
