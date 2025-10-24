<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TrazabilidadRepository
{
    protected $connection = 'sqlsrv';

    /**
     * Registrar evento de trazabilidad
     */
    public function registrarEvento($datos)
    {
        try {
            $query = "
                INSERT INTO Trazabilidad (
                    tabla_afectada, id_registro, accion, usuario, ip_address, 
                    datos_anteriores, datos_nuevos, observaciones, fecha_registro
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $params = [
                $datos['tabla_afectada'],
                $datos['id_registro'],
                $datos['accion'],
                $datos['usuario'],
                $datos['ip_address'] ?? null,
                $datos['datos_anteriores'] ?? null,
                $datos['datos_nuevos'] ?? null,
                $datos['observaciones'] ?? null,
                Carbon::now()
            ];

            return DB::connection($this->connection)->insert($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en TrazabilidadRepository::registrarEvento: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener trazabilidad por tabla y registro
     */
    public function obtenerTrazabilidad($tabla, $idRegistro, $limite = null)
    {
        try {
            $query = "
                SELECT 
                    t.*,
                    u.nombre as usuario_nombre,
                    CASE t.accion
                        WHEN 'CREATE' THEN 'Creación'
                        WHEN 'UPDATE' THEN 'Actualización'
                        WHEN 'DELETE' THEN 'Eliminación'
                        WHEN 'VIEW' THEN 'Consulta'
                        WHEN 'LOGIN' THEN 'Inicio de sesión'
                        WHEN 'LOGOUT' THEN 'Cierre de sesión'
                        ELSE t.accion
                    END as accion_legible
                FROM Trazabilidad t
                LEFT JOIN accesoweb u ON t.usuario = u.idusuario
                WHERE t.tabla_afectada = ? AND t.id_registro = ?
            ";

            $params = [$tabla, $idRegistro];

            if ($limite) {
                $query .= " ORDER BY t.fecha_registro DESC OFFSET 0 ROWS FETCH NEXT {$limite} ROWS ONLY";
            } else {
                $query .= " ORDER BY t.fecha_registro DESC";
            }

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en TrazabilidadRepository::obtenerTrazabilidad: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener trazabilidad por usuario
     */
    public function obtenerPorUsuario($idusuario, $fechaInicio = null, $fechaFin = null, $limite = 50)
    {
        try {
            $query = "
                SELECT 
                    t.*,
                    CASE t.accion
                        WHEN 'CREATE' THEN 'Creación'
                        WHEN 'UPDATE' THEN 'Actualización'
                        WHEN 'DELETE' THEN 'Eliminación'
                        WHEN 'VIEW' THEN 'Consulta'
                        ELSE t.accion
                    END as accion_legible
                FROM Trazabilidad t
                WHERE t.usuario = ?
            ";

            $params = [$idusuario];

            if ($fechaInicio && $fechaFin) {
                $query .= " AND t.fecha_registro BETWEEN ? AND ?";
                $params[] = $fechaInicio;
                $params[] = $fechaFin;
            }

            $query .= " ORDER BY t.fecha_registro DESC OFFSET 0 ROWS FETCH NEXT {$limite} ROWS ONLY";

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en TrazabilidadRepository::obtenerPorUsuario: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener trazabilidad por período
     */
    public function obtenerPorPeriodo($fechaInicio, $fechaFin, $tabla = null, $accion = null)
    {
        try {
            $query = "
                SELECT 
                    t.*,
                    u.nombre as usuario_nombre,
                    CASE t.accion
                        WHEN 'CREATE' THEN 'Creación'
                        WHEN 'UPDATE' THEN 'Actualización'
                        WHEN 'DELETE' THEN 'Eliminación'
                        WHEN 'VIEW' THEN 'Consulta'
                        ELSE t.accion
                    END as accion_legible
                FROM Trazabilidad t
                LEFT JOIN accesoweb u ON t.usuario = u.idusuario
                WHERE t.fecha_registro BETWEEN ? AND ?
            ";

            $params = [$fechaInicio, $fechaFin];

            if ($tabla) {
                $query .= " AND t.tabla_afectada = ?";
                $params[] = $tabla;
            }

            if ($accion) {
                $query .= " AND t.accion = ?";
                $params[] = $accion;
            }

            $query .= " ORDER BY t.fecha_registro DESC";

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en TrazabilidadRepository::obtenerPorPeriodo: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener estadísticas de actividad
     */
    public function obtenerEstadisticasActividad($fechaInicio, $fechaFin)
    {
        try {
            $query = "
                SELECT 
                    COUNT(*) as total_eventos,
                    COUNT(DISTINCT t.usuario) as usuarios_activos,
                    COUNT(CASE WHEN t.accion = 'CREATE' THEN 1 END) as total_creaciones,
                    COUNT(CASE WHEN t.accion = 'UPDATE' THEN 1 END) as total_actualizaciones,
                    COUNT(CASE WHEN t.accion = 'DELETE' THEN 1 END) as total_eliminaciones,
                    COUNT(CASE WHEN t.accion = 'VIEW' THEN 1 END) as total_consultas,
                    COUNT(DISTINCT t.tabla_afectada) as tablas_afectadas
                FROM Trazabilidad t
                WHERE t.fecha_registro BETWEEN ? AND ?
            ";

            return DB::connection($this->connection)->selectOne($query, [$fechaInicio, $fechaFin]);
        } catch (\Exception $e) {
            Log::error('Error en TrazabilidadRepository::obtenerEstadisticasActividad: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Top usuarios más activos
     */
    public function obtenerTopUsuarios($fechaInicio, $fechaFin, $limite = 10)
    {
        try {
            $query = "
                SELECT TOP {$limite}
                    u.nombre as usuario_nombre,
                    COUNT(t.id) as total_eventos,
                    COUNT(CASE WHEN t.accion = 'CREATE' THEN 1 END) as total_creaciones,
                    COUNT(CASE WHEN t.accion = 'UPDATE' THEN 1 END) as total_actualizaciones,
                    COUNT(DISTINCT t.tabla_afectada) as tablas_modificadas
                FROM accesoweb u
                LEFT JOIN Trazabilidad t ON u.idusuario = t.usuario 
                    AND t.fecha_registro BETWEEN ? AND ?
                GROUP BY u.idusuario, u.nombre
                ORDER BY total_eventos DESC
            ";

            return DB::connection($this->connection)->select($query, [$fechaInicio, $fechaFin]);
        } catch (\Exception $e) {
            Log::error('Error en TrazabilidadRepository::obtenerTopUsuarios: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener actividad por tabla
     */
    public function obtenerActividadPorTabla($fechaInicio, $fechaFin)
    {
        try {
            $query = "
                SELECT 
                    t.tabla_afectada,
                    COUNT(t.id) as total_eventos,
                    COUNT(CASE WHEN t.accion = 'CREATE' THEN 1 END) as total_creaciones,
                    COUNT(CASE WHEN t.accion = 'UPDATE' THEN 1 END) as total_actualizaciones,
                    COUNT(CASE WHEN t.accion = 'DELETE' THEN 1 END) as total_eliminaciones,
                    COUNT(CASE WHEN t.accion = 'VIEW' THEN 1 END) as total_consultas,
                    COUNT(DISTINCT t.usuario) as usuarios_activos
                FROM Trazabilidad t
                WHERE t.fecha_registro BETWEEN ? AND ?
                GROUP BY t.tabla_afectada
                ORDER BY total_eventos DESC
            ";

            return DB::connection($this->connection)->select($query, [$fechaInicio, $fechaFin]);
        } catch (\Exception $e) {
            Log::error('Error en TrazabilidadRepository::obtenerActividadPorTabla: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Registrar inicio de sesión
     */
    public function registrarLogin($idusuario, $ipAddress, $userAgent = null)
    {
        return $this->registrarEvento([
            'tabla_afectada' => 'sesiones',
            'id_registro' => $idusuario,
            'accion' => 'LOGIN',
            'usuario' => $idusuario,
            'ip_address' => $ipAddress,
            'datos_nuevos' => json_encode(['user_agent' => $userAgent])
        ]);
    }

    /**
     * Registrar cierre de sesión
     */
    public function registrarLogout($idusuario, $ipAddress)
    {
        return $this->registrarEvento([
            'tabla_afectada' => 'sesiones',
            'id_registro' => $idusuario,
            'accion' => 'LOGOUT',
            'usuario' => $idusuario,
            'ip_address' => $ipAddress
        ]);
    }

    /**
     * Registrar consulta de datos
     */
    public function registrarConsulta($tabla, $idRegistro, $idusuario, $ipAddress, $filtros = null)
    {
        return $this->registrarEvento([
            'tabla_afectada' => $tabla,
            'id_registro' => $idRegistro,
            'accion' => 'VIEW',
            'usuario' => $idusuario,
            'ip_address' => $ipAddress,
            'observaciones' => $filtros ? 'Filtros aplicados: ' . $filtros : null
        ]);
    }

    /**
     * Obtener cambios específicos en un registro
     */
    public function obtenerCambios($tabla, $idRegistro, $fechaDesde = null)
    {
        try {
            $query = "
                SELECT 
                    t.fecha_registro,
                    u.nombre as usuario_nombre,
                    t.accion,
                    t.datos_anteriores,
                    t.datos_nuevos,
                    t.observaciones
                FROM Trazabilidad t
                LEFT JOIN accesoweb u ON t.usuario = u.idusuario
                WHERE t.tabla_afectada = ? 
                AND t.id_registro = ? 
                AND t.accion IN ('CREATE', 'UPDATE', 'DELETE')
            ";

            $params = [$tabla, $idRegistro];

            if ($fechaDesde) {
                $query .= " AND t.fecha_registro >= ?";
                $params[] = $fechaDesde;
            }

            $query .= " ORDER BY t.fecha_registro DESC";

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en TrazabilidadRepository::obtenerCambios: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener actividad sospechosa
     */
    public function obtenerActividadSospechosa($fechaInicio, $fechaFin)
    {
        try {
            $query = "
                SELECT 
                    t.*,
                    u.nombre as usuario_nombre,
                    CASE t.accion
                        WHEN 'CREATE' THEN 'Creación'
                        WHEN 'UPDATE' THEN 'Actualización'
                        WHEN 'DELETE' THEN 'Eliminación'
                        ELSE t.accion
                    END as accion_legible
                FROM Trazabilidad t
                LEFT JOIN accesoweb u ON t.usuario = u.idusuario
                WHERE t.fecha_registro BETWEEN ? AND ?
                AND (
                    t.accion = 'DELETE' 
                    OR t.observaciones LIKE '%error%'
                    OR t.observaciones LIKE '%sospechoso%'
                    OR t.observaciones LIKE '%intento fallido%'
                )
                ORDER BY t.fecha_registro DESC
            ";

            return DB::connection($this->connection)->select($query, [$fechaInicio, $fechaFin]);
        } catch (\Exception $e) {
            Log::error('Error en TrazabilidadRepository::obtenerActividadSospechosa: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Limpiar trazabilidad antigua
     */
    public function limpiarAntigua($dias = 365)
    {
        try {
            $query = "
                DELETE FROM Trazabilidad 
                WHERE fecha_registro < DATEADD(day, -?, GETDATE())
                AND accion NOT IN ('DELETE') -- Preservar eliminaciones importantes
            ";

            $eliminados = DB::connection($this->connection)->delete($query, [$dias]);
            
            Log::info("Limpieza de trazabilidad: {$eliminados} registros eliminados");
            
            return $eliminados;
        } catch (\Exception $e) {
            Log::error('Error en TrazabilidadRepository::limpiarAntigua: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener resumen ejecutivo de actividad
     */
    public function obtenerResumenEjecutivo($fechaInicio, $fechaFin)
    {
        try {
            $query = "
                SELECT 
                    COUNT(CASE WHEN CAST(t.fecha_registro AS TIME) BETWEEN '06:00:00' AND '18:00:00' THEN 1 END) as actividad_dia,
                    COUNT(CASE WHEN CAST(t.fecha_registro AS TIME) BETWEEN '18:00:01' AND '05:59:59' THEN 1 END) as actividad_noche,
                    COUNT(DISTINCT CONVERT(DATE, t.fecha_registro)) as dias_con_actividad,
                    COUNT(DISTINCT t.ip_address) as ips_unicas,
                    COUNT(CASE WHEN t.observaciones LIKE '%error%' THEN 1 END) as eventos_con_error
                FROM Trazabilidad t
                WHERE t.fecha_registro BETWEEN ? AND ?
            ";

            $stats = DB::connection($this->connection)->selectOne($query, [$fechaInicio, $fechaFin]);
            
            // Calcular porcentaje de actividad fuera de horario laboral
            $total = $stats->actividad_dia + $stats->actividad_noche;
            $porcentajeNocturno = $total > 0 ? ($stats->actividad_noche / $total) * 100 : 0;
            
            return [
                'actividad_dia' => $stats->actividad_dia ?? 0,
                'actividad_noche' => $stats->actividad_noche ?? 0,
                'dias_con_actividad' => $stats->dias_con_actividad ?? 0,
                'ips_unicas' => $stats->ips_unicas ?? 0,
                'eventos_con_error' => $stats->eventos_con_error ?? 0,
                'porcentaje_nocturno' => round($porcentajeNocturno, 2)
            ];
        } catch (\Exception $e) {
            Log::error('Error en TrazabilidadRepository::obtenerResumenEjecutivo: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Exportar trazabilidad a CSV
     */
    public function exportarCSV($fechaInicio, $fechaFin, $tabla = null, $usuario = null)
    {
        try {
            $query = "
                SELECT 
                    t.fecha_registro,
                    u.nombre as usuario,
                    t.tabla_afectada,
                    t.id_registro,
                    t.accion,
                    t.ip_address,
                    t.observaciones
                FROM Trazabilidad t
                LEFT JOIN accesoweb u ON t.usuario = u.idusuario
                WHERE t.fecha_registro BETWEEN ? AND ?
            ";

            $params = [$fechaInicio, $fechaFin];

            if ($tabla) {
                $query .= " AND t.tabla_afectada = ?";
                $params[] = $tabla;
            }

            if ($usuario) {
                $query .= " AND t.usuario = ?";
                $params[] = $usuario;
            }

            $query .= " ORDER BY t.fecha_registro DESC";

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en TrazabilidadRepository::exportarCSV: ' . $e->getMessage());
            return collect([]);
        }
    }
}