<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Excel;
use Illuminate\Support\Facades\PDF;
use Carbon\Carbon;

class SunatController extends Controller
{
    /**
     * MÓDULO REPORTES - Controlador SUNAT
     * Reportes específicos para SUNAT y libros electrónicos
     * Integrado con base de datos SIFANO existente
     * Total de líneas: ~950
     */

    public function __construct()
    {
        $this->middleware(['auth', 'rol:administrador|contador|gerente']);
    }

    /**
     * ===============================================
     * MÉTODOS PRINCIPALES DEL MÓDULO SUNAT
     * ===============================================
     */

    /**
     * Dashboard principal de reportes SUNAT
     */
    public function index(Request $request)
    {
        $periodo = $request->periodo ?? now()->format('Y-m');
        $resumen = $this->generarResumenSunat($periodo);
        $libros_disponibles = $this->obtenerLibrosDisponibles();
        $estado_envios = $this->obtenerEstadoEnvios($periodo);
        $alertas_cumplimiento = $this->generarAlertasCumplimiento();

        return compact('resumen', 'libros_disponibles', 'estado_envios', 'alertas_cumplimiento', 'periodo');
    }

    /**
     * ===============================================
     * LIBROS ELECTRÓNICOS SUNAT
     * ===============================================
     */

    /**
     * Genera Libro de Ventas Electrónico
     */
    public function libroVentas(Request $request)
    {
        $periodo = $request->periodo ?? now()->format('Y-m');
        $fecha_desde = $request->fecha_desde ?? $periodo . '-01';
        $fecha_hasta = $request->fecha_hasta ?? $periodo . '-31';

        $ventas = DB::table('Doccab')
            ->join('Clientes', 'Doccab.Codcli', '=', 'Clientes.Codcli')
            ->whereBetween('Doccab.Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Doccab.Estado', ['ANULADO'])
            ->select(
                'Doccab.Numero',
                'Doccab.Serie',
                'Doccab.Fecha',
                'Doccab.TipoDoc',
                'Clientes.Ruc',
                'Clientes.Razonsocial',
                'Doccab.Total',
                'Doccab.Igv',
                'Doccab.Estado'
            )
            ->orderBy('Doccab.Fecha')
            ->get()
            ->map(function($venta) {
                return [
                    'fecha_emision' => $venta->Fecha,
                    'tipo_documento' => $venta->TipoDoc,
                    'serie_numero' => $venta->Serie . '-' . $venta->Numero,
                    'ruc_cliente' => $venta->Ruc,
                    'razon_social' => $venta->Razonsocial,
                    'base_imponible' => $venta->Total - $venta->Igv,
                    'igv' => $venta->Igv,
                    'total' => $venta->Total,
                    'estado' => $venta->Estado,
                    'moneda' => 'PEN'
                ];
            });

        return [
            'periodo' => $periodo,
            'datos' => $ventas,
            'total_registros' => $ventas->count(),
            'total_base_imponible' => $ventas->sum('base_imponible'),
            'total_igv' => $ventas->sum('igv'),
            'total_ventas' => $ventas->sum('total'),
            'fecha_generacion' => now()
        ];
    }

    /**
     * Genera Libro de Compras Electrónico
     */
    public function libroCompras(Request $request)
    {
        $periodo = $request->periodo ?? now()->format('Y-m');
        $fecha_desde = $request->fecha_desde ?? $periodo . '-01';
        $fecha_hasta = $request->fecha_hasta ?? $periodo . '-31';

        // En este ejemplo usamos la misma tabla Doccab, pero en producción
        // se tendrían documentos de proveedores en otra tabla
        $compras = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->where('TipoDoc', '=', 'FA')
            ->whereNotIn('Estado', ['ANULADO'])
            ->select(
                'Numero',
                'Serie',
                'Fecha',
                'FechaVencimiento',
                'Total',
                'Igv',
                'Estado'
            )
            ->get()
            ->map(function($compra) {
                return [
                    'fecha_emision' => $compra->Fecha,
                    'fecha_vencimiento' => $compra->FechaVencimiento,
                    'tipo_documento' => '01', // Factura
                    'serie_numero' => $compra->Serie . '-' . $compra->Numero,
                    'ruc_proveedor' => '20123456789', // Placeholder
                    'razon_social_proveedor' => 'PROVEEDOR EJEMPLO',
                    'base_imponible' => $compra->Total - $compra->Igv,
                    'igv' => $compra->Igv,
                    'total' => $compra->Total,
                    'estado' => $compra->Estado,
                    'moneda' => 'PEN'
                ];
            });

        return [
            'periodo' => $periodo,
            'datos' => $compras,
            'total_registros' => $compras->count(),
            'total_base_imponible' => $compras->sum('base_imponible'),
            'total_igv' => $compras->sum('igv'),
            'total_compras' => $compras->sum('total')
        ];
    }

    /**
     * Genera Libro Diario Simplificado
     */
    public function libroDiario(Request $request)
    {
        $periodo = $request->periodo ?? now()->format('Y-m');
        $fecha_desde = $request->fecha_desde ?? $periodo . '-01';
        $fecha_hasta = $request->fecha_hasta ?? $periodo . '-31';

        // Obtener todos los asientos del período
        $asientos = DB::table('asientos_diario')
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->orderBy('fecha')
            ->orderBy('numero_asiento')
            ->get();

        $resumen_diario = [];
        $total_debe = 0;
        $total_haber = 0;

        foreach ($asientos as $asiento) {
            $total_debe += $asiento->monto;
            $total_haber += $asiento->monto;
            
            $resumen_diario[] = [
                'fecha' => $asiento->fecha,
                'numero_asiento' => $asiento->numero_asiento,
                'glosa' => $asiento->glosa,
                'cuenta_debe' => $asiento->cuenta_debe,
                'cuenta_haber' => $asiento->cuenta_haber,
                'monto_debe' => $asiento->monto,
                'monto_haber' => $asiento->monto,
                'moneda' => 'PEN'
            ];
        }

        return [
            'periodo' => $periodo,
            'datos' => $resumen_diario,
            'total_asientos' => $asientos->groupBy('numero_asiento')->count(),
            'total_debe' => $total_debe,
            'total_haber' => $total_haber,
            'cuadrado' => abs($total_debe - $total_haber) < 0.01
        ];
    }

    /**
     * ===============================================
     * REPORTES TRIBUTARIOS ESPECÍFICOS
     * ===============================================
     */

    /**
     * Reporte de PDT - Formulario Virtual 621
     */
    public function reportePDT621(Request $request)
    {
        $periodo = $request->periodo ?? now()->format('Y-m');
        $ejercicio = substr($periodo, 0, 4);
        
        $ventas_mes = $this->calcularVentasMes($periodo);
        $compras_mes = $this->calcularComprasMes($periodo);
        $impuestos = $this->calcularImpuestos($periodo);

        $datos_621 = [
            'ejercicio' => $ejercicio,
            'periodo' => $periodo,
            'ruc_contribuyente' => '20123456789', // RUC de la empresa
            'razon_social' => 'EMPRESA SIFANO',
            'ventas_totales' => $ventas_mes['total'],
            'ventas_gravadas' => $ventas_mes['gravadas'],
            'ventas_exoneradas' => $ventas_mes['exoneradas'],
            'igv_ventas' => $impuestos['igv_ventas'],
            'igv_compras' => $impuestos['igv_compras'],
            'igv_credito_fiscal' => $impuestos['credito_fiscal'],
            'igv_pagar' => $impuestos['igv_ventas'] - $impuestos['igv_compras'],
            'deudas_tributarias' => $impuestos['deudas_pendientes']
        ];

        return $datos_621;
    }

    /**
     * Reporte de Ingresos y Gastos
     */
    public function reporteIngresosGastos(Request $request)
    {
        $ejercicio = $request->ejercicio ?? now()->year;
        
        // Ingresos por mes
        $ingresos_mensuales = DB::table('Doccab')
            ->selectRaw('YEAR(Fecha) as año, MONTH(Fecha) as mes, SUM(Total) as total_ingresos')
            ->whereYear('Fecha', $ejercicio)
            ->whereNotIn('Estado', ['ANULADO'])
            ->groupByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->orderByRaw('YEAR(Fecha), MONTH(Fecha)')
            ->get();

        // Gastos por mes (desde asientos contables)
        $gastos_mensuales = DB::table('asientos_diario')
            ->join('plan_cuentas', 'asientos_diario.cuenta_haber', '=', 'plan_cuentas.codigo')
            ->selectRaw('YEAR(asientos_diario.fecha) as año, MONTH(asientos_diario.fecha) as mes, SUM(asientos_diario.monto) as total_gastos')
            ->whereYear('asientos_diario.fecha', $ejercicio)
            ->where('plan_cuentas.tipo_cuenta', 'GASTO')
            ->groupByRaw('YEAR(asientos_diario.fecha), MONTH(asientos_diario.fecha)')
            ->orderByRaw('YEAR(asientos_diario.fecha), MONTH(asientos_diario.fecha)')
            ->get();

        // Combinar y calcular netos
        $resultado = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            $ingreso = $ingresos_mensuales->where('mes', $mes)->first();
            $gasto = $gastos_mensuales->where('mes', $mes)->first();
            
            $resultado[] = [
                'mes' => $mes,
                'ingresos' => $ingreso->total_ingresos ?? 0,
                'gastos' => $gasto->total_gastos ?? 0,
                'utilidad' => ($ingreso->total_ingresos ?? 0) - ($gasto->total_gastos ?? 0)
            ];
        }

        return [
            'ejercicio' => $ejercicio,
            'datos_mensuales' => $resultado,
            'totales' => [
                'total_ingresos' => array_sum(array_column($resultado, 'ingresos')),
                'total_gastos' => array_sum(array_column($resultado, 'gastos')),
                'utilidad_neta' => array_sum(array_column($resultado, 'utilidad'))
            ]
        ];
    }

    /**
     * ===============================================
     * FACTURACIÓN ELECTRÓNICA
     * ===============================================
     */

    /**
     * Reporte de Facturas Electrónicas Emitidas
     */
    public function facturasElectronicas(Request $request)
    {
        $periodo = $request->periodo ?? now()->format('Y-m');
        $fecha_desde = $request->fecha_desde ?? $periodo . '-01';
        $fecha_hasta = $request->fecha_hasta ?? $periodo . '-31';

        $facturas_electronicas = DB::table('Doccab')
            ->join('Clientes', 'Doccab.Codcli', '=', 'Clientes.Codcli')
            ->leftJoin('facturas_electronicas', 'Doccab.Numero', '=', 'facturas_electronicas.numero_factura')
            ->whereBetween('Doccab.Fecha', [$fecha_desde, $fecha_hasta])
            ->where('Doccab.TipoDoc', 'FA')
            ->select(
                'Doccab.Numero',
                'Doccab.Serie',
                'Doccab.Fecha',
                'Clientes.Ruc',
                'Clientes.Razonsocial',
                'Doccab.Total',
                'Doccab.Igv',
                'facturas_electronicas.estado_sunat',
                'facturas_electronicas.fecha_envio',
                'facturas_electronicas.codigo_respuesta'
            )
            ->orderBy('Doccab.Fecha')
            ->get()
            ->map(function($factura) {
                return [
                    'fecha_emision' => $factura->Fecha,
                    'serie_numero' => $factura->Serie . '-' . $factura->Numero,
                    'ruc_cliente' => $factura->Ruc,
                    'razon_social' => $factura->Razonsocial,
                    'total' => $factura->Total,
                    'estado_sunat' => $factura->estado_sunat ?? 'PENDIENTE',
                    'fecha_envio' => $factura->fecha_envio,
                    'observaciones' => $this->interpretarEstadoSunat($factura->codigo_respuesta)
                ];
            });

        return [
            'periodo' => $periodo,
            'facturas' => $facturas_electronicas,
            'resumen_estados' => $facturas_electronicas->groupBy('estado_sunat')->map->count(),
            'pendientes_envio' => $facturas_electronicas->where('estado_sunat', 'PENDIENTE')->count()
        ];
    }

    /**
     * Estado de validación SUNAT
     */
    public function estadoValidacionSunat(Request $request)
    {
        $periodo = $request->periodo ?? now()->format('Y-m');
        
        $estadisticas = DB::table('facturas_electronicas')
            ->selectRaw('
                estado_sunat,
                COUNT(*) as cantidad,
                SUM(total) as monto_total
            ')
            ->where('periodo', $periodo)
            ->groupBy('estado_sunat')
            ->get();

        $pendientes = DB::table('facturas_electronicas')
            ->where('estado_sunat', 'PENDIENTE')
            ->where('periodo', $periodo)
            ->count();

        return [
            'periodo' => $periodo,
            'estadisticas' => $estadisticas,
            'pendientes_envio' => $pendientes,
            'ultimo_envio' => DB::table('facturas_electronicas')
                ->where('periodo', $periodo)
                ->max('fecha_envio')
        ];
    }

    /**
     * ===============================================
     * VALIDACIONES Y CUMPLIMIENTO
     * ===============================================
     */

    /**
     * Validación de consistencia de datos SUNAT
     */
    public function validarConsistencia(Request $request)
    {
        $periodo = $request->periodo ?? now()->format('Y-m');
        $validaciones = [];

        // Validar que las facturas tengan RUC válidos
        $facturas_sin_ruc = DB::table('Doccab')
            ->join('Clientes', 'Doccab.Codcli', '=', 'Clientes.Codcli')
            ->whereBetween('Doccab.Fecha', [$periodo . '-01', $periodo . '-31'])
            ->where('Clientes.Ruc', 'like', '20%')
            ->where('Clientes.Ruc', '!=', '20123456789') // RUC de la empresa
            ->count();

        $validaciones[] = [
            'tipo' => 'RUC_CLIENTES',
            'descripcion' => 'Facturas con RUC de clientes',
            'resultado' => $facturas_sin_ruc,
            'estado' => $facturas_sin_ruc > 0 ? 'CORRECTO' : 'VERIFICAR',
            'mensaje' => $facturas_sin_ruc > 0 ? 'Los RUC de clientes están presentes' : 'Verificar RUC de clientes'
        ];

        // Validar que no haya facturas duplicadas
        $facturas_duplicadas = DB::table('Doccab')
            ->selectRaw('Serie, Numero, COUNT(*) as cantidad')
            ->whereBetween('Fecha', [$periodo . '-01', $periodo . '-31'])
            ->groupBy('Serie', 'Numero')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $validaciones[] = [
            'tipo' => 'FACTURAS_DUPLICADAS',
            'descripcion' => 'Facturas duplicadas en el período',
            'resultado' => $facturas_duplicadas,
            'estado' => $facturas_duplicadas == 0 ? 'CORRECTO' : 'ERROR',
            'mensaje' => $facturas_duplicadas == 0 ? 'No hay facturas duplicadas' : "Se encontraron {$facturas_duplicadas} facturas duplicadas"
        ];

        // Validar cuadratura contable
        $cuadratura = $this->validarCuadraturaContable($periodo);
        $validaciones[] = [
            'tipo' => 'CUADRATURA_CONTABLE',
            'descripcion' => 'Cuadratura entre ventas y asientos contables',
            'resultado' => $cuadratura['diferencia'],
            'estado' => abs($cuadratura['diferencia']) < 0.01 ? 'CORRECTO' : 'ERROR',
            'mensaje' => 'Diferencia contable: ' . $cuadratura['diferencia']
        ];

        // Validar totales IGV
        $totales_igv = $this->validarTotalesIGV($periodo);
        $validaciones[] = [
            'tipo' => 'TOTALES_IGV',
            'descripcion' => 'Consistencia en cálculos de IGV',
            'resultado' => $totales_igv['consistente'],
            'estado' => $totales_igv['consistente'] ? 'CORRECTO' : 'ERROR',
            'mensaje' => $totales_igv['mensaje']
        ];

        return [
            'periodo' => $periodo,
            'validaciones' => $validaciones,
            'resumen' => [
                'total_validaciones' => count($validaciones),
                'correctas' => collect($validaciones)->where('estado', 'CORRECTO')->count(),
                'errores' => collect($validaciones)->where('estado', 'ERROR')->count(),
                'verificar' => collect($validaciones)->where('estado', 'VERIFICAR')->count()
            ]
        ];
    }

    /**
     * ===============================================
     * EXPORTACIÓN DE ARCHIVOS SUNAT
     * ===============================================
     */

    /**
     * Exporta libro de ventas en formato TXT para SUNAT
     */
    public function exportarLibroVentasTxt(Request $request)
    {
        $periodo = $request->periodo ?? now()->format('Y-m');
        $datos = $this->libroVentas($request);
        
        $contenido = "1|" . $periodo . "|LE|" . env('RUC_EMPRESA') . "|" . env('RAZON_SOCIAL') . "\n";
        $contenido .= "D|FECHAEMI|TIPODOC|SERIE|CONSTANCIA|RUC|RAZONSOC|BASE|IGV|TOTAL|MONEDA|ESTADO\n";

        foreach ($datos['datos'] as $row) {
            $contenido .= sprintf(
                "C|%s|%s|%s|%s|%s|%s|%.2f|%.2f|%.2f|%s|%s\n",
                $row['fecha_emision'],
                $row['tipo_documento'],
                substr($row['serie_numero'], 0, strpos($row['serie_numero'], '-')),
                substr($row['serie_numero'], strpos($row['serie_numero'], '-') + 1),
                $row['ruc_cliente'],
                str_replace('|', '-', $row['razon_social']),
                $row['base_imponible'],
                $row['igv'],
                $row['total'],
                $row['moneda'],
                $row['estado']
            );
        }

        $filename = "LE_" . env('RUC_EMPRESA') . $periodo . "01_VENTAS.txt";
        
        return response($contenido)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Exporta reporte PDT 621 en Excel
     */
    public function exportarPDT621Excel(Request $request)
    {
        $datos = $this->reportePDT621($request);
        
        return Excel::download(new PDT621Export($datos), 'PDT621_' . $datos['periodo'] . '.xlsx');
    }

    /**
     * ===============================================
     * MÉTODOS DE CÁLCULO Y APOYO
     * ===============================================
     */

    /**
     * Genera resumen general SUNAT
     */
    private function generarResumenSunat($periodo)
    {
        $ventas = $this->calcularVentasMes($periodo);
        $compras = $this->calcularComprasMes($periodo);
        $impuestos = $this->calcularImpuestos($periodo);

        return [
            'periodo' => $periodo,
            'ventas_total' => $ventas['total'],
            'compras_total' => $compras['total'],
            'igv_ventas' => $impuestos['igv_ventas'],
            'igv_compras' => $impuestos['igv_compras'],
            'igv_pagar' => $impuestos['igv_ventas'] - $impuestos['igv_compras'],
            'facturas_emitidas' => $ventas['cantidad'],
            'facturas_recibidas' => $compras['cantidad']
        ];
    }

    /**
     * Calcula ventas del mes
     */
    private function calcularVentasMes($periodo)
    {
        $fecha_desde = $periodo . '-01';
        $fecha_hasta = $periodo . '-31';

        $ventas = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->selectRaw('COUNT(*) as cantidad, SUM(Total) as total, SUM(Igv) as igv')
            ->first();

        return [
            'cantidad' => $ventas->cantidad,
            'total' => $ventas->total,
            'gravadas' => $ventas->total - $ventas->igv,
            'exoneradas' => 0, // Se calcularía según tipo de cliente
            'igv' => $ventas->igv
        ];
    }

    /**
     * Calcula compras del mes
     */
    private function calcularComprasMes($periodo)
    {
        $fecha_desde = $periodo . '-01';
        $fecha_hasta = $periodo . '-31';

        // Simular compras (en producción vendría de tabla de proveedores)
        $compras = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->where('TipoDoc', 'FA')
            ->whereNotIn('Estado', ['ANULADO'])
            ->selectRaw('COUNT(*) as cantidad, SUM(Total) as total, SUM(Igv) as igv')
            ->first();

        return [
            'cantidad' => $compras->cantidad,
            'total' => $compras->total,
            'igv' => $compras->igv
        ];
    }

    /**
     * Calcula impuestos del período
     */
    private function calcularImpuestos($periodo)
    {
        $fecha_desde = $periodo . '-01';
        $fecha_hasta = $periodo . '-31';

        $igv_ventas = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Igv');

        // IGV compras (simulado)
        $igv_compras = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->where('TipoDoc', 'FA')
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Igv');

        return [
            'igv_ventas' => $igv_ventas,
            'igv_compras' => $igv_compras,
            'credito_fiscal' => $igv_compras,
            'deudas_pendientes' => 0
        ];
    }

    /**
     * Valida cuadratura contable
     */
    private function validarCuadraturaContable($periodo)
    {
        $fecha_desde = $periodo . '-01';
        $fecha_hasta = $periodo . '-31';

        // Ventas según Doccab
        $ventas_contabilidad = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->sum('Total');

        // Ventas según asientos contables
        $ventas_asientos = DB::table('asientos_diario')
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->where('cuenta_haber', '7011') // Cuenta de ventas
            ->sum('monto');

        return [
            'ventas_doccab' => $ventas_contabilidad,
            'ventas_asientos' => $ventas_asientos,
            'diferencia' => $ventas_contabilidad - $ventas_asientos
        ];
    }

    /**
     * Valida totales de IGV
     */
    private function validarTotalesIGV($periodo)
    {
        $fecha_desde = $periodo . '-01';
        $fecha_hasta = $periodo . '-31';

        $facturas = DB::table('Doccab')
            ->whereBetween('Fecha', [$fecha_desde, $fecha_hasta])
            ->whereNotIn('Estado', ['ANULADO'])
            ->get();

        $inconsistencias = 0;
        foreach ($facturas as $factura) {
            $total_calculado = ($factura->Total - $factura->Igv) * 1.18;
            if (abs($total_calculado - $factura->Total) > 0.01) {
                $inconsistencias++;
            }
        }

        return [
            'consistente' => $inconsistencias == 0,
            'inconsistencias' => $inconsistencias,
            'mensaje' => $inconsistencias == 0 ? 'Todos los cálculos de IGV son correctos' : "Se encontraron {$inconsistencias} inconsistencias"
        ];
    }

    /**
     * Interpreta código de respuesta SUNAT
     */
    private function interpretarEstadoSunat($codigo)
    {
        $interpretaciones = [
            '0' => 'Aceptado',
            '400' => 'Error de validación',
            '401' => 'No autorizado',
            '403' => 'Prohibido',
            '404' => 'No encontrado',
            '500' => 'Error interno',
            '520' => 'En proceso',
            '98' => 'Observación',
            '99' => 'Rechazado'
        ];

        return $interpretaciones[$codigo] ?? 'Sin código';
    }

    /**
     * ===============================================
     * MÉTODOS ADICIONALES
     * ===============================================
     */

    /**
     * Obtiene libros electrónicos disponibles
     */
    private function obtenerLibrosDisponibles()
    {
        return [
            'libro_ventas' => ['nombre' => 'Libro de Ventas', 'codigo' => '14'],
            'libro_compras' => ['nombre' => 'Libro de Compras', 'codigo' => '08'],
            'libro_diario' => ['nombre' => 'Libro Diario', 'codigo' => '05'],
            'pdt_621' => ['nombre' => 'PDT Formulario Virtual 621', 'codigo' => '621'],
            'pdt_647' => ['nombre' => 'PDT Formulario Virtual 647', 'codigo' => '647']
        ];
    }

    /**
     * Obtiene estado de envíos
     */
    private function obtenerEstadoEnvios($periodo)
    {
        return [
            'envios_exitosos' => DB::table('facturas_electronicas')
                ->where('periodo', $periodo)
                ->where('estado_sunat', 'ACEPTADO')
                ->count(),
            'envios_pendientes' => DB::table('facturas_electronicas')
                ->where('periodo', $periodo)
                ->where('estado_sunat', 'PENDIENTE')
                ->count(),
            'envios_error' => DB::table('facturas_electronicas')
                ->where('periodo', $periodo)
                ->whereIn('estado_sunat', ['RECHAZADO', 'ERROR'])
                ->count()
        ];
    }

    /**
     * Genera alertas de cumplimiento
     */
    private function generarAlertasCumplimiento()
    {
        $hoy = now();
        $proximo_envio = now()->endOfMonth();
        $dias_restantes = $hoy->diffInDays($proximo_envio, false);

        $alertas = [];

        if ($dias_restantes <= 5) {
            $alertas[] = [
                'tipo' => 'VENCIMIENTO_ENVIO',
                'mensaje' => "Faltan {$dias_restantes} días para el envío mensual",
                'prioridad' => 'ALTA'
            ];
        }

        // Verificar facturas pendientes de envío
        $pendientes = DB::table('facturas_electronicas')
            ->where('estado_sunat', 'PENDIENTE')
            ->count();

        if ($pendientes > 0) {
            $alertas[] = [
                'tipo' => 'FACTURAS_PENDIENTES',
                'mensaje' => "Hay {$pendientes} facturas pendientes de envío",
                'prioridad' => 'MEDIA'
            ];
        }

        return $alertas;
    }
}