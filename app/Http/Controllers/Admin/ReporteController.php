<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Controller;
use App\Exports\FacturasExport;
use App\Exports\MovimientosExport;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    /**
     * 📋 Reporte unificado: Facturas/Boletas + Notas de Crédito
     */
    public function facturas(Request $request)
    {
        // 🔹 FACTURAS / BOLETAS (tabla doccab)
        $ventas = DB::table('doccab as d')
            ->select(
                'd.Numero',
                DB::raw('d.Tipo as TipoDoc'),
                DB::raw('NULL as TipoNota'),
                'd.CodClie',
                'd.Fecha',
                DB::raw('NULL as Documento'),
                'd.Subtotal as Bruto',
                'd.Igv',
                'd.Total',
                DB::raw('0 as Anulado'), // asumimos que no están anulados si están en doccab y Eliminado=0
                DB::raw('NULL as Observacion'),
                DB::raw("'VENTA' AS fuente")
            )
            ->where('d.Eliminado', 0)
            ->where('d.Tipo', '<', 8);

        // 🔸 NOTAS DE CRÉDITO
        $notas = DB::table('notas_credito as nc')
            ->select(
                'nc.Numero',
                'nc.TipoDoc',
                'nc.TipoNota',
                'nc.Codclie',
                'nc.Fecha',
                'nc.Documento',
                DB::raw('NULL as Bruto'),
                DB::raw('NULL as Igv'),
                'nc.Total',
                'nc.Anulado',
                'nc.Observacion',
                DB::raw("'NC' AS fuente")
            )
            ->where('nc.Anulado', 0);

        $union = $ventas->unionAll($notas);

        $documentos = DB::query()
            ->fromSub($union, 'documentos_unificados')
            ->orderBy('Fecha', 'DESC')
            ->get();

        $page = $request->get('page', 1);
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $paginated = new LengthAwarePaginator(
            $documentos->slice($offset, $perPage)->values(),
            $documentos->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.reportes.facturas', ['documentos' => $paginated]);
    }

    /**
     * 📥 Exportar Facturas/Boletas + Notas de Crédito a Excel
     */
    public function exportFacturas(Request $request)
    {
        // Misma lógica que en facturas(), pero sin paginación
        $ventas = DB::table('doccab as d')
            ->select(
                'd.Numero',
                DB::raw('d.Tipo as TipoDoc'),
                DB::raw('NULL as TipoNota'),
                'd.CodClie',
                'd.Fecha',
                DB::raw('NULL as Documento'),
                'd.Subtotal as Bruto',
                'd.Igv',
                'd.Total',
                DB::raw('0 as Anulado'),
                DB::raw("'VENTA' AS fuente")
            )
            ->where('d.Eliminado', 0)
            ->where('d.Tipo', '<', 8);

        $notas = DB::table('notas_credito as nc')
            ->select(
                'nc.Numero',
                'nc.TipoDoc',
                'nc.TipoNota',
                'nc.Codclie',
                'nc.Fecha',
                'nc.Documento',
                DB::raw('NULL as Bruto'),
                DB::raw('NULL as Igv'),
                'nc.Total',
                'nc.Anulado',
                DB::raw("'NC' AS fuente")
            )
            ->where('nc.Anulado', 0);

        $union = $ventas->unionAll($notas);
        $documentos = DB::query()->fromSub($union, 'documentos_unificados')->get();

        return Excel::download(new FacturasExport($documentos), 'reporte_facturas_' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * 💰 Reporte de Movimientos (Caja + CtaBanco)
     */
    public function movimientos(Request $request)
    {
        $caja = DB::table('Caja as c')
            ->select(
                'c.Numero',
                'c.Documento',
                DB::raw("'caja' as origen"),
                'c.Tipo',
                'c.Fecha',
                'c.Moneda',
                'c.Monto',
                'c.Razon',
                DB::raw('NULL as Cuenta')
            )
            ->where('c.Eliminado', 0);

        $banco = DB::table('CtaBanco as b')
            ->select(
                'b.Numero',
                'b.Documento',
                DB::raw("'banco' as origen"),
                'b.Tipo',
                'b.Fecha',
                DB::raw('1 as Moneda'),
                'b.Monto',
                'b.Clase as Razon',
                'b.Cuenta'
            );

        $union = $banco->unionAll($caja);
        $movimientos = DB::query()->fromSub($union, 'movs')->orderBy('Fecha', 'DESC')->get();

        $page = $request->get('page', 1);
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $paginated = new LengthAwarePaginator(
            $movimientos->slice($offset, $perPage)->values(),
            $movimientos->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.reportes.movimientos', ['movimientos' => $paginated]);
    }

    /**
     * 📥 Exportar Movimientos (Caja + Banco) a Excel
     */
    public function exportMovimientos(Request $request)
    {
        $caja = DB::table('Caja as c')
            ->select(
                'c.Numero',
                'c.Documento',
                DB::raw("'caja' as origen"),
                'c.Tipo',
                'c.Fecha',
                'c.Moneda',
                'c.Monto',
                'c.Razon',
                DB::raw('NULL as Cuenta')
            )
            ->where('c.Eliminado', 0);

        $banco = DB::table('CtaBanco as b')
            ->select(
                'b.Numero',
                'b.Documento',
                DB::raw("'banco' as origen"),
                'b.Tipo',
                'b.Fecha',
                DB::raw('1 as Moneda'),
                'b.Monto',
                'b.Clase as Razon',
                'b.Cuenta'
            );

        $union = $banco->unionAll($caja);
        $movimientos = DB::query()->fromSub($union, 'movs')->get();

        return Excel::download(new MovimientosExport($movimientos), 'reporte_movimientos_' . now()->format('Y-m-d') . '.xlsx');
    }
}