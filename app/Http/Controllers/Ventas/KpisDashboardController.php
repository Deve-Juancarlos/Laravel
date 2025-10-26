<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KpisDashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));
            $vendedor = $request->input('vendedor');
            $zona = $request->input('zona');

            // KPIs principales
            $kpisPrincipales = $this->obtenerKpisPrincipales($fechaInicio, $fechaFin, $vendedor, $zona);
            
            // Comparación con período anterior
            $comparacionPeriodo = $this->obtenerComparacionPeriodo($fechaInicio, $fechaFin, $vendedor, $zona);
            
            // Top performers
            $topPerformers = $this->obtenerTopPerformers($fechaInicio, $fechaFin, $vendedor, $zona);
            
            // Tendencias mensuales
            $tendenciasMensuales = $this->obtenerTendenciasMensuales($fechaInicio, $fechaFin, $vendedor);
            
            // Alertas y métricas de riesgo
            $alertasRiesgo = $this->obtenerAlertasRiesgo($fechaInicio, $fechaFin, $vendedor);
            
            // Objetivos y metas
            $objetivos = $this->obtenerObjetivos($fechaInicio, $fechaFin, $vendedor);

            return response()->json([
                'success' => true,
                'data' => [
                    'kpis_principales' => $kpisPrincipales,
                    'comparacion_periodo' => $comparacionPeriodo,
                    'top_performers' => $topPerformers,
                    'tendencias_mensuales' => $tendenciasMensuales,
                    'alertas_riesgo' => $alertasRiesgo,
                    'objetivos' => $objetivos,
                    'periodo' => [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin
                    ]
                ],
                'message' => 'KPIs obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener KPIs: ' . $e->getMessage()
            ], 500);
        }
    }

    public function metricaEspecifica(Request $request)
    {
        try {
            $tipo = $request->input('tipo', 'ventas');
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));
            $vendedor = $request->input('vendedor');
            $zona = $request->input('zona');
            $agrupacion = $request->input('agrupacion', 'dia'); // dia, semana, mes

            $metrica = $this->calcularMetricaEspecifica($tipo, $fechaInicio, $fechaFin, $vendedor, $zona, $agrupacion);

            return response()->json([
                'success' => true,
                'data' => $metrica,
                'message' => 'Métrica específica obtenida exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métrica específica: ' . $e->getMessage()
            ], 500);
        }
    }

    private function obtenerKpisPrincipales($fechaInicio, $fechaFin, $vendedor = null, $zona = null)
    {
        $query = DB::table('Doccab as dc')
            ->join('Docdet as dd', function ($join) {
                $join->on('dc.Numero', '=', 'dd.Numero')
                     ->on('dc.Tipo', '=', 'dd.Tipo');
            })
            ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Eliminado', 0);

        if ($vendedor) {
            $query->where('dc.Vendedor', $vendedor);
        }

        if ($zona) {
            $query->where('c.Zona', $zona);
        }

        $estadisticas = $query->select(
                DB::raw('COUNT(DISTINCT dc.Numero) as total_ventas'),
                DB::raw('SUM(dd.Subtotal) as total_ingresos'),
                DB::raw('SUM(dd.Unidades) as total_productos_vendidos'),
                DB::raw('COUNT(DISTINCT c.Codclie) as clientes_unicos'),
                DB::raw('SUM(dd.Costo * dd.Unidades) as costo_total'),
                DB::raw('SUM(dd.Subtotal) - SUM(dd.Costo * dd.Unidades) as utilidad_total')
            )
            ->first();

        // Calcular promedios
        $diasPeriodo = Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1;
        $productosPorVenta = $estadisticas->total_productos_vendidos > 0 && $estadisticas->total_ventas > 0 
            ? $estadisticas->total_productos_vendidos / $estadisticas->total_ventas 
            : 0;

        $ticketPromedio = $estadisticas->total_ingresos > 0 && $estadisticas->total_ventas > 0 
            ? $estadisticas->total_ingresos / $estadisticas->total_ventas 
            : 0;

        $valorPorCliente = $estadisticas->total_ingresos > 0 && $estadisticas->clientes_unicos > 0 
            ? $estadisticas->total_ingresos / $estadisticas->clientes_unicos 
            : 0;

        $margenUtilidad = $estadisticas->total_ingresos > 0 
            ? ($estadisticas->utilidad_total / $estadisticas->total_ingresos) * 100 
            : 0;

        return [
            'metricas_basicas' => [
                'total_ventas' => intval($estadisticas->total_ventas ?? 0),
                'total_ingresos' => floatval($estadisticas->total_ingresos ?? 0),
                'total_productos_vendidos' => floatval($estadisticas->total_productos_vendidos ?? 0),
                'clientes_unicos' => intval($estadisticas->clientes_unicos ?? 0),
                'costo_total' => floatval($estadisticas->costo_total ?? 0),
                'utilidad_total' => floatval($estadisticas->utilidad_total ?? 0)
            ],
            'metricas_calculadas' => [
                'ticket_promedio' => round($ticketPromedio, 2),
                'productos_por_venta' => round($productosPorVenta, 2),
                'valor_por_cliente' => round($valorPorCliente, 2),
                'margen_utilidad' => round($margenUtilidad, 2),
                'ventas_por_dia' => round(($estadisticas->total_ventas ?? 0) / $diasPeriodo, 2),
                'ingresos_por_dia' => round(($estadisticas->total_ingresos ?? 0) / $diasPeriodo, 2)
            ]
        ];
    }

    private function obtenerComparacionPeriodo($fechaInicio, $fechaFin, $vendedor = null, $zona = null)
    {
        $periodoActual = $this->obtenerKpisPrincipales($fechaInicio, $fechaFin, $vendedor, $zona);
        
        // Calcular período anterior del mismo tamaño
        $inicio = Carbon::parse($fechaInicio);
        $fin = Carbon::parse($fechaFin);
        $dias = $inicio->diffInDays($fin) + 1;

        $periodoAnteriorFin = $inicio->copy()->subDay();
        $periodoAnteriorInicio = $periodoAnteriorFin->copy()->subDays($dias - 1);

        $periodoAnterior = $this->obtenerKpisPrincipales(
            $periodoAnteriorInicio->format('Y-m-d'),
            $periodoAnteriorFin->format('Y-m-d'),
            $vendedor,
            $zona
        );

        $actual = $periodoActual['metricas_basicas'];
        $anterior = $periodoAnterior['metricas_basicas'];

        return [
            'periodo_actual' => [
                'ventas' => $actual['total_ventas'],
                'ingresos' => $actual['total_ingresos'],
                'clientes' => $actual['clientes_unicos']
            ],
            'periodo_anterior' => [
                'ventas' => $anterior['total_ventas'],
                'ingresos' => $anterior['total_ingresos'],
                'clientes' => $anterior['clientes_unicos']
            ],
            'cambios' => [
                'ventas' => [
                    'absoluto' => $actual['total_ventas'] - $anterior['total_ventas'],
                    'porcentual' => $this->calcularPorcentajeCambio($anterior['total_ventas'], $actual['total_ventas'])
                ],
                'ingresos' => [
                    'absoluto' => $actual['total_ingresos'] - $anterior['total_ingresos'],
                    'porcentual' => $this->calcularPorcentajeCambio($anterior['total_ingresos'], $actual['total_ingresos'])
                ],
                'clientes' => [
                    'absoluto' => $actual['clientes_unicos'] - $anterior['clientes_unicos'],
                    'porcentual' => $this->calcularPorcentajeCambio($anterior['clientes_unicos'], $actual['clientes_unicos'])
                ]
            ]
        ];
    }

    private function obtenerTopPerformers($fechaInicio, $fechaFin, $vendedor = null, $zona = null)
    {
        // Top productos
        $topProductos = DB::table('Docdet as dd')
            ->join('Doccab as dc', function ($join) {
                $join->on('dd.Numero', '=', 'dc.Numero')
                     ->on('dd.Tipo', '=', 'dd.Tipo');
            })
            ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Eliminado', 0);

        if ($vendedor) {
            $topProductos->where('dc.Vendedor', $vendedor);
        }

        $topProductos = $topProductos->select(
                'p.CodPro',
                'p.Nombre',
                DB::raw('SUM(dd.Unidades) as cantidad_vendida'),
                DB::raw('SUM(dd.Subtotal) as ingresos_generados'),
                DB::raw('AVG(dd.Precio) as precio_promedio')
            )
            ->groupBy('p.CodPro', 'p.Nombre')
            ->orderBy('cantidad_vendida', 'desc')
            ->limit(10)
            ->get();

        // Top vendedores
        $topVendedores = DB::table('Doccab as dc')
            ->join('Docdet as dd', function ($join) {
                $join->on('dc.Numero', '=', 'dd.Numero')
                     ->on('dc.Tipo', '=', 'dd.Tipo');
            })
            ->join('Empleados as e', 'dc.Vendedor', '=', 'e.Codemp')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Eliminado', 0);

        if ($zona) {
            $topVendedores->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                          ->where('c.Zona', $zona);
        }

        $topVendedores = $topVendedores->select(
                'dc.Vendedor',
                'e.Nombre as NombreVendedor',
                DB::raw('COUNT(DISTINCT dc.Numero) as total_ventas'),
                DB::raw('SUM(dd.Subtotal) as ingresos_generados'),
                DB::raw('COUNT(DISTINCT dc.CodClie) as clientes_atendidos')
            )
            ->groupBy('dc.Vendedor', 'e.Nombre')
            ->orderBy('ingresos_generados', 'desc')
            ->limit(5)
            ->get();

        // Top zonas
        $topZonas = DB::table('Doccab as dc')
            ->join('Docdet as dd', function ($join) {
                $join->on('dc.Numero', '=', 'dd.Numero')
                     ->on('dc.Tipo', '=', 'dd.Tipo');
            })
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Eliminado', 0);

        if ($vendedor) {
            $topZonas->where('dc.Vendedor', $vendedor);
        }

        $topZonas = $topZonas->select(
                'c.Zona',
                'z.Descripcion as NombreZona',
                DB::raw('COUNT(DISTINCT dc.Numero) as total_ventas'),
                DB::raw('SUM(dd.Subtotal) as ingresos_generados'),
                DB::raw('COUNT(DISTINCT c.Codclie) as clientes_atendidos')
            )
            ->groupBy('c.Zona', 'z.Descripcion')
            ->orderBy('ingresos_generados', 'desc')
            ->limit(5)
            ->get();

        return [
            'productos' => $topProductos,
            'vendedores' => $topVendedores,
            'zonas' => $topZonas
        ];
    }

    private function obtenerTendenciasMensuales($fechaInicio, $fechaFin, $vendedor = null)
    {
        $query = DB::table('Doccab as dc')
            ->join('Docdet as dd', function ($join) {
                $join->on('dc.Numero', '=', 'dd.Numero')
                     ->on('dc.Tipo', '=', 'dd.Tipo');
            })
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Eliminado', 0);

        if ($vendedor) {
            $query->where('dc.Vendedor', $vendedor);
        }

        $tendencias = $query->select(
                DB::raw('YEAR(dc.Fecha) as año'),
                DB::raw('MONTH(dc.Fecha) as mes'),
                DB::raw('COUNT(DISTINCT dc.Numero) as total_ventas'),
                DB::raw('SUM(dd.Subtotal) as total_ingresos'),
                DB::raw('COUNT(DISTINCT dc.CodClie) as clientes_unicos')
            )
            ->groupBy(DB::raw('YEAR(dc.Fecha)'), DB::raw('MONTH(dc.Fecha)'))
            ->orderBy('año', 'desc')
            ->orderBy('mes', 'desc')
            ->get();

        return $tendencias;
    }

    private function obtenerAlertasRiesgo($fechaInicio, $fechaFin, $vendedor = null)
    {
        // Clientes con deuda vencida
        $clientesDeudaVencida = DB::table('CtaCliente as cc')
            ->join('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
            ->where('cc.Saldo', '>', 0)
            ->where('cc.FechaV', '<', Carbon::now())
            ->select(
                'c.Codclie',
                'c.Razon',
                'cc.Saldo as deuda',
                'cc.FechaV'
            )
            ->orderBy('cc.FechaV')
            ->limit(10)
            ->get();

        // Productos con baja rotación
        $productosBajaRotacion = DB::table('Productos as p')
            ->leftJoin('Docdet as dd', 'p.CodPro', '=', 'dd.Codpro')
            ->leftJoin('Doccab as dc', function ($join) {
                $join->on('dd.Numero', '=', 'dc.Numero')
                     ->on('dd.Tipo', '=', 'dc.Tipo');
            })
            ->where('p.Eliminado', 0)
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereNull('dc.Fecha')
                      ->orWhereNotBetween('dc.Fecha', [$fechaInicio, $fechaFin]);
            })
            ->select(
                'p.CodPro',
                'p.Nombre',
                'p.Stock',
                DB::raw('COUNT(dd.Codpro) as ventas_periodo')
            )
            ->groupBy('p.CodPro', 'p.Nombre', 'p.Stock')
            ->havingRaw('ventas_periodo = 0 OR ventas_periodo IS NULL')
            ->orderBy('p.Stock', 'desc')
            ->limit(10)
            ->get();

        // Vendedores con bajo rendimiento
        $vendedoresBajoRendimiento = DB::table('Empleados as e')
            ->leftJoin('Doccab as dc', function ($join) use ($fechaInicio, $fechaFin) {
                $join->on('e.Codemp', '=', 'dc.Vendedor')
                     ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin]);
            })
            ->leftJoin('Docdet as dd', function ($join) {
                $join->on('dc.Numero', '=', 'dd.Numero')
                     ->on('dc.Tipo', '=', 'dd.Tipo');
            })
            ->where('e.Tipo', 2) // Solo vendedores
            ->select(
                'e.Codemp',
                'e.Nombre',
                DB::raw('COUNT(DISTINCT dc.Numero) as ventas_realizadas'),
                DB::raw('SUM(ISNULL(dd.Subtotal, 0)) as ingresos_generados')
            )
            ->groupBy('e.Codemp', 'e.Nombre')
            ->havingRaw('ventas_realizadas < 5 OR ingresos_generados < 10000')
            ->get();

        return [
            'clientes_deuda_vencida' => $clientesDeudaVencida,
            'productos_baja_rotacion' => $productosBajaRotacion,
            'vendedores_bajo_rendimiento' => $vendedoresBajoRendimiento
        ];
    }

    private function obtenerObjetivos($fechaInicio, $fechaFin, $vendedor = null)
    {
        // Objetivos simulados (en un sistema real, estos vendrían de una tabla de objetivos)
        $objetivosGlobales = [
            'ingresos_mensuales' => [
                'meta' => 500000,
                'actual' => $this->calcularMetaActual($fechaInicio, $fechaFin, 'ingresos'),
                'porcentaje' => 0
            ],
            'ventas_mensuales' => [
                'meta' => 1000,
                'actual' => $this->calcularMetaActual($fechaInicio, $fechaFin, 'ventas'),
                'porcentaje' => 0
            ],
            'nuevos_clientes' => [
                'meta' => 50,
                'actual' => $this->calcularMetaActual($fechaInicio, $fechaFin, 'clientes'),
                'porcentaje' => 0
            ]
        ];

        // Calcular porcentajes
        foreach ($objetivosGlobales as $key => $objetivo) {
            $objetivosGlobales[$key]['porcentaje'] = $objetivo['meta'] > 0 
                ? round(($objetivo['actual'] / $objetivo['meta']) * 100, 2) 
                : 0;
        }

        return $objetivosGlobales;
    }

    private function calcularMetricaEspecifica($tipo, $fechaInicio, $fechaFin, $vendedor, $zona, $agrupacion)
    {
        $query = DB::table('Doccab as dc')
            ->join('Docdet as dd', function ($join) {
                $join->on('dc.Numero', '=', 'dd.Numero')
                     ->on('dc.Tipo', '=', 'dd.Tipo');
            })
            ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Eliminado', 0);

        if ($vendedor) {
            $query->where('dc.Vendedor', $vendedor);
        }

        if ($zona) {
            $query->where('c.Zona', $zona);
        }

        switch ($agrupacion) {
            case 'mes':
                $groupBy = [DB::raw('YEAR(dc.Fecha)'), DB::raw('MONTH(dc.Fecha)')];
                break;
            case 'semana':
                $groupBy = [DB::raw('YEAR(dc.Fecha)'), DB::raw('WEEK(dc.Fecha)')];
                break;
            default: // dia
                $groupBy = [DB::raw('CONVERT(date, dc.Fecha)')];
        }

        $selectFields = [
            ...$groupBy,
            DB::raw('COUNT(DISTINCT dc.Numero) as total_ventas'),
            DB::raw('SUM(dd.Subtotal) as total_ingresos'),
            DB::raw('COUNT(DISTINCT c.Codclie) as clientes_unicos')
        ];

        $query->select($selectFields)
              ->groupBy(...$groupBy)
              ->orderBy($groupBy[0], 'desc');

        if ($agrupacion === 'dia') {
            $query->orderBy(DB::raw('CONVERT(date, dc.Fecha)'), 'desc');
        }

        return $query->get();
    }

    private function calcularMetaActual($fechaInicio, $fechaFin, $tipo)
    {
        $query = DB::table('Doccab as dc')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Eliminado', 0);

        switch ($tipo) {
            case 'ingresos':
                $query->join('Docdet as dd', function ($join) {
                    $join->on('dc.Numero', '=', 'dd.Numero')
                         ->on('dc.Tipo', '=', 'dd.Tipo');
                });
                return floatval($query->sum('dd.Subtotal') ?? 0);
            case 'ventas':
                return intval($query->count(DB::raw('DISTINCT dc.Numero')));
            case 'clientes':
                return intval($query->count(DB::raw('DISTINCT dc.CodClie')));
            default:
                return 0;
        }
    }

    private function calcularPorcentajeCambio($anterior, $actual)
    {
        if ($anterior == 0) {
            return $actual > 0 ? 100 : 0;
        }
        
        return round((($actual - $anterior) / $anterior) * 100, 2);
    }
}