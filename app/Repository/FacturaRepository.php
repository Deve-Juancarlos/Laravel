<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FacturaRepository
{
    protected $connection = 'sqlsrv';

    /**
     * Obtener facturas por fecha
     */
    public function obtenerPorFecha($fechaInicio, $fechaFin, $idusuario = null)
    {
        try {
            $query = "
                SELECT 
                    f.*,
                    c.nombre as cliente_nombre,
                    c.dni as cliente_dni,
                    v.Nombres + ' ' + v.Apellidos as vendedor_nombre
                FROM dbo.PlanD_cobranza f
                LEFT JOIN Clientes c ON f.idcliente = c.id
                LEFT JOIN Vendedores v ON f.Vendedor = v.Codigo
                WHERE f.FechaFac BETWEEN ? AND ?
            ";

            $params = [$fechaInicio, $fechaFin];

            if ($idusuario) {
                $query .= " AND f.idusuario_vendedor = ?";
                $params[] = $idusuario;
            }

            $query .= " ORDER BY f.FechaFac DESC, f.serie + '-' + f.numero DESC";

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en FacturaRepository::obtenerPorFecha: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener factura por ID
     */
    public function obtenerPorId($id)
    {
        try {
            $query = "
                SELECT 
                    f.*,
                    c.nombre as cliente_nombre,
                    c.dni as cliente_dni,
                    c.direccion as cliente_direccion,
                    v.Nombres + ' ' + v.Apellidos as vendedor_nombre
                FROM dbo.PlanD_cobranza f
                LEFT JOIN Clientes c ON f.idcliente = c.id
                LEFT JOIN Vendedores v ON f.Vendedor = v.Codigo
                WHERE f.id = ?
            ";

            return DB::connection($this->connection)->selectOne($query, [$id]);
        } catch (\Exception $e) {
            Log::error('Error en FacturaRepository::obtenerPorId: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener facturas pendientes de cobro
     */
    public function obtenerPendientes($idusuario = null)
    {
        try {
            $query = "
                SELECT 
                    f.*,
                    c.nombre as cliente_nombre,
                    c.dni as cliente_dni,
                    ISNULL(f.monto,0) - ISNULL(f.abono,0) as saldo_pendiente,
                    DATEDIFF(day, f.FechaFac, GETDATE()) as dias_vencido
                FROM dbo.PlanD_cobranza f
                LEFT JOIN Clientes c ON f.idcliente = c.id
                WHERE ISNULL(f.monto,0) - ISNULL(f.abono,0) > 0
                AND f.estado != 'anulado'
            ";

            $params = [];

            if ($idusuario) {
                $query .= " AND f.idusuario_vendedor = ?";
                $params[] = $idusuario;
            }

            $query .= " ORDER BY dias_vencido DESC, f.FechaFac ASC";

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en FacturaRepository::obtenerPendientes: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Crear nueva factura
     */
    public function crear($datos)
    {
        try {
            $query = "
                INSERT INTO dbo.PlanD_cobranza (
                    serie, numero, idcliente, Vendedor, FechaFac, monto, 
                    tipo_doc, idusuario_vendedor, fecha_creacion
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $params = [
                $datos['serie'],
                $datos['numero'],
                $datos['idcliente'],
                $datos['vendedor'],
                $datos['fecha'],
                $datos['monto'],
                $datos['tipo_doc'],
                $datos['idusuario_vendedor'],
                Carbon::now()
            ];

            return DB::connection($this->connection)->insert($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en FacturaRepository::crear: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar factura
     */
    public function actualizar($id, $datos)
    {
        try {
            $query = "
                UPDATE dbo.PlanD_cobranza 
                SET monto = ?, fecha_modificacion = ?, idusuario_modificacion = ?
                WHERE id = ?
            ";

            $params = [
                $datos['monto'],
                Carbon::now(),
                $datos['idusuario_modificacion'],
                $id
            ];

            return DB::connection($this->connection)->update($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en FacturaRepository::actualizar: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Aplicar pago a factura
     */
    public function aplicarPago($id, $monto, $tipoPago, $idusuario)
    {
        try {
            DB::connection($this->connection)->beginTransaction();
            
            // Obtener factura actual
            $factura = $this->obtenerPorId($id);
            if (!$factura) {
                DB::connection($this->connection)->rollback();
                return false;
            }

            $nuevoAbono = $factura->abono + $monto;
            $saldoPendiente = $factura->monto - $nuevoAbono;

            // Actualizar factura
            $query = "
                UPDATE dbo.PlanD_cobranza 
                SET abono = ?, fecha_ultimo_pago = ?, idusuario_ultimo_pago = ?
                WHERE id = ?
            ";

            DB::connection($this->connection)->update($query, [
                $nuevoAbono,
                Carbon::now(),
                $idusuario,
                $id
            ]);

            DB::connection($this->connection)->commit();
            
            return [
                'saldo_pendiente' => $saldoPendiente,
                'pagada' => $saldoPendiente <= 0
            ];
        } catch (\Exception $e) {
            DB::connection($this->connection)->rollback();
            Log::error('Error en FacturaRepository::aplicarPago: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de facturas
     */
    public function obtenerEstadisticas($fechaInicio, $fechaFin, $idusuario = null)
    {
        try {
            $query = "
                SELECT 
                    COUNT(*) as total_facturas,
                    ISNULL(SUM(monto), 0) as total_monto,
                    ISNULL(SUM(abono), 0) as total_abonado,
                    COUNT(CASE WHEN ISNULL(monto,0) - ISNULL(abono,0) = 0 THEN 1 END) as facturas_pagadas,
                    COUNT(CASE WHEN ISNULL(monto,0) - ISNULL(abono,0) > 0 THEN 1 END) as facturas_pendientes
                FROM dbo.PlanD_cobranza f
                WHERE f.FechaFac BETWEEN ? AND ?
            ";

            $params = [$fechaInicio, $fechaFin];

            if ($idusuario) {
                $query .= " AND f.idusuario_vendedor = ?";
                $params[] = $idusuario;
            }

            return DB::connection($this->connection)->selectOne($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en FacturaRepository::obtenerEstadisticas: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Anular factura
     */
    public function anular($id, $motivo, $idusuario)
    {
        try {
            $query = "
                UPDATE dbo.PlanD_cobranza 
                SET estado = 'anulado', 
                    motivo_anulacion = ?, 
                    fecha_anulacion = ?, 
                    idusuario_anulacion = ?
                WHERE id = ?
            ";

            return DB::connection($this->connection)->update($query, [
                $motivo,
                Carbon::now(),
                $idusuario,
                $id
            ]);
        } catch (\Exception $e) {
            Log::error('Error en FacturaRepository::anular: ' . $e->getMessage());
            return false;
        }
    }
}