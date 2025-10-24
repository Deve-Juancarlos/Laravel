<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardVentasController extends Controller
{
    /**
     * Constructor con middleware de autenticación
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('rol:vendedor|administrador');
    }

    /**
     * Dashboard principal de ventas con KPIs
     */
    public function index()
    {
        $fechaInicio = now()->startOfMonth();
        $fechaFin = now()->endOfMonth();
        
        $kpis = $this->calcularKPIs($fechaInicio, $fechaFin);
        $ventasDiarias = $this->ventasDiarias($fechaInicio, $fechaFin);
        $topProductos = $this->topProductos($fechaInicio, $fechaFin);
        $topClientes = $this->topClientes($fechaInicio, $fechaFin);
        $comisiones = $this->analizarComisiones($fechaInicio, $fechaFin);
        
        return response()->json([
            'kpis' => $kpis,
            'ventas_diarias' => $ventasDiarias,
            'top_productos' => $topProductos,
            'top_clientes' => $topClientes,
            'comisiones' => $comisiones,
            'periodo' => [
                'inicio' => $fechaInicio->format('Y-m-d'),
                'fin' => $fechaFin->format('Y-m-d')
            ]
        ]);
    }

    /**
     * KPIs principales de ventas
     */
    public function calcularKPIs($fechaInicio, $fechaFin)
    {
        $ventas = DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Tipodoc', '!=', 'AN')
            ->select([
                DB::raw('COUNT(*) as total_ventas'),
                DB::raw('SUM(Total) as ventas_totales'),
                DB::raw('AVG(Total) as promedio_venta'),
                DB::raw('SUM(CASE WHEN Estado = "PAGADO" THEN Total ELSE 0 END) as ventas_cobradas'),
                DB::raw('SUM(CASE WHEN Estado = "PENDIENTE" THEN Total ELSE 0 END) as ventas_pendientes')
            ])
            ->first();

        $metaVentas = DB::table('Metas')
            ->where('Tipo', 'VENTAS')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->sum('Monto');

        $cumplimiento = $metaVentas > 0 ? ($ventas->ventas_totales / $metaVentas) * 100 : 0;

        // Ventas del mes anterior para comparación
        $ventasAnterior = DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio->copy()->subMonth(), $fechaFin->copy()->subMonth()])
            ->where('Tipodoc', '!=', 'AN')
            ->sum('Total');

        $crecimiento = $ventasAnterior > 0 ? 
            (($ventas->ventas_totales - $ventasAnterior) / $ventasAnterior) * 100 : 0;

        return [
            'total_ventas' => number_format($ventas->total_ventas, 0),
            'ventas_totales' => number_format($ventas->ventas_totales, 2),
            'promedio_venta' => number_format($ventas->promedio_venta, 2),
            'ventas_cobradas' => number_format($ventas->ventas_cobradas, 2),
            'ventas_pendientes' => number_format($ventas->ventas_pendientes, 2),
            'meta_ventas' => number_format($metaVentas, 2),
            'cumplimiento_meta' => number_format($cumplimiento, 2),
            'crecimiento_mensual' => number_format($crecimiento, 2),
            'tasa_cobranza' => $ventas->ventas_totales > 0 ? 
                number_format(($ventas->ventas_cobradas / $ventas->ventas_totales) * 100, 2) : '0.00'
        ];
    }

    /**
     * Análisis de ventas diarias
     */
    public function ventasDiarias($fechaInicio, $fechaFin)
    {
        $ventasDiarias = DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Tipodoc', '!=', 'AN')
            ->select([
                DB::raw('CAST(Fecha as DATE) as fecha'),
                DB::raw('COUNT(*) as numero_ventas'),
                DB::raw('SUM(Total) as total_ventas')
            ])
            ->groupBy(DB::raw('CAST(Fecha as DATE)'))
            ->orderBy('fecha')
            ->get();

        // Generar array completo con fechas faltantes
        $fechasCompletas = [];
        $fechaActual = $fechaInicio->copy();
        
        while ($fechaActual <= $fechaFin) {
            $fechaKey = $fechaActual->format('Y-m-d');
            $ventaDia = $ventasDiarias->firstWhere('fecha', $fechaKey);
            
            $fechasCompletas[] = [
                'fecha' => $fechaKey,
                'numero_ventas' => $ventaDia ? $ventaDia->numero_ventas : 0,
                'total_ventas' => $ventaDia ? $ventaDia->total_ventas : 0,
                'dia_semana' => $fechaActual->dayOfWeek
            ];
            
            $fechaActual->addDay();
        }

        return $fechasCompletas;
    }

    /**
     * Top productos más vendidos
     */
    public function topProductos($fechaInicio, $fechaFin)
    {
        return DB::table('Docdet')
            ->join('Doccab', 'Docdet.Numero', '=', 'Doccab.Numero')
            ->join('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->whereBetween('Doccab.Fecha', [$fechaInicio, $fechaFin])
            ->where('Doccab.Tipodoc', '!=', 'AN')
            ->select([
                'Productos.CodPro',
                'Productos.Nombre',
                DB::raw('SUM(Docdet.Cantidad) as total_vendido'),
                DB::raw('SUM(Docdet.Subtotal) as total_ingresos'),
                DB::raw('AVG(Docdet.Precio) as precio_promedio')
            ])
            ->groupBy('Productos.CodPro', 'Productos.Nombre')
            ->orderBy('total_vendido', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Top clientes por volumen de compras
     */
    public function topClientes($fechaInicio, $fechaFin)
    {
        return DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodCli', '=', 'Clientes.CodCli')
            ->whereBetween('Doccab.Fecha', [$fechaInicio, $fechaFin])
            ->where('Doccab.Tipodoc', '!=', 'AN')
            ->select([
                'Clientes.CodCli',
                'Clientes.Razon',
                'Clientes.Direccion',
                DB::raw('COUNT(*) as numero_compras'),
                DB::raw('SUM(Doccab.Total) as total_compras'),
                DB::raw('AVG(Doccab.Total) as ticket_promedio')
            ])
            ->groupBy('Clientes.CodCli', 'Clientes.Razon', 'Clientes.Direccion')
            ->orderBy('total_compras', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Análisis de comisiones por vendedor
     */
    public function analizarComisiones($fechaInicio, $fechaFin)
    {
        $vendedores = DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Tipodoc', '!=', 'AN')
            ->whereNotNull('Vendedor')
            ->select([
                'Vendedor',
                DB::raw('COUNT(*) as ventas_realizadas'),
                DB::raw('SUM(Total) as ventas_totales'),
                DB::raw('AVG(Total) as ticket_promedio')
            ])
            ->groupBy('Vendedor')
            ->orderBy('ventas_totales', 'desc')
            ->get();

        // Calcular comisiones basadas en estructura
        $comisiones = $vendedores->map(function ($vendedor) {
            $tasaComision = $this->calcularTasaComision($vendedor->ventas_totales);
            $comisionCalculada = ($vendedor->ventas_totales * $tasaComision) / 100;
            
            return [
                'vendedor' => $vendedor->Vendedor,
                'ventas_realizadas' => $vendedor->ventas_realizadas,
                'ventas_totales' => number_format($vendedor->ventas_totales, 2),
                'ticket_promedio' => number_format($vendedor->ticket_promedio, 2),
                'tasa_comision' => $tasaComision,
                'comision_calculada' => number_format($comisionCalculada, 2)
            ];
        });

        return $comisiones;
    }

    /**
     * Calcular tasa de comisión basada en ventas
     */
    private function calcularTasaComision($ventasTotales)
    {
        if ($ventasTotales >= 100000) return 5.0;      // 5% para > 100k
        if ($ventasTotales >= 50000) return 4.0;       // 4% para > 50k
        if ($ventasTotales >= 25000) return 3.0;       // 3% para > 25k
        if ($ventasTotales >= 10000) return 2.0;       // 2% para > 10k
        return 1.0;                                     // 1% base
    }

    /**
     * Dashboard en tiempo real
     */
    public function tiempoReal()
    {
        $hoy = now()->toDateString();
        
        // Ventas del día
        $ventasHoy = DB::table('Doccab')
            ->where('Fecha', $hoy)
            ->where('Tipodoc', '!=', 'AN')
            ->select([
                DB::raw('COUNT(*) as ventas_hoy'),
                DB::raw('SUM(Total) as total_hoy')
            ])
            ->first();

        // Última venta
        $ultimaVenta = DB::table('Doccab')
            ->where('Fecha', $hoy)
            ->where('Tipodoc', '!=', 'AN')
            ->orderBy('Fecha', 'desc')
            ->first();

        // Productos más vendidos hoy
        $productosHoy = DB::table('Docdet')
            ->join('Doccab', 'Docdet.Numero', '=', 'Doccab.Numero')
            ->join('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->where('Doccab.Fecha', $hoy)
            ->where('Doccab.Tipodoc', '!=', 'AN')
            ->select([
                'Productos.Nombre',
                DB::raw('SUM(Docdet.Cantidad) as cantidad'),
                DB::raw('SUM(Docdet.Subtotal) as ingresos')
            ])
            ->groupBy('Productos.Nombre')
            ->orderBy('cantidad', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'fecha' => $hoy,
            'ventas_hoy' => $ventasHoy->ventas_hoy,
            'total_hoy' => number_format($ventasHoy->total_hoy, 2),
            'ultima_venta' => $ultimaVenta ? [
                'numero' => $ultimaVenta->Numero,
                'cliente' => $this->obtenerNombreCliente($ultimaVenta->CodCli),
                'total' => $ultimaVenta->Total,
                'hora' => $ultimaVenta->Fecha
            ] : null,
            'productos_hoy' => $productosHoy
        ]);
    }

    /**
     * Reporte mensual de ventas
     */
    public function reporteMensual($año, $mes = null)
    {
        $fechaInicio = Carbon::create($año, $mes ?? now()->month)->startOfMonth();
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        $ventasMensuales = DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Tipodoc', '!=', 'AN')
            ->select([
                DB::raw('WEEK(Fecha) as semana'),
                DB::raw('COUNT(*) as total_ventas'),
                DB::raw('SUM(Total) as ventas_totales'),
                DB::raw('AVG(Total) as ticket_promedio')
            ])
            ->groupBy(DB::raw('WEEK(Fecha)'))
            ->orderBy('semana')
            ->get();

        $resumenMensual = $this->calcularKPIs($fechaInicio, $fechaFin);

        return response()->json([
            'periodo' => [
                'año' => $año,
                'mes' => $mes ?? now()->month,
                'nombre_mes' => Carbon::create($año, $mes ?? now()->month)->format('F')
            ],
            'resumen' => $resumenMensual,
            'ventas_semanales' => $ventasMensuales
        ]);
    }

    /**
     * Análisis de tendencias
     */
    public function tendencias($tipo = 'semanal')
    {
        $fechaInicio = match($tipo) {
            'diario' => now()->subDays(30),
            'semanal' => now()->subWeeks(12),
            'mensual' => now()->subMonths(12),
            default => now()->subWeeks(12)
        };

        $ventas = DB::table('Doccab')
            ->where('Fecha', '>=', $fechaInicio)
            ->where('Tipodoc', '!=', 'AN')
            ->select([
                DB::raw('CAST(Fecha as DATE) as fecha'),
                DB::raw('SUM(Total) as total_ventas'),
                DB::raw('COUNT(*) as numero_ventas')
            ])
            ->groupBy(DB::raw('CAST(Fecha as DATE)'))
            ->orderBy('fecha')
            ->get();

        return response()->json([
            'tipo_analisis' => $tipo,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
            'ventas' => $ventas,
            'tendencias' => $this->calcularTendencias($ventas)
        ]);
    }

    /**
     * Calcular tendencias estadísticas
     */
    private function calcularTendencias($ventas)
    {
        if ($ventas->count() < 2) {
            return ['tendencia' => 'insuficientes_datos'];
        }

        $totalVentas = $ventas->pluck('total_ventas');
        $media = $totalVentas->avg();
        $tendencia = $this->calcularRegresionLineal($totalVentas->values());

        return [
            'tendencia' => $tendencia > 0 ? 'crecimiento' : ($tendencia < 0 ? 'decrecimiento' : 'estable'),
            'variacion' => number_format(abs($tendencia), 2),
            'media_ventas' => number_format($media, 2),
            'coeficiente_variacion' => $media > 0 ? 
                number_format(($totalVentas->stdDev() / $media) * 100, 2) : '0.00'
        ];
    }

    /**
     * Regresión lineal simple
     */
    private function calcularRegresionLineal($valores)
    {
        $n = count($valores);
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumXX = 0;

        for ($i = 0; $i < $n; $i++) {
            $x = $i + 1;
            $y = $valores[$i];
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumXX += $x * $x;
        }

        return ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
    }

    /**
     * Utilidad para obtener nombre del cliente
     */
    private function obtenerNombreCliente($codCli)
    {
        $cliente = DB::table('Clientes')->where('CodCli', $codCli)->first();
        return $cliente ? $cliente->Razon : 'Cliente no encontrado';
    }

    /**
     * Exportar datos del dashboard
     */
    public function exportar(Request $request)
    {
        $tipo = $request->input('tipo', 'json');
        $fechaInicio = Carbon::parse($request->input('fecha_inicio', now()->startOfMonth()));
        $fechaFin = Carbon::parse($request->input('fecha_fin', now()->endOfMonth()));

        $datos = [
            'kpis' => $this->calcularKPIs($fechaInicio, $fechaFin),
            'ventas_diarias' => $this->ventasDiarias($fechaInicio, $fechaFin),
            'top_productos' => $this->topProductos($fechaInicio, $fechaFin),
            'top_clientes' => $this->topClientes($fechaInicio, $fechaFin),
            'comisiones' => $this->analizarComisiones($fechaInicio, $fechaFin),
            'periodo' => [
                'inicio' => $fechaInicio->format('Y-m-d'),
                'fin' => $fechaFin->format('Y-m-d'),
                'generado' => now()->format('Y-m-d H:i:s')
            ]
        ];

        if ($tipo === 'excel') {
            return $this->exportarExcel($datos);
        }

        return response()->json($datos);
    }

    /**
     * Exportar a Excel
     */
    private function exportarExcel($datos)
    {
        $filename = 'dashboard_ventas_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        // Aquí iría la lógica de exportación a Excel
        // Por simplicidad, retornamos JSON con información de exportación
        
        return response()->json([
            'mensaje' => 'Exportación iniciada',
            'archivo' => $filename,
            'datos' => $datos
        ]);
    }

    /**
     * API endpoints para widgets en tiempo real
     */
    public function widgetVentasHoy()
    {
        return $this->tiempoReal();
    }

    public function widgetTopProductos()
    {
        $fechaInicio = now()->startOfWeek();
        $fechaFin = now()->endOfWeek();
        
        $productos = $this->topProductos($fechaInicio, $fechaFin)->take(5);
        
        return response()->json([
            'titulo' => 'Top Productos de la Semana',
            'productos' => $productos,
            'actualizado' => now()->format('Y-m-d H:i:s')
        ]);
    }

    public function widgetCumplimientoMetas()
    {
        $fechaInicio = now()->startOfMonth();
        $fechaFin = now()->endOfMonth();
        
        $metaMensual = DB::table('Metas')
            ->where('Tipo', 'VENTAS')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->sum('Monto');

        $ventasActuales = DB::table('Doccab')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Tipodoc', '!=', 'AN')
            ->sum('Total');

        $cumplimiento = $metaMensual > 0 ? ($ventasActuales / $metaMensual) * 100 : 0;
        
        return response()->json([
            'meta' => number_format($metaMensual, 2),
            'actual' => number_format($ventasActuales, 2),
            'cumplimiento' => number_format($cumplimiento, 2),
            'restante' => number_format($metaMensual - $ventasActuales, 2),
            'dias_restantes' => now()->diffInDays(now()->endOfMonth())
        ]);
    }
}
