<?php

namespace App\Http\Controllers;

use App\Models\WorkReport;
use App\Models\Client;
use Illuminate\View\View;

/**
 * Controller para el dashboard de técnicos.
 *
 * Controller fino: solo orquesta la vista con datos básicos, sin lógica de negocio.
 * La lógica de negocio debe estar en Services.
 * Regla: El técnico SOLO ve sus partes (technician_id = auth()->id()).
 */
class TechnicianDashboardController extends Controller
{
    /**
     * Muestra el dashboard de técnico con resumen de partes.
     *
     * @return View
     */
    public function index(): View
    {
        $technicianId = auth()->id();

        // Contar partes por estado - optimización: una sola query con groupBy
        // NOTE: Evita 4 queries separadas, más eficiente con muchos partes
        $workReportsByStatus = WorkReport::where('technician_id', $technicianId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $inProgress = $workReportsByStatus[WorkReport::STATUS_IN_PROGRESS] ?? 0;
        $paused = $workReportsByStatus[WorkReport::STATUS_PAUSED] ?? 0;
        $finished = $workReportsByStatus[WorkReport::STATUS_FINISHED] ?? 0;
        $validated = $workReportsByStatus[WorkReport::STATUS_VALIDATED] ?? 0;

        // Partes recientes (últimos 5) - eager loading para evitar N+1
        // NOTE: Cargar relación client de una vez para evitar queries adicionales en el loop
        $recentWorkReports = WorkReport::where('technician_id', $technicianId)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // BONUS: Si quieres mostrar clientes con bonos
        $clientsWithBonuses = Client::whereHas('bonusIssues')
            ->withCount('bonusIssues')
            ->get();

        return view('dashboard.technician', compact(
            'inProgress',
            'paused',
            'finished',
            'validated',
            'recentWorkReports',
            'clientsWithBonuses'
        ));
    }
}
