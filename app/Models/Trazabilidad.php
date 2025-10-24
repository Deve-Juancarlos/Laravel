<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Trazabilidad extends Model
{
    use HasFactory;

    protected $table = 'Trazabilidad';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'TipoMovimiento',
        'ProductoId',
        'Lote',
        'Cantidad',
        'UsuarioId',
        'Fecha',
        'Origen',
        'Destino',
        'UbicacionOrigen',
        'UbicacionDestino',
        'ReferenciaId',
        'ReferenciaTabla',
        'Observaciones',
        'Estado',
        'FarmaceuticoResponsable',
        'Vencimiento',
        'Temperatura',
        'NumeroLote'
    ];

    protected $casts = [
        'Cantidad' => 'decimal:3',
        'Fecha' => 'datetime',
        'Vencimiento' => 'date',
        'Temperatura' => 'decimal:2'
    ];

    protected $dates = ['Fecha', 'Vencimiento'];

    // Relacionamentos
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'Codigo');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'UsuarioId', 'Id');
    }

    public function referencia()
    {
        return $this->morphTo();
    }

    // Métodos de negocio
    public function getTipoMovimientoLabel()
    {
        $tipos = [
            'Ingreso' => 'Ingreso a Almacén',
            'Salida' => 'Salida de Almacén',
            'Venta' => 'Venta',
            'DevolucionVenta' => 'Devolución de Venta',
            'Compra' => 'Compra',
            'DevolucionCompra' => 'Devolución de Compra',
            'Traslado' => 'Traslado',
            'Ajuste' => 'Ajuste de Inventario',
            'Merma' => 'Merma',
            'Vencimiento' => 'Producto Vencido',
            'ControlCalidad' => 'Control de Calidad',
            'Prescripcion' => 'Dispensación con Receta',
            'Despacho' => 'Despacho a Botica',
            'RetornoBotica' => 'Retorno de Botica',
            'Cuarentena' => 'En Cuarentena',
            'Liberacion' => 'Liberado de Cuarentena',
            'DevolucionPaciente' => 'Devolución por Paciente',
            'Inventario' => 'Conteo de Inventario',
            'ControlTemperatura' => 'Control de Temperatura',
            'RetiradoMercado' => 'Retirado del Mercado',
            'MuestraMedica' => 'Muestra Médica',
            'PrescripcionControlada' => 'Dispensación Controlada'
        ];
        
        return $tipos[$this->TipoMovimiento] ?? $this->TipoMovimiento;
    }

    public function getEstadoLabel()
    {
        $estados = [
            'Pendiente' => 'Pendiente',
            'EnProceso' => 'En Proceso',
            'Completado' => 'Completado',
            'Cancelado' => 'Cancelado',
            'Rechazado' => 'Rechazado',
            'EnCuarentena' => 'En Cuarentena'
        ];
        
        return $estados[$this->Estado] ?? $this->Estado;
    }

    public function getDireccionMovimiento()
    {
        switch ($this->TipoMovimiento) {
            case 'Ingreso':
            case 'Compra':
            case 'DevolucionVenta':
            case 'Despacho':
            case 'MuestraMedica':
                return 'Entrada';
            case 'Salida':
            case 'Venta':
            case 'DevolucionCompra':
            case 'RetornoBotica':
            case 'RetiradoMercado':
                return 'Salida';
            case 'Traslado':
            case 'Ajuste':
            case 'Inventario':
                return $this->UbicacionOrigen === $this->UbicacionDestino ? 'Interno' : 'Traslado';
            default:
                return 'Sin definir';
        }
    }

    public function getRutaCompleta()
    {
        $ruta = [];
        
        if ($this->UbicacionOrigen) {
            $ruta[] = $this->UbicacionOrigen;
        }
        
        if ($this->UbicacionDestino) {
            $ruta[] = $this->UbicacionDestino;
        }
        
        return implode(' → ', $ruta);
    }

    public function calcularVidaUtilRestante()
    {
        if (!$this->Vencimiento) {
            return null;
        }
        
        $diasRestantes = $this->Vencimiento->diffInDays(now());
        return $diasRestantes;
    }

    public function isVencido()
    {
        if (!$this->Vencimiento) {
            return false;
        }
        
        return $this->Vencimiento->isPast();
    }

    public function isProximoVencer($dias = 30)
    {
        if (!$this->Vencimiento) {
            return false;
        }
        
        return $this->Vencimiento->diffInDays(now()) <= $dias;
    }

    public function validarTemperatura($minima, $maxima)
    {
        if (!$this->Temperatura) {
            return true; // Sin registro de temperatura
        }
        
        return $this->Temperatura >= $minima && $this->Temperatura <= $maxima;
    }

    public function marcarCompletado($observaciones = null)
    {
        $this->Estado = 'Completado';
        if ($observaciones) {
            $this->Observaciones = ($this->Observaciones ?? '') . "\nCompletado: " . $observaciones;
        }
        $this->save();
        
        return $this;
    }

    public function marcarRechazado($motivo)
    {
        $this->Estado = 'Rechazado';
        $this->Observaciones = ($this->Observaciones ?? '') . "\nRechazado: " . $motivo;
        $this->save();
        
        return $this;
    }

    public function duplicar($cambios = [])
    {
        $nuevoRegistro = $this->replicate();
        $nuevoRegistro->fill($cambios);
        $nuevoRegistro->Estado = 'Pendiente';
        
        return $nuevoRegistro;
    }

    // Métodos estáticos para registro de movimientos
    public static function registrarMovimiento($datos)
    {
        $registro = new self();
        $registro->fill($datos);
        $registro->Fecha = $registro->Fecha ?? now();
        $registro->Estado = $registro->Estado ?? 'Completado';
        $registro->UsuarioId = $registro->UsuarioId ?? (auth()->id() ?? 1);
        $registro->save();
        
        return $registro;
    }

    public static function registrarIngreso($productoId, $cantidad, $lote, $origen = 'Compra', $datos = [])
    {
        return self::registrarMovimiento(array_merge([
            'TipoMovimiento' => 'Ingreso',
            'ProductoId' => $productoId,
            'Lote' => $lote,
            'Cantidad' => $cantidad,
            'Origen' => $origen,
            'Observaciones' => "Ingreso de {$cantidad} unidades del lote {$lote}"
        ], $datos));
    }

    public static function registrarSalida($productoId, $cantidad, $lote, $destino = 'Venta', $datos = [])
    {
        return self::registrarMovimiento(array_merge([
            'TipoMovimiento' => 'Salida',
            'ProductoId' => $productoId,
            'Lote' => $lote,
            'Cantidad' => -$cantidad, // Negativo para salida
            'Destino' => $destino,
            'Observaciones' => "Salida de {$cantidad} unidades del lote {$lote}"
        ], $datos));
    }

    public static function registrarTraslado($productoId, $cantidad, $lote, $origen, $destino, $datos = [])
    {
        return self::registrarMovimiento(array_merge([
            'TipoMovimiento' => 'Traslado',
            'ProductoId' => $productoId,
            'Lote' => $lote,
            'Cantidad' => 0, // Traslado no afecta cantidad total
            'UbicacionOrigen' => $origen,
            'UbicacionDestino' => $destino,
            'Observaciones' => "Traslado de {$cantidad} unidades del lote {$lote} desde {$origen} a {$destino}"
        ], $datos));
    }

    public static function registrarVenta($facturaId, $productoId, $cantidad, $lote, $datos = [])
    {
        return self::registrarMovimiento(array_merge([
            'TipoMovimiento' => 'Venta',
            'ProductoId' => $productoId,
            'Lote' => $lote,
            'Cantidad' => -$cantidad,
            'ReferenciaId' => $facturaId,
            'ReferenciaTabla' => 'Facturas',
            'Observaciones' => "Venta de {$cantidad} unidades del lote {$lote}"
        ], $datos));
    }

    public static function registrarPrescripcion($recetaId, $productoId, $cantidad, $lote, $farmaceutico, $datos = [])
    {
        return self::registrarMovimiento(array_merge([
            'TipoMovimiento' => 'Prescripcion',
            'ProductoId' => $productoId,
            'Lote' => $lote,
            'Cantidad' => -$cantidad,
            'ReferenciaId' => $recetaId,
            'ReferenciaTabla' => 'RecetasMedicas',
            'FarmaceuticoResponsable' => $farmaceutico,
            'Observaciones' => "Dispensación con receta de {$cantidad} unidades del lote {$lote}"
        ], $datos));
    }

    public static function registrarMerma($productoId, $cantidad, $lote, $causa, $datos = [])
    {
        return self::registrarMovimiento(array_merge([
            'TipoMovimiento' => 'Merma',
            'ProductoId' => $productoId,
            'Lote' => $lote,
            'Cantidad' => -$cantidad,
            'Observaciones' => "Merma de {$cantidad} unidades del lote {$lote}. Causa: {$causa}"
        ], $datos));
    }

    public static function registrarControlTemperatura($productoId, $temperatura, $lote, $cumple, $datos = [])
    {
        return self::registrarMovimiento(array_merge([
            'TipoMovimiento' => 'ControlTemperatura',
            'ProductoId' => $productoId,
            'Lote' => $lote,
            'Temperatura' => $temperatura,
            'Observaciones' => $cumple ? 
                "Control de temperatura cumple: {$temperatura}°C" : 
                "Control de temperatura NO cumple: {$temperatura}°C"
        ], $datos));
    }

    // Scopes
    public function scopePorProducto($query, $productoId)
    {
        return $query->where('ProductoId', $productoId);
    }

    public function scopePorLote($query, $lote)
    {
        return $query->where('Lote', $lote);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('TipoMovimiento', $tipo);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('Estado', $estado);
    }

    public function scopeCompletados($query)
    {
        return $query->where('Estado', 'Completado');
    }

    public function scopePendientes($query)
    {
        return $query->where('Estado', 'Pendiente');
    }

    public function scopeRechazados($query)
    {
        return $query->where('Estado', 'Rechazado');
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('Fecha', [$fechaInicio, $fechaFin]);
        }
        return $query->whereDate('Fecha', $fechaInicio);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('UsuarioId', $usuarioId);
    }

    public function scopePorOrigen($query, $origen)
    {
        return $query->where('Origen', $origen);
    }

    public function scopePorDestino($query, $destino)
    {
        return $query->where('Destino', $destino);
    }

    public function scopeConReferencia($query)
    {
        return $query->whereNotNull('ReferenciaId');
    }

    public function scopeSinReferencia($query)
    {
        return $query->whereNull('ReferenciaId');
    }

    public function scopeVencidos($query)
    {
        return $query->where('Vencimiento', '<', now());
    }

    public function scopeProximoVencer($query, $dias = 30)
    {
        return $query->where('Vencimiento', '<=', now()->addDays($dias))
                    ->where('Vencimiento', '>=', now());
    }

    public function scopeFueraRangoTemperatura($query)
    {
        return $query->whereNotNull('Temperatura')
                    ->where(function($q) {
                        $q->where('Temperatura', '<', 2)
                          ->orWhere('Temperatura', '>', 8);
                    });
    }

    // Métodos de consulta y reportes
    public static function obtenerTrazabilidadProducto($productoId, $lote = null)
    {
        $query = self::porProducto($productoId)->completados()->orderBy('Fecha');
        
        if ($lote) {
            $query->where('Lote', $lote);
        }
        
        return $query->with(['usuario', 'producto'])
                    ->get()
                    ->map(function($registro) {
                        $registro->dias_desde_registro = $registro->Fecha->diffInDays(now());
                        return $registro;
                    });
    }

    public static function obtenerRutaCompleta($productoId, $lote, $fechaLimite = null)
    {
        $query = self::porProducto($productoId)
                    ->porLote($lote)
                    ->completados();
        
        if ($fechaLimite) {
            $query->where('Fecha', '<=', $fechaLimite);
        }
        
        return $query->orderBy('Fecha')
                    ->get();
    }

    public static function generarCertificadoTrazabilidad($productoId, $lote, $fechaInicio, $fechaFin)
    {
        $registros = self::obtenerTrazabilidadProducto($productoId, $lote)
                        ->whereBetween('Fecha', [$fechaInicio, $fechaFin]);
        
        $certificado = [
            'producto_id' => $productoId,
            'lote' => $lote,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'registros' => $registros,
            'resumen' => [
                'total_movimientos' => $registros->count(),
                'ingresos' => $registros->where('TipoMovimiento', 'Ingreso')->sum('Cantidad'),
                'salidas' => abs($registros->where('TipoMovimiento', 'Salida')->sum('Cantidad')),
                'ventas' => abs($registros->where('TipoMovimiento', 'Venta')->sum('Cantidad')),
                'mermas' => abs($registros->where('TipoMovimiento', 'Merma')->sum('Cantidad'))
            ],
            'control_calidad' => $registros->where('TipoMovimiento', 'ControlCalidad')->count(),
            'ultima_actualizacion' => now()
        ];
        
        return $certificado;
    }

    public static function obtenerProductosVencidos()
    {
        return self::selectRaw('ProductoId, Lote, MIN(Vencimiento) as fecha_vencimiento, COUNT(*) as movimientos')
                    ->vencidos()
                    ->groupBy('ProductoId', 'Lote')
                    ->with('producto')
                    ->having('fecha_vencimiento', '<', now()->subDays(30))
                    ->orderBy('fecha_vencimiento')
                    ->get();
    }

    public static function obtenerAlertasTrazabilidad()
    {
        $alertas = [];
        
        // Productos vencidos
        $productosVencidos = self::obtenerProductosVencidos();
        if ($productosVencidos->count() > 0) {
            $alertas[] = [
                'tipo' => 'productos_vencidos',
                'titulo' => 'Productos Vencidos',
                'cantidad' => $productosVencidos->count(),
                'productos' => $productosVencidos->take(10)
            ];
        }
        
        // Productos próximos a vencer
        $productosProximosVencer = self::selectRaw('ProductoId, COUNT(*) as movimientos')
                                      ->proximosVencer(15)
                                      ->groupBy('ProductoId')
                                      ->with('producto')
                                      ->get();
        
        if ($productosProximosVencer->count() > 0) {
            $alertas[] = [
                'tipo' => 'productos_proximo_vencer',
                'titulo' => 'Productos Próximos a Vencer',
                'cantidad' => $productosProximosVencer->count(),
                'productos' => $productosProximosVencer->take(10)
            ];
        }
        
        // Controles de temperatura fuera de rango
        $controlesTemperatura = self::scopeFueraRangoTemperatura(null)->get();
        
        if ($controlesTemperatura->count() > 0) {
            $alertas[] = [
                'tipo' => 'temperatura_fuera_rango',
                'titulo' => 'Controles de Temperatura Fuera de Rango',
                'cantidad' => $controlesTemperatura->count(),
                'controles' => $controlesTemperatura->take(10)
            ];
        }
        
        return $alertas;
    }

    public static function validarTrazabilidad($productoId, $lote)
    {
        $registros = self::obtenerTrazabilidadProducto($productoId, $lote);
        
        if ($registros->isEmpty()) {
            return [
                'valido' => false,
                'errores' => ['No se encontró trazabilidad para el producto y lote']
            ];
        }
        
        $errores = [];
        
        // Verificar que el primer movimiento sea un ingreso
        $primerMovimiento = $registros->first();
        if (!in_array($primerMovimiento->TipoMovimiento, ['Ingreso', 'Compra'])) {
            $errores[] = 'El primer movimiento no es un ingreso válido';
        }
        
        // Verificar balance de cantidades
        $totalMovimientos = $registros->sum('Cantidad');
        if (abs($totalMovimientos) < 0.001) {
            $errores[] = 'Las cantidades no están balanceadas';
        }
        
        // Verificar que no hay movimientos posteriores a la fecha actual
        $movimientosFuturos = $registros->filter(function($registro) {
            return $registro->Fecha->isFuture();
        });
        
        if ($movimientosFuturos->count() > 0) {
            $errores[] = 'Existen movimientos con fecha futura';
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores,
            'registros' => $registros->count(),
            'balance_cantidades' => $totalMovimientos
        ];
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registro) {
            // Establecer fecha por defecto
            if (!$registro->Fecha) {
                $registro->Fecha = now();
            }
            
            // Establecer usuario por defecto
            if (!$registro->UsuarioId) {
                $registro->UsuarioId = auth()->id() ?? 1;
            }
        });

        static::saved(function ($registro) {
            // Generar alertas para situaciones críticas
            
            // Producto vencido
            if ($registro->isVencido()) {
                Notificacion::crear([
                    'tipo' => 'producto_vencido_trazabilidad',
                    'titulo' => 'Producto Vencido en Trazabilidad',
                    'mensaje' => "Producto {$registro->producto->Descripcion} - Lote {$registro->Lote} ha vencido",
                    'referencia_id' => $registro->Id,
                    'referencia_tabla' => 'Trazabilidad',
                    'prioridad' => 'critica'
                ]);
            }
            
            // Control de temperatura fuera de rango
            if ($registro->TipoMovimiento === 'ControlTemperatura' && 
                !$registro->validarTemperatura(2, 8)) {
                Notificacion::crear([
                    'tipo' => 'temperatura_fuera_rango',
                    'titulo' => 'Temperatura Fuera de Rango',
                    'mensaje' => "Control de temperatura fuera de rango: {$registro->Temperatura}°C para lote {$registro->Lote}",
                    'referencia_id' => $registro->Id,
                    'referencia_tabla' => 'Trazabilidad',
                    'prioridad' => 'alta'
                ]);
            }
            
            // Merma alta
            if ($registro->TipoMovimiento === 'Merma' && abs($registro->Cantidad) > 10) {
                Notificacion::crear([
                    'tipo' => 'merma_alta',
                    'titulo' => 'Merma Alta',
                    'mensaje' => "Merma alta detectada: " . abs($registro->Cantidad) . " unidades del lote {$registro->Lote}",
                    'referencia_id' => $registro->Id,
                    'referencia_tabla' => 'Trazabilidad',
                    'prioridad' => 'media'
                ]);
            }
        });
    }
}