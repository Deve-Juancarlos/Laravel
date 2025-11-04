<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CuentasPorPagarController extends Controller
{
    protected $connection = 'sqlsrv';

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $filtros = [
            'proveedor' => $request->input('proveedor'),
            'estado' => $request->input('estado', 'pendientes'), 
        ];

        $query = DB::connection($this->connection)->table('CtaProveedor as cp')
            ->join('Proveedores as p', 'cp.CodProv', '=', 'p.CodProv')
            ->where('cp.Saldo', '>', 0) 
            ->select(
                'p.CodProv',
                'p.RazonSocial',
                'p.Ruc as RucProveedor',
                'cp.Documento', 
                'cp.Tipo',
                'cp.FechaF',
                'cp.FechaV',
                'cp.Importe',
                'cp.Saldo',
                DB::raw("DATEDIFF(day, cp.FechaV, GETDATE()) AS dias_vencidos")
            );

        if ($filtros['proveedor']) {
            $query->where('p.RazonSocial', 'LIKE', '%' . $filtros['proveedor'] . '%');
        }
        if ($filtros['estado'] == 'vencidas') {
            $query->whereRaw("DATEDIFF(day, cp.FechaV, GETDATE()) > 0");
        }

        $documentos = $query->orderBy('dias_vencidos', 'desc')->paginate(50);

        $kpisQuery = DB::connection($this->connection)->table('CtaProveedor')
            ->where('Saldo', '>', 0)
            ->select(
                DB::raw('SUM(Saldo) as totalDeuda'),
                DB::raw("SUM(CASE WHEN DATEDIFF(day, FechaV, GETDATE()) > 0 THEN Saldo ELSE 0 END) as totalVencido"),
                DB::raw("SUM(CASE WHEN DATEDIFF(day, FechaV, GETDATE()) <= 0 THEN Saldo ELSE 0 END) as totalPorVencer"),
                DB::raw("SUM(CASE WHEN DATEDIFF(day, FechaV, GETDATE()) BETWEEN 1 AND 30 THEN Saldo ELSE 0 END) as aging_1_30"),
                DB::raw("SUM(CASE WHEN DATEDIFF(day, FechaV, GETDATE()) BETWEEN 31 AND 60 THEN Saldo ELSE 0 END) as aging_31_60"),
                DB::raw("SUM(CASE WHEN DATEDIFF(day, FechaV, GETDATE()) > 60 THEN Saldo ELSE 0 END) as aging_60_mas")
            )->first();
            
        return view('contabilidad.cxp.index', [ 
            'documentos' => $documentos,
            'kpis' => (array) $kpisQuery,
            'filtros' => $filtros,
        ]);
    }
}