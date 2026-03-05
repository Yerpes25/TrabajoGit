<?php

namespace App\Policies;

use App\Models\Evidence;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EvidencePolicy
{
    /**
     * Determina si el usuario puede ver cualquier evidencia.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Admin puede ver todas, technician y client ven solo las suyas (filtrado en query)
        return true;
    }

    /**
     * Determina si el usuario puede ver una evidencia específica.
     *
     * Reglas:
     * - Admin: puede ver cualquier evidencia
     * - Technician: solo evidencias de partes donde work_reports.technician_id = auth()->id()
     * - Client: solo evidencias de partes del cliente autenticado y solo si está finished o validated
     *
     * @param User $user
     * @param Evidence $evidence
     * @return bool|Response
     */
    public function view(User $user, Evidence $evidence): bool|Response
    {
        // Cargar relaciones necesarias si no están cargadas
        if (!$evidence->relationLoaded('workReport')) {
            $evidence->load('workReport');
        }

        $workReport = $evidence->workReport;

        // Admin: puede ver cualquier evidencia
        if ($user->isAdmin()) {
            return true;
        }

        // Technician: solo evidencias de sus partes
        if ($user->isTechnician()) {
            return $workReport->technician_id === $user->id;
        }

        // Client: solo evidencias de sus partes y solo si está finished o validated
        if ($user->isClient()) {
            $client = $user->client;
            
            // Si el usuario no tiene cliente asociado, no puede ver
            if (!$client) {
                return false;
            }

            // Verificar que el parte pertenece al cliente
            if ($workReport->client_id !== $client->id) {
                return false;
            }

            // Solo puede ver si el parte está finished o validated
            return in_array($workReport->status, [
                \App\Models\WorkReport::STATUS_FINISHED,
                \App\Models\WorkReport::STATUS_VALIDATED,
            ]);
        }

        return false;
    }

    /**
     * Determina si el usuario puede crear/subir evidencias.
     *
     * Reglas:
     * - Admin: puede subir evidencias a cualquier parte
     * - Technician: solo puede subir evidencias a partes donde work_reports.technician_id = auth()->id()
     *
     * @param User $user
     * @param Evidence|null $evidence Si se proporciona, verifica el parte asociado
     * @return bool|Response
     */
    public function create(User $user, ?Evidence $evidence = null): bool|Response
    {
        // Solo técnicos y admin pueden subir evidencias
        if (!$user->isTechnician() && !$user->isAdmin()) {
            return false;
        }

        // Si se proporciona evidencia, verificar el parte
        if ($evidence && $evidence->relationLoaded('workReport')) {
            $workReport = $evidence->workReport;
            if ($user->isAdmin()) {
                return true;
            }
            return $workReport->technician_id === $user->id;
        }

        return true;
    }

    /**
     * Determina si el usuario puede subir una evidencia a un parte específico.
     *
     * Reglas:
     * - Admin: puede subir a cualquier parte
     * - Technician: solo a partes donde work_reports.technician_id = auth()->id()
     *
     * @param User $user
     * @param \App\Models\WorkReport $workReport
     * @return bool|Response
     */
    public function upload(User $user, \App\Models\WorkReport $workReport): bool|Response
    {
        // Admin: puede subir a cualquier parte
        if ($user->isAdmin()) {
            return true;
        }

        // Technician: solo a sus partes
        if ($user->isTechnician()) {
            return $workReport->technician_id === $user->id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede actualizar una evidencia.
     *
     * @param User $user
     * @param Evidence $evidence
     * @return bool|Response
     */
    public function update(User $user, Evidence $evidence): bool|Response
    {
        // Por ahora, las evidencias no se pueden actualizar
        return false;
    }

    /**
     * Determina si el usuario puede eliminar una evidencia.
     *
     * Reglas:
     * - Admin: puede eliminar cualquier evidencia
     * - Technician: solo evidencias de partes donde work_reports.technician_id = auth()->id()
     *
     * @param User $user
     * @param Evidence $evidence
     * @return bool|Response
     */
    public function delete(User $user, Evidence $evidence): bool|Response
    {
        // Cargar relaciones necesarias si no están cargadas
        if (!$evidence->relationLoaded('workReport')) {
            $evidence->load('workReport');
        }

        $workReport = $evidence->workReport;

        // Admin: puede eliminar cualquier evidencia
        if ($user->isAdmin()) {
            return true;
        }

        // Technician: solo evidencias de sus partes
        if ($user->isTechnician()) {
            return $workReport->technician_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Evidence $evidence): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Evidence $evidence): bool
    {
        return false;
    }

    /**
     * Determina si el usuario puede descargar una evidencia.
     *
     * Reglas de autorización:
     * - Admin: puede descargar cualquier evidencia
     * - Technician: solo evidencias de partes donde work_reports.technician_id = auth()->id()
     * - Client: solo evidencias de partes del cliente autenticado por FK (auth()->user()->client->id)
     *   y solo si el parte está finished o validated
     *
     * @param User $user Usuario autenticado
     * @param Evidence $evidence Evidencia a descargar
     * @return bool|Response
     */
    public function download(User $user, Evidence $evidence): bool|Response
    {
        // Cargar relaciones necesarias si no están cargadas
        if (!$evidence->relationLoaded('workReport')) {
            $evidence->load('workReport');
        }

        $workReport = $evidence->workReport;

        // Admin: puede descargar cualquier evidencia
        if ($user->isAdmin()) {
            return true;
        }

        // Technician: solo evidencias de sus partes
        if ($user->isTechnician()) {
            return $workReport->technician_id === $user->id;
        }

        // Client: solo evidencias de sus partes y solo si está finished o validated
        if ($user->isClient()) {
            $client = $user->client;
            
            // Si el usuario no tiene cliente asociado, no puede descargar
            if (!$client) {
                return false;
            }

            // Verificar que el parte pertenece al cliente
            if ($workReport->client_id !== $client->id) {
                return false;
            }

            // Solo puede descargar si el parte está finished o validated
            return in_array($workReport->status, [
                \App\Models\WorkReport::STATUS_FINISHED,
                \App\Models\WorkReport::STATUS_VALIDATED,
            ]);
        }

        // Por defecto, denegar acceso
        return false;
    }
}
