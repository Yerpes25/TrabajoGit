<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTechnicianRequest;
use App\Http\Requests\Admin\UpdateTechnicianRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller para gestionar técnicos desde el panel admin.
 *
 * Controller fino: solo orquesta, sin lógica de negocio.
 * La lógica de negocio está en UserService.
 * Regla: "Eliminar" = desactivar (is_active=false) si hay actividad asociada.
 */
class AdminTechnicianController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Lista todos los técnicos con paginación.
     *
     * @return View
     */
    public function index(): View
    {
        $technicians = User::where('role', 'technician')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.technicians.index', compact('technicians'));
    }

    /**
     * Muestra el formulario para crear un nuevo técnico.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.technicians.create');
    }

    /**
     * Almacena un nuevo técnico.
     *
     * Controller fino: solo valida, crea y redirige.
     * La lógica de negocio está en UserService.
     *
     * @param StoreTechnicianRequest $request
     * @return RedirectResponse
     */
    public function store(StoreTechnicianRequest $request): RedirectResponse
    {
        $technician = $this->userService->createTechnician([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'is_active' => $request->input('is_active', true),
        ]);

        return redirect()->route('admin.technicians.index')
            ->with('success', "Técnico {$technician->name} creado correctamente.");
    }

    /**
     * Muestra el detalle de un técnico.
     *
     * @param User $technician
     * @return View
     */
    public function show(User $technician): View
    {
        // Verificar que es técnico
        if ($technician->role !== 'technician') {
            abort(404);
        }

        // Cargar partes de trabajo del técnico
        $workReports = $technician->workReports()
            ->with(['client'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.technicians.show', compact('technician', 'workReports'));
    }

    /**
     * Muestra el formulario para editar un técnico.
     *
     * @param User $technician
     * @return View
     */
    public function edit(User $technician): View
    {
        // Verificar que es técnico
        if ($technician->role !== 'technician') {
            abort(404);
        }

        return view('admin.technicians.edit', compact('technician'));
    }

    /**
     * Actualiza un técnico existente.
     *
     * Controller fino: solo valida, actualiza y redirige.
     * La lógica de negocio está en UserService.
     *
     * @param UpdateTechnicianRequest $request
     * @param User $technician
     * @return RedirectResponse
     */
    public function update(UpdateTechnicianRequest $request, User $technician): RedirectResponse
    {
        // Verificar que es técnico
        if ($technician->role !== 'technician') {
            abort(404);
        }

        $this->userService->updateTechnician($technician, $request->only([
            'name',
            'email',
            'password',
            'is_active',
        ]));

        return redirect()->route('admin.technicians.index')
            ->with('success', "Técnico {$technician->name} actualizado correctamente.");
    }

    /**
     * "Elimina" un técnico (desactiva si tiene actividad).
     *
     * Regla: Si tiene actividad (work_reports), solo se desactiva (is_active=false).
     * Esto mantiene la integridad referencial de los datos históricos.
     *
     * @param User $technician
     * @return RedirectResponse
     */
    public function destroy(User $technician): RedirectResponse
    {
        // Verificar que es técnico
        if ($technician->role !== 'technician') {
            abort(404);
        }

        $hasActivity = $this->userService->hasActivity($technician);

        if ($hasActivity) {
            // Desactivar en vez de eliminar
            $this->userService->deactivate($technician);
            $message = "Técnico {$technician->name} desactivado correctamente (tiene actividad asociada).";
        } else {
            // Si no tiene actividad, se puede eliminar físicamente
            $technician->delete();
            $message = "Técnico {$technician->name} eliminado correctamente.";
        }

        return redirect()->route('admin.technicians.index')
            ->with('success', $message);
    }
}
