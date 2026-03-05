<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Models\WorkReport;
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
        $clients = Client::orderBy('name')->get();
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
}
