<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWorkReportRequest;
use App\Models\Client;
use App\Models\User;
use App\Models\WorkReport;
use App\Services\AuditService;
use App\Services\BalanceService;
use App\Services\WorkReportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller para gestionar partes de trabajo desde el panel admin.
 *
 * Controller fino: solo orquesta consultas y vistas, sin lógica de negocio.
 * Admin puede ver TODOS los work_reports con filtros.
 */
class AdminWorkReportController extends Controller
{
    use AuthorizesRequests;

    private WorkReportService $workReportService;

    public function __construct(WorkReportService $workReportService)
    {
        $this->workReportService = $workReportService;
    }

    /**
     * Lista todos los partes con filtros y paginación.
     *
     * Filtros disponibles: cliente, técnico, estado, rango fechas.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Optimización performance: eager loading de relaciones para evitar N+1
        // NOTE: La vista accede a $report->client->name y $report->technician->name en el loop
        // Sin eager loading, haría 1 query por cada relación por cada parte (N+1 problem)
        $query = WorkReport::with(['client', 'technician', 'validator']);

        // Filtro por cliente
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        // Filtro por técnico
        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->input('technician_id'));
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtro por rango de fechas (created_at)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $workReports = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Cargar datos para filtros
        $clients = Client::join('users', 'users.id', 'clients.user_id')
            ->orderBy('name')
            ->select('clients.*', 'users.name as name')
            ->get();
        $technicians = User::where('role', 'technician')->orderBy('name')->get();

        return view('admin.work-reports.index', compact('workReports', 'clients', 'technicians'));
    }

    /**
     * Muestra el detalle de un parte: información, eventos y evidencias.
     *
     * Controller fino: solo carga relaciones y pasa a la vista.
     *
     * @param WorkReport $workReport
     * @return View
     */
    public function show(WorkReport $workReport): View
    {
        // Optimización performance: eager loading completo de todas las relaciones
        // NOTE: La vista accede a eventos con creator y evidencias con uploader
        // Sin eager loading anidado, haría queries adicionales en el loop (N+1 problem)
        $workReport->load([
            'client',
            'technician',
            'validator',
            'events.creator', // Eager loading anidado: evita N+1 al acceder a $event->creator->name
            'evidences.uploader', // Eager loading anidado: evita N+1 al acceder a $evidence->uploader->name
        ]);

        return view('admin.work-reports.show', compact('workReport'));
    }

    /**
     * Muestra el formulario para editar un parte.
     *
     * Controller fino: solo verifica permisos y carga la vista.
     *
     * @param WorkReport $workReport
     * @return View
     */
    public function edit(WorkReport $workReport): View
    {
        // Verificar permisos mediante Policy
        $this->authorize('update', $workReport);

        return view('admin.work-reports.edit', compact('workReport'));
    }

    /**
     * Actualiza un parte existente.
     *
     * Controller fino: solo valida, delega a WorkReportService y redirige.
     * Regla: NO se permite cambiar tiempos manualmente (solo vía cronómetro).
     * Regla: La lógica de negocio (validación de estado, diff, evento, auditoría) está en WorkReportService.
     *
     * @param UpdateWorkReportRequest $request
     * @param WorkReport $workReport
     * @return RedirectResponse
     */
    public function update(UpdateWorkReportRequest $request, WorkReport $workReport): RedirectResponse
    {
        // Verificar permisos mediante Policy
        $this->authorize('update', $workReport);

        try {
            // Delegar a WorkReportService (lógica de negocio)
            // Regla: updateDetails() valida estado, calcula diff, crea evento y auditoría
            $this->workReportService->updateDetails(
                $workReport,
                $request->only(['title', 'description', 'summary']),
                auth()->id()
            );

            return redirect()->route('admin.work-reports.show', $workReport)
                ->with('success', 'Parte actualizado correctamente.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.work-reports.edit', $workReport)
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}
