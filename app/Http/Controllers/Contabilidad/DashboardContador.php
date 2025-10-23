<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardContador extends Controller
{
    /**
     * DASHBOARD PRINCIPAL DEL CONTADOR
     * Vista principal que ve el contador al entrar al sistema
     */
    public function index()
    {
        try {
            // Datos principales del dashboard
            $datos = [
                'resumen_general' => $this->obtenerResumenGeneral(),
                'cuentas_principales' => $this->obtenerCuentasPrincipales(),
                'alertas_contables' => $this->obtenerAlertasContables(),
                'cartera_vencida' => $this->obtenerCarteraVencida(),
                'productos_farmaceuticos' => $this->obtenerAlertasFarmaceuticas(),
                'reportes_tributarios' => $this->obtenerEstadoTributario(),
                'ultimos_movimientos' => $this->obtenerUltimosMovimientos(),
                'actividad_reciente' => $this->obtenerActividadReciente()
            ];

            return view('contabilidad.dashboard-contador', $datos);
            
        } catch (\Exception $e) {
            Log::error('Error en dashboard contador: ' . $e->getMessage());
            
            return view('contabilidad.dashboard-contador')
                ->with('error', 'Error al cargar el dashboard. Verificando conexión con base de datos...');
        }
    }

    /**
     * LIBRO MAYOR - VER UNA CUENTA ESPECÍFICA
     * El contador selecciona una cuenta y ve todos sus movimientos
     */
    public function libroMayor(Request $request)
    {
        try {
            $request->validate([
                'cuenta' => 'required|string',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
            ]);

            $cuenta = $request->cuenta;
            $fechaInicio = $request->fecha_inicio;
            $fechaFin = $request->fecha_fin;

            // Obtener saldo inicial de la cuenta
            $saldoInicial = DB::connection('sqlsrv')
                ->select("
                    SELECT ISNULL(SUM(CASE 
                        WHEN tipo_cuenta IN ('12', '11', '10', '15', '16', '17') THEN debe - haber 
                        ELSE haber - debe 
                    END), 0) as saldo_inicial
                    FROM dbo.movimientos_contables 
                    WHERE cuenta = ? AND fecha < ?
                ", [$cuenta, $fechaInicio]);

            // Obtener movimientos del período
            $movimientos = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        m.fecha,
                        m.comprobante,
                        m.glosa,
                        c.descripcion as concepto,
                        m.debe,
                        m.haber,
                        m.usuario,
                        m.created_at
                    FROM dbo.movimientos_contables m
                    LEFT JOIN dbo.conceptos_contables c ON m.concepto = c.codigo
                    WHERE m.cuenta = ? 
                    AND m.fecha BETWEEN ? AND ?
                    ORDER BY m.fecha, m.id
                ", [$cuenta, $fechaInicio, $fechaFin]);

            // Calcular saldos acumulados
            $saldoAcumulado = 0;
            foreach ($movimientos as &$mov) {
                if (in_array($cuenta, ['12', '11', '10', '15', '16', '17'])) {
                    $saldoAcumulado += $mov->debe - $mov->haber;
                } else {
                    $saldoAcumulado += $mov->haber - $mov->debe;
                }
                $mov->saldo_acumulado = $saldoAcumulado;
            }

            $datos = [
                'cuenta' => $cuenta,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'saldo_inicial' => $saldoInicial[0]->saldo_inicial ?? 0,
                'movimientos' => $movimientos,
                'saldo_final' => $saldoAcumulado + ($saldoInicial[0]->saldo_inicial ?? 0)
            ];

            return view('contabilidad.libro-mayor', $datos);
            
        } catch (\Exception $e) {
            Log::error('Error en libro mayor: ' . $e->getMessage());
            return back()->with('error', 'Error al obtener el libro mayor: ' . $e->getMessage());
        }
    }

    /**
     * BALANCE GENERAL - ESTADO FINANCIERO
     * Balance general tradicional de contabilidad
     */
    public function balanceGeneral(Request $request)
    {
        try {
            $fecha = $request->get('fecha', now()->toDateString());
            
            // Cuentas del Activo (1)
            $activo = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        LEFT(cuenta, 2) as grupo,
                        cuenta,
                        ISNULL(SUM(CASE WHEN naturaleza = 'A' THEN saldo ELSE -saldo END), 0) as saldo
                    FROM dbo.cuentas_contables c
                    LEFT JOIN dbo.saldos_contables s ON c.codigo = s.cuenta AND s.fecha = ?
                    WHERE cuenta LIKE '1%'
                    AND activo = 1
                    GROUP BY LEFT(cuenta, 2), cuenta
                    ORDER BY cuenta
                ", [$fecha]);

            // Cuentas del Pasivo (2)
            $pasivo = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        LEFT(cuenta, 2) as grupo,
                        cuenta,
                        ISNULL(SUM(CASE WHEN naturaleza = 'P' THEN saldo ELSE -saldo END), 0) as saldo
                    FROM dbo.cuentas_contables c
                    LEFT JOIN dbo.saldos_contables s ON c.codigo = s.cuenta AND s.fecha = ?
                    WHERE cuenta LIKE '2%'
                    AND activo = 1
                    GROUP BY LEFT(cuenta, 2), cuenta
                    ORDER BY cuenta
                ", [$fecha]);

            // Cuentas del Patrimonio (3)
            $patrimonio = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        LEFT(cuenta, 2) as grupo,
                        cuenta,
                        ISNULL(SUM(CASE WHEN naturaleza = 'P' THEN saldo ELSE -saldo END), 0) as saldo
                    FROM dbo.cuentas_contables c
                    LEFT JOIN dbo.saldos_contables s ON c.codigo = s.cuenta AND s.fecha = ?
                    WHERE cuenta LIKE '3%'
                    AND activo = 1
                    GROUP BY LEFT(cuenta, 2), cuenta
                    ORDER BY cuenta
                ", [$fecha]);

            // Calcular totales
            $totalActivo = array_sum(array_column($activo, 'saldo'));
            $totalPasivo = array_sum(array_column($pasivo, 'saldo'));
            $totalPatrimonio = array_sum(array_column($patrimonio, 'saldo'));

            $datos = [
                'fecha' => $fecha,
                'activo' => $activo,
                'pasivo' => $pasivo,
                'patrimonio' => $patrimonio,
                'total_activo' => $totalActivo,
                'total_pasivo' => $totalPasivo,
                'total_patrimonio' => $totalPatrimonio,
                'ecuacion_contable' => $totalActivo == ($totalPasivo + $totalPatrimonio)
            ];

            return view('contabilidad.balance-general', $datos);
            
        } catch (\Exception $e) {
            Log::error('Error en balance general: ' . $e->getMessage());
            return back()->with('error', 'Error al generar balance general: ' . $e->getMessage());
        }
    }

    /**
     * ESTADO DE RESULTADOS - GANANCIAS Y PÉRDIDAS
     * Estado de resultados del período
     */
    public function estadoResultados(Request $request)
    {
        try {
            $fechaInicio = $request->get('fecha_inicio', now()->startOfYear()->toDateString());
            $fechaFin = $request->get('fecha_fin', now()->toDateString());
            
            // Ingresos (4)
            $ingresos = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        LEFT(cuenta, 2) as grupo,
                        cuenta,
                        c.descripcion,
                        ISNULL(SUM(m.haber - m.debe), 0) as total
                    FROM dbo.cuentas_contables c
                    LEFT JOIN dbo.movimientos_contables m ON c.codigo = m.cuenta 
                        AND m.fecha BETWEEN ? AND ?
                    WHERE cuenta LIKE '4%'
                    AND c.activo = 1
                    GROUP BY LEFT(cuenta, 2), cuenta, c.descripcion
                    ORDER BY cuenta
                ", [$fechaInicio, $fechaFin]);

            // Gastos (6)
            $gastos = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        LEFT(cuenta, 2) as grupo,
                        cuenta,
                        c.descripcion,
                        ISNULL(SUM(m.debe - m.haber), 0) as total
                    FROM dbo.cuentas_contables c
                    LEFT JOIN dbo.movimientos_contables m ON c.codigo = m.cuenta 
                        AND m.fecha BETWEEN ? AND ?
                    WHERE cuenta LIKE '6%'
                    AND c.activo = 1
                    GROUP BY LEFT(cuenta, 2), cuenta, c.descripcion
                    ORDER BY cuenta
                ", [$fechaInicio, $fechaFin]);

            // Costos (6)
            $costos = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        LEFT(cuenta, 2) as grupo,
                        cuenta,
                        c.descripcion,
                        ISNULL(SUM(m.debe - m.haber), 0) as total
                    FROM dbo.cuentas_contables c
                    LEFT JOIN dbo.movimientos_contables m ON c.codigo = m.cuenta 
                        AND m.fecha BETWEEN ? AND ?
                    WHERE cuenta LIKE '63%'
                    AND c.activo = 1
                    GROUP BY LEFT(cuenta, 2), cuenta, c.descripcion
                    ORDER BY cuenta
                ", [$fechaInicio, $fechaFin]);

            $totalIngresos = array_sum(array_column($ingresos, 'total'));
            $totalGastos = array_sum(array_column($gastos, 'total'));
            $totalCostos = array_sum(array_column($costos, 'total'));
            $utilidadBruta = $totalIngresos - $totalCostos;
            $utilidadNeta = $utilidadBruta - $totalGastos;

            $datos = [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'ingresos' => $ingresos,
                'gastos' => $gastos,
                'costos' => $costos,
                'total_ingresos' => $totalIngresos,
                'total_gastos' => $totalGastos,
                'total_costos' => $totalCostos,
                'utilidad_bruta' => $utilidadBruta,
                'utilidad_neta' => $utilidadNeta,
                'margen_bruto' => $totalIngresos > 0 ? ($utilidadBruta / $totalIngresos) * 100 : 0,
                'margen_neto' => $totalIngresos > 0 ? ($utilidadNeta / $totalIngresos) * 100 : 0
            ];

            return view('contabilidad.estado-resultados', $datos);
            
        } catch (\Exception $e) {
            Log::error('Error en estado de resultados: ' . $e->getMessage());
            return back()->with('error', 'Error al generar estado de resultados: ' . $e->getMessage());
        }
    }

    public function error404()
    {
        return response()->view('errors.403', [], 404);
    }

    /**
     * ANÁLISIS DE CARTERA VENCIDA
     * Para ver qué facturas están vencidas y necesitan cobro
     */
    public function analisisCartera()
    {
        try {
            // Cartera vencida por antigüedad
            $carteraVencida = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        c.Razon as cliente,
                        c.Documento,
                        f.Numero as documento,
                        f.Fecha as fecha_emision,
                        f.FechaV as fecha_vencimiento,
                        f.Total,
                        cc.Saldo,
                        DATEDIFF(DAY, f.FechaV, GETDATE()) as dias_vencimiento,
                        CASE 
                            WHEN DATEDIFF(DAY, f.FechaV, GETDATE()) <= 30 THEN '1-30 días'
                            WHEN DATEDIFF(DAY, f.FechaV, GETDATE()) <= 60 THEN '31-60 días'
                            WHEN DATEDIFF(DAY, f.FechaV, GETDATE()) <= 90 THEN '61-90 días'
                            ELSE 'Más de 90 días'
                        END as antiguedad,
                        e.Nombre as vendedor
                    FROM dbo.CtaCliente cc
                    INNER JOIN dbo.Doccab f ON cc.Documento = f.Numero AND cc.Tipo = f.Tipo
                    INNER JOIN dbo.Clientes c ON f.CodClie = c.Codclie
                    LEFT JOIN dbo.Empleados e ON f.Vendedor = e.Codemp
                    WHERE cc.Saldo > 0 AND f.FechaV < GETDATE() AND f.Eliminado = 0
                    ORDER BY f.FechaV
                ");

            // Resumen por antigüedad
            $resumenAntiguedad = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        CASE 
                            WHEN DATEDIFF(DAY, f.FechaV, GETDATE()) <= 30 THEN '1-30 días'
                            WHEN DATEDIFF(DAY, f.FechaV, GETDATE()) <= 60 THEN '31-60 días'
                            WHEN DATEDIFF(DAY, f.FechaV, GETDATE()) <= 90 THEN '61-90 días'
                            ELSE 'Más de 90 días'
                        END as antiguedad,
                        COUNT(*) as cantidad_facturas,
                        SUM(cc.Saldo) as total_saldo
                    FROM dbo.CtaCliente cc
                    INNER JOIN dbo.Doccab f ON cc.Documento = f.Numero AND cc.Tipo = f.Tipo
                    WHERE cc.Saldo > 0 AND f.FechaV < GETDATE() AND f.Eliminado = 0
                    GROUP BY 
                        CASE 
                            WHEN DATEDIFF(DAY, f.FechaV, GETDATE()) <= 30 THEN '1-30 días'
                            WHEN DATEDIFF(DAY, f.FechaV, GETDATE()) <= 60 THEN '31-60 días'
                            WHEN DATEDIFF(DAY, f.FechaV, GETDATE()) <= 90 THEN '61-90 días'
                            ELSE 'Más de 90 días'
                        END
                    ORDER BY MIN(DATEDIFF(DAY, f.FechaV, GETDATE()))
                ");

            // Top 10 clientes con mayor deuda vencida
            $topDeudores = DB::connection('sqlsrv')
                ->select("
                    SELECT TOP 10
                        c.Razon as cliente,
                        c.Documento,
                        SUM(cc.Saldo) as total_deuda,
                        COUNT(*) as facturas_vencidas,
                        MAX(DATEDIFF(DAY, f.FechaV, GETDATE())) as dias_maximo_vencimiento
                    FROM dbo.CtaCliente cc
                    INNER JOIN dbo.Doccab f ON cc.Documento = f.Numero AND cc.Tipo = f.Tipo
                    INNER JOIN dbo.Clientes c ON f.CodClie = c.Codclie
                    WHERE cc.Saldo > 0 AND f.FechaV < GETDATE() AND f.Eliminado = 0
                    GROUP BY c.Razon, c.Documento
                    ORDER BY total_deuda DESC
                ");

            $datos = [
                'cartera_vencida' => $carteraVencida,
                'resumen_antiguedad' => $resumenAntiguedad,
                'top_deudores' => $topDeudores
            ];

            return view('contabilidad.analisis-cartera', $datos);
            
        } catch (\Exception $e) {
            Log::error('Error en análisis de cartera: ' . $e->getMessage());
            return back()->with('error', 'Error al analizar cartera: ' . $e->getMessage());
        }
    }

    /**
     * CONTROL FARMACÉUTICO - PRODUCTOS VENCIDOS/PROXIMOS VENCER
     * Alertas específicas del sector farmacéutico
     */
    public function controlFarmaceutico()
    {
        try {
            // Productos próximos a vencer (30 días)
            $productosPorVencer = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        p.CodPro,
                        p.Nombre,
                        p.Principio,
                        l.Descripcion as laboratorio,
                        s.Lote,
                        s.Vencimiento,
                        s.Saldo,
                        DATEDIFF(DAY, GETDATE(), s.Vencimiento) as dias_vencimiento,
                        p.RegSanit
                    FROM dbo.Saldos s
                    INNER JOIN dbo.Productos p ON s.codpro = p.CodPro
                    LEFT JOIN dbo.Laboratorios l ON p.CodLab = l.CodLab
                    WHERE s.Saldo > 0 
                    AND s.Vencimiento BETWEEN GETDATE() AND DATEADD(DAY, 30, GETDATE())
                    AND p.Eliminado = 0
                    ORDER BY s.Vencimiento
                ");

            // Productos vencidos
            $productosVencidos = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        p.CodPro,
                        p.Nombre,
                        p.Principio,
                        l.Descripcion as laboratorio,
                        s.Lote,
                        s.Vencimiento,
                        s.Saldo,
                        DATEDIFF(DAY, s.Vencimiento, GETDATE()) as dias_vencido,
                        p.RegSanit
                    FROM dbo.Saldos s
                    INNER JOIN dbo.Productos p ON s.codpro = p.CodPro
                    LEFT JOIN dbo.Laboratorios l ON p.CodLab = l.CodLab
                    WHERE s.Saldo > 0 
                    AND s.Vencimiento < GETDATE()
                    AND p.Eliminado = 0
                    ORDER BY s.Vencimiento
                ");

            // Productos con temperatura crítica
            $productosTemperatura = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        p.CodPro,
                        p.Nombre,
                        p.Principio,
                        l.Descripcion as laboratorio,
                        p.TemMin,
                        p.TemMax,
                        s.Saldo,
                        s.Lote,
                        s.Vencimiento
                    FROM dbo.Productos p
                    LEFT JOIN dbo.Saldos s ON p.CodPro = s.codpro AND s.Saldo > 0
                    LEFT JOIN dbo.Laboratorios l ON p.CodLab = l.CodLab
                    WHERE (p.TemMin IS NOT NULL OR p.TemMax IS NOT NULL)
                    AND p.Eliminado = 0
                    ORDER BY p.CodPro
                ");

            // Resumen por laboratorio
            $resumenLaboratorios = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        l.Descripcion as laboratorio,
                        COUNT(DISTINCT p.CodPro) as productos_unicos,
                        SUM(s.Saldo) as stock_total,
                        MIN(s.Vencimiento) as proximo_vencimiento,
                        COUNT(CASE WHEN s.Vencimiento < GETDATE() THEN 1 END) as lotes_vencidos,
                        COUNT(CASE WHEN s.Vencimiento BETWEEN GETDATE() AND DATEADD(DAY, 30, GETDATE()) THEN 1 END) as lotes_proximos_vencer
                    FROM dbo.Laboratorios l
                    LEFT JOIN dbo.Productos p ON l.CodLab = p.CodLab AND p.Eliminado = 0
                    LEFT JOIN dbo.Saldos s ON p.CodPro = s.codpro AND s.Saldo > 0
                    WHERE l.Mantiene = 1
                    GROUP BY l.CodLab, l.Descripcion
                    ORDER BY stock_total DESC
                ");

            $datos = [
                'productos_por_vencer' => $productosPorVencer,
                'productos_vencidos' => $productosVencidos,
                'productos_temperatura' => $productosTemperatura,
                'resumen_laboratorios' => $resumenLaboratorios
            ];

            return view('contabilidad.control-farmaceutico', $datos);
            
        } catch (\Exception $e) {
            Log::error('Error en control farmacéutico: ' . $e->getMessage());
            return back()->with('error', 'Error en control farmacéutico: ' . $e->getMessage());
        }
    }

    public function contador()
    {

       return view('layouts.contador');
    }

    /**
     * REPORTES TRIBUTARIOS - ESTADO SUNAT
     * Para ver el estado de obligaciones tributarias
     */
    public function reportesTributarios()
    {
        try {
            // Estado de libros electrónicos
            $librosElectronicos = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        t.tipo_libro,
                        t.periodo,
                        t.estado,
                        t.fecha_envio,
                        t.codigo_respuesta,
                        t.mensaje_respuesta
                    FROM dbo.libros_electronicos t
                    WHERE t.periodo >= DATEADD(MONTH, -6, GETDATE())
                    ORDER BY t.periodo DESC, t.tipo_libro
                ");

            // Resumen de documentos del mes
            $resumenDocumentos = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        MONTH(Fecha) as mes,
                        YEAR(Fecha) as año,
                        Tipo,
                        COUNT(*) as cantidad,
                        SUM(Total) as total_mes
                    FROM dbo.Doccab
                    WHERE Fecha >= DATEADD(MONTH, -3, GETDATE())
                    AND Eliminado = 0
                    GROUP BY MONTH(Fecha), YEAR(Fecha), Tipo
                    ORDER BY año DESC, mes DESC, Tipo
                ");

            // IGV por períodos
            $igvPeriodos = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        MONTH(Fecha) as mes,
                        YEAR(Fecha) as año,
                        SUM(Igv) as total_igv,
                        COUNT(*) as numero_documentos
                    FROM dbo.Doccab
                    WHERE Fecha >= DATEADD(YEAR, -1, GETDATE())
                    AND Eliminado = 0
                    GROUP BY MONTH(Fecha), YEAR(Fecha)
                    ORDER BY año DESC, mes DESC
                ");

            $datos = [
                'libros_electronicos' => $librosElectronicos,
                'resumen_documentos' => $resumenDocumentos,
                'igv_periodos' => $igvPeriodos
            ];

            return view('contabilidad.reportes-tributarios', $datos);
            
        } catch (\Exception $e) {
            Log::error('Error en reportes tributarios: ' . $e->getMessage());
            return back()->with('error', 'Error en reportes tributarios: ' . $e->getMessage());
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================

    private function obtenerResumenGeneral()
    {
        return [
            'total_activos' => 0, // Se calculará desde cuentas contables
            'total_pasivos' => 0,
            'total_patrimonio' => 0,
            'ventas_mes' => 0,
            'cartera_vencida' => 0,
            'productos_vencer' => 0
        ];
    }

    private function obtenerCuentasPrincipales()
    {
        return DB::connection('sqlsrv')
            ->select("
                SELECT 
                    cuenta,
                    descripcion,
                    saldo,
                    naturaleza,
                    grupo
                FROM dbo.cuentas_contables
                WHERE nivel = 1
                AND activo = 1
                ORDER BY cuenta
            ");
    }

    private function obtenerAlertasContables()
    {
        $alertas = [];

        // Facturas vencidas
        $facturasVencidas = DB::connection('sqlsrv')
            ->select("SELECT COUNT(*) as cantidad FROM dbo.CtaCliente WHERE Saldo > 0 AND FechaV < GETDATE()");
        
        if ($facturasVencidas[0]->cantidad > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'icono' => 'fa-exclamation-triangle',
                'titulo' => 'Facturas Vencidas',
                'mensaje' => "Tienes {$facturasVencidas[0]->cantidad} facturas vencidas que requieren atención",
                'url' => route('contabilidad.analisis-cartera')
            ];
        }

        // Productos próximos a vencer
        $productosVencer = DB::connection('sqlsrv')
            ->select("SELECT COUNT(*) as cantidad FROM dbo.Saldos s JOIN dbo.Productos p ON s.codpro = p.CodPro WHERE s.Saldo > 0 AND s.Vencimiento BETWEEN GETDATE() AND DATEADD(DAY, 30, GETDATE()) AND p.Eliminado = 0");
        
        if ($productosVencer[0]->cantidad > 0) {
            $alertas[] = [
                'tipo' => 'info',
                'icono' => 'fa-pills',
                'titulo' => 'Productos Próximos a Vencer',
                'mensaje' => "Tienes {$productosVencer[0]->cantidad} lotes de productos que vencen en los próximos 30 días",
                'url' => route('contabilidad.control-farmaceutico')
            ];
        }

        return $alertas;
    }

    private function obtenerCarteraVencida()
    {
        return DB::connection('sqlsrv')
            ->select("
                SELECT TOP 5
                    c.Razon,
                    SUM(cc.Saldo) as total_vencido,
                    MAX(DATEDIFF(DAY, f.FechaV, GETDATE())) as dias_maximo
                FROM dbo.CtaCliente cc
                INNER JOIN dbo.Doccab f ON cc.Documento = f.Numero AND cc.Tipo = f.Tipo
                INNER JOIN dbo.Clientes c ON f.CodClie = c.Codclie
                WHERE cc.Saldo > 0 AND f.FechaV < GETDATE()
                GROUP BY c.Razon
                ORDER BY total_vencido DESC
            ");
    }

    private function obtenerAlertasFarmaceuticas()
    {
        return [
            'productos_vencidos' => 0,
            'productos_temperatura' => 0,
            'registros_sanitarios' => 0
        ];
    }

    private function obtenerEstadoTributario()
    {
        return [
            'libros_pendientes' => 0,
            'igv_mes_actual' => 0,
            'declaraciones_vencidas' => 0
        ];
    }

    private function obtenerUltimosMovimientos()
    {
        return DB::connection('sqlsrv')
            ->select("
                SELECT TOP 10
                    m.fecha,
                    m.comprobante,
                    m.glosa,
                    m.debe,
                    m.haber,
                    u.name as usuario
                FROM dbo.movimientos_contables m
                LEFT JOIN users u ON m.usuario_id = u.id
                ORDER BY m.created_at DESC
            ");
    }

    private function obtenerActividadReciente()
    {
        return [
            'ventas_hoy' => 0,
            'facturas_emitidas' => 0,
            'cobros_realizados' => 0,
            'productos_vendidos' => 0
        ];
    }
}
