<?php

namespace App\Events;

use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\Accesoweb;
use App\Models\Cliente;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class MedicamentoControladoVendido implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $factura;
    public $facturaDetalle;
    public $cliente;
    public $usuarioVenta;
    public $medicamentosControlados;
    public $numeroControl;
    public $fechaEvento;

    /**
     * Create a new event instance.
     *
     * @param Factura $factura
     * @param FacturaDetalle $facturaDetalle
     * @param Cliente $cliente
     * @param Accesoweb $usuarioVenta
     * @param array $medicamentosControlados
     */
    public function __construct(
        Factura $factura,
        FacturaDetalle $facturaDetalle,
        Cliente $cliente,
        Accesoweb $usuarioVenta,
        array $medicamentosControlados
    ) {
        $this->factura = $factura;
        $this->facturaDetalle = $facturaDetalle;
        $this->cliente = $cliente;
        $this->usuarioVenta = $usuarioVenta;
        $this->medicamentosControlados = $medicamentosControlados;
        $this->fechaEvento = now();
        $this->numeroControl = $this->generarNumeroControl();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return [
            new Channel('medicamentos-controlados'),
            new Channel('farmacia'),
            new Channel('auditoria')
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'medicamento.controlado.vendido';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'numero_control' => $this->numeroControl,
            'factura_id' => $this->factura->id,
            'numero_factura' => $this->factura->numero_factura,
            'fecha_venta' => $this->fechaEvento->format('Y-m-d H:i:s'),
            'cliente' => [
                'id' => $this->cliente->id,
                'nombre' => $this->cliente->nombre,
                'dni' => $this->cliente->dni,
                'direccion' => $this->cliente->direccion ?? '',
                'telefono' => $this->cliente->telefono ?? '',
                'edad' => $this->calcularEdadCliente(),
                'es_menor_edad' => $this->cliente->esMenorEdad() ?? false
            ],
            'usuario_venta' => [
                'id' => $this->usuarioVenta->id,
                'nombre' => $this->usuarioVenta->nombre,
                'email' => $this->usuarioVenta->email
            ],
            'medicamentos_controlados' => $this->formatearMedicamentosControlados(),
            'total_medicamentos_controlados' => $this->calcularTotalMedicamentosControlados(),
            'valor_total_controlados' => $this->calcularValorTotalControlados(),
            'requiere_receta_medica' => $this->verificarRecetaMedica(),
            'observaciones_especiales' => $this->generarObservacionesEspeciales(),
            'verificacion_regulatoria' => $this->realizarVerificacionRegulatoria(),
            'auditoria_completa' => $this->generarAuditoriaCompleta(),
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
        return !empty($this->medicamentosControlados);
    }

    /**
     * Generar número de control único
     *
     * @return string
     */
    private function generarNumeroControl(): string
    {
        $prefijo = 'CTRL';
        $fecha = $this->fechaEvento->format('Ymd');
        $hora = $this->fechaEvento->format('His');
        $secuencial = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefijo . $fecha . $hora . $secuencial;
    }

    /**
     * Calcular edad del cliente
     *
     * @return int|null
     */
    private function calcularEdadCliente(): ?int
    {
        if ($this->cliente->fecha_nacimiento) {
            return $this->fechaEvento->diffInYears($this->cliente->fecha_nacimiento);
        }
        
        return null;
    }

    /**
     * Formatear medicamentos controlados para la respuesta
     *
     * @return array
     */
    private function formatearMedicamentosControlados(): array
    {
        return array_map(function ($medicamento) {
            return [
                'producto_id' => $medicamento['producto_id'],
                'codigo' => $medicamento['codigo'],
                'nombre' => $medicamento['nombre'],
                'descripcion' => $medicamento['descripcion'],
                'cantidad_vendida' => $medicamento['cantidad'],
                'precio_unitario' => $medicamento['precio_unitario'],
                'subtotal' => $medicamento['cantidad'] * $medicamento['precio_unitario'],
                'nivel_control' => $medicamento['nivel_control'] ?? 'L2',
                'fecha_vencimiento' => $medicamento['fecha_vencimiento'] ?? null,
                'lote' => $medicamento['lote'] ?? '',
                'receta_medica' => $medicamento['receta_medica'] ?? false,
                'numero_receta' => $medicamento['numero_receta'] ?? '',
                'medico_prescriptor' => $medicamento['medico_prescriptor'] ?? '',
                'posologia' => $medicamento['posologia'] ?? ''
            ];
        }, $this->medicamentosControlados);
    }

    /**
     * Calcular total de medicamentos controlados vendidos
     *
     * @return int
     */
    private function calcularTotalMedicamentosControlados(): int
    {
        return array_sum(array_column($this->medicamentosControlados, 'cantidad'));
    }

    /**
     * Calcular valor total de medicamentos controlados
     *
     * @return float
     */
    private function calcularValorTotalControlados(): float
    {
        return array_sum(array_map(function ($medicamento) {
            return $medicamento['cantidad'] * $medicamento['precio_unitario'];
        }, $this->medicamentosControlados));
    }

    /**
     * Verificar si se requiere receta médica
     *
     * @return bool
     */
    private function verificarRecetaMedica(): bool
    {
        return array_reduce($this->medicamentosControlados, function ($carry, $medicamento) {
            return $carry || ($medicamento['requiere_receta'] ?? true);
        }, false);
    }

    /**
     * Generar observaciones especiales
     *
     * @return array
     */
    private function generarObservacionesEspeciales(): array
    {
        $observaciones = [];

        // Verificar si hay menores de edad
        if ($this->cliente->esMenorEdad() ?? false) {
            $observaciones[] = 'VENTA A MENOR DE EDAD - Verificar autorización parental';
        }

        // Verificar cantidades altas
        $cantidadTotal = $this->calcularTotalMedicamentosControlados();
        if ($cantidadTotal > 5) {
            $observaciones[] = 'CANTIDAD ELEVADA - Revisar justificación médica';
        }

        // Verificar frecuencia de compra
        $comprasRecientes = $this->verificarComprasRecientes();
        if ($comprasRecientes > 2) {
            $observaciones[] = 'COMPRA FRECUENTE - Verificar adherencia al tratamiento';
        }

        // Verificar medicamentos de alto control
        $medicamentosAltoControl = array_filter($this->medicamentosControlados, function ($med) {
            return ($med['nivel_control'] ?? '') === 'L1';
        });
        
        if (!empty($medicamentosAltoControl)) {
            $observaciones[] = 'MEDICAMENTOS DE ALTO CONTROL - Reporte obligatorio';
        }

        return $observaciones;
    }

    /**
     * Verificar compras recientes del cliente
     *
     * @return int
     */
    private function verificarComprasRecientes(): int
    {
        $fechaInicio = $this->fechaEvento->copy()->subDays(30);
        
        return DB::table('facturas')
            ->join('factura_detalles', 'facturas.id', '=', 'factura_detalles.factura_id')
            ->join('productos', 'factura_detalles.producto_id', '=', 'productos.id')
            ->where('facturas.cliente_id', $this->cliente->id)
            ->where('productos.es_controlado', true)
            ->where('facturas.created_at', '>=', $fechaInicio)
            ->count();
    }

    /**
     * Realizar verificación regulatoria
     *
     * @return array
     */
    private function realizarVerificacionRegulatoria(): array
    {
        $verificaciones = [
            'cliente_autorizado' => true,
            'medicamentos_autorizados' => true,
            'cantidades_dentro_rango' => true,
            'documentacion_completa' => true,
            'notificaciones_requeridas' => false
        ];

        // Verificar autorización del cliente
        if (($this->cliente->autorizado_venta_controlados ?? false) === false) {
            $verificaciones['cliente_autorizado'] = false;
        }

        // Verificar rangos de cantidad
        foreach ($this->medicamentosControlados as $medicamento) {
            $cantidadMaxima = $medicamento['cantidad_maxima_autorizada'] ?? 1;
            if ($medicamento['cantidad'] > $cantidadMaxima) {
                $verificaciones['cantidades_dentro_rango'] = false;
            }
        }

        // Determinar si se requieren notificaciones
        if ($this->contieneMedicamentosAltoControl()) {
            $verificaciones['notificaciones_requeridas'] = true;
        }

        return $verificaciones;
    }

    /**
     * Verificar si contiene medicamentos de alto control
     *
     * @return bool
     */
    private function contieneMedicamentosAltoControl(): bool
    {
        return array_reduce($this->medicamentosControlados, function ($carry, $medicamento) {
            return $carry || ($medicamento['nivel_control'] ?? '') === 'L1';
        }, false);
    }

    /**
     * Generar auditoría completa del evento
     *
     * @return array
     */
    private function generarAuditoriaCompleta(): array
    {
        return [
            'evento_generado' => 'MEDICAMENTO_CONTROLADO_VENDIDO',
            'timestamp_evento' => $this->fechaEvento->format('Y-m-d H:i:s'),
            'numero_control' => $this->numeroControl,
            'responsable_venta' => $this->usuarioVenta->nombre,
            'cliente_id' => $this->cliente->id,
            'factura_id' => $this->factura->id,
            'medicamentos_count' => count($this->medicamentosControlados),
            'requiere_archivo' => $this->contieneMedicamentosAltoControl(),
            'reportes_generados' => $this->generarReportes(),
            'estado_verificacion' => $this->evaluarEstadoVerificacion(),
            'acciones_seguimiento' => $this->generarAccionesSeguimiento()
        ];
    }

    /**
     * Generar reportes necesarios
     *
     * @return array
     */
    private function generarReportes(): array
    {
        $reportes = ['CONTROL_INTERNO'];

        if ($this->contieneMedicamentosAltoControl()) {
            $reportes[] = 'SUNAT';
            $reportes[] = 'MINSA';
        }

        return $reportes;
    }

    /**
     * Evaluar estado de verificación
     *
     * @return string
     */
    private function evaluarEstadoVerificacion(): string
    {
        $verificaciones = $this->realizarVerificacionRegulatoria();
        
        if (!$verificaciones['cliente_autorizado']) {
            return 'DENEGADO';
        }
        
        if (!$verificaciones['cantidades_dentro_rango']) {
            return 'REVISAR';
        }
        
        if ($this->contieneMedicamentosAltoControl()) {
            return 'APROBADO_PENDIENTE_REPORTE';
        }
        
        return 'APROBADO';
    }

    /**
     * Generar acciones de seguimiento
     *
     * @return array
     */
    private function generarAccionesSeguimiento(): array
    {
        $acciones = [];

        if ($this->contieneMedicamentosAltoControl()) {
            $acciones[] = 'Generar reporte SUNAT en 24 horas';
            $acciones[] = 'Archivar documentación por 10 años';
        }

        if (($this->cliente->esMenorEdad() ?? false)) {
            $acciones[] = 'Verificar autorización parental';
            $acciones[] = 'Notificar a supervisor';
        }

        $acciones[] = 'Actualizar log de medicamentos controlados';
        $acciones[] = 'Verificar stock de medicamentos controlados';

        return $acciones;
    }
}