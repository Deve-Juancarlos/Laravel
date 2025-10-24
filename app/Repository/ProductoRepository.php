<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProductoRepository
{
    protected $connection = 'sqlsrv';

    /**
     * Obtener todos los productos
     */
    public function obtenerTodos($paginado = null)
    {
        try {
            $query = "
                SELECT 
                    p.*,
                    ISNULL(SUM(d.cantidad), 0) as stock_actual,
                    ISNULL(SUM(d.cantidad * d.precio_unitario), 0) as valor_inventario
                FROM Productos p
                LEFT JOIN DetalleFactura d ON p.id = d.idproducto
                WHERE p.estado = 'activo'
                GROUP BY p.id, p.codigo, p.nombre, p.descripcion, p.precio, 
                         p.stock_minimo, p.estado, p.fecha_creacion
                ORDER BY p.nombre
            ";

            $resultados = DB::connection($this->connection)->select($query);

            return $paginado ? collect($resultados)->paginate($paginado) : $resultados;
        } catch (\Exception $e) {
            Log::error('Error en ProductoRepository::obtenerTodos: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Buscar producto por código
     */
    public function buscarPorCodigo($codigo)
    {
        try {
            $query = "
                SELECT 
                    p.*,
                    ISNULL(SUM(d.cantidad), 0) as stock_actual,
                    ISNULL(SUM(d.cantidad * d.precio_unitario), 0) as valor_inventario
                FROM Productos p
                LEFT JOIN DetalleFactura d ON p.id = d.idproducto
                WHERE p.codigo = ? AND p.estado = 'activo'
                GROUP BY p.id, p.codigo, p.nombre, p.descripcion, p.precio, 
                         p.stock_minimo, p.estado, p.fecha_creacion
            ";

            return DB::connection($this->connection)->selectOne($query, [$codigo]);
        } catch (\Exception $e) {
            Log::error('Error en ProductoRepository::buscarPorCodigo: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener producto por ID
     */
    public function obtenerPorId($id)
    {
        try {
            $query = "
                SELECT 
                    p.*,
                    ISNULL(SUM(d.cantidad), 0) as stock_actual,
                    ISNULL(SUM(d.cantidad * d.precio_unitario), 0) as valor_inventario
                FROM Productos p
                LEFT JOIN DetalleFactura d ON p.id = d.idproducto
                WHERE p.id = ? AND p.estado = 'activo'
                GROUP BY p.id, p.codigo, p.nombre, p.descripcion, p.precio, 
                         p.stock_minimo, p.estado, p.fecha_creacion
            ";

            return DB::connection($this->connection)->selectOne($query, [$id]);
        } catch (\Exception $e) {
            Log::error('Error en ProductoRepository::obtenerPorId: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Crear nuevo producto
     */
    public function crear($datos)
    {
        try {
            $query = "
                INSERT INTO Productos (
                    codigo, nombre, descripcion, precio, stock_minimo, 
                    estado, fecha_creacion
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ";

            $params = [
                $datos['codigo'],
                $datos['nombre'],
                $datos['descripcion'] ?? null,
                $datos['precio'],
                $datos['stock_minimo'] ?? 0,
                'activo',
                Carbon::now()
            ];

            return DB::connection($this->connection)->insert($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en ProductoRepository::crear: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar producto
     */
    public function actualizar($id, $datos)
    {
        try {
            $query = "
                UPDATE Productos 
                SET nombre = ?, descripcion = ?, precio = ?, stock_minimo = ?, fecha_modificacion = ?
                WHERE id = ?
            ";

            $params = [
                $datos['nombre'],
                $datos['descripcion'] ?? null,
                $datos['precio'],
                $datos['stock_minimo'] ?? 0,
                Carbon::now(),
                $id
            ];

            return DB::connection($this->connection)->update($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en ProductoRepository::actualizar: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener productos con stock bajo
     */
    public function obtenerStockBajo()
    {
        try {
            $query = "
                SELECT 
                    p.*,
                    ISNULL(SUM(d.cantidad), 0) as stock_actual,
                    p.stock_minimo - ISNULL(SUM(d.cantidad), 0) as deficit_stock
                FROM Productos p
                LEFT JOIN DetalleFactura d ON p.id = d.idproducto
                WHERE p.estado = 'activo'
                GROUP BY p.id, p.codigo, p.nombre, p.descripcion, p.precio, p.stock_minimo, p.estado, p.fecha_creacion
                HAVING ISNULL(SUM(d.cantidad), 0) <= p.stock_minimo
                ORDER BY deficit_stock DESC
            ";

            return DB::connection($this->connection)->select($query);
        } catch (\Exception $e) {
            Log::error('Error en ProductoRepository::obtenerStockBajo: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener historial de movimientos del producto
     */
    public function obtenerHistorialMovimientos($id, $fechaInicio = null, $fechaFin = null)
    {
        try {
            $query = "
                SELECT 
                    m.*,
                    f.serie + '-' + f.numero as factura_numero,
                    v.Nombres + ' ' + v.Apellidos as vendedor_nombre
                FROM DetalleFactura m
                INNER JOIN dbo.PlanD_cobranza f ON m.idfactura = f.id
                LEFT JOIN Vendedores v ON f.Vendedor = v.Codigo
                WHERE m.idproducto = ?
            ";

            $params = [$id];

            if ($fechaInicio && $fechaFin) {
                $query .= " AND f.FechaFac BETWEEN ? AND ?";
                $params[] = $fechaInicio;
                $params[] = $fechaFin;
            }

            $query .= " ORDER BY f.FechaFac DESC";

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en ProductoRepository::obtenerHistorialMovimientos: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener ventas de producto por período
     */
    public function obtenerVentasPorPeriodo($id, $fechaInicio, $fechaFin)
    {
        try {
            $query = "
                SELECT 
                    f.FechaFac,
                    f.serie + '-' + f.numero as factura,
                    d.cantidad,
                    d.precio_unitario,
                    (d.cantidad * d.precio_unitario) as subtotal,
                    c.nombre as cliente_nombre
                FROM DetalleFactura d
                INNER JOIN dbo.PlanD_cobranza f ON d.idfactura = f.id
                LEFT JOIN Clientes c ON f.idcliente = c.id
                WHERE d.idproducto = ?
                AND f.FechaFac BETWEEN ? AND ?
                AND f.estado != 'anulado'
                ORDER BY f.FechaFac DESC, f.serie + '-' + f.numero
            ";

            return DB::connection($this->connection)->select($query, [$id, $fechaInicio, $fechaFin]);
        } catch (\Exception $e) {
            Log::error('Error en ProductoRepository::obtenerVentasPorPeriodo: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener estadísticas de producto
     */
    public function obtenerEstadisticas($id, $fechaInicio, $fechaFin)
    {
        try {
            $query = "
                SELECT 
                    COUNT(d.id) as total_ventas,
                    ISNULL(SUM(d.cantidad), 0) as cantidad_vendida,
                    ISNULL(SUM(d.cantidad * d.precio_unitario), 0) as total_ingresos,
                    ISNULL(AVG(d.precio_unitario), 0) as precio_promedio,
                    ISNULL(MIN(d.precio_unitario), 0) as precio_minimo,
                    ISNULL(MAX(d.precio_unitario), 0) as precio_maximo,
                    COUNT(DISTINCT f.idcliente) as clientes_unicos
                FROM DetalleFactura d
                INNER JOIN dbo.PlanD_cobranza f ON d.idfactura = f.id
                WHERE d.idproducto = ?
                AND f.FechaFac BETWEEN ? AND ?
                AND f.estado != 'anulado'
            ";

            return DB::connection($this->connection)->selectOne($query, [$id, $fechaInicio, $fechaFin]);
        } catch (\Exception $e) {
            Log::error('Error en ProductoRepository::obtenerEstadisticas: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Top productos más vendidos
     */
    public function obtenerTopVendidos($fechaInicio, $fechaFin, $limite = 10)
    {
        try {
            $query = "
                SELECT TOP {$limite}
                    p.*,
                    ISNULL(SUM(d.cantidad), 0) as total_vendido,
                    ISNULL(SUM(d.cantidad * d.precio_unitario), 0) as total_ingresos,
                    COUNT(DISTINCT f.id) as num_ventas
                FROM Productos p
                INNER JOIN DetalleFactura d ON p.id = d.idproducto
                INNER JOIN dbo.PlanD_cobranza f ON d.idfactura = f.id
                WHERE p.estado = 'activo'
                AND f.FechaFac BETWEEN ? AND ?
                AND f.estado != 'anulado'
                GROUP BY p.id, p.codigo, p.nombre, p.descripcion, p.precio, 
                         p.stock_minimo, p.estado, p.fecha_creacion
                HAVING ISNULL(SUM(d.cantidad), 0) > 0
                ORDER BY total_vendido DESC
            ";

            return DB::connection($this->connection)->select($query, [$fechaInicio, $fechaFin]);
        } catch (\Exception $e) {
            Log::error('Error en ProductoRepository::obtenerTopVendidos: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Actualizar stock
     */
    public function actualizarStock($id, $cantidad, $tipoMovimiento, $observacion = null)
    {
        try {
            DB::connection($this->connection)->beginTransaction();
            
            // Registrar movimiento de stock
            $queryMovimiento = "
                INSERT INTO MovimientosStock (
                    idproducto, tipo_movimiento, cantidad, observacion, fecha, usuario
                ) VALUES (?, ?, ?, ?, ?, ?)
            ";

            DB::connection($this->connection)->insert($queryMovimiento, [
                $id,
                $tipoMovimiento, // 'entrada' o 'salida'
                abs($cantidad),
                $observacion,
                Carbon::now(),
                auth()->id()
            ]);

            DB::connection($this->connection)->commit();
            
            return true;
        } catch (\Exception $e) {
            DB::connection($this->connection)->rollback();
            Log::error('Error en ProductoRepository::actualizarStock: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Desactivar producto
     */
    public function desactivar($id, $motivo = null)
    {
        try {
            $query = "
                UPDATE Productos 
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
            Log::error('Error en ProductoRepository::desactivar: ' . $e->getMessage());
            return false;
        }
    }
}