<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Clase EventoNuevaIntervencion
 * * Esta clase se encarga de emitir un evento en tiempo real a traves de WebSockets
 * cuando un tecnico registra o modifica un parte de trabajo.
 * Implementa ShouldBroadcastNow para emitir el evento instantaneamente sin colas.
 */
class EventoNuevaIntervencion implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $titulo;
    public $estado;
    public $clienteId;

    public function __construct($titulo, $estado, $clienteId)
    {
        $this->titulo = $titulo;
        $this->estado = $estado;
        $this->clienteId = $clienteId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('canal-cliente.' . $this->clienteId),
        ];
    }
    public function broadcastAs(): string
    {
        return 'alerta.nueva.intervencion';
    }
}