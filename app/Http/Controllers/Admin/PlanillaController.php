<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class PlanillaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        
        // Obtener planillas con paginación
        $planillas = DB::table('PlanC_cobranza')
            ->orderBy('FechaCrea', 'DESC')
            ->paginate($perPage);

        // Cargar vendedores para mostrar nombres
        $vendedores = collect(DB::select('SELECT Codemp, Nombre FROM Empleados WHERE Tipo = 1'));

        return view('admin.planillas.index', compact('planillas', 'vendedores'));
    }

    public function show($serie, $numero)
    {
        $planilla = DB::selectOne('SELECT * FROM PlanC_cobranza WHERE Serie = ? AND Numero = ?', [$serie, $numero]);
        if (!$planilla) {
            abort(404, 'Planilla no encontrada');
        }

        $detalle = collect(DB::select('SELECT * FROM PlanD_cobranza WHERE Serie = ? AND Numero = ?', [$serie, $numero]));
        $vendedores = collect(DB::select('SELECT Codemp, Nombre FROM Empleados WHERE Tipo = 1'));

        return view('admin.planillas.show', compact('planilla', 'detalle', 'vendedores'));
    }

    public function edit($serie, $numero)
    {
        $planilla = DB::selectOne('SELECT * FROM PlanC_cobranza WHERE Serie = ? AND Numero = ?', [$serie, $numero]);
        if (!$planilla) {
            abort(404, 'Planilla no encontrada');
        }

        if ($planilla->Confirmacion || $planilla->Impreso) {
            return redirect()->route('admin.planillas.show', [$serie, $numero])
                             ->with('error', 'No se puede editar una planilla confirmada o impresa.');
        }

        $vendedores = DB::select('SELECT Codemp, Nombre FROM Empleados WHERE Tipo = 1');
        return view('admin.planillas.edit', compact('planilla', 'vendedores'));
    }

    public function update(Request $request, $serie, $numero)
    {
        $request->validate([
            'Vendedor' => 'required|integer',
            'FechaCrea' => 'required|date',
            'FechaIng' => 'required|date|after_or_equal:FechaCrea',
            'Confirmacion' => 'required|in:0,1',
            'Impreso' => 'required|in:0,1',
        ]);

        $planilla = DB::selectOne('SELECT * FROM PlanC_cobranza WHERE Serie = ? AND Numero = ?', [$serie, $numero]);
        if (!$planilla || $planilla->Confirmacion || $planilla->Impreso) {
            return back()->withErrors(['error' => 'No se puede editar esta planilla']);
        }

        try {
            DB::update('
                UPDATE PlanC_cobranza 
                SET Vendedor = ?, FechaCrea = ?, FechaIng = ?, Confirmacion = ?, Impreso = ?
                WHERE Serie = ? AND Numero = ?
            ', [
                $request->Vendedor,
                $request->FechaCrea,
                $request->FechaIng,
                $request->Confirmacion,
                $request->Impreso,
                $serie,
                $numero
            ]);

            return redirect()->route('admin.planillas.show', [$serie, $numero])
                             ->with('success', 'Planilla actualizada correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    public function destroy($serie, $numero)
    {
        if (Auth::user()->tipousuario !== 'ADMIN') {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        try {
            DB::select('EXEC sp_planilla_eliminar ?, ?, ?', [
                $serie,
                $numero,
                Auth::user()->usuario
            ]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}