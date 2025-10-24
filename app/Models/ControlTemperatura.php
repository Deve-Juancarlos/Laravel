<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ControlTemperatura extends Model
{
    use HasFactory;

    protected $table = 'ControlTemperatura';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'Ubicacion',
        'ProductoId',
        'Lote',
        'Temperatura',
        'Humedad',
        'FechaControl',
        'HoraControl',
        'UsuarioId',
        'Estado',
        'CumpleRango',
        'RangoMinimo',
        'RangoMaximo',
        'SensorId',
        'Observaciones',
        'AccionCorrectiva',
        'ResponsableAccion',
        'FechaAccion',
        'TipoControl',
        'Zona'
    ];

    protected $casts = [
        'Temperatura' => 'decimal:2',
        'Humedad' => 'decimal:2',
        'FechaControl' => 'date',
        'RangoMinimo' => 'decimal:2',
        'RangoMaximo' => 'decimal:2',
        'FechaAccion' => 'datetime'
    ];

    protected $dates = ['FechaControl', 'FechaAccion'];

    // Relacionamentos
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'Codigo');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'UsuarioId', 'Id');
    }

    public function responsableAccion()
    {
        return $this->belongsTo(Usuario::class, 'ResponsableAccion', 'Id');
    }

    // Métodos de negocio
    public function getEstadoLabel()
    {
        $estados = [
            'Normal' => 'Normal',
            'Alerta' => 'Alerta',
            'Critico' => 'Crítico',
            'Corregido' => 'Corregido',
            'Falla' => 'Falla del Sensor'
        ];
        
        return $estados[$this->Estado] ?? $this->Estado;
    }

    public function getTipoControlLabel()
    {
        $tipos = [
            'Manual' => 'Control Manual',
            'Automatico' => 'Control Automático',
            'Verificacion' => 'Verificación',
            'Monitoreo' => 'Monitoreo Continuo',
            'Calibracion' => 'Calibración',
            'Mantenimiento' => 'Mantenimiento'
        ];
        
        return $tipos[$this->TipoControl] ?? $this->TipoControl;
    }

    public function getTemperaturaFormateada()
    {
        return number_format($this->Temperatura, 1) . '°C';
    }

    public function getHumedadFormateada()
    {
        return number_format($this->Humedad, 1) . '%';
    }

    public function getRangoFormateado()
    {
        return number_format($this->RangoMinimo, 1) . '°C - ' . number_format($this->RangoMaximo, 1) . '°C';
    }

    public function getDesviacionTemperatura()
    {
        if (!$this->RangoMinimo || !$this->RangoMaximo) {
            return null;
        }
        
        $centroRango = ($this->RangoMinimo + $this->RangoMaximo) / 2;
        return $this->Temperatura - $centroRango;
    }

    public function getDesviacionPorcentual()
    {
        if (!$this->RangoMinimo || !$this->RangoMaximo) {
            return null;
        }
        
        $desviacion = abs($this->getDesviacionTemperatura());
        $rangoTotal = $this->RangoMaximo - $this->RangoMinimo;
        
        if ($rangoTotal > 0) {
            return ($desviacion / $rangoTotal) * 100;
        }
        
        return 0;
    }

    public function isTemperaturaNormal()
    {
        if (!$this->RangoMinimo || !$this->RangoMaximo) {
            return true; // Si no hay rango definido, se considera normal
        }
        
        return $this->Temperatura >= $this->RangoMinimo && 
               $this->Temperatura <= $this->RangoMaximo;
    }

    public function isTemperaturaAlerta()
    {
        if (!$this->RangoMinimo || !$this->RangoMaximo) {
            return false;
        }
        
        $alertaSuperior = $this->RangoMaximo * 1.05; // 5% por encima del máximo
        $alertaInferior = $this->RangoMinimo * 0.95; // 5% por debajo del mínimo
        
        return $this->Temperatura > $alertaSuperior || 
               $this->Temperatura < $alertaInferior;
    }

    public function isTemperaturaCritica()
    {
        if (!$this->RangoMinimo || !$this->RangoMaximo) {
            return false;
        }
        
        $criticaSuperior = $this->RangoMaximo * 1.1; // 10% por encima del máximo
        $criticaInferior = $this->RangoMinimo * 0.9; // 10% por debajo del mínimo
        
        return $this->Temperatura > $criticaSuperior || 
               $this->Temperatura < $criticaInferior;
    }

    public function clasificarDesviacion()
    {
        if (!$this->isTemperaturaNormal()) {
            $desviacionPorcentual = $this->getDesviacionPorcentual();
            
            if ($desviacionPorcentual > 20) {
                return 'Crítica';
            } elseif ($desviacionPorcentual > 10) {
                return 'Alta';
            } elseif ($desviacionPorcentual > 5) {
                return 'Media';
            } else {
                return 'Baja';
            }
        }
        
        return 'Normal';
    }

    public function getTiempoExposicion()
    {
        $ultimoNormal = self::where('Ubicacion', $this->Ubicacion)
                           ->where('ProductoId', $this->ProductoId)
                           ->where('Id', '<', $this->Id)
                           ->where('Estado', 'Normal')
                           ->orderBy('Id', 'desc')
                           ->first();
        
        if ($ultimoNormal) {
            return $this->created_at->diffForHumans($ultimoNormal->created_at);
        }
        
        return null;
    }

    public function registrarAccionCorrectiva($accion, $responsableId)
    {
        $this->AccionCorrectiva = $accion;
        $this->ResponsableAccion = $responsableId;
        $this->FechaAccion = now();
        $this->Estado = $this->Estado === 'Critico' ? 'Corregido' : 'Normal';
        $this->save();

        // Registrar en trazabilidad
        Trazabilidad::registrarControlTemperatura(
            $this->ProductoId,
            $this->Temperatura,
            $this->Lote,
            true,
            [
                'UbicacionOrigen' => $this->Ubicacion,
                'Observaciones' => "Acción correctiva aplicada: {$accion}"
            ]
        );

        return $this;
    }

    public function calcularEstadisticasPeriodo($fechaInicio, $fechaFin)
    {
        $registros = self::whereBetween('FechaControl', [$fechaInicio, $fechaFin])
                          ->where('Ubicacion', $this->Ubicacion)
                          ->where('ProductoId', $this->ProductoId);
        
        $estadisticas = [
            'total_registros' => $registros->count(),
            'temperatura_promedio' => $registros->avg('Temperatura'),
            'temperatura_minima' => $registros->min('Temperatura'),
            'temperatura_maxima' => $registros->max('Temperatura'),
            'desviacion_estandar' => 0,
            'porcentaje_cumplimiento' => 0,
            'horas_alerta' => 0,
            'horas_critico' => 0
        ];

        if ($estadisticas['total_registros'] > 0) {
            // Calcular desviación estándar
            $temps = $registros->pluck('Temperatura')->toArray();
            $media = $estadisticas['temperatura_promedio'];
            $varianza = array_sum(array_map(function($temp) use ($media) {
                return pow($temp - $media, 2);
            }, $temps)) / count($temps);
            $estadisticas['desviacion_estandar'] = sqrt($varianza);
            
            // Porcentaje de cumplimiento
            $registrosCumplimiento = $registros->where('CumpleRango', true)->count();
            $estadisticas['porcentaje_cumplimiento'] = ($registrosCumplimiento / $estadisticas['total_registros']) * 100;
        }

        return $estadisticas;
    }

    // Scopes
    public function scopeNormales($query)
    {
        return $query->where('Estado', 'Normal')
                    ->where('CumpleRango', true);
    }

    public function scopeEnAlerta($query)
    {
        return $query->where('Estado', 'Alerta');
    }

    public function scopeCriticos($query)
    {
        return $query->where('Estado', 'Critico');
    }

    public function scopeCorregidos($query)
    {
        return $query->where('Estado', 'Corregido');
    }

    public function scopePorUbicacion($query, $ubicacion)
    {
        return $query->where('Ubicacion', 'like', '%' . $ubicacion . '%');
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('ProductoId', $productoId);
    }

    public function scopePorLote($query, $lote)
    {
        return $query->where('Lote', $lote);
    }

    public function scopePorZona($query, $zona)
    {
        return $query->where('Zona', $zona);
    }

    public function scopePorPeriodo($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('FechaControl', [$fechaInicio, $fechaFin]);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('UsuarioId', $usuarioId);
    }

    public function scopePorSensor($query, $sensorId)
    {
        return $query->where('SensorId', $sensorId);
    }

    public function scopeCumpleRango($query)
    {
        return $query->where('CumpleRango', true);
    }

    public function scopeNoCumpleRango($query)
    {
        return $query->where('CumpleRango', false);
    }

    public function scopeFueraDeRango($query)
    {
        return $query->where(function($q) {
            $q->where(function($sub) {
                $sub->whereNotNull('RangoMinimo')
                    ->where('Temperatura', '<', DB::raw('RangoMinimo'));
            })->orWhere(function($sub) {
                $sub->whereNotNull('RangoMaximo')
                    ->where('Temperatura', '>', DB::raw('RangoMaximo'));
            });
        });
    }

    public function scopeConAccion($query)
    {
        return $query->whereNotNull('AccionCorrectiva');
    }

    public function scopeSinAccion($query)
    {
        return $query->whereNull('AccionCorrectiva')
                    ->whereIn('Estado', ['Alerta', 'Critico']);
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('FechaControl', now()->toDateString());
    }

    public function scopeEstaSemana($query)
    {
        return $query->whereBetween('FechaControl', [
            now()->startOfWeek()->toDateString(),
            now()->endOfWeek()->toDateString()
        ]);
    }

    public function scopeEsteMes($query)
    {
        return $query->whereMonth('FechaControl', now()->month)
                    ->whereYear('FechaControl', now()->year);
    }

    // Métodos estáticos
    public static function obtenerRangoPorProducto($productoId)
    {
        $producto = Producto::find($productoId);
        if (!$producto) {
            return ['minimo' => null, 'maximo' => null];
        }
        
        // Rangos por tipo de producto farmacéutico
        switch (true) {
            case strpos(strtolower($producto->FormaFarmaceutica), 'inmunobiológico') !== false:
            case strpos(strtolower($producto->FormaFarmaceutica), 'vacuna') !== false:
                return ['minimo' => 2, 'maximo' => 8]; // Vacunas e inmunobiológicos
            
            case strpos(strtolower($producto->FormaFarmaceutica), 'antibiótico') !== false:
            case strpos(strtolower($producto->FormaFarmaceutica), 'antimicrobiano') !== false:
                return ['minimo' => 15, 'maximo' => 25]; // Antibióticos
            
            case strpos(strtolower($producto->FormaFarmaceutica), 'insulina') !== false:
                return ['minimo' => 2, 'maximo' => 8]; // Insulina
            
            case strpos(strtolower($producto->FormaFarmaceutica), 'probiotico') !== false:
            case strpos(strtolower($producto->FormaFarmaceutica), 'cultivo') !== false:
                return ['minimo' => 2, 'maximo' => 8]; // Probióticos
            
            default:
                return ['minimo' => 15, 'maximo' => 25]; // Temperatura ambiente
        }
    }

    public static function obtenerUltimosRegistros($ubicacion, $limite = 10)
    {
        return self::where('Ubicacion', $ubicacion)
                  ->orderBy('FechaControl', 'desc')
                  ->orderBy('HoraControl', 'desc')
                  ->limit($limite)
                  ->get();
    }

    public static function generarAlertasAutomaticas()
    {
        $alertas = [];
        
        // Temperaturas críticas en las últimas 24 horas
        $temperaturasCriticas = self::where('Estado', 'Critico')
                                   ->where('created_at', '>=', now()->subDay())
                                   ->get();
        
        if ($temperaturasCriticas->count() > 0) {
            $alertas[] = [
                'tipo' => 'temperatura_critica',
                'titulo' => 'Temperaturas Críticas',
                'cantidad' => $temperaturasCriticas->count(),
                'ubicaciones' => $temperaturasCriticas->groupBy('Ubicacion')->keys()->toArray()
            ];
        }
        
        // Ubicaciones sin control en las últimas 4 horas
        $ubicacionesActivas = self::selectRaw('Ubicacion, MAX(created_at) as ultimo_control')
                                 ->where('created_at', '>=', now()->subHours(4))
                                 ->groupBy('Ubicacion')
                                 ->pluck('Ubicacion')
                                 ->toArray();
        
        $ubicaciones = ['Refrigerador Principal', 'Refrigerador Secundario', 'Congelador', 'Cámara Fría'];
        $ubicacionesSinControl = array_diff($ubicaciones, $ubicacionesActivas);
        
        if (count($ubicacionesSinControl) > 0) {
            $alertas[] = [
                'tipo' => 'falta_control',
                'titulo' => 'Falta Control de Temperatura',
                'cantidad' => count($ubicacionesSinControl),
                'ubicaciones' => $ubicacionesSinControl
            ];
        }
        
        // Tendencias de temperatura
        $tendencia = self::selectRaw('Ubicacion, DATE_FORMAT(FechaControl, "%Y-%m-%d") as fecha, AVG(Temperatura) as temp_promedio')
                        ->where('created_at', '>=', now()->subDays(7))
                        ->groupBy('Ubicacion', 'fecha')
                        ->having('temp_promedio', '<', 5)
                        ->get();
        
        if ($tendencia->count() > 0) {
            $alertas[] = [
                'tipo' => 'tendencia_baja',
                'titulo' => 'Tendencia de Temperatura Baja',
                'cantidad' => $tendencia->count(),
                'ubicaciones' => $tendencia->groupBy('Ubicacion')->keys()->toArray()
            ];
        }
        
        return $alertas;
    }

    public static function obtenerDashboardTemperaturatura($periodo = 24)
    {
        $fechaDesde = now()->subHours($periodo);
        
        $dashboard = [
            'periodo_horas' => $periodo,
            'total_registros' => self::where('created_at', '>=', $fechaDesde)->count(),
            'ubicaciones' => [],
            'estado_general' => 'Normal',
            'alertas' => [],
            'graficos' => [
                'temperaturas_por_hora' => [],
                'cumplimiento_por_ubicacion' => []
            ]
        ];

        // Obtener datos por ubicación
        $ubicaciones = self::where('created_at', '>=', $fechaDesde)
                          ->selectRaw('Ubicacion, COUNT(*) as total, 
                                     SUM(CASE WHEN Estado = "Normal" THEN 1 ELSE 0 END) as normales,
                                     SUM(CASE WHEN Estado = "Alerta" THEN 1 ELSE 0 END) as alertas,
                                     SUM(CASE WHEN Estado = "Critico" THEN 1 ELSE 0 END) as criticos,
                                     AVG(Temperatura) as temp_promedio,
                                     MIN(Temperatura) as temp_minima,
                                     MAX(Temperatura) as temp_maxima')
                          ->groupBy('Ubicacion')
                          ->get();

        foreach ($ubicaciones as $ubicacion) {
            $dashboard['ubicaciones'][] = [
                'nombre' => $ubicacion->Ubicacion,
                'total_registros' => $ubicacion->total,
                'normales' => $ubicacion->normales,
                'alertas' => $ubicacion->alertas,
                'criticos' => $ubicacion->criticos,
                'porcentaje_cumplimiento' => $ubicacion->total > 0 ? ($ubicacion->normales / $ubicacion->total) * 100 : 0,
                'temperatura_promedio' => round($ubicacion->temp_promedio, 1),
                'temperatura_minima' => round($ubicacion->temp_minima, 1),
                'temperatura_maxima' => round($ubicacion->temp_maxima, 1)
            ];
        }

        // Estado general
        $totalAlertas = $ubicaciones->sum('alertas') + $ubicaciones->sum('criticos');
        if ($totalAlertas > 0) {
            $dashboard['estado_general'] = $ubicaciones->sum('criticos') > 0 ? 'Crítico' : 'Alerta';
        }

        // Obtener alertas
        $dashboard['alertas'] = self::generarAlertasAutomaticas();

        return $dashboard;
    }

    public static function generarReporteTemperaturatura($fechaInicio, $fechaFin, $ubicacion = null)
    {
        $query = self::porPeriodo($fechaInicio, $fechaFin);
        
        if ($ubicacion) {
            $query->porUbicacion($ubicacion);
        }
        
        $registros = $query->orderBy('FechaControl')
                          ->orderBy('HoraControl')
                          ->get();
        
        $reporte = [
            'periodo' => [
                'inicio' => $fechaInicio->format('Y-m-d'),
                'fin' => $fechaFin->format('Y-m-d')
            ],
            'ubicacion' => $ubicacion,
            'resumen' => [
                'total_registros' => $registros->count(),
                'normales' => $registros->where('Estado', 'Normal')->count(),
                'alertas' => $registros->where('Estado', 'Alerta')->count(),
                'criticos' => $registros->where('Estado', 'Critico')->count(),
                'porcentaje_cumplimiento' => $registros->count() > 0 ? 
                    ($registros->where('Estado', 'Normal')->count() / $registros->count()) * 100 : 0
            ],
            'estadisticas' => [
                'temperatura_promedio' => $registros->avg('Temperatura'),
                'temperatura_minima' => $registros->min('Temperatura'),
                'temperatura_maxima' => $registros->max('Temperatura'),
                'humedad_promedio' => $registros->avg('Humedad'),
                'registros_con_accion' => $registros->whereNotNull('AccionCorrectiva')->count()
            ],
            'detalle' => $registros->map(function($registro) {
                return [
                    'fecha' => $registro->FechaControl,
                    'hora' => $registro->HoraControl,
                    'ubicacion' => $registro->Ubicacion,
                    'temperatura' => $registro->getTemperaturaFormateada(),
                    'humedad' => $registro->getHumedadFormateada(),
                    'estado' => $registro->getEstadoLabel(),
                    'cumple' => $registro->CumpleRango ? 'Sí' : 'No',
                    'rango' => $registro->getRangoFormateado(),
                    'observaciones' => $registro->Observaciones,
                    'accion_correctiva' => $registro->AccionCorrectiva,
                    'clasificacion' => $registro->clasificarDesviacion()
                ];
            })
        ];

        return $reporte;
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($control) {
            // Establecer fecha y hora por defecto
            if (!$control->FechaControl) {
                $control->FechaControl = now()->toDateString();
            }
            
            if (!$control->HoraControl) {
                $control->HoraControl = now()->format('H:i:s');
            }
            
            // Establecer usuario por defecto
            if (!$control->UsuarioId) {
                $control->UsuarioId = auth()->id() ?? 1;
            }
            
            // Obtener rango automático si no está definido
            if (!$control->RangoMinimo || !$control->RangoMaximo) {
                $rango = self::obtenerRangoPorProducto($control->ProductoId);
                $control->RangoMinimo = $rango['minimo'];
                $control->RangoMaximo = $rango['maximo'];
            }
            
            // Determinar estado y cumplimiento
            $control->CumpleRango = $control->isTemperaturaNormal();
            
            if ($control->isTemperaturaCritica()) {
                $control->Estado = 'Critico';
            } elseif ($control->isTemperaturaAlerta()) {
                $control->Estado = 'Alerta';
            } else {
                $control->Estado = 'Normal';
            }
        });

        static::saved(function ($control) {
            // Generar notificaciones según el estado
            
            if ($control->Estado === 'Critico') {
                Notificacion::crear([
                    'tipo' => 'temperatura_critica',
                    'titulo' => 'Temperatura Crítica',
                    'mensaje' => "Temperatura crítica en {$control->Ubicacion}: {$control->getTemperaturaFormateada()} (Rango: {$control->getRangoFormateado()})",
                    'referencia_id' => $control->Id,
                    'referencia_tabla' => 'ControlTemperatura',
                    'prioridad' => 'critica'
                ]);
            }
            
            if ($control->Estado === 'Alerta') {
                Notificacion::crear([
                    'tipo' => 'temperatura_alerta',
                    'titulo' => 'Temperatura en Alerta',
                    'mensaje' => "Temperatura en alerta en {$control->Ubicacion}: {$control->getTemperaturaFormateada()}",
                    'referencia_id' => $control->Id,
                    'referencia_tabla' => 'ControlTemperatura',
                    'prioridad' => 'alta'
                ]);
            }
            
            // Registrar en trazabilidad
            Trazabilidad::registrarControlTemperatura(
                $control->ProductoId,
                $control->Temperatura,
                $control->Lote,
                $control->CumpleRango,
                [
                    'UbicacionOrigen' => $control->Ubicacion,
                    'Observaciones' => $control->Estado . ': ' . ($control->Observaciones ?? '')
                ]
            );
        });
    }
}