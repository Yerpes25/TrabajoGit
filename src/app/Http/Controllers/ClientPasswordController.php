<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;

class ClientPasswordController extends Controller
{
    // --- NUEVA FUNCIÓN PARA SOLICITAR ACCESO DESDE EL LOGIN ---
    public function requestAccess(Request $request)
    {
        // 1. Validar que nos envían un email correcto
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // 2. Buscar al usuario por ese email
        $user = User::where('email', $request->email)->first();

        // Si el usuario existe y tiene un cliente asociado...
        if ($user && $user->client) {
            $client = $user->client;

            // 3. Generamos el mismo enlace del administrador
            $url = URL::temporarySignedRoute(
                'client.password.setup',
                now()->addHours(48),
                ['user' => $user->id]
            );

            // 4. Enviamos el mismo correo
            Mail::send('emails.setup-password', ['url' => $url, 'client' => $client], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Configura tu contraseña de acceso - Cubetic');
            });
        }

        // 5. Devolvemos siempre el mismo mensaje (por seguridad, para que nadie sepa si un correo existe o no en tu base de datos)
        return back()->with('status', 'Si tu correo está registrado, te hemos enviado un enlace de acceso.');
    }

    // --- TUS FUNCIONES ANTERIORES SE QUEDAN IGUAL ---
    public function create(Request $request, User $user)
    {
        return view('auth.setup-password', compact('user'));
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'], 
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('login')->with('status', '¡Contraseña configurada con éxito! Ya puedes iniciar sesión.');
    }
}