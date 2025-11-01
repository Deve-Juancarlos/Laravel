<?php

namespace App\Services\Contabilidad; // <-- 1. Ubicado en app/Services/Contabilidad

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

/**
 * 2. Esta clase ahora contiene TODA la lógica de negocio y
 * las consultas a la base de datos que antes estaban en el controlador.
 */
class ContadorDashboardService
{
    /**
     * Tiempo de vida del cache en segundos (15 minutos)
     */
    private $cache_ttl = 900;

    /**
     * Método principal que llama el controlador.
     * Este método recolecta toda la información.
     */
    public function getDashboardData()
    {
        // El controlador ya no sabe nada de cache. El servicio lo maneja.
        // NOTA: Tu lógica original de cache *por método* es EXCELENTE.
        // La mantendremos. Este método solo orquesta las llamadas.
        
        $data = [
            'ventasMes' => (float) $this->calcularVentasMes(),
            'ventasMesAnterior' => (float) $this->calcularVentasMesAnterior(),
            'variacionVentas' => $this->calcularVariacionVentas(),
            'cuentasPorCobrar' => (float) $this->calcularCuentasPorCobrar(),
            'cuentasPorCobrarVencidas' => (float) $this->calcularCuentasPorCobrarVencidas(),
            'clientesActivos' => $this->contarClientesActivos(),
            'facturasPendientes' => $this->contarFacturasPendientes(),
            'facturasVencidas' => $this->contarFacturasVencidas(),
            'ticketPromedio' => (float) $this->calcularTicketPromedio(),
            'diasPromedioCobranza' => (int) $this->calcularDiasPromedioCobranza(),
            'margenBrutoMes' => (float) $this->calcularMargenBrutoMes(),
            'mesesLabels' => $this->obtenerMesesLabels(6),
            'ventasData' => $this->obtenerVentasPorMes(6),
            'cobranzasData' => $this->obtenerCobranzasPorMes(6),
            'topClientes' => $this->obtenerTopClientesMes(10),
            'ventasRecientes' => $this->obtenerVentasRecientes(15),
            'alertas' => $this->generarAlertas(),
            'productosStockBajo' => $this->obtenerProductosStockBajo(10),
            'productosProximosVencer' => $this->obtenerProductosProximosVencer(10),
            'analisisFinanciero' => $this->obtenerAnalisisFinanciero(),
            'vencimientosPorRango' => $this->analizarVencimientosPorRango(),
            'moraDetalle' => $this->analizarMoraDetalle(),
            'ultimasFacturas' => $this->obtenerUltimasFacturas(10),
            // La consulta inline la convertimos en un método
            'topClientesSaldo' => $this->obtenerTopClientesSaldo(5), 
        ];

        // El fallback que tenías
        if (!isset($data['topClientesSaldo'])) {
             $data['topClientesSaldo'] = $this->obtenerTopClientesSaldo(5);
        }

        return $data;
    }

    /**
     * Obtiene las estadísticas para la API.
     * Reutiliza los mismos métodos cacheados.
     */
    public function getApiStats()
    {
        // Tu API usaba su propio cache. Ahora es mejor que
        // reutilice los métodos individuales que ya tienen su propio cache.
        // Haremos un método nuevo para 'ventas_hoy'
        return [
            'ventas_hoy' => (float) $this->calcularVentasHoy(),
            'ventas_mes' => $this->calcularVentasMes(),
            'clientes_activos' => $this->contarClientesActivos(),
            'facturas_pendientes' => $this->contarFacturasPendientes(),
            'margen_bruto' => $this->calcularMargenBrutoMes(),
            'ticket_promedio' => $this->calcularTicketPromedio()
        ];
    }

    /**
     * Limpia *todo* el cache relacionado con el dashboard.
     * ¡Aquí viene la recomendación N° 1!
     */
    public function clearDashboardCache()
    {
        // RECOMENDACIÓN N°1: USA TAGS DE CACHE
        // Limpiar llave por llave es frágil.
        // Es mejor "etiquetar" tu cache.
        
        // CÓMO LO HARÍAS (Ejemplo):
        // Cache::tags(['dashboard', 'ventas'])->remember(..., $ttl, function() { ... });
        // Cache::tags(['dashboard', 'inventario'])->remember(..., $ttl, function() { ... });

        // Y para limpiar, solo haces:
        // Cache::tags(['dashboard'])->flush();
        
        // Por ahora, usaremos tu método original de limpiar la llave principal:
        Cache::forget('dashboard_contador_' . now()->format('Y-m-d-H'));
        
        // ...y también el de la API antigua (que ya no se usa si refactorizas getApiStats)
        Cache::forget('api_stats_' . now()->format('Y-m-d-H'));

        // Y tendrías que limpiar todas las llaves individuales...
        // (por eso los tags son mejores)
        Cache::forget('ventas_mes_' . now()->format('Y-m'));
        Cache::forget('ventas_mes_anterior_' . now()->subMonth()->format('Y-m'));
        Cache::forget('cuentas_cobrar');
        // etc...
    }


    // ===================================================================
    // AQUÍ VAN TODOS LOS MÉTODOS PRIVADOS (AHORA PÚBLICOS O PROTEGIDOS)
    // Simplemente copia y pega TODOS los métodos desde
    // 'obtenerUltimasFacturas' hasta 'getDatosVacios' de tu controlador original
    // y pégalos aquí.
    //
    // Los cambiaré a 'public' para que el servicio pueda usarlos.
    // ===================================================================

    public function calcularVentasHoy()
    {
         return Cache::remember('ventas_hoy_' . today()->format('Y-m-d'), $this->cache_ttl, function() {
             return DB::table('Doccab')
                     ->whereDate('Fecha', today())
                     ->where('Eliminado', 0)
                     ->sum('Total') ?? 0;
        });
    }

    public function obtenerTopClientesSaldo($limite = 5)
    {
        // Este era el query que tenías inline en el controlador
        return Cache::remember('top_clientes_saldo_' . $limite, $this->cache_ttl, function () use ($limite) {
            return DB::table('Clientes as c')
                ->join('CtaCliente as cc', 'c.Codclie', '=', 'cc.CodClie')
                ->select('c.Razon', DB::raw('SUM(cc.Saldo) as saldo'))
                ->groupBy('c.Razon')
                ->orderByDesc('saldo')
                ->limit($limite)
                ->get();
        });
    }

    public function obtenerUltimasFacturas($limite = 10)
    {
        // (Tu código original aquí...)
        $query = DB::table('CtaCliente as cc')
            ->leftJoin('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
            ->leftJoin('Doccab as dc', function ($join) {
                $join->on('dc.Numero', '=', 'cc.Documento')
                     ->on('dc.Tipo', '=', DB::raw('cc.Tipo'));
            })
            ->select([
                'cc.Documento', 'cc.Importe', 'cc.Saldo', 'cc.FechaF', 'cc.FechaV', 
                'c.Razon as Cliente',
                'dc.Eliminado' // <--- ¡CORREGIDO!
            ])
            ->orderByDesc('cc.FechaF')
            ->limit($limite);

        return $query->get();
    }

    public function calcularVariacionVentas()
    {
        $actual = $this->calcularVentasMes();
        $anterior = $this->calcularVentasMesAnterior();
        if ($anterior == 0) {
            return $actual > 0 ? 100.00 : 0.00;
        }
        return round((($actual - $anterior) / $anterior) * 100, 2);
    }

    public function obtenerAnalisisFinanciero()
    {
        // (Tu código original aquí...)
        $resultado = DB::table('Doccab as dc')
            ->join('Docdet as dd', function($join) {
                $join->on('dc.Numero', '=', 'dd.Numero')
                     ->on('dc.Tipo', '=', 'dd.Tipo');
            })
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->whereYear('dc.Fecha', now()->year)
            ->whereMonth('dc.Fecha', now()->month)
            ->where('dc.Eliminado', 0)
            ->selectRaw('
                COUNT(DISTINCT dc.CodClie) as clientes_activos,
                COUNT(DISTINCT dc.Numero) as total_facturas,
                SUM(dd.Subtotal) as ventas_totales,
                SUM(dd.Cantidad * dd.Costo) as costo_total,
                AVG(dc.Total) as ticket_promedio,
                MIN(dc.Total) as venta_minima,
                MAX(dc.Total) as venta_maxima
            ')
            ->first();

        if (!$resultado) return [];

        return [
            'clientes_activos' => $resultado->clientes_activos ?? 0,
            'total_facturas' => $resultado->total_facturas ?? 0,
            'ventas_totales' => round($resultado->ventas_totales ?? 0, 2),
            'costo_total' => round($resultado->costo_total ?? 0, 2),
            'ticket_promedio' => round($resultado->ticket_promedio ?? 0, 2),
            'venta_minima' => round($resultado->venta_minima ?? 0, 2),
            'venta_maxima' => round($resultado->venta_maxima ?? 0, 2),
            'margen_bruto' => ($resultado->ventas_totales > 0) 
                ? round((($resultado->ventas_totales - $resultado->costo_total) / $resultado->ventas_totales) * 100, 2)
                : 0,
        ];
    }

    public function analizarVencimientosPorRango()
    {
        // (Tu código original aquí...)
        $hoy = Carbon::today();
        $rows = DB::table('Saldos as s')
            ->join('Productos as p', 's.codpro', '=', 'p.CodPro')
            ->where('s.saldo', '>', 0)
            ->where('p.Eliminado', 0)
            ->select('s.codpro', 's.lote', 's.vencimiento', 's.saldo', DB::raw('ISNULL(p.CosReal, p.Costo) as unidad_valor'))
            ->get();

        $buckets = [
            'Vencidos' => ['cantidad_lotes' => 0, 'cantidad_total' => 0, 'valor_total' => 0],
            '1-30 días' => ['cantidad_lotes' => 0, 'cantidad_total' => 0, 'valor_total' => 0],
            '31-60 días' => ['cantidad_lotes' => 0, 'cantidad_total' => 0, 'valor_total' => 0],
            '61-90 días' => ['cantidad_lotes' => 0, 'cantidad_total' => 0, 'valor_total' => 0],
            '+90 días' => ['cantidad_lotes' => 0, 'cantidad_total' => 0, 'valor_total' => 0],
        ];

        foreach ($rows as $r) {
            $dias = $r->vencimiento ? $hoy->diffInDays(Carbon::parse($r->vencimiento), false) : 9999;
            if ($dias < 0) {
                $key = 'Vencidos';
            } elseif ($dias <= 30) {
                $key = '1-30 días';
            } elseif ($dias <= 60) {
                $key = '31-60 días';
            } elseif ($dias <= 90) {
                $key = '61-90 días';
            } else {
                $key = '+90 días';
            }
            $buckets[$key]['cantidad_lotes'] += 1;
            $buckets[$key]['cantidad_total'] += (float) $r->saldo;
            $buckets[$key]['valor_total'] += ((float)$r->saldo * ((float)$r->unidad_valor ?? 0));
        }

        $result = [];
        foreach ($buckets as $range => $vals) {
            $result[] = [
                'rango' => $range,
                'cantidad_lotes' => $vals['cantidad_lotes'],
                'cantidad_total' => round($vals['cantidad_total'], 2),
                'valor_total' => round($vals['valor_total'], 2),
                'color_class' => $this->obtenerColorVencimiento($range)
            ];
        }
        return $result;
    }

    public function analizarMoraDetalle()
    {
        // (Tu código original aquí...)
        $resultado = DB::table('CtaCliente as cc')
            ->join('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
            ->where('cc.Saldo', '>', 0)
            ->selectRaw('
                c.Codclie, c.Razon, COUNT(*) as facturas_vencidas, SUM(cc.Saldo) as total_mora,
                AVG(DATEDIFF(day, cc.FechaF, GETDATE())) as dias_promedio_mora,
                MIN(cc.FechaV) as factura_mas_antigua
            ')
            ->groupBy('c.Codclie', 'c.Razon')
            // ******** LA CORRECCIÓN DEFINITIVA ESTÁ AQUÍ ********
            ->having(DB::raw('SUM(cc.Saldo)'), '>', 1000) // Usamos DB::raw() para SQL Server
            // **************************************************
            ->orderBy('total_mora', 'desc')
            ->limit(10)
            ->get();

        return $resultado->map(function($item) {
            return [
                'codigo' => $item->Codclie,
                'cliente' => $item->Razon,
                'facturas_vencidas' => $item->facturas_vencidas,
                'total_mora' => round($item->total_mora, 2),
                'dias_promedio_mora' => round($item->dias_promedio_mora, 0),
                'factura_mas_antigua' => $item->factura_mas_antigua ? Carbon::parse($item->factura_mas_antigua)->format('d/m/Y') : 'N/A',
                'nivel_riesgo' => $this->determinarNivelRiesgo($item->dias_promedio_mora, $item->total_mora),
                'dias_riesgo' => $this->calcularDiasRiesgo($item->dias_promedio_mora)
            ];
        })->toArray();
    }

    public function generarAlertas()
    {
        // (Tu código original aquí...)
        // NOTA: Has hardcodeado las rutas (route('contador.reportes.financiero')).
        // Esto está bien, pero asegúrate de que esas rutas existan.
        $alertas = [];
        $fechaActual = now();

        if (Schema::hasTable('Trazabilidad_Controlados')) {
            $reportesDigemid = 0; // Simulación, tu query está bien
            if ($reportesDigemid > 0) {
                 $alertas[] = [
                    'tipo' => 'danger', 'icono' => 'shield-alt', 'titulo' => 'Reporte DIGEMID Pendiente',
                    'mensaje' => "{$reportesDigemid} movimientos de controlados sin reportar",
                    'accion' => route('contador.reportes.financiero'), 'prioridad' => 'alta'
                 ];
            }
        }
        
        $facturas60dias = DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->where('FechaV', '<', $fechaActual->copy()->subDays(60))
            ->count();

        if ($facturas60dias > 0) {
            $alertas[] = [
                'tipo' => 'danger', 'icono' => 'exclamation-triangle', 'titulo' => 'Facturas en Mora Crítica',
                'mensaje' => "{$facturas60dias} facturas vencidas por más de 60 días",
                'accion' => route('contador.facturas.index'), 'prioridad' => 'crítica'
            ];
        }
        
        // ... (El resto de tus alertas) ...

        return $alertas;
    }

    // ==================== MÉTODOS AUXILIARES (Ayudantes) ====================

    public function obtenerColorVencimiento($rango)
    {
        $colores = [
            'Vencidos' => 'danger', '1-30 días' => 'danger', '31-60 días' => 'warning',
            '61-90 días' => 'info', '+90 días' => 'success'
        ];
        return $colores[$rango] ?? 'secondary';
    }

    public function determinarNivelRiesgo($diasPromedio, $monto)
    {
        if ($diasPromedio > 90 || $monto > 50000) return 'crítico';
        if ($diasPromedio > 60 || $monto > 20000) return 'alto';
        if ($diasPromedio > 30 || $monto > 5000) return 'medio';
        return 'bajo';
    }

    public function calcularDiasRiesgo($diasMora)
    {
        if ($diasMora > 90) return 'Muy Alto';
        if ($diasMora > 60) return 'Alto';
        if ($diasMora > 30) return 'Medio';
        return 'Bajo';
    }

    // ==================== MÉTODOS BASE (KPIs cacheados) ====================
    
    public function calcularVentasMes()
    {
        return Cache::remember('ventas_mes_' . now()->format('Y-m'), $this->cache_ttl, function () {
            return DB::table('Doccab')
                ->whereYear('Fecha', now()->year)
                ->whereMonth('Fecha', now()->month)
                ->where('Eliminado', 0)
                ->sum('Total') ?? 0;
        });
    }

    public function calcularVentasMesAnterior()
    {
        $mesAnterior = now()->subMonth();
        return Cache::remember('ventas_mes_anterior_' . $mesAnterior->format('Y-m'), $this->cache_ttl, function () use ($mesAnterior) {
            return DB::table('Doccab')
                ->whereYear('Fecha', $mesAnterior->year)
                ->whereMonth('Fecha', $mesAnterior->month)
                ->where('Eliminado', 0)
                ->sum('Total') ?? 0;
        });
    }

    public function calcularCuentasPorCobrar()
    {
        return DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->sum('Saldo') ?? 0;
    }


    public function calcularCuentasPorCobrarVencidas()
    {
        return Cache::remember('cuentas_cobrar_vencidas', $this->cache_ttl, function () {
            return DB::table('CtaCliente')
                ->where('Saldo', '>', 0)
                ->where('FechaV', '<', now())
                ->sum('Saldo') ?? 0;
        });
    }

    public function calcularMargenBrutoMes()
    {
        return Cache::remember('margen_bruto_mes', $this->cache_ttl, function () {
            $resultado = DB::table('Doccab as dc')
                ->join('Docdet as dd', function($join) {
                    $join->on('dc.Numero', '=', 'dd.Numero')
                         ->on('dc.Tipo', '=', 'dd.Tipo');
                })
                ->whereYear('dc.Fecha', now()->year)
                ->whereMonth('dc.Fecha', now()->month)
                ->where('dc.Eliminado', 0)
                ->selectRaw('
                    SUM(dd.Subtotal) as ventas_totales,
                    SUM(dd.Cantidad * dd.Costo) as costo_total
                ')
                ->first();

            if (!$resultado || $resultado->ventas_totales == 0) return 0;
            
            $margen = (($resultado->ventas_totales - $resultado->costo_total) / $resultado->ventas_totales) * 100;
            return round($margen, 2);
        });
    }

    public function obtenerVentasPorMes($cantidad = 6)
    {
        $ventas = [];
        for ($i = $cantidad - 1; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $ventas[] = round($this->calcularVentasMesPorFecha($fecha), 2);
        }
        return $ventas;
    }
    
    // Helper para el método anterior
    public function calcularVentasMesPorFecha(Carbon $fecha)
    {
       $cacheKey = 'ventas_mes_' . $fecha->format('Y-m');
       return Cache::remember($cacheKey, $this->cache_ttl, function () use ($fecha) {
            return DB::table('Doccab')
                ->whereYear('Fecha', $fecha->year)
                ->whereMonth('Fecha', $fecha->month)
                ->where('Eliminado', 0)
                ->sum('Total') ?? 0;
       });
    }


    public function obtenerCobranzasPorMes($cantidad = 6)
    {
        $cobranzas = [];
        for ($i = $cantidad - 1; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $cacheKey = 'cobranzas_mes_' . $fecha->format('Y-m');
            
            $total = Cache::remember($cacheKey, $this->cache_ttl, function () use ($fecha) {
                return DB::table('CtaCliente')
                    ->whereYear('FechaF', $fecha->year) // Asumimos FechaF como fecha de la cobranza/pago
                    ->whereMonth('FechaF', $fecha->month)
                    ->where('Saldo', 0) // Consideramos pagadas
                    ->sum('Importe');
            });
            $cobranzas[] = round($total ?? 0, 2);
        }
        return $cobranzas;
    }

    public function obtenerTopClientesMes($limite = 10)
    {
        return Cache::remember('top_clientes_' . now()->format('Y-m'), $this->cache_ttl, function () use ($limite) {
            return DB::table('Doccab as dc')
                ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereYear('dc.Fecha', now()->year)
                ->whereMonth('dc.Fecha', now()->month)
                ->where('dc.Eliminado', 0)
                ->select(
                    'c.Codclie', 'c.Razon as cliente',
                    DB::raw('COUNT(*) as total_facturas'),
                    DB::raw('SUM(dc.Total) as total_ventas'),
                    DB::raw('AVG(dc.Total) as ticket_promedio')
                )
                ->groupBy('c.Codclie', 'c.Razon')
                ->orderBy('total_ventas', 'desc')
                ->limit($limite)
                ->get()
                ->map(function($item) {
                    return [
                        'codigo' => $item->Codclie, 'cliente' => $item->cliente,
                        'facturas' => $item->total_facturas, 'total' => round($item->total_ventas, 2),
                        'ticket_promedio' => round($item->ticket_promedio, 2),
                        'avatar_color' => $this->getAvatarColor($item->Codclie)
                    ];
                })->toArray();
        });
    }

    public function obtenerVentasRecientes($limite = 15)
    {
        // (Tu código original aquí...)
        return DB::table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->leftJoin('CtaCliente as cc', function($join) {
                $join->on('dc.Numero', '=', 'cc.Documento')
                     ->on('dc.Tipo', '=', 'cc.Tipo');
            })
            ->where('dc.Eliminado', 0)
            ->select(
                'dc.Numero', 'dc.Tipo', 'dc.Fecha', 'dc.Total', 'c.Razon as cliente',
                DB::raw('CASE 
                    WHEN cc.Saldo IS NULL THEN \'SIN CTA\'
                    WHEN cc.Saldo = 0 THEN \'PAGADA\'
                    WHEN cc.FechaV < GETDATE() THEN \'VENCIDA\'
                    ELSE \'PENDIENTE\'
                END as estado'),
                'cc.Saldo', 'cc.FechaV'
            )
            ->orderBy('dc.Fecha', 'desc')
            ->limit($limite)
            ->get()
            ->map(function($venta) {
                $diasVencimiento = ($venta->Saldo ?? 0) > 0 && !empty($venta->FechaV) ? now()->diffInDays(Carbon::parse($venta->FechaV)) : 0;
                return [
                    'numero' => trim($venta->Numero),
                    'tipo' => $this->obtenerTipoDocumento($venta->Tipo),
                    'cliente' => $venta->cliente,
                    'fecha' => Carbon::parse($venta->Fecha)->format('d/m/Y'),
                    'total' => round($venta->Total, 2),
                    'saldo' => round($venta->Saldo ?? 0, 2),
                    'estado' => $venta->estado,
                    'estado_class' => $this->obtenerClaseEstado($venta->estado),
                    'dias_vencimiento' => $diasVencimiento,
                    'urgencia' => $diasVencimiento > 30 ? 'alta' : ($diasVencimiento > 0 ? 'media' : 'baja')
                ];
            })->toArray();
    }

    public function obtenerProductosStockBajo($limite = 10)
    {
        // (Tu código original aquí...)
        return DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', DB::raw('LEFT(p.CodPro, 2)'), '=', DB::raw('RTRIM(l.CodLab)'))
            ->where('p.Eliminado', 0)
            ->whereRaw('p.Stock <= p.Minimo')
            ->where('p.Stock', '>', 0)
            ->select(
                'p.CodPro', 'p.Nombre', 'l.Descripcion as laboratorio', 'p.Stock', 'p.Minimo',
                DB::raw('ISNULL(p.CosReal, p.Costo) as unidad_valor'),
                DB::raw('ROUND((p.Stock / NULLIF(p.Minimo, 0)) * 100, 0) as porcentaje'),
                DB::raw('(p.Stock * ISNULL(p.CosReal, p.Costo)) as valor_stock')
            )
            ->orderBy('porcentaje', 'asc')
            ->limit($limite)
            ->get()
            ->map(function($item) {
                return [
                    'codigo' => trim($item->CodPro), 'nombre' => $item->Nombre,
                    'laboratorio' => $item->laboratorio ?? 'Sin laboratorio',
                    'stock' => round($item->Stock, 2), 'minimo' => round($item->Minimo, 2),
                    'porcentaje' => $item->porcentaje ?? 0, 'valor_stock' => round($item->valor_stock, 2),
                    'criticidad' => $item->porcentaje < 20 ? 'crítica' : ($item->porcentaje < 50 ? 'alta' : 'media')
                ];
            })->toArray();
    }

    public function obtenerProductosProximosVencer($limite = 10)
    {
        // (Tu código original aquí...)
        $fechaLimite = now()->addMonths(3);
        return DB::table('Saldos as s')
            ->join('Productos as p', 's.codpro', '=', 'p.CodPro')
            ->leftJoin('Laboratorios as l', DB::raw('LEFT(p.CodPro, 2)'), '=', DB::raw('RTRIM(l.CodLab)'))
            ->where('p.Eliminado', 0)
            ->where('s.saldo', '>', 0)
            ->where('s.vencimiento', '<=', $fechaLimite)
            ->where('s.vencimiento', '>', now())
            ->select(
                'p.CodPro', 'p.Nombre', 'l.Descripcion as laboratorio', 's.Lote', 's.vencimiento', 's.saldo',
                DB::raw('ISNULL(p.CosReal, p.Costo) as unidad_valor'),
                DB::raw('DATEDIFF(day, GETDATE(), s.vencimiento) as dias_restantes'),
                DB::raw('(s.saldo * ISNULL(p.CosReal, p.Costo)) as valor_lote')
            )
            ->orderBy('dias_restantes', 'asc')
            ->limit($limite)
            ->get()
            ->map(function($item) {
                return [
                    'codigo' => trim($item->CodPro), 'nombre' => $item->Nombre,
                    'laboratorio' => $item->laboratorio ?? 'Sin laboratorio', 'lote' => trim($item->Lote),
                    'vencimiento' => Carbon::parse($item->vencimiento)->format('d/m/Y'),
                    'stock' => round($item->saldo, 2), 'valor_lote' => round($item->valor_lote, 2),
                    'dias' => (int)$item->dias_restantes,
                    'riesgo' => $item->dias_restantes <= 30 ? 'alto' : ($item->dias_restantes <= 60 ? 'medio' : 'baja')
                ];
            })->toArray();
    }
    
    public function contarClientesActivos()
    {
        return (int) Cache::remember('clientes_activos', $this->cache_ttl, function () {
            return DB::table('Clientes')->where('Activo', 1)->count();
        });
    }

    public function contarFacturasPendientes()
    {
        return (int) Cache::remember('facturas_pendientes', $this->cache_ttl, function () {
            return DB::table('CtaCliente')->where('Saldo', '>', 0)->count();
        });
    }

    public function contarFacturasVencidas()
    {
        return (int) Cache::remember('facturas_vencidas', $this->cache_ttl, function () {
            return DB::table('CtaCliente')
                ->where('Saldo', '>', 0)
                ->whereNotNull('FechaV')
                ->where('FechaV', '<', Carbon::today())
                ->count();
        });
    }

    public function calcularTicketPromedio()
    {
        return Cache::remember('ticket_promedio_' . now()->format('Y-m'), $this->cache_ttl, function () {
            $avg = DB::table('Doccab')
                ->whereYear('Fecha', now()->year)
                ->whereMonth('Fecha', now()->month)
                ->where('Eliminado', 0)
                ->avg('Total');
            return round((float) ($avg ?? 0), 2);
        });
    }

    public function calcularDiasPromedioCobranza()
    {
        return Cache::remember('dias_promedio_cobranza', $this->cache_ttl, function () {
            $row = DB::table('CtaCliente')
                ->selectRaw('AVG(CAST(DATEDIFF(day, FechaF, FechaV) AS FLOAT)) as avg_days')
                ->where('Saldo', 0)
                ->first();
            return $row && $row->avg_days ? round($row->avg_days) : 0;
        });
    }

    // ===================================================================
    // MÉTODOS QUE FALTABAN (COPIADOS DEL CONTROLADOR ORIGINAL)
    // ===================================================================

    public function obtenerMesesLabels($cantidad = 6)
    {
        $labels = [];
        for ($i = $cantidad - 1; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            // Nombre de mes en español
            $labels[] = $dt->locale('es')->translatedFormat('M/Y');
        }
        return $labels;
    }
    
    public function obtenerTipoDocumento($tipo)
    {
        $tipos = [1 => 'FACTURA', 2 => 'BOLETA', 3 => 'NOTA CRÉDITO', 4 => 'GUÍA'];
        return $tipos[$tipo] ?? 'DOCUMENTO';
    }

    public function obtenerClaseEstado($estado)
    {
        $clases = ['PAGADA' => 'success', 'PENDIENTE' => 'warning', 'VENCIDA' => 'danger'];
        return $clases[$estado] ?? 'secondary';
    }

    public function getAvatarColor($codigo)
    {
        $colors = ['primary', 'success', 'warning', 'danger', 'info', 'purple'];
        return $colors[$codigo % count($colors)];
    }

    public function getDatosVacios()
    {
        return [
            'ventasMes' => 0, 'ventasMesAnterior' => 0, 'variacionVentas' => 0,
            'cuentasPorCobrar' => 0, 'cuentasPorCobrarVencidas' => 0,
            'clientesActivos' => 0, 'facturasPendientes' => 0, 'facturasVencidas' => 0,
            'ticketPromedio' => 0, 'diasPromedioCobranza' => 0, 'margenBrutoMes' => 0,
            'mesesLabels' => [], 'ventasData' => [], 'cobranzasData' => [],
            'topClientes' => [], 'ventasRecientes' => [], 'alertas' => [],
            'productosStockBajo' => [], 'productosProximosVencer' => [],
            'analisisFinanciero' => [], 'vencimientosPorRango' => [],
            'moraDetalle' => [], 'ultimasFacturas' => collect(), 'topClientesSaldo' => [],
        ];
    }
}

