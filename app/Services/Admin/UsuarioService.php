<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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
                'accesoweb.created_at as ultimoacceso',
                'Empleados.Nombre as empleado_nombre',
                'Empleados.Documento as empleado_dni',
                'Empleados.Telefono1 as empleado_telefono',
                'Empleados.Celular as empleado_celular',
                DB::raw('CASE WHEN accesoweb.idusuario IS NOT NULL THEN 1 ELSE 0 END as estado') // <- Estado virtual
            );

        if (!empty($filtros['tipo'])) {
            $query->where('accesoweb.tipousuario', $filtros['tipo']);
        }

        if (!empty($filtros['buscar'])) {
            $query->where(function($q) use ($filtros) {
                $q->where('accesoweb.usuario', 'like', '%' . $filtros['buscar'] . '%')
                ->orWhere('Empleados.Nombre', 'like', '%' . $filtros['buscar'] . '%')
                ->orWhere('Empleados.Documento', 'like', '%' . $filtros['buscar'] . '%');
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
            'administradores' => DB::table('accesoweb')->where('tipousuario', 'ADMIN')->count(),
            'contadores'      => DB::table('accesoweb')->where('tipousuario', 'CONTADOR')->count(),
            'vendedores'      => DB::table('accesoweb')->where('tipousuario', 'VENDEDOR')->count(),
            'sin_vincular'    => DB::table('accesoweb')->whereNull('idusuario')->count(),
            'activos'         => DB::table('accesoweb')->whereNotNull('idusuario')->count(), 
            'inactivos'       => DB::table('accesoweb')->whereNull('idusuario')->count(),    
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
            $empleado = $this->obtenerEmpleadoPorId($datos['idusuario']);
            if (!$empleado) return false;

            $existe = DB::table('accesoweb')
                ->where('idusuario', $datos['idusuario'])
                ->exists();
            if ($existe) return false;

            DB::table('accesoweb')->insert([
                'usuario' => $datos['usuario'],
                'password' => Hash::make($datos['password']), // ✅ CORREGIDO: hashear
                'tipousuario' => $datos['tipousuario'],
                'idusuario' => $datos['idusuario'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->registrarAuditoria('CREAR_USUARIO', "Usuario {$datos['usuario']} creado y vinculado al empleado {$empleado->Nombre}");
            return true;
        } catch (\Exception $e) {
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
     * Registrar acción en auditoría
     */
    private function registrarAuditoria($accion, $descripcion)
    {
        try {
            $usuario = auth()->user()->usuario ?? 'SISTEMA';
            DB::table('Auditoria_Sistema')->insert([
                'usuario' => $usuario,
                'accion' => $accion,
                'tabla' => 'accesoweb',
                'detalle' => $descripcion,
                'fecha' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al registrar auditoría: ' . $e->getMessage());
        }
    }
    public function desactivarUsuario($usuario)
    {
        try {
            DB::table('accesoweb')
                ->where('usuario', $usuario)
                ->update(['estado' => 'INACTIVO']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function obtenerHistorialAccesos($usuario)
    {
        
        try {
            return DB::table('accesoweb_historial')
                ->where('usuario', $usuario)
                ->orderBy('fecha_acceso', 'desc')
                ->get();
        } catch (\Exception $e) {
            return collect(); 
        }
    }

    public function activarUsuario($usuario)
    {
        try {
            DB::table('accesoweb')
                ->where('usuario', $usuario)
                ->update(['estado' => 'ACTIVO']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }




}