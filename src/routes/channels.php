<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Client;

Broadcast::channel('canal-cliente.{id}', function ($user, $id) {
    // Buscamos el perfil de cliente asociado al usuario logueado
    $cliente = Client::where('user_id', $user->id)->first();

    // Solo le damos permiso si existe y su ID coincide con el canal que intenta escuchar
    return $cliente !== null && $cliente->id === (int) $id;
});
