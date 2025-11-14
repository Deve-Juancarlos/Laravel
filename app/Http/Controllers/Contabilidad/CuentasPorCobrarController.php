<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CuentasPorCobrarController extends Controller
{
    protected $connection = 'sqlsrv';

    
    public function index(Request $request)
    {
        // --- 1. Obtener Filtros ---
        $filtros = [
            'cliente' => $request->input('cliente'),
            'vendedor' => $request->input('vendedor'),
            'estado' => $request->input('estado', 'pendientes'), // 'pendientes', 'vencidas', 'todas'
        ];

        // --- 2. Consulta Principal de Deudas ---
        $query = DB::connection($this->connection)->table('CtaCliente as cc')
            ->join('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
            ->leftJoin('Empleados as e', 'c.Vendedor', '=', 'e.Codemp') // Unimos con Vendedores
            ->where('cc.Saldo', '>', 0) // ¡Solo documentos con deuda!
            ->select(
                'c.Codclie',
                'c.Razon',
                'c.Documento as RucCliente',
                'e.Nombre as VendedorNombre',
                'cc.Documento',
                'cc.Tipo',
                'cc.FechaF',
                'cc.FechaV',
                'cc.Importe',
                'cc.Saldo',
                // Calculamos los días vencidos
                DB::raw("DATEDIFF(day, cc.FechaV, GETDATE()) AS dias_vencidos")
            );

        // --- 3. Aplicar Filtros ---
        if ($filtros['cliente']) {
            $query->where('c.Razon', 'LIKE', '%' . $filtros['cliente'] . '%');
        }
        if ($filtros['vendedor']) {
            $query->where('e.Codemp', $filtros['vendedor']);
        }
        if ($filtros['estado'] == 'vencidas') {
            $query->whereRaw("DATEDIFF(day, cc.FechaV, GETDATE()) > 0");
        }

        $documentos = $query->orderBy('dias_vencidos', 'desc')->paginate(50);

        $kpisQuery = DB::connection($this->connection)->table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->select(
                DB::raw('SUM(Saldo) as totalDeuda'),
                DB::raw("SUM(CASE WHEN DATEDIFF(day, FechaV, GETDATE()) > 0 THEN Saldo ELSE 0 END) as totalVencido"),
                DB::raw("SUM(CASE WHEN DATEDIFF(day, FechaV, GETDATE()) <= 0 THEN Saldo ELSE 0 END) as totalPorVencer"),
                DB::raw("SUM(CASE WHEN DATEDIFF(day, FechaV, GETDATE()) BETWEEN 1 AND 30 THEN Saldo ELSE 0 END) as aging_1_30"),
                DB::raw("SUM(CASE WHEN DATEDIFF(day, FechaV, GETDATE()) BETWEEN 31 AND 60 THEN Saldo ELSE 0 END) as aging_31_60"),
                DB::raw("SUM(CASE WHEN DATEDIFF(day, FechaV, GETDATE()) > 60 THEN Saldo ELSE 0 END) as aging_60_mas")
            )->first();
            
        // --- 5. Obtener listas para filtros ---
        $vendedores = DB::connection($this->connection)->table('Empleados')->get();

        return view('contabilidad.cxc.index', [
            'documentos' => $documentos,
            'kpis' => (array) $kpisQuery,
            'vendedores' => $vendedores,
            'filtros' => $filtros,
        ]);
    }
}