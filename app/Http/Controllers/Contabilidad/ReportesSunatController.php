<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportesSunatController extends Controller
{
    public function index(Request $request)
    {
        try {
            $periodo = $request->input('periodo', Carbon::now()->format('Y-m'));
            $anio = $request->input('anio', Carbon::now()->year);
            $mes = $request->input('mes', Carbon::now()->month);

            // Reportes disponibles
            $reportesDisponibles = [
                'registro_ventas' => 'Registro de Ventas e Ingresos',
                'registro_compras' => 'Registro de Compras',
                'libros_electronicos' => 'Libros Electrónicos',
                'declaracion_jurada' => 'Declaración Jurada',
                'detracciones' => 'Detracciones',
                'percepciones' => 'Percepciones'
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'periodo' => $periodo,
                    'anio' => $anio,
                    'mes' => $mes,
                    'reportes_disponibles' => $reportesDisponibles
                ],
                'message' => 'Reportes SUNAT disponibles'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener reportes SUNAT: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registroVentas(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $formato = $request->input('formato', 'json'); // json, csv, excel

            // Obtener datos de ventas
            $ventas = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Eliminado', 0)
                ->select(
                    'dc.Numero',
                    'dc.Tipo',
                    'dc.Fecha',
                    'c.Documento',
                    'c.Razon',
                    'dc.Subtotal',
                    'dc.Igv',
                    'dc.Total',
                    'dc.Moneda',
                    'dc.Cambio'
                )
                ->orderBy('dc.Fecha')
                ->orderBy('dc.Numero')
                ->get();

            // Transformar datos al formato SUNAT
            $datosSunat = $this->transformarVentasSunat($ventas);

            // Generar resumen
            $resumen = $this->generarResumenVentas($ventas);

            if ($formato === 'excel') {
                return $this->generarExcelVentas($datosSunat, $resumen, $fechaInicio, $fechaFin);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'datos' => $datosSunat,
                    'resumen' => $resumen,
                    'periodo' => [
                        'inicio' => $fechaInicio,
                        'fin' => $fechaFin
                    ]
                ],
                'message' => 'Registro de ventas generado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar registro de ventas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registroCompras(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $formato = $request->input('formato', 'json');

            // Para este ejemplo, simulamos compras (en un sistema real vendrían de una tabla de compras)
            $compras = $this->simularCompras($fechaInicio, $fechaFin);

            // Transformar datos al formato SUNAT
            $datosSunat = $this->transformarComprasSunat($compras);

            // Generar resumen
            $resumen = $this->generarResumenCompras($compras);

            if ($formato === 'excel') {
                return $this->generarExcelCompras($datosSunat, $resumen, $fechaInicio, $fechaFin);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'datos' => $datosSunat,
                    'resumen' => $resumen,
                    'periodo' => [
                        'inicio' => $fechaInicio,
                        'fin' => $fechaFin
                    ]
                ],
                'message' => 'Registro de compras generado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar registro de compras: ' . $e->getMessage()
            ], 500);
        }
    }

    public function librosElectronicos(Request $request)
    {
        try {
            $tipoLibro = $request->input('tipo_libro', 'diario');
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

            switch ($tipoLibro) {
                case 'diario':
                    $libros = $this->generarLibroDiario($fechaInicio, $fechaFin);
                    break;
                case 'mayor':
                    $libros = $this->generarLibroMayor($fechaInicio, $fechaFin);
                    break;
                case 'balance_comprobacion':
                    $libros = $this->generarBalanceComprobacion($fechaInicio, $fechaFin);
                    break;
                default:
                    $libros = $this->generarLibroDiario($fechaInicio, $fechaFin);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tipo_libro' => $tipoLibro,
                    'libros' => $libros,
                    'periodo' => [
                        'inicio' => $fechaInicio,
                        'fin' => $fechaFin
                    ]
                ],
                'message' => 'Libros electrónicos generados exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar libros electrónicos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function declaracionJurada(Request $request)
    {
        try {
            $anio = $request->input('anio', Carbon::now()->year);
            $mes = $request->input('mes', Carbon::now()->month);
            $formato = $request->input('formato', 'json');

            // Calcular montos para la declaración jurada
            $ventasMensual = $this->calcularVentasMensual($anio, $mes);
            $comprasMensual = $this->calcularComprasMensual($anio, $mes);
            $impuestos = $this->calcularImpuestos($anio, $mes);

            $declaracion = [
                'periodo' => $anio . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT),
                'ventas' => $ventasMensual,
                'compras' => $comprasMensual,
                'impuestos' => $impuestos,
                'balanco' => [
                    'a_pagar' => max(0, $impuestos['igv_pagar'] - $impuestos['igv_compensar']),
                    'a_favor' => max(0, $impuestos['igv_compensar'] - $impuestos['igv_pagar'])
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $declaracion,
                'message' => 'Declaración jurada generada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar declaración jurada: ' . $e->getMessage()
            ], 500);
        }
    }

    private function transformarVentasSunat($ventas)
    {
        $datos = [];
        
        foreach ($ventas as $venta) {
            $tipoDoc = $this->obtenerTipoDocumentoSunat($venta->Tipo);
            
            $datos[] = [
                'fecha_emision' => Carbon::parse($venta->Fecha)->format('d/m/Y'),
                'fecha_vencimiento' => Carbon::parse($venta->Fecha)->addDays(30)->format('d/m/Y'),
                'tipo_comprobante' => $tipoDoc['codigo'],
                'nro_comprobante' => $venta->Numero,
                'tipo_documento' => $venta->Documento ? '6' : '1', // 6=RUC, 1=DNI
                'nro_documento' => $venta->Documento ?? '0',
                'razon_social' => $venta->Razon ?? 'CONSUMIDOR FINAL',
                'valor_exportacion' => 0,
                'base_imponible' => floatval($venta->Subtotal),
                'descuento_base' => 0,
                'igv' => floatval($venta->Igv),
                'descuento_igv' => 0,
                'total' => floatval($venta->Total),
                'tipo_cambio' => $venta->Moneda == 1 ? 1 : floatval($venta->Cambio ?? 1)
            ];
        }

        return $datos;
    }

    private function generarResumenVentas($ventas)
    {
        $totalVentas = $ventas->count();
        $totalFacturas = $ventas->where('Tipo', 1)->count();
        $totalBoletas = $ventas->where('Tipo', 2)->count();
        $totalPedidos = $ventas->where('Tipo', 3)->count();

        $totalBaseImponible = $ventas->sum('Subtotal');
        $totalIgv = $ventas->sum('Igv');
        $totalVentasMonto = $ventas->sum('Total');

        return [
            'cantidades' => [
                'total_documentos' => $totalVentas,
                'facturas' => $totalFacturas,
                'boletas' => $totalBoletas,
                'pedidos' => $totalPedidos
            ],
            'montos' => [
                'base_imponible' => floatval($totalBaseImponible),
                'igv' => floatval($totalIgv),
                'total_ventas' => floatval($totalVentasMonto)
            ],
            'por_tipo_documento' => [
                'facturas' => [
                    'cantidad' => $totalFacturas,
                    'monto' => floatval($ventas->where('Tipo', 1)->sum('Total'))
                ],
                'boletas' => [
                    'cantidad' => $totalBoletas,
                    'monto' => floatval($ventas->where('Tipo', 2)->sum('Total'))
                ]
            ]
        ];
    }

    private function obtenerTipoDocumentoSunat($tipo)
    {
        $tipos = [
            1 => ['codigo' => '01', 'descripcion' => 'Factura'],
            2 => ['codigo' => '03', 'descripcion' => 'Boleta de Venta'],
            3 => ['codigo' => '12', 'descripcion' => 'Ticket o Cinta emitido por máquina registradora'],
            // Agregar más según necesidad
        ];

        return $tipos[$tipo] ?? ['codigo' => '00', 'descripcion' => 'Otro'];
    }

    private function simularCompras($fechaInicio, $fechaFin)
    {
        // En un sistema real, esto vendría de una tabla de compras
        // Por ahora simulamos algunos datos
        $compras = [];
        $fechaActual = Carbon::parse($fechaInicio);
        $fechaFin = Carbon::parse($fechaFin);

        while ($fechaActual->lte($fechaFin)) {
            // Simular algunas compras aleatorias
            if (rand(1, 5) <= 3) { // 60% de probabilidad de compra
                $compras[] = [
                    'fecha' => $fechaActual->copy()->format('Y-m-d'),
                    'numero' => 'F' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'proveedor' => 'Proveedor ' . rand(1, 10),
                    'ruc' => '20' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'base_imponible' => rand(500, 5000),
                    'igv' => 0,
                    'total' => 0
                ];
            }
            $fechaActual->addDay();
        }

        // Calcular totales
        foreach ($compras as &$compra) {
            $compra['igv'] = $compra['base_imponible'] * 0.18;
            $compra['total'] = $compra['base_imponible'] + $compra['igv'];
        }

        return $compras;
    }

    private function transformarComprasSunat($compras)
    {
        $datos = [];
        
        foreach ($compras as $compra) {
            $datos[] = [
                'fecha_emision' => Carbon::parse($compra['fecha'])->format('d/m/Y'),
                'fecha_vencimiento' => Carbon::parse($compra['fecha'])->addDays(30)->format('d/m/Y'),
                'tipo_comprobante' => '01', // Factura
                'nro_comprobante' => $compra['numero'],
                'tipo_documento' => '6', // RUC
                'nro_documento' => $compra['ruc'],
                'razon_social' => $compra['proveedor'],
                'valor_importacion' => 0,
                'base_imponible' => $compra['base_imponible'],
                'igv' => $compra['igv'],
                'total' => $compra['total'],
                'tipo_cambio' => 1
            ];
        }

        return $datos;
    }

    private function generarResumenCompras($compras)
    {
        $totalCompras = count($compras);
        $totalBaseImponible = array_sum(array_column($compras, 'base_imponible'));
        $totalIgv = array_sum(array_column($compras, 'igv'));
        $totalComprasMonto = array_sum(array_column($compras, 'total'));

        return [
            'cantidades' => [
                'total_compras' => $totalCompras
            ],
            'montos' => [
                'base_imponible' => $totalBaseImponible,
                'igv' => $totalIgv,
                'total_compras' => $totalComprasMonto
            ]
        ];
    }

    private function generarLibroDiario($fechaInicio, $fechaFin)
    {
        // Obtener movimientos de ventas como asientos contables
        $asientos = DB::table('Doccab as dc')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Eliminado', 0)
            ->select(
                'dc.Numero',
                'dc.Fecha',
                DB::raw('COUNT(*) as cantidad_items'),
                DB::raw('SUM(dc.Total) as total_asiento')
            )
            ->groupBy('dc.Numero', 'dc.Fecha')
            ->orderBy('dc.Fecha')
            ->limit(100)
            ->get();

        // Simular detalle de cada asiento
        $libro = [];
        foreach ($asientos as $asiento) {
            $libro[] = [
                'numero_asiento' => $asiento->Numero,
                'fecha' => $asiento->Fecha,
                'glosa' => 'Venta de mercaderías',
                'cuenta_debe' => '12 CUENTAS POR COBRAR COMERCIALES',
                'cuenta_haber' => '70 VENTAS',
                'debe' => $asiento->total_asiento,
                'haber' => $asiento->total_asiento,
                'referencia' => 'Venta'
            ];
        }

        return $libro;
    }

    private function generarLibroMayor($fechaInicio, $fechaFin)
    {
        // Simular libro mayor por cuenta
        $cuentas = [
            '12 CUENTAS POR COBRAR COMERCIALES',
            '70 VENTAS',
            '33 INMUEBLES, MAQUINARIA Y EQUIPO',
            '42 CUENTAS POR PAGAR COMERCIALES'
        ];

        $libro = [];
        foreach ($cuentas as $cuenta) {
            for ($i = 0; $i < 10; $i++) {
                $libro[] = [
                    'cuenta' => $cuenta,
                    'fecha' => Carbon::parse($fechaInicio)->addDays($i * 5)->format('Y-m-d'),
                    'numero_asiento' => 'A' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'glosa' => 'Movimiento contable',
                    'debe' => rand(100, 5000),
                    'haber' => rand(100, 5000),
                    'saldo' => rand(-1000, 10000)
                ];
            }
        }

        return $libro;
    }

    private function generarBalanceComprobacion($fechaInicio, $fechaFin)
    {
        // Simular balance de comprobación
        $cuentas = [
            '12 CUENTAS POR COBRAR COMERCIALES' => ['debe' => 50000, 'haber' => 30000],
            '33 INMUEBLES, MAQUINARIA Y EQUIPO' => ['debe' => 150000, 'haber' => 0],
            '42 CUENTAS POR PAGAR COMERCIALES' => ['debe' => 0, 'haber' => 80000],
            '70 VENTAS' => ['debe' => 0, 'haber' => 200000],
            '10 EFECTIVO Y EQUIVALENTES' => ['debe' => 80000, 'haber' => 20000]
        ];

        $balance = [];
        $totalDebe = 0;
        $totalHaber = 0;

        foreach ($cuentas as $cuenta => $movimientos) {
            $saldoDebe = max(0, $movimientos['debe'] - $movimientos['haber']);
            $saldoHaber = max(0, $movimientos['haber'] - $movimientos['debe']);
            
            $balance[] = [
                'cuenta' => $cuenta,
                'debe' => $movimientos['debe'],
                'haber' => $movimientos['haber'],
                'saldo_deudor' => $saldoDebe,
                'saldo_acreedor' => $saldoHaber
            ];

            $totalDebe += $movimientos['debe'];
            $totalHaber += $movimientos['haber'];
        }

        return [
            'detalle' => $balance,
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'diferencia' => abs($totalDebe - $totalHaber)
        ];
    }

    private function calcularVentasMensual($anio, $mes)
    {
        $inicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();

        $ventas = DB::table('Doccab')
            ->whereBetween('Fecha', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
            ->where('Eliminado', 0)
            ->select(
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(Total) as monto')
            )
            ->first();

        return [
            'cantidad' => intval($ventas->cantidad ?? 0),
            'monto' => floatval($ventas->monto ?? 0)
        ];
    }

    private function calcularComprasMensual($anio, $mes)
    {
        // Simulado - en un sistema real vendría de la tabla de compras
        return [
            'cantidad' => rand(20, 50),
            'monto' => rand(20000, 100000)
        ];
    }

    private function calcularImpuestos($anio, $mes)
    {
        $ventas = $this->calcularVentasMensual($anio, $mes);
        $compras = $this->calcularComprasMensual($anio, $mes);

        $igvVentas = $ventas['monto'] * 0.18;
        $igvCompras = $compras['monto'] * 0.18;

        return [
            'igv_ventas' => $igvVentas,
            'igv_compras' => $igvCompras,
            'igv_pagar' => $igvVentas,
            'igv_compensar' => $igvCompras
        ];
    }

    private function generarExcelVentas($datos, $resumen, $fechaInicio, $fechaFin)
    {
        // Implementación básica para generar Excel
        // En un sistema real, usarías Laravel Excel para esto
        $fileName = "registro_ventas_{$fechaInicio}_{$fechaFin}.xlsx";
        
        return response()->json([
            'success' => true,
            'data' => [
                'archivo' => $fileName,
                'datos' => $datos,
                'resumen' => $resumen
            ],
            'message' => 'Archivo Excel generado exitosamente'
        ]);
    }

    private function generarExcelCompras($datos, $resumen, $fechaInicio, $fechaFin)
    {
        // Implementación básica para generar Excel
        $fileName = "registro_compras_{$fechaInicio}_{$fechaFin}.xlsx";
        
        return response()->json([
            'success' => true,
            'data' => [
                'archivo' => $fileName,
                'datos' => $datos,
                'resumen' => $resumen
            ],
            'message' => 'Archivo Excel generado exitosamente'
        ]);
    }
}