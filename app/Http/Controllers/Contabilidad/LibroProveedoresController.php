<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LibroProveedoresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));
            $proveedor = $request->input('proveedor');

            // Obtener información de proveedores (basado en la tabla Clientes para proveedores)
            $query = DB::table('Clientes as c')
                ->where('c.TipoClie', 2) // Asumimos que TipoClie = 2 son proveedores
                ->leftJoin('t_Clientes_ubigeo as ub', 'c.Codclie', '=', 'ub.CODIGO')
                ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona');

            if ($proveedor) {
                $query->where(function($q) use ($proveedor) {
                    $q->where('c.Razon', 'like', "%$proveedor%")
                      ->orWhere('c.Documento', 'like', "%$proveedor%");
                });
            }

            $proveedores = $query->select([
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
                'c.TipoClie',
                'c.Limite',
                'c.Activo',
                'c.Fecha as fecha_registro'
            ])
            ->orderBy('c.Razon')
            ->paginate(50);

            // Obtener saldos por proveedor
            $saldosPorProveedor = [];
            foreach ($proveedores as $proveedorItem) {
                $saldosPorProveedor[$proveedorItem->Codclie] = $this->obtenerSaldosProveedor($proveedorItem->Codclie);
            }

            // Obtener compras del período
            $comprasPeriodo = $this->obtenerComprasPorProveedor($fechaInicio, $fechaFin);

            // Resumen general
            $resumenGeneral = [
                'total_proveedores' => DB::table('Clientes')->where('TipoClie', 2)->count(),
                'proveedores_activos' => DB::table('Clientes')->where('TipoClie', 2)->where('Activo', 1)->count(),
                'total_cartera_pagar' => $this->obtenerTotalCarteraPagar(),
                'proveedores_con_deuda' => $this->obtenerProveedoresConDeuda(),
                'mayor_proveedor' => $this->obtenerMayorProveedor($fechaInicio, $fechaFin)
            ];

            return view('contabilidad.auxiliares.proveedores', compact(
                'proveedores', 'saldosPorProveedor', 'comprasPeriodo', 'resumenGeneral', 
                'fechaInicio', 'fechaFin', 'proveedor'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el libro de proveedores: ' . $e->getMessage());
        }
    }

    /**
     * Get detailed account statement for a supplier
     */
    public function estadoCuenta($proveedorId, Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Información del proveedor
            $proveedor = DB::table('Clientes as c')
                ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona')
                ->where('c.Codclie', $proveedorId)
                ->where('c.TipoClie', 2)
                ->select([
                    'c.Codclie',
                    'c.Razon',
                    'c.Documento',
                    'c.Direccion',
                    'c.Telefono1',
                    'c.Email',
                    'c.Zona',
                    'z.Descripcion as zona_nombre',
                    'c.Limite'
                ])
                ->first();

            if (!$proveedor) {
                return redirect()->route('libro-proveedores')->with('error', 'Proveedor no encontrado');
            }

            // Compras del proveedor (facturas tipo 2 = compras)
            $compras = DB::table('Doccab as dc')
                ->where('dc.CodClie', $proveedorId)
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 2) // Tipo 2 = Compras
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

            // Pagos realizados al proveedor
            $pagos = DB::table('Caja as c')
                ->where('c.Razon', $proveedorId)
                ->where('c.Tipo', 2) // Egresos = Pagos
                ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    'c.Numero',
                    'c.Fecha',
                    'c.Documento',
                    'c.Monto',
                    'c.Moneda',
                    'c.Cambio',
                    DB::raw('CASE WHEN c.Moneda = 1 THEN c.Monto ELSE c.Monto * c.Cambio END as total_soles')
                ])
                ->orderBy('c.Fecha', 'desc')
                ->get();

            // Saldos pendientes por pagar
            $saldoPendiente = DB::table('CtaCliente')
                ->where('CodClie', $proveedorId)
                ->where('Saldo', '>', 0)
                ->select([
                    'Documento',
                    'FechaF',
                    'FechaV',
                    'Importe',
                    'Saldo'
                ])
                ->get();

            // Calcular días de vencimiento
            foreach ($saldoPendiente as $saldo) {
                $saldo->dias_vencido = Carbon::parse($saldo->FechaV)->diffInDays(Carbon::now(), false);
                $saldo->clasificacion_vencimiento = $this->clasificarVencimiento($saldo->dias_vencido);
            }

            // Totales
            $totales = [
                'total_comprado' => $compras->sum('total_soles'),
                'total_pagado' => $pagos->sum('total_soles'),
                'saldo_pendiente' => $saldoPendiente->sum('Saldo'),
                'compras_pendientes' => $saldoPendiente->count()
            ];

            // Clasificación de cuentas por pagar
            $clasificacionVencimiento = [
                'POR_VENCER' => $saldoPendiente->where('dias_vencido', '<=', 0)->sum('Saldo'),
                '1_15_DIAS' => $saldoPendiente->whereBetween('dias_vencido', [1, 15])->sum('Saldo'),
                '16_30_DIAS' => $saldoPendiente->whereBetween('dias_vencido', [16, 30])->sum('Saldo'),
                '31_45_DIAS' => $saldoPendiente->whereBetween('dias_vencido', [31, 45])->sum('Saldo'),
                'MAS_45_DIAS' => $saldoPendiente->where('dias_vencido', '>', 45)->sum('Saldo')
            ];

            return view('contabilidad.auxiliares.proveedores-estado-cuenta', compact(
                'proveedor', 'compras', 'pagos', 'saldoPendiente', 'totales', 
                'clasificacionVencimiento', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar estado de cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Get accounts payable aging report
     */
    public function clasificacionVencimiento(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));

            // Obtener todos los saldos pendientes de proveedores
            $saldosPendientes = DB::table('CtaCliente as cc')
                ->leftJoin('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
                ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona')
                ->where('c.TipoClie', 2) // Solo proveedores
                ->where('cc.Saldo', '>', 0)
                ->select([
                    'cc.Documento',
                    'cc.CodClie',
                    'c.Razon',
                    'c.Documento as ruc',
                    'c.Zona',
                    'z.Descripcion as zona_nombre',
                    'cc.FechaF',
                    'cc.FechaV',
                    'cc.Importe',
                    'cc.Saldo'
                ])
                ->get();

            // Clasificar por vencimiento
            $clasificacionVencimiento = [];
            $totalCuentasPagar = 0;

            foreach ($saldosPendientes as $saldo) {
                $diasVencido = Carbon::parse($saldo->FechaV)->diffInDays(Carbon::now(), false);
                $saldo->dias_vencido = $diasVencido;
                
                if ($diasVencido <= 0) {
                    $clasificacionVencimiento['POR_VENCER'][] = $saldo;
                } elseif ($diasVencido <= 15) {
                    $clasificacionVencimiento['1_15_DIAS'][] = $saldo;
                } elseif ($diasVencido <= 30) {
                    $clasificacionVencimiento['16_30_DIAS'][] = $saldo;
                } elseif ($diasVencido <= 45) {
                    $clasificacionVencimiento['31_45_DIAS'][] = $saldo;
                } else {
                    $clasificacionVencimiento['MAS_45_DIAS'][] = $saldo;
                }
                
                $totalCuentasPagar += $saldo->Saldo;
            }

            // Calcular totales por clasificación
            $totalesClasificacion = [];
            foreach ($clasificacionVencimiento as $categoria => $saldos) {
                $totalesClasificacion[$categoria] = [
                    'cantidad_proveedores' => count(array_unique(array_column($saldos, 'CodClie'))),
                    'cantidad_documentos' => count($saldos),
                    'saldo_total' => array_sum(array_column($saldos, 'Saldo')),
                    'porcentaje' => $totalCuentasPagar > 0 ? (array_sum(array_column($saldos, 'Saldo')) / $totalCuentasPagar) * 100 : 0
                ];
            }

            // Proveedores críticos (más de 30 días vencidos)
            $proveedoresCriticos = collect($clasificacionVencimiento['MAS_45_DIAS'] ?? [])
                ->merge($clasificacionVencimiento['31_45_DIAS'] ?? [])
                ->sortByDesc('Saldo')
                ->take(20);

            return view('contabilidad.auxiliares.proveedores-clasificacion-vencimiento', compact(
                'clasificacionVencimiento', 'totalesClasificacion', 'totalCuentasPagar', 
                'proveedoresCriticos', 'fecha'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en clasificación de vencimiento: ' . $e->getMessage());
        }
    }

    /**
     * Get supplier payment analysis
     */
    public function analisisPagos(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Obtener pagos realizados por proveedor
            $pagosProveedores = DB::table('Caja as c')
                ->leftJoin('Clientes as cl', 'c.Razon', '=', 'cl.Codclie')
                ->where('cl.TipoClie', 2) // Solo proveedores
                ->where('c.Tipo', 2) // Egresos = Pagos
                ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
                ->select([
                    'c.Razon as CodClie',
                    'cl.Razon',
                    'c.Documento',
                    'c.Fecha',
                    'c.Monto',
                    'c.Moneda',
                    'c.Cambio'
                ])
                ->get();

            // Agrupar por proveedor
            $pagosPorProveedor = $pagosPorProveedor = $pagosProveedores->groupBy('CodClie')->map(function($grupo) {
                return [
                    'proveedor' => $grupo->first()->Razon,
                    'total_pagado' => $grupo->sum('Monto'),
                    'cantidad_pagos' => $grupo->count(),
                    'promedio_pago' => $grupo->avg('Monto'),
                    'mayor_pago' => $grupo->max('Monto'),
                    'menor_pago' => $grupo->min('Monto')
                ];
            })->values();

            // Análisis de términos de pago
            $terminosPago = $this->analizarTerminosPago($fechaInicio, $fechaFin);

            return view('contabilidad.auxiliares.proveedores-analisis-pagos', compact(
                'pagosPorProveedor', 'terminosPago', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en análisis de pagos: ' . $e->getMessage());
        }
    }

    /**
     * Get supplier performance analysis
     */
    public function rendimiento(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Calcular rendimiento por proveedor
            $rendimientoProveedores = DB::table('Doccab as dc')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->leftJoin('Docdet as dd', 'dc.Numero', '=', 'dd.Numero')
                ->leftJoin('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 2) // Compras
                ->where('dc.Eliminado', 0)
                ->where('c.TipoClie', 2) // Solo proveedores
                ->select([
                    'c.Codclie',
                    'c.Razon',
                    'c.Zona',
                    'p.CodPro',
                    'p.Nombre as producto',
                    'l.Descripcion as laboratorio',
                    DB::raw('COUNT(DISTINCT dc.Numero) as cantidad_ordenes'),
                    DB::raw('SUM(dd.Cantidad) as cantidad_comprada'),
                    DB::raw('SUM(CAST(dd.Subtotal as MONEY)) as total_compras'),
                    DB::raw('AVG(CAST(dd.Precio as MONEY)) as precio_promedio')
                ])
                ->groupBy('c.Codclie', 'c.Razon', 'c.Zona', 'p.CodPro', 'p.Nombre', 'l.Descripcion')
                ->orderBy('total_compras', 'desc')
                ->get();

            // Agrupar por proveedor
            $rendimientoAgrupado = $rendimientoProveedores->groupBy('Codclie')->map(function($grupo) {
                $primero = $grupo->first();
                return [
                    'proveedor' => $primero->Razon,
                    'zona' => $primero->Zona,
                    'total_compras' => $grupo->sum('total_compras'),
                    'cantidad_ordenes' => $grupo->sum('cantidad_ordenes'),
                    'productos_diferentes' => $grupo->unique('CodPro')->count(),
                    'laboratorios' => $grupo->unique('laboratorio')->count(),
                    'precio_promedio' => $grupo->avg('precio_promedio')
                ];
            })->values()->sortByDesc('total_compras');

            return view('contabilidad.auxiliares.proveedores-rendimiento', compact(
                'rendimientoAgrupado', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en análisis de rendimiento: ' . $e->getMessage());
        }
    }

    /**
     * Get pharmaceutical suppliers analysis
     */
    public function proveedoresFarmaceuticos(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Obtener proveedores farmacéuticos específicos
            $proveedoresFarmaceuticos = DB::table('Docdet as dd')
                ->join('Doccab as dc', 'dd.Numero', '=', 'dc.Numero')
                ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where('dc.Tipo', 2) // Compras
                ->where('dc.Eliminado', 0)
                ->where('c.TipoClie', 2) // Solo proveedores
                ->whereNotNull('p.RegSanit') // Productos con registro sanitario
                ->select([
                    'c.Codclie',
                    'c.Razon as proveedor',
                    'p.CodPro',
                    'p.Nombre as producto',
                    'p.RegSanit',
                    'p.Principio',
                    'l.Descripcion as laboratorio',
                    DB::raw('SUM(dd.Cantidad) as cantidad_comprada'),
                    DB::raw('SUM(CAST(dd.Subtotal as MONEY)) as total_compras'),
                    DB::raw('AVG(CAST(dd.Precio as MONEY)) as precio_promedio')
                ])
                ->groupBy('c.Codclie', 'c.Razon', 'p.CodPro', 'p.Nombre', 'p.RegSanit', 'p.Principio', 'l.Descripcion')
                ->orderBy('total_compras', 'desc')
                ->get();

            // Clasificar proveedores por tipo
            $clasificacionProveedores = [
                'LABORATORIOS' => [],
                'DISTRIBUIDORES' => [],
                'IMPORTADORES' => [],
                'OTROS' => []
            ];

            foreach ($proveedoresFarmaceuticos->groupBy('Codclie') as $proveedorId => $productos) {
                $proveedor = $productos->first();
                $nombre = strtoupper($proveedor->proveedor);
                
                if (strpos($nombre, 'LABORATORIO') !== false || strpos($nombre, 'LAB') !== false) {
                    $clasificacionProveedores['LABORATORIOS'][$proveedorId] = $productos;
                } elseif (strpos($nombre, 'DISTRIBUIDORA') !== false || strpos($nombre, 'DISTRIBUIDOR') !== false) {
                    $clasificacionProveedores['DISTRIBUIDORES'][$proveedorId] = $productos;
                } elseif (strpos($nombre, 'IMPORT') !== false) {
                    $clasificacionProveedores['IMPORTADORES'][$proveedorId] = $productos;
                } else {
                    $clasificacionProveedores['OTROS'][$proveedorId] = $productos;
                }
            }

            // Totales farmacéuticos
            $totalesFarmaceuticos = [
                'total_proveedores' => count($proveedoresFarmaceuticos->groupBy('Codclie')),
                'total_compras' => $proveedoresFarmaceuticos->sum('total_compras'),
                'total_productos' => $proveedoresFarmaceuticos->count(),
                'precio_promedio' => $proveedoresFarmaceuticos->avg('precio_promedio')
            ];

            return view('contabilidad.auxiliares.proveedores-farmaceuticos', compact(
                'clasificacionProveedores', 'totalesFarmaceuticos', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en proveedores farmacéuticos: ' . $e->getMessage());
        }
    }

    /**
     * Get cash flow forecast for supplier payments
     */
    public function proyeccionPagos(Request $request)
    {
        try {
            $diasProyeccion = $request->input('dias', 30);
            $fechaInicio = Carbon::now()->startOfDay();
            $fechaFin = $fechaInicio->copy()->addDays($diasProyeccion);

            // Obtener vencimientos próximos
            $vencimientosProximos = DB::table('CtaCliente as cc')
                ->leftJoin('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
                ->where('c.TipoClie', 2) // Solo proveedores
                ->whereBetween('cc.FechaV', [$fechaInicio, $fechaFin])
                ->where('cc.Saldo', '>', 0)
                ->select([
                    'cc.FechaV',
                    'cc.Saldo',
                    'c.Razon as proveedor',
                    'cc.Documento'
                ])
                ->orderBy('cc.FechaV')
                ->get();

            // Agrupar por fecha
            $proyeccionDiaria = $vencimientosProximos->groupBy('FechaV')->map(function($grupo) {
                return [
                    'cantidad_documentos' => $grupo->count(),
                    'total_pagar' => $grupo->sum('Saldo'),
                    'proveedores' => $grupo->unique('proveedor')->count()
                ];
            });

            // Resumen de proyección
            $resumenProyeccion = [
                'total_a_pagar' => $vencimientosProximos->sum('Saldo'),
                'total_documentos' => $vencimientosProximos->count(),
                'total_proveedores' => $vencimientosProximos->unique('proveedor')->count(),
                'fecha_mayor_pago' => $vencimientosProximos->sortByDesc('Saldo')->first()->FechaV ?? null
            ];

            // Alertas de pago
            $alertas = $this->generarAlertasPagos($vencimientosProximos);

            return view('contabilidad.auxiliares.proveedores-proyeccion', compact(
                'vencimientosProximos', 'proyeccionDiaria', 'resumenProyeccion', 'alertas', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en proyección de pagos: ' . $e->getMessage());
        }
    }

    /**
     * Get balances for a specific supplier
     */
    private function obtenerSaldosProveedor($proveedorId)
    {
      
        $saldos = DB::table('CtaCliente')
            ->where('CodClie', $proveedorId)
            ->select([
                DB::raw('SUM(Importe) as total_comprado'),
                DB::raw('SUM(Saldo) as saldo_pendiente'),
                DB::raw('SUM(Importe) - SUM(Saldo) as total_pagado')
            ])
            ->first();

        return $saldos ?: (object)['total_comprado' => 0, 'saldo_pendiente' => 0, 'total_pagado' => 0];
    }

 
    private function obtenerComprasPorProveedor($fechaInicio, $fechaFin)
    {
        return DB::table('Doccab as dc')
            ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Tipo', 2) // Compras
            ->where('dc.Eliminado', 0)
            ->where('c.TipoClie', 2) // Solo proveedores
            ->select([
                'c.Codclie',
                'c.Razon',
                DB::raw('COUNT(DISTINCT dc.Numero) as cantidad_ordenes'),
                DB::raw('SUM(CAST(dc.Total as MONEY)) as total_compras')
            ])
            ->groupBy('c.Codclie', 'c.Razon')
            ->orderBy('total_compras', 'desc')
            ->get();
    }

    
    private function obtenerTotalCarteraPagar()
    {
        return DB::table('CtaCliente as cc')
            ->leftJoin('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
            ->where('c.TipoClie', 2) // Solo proveedores
            ->where('cc.Saldo', '>', 0)
            ->sum('cc.Saldo') ?? 0;
    }

    private function obtenerProveedoresConDeuda()
    {
        return DB::table('CtaCliente as cc')
            ->leftJoin('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
            ->where('c.TipoClie', 2) // Solo proveedores
            ->where('cc.Saldo', '>', 0)
            ->distinct('cc.CodClie')
            ->count('cc.CodClie');
    }

    
    private function obtenerMayorProveedor($fechaInicio, $fechaFin)
    {
        return DB::table('Doccab as dc')
            ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Tipo', 2) // Compras
            ->where('dc.Eliminado', 0)
            ->where('c.TipoClie', 2) // Solo proveedores
            ->groupBy('c.Codclie', 'c.Razon')
            ->select([
                'c.Razon',
                DB::raw('SUM(CAST(dc.Total as MONEY)) as total_compras')
            ])
            ->orderBy('total_compras', 'desc')
            ->first();
    }

    private function clasificarVencimiento($diasVencido)
    {
        if ($diasVencido <= 0) return 'POR_VENCER';
        if ($diasVencido <= 15) return '1-15 DÍAS';
        if ($diasVencido <= 30) return '16-30 DÍAS';
        if ($diasVencido <= 45) return '31-45 DÍAS';
        return 'MÁS DE 45 DÍAS';
    }

    
    private function analizarTerminosPago($fechaInicio, $fechaFin)
    {
  
        $terminos = DB::table('Doccab as dc')
            ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where('dc.Tipo', 2) // Compras
            ->where('dc.Eliminado', 0)
            ->where('c.TipoClie', 2) // Solo proveedores
            ->select([
                'dc.Numero',
                'dc.Fecha',
                'dc.FechaV',
                'c.Razon as proveedor',
                DB::raw('DATEDIFF(day, dc.Fecha, dc.FechaV) as dias_credito')
            ])
            ->get();

        $diasCredito = $terminos->pluck('dias_credito')->filter()->toArray();
        
        return [
            'promedio_dias_credito' => count($diasCredito) > 0 ? array_sum($diasCredito) / count($diasCredito) : 0,
            'minimo_dias_credito' => count($diasCredito) > 0 ? min($diasCredito) : 0,
            'maximo_dias_credito' => count($diasCredito) > 0 ? max($diasCredito) : 0,
            'terminos_mas_comunes' => $this->obtenerTerminosMasComunes($terminos)
        ];
    }

   
    private function obtenerTerminosMasComunes($terminos)
    {
        $frecuencia = $terminos->groupBy('dias_credito')
            ->map->count()
            ->sortDesc()
            ->take(5);

        return $frecuencia;
    }

    /**
     * Generate payment alerts
     */
    private function generarAlertasPagos($vencimientosProximos)
    {
        $alertas = [];
        
        // Alerta de pagos críticos (próximos 7 días)
        $pagosCriticos = $vencimientosProximos->where('FechaV', '<=', Carbon::now()->addDays(7)->format('Y-m-d'));
        if ($pagosCriticos->count() > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => $pagosCriticos->count() . ' pagos críticos próximos (7 días)',
                'total' => $pagosCriticos->sum('Saldo')
            ];
        }
        
        return $alertas;
    }
}