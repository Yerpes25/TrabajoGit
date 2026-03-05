<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkReport;
use Illuminate\View\View;

/**
 * Controller para el dashboard de administradores.
 *
 * Controller fino: solo orquesta la vista con KPIs básicos, sin lógica de negocio.
 * La lógica de negocio debe estar en Services.
 */
class AdminDashboardController extends Controller
{
    /**
     * Muestra el dashboard de administrador con KPIs básicos.
     *
     * @return View
     */
    public function index(): View
    {
        // KPIs básicos (sin lógica de negocio, solo conteos)
        // NOTE: Optimización performance - queries simples de count son eficientes
        $totalClients = Client::count();
        $totalTechnicians = User::where('role', 'technician')->count();
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();

        // Partes por estado - optimización: una sola query con groupBy en lugar de múltiples count()
        // NOTE: Esto evita N queries separadas y es más eficiente con muchos registros
        $workReportsByStatus = WorkReport::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $workReportsInProgress = $workReportsByStatus[WorkReport::STATUS_IN_PROGRESS] ?? 0;
        $workReportsFinished = $workReportsByStatus[WorkReport::STATUS_FINISHED] ?? 0;
        $workReportsValidated = $workReportsByStatus[WorkReport::STATUS_VALIDATED] ?? 0;

        // Partes recientes (últimos 10) - eager loading para evitar N+1
        // NOTE: Cargar relaciones client y technician de una vez para evitar queries adicionales en el loop
        $recentWorkReports = WorkReport::with(['client', 'technician'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.admin', compact(
            'totalClients',
            'totalTechnicians',
            'totalUsers',
            'activeUsers',
            'workReportsInProgress',
            'workReportsFinished',
            'workReportsValidated',
            'recentWorkReports'
        ));
    }
}
