<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LibroClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));
            $cliente = $request->input('cliente');

            // Obtener información de clientes con sus cuentas por cobrar
            $query = DB::table('Clientes as c')
                ->leftJoin('t_Clientes_ubigeo as ub', 'c.Codclie', '=', 'ub.CODIGO')
                ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona')
                ->leftJoin('Empleados as e', 'c.Vendedor', '=', 'e.Codemp');

            if ($cliente) {
                $query->where(function($q) use ($cliente) {
                    $q->where('c.Razon', 'like', "%$cliente%")
                      ->orWhere('c.Documento', 'like', "%$cliente%");
                });
            }

            $clientes = $query->select([
                'c.Codclie',
                'c.Razon',
                'c.Documento',
                'c.Direccion',
                'c.Telefono1',
                'c.Telefono2',
                'c.Celular',
                'c.Email',
                'c.Zona',
                'z.Descripcion as zona_nombre',
                'c.TipoNeg',
                'c.TipoClie',
                'c.Vendedor',
                'e.Nombre as vendedor_nombre',
                'c.Limite',
                'c.Activo',
                'c.Fecha as fecha_registro'
            ])
            ->orderBy('c.Razon')
            ->paginate(50);

            // Obtener saldos por cliente
            $saldosPorCliente = [];
            foreach ($clientes as $clienteItem) {
                $saldosPorCliente[$clienteItem->Codclie] = $this->obtenerSaldosCliente($clienteItem->Codclie);
            }

            // Resumen general
            $resumenGeneral = [
                'total_clientes' => DB::table('Clientes')->count(),
                'clientes_activos' => DB::table('Clientes')->where('Activo', 1)->count(),
                'total_cartera' => $this->obtenerTotalCartera(),
                'clientes_con_deuda' => $this->obtenerClientesConDeuda(),
                'mayor_deudor' => $this->obtenerMayorDeudor()
            ];

            return view('contabilidad.auxiliares.clientes', compact(
                'clientes', 'saldosPorCliente', 'resumenGeneral', 'fechaInicio', 'fechaFin', 'cliente'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el libro de clientes: ' . $e->getMessage());
        }
    }

    /**
     * Get detailed account statement for a customer
     */
    public function estadoCuenta($clienteId, Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Información del cliente
            $cliente = DB::table('Clientes as c')
                ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona')
                ->leftJoin('Empleados as e', 'c.Vendedor', '=', 'e.Codemp')
                ->where('c.Codclie', $clienteId)
                ->select([
                    'c.Codclie',
                    'c.Razon',
                    'c.Documento',
                    'c.Direccion',
                    'c.Telefono1',
                    'c.Email',
                    'c.Zona',
                    'z.Descripcion as zona_nombre',
                    'e.Nombre as vendedor_nombre',
                    'c.Limite'
                ])
                ->first();

            if (!$cliente) {
                return redirect()->route('libro-clientes')->with('error', 'Cliente no encontrado');
            }

            // Movimientos del cliente (facturas)
            $facturas = DB::table('Doccab as dc')
                ->where('dc.CodClie', $clienteId)
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    'dc.Numero',
                    'dc.Fecha',
                    'dc.FechaV',
                    'dc.Subtotal',
                    'dc.Igv',
                    'dc.Total',
                    'dc.Moneda',
                    'dc.Cambio',
                    DB::raw('CASE WHEN dc.Moneda = 1 THEN dc.Total ELSE dc.Total * dc.Cambio END as total_soles')
                ])
                ->orderBy('dc.Fecha', 'desc')
                ->get();

            // Pagos recibidos
            $pagos = DB::table('PlanD_cobranza as pd')
                ->leftJoin('PlanC_cobranza as pc', function($join) {
                    $join->on('pd.Serie', '=', 'pc.Serie')
                         ->on('pd.Numero', '=', 'pc.Numero');
                })
                ->where('pd.CodClie', $clienteId)
                ->whereBetween('pc.FechaCrea', [$fechaInicio, $fechaFin])
                ->select([
                    'pd.Serie',
                    'pd.Numero',
                    'pd.Documento',
                    'pd.FechaFac',
                    'pc.FechaCrea as fecha_pago',
                    'pd.Efectivo',
                    'pd.Cheque',
                    'pd.Descuento',
                    DB::raw('(ISNULL(pd.Efectivo, 0) + ISNULL(pd.Cheque, 0) + ISNULL(pd.Descuento, 0)) as total_pagado')
                ])
                ->orderBy('pc.FechaCrea', 'desc')
                ->get();

            // Saldos actuales
            $saldoPendiente = DB::table('CtaCliente')
                ->where('CodClie', $clienteId)
                ->where('Saldo', '>', 0)
                ->select([
                    'Documento',
                    'FechaF',
                    'FechaV',
                    'Importe',
                    'Saldo'
                ])
                ->get();

            // Cálculo de antigüedad de la deuda
            foreach ($saldoPendiente as $saldo) {
                $saldo->dias_vencido = Carbon::parse($saldo->FechaV)->diffInDays(Carbon::now(), false);
                $saldo->clasificacion_edad = $this->clasificarEdadDeuda($saldo->dias_vencido);
            }

            // Totales
            $totales = [
                'total_facturado' => $facturas->sum('total_soles'),
                'total_pagado' => $pagos->sum('total_pagado'),
                'saldo_pendiente' => $saldoPendiente->sum('Saldo'),
                'facturas_pendientes' => $saldoPendiente->count()
            ];

            // Clasificación de cartera
            $clasificacionCartera = [
                'VIGENTE' => $saldoPendiente->where('dias_vencido', '<=', 0)->sum('Saldo'),
                '1_30_DIAS' => $saldoPendiente->whereBetween('dias_vencido', [1, 30])->sum('Saldo'),
                '31_60_DIAS' => $saldoPendiente->whereBetween('dias_vencido', [31, 60])->sum('Saldo'),
                '61_90_DIAS' => $saldoPendiente->whereBetween('dias_vencido', [61, 90])->sum('Saldo'),
                'MAS_90_DIAS' => $saldoPendiente->where('dias_vencido', '>', 90)->sum('Saldo')
            ];

            return view('contabilidad.auxiliares.clientes-estado-cuenta', compact(
                'cliente', 'facturas', 'pagos', 'saldoPendiente', 'totales', 
                'clasificacionCartera', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar estado de cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Get aging report of accounts receivable
     */
    public function clasificacionEdad(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));

            // Obtener todos los saldos pendientes
            $saldosPendientes = DB::table('CtaCliente as cc')
                ->leftJoin('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
                ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona')
                ->leftJoin('Empleados as e', 'c.Vendedor', '=', 'e.Codemp')
                ->where('cc.Saldo', '>', 0)
                ->select([
                    'cc.Documento',
                    'cc.CodClie',
                    'c.Razon',
                    'c.Documento as ruc',
                    'c.Zona',
                    'z.Descripcion as zona_nombre',
                    'c.Vendedor',
                    'e.Nombre as vendedor_nombre',
                    'cc.FechaF',
                    'cc.FechaV',
                    'cc.Importe',
                    'cc.Saldo'
                ])
                ->get();

            // Clasificar por edad
            $clasificacionEdad = [];
            $totalCartera = 0;

            foreach ($saldosPendientes as $saldo) {
                $diasVencido = Carbon::parse($saldo->FechaV)->diffInDays(Carbon::now(), false);
                $saldo->dias_vencido = $diasVencido;
                
                if ($diasVencido <= 0) {
                    $clasificacionEdad['VIGENTE'][] = $saldo;
                } elseif ($diasVencido <= 30) {
                    $clasificacionEdad['1_30_DIAS'][] = $saldo;
                } elseif ($diasVencido <= 60) {
                    $clasificacionEdad['31_60_DIAS'][] = $saldo;
                } elseif ($diasVencido <= 90) {
                    $clasificacionEdad['61_90_DIAS'][] = $saldo;
                } else {
                    $clasificacionEdad['MAS_90_DIAS'][] = $saldo;
                }
                
                $totalCartera += $saldo->Saldo;
            }

            // Calcular totales por clasificación
            $totalesClasificacion = [];
            foreach ($clasificacionEdad as $categoria => $saldos) {
                $totalesClasificacion[$categoria] = [
                    'cantidad_clientes' => count(array_unique(array_column($saldos, 'CodClie'))),
                    'cantidad_documentos' => count($saldos),
                    'saldo_total' => array_sum(array_column($saldos, 'Saldo')),
                    'porcentaje' => $totalCartera > 0 ? (array_sum(array_column($saldos, 'Saldo')) / $totalCartera) * 100 : 0
                ];
            }

            // Clientes críticos (más de 60 días)
            $clientesCriticos = collect($clasificacionEdad['MAS_90_DIAS'] ?? [])
                ->merge($clasificacionEdad['61_90_DIAS'] ?? [])
                ->sortByDesc('Saldo')
                ->take(20);

            return view('contabilidad.auxiliares.clientes-clasificacion-edad', compact(
                'clasificacionEdad', 'totalesClasificacion', 'totalCartera', 
                'clientesCriticos', 'fecha'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en clasificación de edad: ' . $e->getMessage());
        }
    }

    /**
     * Get customer analysis by zone
     */
    public function analisisPorZona(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Análisis por zona
            $analisisZonas = DB::table('Clientes as c')
                ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona')
                ->leftJoin('Empleados as e', 'c.Vendedor', '=', 'e.Codemp')
                ->select([
                    'c.Zona',
                    'z.Descripcion as zona_nombre',
                    'c.Vendedor',
                    'e.Nombre as vendedor_nombre',
                    DB::raw('COUNT(*) as total_clientes'),
                    DB::raw('COUNT(CASE WHEN c.Activo = 1 THEN 1 END) as clientes_activos'),
                    DB::raw('SUM(CAST(c.Limite as MONEY)) as limite_total')
                ])
                ->groupBy('c.Zona', 'z.Descripcion', 'c.Vendedor', 'e.Nombre')
                ->orderBy('c.Zona')
                ->get();

            // Obtener cartera por zona
            foreach ($analisisZonas as $zona) {
                $zona->cartera_vencida = $this->obtenerCarteraVencidaZona($zona->Zona, $fechaInicio, $fechaFin);
                $zona->ventas_periodo = $this->obtenerVentasZona($zona->Zona, $fechaInicio, $fechaFin);
            }

            // Resumen de desempeño por zona
            $resumenZonas = [
                'total_zonas' => $analisisZonas->unique('Zona')->count(),
                'total_vendedores' => $analisisZonas->unique('Vendedor')->count(),
                'mejor_zona_ventas' => $analisisZonas->sortByDesc('ventas_periodo')->first(),
                'zona_mayor_cartera' => $analisisZonas->sortByDesc('cartera_vencida')->first()
            ];

            return view('contabilidad.auxiliares.clientes-analisis-zona', compact(
                'analisisZonas', 'resumenZonas', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en análisis por zona: ' . $e->getMessage());
        }
    }

    /**
     * Get customer payment behavior analysis
     */
    public function comportamientoPago(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Analizar comportamiento de pago
            $comportamientoPago = DB::table('CtaCliente as cc')
                ->leftJoin('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
                ->leftJoin('Doccab as dc', 'cc.Documento', '=', 'dc.Numero')
                ->whereBetween('cc.FechaF', [$fechaInicio, $fechaFin])
                ->select([
                    'cc.CodClie',
                    'c.Razon',
                    'cc.Documento',
                    'cc.FechaF',
                    'cc.FechaV',
                    'cc.Importe',
                    'cc.Saldo',
                    'dc.Fecha as fecha_facturacion'
                ])
                ->get();

            // Calcular días de pago promedio por cliente
            $diasPagoPorCliente = [];
            foreach ($comportamientoPago->groupBy('CodClie') as $clienteId => $documentos) {
                $diasPago = [];
                foreach ($documentos as $doc) {
                    if ($doc->Saldo <= 0) { // Documento pagado
                        $dias_pago = Carbon::parse($doc->fecha_facturacion)
                            ->diffInDays(Carbon::now()); // Días hasta ahora (asumiendo pago)
                        $diasPago[] = $dias_pago;
                    }
                }
                
                if (count($diasPago) > 0) {
                    $diasPagoPorCliente[$clienteId] = [
                        'cliente' => $documentos->first()->Razon,
                        'promedio_dias_pago' => array_sum($diasPago) / count($diasPago),
                        'documentos_pagados' => count($diasPago)
                    ];
                }
            }

            // Clasificar comportamiento
            $clasificacionComportamiento = [
                'PUNTUAL' => [], // 0-15 días
                'NORMAL' => [], // 16-30 días
                'TARDIO' => [], // 31-45 días
                'MOROSO' => [] // >45 días
            ];

            foreach ($diasPagoPorCliente as $cliente) {
                if ($cliente['promedio_dias_pago'] <= 15) {
                    $clasificacionComportamiento['PUNTUAL'][] = $cliente;
                } elseif ($cliente['promedio_dias_pago'] <= 30) {
                    $clasificacionComportamiento['NORMAL'][] = $cliente;
                } elseif ($cliente['promedio_dias_pago'] <= 45) {
                    $clasificacionComportamiento['TARDIO'][] = $cliente;
                } else {
                    $clasificacionComportamiento['MOROSO'][] = $cliente;
                }
            }

            return view('contabilidad.auxiliares.clientes-comportamiento', compact(
                'diasPagoPorCliente', 'clasificacionComportamiento', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en análisis de comportamiento: ' . $e->getMessage());
        }
    }

    /**
     * Get customer profitability analysis
     */
    public function rentabilidad(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Calcular rentabilidad por cliente
            $rentabilidadClientes = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->leftJoin('Docdet as dd', 'dc.Numero', '=', 'dd.Numero')
                ->leftJoin('Empleados as e', 'dc.Vendedor', '=', 'e.Codemp')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 1) // Ventas
                ->where('dc.Eliminado', 0)
                ->select([
                    'c.Codclie',
                    'c.Razon',
                    'c.Zona',
                    'dc.Vendedor',
                    'e.Nombre as vendedor_nombre',
                    DB::raw('COUNT(DISTINCT dc.Numero) as cantidad_facturas'),
                    DB::raw('SUM(CAST(dc.Total as MONEY)) as total_ventas'),
                    DB::raw('SUM(CAST(dd.Costo as MONEY) * dd.Cantidad) as costo_ventas'),
                    DB::raw('SUM(CAST(dc.Total as MONEY)) - SUM(CAST(dd.Costo as MONEY) * dd.Cantidad) as utilidad_bruta')
                ])
                ->groupBy('c.Codclie', 'c.Razon', 'c.Zona', 'dc.Vendedor', 'e.Nombre')
                ->orderBy('total_ventas', 'desc')
                ->get();

            // Calcular margen y otras métricas
            foreach ($rentabilidadClientes as $cliente) {
                $cliente->margen_bruto = $cliente->total_ventas > 0 ? 
                    ($cliente->utilidad_bruta / $cliente->total_ventas) * 100 : 0;
                
                $cliente->promedio_factura = $cliente->cantidad_facturas > 0 ?
                    $cliente->total_ventas / $cliente->cantidad_facturas : 0;
            }

            // Top 20 clientes más rentables
            $topRentables = $rentabilidadClientes->sortByDesc('utilidad_bruta')->take(20);
            $topVentas = $rentabilidadClientes->sortByDesc('total_ventas')->take(20);
            $topMargen = $rentabilidadClientes->sortByDesc('margen_bruto')->take(20);

            return view('contabilidad.auxiliares.clientes-rentabilidad', compact(
                'rentabilidadClientes', 'topRentables', 'topVentas', 'topMargen', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en análisis de rentabilidad: ' . $e->getMessage());
        }
    }

    private function obtenerSaldosCliente($clienteId)
    {
        $saldos = DB::table('CtaCliente')
            ->where('CodClie', $clienteId)
            ->select([
                DB::raw('SUM(Importe) as total_facturado'),
                DB::raw('SUM(Saldo) as saldo_pendiente'),
                DB::raw('SUM(Importe) - SUM(Saldo) as total_pagado')
            ])
            ->first();

        return $saldos ?: (object)['total_facturado' => 0, 'saldo_pendiente' => 0, 'total_pagado' => 0];
    }

    private function obtenerTotalCartera()
    {
        return DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->sum('Saldo') ?? 0;
    }

    
    private function obtenerClientesConDeuda()
    {
        return DB::table('CtaCliente')
            ->where('Saldo', '>', 0)
            ->distinct('CodClie')
            ->count('CodClie');
    }

    
    private function obtenerMayorDeudor()
    {
        return DB::table('CtaCliente as cc')
            ->leftJoin('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
            ->where('cc.Saldo', '>', 0)
            ->orderBy('cc.Saldo', 'desc')
            ->select(['c.Razon', 'cc.Saldo'])
            ->first();
    }

  
    private function clasificarEdadDeuda($diasVencido)
    {
        if ($diasVencido <= 0) return 'VIGENTE';
        if ($diasVencido <= 30) return '1-30 DÍAS';
        if ($diasVencido <= 60) return '31-60 DÍAS';
        if ($diasVencido <= 90) return '61-90 DÍAS';
        return 'MÁS DE 90 DÍAS';
    }

    
    private function obtenerCarteraVencidaZona($zona, $fechaInicio, $fechaFin)
    {
        return DB::table('CtaCliente as cc')
            ->leftJoin('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
            ->where('c.Zona', $zona)
            ->where('cc.Saldo', '>', 0)
            ->where('cc.FechaV', '<', Carbon::now())
            ->sum('cc.Saldo') ?? 0;
    }

    
    private function obtenerVentasZona($zona, $fechaInicio, $fechaFin)
    {
        return DB::table('Doccab as dc')
            ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->where('c.Zona', $zona)
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Tipo', 1)
            ->where('dc.Eliminado', 0)
            ->sum('dc.Total') ?? 0;
    }
}