<?php

namespace App\Services\Contabilidad;

// 1. IMPORTAMOS TODOS LOS MODELOS QUE VAMOS A USAR
use App\Models\Cliente;
use App\Models\CtaCliente;
use App\Models\Doccab;
use App\Models\Docdet;
use App\Models\Producto;
use App\Models\Saldo;
// Importamos los Modelos de Vistas (¡Observa la nueva ruta!)
use App\Models\Vistas\VistaAgingCartera;
use App\Models\Vistas\VistaProductosPorVencer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ContadorDashboardService
{
    
    private $cache_ttl = 900;
    private $cache_tag = 'dashboard_contador'; // ¡Perfecto!

 
    public function getDashboardData()
    {
        // El orquestador principal no cambia, sigue siendo perfecto.
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
            'topClientesSaldo' => $this->obtenerTopClientesSaldo(5), 
        ];

        if (!isset($data['topClientesSaldo'])) {
             $data['topClientesSaldo'] = $this->obtenerTopClientesSaldo(5);
        }

        return $data;
    }

    public function getApiStats()
    {
        // Esto está perfecto, reutiliza los métodos cacheados.
        return [
            'ventas_hoy' => (float) $this->calcularVentasHoy(),
            'ventas_mes' => $this->calcularVentasMes(),
            'clientes_activos' => $this->contarClientesActivos(),
            'facturas_pendientes' => $this->contarFacturasPendientes(),
            'margen_bruto' => $this->calcularMargenBrutoMes(),
            'ticket_promedio' => $this->calcularTicketPromedio()
        ];
    }

    public function clearDashboardCache()
    {
        // Esta implementación con Tags es robusta y correcta.
        Log::info('Limpiando caché del dashboard de contador...');
        Cache::tags($this->cache_tag)->flush();
    }

    // --- MÉTODOS DE CÁLCULO REFACTORIZADOS ---

    public function calcularVentasHoy()
    {
         return Cache::tags($this->cache_tag)->remember('ventas_hoy_' . today()->format('Y-m-d'), $this->cache_ttl, function() {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularVentasHoy');
            // REFACTOR: DB::table('Doccab') -> Modelo Doccab
            return Doccab::whereDate('Fecha', today())
                         ->where('Eliminado', 0)
                         ->sum('Total') ?? 0;
         });
    }

    public function obtenerTopClientesSaldo($limite = 5)
    {
        return Cache::tags($this->cache_tag)->remember('top_clientes_saldo_' . $limite, $this->cache_ttl, function () use ($limite) {
            Log::debug('EJECUTANDO QUERY (CON MODELO): obtenerTopClientesSaldo');
            // REFACTOR: DB::table('Clientes') -> Modelo Cliente
            // Usamos 'withSum' para cargar la suma del saldo de la relación 'cuentasPorCobrar'
            return Cliente::select('Razon')
                ->withSum(['cuentasPorCobrar as saldo' => fn($query) => $query->where('Saldo', '>', 0)], 'Saldo')
                ->orderByDesc('saldo')
                ->limit($limite)
                ->get();
        });
    }

    public function obtenerUltimasFacturas($limite = 10)
    {
        return Cache::tags($this->cache_tag)->remember('ultimas_facturas_' . $limite, $this->cache_ttl, function () use ($limite) {
            Log::debug('EJECUTANDO QUERY (CON MODELO): obtenerUltimasFacturas');
            // REFACTOR: DB::table('CtaCliente') -> Modelo CtaCliente
            // Cargamos la relación 'cliente' que definimos en el Modelo
            return CtaCliente::with('cliente:Codclie,Razon') // Carga Cliente (solo Codclie y Razon)
                ->select('Documento', 'Importe', 'Saldo', 'FechaF', 'FechaV', 'CodClie')
                // ->whereHas('cabecera', fn($q) => $q->where('Eliminado', 0)) // (Opcional, si 'cabecera' está definida)
                ->orderByDesc('FechaF')
                ->limit($limite)
                ->get();
        });
    }

    public function calcularVariacionVentas()
    {
        // Esta lógica de PHP está perfecta, no se toca.
        $actual = $this->calcularVentasMes();
        $anterior = $this->calcularVentasMesAnterior();
        if ($anterior == 0) {
            return $actual > 0 ? 100.00 : 0.00;
        }
        return round((($actual - $anterior) / $anterior) * 100, 2);
    }

    public function obtenerAnalisisFinanciero()
    {
        // ANÁLISIS (PROFESOR): Esta consulta es muy compleja (multi-join, aggregates).
        // Es un candidato perfecto para ser una VISTA o un SP en SQL Server.
        // Por ahora, lo dejamos en Query Builder (DB::table) porque es eficiente
        // y moverlo a Eloquent sería más lento y complejo.
        return Cache::tags($this->cache_tag)->remember('analisis_financiero_mes_actual', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (DB::table): obtenerAnalisisFinanciero');
            // (Mantenemos la consulta original de DB::table... es la mejor herramienta para este trabajo)
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

            if (!$resultado) return [
                'clientes_activos' => 0, 'total_facturas' => 0, 'ventas_totales' => 0,
                'costo_total' => 0, 'ticket_promedio' => 0, 'venta_minima' => 0,
                'venta_maxima' => 0, 'margen_bruto' => 0,
            ];

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
        });
    }

    public function analizarVencimientosPorRango()
    {
        return Cache::tags($this->cache_tag)->remember('vencimientos_por_rango', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): analizarVencimientosPorRango');
            $hoy = Carbon::today();
            
            // REFACTOR: DB::table('Saldos') -> Modelo Saldo
            // Cargamos la relación 'producto' (que definimos en el Modelo)
            $rows = Saldo::with('producto:CodPro,CosReal,Costo') // Carga solo las columnas necesarias
                ->where('saldo', '>', 0)
                ->whereHas('producto', fn($q) => $q->where('Eliminado', 0)) // Filtra por productos no eliminados
                ->select('codpro', 'lote', 'vencimiento', 'saldo')
                ->get();

            $buckets = [
                'Vencidos' => ['cantidad_lotes' => 0, 'cantidad_total' => 0, 'valor_total' => 0],
                '1-30 días' => ['cantidad_lotes' => 0, 'cantidad_total' => 0, 'valor_total' => 0],
                '31-60 días' => ['cantidad_lotes' => 0, 'cantidad_total' => 0, 'valor_total' => 0],
                '61-90 días' => ['cantidad_lotes' => 0, 'cantidad_total' => 0, 'valor_total' => 0],
                '+90 días' => ['cantidad_lotes' => 0, 'cantidad_total' => 0, 'valor_total' => 0],
            ];

            foreach ($rows as $r) {
                // (Tu lógica de bucketing es perfecta)
                $dias = $r->vencimiento ? $hoy->diffInDays($r->vencimiento, false) : 9999;
                $key = '';
                if ($dias < 0) { $key = 'Vencidos'; }
                elseif ($dias <= 30) { $key = '1-30 días'; }
                elseif ($dias <= 60) { $key = '31-60 días'; }
                elseif ($dias <= 90) { $key = '61-90 días'; }
                else { $key = '+90 días'; }
                
                $buckets[$key]['cantidad_lotes'] += 1;
                $buckets[$key]['cantidad_total'] += (float) $r->saldo;
                // Usamos el producto cargado desde la relación
                $unidad_valor = $r->producto->CosReal ?? $r->producto->Costo ?? 0;
                $buckets[$key]['valor_total'] += ((float)$r->saldo * (float)$unidad_valor);
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
        });
    }

    public function analizarMoraDetalle()
    {
        return Cache::tags($this->cache_tag)->remember('mora_detalle', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON VISTA): analizarMoraDetalle');
            
            // 🚀 REFACTORIZACIÓN DEL CONTADOR
            // En lugar de una consulta compleja, usamos la VISTA que ya creamos y optimizamos.
            // [v_aging_cartera]
            $resultados = VistaAgingCartera::select('Codclie', 'Razon')
                ->selectRaw('COUNT(*) as facturas_vencidas, 
                             SUM(Saldo) as total_mora,
                             AVG(dias_vencidos) as dias_promedio_mora,
                             MIN(FechaV) as factura_mas_antigua')
                ->where('dias_vencidos', '>', 0) // Solo vencidas
                ->groupBy('Codclie', 'Razon')
                ->having('total_mora', '>', 1000)
                ->orderByDesc('total_mora')
                ->limit(10)
                ->get();

            return $resultados->map(function($item) {
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
        });
    }

    public function generarAlertas()
    {
        Log::debug('EJECUTANDO QUERY (CON MODELO): generarAlertas');
        $alertas = [];
        $fechaActual = now();
        
        if (Schema::hasTable('Trazabilidad_Controlados')) {
            $reportesDigemid = 0; // Tu lógica para contar esto aquí
            if ($reportesDigemid > 0) {
                 $alertas[] = [
                    'tipo' => 'danger', 'icono' => 'shield-alt', 'titulo' => 'Reporte DIGEMID Pendiente',
                    'mensaje' => "{$reportesDigemid} movimientos de controlados sin reportar",
                    'accion' => route('contador.reportes.financiero'), 'prioridad' => 'alta'
                 ];
            }
        }
        
        // REFACTOR: DB::table('CtaCliente') -> Modelo CtaCliente
        $facturas60dias = CtaCliente::where('Saldo', '>', 0)
            ->where('FechaV', '<', $fechaActual->copy()->subDays(60))
            ->count();

        if ($facturas60dias > 0) {
            $alertas[] = [
                'tipo' => 'danger', 'icono' => 'exclamation-triangle', 'titulo' => 'Facturas en Mora Crítica',
                'mensaje' => "{$facturas60dias} facturas vencidas por más de 60 días",
                'accion' => route('contador.facturas.index'), 'prioridad' => 'crítica'
            ];
        }
        
        return $alertas;
    }

    
    public function calcularVentasMes()
    {
        return Cache::tags($this->cache_tag)->remember('ventas_mes_' . now()->format('Y-m'), $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularVentasMes');
            // REFACTOR: DB::table('Doccab') -> Modelo Doccab
            return Doccab::whereYear('Fecha', now()->year)
                ->whereMonth('Fecha', now()->month)
                ->where('Eliminado', 0)
                ->sum('Total') ?? 0;
        });
    }

    public function calcularVentasMesAnterior()
    {
        $mesAnterior = now()->subMonth();
        return Cache::tags($this->cache_tag)->remember('ventas_mes_anterior_' . $mesAnterior->format('Y-m'), $this->cache_ttl, function () use ($mesAnterior) {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularVentasMesAnterior');
            // REFACTOR: DB::table('Doccab') -> Modelo Doccab
            return Doccab::whereYear('Fecha', $mesAnterior->year)
                ->whereMonth('Fecha', $mesAnterior->month)
                ->where('Eliminado', 0)
                ->sum('Total') ?? 0;
        });
    }

    public function calcularCuentasPorCobrar()
    {
        return Cache::tags($this->cache_tag)->remember('cuentas_cobrar_total', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularCuentasPorCobrar');
            // REFACTOR: DB::table('CtaCliente') -> Modelo CtaCliente
            return CtaCliente::where('Saldo', '>', 0)->sum('Saldo') ?? 0;
        });
    }

    public function calcularCuentasPorCobrarVencidas()
    {
        return Cache::tags($this->cache_tag)->remember('cuentas_cobrar_vencidas', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularCuentasPorCobrarVencidas');
            // REFACTOR: DB::table('CtaCliente') -> Modelo CtaCliente
            return CtaCliente::where('Saldo', '>', 0)
                ->where('FechaV', '<', now())
                ->sum('Saldo') ?? 0;
        });
    }

    public function calcularMargenBrutoMes()
    {
        // ANÁLISIS (PROFESOR): Esta consulta sigue siendo compleja.
        // DB::table es la herramienta correcta aquí.
        return Cache::tags($this->cache_tag)->remember('margen_bruto_mes', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (DB::table): calcularMargenBrutoMes');
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

    // 🚀 SOLUCIÓN: N+1
    public function obtenerVentasPorMes($cantidad = 6)
    {
        $cacheKey = 'ventas_por_mes_ultimos_' . $cantidad;
        return Cache::tags($this->cache_tag)->remember($cacheKey, $this->cache_ttl, function () use ($cantidad) {
            Log::debug('EJECUTANDO QUERY (OPTIMIZADA N+1): obtenerVentasPorMes');
            
            $fechaInicio = now()->subMonths($cantidad - 1)->startOfMonth();

            // 1. UNA SOLA CONSULTA a la base de datos
            $ventasPorMes = Doccab::where('Fecha', '>=', $fechaInicio)
                ->where('Eliminado', 0)
                ->select(
                    DB::raw('YEAR(Fecha) as anio'),
                    DB::raw('MONTH(Fecha) as mes'),
                    DB::raw('SUM(Total) as total')
                )
                ->groupBy('anio', 'mes')
                ->orderBy('anio', 'asc')
                ->orderBy('mes', 'asc')
                ->get()
                ->keyBy(fn($item) => $item->anio . '-' . $item->mes); // Crea un mapa "2023-11" => total

            // 2. Construimos el array de datos en PHP (instantáneo)
            $datos = [];
            for ($i = $cantidad - 1; $i >= 0; $i--) {
                $fecha = now()->subMonths($i);
                $key = $fecha->year . '-' . $fecha->month;
                // Si existe la llave, usa el total. Si no, 0.
                $datos[] = round($ventasPorMes->get($key)->total ?? 0, 2);
            }
            return $datos;
        });
    }
    
    // Ya no necesitamos 'calcularVentasMesPorFecha', el método de arriba lo reemplaza.

    // 🚀 SOLUCIÓN: N+1 (Aplicada también a Cobranzas)
    public function obtenerCobranzasPorMes($cantidad = 6)
    {
        $cacheKey = 'cobranzas_por_mes_ultimos_' . $cantidad;
        return Cache::tags($this->cache_tag)->remember($cacheKey, $this->cache_ttl, function () use ($cantidad) {
            Log::debug('EJECUTANDO QUERY (OPTIMIZADA N+1): obtenerCobranzasPorMes');
            
            $fechaInicio = now()->subMonths($cantidad - 1)->startOfMonth();

            // 1. UNA SOLA CONSULTA
            $cobranzasPorMes = CtaCliente::where('FechaF', '>=', $fechaInicio)
                ->where('Saldo', 0) // Que esté pagada
                ->select(
                    DB::raw('YEAR(FechaF) as anio'),
                    DB::raw('MONTH(FechaF) as mes'),
                    DB::raw('SUM(Importe) as total')
                )
                ->groupBy('anio', 'mes')
                ->orderBy('anio', 'asc')
                ->orderBy('mes', 'asc')
                ->get()
                ->keyBy(fn($item) => $item->anio . '-' . $item->mes);

            // 2. Construimos el array
            $datos = [];
            for ($i = $cantidad - 1; $i >= 0; $i--) {
                $fecha = now()->subMonths($i);
                $key = $fecha->year . '-' . $fecha->month;
                $datos[] = round($cobranzasPorMes->get($key)->total ?? 0, 2);
            }
            return $datos;
        });
    }

    public function obtenerTopClientesMes($limite = 10)
    {
        return Cache::tags($this->cache_tag)->remember('top_clientes_' . now()->format('Y-m'), $this->cache_ttl, function () use ($limite) {
            Log::debug('EJECUTANDO QUERY (CON MODELO): obtenerTopClientesMes');
            
            // REFACTOR: DB::table('Doccab') -> Modelo Doccab
            // Usamos 'with' para cargar la relación 'cliente'
            return Doccab::with('cliente:Codclie,Razon')
                ->whereYear('Fecha', now()->year)
                ->whereMonth('Fecha', now()->month)
                ->where('Eliminado', 0)
                ->select(
                    'CodClie',
                    DB::raw('COUNT(*) as total_facturas'),
                    DB::raw('SUM(Total) as total_ventas'),
                    DB::raw('AVG(Total) as ticket_promedio')
                )
                ->groupBy('CodClie') // Agrupamos solo por CodClie
                ->orderBy('total_ventas', 'desc')
                ->limit($limite)
                ->get()
                ->map(function($item) {
                    $clienteNombre = trim($item->cliente->Razon ?? '');
                    $initial = empty($clienteNombre) ? '?' : mb_substr($clienteNombre, 0, 1);
                    return [
                        'codigo' => $item->CodClie,
                        'cliente' => $clienteNombre ?: 'Cliente Anónimo',
                        'initial' => $initial,
                        'facturas' => $item->total_facturas,
                        'total' => round($item->total_ventas, 2),
                        'ticket_promedio' => round($item->ticket_promedio, 2),
                        'avatar_color' => $this->getAvatarColor($item->CodClie)
                    ];
                })->toArray();
        });
    }

    public function obtenerVentasRecientes($limite = 15)
    {
        return Cache::tags($this->cache_tag)->remember('ventas_recientes_' . $limite, $this->cache_ttl, function () use ($limite) {
            Log::debug('EJECUTANDO QUERY (CON MODELO): obtenerVentasRecientes');
            
            // REFACTOR: DB::table('Doccab') -> Modelo Doccab
            // ¡LÓGICA DEL FUTURO! Cargamos ambas relaciones
            return Doccab::with([
                    'cliente:Codclie,Razon', 
                    'cuentaPorCobrar' // ¡Esta relación la añadimos al Modelo Doccab.php!
                ])
                ->where('Eliminado', 0)
                ->orderBy('Fecha', 'desc')
                ->limit($limite)
                ->get()
                ->map(function($venta) {
                    $cuenta = $venta->cuentaPorCobrar; // Accedemos a la relación
                    $estado = 'SIN CTA';
                    $diasVencimiento = 0;
                    $saldo = 0;

                    if ($cuenta) {
                        $saldo = $cuenta->Saldo;
                        if ($saldo == 0) {
                            $estado = 'PAGADA';
                        } elseif ($cuenta->FechaV < now()) {
                            $estado = 'VENCIDA';
                            $diasVencimiento = now()->diffInDays(Carbon::parse($cuenta->FechaV));
                        } else {
                            $estado = 'PENDIENTE';
                        }
                    }

                    return [
                        'numero' => trim($venta->Numero),
                        'tipo' => $this->obtenerTipoDocumento($venta->Tipo),
                        'cliente' => $venta->cliente->Razon ?? 'Sin Cliente',
                        'fecha' => Carbon::parse($venta->Fecha)->format('d/m/Y'),
                        'total' => round($venta->Total, 2),
                        'saldo' => round($saldo, 2),
                        'estado' => $estado,
                        'estado_class' => $this->obtenerClaseEstado($estado),
                        'dias_vencimiento' => $diasVencimiento,
                        'urgencia' => $diasVencimiento > 30 ? 'alta' : ($diasVencimiento > 0 ? 'media' : 'baja')
                    ];
                })->toArray();
        });
    }

    // 🚀 SOLUCIÓN: "JOIN ROTO" ARREGLADO
    public function obtenerProductosStockBajo($limite = 10)
    {
        return Cache::tags($this->cache_tag)->remember('productos_stock_bajo_' . $limite, $this->cache_ttl, function () use ($limite) {
            Log::debug('EJECUTANDO QUERY (CON MODELO Y JOIN CORREGIDO): obtenerProductosStockBajo');
            
            // REFACTOR: DB::table('Productos') -> Modelo Producto
            // ¡Aquí está la magia!
            // 1. 'with('laboratorio')' -> Carga la relación (p.CodProv = l.CodLab) que definimos en el Modelo
            // 2. 'stockBajo()' -> Usa el scope que definimos
            $productos = Producto::with('laboratorio:CodLab,Descripcion')
                ->stockBajo() // El scope ya filtra Eliminado=0, Stock<=Minimo, Stock>0
                ->select(
                    'CodPro', 'Nombre', 'CodProv', 'Stock', 'Minimo', 'CosReal', 'Costo',
                    DB::raw('(Stock / NULLIF(Minimo, 0)) * 100 as porcentaje')
                )
                ->orderBy('porcentaje', 'asc')
                ->limit($limite)
                ->get();
                
            return $productos->map(function($item) {
                $porcentaje = $item->porcentaje ?? 0;
                $unidad_valor = $item->CosReal ?? $item->Costo ?? 0;
                return [
                    'codigo' => trim($item->CodPro), 'nombre' => $item->Nombre,
                    'laboratorio' => $item->laboratorio->Descripcion ?? 'Sin laboratorio',
                    'stock' => round($item->Stock, 2), 'minimo' => round($item->Minimo, 2),
                    'porcentaje' => round($porcentaje, 0),
                    'valor_stock' => round($item->Stock * $unidad_valor, 2),
                    'criticidad' => $porcentaje < 20 ? 'crítica' : ($porcentaje < 50 ? 'alta' : 'media')
                ];
            })->toArray();
        });
    }

    // 🚀 SOLUCIÓN: "JOIN ROTO" ARREGLADO (y usando VISTA)
    public function obtenerProductosProximosVencer($limite = 10)
    {
        return Cache::tags($this->cache_tag)->remember('productos_proximos_vencer_' . $limite, $this->cache_ttl, function () use ($limite) {
            Log::debug('EJECUTANDO QUERY (CON VISTA): obtenerProductosProximosVencer');
            
            // REFACTOR: Usamos la VISTA que ya optimizamos en SQL
            // [v_productos_por_vencer]
            // Nota: Debes crear el Modelo 'VistaProductosPorVencer'
            return VistaProductosPorVencer::orderBy('DiasParaVencer', 'asc')
                ->limit($limite)
                ->get()
                ->map(function($item) {
                    return [
                        'codigo' => trim($item->CodPro), 'nombre' => $item->Nombre,
                        'laboratorio' => $item->Laboratorio ?? 'Sin laboratorio', 'lote' => trim($item->Lote),
                        'vencimiento' => Carbon::parse($item->Vencimiento)->format('d/m/Y'),
                        'stock' => round($item->Stock, 2),
                        'valor_lote' => round($item->ValorInventario, 2),
                        'dias' => (int)$item->DiasParaVencer,
                        'riesgo' => $item->DiasParaVencer <= 30 ? 'alto' : ($item->DiasParaVencer <= 60 ? 'medio' : 'baja')
                    ];
                })->toArray();
        });
    }
    
    public function contarClientesActivos()
    {
        return (int) Cache::tags($this->cache_tag)->remember('clientes_activos', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): contarClientesActivos');
            // REFACTOR: DB::table('Clientes') -> Modelo Cliente
            return Cliente::where('Activo', 1)->count();
        });
    }

    public function contarFacturasPendientes()
    {
        return (int) Cache::tags($this->cache_tag)->remember('facturas_pendientes', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): contarFacturasPendientes');
            // REFACTOR: DB::table('CtaCliente') -> Modelo CtaCliente
            return CtaCliente::where('Saldo', '>', 0)->count();
        });
    }

    public function contarFacturasVencidas()
    {
        return (int) Cache::tags($this->cache_tag)->remember('facturas_vencidas', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): contarFacturasVencidas');
            // REFACTOR: DB::table('CtaCliente') -> Modelo CtaCliente
            return CtaCliente::where('Saldo', '>', 0)
                ->whereNotNull('FechaV')
                ->where('FechaV', '<', Carbon::today())
                ->count();
        });
    }

    public function calcularTicketPromedio()
    {
        return Cache::tags($this->cache_tag)->remember('ticket_promedio_' . now()->format('Y-m'), $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularTicketPromedio');
            // REFACTOR: DB::table('Doccab') -> Modelo Doccab
            $avg = Doccab::whereYear('Fecha', now()->year)
                ->whereMonth('Fecha', now()->month)
                ->where('Eliminado', 0)
                ->avg('Total');
            return round((float) ($avg ?? 0), 2);
        });
    }

    public function calcularDiasPromedioCobranza()
    {
        return Cache::tags($this->cache_tag)->remember('dias_promedio_cobranza', $this->cache_ttl, function () {
            Log::debug('EJECUTANDO QUERY (CON MODELO): calcularDiasPromedioCobranza');
            
            // REFACTOR: DB::table('CtaCliente') -> Modelo CtaCliente
            $row = CtaCliente::selectRaw('AVG(CAST(DATEDIFF(day, FechaF, FechaV) AS FLOAT)) as avg_days')
                ->where('Saldo', 0) // Solo facturas pagadas
                ->whereYear('FechaV', now()->year) // Del año actual
                ->first();
            return $row && $row->avg_days ? round($row->avg_days) : 0;
        });
    }

    // --- MÉTODOS HELPERS (Lógica Pura, no SQL) ---
    // (Estos no necesitan caché y están perfectos)

    public function obtenerMesesLabels($cantidad = 6)
    {
        $labels = [];
        for ($i = $cantidad - 1; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $labels[] = $dt->locale('es')->translatedFormat('M/Y');
        }
        return $labels;
    }
    
    public function obtenerTipoDocumento($tipo)
    {
        // 🚀 LÓGICA DE FUTURO: Esto debería venir de la tabla 'Tablas'
        // pero por ahora, está bien hardcodeado.
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
        $hash = is_numeric($codigo) ? $codigo : hexdec(substr(md5($codigo), 0, 6));
        return $colors[$hash % count($colors)];
    }

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

    public function getDatosVacios()
    {
        // Esta función de fallback es excelente.
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