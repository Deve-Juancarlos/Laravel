<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // Tu usuario de accesoweb

        return view('user_menu.configuracion', compact('user'));
        // o: return view('perfil', ['user' => $user]);
    }
}
