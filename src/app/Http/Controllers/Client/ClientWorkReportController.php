<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\WorkReport;
use App\Services\EvidenceService;
use App\Services\AuditService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;

/**
 * Controller para gestionar partes de trabajo desde el portal del cliente.
 *
 * Controller fino: solo orquesta, sin lógica de negocio.
 * Autorización centralizada en WorkReportPolicy.
 * Regla: El cliente SOLO ve estados finished y validated (verificado en Policy).
 */
class ClientWorkReportController extends Controller
{
    use AuthorizesRequests;
    private EvidenceService $evidenceService;

    public function __construct(EvidenceService $evidenceService)
    {
        $this->evidenceService = $evidenceService;
    }

    /**
     * Obtiene el cliente asociado al usuario autenticado.
     *
     * Regla: Usa la relación FK user_id (no email) para obtener el cliente.
     * Esto asegura que cambiar el email del usuario no rompa la relación.
     *
     * @return Client|null
     */
    private function getClientForUser(): ?Client
    {
        $user = auth()->user();
        return $user->client;
    }

    /**
     * Lista los partes del cliente autenticado.
     *
     * Regla: Solo muestra partes donde client_id = cliente asociado al usuario.
     * Regla: El cliente solo puede ver partes en estado validated.
     *
     * @return View
     */
    public function index(): View
    {
        $client = $this->getClientForUser();

        // Si el usuario no tiene cliente asociado, devolvemos paginación vacía
        if (!$client) {
            $workReports = collect()->paginate(15);
            return view('client.work-reports.index', compact('workReports'));
        }

        $workReports = WorkReport::query()
            ->where('client_id', $client->id)
            ->where('status', WorkReport::STATUS_VALIDATED) // el cliente solo ve validados
            ->with(['technician', 'validator']) // evita N+1 queries
            ->latest() // equivalente a orderBy('created_at','desc')
            ->paginate(15);

        return view('client.work-reports.index', compact('workReports'));
    }

    /**
     * Muestra el detalle de un parte.
     *
     * Regla: Solo puede ver sus propios partes (verificación de pertenencia).
     * Regla: Solo puede ver estados finished y validated.
     *
     * @param WorkReport $workReport
     * @return View
     */
    public function show(WorkReport $workReport): View
    {
        // Verificar permisos mediante Policy (incluye verificación de pertenencia y estado)
        $this->authorize('view', $workReport);

        $client = $this->getClientForUser();

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

        return view('client.work-reports.show', compact('workReport', 'client'));
    }
}
