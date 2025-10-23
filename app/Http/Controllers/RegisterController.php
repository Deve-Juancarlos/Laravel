<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccesoWeb;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        // ✅ 1. Valida contra la tabla real 'accesoweb'
        $request->validate([
            'usuario' => 'required|string|max:50|unique:accesoweb,usuario',
            'tipousuario' => 'required|string|in:administrador,vendedor,contador',
            'password' => 'required|string|min:4|max:255|confirmed',
        ]);

        // ✅ 2. Calcula el nuevo idusuario manualmente (porque no es autoincremental)
        $maxId = AccesoWeb::max('idusuario') ?? 0;

        // ✅ 3. Crea el registro
        AccesoWeb::create([
            'usuario'     => $request->usuario,
            'tipousuario' => $request->tipousuario,
            'idusuario'   => $maxId + 1,
            'password'    => Hash::make($request->password),
        ]);

        return redirect()->route('login')->with('success', '✅ Registro exitoso. Ahora puedes iniciar sesión.');
    }
}
