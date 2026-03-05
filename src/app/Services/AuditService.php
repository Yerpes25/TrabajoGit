<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para gestionar auditoría global del sistema.
 *
 * Reglas de negocio:
 * - audit_logs es append-only (no se edita)
 * - Retención mínima: 5 años (documentado, el borrado/archivado sería tarea futura)
 * - La auditoría no debe romper la operación principal: si falla el log,
 *   se captura la excepción y se registra un warning, pero no se interrumpe el flujo
 */
class AuditService
{
    /**
     * Registra un evento de auditoría.
     *
     * Crea un registro en audit_logs con la información del evento.
     * Si falla la creación del log, captura la excepción y registra un warning
     * sin interrumpir el flujo principal de la aplicación.
     *
     * @param string $event Tipo de evento (ej: 'saldo_change', 'work_report_validated', 'evidence_uploaded')
     * @param int|null $actorId ID del usuario que realizó la acción (null para acciones del sistema)
     * @param string|null $entityType Tipo de entidad afectada (ej: 'WorkReport', 'Client', 'BalanceMovement')
     * @param int|null $entityId ID de la entidad afectada
     * @param array|null $payload Información adicional en formato array
     * @param string|null $ip Dirección IP desde la que se realizó la acción
     * @param string|null $userAgent Agente de usuario del navegador/cliente
     * @return AuditLog|null Log creado o null si falló (sin interrumpir flujo)
     */
    public function log(
        string $event,
        ?int $actorId = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $payload = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): ?AuditLog {
        try {
            return AuditLog::create([
                'event' => $event,
                'actor_id' => $actorId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'payload' => $payload,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Regla: la auditoría no debe romper la operación principal
            // Si falla el log, registramos warning y continuamos sin interrumpir
            Log::warning('Error al crear audit log', [
                'event' => $event,
                'actor_id' => $actorId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'error' => $e->getMessage(),
            ]);

            // Retornar null en lugar de lanzar excepción
            return null;
        }
    }
}
