<?php

namespace App\Services\Contabilidad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Log;  
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon; 
use Illuminate\Support\Collection;

class CajaService
{
    protected $connection = 'sqlsrv'; 

   
    public function __construct()
    {
       
    }

   
    public function getIndexData(array $filters): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $tipoMovimiento = $filters['tipo_movimiento'] ?? null;
        $usuario = $filters['usuario'] ?? null;

        $saldoInicial = DB::connection($this->connection)->table('Caja')
            ->where('Fecha', '<', $fechaInicio)
            ->where('Eliminado', 0)
            ->selectRaw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END) as saldo')
            ->value('saldo') ?? 0;

        $query = DB::connection($this->connection)->table('Caja as c')
            ->leftJoin('plan_cuentas as pc', 'c.Razon', '=', 'pc.codigo')
            ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
            ->select('c.*', 'pc.nombre as razon_descripcion');

        if ($tipoMovimiento) {
            $query->where('c.Tipo', $tipoMovimiento);
        }
        if ($usuario) {
            $query->where('c.Razon', $usuario); 
        }

        $movimientos = $query->clone()->orderBy('c.Fecha', 'desc')->orderBy('c.Numero', 'desc')->paginate(50);

        $totalesQuery = DB::connection($this->connection)->table('Caja')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Eliminado', 0);
        
        if ($tipoMovimiento) $totalesQuery->where('Tipo', $tipoMovimiento);
        if ($usuario) $totalesQuery->where('Razon', $usuario);
        
        $totales = $totalesQuery->selectRaw('
                SUM(CASE WHEN Tipo = 1 THEN Monto ELSE 0 END) as ingresos,
                SUM(CASE WHEN Tipo = 2 THEN Monto ELSE 0 END) as egresos
            ')
            ->first();

        $totalesPeriodo = [
            'ingresos' => $totales->ingresos ?? 0,
            'egresos' => $totales->egresos ?? 0,
            'neto' => ($totales->ingresos ?? 0) - ($totales->egresos ?? 0)
        ];

        $saldoFinal = $saldoInicial + $totalesPeriodo['neto'];

        $listaUsuarios = DB::connection($this->connection)->table('plan_cuentas')
            ->where('activo', 1)
            ->where(function($q) {
                $q->where('codigo', 'LIKE', '6%')
                  ->orWhere('codigo', 'LIKE', '7%')
                  ->orWhere('codigo', 'LIKE', '12%');
            })
            ->select('codigo as id', 'nombre')
            ->orderBy('codigo')
            ->get();

        return [
            'movimientos' => $movimientos,
            'saldoInicial' => $saldoInicial,
            'totalesPeriodo' => (object) $totalesPeriodo,
            'saldoFinal' => $saldoFinal,
            'listaUsuarios' => $listaUsuarios,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'tipoMovimiento' => $tipoMovimiento,
            'usuario' => $usuario,
        ];
    }
 
    public function getCreateData(): array
    {
        $tiposMovimiento = collect(DB::connection($this->connection)->select("SELECT n_numero, c_describe FROM Tablas WHERE n_codtabla = 3 AND n_numero IN (1, 2)"));
        $clasesOperacion = collect(DB::connection($this->connection)->select("SELECT n_numero, c_describe FROM Tablas WHERE n_codtabla = 4"));
        
        $cuentasCaja = DB::connection($this->connection)->table('plan_cuentas')
            ->where('codigo', 'LIKE', '101%')
            ->where('activo', 1)
            ->select('codigo', 'nombre')
            ->get();
            
        $cuentasContrapartida = DB::connection($this->connection)->table('plan_cuentas')
            ->where('activo', 1)
            ->where('codigo', 'NOT LIKE', '101%')
            ->select('codigo', 'nombre', 'tipo')
            ->orderBy('codigo')
            ->get()
            ->groupBy('tipo');

        return compact('tiposMovimiento', 'clasesOperacion', 'cuentasCaja', 'cuentasContrapartida');
    }

   
    public function storeMovimiento(array $data)
    {
        return DB::connection($this->connection)->transaction(function () use ($data) {
            $monto = (float) $data['monto'];
            $tipo = (int) $data['tipo'];
            $glosa = $data['glosa'];
            $fecha = $data['fecha']; 
            $usuarioId = Auth::id() ?? null;
            
            $cuentaCaja = $data['cuenta_caja'];
            $cuentaContrapartida = $data['razon_id'];
            $esIngreso = ($tipo == 1);
            $numeroAsiento = $this->obtenerSiguienteNumeroAsiento($fecha); 
            
            $asientoId = DB::connection($this->connection)->table('libro_diario')->insertGetId([
                'numero' => $numeroAsiento,
                'fecha' => $fecha,
                'glosa' => $glosa,
                'total_debe' => $monto,
                'total_haber' => $monto,
                'balanceado' => 1,
                'estado' => 'ACTIVO',
                'usuario_id' => $usuarioId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::connection($this->connection)->table('libro_diario_detalles')->insert([
                'asiento_id' => $asientoId,
                'cuenta_contable' => $cuentaCaja,
                'debe' => $esIngreso ? $monto : 0.0,
                'haber' => $esIngreso ? 0.0 : $monto,
                'concepto' => $glosa,
                'documento_referencia' => $data['documento'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::connection($this->connection)->table('libro_diario_detalles')->insert([
                'asiento_id' => $asientoId,
                'cuenta_contable' => $cuentaContrapartida,
                'debe' => $esIngreso ? 0.0 : $monto,
                'haber' => $esIngreso ? $monto : 0.0,
                'concepto' => $glosa,
                'documento_referencia' => $data['documento'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $cajaId = DB::connection($this->connection)->table('Caja')->insertGetId([
                'Documento' => $data['documento'],
                'Tipo' => $tipo,
                'Razon' => $data['razon_id'],
                'Fecha' => $fecha,
                'Moneda' => 1,
                'Cambio' => 1.00,
                'Monto' => $monto,
                'Eliminado' => 0,
                'asiento_id' => $asientoId
            ]);
            
            $movimiento = DB::connection($this->connection)->table('Caja')->where('Numero', $cajaId)->first();
            $movimiento->asiento_numero = $numeroAsiento;
            
            return $movimiento;
        });
    }

    
    public function getShowData($id): array
    {
        $movimiento = DB::connection($this->connection)->table('Caja as c')
            ->leftJoin('plan_cuentas as pc', 'c.Razon', '=', 'pc.codigo')
            ->where('c.Numero', $id)
            ->select('c.*', 'pc.nombre as contrapartida_nombre')
            ->first();

        $asiento = null;
        $detalles = null;

        if ($movimiento && $movimiento->asiento_id) {
            $asiento = DB::connection($this->connection)->table('libro_diario')->where('id', $movimiento->asiento_id)->first();
            if ($asiento) {
                $detalles = DB::connection($this->connection)->table('libro_diario_detalles as d')
                    ->leftJoin('plan_cuentas as pc', 'd.cuenta_contable', '=', 'pc.codigo')
                    ->where('d.asiento_id', $asiento->id)
                    ->select('d.*', 'pc.nombre as cuenta_nombre')
                    ->get();
            }
        }

        return compact('movimiento', 'asiento', 'detalles');
    }

    
    public function getEditData($id): array
    {
        $data = $this->getShowData($id);
        $formData = $this->getCreateData();
        return array_merge($data, $formData);
    }

    
    public function updateMovimiento($id, array $data)
    {
        return DB::connection($this->connection)->transaction(function () use ($id, $data) {
            $movimiento = DB::connection($this->connection)->table('Caja')->where('Numero', $id)->first();
            if (!$movimiento) {
                throw new \Exception('Movimiento de caja no encontrado.');
            }
            
            DB::connection($this->connection)->table('Caja')->where('Numero', $id)->update([
                'Fecha' => $data['fecha'],
                'Documento' => $data['documento'],
            ]);

            if ($movimiento->asiento_id) {
                DB::connection($this->connection)->table('libro_diario')->where('id', $movimiento->asiento_id)->update([
                    'fecha' => $data['fecha'],
                    'glosa' => $data['glosa'],
                    'observaciones' => 'Actualizado el ' . now(),
                    'updated_at' => now()
                ]);
                
                DB::connection($this->connection)->table('libro_diario_detalles')->where('asiento_id', $movimiento->asiento_id)->update([
                    'concepto' => $data['glosa'],
                    'documento_referencia' => $data['documento'],
                    'updated_at' => now()
                ]);
            }
            
            return $movimiento;
        });
    }

    
    public function anularMovimiento($id)
    {
        return DB::connection($this->connection)->transaction(function () use ($id) {
            $movimiento = DB::connection($this->connection)->table('Caja')->where('Numero', $id)->first();
            if (!$movimiento) {
                throw new \Exception('Movimiento de caja no encontrado.');
            }

            DB::connection($this->connection)->table('Caja')->where('Numero', $id)->update([
                'Eliminado' => 1
            ]);

            if ($movimiento->asiento_id) {
                $glosaActual = DB::connection($this->connection)->table('libro_diario')->where('id', $movimiento->asiento_id)->value('glosa');
                DB::connection($this->connection)->table('libro_diario')->where('id', $movimiento->asiento_id)->update([
                    'estado' => 'ANULADO',
                    'glosa' => '[ANULADO] ' . $glosaActual,
                    'total_debe' => 0,
                    'total_haber' => 0,
                    'updated_at' => now()
                ]);
                
                DB::connection($this->connection)->table('libro_diario_detalles')->where('asiento_id', $movimiento->asiento_id)->update([
                    'debe' => 0,
                    'haber' => 0,
                ]);
            }

            return $movimiento;
        });
    }
    
  
    public function obtenerSiguienteNumeroAsiento(string $fecha): string
    {
        
        $anio = Carbon::parse($fecha)->year;
        $ultimoAsiento = DB::connection($this->connection)
            ->table('libro_diario')
            ->whereYear('fecha', $anio) 
            ->where('numero', 'LIKE', $anio . '-%') 
            ->orderBy('numero', 'desc')
            ->first();

        $nuevoNumero = 1;
        if ($ultimoAsiento) {
            $partes = explode('-', $ultimoAsiento->numero);
            if (isset($partes[1])) { 
                 $nuevoNumero = (int)$partes[1] + 1;
            }
        }
        
        return $anio . '-' . str_pad($nuevoNumero, 4, '0', STR_PAD_LEFT);
    }
}