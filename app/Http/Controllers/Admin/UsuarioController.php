<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class UsuarioController extends Controller
{
    public function index()
    {
        // Cargar todos los usuarios de accesoweb
        $usuarios = DB::table('accesoweb')
            ->orderBy('tipousuario', 'DESC')
            ->orderBy('usuario')
            ->get();

        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function roles($usuario)
    {
        $user = DB::table('accesoweb')->where('usuario', $usuario)->first();
        if (!$user) {
            abort(404, 'Usuario no encontrado');
        }

        // No permitir editar al usuario actual (seguridad)
        if ($usuario === Auth::user()->usuario) {
            return redirect()->route('admin.usuarios.index')
                             ->with('error', 'No puedes modificar tu propio rol.');
        }

        return view('admin.usuarios.roles', compact('user'));
    }

    public function updateRol(Request $request, $usuario)
    {
       
        $request->validate([
            'tipousuario' => 'required|in:ADMIN,USER',
        ]);

        
        if ($usuario === Auth::user()->usuario) {
            return back()->withErrors(['error' => 'No puedes modificar tu propio rol.']);
        }

        
        $user = DB::table('accesoweb')->where('usuario', $usuario)->first();
        if (!$user) {
            abort(404);
        }

        try {
            DB::table('accesoweb')
                ->where('usuario', $usuario)
                ->update(['tipousuario' => $request->tipousuario]);

            return redirect()->route('admin.usuarios.index')
                             ->with('success', 'Rol actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el rol: ' . $e->getMessage()]);
        }
    }
}