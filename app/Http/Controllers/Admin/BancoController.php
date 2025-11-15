<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BancoController extends Controller
{
    public function index()
    {
        $bancos = DB::table('Bancos')->orderBy('Cuenta')->get();
        return view('admin.bancos.index', compact('bancos'));
    }

    public function create()
    {
        return view('admin.bancos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Cuenta' => 'required|string|max:20|unique:Bancos,Cuenta',
            'Banco'  => 'required|string|max:50', // ✅ Corregido: era 'Nombre'
            'Moneda' => 'required|in:1,2',
        ]);

        try {
            DB::table('Bancos')->insert([
                'Cuenta' => $request->Cuenta,
                'Banco'  => $request->Banco, // ✅ Corregido
                'Moneda' => $request->Moneda,
            ]);

            return redirect()->route('admin.bancos.index')
                ->with('success', 'Cuenta bancaria creada exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear la cuenta: ' . $e->getMessage()]);
        }
    }

    public function edit($cuenta)
    {
        $banco = DB::table('Bancos')->where('Cuenta', $cuenta)->first();
        if (!$banco) {
            abort(404, 'Cuenta bancaria no encontrada');
        }
        return view('admin.bancos.edit', compact('banco'));
    }

    public function update(Request $request, $cuenta)
    {
        $request->validate([
            'Banco'  => 'required|string|max:50', // ✅ Corregido
            'Moneda' => 'required|in:1,2',
        ]);

        $banco = DB::table('Bancos')->where('Cuenta', $cuenta)->first();
        if (!$banco) {
            abort(404);
        }

        try {
            DB::table('Bancos')
                ->where('Cuenta', $cuenta)
                ->update([
                    'Banco'  => $request->Banco, // ✅ Corregido
                    'Moneda' => $request->Moneda,
                ]);

            return redirect()->route('admin.bancos.index')
                ->with('success', 'Cuenta bancaria actualizada exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    public function destroy($cuenta)
    {
        // Validar que no esté en uso en CtaBanco
        $enUso = DB::table('CtaBanco')->where('Cuenta', $cuenta)->exists();
        if ($enUso) {
            return back()->withErrors(['error' => 'No se puede eliminar: la cuenta está en uso en movimientos bancarios.']);
        }

        DB::table('Bancos')->where('Cuenta', $cuenta)->delete();
        return back()->with('success', 'Cuenta bancaria eliminada.');
    }
}