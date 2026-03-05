<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBonusRequest;
use App\Http\Requests\Admin\UpdateBonusRequest;
use App\Models\Bonus;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller para gestionar el catálogo de bonos desde el panel admin.
 *
 * Controller fino: solo orquesta, sin lógica de negocio.
 * Regla: "Eliminar" = archivar (is_active=false) si tiene emisiones, o eliminar físicamente si no tiene.
 */
class AdminBonusController extends Controller
{
    /**
     * Lista todos los bonos con paginación.
     *
     * @return View
     */
    public function index(): View
    {
        $bonuses = Bonus::orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.bonuses.index', compact('bonuses'));
    }

    /**
     * Muestra el formulario para crear un nuevo bono.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.bonuses.create');
    }

    /**
     * Almacena un nuevo bono.
     *
     * Controller fino: solo valida, crea y redirige.
     *
     * @param StoreBonusRequest $request
     * @return RedirectResponse
     */
    public function store(StoreBonusRequest $request): RedirectResponse
    {
        $bonus = Bonus::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'seconds_total' => $request->input('seconds_total'),
            'is_active' => $request->input('is_active', true),
        ]);

        return redirect()->route('admin.bonuses.index')
            ->with('success', "Bono {$bonus->name} creado correctamente.");
    }

    /**
     * Muestra el detalle de un bono.
     *
     * @param Bonus $bonus
     * @return View
     */
    public function show(Bonus $bonus): View
    {
        // Cargar emisiones del bono
        $bonusIssues = $bonus->bonusIssues()
            ->with(['client', 'issuer'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.bonuses.show', compact('bonus', 'bonusIssues'));
    }

    /**
     * Muestra el formulario para editar un bono.
     *
     * @param Bonus $bonus
     * @return View
     */
    public function edit(Bonus $bonus): View
    {
        return view('admin.bonuses.edit', compact('bonus'));
    }

    /**
     * Actualiza un bono existente.
     *
     * Controller fino: solo valida, actualiza y redirige.
     *
     * @param UpdateBonusRequest $request
     * @param Bonus $bonus
     * @return RedirectResponse
     */
    public function update(UpdateBonusRequest $request, Bonus $bonus): RedirectResponse
    {
        $bonus->update($request->only([
            'name',
            'description',
            'seconds_total',
            'is_active',
        ]));

        return redirect()->route('admin.bonuses.index')
            ->with('success', "Bono {$bonus->name} actualizado correctamente.");
    }

    /**
     * "Elimina" un bono (archiva si tiene emisiones, elimina si no).
     *
     * Regla: Si tiene emisiones (bonus_issues), NO borrar físicamente; marcar is_active=false (archivar).
     * Si NO tiene emisiones, permitir borrado físico.
     *
     * @param Bonus $bonus
     * @return RedirectResponse
     */
    public function destroy(Bonus $bonus): RedirectResponse
    {
        if ($bonus->hasIssues()) {
            // Archivar en vez de eliminar
            $bonus->update(['is_active' => false]);
            $message = "Bono {$bonus->name} archivado correctamente (tiene emisiones asociadas).";
        } else {
            // Eliminar físicamente si no tiene emisiones
            $bonus->delete();
            $message = "Bono {$bonus->name} eliminado correctamente.";
        }

        return redirect()->route('admin.bonuses.index')
            ->with('success', $message);
    }
}
