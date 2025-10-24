    <?php

namespace App\Events;

use App\Models\Accesoweb;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TemperaturaFueraRango implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $temperatura;
    public $rangoMinimo;
    public $rangoMaximo;
    public $ubicacion;
    public $tipoAlerta;
    public $productosAfectados;
    public $fechaEvento;
    public $usuarioDetecta;

    const TEMP_MINIMA_CRITICA = 2;     // 2°C
    const TEMP_MAXIMA_CRITICA = 30;    // 30°C
    const TEMP_MINIMA_NORMAL = 5;      // 5°C
    const TEMP_MAXIMA_NORMAL = 25;     // 25°C

    /**
     * Create a new event instance.
     *
     * @param float $temperatura
     * @param float $rangoMinimo
     * @param float $rangoMaximo
     * @param string $ubicacion
     * @param array $productosAfectados
     * @param Accesoweb|null $usuarioDetecta
     */
    public function __construct(
        float $temperatura,
        float $rangoMinimo,
        float $rangoMaximo,
        string $ubicacion,
        array $productosAfectados = [],
        ?Accesoweb $usuarioDetecta = null
    ) {
        $this->temperatura = $temperatura;
        $this->rangoMinimo = $rangoMinimo;
        $this->rangoMaximo = $rangoMaximo;
        $this->ubicacion = $ubicacion;
        $this->productosAfectados = $productosAfectados;
        $this->fechaEvento = now();
        $this->usuarioDetecta = $usuarioDetecta;
        
        $this->tipoAlerta = $this->determinarTipoAlerta($temperatura);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        $channels = ['temperatura', 'alertas'];
        
        if ($this->tipoAlerta === 'CRITICA') {
            $channels[] = 'alertas-criticas';
        }
        
        if ($this->tipoAlerta === 'MUY_ALTA' || $this->tipoAlerta === 'MUY_BAJA') {
            $channels[] = 'alertas-temperatura';
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
        return 'temperatura.fuera.rango';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'temperatura_actual' => $this->temperatura,
            'temperatura_formateada' => number_format($this->temperatura, 1) . '°C',
            'rango_minimo' => $this->rangoMinimo,
            'rango_maximo' => $this->rangoMaximo,
            'ubicacion' => $this->ubicacion,
            'tipo_alerta' => $this->tipoAlerta,
            'nivel_urgencia' => $this->determinarNivelUrgencia(),
            'productos_afectados' => $this->productosAfectados,
            'total_productos_afectados' => count($this->productosAfectados),
            'valor_total_productos' => $this->calcularValorTotalProductos(),
            'tiempo_exposicion' => $this->calcularTiempoExposicion(),
            'temperatura_ambiente' => $this->obtenerTemperaturaAmbiente(),
            'usuario_detecta' => $this->usuarioDetecta ? [
                'id' => $this->usuarioDetecta->id,
                'nombre' => $this->usuarioDetecta->nombre,
                'email' => $this->usuarioDetecta->email
            ] : null,
            'timestamp' => $this->fechaEvento->format('Y-m-d H:i:s'),
            'acciones_recomendadas' => $this->generarAccionesRecomendadas(),
            'medidas_inmediatas' => $this->generarMedidasInmediatas(),
            'es_critico' => $this->tipoAlerta === 'CRITICA'
        ];
    }

    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen()
    {
        return $this->temperatura < $this->rangoMinimo || $this->temperatura > $this->rangoMaximo;
    }

    /**
     * Determinar el tipo de alerta basado en la temperatura
     *
     * @param float $temperatura
     * @return string
     */
    private function determinarTipoAlerta(float $temperatura): string
    {
        if ($temperatura < self::TEMP_MINIMA_CRITICA || $temperatura > self::TEMP_MAXIMA_CRITICA) {
            return 'CRITICA';
        } elseif ($temperatura < self::TEMP_MINIMA_NORMAL) {
            return 'MUY_BAJA';
        } elseif ($temperatura > self::TEMP_MAXIMA_NORMAL) {
            return 'MUY_ALTA';
        } else {
            return 'FUERA_RANGO';
        }
    }

    /**
     * Determinar el nivel de urgencia
     *
     * @return string
     */
    private function determinarNivelUrgencia(): string
    {
        switch ($this->tipoAlerta) {
            case 'CRITICA':
                return 'URGENTE';
            case 'MUY_ALTA':
            case 'MUY_BAJA':
                return 'ALTA';
            case 'FUERA_RANGO':
                return 'MEDIA';
            default:
                return 'BAJA';
        }
    }

    /**
     * Calcular el valor total de productos afectados
     *
     * @return float
     */
    private function calcularValorTotalProductos(): float
    {
        return array_sum(array_map(function ($producto) {
            return ($producto['stock'] ?? 0) * ($producto['precio_venta'] ?? 0);
        }, $this->productosAfectados));
    }

    /**
     * Calcular tiempo de exposición estimado
     *
     * @return string
     */
    private function calcularTiempoExposicion(): string
    {
        // Esta lógica podría basarse en registros históricos de temperatura
        return 'Estimado: 15-30 minutos';
    }

    /**
     * Obtener temperatura ambiente
     *
     * @return float|null
     */
    private function obtenerTemperaturaAmbiente(): ?float
    {
        // Aquí podrías integrar con sensores ambientales
        return null;
    }

    /**
     * Generar acciones recomendadas
     *
     * @return array
     */
    private function generarAccionesRecomendadas(): array
    {
        $acciones = [];

        switch ($this->tipoAlerta) {
            case 'CRITICA':
                $acciones = [
                    'ACTIVAR PROTOCOLO DE EMERGENCIA INMEDIATA',
                    'Verificar sistema de refrigeración/calefacción',
                    'Mover productos a ubicación segura',
                    'Contactar soporte técnico',
                    'Documentar incident inmediatamente'
                ];
                break;
            case 'MUY_ALTA':
                $acciones = [
                    'Aumentar monitoreo de temperatura',
                    'Verificar sistemas de ventilación',
                    'Considerar mover productos sensibles',
                    'Revisar procedimientos de almacenamiento'
                ];
                break;
            case 'MUY_BAJA':
                $acciones = [
                    'Verificar calefacción del área',
                    'Aumentar aislamiento térmico',
                    'Monitorear productos sensibles al frío',
                    'Revisar sensores de temperatura'
                ];
                break;
            case 'FUERA_RANGO':
                $acciones = [
                    'Monitorear tendencia de temperatura',
                    'Ajustar sistemas de control climático',
                    'Documentar incident para análisis'
                ];
                break;
        }

        return $acciones;
    }

    /**
     * Generar medidas inmediatas
     *
     * @return array
     */
    private function generarMedidasInmediatas(): array
    {
        if ($this->tipoAlerta === 'CRITICA') {
            return [
                '1. Mover medicamentos termolábiles a refrigerador de emergencia',
                '2. Activar sistema de respaldo de refrigeración',
                '3. Informar al supervisor de turno',
                '4. Suspender dispensación hasta normalización',
                '5. Realizar inspección visual de productos'
            ];
        }

        return [
            '1. Verificar calibración de sensores',
            '2. Documentar incident en log de temperatura',
            '3. Programar revisión de sistemas de climatización'
        ];
    }
}