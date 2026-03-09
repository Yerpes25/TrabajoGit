<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientPasswordController extends Controller
{
    public function create(Request $request, User $user)
    {
        // Si el enlace fue alterado o caducó, el middleware 'signed' dará error 403 antes de llegar aquí.
        return view('auth.setup-password', compact('user'));
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'], // Exige que 'password_confirmation' coincida
        ]);

        // Actualizamos la contraseña encriptada
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('login')->with('status', '¡Contraseña configurada con éxito! Ya puedes iniciar sesión.');
    }
}