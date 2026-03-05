<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar que el usuario autenticado tiene un rol específico.
 *
 * Uso: ->middleware(['auth', 'role:admin'])
 * Regla: Si el usuario no tiene el rol requerido, redirige a su dashboard o devuelve 403.
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Verifica que el usuario autenticado tenga el rol especificado.
     * Si no tiene el rol, redirige a su dashboard correspondiente o devuelve 403.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles Roles permitidos (puede ser múltiple: role:admin,technician)
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Verificar que el usuario está autenticado (debe estar después de 'auth')
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Verificar que el usuario tiene uno de los roles permitidos
        if (!in_array($user->role, $roles)) {
            // Si no tiene el rol, redirigir a su dashboard correspondiente
            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'technician' => redirect()->route('technician.dashboard'),
                'client' => redirect()->route('client.dashboard'),
                default => abort(403, 'No tienes permiso para acceder a esta página.'),
            };
        }

        return $next($request);
    }
}
