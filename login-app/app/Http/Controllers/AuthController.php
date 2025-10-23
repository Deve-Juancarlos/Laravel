<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\AccesoWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        $usuario = Usuario::where('usuario', $request->usuario)->first();

        if ($usuario && Hash::check($request->password, $usuario->password)) {
            Auth::login($usuario);

            // Redirect based on user role
            if ($usuario->role === 'admin') {
                return redirect()->route('dashboard.admin');
            } elseif ($usuario->role === 'vendedor') {
                return redirect()->route('dashboard.vendedor');
            }
        }

        return back()->withErrors(['usuario' => 'Credenciales incorrectas.']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function showRegistrationForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string|unique:usuarios',
            'password' => 'required|string|confirmed',
            'role' => 'required|string',
        ]);

        $usuario = new Usuario();
        $usuario->usuario = $request->usuario;
        $usuario->password = Hash::make($request->password);
        $usuario->role = $request->role;
        $usuario->save();

        // Optionally, create access record
        AccesoWeb::create(['usuario_id' => $usuario->id]);

        return redirect()->route('login')->with('success', 'Registro exitoso. Puedes iniciar sesión.');
    }
}