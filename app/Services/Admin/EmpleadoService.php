<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmpleadoService
{
    public function obtenerEmpleados($filtros = [])
    {
        $query = DB::table('Empleados')
            ->select('Empleados.*')
            ->addSelect(DB::raw("CASE WHEN accesoweb.idusuario IS NOT NULL THEN 1 ELSE 0 END as tiene_usuario"))
            ->leftJoin('accesoweb', 'Empleados.Codemp', '=', 'accesoweb.idusuario')
            ->orderBy('Empleados.Nombre', 'asc');

        if (!empty($filtros['buscar'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('Empleados.Nombre', 'like', '%' . $filtros['buscar'] . '%')
                  ->orWhere(DB::raw("RTRIM(Empleados.Documento)"), 'like', '%' . $filtros['buscar'] . '%');
            });
        }

        if (!empty($filtros['tipo'])) {
            $query->where('Empleados.Tipo', $filtros['tipo']);
        }

        return $query->get();
    }

    public function obtenerEstadisticas()
    {
        $total = DB::table('Empleados')->count();

        $con_usuario = DB::table('Empleados')
            ->join('accesoweb', 'Empleados.Codemp', '=', 'accesoweb.idusuario')
            ->distinct()
            ->count('Empleados.Codemp');

        $sin_usuario = DB::table('Empleados')
            ->leftJoin('accesoweb', 'Empleados.Codemp', '=', 'accesoweb.idusuario')
            ->whereNull('accesoweb.usuario')
            ->count();

        return compact('total', 'con_usuario', 'sin_usuario');
    }

    public function obtenerEmpleado($id)
    {
        return DB::table('Empleados')
            ->where('Codemp', $id)
            ->first();
    }

    public function crearEmpleado($datos)
    {
        try {
            if (empty($datos['Codemp'])) {
                Log::error('Codemp es requerido para crear empleado.');
                return false;
            }

            if (DB::table('Empleados')->where('Codemp', $datos['Codemp'])->exists()) {
                Log::error("Codemp {$datos['Codemp']} ya existe.");
                return false;
            }

            // Normalizar campos vacíos a null para teléfonos y otros opcionales
            $telefono1 = empty($datos['Telefono1']) ? null : $datos['Telefono1'];
            $telefono2 = empty($datos['Telefono2']) ? null : $datos['Telefono2'];
            $celular   = empty($datos['Celular'])   ? null : $datos['Celular'];
            $nextel    = empty($datos['Nextel'])    ? null : $datos['Nextel'];

            DB::table('Empleados')->insert([
                'Codemp'     => $datos['Codemp'],
                'Nombre'     => $datos['Nombre'],
                'Direccion'  => $datos['Direccion'] ?? null,
                'Documento'  => $datos['Documento'] ?? null,
                'Telefono1'  => $telefono1,
                'Telefono2'  => $telefono2,
                'Celular'    => $celular,
                'Nextel'     => $nextel,
                'Cumpleaños' => $datos['Cumpleaños'] ?? null,
                'Tipo'       => $datos['Tipo'],
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al crear empleado: ' . $e->getMessage());
            return false;
        }
    }

    public function actualizarEmpleado($id, $datos)
    {
        try {
            $telefono1 = empty($datos['Telefono1']) ? null : $datos['Telefono1'];
            $telefono2 = empty($datos['Telefono2']) ? null : $datos['Telefono2'];
            $celular   = empty($datos['Celular'])   ? null : $datos['Celular'];
            $nextel    = empty($datos['Nextel'])    ? null : $datos['Nextel'];

            DB::table('Empleados')
                ->where('Codemp', $id)
                ->update([
                    'Nombre'     => $datos['Nombre'],
                    'Direccion'  => $datos['Direccion'] ?? null,
                    'Documento'  => $datos['Documento'] ?? null,
                    'Telefono1'  => $telefono1,
                    'Telefono2'  => $telefono2,
                    'Celular'    => $celular,
                    'Nextel'     => $nextel,
                    'Cumpleaños' => $datos['Cumpleaños'] ?? null,
                    'Tipo'       => $datos['Tipo'],
                ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al actualizar empleado: ' . $e->getMessage());
            return false;
        }
    }

    public function eliminarEmpleado($id)
    {
        try {
            // Verificar dependencias explícitas
            $tieneUsuario = DB::table('accesoweb')->where('idusuario', $id)->exists();
            $esVendedor = DB::table('Doccab')->where('Vendedor', $id)->exists();

            if ($tieneUsuario || $esVendedor) {
                return false;
            }

            DB::table('Empleados')->where('Codemp', $id)->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Error al eliminar empleado: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerUsuarioVinculado($idEmpleado)
    {
        return DB::table('accesoweb')
            ->where('idusuario', $idEmpleado)
            ->first();
    }

    public function obtenerEmpleadosSinUsuario()
    {
        return DB::table('Empleados')
            ->leftJoin('accesoweb', 'Empleados.Codemp', '=', 'accesoweb.idusuario')
            ->whereNull('accesoweb.usuario')
            ->select('Empleados.*')
            ->orderBy('Empleados.Nombre', 'asc')
            ->get();
    }

    public function obtenerEmpleadosPorTipo()
    {
        return DB::table('Empleados')
            ->select('Tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('Tipo')
            ->orderByDesc('total')
            ->get();
    }
}