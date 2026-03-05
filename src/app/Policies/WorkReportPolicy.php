<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkReport;
use Illuminate\Auth\Access\Response;

/**
 * Policy para gestionar permisos de WorkReport.
 *
 * Reglas de autorización:
 * - Admin: puede ver/gestionar todo
 * - Technician: solo puede view/update/start/pause/resume/finish work_reports donde technician_id = auth()->id()
 * - Client: solo puede view work_reports cuyo client_id = auth()->user()->client->id
 */
class WorkReportPolicy
{
    /**
     * Determina si el usuario puede ver cualquier parte.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Admin puede ver todos, technician y client ven solo los suyos (filtrado en query)
        return true;
    }

    /**
     * Determina si el usuario puede ver un parte específico.
     *
     * Reglas:
     * - Admin: puede ver cualquier parte
     * - Technician: solo partes donde technician_id = auth()->id()
     * - Client: solo partes donde client_id = auth()->user()->client->id
     *
     * @param User $user
     * @param WorkReport $workReport
     * @return bool|Response
     */
    public function view(User $user, WorkReport $workReport): bool|Response
    {
        // Admin: puede ver cualquier parte
        if ($user->isAdmin()) {
            return true;
        }

        // Technician: solo sus partes
        if ($user->isTechnician()) {
            return $workReport->technician_id === $user->id;
        }

        // Client: solo sus partes y solo si están finished o validated
        if ($user->isClient()) {
            $client = $user->client;
            if (!$client) {
                return false;
            }
            
            // Verificar que el parte pertenece al cliente
            if ($workReport->client_id !== $client->id) {
                return false;
            }

            // Regla: Cliente solo puede ver partes finished o validated
            return in_array($workReport->status, [
                WorkReport::STATUS_FINISHED,
                WorkReport::STATUS_VALIDATED,
            ]);
        }

        return false;
    }

    /**
     * Determina si el usuario puede crear partes.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Solo técnicos pueden crear partes
        return $user->isTechnician();
    }

    /**
     * Determina si el usuario puede actualizar un parte.
     *
     * Reglas:
     * - Admin: puede actualizar cualquier parte
     * - Technician: solo partes donde technician_id = auth()->id()
     *
     * @param User $user
     * @param WorkReport $workReport
     * @return bool|Response
     */
    public function update(User $user, WorkReport $workReport): bool|Response
    {
        // Admin: puede actualizar cualquier parte
        if ($user->isAdmin()) {
            return true;
        }

        // Technician: solo sus partes
        if ($user->isTechnician()) {
            return $workReport->technician_id === $user->id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede eliminar un parte.
     *
     * @param User $user
     * @param WorkReport $workReport
     * @return bool|Response
     */
    public function delete(User $user, WorkReport $workReport): bool|Response
    {
        // Solo admin puede eliminar partes
        return $user->isAdmin();
    }

    /**
     * Determina si el usuario puede iniciar un parte.
     *
     * Reglas:
     * - Admin: puede iniciar cualquier parte
     * - Technician: solo partes donde technician_id = auth()->id()
     *
     * @param User $user
     * @param WorkReport $workReport
     * @return bool|Response
     */
    public function start(User $user, WorkReport $workReport): bool|Response
    {
        // Admin: puede iniciar cualquier parte
        if ($user->isAdmin()) {
            return true;
        }

        // Technician: solo sus partes
        if ($user->isTechnician()) {
            return $workReport->technician_id === $user->id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede pausar un parte.
     *
     * Reglas:
     * - Admin: puede pausar cualquier parte
     * - Technician: solo partes donde technician_id = auth()->id()
     *
     * @param User $user
     * @param WorkReport $workReport
     * @return bool|Response
     */
    public function pause(User $user, WorkReport $workReport): bool|Response
    {
        // Admin: puede pausar cualquier parte
        if ($user->isAdmin()) {
            return true;
        }

        // Technician: solo sus partes
        if ($user->isTechnician()) {
            return $workReport->technician_id === $user->id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede reanudar un parte.
     *
     * Reglas:
     * - Admin: puede reanudar cualquier parte
     * - Technician: solo partes donde technician_id = auth()->id()
     *
     * @param User $user
     * @param WorkReport $workReport
     * @return bool|Response
     */
    public function resume(User $user, WorkReport $workReport): bool|Response
    {
        // Admin: puede reanudar cualquier parte
        if ($user->isAdmin()) {
            return true;
        }

        // Technician: solo sus partes
        if ($user->isTechnician()) {
            return $workReport->technician_id === $user->id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede finalizar un parte.
     *
     * Reglas:
     * - Admin: puede finalizar cualquier parte
     * - Technician: solo partes donde technician_id = auth()->id()
     *
     * @param User $user
     * @param WorkReport $workReport
     * @return bool|Response
     */
    public function finish(User $user, WorkReport $workReport): bool|Response
    {
        // Admin: puede finalizar cualquier parte
        if ($user->isAdmin()) {
            return true;
        }

        // Technician: solo sus partes
        if ($user->isTechnician()) {
            return $workReport->technician_id === $user->id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede validar un parte.
     *
     * Reglas:
     * - Admin: puede validar cualquier parte
     * - Technician: solo partes donde technician_id = auth()->id()
     *
     * NOTE: La validación descuenta saldo, por lo que solo técnicos y admin pueden validar.
     *
     * @param User $user
     * @param WorkReport $workReport
     * @return bool|Response
     */
    public function validate(User $user, WorkReport $workReport): bool|Response
    {
        // Admin: puede validar cualquier parte
        if ($user->isAdmin()) {
            return true;
        }

        // Technician: solo sus partes
        if ($user->isTechnician()) {
            return $workReport->technician_id === $user->id;
        }

        return false;
    }
}
