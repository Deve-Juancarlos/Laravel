<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // Tu usuario de accesoweb

        return view('user_menu.perfil', compact('user'));
        // o: return view('perfil', ['user' => $user]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:4|confirmed',
        ]);

        $user = Auth::user();

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return back()
            ->withErrors(['current_password' => 'La contraseña actual es incorrecta'])
            ->withInput()
            ->with('tab', 'security');
        }

        // Actualizar contraseña
        $user->password = Hash::make($request->password);
        $user->save();

        return back()
        ->with('success', '¡Contraseña actualizada con éxito!')
        ->with('tab', 'security');
    }

}
