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

    public function register(Request $request) {
        $request->validate([
            'usuario' => 'required|string|max:50|unique:accesoweb,usuario',
            'tipousuario' => 'required|string|in:administrador,vendedor,contador',
            'password' => 'required|string|min:4|max:255|confirmed',
        ]);

        $maxId = AccesoWeb::max('idusuario') ?? 0;

        $user = AccesoWeb::create([
            'usuario'     => $request->usuario,
            'tipousuario' => $request->tipousuario,
            'idusuario'   => $maxId + 1,
            'password'    => Hash::make($request->password),
        ]);

        // REDIRIGIR AL MENSAJE BONITO
        return redirect()->route('welcome.message')
                        ->with('user_name', $request->usuario); // Tu campo es 'usuario'
    }
}
