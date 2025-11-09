<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Este controlador genera reportes analíticos sobre el Inventario.
 */
class ReporteInventarioController extends Controller
{
    /**
     * Muestra el reporte de Sugerencias de Compra (Reposición de Stock).
     * Compara el Stock Mínimo (de Productos) vs el Stock Actual (de Saldos).
     */
    public function sugerenciasCompra(Request $request)
    {
        // 1. La Consulta de Sugerencias
        $reporte = DB::table('Productos as p')
            // Unimos con Laboratorios para saber de quién es el producto
            ->join('Laboratorios as l', DB::raw('RTRIM(l.CodLab)'), '=', DB::raw('LEFT(p.CodPro, 2)'))
            
            // (A) Subconsulta para obtener el stock actual REAL de la tabla Saldos
            ->leftJoinSub(
                DB::table('Saldos')
                    ->select('codpro', DB::raw('SUM(saldo) as stock_actual'))
                    ->groupBy('codpro'),
                'stock', // Nombramos a esta subconsulta 'stock'
                'stock.codpro', '=', 'p.CodPro'
            )
            
            // (B) Seleccionamos los campos y calculamos la sugerencia
            ->select(
                'p.CodPro',
                'p.Nombre',
                'l.Descripcion as Laboratorio',
                'p.Minimo', // El Stock Mínimo que definiste
                DB::raw('ISNULL(stock.stock_actual, 0) as stock_actual'),
                
                // La cantidad sugerida es: (Mínimo que debo tener) - (Stock que tengo)
                DB::raw('(p.Minimo - ISNULL(stock.stock_actual, 0)) as CantidadSugerida')
            )
            
            // (C) La Lógica Principal:
            ->where('p.Eliminado', 0)     // Solo productos activos
            ->where('p.Minimo', '>', 0)   // Solo productos a los que les definiste un mínimo
            ->whereRaw('ISNULL(stock.stock_actual, 0) < p.Minimo') // ¡LA CLAVE!
            
            ->orderBy('p.Nombre')
            ->paginate(50); // Paginar por si la lista es muy larga

        // 2. Enviar los datos a la vista
        return view('reportes.inventario.sugerencias_compra', [
            'reporte' => $reporte,
        ]);
    }

    public function productosPorVencer(Request $request)
    {
        // 1. Validar el filtro.
        // Por defecto, mostramos todo lo que vence en 180 días o menos.
        $request->validate([
            'dias_maximos' => 'nullable|integer|min:1|max:730'
        ]);
        
        // El filtro de la vista (v_productos_por_vencer) ya está en 180 días.
        // Si el usuario no filtra, usamos 180.
        $diasMaximos = $request->input('dias_maximos', 180);

        // 2. La Consulta (¡Súper simple gracias a la Vista!)
        $reporte = DB::table('v_productos_por_vencer as v')
            ->select(
                'v.CodPro',
                'v.Nombre',
                'v.Laboratorio',
                'v.Lote',
                'v.Almacen',
                'v.Vencimiento',
                'v.Stock',
                'v.DiasParaVencer',
                'v.EstadoVencimiento',
                'v.ValorInventario' // ¡Importante! Cuánto dinero representa
            )
            // Filtramos por los días que el usuario pidió
            ->where('v.DiasParaVencer', '<=', $diasMaximos)
            
            ->orderBy('v.DiasParaVencer', 'asc') // ¡El más crítico primero!
            ->paginate(50)
            ->appends($request->query()); // Mantener el filtro al paginar

        // Calculamos el total de pérdida potencial
        $totalPerdida = DB::table('v_productos_por_vencer as v')
                            ->where('v.DiasParaVencer', '<=', $diasMaximos)
                            ->sum('v.ValorInventario');

        // 3. Enviar los datos a la vista
        return view('reportes.inventario.por_vencer', [
            'reporte' => $reporte,
            'diasMaximos' => $diasMaximos,
            'totalPerdida' => $totalPerdida,
        ]);
    }

    // ... Aquí puedes añadir el reporte de Vencimientos, Rotación ABC, etc. ...
}