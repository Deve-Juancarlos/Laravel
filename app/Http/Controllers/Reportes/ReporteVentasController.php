<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Contabilidad\ContadorDashboardService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VentasCobranzasExport;
use Carbon\Carbon;


class ReporteVentasController extends Controller
{
    /**
     * Muestra el reporte de Rentabilidad por Cliente
     */
    public function rentabilidadCliente(Request $request)
    {
        // 1. Definir las fechas. Si el usuario no envía, poner un rango por defecto (ej. últimos 30 días)
        $fechaInicio = $request->input('fecha_inicio', now()->subDays(30)->toDateString());
        $fechaFin    = $request->input('fecha_fin', now()->toDateString());

        // 2. La Consulta Mágica (Business Intelligence)
        // Usamos la base de datos [SIFANO] que me diste
        
        $reporte = DB::table('Clientes as c')
            // (A) Subconsulta para sumar todas las VENTAS (Doccab)
            ->leftJoinSub(
                DB::table('Doccab')
                    ->select(
                        'CodClie',
                        DB::raw('COUNT(Numero) as CantidadFacturas'),
                        DB::raw('SUM(Total) as TotalFacturado'),
                        DB::raw('SUM(Descuento) as TotalDescuentos')
                    )
                    ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                    ->where('Eliminado', 0)
                    ->groupBy('CodClie'),
                'ventas',
                'ventas.CodClie', '=', 'c.Codclie'
            )
            // (B) Subconsulta para sumar todas las DEVOLUCIONES (notas_credito)
            ->leftJoinSub(
                DB::table('notas_credito')
                    ->select(
                        'Codclie',
                        DB::raw('SUM(Total) as TotalDevoluciones')
                    )
                    ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                    ->where('Anulado', 0)
                    ->groupBy('Codclie'),
                'devoluciones',
                'devoluciones.Codclie', '=', 'c.Codclie'
            )
            // (C) Seleccionamos y calculamos
            ->select(
                'c.Codclie',
                'c.Razon',
                'c.Limite as LimiteCredito',
                'c.Telefono1',
                DB::raw('ISNULL(ventas.CantidadFacturas, 0) as CantidadFacturas'),
                DB::raw('ISNULL(ventas.TotalFacturado, 0) as TotalFacturado'),
                DB::raw('ISNULL(ventas.TotalDescuentos, 0) as TotalDescuentos'),
                DB::raw('ISNULL(devoluciones.TotalDevoluciones, 0) as TotalDevoluciones'),
                DB::raw('ISNULL(ventas.TotalFacturado, 0) - ISNULL(devoluciones.TotalDevoluciones, 0) - ISNULL(ventas.TotalDescuentos, 0) as VentaNeta')
            )
            // Solo mostrar clientes que tuvieron movimiento
            ->where(function($query) {
                $query->where('ventas.CantidadFacturas', '>', 0)
                      ->orWhere('devoluciones.TotalDevoluciones', '>', 0);
            })
            ->orderBy('VentaNeta', 'desc') // <-- ¡El más rentable primero!
            ->get();

        // 3. Enviar los datos a la vista
        return view('reportes.ventas.rentabilidad_cliente', [
            'reporte' => $reporte,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ]);
    }

    public function flujoVentasCobranzas(Request $request, ContadorDashboardService $dashboardService)
    {
        // 1. Aquí puedes obtener más datos, usando filtros del $request
        // Por ahora, solo tomaremos los últimos 12 meses como ejemplo
        $labels = $dashboardService->obtenerMesesLabels(12);
        $ventas = $dashboardService->obtenerVentasPorMes(12);
        $cobranzas = $dashboardService->obtenerCobranzasPorMes(12);

        // 2. Preparamos un array con la tabla de datos
        $datosTabla = [];
        for ($i = 0; $i < count($labels); $i++) {
            $datosTabla[] = [
                'mes' => $labels[$i],
                'ventas' => $ventas[$i],
                'cobranzas' => $cobranzas[$i],
            ];
        }

        
        return view('reportes.ventas-cobranzas', [
            'labels' => $labels,
            'ventasData' => $ventas,
            'cobranzasData' => $cobranzas,
            'datosTabla' => array_reverse($datosTabla) // Revertimos para ver el más reciente arriba
        ]);
    }

    public function exportarVentasCobranzasExcel(Request $request, ContadorDashboardService $dashboardService)
    {
        // 1. Obtenemos los mismos datos que en el reporte web
        // (Eventualmente aquí leerás los filtros del $request)
        $labels = $dashboardService->obtenerMesesLabels(12);
        $ventas = $dashboardService->obtenerVentasPorMes(12);
        $cobranzas = $dashboardService->obtenerCobranzasPorMes(12);

        // 2. Preparamos el array de la tabla
        $datosTabla = [];
        for ($i = 0; $i < count($labels); $i++) {
            $datosTabla[] = [
                'mes' => $labels[$i],
                'ventas' => $ventas[$i],
                'cobranzas' => $cobranzas[$i],
            ];
        }
        $datosTabla = array_reverse($datosTabla); // Igual que en la web

        // 3. Preparamos los totales
        $totalVentas = array_sum(array_column($datosTabla, 'ventas'));
        $totalCobranzas = array_sum(array_column($datosTabla, 'cobranzas'));
        $totales = [
            'totalVentas' => $totalVentas,
            'totalCobranzas' => $totalCobranzas,
            'totalBrecha' => $totalCobranzas - $totalVentas,
        ];

        // 4. Generamos el nombre del archivo
        $fileName = 'Reporte_Ventas_vs_Cobranzas_' . Carbon::now()->format('Y-m-d') . '.xlsx';

        // 5. ¡LA MAGIA!
        // Le pasamos los datos a nuestra clase de exportación y la descargamos.
        return Excel::download(new VentasCobranzasExport($datosTabla, $totales), $fileName);
    }
}