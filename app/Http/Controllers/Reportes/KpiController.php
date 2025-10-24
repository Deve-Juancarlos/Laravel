<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KpiController extends Controller
{
    /**
     * MÓDULO REPORTES - Controlador de KPIs
     * KPIs y métricas clave para monitoreo empresarial
     * Integrado con base de datos SIFANO existente
     * Total de líneas: ~950
     */

    public function __construct()
    {
        $this->middleware(['auth', 'rol:administrador|vendedor|contador|gerente']);
    }

    /**
     * ===============================================
     * MÉTODOS PRINCIPALES DE KPIs
     * ===============================================
     */

    /**
     * Dashboard principal de KPIs
     */
    public function index(Request $request)
    {
        $periodo = $request->periodo ?? '1m'; // Por defecto 1 mes
        $vista = $request->vista ?? 'general'; // general, ventas, financiero, operativo
        
        $kpis_principales = $this->calcularKPIsPrincipales($periodo);
        $comparacion_periodo = $this->compararConPeriodoAnterior($periodo);
        $alertas_kpis = $this->generarAlertasKPIs($kpis_principales);
        $objetivos = $this->evaluarCumplimientoObjetivos($kpis_principales);

        return compact('kpis_principales', 'comparacion_periodo', 'alertas_kpis', 'objetivos', 'periodo', 'vista');
    }

    /**
     * Obtiene KPIs específicos por categoría
     */
    public function kpisPorCategoria(Request $request)
    {
        $categoria = $request->categoria ?? 'ventas';
        $periodo = $request->periodo ?? '1m';

        return match($categoria) {
            'ventas' => $this->kpisVentas($periodo),
            'financiero' => $this->kpisFinancieros($periodo),
            'operativo' => $this->kpisOperativos($periodo),
            'calidad' => $this->kpisCalidad($periodo),
            'recursos_humanos' => $this->kpisRRHH($periodo),
            default => $this->calcularKPIsPrincipales($periodo)
        };
    }

    /**
     * ===============================================
     * KPIs DE VENTAS Y COMERCIALES
     * ===============================================
     */

    /**
     * KPIs específicos de ventas
     */
    public function kpisVentas($periodo = '1m')
    {
        $fecha_desde = $this->calcularFechaDesde($periodo);
        $fecha_hasta = now();

        // KPIs principales de ventas
        $ventas_totales = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        $numero_transacciones = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->count();

        $clientes_unicos = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->distinct()
            ->count('Codcli');

        $ticket_promedio = $numero_transacciones > 0 ? $ventas_totales / $numero_transacciones : 0;

        // KPIs de crecimiento
        $crecimiento_ventas = $this->calcularCrecimientoVentas($periodo);
        $crecimiento_clientes = $this->calcularCrecimientoClientes($periodo);

        // KPIs de productividad
        $ventas_por_cliente = $clientes_unicos > 0 ? $ventas_totales / $clientes_unicos : 0;
        $frecuencia_compra = $numero_transacciones > 0 && $clientes_unicos > 0 ? $numero_transacciones / $clientes_unicos : 0;

        // KPIs de vendedores
        $kpis_vendedores = $this->calcularKPIsVendedores($fecha_desde, $fecha_hasta);

        // KPIs de productos
        $kpis_productos = $this->calcularKPIsProductos($fecha_desde, $fecha_hasta);

        return [
            'categoria' => 'ventas',
            'periodo' => $periodo,
            'principales' => [
                'ventas_totales' => round($ventas_totales, 2),
                'numero_transacciones' => $numero_transacciones,
                'clientes_unicos' => $clientes_unicos,
                'ticket_promedio' => round($ticket_promedio, 2),
                'ventas_por_cliente' => round($ventas_por_cliente, 2),
                'frecuencia_compra' => round($frecuencia_compra, 2)
            ],
            'crecimiento' => [
                'ventas' => round($crecimiento_ventas, 2),
                'clientes' => round($crecimiento_clientes, 2)
            ],
            'vendedores' => $kpis_vendedores,
            'productos' => $kpis_productos,
            'objetivos_mes' => [
                'ventas_objetivo' => 100000, // Simulado
                'progreso_ventas' => round(($ventas_totales / 100000) * 100, 1),
                'clientes_objetivo' => 150,
                'progreso_clientes' => round(($clientes_unicos / 150) * 100, 1)
            ]
        ];
    }

    /**
     * KPIs de vendedores
     */
    public function calcularKPIsVendedores($fecha_desde, $fecha_hasta)
    {
        // Esta consulta asume una relación entre clientes y vendedores
        $vendedores = DB::table('empleados')
            ->where('Cargo', 'like', '%vendedor%')
            ->where('Estado', 'ACTIVO')
            ->get();

        $kpis_vendedores = [];

        foreach ($vendedores as $vendedor) {
            // Simular datos de ventas por vendedor
            $ventas_vendedor = rand(5000, 25000); // Simulado
            $clientes_vendedor = rand(5, 30); // Simulado
            
            $kpis_vendedores[] = [
                'vendedor_id' => $vendedor->Codemp,
                'nombre' => $vendedor->Nombres . ' ' . $vendedor->Apellidos,
                'ventas_totales' => $ventas_vendedor,
                'clientes_atendidos' => $clientes_vendedor,
                'ticket_promedio' => $clientes_vendedor > 0 ? round($ventas_vendedor / $clientes_vendedor, 2) : 0,
                'eficiencia' => $ventas_vendedor > 0 ? round(($ventas_vendedor / 25000) * 100, 1) : 0
            ];
        }

        return $kpis_vendedores;
    }

    /**
     * KPIs de productos
     */
    public function calcularKPIsProductos($fecha_desde, $fecha_hasta)
    {
        $productos_top = DB::table('Docdet')
            ->join('Doccab', 'Docdet.Numero', '=', 'Doccab.Numero')
            ->join('Productos', 'Docdet.Codpro', '=', 'Productos.Codpro')
            ->select(
                'Productos.Codpro',
                'Productos.Descripcion',
                DB::raw('SUM(Docdet.Cantidad * Docdet.Precio) as valor_ventas'),
                DB::raw('SUM(Docdet.Cantidad) as cantidad_vendida')
            )
            ->whereBetween('Doccab.Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Doccab.Estado', ['ANULADO'])
            ->groupBy('Productos.Codpro', 'Productos.Descripcion')
            ->orderBy('valor_ventas', 'desc')
            ->limit(10)
            ->get();

        return [
            'top_productos' => $productos_top->map(function($producto) {
                return [
                    'codigo' => $producto->Codpro,
                    'descripcion' => $producto->Descripcion,
                    'valor_ventas' => $producto->valor_ventas,
                    'cantidad_vendida' => $producto->cantidad_vendida
                ];
            }),
            'total_productos_vendidos' => $productos_top->count(),
            'valor_total_top10' => $productos_top->sum('valor_ventas')
        ];
    }

    /**
     * ===============================================
     * KPIs FINANCIEROS
     * ===============================================
     */

    /**
     * KPIs específicos financieros
     */
    public function kpisFinancieros($periodo = '1m')
    {
        $fecha_desde = $this->calcularFechaDesde($periodo);
        $fecha_hasta = now();

        // Ingresos
        $ingresos_totales = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        $ingresos_promedio_diario = $this->calcularPromedioDiario($ingresos_totales, $fecha_desde, $fecha_hasta);

        // Cuentas por cobrar
        $cuentas_cobrar = DB::table('Doccab')
            ->whereIn('Estado', ['PENDIENTE', 'VENCIDO'])
            ->sum('Saldo');

        $antiguedad_cuentas = $this->calcularAntiguedadCuentas();
        $dias_cxc_promedio = $antiguedad_cuentas['promedio_dias'];

        // Liquidez y solvencia
        $disponibilidad = $this->calcularDisponibilidad();
        $rotacion_inventario = $this->calcularRotacionInventario($fecha_desde, $fecha_hasta);

        // Rentabilidad
        $margen_bruto = $this->calcularMargenBruto($fecha_desde, $fecha_hasta);
        $roi = $this->calcularROI($fecha_desde, $fecha_hasta);

        return [
            'categoria' => 'financiero',
            'periodo' => $periodo,
            'ingresos' => [
                'total' => round($ingresos_totales, 2),
                'promedio_diario' => round($ingresos_promedio_diario, 2),
                'crecimiento_vs_anterior' => $this->calcularCrecimientoFinanciero($periodo)
            ],
            'cuentas_cobrar' => [
                'total_pendiente' => round($cuentas_cobrar, 2),
                'dias_promedio' => round($dias_cxc_promedio, 1),
                'distribucion_antiguedad' => $antiguedad_cuentas
            ],
            'liquidez' => [
                'disponibilidad' => $disponibilidad,
                'rotacion_inventario' => round($rotacion_inventario, 2)
            ],
            'rentabilidad' => [
                'margen_bruto' => round($margen_bruto, 2),
                'roi' => round($roi, 2)
            ],
            'objetivos' => [
                'cobranza_objetivo' => 15,
                'margen_objetivo' => 35,
                'roi_objetivo' => 20
            ]
        ];
    }

    /**
     * ===============================================
     * KPIs OPERATIVOS
     * ===============================================
     */

    /**
     * KPIs específicos operativos
     */
    public function kpisOperativos($periodo = '1m')
    {
        $fecha_desde = $this->calcularFechaDesde($periodo);
        $fecha_hasta = now();

        // Eficiencia operativa
        $ventas_por_hora = $this->calcularVentasPorHora($fecha_desde, $fecha_hasta);
        $tiempo_promedio_atencion = $this->calcularTiempoAtencion($fecha_desde, $fecha_hasta);

        // Productividad
        $ventas_por_empleado = $this->calcularVentasPorEmpleado($fecha_desde, $fecha_hasta);
        $rotacion_personal = $this->calcularRotacionPersonal($periodo);

        // Calidad del servicio
        $tasa_devoluciones = $this->calcularTasaDevoluciones($fecha_desde, $fecha_hasta);
        $satisfaccion_cliente = $this->calcularSatisfaccionCliente();

        // Inventario
        $rotacion_inventario_detallada = $this->analizarRotacionInventarioDetallada($fecha_desde, $fecha_hasta);
        $nivel_stock = $this->analizarNivelStock();

        return [
            'categoria' => 'operativo',
            'periodo' => $periodo,
            'eficiencia' => [
                'ventas_por_hora' => round($ventas_por_hora, 2),
                'tiempo_atencion_promedio' => round($tiempo_promedio_atencion, 1)
            ],
            'productividad' => [
                'ventas_por_empleado' => round($ventas_por_empleado, 2),
                'rotacion_personal' => round($rotacion_personal, 2)
            ],
            'calidad' => [
                'tasa_devoluciones' => round($tasa_devoluciones, 2),
                'satisfaccion_cliente' => round($satisfaccion_cliente, 1)
            ],
            'inventario' => [
                'rotacion_detallada' => $rotacion_inventario_detallada,
                'nivel_stock' => $nivel_stock
            ]
        ];
    }

    /**
     * ===============================================
     * KPIs DE CALIDAD
     * ===============================================
     */

    /**
     * KPIs específicos de calidad
     */
    public function kpisCalidad($periodo = '1m')
    {
        $fecha_desde = $this->calcularFechaDesde($periodo);
        $fecha_hasta = now();

        // Calidad de productos
        $productos_con_problemas = $this->analizarProductosConProblemas($fecha_desde, $fecha_hasta);
        $vencimientos_cercanos = $this->analizarVencimientosCercanos();

        // Calidad de servicio
        $quejas_clientes = $this->analizarQuejasClientes($fecha_desde, $fecha_hasta);
        $tiempo_resolucion = $this->calcularTiempoResolucionQuejas($fecha_desde, $fecha_hasta);

        // Cumplimiento
        $cumplimiento_entregas = $this->calcularCumplimientoEntregas($fecha_desde, $fecha_hasta);
        $precision_inventario = $this->calcularPrecisionInventario();

        // Eficiencia de procesos
        $tiempo_promedio_proceso = $this->calcularTiempoPromedioProceso($fecha_desde, $fecha_hasta);
        $errores_proceso = $this->calcularErroresProceso($fecha_desde, $fecha_hasta);

        return [
            'categoria' => 'calidad',
            'periodo' => $periodo,
            'productos' => [
                'productos_problema' => $productos_con_problemas,
                'vencimientos_cercanos' => $vencimientos_cercanos
            ],
            'servicio' => [
                'quejas_totales' => $quejas_clientes['total'],
                'tiempo_resolucion_promedio' => round($tiempo_resolucion, 1),
                'tasa_resolucion' => round($quejas_clientes['tasa_resolucion'], 1)
            ],
            'cumplimiento' => [
                'entregas_a_tiempo' => round($cumplimiento_entregas, 1),
                'precision_inventario' => round($precision_inventario, 1)
            ],
            'procesos' => [
                'tiempo_promedio' => round($tiempo_promedio_proceso, 1),
                'tasa_errores' => round($errores_proceso, 2)
            ]
        ];
    }

    /**
     * ===============================================
     * KPIs DE RECURSOS HUMANOS
     * ===============================================
     */

    /**
     * KPIs específicos de RRHH
     */
    public function kpisRRHH($periodo = '1m')
    {
        $fecha_desde = $this->calcularFechaDesde($periodo);
        $fecha_hasta = now();

        // Plantilla
        $total_empleados = DB::table('empleados')
            ->where('Estado', 'ACTIVO')
            ->count();

        $nuevos_ingresos = DB::table('empleados')
            ->whereBetween('created_at', [$fecha_desde, $fecha_hasta])
            ->count();

        $rotacion_empleados = $this->calcularRotacionEmpleados($fecha_desde, $fecha_hasta);

        // Productividad
        $ventas_por_empleado = $this->calcularVentasPorEmpleado($fecha_desde, $fecha_hasta);
        $ausentismo = $this->calcularAusentismo($fecha_desde, $fecha_hasta);

        // Capacitación
        $horas_capacitacion = $this->calcularHorasCapacitacion($fecha_desde, $fecha_hasta);
        $inversion_capacitacion = $this->calcularInversionCapacitacion($fecha_desde, $fecha_hasta);

        // Clima laboral
        $encuestas_satisfaccion = $this->calcularSatisfaccionEmpleados();

        return [
            'categoria' => 'recursos_humanos',
            'periodo' => $periodo,
            'plantilla' => [
                'total_empleados' => $total_empleados,
                'nuevos_ingresos' => $nuevos_ingresos,
                'rotacion_porcentaje' => round($rotacion_empleados, 2)
            ],
            'productividad' => [
                'ventas_por_empleado' => round($ventas_por_empleado, 2),
                'ausentismo_porcentaje' => round($ausentismo, 2)
            ],
            'capacitacion' => [
                'horas_totales' => $horas_capacitacion,
                'inversion_total' => $inversion_capacitacion
            ],
            'clima' => [
                'satisfaccion_empleados' => round($encuestas_satisfaccion, 1)
            ]
        ];
    }

    /**
     * ===============================================
     * MÉTODOS DE CÁLCULO Y COMPARACIÓN
     * ===============================================
     */

    /**
     * Calcula KPIs principales consolidados
     */
    public function calcularKPIsPrincipales($periodo = '1m')
    {
        $fecha_desde = $this->calcularFechaDesde($periodo);
        $fecha_hasta = now();

        return [
            'ventas' => $this->kpisVentas($periodo),
            'financiero' => $this->kpisFinancieros($periodo),
            'operativo' => $this->kpisOperativos($periodo),
            'calidad' => $this->kpisCalidad($periodo),
            'recursos_humanos' => $this->kpisRRHH($periodo)
        ];
    }

    /**
     * Compara KPIs con período anterior
     */
    public function compararConPeriodoAnterior($periodo)
    {
        $periodo_actual = $this->calcularFechaDesde($periodo);
        $periodo_anterior = $this->calcularPeriodoAnterior($periodo);

        // Comparación de ventas
        $ventas_actuales = DB::table('Doccab')
            ->whereBetween('Fecha', [$periodo_actual, now()])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        $ventas_anteriores = DB::table('Doccab')
            ->whereBetween('Fecha', [$periodo_anterior['inicio'], $periodo_anterior['fin']])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        // Comparación de clientes
        $clientes_actuales = DB::table('Doccab')
            ->whereBetween('Fecha', [$periodo_actual, now()])
            ->whereNotIn('Estado', ['ANULADO'])
            ->distinct()
            ->count('Codcli');

        $clientes_anteriores = DB::table('Doccab')
            ->whereBetween('Fecha', [$periodo_anterior['inicio'], $periodo_anterior['fin']])
            ->whereNotIn('Estado', ['ANULADO'])
            ->distinct()
            ->count('Codcli');

        return [
            'ventas' => [
                'actual' => $ventas_actuales,
                'anterior' => $ventas_anteriores,
                'cambio_absoluto' => $ventas_actuales - $ventas_anteriores,
                'cambio_porcentual' => $ventas_anteriores > 0 ? (($ventas_actuales - $ventas_anteriores) / $ventas_anteriores) * 100 : 0
            ],
            'clientes' => [
                'actual' => $clientes_actuales,
                'anterior' => $clientes_anteriores,
                'cambio_absoluto' => $clientes_actuales - $clientes_anteriores,
                'cambio_porcentual' => $clientes_anteriores > 0 ? (($clientes_actuales - $clientes_anteriores) / $clientes_anteriores) * 100 : 0
            ]
        ];
    }

    /**
     * Evalúa cumplimiento de objetivos
     */
    public function evaluarCumplimientoObjetivos($kpis)
    {
        $objetivos = [
            'ventas_mensuales' => 100000,
            'clientes_mensuales' => 150,
            'ticket_promedio' => 200,
            'dias_cobranza' => 15,
            'margen_bruto' => 35,
            'satisfaccion_cliente' => 4.5,
            'rotacion_inventario' => 12
        ];

        $cumplimiento = [];

        // Ventas
        $ventas_actuales = $kpis['ventas']['principales']['ventas_totales'];
        $cumplimiento['ventas'] = round(($ventas_actuales / $objetivos['ventas_mensuales']) * 100, 1);

        // Clientes
        $clientes_actuales = $kpis['ventas']['principales']['clientes_unicos'];
        $cumplimiento['clientes'] = round(($clientes_actuales / $objetivos['clientes_mensuales']) * 100, 1);

        // Ticket promedio
        $ticket_actual = $kpis['ventas']['principales']['ticket_promedio'];
        $cumplimiento['ticket_promedio'] = round(($ticket_actual / $objetivos['ticket_promedio']) * 100, 1);

        // Días de cobranza
        $dias_cobranza = $kpis['financiero']['cuentas_cobrar']['dias_promedio'];
        $cumplimiento['dias_cobranza'] = round((1 - (($dias_cobranza - $objetivos['dias_cobranza']) / $objetivos['dias_cobranza'])) * 100, 1);

        // Margen bruto
        $margen_actual = $kpis['financiero']['rentabilidad']['margen_bruto'];
        $cumplimiento['margen_bruto'] = round(($margen_actual / $objetivos['margen_bruto']) * 100, 1);

        // Satisfacción cliente
        $satisfaccion = $kpis['operativo']['calidad']['satisfaccion_cliente'];
        $cumplimiento['satisfaccion'] = round(($satisfaccion / $objetivos['satisfaccion_cliente']) * 100, 1);

        // Rotación inventario
        $rotacion = $kpis['financiero']['liquidez']['rotacion_inventario'];
        $cumplimiento['rotacion_inventario'] = round(($rotacion / $objetivos['rotacion_inventario']) * 100, 1);

        return [
            'objetivos' => $objetivos,
            'cumplimiento' => $cumplimiento,
            'resumen' => [
                'objetivos_cumplidos' => collect($cumplimiento)->where('>=', 100)->count(),
                'objetivos_total' => count($cumplimiento),
                'cumplimiento_promedio' => round(collect($cumplimiento)->avg(), 1)
            ]
        ];
    }

   

    private function calcularFechaDesde($periodo)
    {
        return (
            match($periodo) {
                '1w' => now()->subWeek(),
                '1m' => now()->subMonth(),
                '3m' => now()->subMonths(3),
                '6m' => now()->subMonths(6),
                '12m' => now()->subYear(),
                default => now()->subMonth()
            }
        )->format('Y-m-d');
    }

    private function calcularPeriodoAnterior($periodo)
    {
        $duracion = match($periodo) {
            '1w' => '1 week',
            '1m' => '1 month',
            '3m' => '3 months',
            '6m' => '6 months',
            '12m' => '1 year',
            default => '1 month'
        };

        $fin = now()->sub($duracion);
        $inicio = match($periodo) {
            '1w' => $fin->copy()->subWeek(),
            '1m' => $fin->copy()->subMonth(),
            '3m' => $fin->copy()->subMonths(3),
            '6m' => $fin->copy()->subMonths(6),
            '12m' => $fin->copy()->subYear(),
            default => $fin->copy()->subMonth()
        };

        return [
            'inicio' => $inicio->format('Y-m-d'),
            'fin' => $fin->format('Y-m-d')
        ];
    }

    private function calcularCrecimientoVentas($periodo)
    {
        $periodo_actual = $this->calcularFechaDesde($periodo);
        $periodo_anterior = $this->calcularPeriodoAnterior($periodo);

        $ventas_actuales = DB::table('Doccab')
            ->whereBetween('Fecha', [$periodo_actual, now()])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        $ventas_anteriores = DB::table('Doccab')
            ->whereBetween('Fecha', [$periodo_anterior['inicio'], $periodo_anterior['fin']])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        return $ventas_anteriores > 0 ? (($ventas_actuales - $ventas_anteriores) / $ventas_anteriores) * 100 : 0;
    }

    private function calcularCrecimientoClientes($periodo)
    {
        $periodo_actual = $this->calcularFechaDesde($periodo);
        $periodo_anterior = $this->calcularPeriodoAnterior($periodo);

        $clientes_actuales = DB::table('Doccab')
            ->whereBetween('Fecha', [$periodo_actual, now()])
            ->whereNotIn('Estado', ['ANULADO'])
            ->distinct()
            ->count('Codcli');

        $clientes_anteriores = DB::table('Doccab')
            ->whereBetween('Fecha', [$periodo_anterior['inicio'], $periodo_anterior['fin']])
            ->whereNotIn('Estado', ['ANULADO'])
            ->distinct()
            ->count('Codcli');

        return $clientes_anteriores > 0 ? (($clientes_actuales - $clientes_anteriores) / $clientes_anteriores) * 100 : 0;
    }

    private function calcularPromedioDiario($total, $fecha_desde, $fecha_hasta)
    {
        $dias = Carbon::parse($fecha_desde)->diffInDays(Carbon::parse($fecha_hasta)) + 1;
        return $dias > 0 ? $total / $dias : 0;
    }

    private function calcularAntiguedadCuentas()
    {
        $facturas = DB::table('Doccab')
            ->whereIn('Estado', ['PENDIENTE', 'VENCIDO'])
            ->select('Saldo', 'FechaVencimiento')
            ->get();

        $total_saldo = $facturas->sum('Saldo');
        $dias_totales = 0;
        $facturas_con_saldo = 0;

        foreach ($facturas as $factura) {
            if ($factura->Saldo > 0) {
                $dias_vencimiento = now()->diffInDays($factura->FechaVencimiento);
                $dias_totales += max(0, $dias_vencimiento);
                $facturas_con_saldo++;
            }
        }

        return [
            'promedio_dias' => $facturas_con_saldo > 0 ? $dias_totales / $facturas_con_saldo : 0,
            'total_saldo' => $total_saldo,
            'facturas_vencidas' => $facturas->where('FechaVencimiento', '<', now())->count()
        ];
    }

    private function calcularDisponibilidad()
    {
        // Simulado - en producción vendría de tesorería
        return rand(50000, 150000);
    }

    private function calcularRotacionInventario($fecha_desde, $fecha_hasta)
    {
        // Costo de ventas simulado
        $costo_ventas = DB::table('Docdet')
            ->join('Doccab', 'Docdet.Numero', '=', 'Doccab.Numero')
            ->whereBetween('Doccab.Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Doccab.Estado', ['ANULADO'])
            ->sum(DB::raw('Docdet.Cantidad * Docdet.Costo'));

        // Inventario promedio simulado
        $inventario_promedio = rand(50000, 100000);

        return $inventario_promedio > 0 ? $costo_ventas / $inventario_promedio : 0;
    }

    private function calcularMargenBruto($fecha_desde, $fecha_hasta)
    {
        $ventas = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        $costo_ventas = DB::table('Docdet')
            ->join('Doccab', 'Docdet.Numero', '=', 'Doccab.Numero')
            ->whereBetween('Doccab.Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Doccab.Estado', ['ANULADO'])
            ->sum(DB::raw('Docdet.Cantidad * Docdet.Costo'));

        return $ventas > 0 ? (($ventas - $costo_ventas) / $ventas) * 100 : 0;
    }

    private function calcularROI($fecha_desde, $fecha_hasta)
    {
        // ROI simplificado
        $utilidad = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total') * 0.2; // Asumiendo 20% de margen

        $inversion = 50000; // Simulado

        return $inversion > 0 ? ($utilidad / $inversion) * 100 : 0;
    }

    private function calcularCrecimientoFinanciero($periodo)
    {
        // Implementar comparación de crecimiento financiero
        return rand(-10, 15); // Simulado
    }

    private function calcularVentasPorHora($fecha_desde, $fecha_hasta)
    {
        // Asumiendo horario de 12 horas (8am - 8pm)
        $ventas_totales = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        $dias_operativos = Carbon::parse($fecha_desde)->diffInDays(Carbon::parse($fecha_hasta)) + 1;
        $horas_diarias = 12;

        return $dias_operativos > 0 ? ($ventas_totales / ($dias_operativos * $horas_diarias)) : 0;
    }

    private function calcularTiempoAtencion($fecha_desde, $fecha_hasta)
    {
        // Simulado - en producción vendría de logs de atención
        return rand(5, 15); // minutos
    }

    private function calcularVentasPorEmpleado($fecha_desde, $fecha_hasta)
    {
        $ventas_totales = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        $empleados_activos = DB::table('empleados')
            ->where('Estado', 'ACTIVO')
            ->count();

        return $empleados_activos > 0 ? $ventas_totales / $empleados_activos : 0;
    }

    private function calcularRotacionPersonal($periodo)
    {
        // Empleados que salieron en el período
        $salidas = DB::table('empleados')
            ->where('Estado', 'INACTIVO')
            ->whereBetween('updated_at', [$this->calcularFechaDesde($periodo), now()])
            ->count();

        $total_empleados = DB::table('empleados')
            ->where('Estado', 'ACTIVO')
            ->count();

        return $total_empleados > 0 ? ($salidas / $total_empleados) * 100 : 0;
    }

    private function calcularTasaDevoluciones($fecha_desde, $fecha_hasta)
    {
        // Simulado
        $ventas_totales = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->count();

        $devoluciones = rand(1, 10); // Simulado

        return $ventas_totales > 0 ? ($devoluciones / $ventas_totales) * 100 : 0;
    }

    private function calcularSatisfaccionCliente()
    {
        // Simulado - vendría de encuestas
        return rand(3.5, 5.0);
    }

    private function analizarRotacionInventarioDetallada($fecha_desde, $fecha_hasta)
    {
        return [
            'alta_rotacion' => rand(30, 50),
            'media_rotacion' => rand(20, 35),
            'baja_rotacion' => rand(10, 25)
        ];
    }

    private function analizarNivelStock()
    {
        return [
            'sobre_stock' => rand(15, 25),
            'stock_optimo' => rand(60, 75),
            'bajo_stock' => rand(5, 15)
        ];
    }

    private function analizarProductosConProblemas($fecha_desde, $fecha_hasta)
    {
        return rand(5, 20); // Cantidad de productos con problemas
    }

    private function analizarVencimientosCercanos()
    {
        return rand(10, 30); // Productos próximos a vencer
    }

    private function analizarQuejasClientes($fecha_desde, $fecha_hasta)
    {
        $total = rand(5, 25);
        $resueltas = rand($total - 5, $total);
        
        return [
            'total' => $total,
            'resueltas' => $resueltas,
            'tasa_resolucion' => ($resueltas / $total) * 100
        ];
    }

    private function calcularTiempoResolucionQuejas($fecha_desde, $fecha_hasta)
    {
        return rand(1, 7); // días
    }

    private function calcularCumplimientoEntregas($fecha_desde, $fecha_hasta)
    {
        return rand(85, 98); // porcentaje
    }

    private function calcularPrecisionInventario()
    {
        return rand(92, 99); // porcentaje
    }

    private function calcularTiempoPromedioProceso($fecha_desde, $fecha_hasta)
    {
        return rand(30, 120); // minutos
    }

    private function calcularErroresProceso($fecha_desde, $fecha_hasta)
    {
        return rand(0.5, 3.0); // porcentaje
    }

    private function calcularRotacionEmpleados($fecha_desde, $fecha_hasta)
    {
        $salidas = DB::table('empleados')
            ->where('Estado', 'INACTIVO')
            ->whereBetween('updated_at', [$fecha_desde, $fecha_hasta])
            ->count();

        $total_empleados = DB::table('empleados')->count();

        return $total_empleados > 0 ? ($salidas / $total_empleados) * 100 : 0;
    }

    private function calcularAusentismo($fecha_desde, $fecha_hasta)
    {
        return rand(2, 8); // porcentaje
    }

    private function calcularHorasCapacitacion($fecha_desde, $fecha_hasta)
    {
        return rand(20, 100); // horas totales
    }

    private function calcularInversionCapacitacion($fecha_desde, $fecha_hasta)
    {
        return rand(2000, 8000); // soles
    }

    private function calcularSatisfaccionEmpleados()
    {
        return rand(3.0, 4.5); // escala 1-5
    }

    /**
     * ===============================================
     * MÉTODOS DE ALERTAS
     * ===============================================
     */

    private function generarAlertasKPIs($kpis)
    {
        $alertas = [];

        // Alerta por ventas bajo objetivo
        $progreso_ventas = $kpis['ventas']['objetivos_mes']['progreso_ventas'];
        if ($progreso_ventas < 70) {
            $alertas[] = [
                'tipo' => 'VENTAS_BAJO_OBJETIVO',
                'mensaje' => "Ventas al {$progreso_ventas}% del objetivo mensual",
                'prioridad' => 'ALTA',
                'valor' => $progreso_ventas
            ];
        }

        // Alerta por días de cobranza altos
        $dias_cobranza = $kpis['financiero']['cuentas_cobrar']['dias_promedio'];
        if ($dias_cobranza > 20) {
            $alertas[] = [
                'tipo' => 'DIAS_COBRANZA_ALTOS',
                'mensaje' => "Promedio de cobranza: {$dias_cobranza} días",
                'prioridad' => 'MEDIA',
                'valor' => $dias_cobranza
            ];
        }

        // Alerta por satisfacción baja
        $satisfaccion = $kpis['operativo']['calidad']['satisfaccion_cliente'];
        if ($satisfaccion < 4.0) {
            $alertas[] = [
                'tipo' => 'SATISFACCION_BAJA',
                'mensaje' => "Satisfacción de clientes por debajo de 4.0",
                'prioridad' => 'MEDIA',
                'valor' => $satisfaccion
            ];
        }

        return $alertas;
    }
}