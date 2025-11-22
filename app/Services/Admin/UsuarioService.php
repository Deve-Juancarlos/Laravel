<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\AccesoWeb;

class UsuarioService
{
    /**
     * Obtener todos los usuarios con información del empleado vinculado
     */
    public function obtenerUsuarios($filtros = [])
    {
        $query = DB::table('accesoweb')
            ->leftJoin('Empleados', 'accesoweb.idusuario', '=', 'Empleados.Codemp')
            ->select(
                'accesoweb.usuario',
                'accesoweb.tipousuario',
                'accesoweb.idusuario',
                'accesoweb.estado',
                'accesoweb.created_at as ultimoacceso',
                'Empleados.Nombre as empleado_nombre',
                'Empleados.Documento as empleado_dni',
                'Empleados.Telefono1 as empleado_telefono',
                'Empleados.Celular as empleado_celular'
            );

        if (!empty($filtros['tipo'])) {
            $query->where('accesoweb.tipousuario', $filtros['tipo']);
        }
        
        if (!empty($filtros['estado'])) {
            $query->where('accesoweb.estado', $filtros['estado']);
        }

        if (!empty($filtros['buscar'])) {
            $busqueda = trim($filtros['buscar']);
            $query->where(function($q) use ($busqueda) {
                $q->where('accesoweb.usuario', 'like', '%' . $busqueda . '%')
                  ->orWhere('Empleados.Nombre', 'like', '%' . $busqueda . '%')
                  ->orWhere('Empleados.Documento', 'like', '%' . $busqueda . '%');
            });
        }

        return $query->orderBy('accesoweb.tipousuario', 'asc')
                     ->orderBy('Empleados.Nombre', 'asc')
                     ->get();
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function obtenerEstadisticas()
    {
        return [
            'total'           => DB::table('accesoweb')->count(),
            'administradores' => DB::table('accesoweb')->where('tipousuario', 'administrador')->count(),
            'contadores'      => DB::table('accesoweb')->where('tipousuario', 'CONTADOR')->count(),
            'vendedores'      => DB::table('accesoweb')->where('tipousuario', 'VENDEDOR')->count(),
            'sin_vincular'    => DB::table('accesoweb')->whereNull('idusuario')->count(),
            'activos'         => DB::table('accesoweb')->where('estado', 'ACTIVO')->count(),
            'inactivos'       => DB::table('accesoweb')->where('estado', 'INACTIVO')->count(),
        ];
    }

    /**
     * Obtener usuario por nombre con información del empleado
     */
    public function obtenerUsuarioPorNombre($usuario)
    {
        return DB::table('accesoweb')
            ->leftJoin('Empleados', 'accesoweb.idusuario', '=', 'Empleados.Codemp')
            ->select(
                'accesoweb.*',
                'Empleados.Nombre as empleado_nombre',
                'Empleados.Documento as empleado_dni',
                'Empleados.Telefono1 as empleado_telefono',
                'Empleados.Celular as empleado_celular'
            )
            ->where('accesoweb.usuario', $usuario)
            ->first();
    }

    /**
     * Obtener empleados que NO tienen usuario asignado
     */
    public function obtenerEmpleadosSinUsuario()
    {
        return DB::table('Empleados')
            ->leftJoin('accesoweb', 'Empleados.Codemp', '=', 'accesoweb.idusuario')
            ->whereNull('accesoweb.usuario')
            ->select(
                'Empleados.Codemp',
                'Empleados.Nombre',
                'Empleados.Documento',
                'Empleados.Telefono1',
                'Empleados.Celular'
            )
            ->orderBy('Empleados.Nombre', 'asc')
            ->get();
    }

    /**
     * Obtener un empleado específico
     */
    public function obtenerEmpleadoPorId($idEmpleado)
    {
        return DB::table('Empleados')
            ->where('Codemp', $idEmpleado)
            ->first();
    }

    /**
     * Crear un nuevo usuario vinculado a un empleado
     */
    public function crearUsuario($datos)
    {
        try {
            DB::beginTransaction();

            $empleado = $this->obtenerEmpleadoPorId($datos['idusuario']);
            if (!$empleado) {
                DB::rollBack();
                return false;
            }

            $existe = DB::table('accesoweb')
                ->where('idusuario', $datos['idusuario'])
                ->exists();
            if ($existe) {
                DB::rollBack();
                return false;
            }

            DB::table('accesoweb')->insert([
                'usuario' => $datos['usuario'],
                'password' => Hash::make($datos['password']),
                'tipousuario' => $datos['tipousuario'],
                'idusuario' => $datos['idusuario'],
                'estado' => 'ACTIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->registrarAuditoria('CREAR_USUARIO', "Usuario {$datos['usuario']} creado y vinculado al empleado {$empleado->Nombre}");
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear usuario: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar rol de un usuario
     */
    public function cambiarRol($usuario, $nuevoRol)
    {
        try {
            $usuarioData = $this->obtenerUsuarioPorNombre($usuario);
            $rolAnterior = $usuarioData->tipousuario ?? 'DESCONOCIDO';

            DB::table('accesoweb')
                ->where('usuario', $usuario)
                ->update([
                    'tipousuario' => $nuevoRol,
                    'updated_at' => now(),
                ]);

            $this->registrarAuditoria('CAMBIAR_ROL', "Usuario {$usuario}: Rol cambiado de {$rolAnterior} a {$nuevoRol}");
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al cambiar rol: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar usuario (cambiar empleado vinculado o rol)
     */
    public function actualizarUsuario($usuario, $datos)
    {
        try {
            $usuarioData = $this->obtenerUsuarioPorNombre($usuario);
            $empleadoAnterior = $usuarioData->empleado_nombre ?? 'Sin asignar';
            $nuevoEmpleado = $this->obtenerEmpleadoPorId($datos['idusuario']);

            if (!$nuevoEmpleado) {
                return false;
            }

            DB::table('accesoweb')
                ->where('usuario', $usuario)
                ->update([
                    'idusuario' => $datos['idusuario'],
                    'tipousuario' => $datos['tipousuario'],
                    'updated_at' => now(),
                ]);

            $this->registrarAuditoria(
                'ACTUALIZAR_USUARIO',
                "Usuario {$usuario}: Empleado cambiado de {$empleadoAnterior} a {$nuevoEmpleado->Nombre}, Rol: {$datos['tipousuario']}"
            );
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al actualizar usuario: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resetear contraseña de usuario
     */
    public function resetearPassword($usuario, $nuevaPassword)
    {
        try {
            DB::table('accesoweb')
                ->where('usuario', $usuario)
                ->update([
                    'password' => Hash::make($nuevaPassword),
                    'updated_at' => now(),
                ]);

            $this->registrarAuditoria('RESETEAR_PASSWORD', "Contraseña restablecida para usuario {$usuario}");
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al resetear contraseña: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar acción en auditoría del sistema
     * 
     * @param string $accion Tipo de acción realizada
     * @param string $descripcion Descripción detallada de la acción
     * @param string $tabla Tabla afectada por la acción
     * @return void
     */
    private function registrarAuditoria($accion, $descripcion, $tabla = 'accesoweb')
    {
        try {
            // Obtener usuario de forma segura
            $usuario = 'SISTEMA';
            
            if (Auth::check()) {
                /** @var AccesoWeb $usuarioAuth */
                $usuarioAuth = Auth::user();
                $usuario = $usuarioAuth->usuario ?? 'SISTEMA';
            }
            
            // Capturar información adicional
            $ip = request()->ip() ?? 'DESCONOCIDA';
            $userAgent = substr(request()->header('User-Agent', 'DESCONOCIDO'), 0, 100);
            
            // Construir detalle completo
            $detalleCompleto = sprintf(
                '%s | IP: %s | UA: %s',
                $descripcion,
                $ip,
                $userAgent
            );
            
            // Insertar registro de auditoría
            DB::table('Auditoria_Sistema')->insert([
                'usuario' => $usuario,
                'accion' => $accion,
                'tabla' => $tabla,
                'detalle' => $detalleCompleto,
                'fecha' => now(),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al registrar auditoría: ' . $e->getMessage(), [
                'accion' => $accion,
                'tabla' => $tabla,
                'descripcion' => $descripcion,
            ]);
        }
    }

    /**
     * Desactivar usuario
     */
    public function desactivarUsuario($usuario)
    {
        try {
            DB::table('accesoweb')
                ->where('usuario', $usuario)
                ->update([
                    'estado' => 'INACTIVO',
                    'updated_at' => now()
                ]);
            
            $this->registrarAuditoria('DESACTIVAR_USUARIO', "Usuario {$usuario} desactivado");
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al desactivar usuario: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener historial de accesos
     */
    public function obtenerHistorialAccesos($usuario)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('accesoweb_historial')) {
                return collect();
            }
            
            return DB::table('accesoweb_historial')
                ->where('usuario', $usuario)
                ->orderBy('fecha_acceso', 'desc')
                ->limit(50)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error al obtener historial: ' . $e->getMessage());
            return collect(); 
        }
    }

    /**
     * Activar usuario
     */
    public function activarUsuario($usuario)
    {
        try {
            DB::table('accesoweb')
                ->where('usuario', $usuario)
                ->update([
                    'estado' => 'ACTIVO',
                    'updated_at' => now()
                ]);
            
            $this->registrarAuditoria('ACTIVAR_USUARIO', "Usuario {$usuario} activado");
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al activar usuario: ' . $e->getMessage());
            return false;
        }
    }
}
