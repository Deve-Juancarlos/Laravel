<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Contabilidad\ContadorDashboardService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VentasCobranzasExport;
use Carbon\Carbon;


use App\Models\Cliente;
use App\Models\Doccab;
use App\Models\NotaCredito;

class ReporteVentasController extends Controller
{
    
    public function rentabilidadCliente(Request $request)
    {
        
        $fechaInicio = $request->input('fecha_inicio', now('America/Lima')->subDays(30)->toDateString());
        $fechaFin    = $request->input('fecha_fin', now('America/Lima')->toDateString());

        
        
        $reporte = Cliente::select('Codclie', 'Razon', 'Limite as LimiteCredito', 'Telefono1')
            // (A) Subconsulta para sumar todas las VENTAS (Doccab)
            ->withCount(['facturas as CantidadFacturas' => function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                      ->where('Eliminado', 0);
            }])
            ->withSum(['facturas as TotalFacturado' => function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                      ->where('Eliminado', 0);
            }], 'Total')
            ->withSum(['facturas as TotalDescuentos' => function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                      ->where('Eliminado', 0);
            }], 'Descuento')
            
            // (B) Subconsulta para sumar todas las DEVOLUCIONES (notas_credito)
            ->withSum(['notasCredito as TotalDevoluciones' => function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                      ->where('Anulado', 0);
            }], 'Total')
            
            // (C) Obtenemos los resultados
            ->get()
            
            // (D) Calculamos la Venta Neta en PHP
            ->map(function ($cliente) {
                $cliente->TotalFacturado = $cliente->TotalFacturado ?? 0;
                $cliente->TotalDescuentos = $cliente->TotalDescuentos ?? 0;
                $cliente->TotalDevoluciones = $cliente->TotalDevoluciones ?? 0;
                
                $cliente->VentaNeta = $cliente->TotalFacturado - $cliente->TotalDevoluciones - $cliente->TotalDescuentos;
                return $cliente;
            })
            
            // (E) Filtramos clientes que SÍ tuvieron movimiento
            ->filter(function ($cliente) {
                return $cliente->CantidadFacturas > 0 || $cliente->TotalDevoluciones > 0;
            })
            
            // (F) Ordenamos por el más rentable
            ->sortByDesc('VentaNeta');


        // 3. Enviar los datos a la vista
        return view('reportes.ventas.rentabilidad_cliente', [
            'reporte' => $reporte,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ]);
    }

    /**
     * Este método está PERFECTO. Reutiliza el servicio.
     */
    public function flujoVentasCobranzas(Request $request, ContadorDashboardService $dashboardService)
    {
        // 1. (El servicio ya está corregido con la hora peruana y el GROUP BY)
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

    /**
     * Este método también está PERFECTO.
     */
    public function exportarVentasCobranzasExcel(Request $request, ContadorDashboardService $dashboardService)
    {
        $labels = $dashboardService->obtenerMesesLabels(12);
        $ventas = $dashboardService->obtenerVentasPorMes(12);
        $cobranzas = $dashboardService->obtenerCobranzasPorMes(12);

        $datosTabla = [];
        for ($i = 0; $i < count($labels); $i++) {
            $datosTabla[] = [
                'mes' => $labels[$i],
                'ventas' => $ventas[$i],
                'cobranzas' => $cobranzas[$i],
            ];
        }
        $datosTabla = array_reverse($datosTabla);

        $totalVentas = array_sum(array_column($datosTabla, 'ventas'));
        $totalCobranzas = array_sum(array_column($datosTabla, 'cobranzas'));
        $totales = [
            'totalVentas' => $totalVentas,
            'totalCobranzas' => $totalCobranzas,
            'totalBrecha' => $totalCobranzas - $totalVentas,
        ];

        $fileName = 'Reporte_Ventas_vs_Cobranzas_' . Carbon::now('America/Lima')->format('Y-m-d') . '.xlsx';

        return Excel::download(new VentasCobranzasExport($datosTabla, $totales), $fileName);
    }
}