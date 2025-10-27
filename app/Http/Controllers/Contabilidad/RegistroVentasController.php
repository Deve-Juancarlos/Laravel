<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RegistroVentasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $cliente = $request->input('cliente');
            $tipoDoc = $request->input('tipo_documento');

            // Obtener registro de ventas desde Doccab y Docdet
            $ventas = DB::table('Doccab as dc')
                ->leftJoin('Docdet as dd', 'dc.Numero', '=', 'dd.Numero')
                ->leftJoin('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->leftJoin('Empleados as e', 'dc.Vendedor', '=', 'e.Codemp')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1) // Tipo 1 = Ventas
                ->where('dc.Eliminado', 0)
                ->select([
                    'dc.Numero',
                    'dc.Fecha',
                    'dc.FechaV',
                    'dc.Subtotal',
                    'dc.Igv',
                    'dc.Total',
                    'dc.Moneda',
                    'dc.Cambio',
                    'dc.CodClie',
                    'c.Razon as cliente',
                    'c.Documento as documento_cliente',
                    'e.Nombre as vendedor',
                    'p.Nombre as producto',
                    'dd.Cantidad',
                    'dd.Precio',
                    'dd.Subtotal as subtotal_producto',
                    'dd.Costo'
                ])
                ->orderBy('dc.Fecha', 'desc')
                ->orderBy('dc.Numero', 'desc')
                ->paginate(50);

            // Resumen por tipo de documento
            $resumenDocumentos = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->select([
                    DB::raw('COUNT(*) as cantidad'),
                    DB::raw('SUM(CAST(dc.Subtotal as MONEY)) as total_subtotal'),
                    DB::raw('SUM(CAST(dc.Igv as MONEY)) as total_igv'),
                    DB::raw('SUM(CAST(dc.Total as MONEY)) as total_total')
                ])
                ->first();

            // Top clientes
            $topClientes = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->select([
                    'c.Codclie',
                    'c.Razon as cliente',
                    'c.Documento as documento',
                    'c.Zona',
                    DB::raw('SUM(CAST(dc.Total as MONEY)) as total_ventas'),
                    DB::raw('COUNT(*) as cantidad_documentos')
                ])
                ->groupBy('c.Codclie', 'c.Razon', 'c.Documento', 'c.Zona')
                ->orderBy('total_ventas', 'desc')
                ->limit(10)
                ->get();

            // Rendimiento por vendedor
            $rendimientoVendedores = DB::table('Doccab as dc')
                ->leftJoin('Empleados as e', 'dc.Vendedor', '=', 'e.Codemp')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->select([
                    'e.Codemp',
                    'e.Nombre as vendedor',
                    DB::raw('SUM(CAST(dc.Total as MONEY)) as total_ventas'),
                    DB::raw('COUNT(*) as cantidad_documentos'),
                    DB::raw('AVG(CAST(dc.Total as MONEY)) as promedio_venta')
                ])
                ->groupBy('e.Codemp', 'e.Nombre')
                ->orderBy('total_ventas', 'desc')
                ->get();

            // Totales generales
            $totales = DB::table('Doccab')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->where('Tipo', 1)
                ->where('Eliminado', 0)
                ->select([
                    DB::raw('SUM(Subtotal) as total_subtotal'),
                    DB::raw('SUM(Igv) as total_igv'),
                    DB::raw('SUM(Total) as total_general'),
                    DB::raw('COUNT(*) as total_documentos')
                ])
                ->first();

            return view('contabilidad.registros.ventas', compact(
                'ventas', 'fechaInicio', 'fechaFin', 'resumenDocumentos', 
                'topClientes', 'rendimientoVendedores', 'totales', 'cliente', 'tipoDoc'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el registro de ventas: ' . $e->getMessage());
        }
    }

    /**
     * Get daily sales summary
     */
    public function resumenDiario(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));

            // Ventas del día
            $ventasDiarias = DB::table('Doccab')
                ->whereDate('Fecha', $fecha)
                ->where('Tipo', 1) // Ventas
                ->where('Eliminado', 0)
                ->select([
                    'Numero',
                    'Fecha',
                    'Subtotal',
                    'Igv',
                    'Total',
                    'Moneda',
                    'Vendedor',
                    'CodClie'
                ])
                ->get();

            // Agrupar por moneda
            $resumenPorMoneda = $ventasDiarias->groupBy('Moneda')->map(function($group) {
                return [
                    'cantidad' => $group->count(),
                    'subtotal' => $group->sum('Subtotal'),
                    'igv' => $group->sum('Igv'),
                    'total' => $group->sum('Total')
                ];
            });

            // Clientes del día
            $clientesDiarios = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereDate('dc.Fecha', $fecha)
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->select([
                    'dc.CodClie',
                    'c.Razon as cliente',
                    'c.Documento as documento',
                    DB::raw('SUM(CAST(dc.Total as MONEY)) as total'),
                    DB::raw('COUNT(*) as cantidad')
                ])
                ->groupBy('dc.CodClie', 'c.Razon', 'c.Documento')
                ->orderBy('total', 'desc')
                ->get();

            // Ventas por vendedor
            $ventasPorVendedor = DB::table('Doccab as dc')
                ->leftJoin('Empleados as e', 'dc.Vendedor', '=', 'e.Codemp')
                ->whereDate('dc.Fecha', $fecha)
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->select([
                    'dc.Vendedor',
                    'e.Nombre as vendedor',
                    DB::raw('SUM(CAST(dc.Total as MONEY)) as total'),
                    DB::raw('COUNT(*) as cantidad')
                ])
                ->groupBy('dc.Vendedor', 'e.Nombre')
                ->orderBy('total', 'desc')
                ->get();

            // Totales
            $totalesDiarios = [
                'fecha' => $fecha,
                'total_documentos' => $ventasDiarias->count(),
                'total_subtotal' => $ventasDiarias->sum('Subtotal'),
                'total_igv' => $ventasDiarias->sum('Igv'),
                'total_general' => $ventasDiarias->sum('Total')
            ];

            return view('contabilidad.registros.ventas', compact(
                'ventasDiarias', 'resumenPorMoneda', 'clientesDiarios', 'ventasPorVendedor', 'totalesDiarios', 'fecha'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar resumen diario: ' . $e->getMessage());
        }
    }

    /**
     * Get monthly summary
     */
    public function resumenMensual(Request $request)
    {
        try {
            $anio = $request->input('anio', Carbon::now()->year);
            $mes = $request->input('mes', Carbon::now()->month);

            $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth()->format('Y-m-d');
            $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

            // Resumen diario del mes
            $resumenDiario = DB::table('Doccab')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->where('Tipo', 1)
                ->where('Eliminado', 0)
                ->select([
                    DB::raw('DAY(Fecha) as dia'),
                    DB::raw('COUNT(*) as documentos'),
                    DB::raw('SUM(Subtotal) as subtotal'),
                    DB::raw('SUM(Igv) as igv'),
                    DB::raw('SUM(Total) as total')
                ])
                ->groupBy('dia')
                ->orderBy('dia')
                ->get();

            // Productos más vendidos en el mes
            $productosTop = DB::table('Docdet as dd')
                ->join('Doccab as dc', 'dd.Numero', '=', 'dc.Numero')
                ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->select([
                    'p.CodPro',
                    'p.Nombre',
                    DB::raw('SUM(dd.Cantidad) as cantidad_vendida'),
                    DB::raw('SUM(CAST(dd.Subtotal as MONEY)) as total_ventas')
                ])
                ->groupBy('p.CodPro', 'p.Nombre')
                ->orderBy('total_ventas', 'desc')
                ->limit(10)
                ->get();

            // Top 10 clientes del mes
            $topClientesMes = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->select([
                    'c.Codclie',
                    'c.Razon as cliente',
                    'c.Documento',
                    'c.Zona',
                    DB::raw('SUM(CAST(dc.Total as MONEY)) as total_ventas'),
                    DB::raw('COUNT(*) as cantidad_facturas')
                ])
                ->groupBy('c.Codclie', 'c.Razon', 'c.Documento', 'c.Zona')
                ->orderBy('total_ventas', 'desc')
                ->limit(10)
                ->get();

            // Totales del mes
            $totalesMes = DB::table('Doccab')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->where('Tipo', 1)
                ->where('Eliminado', 0)
                ->select([
                    DB::raw('COUNT(*) as total_documentos'),
                    DB::raw('SUM(Subtotal) as total_subtotal'),
                    DB::raw('SUM(Igv) as total_igv'),
                    DB::raw('SUM(Total) as total_general')
                ])
                ->first();

            return view('contabilidad.registros.ventas', compact(
                'resumenDiario', 'productosTop', 'topClientesMes', 'totalesMes', 'anio', 'mes'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar resumen mensual: ' . $e->getMessage());
        }
    }

    /**
     * Get sales analysis by customer
     */
    public function porCliente(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Análisis por cliente
            $analisisClientes = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->select([
                    'c.Codclie',
                    'c.Razon as cliente',
                    'c.Documento',
                    'c.Zona',
                    'z.Descripcion as zona_nombre',
                    'c.Direccion',
                    'c.Telefono1',
                    DB::raw('COUNT(DISTINCT dc.Numero) as cantidad_facturas'),
                    DB::raw('SUM(CAST(dc.Subtotal as MONEY)) as total_subtotal'),
                    DB::raw('SUM(CAST(dc.Igv as MONEY)) as total_igv'),
                    DB::raw('SUM(CAST(dc.Total as MONEY)) as total_ventas'),
                    DB::raw('MIN(dc.Fecha) as primera_compra'),
                    DB::raw('MAX(dc.Fecha) as ultima_compra')
                ])
                ->groupBy('c.Codclie', 'c.Razon', 'c.Documento', 'c.Zona', 'z.Descripcion', 'c.Direccion', 'c.Telefono1')
                ->orderBy('total_ventas', 'desc')
                ->get();

            // Calcular estadísticas por cliente
            foreach ($analisisClientes as $cliente) {
                // Promedio por factura
                $cliente->promedio_por_factura = $cliente->cantidad_facturas > 0 ? 
                    $cliente->total_ventas / $cliente->cantidad_facturas : 0;
                
                // Días desde última compra
                $cliente->dias_ultima_compra = Carbon::parse($cliente->ultima_compra)
                    ->diffInDays(Carbon::now());
                
                // Frecuencia de compra
                $diasTranscurridos = Carbon::parse($cliente->primera_compra)
                    ->diffInDays(Carbon::parse($cliente->ultima_compra));
                $cliente->frecuencia_compra = $diasTranscurridos > 0 ?
                    $cliente->cantidad_facturas / ($diasTranscurridos / 30) : 0;
            }

            // Totales generales
            $totalesGenerales = [
                'total_clientes' => $analisisClientes->count(),
                'total_ventas' => $analisisClientes->sum('total_ventas'),
                'total_facturas' => $analisisClientes->sum('cantidad_facturas'),
                'promedio_por_cliente' => $analisisClientes->count() > 0 ?
                    $analisisClientes->sum('total_ventas') / $analisisClientes->count() : 0
            ];

            return view('contabilidad.registros.ventas', compact(
                'analisisClientes', 'totalesGenerales', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al analizar clientes: ' . $e->getMessage());
        }
    }

    /**
     * Get sales trends
     */
    public function tendencias(Request $request)
    {
        try {
            $anio = $request->input('anio', Carbon::now()->year);

            // Tendencias mensuales
            $tendenciasMensuales = [];
            for ($mes = 1; $mes <= 12; $mes++) {
                $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth()->format('Y-m-d');
                $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

                $totales = DB::table('Doccab')
                    ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                    ->where('Tipo', 1)
                    ->where('Eliminado', 0)
                    ->select([
                        DB::raw('COUNT(*) as documentos'),
                        DB::raw('SUM(Subtotal) as subtotal'),
                        DB::raw('SUM(Total) as total')
                    ])
                    ->first();

                $tendenciasMensuales[$mes] = [
                    'mes' => Carbon::create($anio, $mes, 1)->format('F'),
                    'documentos' => $totales->documentos ?? 0,
                    'subtotal' => $totales->subtotal ?? 0,
                    'total' => $totales->total ?? 0
                ];
            }

            // Comparación con año anterior
            $anioAnterior = $anio - 1;
            $tendenciasAnioAnterior = [];

            for ($mes = 1; $mes <= 12; $mes++) {
                $fechaInicio = Carbon::create($anioAnterior, $mes, 1)->startOfMonth()->format('Y-m-d');
                $fechaFin = Carbon::create($anioAnterior, $mes, 1)->endOfMonth()->format('Y-m-d');

                $totalesAnterior = DB::table('Doccab')
                    ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                    ->where('Tipo', 1)
                    ->where('Eliminado', 0)
                    ->sum('Total');

                $tendenciasAnioAnterior[$mes] = $totalesAnterior;
            }

            // Calcular crecimiento
            foreach ($tendenciasMensuales as $mes => &$datos) {
                $datos['crecimiento'] = $tendenciasAnioAnterior[$mes] > 0 ?
                    (($datos['total'] - $tendenciasAnioAnterior[$mes]) / $tendenciasAnioAnterior[$mes]) * 100 : 0;
            }

            // Proyección para el resto del año
            $proyeccionAnio = $this->proyectarVentasAnio($anio, $tendenciasMensuales);

            return view('contabilidad.registros.ventas', compact(
                'tendenciasMensuales', 'anio', 'anioAnterior', 'proyeccionAnio'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al analizar tendencias: ' . $e->getMessage());
        }
    }

    /**
     * Get pharmacy-specific sales (medicamentos, etc.)
     */
    public function farmaceuticas(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Ventas farmacéuticas específicas
            $ventasFarmaceuticas = DB::table('Docdet as dd')
                ->join('Doccab as dc', 'dd.Numero', '=', 'dc.Numero')
                ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->whereNotNull('p.RegSanit') // Productos con registro sanitario
                ->select([
                    'p.CodPro',
                    'p.Nombre',
                    'p.RegSanit',
                    'p.Principio',
                    'l.Descripcion as laboratorio',
                    DB::raw('SUM(dd.Cantidad) as cantidad_vendida'),
                    DB::raw('SUM(CAST(dd.Subtotal as MONEY)) as total_ventas'),
                    DB::raw('AVG(CAST(dd.Precio as MONEY)) as precio_promedio'),
                    DB::raw('AVG(CAST(dd.Costo as MONEY)) as costo_promedio')
                ])
                ->groupBy('p.CodPro', 'p.Nombre', 'p.RegSanit', 'p.Principio', 'l.Descripcion')
                ->orderBy('total_ventas', 'desc')
                ->get();

            // Clasificar productos farmacéuticos
            $clasificacionProductos = $this->clasificarProductosFarmaceuticos($ventasFarmaceuticas);

            // Control de ventas por tipo de cliente
            $ventasPorTipoCliente = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->leftJoin('Docdet as dd', 'dc.Numero', '=', 'dd.Numero')
                ->leftJoin('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->whereNotNull('p.RegSanit')
                ->select([
                    'c.TipoClie',
                    DB::raw('COUNT(DISTINCT dc.CodClie) as clientes_unicos'),
                    DB::raw('COUNT(dc.Numero) as documentos'),
                    DB::raw('SUM(CAST(dd.Subtotal as MONEY)) as total_ventas'),
                    DB::raw('SUM(dd.Cantidad) as cantidad_total')
                ])
                ->groupBy('c.TipoClie')
                ->orderBy('total_ventas', 'desc')
                ->get();

            // Totales farmacéuticos
            $totalesFarmaceuticos = [
                'total_productos' => $ventasFarmaceuticas->count(),
                'total_ventas' => $ventasFarmaceuticas->sum('total_ventas'),
                'total_cantidad' => $ventasFarmaceuticas->sum('cantidad_vendida'),
                'precio_promedio' => $ventasFarmaceuticas->count() > 0 ?
                    $ventasFarmaceuticas->avg('precio_promedio') : 0,
                'margen_promedio' => $this->calcularMargenPromedio($ventasFarmaceuticas)
            ];

            return view('contabilidad.registros.ventas', compact(
                'ventasFarmaceuticas', 'clasificacionProductos', 'ventasPorTipoCliente',
                'totalesFarmaceuticos', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en ventas farmacéuticas: ' . $e->getMessage());
        }
    }

    /**
     * Get tax analysis for sales
     */
    public function analisisImpuestos(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Análisis de IGV por tipo de documento
            $analisisIgv = DB::table('Doccab')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->where('Tipo', 1)
                ->where('Eliminado', 0)
                ->select([
                    DB::raw('COUNT(*) as cantidad_documentos'),
                    DB::raw('SUM(Subtotal) as total_subtotal'),
                    DB::raw('SUM(Igv) as total_igv'),
                    DB::raw('AVG(Igv/Subtotal*100) as porcentaje_igv_promedio')
                ])
                ->first();

            // IGV por tipo de cliente
            $igvPorTipoCliente = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->select([
                    'c.TipoClie',
                    DB::raw('COUNT(*) as documentos'),
                    DB::raw('SUM(CAST(dc.Igv as MONEY)) as igv_total'),
                    DB::raw('AVG(CAST(dc.Igv as MONEY)) as igv_promedio')
                ])
                ->groupBy('c.TipoClie')
                ->get();

            // Comparación con compras para determinar IGV por pagar
            $comprasIgv = DB::table('Doccab')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->where('Tipo', 2)
                ->where('Eliminado', 0)
                ->sum('Igv');

            $igvPorPagar = $analisisIgv->total_igv - $comprasIgv;

            // Resumen por moneda
            $resumenPorMoneda = DB::table('Doccab')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->where('Tipo', 1)
                ->where('Eliminado', 0)
                ->select([
                    'Moneda',
                    DB::raw('COUNT(*) as documentos'),
                    DB::raw('SUM(Igv) as igv_total')
                ])
                ->groupBy('Moneda')
                ->get();

            return view('contabilidad.registros.bancos', compact(
                'analisisIgv', 'igvPorTipoCliente', 'comprasIgv', 'igvPorPagar', 
                'resumenPorMoneda', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en análisis de impuestos: ' . $e->getMessage());
        }
    }

    /**
     * Export sales register for SUNAT
     */
    public function exportarSunat(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Formato para SUNAT (Registro de Ventas)
            $registroSunat = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1)
                ->where('dc.Eliminado', 0)
                ->select([
                    'dc.Fecha as FECHA_DOCUMENTO',
                    'dc.Fecha as FECHA_VENCIMIENTO',
                    'dc.Numero as NUMERO_DOCUMENTO',
                    'c.Documento as RUC_CLIENTE',
                    'c.Razon as RAZON_SOCIAL',
                    'dc.Subtotal as BASE_IMPONIBLE',
                    'dc.Igv as IGV',
                    'dc.Total as TOTAL',
                    DB::raw('CASE WHEN dc.Moneda = 1 THEN \'PEN\' ELSE \'USD\' END as MONEDA'),
                    'dc.Cambio as TIPO_CAMBIO'
                ])
                ->orderBy('dc.Fecha')
                ->orderBy('dc.Numero')
                ->get();

            // Aquí implementarías la generación del archivo para SUNAT
            return view('contabilidad.registros.caja', compact(
                'registroSunat', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar para SUNAT: ' . $e->getMessage());
        }
    }

    /**
     * Classify pharmaceutical products
     */
    private function clasificarProductosFarmaceuticos($productos)
    {
        $clasificacion = [
            'MEDICAMENTOS' => [],
            'EQUIPOS_MEDICOS' => [],
            'INSUMOS' => [],
            'OTROS' => []
        ];

        foreach ($productos as $producto) {
            $nombre = strtoupper($producto->Nombre);
            
            if (strpos($nombre, 'MEDICAMENTO') !== false || 
                strpos($nombre, 'CAPSULA') !== false || 
                strpos($nombre, 'TABLETA') !== false ||
                strpos($nombre, 'JARABE') !== false) {
                $clasificacion['MEDICAMENTOS'][] = $producto;
            } elseif (strpos($nombre, 'EQUIPO') !== false || 
                      strpos($nombre, 'TERMOMETRO') !== false ||
                      strpos($nombre, 'TENSIOMETRO') !== false) {
                $clasificacion['EQUIPOS_MEDICOS'][] = $producto;
            } elseif (strpos($nombre, 'GUANTE') !== false || 
                      strpos($nombre, 'JERINGA') !== false ||
                      strpos($nombre, 'ALCOHOL') !== false) {
                $clasificacion['INSUMOS'][] = $producto;
            } else {
                $clasificacion['OTROS'][] = $producto;
            }
        }

        return $clasificacion;
    }

    /**
     * Calculate average margin
     */
    private function calcularMargenPromedio($productos)
    {
        $márgenes = [];
        foreach ($productos as $producto) {
            if ($producto->precio_promedio > 0 && $producto->costo_promedio > 0) {
                $margen = (($producto->precio_promedio - $producto->costo_promedio) / $producto->precio_promedio) * 100;
                $márgenes[] = $margen;
            }
        }
        
        return count($márgenes) > 0 ? array_sum($márgenes) / count($márgenes) : 0;
    }

    /**
     * Project sales for the year
     */
    private function proyectarVentasAnio($anio, $tendenciasMensuales)
    {
        $ventasAcumuladas = 0;
        $mesesProyectados = 0;
        
        foreach ($tendenciasMensuales as $mes => $datos) {
            $fechaMes = Carbon::create($anio, $mes, 1);
            if ($fechaMes->lte(Carbon::now())) {
                $ventasAcumuladas += $datos['total'];
                $mesesProyectados++;
            }
        }
        
        $promedioMensual = $mesesProyectados > 0 ? $ventasAcumuladas / $mesesProyectados : 0;
        $mesesRestantes = 12 - $mesesProyectados;
        $proyeccionRestoAnio = $promedioMensual * $mesesRestantes;
        
        return [
            'ventas_acumuladas' => $ventasAcumuladas,
            'proyeccion_resto_ano' => $proyeccionRestoAnio,
            'proyeccion_total_ano' => $ventasAcumuladas + $proyeccionRestoAnio,
            'promedio_mensual' => $promedioMensual
        ];
    }
}