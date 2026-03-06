<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

/**
 * Controller para gestionar usuarios (técnicos y clientes) desde el panel admin.
 *
 * Controller fino: solo orquesta, sin lógica de negocio.
 * La lógica de negocio debe estar en Services.
 */
class AdminUserController extends Controller
{
    /**
     * Lista todos los usuarios con paginación.
     *
     * @return View
     */
    public function index(): View
    {
        $users = User::orderBy('created_at', 'desc')
            ->whereNot('role','admin')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Almacena un nuevo usuario.
     *
     * Controller fino: solo valida, crea y redirige.
     *
     * @param StoreUserRequest $request
     * @return RedirectResponse
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
            'is_active' => $request->input('is_active', true),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Usuario {$user->name} creado correctamente.");
    }

    /**
     * Muestra el formulario para editar un usuario.
     *
     * @param User $user
     * @return View
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Actualiza un usuario existente.
     *
     * Controller fino: solo valida, actualiza y redirige.
     * Password es opcional (solo se actualiza si se proporciona).
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->only(['name', 'email', 'role', 'is_active']);

        // Actualizar password solo si se proporciona
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', "Usuario {$user->name} actualizado correctamente.");
    }

    /**
     * Alterna el estado activo/inactivo de un usuario.
     *
     * Regla: is_active=false bloquea acceso al sistema.
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function toggleActive(User $user): RedirectResponse
    {
        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'activado' : 'desactivado';

        return redirect()->route('admin.users.index')
            ->with('success', "Usuario {$user->name} {$status} correctamente.");
    }
}
