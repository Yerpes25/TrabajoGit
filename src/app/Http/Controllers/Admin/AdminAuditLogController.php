<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller para consultar auditoría desde el panel admin.
 *
 * Controller fino: solo orquesta consultas con filtros, sin lógica de negocio.
 * Admin puede consultar logs con filtros por: evento, actor, entidad, fechas.
 */
class AdminAuditLogController extends Controller
{
    /**
     * Lista los logs de auditoría con filtros y paginación.
     *
     * Filtros disponibles: evento, actor, entidad (tipo + id), rango fechas.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = AuditLog::with('actor');

        // Filtro por evento
        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        // Filtro por actor
        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->input('actor_id'));
        }

        // Filtro por tipo de entidad
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->input('entity_type'));
        }

        // Filtro por ID de entidad
        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->input('entity_id'));
        }

        // Filtro por rango de fechas (created_at)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $auditLogs = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Cargar datos para filtros
        $actors = User::orderBy('name')->get();
        $events = AuditLog::distinct()->pluck('event')->sort();
        $entityTypes = AuditLog::distinct()->whereNotNull('entity_type')->pluck('entity_type')->sort();

        return view('admin.audit-logs.index', compact('auditLogs', 'actors', 'events', 'entityTypes'));
    }
}
