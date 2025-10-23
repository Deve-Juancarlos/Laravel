<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Exports\CuentasCorrientesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;

class CuentaController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('CtaCliente');

        if ($request->filled('cliente')) {
            $query->where('Codclie', 'LIKE', "%{$request->cliente}%");
        }

        $cuentas = $query->orderBy('Codclie')
                         ->orderBy('FechaF', 'DESC')
                         ->paginate(25);

        return view('admin.cuentas.index', compact('cuentas'));
    }

    public function exportar(Request $request) {
        $query = DB::table('CtaCliente');

        if ($request->filled('cliente')) {
            $query->where('Codclie', 'LIKE', "%{$request->cliente}%");
        }

        $cuentas = $query->orderBy('Codclie')
                        ->orderBy('FechaF', 'DESC')
                        ->get(); // <- usar get() para obtener todos los registros

        return Excel::download(new CuentasCorrientesExport($cuentas), 'cuentas-corrientes.xlsx');
    }
}