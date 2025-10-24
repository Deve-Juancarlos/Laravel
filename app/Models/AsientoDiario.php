<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AsientoDiario extends Model
{
    use HasFactory;

    protected $table = 'LibroDiario';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Numero',
        'Fecha',
        'Tipo',
        'Periodo',
        'Glosa',
        'Debe',
        'Haber',
        'Cuenta',
        'CentroCosto',
        'Proyecto',
        'Referencia',
        'Usuario',
        'Estado',
        'Aprobado',
        'FechaAprobacion',
        'Observaciones'
    ];

    protected $casts = [
        'Fecha' => 'date',
        'Debe' => 'decimal:2',
        'Haber' => 'decimal:2',
        'FechaAprobacion' => 'datetime'
    ];

    protected $dates = ['Fecha', 'FechaAprobacion'];

    // Relacionamentos
    public function cuenta()
    {
        return $this->belongsTo(CuentaMayor::class, 'Cuenta', 'Codigo');
    }

    public function centroCosto()
    {
        return $this->belongsTo(CentroCosto::class, 'CentroCosto', 'Codigo');
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'Proyecto', 'Codigo');
    }

    public function usuario()
    {
        return $this->belongsTo(AccesoWeb::class, 'Usuario', 'Id');
    }

    public function referenciaDocumento()
    {
        if ($this->Tipo === 'Factura') {
            return $this->belongsTo(Factura::class, 'Referencia', 'Id');
        }
        return null;
    }

    // Métodos de negocio
    public function getMontoDebe()
    {
        return floatval($this->Debe);
    }

    public function getMontoHaber()
    {
        return floatval($this->Haber);
    }

    public function getSaldo()
    {
        return $this->getMontoDebe() - $this->getMontoHaber();
    }

    public function getTipoAsiento()
    {
        $tipos = [
            'DI' => 'Diario',
            'IN' => 'Ingresos',
            'EG' => 'Egresos',
            'AP' => 'Apertura',
            'CI' => 'Cierre',
            'AJ' => 'Ajuste',
            'JE' => 'Reversion'
        ];
        
        return $tipos[$this->Tipo] ?? $this->Tipo;
    }

    public function getEstadoColor()
    {
        $colores = [
            'Borrador' => 'warning',
            'Pendiente' => 'info',
            'Aprobado' => 'success',
            'Anulado' => 'danger'
        ];
        
        return $colores[$this->Estado] ?? 'secondary';
    }

    public function isBalanceado()
    {
        return abs($this->Debe - $this->Haber) < 0.01;
    }

    public function isAprobado()
    {
        return $this->Estado === 'Aprobado' && $this->Aprobado;
    }

    public function isPendiente()
    {
        return $this->Estado === 'Pendiente';
    }

    public function isAnulado()
    {
        return $this->Estado === 'Anulado';
    }

    public function aprobar($usuarioAprobacion)
    {
        if ($this->isPendiente()) {
            $this->Estado = 'Aprobado';
            $this->Aprobado = true;
            $this->FechaAprobacion = now();
            $this->save();

            // Registrar en trazabilidad
            Trazabilidad::registrarMovimiento([
                'tipo_movimiento' => 'Aprobación Asiento',
                'referencia_id' => $this->Id,
                'referencia_tabla' => 'LibroDiario',
                'comentarios' => "Asiento {$this->Numero} aprobado por usuario {$usuarioAprobacion}",
                'usuario_id' => auth()->id() ?? 1,
                'fecha' => now()
            ]);

            return true;
        }
        
        return false;
    }

    public function anular($motivo)
    {
        if ($this->isAprobado()) {
            $this->Estado = 'Anulado';
            $this->Aprobado = false;
            $this->Observaciones = ($this->Observaciones ?? '') . "\nAnulado: " . $motivo;
            $this->save();

            // Registrar en trazabilidad
            Trazabilidad::registrarMovimiento([
                'tipo_movimiento' => 'Anulación Asiento',
                'referencia_id' => $this->Id,
                'referencia_tabla' => 'LibroDiario',
                'comentarios' => "Asiento {$this->Numero} anulado. Motivo: {$motivo}",
                'usuario_id' => auth()->id() ?? 1,
                'fecha' => now()
            ]);

            return true;
        }
        
        return false;
    }

    public function copiar($cambios = [])
    {
        $nuevoAsiento = $this->replicate();
        $nuevoAsiento->fill($cambios);
        
        // Generar nuevo número
        $nuevoAsiento->Numero = self::generarNumeroAsiento($this->Fecha);
        $nuevoAsiento->Estado = 'Borrador';
        $nuevoAsiento->Aprobado = false;
        $nuevoAsiento->FechaAprobacion = null;
        
        return $nuevoAsiento;
    }

    public function calcularTotales()
    {
        $asientos = self::where('Numero', $this->Numero)->get();
        
        $totales = [
            'total_debe' => $asientos->sum('Debe'),
            'total_haber' => $asientos->sum('Haber'),
            'diferencia' => 0,
            'balanceado' => false
        ];
        
        $totales['diferencia'] = $totales['total_debe'] - $totales['total_haber'];
        $totales['balanceado'] = abs($totales['diferencia']) < 0.01;
        
        return $totales;
    }

    // Scopes
    public function scopeAprobados($query)
    {
        return $query->where('Estado', 'Aprobado')
                    ->where('Aprobado', true);
    }

    public function scopePendientes($query)
    {
        return $query->where('Estado', 'Pendiente');
    }

    public function scopeBorradores($query)
    {
        return $query->where('Estado', 'Borrador');
    }

    public function scopeAnulados($query)
    {
        return $query->where('Estado', 'Anulado');
    }

    public function scopeBalanceados($query)
    {
        return $query->whereRaw('ABS(Debe - Haber) < 0.01');
    }

    public function scopeNoBalanceados($query)
    {
        return $query->whereRaw('ABS(Debe - Haber) >= 0.01');
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('Tipo', $tipo);
    }

    public function scopePorPeriodo($query, $periodo)
    {
        return $query->where('Periodo', $periodo);
    }

    public function scopePorCuenta($query, $cuenta)
    {
        return $query->where('Cuenta', $cuenta);
    }

    public function scopePorCentroCosto($query, $centroCosto)
    {
        return $query->where('CentroCosto', $centroCosto);
    }

    public function scopePorProyecto($query, $proyecto)
    {
        return $query->where('Proyecto', $proyecto);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('Fecha', [$fechaInicio, $fechaFin]);
        }
        return $query->where('Fecha', $fechaInicio);
    }

    public function scopePorMes($query, $año, $mes)
    {
        $fechaInicio = Carbon::create($año, $mes, 1);
        $fechaFin = $fechaInicio->copy()->endOfMonth();
        
        return $query->whereBetween('Fecha', [$fechaInicio, $fechaFin]);
    }

    public function scopePorAño($query, $año)
    {
        $fechaInicio = Carbon::create($año, 1, 1);
        $fechaFin = Carbon::create($año, 12, 31);
        
        return $query->whereBetween('Fecha', [$fechaInicio, $fechaFin]);
    }

    public function scopeConReferencia($query)
    {
        return $query->whereNotNull('Referencia');
    }

    public function scopeSinReferencia($query)
    {
        return $query->whereNull('Referencia');
    }

    // Métodos estáticos
    public static function generarNumeroAsiento($fecha = null, $tipo = 'DI')
    {
        $fecha = $fecha ? Carbon::parse($fecha) : now();
        $periodo = $fecha->format('Y-m');
        $prefijo = $tipo;
        
        $ultimoNumero = self::where('Tipo', $tipo)
                           ->where('Periodo', $periodo)
                           ->max('Numero');
        
        if ($ultimoNumero) {
            $numero = intval(substr($ultimoNumero, -6)) + 1;
        } else {
            $numero = 1;
        }
        
        return $prefijo . '-' . $periodo . '-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public static function validarBalance($numeroAsiento)
    {
        $asientos = self::where('Numero', $numeroAsiento)->get();
        
        if ($asientos->isEmpty()) {
            return false;
        }
        
        $totalDebe = $asientos->sum('Debe');
        $totalHaber = $asientos->sum('Haber');
        
        return abs($totalDebe - $totalHaber) < 0.01;
    }

    public static function obtenerSaldosPorCuenta($fechaFin, $cuenta = null, $centroCosto = null)
    {
        $query = self::aprobados()
                    ->where('Fecha', '<=', $fechaFin);
        
        if ($cuenta) {
            $query->where('Cuenta', $cuenta);
        }
        
        if ($centroCosto) {
            $query->where('CentroCosto', $centroCosto);
        }
        
        $saldos = $query->selectRaw('Cuenta, SUM(Debe) as total_debe, SUM(Haber) as total_haber')
                       ->groupBy('Cuenta')
                       ->get();
        
        foreach ($saldos as $saldo) {
            $saldo->saldo = $saldo->total_debe - $saldo->total_haber;
        }
        
        return $saldos;
    }

    public static function obtenerMovimientosPorPeriodo($fechaInicio, $fechaFin, $cuenta = null)
    {
        $query = self::aprobados()
                    ->porFecha($fechaInicio, $fechaFin);
        
        if ($cuenta) {
            $query->where('Cuenta', $cuenta);
        }
        
        return $query->with(['cuenta', 'centroCosto', 'proyecto'])
                    ->orderBy('Fecha', 'desc')
                    ->orderBy('Numero', 'desc')
                    ->get();
    }

    public static function generarLibroMayor($cuenta, $fechaInicio, $fechaFin, $centroCosto = null)
    {
        // Obtener saldo inicial
        $saldoInicial = self::aprobados()
                           ->where('Cuenta', $cuenta)
                           ->where('Fecha', '<', $fechaInicio);
        
        if ($centroCosto) {
            $saldoInicial->where('CentroCosto', $centroCosto);
        }
        
        $saldoInicial = $saldoInicial->selectRaw('SUM(Debe - Haber) as saldo')
                                   ->first()
                                   ->saldo ?? 0;
        
        // Obtener movimientos del período
        $movimientos = self::aprobados()
                          ->where('Cuenta', $cuenta)
                          ->porFecha($fechaInicio, $fechaFin);
        
        if ($centroCosto) {
            $movimientos->where('CentroCosto', $centroCosto);
        }
        
        $movimientos = $movimientos->orderBy('Fecha')
                                  ->orderBy('Numero')
                                  ->get();
        
        // Calcular saldos
        $saldoCorriente = $saldoInicial;
        foreach ($movimientos as $movimiento) {
            $movimiento->saldo_anterior = $saldoCorriente;
            $movimiento->saldo_corriente = $saldoCorriente + $movimiento->Debe - $movimiento->Haber;
            $saldoCorriente = $movimiento->saldo_corriente;
        }
        
        return [
            'saldo_inicial' => $saldoInicial,
            'movimientos' => $movimientos,
            'saldo_final' => $saldoCorriente
        ];
    }

    public static function validarCierresMensuales($año, $mes)
    {
        $fechaCierre = Carbon::create($año, $mes, 1)->endOfMonth();
        $asientosCierre = self::aprobados()
                              ->where('Tipo', 'CI')
                              ->porMes($año, $mes)
                              ->count();
        
        if ($asientosCierre > 0) {
            return true;
        }
        
        // Verificar si todos los asientos del mes están aprobados
        $asientosPendientes = self::where('Periodo', sprintf('%02d-%02d', $año, $mes))
                                 ->where('Estado', 'Pendiente')
                                 ->count();
        
        return $asientosPendientes === 0;
    }

    public static function generarReportesDiarios($fechaInicio, $fechaFin)
    {
        $asientos = self::aprobados()
                        ->porFecha($fechaInicio, $fechaFin)
                        ->with(['cuenta'])
                        ->orderBy('Fecha')
                        ->orderBy('Numero')
                        ->get();
        
        $reportes = [
            'total_asientos' => $asientos->unique('Numero')->count(),
            'total_debe' => $asientos->sum('Debe'),
            'total_haber' => $asientos->sum('Haber'),
            'por_tipo' => [],
            'por_estado' => [],
            'detalle' => $asientos
        ];
        
        // Agrupar por tipo
        foreach ($asientos->groupBy('Tipo') as $tipo => $items) {
            $reportes['por_tipo'][$tipo] = [
                'cantidad' => $items->unique('Numero')->count(),
                'debe' => $items->sum('Debe'),
                'haber' => $items->sum('Haber')
            ];
        }
        
        // Agrupar por estado
        foreach ($asientos->groupBy('Estado') as $estado => $items) {
            $reportes['por_estado'][$estado] = [
                'cantidad' => $items->count(),
                'debe' => $items->sum('Debe'),
                'haber' => $items->sum('Haber')
            ];
        }
        
        return $reportes;
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asiento) {
            // Generar número automático si no existe
            if (!$asiento->Numero) {
                $asiento->Numero = self::generarNumeroAsiento($asiento->Fecha, $asiento->Tipo);
            }
            
            // Establecer periodo
            if (!$asiento->Periodo && $asiento->Fecha) {
                $asiento->Periodo = $asiento->Fecha->format('Y-m');
            }
            
            // Estado por defecto
            if (!$asiento->Estado) {
                $asiento->Estado = 'Borrador';
            }
            
            // Usuario por defecto
            if (!$asiento->Usuario) {
                $asiento->Usuario = auth()->id() ?? 1;
            }
        });

        static::saving(function ($asiento) {
            // Validar que el asiento esté balanceado antes de guardar
            if ($asiento->isDirty(['Debe', 'Haber']) && !$asiento->isBalanceado()) {
                throw new \Exception('El asiento debe estar balanceado (Debe = Haber)');
            }
        });

        static::saved(function ($asiento) {
            // Verificar si el número completo del asiento está balanceado
            if (!self::validarBalance($asiento->Numero)) {
                Notificacion::crear([
                    'tipo' => 'asiento_no_balanceado',
                    'titulo' => 'Asiento No Balanceado',
                    'mensaje' => "El asiento {$asiento->Numero} no está balanceado",
                    'referencia_id' => $asiento->Id,
                    'referencia_tabla' => 'LibroDiario',
                    'prioridad' => 'alta'
                ]);
            }
        });

        static::updating(function ($asiento) {
            // No permitir modificar asientos ya aprobados
            if ($asiento->isDirty('Estado') && $asiento->isAprobed) {
                throw new \Exception('No se puede modificar un asiento ya aprobado');
            }
        });
    }
}