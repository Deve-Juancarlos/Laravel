
<?php

namespace App\Events;

use App\Models\Factura;
use App\Models\Accesoweb;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FacturaCreada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $factura;
    public $usuario;
    public $fechaEvento;

    /**
     * Create a new event instance.
     *
     * @param Factura $factura
     * @param Accesoweb $usuario
     */
    public function __construct(Factura $factura, Accesoweb $usuario)
    {
        $this->factura = $factura;
        $this->usuario = $usuario;
        $this->fechaEvento = now();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('facturas');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'factura.creada';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'factura_id' => $this->factura->id,
            'numero_factura' => $this->factura->numero_factura,
            'cliente' => [
                'id' => $this->factura->cliente_id,
                'nombre' => $this->factura->cliente->nombre ?? 'Cliente no encontrado',
                'dni' => $this->factura->cliente->dni ?? ''
            ],
            'total' => $this->factura->total,
            'estado' => $this->factura->estado,
            'fecha_creacion' => $this->factura->created_at->format('Y-m-d H:i:s'),
            'usuario_creacion' => [
                'id' => $this->usuario->id,
                'nombre' => $this->usuario->nombre,
                'email' => $this->usuario->email
            ],
            'timestamp' => $this->fechaEvento->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen()
    {
        return $this->factura->estado === 'PENDIENTE';
    }
}