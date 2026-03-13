<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * NotificacionParte
 * * Esta clase gestiona el envio de notificaciones relacionadas con los partes
 * de trabajo. Guarda el aviso en la base de datos para que sea permanente
 * y envia una senal por WebSockets para que se actualice en tiempo real.
 */
class NotificacionParte extends Notification implements ShouldQueue
{
    use Queueable;

    private string $mensaje;
    private int $idParte;

    /**
     * Constructor de la notificacion.
     * @param string $mensaje El texto descriptivo del aviso.
     * @param int $idParte El identificador del parte afectado.
     */
    public function __construct(string $mensaje, int $idParte)
    {
        $this->mensaje = $mensaje;
        $this->idParte = $idParte;
    }

    /**
     * Define por que canales se va a enviar esta notificacion.
     * 'database' lo guarda en la tabla. 'broadcast' lo manda por WebSockets (Reverb).
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Define los datos que se guardaran en la columna 'data' de la base de datos.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'mensaje' => $this->mensaje,
            'parte_id' => $this->idParte
        ];
    }

    /**
     * Define los datos que viajaran por el WebSocket.
     * Laravel automaticamente lo mandara al canal privado 'App.Models.User.{id}'
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'mensaje' => $this->mensaje,
            'parte_id' => $this->idParte
        ]);
    }
}
