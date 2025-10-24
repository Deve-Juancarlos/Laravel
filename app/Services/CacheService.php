<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CacheService
{
    /**
     * TTL por defecto en minutos
     */
    const DEFAULT_TTL = 60;

    /**
     * Cache para datos de clientes contables
     */
    public function cacheDatosClientes($cacheKey, $callback, $ttl = self::DEFAULT_TTL)
    {
        return Cache::remember($cacheKey, $ttl, function () use ($callback) {
            return $callback();
        });
    }

    /**
     * Cache para saldos de cuentas por cobrar
     */
    public function cacheSaldosCtaCliente($idCliente, $ttl = 30)
    {
        $cacheKey = "saldos_cta_cliente_{$idCliente}";
        
        return Cache::remember($cacheKey, $ttl, function () use ($idCliente) {
            try {
                $query = "
                    SELECT 
                        ISNULL(SUM(ISNULL(c.monto,0) - ISNULL(c.abono,0)), 0) as saldo_pendiente,
                        COUNT(*) as total_documentos,
                        MAX(c.fecha) as ultima_actividad
                    FROM CtaCliente c
                    WHERE c.id = ?
                    AND ISNULL(c.monto,0) - ISNULL(c.abono,0) > 0
                ";

                $result = DB::connection('sqlsrv')->selectOne($query, [$idCliente]);
                
                return [
                    'saldo_pendiente' => $result->saldo_pendiente ?? 0,
                    'total_documentos' => $result->total_documentos ?? 0,
                    'ultima_actividad' => $result->ultima_actividad,
                    'cache_timestamp' => Carbon::now()
                ];
            } catch (\Exception $e) {
                Log::error("Error al cachear saldos cliente {$idCliente}: " . $e->getMessage());
                return ['saldo_pendiente' => 0, 'total_documentos' => 0];
            }
        });
    }

    /**
     * Cache para reportes de estado financiero
     */
    public function cacheReporteEstadoFinanciero($fechaInicio, $fechaFin, $ttl = 120)
    {
        $cacheKey = "estado_financiero_" . md5($fechaInicio . $fechaFin);
        
        return Cache::remember($cacheKey, $ttl, function () use ($fechaInicio, $fechaFin) {
            try {
                // Ingresos
                $ingresos = DB::connection('sqlsrv')->select("
                    SELECT ISNULL(SUM(ISNULL(p.Efectivo,0) + ISNULL(p.Cheque,0)), 0) as total
                    FROM dbo.PlanD_cobranza p
                    WHERE p.FechaFac BETWEEN ? AND ?
                ", [$fechaInicio, $fechaFin]);

                // Gastos
                $gastos = DB::connection('sqlsrv')->select("
                    SELECT ISNULL(SUM(ISNULL(p.Descuento,0)), 0) as total
                    FROM dbo.PlanD_cobranza p
                    WHERE p.FechaFac BETWEEN ? AND ?
                ", [$fechaInicio, $fechaFin]);

                // Costos
                $costos = DB::connection('sqlsrv')->select("
                    SELECT ISNULL(SUM(ISNULL(p.Valor,0)), 0) as total
                    FROM dbo.PlanD_cobranza p
                    WHERE p.FechaFac BETWEEN ? AND ?
                ", [$fechaInicio, $fechaFin]);

                return [
                    'ingresos' => $ingresos[0]->total ?? 0,
                    'gastos' => $gastos[0]->total ?? 0,
                    'costos' => $costos[0]->total ?? 0,
                    'utilidad' => ($ingresos[0]->total ?? 0) - ($gastos[0]->total ?? 0) - ($costos[0]->total ?? 0),
                    'periodo' => ['inicio' => $fechaInicio, 'fin' => $fechaFin],
                    'cache_timestamp' => Carbon::now()
                ];
            } catch (\Exception $e) {
                Log::error("Error al cachear reporte financiero: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Cache para top vendedores
     */
    public function cacheTopVendedores($fechaInicio, $fechaFin, $top = 10, $ttl = 60)
    {
        $cacheKey = "top_vendedores_" . md5($fechaInicio . $fechaFin . $top);
        
        return Cache::remember($cacheKey, $ttl, function () use ($fechaInicio, $fechaFin, $top) {
            try {
                $query = "
                    SELECT TOP {$top}
                        v.Codigo as codigo_vendedor,
                        v.Nombres + ' ' + v.Apellidos as nombre_vendedor,
                        ISNULL(SUM(p.Efectivo + p.Cheque), 0) as total_ventas,
                        COUNT(*) as total_transacciones
                    FROM Vendedores v
                    LEFT JOIN PlanD_cobranza p ON v.Codigo = p.Vendedor
                    WHERE (p.FechaFac BETWEEN ? AND ? OR p.FechaFac IS NULL)
                    GROUP BY v.Codigo, v.Nombres, v.Apellidos
                    ORDER BY total_ventas DESC
                ";

                return DB::connection('sqlsrv')->select($query, [$fechaInicio, $fechaFin]);
            } catch (\Exception $e) {
                Log::error("Error al cachear top vendedores: " . $e->getMessage());
                return collect([]);
            }
        });
    }

    /**
     * Cache para estadísticas de usuario activo (accesoweb)
     */
    public function cacheEstadisticasUsuario($idusuario, $ttl = 30)
    {
        $cacheKey = "stats_usuario_{$idusuario}";
        
        return Cache::remember($cacheKey, $ttl, function () use ($idusuario) {
            try {
                $query = "
                    SELECT 
                        u.idusuario,
                        u.nombre,
                        u.perfil,
                        u.ultimo_acceso,
                        COUNT(p.id) as total_planillas_mes,
                        ISNULL(SUM(CASE WHEN p.fecha >= DATEADD(month, -1, GETDATE()) 
                            THEN p.total_monto ELSE 0 END), 0) as monto_mes_actual
                    FROM accesoweb u
                    LEFT JOIN Planillas p ON u.idusuario = p.idusuario_vendedor
                    WHERE u.idusuario = ?
                    GROUP BY u.idusuario, u.nombre, u.perfil, u.ultimo_acceso
                ";

                $result = DB::connection('sqlsrv')->selectOne($query, [$idusuario]);
                
                return $result ? [
                    'usuario' => [
                        'id' => $result->idusuario,
                        'nombre' => $result->nombre,
                        'perfil' => $result->perfil,
                        'ultimo_acceso' => $result->ultimo_acceso
                    ],
                    'estadisticas' => [
                        'total_planillas_mes' => $result->total_planillas_mes ?? 0,
                        'monto_mes_actual' => $result->monto_mes_actual ?? 0
                    ],
                    'cache_timestamp' => Carbon::now()
                ] : null;
            } catch (\Exception $e) {
                Log::error("Error al cachear estadísticas usuario {$idusuario}: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Cache para documentos pendientes de cobro
     */
    public function cacheDocumentosPendientes($idusuario = null, $ttl = 45)
    {
        $cacheKey = $idusuario ? "docs_pendientes_user_{$idusuario}" : "docs_pendientes_all";
        
        return Cache::remember($cacheKey, $ttl, function () use ($idusuario) {
            try {
                $query = "
                    SELECT 
                        c.id,
                        c.tipo_doc,
                        c.serie,
                        c.numero,
                        c.cliente_nombre,
                        c.fecha,
                        ISNULL(c.monto,0) - ISNULL(c.abono,0) as saldo_pendiente,
                        c.dias_vencido,
                        c.idusuario_vendedor
                    FROM CtaCliente c
                    WHERE ISNULL(c.monto,0) - ISNULL(c.abono,0) > 0
                ";

                $params = [];

                if ($idusuario) {
                    $query .= " AND c.idusuario_vendedor = ?";
                    $params[] = $idusuario;
                }

                $query .= " ORDER BY c.dias_vencido DESC, c.fecha DESC";

                return DB::connection('sqlsrv')->select($query, $params);
            } catch (\Exception $e) {
                Log::error("Error al cachear documentos pendientes: " . $e->getMessage());
                return collect([]);
            }
        });
    }

    /**
     * Invalida cache específico
     */
    public function forget($key)
    {
        return Cache::forget($key);
    }

    /**
     * Invalida múltiples claves de cache
     */
    public function forgetMultiple($keys)
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        return true;
    }

    /**
     * Limpiar cache relacionado con un cliente
     */
    public function limpiarCacheCliente($idCliente)
    {
        $keys = [
            "saldos_cta_cliente_{$idCliente}",
            "docs_pendientes_user_{$idCliente}",
            "docs_pendientes_all"
        ];
        
        return $this->forgetMultiple($keys);
    }

    /**
     * Limpiar cache cuando se actualiza una planilla
     */
    public function limpiarCachePlanilla($idusuario = null)
    {
        $keys = [
            'estado_financiero_' . '%', // Usar pattern matching
            'top_vendedores_' . '%',
            'stats_usuario_' . ($idusuario ?? '%'),
            'docs_pendientes_user_' . ($idusuario ?? '%'),
            'docs_pendientes_all'
        ];
        
        // Para limpiar patrones de cache, necesitamos usar flush específico
        if ($idusuario) {
            $this->forgetMultiple([
                "stats_usuario_{$idusuario}",
                "docs_pendientes_user_{$idusuario}"
            ]);
        } else {
            // Limpiar todos los relacionados
            Cache::flush();
        }
        
        return true;
    }

    /**
     * Verificar estado del cache
     */
    public function cacheStatus()
    {
        try {
            $cacheInfo = Cache::getStore()->getPrefix() ? 'con prefijo' : 'sin prefijo';
            
            return [
                'status' => 'ok',
                'driver' => config('cache.default'),
                'prefix' => config('cache.prefix'),
                'info' => $cacheInfo,
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'timestamp' => Carbon::now()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'timestamp' => Carbon::now()
            ];
        }
    }

    /**
     * Cache warming - precargar datos importantes
     */
    public function warmupCache($idusuario)
    {
        try {
            // Cachear datos importantes
            $this->cacheEstadisticasUsuario($idusuario, 60);
            $this->cacheDocumentosPendientes($idusuario, 60);
            
            // Cache de reportes recientes
            $inicioMes = Carbon::now()->startOfMonth()->format('Y-m-d');
            $finMes = Carbon::now()->endOfMonth()->format('Y-m-d');
            
            $this->cacheReporteEstadoFinanciero($inicioMes, $finMes, 120);
            
            Log::info("Cache warmed up for user: {$idusuario}");
            return true;
        } catch (\Exception $e) {
            Log::error("Error warming up cache for user {$idusuario}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de hit/miss del cache
     */
    public function getCacheMetrics()
    {
        return [
            'cache_store' => config('cache.default'),
            'prefix' => config('cache.prefix'),
            'available_drivers' => array_keys(config('cache.stores')),
            'timestamp' => Carbon::now()
        ];
    }
}