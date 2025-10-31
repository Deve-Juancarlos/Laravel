<?php

namespace App\Services\Contabilidad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EstadoResultadosService
{
    /**
     * Obtiene los datos para el Estado de Resultados principal.
     */
    public function getEstadoResultadosData(array $filters): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? now()->startOfMonth()->toDateString();
        $fechaFin = $filters['fecha_fin'] ?? now()->endOfMonth()->toDateString();

        // INGRESOS (Clase 7)
        $ingresos = $this->getCuentasPorClase($fechaInicio, $fechaFin, '7%', 'haber');

        // GASTOS (Clase 6 y 9)
        $gastos = $this->getCuentasPorClase($fechaInicio, $fechaFin, ['6%', '9%'], 'debe');
        
        // Ventas y Costo de Ventas (Datos más precisos de Doccab/Docdet)
        $ventasNetas = $this->obtenerVentasNetas($fechaInicio, $fechaFin);
        $costoVentas = $this->obtenerCostoVentas($fechaInicio, $fechaFin); // Asumimos que es la cuenta 69

        $totalIngresos = $ingresos->sum('total');
        $totalGastos = $gastos->sum('total'); // Incluye el costo de ventas si está en la 6

        $utilidadBruta = $ventasNetas - $costoVentas;
        
        // Gastos Operativos = Total Gastos (6, 9) MENOS el Costo de Ventas (69)
        $costoVentasDesdeDiario = $this->getCuentasPorClase($fechaInicio, $fechaFin, '69%', 'debe')->sum('total');
        $gastosOperativos = $totalGastos - $costoVentasDesdeDiario; 
        
        $utilidadOperativa = $utilidadBruta - $gastosOperativos;
        
        // Simplificación: Utilidad Neta = Utilidad Operativa (faltarían otros ingresos/gastos, impuestos)
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
        
        return compact(
            'ingresos', 'gastos', 'ventasNetas', 'costoVentas', 'resultados',
            'comparacion', 'fechaInicio', 'fechaFin'
        );
    }

    /**
     * Obtiene los resultados por períodos (mensual).
     */
    public function getResultadosPorPeriodos(array $filters): array
    {
        $anio = $filters['anio'] ?? now()->year;
        $resultadosMensuales = [];

        for ($mes = 1; $mes <= 12; $mes++) {
            $inicioMes = Carbon::create($anio, $mes, 1)->startOfMonth()->toDateString();
            $finMes = Carbon::create($anio, $mes, 1)->endOfMonth()->toDateString();

            $ventasNetas = $this->obtenerVentasNetas($inicioMes, $finMes);
            $costoVentas = $this->obtenerCostoVentas($inicioMes, $finMes);
            $utilidadBruta = $ventasNetas - $costoVentas;

            $gastosTotales = $this->getCuentasPorClase($inicioMes, $finMes, ['6%', '9%'], 'debe')->sum('total');
            $costoVentasDesdeDiario = $this->getCuentasPorClase($inicioMes, $finMes, '69%', 'debe')->sum('total');
            $gastosOperativos = $gastosTotales - $costoVentasDesdeDiario;
            
            $utilidadOperativa = $utilidadBruta - $gastosOperativos;

            $resultadosMensuales[$mes] = [
                'mes' => Carbon::create($anio, $mes, 1)->locale('es')->isoFormat('MMMM'),
                'ventas_netas' => round($ventasNetas, 2),
                'costo_ventas' => round($costoVentas, 2),
                'utilidad_bruta' => round($utilidadBruta, 2),
                'gastos_operativos' => round($gastosOperativos, 2),
                'utilidad_operativa' => round($utilidadOperativa, 2),
                'margen_bruto' => $ventasNetas > 0 ? round(($utilidadBruta / $ventasNetas) * 100, 2) : 0,
                'margen_operativo' => $ventasNetas > 0 ? round(($utilidadOperativa / $ventasNetas) * 100, 2) : 0,
            ];
        }

        $mesMayorVenta = collect($resultadosMensuales)->sortByDesc('ventas_netas')->keys()->first() ?? null;
        $mesMayorUtilidad = collect($resultadosMensuales)->sortByDesc('utilidad_operativa')->keys()->first() ?? null;
        $ventasAnio = collect($resultadosMensuales)->sum('ventas_netas');
        $promedioMensual = $ventasAnio > 0 ? round($ventasAnio / 12, 0) : 0;

        $tendencias = [
            'promedio_mensual_ventas' => $promedioMensual,
            'mes_mayor_venta' => $mesMayorVenta,
            'mes_mayor_utilidad' => $mesMayorUtilidad,
        ];

        return compact('anio', 'resultadosMensuales', 'tendencias');
    }

    /**
     * Obtiene los datos para la vista comparativa.
     */
    public function getComparativoData(array $filters): array
    {
        $fechaInicio1 = $filters['fecha_inicio'] ?? now()->startOfMonth()->toDateString();
        $fechaFin1 = $filters['fecha_fin'] ?? now()->endOfMonth()->toDateString();

        $fechaInicio2 = $filters['anterior_inicio'] ?? Carbon::parse($fechaInicio1)->subMonth()->startOfMonth()->toDateString();
        $fechaFin2 = $filters['anterior_fin'] ?? Carbon::parse($fechaFin1)->subMonth()->endOfMonth()->toDateString();

        // Resultados primer período (ACTUAL)
        $ventas1 = $this->obtenerVentasNetas($fechaInicio1, $fechaFin1);
        $costo1 = $this->obtenerCostoVentas($fechaInicio1, $fechaFin1);
        $utilidadBruta1 = $ventas1 - $costo1;
        $gastosTotales1 = $this->getCuentasPorClase($fechaInicio1, $fechaFin1, ['6%', '9%'], 'debe')->sum('total');
        $costoVentasDesdeDiario1 = $this->getCuentasPorClase($fechaInicio1, $fechaFin1, '69%', 'debe')->sum('total');
        $gastosOperativos1 = $gastosTotales1 - $costoVentasDesdeDiario1;
        $utilidadOperativa1 = $utilidadBruta1 - $gastosOperativos1;

        // Resultados segundo período (ANTERIOR)
        $ventas2 = $this->obtenerVentasNetas($fechaInicio2, $fechaFin2);
        $costo2 = $this->obtenerCostoVentas($fechaInicio2, $fechaFin2);
        $utilidadBruta2 = $ventas2 - $costo2;
        $gastosTotales2 = $this->getCuentasPorClase($fechaInicio2, $fechaFin2, ['6%', '9%'], 'debe')->sum('total');
        $costoVentasDesdeDiario2 = $this->getCuentasPorClase($fechaInicio2, $fechaFin2, '69%', 'debe')->sum('total');
        $gastosOperativos2 = $gastosTotales2 - $costoVentasDesdeDiario2;
        $utilidadOperativa2 = $utilidadBruta2 - $gastosOperativos2;

        $comparativo = [
            'actual' => ['ventas' => $ventas1, 'costo' => $costo1, 'utilidad_bruta' => $utilidadBruta1],
            'anterior' => ['ventas' => $ventas2, 'costo' => $costo2, 'utilidad_bruta' => $utilidadBruta2],
            'variacion' => [
                'ventas' => $ventas2 > 0 ? round((($ventas1 - $ventas2) / $ventas2) * 100, 2) : ($ventas1 > 0 ? 100 : 0),
                'utilidad_bruta' => $utilidadBruta2 > 0 ? round((($utilidadBruta1 - $utilidadBruta2) / $utilidadBruta2) * 100, 2) : ($utilidadBruta1 > 0 ? 100 : 0),
            ]
        ];

        $periodos = [
            'actual_mensual' => [
                'inicio' => $fechaInicio1, 'fin' => $fechaFin1,
                'resultados' => ['ventas_netas' => $ventas1, 'costo_ventas' => $costo1, 'utilidad_bruta' => $utilidadBruta1, 'gastos_operativos' => $gastosOperativos1, 'utilidad_operativa' => $utilidadOperativa1]
            ],
            'anterior_mensual' => [
                'inicio' => $fechaInicio2, 'fin' => $fechaFin2,
                'resultados' => ['ventas_netas' => $ventas2, 'costo_ventas' => $costo2, 'utilidad_bruta' => $utilidadBruta2, 'gastos_operativos' => $gastosOperativos2, 'utilidad_operativa' => $utilidadOperativa2]
            ]
        ];

        $variaciones = [
            'mensual' => [
                'ventas' => $ventas2 > 0 ? round((($ventas1 - $ventas2) / $ventas2) * 100, 2) : ($ventas1 > 0 ? 100 : 0),
                'utilidad' => $utilidadOperativa2 > 0 ? round((($utilidadOperativa1 - $utilidadOperativa2) / $utilidadOperativa2) * 100, 2) : ($utilidadOperativa1 > 0 ? 100 : 0),
            ]
        ];

        return compact('comparativo', 'periodos', 'variaciones');
    }

    /**
     * Obtiene el detalle de movimientos de una cuenta de resultados.
     */
    public function getDetalleCuentaData(array $filters, string $cuenta): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? now()->startOfMonth()->toDateString();
        $fechaFin = $filters['fecha_fin'] ?? now()->endOfMonth()->toDateString();
        $busqueda = $filters['busqueda'] ?? null;

        $movimientosQuery = DB::table('libro_diario_detalles as d')
            ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
            ->join('plan_cuentas as pc', 'd.cuenta_contable', '=', 'pc.codigo')
            ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
            ->where('c.estado', 'ACTIVO');

        if ($cuenta !== 'all') {
            $movimientosQuery->where('d.cuenta_contable', $cuenta);
        }

        if ($busqueda) {
            $movimientosQuery->where('d.concepto', 'like', "%{$busqueda}%");
        }

        $movimientos = $movimientosQuery
            ->select('c.id as asiento_id', 'c.fecha', 'c.numero', 'd.cuenta_contable', 'pc.nombre as concepto', 'd.debe as debito', 'd.haber as credito')
            ->orderBy('c.fecha')
            ->get();

        $cuentaMostrar = $cuenta === 'all' ? 'Todas las Cuentas' : $cuenta;
        $clasificacion = null;
        if ($cuenta !== 'all') {
            $clasificacion = DB::table('plan_cuentas')->where('codigo', $cuenta)->value('tipo');
        }

        return compact('movimientos', 'cuentaMostrar', 'clasificacion', 'fechaInicio', 'fechaFin');
    }

    /**
     * Exporta el Estado de Resultados. (Lógica de exportación no implementada)
     */
    public function exportar(array $filters)
    {
        // Lógica de exportación (p.ej. a CSV o PDF)
        // Esta función no estaba implementada en el controlador original.
        // Devolvemos un error amigable por ahora.
        Log::info("Intento de exportación de EERR con filtros: ", $filters);
        throw new \Exception("La función de exportar para Estado de Resultados no está implementada.");
    }


    // ========================================
    // MÉTODOS HELPER (Públicos para ser usados por otros servicios)
    // ========================================

    /**
     * Helper para obtener Ventas Netas desde Doccab (Facturas)
     */
    public function obtenerVentasNetas($fechaInicio, $fechaFin)
    {
        return DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Tipo', 1) // Tipo 1 = Factura (asumido)
            ->where('Eliminado', 0)
            ->sum('Subtotal') ?? 0;
    }

    /**
     * Helper para obtener Costo de Ventas desde Docdet (Facturas)
     */
    public function obtenerCostoVentas($fechaInicio, $fechaFin)
    {
        // Asumimos que el costo de ventas (Clase 69) se registra en Docdet.Costo
        // Si se registra en libro_diario, la lógica debe cambiar.
        return DB::table('Docdet as d')
            ->join('Doccab as c', function($join) {
                 $join->on('d.Numero', '=', 'c.Numero')
                      ->on('d.Tipo', '=', 'c.Tipo'); // Unir también por tipo
            })
            ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
            ->where('c.Tipo', 1) // Tipo 1 = Factura (asumido)
            ->where('c.Eliminado', 0)
            ->sum(DB::raw('ISNULL(d.Cantidad, 0) * ISNULL(d.Costo, 0)')) ?? 0;
    }
    
    /**
     * Helper genérico para obtener saldos de cuentas por clase/patrón.
     * **CORRECCIÓN: Cambiado de 'private' a 'public' para que BalanceGeneralService pueda usarlo.**
     */
    public function getCuentasPorClase($fechaInicio, $fechaFin, $patrones, $campoTotal): Collection
    {
        $query = DB::table('libro_diario_detalles as d')
            ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
            ->join('plan_cuentas as pc', 'd.cuenta_contable', '=', 'pc.codigo')
            ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
            ->where('c.estado', 'ACTIVO');

        if (is_array($patrones)) {
            $query->where(function ($q) use ($patrones) {
                foreach ($patrones as $patron) {
                    $q->orWhere('d.cuenta_contable', 'LIKE', $patron);
                }
            });
        } else {
            $query->where('d.cuenta_contable', 'LIKE', $patrones);
        }

        return $query->select('d.cuenta_contable', 'pc.nombre as descripcion', DB::raw("SUM(d.$campoTotal) as total"))
            ->groupBy('d.cuenta_contable', 'pc.nombre')
            ->get()
            ->map(fn($item) => (object)[
                'cuenta_contable' => $item->cuenta_contable,
                'descripcion' => $item->descripcion,
                'total' => round($item->total, 2),
                'movimientos' => 1 // Placeholder, no se usa en EERR
            ]);
    }

    /**
     * Helper para obtener la comparación de un período
     */
    private function obtenerComparacionPeriodo($fechaInicioAnterior, $fechaFinAnterior, $fechaInicioActual, $fechaFinActual)
    {
        $ventasAnterior = $this->obtenerVentasNetas($fechaInicioAnterior, $fechaFinAnterior);
        $ventasActual = $this->obtenerVentasNetas($fechaInicioActual, $fechaFinActual);
        $costosAnterior = $this->obtenerCostoVentas($fechaInicioAnterior, $fechaFinAnterior);
        $costosActual = $this->obtenerCostoVentas($fechaInicioActual, $fechaFinActual);

        return [
            'ventas_actual' => $ventasActual,
            'ventas_anterior' => $ventasAnterior,
            'ventas_variacion' => $ventasAnterior > 0 ? round((($ventasActual - $ventasAnterior) / $ventasAnterior) * 100, 2) : ($ventasActual > 0 ? 100 : 0),
            'costos_actual' => $costosActual,
            'costos_anterior' => $costosAnterior,
            'costos_variacion' => $costosAnterior > 0 ? round((($costosActual - $costosAnterior) / $costosAnterior) * 100, 2) : ($costosActual > 0 ? 100 : 0),
        ];
    }
}

