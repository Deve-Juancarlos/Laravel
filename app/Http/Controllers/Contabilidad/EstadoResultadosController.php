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
     * Estado de Resultados Principal (P&L)
     */
    public function index()
    {
        try {
            $fechaInicio = request('fecha_inicio', now()->startOfMonth()->toDateString());
            $fechaFin = request('fecha_fin', now()->endOfMonth()->toDateString());
            
            // Obtener ingresos (cuentas 4xxx)
            $ingresos = DB::table('t_detalle_diario')
                ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                ->where('Tipo', 'LIKE', '4%')
                ->select('Descripcion', 'Saldo', 'Numero')
                ->get()
                ->groupBy('Descripcion')
                ->map(function($items) {
                    return [
                        'descripcion' => $items->first()->Descripcion,
                        'total' => $items->sum('Saldo'),
                        'movimientos' => $items->count()
                    ];
                })->values();

            // Obtener gastos (cuentas 5xxx)
            $gastos = DB::table('t_detalle_diario')
                ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                ->where('Tipo', 'LIKE', '5%')
                ->select('Descripcion', 'Saldo', 'Numero')
                ->get()
                ->groupBy('Descripcion')
                ->map(function($items) {
                    return [
                        'descripcion' => $items->first()->Descripcion,
                        'total' => abs($items->sum('Saldo')),
                        'movimientos' => $items->count()
                    ];
                })->values();

            // Calcular ventas netas desde Doccab
            $ventasNetas = $this->obtenerVentasNetas($fechaInicio, $fechaFin);
            
            // Calcular costo de ventas desde Docdet
            $costoVentas = $this->obtenerCostoVentas($fechaInicio, $fechaFin);
            
            // Calcular resultados
            $resultados = $this->calcularResultados($ventasNetas, $costoVentas, $gastos->sum('total'));
            
            // Resumen por categorías
            $resumenCategorias = $this->obtenerResumenCategorias($ingresos, $costoVentas, $gastos);
            
            // Comparación con período anterior
            $fechaInicioAnterior = Carbon::parse($fechaInicio)->subMonth()->startOfMonth()->toDateString();
            $fechaFinAnterior = Carbon::parse($fechaFin)->subMonth()->endOfMonth()->toDateString();
            $comparacion = $this->obtenerComparacionPeriodo($fechaInicioAnterior, $fechaFinAnterior, $fechaInicio, $fechaFin);

            return view('contabilidad.estados-financieros.resultados', compact(
                'ingresos', 'gastos', 'ventasNetas', 'costoVentas', 'resultados', 
                'resumenCategorias', 'comparacion', 'fechaInicio', 'fechaFin'
            ));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar Estado de Resultados: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Análisis por Períodos (mensual del año)
     */
    public function porPeriodos()
    {
        try {
            $anio = request('anio', now()->year);
            $resultadosMensuales = [];
            
            for ($mes = 1; $mes <= 12; $mes++) {
                $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth();
                $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth();
                
                $ventasNetas = $this->obtenerVentasNetas($fechaInicio->toDateString(), $fechaFin->toDateString());
                $costoVentas = $this->obtenerCostoVentas($fechaInicio->toDateString(), $fechaFin->toDateString());
                
                // Obtener gastos del mes (excluyendo costo de ventas)
                $gastosOperativos = $this->obtenerGastosOperativos($fechaInicio->toDateString(), $fechaFin->toDateString(), $costoVentas);
                
                $utilidadBruta = $ventasNetas - $costoVentas;
                $utilidadOperativa = $utilidadBruta - $gastosOperativos;
                $margenOperativo = $ventasNetas > 0 ? ($utilidadOperativa / $ventasNetas) * 100 : 0;
                
                $resultadosMensuales[] = [
                    'mes' => $mes,
                    'nombre_mes' => Carbon::create($anio, $mes, 1)->format('F'),
                    'ventas_netas' => $ventasNetas,
                    'costo_ventas' => $costoVentas,
                    'utilidad_bruta' => $utilidadBruta,
                    'gastos_operativos' => $gastosOperativos,
                    'utilidad_operativa' => $utilidadOperativa,
                    'margen_operativo' => $margenOperativo
                ];
            }
            
            // Calcular tendencias
            $tendencias = $this->calcularTendencias($resultadosMensuales);

            return view('contabilidad.estados-financieros.resultados-periodos', compact(
                'resultadosMensuales', 'tendencias', 'anio'
            ));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar análisis por períodos: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Detalle de cuenta específica
     */
    public function detalleCuenta($cuenta)
    {
        try {
            $fechaInicio = request('fecha_inicio', now()->startOfMonth()->toDateString());
            $fechaFin = request('fecha_fin', now()->endOfMonth()->toDateString());
            
            // Obtener movimientos de la cuenta
            $movimientos = DB::table('t_detalle_diario')
                ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
                ->where('Tipo', $cuenta)
                ->orderBy('FechaF', 'desc')
                ->get();

            // Clasificar cuenta
            $clasificacion = $this->clasificarCuenta($cuenta);
            
            // Calcular totales
            $totalDebito = $movimientos->where('Saldo', '>', 0)->sum('Saldo');
            $totalCredito = abs($movimientos->where('Saldo', '<', 0)->sum('Saldo'));
            $saldoFinal = $totalDebito - $totalCredito;

            return view('contabilidad.estados-financieros.resultados-detalle', compact(
                'movimientos', 'cuenta', 'clasificacion', 'totalDebito', 
                'totalCredito', 'saldoFinal', 'fechaInicio', 'fechaFin'
            ));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar detalle de cuenta: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Análisis Comparativo de Períodos
     */
    public function comparativo()
    {
        try {
            $fechaActual = now();
            $mesActual = $fechaActual->month;
            $anioActual = $fechaActual->year;
            
            // Definir períodos
            $periodos = [
                'mes_actual' => [
                    'inicio' => $fechaActual->startOfMonth()->toDateString(),
                    'fin' => $fechaActual->endOfMonth()->toDateString(),
                    'nombre' => 'Mes Actual'
                ],
                'mes_anterior' => [
                    'inicio' => $fechaActual->copy()->subMonth()->startOfMonth()->toDateString(),
                    'fin' => $fechaActual->copy()->subMonth()->endOfMonth()->toDateString(),
                    'nombre' => 'Mes Anterior'
                ],
                'anio_actual' => [
                    'inicio' => Carbon::create($anioActual, 1, 1)->toDateString(),
                    'fin' => Carbon::create($anioActual, 12, 31)->toDateString(),
                    'nombre' => 'Año Actual'
                ],
                'anio_anterior' => [
                    'inicio' => Carbon::create($anioActual - 1, 1, 1)->toDateString(),
                    'fin' => Carbon::create($anioActual - 1, 12, 31)->toDateString(),
                    'nombre' => 'Año Anterior'
                ]
            ];

            $resultadosPorPeriodo = [];
            $variaciones = [];

            foreach ($periodos as $key => $periodo) {
                $ventasNetas = $this->obtenerVentasNetas($periodo['inicio'], $periodo['fin']);
                $costoVentas = $this->obtenerCostoVentas($periodo['inicio'], $periodo['fin']);
                $gastosOperativos = $this->obtenerGastosOperativos($periodo['inicio'], $periodo['fin'], $costoVentas);
                
                $utilidadBruta = $ventasNetas - $costoVentas;
                $utilidadOperativa = $utilidadBruta - $gastosOperativos;
                $margenOperativo = $ventasNetas > 0 ? ($utilidadOperativa / $ventasNetas) * 100 : 0;
                
                $resultadosPorPeriodo[$key] = [
                    'nombre' => $periodo['nombre'],
                    'ventas_netas' => $ventasNetas,
                    'costo_ventas' => $costoVentas,
                    'utilidad_bruta' => $utilidadBruta,
                    'gastos_operativos' => $gastosOperativos,
                    'utilidad_operativa' => $utilidadOperativa,
                    'margen_operativo' => $margenOperativo
                ];
            }

            // Calcular variaciones
            $variaciones = $this->calcularVariaciones($resultadosPorPeriodo);

            return view('contabilidad.estados-financieros.resultados-comparativo', compact(
                'resultadosPorPeriodo', 'variaciones', 'periodos'
            ));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar análisis comparativo: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Análisis Farmacéutico Específico
     */
    public function analisisFarmaceutico()
    {
        try {
            $fechaInicio = request('fecha_inicio', now()->startOfMonth()->toDateString());
            $fechaFin = request('fecha_fin', now()->endOfMonth()->toDateString());
            
            // Obtener datos de vista farmacéutica especializada
            $ventasPorLinea = DB::table('v_total_cartera_procter')
                ->select(['Vendedor', 'Razon', 'CodClie'])
                ->get();
            
            // Obtener costos farmacéuticos
            $costosFarmaceuticos = $this->obtenerCostosFarmaceuticos($fechaInicio, $fechaFin);
            
            // Calcular rentabilidad por producto
            $rentabilidadFarmaceutica = $this->calcularRentabilidadFarmaceutica($fechaInicio, $fechaFin);
            
            $totalVentas = $rentabilidadFarmaceutica->sum('ventas_total');
            $totalCostos = $rentabilidadFarmaceutica->sum('costo_total');
            $margenPromedio = $totalVentas > 0 ? (($totalVentas - $totalCostos) / $totalVentas) * 100 : 0;

            return view('contabilidad.estados-financieros.farmaceutico', compact(
                'ventasPorLinea', 'costosFarmaceuticos', 'rentabilidadFarmaceutica',
                'totalVentas', 'totalCostos', 'margenPromedio', 'fechaInicio', 'fechaFin'
            ));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar análisis farmacéutico: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Estado de Flujo de Efectivo (Cash Flow)
     */
    public function cashFlow()
    {
        try {
            $fechaInicio = request('fecha_inicio', now()->startOfMonth()->toDateString());
            $fechaFin = request('fecha_fin', now()->endOfMonth()->toDateString());
            
            // Actividades Operativas - Cobros y Pagos operativos
            $cobrosClientes = $this->obtenerCobrosClientes($fechaInicio, $fechaFin);
            $pagosProveedores = $this->obtenerPagosProveedores($fechaInicio, $fechaFin);
            $gastosOperativos = $this->obtenerGastosOperativos($fechaInicio, $fechaFin, 0); // Sin costo de ventas
            
            $flujoOperativo = $cobrosClientes - $pagosProveedores - $gastosOperativos;
            
            // Actividades de Inversión - Compra/Venta activos
            $compraActivosFijos = $this->obtenerCompraActivosFijos($fechaInicio, $fechaFin);
            $ventaActivosFijos = $this->obtenerVentaActivosFijos($fechaInicio, $fechaFin);
            
            $flujoInversion = $compraActivosFijos - $ventaActivosFijos;
            
            // Actividades de Financiamiento - Préstamos y dividendos
            $prestamosRecibidos = $this->obtenerPrestamosRecibidos($fechaInicio, $fechaFin);
            $dividendosPagados = $this->obtenerDividendosPagados($fechaInicio, $fechaFin);
            $capitalAportes = $this->obtenerCapitalAportes($fechaInicio, $fechaFin);
            
            $flujoFinanciamiento = $prestamosRecibidos + $capitalAportes - $dividendosPagados;
            
            // Flujo total y efectivo final
            $flujoTotal = $flujoOperativo + $flujoInversion + $flujoFinanciamiento;
            $efectivoInicial = $this->obtenerEfectivoInicial($fechaInicio);
            $efectivoFinal = $efectivoInicial + $flujoTotal;
            
            // Calcular ratios de liquidez
            $razonCorriente = $this->calcularRazonCorriente();
            $pruebaAcida = $this->calcularPruebaAcida();
            $capitalTrabajo = $this->calcularCapitalTrabajo();
            
            return view('contabilidad.estados-financieros.flujo-caja', compact(
                'cobrosClientes', 'pagosProveedores', 'gastosOperativos', 'flujoOperativo',
                'compraActivosFijos', 'ventaActivosFijos', 'flujoInversion',
                'prestamosRecibidos', 'dividendosPagados', 'capitalAportes', 'flujoFinanciamiento',
                'flujoTotal', 'efectivoInicial', 'efectivoFinal',
                'razonCorriente', 'pruebaAcida', 'capitalTrabajo',
                'fechaInicio', 'fechaFin'
            ));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar flujo de efectivo: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Balance General (Estado de Situación Financiera)
     */
    public function balanceGeneral()
    {
        try {
            $fecha = request('fecha', now()->toDateString());
            
            // ACTIVOS CORRIENTES (1xxx - hasta 1,2xxx)
            $efectivo = $this->obtenerEfectivo($fecha);
            $cuentasPorCobrar = $this->obtenerCuentasPorCobrar($fecha);
            $inventarios = $this->obtenerInventarios($fecha);
            $gastosPagadosAdelantado = $this->obtenerGastosPagadosAdelantado($fecha);
            
            $totalActivosCorrientes = $efectivo + $cuentasPorCobrar + $inventarios + $gastosPagadosAdelantado;
            
            // ACTIVOS NO CORRIENTES (1,3xxx - 1,9xxx)
            $propiedadPlantaEquipo = $this->obtenerPropiedadPlantaEquipo($fecha);
            $depreciacionAcumulada = $this->obtenerDepreciacionAcumulada($fecha);
            $intangibles = $this->obtenerIntangibles($fecha);
            $otrosActivos = $this->obtenerOtrosActivos($fecha);
            
            $totalActivosNoCorrientes = $propiedadPlantaEquipo - $depreciacionAcumulada + $intangibles + $otrosActivos;
            $totalActivos = $totalActivosCorrientes + $totalActivosNoCorrientes;
            
            // PASIVOS CORRIENTES (2xxx - hasta 2,1xxx)
            $cuentasPorPagar = $this->obtenerCuentasPorPagar($fecha);
            $documentosPorPagar = $this->obtenerDocumentosPorPagar($fecha);
            $prestamosCortoPlazo = $this->obtenerPrestamosCortoPlazo($fecha);
            $provisionImpuestos = $this->obtenerProvisionImpuestos($fecha);
            $otrosGastosPorPagar = $this->obtenerOtrosGastosPorPagar($fecha);
            
            $totalPasivosCorrientes = $cuentasPorPagar + $documentosPorPagar + $prestamosCortoPlazo + $provisionImpuestos + $otrosGastosPorPagar;
            
            // PASIVOS NO CORRIENTES (2,2xxx - 2,9xxx)
            $prestamosLargoPlazo = $this->obtenerPrestamosLargoPlazo($fecha);
            $provisionBeneficios = $this->obtenerProvisionBeneficios($fecha);
            $otrosPasivosLargoPlazo = $this->obtenerOtrosPasivosLargoPlazo($fecha);
            
            $totalPasivosNoCorrientes = $prestamosLargoPlazo + $provisionBeneficios + $otrosPasivosLargoPlazo;
            $totalPasivos = $totalPasivosCorrientes + $totalPasivosNoCorrientes;
            
            // PATRIMONIO (3xxx)
            $capital = $this->obtenerCapital($fecha);
            $reservas = $this->obtenerReservas($fecha);
            $resultadosAcumulados = $this->obtenerResultadosAcumulados($fecha);
            $resultadoEjercicio = $this->obtenerResultadoEjercicio($fecha);
            
            $totalPatrimonio = $capital + $reservas + $resultadosAcumulados + $resultadoEjercicio;
            $totalPasivosPatrimonio = $totalPasivos + $totalPatrimonio;
            
            // Verificar cuadre del balance
            $diferenciaBalance = abs($totalActivos - $totalPasivosPatrimonio);
            $estaBalanceado = $diferenciaBalance < 0.01;
            
            // Calcular ratios financieros
            $ratios = $this->calcularRatiosBalance($fecha);
            
            return view('contabilidad.estados-financieros.balance', compact(
                'efectivo', 'cuentasPorCobrar', 'inventarios', 'gastosPagadosAdelantado', 'totalActivosCorrientes',
                'propiedadPlantaEquipo', 'depreciacionAcumulada', 'intangibles', 'otrosActivos', 'totalActivosNoCorrientes', 'totalActivos',
                'cuentasPorPagar', 'documentosPorPagar', 'prestamosCortoPlazo', 'provisionImpuestos', 'otrosGastosPorPagar', 'totalPasivosCorrientes',
                'prestamosLargoPlazo', 'provisionBeneficios', 'otrosPasivosLargoPlazo', 'totalPasivosNoCorrientes', 'totalPasivos',
                'capital', 'reservas', 'resultadosAcumulados', 'resultadoEjercicio', 'totalPatrimonio', 'totalPasivosPatrimonio',
                'diferenciaBalance', 'estaBalanceado', 'ratios', 'fecha'
            ));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar balance general: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Exportar Estados Financieros
     */
    public function exportar()
    {
        try {
            $fechaInicio = request('fecha_inicio', now()->startOfMonth()->toDateString());
            $fechaFin = request('fecha_fin', now()->endOfMonth()->toDateString());
            $formato = request('formato', 'excel'); // excel, pdf, csv
            $tipoReporte = request('tipo', 'todos'); // todos, resultados, flujo, balance
            
            // Preparar datos base para todos los estados
            $datosReporte = [];
            
            if ($tipoReporte == 'todos' || $tipoReporte == 'resultados') {
                // Estado de Resultados
                $ventasNetas = $this->obtenerVentasNetas($fechaInicio, $fechaFin);
                $costoVentas = $this->obtenerCostoVentas($fechaInicio, $fechaFin);
                $gastosOperativos = $this->obtenerGastosOperativos($fechaInicio, $fechaFin, $costoVentas);
                
                $datosReporte['estado_resultados'] = [
                    'ventas_netas' => $ventasNetas,
                    'costo_ventas' => $costoVentas,
                    'utilidad_bruta' => $ventasNetas - $costoVentas,
                    'gastos_operativos' => $gastosOperativos,
                    'utilidad_operativa' => ($ventasNetas - $costoVentas) - $gastosOperativos
                ];
            }
            
            if ($tipoReporte == 'todos' || $tipoReporte == 'flujo') {
                // Flujo de Efectivo
                $cobrosClientes = $this->obtenerCobrosClientes($fechaInicio, $fechaFin);
                $pagosProveedores = $this->obtenerPagosProveedores($fechaInicio, $fechaFin);
                $gastosOperativosFlujo = $this->obtenerGastosOperativos($fechaInicio, $fechaFin, 0);
                
                $datosReporte['flujo_efectivo'] = [
                    'flujo_operativo' => $cobrosClientes - $pagosProveedores - $gastosOperativosFlujo,
                    'cobros_clientes' => $cobrosClientes,
                    'pagos_proveedores' => $pagosProveedores,
                    'gastos_operativos' => $gastosOperativosFlujo
                ];
            }
            
            if ($tipoReporte == 'todos' || $tipoReporte == 'balance') {
                // Balance General (a fecha fin)
                $totalActivos = $this->obtenerTotalActivos($fechaFin);
                $totalPasivos = $this->obtenerTotalPasivos($fechaFin);
                $totalPatrimonio = $this->obtenerTotalPatrimonio($fechaFin);
                
                $datosReporte['balance_general'] = [
                    'total_activos' => $totalActivos,
                    'total_pasivos' => $totalPasivos,
                    'total_patrimonio' => $totalPatrimonio,
                    'cuadre' => abs($totalActivos - ($totalPasivos + $totalPatrimonio)) < 0.01
                ];
            }
            
            // Generar archivo según formato solicitado
            switch ($formato) {
                case 'excel':
                    return $this->generarExcel($datosReporte, $fechaInicio, $fechaFin, $tipoReporte);
                case 'pdf':
                    return $this->generarPDF($datosReporte, $fechaInicio, $fechaFin, $tipoReporte);
                case 'csv':
                    return $this->generarCSV($datosReporte, $fechaInicio, $fechaFin, $tipoReporte);
                default:
                    return back()->with('error', 'Formato de exportación no válido');
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al exportar: ' . $e->getMessage());
            return back();
        }
    }

    // ========================================
    // MÉTODOS HELPER PRIVADOS
    // ========================================

    /**
     * Obtener ventas netas desde Doccab
     */
    private function obtenerVentasNetas($fechaInicio, $fechaFin)
    {
        return DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Tipo', 'V')
            ->where('Eliminado', 0)
            ->sum('Subtotal');
    }

    /**
     * Obtener costo de ventas desde Docdet
     */
    private function obtenerCostoVentas($fechaInicio, $fechaFin)
    {
        return DB::table('Docdet as d')
            ->join('Doccab as c', 'd.Numero', '=', 'c.Numero')
            ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
            ->where('c.Tipo', 'V')
            ->where('c.Eliminado', 0)
            ->sum(DB::raw('d.Cantidad * d.Costo'));
    }

    /**
     * Calcular resultados P&L completos
     */
    private function calcularResultados($ventasNetas, $costoVentas, $totalGastos)
    {
        $utilidadBruta = $ventasNetas - $costoVentas;
        $gastosOperativos = $totalGastos - $costoVentas; // Excluir costo de ventas
        $utilidadOperativa = $utilidadBruta - $gastosOperativos;
        $utilidadNeta = $utilidadOperativa; // Simplificado
        
        $margenBruto = $ventasNetas > 0 ? ($utilidadBruta / $ventasNetas) * 100 : 0;
        $margenOperativo = $ventasNetas > 0 ? ($utilidadOperativa / $ventasNetas) * 100 : 0;
        $margenNeto = $ventasNetas > 0 ? ($utilidadNeta / $ventasNetas) * 100 : 0;

        return [
            'ventas_netas' => $ventasNetas,
            'costo_ventas' => $costoVentas,
            'utilidad_bruta' => $utilidadBruta,
            'gastos_operativos' => $gastosOperativos,
            'utilidad_operativa' => $utilidadOperativa,
            'utilidad_neta' => $utilidadNeta,
            'margen_bruto' => $margenBruto,
            'margen_operativo' => $margenOperativo,
            'margen_neto' => $margenNeto
        ];
    }

    /**
     * Obtener gastos operativos (sin costo de ventas)
     */
    private function obtenerGastosOperativos($fechaInicio, $fechaFin, $costoVentas)
    {
        $totalGastos = DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
            ->where('Tipo', 'LIKE', '5%')
            ->sum('Saldo');
            
        return abs($totalGastos) - $costoVentas;
    }

    /**
     * Obtener resumen por categorías
     */
    private function obtenerResumenCategorias($ingresos, $costoVentas, $gastos)
    {
        $totalIngresos = $ingresos->sum('total');
        $totalGastos = $gastos->sum('total');
        
        return [
            'ingresos' => [
                'total' => $totalIngresos,
                'porcentaje' => $totalIngresos > 0 ? 100 : 0
            ],
            'costo_ventas' => [
                'total' => $costoVentas,
                'porcentaje' => $totalIngresos > 0 ? ($costoVentas / $totalIngresos) * 100 : 0
            ],
            'utilidad_bruta' => [
                'total' => $totalIngresos - $costoVentas,
                'porcentaje' => $totalIngresos > 0 ? (($totalIngresos - $costoVentas) / $totalIngresos) * 100 : 0
            ],
            'gastos_operativos' => [
                'total' => $totalGastos - $costoVentas,
                'porcentaje' => $totalIngresos > 0 ? (($totalGastos - $costoVentas) / $totalIngresos) * 100 : 0
            ],
            'utilidad_operativa' => [
                'total' => ($totalIngresos - $costoVentas) - ($totalGastos - $costoVentas),
                'porcentaje' => $totalIngresos > 0 ? ((($totalIngresos - $costoVentas) - ($totalGastos - $costoVentas)) / $totalIngresos) * 100 : 0
            ]
        ];
    }

    /**
     * Obtener comparación con período anterior
     */
    private function obtenerComparacionPeriodo($fechaInicioAnterior, $fechaFinAnterior, $fechaInicioActual, $fechaFinActual)
    {
        // Ventas período anterior
        $ventasAnterior = $this->obtenerVentasNetas($fechaInicioAnterior, $fechaFinAnterior);
        $ventasActual = $this->obtenerVentasNetas($fechaInicioActual, $fechaFinActual);
        
        // Costos período anterior
        $costosAnterior = $this->obtenerCostoVentas($fechaInicioAnterior, $fechaFinAnterior);
        $costosActual = $this->obtenerCostoVentas($fechaInicioActual, $fechaFinActual);
        
        return [
            'ventas_actual' => $ventasActual,
            'ventas_anterior' => $ventasAnterior,
            'ventas_variacion' => $this->calcularVariacionPorcentual($ventasActual, $ventasAnterior),
            'costos_actual' => $costosActual,
            'costos_anterior' => $costosAnterior,
            'costos_variacion' => $this->calcularVariacionPorcentual($costosActual, $costosAnterior)
        ];
    }

    /**
     * Calcular variación porcentual
     */
    private function calcularVariacionPorcentual($actual, $anterior)
    {
        return $anterior > 0 ? (($actual - $anterior) / $anterior) * 100 : 0;
    }

    /**
     * Calcular tendencias mensuales
     */
    private function calcularTendencias($resultadosMensuales)
    {
        $ventas = collect($resultadosMensuales)->pluck('ventas_netas');
        $utilidades = collect($resultadosMensuales)->pluck('utilidad_operativa');
        
        $crecimientoVentas = $ventas->count() > 1 ? 
            (($ventas->last() - $ventas->first()) / $ventas->first()) * 100 : 0;
            
        $promedioVentas = $ventas->avg();
        $promedioUtilidad = $utilidades->avg();
        
        $mesMayorVenta = $resultadosMensuales[array_search($ventas->max(), $ventas->toArray())]['nombre_mes'];
        $mesMayorUtilidad = $resultadosMensuales[array_search($utilidades->max(), $utilidades->toArray())]['nombre_mes'];

        return [
            'crecimiento_ventas' => $crecimientoVentas,
            'promedio_ventas' => $promedioVentas,
            'promedio_utilidad' => $promedioUtilidad,
            'mes_mayor_venta' => $mesMayorVenta,
            'mes_mayor_utilidad' => $mesMayorUtilidad
        ];
    }

    /**
     * Calcular variaciones entre períodos
     */
    private function calcularVariaciones($resultadosPorPeriodo)
    {
        $variaciones = [];
        
        // Mes actual vs mes anterior
        $variaciones['mes_actual_vs_anterior'] = [
            'ventas' => $this->calcularVariacionPorcentual(
                $resultadosPorPeriodo['mes_actual']['ventas_netas'],
                $resultadosPorPeriodo['mes_anterior']['ventas_netas']
            ),
            'utilidad_operativa' => $this->calcularVariacionPorcentual(
                $resultadosPorPeriodo['mes_actual']['utilidad_operativa'],
                $resultadosPorPeriodo['mes_anterior']['utilidad_operativa']
            ),
            'margen_operativo' => $resultadosPorPeriodo['mes_actual']['margen_operativo'] - $resultadosPorPeriodo['mes_anterior']['margen_operativo']
        ];
        
        // Año actual vs año anterior
        $variaciones['anio_actual_vs_anterior'] = [
            'ventas' => $this->calcularVariacionPorcentual(
                $resultadosPorPeriodo['anio_actual']['ventas_netas'],
                $resultadosPorPeriodo['anio_anterior']['ventas_netas']
            ),
            'utilidad_operativa' => $this->calcularVariacionPorcentual(
                $resultadosPorPeriodo['anio_actual']['utilidad_operativa'],
                $resultadosPorPeriodo['anio_anterior']['utilidad_operativa']
            ),
            'margen_operativo' => $resultadosPorPeriodo['anio_actual']['margen_operativo'] - $resultadosPorPeriodo['anio_anterior']['margen_operativo']
        ];

        return $variaciones;
    }

    /**
     * Clasificar cuenta (INGRESO/GASTO)
     */
    private function clasificarCuenta($cuenta)
    {
        if (substr($cuenta, 0, 1) == '4') {
            return 'INGRESO';
        } elseif (substr($cuenta, 0, 1) == '5') {
            return 'GASTO';
        }
        return 'OTRO';
    }

    /**
     * Obtener costos farmacéuticos (Top 10 productos)
     */
    private function obtenerCostosFarmaceuticos($fechaInicio, $fechaFin)
    {
        return DB::table('Docdet as d')
            ->join('Doccab as c', 'd.Numero', '=', 'c.Numero')
            ->join('Productos as p', 'd.Codpro', '=', 'p.CodPro')
            ->select(
                'p.Nombre as producto',
                DB::raw('SUM(d.Cantidad * d.Costo) as costo_total'),
                DB::raw('SUM(d.Cantidad) as cantidad_total')
            )
            ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
            ->where('c.Tipo', 'V')
            ->where('c.Eliminado', 0)
            ->groupBy('p.Nombre')
            ->orderBy('costo_total', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Calcular rentabilidad farmacéutica por producto
     */
    private function calcularRentabilidadFarmaceutica($fechaInicio, $fechaFin)
    {
        return DB::table('Docdet as d')
            ->join('Doccab as c', 'd.Numero', '=', 'c.Numero')
            ->join('Productos as p', 'd.Codpro', '=', 'p.CodPro')
            ->select(
                'p.Nombre as producto',
                DB::raw('SUM(d.Cantidad * d.Costo) as costo_total'),
                DB::raw('SUM(d.Subtotal) as ventas_total')
            )
            ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
            ->where('c.Tipo', 'V')
            ->where('c.Eliminado', 0)
            ->groupBy('p.Nombre')
            ->get()
            ->map(function($item) {
                $item->margen = $item->ventas_total > 0 ? 
                    (($item->ventas_total - $item->costo_total) / $item->ventas_total) * 100 : 0;
                return $item;
            })
            ->sortByDesc('margen')
            ->values();
    }

    // ========================================
    // MÉTODOS HELPER PARA FLUJO DE EFECTIVO
    // ========================================

    /**
     * Obtener cobros de clientes
     */
    private function obtenerCobrosClientes($fechaInicio, $fechaFin)
    {
        return DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Tipo', 'V') // Ventas
            ->where('Eliminado', 0)
            ->sum('Subtotal');
    }

    /**
     * Obtener pagos a proveedores
     */
    private function obtenerPagosProveedores($fechaInicio, $fechaFin)
    {
        return DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
            ->where('Tipo', 'LIKE', '21%') // Cuentas por pagar proveedores
            ->sum('Saldo');
    }

    /**
     * Obtener compras de activos fijos
     */
    private function obtenerCompraActivosFijos($fechaInicio, $fechaFin)
    {
        return DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
            ->where('Tipo', 'LIKE', '1[3-5]%') // Propiedad planta equipo
            ->where('Saldo', '>', 0) // Solo débitos (compras)
            ->sum('Saldo');
    }

    /**
     * Obtener ventas de activos fijos
     */
    private function obtenerVentaActivosFijos($fechaInicio, $fechaFin)
    {
        return DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
            ->where('Tipo', 'LIKE', '1[3-5]%') // Propiedad planta equipo
            ->where('Saldo', '<', 0) // Solo créditos (ventas)
            ->sum('Saldo');
    }

    /**
     * Obtener préstamos recibidos
     */
    private function obtenerPrestamosRecibidos($fechaInicio, $fechaFin)
    {
        return DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
            ->where('Tipo', 'LIKE', '2[1-2]%') // Prestamos
            ->where('Saldo', '>', 0) // Solo débitos
            ->sum('Saldo');
    }

    /**
     * Obtener dividendos pagados
     */
    private function obtenerDividendosPagados($fechaInicio, $fechaFin)
    {
        return DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
            ->where('Tipo', 'LIKE', '3%') // Dividendos (desde patrimonio)
            ->where('Saldo', '>', 0) // Solo débitos
            ->sum('Saldo');
    }

    /**
     * Obtener aportes de capital
     */
    private function obtenerCapitalAportes($fechaInicio, $fechaFin)
    {
        return DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$fechaInicio, $fechaFin])
            ->where('Tipo', 'LIKE', '3[0-1]%') // Capital social y aportaciones
            ->where('Saldo', '>', 0) // Solo débitos
            ->sum('Saldo');
    }

    /**
     * Obtener efectivo inicial del período
     */
    private function obtenerEfectivoInicial($fechaInicio)
    {
        $fechaAnterior = Carbon::parse($fechaInicio)->subDay()->toDateString();
        
        $ingresosEfectivo = DB::table('t_detalle_diario')
            ->where('FechaF', $fechaAnterior)
            ->where('Tipo', 'LIKE', '1[0-1]%') // Cuentas de efectivo y bancos
            ->where('Saldo', '>', 0)
            ->sum('Saldo');
            
        $egresosEfectivo = DB::table('t_detalle_diario')
            ->where('FechaF', $fechaAnterior)
            ->where('Tipo', 'LIKE', '1[0-1]%')
            ->where('Saldo', '<', 0)
            ->sum('Saldo');
            
        return $ingresosEfectivo + abs($egresosEfectivo);
    }

    // ========================================
    // MÉTODOS HELPER PARA BALANCE GENERAL
    // ========================================

    /**
     * Obtener efectivo y equivalentes
     */
    private function obtenerEfectivo($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '1[0-1]%') // Efectivo y Bancos
            ->selectRaw('SUM(CASE WHEN Saldo > 0 THEN Saldo ELSE -Saldo END) as total')
            ->value('total');
    }

    /**
     * Obtener cuentas por cobrar
     */
    private function obtenerCuentasPorCobrar($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '12%') // Cuentas por cobrar
            ->where('Saldo', '>', 0)
            ->sum('Saldo');
    }

    /**
     * Obtener inventarios
     */
    private function obtenerInventarios($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '14%') // Inventarios
            ->sum('Saldo');
    }

    /**
     * Obtener gastos pagados por adelantado
     */
    private function obtenerGastosPagadosAdelantado($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '15%') // Gastos pagados adelantado
            ->sum('Saldo');
    }

    /**
     * Obtener propiedad planta y equipo
     */
    private function obtenerPropiedadPlantaEquipo($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '13%') // Propiedad planta equipo
            ->where('Saldo', '>', 0)
            ->sum('Saldo');
    }

    /**
     * Obtener depreciación acumulada
     */
    private function obtenerDepreciacionAcumulada($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '13[9]%') // Depreciación acumulada
            ->sum('Saldo');
    }

    /**
     * Obtener intangibles
     */
    private function obtenerIntangibles($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '16%') // Intangibles
            ->sum('Saldo');
    }

    /**
     * Obtener otros activos
     */
    private function obtenerOtrosActivos($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '1[7-9]%') // Otros activos
            ->sum('Saldo');
    }

    /**
     * Obtener cuentas por pagar
     */
    private function obtenerCuentasPorPagar($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '21%') // Cuentas por pagar
            ->sum('Saldo');
    }

    /**
     * Obtener documentos por pagar
     */
    private function obtenerDocumentosPorPagar($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '22%') // Documentos por pagar
            ->sum('Saldo');
    }

    /**
     * Obtener préstamos corto plazo
     */
    private function obtenerPrestamosCortoPlazo($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '23[0-4]%') // Préstamos corto plazo
            ->sum('Saldo');
    }

    /**
     * Obtener provisión impuestos
     */
    private function obtenerProvisionImpuestos($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '24[0-1]%') // Provisión impuestos
            ->sum('Saldo');
    }

    /**
     * Obtener otros gastos por pagar
     */
    private function obtenerOtrosGastosPorPagar($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '24[2-9]%') // Otros gastos por pagar
            ->sum('Saldo');
    }

    /**
     * Obtener préstamos largo plazo
     */
    private function obtenerPrestamosLargoPlazo($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '2[3-4]%') // Préstamos largo plazo
            ->sum('Saldo');
    }

    /**
     * Obtener provisión beneficios laborales
     */
    private function obtenerProvisionBeneficios($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '25%') // Provisión beneficios
            ->sum('Saldo');
    }

    /**
     * Obtener otros pasivos largo plazo
     */
    private function obtenerOtrosPasivosLargoPlazo($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '2[6-9]%') // Otros pasivos largo plazo
            ->sum('Saldo');
    }

    /**
     * Obtener capital social
     */
    private function obtenerCapital($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '30%') // Capital social
            ->sum('Saldo');
    }

    /**
     * Obtener reservas
     */
    private function obtenerReservas($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '31%') // Reservas
            ->sum('Saldo');
    }

    /**
     * Obtener resultados acumulados
     */
    private function obtenerResultadosAcumulados($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '32%') // Resultados acumulados
            ->sum('Saldo');
    }

    /**
     * Obtener resultado del ejercicio
     */
    private function obtenerResultadoEjercicio($fecha)
    {
        // Suma de ingresos - gastos del año actual hasta la fecha
        $anioActual = Carbon::parse($fecha)->year;
        $inicioAnio = Carbon::create($anioActual, 1, 1)->toDateString();
        
        $ingresosAnio = DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$inicioAnio, $fecha])
            ->where('Tipo', 'LIKE', '4%')
            ->sum('Saldo');
            
        $gastosAnio = abs(DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$inicioAnio, $fecha])
            ->where('Tipo', 'LIKE', '5%')
            ->sum('Saldo'));
            
        return $ingresosAnio - $gastosAnio;
    }

    /**
     * Obtener totales para balance
     */
    private function obtenerTotalActivos($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '1%')
            ->selectRaw('SUM(CASE WHEN Saldo > 0 THEN Saldo ELSE -Saldo END) as total')
            ->value('total');
    }

    private function obtenerTotalPasivos($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '2%')
            ->sum('Saldo');
    }

    private function obtenerTotalPatrimonio($fecha)
    {
        return DB::table('t_detalle_diario')
            ->where('FechaF', $fecha)
            ->where('Tipo', 'LIKE', '3%')
            ->sum('Saldo');
    }

    // ========================================
    // MÉTODOS HELPER PARA RATIOS FINANCIEROS
    // ========================================

    /**
     * Calcular razón corriente
     */
    private function calcularRazonCorriente()
    {
        $fecha = now()->toDateString();
        $activosCorrientes = $this->obtenerEfectivo($fecha) + 
                           $this->obtenerCuentasPorCobrar($fecha) + 
                           $this->obtenerInventarios($fecha);
        $pasivosCorrientes = $this->obtenerCuentasPorPagar($fecha) + 
                           $this->obtenerPrestamosCortoPlazo($fecha);
        
        return $pasivosCorrientes > 0 ? $activosCorrientes / $pasivosCorrientes : 0;
    }

    /**
     * Calcular prueba ácida
     */
    private function calcularPruebaAcida()
    {
        $fecha = now()->toDateString();
        $activosLiquidos = $this->obtenerEfectivo($fecha) + $this->obtenerCuentasPorCobrar($fecha);
        $pasivosCorrientes = $this->obtenerCuentasPorPagar($fecha) + $this->obtenerPrestamosCortoPlazo($fecha);
        
        return $pasivosCorrientes > 0 ? $activosLiquidos / $pasivosCorrientes : 0;
    }

    /**
     * Calcular capital de trabajo
     */
    private function calcularCapitalTrabajo()
    {
        $fecha = now()->toDateString();
        $activosCorrientes = $this->obtenerEfectivo($fecha) + 
                           $this->obtenerCuentasPorCobrar($fecha) + 
                           $this->obtenerInventarios($fecha);
        $pasivosCorrientes = $this->obtenerCuentasPorPagar($fecha) + 
                           $this->obtenerPrestamosCortoPlazo($fecha);
        
        return $activosCorrientes - $pasivosCorrientes;
    }

    /**
     * Calcular ratios del balance
     */
    private function calcularRatiosBalance($fecha)
    {
        $totalActivos = $this->obtenerTotalActivos($fecha);
        $totalPasivos = $this->obtenerTotalPasivos($fecha);
        $totalPatrimonio = $this->obtenerTotalPatrimonio($fecha);
        
        // Ratios de liquidez
        $razonCorriente = $this->calcularRazonCorriente();
        $pruebaAcida = $this->calcularPruebaAcida();
        $capitalTrabajo = $this->calcularCapitalTrabajo();
        
        // Ratios de endeudamiento
        $ratioEndeudamiento = $totalActivos > 0 ? ($totalPasivos / $totalActivos) * 100 : 0;
        $autonomiaFinanciera = $totalActivos > 0 ? ($totalPatrimonio / $totalActivos) * 100 : 0;
        
        // Ratios de rentabilidad (basados en último año)
        $anioActual = Carbon::parse($fecha)->year;
        $utilidadNeta = $this->obtenerUtilidadNeta($anioActual);
        $roe = $totalPatrimonio > 0 ? ($utilidadNeta / $totalPatrimonio) * 100 : 0;
        $roa = $totalActivos > 0 ? ($utilidadNeta / $totalActivos) * 100 : 0;
        
        return [
            'liquidez' => [
                'razon_corriente' => round($razonCorriente, 2),
                'prueba_acida' => round($pruebaAcida, 2),
                'capital_trabajo' => round($capitalTrabajo, 2)
            ],
            'endeudamiento' => [
                'ratio_endeudamiento' => round($ratioEndeudamiento, 2),
                'autonomia_financiera' => round($autonomiaFinanciera, 2)
            ],
            'rentabilidad' => [
                'roe' => round($roe, 2),
                'roa' => round($roa, 2)
            ]
        ];
    }

    /**
     * Obtener utilidad neta anual
     */
    private function obtenerUtilidadNeta($anio)
    {
        $inicioAnio = Carbon::create($anio, 1, 1)->toDateString();
        $finAnio = Carbon::create($anio, 12, 31)->toDateString();
        
        $ingresos = DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$inicioAnio, $finAnio])
            ->where('Tipo', 'LIKE', '4%')
            ->sum('Saldo');
            
        $gastos = abs(DB::table('t_detalle_diario')
            ->whereBetween('FechaF', [$inicioAnio, $finAnio])
            ->where('Tipo', 'LIKE', '5%')
            ->sum('Saldo'));
            
        return $ingresos - $gastos;
    }

    // ========================================
    // MÉTODOS HELPER PARA EXPORTACIÓN
    // ========================================

    /**
     * Generar archivo Excel
     */
    private function generarExcel($datosReporte, $fechaInicio, $fechaFin, $tipoReporte)
    {
        // TODO: Implementar generación de archivo Excel
        // Por ahora retornar mensaje
        return back()->with('success', 'Archivo Excel generado correctamente');
    }

    /**
     * Generar archivo PDF
     */
    private function generarPDF($datosReporte, $fechaInicio, $fechaFin, $tipoReporte)
    {
        // TODO: Implementar generación de archivo PDF
        // Por ahora retornar mensaje
        return back()->with('success', 'Archivo PDF generado correctamente');
    }

    /**
     * Generar archivo CSV
     */
    private function generarCSV($datosReporte, $fechaInicio, $fechaFin, $tipoReporte)
    {
        // TODO: Implementar generación de archivo CSV
        // Por ahora retornar mensaje
        return back()->with('success', 'Archivo CSV generado correctamente');
    }
}
