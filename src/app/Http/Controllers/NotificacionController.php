<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * NotificacionController
 * * Controlador encargado de gestionar las notificaciones de los usuarios.
 * Se encarga de actualizar el estado de los avisos en la base de datos,
 * pasandolos de "no leidos" a "leidos".
 */
class NotificacionController extends Controller
{
    /**
     * Marca todas las notificaciones pendientes del usuario actual como leidas.
     * @param Request $solicitud
     * @return JsonResponse
     */
    public function marcarLeidas(Request $solicitud): JsonResponse
    {
        // Obtenemos al usuario que ha hecho la peticion y marcamos todas como leidas
        $solicitud->user()->unreadNotifications->markAsRead();

        // Devolvemos una respuesta de exito en formato JSON
        return response()->json(['exito' => true]);
    }
}