<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\WorkReport;
use App\Services\BalanceService;
use App\Services\AuditService;
use Illuminate\View\View;
use App\Models\Bonus;

/**
 * Controller para el dashboard de clientes.
 *
 * Controller fino: solo orquesta la vista con datos básicos, sin lógica de negocio.
 * La lógica de negocio debe estar en Services.
 * Regla: El cliente SOLO ve sus partes (work_reports.client_id = client_id asociado al usuario).
 * Regla: El cliente SOLO ve estados finished y validated.
 */
class ClientDashboardController extends Controller
{
    /**
     * Muestra el dashboard de cliente con resumen de partes y saldo.
     *
     * Regla: Usa la relación FK user_id (no email) para obtener el cliente.
     * Esto asegura que cambiar el email del usuario no rompa la relación.
     *
     * @return View
     */
    public function index(): View
    {
        $user = auth()->user();

        // Obtener el cliente asociado al usuario por FK (user_id)
        // Regla: Esta relación es robusta y no se rompe si cambia el email
        $client = $user->client;

        $activeBonuses = Bonus::where('is_active', true)
            ->withCount('bonusIssues') // <--- Cambiado para que coincida con tu modelo Bonus.php
            ->orderBy('bonus_issues_count', 'desc')
            ->get();

        $maxHoursId = $activeBonuses->sortByDesc('seconds_total')->first()->id ?? null;

        if (!$client) {
            // Si no hay cliente asociado, mostrar vista vacía
            return view('dashboard.client', [
                'client' => null,
                'finished' => 0,
                'validated' => 0,
                'recentWorkReports' => collect(),
                'balanceSeconds' => 0,
                'activeBonuses' => $activeBonuses,
                'maxHoursId' => $maxHoursId,
            ]);
        }

        // Contar partes por estado (solo finished y validated) - optimización: una sola query con groupBy
        // NOTE: Evita 2 queries separadas, más eficiente
        $workReportsByStatus = WorkReport::where('client_id', $client->id)
            ->whereIn('status', [WorkReport::STATUS_FINISHED, WorkReport::STATUS_VALIDATED])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $finished = $workReportsByStatus[WorkReport::STATUS_FINISHED] ?? 0;
        $validated = $workReportsByStatus[WorkReport::STATUS_VALIDATED] ?? 0;

        // Partes recientes (últimos 5, solo finished y validated) - eager loading para evitar N+1
        // NOTE: Cargar relación technician de una vez para evitar queries adicionales en el loop
        $recentWorkReports = WorkReport::where('client_id', $client->id)
            ->whereIn('status', [WorkReport::STATUS_FINISHED, WorkReport::STATUS_VALIDATED])
            ->with('technician')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Obtener saldo actual (solo lectura)
        $balanceService = new BalanceService(new AuditService());
        $balanceSeconds = $balanceService->getBalanceSeconds($client);

        return view('dashboard.client', compact(
            'client',
            'finished',
            'validated',
            'recentWorkReports',
            'balanceSeconds',
            'activeBonuses',
            'maxHoursId'
        ));
    }
}
