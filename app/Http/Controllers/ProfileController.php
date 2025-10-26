<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * PERFIL DE USUARIO - Gestión de perfil basado en tabla accesoweb
     * Sistema SIFANO - Farmacia/Contabilidad
     */

    public function __construct()
    {
        $this->middleware(['auth', 'check.admin']);
    }

    /**
     * Muestra el dashboard del perfil de usuario
     */
    public function index()
    {
        try {
            $usuario = Auth::user();
            
            // Obtener estadísticas de actividad del usuario
            $estadisticas = $this->obtenerEstadisticasUsuario($usuario->idusuario);
            
            // Obtener actividad reciente
            $actividad_reciente = $this->obtenerActividadReciente($usuario->idusuario);

            return [
                'usuario' => $usuario,
                'estadisticas' => $estadisticas,
                'actividad_reciente' => $actividad_reciente
            ];

        } catch (\Exception $e) {
            \Log::error('Error en ProfileController::index: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar perfil'], 500);
        }
    }

    /**
     * Muestra el formulario de edición de perfil
     */
    public function edit()
    {
        $usuario = Auth::user();
        
        return [
            'usuario' => $usuario,
            'tipos_usuario' => $this->obtenerTiposUsuario()
        ];
    }

    /**
     * Actualiza los datos del perfil del usuario
     */
    public function update(Request $request)
    {
        $usuario = Auth::user();

        $validacion = $this->validarDatosPerfil($request, $usuario->idusuario);
        if ($validacion['errors']) {
            return response()->json($validacion, 400);
        }

        try {
            DB::beginTransaction();

            // Actualizar usuario en tabla accesoweb
            DB::table('accesoweb')->where('idusuario', $usuario->idusuario)->update([
                'usuario' => $request->usuario,
                'tipousuario' => $request->tipousuario,
                'updated_at' => now()
            ]);

            // Si se proporciona nueva contraseña, la actualiza
            if ($request->nueva_password) {
                DB::table('accesoweb')->where('idusuario', $usuario->idusuario)->update([
                    'password' => Hash::make($request->nueva_password)
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Perfil actualizado exitosamente',
                'usuario' => DB::table('accesoweb')->where('idusuario', $usuario->idusuario)->first()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error al actualizar perfil: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cambia la contraseña del usuario
     */
    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required',
            'nueva_password' => 'required|min:6|confirmed',
        ]);

        $usuario = Auth::user();

        // Verificar contraseña actual
        if (!Hash::check($request->password_actual, $usuario->password)) {
            return response()->json(['error' => 'La contraseña actual es incorrecta'], 400);
        }

        try {
            DB::table('accesoweb')->where('idusuario', $usuario->idusuario)->update([
                'password' => Hash::make($request->nueva_password),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Contraseña actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar contraseña'], 500);
        }
    }

    /**
     * Muestra configuraciones personales del usuario
     */
    public function configuraciones()
    {
        $usuario = Auth::user();
        
        // Aquí se podrían obtener configuraciones específicas del usuario
        // como idioma preferido, tema, etc.
        
        return [
            'usuario' => $usuario,
            'configuraciones' => [
                'idioma' => 'es',
                'tema' => 'light',
                'notificaciones' => true
            ]
        ];
    }

    /**
     * Actualiza configuraciones personales
     */
    public function actualizarConfiguraciones(Request $request)
    {
        $usuario = Auth::user();
        
        // Validar configuraciones
        $request->validate([
            'idioma' => 'nullable|in:es,en',
            'tema' => 'nullable|in:light,dark',
            'notificaciones' => 'nullable|boolean'
        ]);

        // Aquí se guardarían las configuraciones en una tabla específica
        // Por ahora solo respondemos exitosamente
        
        return response()->json([
            'success' => true,
            'mensaje' => 'Configuraciones actualizadas exitosamente'
        ]);
    }

    /**
     * Elimina la cuenta del usuario (soft delete)
     */
    public function eliminarCuenta(Request $request)
    {
        $usuario = Auth::user();
        
        // Verificar que no tenga procesos pendientes si es necesario
        // Por ejemplo, facturas pendientes, etc.

        try {
            DB::beginTransaction();

            // Marcar usuario como inactivo (soft delete)
            DB::table('accesoweb')->where('idusuario', $usuario->idusuario)->update([
                'usuario' => 'INACTIVO_' . $usuario->idusuario,
                'updated_at' => now()
            ]);

            // Cerrar sesión del usuario
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Cuenta eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error al eliminar cuenta'], 500);
        }
    }

    /**
     * ===============================================
     * MÉTODOS PRIVADOS DE APOYO
     * ===============================================
     */

    /**
     * Obtiene estadísticas de actividad del usuario
     */
    private function obtenerEstadisticasUsuario($idusuario)
    {
        // Aquí se podrían obtener estadísticas reales basadas en 
        // las acciones del usuario en el sistema
        
        return [
            'total_sesiones' => rand(10, 100), // Temporal
            'ultimo_acceso' => now(),
            'acciones_realizadas' => rand(50, 500), // Temporal
            'tiempo_total_activo' => '45 horas' // Temporal
        ];
    }

    /**
     * Obtiene actividad reciente del usuario
     */
    private function obtenerActividadReciente($idusuario)
    {
        // Aquí se obtendría de una tabla de logs o auditoria
        // Por ahora devolvemos datos de ejemplo
        
        return [
            [
                'accion' => 'Acceso al sistema',
                'fecha' => now()->subHours(2),
                'descripcion' => 'Inicio de sesión exitoso'
            ],
            [
                'accion' => 'Gestión de clientes',
                'fecha' => now()->subHours(3),
                'descripcion' => 'Actualización de datos de cliente'
            ]
        ];
    }

    /**
     * Obtiene tipos de usuario disponibles
     */
    private function obtenerTiposUsuario()
    {
        return [
            'administrador' => 'Administrador',
            'vendedor' => 'Vendedor',
            'contador' => 'Contador'
        ];
    }

    /**
     * Valida datos del perfil
     */
    private function validarDatosPerfil(Request $request, $idusuario)
    {
        $rules = [
            'usuario' => [
                'required',
                'string',
                'max:50',
                Rule::unique('accesoweb', 'usuario')->ignore($idusuario, 'idusuario')
            ],
            'tipousuario' => 'required|in:administrador,vendedor,contador',
            'nueva_password' => 'nullable|min:6|confirmed'
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return [
                'errors' => $validator->errors(),
                'valid' => false
            ];
        }

        return ['valid' => true, 'errors' => null];
    }
}