<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
}