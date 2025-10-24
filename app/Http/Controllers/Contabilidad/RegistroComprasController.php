<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RegistroComprasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $proveedor = $request->input('proveedor');
            $tipoDoc = $request->input('tipo_documento');

            // Obtener registro de compras desde Doccab y Docdet
            $compras = DB::table('Doccab as dc')
                ->leftJoin('Docdet as dd', 'dc.Numero', '=', 'dd.Numero')
                ->leftJoin('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 2) // Tipo 2 = Compras
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
                    'c.Razon as proveedor',
                    'c.Documento as ruc_proveedor',
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
                ->where('dc.Tipo', 2)
                ->where('dc.Eliminado', 0)
                ->select([
                    DB::raw('COUNT(*) as cantidad'),
                    DB::raw('SUM(CAST(dc.Subtotal as MONEY)) as total_subtotal'),
                    DB::raw('SUM(CAST(dc.Igv as MONEY)) as total_igv'),
                    DB::raw('SUM(CAST(dc.Total as MONEY)) as total_total')
                ])
                ->first();

            // Top proveedores
            $topProveedores = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 2)
                ->where('dc.Eliminado', 0)
                ->select([
                    'c.Codclie',
                    'c.Razon as proveedor',
                    'c.Documento as ruc',
                    DB::raw('SUM(CAST(dc.Total as MONEY)) as total_compras'),
                    DB::raw('COUNT(*) as cantidad_documentos')
                ])
                ->groupBy('c.Codclie', 'c.Razon', 'c.Documento')
                ->orderBy('total_compras', 'desc')
                ->limit(10)
                ->get();

            // Totales generales
            $totales = DB::table('Doccab')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->where('Tipo', 2)
                ->where('Eliminado', 0)
                ->select([
                    DB::raw('SUM(Subtotal) as total_subtotal'),
                    DB::raw('SUM(Igv) as total_igv'),
                    DB::raw('SUM(Total) as total_general'),
                    DB::raw('COUNT(*) as total_documentos')
                ])
                ->first();

            return view('contabilidad.registros.compras', compact(
                'compras', 'fechaInicio', 'fechaFin', 'resumenDocumentos', 
                'topProveedores', 'totales', 'proveedor', 'tipoDoc'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el registro de compras: ' . $e->getMessage());
        }
    }

    /**
     * Get daily purchases summary
     */
    public function resumenDiario(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));

            // Compras del día
            $comprasDiarias = DB::table('Doccab')
                ->whereDate('Fecha', $fecha)
                ->where('Tipo', 2) // Compras
                ->where('Eliminado', 0)
                ->select([
                    'Numero',
                    'Fecha',
                    'Subtotal',
                    'Igv',
                    'Total',
                    'Moneda',
                    'CodClie'
                ])
                ->get();

            // Agrupar por moneda
            $resumenPorMoneda = $comprasDiarias->groupBy('Moneda')->map(function($group) {
                return [
                    'cantidad' => $group->count(),
                    'subtotal' => $group->sum('Subtotal'),
                    'igv' => $group->sum('Igv'),
                    'total' => $group->sum('Total')
                ];
            });

            // Proveedores del día
            $proveedoresDiarios = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereDate('dc.Fecha', $fecha)
                ->where('dc.Tipo', 2)
                ->where('dc.Eliminado', 0)
                ->select([
                    'dc.CodClie',
                    'c.Razon as proveedor',
                    'c.Documento as ruc',
                    DB::raw('SUM(CAST(dc.Total as MONEY)) as total'),
                    DB::raw('COUNT(*) as cantidad')
                ])
                ->groupBy('dc.CodClie', 'c.Razon', 'c.Documento')
                ->orderBy('total', 'desc')
                ->get();

            // Totales
            $totalesDiarios = [
                'fecha' => $fecha,
                'total_documentos' => $comprasDiarias->count(),
                'total_subtotal' => $comprasDiarias->sum('Subtotal'),
                'total_igv' => $comprasDiarias->sum('Igv'),
                'total_general' => $comprasDiarias->sum('Total')
            ];

            return view('contabilidad.registros.compras-diario', compact(
                'comprasDiarias', 'resumenPorMoneda', 'proveedoresDiarios', 'totalesDiarios', 'fecha'
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
                ->where('Tipo', 2)
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

            // Productos más comprados en el mes
            $productosTop = DB::table('Docdet as dd')
                ->join('Doccab as dc', 'dd.Numero', '=', 'dc.Numero')
                ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 2)
                ->where('dc.Eliminado', 0)
                ->select([
                    'p.CodPro',
                    'p.Nombre',
                    DB::raw('SUM(dd.Cantidad) as cantidad_total'),
                    DB::raw('SUM(CAST(dd.Subtotal as MONEY)) as total_compras')
                ])
                ->groupBy('p.CodPro', 'p.Nombre')
                ->orderBy('total_compras', 'desc')
                ->limit(10)
                ->get();

            // Totales del mes
            $totalesMes = DB::table('Doccab')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->where('Tipo', 2)
                ->where('Eliminado', 0)
                ->select([
                    DB::raw('COUNT(*) as total_documentos'),
                    DB::raw('SUM(Subtotal) as total_subtotal'),
                    DB::raw('SUM(Igv) as total_igv'),
                    DB::raw('SUM(Total) as total_general')
                ])
                ->first();

            return view('contabilidad.registros.compras-mensual', compact(
                'resumenDiario', 'productosTop', 'totalesMes', 'anio', 'mes'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar resumen mensual: ' . $e->getMessage());
        }
    }

    /**
     * Get purchase analysis by provider
     */
    public function porProveedor(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Análisis por proveedor
            $analisisProveedores = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 2)
                ->where('dc.Eliminado', 0)
                ->select([
                    'c.Codclie',
                    'c.Razon as proveedor',
                    'c.Documento as ruc',
                    'c.Direccion',
                    'c.Telefono1',
                    DB::raw('COUNT(DISTINCT dc.Numero) as cantidad_documentos'),
                    DB::raw('SUM(CAST(dc.Subtotal as MONEY)) as total_subtotal'),
                    DB::raw('SUM(CAST(dc.Igv as MONEY)) as total_igv'),
                    DB::raw('SUM(CAST(dc.Total as MONEY)) as total_compras'),
                    DB::raw('MIN(dc.Fecha) as primera_compra'),
                    DB::raw('MAX(dc.Fecha) as ultima_compra')
                ])
                ->groupBy('c.Codclie', 'c.Razon', 'c.Documento', 'c.Direccion', 'c.Telefono1')
                ->orderBy('total_compras', 'desc')
                ->get();

            // Calcular estadísticas por proveedor
            foreach ($analisisProveedores as $proveedor) {
                // Promedio por compra
                $proveedor->promedio_por_compra = $proveedor->cantidad_documentos > 0 ? 
                    $proveedor->total_compras / $proveedor->cantidad_documentos : 0;
                
                // Días desde última compra
                $proveedor->dias_ultima_compra = Carbon::parse($proveedor->ultima_compra)
                    ->diffInDays(Carbon::now());
            }

            // Totales generales
            $totalesGenerales = [
                'total_proveedores' => $analisisProveedores->count(),
                'total_compras' => $analisisProveedores->sum('total_compras'),
                'total_documentos' => $analisisProveedores->sum('cantidad_documentos'),
                'promedio_por_proveedor' => $analisisProveedores->count() > 0 ?
                    $analisisProveedores->sum('total_compras') / $analisisProveedores->count() : 0
            ];

            return view('contabilidad.registros.compras-proveedores', compact(
                'analisisProveedores', 'totalesGenerales', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al analizar proveedores: ' . $e->getMessage());
        }
    }

    /**
     * Get purchase trends
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
                    ->where('Tipo', 2)
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
                    ->where('Tipo', 2)
                    ->where('Eliminado', 0)
                    ->sum('Total');

                $tendenciasAnioAnterior[$mes] = $totalesAnterior;
            }

            // Calcular crecimiento
            foreach ($tendenciasMensuales as $mes => &$datos) {
                $datos['crecimiento'] = $tendenciasAnioAnterior[$mes] > 0 ?
                    (($datos['total'] - $tendenciasAnioAnterior[$mes]) / $tendenciasAnioAnterior[$mes]) * 100 : 0;
            }

            return view('contabilidad.registros.compras-tendencias', compact(
                'tendenciasMensuales', 'anio', 'anioAnterior'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al analizar tendencias: ' . $e->getMessage());
        }
    }

    /**
     * Get pharmacy-specific purchases (medicamentos, equipos, etc.)
     */
    public function farmaceuticas(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Compras farmacéuticas específicas
            $comprasFarmaceuticas = DB::table('Docdet as dd')
                ->join('Doccab as dc', 'dd.Numero', '=', 'dc.Numero')
                ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 2)
                ->where('dc.Eliminado', 0)
                ->whereNotNull('p.RegSanit') // Productos con registro sanitario
                ->select([
                    'p.CodPro',
                    'p.Nombre',
                    'p.RegSanit',
                    'p.Coddigemin',
                    'p.Principio',
                    'l.Descripcion as laboratorio',
                    DB::raw('SUM(dd.Cantidad) as cantidad_total'),
                    DB::raw('SUM(CAST(dd.Subtotal as MONEY)) as total_compras'),
                    DB::raw('AVG(CAST(dd.Precio as MONEY)) as precio_promedio')
                ])
                ->groupBy('p.CodPro', 'p.Nombre', 'p.RegSanit', 'p.Coddigemin', 'p.Principio', 'l.Descripcion')
                ->orderBy('total_compras', 'desc')
                ->get();

            // Clasificar por tipo de producto
            $clasificacionProductos = $this->clasificarProductosFarmaceuticos($comprasFarmaceuticas);

            // Control de proveedores farmacéuticos
            $proveedoresFarmaceuticos = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->leftJoin('Docdet as dd', 'dc.Numero', '=', 'dd.Numero')
                ->leftJoin('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 2)
                ->where('dc.Eliminado', 0)
                ->whereNotNull('p.RegSanit')
                ->select([
                    'c.Codclie',
                    'c.Razon as proveedor',
                    'c.Documento as ruc',
                    DB::raw('COUNT(DISTINCT p.CodPro) as productos_diferentes'),
                    DB::raw('SUM(CAST(dd.Subtotal as MONEY)) as total_compras'),
                    DB::raw('SUM(dd.Cantidad) as cantidad_total')
                ])
                ->groupBy('c.Codclie', 'c.Razon', 'c.Documento')
                ->orderBy('total_compras', 'desc')
                ->get();

            // Totales
            $totalesFarmaceuticos = [
                'total_productos' => $comprasFarmaceuticas->count(),
                'total_compras' => $comprasFarmaceuticas->sum('total_compras'),
                'total_cantidad' => $comprasFarmaceuticas->sum('cantidad_total'),
                'proveedores_especializados' => $proveedoresFarmaceuticos->count()
            ];

            return view('contabilidad.registros.compras-farmaceuticas', compact(
                'comprasFarmaceuticas', 'clasificacionProductos', 'proveedoresFarmaceuticos',
                'totalesFarmaceuticos', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en compras farmacéuticas: ' . $e->getMessage());
        }
    }

    /**
     * Get IGv analysis for purchases
     */
    public function analisisIgv(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Análisis de IGV por tipo de documento
            $analisisIgv = DB::table('Doccab')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->where('Tipo', 2)
                ->where('Eliminado', 0)
                ->select([
                    DB::raw('COUNT(*) as cantidad_documentos'),
                    DB::raw('SUM(Subtotal) as total_subtotal'),
                    DB::raw('SUM(Igv) as total_igv'),
                    DB::raw('AVG(Igv/Subtotal*100) as porcentaje_igv_promedio')
                ])
                ->first();

            // IGV acreditable vs no acreditable
            $igvDetalle = DB::table('Doccab')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->where('Tipo', 2)
                ->where('Eliminado', 0)
                ->select([
                    'Moneda',
                    DB::raw('COUNT(*) as documentos'),
                    DB::raw('SUM(Igv) as igv_total')
                ])
                ->groupBy('Moneda')
                ->get();

            // Comparación con ventas para determinar IGV por pagar
            $ventasIgv = DB::table('Doccab')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->where('Tipo', 1)
                ->where('Eliminado', 0)
                ->sum('Igv');

            $igvPorPagar = $analisisIgv->total_igv - $ventasIgv;

            return view('contabilidad.registros.compras-igv', compact(
                'analisisIgv', 'igvDetalle', 'ventasIgv', 'igvPorPagar', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en análisis de IGV: ' . $e->getMessage());
        }
    }

    /**
     * Export purchase register for SUNAT
     */
    public function exportarSunat(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Formato para SUNAT (Registro de Compras)
            $registroSunat = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 2)
                ->where('dc.Eliminado', 0)
                ->select([
                    'dc.Fecha as FECHA_DOCUMENTO',
                    'dc.Fecha as FECHA_VENCIMIENTO',
                    'dc.Numero as NUMERO_DOCUMENTO',
                    'c.Documento as RUC_PROVEEDOR',
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
            return view('contabilidad.registros.compras-exportar', compact(
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
}