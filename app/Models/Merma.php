<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Merma extends Model
{
    use HasFactory;

    protected $table = 'Mermas';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'ProductoId',
        'Lote',
        'Cantidad',
        'TipoMerma',
        'Causa',
        'Descripcion',
        'FechaDetecion',
        'FechaReporte',
        'Responsable',
        'Aprobado',
        'AprobadoPor',
        'FechaAprobacion',
        'ValorCosto',
        'ValorVenta',
        'CentroCosto',
        'Estado',
        'Observaciones',
        'ImagenEvidencia',
        'NumeroReporte',
        'Motivo'
    ];

    protected $casts = [
        'Cantidad' => 'decimal:3',
        'FechaDetecion' => 'date',
        'FechaReporte' => 'date',
        'FechaAprobacion' => 'datetime',
        'ValorCosto' => 'decimal:4',
        'ValorVenta' => 'decimal:4'
    ];

    protected $dates = ['FechaDetecion', 'FechaReporte', 'FechaAprobacion'];

    // Relacionamentos
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'Codigo');
    }

    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'Responsable', 'Id');
    }

    public function aprobador()
    {
        return $this->belongsTo(Usuario::class, 'AprobadoPor', 'Id');
    }

    public function centroCosto()
    {
        return $this->belongsTo(CentroCosto::class, 'CentroCosto', 'Codigo');
    }

    // Métodos de negocio
    public function getTipoMermaLabel()
    {
        $tipos = [
            'Vencimiento' => 'Por Vencimiento',
            'Temperatura' => 'Por Temperatura',
            'DeterioroFisico' => 'Deterioro Físico',
            'RoboHurto' => 'Robo/Hurto',
            'ErrorOperativo' => 'Error Operativo',
            'Calidad' => 'Control de Calidad',
            'DevolucionProveedor' => 'Devolución al Proveedor',
            'DevolucionCliente' => 'Devolución de Cliente',
            'Siniestro' => 'Siniestro',
            'Obsolescencia' => 'Obsolescencia',
            'Desperfecto' => 'Desperfecto',
            'Contaminacion' => 'Contaminación',
            'MalaAlmacenaje' => 'Mala Almacenaje',
            'Desperdicio' => 'Desperdicio'
        ];
        
        return $tipos[$this->TipoMerma] ?? $this->TipoMerma;
    }

    public function getEstadoLabel()
    {
        $estados = [
            'Detectado' => 'Detectado',
            'Reportado' => 'Reportado',
            'EnRevision' => 'En Revisión',
            'Aprobado' => 'Aprobado',
            'Rechazado' => 'Rechazado',
            'Aplicado' => 'Aplicado'
        ];
        
        return $estados[$this->Estado] ?? $this->Estado;
    }

    public function getCausaLabel()
    {
        $causas = [
            'ProductoVencido' => 'Producto Vencido',
            'TemperaturaInadecuada' => 'Temperatura Inadecuada',
            'ManejoInadecuado' => 'Manejo Inadecuado',
            'FallaRefrigeracion' => 'Falla en Refrigeración',
            'ErrorPersonal' => 'Error del Personal',
            'ProblemaProveedor' => 'Problema del Proveedor',
            'ProductoDefectuoso' => 'Producto Defectuoso',
            'Desgaste' => 'Desgaste',
            'Accidente' => 'Accidente',
            'CondicionesAmbientales' => 'Condiciones Ambientales',
            'FallaEquipos' => 'Falla de Equipos',
            'MalaPracticas' => 'Mala Prácticas',
            'Limpieza' => 'Limpieza',
            'Otros' => 'Otros'
        ];
        
        return $causas[$this->Causa] ?? $this->Causa;
    }

    public function getValorImpacto()
    {
        return $this->ValorCosto * $this->Cantidad;
    }

    public function getValorPerdidaVenta()
    {
        return $this->ValorVenta * $this->Cantidad;
    }

    public function getMargenPerdido()
    {
        if ($this->ValorCosto > 0) {
            return (($this->ValorVenta - $this->ValorCosto) / $this->ValorCosto) * 100;
        }
        return 0;
    }

    public function isAprobada()
    {
        return $this->Aprobado && $this->Estado === 'Aprobado';
    }

    public function isPendiente()
    {
        return in_array($this->Estado, ['Detectado', 'Reportado', 'EnRevision']);
    }

    public function isRechazada()
    {
        return $this->Estado === 'Rechazado';
    }

    public function aprobar($aprobadorId, $observaciones = null)
    {
        if ($this->isPendiente()) {
            $this->Estado = 'Aprobado';
            $this->Aprobado = true;
            $this->AprobadoPor = $aprobadorId;
            $this->FechaAprobacion = now();
            
            if ($observaciones) {
                $this->Observaciones = ($this->Observaciones ?? '') . "\nAprobado: " . $observaciones;
            }
            
            $this->save();

            // Registrar en trazabilidad
            Trazabilidad::registrarMerma(
                $this->ProductoId,
                $this->Cantidad,
                $this->Lote,
                "Merma aprobada: {$this->getTipoMermaLabel()}"
            );

            // Aplicar merma al inventario
            $this->aplicarAlInventario();

            return true;
        }
        
        return false;
    }

    public function rechazar($motivo)
    {
        if ($this->isPendiente()) {
            $this->Estado = 'Rechazado';
            $this->Aprobado = false;
            $this->Observaciones = ($this->Observaciones ?? '') . "\nRechazado: " . $motivo;
            $this->save();

            return true;
        }
        
        return false;
    }

    public function aplicarAlInventario()
    {
        if (!$this->isAprobada()) {
            return false;
        }

        // Buscar saldo correspondiente
        $saldo = Saldo::where('Producto', $this->ProductoId)
                     ->where('Lote', $this->Lote)
                     ->first();

        if (!$saldo) {
            throw new \Exception("No se encontró saldo para el producto {$this->ProductoId} y lote {$this->Lote}");
        }

        // Reducir stock
        $saldo->actualizarStock(-$this->Cantidad, 'Merma');

        // Marcar como aplicado
        $this->Estado = 'Aplicado';
        $this->save();

        return $saldo;
    }

    public function generarNumeroReporte()
    {
        if (!$this->NumeroReporte) {
            $numero = 'MER-' . date('Y') . '-' . str_pad(static::whereYear('FechaReporte', date('Y'))->count() + 1, 6, '0', STR_PAD_LEFT);
            $this->NumeroReporte = $numero;
            $this->save();
        }
        
        return $this->NumeroReporte;
    }

    public function calcularPorcentajeMerma($stockTotal)
    {
        if ($stockTotal > 0) {
            return ($this->Cantidad / $stockTotal) * 100;
        }
        return 0;
    }

    public function evaluarSeveridad()
    {
        $merma = $this->Cantidad;
        $porcentaje = $this->calcularPorcentajeMerma($this->producto->saldos()->sum('Stock'));
        
        if ($merma > 100 || $porcentaje > 5) {
            return 'Crítica';
        } elseif ($merma > 50 || $porcentaje > 2) {
            return 'Alta';
        } elseif ($merma > 10 || $porcentaje > 0.5) {
            return 'Media';
        } else {
            return 'Baja';
        }
    }

    public function generarInforme()
    {
        $mermaPorcentaje = $this->calcularPorcentajeMerma($this->producto->saldos()->sum('Stock'));
        
        return [
            'numero_reporte' => $this->NumeroReporte,
            'fecha_deteccion' => $this->FechaDetecion,
            'fecha_reporte' => $this->FechaReporte,
            'producto' => [
                'codigo' => $this->producto->Codigo,
                'descripcion' => $this->producto->Descripcion,
                'laboratorio' => $this->producto->Laboratorio
            ],
            'lote' => $this->Lote,
            'cantidad_merma' => $this->Cantidad,
            'porcentaje_merma' => round($mermaPorcentaje, 2),
            'tipo_merma' => $this->getTipoMermaLabel(),
            'causa' => $this->getCausaLabel(),
            'descripcion' => $this->Descripcion,
            'responsable' => $this->responsable->nombre ?? 'N/A',
            'estado' => $this->getEstadoLabel(),
            'valor_impacto' => $this->getValorImpacto(),
            'valor_perdida_venta' => $this->getValorPerdidaVenta(),
            'margen_perdido' => round($this->getMargenPerdido(), 2),
            'severidad' => $this->evaluarSeveridad(),
            'observaciones' => $this->Observaciones
        ];
    }

    // Scopes
    public function scopeAprobadas($query)
    {
        return $query->where('Aprobado', true)
                    ->where('Estado', 'Aprobado');
    }

    public function scopePendientes($query)
    {
        return $query->whereIn('Estado', ['Detectado', 'Reportado', 'EnRevision']);
    }

    public function scopeRechazadas($query)
    {
        return $query->where('Estado', 'Rechazado');
    }

    public function scopeAplicadas($query)
    {
        return $query->where('Estado', 'Aplicado');
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('TipoMerma', $tipo);
    }

    public function scopePorCausa($query, $causa)
    {
        return $query->where('Causa', $causa);
    }

    public function scopePorPeriodo($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('FechaDetecion', [$fechaInicio, $fechaFin]);
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('ProductoId', $productoId);
    }

    public function scopePorLote($query, $lote)
    {
        return $query->where('Lote', $lote);
    }

    public function scopePorResponsable($query, $responsableId)
    {
        return $query->where('Responsable', $responsableId);
    }

    public function scopeConValorAlto($query, $valorMinimo = 1000)
    {
        return $query->where('ValorCosto', '>', 0)
                    ->whereRaw('(ValorCosto * Cantidad) >= ?', [$valorMinimo]);
    }

    public function scopeCriticas($query)
    {
        return $query->whereHas('producto', function($q) {
            $q->whereRaw('? <= (SELECT SUM(Stock * PrecioCosto) FROM Saldos WHERE Producto = Productos.Codigo) * 0.05', function($subq) {
                $subq->selectRaw('ValorCosto * Cantidad')
                     ->from('Mermas')
                     ->whereColumn('Mermas.ProductoId', 'Productos.Codigo');
            });
        });
    }

    public function scopeSinEvidencia($query)
    {
        return $query->whereNull('ImagenEvidencia');
    }

    public function scopeConEvidencia($query)
    {
        return $query->whereNotNull('ImagenEvidencia');
    }

    // Métodos estáticos
    public static function generarNumeroReporte($año = null)
    {
        $año = $año ?? date('Y');
        $numero = 'MER-' . $año . '-' . str_pad(static::whereYear('FechaReporte', $año)->count() + 1, 6, '0', STR_PAD_LEFT);
        return $numero;
    }

    public static function obtenerEstadisticas($fechaInicio, $fechaFin)
    {
        $mermas = self::aprobadas()
                     ->porPeriodo($fechaInicio, $fechaFin);
        
        $estadisticas = [
            'total_mermas' => $mermas->count(),
            'total_cantidad' => $mermas->sum('Cantidad'),
            'total_valor_costo' => $mermas->sum(DB::raw('ValorCosto * Cantidad')),
            'total_valor_venta' => $mermas->sum(DB::raw('ValorVenta * Cantidad')),
            'por_tipo' => [],
            'por_causa' => [],
            'por_mes' => [],
            'productos_mas_afectados' => [],
            'promedio_merma_diaria' => 0,
            'porcentaje_vs_inventario' => 0
        ];

        // Agrupar por tipo
        foreach ($mermas->get()->groupBy('TipoMerma') as $tipo => $items) {
            $estadisticas['por_tipo'][$tipo] = [
                'cantidad' => $items->sum('Cantidad'),
                'valor' => $items->sum(function($merma) { return $merma->ValorCosto * $merma->Cantidad; }),
                'frecuencia' => $items->count()
            ];
        }

        // Agrupar por causa
        foreach ($mermas->get()->groupBy('Causa') as $causa => $items) {
            $estadisticas['por_causa'][$causa] = [
                'cantidad' => $items->sum('Cantidad'),
                'valor' => $items->sum(function($merma) { return $merma->ValorCosto * $merma->Cantidad; }),
                'frecuencia' => $items->count()
            ];
        }

        // Agrupar por mes
        foreach ($mermas->get()->groupBy(function($merma) {
            return $merma->FechaDetecion->format('Y-m');
        }) as $mes => $items) {
            $estadisticas['por_mes'][$mes] = [
                'cantidad' => $items->sum('Cantidad'),
                'valor' => $items->sum(function($merma) { return $merma->ValorCosto * $merma->Cantidad; }),
                'frecuencia' => $items->count()
            ];
        }

        // Productos más afectados
        $productosAfectados = $mermas->selectRaw('ProductoId, SUM(Cantidad) as total_cantidad, SUM(ValorCosto * Cantidad) as total_valor')
                                    ->groupBy('ProductoId')
                                    ->orderBy('total_valor', 'desc')
                                    ->take(10)
                                    ->get();

        foreach ($productosAfectados as $producto) {
            $estadisticas['productos_mas_afectados'][] = [
                'producto_id' => $producto->ProductoId,
                'producto' => $producto->producto->Descripcion ?? 'N/A',
                'cantidad' => $producto->total_cantidad,
                'valor' => $producto->total_valor
            ];
        }

        // Promedio diario
        $dias = $fechaInicio->diffInDays($fechaFin) + 1;
        $estadisticas['promedio_merma_diaria'] = $estadisticas['total_cantidad'] / $dias;

        return $estadisticas;
    }

    public static function obtenerAlertasMermas()
    {
        $alertas = [];
        
        // Mermas críticas pendientes
        $mermasCriticas = self::pendientes()
                             ->whereHas('producto', function($q) {
                                 $q->whereRaw('(SELECT SUM(Stock * PrecioCosto) FROM Saldos WHERE Producto = Productos.Codigo) > 1000');
                             })
                             ->get();
        
        if ($mermasCriticas->count() > 0) {
            $alertas[] = [
                'tipo' => 'mermas_criticas_pendientes',
                'titulo' => 'Mermas Críticas Pendientes',
                'cantidad' => $mermasCriticas->count(),
                'mermas' => $mermasCriticas->take(10)->map(function($merma) {
                    return [
                        'producto' => $merma->producto->Descripcion,
                        'lote' => $merma->Lote,
                        'cantidad' => $merma->Cantidad,
                        'valor' => $merma->getValorImpacto()
                    ];
                })
            ];
        }
        
        // Mermas frecuentes
        $mermasFrecuentes = self::aprobadas()
                               ->where('FechaDetecion', '>=', now()->subDays(30))
                               ->selectRaw('ProductoId, COUNT(*) as frecuencia')
                               ->groupBy('ProductoId')
                               ->having('frecuencia', '>=', 3)
                               ->get();
        
        if ($mermasFrecuentes->count() > 0) {
            $alertas[] = [
                'tipo' => 'productos_mermas_frecuentes',
                'titulo' => 'Productos con Mermas Frecuentes',
                'cantidad' => $mermasFrecuentes->count(),
                'productos' => $mermasFrecuentes->map(function($frecuencia) {
                    return [
                        'producto_id' => $frecuencia->ProductoId,
                        'producto' => $frecuencia->producto->Descripcion ?? 'N/A',
                        'frecuencia' => $frecuencia->frecuencia
                    ];
                })
            ];
        }
        
        return $alertas;
    }

    public static function generarReporteCompleto($fechaInicio, $fechaFin)
    {
        $estadisticas = self::obtenerEstadisticas($fechaInicio, $fechaFin);
        
        // Obtener detalles de mermas por periodo
        $mermasDetalle = self::aprobadas()
                            ->porPeriodo($fechaInicio, $fechaFin)
                            ->with(['producto', 'responsable', 'centroCosto'])
                            ->orderBy('FechaDetecion', 'desc')
                            ->get();
        
        // Calcular KPIs adicionales
        $kpis = [
            'costo_por_merma' => $estadisticas['total_cantidad'] > 0 ? 
                $estadisticas['total_valor_costo'] / $estadisticas['total_cantidad'] : 0,
            'tasa_merma_mensual' => ($estadisticas['total_cantidad'] / $fechaInicio->diffInDays($fechaFin)) * 30,
            'productos_afectados' => $mermasDetalle->unique('ProductoId')->count(),
            'responsables_con_mermas' => $mermasDetalle->unique('Responsable')->count()
        ];
        
        return [
            'periodo' => [
                'inicio' => $fechaInicio->format('Y-m-d'),
                'fin' => $fechaFin->format('Y-m-d')
            ],
            'estadisticas' => $estadisticas,
            'kpis' => $kpis,
            'detalle' => $mermasDetalle->map(function($merma) {
                return $merma->generarInforme();
            })
        ];
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($merma) {
            // Establecer fechas por defecto
            if (!$merma->FechaDetecion) {
                $merma->FechaDetecion = now()->toDateString();
            }
            
            if (!$merma->FechaReporte) {
                $merma->FechaReporte = now()->toDateString();
            }
            
            // Estado por defecto
            if (!$merma->Estado) {
                $merma->Estado = 'Detectado';
            }
            
            // Responsable por defecto
            if (!$merma->Responsable) {
                $merma->Responsable = auth()->id() ?? 1;
            }
            
            // Generar número de reporte
            if (!$merma->NumeroReporte) {
                $merma->NumeroReporte = self::generarNumeroReporte();
            }
        });

        static::saving(function ($merma) {
            // Validar cantidad positiva
            if ($merma->Cantidad <= 0) {
                throw new \Exception('La cantidad de merma debe ser mayor a cero');
            }
            
            // Validar que existe el producto
            $producto = Producto::find($merma->ProductoId);
            if (!$producto) {
                throw new \Exception('El producto especificado no existe');
            }
            
            // Validar saldo disponible
            $saldoDisponible = Saldo::where('Producto', $merma->ProductoId)
                                  ->where('Lote', $merma->Lote)
                                  ->sum('Stock');
            
            if ($saldoDisponible < $merma->Cantidad) {
                throw new \Exception("No hay suficiente stock disponible. Disponible: {$saldoDisponible}, Solicitado: {$merma->Cantidad}");
            }
            
            // Calcular valores si no están definidos
            if (!$merma->ValorCosto || !$merma->ValorVenta) {
                $saldo = Saldo::where('Producto', $merma->ProductoId)
                             ->where('Lote', $merma->Lote)
                             ->first();
                
                if ($saldo) {
                    $merma->ValorCosto = $merma->ValorCosto ?? $saldo->PrecioCosto;
                    $merma->ValorVenta = $merma->ValorVenta ?? $saldo->PrecioVenta;
                }
            }
        });

        static::saved(function ($merma) {
            // Generar alertas
            
            // Merma crítica
            if ($merma->isAprobada() && $merma->evaluarSeveridad() === 'Crítica') {
                Notificacion::crear([
                    'tipo' => 'merma_critica',
                    'titulo' => 'Merma Crítica',
                    'mensaje' => "Merma crítica detectada: {$merma->Cantidad} unidades de {$merma->producto->Descripcion} (Lote: {$merma->Lote})",
                    'referencia_id' => $merma->Id,
                    'referencia_tabla' => 'Mermas',
                    'prioridad' => 'critica'
                ]);
            }
            
            // Merma con valor alto
            if ($merma->getValorImpacto() > 5000) {
                Notificacion::crear([
                    'tipo' => 'merma_valor_alto',
                    'titulo' => 'Merma de Alto Valor',
                    'mensaje' => "Merma de alto valor: S/. " . number_format($merma->getValorImpacto(), 2) . " en {$merma->producto->Descripcion}",
                    'referencia_id' => $merma->Id,
                    'referencia_tabla' => 'Mermas',
                    'prioridad' => 'alta'
                ]);
            }
        });

        static::updating(function ($merma) {
            // No permitir modificar aprobaciones
            if ($merma->isAprobada() && $merma->isDirty('Aprobado')) {
                throw new \Exception('No se puede modificar el estado de aprobación de una merma ya aplicada');
            }
        });
    }
}