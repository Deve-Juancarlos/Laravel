<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstadoResultadosController extends Controller
{
   public function index(Request $request) 
    {
        try {
            $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->toDateString());
            $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->toDateString());

            // INGRESOS: cuentas 7xxx (Ventas, etc.)
            $ingresos = DB::table('libro_diario_detalles as d')
                ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
                ->join('plan_cuentas as pc', 'd.cuenta_contable', '=', 'pc.codigo')
                ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
                ->where('c.estado', 'ACTIVO')
                ->where('d.cuenta_contable', 'LIKE', '7%')
                ->select('pc.nombre as descripcion', DB::raw('SUM(d.haber) as total'))
                ->groupBy('pc.nombre')
                ->get()
                ->map(fn($item) => (object)[
                    'descripcion' => $item->descripcion,
                    'total' => round($item->total, 2),
                    'movimientos' => 1
                ]);

            // GASTOS: cuentas 6xxx (Compras) y 9xxx (Gastos operativos)
            $gastos = DB::table('libro_diario_detalles as d')
                ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
                ->join('plan_cuentas as pc', 'd.cuenta_contable', '=', 'pc.codigo')
                ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
                ->where('c.estado', 'ACTIVO')
                ->where(function ($q) {
                    $q->where('d.cuenta_contable', 'LIKE', '6%')
                    ->orWhere('d.cuenta_contable', 'LIKE', '9%');
                })
                ->select('d.cuenta_contable', 'pc.nombre as descripcion', DB::raw('SUM(d.debe) as total'))
                ->groupBy('d.cuenta_contable', 'pc.nombre')
                ->get()
                ->map(fn($item) => (object)[
                    'cuenta_contable' => $item->cuenta_contable,
                    'descripcion' => $item->descripcion,
                    'total' => round($item->total, 2),
                    'movimientos' => 1
                ]);

            // Ventas y costo desde facturación (para margen bruto)
            $ventasNetas = $this->obtenerVentasNetas($fechaInicio, $fechaFin);
            $costoVentas = $this->obtenerCostoVentas($fechaInicio, $fechaFin);

            $totalIngresos = $ingresos->sum('total');
            $totalGastos = $gastos->sum('total');
            $utilidadBruta = $ventasNetas - $costoVentas;
            $gastosOperativos = $totalGastos - $costoVentas;
            $utilidadOperativa = $utilidadBruta - $gastosOperativos;
            $utilidadNeta = $utilidadOperativa;

            $resultados = [
                'ventas_netas' => $ventasNetas,
                'costo_ventas' => $costoVentas,
                'utilidad_bruta' => $utilidadBruta,
                'gastos_operativos' => $gastosOperativos,
                'utilidad_operativa' => $utilidadOperativa,
                'utilidad_neta' => $utilidadNeta,
                'margen_bruto' => $ventasNetas > 0 ? round(($utilidadBruta / $ventasNetas) * 100, 2) : 0,
                'margen_operativo' => $ventasNetas > 0 ? round(($utilidadOperativa / $ventasNetas) * 100, 2) : 0,
                'margen_neto' => $ventasNetas > 0 ? round(($utilidadNeta / $ventasNetas) * 100, 2) : 0,
            ];

            // Comparación con mes anterior
            $fechaInicioAnterior = Carbon::parse($fechaInicio)->subMonth()->startOfMonth()->toDateString();
            $fechaFinAnterior = Carbon::parse($fechaFin)->subMonth()->endOfMonth()->toDateString();
            $comparacion = $this->obtenerComparacionPeriodo($fechaInicioAnterior, $fechaFinAnterior, $fechaInicio, $fechaFin);

            return view('contabilidad.libros.estados-financieros.resultados', compact(
                'ingresos', 'gastos', 'ventasNetas', 'costoVentas', 'resultados',
                'comparacion', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar Estado de Resultados: ' . $e->getMessage());
        }
    }

   
    public function porPeriodos(Request $request)
    {
        $anio = $request->get('anio', now()->year);

        $resultadosMensuales = [];

        for ($mes = 1; $mes <= 12; $mes++) {
            $inicioMes = Carbon::create($anio, $mes, 1)->startOfMonth()->toDateString();
            $finMes = Carbon::create($anio, $mes, 1)->endOfMonth()->toDateString();

            $ventasNetas = DB::table('Doccab')
                ->whereBetween('Fecha', [$inicioMes, $finMes])
                ->where('Tipo', 1)
                ->where('Eliminado', 0)
                ->sum('Subtotal');

            $costoVentas = DB::table('Docdet as d')
                ->join('Doccab as c', 'd.Numero', '=', 'c.Numero')
                ->whereBetween('c.Fecha', [$inicioMes, $finMes])
                ->where('c.Tipo', 1)
                ->where('c.Eliminado', 0)
                ->sum(DB::raw('d.Cantidad * d.Costo'));

            $utilidadBruta = $ventasNetas - $costoVentas;

            $gastosOperativos = DB::table('libro_diario_detalles as d')
                ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
                ->whereBetween('c.fecha', [$inicioMes, $finMes])
                ->where('c.estado', 'ACTIVO')
                ->where(function($q) {
                    $q->where('d.cuenta_contable', 'LIKE', '6%')
                    ->orWhere('d.cuenta_contable', 'LIKE', '9%');
                })
                ->sum('d.debe');

            $utilidadOperativa = $utilidadBruta - $gastosOperativos;

            $resultadosMensuales[$mes] = [
                'mes' => Carbon::create($anio, $mes, 1)->format('F'),
                'ventas_netas' => $ventasNetas,
                'costo_ventas' => $costoVentas,
                'utilidad_bruta' => $utilidadBruta,
                'gastos_operativos' => $gastosOperativos,
                'utilidad_operativa' => $utilidadOperativa,
                'margen_bruto' => $ventasNetas > 0 ? ($utilidadBruta / $ventasNetas) * 100 : 0,
                'margen_operativo' => $ventasNetas > 0 ? ($utilidadOperativa / $ventasNetas) * 100 : 0,
            ];
        }

        // Evitar undefined key: usamos null si no hay datos
        $mesMayorVenta = collect($resultadosMensuales)->sortByDesc('ventas_netas')->keys()->first() ?? null;
        $mesMayorUtilidad = collect($resultadosMensuales)->sortByDesc('utilidad_operativa')->keys()->first() ?? null;

        $ventasAnio = collect($resultadosMensuales)->sum('ventas_netas');
        $promedioMensual = $ventasAnio > 0 ? round($ventasAnio / 12, 0) : 0;

        $tendencias = [
            'crecimiento_ventas' => 0, // opcional: puedes calcular crecimiento interanual
            'promedio_mensual_ventas' => $promedioMensual,
            'mes_mayor_venta' => $mesMayorVenta,
            'mes_mayor_utilidad' => $mesMayorUtilidad,
        ];

        return view('contabilidad.libros.estados-financieros.resultados-periodos', compact(
            'anio', 'resultadosMensuales', 'tendencias'
        ));
    }



    public function balanceGeneral(Request $request)
    {
        try {
            $fecha = $request->get('fecha', now()->toDateString());

            // ACTIVOS CORRIENTES
            $efectivo = $this->obtenerSaldoCuentaRango('10%', $fecha);
            $cuentasPorCobrar = $this->obtenerSaldoCuentaRango('12%', $fecha);
            $inventarios = $this->obtenerSaldoCuentaRango('13%', $fecha);
            $gastosAdelantado = $this->obtenerSaldoCuentaRango('15%', $fecha);
            $totalActivosCorrientes = $efectivo + $cuentasPorCobrar + $inventarios + $gastosAdelantado;

            // ACTIVOS NO CORRIENTES
            $propiedadPlanta = $this->obtenerSaldoCuentaRango('14%', $fecha);
            $depreciacion = $this->obtenerSaldoCuentaRango('149%', $fecha);
            $intangibles = $this->obtenerSaldoCuentaRango('16%', $fecha);
            $otrosActivos = $this->obtenerSaldoCuentaRango('1[7-9]%', $fecha);
            $totalActivosNoCorrientes = $propiedadPlanta - $depreciacion + $intangibles + $otrosActivos;
            $totalActivos = $totalActivosCorrientes + $totalActivosNoCorrientes;

            // PASIVOS CORRIENTES
            $cuentasPorPagar = $this->obtenerSaldoCuentaRango('21%', $fecha);
            $documentosPorPagar = $this->obtenerSaldoCuentaRango('22%', $fecha);
            $prestamosCorto = $this->obtenerSaldoCuentaRango('23[0-4]%', $fecha);
            $provisionImpuestos = $this->obtenerSaldoCuentaRango('24[0-1]%', $fecha);
            $otrosGastosPagar = $this->obtenerSaldoCuentaRango('24[2-9]%', $fecha);
            $totalPasivosCorrientes = $cuentasPorPagar + $documentosPorPagar + $prestamosCorto + $provisionImpuestos + $otrosGastosPagar;

            // PASIVOS NO CORRIENTES
            $prestamosLargo = $this->obtenerSaldoCuentaRango('2[5-9]%', $fecha);
            $provisionBeneficios = $this->obtenerSaldoCuentaRango('25%', $fecha);
            $otrosPasivosLargo = $this->obtenerSaldoCuentaRango('26%', $fecha);
            $totalPasivosNoCorrientes = $prestamosLargo + $provisionBeneficios + $otrosPasivosLargo;
            $totalPasivos = $totalPasivosCorrientes + $totalPasivosNoCorrientes;

            // PATRIMONIO
            $capital = $this->obtenerSaldoCuentaRango('30%', $fecha);
            $reservas = $this->obtenerSaldoCuentaRango('31%', $fecha);
            $resultadosAcum = $this->obtenerSaldoCuentaRango('32%', $fecha);
            $resultadoEjercicio = $this->obtenerResultadoEjercicio($fecha);
            $totalPatrimonio = $capital + $reservas + $resultadosAcum + $resultadoEjercicio;
            $totalPasivosPatrimonio = $totalPasivos + $totalPatrimonio;

            $diferenciaBalance = abs($totalActivos - $totalPasivosPatrimonio);
            $estaBalanceado = $diferenciaBalance < 0.01;

            return view('contabilidad.estados-financieros.balance', compact(
                'efectivo', 'cuentasPorCobrar', 'inventarios', 'gastosAdelantado', 'totalActivosCorrientes',
                'propiedadPlanta', 'depreciacion', 'intangibles', 'otrosActivos', 'totalActivosNoCorrientes', 'totalActivos',
                'cuentasPorPagar', 'documentosPorPagar', 'prestamosCorto', 'provisionImpuestos', 'otrosGastosPagar', 'totalPasivosCorrientes',
                'prestamosLargo', 'provisionBeneficios', 'otrosPasivosLargo', 'totalPasivosNoCorrientes', 'totalPasivos',
                'capital', 'reservas', 'resultadosAcum', 'resultadoEjercicio', 'totalPatrimonio', 'totalPasivosPatrimonio',
                'diferenciaBalance', 'estaBalanceado', 'fecha'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar balance general: ' . $e->getMessage());
        }
    }

    // ========================================
    // MÉTODOS HELPER
    // ========================================

    private function obtenerVentasNetas($fechaInicio, $fechaFin)
    {
        return DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Tipo', 1) // <-- reemplazado
            ->where('Eliminado', 0)
            ->sum('Subtotal');
    }

    private function obtenerCostoVentas($fechaInicio, $fechaFin)
    {
        return DB::table('Docdet as d')
            ->join('Doccab as c', 'd.Numero', '=', 'c.Numero')
            ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
            ->where('c.Tipo', 1) // <-- reemplazado
            ->where('c.Eliminado', 0)
            ->sum(DB::raw('d.Cantidad * d.Costo'));
    }

    private function obtenerSaldoCuentaRango($patron, $fecha)
    {
        return DB::table('libro_diario_detalles as d')
            ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
            ->where('c.fecha', '<=', $fecha)
            ->where('c.estado', 'ACTIVO')
            ->where('d.cuenta_contable', 'LIKE', $patron)
            ->selectRaw('SUM(d.debe) - SUM(d.haber) as saldo')
            ->value('saldo') ?? 0;
    }

    private function obtenerResultadoEjercicio($fecha)
    {
        $anio = Carbon::parse($fecha)->year;
        $inicioAnio = Carbon::create($anio, 1, 1)->toDateString();

        $ingresos = DB::table('libro_diario_detalles as d')
            ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
            ->whereBetween('c.fecha', [$inicioAnio, $fecha])
            ->where('c.estado', 'ACTIVO')
            ->where('d.cuenta_contable', 'LIKE', '7%')
            ->sum('d.haber');

        $gastos = DB::table('libro_diario_detalles as d')
            ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
            ->whereBetween('c.fecha', [$inicioAnio, $fecha])
            ->where('c.estado', 'ACTIVO')
            ->where(function ($q) {
                $q->where('d.cuenta_contable', 'LIKE', '6%')
                  ->orWhere('d.cuenta_contable', 'LIKE', '9%');
            })
            ->sum('d.debe');

        return ($ingresos ?? 0) - ($gastos ?? 0);
    }

    private function obtenerComparacionPeriodo($fechaInicioAnterior, $fechaFinAnterior, $fechaInicioActual, $fechaFinActual)
    {
        $ventasAnterior = $this->obtenerVentasNetas($fechaInicioAnterior, $fechaFinAnterior);
        $ventasActual = $this->obtenerVentasNetas($fechaInicioActual, $fechaFinActual);
        $costosAnterior = $this->obtenerCostoVentas($fechaInicioAnterior, $fechaFinAnterior);
        $costosActual = $this->obtenerCostoVentas($fechaInicioActual, $fechaFinActual);

        return [
            'ventas_actual' => $ventasActual,
            'ventas_anterior' => $ventasAnterior,
            'ventas_variacion' => $ventasAnterior > 0 ? round((($ventasActual - $ventasAnterior) / $ventasAnterior) * 100, 2) : 0,
            'costos_actual' => $costosActual,
            'costos_anterior' => $costosAnterior,
            'costos_variacion' => $costosAnterior > 0 ? round((($costosActual - $costosAnterior) / $costosAnterior) * 100, 2) : 0,
        ];
    }
}