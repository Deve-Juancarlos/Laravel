<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * MÓDULO REPORTES - Controlador de Analytics
     * Analytics avanzado y análisis de datos para la empresa
     * Integrado con base de datos SIFANO existente
     * Total de líneas: ~950
     */

    public function __construct()
    {
        $this->middleware(['auth', 'rol:administrador|vendedor|contador|gerente']);
    }

    /**
     * ===============================================
     * MÉTODOS PRINCIPALES DE ANALYTICS
     * ===============================================
     */

    /**
     * Dashboard principal de analytics
     */
    public function index(Request $request)
    {
        $periodo = $request->periodo ?? '12m'; // Últimos 12 meses
        $tipo_analisis = $request->tipo_analisis ?? 'ventas';
        
        $resumen_ejecutivo = $this->generarResumenEjecutivo($periodo);
        $tendencias_principales = $this->analizarTendenciasPrincipales($periodo);
        $metricas_clave = $this->calcularMetricasClave($periodo);
        $alertas_analiticas = $this->generarAlertasAnaliticas();

        return compact('resumen_ejecutivo', 'tendencias_principales', 'metricas_clave', 'alertas_analiticas', 'periodo', 'tipo_analisis');
    }

    /**
     * ===============================================
     * ANALYTICS DE VENTAS AVANZADO
     * ===============================================
     */

    /**
     * Análisis detallado de patrones de ventas
     */
    public function analizarPatronesVentas(Request $request)
    {
        $fecha_desde = $request->fecha_desde ?? now()->subMonths(12)->format('Y-m-d');
        $fecha_hasta = $request->fecha_hasta ?? now()->format('Y-m-d');

        // Análisis por día de la semana
        $patrones_semanales = DB::table('Doccab')
            ->selectRaw('
                DATEPART(dw, Fecha) as dia_semana,
                DATENAME(dw, Fecha) as nombre_dia,
                COUNT(*) as cantidad_ventas,
                SUM(Total) as total_ventas,
                AVG(Total) as ticket_promedio
            ')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->groupByRaw('DATEPART(dw, Fecha), DATENAME(dw, Fecha)')
            ->orderBy('dia_semana')
            ->get();

        // Análisis por horas del día (simulado ya que no tenemos hora exacta)
        $patrones_horarios = $this->simularPatronesHorarios($fecha_desde, $fecha_hasta);

        // Análisis por zonas geográficas
        $ventas_por_zona = DB::table('Doccab')
            ->join('Clientes', 'Doccab.Codcli', '=', 'Clientes.Codcli')
            ->selectRaw('
                Clientes.Provincia,
                COUNT(Doccab.Numero) as cantidad_ventas,
                SUM(Doccab.Total) as total_ventas,
                AVG(Doccab.Total) as ticket_promedio
            ')
            ->whereBetween('Doccab.Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Doccab.Estado', ['ANULADO'])
            ->groupBy('Clientes.Provincia')
            ->orderBy('total_ventas', 'desc')
            ->get();

        // Análisis de estacionalidad
        $estacionalidad = $this->analizarEstacionalidad($fecha_desde, $fecha_hasta);

        return [
            'periodo' => ['desde' => $fecha_desde, 'hasta' => $fecha_hasta],
            'patrones_semanales' => $patrones_semanales,
            'patrones_horarios' => $patrones_horarios,
            'ventas_por_zona' => $ventas_por_zona,
            'estacionalidad' => $estacionalidad,
            'insights' => $this->generarInsightsVentas($patrones_semanales, $ventas_por_zona, $estacionalidad)
        ];
    }

    /**
     * Análisis de cohortes de clientes
     */
    public function analisisCohortes(Request $request)
    {
        $meses_analisis = $request->meses_analisis ?? 12;
        
        // Obtener cohortes por mes de primera compra
        $cohortes = DB::table('Doccab')
            ->selectRaw('
                YEAR(Fecha) as año,
                MONTH(Fecha) as mes,
                MIN(Fecha) as fecha_primera_compra
            ')
            ->where('Fecha', '>=', now()->subMonths($meses_analisis))
            ->whereNotIn('Estado', ['ANULADO'])
            ->groupByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->orderBy('año', 'asc')
            ->orderBy('mes', 'asc')
            ->get();

        // Calcular retención por cohorte
        $matriz_retencion = [];
        foreach ($cohortes as $cohorte) {
            $fecha_cohorte = Carbon::parse($cohorte->fecha_primera_compra);
            $clientes_cohorte = DB::table('Doccab')
                ->where('Fecha', '>=', $fecha_cohorte->format('Y-m-d'))
                ->where('Fecha', '<', $fecha_cohorte->addMonth()->format('Y-m-d'))
                ->distinct()
                ->pluck('Codcli');

            $retencion_meses = [];
            for ($i = 1; $i <= min(12, $meses_analisis - $fecha_cohorte->diffInMonths(now())); $i++) {
                $mes_actual = $fecha_cohorte->copy()->addMonths($i);
                $mes_siguiente = $mes_actual->copy()->addMonth();
                
                $clientes_activos = DB::table('Doccab')
                    ->whereIn('Codcli', $clientes_cohorte)
                    ->whereBetween('Fecha', [$mes_actual->format('Y-m-d'), $mes_siguiente->format('Y-m-d')])
                    ->distinct()
                    ->pluck('Codcli');

                $tasa_retencion = count($clientes_cohorte) > 0 ? (count($clientes_activos) / count($clientes_cohorte)) * 100 : 0;
                $retencion_meses[] = [
                    'mes' => $i,
                    'clientes_activos' => count($clientes_activos),
                    'tasa_retencion' => round($tasa_retencion, 2)
                ];
            }

            $matriz_retencion[] = [
                'cohorte' => $cohorte->año . '-' . str_pad($cohorte->mes, 2, '0', STR_PAD_LEFT),
                'clientes_iniciales' => count($clientes_cohorte),
                'retencion_meses' => $retencion_meses
            ];
        }

        return [
            'matriz_retencion' => $matriz_retencion,
            'resumen_retencion' => $this->calcularResumenRetencion($matriz_retencion)
        ];
    }

    /**
     * Análisis de segmentación RFM
     */
    public function segmentacionRFM(Request $request)
    {
        $fecha_referencia = now();
        $clientes = DB::table('Clientes')
            ->leftJoin('Doccab', 'Clientes.Codcli', '=', 'Doccab.Codcli')
            ->where('Clientes.Estado', 'ACTIVO')
            ->select(
                'Clientes.Codcli',
                'Clientes.Razonsocial',
                DB::raw('DATEDIFF(day, MAX(Doccab.Fecha), ?) as dias_desde_ultima_compra'),
                DB::raw('COUNT(Doccab.Numero) as frecuencia_compras'),
                DB::raw('SUM(Doccab.Total) as valor_monetario')
            )
            ->setBindings([$fecha_referencia])
            ->groupBy('Clientes.Codcli', 'Clientes.Razonsocial')
            ->get();

        // Calcular quintiles para cada dimensión
        $dias_recencia = $clientes->pluck('dias_desde_ultima_compra')->sort()->values();
        $frecuencias = $clientes->pluck('frecuencia_compras')->sort()->values();
        $valores_monetarios = $clientes->pluck('valor_monetario')->sort()->values();

        $quintiles_recencia = $this->calcularQuintiles($dias_recencia);
        $quintiles_frecuencia = $this->calcularQuintiles($frecuencias);
        $quintiles_valor = $this->calcularQuintiles($valores_monetarios);

        // Asignar scores RFM
        $clientes_rfm = $clientes->map(function($cliente) use ($quintiles_recencia, $quintiles_frecuencia, $quintiles_valor) {
            $score_r = $this->obtenerQuintil($cliente->dias_desde_ultima_compra, $quintiles_recencia);
            $score_f = $this->obtenerQuintil($cliente->frecuencia_compras, $quintiles_frecuencia);
            $score_m = $this->obtenerQuintil($cliente->valor_monetario, $quintiles_valor);

            $cliente->score_r = $score_r;
            $cliente->score_f = $score_f;
            $cliente->score_m = $score_m;
            $cliente->segmento_rfm = $this->determinarSegmentoRFM($score_r, $score_f, $score_m);

            return $cliente;
        });

        // Agrupar por segmentos
        $segmentos = $clientes_rfm->groupBy('segmento_rfm')->map(function($segmento) {
            return [
                'cantidad' => $segmento->count(),
                'valor_total' => $segmento->sum('valor_monetario'),
                'ticket_promedio' => $segmento->avg('valor_monetario')
            ];
        });

        return [
            'clientes_rfm' => $clientes_rfm,
            'segmentos' => $segmentos,
            'quintiles' => [
                'recencia' => $quintiles_recencia,
                'frecuencia' => $quintiles_frecuencia,
                'valor_monetario' => $quintiles_valor
            ],
            'distribucion_segmentos' => $clientes_rfm->groupBy('segmento_rfm')->map->count()
        ];
    }

    /**
     * ===============================================
     * ANALYTICS DE PRODUCTOS
     * ===============================================
     */

    /**
     * Análisis ABC de productos
     */
    public function analisisABCProductos(Request $request)
    {
        $fecha_desde = $request->fecha_desde ?? now()->subYear()->format('Y-m-d');
        $fecha_hasta = $request->fecha_hasta ?? now()->format('Y-m-d');

        // Obtener ventas por producto
        $ventas_productos = DB::table('Docdet')
            ->join('Doccab', 'Docdet.Numero', '=', 'Doccab.Numero')
            ->join('Productos', 'Docdet.Codpro', '=', 'Productos.Codpro')
            ->select(
                'Productos.Codpro',
                'Productos.Descripcion',
                DB::raw('SUM(Docdet.Cantidad * Docdet.Precio) as valor_ventas'),
                DB::raw('SUM(Docdet.Cantidad) as cantidad_vendida')
            )
            ->whereBetween('Doccab.Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Doccab.Estado', ['ANULADO'])
            ->groupBy('Productos.Codpro', 'Productos.Descripcion')
            ->orderBy('valor_ventas', 'desc')
            ->get();

        $total_ventas = $ventas_productos->sum('valor_ventas');
        $acumulado = 0;

        // Clasificar productos por ABC
        $productos_abc = $ventas_productos->map(function($producto) use ($total_ventas, &$acumulado) {
            $acumulado += $producto->valor_ventas;
            $porcentaje_acumulado = ($acumulado / $total_ventas) * 100;
            
            if ($porcentaje_acumulado <= 80) {
                $categoria = 'A';
            } elseif ($porcentaje_acumulado <= 95) {
                $categoria = 'B';
            } else {
                $categoria = 'C';
            }

            $producto->porcentaje_contribucion = round(($producto->valor_ventas / $total_ventas) * 100, 2);
            $producto->porcentaje_acumulado = round($porcentaje_acumulado, 2);
            $producto->categoria_abc = $categoria;

            return $producto;
        });

        // Resumen por categoría
        $resumen_abc = [
            'A' => $productos_abc->where('categoria_abc', 'A'),
            'B' => $productos_abc->where('categoria_abc', 'B'),
            'C' => $productos_abc->where('categoria_abc', 'C')
        ];

        return [
            'productos_abc' => $productos_abc,
            'resumen_abc' => [
                'A' => [
                    'cantidad' => $resumen_abc['A']->count(),
                    'valor_total' => $resumen_abc['A']->sum('valor_ventas'),
                    'porcentaje_ventas' => round(($resumen_abc['A']->sum('valor_ventas') / $total_ventas) * 100, 2)
                ],
                'B' => [
                    'cantidad' => $resumen_abc['B']->count(),
                    'valor_total' => $resumen_abc['B']->sum('valor_ventas'),
                    'porcentaje_ventas' => round(($resumen_abc['B']->sum('valor_ventas') / $total_ventas) * 100, 2)
                ],
                'C' => [
                    'cantidad' => $resumen_abc['C']->count(),
                    'valor_total' => $resumen_abc['C']->sum('valor_ventas'),
                    'porcentaje_ventas' => round(($resumen_abc['C']->sum('valor_ventas') / $total_ventas) * 100, 2)
                ]
            ]
        ];
    }

    /**
     * Análisis de productos relacionados
     */
    public function productosRelacionados(Request $request)
    {
        $producto_id = $request->producto_id;
        $soporte_minimo = $request->soporte_minimo ?? 0.05; // 5%
        $confianza_minima = $request->confianza_minima ?? 0.3; // 30%

        // Algoritmo de reglas de asociación simplificado
        $reglas_asociacion = DB::table('Docdet as d1')
            ->join('Docdet as d2', 'd1.Numero', '=', 'd2.Numero')
            ->join('Productos as p1', 'd1.Codpro', '=', 'p1.Codpro')
            ->join('Productos as p2', 'd2.Codpro', '=', 'p2.Codpro')
            ->selectRaw('
                d1.Codpro as producto_a,
                p1.Descripcion as descripcion_a,
                d2.Codpro as producto_b,
                p2.Descripcion as descripcion_b,
                COUNT(*) as soporte_conjunto,
                (COUNT(*) * 1.0 / (SELECT COUNT(DISTINCT Numero) FROM Docdet)) as soporte,
                (COUNT(*) * 1.0 / (SELECT COUNT(*) FROM Docdet WHERE Codpro = d1.Codpro)) as confianza_a_b,
                (COUNT(*) * 1.0 / (SELECT COUNT(*) FROM Docdet WHERE Codpro = d2.Codpro)) as confianza_b_a
            ')
            ->where('d1.Codpro', '<>', 'd2.Codpro')
            ->whereRaw('d1.Numero = d2.Numero')
            ->groupBy('d1.Codpro', 'd2.Codpro', 'p1.Descripcion', 'p2.Descripcion')
            ->havingRaw('COUNT(*) >= 2') // Mínimo 2 transacciones en común
            ->orderBy('confianza_a_b', 'desc')
            ->limit(50)
            ->get()
            ->filter(function($regla) use ($soporte_minimo, $confianza_minima) {
                return $regla->soporte >= $soporte_minimo && 
                       ($regla->confianza_a_b >= $confianza_minima || $regla->confianza_b_a >= $confianza_minima);
            });

        return [
            'reglas_asociacion' => $reglas_asociacion,
            'estadisticas' => [
                'total_reglas' => $reglas_asociacion->count(),
                'reglas_alta_confianza' => $reglas_asociacion->where('confianza_a_b', '>=', 0.7)->count(),
                'productos_unicos' => $reglas_asociacion->pluck('producto_a')->concat($reglas_asociacion->pluck('producto_b'))->unique()->count()
            ]
        ];
    }

    /**
     * ===============================================
     * ANALYTICS PREDICTIVO
     * ===============================================
     */

    /**
     * Predicción de ventas usando regresión lineal
     */
    public function predecirVentas(Request $request)
    {
        $meses_historicos = $request->meses_historicos ?? 24;
        $meses_proyeccion = $request->meses_proyeccion ?? 6;

        // Obtener datos históricos
        $datos_historicos = DB::table('Doccab')
            ->selectRaw('YEAR(Fecha) as año, MONTH(Fecha) as mes, SUM(Total) as total_ventas')
            ->where('Fecha', '>=', now()->subMonths($meses_historicos))
            ->whereNotIn('Estado', ['ANULADO'])
            ->groupByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->orderByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->get();

        // Preparar datos para regresión
        $datos_regresion = $datos_historicos->map(function($dato, $index) {
            return [
                'x' => $index + 1,
                'y' => $dato->total_ventas
            ];
        })->toArray();

        // Calcular regresión lineal
        $regresion = $this->calcularRegresionLineal($datos_regresion);
        
        // Generar predicciones
        $predicciones = [];
        for ($i = 1; $i <= $meses_proyeccion; $i++) {
            $x = count($datos_regresion) + $i;
            $y_predicha = $regresion['pendiente'] * $x + $regresion['intercepto'];
            
            // Agregar factor de estacionalidad
            $fecha_predicha = now()->addMonths($i);
            $estacionalidad = $this->obtenerFactorEstacionalidad($fecha_predicha->month);
            $y_predicha *= $estacionalidad;

            $predicciones[] = [
                'mes' => $fecha_predicha->format('Y-m'),
                'venta_predicha' => max(0, round($y_predicha, 2)),
                'intervalo_inferior' => round($y_predicha * 0.85, 2),
                'intervalo_superior' => round($y_predicha * 1.15, 2),
                'confianza' => $this->calcularConfianzaPrediccion($regresion['r_cuadrado'], $i)
            ];
        }

        return [
            'datos_historicos' => $datos_historicos,
            'parametros_regresion' => $regresion,
            'predicciones' => $predicciones,
            'metricas' => [
                'r_cuadrado' => $regresion['r_cuadrado'],
                'error_estandar' => $regresion['error_estandar'],
                'pendiente_mensual' => $regresion['pendiente']
            ]
        ];
    }

    /**
     * Análisis de estacionalidad
     */
    public function analizarEstacionalidad(Request $request)
    {
        $años_analisis = $request->años_analisis ?? 3;
        $fecha_desde = now()->subYears($años_analisis)->format('Y-m-d');
        $fecha_hasta = now()->format('Y-m-d');

        // Ventas por mes y año
        $ventas_mensuales = DB::table('Doccab')
            ->selectRaw('YEAR(Fecha) as año, MONTH(Fecha) as mes, SUM(Total) as total_ventas')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->groupByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->orderByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->get();

        // Calcular promedios por mes
        $promedios_mensuales = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            $ventas_mes = $ventas_mensuales->where('mes', $mes);
            $promedio = $ventas_mes->avg('total_ventas');
            $promedios_mensuales[$mes] = [
                'mes' => $mes,
                'promedio' => round($promedio, 2),
                'años_datos' => $ventas_mes->count(),
                'ventas_por_año' => $ventas_mes->values()
            ];
        }

        // Calcular índices estacionales
        $promedio_general = $ventas_mensuales->avg('total_ventas');
        $indices_estacionales = [];
        foreach ($promedios_mensuales as $mes => $datos) {
            $indice_estacional = $promedio_general > 0 ? ($datos['promedio'] / $promedio_general) * 100 : 100;
            $indices_estacionales[$mes] = round($indice_estacional, 2);
        }

        // Identificar meses pico y valle
        $mes_pico = array_search(max($indices_estacionales), $indices_estacionales);
        $mes_valle = array_search(min($indices_estacionales), $indices_estacionales);

        return [
            'promedios_mensuales' => $promedios_mensuales,
            'indices_estacionales' => $indices_estacionales,
            'promedio_general' => round($promedio_general, 2),
            'mes_pico' => [
                'numero' => $mes_pico,
                'nombre' => Carbon::create()->month($mes_pico)->format('F'),
                'indice' => $indices_estacionales[$mes_pico]
            ],
            'mes_valle' => [
                'numero' => $mes_valle,
                'nombre' => Carbon::create()->month($mes_valle)->format('F'),
                'indice' => $indices_estacionales[$mes_valle]
            ]
        ];
    }

    /**
     * ===============================================
     * ANÁLISIS COMPETITIVO Y BENCHMARKING
     * ===============================================
     */

    /**
     * Análisis de participación de mercado (simulado)
     */
    public function participacionMercado(Request $request)
    {
        // En un sistema real, esto vendría de datos externos o APIs
        // Por ahora simulamos datos comparativos
        
        $sector = $request->sector ?? 'farmaceutico';
        $periodo = $request->periodo ?? now()->format('Y-m');

        $empresa_actual = DB::table('Doccab')
            ->where('Fecha', '>=', $periodo . '-01')
            ->where('Fecha', '<=', $periodo . '-31')
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        // Datos simulados de competidores
        $competidores_simulados = [
            ['nombre' => 'Competidor A', 'ventas' => $empresa_actual * 1.3],
            ['nombre' => 'Competidor B', 'ventas' => $empresa_actual * 0.8],
            ['nombre' => 'Competidor C', 'ventas' => $empresa_actual * 0.6],
            ['nombre' => 'Otros', 'ventas' => $empresa_actual * 2.5]
        ];

        $total_mercado = $empresa_actual + array_sum(array_column($competidores_simulados, 'ventas'));

        $analisis_mercado = [
            [
                'empresa' => 'Nuestra Empresa',
                'ventas' => $empresa_actual,
                'participacion' => round(($empresa_actual / $total_mercado) * 100, 2),
                'ranking' => 1
            ]
        ];

        // Ordenar competidores por ventas
        usort($competidores_simulados, function($a, $b) {
            return $b['ventas'] - $a['ventas'];
        });

        $ranking = 2;
        foreach ($competidores_simulados as $competidor) {
            $analisis_mercado[] = [
                'empresa' => $competidor['nombre'],
                'ventas' => $competidor['ventas'],
                'participacion' => round(($competidor['ventas'] / $total_mercado) * 100, 2),
                'ranking' => $ranking
            ];
            $ranking++;
        }

        return [
            'periodo' => $periodo,
            'total_mercado' => $total_mercado,
            'analisis_mercado' => $analisis_mercado,
            'posicion_competitiva' => [
                'ranking' => 1,
                'participacion' => round(($empresa_actual / $total_mercado) * 100, 2),
                'lider_mercado' => $analisis_mercado[0]['empresa'] == 'Nuestra Empresa'
            ]
        ];
    }

    /**
     * ===============================================
     * MÉTODOS DE CÁLCULO Y UTILIDAD
     * ===============================================
     */

    /**
     * Genera resumen ejecutivo de analytics
     */
    private function generarResumenEjecutivo($periodo)
    {
        $fecha_desde = $this->calcularFechaDesde($periodo);
        $fecha_hasta = now();

        $ventas_totales = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        $clientes_unicos = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->distinct()
            ->count('Codcli');

        $ticket_promedio = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->avg('Total');

        return [
            'ventas_totales' => $ventas_totales,
            'clientes_unicos' => $clientes_unicos,
            'ticket_promedio' => round($ticket_promedio, 2),
            'periodo_analizado' => $fecha_desde . ' - ' . $fecha_hasta,
            'fecha_generacion' => now()
        ];
    }

    /**
     * Analiza tendencias principales
     */
    private function analizarTendenciasPrincipales($periodo)
    {
        $fecha_desde = $this->calcularFechaDesde($periodo);
        $fecha_hasta = now();

        // Tendencia de ventas mensual
        $ventas_mensuales = DB::table('Doccab')
            ->selectRaw('YEAR(Fecha) as año, MONTH(Fecha) as mes, SUM(Total) as total')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->groupByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->orderByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->get();

        $tendencia_ventas = $this->calcularTendencia($ventas_mensuales->pluck('total')->toArray());

        // Tendencia de clientes
        $clientes_mensuales = DB::table('Doccab')
            ->selectRaw('YEAR(Fecha) as año, MONTH(Fecha) as mes, COUNT(DISTINCT Codcli) as clientes')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->groupByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->orderByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->get();

        $tendencia_clientes = $this->calcularTendencia($clientes_mensuales->pluck('clientes')->toArray());

        return [
            'ventas' => [
                'tendencia' => $tendencia_ventas['direccion'],
                'crecimiento_promedio' => round($tendencia_ventas['crecimiento_promedio'], 2),
                'volatilidad' => round($tendencia_ventas['volatilidad'], 2)
            ],
            'clientes' => [
                'tendencia' => $tendencia_clientes['direccion'],
                'crecimiento_promedio' => round($tendencia_clientes['crecimiento_promedio'], 2),
                'volatilidad' => round($tendencia_clientes['volatilidad'], 2)
            ]
        ];
    }

    /**
     * Calcula métricas clave
     */
    private function calcularMetricasClave($periodo)
    {
        $fecha_desde = $this->calcularFechaDesde($periodo);
        $fecha_hasta = now();

        return [
            'ltv' => $this->calcularLTV($fecha_desde, $fecha_hasta),
            'cac' => $this->calcularCAC($fecha_desde, $fecha_hasta),
            'tasa_retencion' => $this->calcularTasaRetencion($fecha_desde, $fecha_hasta),
            'nps' => $this->calcularNPS(),
            'tasa_conversion' => $this->calcularTasaConversion($fecha_desde, $fecha_hasta)
        ];
    }

    /**
     * Genera alertas analíticas
     */
    private function generarAlertasAnaliticas()
    {
        $alertas = [];

        // Alerta por caída en ventas
        $ventas_mes_actual = DB::table('Doccab')
            ->whereMonth('Fecha', now()->month)
            ->whereYear('Fecha', now()->year)
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        $ventas_mes_anterior = DB::table('Doccab')
            ->whereMonth('Fecha', now()->subMonth()->month)
            ->whereYear('Fecha', now()->subMonth()->year)
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        if ($ventas_mes_anterior > 0) {
            $cambio_porcentual = (($ventas_mes_actual - $ventas_mes_anterior) / $ventas_mes_anterior) * 100;
            
            if ($cambio_porcentual < -10) {
                $alertas[] = [
                    'tipo' => 'CAIDA_VENTAS',
                    'mensaje' => "Caída del " . round(abs($cambio_porcentual), 1) . "% en ventas vs mes anterior",
                    'prioridad' => 'ALTA',
                    'valor' => round($cambio_porcentual, 2)
                ];
            }
        }

        // Alerta por productos sin rotación
        $productos_sin_rotacion = DB::table('Docdet')
            ->join('Doccab', 'Docdet.Numero', '=', 'Doccab.Numero')
            ->select('Docdet.Codpro')
            ->where('Doccab.Fecha', '<', now()->subDays(90))
            ->distinct()
            ->count();

        if ($productos_sin_rotacion > 50) {
            $alertas[] = [
                'tipo' => 'PRODUCTOS_SIN_ROTACION',
                'mensaje' => "{$productos_sin_rotacion} productos sin rotación en 90 días",
                'prioridad' => 'MEDIA'
            ];
        }

        return $alertas;
    }

    /**
     * ===============================================
     * MÉTODOS PRIVADOS DE CÁLCULO
     * ===============================================
     */

    private function calcularFechaDesde($periodo)
    {
        return match($periodo) {
            '1m' => now()->subMonth(),
            '3m' => now()->subMonths(3),
            '6m' => now()->subMonths(6),
            '12m' => now()->subYear(),
            '24m' => now()->subYears(2),
            default => now()->subYear()
        }->format('Y-m-d');
    }

    private function calcularQuintiles($datos)
    {
        $datos_ordenados = $datos->sort()->values();
        $total = count($datos_ordenados);
        
        return [
            $datos_ordenados[floor($total * 0.2)],
            $datos_ordenados[floor($total * 0.4)],
            $datos_ordenados[floor($total * 0.6)],
            $datos_ordenados[floor($total * 0.8)]
        ];
    }

    private function obtenerQuintil($valor, $quintiles)
    {
        $score = 5; // Mejor quintil por defecto
        
        if ($valor <= $quintiles[0]) $score = 5; // Top 20%
        elseif ($valor <= $quintiles[1]) $score = 4; // Top 40%
        elseif ($valor <= $quintiles[2]) $score = 3; // Top 60%
        elseif ($valor <= $quintiles[3]) $score = 2; // Top 80%
        else $score = 1; // Bottom 20%
        
        return $score;
    }

    private function determinarSegmentoRFM($r, $f, $m)
    {
        // Lógica simplificada para determinar segmento
        $promedio_r = ($r + $f + $m) / 3;
        
        if ($r >= 4 && $f >= 4 && $m >= 4) return 'Campeones';
        if ($r >= 3 && $f >= 3 && $m >= 3) return 'Clientes Fieles';
        if ($r <= 2 && $f >= 3) return 'Potenciales Fieles';
        if ($r >= 4 && $f <= 2) return 'Clientes Nuevos';
        if ($r <= 2 && $f <= 2 && $m >= 3) return 'En Riesgo';
        if ($r <= 2 && $f <= 2 && $m <= 2) return 'Perdidos';
        
        return 'Regulares';
    }

    private function calcularRegresionLineal($datos)
    {
        $n = count($datos);
        $sum_x = array_sum(array_column($datos, 'x'));
        $sum_y = array_sum(array_column($datos, 'y'));
        $sum_xy = 0;
        $sum_x2 = 0;
        $sum_y2 = 0;

        foreach ($datos as $dato) {
            $sum_xy += $dato['x'] * $dato['y'];
            $sum_x2 += $dato['x'] * $dato['x'];
            $sum_y2 += $dato['y'] * $dato['y'];
        }

        $pendiente = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_x2 - $sum_x * $sum_x);
        $intercepto = ($sum_y - $pendiente * $sum_x) / $n;
        
        // R cuadrado
        $ss_tot = $sum_y2 - ($sum_y * $sum_y / $n);
        $ss_res = 0;
        foreach ($datos as $dato) {
            $y_pred = $pendiente * $dato['x'] + $intercepto;
            $ss_res += pow($dato['y'] - $y_pred, 2);
        }
        $r_cuadrado = $ss_tot > 0 ? 1 - ($ss_res / $ss_tot) : 0;
        
        return [
            'pendiente' => $pendiente,
            'intercepto' => $intercepto,
            'r_cuadrado' => $r_cuadrado,
            'error_estandar' => sqrt($ss_res / ($n - 2))
        ];
    }

    private function calcularTendencia($datos)
    {
        if (count($datos) < 2) {
            return ['direccion' => 'INSUFICIENTES_DATOS', 'crecimiento_promedio' => 0, 'volatilidad' => 0];
        }

        // Calcular crecimiento promedio
        $crecimientos = [];
        for ($i = 1; $i < count($datos); $i++) {
            if ($datos[$i-1] > 0) {
                $crecimientos[] = (($datos[$i] - $datos[$i-1]) / $datos[$i-1]) * 100;
            }
        }
        
        $crecimiento_promedio = count($crecimientos) > 0 ? array_sum($crecimientos) / count($crecimientos) : 0;
        
        // Calcular volatilidad (desviación estándar)
        $media = array_sum($datos) / count($datos);
        $varianza = 0;
        foreach ($datos as $dato) {
            $varianza += pow($dato - $media, 2);
        }
        $volatilidad = sqrt($varianza / count($datos));

        // Determinar dirección
        if ($crecimiento_promedio > 5) $direccion = 'CRECIENTE';
        elseif ($crecimiento_promedio < -5) $direccion = 'DECRECIENTE';
        else $direccion = 'ESTABLE';

        return [
            'direccion' => $direccion,
            'crecimiento_promedio' => $crecimiento_promedio,
            'volatilidad' => $volatilidad
        ];
    }

    private function simularPatronesHorarios($fecha_desde, $fecha_hasta)
    {
        // Simulación de patrones horarios (en producción vendría de datos reales)
        $horas = [];
        for ($hora = 8; $hora <= 20; $hora++) {
            $ventas_promedio = rand(500, 2000);
            $factor_hora = $this->obtenerFactorHora($hora);
            $horas[] = [
                'hora' => sprintf('%02d:00', $hora),
                'ventas_promedio' => round($ventas_promedio * $factor_hora, 2),
                'factor' => $factor_hora
            ];
        }
        return $horas;
    }

    private function obtenerFactorHora($hora)
    {
        // Factores de venta por hora del día
        $factores = [
            8 => 0.3, 9 => 0.4, 10 => 0.6, 11 => 0.8, 12 => 0.5,
            13 => 0.4, 14 => 0.6, 15 => 0.8, 16 => 1.0, 17 => 1.2,
            18 => 0.9, 19 => 0.6, 20 => 0.4
        ];
        return $factores[$hora] ?? 0.5;
    }

    private function obtenerFactorEstacionalidad($mes)
    {
        // Factores estacionales simulados
        $factores = [1 => 0.8, 2 => 0.7, 3 => 0.9, 4 => 1.0, 5 => 1.1, 6 => 1.0,
                     7 => 0.9, 8 => 1.0, 9 => 1.2, 10 => 1.3, 11 => 1.1, 12 => 1.4];
        return $factores[$mes] ?? 1.0;
    }

    private function calcularConfianzaPrediccion($r_cuadrado, $meses_adelante)
    {
        // La confianza disminuye con el tiempo y aumenta con R²
        $confianza_base = $r_cuadrado * 100;
        $descuento_tiempo = $meses_adelante * 5; // 5% menos por mes
        return max(30, $confianza_base - $descuento_tiempo);
    }

    private function analizarEstacionalidad($fecha_desde, $fecha_hasta)
    {
        $ventas_mensuales = DB::table('Doccab')
            ->selectRaw('MONTH(Fecha) as mes, AVG(Total) as promedio_mensual')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->groupByRaw('MONTH(Fecha)')
            ->orderBy('mes')
            ->get();

        $promedio_general = $ventas_mensuales->avg('promedio_mensual');
        
        return $ventas_mensuales->map(function($mes) use ($promedio_general) {
            $mes->indice_estacional = $promedio_general > 0 ? 
                round(($mes->promedio_mensual / $promedio_general) * 100, 2) : 100;
            return $mes;
        });
    }

    private function generarInsightsVentas($patrones_semanales, $ventas_por_zona, $estacionalidad)
    {
        $insights = [];

        // Insight de día más fuerte
        $dia_fuerte = $patrones_semanales->sortByDesc('total_ventas')->first();
        if ($dia_fuerte) {
            $insights[] = [
                'tipo' => 'PATRON_SEMANAL',
                'mensaje' => "El {$dia_fuerte->nombre_dia} es el día con más ventas",
                'valor' => $dia_fuerte->total_ventas
            ];
        }

        // Insight de zona principal
        $zona_principal = $ventas_por_zona->first();
        if ($zona_principal) {
            $insights[] = [
                'tipo' => 'ZONA_PRINCIPAL',
                'mensaje' => "La provincia de {$zona_principal->Provincia} genera más ventas",
                'valor' => $zona_principal->total_ventas
            ];
        }

        // Insight de estacionalidad
        $mes_alto = $estacionalidad->sortByDesc('indice_estacional')->first();
        if ($mes_alto) {
            $insights[] = [
                'tipo' => 'ESTACIONALIDAD',
                'mensaje' => "El mes con mayor índice estacional es " . Carbon::create()->month($mes_alto->mes)->format('F'),
                'valor' => $mes_alto->indice_estacional
            ];
        }

        return $insights;
    }

    private function calcularLTV($fecha_desde, $fecha_hasta)
    {
        // Lifetime Value simplificado
        $ventas_totales = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        $clientes_unicos = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->distinct()
            ->count('Codcli');

        return $clientes_unicos > 0 ? round($ventas_totales / $clientes_unicos, 2) : 0;
    }

    private function calcularCAC($fecha_desde, $fecha_hasta)
    {
        // Customer Acquisition Cost (simulado)
        $gastos_marketing = 5000; // Simulado
        $nuevos_clientes = DB::table('Clientes')
            ->whereBetween('created_at', [$fecha_desde, $fecha_hasta])
            ->count();

        return $nuevos_clientes > 0 ? round($gastos_marketing / $nuevos_clientes, 2) : 0;
    }

    private function calcularTasaRetencion($fecha_desde, $fecha_hasta)
    {
        // Tasa de retención simplificada
        $clientes_periodo_anterior = DB::table('Doccab')
            ->where('Fecha', '<', $fecha_desde)
            ->whereNotIn('Estado', ['ANULADO'])
            ->distinct()
            ->count('Codcli');

        $clientes_retenidos = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->whereIn('Codcli', function($query) use ($fecha_desde) {
                $query->select('Codcli')
                      ->from('Doccab')
                      ->where('Fecha', '<', $fecha_desde)
                      ->whereNotIn('Estado', ['ANULADO'])
                      ->distinct();
            })
            ->distinct()
            ->count('Codcli');

        return $clientes_periodo_anterior > 0 ? round(($clientes_retenidos / $clientes_periodo_anterior) * 100, 2) : 0;
    }

    private function calcularNPS()
    {
        // Net Promoter Score (simulado)
        return rand(30, 70); // Valor simulado
    }

    private function calcularTasaConversion($fecha_desde, $fecha_hasta)
    {
        // Tasa de conversión (simulado)
        return rand(15, 35); // Porcentaje simulado
    }
}