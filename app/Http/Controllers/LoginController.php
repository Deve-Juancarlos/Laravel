<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\AccesoWeb;

class LoginController extends Controller
{
    
    public function showLoginForm()
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();

                Log::info('Usuario ya autenticado intentando acceder', [
                    'usuario' => $user->usuario,
                    'tipo' => $user->tipousuario
                ]);

                return $this->redirectByRole($user->tipousuario);
            }

            return view('login');

        } catch (\Exception $e) {
            Log::error('Error en showLoginForm: ' . $e->getMessage());
            return view('login')->with('error', 'Error al cargar la página de login');
        }
    }

  
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string|max:50',
            'password' => 'required|string|min:4|max:255',
        ]);

        try {
            Log::info('Intento de login', ['usuario' => $credentials['usuario']]);

        
            $user = AccesoWeb::whereRaw('LOWER(usuario) = ?', [strtolower($credentials['usuario'])])->first();

            if (!$user) {
                Log::warning('Usuario no encontrado', ['usuario' => $credentials['usuario']]);
                return back()->withErrors([
                    'usuario' => 'El usuario no existe en el sistema.'
                ])->withInput();
            }

            if (!Hash::check($credentials['password'], $user->password)) {
                Log::warning('Contraseña incorrecta', ['usuario' => $credentials['usuario']]);
                return back()->withErrors([
                    'password' => 'La contraseña es incorrecta.'
                ])->withInput();
            }

            // ✅ VERIFICAR ESTADO DEL USUARIO
            if (isset($user->estado) && $user->estado !== 'ACTIVO') {
                Log::warning('Usuario inactivo intentando acceder', [
                    'usuario' => $user->usuario,
                    'estado' => $user->estado
                ]);
                return back()->withErrors([
                    'usuario' => 'Este usuario está inactivo. Contacte al administrador.'
                ])->withInput();
            }

            $tipo = strtolower($user->tipousuario ?? '');
            if (!in_array($tipo, ['administrador', 'contador'])) { // Dejamos solo los dos
                Log::warning('Tipo de usuario no válido', ['usuario' => $user->usuario, 'tipo' => $tipo]);
                return back()->withErrors([
                    'usuario' => 'Tipo de usuario no válido. Contacte al administrador.'
                ])->withInput();
            }

            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            Log::info('Login exitoso', ['usuario' => $user->usuario, 'tipo' => $tipo]);

            // ✅ REGISTRAR ACCESO EN HISTORIAL
            $this->registrarAcceso($request, $user->usuario);

            return $this->redirectByRole($tipo);

        } catch (\Exception $e) {
            Log::error('Error en login: ' . $e->getMessage(), [
                'usuario' => $credentials['usuario'] ?? 'desconocido',
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'error' => 'Error interno al iniciar sesión. Inténtelo de nuevo.'
            ])->withInput();
        }
    }


    public function logout(Request $request)
    {
        try {
            $user = Auth::user();

            Log::info('Inicio de logout', [
                'usuario' => $user?->usuario ?? 'desconocido'
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Log::info('Logout exitoso', [
                'usuario' => $user?->usuario ?? 'desconocido'
            ]);

            return redirect()->route('login')
                ->with('success', 'Sesión cerrada correctamente.');

        } catch (\Exception $e) {
            Log::error('Error en logout: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Error al cerrar sesión. Intente nuevamente.');
        }
    }

    /**
     * Redirigir según el rol del usuario
     */
    private function redirectByRole(string $tipoUsuario)
    {
        $tipo = strtolower($tipoUsuario);

        return match ($tipo) {
            'administrador' => redirect()->route('admin.dashboard'),
            #'vendedor'      => redirect()->route(),
            'contador'      => redirect()->route('contador.dashboard.contador'),
            default         => redirect()->route('login')->with('error', 'Tipo de usuario inválido'),
        };
    }

    /**
     * ✅ REGISTRAR ACCESO EN HISTORIAL
     * 
     * Registra el acceso del usuario en la tabla accesoweb_historial
     * capturando IP, navegador y fecha/hora del acceso.
     * 
     * @param Request $request
     * @param string $usuario
     * @return void
     */
    private function registrarAcceso(Request $request, string $usuario)
    {
        try {
            // Verificar si la tabla accesoweb_historial existe
            if (!DB::getSchemaBuilder()->hasTable('accesoweb_historial')) {
                Log::info('Tabla accesoweb_historial no existe, omitiendo registro de acceso');
                return;
            }

            // Insertar registro de acceso
            DB::table('accesoweb_historial')->insert([
                'usuario' => $usuario,
                'fecha_acceso' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Log::info('Acceso registrado en historial', [
                'usuario' => $usuario,
                'ip' => $request->ip()
            ]);

        } catch (\Exception $e) {
            // No detener el login si falla el registro del historial
            Log::warning('Error al registrar acceso en historial: ' . $e->getMessage(), [
                'usuario' => $usuario,
                'error' => $e->getMessage()
            ]);
        }
    }
}
