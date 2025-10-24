<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClienteRepository
{
    protected $connection = 'sqlsrv';

    /**
     * Obtener todos los clientes
     */
    public function obtenerTodos($paginado = null)
    {
        try {
            $query = "
                SELECT 
                    c.*,
                    COUNT(f.id) as total_facturas,
                    ISNULL(SUM(f.monto), 0) as total_compras,
                    ISNULL(SUM(f.abono), 0) as total_pagado,
                    ISNULL(SUM(f.monto - f.abono), 0) as saldo_pendiente,
                    MAX(f.FechaFac) as ultima_compra
                FROM Clientes c
                LEFT JOIN dbo.PlanD_cobranza f ON c.id = f.idcliente
                WHERE c.estado = 'activo'
                GROUP BY c.id, c.nombre, c.dni, c.direccion, c.telefono, 
                         c.email, c.estado, c.fecha_creacion
                ORDER BY c.nombre
            ";

            $resultados = DB::connection($this->connection)->select($query);

            return $paginado ? collect($resultados)->paginate($paginado) : $resultados;
        } catch (\Exception $e) {
            Log::error('Error en ClienteRepository::obtenerTodos: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Buscar cliente por DNI
     */
    public function buscarPorDni($dni)
    {
        try {
            $query = "
                SELECT TOP 1 
                    c.*,
                    ISNULL(SUM(CASE WHEN f.estado != 'anulado' THEN f.monto - f.abono ELSE 0 END), 0) as saldo_total
                FROM Clientes c
                LEFT JOIN dbo.PlanD_cobranza f ON c.id = f.idcliente
                WHERE c.dni = ? AND c.estado = 'activo'
                GROUP BY c.id, c.nombre, c.dni, c.direccion, c.telefono, 
                         c.email, c.estado, c.fecha_creacion
            ";

            return DB::connection($this->connection)->selectOne($query, [$dni]);
        } catch (\Exception $e) {
            Log::error('Error en ClienteRepository::buscarPorDni: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener cliente por ID
     */
    public function obtenerPorId($id)
    {
        try {
            $query = "
                SELECT 
                    c.*,
                    COUNT(f.id) as total_facturas,
                    ISNULL(SUM(CASE WHEN f.estado != 'anulado' THEN f.monto - f.abono ELSE 0 END), 0) as saldo_pendiente
                FROM Clientes c
                LEFT JOIN dbo.PlanD_cobranza f ON c.id = f.idcliente
                WHERE c.id = ? AND c.estado = 'activo'
                GROUP BY c.id, c.nombre, c.dni, c.direccion, c.telefono, 
                         c.email, c.estado, c.fecha_creacion
            ";

            return DB::connection($this->connection)->selectOne($query, [$id]);
        } catch (\Exception $e) {
            Log::error('Error en ClienteRepository::obtenerPorId: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Crear nuevo cliente
     */
    public function crear($datos)
    {
        try {
            $query = "
                INSERT INTO Clientes (
                    nombre, dni, direccion, telefono, email, estado, fecha_creacion
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ";

            $params = [
                $datos['nombre'],
                $datos['dni'],
                $datos['direccion'] ?? null,
                $datos['telefono'] ?? null,
                $datos['email'] ?? null,
                'activo',
                Carbon::now()
            ];

            return DB::connection($this->connection)->insert($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en ClienteRepository::crear: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar cliente
     */
    public function actualizar($id, $datos)
    {
        try {
            $query = "
                UPDATE Clientes 
                SET nombre = ?, dni = ?, direccion = ?, telefono = ?, email = ?, fecha_modificacion = ?
                WHERE id = ?
            ";

            $params = [
                $datos['nombre'],
                $datos['dni'],
                $datos['direccion'] ?? null,
                $datos['telefono'] ?? null,
                $datos['email'] ?? null,
                Carbon::now(),
                $id
            ];

            return DB::connection($this->connection)->update($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en ClienteRepository::actualizar: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener facturas del cliente
     */
    public function obtenerFacturas($id, $fechaInicio = null, $fechaFin = null)
    {
        try {
            $query = "
                SELECT 
                    f.*,
                    v.Nombres + ' ' + v.Apellidos as vendedor_nombre,
                    ISNULL(f.monto,0) - ISNULL(f.abono,0) as saldo_pendiente,
                    DATEDIFF(day, f.FechaFac, GETDATE()) as dias_vencido
                FROM dbo.PlanD_cobranza f
                LEFT JOIN Vendedores v ON f.Vendedor = v.Codigo
                WHERE f.idcliente = ?
            ";

            $params = [$id];

            if ($fechaInicio && $fechaFin) {
                $query .= " AND f.FechaFac BETWEEN ? AND ?";
                $params[] = $fechaInicio;
                $params[] = $fechaFin;
            }

            $query .= " ORDER BY f.FechaFac DESC, f.serie + '-' + f.numero DESC";

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en ClienteRepository::obtenerFacturas: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener saldo total del cliente
     */
    public function obtenerSaldoTotal($id)
    {
        try {
            $query = "
                SELECT 
                    ISNULL(SUM(CASE WHEN f.estado != 'anulado' THEN f.monto - f.abono ELSE 0 END), 0) as saldo_pendiente,
                    COUNT(CASE WHEN f.estado != 'anulado' AND (f.monto - f.abono) > 0 THEN 1 END) as documentos_pendientes,
                    MAX(CASE WHEN f.estado != 'anulado' AND (f.monto - f.abono) > 0 THEN f.FechaFac END) as fecha_vencimiento_mas_antigua
                FROM dbo.PlanD_cobranza f
                WHERE f.idcliente = ?
            ";

            return DB::connection($this->connection)->selectOne($query, [$id]);
        } catch (\Exception $e) {
            Log::error('Error en ClienteRepository::obtenerSaldoTotal: ' . $e->getMessage());
            return ['saldo_pendiente' => 0, 'documentos_pendientes' => 0];
        }
    }

    /**
     * Clientes con saldos vencidos
     */
    public function obtenerConSaldosVencidos($diasVencimiento = 30)
    {
        try {
            $query = "
                SELECT 
                    c.*,
                    ISNULL(SUM(CASE WHEN f.estado != 'anulado' AND DATEDIFF(day, f.FechaFac, GETDATE()) > ? 
                        THEN f.monto - f.abono ELSE 0 END), 0) as saldo_vencido,
                    COUNT(CASE WHEN f.estado != 'anulado' AND DATEDIFF(day, f.FechaFac, GETDATE()) > ? THEN 1 END) as documentos_vencidos,
                    MAX(CASE WHEN f.estado != 'anulado' AND DATEDIFF(day, f.FechaFac, GETDATE()) > ? THEN f.FechaFac END) as fecha_mas_vencida
                FROM Clientes c
                INNER JOIN dbo.PlanD_cobranza f ON c.id = f.idcliente
                WHERE c.estado = 'activo'
                GROUP BY c.id, c.nombre, c.dni, c.direccion, c.telefono, c.email, c.estado, c.fecha_creacion
                HAVING ISNULL(SUM(CASE WHEN f.estado != 'anulado' AND DATEDIFF(day, f.FechaFac, GETDATE()) > ? 
                    THEN f.monto - f.abono ELSE 0 END), 0) > 0
                ORDER BY saldo_vencido DESC
            ";

            $params = array_fill(0, 4, $diasVencimiento);

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en ClienteRepository::obtenerConSaldosVencidos: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Top clientes por compras
     */
    public function obtenerTopClientes($fechaInicio, $fechaFin, $limite = 10)
    {
        try {
            $query = "
                SELECT TOP {$limite}
                    c.*,
                    COUNT(f.id) as total_facturas,
                    ISNULL(SUM(f.monto), 0) as total_compras,
                    ISNULL(SUM(f.abono), 0) as total_pagado,
                    ISNULL(AVG(f.monto), 0) as promedio_factura
                FROM Clientes c
                INNER JOIN dbo.PlanD_cobranza f ON c.id = f.idcliente
                WHERE c.estado = 'activo'
                AND f.FechaFac BETWEEN ? AND ?
                AND f.estado != 'anulado'
                GROUP BY c.id, c.nombre, c.dni, c.direccion, c.telefono, c.email, c.estado, c.fecha_creacion
                ORDER BY total_compras DESC
            ";

            return DB::connection($this->connection)->select($query, [$fechaInicio, $fechaFin]);
        } catch (\Exception $e) {
            Log::error('Error en ClienteRepository::obtenerTopClientes: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Desactivar cliente
     */
    public function desactivar($id, $motivo = null)
    {
        try {
            $query = "
                UPDATE Clientes 
                SET estado = 'inactivo', 
                    fecha_modificacion = ?, 
                    motivo_inactivacion = ?
                WHERE id = ?
            ";

            return DB::connection($this->connection)->update($query, [
                Carbon::now(),
                $motivo,
                $id
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ClienteRepository::desactivar: ' . $e->getMessage());
            return false;
        }
    }
}