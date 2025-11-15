<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('Auditoria_Sistema')->orderBy('fecha', 'desc');

        if ($request->filled('usuario')) {
            $query->where('usuario', 'like', '%' . trim($request->usuario) . '%');
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        $logs = $query->paginate(50)->appends($request->only(['usuario', 'accion', 'fecha_desde', 'fecha_hasta']));


        $acciones = DB::table('Auditoria_Sistema')
            ->select('accion')
            ->distinct()
            ->orderBy('accion')
            ->pluck('accion');

        return view('admin.auditoria.index', compact('logs', 'acciones'));
    }
}