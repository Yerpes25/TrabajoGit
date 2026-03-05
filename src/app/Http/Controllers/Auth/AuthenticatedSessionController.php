<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * Regla: Redirigir según el rol del usuario después del login:
     * - admin -> /admin
     * - technician -> /technician
     * - client -> /client
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Actualizar last_login_at para auditoría
        $user->update(['last_login_at' => now()]);

        // Redirigir según el rol (regla: post-login redirect por rol)
        $redirectRoute = match ($user->role) {
            'admin' => route('admin.dashboard'),
            'technician' => route('technician.dashboard'),
            'client' => route('client.dashboard'),
            default => route('dashboard'),
        };

        return redirect()->intended($redirectRoute);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
