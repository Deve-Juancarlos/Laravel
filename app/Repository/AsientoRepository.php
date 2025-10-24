<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AsientoRepository
{
    protected $connection = 'sqlsrv';

    /**
     * Obtener todos los asientos contables
     */
    public function obtenerTodos($fechaInicio = null, $fechaFin = null, $paginado = null)
    {
        try {
            $query = "
                SELECT 
                    a.*,
                    u.nombre as usuario_creacion_nombre,
                    COUNT(d.id) as total_detalle
                FROM AsientosContables a
                LEFT JOIN accesoweb u ON a.idusuario_creacion = u.idusuario
                LEFT JOIN DetalleAsiento d ON a.id = d.idasiento
                WHERE 1=1
            ";

            $params = [];

            if ($fechaInicio && $fechaFin) {
                $query .= " AND a.fecha BETWEEN ? AND ?";
                $params[] = $fechaInicio;
                $params[] = $fechaFin;
            }

            $query .= " GROUP BY a.id, a.numero, a.fecha, a.descripcion, a.tipo, 
                             a.idusuario_creacion, a.fecha_creacion, a.estado, u.nombre
                       ORDER BY a.fecha DESC, a.numero DESC";

            $resultados = DB::connection($this->connection)->select($query, $params);

            return $paginado ? collect($resultados)->paginate($paginado) : $resultados;
        } catch (\Exception $e) {
            Log::error('Error en AsientoRepository::obtenerTodos: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener asiento por ID
     */
    public function obtenerPorId($id)
    {
        try {
            $query = "
                SELECT 
                    a.*,
                    u.nombre as usuario_creacion_nombre,
                    COUNT(d.id) as total_detalle
                FROM AsientosContables a
                LEFT JOIN accesoweb u ON a.idusuario_creacion = u.idusuario
                LEFT JOIN DetalleAsiento d ON a.id = d.idasiento
                WHERE a.id = ?
                GROUP BY a.id, a.numero, a.fecha, a.descripcion, a.tipo, 
                         a.idusuario_creacion, a.fecha_creacion, a.estado, u.nombre
            ";

            return DB::connection($this->connection)->selectOne($query, [$id]);
        } catch (\Exception $e) {
            Log::error('Error en AsientoRepository::obtenerPorId: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener detalle de asiento
     */
    public function obtenerDetalle($idasiento)
    {
        try {
            $query = "
                SELECT 
                    d.*,
                    p.codigo as plan_codigo,
                    p.nombre as plan_nombre,
                    d.debe - d.haber as saldo,
                    CASE 
                        WHEN d.debe > d.haber THEN 'deudor'
                        WHEN d.haber > d.debe THEN 'acreedor'
                        ELSE 'saldo_cero'
                    END as naturaleza
                FROM DetalleAsiento d
                LEFT JOIN PlanCuentas p ON d.idplan = p.id
                WHERE d.idasiento = ?
                ORDER BY d.id
            ";

            return DB::connection($this->connection)->select($query, [$idasiento]);
        } catch (\Exception $e) {
            Log::error('Error en AsientoRepository::obtenerDetalle: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Crear nuevo asiento contable
     */
    public function crearAsiento($datos)
    {
        try {
            DB::connection($this->connection)->beginTransaction();
            
            // Obtener siguiente número de asiento
            $numero = $this->obtenerSiguienteNumero($datos['fecha']);
            
            $query = "
                INSERT INTO AsientosContables (
                    numero, fecha, descripcion, tipo, idusuario_creacion, 
                    fecha_creacion, estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ";

            $params = [
                $numero,
                $datos['fecha'],
                $datos['descripcion'],
                $datos['tipo'] ?? 'manual',
                $datos['idusuario_creacion'],
                Carbon::now(),
                'borrador'
            ];

            DB::connection($this->connection)->insert($query, $params);
            
            // Obtener ID del asiento recién creado
            $idasiento = DB::connection($this->connection)->getPdo()->lastInsertId();
            
            DB::connection($this->connection)->commit();
            
            return $idasiento;
        } catch (\Exception $e) {
            DB::connection($this->connection)->rollback();
            Log::error('Error en AsientoRepository::crearAsiento: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Agregar detalle a asiento
     */
    public function agregarDetalle($idasiento, $detalle)
    {
        try {
            $query = "
                INSERT INTO DetalleAsiento (
                    idasiento, idplan, concepto, debe, haber, orden
                ) VALUES (?, ?, ?, ?, ?, ?)
            ";

            $params = [
                $idasiento,
                $detalle['idplan'],
                $detalle['concepto'],
                $detalle['debe'] ?? 0,
                $detalle['haber'] ?? 0,
                $detalle['orden'] ?? 1
            ];

            return DB::connection($this->connection)->insert($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en AsientoRepository::agregarDetalle: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener siguiente número de asiento
     */
    public function obtenerSiguienteNumero($fecha = null)
    {
        try {
            $fechaRef = $fecha ?: Carbon::now()->format('Y-m-d');
            $año = Carbon::parse($fechaRef)->year;
            
            $query = "
                SELECT ISNULL(MAX(CAST(RIGHT(numero, 4) AS INT)), 0) + 1 as siguiente
                FROM AsientosContables
                WHERE YEAR(fecha) = ?
                AND numero LIKE ?
            ";

            $result = DB::connection($this->connection)->selectOne($query, [
                $año,
                $año . '-%'
            ]);

            $siguiente = $result->siguiente ?? 1;
            return $año . '-' . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            Log::error('Error en AsientoRepository::obtenerSiguienteNumero: ' . $e->getMessage());
            return date('Y') . '-0001';
        }
    }

    /**
     * Verificar balance del asiento
     */
    public function verificarBalance($idasiento)
    {
        try {
            $query = "
                SELECT 
                    SUM(debe) as total_debe,
                    SUM(haber) as total_haber,
                    SUM(debe - haber) as diferencia
                FROM DetalleAsiento
                WHERE idasiento = ?
            ";

            $result = DB::connection($this->connection)->selectOne($query, [$idasiento]);
            
            return [
                'balanceado' => abs($result->diferencia) < 0.01,
                'total_debe' => $result->total_debe ?? 0,
                'total_haber' => $result->total_haber ?? 0,
                'diferencia' => $result->diferencia ?? 0
            ];
        } catch (\Exception $e) {
            Log::error('Error en AsientoRepository::verificarBalance: ' . $e->getMessage());
            return ['balanceado' => false, 'total_debe' => 0, 'total_haber' => 0, 'diferencia' => 0];
        }
    }

    /**
     * Contabilizar asiento
     */
    public function contabilizar($idasiento, $idusuario)
    {
        try {
            DB::connection($this->connection)->beginTransaction();
            
            // Verificar balance antes de contabilizar
            $balance = $this->verificarBalance($idasiento);
            
            if (!$balance['balanceado']) {
                DB::connection($this->connection)->rollback();
                return [
                    'exito' => false,
                    'error' => 'El asiento no está balanceado. Diferencia: ' . $balance['diferencia']
                ];
            }

            // Actualizar estado del asiento
            $query = "
                UPDATE AsientosContables 
                SET estado = 'contabilizado', 
                    fecha_contabilizacion = ?, 
                    idusuario_contabilizacion = ?
                WHERE id = ? AND estado = 'borrador'
            ";

            $afectados = DB::connection($this->connection)->update($query, [
                Carbon::now(),
                $idusuario,
                $idasiento
            ]);

            if ($afectados == 0) {
                DB::connection($this->connection)->rollback();
                return [
                    'exito' => false,
                    'error' => 'El asiento ya está contabilizado o no existe'
                ];
            }

            DB::connection($this->connection)->commit();
            
            return [
                'exito' => true,
                'balance' => $balance
            ];
        } catch (\Exception $e) {
            DB::connection($this->connection)->rollback();
            Log::error('Error en AsientoRepository::contabilizar: ' . $e->getMessage());
            return [
                'exito' => false,
                'error' => 'Error interno al contabilizar'
            ];
        }
    }

    /**
     * Obtener asientos por período
     */
    public function obtenerPorPeriodo($fechaInicio, $fechaFin, $estado = null)
    {
        try {
            $query = "
                SELECT 
                    a.*,
                    u.nombre as usuario_creacion_nombre,
                    ISNULL(SUM(d.debe), 0) as total_debe,
                    ISNULL(SUM(d.haber), 0) as total_haber,
                    COUNT(d.id) as total_lineas
                FROM AsientosContables a
                LEFT JOIN accesoweb u ON a.idusuario_creacion = u.idusuario
                LEFT JOIN DetalleAsiento d ON a.id = d.idasiento
                WHERE a.fecha BETWEEN ? AND ?
            ";

            $params = [$fechaInicio, $fechaFin];

            if ($estado) {
                $query .= " AND a.estado = ?";
                $params[] = $estado;
            }

            $query .= " GROUP BY a.id, a.numero, a.fecha, a.descripcion, a.tipo, 
                             a.idusuario_creacion, a.fecha_creacion, a.estado, u.nombre
                       ORDER BY a.fecha DESC, a.numero DESC";

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en AsientoRepository::obtenerPorPeriodo: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener mayores de cuentas
     */
    public function obtenerMayorCuenta($idPlan, $fechaInicio, $fechaFin)
    {
        try {
            $query = "
                SELECT 
                    a.numero,
                    a.fecha,
                    a.descripcion as asiento_descripcion,
                    d.concepto,
                    d.debe,
                    d.haber,
                    d.debe - d.haber as saldo,
                    CASE 
                        WHEN d.debe - d.haber > 0 THEN 'deudor'
                        WHEN d.debe - d.haber < 0 THEN 'acreedor'
                        ELSE 'saldo_cero'
                    END as naturaleza
                FROM DetalleAsiento d
                INNER JOIN AsientosContables a ON d.idasiento = a.id
                WHERE d.idplan = ?
                AND a.fecha BETWEEN ? AND ?
                AND a.estado = 'contabilizado'
                ORDER BY a.fecha, a.numero
            ";

            return DB::connection($this->connection)->select($query, [$idPlan, $fechaInicio, $fechaFin]);
        } catch (\Exception $e) {
            Log::error('Error en AsientoRepository::obtenerMayorCuenta: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Balance de comprobación
     */
    public function balanceComprobacion($fechaInicio, $fechaFin)
    {
        try {
            $query = "
                SELECT 
                    p.codigo,
                    p.nombre,
                    SUM(d.debe) as total_debe,
                    SUM(d.haber) as total_haber,
                    ABS(SUM(d.debe - d.haber)) as saldo_final,
                    CASE 
                        WHEN SUM(d.debe - d.haber) > 0 THEN 'deudor'
                        WHEN SUM(d.debe - d.haber) < 0 THEN 'acreedor'
                        ELSE 'saldo_cero'
                    END as naturaleza
                FROM PlanCuentas p
                INNER JOIN DetalleAsiento d ON p.id = d.idplan
                INNER JOIN AsientosContables a ON d.idasiento = a.id
                WHERE a.fecha BETWEEN ? AND ?
                AND a.estado = 'contabilizado'
                GROUP BY p.codigo, p.nombre, p.id
                HAVING SUM(d.debe) != SUM(d.haber) OR SUM(d.debe) > 0
                ORDER BY p.codigo
            ";

            return DB::connection($this->connection)->select($query, [$fechaInicio, $fechaFin]);
        } catch (\Exception $e) {
            Log::error('Error en AsientoRepository::balanceComprobacion: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Anular asiento
     */
    public function anular($idasiento, $motivo, $idusuario)
    {
        try {
            DB::connection($this->connection)->beginTransaction();
            
            $query = "
                UPDATE AsientosContables 
                SET estado = 'anulado', 
                    fecha_anulacion = ?, 
                    idusuario_anulacion = ?,
                    motivo_anulacion = ?
                WHERE id = ? AND estado != 'anulado'
            ";

            $afectados = DB::connection($this->connection)->update($query, [
                Carbon::now(),
                $idusuario,
                $motivo,
                $idasiento
            ]);

            if ($afectados == 0) {
                DB::connection($this->connection)->rollback();
                return [
                    'exito' => false,
                    'error' => 'El asiento ya está anulado o no existe'
                ];
            }

            DB::connection($this->connection)->commit();
            
            return ['exito' => true];
        } catch (\Exception $e) {
            DB::connection($this->connection)->rollback();
            Log::error('Error en AsientoRepository::anular: ' . $e->getMessage());
            return [
                'exito' => false,
                'error' => 'Error interno al anular'
            ];
        }
    }

    /**
     * Eliminar detalle de asiento
     */
    public function eliminarDetalle($iddetalle)
    {
        try {
            return DB::connection($this->connection)->delete("
                DELETE FROM DetalleAsiento WHERE id = ?
            ", [$iddetalle]);
        } catch (\Exception $e) {
            Log::error('Error en AsientoRepository::eliminarDetalle: ' . $e->getMessage());
            return false;
        }
    }
}