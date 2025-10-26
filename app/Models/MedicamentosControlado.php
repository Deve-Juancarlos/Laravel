<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MedicamentosControlado extends Model
{
    use HasFactory;

    protected $table = 'MedicamentosControlados';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'ProductoId',
        'Lote',
        'CantidadDispensada',
        'CantidadOriginal',
        'RecetaMedicaId',
        'ClienteId',
        'MedicoId',
        'FarmaceuticoId',
        'FechaDispensacion',
        'HoraDispensacion',
        'TipoControl',
        'NivelControl',
        'AutorizacionEspecial',
        'NumeroAutorizacion',
        'Observaciones',
        'Estado',
        'MotivoDispensacion',
        'EdadPaciente',
        'SexoPaciente',
        'DireccionPaciente',
        'TelefonoPaciente',
        'DosisPrescrita',
        'ViaAdministracion',
        'Frecuencia',
        'DuracionTratamiento',
        'IndicacionesEspeciales',
        'SeguimientoRequerido',
        'FechaSeguimiento',
        'NumeroDispensacion',
        'PeriodoEspera'
    ];

    protected $casts = [
        'CantidadDispensada' => 'decimal:3',
        'CantidadOriginal' => 'decimal:3',
        'FechaDispensacion' => 'date',
        'FechaSeguimiento' => 'date',
        'PeriodoEspera' => 'integer'
    ];

    protected $dates = ['FechaDispensacion', 'FechaSeguimiento'];

    // Relacionamentos
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'Codigo');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'ClienteId', 'Codigo');
    }   

    // Métodos de negocio
    public function getTipoControlLabel()
    {
        $tipos = [
            'Psicotropico' => 'Psicotrópico',
            'Estupefaciente' => 'Estupefaciente',
            'SustanciaControlada' => 'Sustancia Controlada',
            'RecetaEspecial' => 'Receta Especial',
            'ControlDuplo' => 'Control Doble',
            'RegistroEspecial' => 'Registro Especial'
        ];
        
        return $tipos[$this->TipoControl] ?? $this->TipoControl;
    }

    public function getNivelControlLabel()
    {
        $niveles = [
            1 => 'Control Básico',
            2 => 'Control Intermedio',
            3 => 'Control Alto',
            4 => 'Control Especial'
        ];
        
        return $niveles[$this->NivelControl] ?? "Nivel {$this->NivelControl}";
    }

    public function getEstadoLabel()
    {
        $estados = [
            'Pendiente' => 'Pendiente',
            'EnRevision' => 'En Revisión',
            'Aprobado' => 'Aprobado',
            'Dispensado' => 'Dispensado',
            'Rechazado' => 'Rechazado',
            'Vencido' => 'Vencido',
            'Cancelado' => 'Cancelado'
        ];
        
        return $estados[$this->Estado] ?? $this->Estado;
    }

    public function getMotivoDispensacionLabel()
    {
        $motivos = [
            'TratamientoMedico' => 'Tratamiento Médico',
            'TerapiaCronica' => 'Terapia Crónica',
            'ManejoDolor' => 'Manejo del Dolor',
            'Emergencia' => 'Emergencia Médica',
            'Urgencia' => 'Urgencia Médica',
            'Seguimiento' => 'Seguimiento Médico',
            'MuestraMedica' => 'Muestra Médica',
            'Rehabilitacion' => 'Rehabilitación',
            'Odontologico' => 'Odontológico',
            'Psiquiatrico' => 'Psiquiátrico',
            'Neurologico' => 'Neurológico'
        ];
        
        return $motivos[$this->MotivoDispensacion] ?? $this->MotivoDispensacion;
    }

    public function getViaAdministracionLabel()
    {
        $vias = [
            'Oral' => 'Oral',
            'Sublingual' => 'Sublingual',
            'Rectal' => 'Rectal',
            'Topica' => 'Tópica',
            'Inyectable' => 'Inyectable',
            'Intravenosa' => 'Intravenosa',
            'Inhalatoria' => 'Inhalatoria',
            'Transdermica' => 'Transdérmica',
            'Intramuscular' => 'Intramuscular',
            'Subcutanea' => 'Subcutánea'
        ];
        
        return $vias[$this->ViaAdministracion] ?? $this->ViaAdministracion;
    }

    public function getDosisPrescritaFormateada()
    {
        if (!$this->DosisPrescrita) {
            return 'No especificada';
        }
        
        return $this->DosisPrescrita . ' ' . ($this->ViaAdministracion ? $this->getViaAdministracionLabel() : '');
    }

    public function getEdadPacienteFormateada()
    {
        if (!$this->EdadPaciente) {
            return 'No especificada';
        }
        
        return $this->EdadPaciente . ' años';
    }

    public function getFrecuenciaFormateada()
    {
        if (!$this->Frecuencia) {
            return 'No especificada';
        }
        
        return $this->Frecuencia;
    }

    public function getDuracionTratamientoFormateada()
    {
        if (!$this->DuracionTratamiento) {
            return 'No especificada';
        }
        
        return $this->DuracionTratamiento;
    }

    public function isDispensado()
    {
        return $this->Estado === 'Dispensado';
    }

    public function isPendiente()
    {
        return in_array($this->Estado, ['Pendiente', 'EnRevision']);
    }

    public function isAprobado()
    {
        return $this->Estado === 'Aprobado';
    }

    public function isRechazado()
    {
        return $this->Estado === 'Rechazado';
    }

    public function isVencido()
    {
        return $this->Estado === 'Vencido';
    }

    public function isControlEspecial()
    {
        return $this->NivelControl >= 3;
    }

    public function getPorcentajeDispensado()
    {
        if ($this->CantidadOriginal > 0) {
            return ($this->CantidadDispensada / $this->CantidadOriginal) * 100;
        }
        return 0;
    }

    public function generarNumeroDispensacion()
    {
        if (!$this->NumeroDispensacion) {
            $año = $this->FechaDispensacion->year;
            $numeroConsecutivo = static::whereYear('FechaDispensacion', $año)->count() + 1;
            $prefijo = $this->getPrefijoNumero();
            
            $this->NumeroDispensacion = $prefijo . '-' . $año . '-' . str_pad($numeroConsecutivo, 6, '0', STR_PAD_LEFT);
            $this->save();
        }
        
        return $this->NumeroDispensacion;
    }

    private function getPrefijoNumero()
    {
        switch ($this->TipoControl) {
            case 'Estupefaciente':
                return 'EST';
            case 'Psicotropico':
                return 'PSI';
            case 'SustanciaControlada':
                return 'CON';
            case 'ControlDuplo':
                return 'CDB';
            default:
                return 'DIS';
        }
    }

    public function validarLimiteDispensacion($nuevaCantidad)
    {
        // Verificar límite diario
        $limiteDiario = $this->obtenerLimiteDispensacion('diario');
        $cantidadHoy = static::where('ClienteId', $this->ClienteId)
                            ->where('ProductoId', $this->ProductoId)
                            ->whereDate('FechaDispensacion', now()->toDateString())
                            ->sum('CantidadDispensada');
        
        if ($cantidadHoy + $nuevaCantidad > $limiteDiario) {
            return [
                'valido' => false,
                'error' => "Límite diario excedido. Límite: {$limiteDiario}, Consumido hoy: {$cantidadHoy}"
            ];
        }
        
        // Verificar límite mensual
        $limiteMensual = $this->obtenerLimiteDispensacion('mensual');
        $cantidadMes = static::where('ClienteId', $this->ClienteId)
                            ->where('ProductoId', $this->ProductoId)
                            ->whereMonth('FechaDispensacion', now()->month)
                            ->whereYear('FechaDispensacion', now()->year)
                            ->sum('CantidadDispensada');
        
        if ($cantidadMes + $nuevaCantidad > $limiteMensual) {
            return [
                'valido' => false,
                'error' => "Límite mensual excedido. Límite: {$limiteMensual}, Consumido este mes: {$cantidadMes}"
            ];
        }
        
        return ['valido' => true];
    }

    private function obtenerLimiteDispensacion($periodo)
    {
        $limites = [
            'Estupefaciente' => [
                'diario' => 5,
                'mensual' => 30
            ],
            'Psicotropico' => [
                'diario' => 10,
                'mensual' => 90
            ],
            'SustanciaControlada' => [
                'diario' => 20,
                'mensual' => 180
            ]
        ];
        
        $limitesPorNivel = [
            1 => ['diario' => 30, 'mensual' => 300],
            2 => ['diario' => 20, 'mensual' => 200],
            3 => ['diario' => 10, 'mensual' => 100],
            4 => ['diario' => 5, 'mensual' => 30]
        ];
        
        if ($this->NivelControl >= 3) {
            return $limitesPorNivel[$this->NivelControl][$periodo] ?? 10;
        }
        
        return $limites[$this->TipoControl][$periodo] ?? 10;
    }

    public function aprobar($farmaceuticoId, $observaciones = null)
    {
        if ($this->isPendiente()) {
            // Validar límites antes de aprobar
            $validacion = $this->validarLimiteDispensacion($this->CantidadDispensada);
            if (!$validacion['valido']) {
                throw new \Exception($validacion['error']);
            }
            
            $this->Estado = 'Aprobado';
            $this->FarmaceuticoId = $farmaceuticoId;
            
            if ($observaciones) {
                $this->Observaciones = ($this->Observaciones ?? '') . "\nAprobado: " . $observaciones;
            }
            
            $this->save();
            
            return true;
        }
        
        return false;
    }

    public function dispensar($farmaceuticoId, $observaciones = null)
    {
        if (!$this->isAprobado()) {
            throw new \Exception('La dispensación debe estar aprobada antes de dispensar');
        }
        
        // Verificar stock disponible
        $saldoDisponible = Saldo::where('Producto', $this->ProductoId)
                               ->where('Lote', $this->Lote)
                               ->sum('Stock');
        
        if ($saldoDisponible < $this->CantidadDispensada) {
            throw new \Exception("Stock insuficiente. Disponible: {$saldoDisponible}, Solicitado: {$this->CantidadDispensada}");
        }
        
        // Aplicar dispensación
        $this->Estado = 'Dispensado';
        $this->FarmaceuticoId = $farmaceuticoId;
        $this->HoraDispensacion = now()->format('H:i:s');
        
        if ($observaciones) {
            $this->Observaciones = ($this->Observaciones ?? '') . "\nDispensado: " . $observaciones;
        }
        
        $this->save();
        
        // Reducir inventario
        $saldo = Saldo::where('Producto', $this->ProductoId)
                     ->where('Lote', $this->Lote)
                     ->first();
        
        $saldo->actualizarStock(-$this->CantidadDispensada, 'Dispensación Controlada');
        
        // Registrar en trazabilidad
        Trazabilidad::registrarPrescripcion(
            $this->RecetaMedicaId,
            $this->ProductoId,
            $this->CantidadDispensada,
            $this->Lote,
            $farmaceuticoId
        );
        
        return true;
    }

    public function rechazar($motivo, $farmaceuticoId)
    {
        if ($this->isPendiente()) {
            $this->Estado = 'Rechazado';
            $this->FarmaceuticoId = $farmaceuticoId;
            $this->Observaciones = ($this->Observaciones ?? '') . "\nRechazado: " . $motivo;
            $this->save();
            
            return true;
        }
        
        return false;
    }

    public function programarSeguimiento()
    {
        if ($this->isDispensado()) {
            $fechaSeguimiento = $this->calcularFechaSeguimiento();
            $this->FechaSeguimiento = $fechaSeguimiento;
            $this->SeguimientoRequerido = true;
            $this->save();
            
            return $fechaSeguimiento;
        }
        
        return null;
    }

    private function calcularFechaSeguimiento()
    {
        $dias = 15; // Por defecto 15 días
        
        switch ($this->TipoControl) {
            case 'Estupefaciente':
                $dias = 7;
                break;
            case 'Psicotropico':
                $dias = 15;
                break;
            case 'SustanciaControlada':
                $dias = 30;
                break;
        }
        
        return now()->addDays($dias);
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->whereIn('Estado', ['Pendiente', 'EnRevision']);
    }

    public function scopeAprobados($query)
    {
        return $query->where('Estado', 'Aprobado');
    }

    public function scopeDispensados($query)
    {
        return $query->where('Estado', 'Dispensado');
    }

    public function scopeRechazados($query)
    {
        return $query->where('Estado', 'Rechazado');
    }

    public function scopeVencidos($query)
    {
        return $query->where('Estado', 'Vencido');
    }

    public function scopePorTipoControl($query, $tipo)
    {
        return $query->where('TipoControl', $tipo);
    }

    public function scopePorNivelControl($query, $nivel)
    {
        return $query->where('NivelControl', $nivel);
    }

    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('ClienteId', $clienteId);
    }

    public function scopePorMedico($query, $medicoId)
    {
        return $query->where('MedicoId', $medicoId);
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('ProductoId', $productoId);
    }

    public function scopePorFarmaceutico($query, $farmaceuticoId)
    {
        return $query->where('FarmaceuticoId', $farmaceuticoId);
    }

    public function scopePorPeriodo($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('FechaDispensacion', [$fechaInicio, $fechaFin]);
    }

    public function scopeConSeguimiento($query)
    {
        return $query->where('SeguimientoRequerido', true)
                    ->whereNotNull('FechaSeguimiento');
    }

    public function scopePendientesSeguimiento($query)
    {
        return $query->where('SeguimientoRequerido', true)
                    ->where('FechaSeguimiento', '<=', now())
                    ->where('Estado', 'Dispensado');
    }

    public function scopePorEdad($query, $edadMin, $edadMax)
    {
        return $query->whereBetween('EdadPaciente', [$edadMin, $edadMax]);
    }

    public function scopeControlEspecial($query)
    {
        return $query->where('NivelControl', '>=', 3);
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('FechaDispensacion', now()->toDateString());
    }

    public function scopeEstaSemana($query)
    {
        return $query->whereBetween('FechaDispensacion', [
            now()->startOfWeek()->toDateString(),
            now()->endOfWeek()->toDateString()
        ]);
    }

    public function scopeEsteMes($query)
    {
        return $query->whereMonth('FechaDispensacion', now()->month)
                    ->whereYear('FechaDispensacion', now()->year);
    }     

    private static function getPrefijoPorTipo($tipoControl)
    {
        $prefijos = [
            'Estupefaciente' => 'EST',
            'Psicotropico' => 'PSI',
            'SustanciaControlada' => 'CON',
            'ControlDuplo' => 'CDB',
            'RecetaEspecial' => 'RES'
        ];
        
        return $prefijos[$tipoControl] ?? 'DIS';
    }

    public static function obtenerEstadisticas($fechaInicio, $fechaFin)
    {
        $dispensaciones = self::dispensados()
                             ->porPeriodo($fechaInicio, $fechaFin);
        
        $estadisticas = [
            'total_dispensaciones' => $dispensaciones->count(),
            'total_cantidad' => $dispensaciones->sum('CantidadDispensada'),
            'por_tipo_control' => [],
            'por_nivel_control' => [],
            'por_mes' => [],
            'productos_mas_dispensados' => [],
            'medicos_mas_activos' => [],
            'clientes_con_mayor_consumo' => [],
            'promedio_dispensacion_diaria' => 0
        ];

        // Agrupar por tipo de control
        foreach ($dispensaciones->get()->groupBy('TipoControl') as $tipo => $items) {
            $estadisticas['por_tipo_control'][$tipo] = [
                'cantidad' => $items->sum('CantidadDispensada'),
                'dispensaciones' => $items->count(),
                'porcentaje' => ($items->count() / $estadisticas['total_dispensaciones']) * 100
            ];
        }

        // Agrupar por nivel de control
        foreach ($dispensaciones->get()->groupBy('NivelControl') as $nivel => $items) {
            $estadisticas['por_nivel_control'][$nivel] = [
                'cantidad' => $items->sum('CantidadDispensada'),
                'dispensaciones' => $items->count()
            ];
        }

        // Agrupar por mes
        foreach ($dispensaciones->get()->groupBy(function($dispensacion) {
            return $dispensacion->FechaDispensacion->format('Y-m');
        }) as $mes => $items) {
            $estadisticas['por_mes'][$mes] = [
                'cantidad' => $items->sum('CantidadDispensada'),
                'dispensaciones' => $items->count()
            ];
        }

        // Productos más dispensados
        $productos = $dispensaciones->selectRaw('ProductoId, SUM(CantidadDispensada) as total_cantidad, COUNT(*) as dispensaciones')
                                   ->groupBy('ProductoId')
                                   ->orderBy('total_cantidad', 'desc')
                                   ->take(10)
                                   ->get();

        foreach ($productos as $producto) {
            $estadisticas['productos_mas_dispensados'][] = [
                'producto_id' => $producto->ProductoId,
                'producto' => $producto->producto->Descripcion ?? 'N/A',
                'cantidad' => $producto->total_cantidad,
                'dispensaciones' => $producto->dispensaciones
            ];
        }

        // Médicos más activos
        $medicos = $dispensaciones->selectRaw('MedicoId, COUNT(*) as dispensaciones')
                                 ->groupBy('MedicoId')
                                 ->orderBy('dispensaciones', 'desc')
                                 ->take(10)
                                 ->get();

        foreach ($medicos as $medico) {
            $estadisticas['medicos_mas_activos'][] = [
                'medico_id' => $medico->MedicoId,
                'medico' => $medico->medico->Nombre ?? 'N/A',
                'dispensaciones' => $medico->dispensaciones
            ];
        }

        // Promedio diario
        $dias = $fechaInicio->diffInDays($fechaFin) + 1;
        $estadisticas['promedio_dispensacion_diaria'] = $estadisticas['total_dispensaciones'] / $dias;

        return $estadisticas;
    }

    public static function obtenerAlertasControlados()
    {
        $alertas = [];
        
        // Pacientes con dispensaciones frecuentes
        $pacientesFrecuentes = self::dispensados()
                                  ->where('FechaDispensacion', '>=', now()->subDays(30))
                                  ->selectRaw('ClienteId, COUNT(*) as frecuencia')
                                  ->groupBy('ClienteId')
                                  ->having('frecuencia', '>=', 5)
                                  ->get();
        
        if ($pacientesFrecuentes->count() > 0) {
            $alertas[] = [
                'tipo' => 'pacientes_frecuentes',
                'titulo' => 'Pacientes con Dispensaciones Frecuentes',
                'cantidad' => $pacientesFrecuentes->count(),
                'pacientes' => $pacientesFrecuentes->map(function($frecuencia) {
                    return [
                        'cliente_id' => $frecuencia->ClienteId,
                        'cliente' => $frecuencia->cliente->RazonSocial ?? 'N/A',
                        'frecuencia' => $frecuencia->frecuencia
                    ];
                })
            ];
        }
        
        // Seguimientos pendientes
        $seguimientosPendientes = self::pendientesSeguimiento()->get();
        
        if ($seguimientosPendientes->count() > 0) {
            $alertas[] = [
                'tipo' => 'seguimientos_pendientes',
                'titulo' => 'Seguimientos Pendientes',
                'cantidad' => $seguimientosPendientes->count(),
                'dispensaciones' => $seguimientosPendientes->take(10)->map(function($dispensacion) {
                    return [
                        'cliente' => $dispensacion->cliente->RazonSocial ?? 'N/A',
                        'producto' => $dispensacion->producto->Descripcion ?? 'N/A',
                        'fecha_seguimiento' => $dispensacion->FechaSeguimiento->format('Y-m-d')
                    ];
                })
            ];
        }
        
        // Medicamentos con dispensaciones altas
        $medicamentosAltaDispensacion = self::dispensados()
                                           ->where('FechaDispensacion', '>=', now()->subDays(7))
                                           ->selectRaw('ProductoId, COUNT(*) as dispensaciones')
                                           ->groupBy('ProductoId')
                                           ->having('dispensaciones', '>=', 10)
                                           ->get();
        
        if ($medicamentosAltaDispensacion->count() > 0) {
            $alertas[] = [
                'tipo' => 'medicamentos_alta_dispensacion',
                'titulo' => 'Medicamentos con Alta Dispensación',
                'cantidad' => $medicamentosAltaDispensacion->count(),
                'medicamentos' => $medicamentosAltaDispensacion->map(function($medicamento) {
                    return [
                        'producto_id' => $medicamento->ProductoId,
                        'producto' => $medicamento->producto->Descripcion ?? 'N/A',
                        'dispensaciones' => $medicamento->dispensaciones
                    ];
                })
            ];
        }
        
        return $alertas;
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dispensacion) {
            // Establecer fecha por defecto
            if (!$dispensacion->FechaDispensacion) {
                $dispensacion->FechaDispensacion = now()->toDateString();
            }
            
            // Establecer hora por defecto
            if (!$dispensacion->HoraDispensacion) {
                $dispensacion->HoraDispensacion = now()->format('H:i:s');
            }
            
            // Generar número de dispensación
            if (!$dispensacion->NumeroDispensacion) {
                $dispensacion->NumeroDispensacion = self::generarNumeroDispensacion($dispensacion->TipoControl, $dispensacion->FechaDispensacion);
            }
            
            // Determinar nivel de control por defecto
            if (!$dispensacion->NivelControl) {
                $dispensacion->NivelControl = $dispensacion->determinarNivelControl();
            }
            
            // Estado por defecto
            if (!$dispensacion->Estado) {
                $dispensacion->Estado = 'Pendiente';
            }
        });

        static::saving(function ($dispensacion) {
            // Validar cantidad
            if ($dispensacion->CantidadDispensada <= 0) {
                throw new \Exception('La cantidad dispensada debe ser mayor a cero');
            }
            
            // Validar que existe el producto
            $producto = Producto::find($dispensacion->ProductoId);
            if (!$producto || !$producto->EsControlado) {
                throw new \Exception('El producto especificado no es un medicamento controlado');
            }
            
            // Validar que existe el médico
            if ($dispensacion->MedicoId) {
                $medico = MedicoAutorizado::find($dispensacion->MedicoId);
                if (!$medico || !$medico->habilitado) {
                    throw new \Exception('El médico especificado no está autorizado o está deshabilitado');
                }
            }
            
            // Validar período de espera
            if ($dispensacion->PeriodoEspera && $dispensacion->PeriodoEspera > 0) {
                $ultimaDispensacion = static::where('ClienteId', $dispensacion->ClienteId)
                                          ->where('ProductoId', $dispensacion->ProductoId)
                                          ->where('Id', '!=', $dispensacion->Id)
                                          ->orderBy('FechaDispensacion', 'desc')
                                          ->first();
                
                if ($ultimaDispensacion) {
                    $fechaUltima = Carbon::parse($ultimaDispensacion->FechaDispensacion);
                    $diasTranscurridos = $fechaUltima->diffInDays(now());
                    
                    if ($diasTranscurridos < $dispensacion->PeriodoEspera) {
                        throw new \Exception("Debe esperar {$dispensacion->PeriodoEspera} días entre dispensaciones. Han transcurrido {$diasTranscurridos} días.");
                    }
                }
            }
        });

        static::saved(function ($dispensacion) {
            // Generar notificaciones según el estado
            
            if ($dispensacion->isDispensado()) {
                // Notificar dispensación de control especial
                if ($dispensacion->isControlEspecial()) {
                    Notificacion::crear([
                        'tipo' => 'dispensacion_control_especial',
                        'titulo' => 'Dispensación de Control Especial',
                        'mensaje' => "Dispensación de {$dispensacion->producto->Descripcion} - Control Especial Nivel {$dispensacion->NivelControl}",
                        'referencia_id' => $dispensacion->Id,
                        'referencia_tabla' => 'MedicamentosControlados',
                        'prioridad' => 'alta'
                    ]);
                }
                
                // Programar seguimiento automático
                $fechaSeguimiento = $dispensacion->programarSeguimiento();
                
                if ($fechaSeguimiento) {
                    Notificacion::crear([
                        'tipo' => 'seguimiento_programado',
                        'titulo' => 'Seguimiento Programado',
                        'mensaje' => "Seguimiento programado para {$fechaSeguimiento->format('Y-m-d')} - Cliente: {$dispensacion->cliente->RazonSocial}",
                        'referencia_id' => $dispensacion->Id,
                        'referencia_tabla' => 'MedicamentosControlados',
                        'fecha_programada' => $fechaSeguimiento,
                        'prioridad' => 'media'
                    ]);
                }
            }
            
            // Alertas por límites
            if ($dispensacion->isAprobado()) {
                $validacion = $dispensacion->validarLimiteDispensacion($dispensacion->CantidadDispensada);
                if (!$validacion['valido']) {
                    Notificacion::crear([
                        'tipo' => 'limite_dispensacion_excedido',
                        'titulo' => 'Límite de Dispensación Excedido',
                        'mensaje' => "Límite excedido para cliente {$dispensacion->cliente->RazonSocial}: {$validacion['error']}",
                        'referencia_id' => $dispensacion->Id,
                        'referencia_tabla' => 'MedicamentosControlados',
                        'prioridad' => 'critica'
                    ]);
                }
            }
        });
    }

    private function determinarNivelControl()
    {
        switch ($this->TipoControl) {
            case 'Estupefaciente':
                return 4; // Máximo control
            case 'Psicotropico':
                return 3; // Control alto
            case 'SustanciaControlada':
                return 2; // Control intermedio
            default:
                return 1; // Control básico
        }
    }
}