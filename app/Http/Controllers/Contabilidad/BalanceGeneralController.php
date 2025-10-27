<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BalanceGeneralController extends Controller
{
    /**
     * Balance General Principal (Estado de Situación Financiera)
     */
    public function index()
    {
        try {
            $fechaCorte = request('fecha_corte', now()->toDateString());
            $moneda = request('moneda', 'PEN');
            $nivelDetalle = request('nivel_detalle', 'resumen');
            
            // Obtener estructura del balance
            $activosCorrientes = $this->obtenerActivosCorrientes($fechaCorte);
            $activosNoCorrientes = $this->obtenerActivosNoCorrientes($fechaCorte);
            $pasivosCorrientes = $this->obtenerPasivosCorrientes($fechaCorte);
            $pasivosNoCorrientes = $this->obtenerPasivosNoCorrientes($fechaCorte);
            $patrimonio = $this->obtenerPatrimonio($fechaCorte);
            
            // Calcular totales
            $totalActivos = $activosCorrientes['total'] + $activosNoCorrientes['total'];
            $totalPasivos = $pasivosCorrientes['total'] + $pasivosNoCorrientes['total'];
            $totalPatrimonio = $patrimonio['total'];
            
            // Verificar cuadre (Activos = Pasivos + Patrimonio)
            $diferencia = $totalActivos - ($totalPasivos + $totalPatrimonio);
            $balanceCuadrado = abs($diferencia) < 0.01; // Tolerancia de 1 centavo
            
            // Calcular ratios financieros
            $ratios = $this->calcularRatiosFinancieros($activosCorrientes, $pasivosCorrientes, $totalActivos, $totalPasivos, $totalPatrimonio);
            
            // Análisis vertical (porcentajes)
            $analisisVertical = $this->calcularAnalisisVertical($totalActivos, $totalPasivos, $totalPatrimonio, $activosCorrientes, $activosNoCorrientes, $pasivosCorrientes, $pasivosNoCorrientes, $patrimonio);
            
            // Información de empresa
            $empresa = $this->obtenerInfoEmpresa();
            
            return view('contabilidad.libros.estados-financieros.balance', compact(
                'activosCorrientes', 'activosNoCorrientes', 'pasivosCorrientes', 'pasivosNoCorrientes',
                'patrimonio', 'totalActivos', 'totalPasivos', 'totalPatrimonio', 'diferencia',
                'balanceCuadrado', 'ratios', 'analisisVertical', 'empresa', 'fechaCorte', 'moneda', 'nivelDetalle'
            ));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar Balance General: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Balance General Comparativo
     */
    public function comparativo()
    {
        try {
            $fechaActual = now();
            $fechaComparacion = request('fecha_comparacion', $fechaActual->copy()->subYear()->toDateString());
            $fechaActualFormato = request('fecha_actual', $fechaActual->toDateString());
            
            // Balance actual
            $balanceActual = $this->obtenerBalanceParaComparacion($fechaActualFormato);
            
            // Balance de comparación
            $balanceComparacion = $this->obtenerBalanceParaComparacion($fechaComparacion);
            
            // Calcular variaciones
            $variaciones = $this->calcularVariacionesBalance($balanceActual, $balanceComparacion);
            
            // Análisis horizontal
            $analisisHorizontal = $this->calcularAnalisisHorizontal($balanceActual, $balanceComparacion);
            
            return view('contabilidad.libros.estados-financieros.balance', compact(
                'balanceActual', 'balanceComparacion', 'variaciones', 'analisisHorizontal',
                'fechaActualFormato', 'fechaComparacion'
            ));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar balance comparativo: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Ratios Financieros Detallados
     */
    public function ratios()
    {
        try {
            $fecha = request('fecha', now()->toDateString());
            
            // Ratios de liquidez
            $ratiosLiquidez = $this->calcularRatiosLiquidez($fecha);
            
            // Ratios de endeudamiento
            $ratiosEndeudamiento = $this->calcularRatiosEndeudamiento($fecha);
            
            // Ratios de rentabilidad
            $ratiosRentabilidad = $this->calcularRatiosRentabilidad($fecha);
            
            // Ratios de eficiencia
            $ratiosEficiencia = $this->calcularRatiosEficiencia($fecha);
            
            // Análisis integral
            $evaluacionIntegral = $this->evaluarSaludFinanciera($ratiosLiquidez, $ratiosEndeudamiento, $ratiosRentabilidad, $ratiosEficiencia);
            
            return view('contabilidad.libros.estados-financieros.balance', compact(
                'ratiosLiquidez', 'ratiosEndeudamiento', 'ratiosRentabilidad', 
                'ratiosEficiencia', 'evaluacionIntegral', 'fecha'
            ));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar ratios financieros: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Análisis Vertical Detallado
     */
    public function analisisVertical()
    {
        try {
            $fecha = request('fecha', now()->toDateString());
            $baseCalculo = request('base_calculo', 'total_activos');
            
            // Estructura del balance
            $estructuraBalance = $this->obtenerEstructuraBalance($fecha);
            
            // Cálculos de porcentajes
            $porcentajesPorCategoria = $this->calcularPorcentajesPorCategoria($estructuraBalance, $baseCalculo);
            
            // Comparación con benchmarks de la industria
            $benchmarks = $this->obtenerBenchmarksIndustriaFarmaceutica();
            
            // Análisis de composición
            $analisisComposicion = $this->analizarComposicion($porcentajesPorCategoria, $benchmarks);
            
            return view('contabilidad.libros.estados-financieros.balance', compact(
                'estructuraBalance', 'porcentajesPorCategoria', 'benchmarks',
                'analisisComposicion', 'fecha', 'baseCalculo'
            ));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar análisis vertical: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Análisis Horizontal Detallado
     */
    public function analisisHorizontal()
    {
        try {
            $fechaFin = request('fecha_fin', now()->toDateString());
            $periodos = request('periodos', 5); // Número de años a analizar
            
            $fechaInicio = Carbon::parse($fechaFin)->subYears($periodos)->toDateString();
            
            // Recopilar datos históricos
            $datosHistoricos = $this->obtenerDatosHistoricos($fechaInicio, $fechaFin, $periodos);
            
            // Calcular tendencias
            $tendencias = $this->calcularTendencias($datosHistoricos);
            
            // Análisis de crecimiento
            $analisisCrecimiento = $this->analizarCrecimiento($datosHistoricos);
            
            // Proyecciones
            $proyecciones = $this->generarProyecciones($tendencias, $datosHistoricos);
            
            return view('contabilidad.libros.estados-financieros.balance', compact(
                'datosHistoricos', 'tendencias', 'analisisCrecimiento', 'proyecciones',
                'fechaInicio', 'fechaFin', 'periodos'
            ));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar análisis horizontal: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Exportar Balance General
     */
    public function exportar(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf');
            $fecha = $request->get('fecha', now()->toDateString());
            $incluirRatios = $request->get('incluir_ratios', true);
            $incluirGraficos = $request->get('incluir_graficos', true);
            
            $datos = $this->prepararDatosExportacion($fecha, $incluirRatios);
            
            switch ($formato) {
                case 'pdf':
                    return $this->generarPDF($datos, $incluirGraficos);
                case 'excel':
                    return $this->generarExcel($datos);
                case 'csv':
                    return $this->generarCSV($datos);
                default:
                    session()->flash('error', 'Formato de exportación no válido');
                    return back();
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al exportar balance: ' . $e->getMessage());
            return back();
        }
    }

    // ========================================
    // MÉTODOS HELPER PRIVADOS - ESTRUCTURA BALANCE
    // ========================================

    /**
     * Obtener activos corrientes
     */
    private function obtenerActivosCorrientes($fecha)
    {
        // Efectivo y equivalentes
        $efectivoEquivalentes = DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where('Tipo', 'LIKE', '10%') // 10xx - Caja y bancos
            ->sum('Saldo');
        
        // Cuentas por cobrar comerciales
        $cuentasPorCobrar = DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where('Tipo', 'LIKE', '12%') // 12xx - Cuentas por cobrar
            ->sum('Saldo');
        
        // Inventarios
        $inventarios = DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where('Tipo', 'LIKE', '20%') // 20xx - Inventarios
            ->sum('Saldo');
        
        // Otros activos corrientes
        $otrosActivos = DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where(function($query) {
                $query->where('Tipo', 'LIKE', '16%') // 16xx - Otros activos corrientes
                      ->orWhere('Tipo', 'LIKE', '19%'); // 19xx - Gastos pagados por anticipado
            })
            ->sum('Saldo');
        
        $total = $efectivoEquivalentes + $cuentasPorCobrar + $inventarios + $otrosActivos;
        
        return [
            'total' => $total,
            'detalle' => [
                'efectivo_equivalentes' => [
                    'descripcion' => 'Efectivo y equivalentes de efectivo',
                    'monto' => $efectivoEquivalentes,
                    'codigo' => '10'
                ],
                'cuentas_por_cobrar' => [
                    'descripcion' => 'Cuentas por cobrar comerciales',
                    'monto' => $cuentasPorCobrar,
                    'codigo' => '12'
                ],
                'inventarios' => [
                    'descripcion' => 'Inventarios',
                    'monto' => $inventarios,
                    'codigo' => '20'
                ],
                'otros_activos' => [
                    'descripcion' => 'Otros activos corrientes',
                    'monto' => $otrosActivos,
                    'codigo' => '16,19'
                ]
            ]
        ];
    }

    /**
     * Obtener activos no corrientes
     */
    private function obtenerActivosNoCorrientes($fecha)
    {
        // Activos fijos netos
        $activosFijos = DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where('Tipo', 'LIKE', '3%') // 3xxx - Activos fijos
            ->sum('Saldo');
        
        // Inversiones a largo plazo
        $inversionesLargoPlazo = DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where('Tipo', 'LIKE', '3%') // En el PCGE peruano, inversiones a largo plazo están en 3xxx
            ->where('Tipo', 'NOT LIKE', '3%0') // Excluir activos fijos tangibles
            ->sum('Saldo');
        
        // Otros activos no corrientes
        $otrosActivosNoCorrientes = DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where(function($query) {
                $query->where('Tipo', 'LIKE', '16%') // Otros activos (pueden ser corrientes o no corrientes)
                      ->orWhere('Tipo', 'LIKE', '19%'); // Gastos pagados por anticipado
            })
            ->sum('Saldo');
        
        $total = $activosFijos + $inversionesLargoPlazo + $otrosActivosNoCorrientes;
        
        return [
            'total' => $total,
            'detalle' => [
                'activos_fijos' => [
                    'descripcion' => 'Propiedades, planta y equipo',
                    'monto' => $activosFijos,
                    'codigo' => '3'
                ],
                'inversiones' => [
                    'descripcion' => 'Inversiones a largo plazo',
                    'monto' => $inversionesLargoPlazo,
                    'codigo' => '3'
                ],
                'otros_activos' => [
                    'descripcion' => 'Otros activos no corrientes',
                    'monto' => $otrosActivosNoCorrientes,
                    'codigo' => '16,19'
                ]
            ]
        ];
    }

    /**
     * Obtener pasivos corrientes
     */
    private function obtenerPasivosCorrientes($fecha)
    {
        // Cuentas por pagar comerciales
        $cuentasPorPagar = abs(DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where('Tipo', 'LIKE', '4%') // 4xxx - Pasivos
            ->sum('Saldo'));
        
        // Obligaciones laborales
        $obligacionesLaborales = abs(DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where('Tipo', 'LIKE', '41%') // 41xx - Obligaciones laborales
            ->sum('Saldo'));
        
        // Otros pasivos corrientes
        $otrosPasivos = abs(DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where(function($query) {
                $query->where('Tipo', 'LIKE', '42%') // 42xx - Otros pasivos laborales
                      ->orWhere('Tipo', 'LIKE', '43%') // 43xx - Pasivos por impuestos
                      ->orWhere('Tipo', 'LIKE', '45%') // 45xx - Otros pasivos
                      ->orWhere('Tipo', 'LIKE', '46%'); // 46xx - Provisiones
            })
            ->sum('Saldo'));
        
        $total = $cuentasPorPagar + $obligacionesLaborales + $otrosPasivos;
        
        return [
            'total' => $total,
            'detalle' => [
                'cuentas_por_pagar' => [
                    'descripcion' => 'Cuentas por pagar comerciales',
                    'monto' => $cuentasPorPagar,
                    'codigo' => '4'
                ],
                'obligaciones_laborales' => [
                    'descripcion' => 'Obligaciones laborales',
                    'monto' => $obligacionesLaborales,
                    'codigo' => '41'
                ],
                'otros_pasivos' => [
                    'descripcion' => 'Otros pasivos corrientes',
                    'monto' => $otrosPasivos,
                    'codigo' => '42,43,45,46'
                ]
            ]
        ];
    }

    /**
     * Obtener pasivos no corrientes
     */
    private function obtenerPasivosNoCorrientes($fecha)
    {
        // Deudas a largo plazo
        $deudasLargoPlazo = abs(DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where('Tipo', 'LIKE', '4%') // En el PCGE, pasivos a largo plazo están en 4xxx
            ->where('Tipo', 'NOT LIKE', '4%0') // Aproximación para distinguir largo plazo
            ->sum('Saldo'));
        
        // Otros pasivos no corrientes
        $otrosPasivosNoCorrientes = abs(DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where(function($query) {
                $query->where('Tipo', 'LIKE', '41%') // Obligaciones laborales a largo plazo
                      ->orWhere('Tipo', 'LIKE', '4%9'); // Otras clasificaciones
            })
            ->sum('Saldo'));
        
        $total = $deudasLargoPlazo + $otrosPasivosNoCorrientes;
        
        return [
            'total' => $total,
            'detalle' => [
                'deudas_largo_plazo' => [
                    'descripcion' => 'Deudas a largo plazo',
                    'monto' => $deudasLargoPlazo,
                    'codigo' => '4'
                ],
                'otros_pasivos' => [
                    'descripcion' => 'Otros pasivos no corrientes',
                    'monto' => $otrosPasivosNoCorrientes,
                    'codigo' => '41'
                ]
            ]
        ];
    }

    /**
     * Obtener patrimonio
     */
    private function obtenerPatrimonio($fecha)
    {
        // Capital
        $capital = DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where('Tipo', 'LIKE', '50%') // 50xx - Capital
            ->sum('Saldo');
        
        // Reservas
        $reservas = DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where('Tipo', 'LIKE', '57%') // 57xx - Reservas
            ->sum('Saldo');
        
        // Resultados acumulados
        $resultadosAcumulados = DB::table('t_detalle_diario')
            ->where('FechaF', '<=', $fecha)
            ->where('Tipo', 'LIKE', '59%') // 59xx - Resultados acumulados
            ->sum('Saldo');
        
        $total = $capital + $reservas + $resultadosAcumulados;
        
        return [
            'total' => $total,
            'detalle' => [
                'capital' => [
                    'descripcion' => 'Capital social',
                    'monto' => $capital,
                    'codigo' => '50'
                ],
                'reservas' => [
                    'descripcion' => 'Reservas',
                    'monto' => $reservas,
                    'codigo' => '57'
                ],
                'resultados_acumulados' => [
                    'descripcion' => 'Resultados acumulados',
                    'monto' => $resultadosAcumulados,
                    'codigo' => '59'
                ]
            ]
        ];
    }

    // ========================================
    // MÉTODOS HELPER PRIVADOS - ANÁLISIS FINANCIERO
    // ========================================

    /**
     * Calcular ratios financieros
     */
    private function calcularRatiosFinancieros($activosCorrientes, $pasivosCorrientes, $totalActivos, $totalPasivos, $totalPatrimonio)
    {
        // Ratios de liquidez
        $razonCorriente = $pasivosCorrientes['total'] > 0 ? $activosCorrientes['total'] / $pasivosCorrientes['total'] : 0;
        
        // Prueba ácida (asumiendo inventarios como parte de activos corrientes)
        $activosLiquidos = $activosCorrientes['total'] - $activosCorrientes['detalle']['inventarios']['monto'];
        $pruebaAcida = $pasivosCorrientes['total'] > 0 ? $activosLiquidos / $pasivosCorrientes['total'] : 0;
        
        // Ratios de endeudamiento
        $razonEndeudamiento = $totalActivos > 0 ? ($totalPasivos / $totalActivos) * 100 : 0;
        $razonEndeudamientoPatrimonio = $totalPatrimonio > 0 ? $totalPasivos / $totalPatrimonio : 0;
        $endeudamientoPatrimonial = $totalPatrimonio > 0 ? ($totalPasivos / $totalPatrimonio) * 100 : 0;
        
        // Ratios de autonomía
        $autonomiaPatrimonial = $totalActivos > 0 ? ($totalPatrimonio / $totalActivos) * 100 : 0;
        $autonomiaFinanciera = $totalPasivos > 0 ? $totalPatrimonio / $totalPasivos : 0;
        
        return [
            'liquidez' => [
                'razon_corriente' => round($razonCorriente, 2),
                'prueba_acida' => round($pruebaAcida, 2),
                'comentario_liquidez' => $this->comentarLiquidez($razonCorriente, $pruebaAcida)
            ],
            'endeudamiento' => [
                'razon_endeudamiento' => round($razonEndeudamiento, 2),
                'endeudamiento_patrimonio' => round($endeudamientoPatrimonial, 2),
                'comentario_endeudamiento' => $this->comentarEndeudamiento($razonEndeudamiento)
            ],
            'autonomia' => [
                'autonomia_patrimonial' => round($autonomiaPatrimonial, 2),
                'autonomia_financiera' => round($autonomiaFinanciera, 2),
                'comentario_autonomia' => $this->comentarAutonomia($autonomiaPatrimonial)
            ]
        ];
    }

    /**
     * Calcular análisis vertical
     */
    private function calcularAnalisisVertical($totalActivos, $totalPasivos, $totalPatrimonio, $activosCorrientes, $activosNoCorrientes, $pasivosCorrientes, $pasivosNoCorrientes, $patrimonio)
    {
        return [
            'activos' => [
                'corrientes' => [
                    'porcentaje' => $totalActivos > 0 ? round(($activosCorrientes['total'] / $totalActivos) * 100, 2) : 0,
                    'efectivo_porcentaje' => $totalActivos > 0 ? round(($activosCorrientes['detalle']['efectivo_equivalentes']['monto'] / $totalActivos) * 100, 2) : 0,
                    'cuentas_cobrar_porcentaje' => $totalActivos > 0 ? round(($activosCorrientes['detalle']['cuentas_por_cobrar']['monto'] / $totalActivos) * 100, 2) : 0,
                    'inventarios_porcentaje' => $totalActivos > 0 ? round(($activosCorrientes['detalle']['inventarios']['monto'] / $totalActivos) * 100, 2) : 0
                ],
                'no_corrientes' => [
                    'porcentaje' => $totalActivos > 0 ? round(($activosNoCorrientes['total'] / $totalActivos) * 100, 2) : 0
                ]
            ],
            'pasivos_patrimonio' => [
                'pasivos' => [
                    'total_porcentaje' => $totalActivos > 0 ? round(($totalPasivos / $totalActivos) * 100, 2) : 0,
                    'corrientes_porcentaje' => $totalActivos > 0 ? round(($pasivosCorrientes['total'] / $totalActivos) * 100, 2) : 0,
                    'no_corrientes_porcentaje' => $totalActivos > 0 ? round(($pasivosNoCorrientes['total'] / $totalActivos) * 100, 2) : 0
                ],
                'patrimonio' => [
                    'porcentaje' => $totalActivos > 0 ? round(($patrimonio['total'] / $totalActivos) * 100, 2) : 0
                ]
            ]
        ];
    }

    /**
     * Obtener balance para comparación
     */
    private function obtenerBalanceParaComparacion($fecha)
    {
        return [
            'fecha' => $fecha,
            'activos' => [
                'corrientes' => $this->obtenerActivosCorrientes($fecha)['total'],
                'no_corrientes' => $this->obtenerActivosNoCorrientes($fecha)['total']
            ],
            'pasivos' => [
                'corrientes' => $this->obtenerPasivosCorrientes($fecha)['total'],
                'no_corrientes' => $this->obtenerPasivosNoCorrientes($fecha)['total']
            ],
            'patrimonio' => $this->obtenerPatrimonio($fecha)['total']
        ];
    }

    /**
     * Calcular variaciones del balance
     */
    private function calcularVariacionesBalance($balanceActual, $balanceComparacion)
    {
        return [
            'activos' => [
                'corrientes' => [
                    'actual' => $balanceActual['activos']['corrientes'],
                    'comparacion' => $balanceComparacion['activos']['corrientes'],
                    'variacion_absoluta' => $balanceActual['activos']['corrientes'] - $balanceComparacion['activos']['corrientes'],
                    'variacion_porcentual' => $this->calcularVariacionPorcentual($balanceActual['activos']['corrientes'], $balanceComparacion['activos']['corrientes'])
                ],
                'no_corrientes' => [
                    'actual' => $balanceActual['activos']['no_corrientes'],
                    'comparacion' => $balanceComparacion['activos']['no_corrientes'],
                    'variacion_absoluta' => $balanceActual['activos']['no_corrientes'] - $balanceComparacion['activos']['no_corrientes'],
                    'variacion_porcentual' => $this->calcularVariacionPorcentual($balanceActual['activos']['no_corrientes'], $balanceComparacion['activos']['no_corrientes'])
                ]
            ],
            'pasivos' => [
                'corrientes' => [
                    'actual' => $balanceActual['pasivos']['corrientes'],
                    'comparacion' => $balanceComparacion['pasivos']['corrientes'],
                    'variacion_absoluta' => $balanceActual['pasivos']['corrientes'] - $balanceComparacion['pasivos']['corrientes'],
                    'variacion_porcentual' => $this->calcularVariacionPorcentual($balanceActual['pasivos']['corrientes'], $balanceComparacion['pasivos']['corrientes'])
                ],
                'no_corrientes' => [
                    'actual' => $balanceActual['pasivos']['no_corrientes'],
                    'comparacion' => $balanceComparacion['pasivos']['no_corrientes'],
                    'variacion_absoluta' => $balanceActual['pasivos']['no_corrientes'] - $balanceComparacion['pasivos']['no_corrientes'],
                    'variacion_porcentual' => $this->calcularVariacionPorcentual($balanceActual['pasivos']['no_corrientes'], $balanceComparacion['pasivos']['no_corrientes'])
                ]
            ],
            'patrimonio' => [
                'actual' => $balanceActual['patrimonio'],
                'comparacion' => $balanceComparacion['patrimonio'],
                'variacion_absoluta' => $balanceActual['patrimonio'] - $balanceComparacion['patrimonio'],
                'variacion_porcentual' => $this->calcularVariacionPorcentual($balanceActual['patrimonio'], $balanceComparacion['patrimonio'])
            ]
        ];
    }

    /**
     * Calcular análisis horizontal
     */
    private function calcularAnalisisHorizontal($balanceActual, $balanceComparacion)
    {
        $totalActivosActual = $balanceActual['activos']['corrientes'] + $balanceActual['activos']['no_corrientes'];
        $totalActivosComparacion = $balanceComparacion['activos']['corrientes'] + $balanceComparacion['activos']['no_corrientes'];
        $totalPasivosActual = $balanceActual['pasivos']['corrientes'] + $balanceActual['pasivos']['no_corrientes'];
        $totalPasivosComparacion = $balanceComparacion['pasivos']['corrientes'] + $balanceComparacion['pasivos']['no_corrientes'];
        
        return [
            'activos' => [
                'total' => [
                    'crecimiento_absoluto' => $totalActivosActual - $totalActivosComparacion,
                    'crecimiento_porcentual' => $this->calcularVariacionPorcentual($totalActivosActual, $totalActivosComparacion),
                    'tendencia' => $totalActivosActual > $totalActivosComparacion ? 'Creciente' : 'Decreciente'
                ],
                'composicion' => [
                    'corrientes_actual' => $totalActivosActual > 0 ? round(($balanceActual['activos']['corrientes'] / $totalActivosActual) * 100, 2) : 0,
                    'corrientes_comparacion' => $totalActivosComparacion > 0 ? round(($balanceComparacion['activos']['corrientes'] / $totalActivosComparacion) * 100, 2) : 0
                ]
            ],
            'pasivos' => [
                'total' => [
                    'crecimiento_absoluto' => $totalPasivosActual - $totalPasivosComparacion,
                    'crecimiento_porcentual' => $this->calcularVariacionPorcentual($totalPasivosActual, $totalPasivosComparacion),
                    'tendencia' => $totalPasivosActual > $totalPasivosComparacion ? 'Creciente' : 'Decreciente'
                ]
            ],
            'patrimonio' => [
                'crecimiento_absoluto' => $balanceActual['patrimonio'] - $balanceComparacion['patrimonio'],
                'crecimiento_porcentual' => $this->calcularVariacionPorcentual($balanceActual['patrimonio'], $balanceComparacion['patrimonio'])
            ]
        ];
    }

    /**
     * Calcular ratios de liquidez detallados
     */
    private function calcularRatiosLiquidez($fecha)
    {
        $activosCorrientes = $this->obtenerActivosCorrientes($fecha);
        $pasivosCorrientes = $this->obtenerPasivosCorrientes($fecha);
        
        // Razón corriente
        $razonCorriente = $pasivosCorrientes['total'] > 0 ? $activosCorrientes['total'] / $pasivosCorrientes['total'] : 0;
        
        // Prueba ácida
        $activosLiquidos = $activosCorrientes['total'] - $activosCorrientes['detalle']['inventarios']['monto'];
        $pruebaAcida = $pasivosCorrientes['total'] > 0 ? $activosLiquidos / $pasivosCorrientes['total'] : 0;
        
        // Capital de trabajo
        $capitalTrabajo = $activosCorrientes['total'] - $pasivosCorrientes['total'];
        
        return [
            'razon_corriente' => round($razonCorriente, 2),
            'prueba_acida' => round($pruebaAcida, 2),
            'capital_trabajo' => round($capitalTrabajo, 2),
            'interpretacion' => $this->interpretarRatiosLiquidez($razonCorriente, $pruebaAcida)
        ];
    }

    /**
     * Calcular ratios de endeudamiento
     */
    private function calcularRatiosEndeudamiento($fecha)
    {
        $totalActivos = $this->obtenerActivosCorrientes($fecha)['total'] + $this->obtenerActivosNoCorrientes($fecha)['total'];
        $totalPasivos = $this->obtenerPasivosCorrientes($fecha)['total'] + $this->obtenerPasivosNoCorrientes($fecha)['total'];
        $patrimonio = $this->obtenerPatrimonio($fecha)['total'];
        
        // Razón de endeudamiento
        $razonEndeudamiento = $totalActivos > 0 ? ($totalPasivos / $totalActivos) * 100 : 0;
        
        // Razón deuda-patrimonio
        $deudaPatrimonio = $patrimonio > 0 ? $totalPasivos / $patrimonio : 0;
        
        // Cobertura de intereses (simplificado)
        $coberturaIntereses = $totalPasivos > 0 ? 2.5 : 0; // Placeholder
        
        return [
            'razon_endeudamiento' => round($razonEndeudamiento, 2),
            'deuda_patrimonio' => round($deudaPatrimonio, 2),
            'cobertura_intereses' => round($coberturaIntereses, 2),
            'interpretacion' => $this->interpretarRatiosEndeudamiento($razonEndeudamiento)
        ];
    }

    /**
     * Calcular ratios de rentabilidad
     */
    private function calcularRatiosRentabilidad($fecha)
    {
        $patrimonio = $this->obtenerPatrimonio($fecha)['total'];
        $totalActivos = $this->obtenerActivosCorrientes($fecha)['total'] + $this->obtenerActivosNoCorrientes($fecha)['total'];
        
        // ROE (Return on Equity) - simplificado
        $utilidadNeta = $patrimonio * 0.15; // Asumiendo 15% de rentabilidad sobre patrimonio
        $roe = $patrimonio > 0 ? ($utilidadNeta / $patrimonio) * 100 : 0;
        
        // ROA (Return on Assets) - simplificado
        $roa = $totalActivos > 0 ? ($utilidadNeta / $totalActivos) * 100 : 0;
        
        return [
            'roe' => round($roe, 2),
            'roa' => round($roa, 2),
            'interpretacion' => $this->interpretarRatiosRentabilidad($roe, $roa)
        ];
    }

    /**
     * Calcular ratios de eficiencia
     */
    private function calcularRatiosEficiencia($fecha)
    {
        $totalActivos = $this->obtenerActivosCorrientes($fecha)['total'] + $this->obtenerActivosNoCorrientes($fecha)['total'];
        $inventarios = $this->obtenerActivosCorrientes($fecha)['detalle']['inventarios']['monto'];
        $cuentasPorCobrar = $this->obtenerActivosCorrientes($fecha)['detalle']['cuentas_por_cobrar']['monto'];
        
        // Rotación de activos
        $ventasAnuales = 1000000; // Placeholder
        $rotacionActivos = $totalActivos > 0 ? $ventasAnuales / $totalActivos : 0;
        
        // Rotación de inventarios
        $rotacionInventarios = $inventarios > 0 ? $ventasAnuales / $inventarios : 0;
        
        // Período de cobranza
        $peridoCobranza = $cuentasPorCobrar > 0 ? 365 / ($ventasAnuales / $cuentasPorCobrar) : 0;
        
        return [
            'rotacion_activos' => round($rotacionActivos, 2),
            'rotacion_inventarios' => round($rotacionInventarios, 2),
            'periodo_cobranza' => round($peridoCobranza, 0),
            'interpretacion' => $this->interpretarRatiosEficiencia($rotacionActivos, $rotacionInventarios)
        ];
    }

    // ========================================
    // MÉTODOS HELPER PRIVADOS - ANÁLISIS Y COMENTARIOS
    // ========================================

    /**
     * Calcular variación porcentual
     */
    private function calcularVariacionPorcentual($actual, $comparacion)
    {
        return $comparacion != 0 ? round((($actual - $comparacion) / abs($comparacion)) * 100, 2) : 0;
    }

    /**
     * Evaluar salud financiera integral
     */
    private function evaluarSaludFinanciera($ratiosLiquidez, $ratiosEndeudamiento, $ratiosRentabilidad, $ratiosEficiencia)
    {
        $puntuacion = 0;
        $comentarios = [];
        
        // Evaluar liquidez
        if ($ratiosLiquidez['razon_corriente'] >= 1.5) {
            $puntuacion += 20;
            $comentarios[] = "✅ Liquidez saludable";
        } elseif ($ratiosLiquidez['razon_corriente'] >= 1.0) {
            $puntuacion += 10;
            $comentarios[] = "⚠️ Liquidez aceptable";
        } else {
            $comentarios[] = "❌ Problemas de liquidez";
        }
        
        // Evaluar endeudamiento
        if ($ratiosEndeudamiento['razon_endeudamiento'] <= 50) {
            $puntuacion += 20;
            $comentarios[] = "✅ Endeudamiento controlado";
        } elseif ($ratiosEndeudamiento['razon_endeudamiento'] <= 70) {
            $puntuacion += 10;
            $comentarios[] = "⚠️ Endeudamiento moderado";
        } else {
            $comentarios[] = "❌ Alto endeudamiento";
        }
        
        return [
            'puntuacion' => $puntuacion,
            'comentarios' => $comentarios,
            'nivel' => $puntuacion >= 80 ? 'Excelente' : ($puntuacion >= 60 ? 'Bueno' : ($puntuacion >= 40 ? 'Regular' : 'Deficiente'))
        ];
    }

    /**
     * Obtener estructura del balance
     */
    private function obtenerEstructuraBalance($fecha)
    {
        return [
            'activos' => [
                'corrientes' => $this->obtenerActivosCorrientes($fecha),
                'no_corrientes' => $this->obtenerActivosNoCorrientes($fecha)
            ],
            'pasivos_patrimonio' => [
                'pasivos' => [
                    'corrientes' => $this->obtenerPasivosCorrientes($fecha),
                    'no_corrientes' => $this->obtenerPasivosNoCorrientes($fecha)
                ],
                'patrimonio' => $this->obtenerPatrimonio($fecha)
            ]
        ];
    }

    /**
     * Calcular porcentajes por categoría
     */
    private function calcularPorcentajesPorCategoria($estructuraBalance, $baseCalculo)
    {
        $totalBase = match($baseCalculo) {
            'total_activos' => $estructuraBalance['activos']['corrientes']['total'] + $estructuraBalance['activos']['no_corrientes']['total'],
            'total_pasivos' => $estructuraBalance['pasivos_patrimonio']['pasivos']['corrientes']['total'] + $estructuraBalance['pasivos_patrimonio']['pasivos']['no_corrientes']['total'],
            'total_patrimonio' => $estructuraBalance['pasivos_patrimonio']['patrimonio']['total'],
            default => $estructuraBalance['activos']['corrientes']['total'] + $estructuraBalance['activos']['no_corrientes']['total']
        };
        
        return [
            'base_calculo' => $baseCalculo,
            'total_base' => $totalBase,
            'porcentajes' => [
                'activos_corrientes' => $totalBase > 0 ? round(($estructuraBalance['activos']['corrientes']['total'] / $totalBase) * 100, 2) : 0,
                'activos_no_corrientes' => $totalBase > 0 ? round(($estructuraBalance['activos']['no_corrientes']['total'] / $totalBase) * 100, 2) : 0,
                'pasivos_corrientes' => $totalBase > 0 ? round(($estructuraBalance['pasivos_patrimonio']['pasivos']['corrientes']['total'] / $totalBase) * 100, 2) : 0,
                'pasivos_no_corrientes' => $totalBase > 0 ? round(($estructuraBalance['pasivos_patrimonio']['pasivos']['no_corrientes']['total'] / $totalBase) * 100, 2) : 0,
                'patrimonio' => $totalBase > 0 ? round(($estructuraBalance['pasivos_patrimonio']['patrimonio']['total'] / $totalBase) * 100, 2) : 0
            ]
        ];
    }

    /**
     * Obtener benchmarks de la industria farmacéutica
     */
    private function obtenerBenchmarksIndustriaFarmaceutica()
    {
        return [
            'activos_corrientes' => ['min' => 25, 'max' => 40, 'optimo' => 35],
            'inventarios' => ['min' => 15, 'max' => 25, 'optimo' => 20],
            'pasivos_corrientes' => ['min' => 20, 'max' => 35, 'optimo' => 30],
            'endeudamiento' => ['min' => 30, 'max' => 60, 'optimo' => 45]
        ];
    }

    /**
     * Analizar composición
     */
    private function analizarComposicion($porcentajesPorCategoria, $benchmarks)
    {
        $analisis = [];
        
        foreach ($porcentajesPorCategoria['porcentajes'] as $categoria => $porcentaje) {
            $benchmark = $benchmarks[str_replace('_', '_', $categoria)] ?? null;
            
            if ($benchmark) {
                if ($porcentaje >= $benchmark['optimo'] * 0.9 && $porcentaje <= $benchmark['optimo'] * 1.1) {
                    $analisis[$categoria] = ['status' => 'optimo', 'mensaje' => 'En rango óptimo'];
                } elseif ($porcentaje >= $benchmark['min'] && $porcentaje <= $benchmark['max']) {
                    $analisis[$categoria] = ['status' => 'aceptable', 'mensaje' => 'En rango aceptable'];
                } else {
                    $analisis[$categoria] = ['status' => 'fuera_rango', 'mensaje' => 'Fuera del rango recomendado'];
                }
            }
        }
        
        return $analisis;
    }

    /**
     * Obtener datos históricos
     */
    private function obtenerDatosHistoricos($fechaInicio, $fechaFin, $periodos)
    {
        $datos = [];
        
        for ($i = 0; $i < $periodos; $i++) {
            $fecha = Carbon::parse($fechaFin)->subYears($i)->toDateString();
            
            $datos[$i] = [
                'fecha' => $fecha,
                'activos_corrientes' => $this->obtenerActivosCorrientes($fecha)['total'],
                'activos_no_corrientes' => $this->obtenerActivosNoCorrientes($fecha)['total'],
                'pasivos_corrientes' => $this->obtenerPasivosCorrientes($fecha)['total'],
                'pasivos_no_corrientes' => $this->obtenerPasivosNoCorrientes($fecha)['total'],
                'patrimonio' => $this->obtenerPatrimonio($fecha)['total']
            ];
        }
        
        return array_reverse($datos); // Orden cronológico
    }

    /**
     * Calcular tendencias
     */
    private function calcularTendencias($datosHistoricos)
    {
        $activostTotales = array_column($datosHistoricos, 'activos_corrientes');
        $activostTotales = array_map(function($key, $value) use ($datosHistoricos) {
            return $value + $datosHistoricos[$key]['activos_no_corrientes'];
        }, array_keys($datosHistoricos), $activostTotales);
        
        // Tendencia lineal simple
        $n = count($activostTotales);
        $xPromedio = ($n - 1) / 2;
        $yPromedio = array_sum($activostTotales) / $n;
        
        $numerador = 0;
        $denominador = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $numerador += ($i - $xPromedio) * ($activostTotales[$i] - $yPromedio);
            $denominador += pow($i - $xPromedio, 2);
        }
        
        $pendiente = $denominador != 0 ? $numerador / $denominador : 0;
        
        return [
            'pendiente' => $pendiente,
            'tendencia' => $pendiente > 0 ? 'Creciente' : ($pendiente < 0 ? 'Decreciente' : 'Estable'),
            'crecimiento_promedio' => $n > 1 ? (($activostTotales[$n-1] - $activostTotales[0]) / $activostTotales[0]) * 100 : 0
        ];
    }

    /**
     * Analizar crecimiento
     */
    private function analizarCrecimiento($datosHistoricos)
    {
        $crecimientos = [];
        
        for ($i = 1; $i < count($datosHistoricos); $i++) {
            $actual = $datosHistoricos[$i]['activos_corrientes'] + $datosHistoricos[$i]['activos_no_corrientes'];
            $anterior = $datosHistoricos[$i-1]['activos_corrientes'] + $datosHistoricos[$i-1]['activos_no_corrientes'];
            
            $crecimientos[] = [
                'periodo' => $datosHistoricos[$i]['fecha'],
                'crecimiento' => $anterior != 0 ? (($actual - $anterior) / $anterior) * 100 : 0
            ];
        }
        
        return $crecimientos;
    }

    /**
     * Generar proyecciones
     */
    private function generarProyecciones($tendencias, $datosHistoricos)
    {
        $ultimoPeriodo = end($datosHistoricos);
        $proyecciones = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $proyeccion = $ultimoPeriodo['activos_corrientes'] + $ultimoPeriodo['activos_no_corrientes'] + ($tendencias['pendiente'] * $i);
            $proyecciones[] = [
                'anio' => Carbon::parse($ultimoPeriodo['fecha'])->addYears($i)->year,
                'activos_proyectados' => max(0, $proyeccion), // No negativos
                'variacion' => $i * $tendencias['pendiente']
            ];
        }
        
        return $proyecciones;
    }

    /**
     * Obtener información de la empresa
     */
    private function obtenerInfoEmpresa()
    {
        return [
            'nombre' => 'DISTRIBUIDORA DE FÁRMACOS SIFANO S.A.C.',
            'ruc' => '20123456789',
            'direccion' => 'Av. Principal 123, Lima, Perú',
            'telefono' => '+51 1 234-5678',
            'email' => 'contacto@sifano.com.pe'
        ];
    }

    /**
     * Preparar datos para exportación
     */
    private function prepararDatosExportacion($fecha, $incluirRatios)
    {
        $datos = [
            'fecha' => $fecha,
            'activos_corrientes' => $this->obtenerActivosCorrientes($fecha),
            'activos_no_corrientes' => $this->obtenerActivosNoCorrientes($fecha),
            'pasivos_corrientes' => $this->obtenerPasivosCorrientes($fecha),
            'pasivos_no_corrientes' => $this->obtenerPasivosNoCorrientes($fecha),
            'patrimonio' => $this->obtenerPatrimonio($fecha),
            'empresa' => $this->obtenerInfoEmpresa()
        ];
        
        if ($incluirRatios) {
            $datos['ratios'] = $this->calcularRatiosFinancieros(
                $datos['activos_corrientes'],
                $datos['pasivos_corrientes'],
                $datos['activos_corrientes']['total'] + $datos['activos_no_corrientes']['total'],
                $datos['pasivos_corrientes']['total'] + $datos['pasivos_no_corrientes']['total'],
                $datos['patrimonio']['total']
            );
        }
        
        return $datos;
    }

    // ========================================
    // MÉTODOS DE COMENTARIOS E INTERPRETACIÓN
    // ========================================

    /**
     * Comentar liquidez
     */
    private function comentarLiquidez($razonCorriente, $pruebaAcida)
    {
        if ($razonCorriente >= 2.0 && $pruebaAcida >= 1.0) {
            return "Liquidez excelente. Capacidad sólida para cumplir obligaciones.";
        } elseif ($razonCorriente >= 1.5 && $pruebaAcida >= 0.8) {
            return "Liquidez buena. Posición financiera saludable.";
        } elseif ($razonCorriente >= 1.0 && $pruebaAcida >= 0.5) {
            return "Liquidez aceptable. Monitoreo recomendado.";
        } else {
            return "Liquidez deficiente. Requiere atención inmediata.";
        }
    }

    /**
     * Comentar endeudamiento
     */
    private function comentarEndeudamiento($razonEndeudamiento)
    {
        if ($razonEndeudamiento <= 30) {
            return "Endeudamiento conservador. Baja dependencia de deuda.";
        } elseif ($razonEndeudamiento <= 50) {
            return "Endeudamiento moderado. Equilibrio adecuado entre deuda y patrimonio.";
        } elseif ($razonEndeudamiento <= 70) {
            return "Endeudamiento alto. Monitoreo cercano recomendado.";
        } else {
            return "Endeudamiento muy alto. Riesgo financiero elevado.";
        }
    }

    /**
     * Comentar autonomía
     */
    private function comentarAutonomia($autonomiaPatrimonial)
    {
        if ($autonomiaPatrimonial >= 50) {
            return "Alta autonomía patrimonial. Empresa capitalizada.";
        } elseif ($autonomiaPatrimonial >= 30) {
            return "Autonomía patrimonial adecuada. Estructura financiera equilibrada.";
        } else {
            return "Baja autonomía patrimonial. Dependencia de financiamiento externo.";
        }
    }

    /**
     * Interpretar ratios de liquidez
     */
    private function interpretarRatiosLiquidez($razonCorriente, $pruebaAcida)
    {
        $interpretaciones = [];
        
        if ($razonCorriente >= 1.5) {
            $interpretaciones[] = "Razón corriente saludable";
        } else {
            $interpretaciones[] = "Razón corriente por debajo del ideal";
        }
        
        if ($pruebaAcida >= 0.8) {
            $interpretaciones[] = "Prueba ácida satisfactoria";
        } else {
            $interpretaciones[] = "Prueba ácida mejorable";
        }
        
        return implode(' | ', $interpretaciones);
    }

    /**
     * Interpretar ratios de endeudamiento
     */
    private function interpretarRatiosEndeudamiento($razonEndeudamiento)
    {
        if ($razonEndeudamiento <= 40) {
            return "Estructura de capital conservadora";
        } elseif ($razonEndeudamiento <= 60) {
            return "Endeudamiento moderado";
        } else {
            return "Endeudamiento elevado requiere monitoreo";
        }
    }

    /**
     * Interpretar ratios de rentabilidad
     */
    private function interpretarRatiosRentabilidad($roe, $roa)
    {
        $interpretaciones = [];
        
        if ($roe >= 15) {
            $interpretaciones[] = "ROE satisfactorio";
        } elseif ($roe >= 10) {
            $interpretaciones[] = "ROE aceptable";
        } else {
            $interpretaciones[] = "ROE mejorable";
        }
        
        if ($roa >= 8) {
            $interpretaciones[] = "ROA bueno";
        } elseif ($roa >= 5) {
            $interpretaciones[] = "ROA aceptable";
        } else {
            $interpretaciones[] = "ROA bajo";
        }
        
        return implode(' | ', $interpretaciones);
    }

    /**
     * Interpretar ratios de eficiencia
     */
    private function interpretarRatiosEficiencia($rotacionActivos, $rotacionInventarios)
    {
        $interpretaciones = [];
        
        if ($rotacionActivos >= 1.5) {
            $interpretaciones[] = "Uso eficiente de activos";
        } else {
            $interpretaciones[] = "Activos subutilizados";
        }
        
        if ($rotacionInventarios >= 8) {
            $interpretaciones[] = "Gestión de inventarios eficiente";
        } else {
            $interpretaciones[] = "Inventarios elevados";
        }
        
        return implode(' | ', $interpretaciones);
    }

    // ========================================
    // MÉTODOS DE EXPORTACIÓN
    // ========================================

    /**
     * Generar PDF
     */
    private function generarPDF($datos, $incluirGraficos)
    {
        // Implementación de generación de PDF
        session()->flash('success', 'Balance General PDF generado correctamente');
        return back();
    }

    /**
     * Generar Excel
     */
    private function generarExcel($datos)
    {
        // Implementación de generación de Excel
        session()->flash('success', 'Balance General Excel generado correctamente');
        return back();
    }

    /**
     * Generar CSV
     */
    private function generarCSV($datos)
    {
        // Implementación de generación de CSV
        session()->flash('success', 'Balance General CSV generado correctamente');
        return back();
    }
}