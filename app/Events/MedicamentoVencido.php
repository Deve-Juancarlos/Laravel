<?php

namespace App\Events;

use App\Models\Producto;
use App\Models\Accesoweb;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MedicamentoVencido implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $producto;
    public $diasParaVencer;
    public $alertaCritica;
    public $fechaEvento;

    /**
     * Create a new event instance.
     *
     * @param Producto $producto
     * @param int $diasParaVencer
     * @param bool $alertaCritica
     */
    public function __construct(Producto $producto, int $diasParaVencer, bool $alertaCritica = false)
    {
        $this->producto = $producto;
        $this->diasParaVencer = $diasParaVencer;
        $this->alertaCritica = $alertaCritica;
        $this->fechaEvento = now();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        $channels = ['medicamentos', 'alertas'];
        
        if ($this->alertaCritica) {
            $channels[] = 'alertas-criticas';
        }

        return array_map(function ($channel) {
            return new Channel($channel);
        }, $channels);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        if ($this->alertaCritica) {
            return 'medicamento.critico.vencido';
        }
        
        return 'medicamento.vencido';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'producto_id' => $this->producto->id,
            'codigo_producto' => $this->producto->codigo,
            'nombre' => $this->producto->nombre,
            'descripcion' => $this->producto->descripcion,
            'laboratorio' => $this->producto->laboratorio,
            'fecha_vencimiento' => $this->producto->fecha_vencimiento->format('Y-m-d'),
            'dias_para_vencer' => $this->diasParaVencer,
            'es_critico' => $this->alertaCritica,
            'nivel_alerta' => $this->alertaCritica ? 'CRITICO' : 'ADVERTENCIA',
            'stock_actual' => $this->producto->stock_actual ?? 0,
            'precio_venta' => $this->producto->precio_venta,
            'valor_total_stock' => ($this->producto->stock_actual ?? 0) * $this->producto->precio_venta,
            'es_medicamento_controlado' => $this->producto->es_controlado ?? false,
            'timestamp' => $this->fechaEvento->format('Y-m-d H:i:s'),
            'recomendaciones' => $this->generarRecomendaciones()
        ];
    }

    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen()
    {
        return $this->diasParaVencer <= 30; // Solo alertar si vence en 30 días o menos
    }

    /**
     * Generate recomendaciones based on diasParaVencer and alertaCritica
     *
     * @return array
     */
    private function generarRecomendaciones()
    {
        $recomendaciones = [];

        if ($this->alertaCritica) {
            $recomendaciones[] = 'VENCIMIENTO CRÍTICO - Acción inmediata requerida';
            $recomendaciones[] = 'Retirar del inventario inmediatamente';
            $recomendaciones[] = 'Contactar al proveedor para devolución';
        } elseif ($this->diasParaVencer <= 7) {
            $recomendaciones[] = 'Vencimiento en menos de una semana';
            $recomendaciones[] = 'Considerar descuentos para venta rápida';
            $recomendaciones[] = 'Monitorear stock diariamente';
        } elseif ($this->diasParaVencer <= 15) {
            $recomendaciones[] = 'Vencimiento próximo - Tomar medidas preventivas';
            $recomendaciones[] = 'Revisar proveedores para rotación de stock';
        } elseif ($this->diasParaVencer <= 30) {
            $recomendaciones[] = 'Vencimiento en menos de un mes';
            $recomendaciones[] = 'Planificar promociones especiales';
        }

        if ($this->producto->es_controlado ?? false) {
            $recomendaciones[] = 'MEDICAMENTO CONTROLADO - Seguimiento especial requerido';
            $recomendaciones[] = 'Verificar cumplimiento normativo para disposición';
        }

        return $recomendaciones;
    }
}