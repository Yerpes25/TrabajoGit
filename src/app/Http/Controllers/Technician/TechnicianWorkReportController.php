<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\StoreWorkReportRequest;
use App\Http\Requests\Technician\UpdateWorkReportRequest;
use App\Models\Client;
use App\Models\WorkReport;
use App\Services\WorkReportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Controller para gestionar partes de trabajo desde el panel técnico.
 *
 * Controller fino: solo orquesta, sin lógica de negocio.
 * La lógica de negocio está en WorkReportService.
 * Autorización centralizada en WorkReportPolicy.
 */
class TechnicianWorkReportController extends Controller
{
    use AuthorizesRequests;
    private WorkReportService $workReportService;

    public function __construct(WorkReportService $workReportService)
    {
        $this->workReportService = $workReportService;
    }

    /**
     * Lista los partes del técnico autenticado con paginación.
     *
     * Regla: Solo muestra partes donde technician_id = auth()->id().
     *
     * @return View
     */
    public function index(): View
    {
        // Optimización performance: eager loading de client para evitar N+1
        // NOTE: La vista accede a $report->client->name en el loop
        // Sin eager loading, haría 1 query por cada parte para obtener el cliente
        $workReports = WorkReport::where('technician_id', Auth::user())
            ->with(['client'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('technician.work-reports.index', compact('workReports'));
    }

    /**
     * Muestra el formulario para crear un nuevo parte.
     *
     * @return View
     */
    public function create(): View
    {
        $clients = Client::with(['profile', 'user'])
            ->join('users', 'users.id', '=', 'clients.user_id')
            ->orderBy('users.name', 'desc')
            ->select(['clients.*', 'users.name'])
            ->get();
        return view('technician.work-reports.create', compact('clients'));
    }

    /**
     * Almacena un nuevo parte.
     *
     * Controller fino: solo valida, crea y redirige.
     * Regla: El técnico autenticado es el technician_id del parte.
     *
     * @param StoreWorkReportRequest $request
     * @return RedirectResponse
     */
    public function store(StoreWorkReportRequest $request): RedirectResponse
    {
        $client = Client::findOrFail($request->input('client_id'));
        $technician = Auth::id();

        $workReport = $this->workReportService->create(
            $client,
            $technician,
            $request->input('title'),
            $request->input('description')
        );

        // Disparar la notificación push por WebSocket al cliente tras la creación del parte
        event(new \App\Events\EventoNuevaIntervencion($workReport->title, $workReport->status, $workReport->client_id));

        return redirect()->route('technician.work-reports.show', $workReport)
            ->with('success', 'Parte creado correctamente.');
    }

    /**
     * Muestra el detalle de un parte.
     *
     * Regla: Solo puede ver sus propios partes (verificación de pertenencia).
     *
     * @param WorkReport $workReport
     * @return View
     */
    public function show(WorkReport $workReport): View
    {
        // Verificar permisos mediante Policy
        $this->authorize('view', $workReport);

        // Optimización performance: eager loading completo de todas las relaciones
        // NOTE: La vista accede a eventos con creator y evidencias con uploader
        // Sin eager loading anidado, haría queries adicionales en el loop (N+1 problem)
        $workReport->load([
            'client',
            'events.creator', // Eager loading anidado: evita N+1 al acceder a $event->creator->name
            'evidences.uploader', // Eager loading anidado: evita N+1 al acceder a $evidence->uploader->name
        ]);

        return view('technician.work-reports.show', compact('workReport'));
    }

    /**
     * Muestra el formulario para editar un parte.
     *
     * Regla: Solo puede editar sus propios partes.
     * Regla: Solo se permiten editar campos básicos (title, description, summary), no tiempos.
     *
     * @param WorkReport $workReport
     * @return View
     */
    public function edit(WorkReport $workReport): View
    {
        // Verificar permisos mediante Policy
        $this->authorize('update', $workReport);

        return view('technician.work-reports.edit', compact('workReport'));
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
                Auth::id()
            );

            // Disparar la notificación push por WebSocket al cliente tras la actualización
            event(new \App\Events\EventoNuevaIntervencion($workReport->title, $workReport->status, $workReport->client_id));

            return redirect()->route('technician.work-reports.show', $workReport)
                ->with('success', 'Parte actualizado correctamente.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('technician.work-reports.edit', $workReport)
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Inicia el cronómetro del parte (start).
     *
     * Controller fino: solo llama a WorkReportService.
     * Regla: Solo puede iniciar sus propios partes.
     *
     * @param WorkReport $workReport
     * @return RedirectResponse
     */
    public function start(WorkReport $workReport): RedirectResponse
    {
        // Verificar permisos mediante Policy
        $this->authorize('start', $workReport);

        try {
            $this->workReportService->start($workReport, Auth::id());
            return redirect()->route('technician.work-reports.show', $workReport)
                ->with('success', 'Parte iniciado correctamente.');
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            return redirect()->route('technician.work-reports.show', $workReport)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Pausa el cronómetro del parte (pause).
     *
     * Controller fino: solo llama a WorkReportService.
     * Regla: Solo puede pausar sus propios partes.
     *
     * @param WorkReport $workReport
     * @return RedirectResponse
     */
    public function pause(WorkReport $workReport): RedirectResponse
    {
        // Verificar permisos mediante Policy
        $this->authorize('pause', $workReport);

        try {
            $this->workReportService->pause($workReport, Auth::id());
            return redirect()->route('technician.work-reports.show', $workReport)
                ->with('success', 'Parte pausado correctamente.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('technician.work-reports.show', $workReport)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reanuda el cronómetro del parte (resume).
     *
     * Controller fino: solo llama a WorkReportService.
     * Regla: Solo puede reanudar sus propios partes.
     *
     * @param WorkReport $workReport
     * @return RedirectResponse
     */
    public function resume(WorkReport $workReport): RedirectResponse
    {
        // Verificar permisos mediante Policy
        $this->authorize('resume', $workReport);

        try {
            $this->workReportService->resume($workReport, Auth::id());
            return redirect()->route('technician.work-reports.show', $workReport)
                ->with('success', 'Parte reanudado correctamente.');
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            return redirect()->route('technician.work-reports.show', $workReport)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Finaliza el parte (finish).
     *
     * Controller fino: solo llama a WorkReportService.
     * Regla: Solo puede finalizar sus propios partes.
     *
     * @param WorkReport $workReport
     * @return RedirectResponse
     */
    public function finish(WorkReport $workReport): RedirectResponse
    {
        // Verificar permisos mediante Policy
        $this->authorize('finish', $workReport);

        try {
            $this->workReportService->finish($workReport, null, Auth::id());
            return redirect()->route('technician.work-reports.show', $workReport)
                ->with('success', 'Parte finalizado correctamente.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('technician.work-reports.show', $workReport)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Finaliza el parte (validate).
     *
     * Controller fino: solo llama a WorkReportService.
     * Regla: Solo puede finalizar sus propios partes.
     *
     * @param WorkReport $workReport
     * @return RedirectResponse
     */
    public function validate(WorkReport $workReport): RedirectResponse
    {
        $this->authorize('validate', $workReport);

        try {
            // Llamamos al nuevo método validate del servicio, no finish
            $this->workReportService->validate($workReport, Auth::id());

            // Determinar ruta según rol
            $user = Auth::user();
            if ($user->role === 'admin') {
                $route = route('admin.work-reports.show', $workReport);
            } else {
                // Por defecto asumimos que es técnico
                $route = route('technician.work-reports.show', $workReport);
            }

            return redirect($route)->with('success', 'Parte validado correctamente.');
        } catch (\InvalidArgumentException $e) {
            // Determinar ruta según rol también en caso de error
            $user = Auth::user();
            if ($user->role === 'admin') {
                $route = route('admin.work-reports.show', $workReport);
            } else {
                $route = route('technician.work-reports.show', $workReport);
            }

            return redirect($route)->with('error', $e->getMessage());
        }
    }
}
