<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CuentaMayor extends Model
{
    use HasFactory;

    protected $table = 'LibroMayor';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Codigo',
        'Nombre',
        'Descripcion',
        'Nivel',
        'CuentaPadre',
        'Tipo',
        'Saldo',
        'SaldoDebe',
        'SaldoHaber',
        'CentroCosto',
        'Activo',
        'Flujo',
        'Usuario',
        'FechaActualizacion'
    ];

    protected $casts = [
        'Saldo' => 'decimal:2',
        'SaldoDebe' => 'decimal:2',
        'SaldoHaber' => 'decimal:2',
        'FechaActualizacion' => 'datetime'
    ];

    protected $dates = ['FechaActualizacion'];

    // Relacionamentos
    public function cuentaPadre()
    {
        return $this->belongsTo(CuentaMayor::class, 'CuentaPadre', 'Codigo');
    }

    public function subCuentas()
    {
        return $this->hasMany(CuentaMayor::class, 'CuentaPadre', 'Codigo');
    }

    public function centroCosto()
    {
        return $this->belongsTo(CentroCosto::class, 'CentroCosto', 'Codigo');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Usuario', 'Id');
    }

    public function asientos()
    {
        return $this->hasMany(AsientoDiario::class, 'Cuenta', 'Codigo');
    }

    // Métodos de negocio
    public function getNombreCompleto()
    {
        $nombres = [];
        $cuenta = $this;
        
        while ($cuenta) {
            array_unshift($nombres, $cuenta->Nombre);
            $cuenta = $cuenta->cuentaPadre;
            if (!$cuenta) break;
        }
        
        return implode(' - ', $nombres);
    }

    public function getNivelCuenta()
    {
        return $this->Nivel;
    }

    public function getTipoCuenta()
    {
        $tipos = [
            'A' => 'Activo',
            'P' => 'Pasivo',
            'C' => 'Capital',
            'I' => 'Ingresos',
            'G' => 'Gastos',
            'C' => 'Costos',
            'D' => 'Devengado'
        ];
        
        return $tipos[$this->Tipo] ?? $this->Tipo;
    }

    public function getNaturalezaCuenta()
    {
        // Naturaleza según el tipo de cuenta
        switch ($this->Tipo) {
            case 'A': // Activo
            case 'G': // Gastos
            case 'C': // Costos
                return 'Deudora';
            case 'P': // Pasivo
            case 'C': // Capital
            case 'I': // Ingresos
                return 'Acreedora';
            default:
                return 'Mixta';
        }
    }

    public function getSaldoCorriente()
    {
        return floatval($this->Saldo);
    }

    public function getSaldoDebe()
    {
        return floatval($this->SaldoDebe);
    }

    public function getSaldoHaber()
    {
        return floatval($this->SaldoHaber);
    }

    public function getSaldoAjustado()
    {
        // Ajustar el saldo según la naturaleza de la cuenta
        $naturaleza = $this->getNaturalezaCuenta();
        
        switch ($naturaleza) {
            case 'Deudora':
                return $this->getSaldoDebe() - $this->getSaldoHaber();
            case 'Acreedora':
                return $this->getSaldoHaber() - $this->getSaldoDebe();
            default:
                return $this->getSaldoDebe() - $this->getSaldoHaber();
        }
    }

    public function getDescripcionDetallada()
    {
        $descripcion = [
            "Código: {$this->Codigo}",
            "Nombre: {$this->Nombre}",
            "Tipo: {$this->getTipoCuenta()}",
            "Naturaleza: {$this->getNaturalezaCuenta()}",
            "Nivel: {$this->Nivel}"
        ];
        
        if ($this->Descripcion) {
            $descripcion[] = "Descripción: {$this->Descripcion}";
        }
        
        return implode("\n", $descripcion);
    }

    public function isActiva()
    {
        return $this->Activo;
    }

    public function isSubCuenta()
    {
        return !empty($this->CuentaPadre);
    }

    public function isCuentaPadre()
    {
        return $this->subCuentas()->exists();
    }

    public function isContable()
    {
        // Cuenta contable si no es de cuentas de control o auxiliares
        return !in_array($this->Nivel, ['M', 'X']);
    }

    public function hasCentroCosto()
    {
        return !empty($this->CentroCosto);
    }

    public function actualizarSaldo($fechaCorte = null)
    {
        $fechaCorte = $fechaCorte ?? now();
        
        // Calcular saldos desde asientos
        $asientos = $this->asientos()
                        ->aprobados()
                        ->where('Fecha', '<=', $fechaCorte)
                        ->get();
        
        $saldoDebe = $asientos->sum('Debe');
        $saldoHaber = $asientos->sum('Haber');
        
        // Actualizar saldos
        $this->SaldoDebe = $saldoDebe;
        $this->SaldoHaber = $saldoHaber;
        $this->Saldo = $this->getSaldoAjustado();
        $this->FechaActualizacion = now();
        $this->save();
        
        // Actualizar saldos de cuenta padre si existe
        if ($this->cuentaPadre) {
            $this->cuentaPadre->actualizarSaldo($fechaCorte);
        }
        
        return $this;
    }

    public function obtenerMovimientos($fechaInicio, $fechaFin, $centroCosto = null)
    {
        $query = $this->asientos()
                     ->aprobados()
                     ->porFecha($fechaInicio, $fechaFin);
        
        if ($centroCosto) {
            $query->where('CentroCosto', $centroCosto);
        }
        
        return $query->with(['centroCosto', 'proyecto'])
                    ->orderBy('Fecha')
                    ->orderBy('Numero')
                    ->get();
    }

    public function obtenerSaldoPorFecha($fecha)
    {
        $asientos = $this->asientos()
                        ->aprobados()
                        ->where('Fecha', '<=', $fecha)
                        ->get();
        
        $saldoDebe = $asientos->sum('Debe');
        $saldoHaber = $asientos->sum('Haber');
        
        return [
            'saldo_debe' => $saldoDebe,
            'saldo_haber' => $saldoHaber,
            'saldo_ajustado' => $saldoDebe - $saldoHaber,
            'naturaleza' => $this->getNaturalezaCuenta(),
            'movimientos' => $asientos->count()
        ];
    }

    public function obtenerDetalleMovimientos($fechaInicio, $fechaFin)
    {
        $movimientos = $this->obtenerMovimientos($fechaInicio, $fechaFin);
        
        $detalle = [
            'saldo_inicial' => $this->obtenerSaldoPorFecha($fechaInicio->copy()->subDay()),
            'movimientos_periodo' => [],
            'saldo_final' => $this->obtenerSaldoPorFecha($fechaFin),
            'total_debe' => 0,
            'total_haber' => 0
        ];
        
        foreach ($movimientos as $movimiento) {
            $detalle['total_debe'] += $movimiento->Debe;
            $detalle['total_haber'] += $movimiento->Haber;
            
            $detalle['movimientos_periodo'][] = [
                'fecha' => $movimiento->Fecha,
                'numero' => $movimiento->Numero,
                'glosa' => $movimiento->Glosa,
                'centro_costo' => $movimiento->centroCosto->Nombre ?? 'N/A',
                'debe' => $movimiento->Debe,
                'haber' => $movimiento->Haber,
                'saldo_corriente' => 0 // Se calculará después
            ];
        }
        
        // Calcular saldos corrientes
        $saldoCorriente = $detalle['saldo_inicial']['saldo_ajustado'];
        foreach ($detalle['movimientos_periodo'] as &$movimiento) {
            $saldoCorriente += $movimiento['debe'] - $movimiento['haber'];
            $movimiento['saldo_corriente'] = $saldoCorriente;
        }
        
        return $detalle;
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('Activo', true);
    }

    public function scopeInactivas($query)
    {
        return $query->where('Activo', false);
    }

    public function scopeContables($query)
    {
        return $query->whereNotIn('Nivel', ['M', 'X']);
    }

    public function scopePorNivel($query, $nivel)
    {
        return $query->where('Nivel', $nivel);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('Tipo', $tipo);
    }

    public function scopePorNaturaleza($query, $naturaleza)
    {
        if ($naturaleza === 'Deudora') {
            return $query->whereIn('Tipo', ['A', 'G', 'C']);
        } elseif ($naturaleza === 'Acreedora') {
            return $query->whereIn('Tipo', ['P', 'C', 'I']);
        }
        
        return $query;
    }

    public function scopeConSaldo($query)
    {
        return $query->where('Saldo', '!=', 0);
    }

    public function scopeSinSaldo($query)
    {
        return $query->where('Saldo', '=', 0);
    }

    public function scopePorCodigo($query, $codigo)
    {
        return $query->where('Codigo', 'like', $codigo . '%');
    }

    public function scopeConSubCuentas($query)
    {
        return $query->has('subCuentas');
    }

    public function scopeSinSubCuentas($query)
    {
        return $query->doesntHave('subCuentas');
    }

    // Métodos estáticos
    public static function obtenerPlanCuentas($activo = true)
    {
        $query = self::orderBy('Codigo');
        
        if ($activo) {
            $query->activas();
        }
        
        return $query->with('subCuentas')
                    ->get()
                    ->nest()
                    ->sortKeys();
    }

    public static function obtenerArbolCuentas()
    {
        $cuentas = self::activas()
                      ->with('subCuentas')
                      ->orderBy('Codigo')
                      ->get();
        
        return $cuentas->toTree();
    }

    public static function obtenerSaldoBalanceGeneral($fecha)
    {
        $cuentas = self::activas()
                      ->contables()
                      ->get();
        
        $balance = [
            'activos' => [
                'corrientes' => 0,
                'no_corrientes' => 0,
                'total' => 0
            ],
            'pasivos' => [
                'corrientes' => 0,
                'no_corrientes' => 0,
                'total' => 0
            ],
            'patrimonio' => [
                'total' => 0
            ]
        ];
        
        foreach ($cuentas as $cuenta) {
            $saldo = $cuenta->obtenerSaldoPorFecha($fecha);
            
            switch ($cuenta->Tipo) {
                case 'A': // Activos
                    if (in_array(substr($cuenta->Codigo, 0, 2), ['11', '12'])) {
                        $balance['activos']['corrientes'] += abs($saldo['saldo_ajustado']);
                    } else {
                        $balance['activos']['no_corrientes'] += abs($saldo['saldo_ajustado']);
                    }
                    $balance['activos']['total'] += abs($saldo['saldo_ajustado']);
                    break;
                    
                case 'P': // Pasivos
                    if (in_array(substr($cuenta->Codigo, 0, 2), ['41', '42'])) {
                        $balance['pasivos']['corrientes'] += abs($saldo['saldo_ajustado']);
                    } else {
                        $balance['pasivos']['no_corrientes'] += abs($saldo['saldo_ajustado']);
                    }
                    $balance['pasivos']['total'] += abs($saldo['saldo_ajustado']);
                    break;
                    
                case 'C': // Capital
                    $balance['patrimonio']['total'] += abs($saldo['saldo_ajustado']);
                    break;
            }
        }
        
        return $balance;
    }

    public static function validarCierreContable($año, $mes)
    {
        $fechaCierre = Carbon::create($año, $mes, 1)->endOfMonth();
        
        // Verificar que todos los asientos del mes estén cerrados
        $asientosPendientes = AsientoDiario::where('Periodo', sprintf('%02d-%02d', $año, $mes))
                                          ->where('Estado', 'Pendiente')
                                          ->count();
        
        if ($asientosPendientes > 0) {
            return [
                'validado' => false,
                'error' => "Existen {$asientosPendientes} asientos pendientes de aprobación"
            ];
        }
        
        // Verificar balance de cada cuenta
        $cuentasDesbalanceadas = self::whereHas('asientos', function($q) use ($año, $mes) {
            $q->porMes($año, $mes);
        })->get()->filter(function($cuenta) use ($fechaCierre) {
            $saldo = $cuenta->obtenerSaldoPorFecha($fechaCorte);
            return abs($saldo['saldo_debe'] - $saldo['saldo_haber']) > 0.01;
        });
        
        if ($cuentasDesbalanceadas->count() > 0) {
            return [
                'validado' => false,
                'error' => "Existen " . $cuentasDesbalanceadas->count() . " cuentas desbalanceadas"
            ];
        }
        
        return ['validado' => true];
    }

    public static function generarReporteBalanceComprobacion($fechaInicio, $fechaFin)
    {
        $cuentas = self::activas()
                      ->contables()
                      ->get();
        
        $reporte = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'cuentas' => [],
            'totales' => [
                'debe_inicial' => 0,
                'haber_inicial' => 0,
                'debe_movimientos' => 0,
                'haber_movimientos' => 0,
                'debe_final' => 0,
                'haber_final' => 0
            ]
        ];
        
        foreach ($cuentas as $cuenta) {
            $saldoInicial = $cuenta->obtenerSaldoPorFecha($fechaInicio->copy()->subDay());
            $movimientos = $cuenta->obtenerMovimientos($fechaInicio, $fechaFin);
            $saldoFinal = $cuenta->obtenerSaldoPorFecha($fechaFin);
            
            $totalDebeMov = $movimientos->sum('Debe');
            $totalHaberMov = $movimientos->sum('Haber');
            
            $reporte['cuentas'][] = [
                'codigo' => $cuenta->Codigo,
                'nombre' => $cuenta->Nombre,
                'saldo_inicial_debe' => $saldoInicial['saldo_debe'],
                'saldo_inicial_haber' => $saldoInicial['saldo_haber'],
                'movimientos_debe' => $totalDebeMov,
                'movimientos_haber' => $totalHaberMov,
                'saldo_final_debe' => $saldoFinal['saldo_debe'],
                'saldo_final_haber' => $saldoFinal['saldo_haber']
            ];
            
            $reporte['totales']['debe_inicial'] += $saldoInicial['saldo_debe'];
            $reporte['totales']['haber_inicial'] += $saldoInicial['saldo_haber'];
            $reporte['totales']['debe_movimientos'] += $totalDebeMov;
            $reporte['totales']['haber_movimientos'] += $totalHaberMov;
            $reporte['totales']['debe_final'] += $saldoFinal['saldo_debe'];
            $reporte['totales']['haber_final'] += $saldoFinal['saldo_haber'];
        }
        
        return $reporte;
    }

    public static function buscarCuentas($termino, $activo = true)
    {
        $query = self::where(function($q) use ($termino) {
            $q->where('Codigo', 'like', '%' . $termino . '%')
              ->orWhere('Nombre', 'like', '%' . $termino . '%');
        });
        
        if ($activo) {
            $query->activas();
        }
        
        return $query->orderBy('Codigo')
                    ->limit(20)
                    ->get();
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cuenta) {
            // Validar código único
            if (self::where('Codigo', $cuenta->Codigo)->exists()) {
                throw new \Exception("El código de cuenta {$cuenta->Codigo} ya existe");
            }
            
            // Establecer usuario actual
            if (!$cuenta->Usuario) {
                $cuenta->Usuario = auth()->id() ?? 1;
            }
            
            // Establecer fecha de actualización
            $cuenta->FechaActualizacion = now();
            
            // Validar nivel
            if ($cuenta->CuentaPadre) {
                $cuentaPadre = self::find($cuenta->CuentaPadre);
                if ($cuentaPadre) {
                    $cuenta->Nivel = $cuentaPadre->Nivel + 1;
                }
            }
        });

        static::saved(function ($cuenta) {
            // Actualizar saldos si hay cambios
            if ($cuenta->isDirty(['SaldoDebe', 'SaldoHaber'])) {
                $cuenta->actualizarSaldo();
            }
            
            // Notificar si la cuenta tiene saldo inusual
            if (abs($cuenta->Saldo) > 1000000) {
                Notificacion::crear([
                    'tipo' => 'saldo_alto',
                    'titulo' => 'Saldo Alto en Cuenta',
                    'mensaje' => "La cuenta {$cuenta->Codigo} - {$cuenta->Nombre} tiene un saldo alto de {$cuenta->Saldo}",
                    'referencia_id' => $cuenta->Id,
                    'referencia_tabla' => 'LibroMayor',
                    'prioridad' => 'media'
                ]);
            }
        });

        static::updating(function ($cuenta) {
            // No permitir modificar código
            if ($cuenta->isDirty('Codigo')) {
                throw new \Exception('No se puede modificar el código de cuenta');
            }
            
            // No permitir desactivar si tiene subcuentas activas
            if ($cuenta->isDirty('Activo') && !$cuenta->Activo) {
                if ($cuenta->subCuentas()->activas()->exists()) {
                    throw new \Exception('No se puede desactivar una cuenta que tiene subcuentas activas');
                }
            }
        });
    }
}