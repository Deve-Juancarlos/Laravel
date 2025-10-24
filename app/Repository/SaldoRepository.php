<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SaldoRepository
{
    protected $connection = 'sqlsrv';

    /**
     * Obtener saldo de cuenta por cliente
     */
    public function obtenerSaldoCliente($idCliente, $incluirAnuladas = false)
    {
        try {
            $whereAnuladas = $incluirAnuladas ? "" : " AND f.estado != 'anulado'";
            
            $query = "
                SELECT 
                    COUNT(f.id) as total_documentos,
                    ISNULL(SUM(f.monto), 0) as total_facturado,
                    ISNULL(SUM(f.abono), 0) as total_abonado,
                    ISNULL(SUM(f.monto - f.abono), 0) as saldo_pendiente,
                    ISNULL(AVG(DATEDIFF(day, f.FechaFac, GETDATE())), 0) as dias_promedio_vencimiento,
                    MAX(CASE WHEN (f.monto - f.abono) > 0 THEN f.FechaFac END) as documento_mas_vencido
                FROM dbo.PlanD_cobranza f
                WHERE f.idcliente = ?{$whereAnuladas}
            ";

            return DB::connection($this->connection)->selectOne($query, [$idCliente]);
        } catch (\Exception $e) {
            Log::error('Error en SaldoRepository::obtenerSaldoCliente: ' . $e->getMessage());
            return ['total_documentos' => 0, 'total_facturado' => 0, 'total_abonado' => 0, 'saldo_pendiente' => 0];
        }
    }

    /**
     * Obtener detalle de saldos por documento
     */
    public function obtenerDetalleSaldos($idCliente, $fechaInicio = null, $fechaFin = null)
    {
        try {
            $query = "
                SELECT 
                    f.*,
                    v.Nombres + ' ' + v.Apellidos as vendedor_nombre,
                    DATEDIFF(day, f.FechaFac, GETDATE()) as dias_vencido,
                    ISNULL(f.monto,0) - ISNULL(f.abono,0) as saldo_pendiente,
                    CASE 
                        WHEN DATEDIFF(day, f.FechaFac, GETDATE()) > 90 THEN 'muy_vencido'
                        WHEN DATEDIFF(day, f.FechaFac, GETDATE()) > 30 THEN 'vencido'
                        WHEN DATEDIFF(day, f.FechaFac, GETDATE()) > 15 THEN 'por_vencer'
                        ELSE 'vigente'
                    END as estado_vencimiento
                FROM dbo.PlanD_cobranza f
                LEFT JOIN Vendedores v ON f.Vendedor = v.Codigo
                WHERE f.idcliente = ?
                AND ISNULL(f.monto,0) - ISNULL(f.abono,0) > 0
                AND f.estado != 'anulado'
            ";

            $params = [$idCliente];

            if ($fechaInicio && $fechaFin) {
                $query .= " AND f.FechaFac BETWEEN ? AND ?";
                $params[] = $fechaInicio;
                $params[] = $fechaFin;
            }

            $query .= " ORDER BY f.FechaFac ASC, f.serie + '-' + f.numero";

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en SaldoRepository::obtenerDetalleSaldos: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener saldos vencidos
     */
    public function obtenerSaldosVencidos($diasVencimiento = 30, $idusuario = null)
    {
        try {
            $whereUsuario = $idusuario ? " AND f.idusuario_vendedor = ?" : "";
            
            $query = "
                SELECT 
                    f.*,
                    c.nombre as cliente_nombre,
                    c.dni as cliente_dni,
                    v.Nombres + ' ' + v.Apellidos as vendedor_nombre,
                    DATEDIFF(day, f.FechaFac, GETDATE()) as dias_vencido,
                    ISNULL(f.monto,0) - ISNULL(f.abono,0) as saldo_pendiente
                FROM dbo.PlanD_cobranza f
                LEFT JOIN Clientes c ON f.idcliente = c.id
                LEFT JOIN Vendedores v ON f.Vendedor = v.Codigo
                WHERE f.estado != 'anulado'
                AND DATEDIFF(day, f.FechaFac, GETDATE()) > ?
                AND ISNULL(f.monto,0) - ISNULL(f.abono,0) > 0
                {$whereUsuario}
                ORDER BY dias_vencido DESC, f.FechaFac ASC
            ";

            $params = array_merge([$diasVencimiento], $idusuario ? [$idusuario] : []);

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en SaldoRepository::obtenerSaldosVencidos: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener resumen de saldos por vendedor
     */
    public function obtenerResumenPorVendedor($fechaInicio, $fechaFin, $idusuario = null)
    {
        try {
            $whereUsuario = $idusuario ? " AND f.idusuario_vendedor = ?" : "";
            
            $query = "
                SELECT 
                    v.Codigo,
                    v.Nombres + ' ' + v.Apellidos as vendedor_nombre,
                    COUNT(f.id) as total_documentos,
                    ISNULL(SUM(f.monto), 0) as total_facturado,
                    ISNULL(SUM(f.abono), 0) as total_abonado,
                    ISNULL(SUM(f.monto - f.abono), 0) as saldo_pendiente,
                    COUNT(CASE WHEN (f.monto - f.abono) > 0 AND DATEDIFF(day, f.FechaFac, GETDATE()) > 30 THEN 1 END) as documentos_vencidos,
                    ISNULL(SUM(CASE WHEN (f.monto - f.abono) > 0 AND DATEDIFF(day, f.FechaFac, GETDATE()) > 30 
                        THEN f.monto - f.abono ELSE 0 END), 0) as saldo_vencido
                FROM Vendedores v
                LEFT JOIN dbo.PlanD_cobranza f ON v.Codigo = f.Vendedor 
                    AND f.FechaFac BETWEEN ? AND ?
                WHERE f.estado != 'anulado' OR f.estado IS NULL
                {$whereUsuario}
                GROUP BY v.Codigo, v.Nombres, v.Apellidos
                ORDER BY saldo_pendiente DESC
            ";

            $params = array_merge([$fechaInicio, $fechaFin], $idusuario ? [$idusuario] : []);

            return DB::connection($this->connection)->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en SaldoRepository::obtenerResumenPorVendedor: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener análisis de antigüedad de saldos
     */
    public function obtenerAnalisisAntiguedad($fechaReferencia = null)
    {
        try {
            $fechaRef = $fechaReferencia ?: Carbon::now()->format('Y-m-d');
            
            $query = "
                SELECT 
                    SUM(CASE WHEN DATEDIFF(day, f.FechaFac, ?) BETWEEN 0 AND 30 
                        THEN f.monto - f.abono ELSE 0 END) as saldo_0_30_dias,
                    SUM(CASE WHEN DATEDIFF(day, f.FechaFac, ?) BETWEEN 31 AND 60 
                        THEN f.monto - f.abono ELSE 0 END) as saldo_31_60_dias,
                    SUM(CASE WHEN DATEDIFF(day, f.FechaFac, ?) BETWEEN 61 AND 90 
                        THEN f.monto - f.abono ELSE 0 END) as saldo_61_90_dias,
                    SUM(CASE WHEN DATEDIFF(day, f.FechaFac, ?) > 90 
                        THEN f.monto - f.abono ELSE 0 END) as saldo_mas_90_dias,
                    SUM(CASE WHEN (f.monto - f.abono) > 0 THEN f.monto - f.abono ELSE 0 END) as saldo_total
                FROM dbo.PlanD_cobranza f
                WHERE f.estado != 'anulado'
            ";

            $params = array_fill(0, 4, $fechaRef);

            return DB::connection($this->connection)->selectOne($query, $params);
        } catch (\Exception $e) {
            Log::error('Error en SaldoRepository::obtenerAnalisisAntiguedad: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Aplicar pago a saldo
     */
    public function aplicarPago($idDocumento, $monto, $tipoPago, $idusuario, $observaciones = null)
    {
        try {
            DB::connection($this->connection)->beginTransaction();
            
            // Obtener documento actual
            $documento = DB::connection($this->connection)->selectOne("
                SELECT * FROM dbo.PlanD_cobranza WHERE id = ?
            ", [$idDocumento]);

            if (!$documento) {
                DB::connection($this->connection)->rollback();
                return false;
            }

            $nuevoAbono = $documento->abono + $monto;
            $saldoPendiente = $documento->monto - $nuevoAbono;

            // Actualizar documento
            $query = "
                UPDATE dbo.PlanD_cobranza 
                SET abono = ?, 
                    fecha_ultimo_pago = ?, 
                    idusuario_ultimo_pago = ?,
                    observaciones = ISNULL(observaciones, '') + ?
                WHERE id = ?
            ";

            DB::connection($this->connection)->update($query, [
                $nuevoAbono,
                Carbon::now(),
                $idusuario,
                " | Pago {$tipoPago}: S/ {$monto} - " . ($observaciones ?? ''),
                $idDocumento
            ]);

            // Registrar movimiento en tabla de pagos si existe
            try {
                DB::connection($this->connection)->insert("
                    INSERT INTO MovimientosPago (id_documento, monto, tipo_pago, fecha_pago, usuario, observaciones)
                    VALUES (?, ?, ?, ?, ?, ?)
                ", [
                    $idDocumento,
                    $monto,
                    $tipoPago,
                    Carbon::now(),
                    $idusuario,
                    $observaciones
                ]);
            } catch (\Exception $e) {
                // La tabla de movimientos puede no existir, continuar
                Log::warning('Tabla MovimientosPago no encontrada: ' . $e->getMessage());
            }

            DB::connection($this->connection)->commit();
            
            return [
                'saldo_pendiente' => $saldoPendiente,
                'pagada' => $saldoPendiente <= 0,
                'nuevo_abono' => $nuevoAbono
            ];
        } catch (\Exception $e) {
            DB::connection($this->connection)->rollback();
            Log::error('Error en SaldoRepository::aplicarPago: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener historial de pagos de un documento
     */
    public function obtenerHistorialPagos($idDocumento)
    {
        try {
            $query = "
                SELECT 
                    mp.*,
                    u.nombre as usuario_nombre,
                    f.serie + '-' + f.numero as factura_numero
                FROM MovimientosPago mp
                LEFT JOIN accesoweb u ON mp.usuario = u.idusuario
                LEFT JOIN dbo.PlanD_cobranza f ON mp.id_documento = f.id
                WHERE mp.id_documento = ?
                ORDER BY mp.fecha_pago DESC
            ";

            return DB::connection($this->connection)->select($query, [$idDocumento]);
        } catch (\Exception $e) {
            // Si la tabla no existe, devolver array vacío
            Log::warning('Tabla MovimientosPago no encontrada: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener estadísticas generales de saldos
     */
    public function obtenerEstadisticasGenerales($fechaInicio, $fechaFin)
    {
        try {
            $query = "
                SELECT 
                    COUNT(f.id) as total_documentos,
                    COUNT(CASE WHEN (f.monto - f.abono) > 0 THEN 1 END) as documentos_pendientes,
                    ISNULL(SUM(f.monto), 0) as total_facturado,
                    ISNULL(SUM(f.abono), 0) as total_abonado,
                    ISNULL(SUM(f.monto - f.abono), 0) as saldo_pendiente_total,
                    ISNULL(AVG(f.monto - f.abono), 0) as saldo_promedio,
                    COUNT(CASE WHEN (f.monto - f.abono) > 0 AND DATEDIFF(day, f.FechaFac, GETDATE()) > 30 THEN 1 END) as documentos_vencidos,
                    ISNULL(SUM(CASE WHEN (f.monto - f.abono) > 0 AND DATEDIFF(day, f.FechaFac, GETDATE()) > 30 
                        THEN f.monto - f.abono ELSE 0 END), 0) as saldo_vencido_total
                FROM dbo.PlanD_cobranza f
                WHERE f.FechaFac BETWEEN ? AND ?
                AND f.estado != 'anulado'
            ";

            return DB::connection($this->connection)->selectOne($query, [$fechaInicio, $fechaFin]);
        } catch (\Exception $e) {
            Log::error('Error en SaldoRepository::obtenerEstadisticasGenerales: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Conciliar saldos
     */
    public function conciliarSaldo($idDocumento, $saldoCalculado, $idusuario)
    {
        try {
            DB::connection($this->connection)->beginTransaction();
            
            $documento = DB::connection($this->connection)->selectOne("
                SELECT * FROM dbo.PlanD_cobranza WHERE id = ?
            ", [$idDocumento]);

            if (!$documento) {
                DB::connection($this->connection)->rollback();
                return false;
            }

            $diferencia = $saldoCalculado - ($documento->monto - $documento->abono);
            $saldoActualizado = $documento->monto - $documento->abono - $diferencia;

            // Actualizar saldo
            $query = "
                UPDATE dbo.PlanD_cobranza 
                SET monto = ?, 
                    fecha_conciliacion = ?, 
                    idusuario_conciliacion = ?,
                    observaciones = ISNULL(observaciones, '') + ?
                WHERE id = ?
            ";

            DB::connection($this->connection)->update($query, [
                $documento->monto - $diferencia,
                Carbon::now(),
                $idusuario,
                " | Conciliación: Ajuste de S/ {$diferencia}",
                $idDocumento
            ]);

            DB::connection($this->connection)->commit();
            
            return [
                'diferencia_ajustada' => $diferencia,
                'saldo_actualizado' => $saldoActualizado
            ];
        } catch (\Exception $e) {
            DB::connection($this->connection)->rollback();
            Log::error('Error en SaldoRepository::conciliarSaldo: ' . $e->getMessage());
            return false;
        }
    }
}