<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EstadoResultadosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Obtener ingresos (cuentas 4xxx)
            $ingresos = DB::table('t_detalle_diario as a')
                ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin])
                ->where('a.Tipo', 'like', '4%')
                ->select([
                    'a.Tipo as cuenta',
                    'a.Descripcion as descripcion',
                    DB::raw('SUM(CAST(a.Saldo as MONEY)) as total')
                ])
                ->groupBy('a.Tipo', 'a.Descripcion')
                ->orderBy('a.Tipo')
                ->get();

            // Obtener costos y gastos (cuentas 5xxx)
            $gastos = DB::table('t_detalle_diario as a')
                ->whereBetween('a.FechaF', [$fechaInicio, $fechaFin])
                ->where('a.Tipo', 'like', '5%')
                ->select([
                    'a.Tipo as cuenta',
                    'a.Descripcion as descripcion',
                    DB::raw('SUM(CAST(a.Importe as MONEY)) as total')
                ])
                ->groupBy('a.Tipo', 'a.Descripcion')
                ->orderBy('a.Tipo')
                ->get();

            // Obtener ventas netas específicamente
            $ventasNetas = $this->obtenerVentasNetas($fechaInicio, $fechaFin);

            // Obtener costos de ventas específicamente
            $costoVentas = $this->obtenerCostoVentas($fechaInicio, $fechaFin);

            // Calcular totales
            $totalIngresos = $ingresos->sum('total');
            $totalGastos = $gastos->sum('total');
            $totalVentas = $ventasNetas;
            $totalCostoVentas = $costoVentas;

            $utilidadBruta = $totalVentas - $totalCostoVentas;
            $utilidadOperativa = $utilidadBruta - ($totalGastos - $totalCostoVentas);
            $utilidadNeta = $utilidadOperativa;

            // Calcular márgenes
            $margenBruto = $totalVentas > 0 ? ($utilidadBruta / $totalVentas) * 100 : 0;
            $margenOperativo = $totalVentas > 0 ? ($utilidadOperativa / $totalVentas) * 100 : 0;
            $margenNeto = $totalVentas > 0 ? ($utilidadNeta / $totalVentas) * 100 : 0;

            // Resumen por categorías
            $resumen = $this->obtenerResumenCategorias($fechaInicio, $fechaFin);

            // Comparación con período anterior
            $comparacion = $this->obtenerComparacionPeriodo($fechaInicio, $fechaFin);

            return view('contabilidad.libros.estados-financieros.resultados', compact(
                'ingresos', 'gastos', 'totalIngresos', 'totalGastos', 'totalVentas', 'totalCostoVentas',
                'utilidadBruta', 'utilidadOperativa', 'utilidadNeta', 'margenBruto', 'margenOperativo', 'margenNeto',
                'fechaInicio', 'fechaFin', 'resumen', 'comparacion'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el estado de resultados: ' . $e->getMessage());
        }
    }

    /**
     * Get detailed analysis by periods
     */
    public function porPeriodos(Request $request)
    {
        try {
            $anio = $request->input('anio', Carbon::now()->year);

            // Obtener resultados mensuales
            $resultadosMensuales = [];
            for ($mes = 1; $mes <= 12; $mes++) {
                $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth()->format('Y-m-d');
                $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

                $resultadosMensuales[$mes] = $this->calcularResultados($fechaInicio, $fechaFin);
                $resultadosMensuales[$mes]['mes'] = Carbon::create($anio, $mes, 1)->format('F');
            }

            // Calcular tendencias y promedios
            $tendencias = $this->calcularTendencias($resultadosMensuales);

            return view('contabilidad.libros.estados-financieros.resultados-periodos', compact(
                'resultadosMensuales', 'tendencias', 'anio'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al analizar períodos: ' . $e->getMessage());
        }
    }

    /**
     * Get detailed breakdown by account
     */
    public function detalleCuenta($cuenta)
    {
        try {
            $fechaInicio = Carbon::now()->startOfYear()->format('Y-m-d');
            $fechaFin = Carbon::now()->endOfYear()->format('Y-m-d');

            // Obtener movimientos de la cuenta
            $movimientos = DB::table('t_detalle_diario')
                ->where('Tipo', $cuenta)
                ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                ->select([
                    'Numero as numero',
                    'FechaF as fecha',
                    'Descripcion as concepto',
                    'Importe as debito',
                    'Saldo as credito',
                    'Nombre as auxiliar'
                ])
                ->orderBy('FechaF')
                ->orderBy('Numero')
                ->get();

            // Clasificar movimientos
            $clasificacion = $this->clasificarCuenta($cuenta);

            return view('contabilidad.libros.estados-financieros.resultados-detalle', compact(
                'cuenta', 'movimientos', 'clasificacion', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar detalle de cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Get comparative analysis
     */
    
public function comparativo(Request $request)
{
    // Fechas
    $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->format('Y-m-d');
    $fechaFin = $request->fecha_fin ?? now()->endOfMonth()->format('Y-m-d');

    // Datos básicos (ventas, costo, gastos)
    $totalVentas = DB::table('Doccab')->whereBetween('Fecha', [$fechaInicio, $fechaFin])
        ->where('Tipo', 1)->where('Eliminado', 0)->sum('Subtotal');

    $totalCostoVentas = DB::table('Docdet as dd')
        ->join('Doccab as dc', 'dd.Numero', '=', 'dc.Numero')
        ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
        ->where('dc.Tipo', 1)
        ->where('dc.Eliminado', 0)
        ->sum(DB::raw('CAST(dd.Costo as MONEY) * dd.Cantidad'));

    $totalGastos = DB::table('t_detalle_diario')
        ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
        ->where('Tipo', 'like', '5%')
        ->whereNotIn('Tipo', ['501','511'])
        ->sum(DB::raw('CAST(Importe as MONEY)'));

    $utilidadBruta = $totalVentas - $totalCostoVentas;
    $utilidadNeta = $utilidadBruta - $totalGastos;

    $margenBruto = $totalVentas ? ($utilidadBruta / $totalVentas) * 100 : 0;
    $margenNeto = $totalVentas ? ($utilidadNeta / $totalVentas) * 100 : 0;
    $margenOperativo = $totalVentas ? (($utilidadBruta - $totalGastos) / $totalVentas) * 100 : 0;

    $resumen = [
        'INGRESOS' => $totalVentas,
        'COSTO_VENTAS' => $totalCostoVentas,
        'UTILIDAD_BRUTA' => $utilidadBruta,
        'GASTOS_OPERATIVOS' => $totalGastos,
        'UTILIDAD_OPERATIVA' => $utilidadBruta - $totalGastos,
    ];

    // Ingresos / Gastos por cuentas
    $ingresos = DB::table('libro_diario_detalles')
    ->select('cuenta_contable as cuenta','concepto as descripcion', DB::raw('SUM(debe - haber) as total'))
    ->join('libro_diario as ld', 'libro_diario_detalles.asiento_id', '=', 'ld.id')
    ->whereBetween('ld.fecha', [$fechaInicio, $fechaFin])
    ->where('cuenta_contable', 'like', '4%')
    ->groupBy('cuenta_contable','concepto')
    ->get();

    $gastos = DB::table('libro_diario_detalles')
        ->select('cuenta_contable as cuenta','concepto as descripcion', DB::raw('SUM(debe - haber) as total'))
        ->join('libro_diario as ld', 'libro_diario_detalles.asiento_id', '=', 'ld.id')
        ->whereBetween('ld.fecha', [$fechaInicio, $fechaFin])
        ->where('cuenta_contable', 'like', '5%')
        ->groupBy('cuenta_contable','concepto')
        ->get();


    // Retorno a la vista con todas las variables
    return view('contabilidad.libros.estados-financieros.resultados', compact(
        'fechaInicio','fechaFin',
        'totalVentas','totalCostoVentas','totalGastos',
        'utilidadBruta','utilidadNeta',
        'margenBruto','margenNeto','margenOperativo',
        'resumen','ingresos','gastos'
    ));
}




    /**
     * Get specific product line analysis (for pharmacy)
     */
    public function analisisFarmaceutico(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Obtener ventas por línea farmacéutica usando vista específica
            $ventasPorLinea = DB::table('v_total_cartera_procter')
                ->select([
                    'Vendedor',
                    'Razon',
                    'CodClie'
                ])
                ->get();

            // Obtener costos desde Saldos y Productos
            $costosFarmaceuticos = $this->obtenerCostosFarmaceuticos($fechaInicio, $fechaFin);

            // Análisis de rentabilidad farmacéutica
            $rentabilidad = $this->calcularRentabilidadFarmaceutica($fechaInicio, $fechaFin);

            return view('contabilidad.libros.estados-financieros.farmaceutico', compact(
                'ventasPorLinea', 'costosFarmaceuticos', 'rentabilidad', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en análisis farmacéutico: ' . $e->getMessage());
        }
    }

    /**
     * Get sales net from Doccab
     */
    private function obtenerVentasNetas($fechaInicio, $fechaFin)
    {
        $ventas = DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Tipo', 1) // Facturas
            ->where('Eliminado', 0)
            ->selectRaw('SUM(Subtotal) as total_ventas')
            ->value('total_ventas') ?? 0;

        return $ventas;
    }

    /**
     * Get cost of goods sold from Docdet
     */
    private function obtenerCostoVentas($fechaInicio, $fechaFin)
    {
        $costos = DB::table('Docdet as dd')
            ->join('Doccab as dc', 'dd.Numero', '=', 'dc.Numero')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Tipo', 1) // Facturas
            ->where('dc.Eliminado', 0)
            ->selectRaw('SUM(CAST(dd.Costo as MONEY) * dd.Cantidad) as total_costo')
            ->value('total_costo') ?? 0;

        return $costos;
    }

    /**
     * Calculate results for a period
     */
    private function calcularResultados($fechaInicio, $fechaFin)
    {
        $ventasNetas = $this->obtenerVentasNetas($fechaInicio, $fechaFin);
        $costoVentas = $this->obtenerCostoVentas($fechaInicio, $fechaFin);
        $gastosOperativos = $this->obtenerGastosOperativos($fechaInicio, $fechaFin);

        $utilidadBruta = $ventasNetas - $costoVentas;
        $utilidadOperativa = $utilidadBruta - $gastosOperativos;
        $margenBruto = $ventasNetas > 0 ? ($utilidadBruta / $ventasNetas) * 100 : 0;
        $margenOperativo = $ventasNetas > 0 ? ($utilidadOperativa / $ventasNetas) * 100 : 0;

        return [
            'ventas_netas' => $ventasNetas,
            'costo_ventas' => $costoVentas,
            'utilidad_bruta' => $utilidadBruta,
            'gastos_operativos' => $gastosOperativos,
            'utilidad_operativa' => $utilidadOperativa,
            'margen_bruto' => $margenBruto,
            'margen_operativo' => $margenOperativo
        ];
    }

    /**
     * Get operating expenses
     */
    private function obtenerGastosOperativos($fechaInicio, $fechaFin)
    {
        $gastos = DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
            ->where('Tipo', 'like', '5%')
            ->whereNotIn('Tipo', ['501', '511']) // Excluir costo de ventas
            ->selectRaw('SUM(CAST(Importe as MONEY)) as total_gastos')
            ->value('total_gastos') ?? 0;

        return $gastos;
    }

    /**
     * Get summary by categories
     */
    private function obtenerResumenCategorias($fechaInicio, $fechaFin)
    {
        $ingresos = $this->obtenerVentasNetas($fechaInicio, $fechaFin);
        $costos = $this->obtenerCostoVentas($fechaInicio, $fechaFin);
        $gastosOperativos = $this->obtenerGastosOperativos($fechaInicio, $fechaFin);

        return [
            'INGRESOS' => $ingresos,
            'COSTO_VENTAS' => $costos,
            'UTILIDAD_BRUTA' => $ingresos - $costos,
            'GASTOS_OPERATIVOS' => $gastosOperativos,
            'UTILIDAD_OPERATIVA' => ($ingresos - $costos) - $gastosOperativos
        ];
    }

    /**
     * Get comparison with previous period
     */
    private function obtenerComparacionPeriodo($fechaInicio, $fechaFin)
    {
        $anioActual = Carbon::parse($fechaInicio)->year;
        
        $periodoAnterior = [
            'inicio' => Carbon::create($anioActual - 1, Carbon::parse($fechaInicio)->month, 1)->format('Y-m-d'),
            'fin' => Carbon::create($anioActual - 1, Carbon::parse($fechaFin)->month, 1)->endOfMonth()->format('Y-m-d')
        ];

        $resultadosActuales = $this->calcularResultados($fechaInicio, $fechaFin);
        $resultadosAnteriores = $this->calcularResultados($periodoAnterior['inicio'], $periodoAnterior['fin']);

        return [
            'actual' => $resultadosActuales,
            'anterior' => $resultadosAnteriores,
            'variacion_ventas' => $this->calcularVariacionPorcentual(
                $resultadosActuales['ventas_netas'], 
                $resultadosAnteriores['ventas_netas']
            ),
            'variacion_utilidad' => $this->calcularVariacionPorcentual(
                $resultadosActuales['utilidad_operativa'], 
                $resultadosAnteriores['utilidad_operativa']
            )
        ];
    }

    /**
     * Calculate percentage variation
     */
    private function calcularVariacionPorcentual($actual, $anterior)
    {
        if ($anterior == 0) {
            return $actual > 0 ? 100 : 0;
        }
        return (($actual - $anterior) / $anterior) * 100;
    }

    /**
     * Calculate trends
     */
   private function calcularTendencias(array $resultadosMensuales): array
    {
        $ventas = array_column($resultadosMensuales, 'ventas_netas');
        $utilidad = array_column($resultadosMensuales, 'utilidad_operativa');

        // Inicializamos valores por defecto
        $crecimiento_ventas = 0;
        $promedio_mensual_ventas = 0;
        $promedio_mensual_utilidad = 0;
        $mes_mayor_venta = null;
        $mes_mayor_utilidad = null;

        // Calculamos crecimiento de ventas (solo si hay más de un mes y el primer valor no es 0)
        if (count($ventas) > 1 && $ventas[0] != 0) {
            $crecimiento_ventas = (($ventas[array_key_last($ventas)] - $ventas[0]) / $ventas[0]) * 100;
        }

        // Promedios mensuales (si hay datos)
        if (!empty($ventas)) {
            $promedio_mensual_ventas = array_sum($ventas) / count($ventas);
        }

        if (!empty($utilidad)) {
            $promedio_mensual_utilidad = array_sum($utilidad) / count($utilidad);
        }

        // Mes con mayor venta/utilidad
        if (!empty($ventas) && max($ventas) != 0) {
            $mes_mayor_venta = array_keys($ventas, max($ventas))[0];
        }

        if (!empty($utilidad) && max($utilidad) != 0) {
            $mes_mayor_utilidad = array_keys($utilidad, max($utilidad))[0];
        }

        return [
            'crecimiento_ventas' => $crecimiento_ventas,
            'promedio_mensual_ventas' => $promedio_mensual_ventas,
            'promedio_mensual_utilidad' => $promedio_mensual_utilidad,
            'mes_mayor_venta' => $mes_mayor_venta,
            'mes_mayor_utilidad' => $mes_mayor_utilidad
        ];
    }


    /**
     * Calculate variations between periods
     */
    private function calcularVariaciones($periodos)
    {
        $variaciones = [];

        // Comparación mensual
        if (isset($periodos['actual_mensual']) && isset($periodos['anterior_mensual'])) {
            $actual = $periodos['actual_mensual']['resultados'];
            $anterior = $periodos['anterior_mensual']['resultados'];
            
            $variaciones['mensual'] = [
                'ventas' => $this->calcularVariacionPorcentual($actual['ventas_netas'], $anterior['ventas_netas']),
                'utilidad' => $this->calcularVariacionPorcentual($actual['utilidad_operativa'], $anterior['utilidad_operativa'])
            ];
        }

        return $variaciones;
    }

    /**
     * Classify account type
     */
    private function clasificarCuenta($cuenta)
    {
        $primeraLetra = substr($cuenta, 0, 1);
        
        switch ($primeraLetra) {
            case '4':
                return 'INGRESO';
            case '5':
                return 'GASTO';
            default:
                return 'OTRO';
        }
    }

    /**
     * Get pharmaceutical costs
     */
    private function obtenerCostosFarmaceuticos($fechaInicio, $fechaFin)
        {
            $costos = DB::table('Docdet as dd')
                ->join('Doccab as dc', 'dd.Numero', '=', 'dc.Numero')
                ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->select([
                    'p.Nombre',
                    DB::raw('SUM(CAST(dd.Costo as MONEY) * dd.Cantidad) as costo_total'),
                    DB::raw('SUM(dd.Cantidad) as cantidad_total')
                ])
                ->groupBy('p.CodPro', 'p.Nombre')
                ->orderBy('costo_total', 'desc')
                ->limit(10)
                ->get();

            return $costos;
        }

        /**
         * Calculate pharmaceutical profitability
         */
        private function calcularRentabilidadFarmaceutica($fechaInicio, $fechaFin)
        {
            $ventas = DB::table('Docdet as dd')
                ->join('Doccab as dc', 'dd.Numero', '=', 'dc.Numero')
                ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->select([
                    'p.Nombre',
                    DB::raw('SUM(CAST(dd.Subtotal as MONEY)) as ventas_total'),
                    DB::raw('SUM(CAST(dd.Costo as MONEY) * dd.Cantidad) as costo_total')
                ])
                ->groupBy('p.CodPro', 'p.Nombre')
                ->get()
                ->map(function ($item) {
                    $item->margen = $item->ventas_total > 0 ? 
                        (($item->ventas_total - $item->costo_total) / $item->ventas_total) * 100 : 0;
                    return $item;
                });

            return $ventas;
        }

        public function estadoResultados(Request $request)
    {
        // Fechas de filtro
        $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?? now()->endOfMonth()->format('Y-m-d');

        // Ventas netas
        $totalVentas = DB::table('movimientos_contables')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('cuenta', 'like', '4%')
            ->sum('debe') - DB::table('movimientos_contables')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('cuenta', 'like', '4%')
            ->sum('haber');

        // Costo de ventas
        $totalCostoVentas = DB::table('movimientos_contables')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('cuenta', 'like', '5%')
            ->sum('debe') - DB::table('movimientos_contables')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('cuenta', 'like', '5%')
            ->sum('haber');

        // Gastos operativos (ejemplo cuentas 51xx)
        $totalGastos = DB::table('movimientos_contables')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('cuenta', 'like', '51%')
            ->sum('debe') - DB::table('movimientos_contables')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('cuenta', 'like', '51%')
            ->sum('haber');

        // Utilidades y márgenes
        $utilidadBruta = $totalVentas - $totalCostoVentas;
        $utilidadNeta = $utilidadBruta - $totalGastos;

        $margenBruto = $totalVentas ? ($utilidadBruta / $totalVentas) * 100 : 0;
        $margenNeto = $totalVentas ? ($utilidadNeta / $totalVentas) * 100 : 0;
        $margenOperativo = $totalVentas ? (($utilidadBruta - $totalGastos) / $totalVentas) * 100 : 0;

        // Resumen
        $resumen = [
            'INGRESOS' => $totalVentas,
            'COSTO_VENTAS' => $totalCostoVentas,
            'UTILIDAD_BRUTA' => $utilidadBruta,
            'GASTOS_OPERATIVOS' => $totalGastos,
            'UTILIDAD_OPERATIVA' => $utilidadBruta - $totalGastos,
        ];

        // Detalle de cuentas
        $ingresos = DB::table('movimientos_contables')
            ->select('cuenta', 'descripcion', DB::raw('SUM(debe - haber) as total'))
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('cuenta', 'like', '4%')
            ->groupBy('cuenta', 'descripcion')
            ->get();

        $gastos = DB::table('movimientos_contables')
            ->select('cuenta', 'descripcion', DB::raw('SUM(debe - haber) as total'))
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('cuenta', 'like', '5%')
            ->groupBy('cuenta', 'descripcion')
            ->get();

        return view('contabilidad.libros.estados-financieros.resultados', compact(
            'fechaInicio', 'fechaFin',
            'totalVentas', 'totalCostoVentas', 'totalGastos',
            'utilidadBruta', 'utilidadNeta',
            'margenBruto', 'margenNeto', 'margenOperativo',
            'resumen', 'ingresos', 'gastos'
        ));
    }


}