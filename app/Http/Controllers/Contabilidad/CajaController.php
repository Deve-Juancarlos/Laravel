<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CajaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $tipo = $request->input('tipo'); // 1=Ingreso, 2=Egreso

            // Obtener movimientos de caja
            $query = DB::table('Caja as c')
                ->leftJoin('Clientes as cl', 'c.Razon', '=', 'cl.Codclie')
                ->leftJoin('Empleados as e', 'c.Razon', '=', 'e.Codemp')
                ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin]);

            if ($tipo) {
                $query->where('c.Tipo', $tipo);
            }

            $movimientosCaja = $query->select([
                'c.Numero',
                'c.Fecha',
                'c.Tipo',
                'c.Razon',
                'c.Documento',
                'c.Monto',
                'c.Moneda',
                'c.Cambio',
                'c.Eliminado',
                'cl.Razon as cliente_nombre',
                'e.Nombre as empleado_nombre',
                DB::raw('CASE 
                    WHEN c.Tipo = 1 THEN "INGRESO"
                    WHEN c.Tipo = 2 THEN "EGRESO"
                    ELSE "OTRO"
                END as tipo_movimiento'),
                DB::raw('CASE 
                    WHEN c.Razon = cl.Codclie THEN cl.Razon
                    WHEN c.Razon = e.Codemp THEN e.Nombre
                    ELSE "TERCERO"
                END as descripcion_razon')
            ])
            ->orderBy('c.Fecha', 'desc')
            ->orderBy('c.Numero', 'desc')
            ->paginate(50);

            // Resumen por tipo de movimiento
            $resumenTipos = DB::table('Caja as c')
                ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    'c.Tipo',
                    DB::raw('COUNT(*) as cantidad'),
                    DB::raw('SUM(CAST(c.Monto as MONEY)) as total_monto'),
                    DB::raw('AVG(CAST(c.Monto as MONEY)) as promedio_monto')
                ])
                ->groupBy('c.Tipo')
                ->get();

            // Resumen por moneda
            $resumenMonedas = DB::table('Caja as c')
                ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    'c.Moneda',
                    DB::raw('COUNT(*) as cantidad'),
                    DB::raw('SUM(CAST(c.Monto as MONEY)) as total_monto'),
                    DB::raw('AVG(CAST(c.Cambio as MONEY)) as cambio_promedio')
                ])
                ->groupBy('c.Moneda')
                ->get();

            // Top 10 movimientos más importantes
            $topMovimientos = DB::table('Caja')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->orderBy('Monto', 'desc')
                ->limit(10)
                ->get();

            // Totales generales
            $totalesGenerales = DB::table('Caja')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    DB::raw('COUNT(*) as total_movimientos'),
                    DB::raw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE 0 END) as total_ingresos'),
                    DB::raw('SUM(CASE WHEN Tipo = 2 THEN Monto ELSE 0 END) as total_egresos'),
                    DB::raw('COUNT(CASE WHEN Tipo = 1 THEN 1 END) as cantidad_ingresos'),
                    DB::raw('COUNT(CASE WHEN Tipo = 2 THEN 1 END) as cantidad_egresos')
                ])
                ->first();

            $saldoNeto = ($totalesGenerales->total_ingresos ?? 0) - ($totalesGenerales->total_egresos ?? 0);

            return view('contabilidad.registros.caja', compact(
                'movimientosCaja', 'resumenTipos', 'resumenMonedas', 'topMovimientos',
                'totalesGenerales', 'saldoNeto', 'fechaInicio', 'fechaFin', 'tipo'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el libro de caja: ' . $e->getMessage());
        }
    }

    /**
     * Show daily cash summary
     */
    public function resumenDiario(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));

            // Movimientos del día
            $movimientosDiarios = DB::table('Caja')
                ->whereDate('Fecha', $fecha)
                ->select([
                    'Numero',
                    'Fecha',
                    'Tipo',
                    'Razon',
                    'Documento',
                    'Monto',
                    'Moneda',
                    'Cambio',
                    'Eliminado'
                ])
                ->orderBy('Tipo')
                ->orderBy('Numero')
                ->get();

            // Resumen por moneda
            $resumenPorMoneda = $movimientosDiarios->groupBy('Moneda')->map(function($grupo) {
                return [
                    'ingresos' => $grupo->where('Tipo', 1)->sum('Monto'),
                    'egresos' => $grupo->where('Tipo', 2)->sum('Monto'),
                    'neto' => $grupo->where('Tipo', 1)->sum('Monto') - $grupo->where('Tipo', 2)->sum('Monto'),
                    'movimientos' => $grupo->count()
                ];
            });

            // Clasificar movimientos por concepto
            $clasificacionMovimientos = $this->clasificarMovimientosDiarios($movimientosDiarios);

            // Saldo anterior
            $saldoAnterior = DB::table('Caja')
                ->where('Fecha', '<', $fecha)
                ->selectRaw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END) as saldo')
                ->value('saldo') ?? 0;

            // Saldo proyectado
            $saldoProyectado = $saldoAnterior + 
                ($movimientosDiarios->where('Tipo', 1)->sum('Monto')) - 
                ($movimientosDiarios->where('Tipo', 2)->sum('Monto'));

            $resumenDiario = [
                'fecha' => $fecha,
                'saldo_anterior' => $saldoAnterior,
                'saldo_proyectado' => $saldoProyectado,
                'total_movimientos' => $movimientosDiarios->count(),
                'total_ingresos' => $movimientosDiarios->where('Tipo', 1)->sum('Monto'),
                'total_egresos' => $movimientosDiarios->where('Tipo', 2)->sum('Monto'),
                'movimientos_activos' => $movimientosDiarios->where('Eliminado', 0)->count()
            ];

            return view('contabilidad.registros.caja-diario', compact(
                'movimientosDiarios', 'resumenPorMoneda', 'clasificacionMovimientos', 'resumenDiario', 'fecha'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar resumen diario: ' . $e->getMessage());
        }
    }

    /**
     * Get monthly cash summary
     */
    public function resumenMensual(Request $request)
    {
        try {
            $anio = $request->input('anio', Carbon::now()->year);
            $mes = $request->input('mes', Carbon::now()->month);

            $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth()->format('Y-m-d');
            $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

            // Resumen diario del mes
            $resumenDiario = DB::table('Caja')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    DB::raw('DAY(Fecha) as dia'),
                    DB::raw('COUNT(*) as movimientos'),
                    DB::raw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE 0 END) as ingresos'),
                    DB::raw('SUM(CASE WHEN Tipo = 2 THEN Monto ELSE 0 END) as egresos'),
                    DB::raw('SUM(CASE WHEN Tipo = 1 THEN Monto WHEN Tipo = 2 THEN -Monto ELSE 0 END) as neto')
                ])
                ->groupBy('dia')
                ->orderBy('dia')
                ->get();

            // Top movimientos del mes
            $topMovimientos = DB::table('Caja')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->orderBy('Monto', 'desc')
                ->limit(20)
                ->get();

            // Análisis por tipo de operación
            $analisisOperaciones = $this->analizarOperacionesMensuales($fechaInicio, $fechaFin);

            // Totales del mes
            $totalesMes = DB::table('Caja')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    DB::raw('COUNT(*) as total_movimientos'),
                    DB::raw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE 0 END) as total_ingresos'),
                    DB::raw('SUM(CASE WHEN Tipo = 2 THEN Monto ELSE 0 END) as total_egresos'),
                    DB::raw('AVG(CAST(Monto as MONEY)) as promedio_movimiento'),
                    DB::raw('MAX(Monto) as mayor_movimiento'),
                    DB::raw('MIN(Monto) as menor_movimiento')
                ])
                ->first();

            return view('contabilidad.registros.caja-mensual', compact(
                'resumenDiario', 'topMovimientos', 'analisisOperaciones', 'totalesMes', 'anio', 'mes'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar resumen mensual: ' . $e->getMessage());
        }
    }

    /**
     * Get cash flow by periods
     */
    public function flujoPorPeriodos(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Flujo por semanas
            $flujoSemanal = DB::table('Caja')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    DB::raw('DATEPART(ISO_WEEK, Fecha) as semana'),
                    DB::raw('YEAR(Fecha) as anio'),
                    DB::raw('COUNT(*) as movimientos'),
                    DB::raw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE 0 END) as ingresos'),
                    DB::raw('SUM(CASE WHEN Tipo = 2 THEN Monto ELSE 0 END) as egresos'),
                    DB::raw('SUM(CASE WHEN Tipo = 1 THEN Monto WHEN Tipo = 2 THEN -Monto ELSE 0 END) as neto')
                ])
                ->groupBy('semana', 'anio')
                ->orderBy('anio', 'desc')
                ->orderBy('semana', 'desc')
                ->get();

            // Flujo por meses
            $flujoMensual = DB::table('Caja')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    DB::raw('YEAR(Fecha) as anio'),
                    DB::raw('MONTH(Fecha) as mes'),
                    DB::raw('DATENAME(month, Fecha) as mes_nombre'),
                    DB::raw('COUNT(*) as movimientos'),
                    DB::raw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE 0 END) as ingresos'),
                    DB::raw('SUM(CASE WHEN Tipo = 2 THEN Monto ELSE 0 END) as egresos')
                ])
                ->groupBy('anio', 'mes', 'mes_nombre')
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->get();

            // Tendencias y estadísticas
            $tendencias = $this->calcularTendenciasFlujo($flujoMensual);

            return view('contabilidad.registros.caja-flujo-periodos', compact(
                'flujoSemanal', 'flujoMensual', 'tendencias', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al analizar flujo por períodos: ' . $e->getMessage());
        }
    }

    /**
     * Get cash reconciliation
     */
    public function conciliacion(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));

            // Saldo según libros (libro de caja)
            $saldoLibros = DB::table('Caja')
                ->where('Fecha', '<=', $fecha)
                ->selectRaw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END) as saldo')
                ->value('saldo') ?? 0;

            // Saldo según arqueo (manual)
            $saldoArqueo = $request->input('saldo_arqueo', 0);

            // Diferencias encontradas
            $diferencia = $saldoBancario ?? $saldoArqueo - $saldoLibros;

            // Movimientos sin comprobante
            $movimientosSinComprobante = DB::table('Caja')
                ->whereDate('Fecha', '<=', $fecha)
                ->where(function($query) {
                    $query->whereNull('Documento')
                          ->orWhere('Documento', '');
                })
                ->select(['Numero', 'Fecha', 'Tipo', 'Monto'])
                ->get();

            // Movimientos por verificar
            $movimientosPorVerificar = DB::table('Caja')
                ->whereDate('Fecha', $fecha)
                ->where('Eliminado', 0)
                ->select(['Numero', 'Fecha', 'Tipo', 'Monto', 'Documento'])
                ->orderBy('Numero')
                ->get();

            $estadoConciliacion = [
                'saldo_libros' => $saldoLibros,
                'saldo_arqueo' => $saldoArqueo,
                'diferencia' => $diferencia,
                'conciliado' => abs($diferencia) < 0.01,
                'movimientos_sin_comprobante' => $movimientosSinComprobante->count(),
                'movimientos_por_verificar' => $movimientosPorVerificar->count()
            ];

            return view('contabilidad.registros.caja-conciliacion', compact(
                'estadoConciliacion', 'movimientosSinComprobante', 'movimientosPorVerificar', 'fecha'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en conciliación: ' . $e->getMessage());
        }
    }

    /**
     * Get cash position analysis
     */
    public function posicionCaja(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));

            // Saldo actual de caja
            $saldoActual = DB::table('Caja')
                ->where('Fecha', '<=', $fecha)
                ->selectRaw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END) as saldo')
                ->value('saldo') ?? 0;

            // Movimientos programados (próximos 7 días)
            $movimientosProgramados = DB::table('Caja')
                ->where('Fecha', '>', $fecha)
                ->where('Fecha', '<=', Carbon::parse($fecha)->addDays(7)->format('Y-m-d'))
                ->select([
                    'Fecha',
                    'Tipo',
                    'Monto',
                    'Documento'
                ])
                ->orderBy('Fecha')
                ->get();

            // Proyección de saldo
            $saldoProyectado = $saldoActual;
            foreach ($movimientosProgramados as $movimiento) {
                if ($movimiento->Tipo == 1) {
                    $saldoProyectado += $movimiento->Monto;
                } else {
                    $saldoProyectado -= $movimiento->Monto;
                }
            }

            // Análisis de liquidez
            $analisisLiquidez = $this->analizarLiquidez($saldoActual, $movimientosProgramados);

            // Alertas de flujo
            $alertas = $this->generarAlertasCaja($saldoActual, $saldoProyectado, $movimientosProgramados);

            $posicionCaja = [
                'fecha' => $fecha,
                'saldo_actual' => $saldoActual,
                'saldo_proyectado' => $saldoProyectado,
                'movimientos_pendientes' => $movimientosProgramados->count(),
                'dias_liquidez' => $this->calcularDiasLiquidez($saldoActual),
                'analisis_liquidez' => $analisisLiquidez,
                'alertas' => $alertas
            ];

            return view('contabilidad.registros.caja-posicion', compact(
                'posicionCaja', 'movimientosProgramados'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al analizar posición de caja: ' . $e->getMessage());
        }
    }

    /**
     * Get pharmacy-specific cash movements
     */
    public function movimientosFarmaceuticos(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Movimientos relacionados con productos farmacéuticos
            $movimientosFarmaceuticos = DB::table('Caja as c')
                ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
                ->where(function($query) {
                    $query->where('c.Documento', 'like', 'FAC%')
                          ->orWhere('c.Documento', 'like', 'BV%')
                          ->orWhere('c.Documento', 'like', 'CP%');
                })
                ->select([
                    'c.Numero',
                    'c.Fecha',
                    'c.Tipo',
                    'c.Documento',
                    'c.Monto',
                    'c.Moneda'
                ])
                ->orderBy('c.Fecha', 'desc')
                ->get();

            // Clasificar por tipo de operación farmacéutica
            $clasificacionFarmaceutica = [
                'VENTAS' => $movimientosFarmaceuticos->where('Documento', 'like', 'FAC%'),
                'BOLETAS_VENTA' => $movimientosFarmaceuticos->where('Documento', 'like', 'BV%'),
                'COMPRAS' => $movimientosFarmaceuticos->where('Documento', 'like', 'CP%'),
                'OTROS' => $movimientosFarmaceuticos->reject(function($item) {
                    return $item->Documento->contains('FAC%') || 
                           $item->Documento->contains('BV%') || 
                           $item->Documento->contains('CP%');
                })
            ];

            // Totales por categoría
            $totalesFarmaceuticos = [];
            foreach ($clasificacionFarmaceutica as $categoria => $movimientos) {
                $totalesFarmaceuticos[$categoria] = [
                    'cantidad' => $movimientos->count(),
                    'total_ingresos' => $movimientos->where('Tipo', 1)->sum('Monto'),
                    'total_egresos' => $movimientos->where('Tipo', 2)->sum('Monto'),
                    'neto' => $movimientos->where('Tipo', 1)->sum('Monto') - 
                             $movimientos->where('Tipo', 2)->sum('Monto')
                ];
            }

            return view('contabilidad.registros.caja-farmaceuticos', compact(
                'clasificacionFarmaceutica', 'totalesFarmaceuticos', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en movimientos farmacéuticos: ' . $e->getMessage());
        }
    }

    /**
     * Classify daily movements
     */
    private function clasificarMovimientosDiarios($movimientos)
    {
        $clasificacion = [
            'VENTAS_EFECTIVO' => [],
            'COMPRAS_EFECTIVO' => [],
            'GASTOS_ADMINISTRATIVOS' => [],
            'PAGO_PROVEEDORES' => [],
            'COBRANZAS' => [],
            'OTROS' => []
        ];

        foreach ($movimientos as $movimiento) {
            $documento = strtoupper($movimiento->Documento ?? '');
            
            if (strpos($documento, 'FAC') !== false || strpos($documento, 'BV') !== false) {
                if ($movimiento->Tipo == 1) {
                    $clasificacion['VENTAS_EFECTIVO'][] = $movimiento;
                } else {
                    $clasificacion['GASTOS_ADMINISTRATIVOS'][] = $movimiento;
                }
            } elseif (strpos($documento, 'CP') !== false) {
                $clasificacion['PAGO_PROVEEDORES'][] = $movimiento;
            } elseif ($movimiento->Tipo == 1) {
                $clasificacion['COBRANZAS'][] = $movimiento;
            } else {
                $clasificacion['OTROS'][] = $movimiento;
            }
        }

        return $clasificacion;
    }

  
    private function analizarOperacionesMensuales($fechaInicio, $fechaFin)
    {
        $operaciones = DB::table('Caja')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->select([
                'Documento',
                'Tipo',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(Monto) as total')
            ])
            ->groupBy('Documento', 'Tipo')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return $operaciones;
    }

    private function calcularTendenciasFlujo($flujoMensual)
    {
        $ingresos = $flujoMensual->pluck('ingresos')->toArray();
        $egresos = $flujoMensual->pluck('egresos')->toArray();
        
        return [
            'promedio_ingresos' => count($ingresos) > 0 ? array_sum($ingresos) / count($ingresos) : 0,
            'promedio_egresos' => count($egresos) > 0 ? array_sum($egresos) / count($egresos) : 0,
            'tendencia_ingresos' => $this->calcularTendencia($ingresos),
            'tendencia_egresos' => $this->calcularTendencia($egresos)
        ];
    }

    private function calcularTendencia($valores)
    {
        if (count($valores) < 2) return 0;
        
        $primero = $valores[0];
        $ultimo = $valores[count($valores) - 1];
        
        return $primero > 0 ? (($ultimo - $primero) / $primero) * 100 : 0;
    }

    private function analizarLiquidez($saldoActual, $movimientosProgramados)
    {
        $ingresosFuturos = $movimientosProgramados->where('Tipo', 1)->sum('Monto');
        $egresosFuturos = $movimientosProgramados->where('Tipo', 2)->sum('Monto');
        
        return [
            'saldo_actual' => $saldoActual,
            'ingresos_programados' => $ingresosFuturos,
            'egresos_programados' => $egresosFuturos,
            'liquidez_breve' => $saldoActual + $ingresosFuturos - $egresosFuturos,
            'nivel_liquidez' => $this->determinarNivelLiquidez($saldoActual)
        ];
    }

 
    private function determinarNivelLiquidez($saldo)
    {
        if ($saldo >= 50000) return 'EXCELENTE';
        if ($saldo >= 20000) return 'BUENA';
        if ($saldo >= 5000) return 'REGULAR';
        return 'CRITICA';
    }

    
    private function generarAlertasCaja($saldoActual, $saldoProyectado, $movimientosProgramados)
    {
        $alertas = [];
        
        if ($saldoActual < 5000) {
            $alertas[] = ['tipo' => 'danger', 'mensaje' => 'Saldo de caja críticamente bajo'];
        }
        
        if ($saldoProyectado < 0) {
            $alertas[] = ['tipo' => 'warning', 'mensaje' => 'Flujo de caja negativo proyectado'];
        }
        
        if ($movimientosProgramados->where('Tipo', 2)->sum('Monto') > $saldoActual) {
            $alertas[] = ['tipo' => 'warning', 'mensaje' => 'Egresos programados exceden saldo actual'];
        }
        
        return $alertas;
    }

   
    private function calcularDiasLiquidez($saldo)
    {
        $egresoDiarioPromedio = DB::table('Caja')
            ->where('Tipo', 2)
            ->where('Fecha', '>=', Carbon::now()->subDays(30))
            ->avg('Monto') ?? 0;
            
        return $egresoDiarioPromedio > 0 ? $saldo / $egresoDiarioPromedio : 0;
    }
}