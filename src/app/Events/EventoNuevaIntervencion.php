<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel; // <-- CAMBIO IMPORTANTE: Ahora usamos PrivateChannel
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EventoNuevaIntervencion
 * * Clase que representa el evento de creacion de un nuevo parte de trabajo.
 * Utiliza WebSockets a traves de un canal PRIVADO para notificar 
 * unica y exclusivamente al cliente asociado al parte.
 */
class EventoNuevaIntervencion implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mensaje;
    
    // Necesitamos saber a quien va dirigido
    public $idCliente;

    /**
     * Constructor del evento.
     * @param string $textoMensaje El mensaje que vera el cliente.
     * @param int $idDelCliente El ID del usuario cliente que debe recibirlo.
     */
    public function __construct($textoMensaje, $idDelCliente)
    {
        $this->mensaje = $textoMensaje;
        $this->idCliente = $idDelCliente;
    }

    /**
     * Define el canal PRIVADO por donde viajara la informacion.
     * El canal se llamara 'cliente.X', donde X es el ID del usuario.
     */
    public function broadcastOn()
    {
        return new PrivateChannel('cliente.' . $this->idCliente);
    }

    /**
     * El nombre del evento que el JavaScript estara escuchando.
     */
    public function broadcastAs()
    {
        return 'NuevaIntervencion';
    }
}