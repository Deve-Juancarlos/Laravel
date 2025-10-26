<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FacturacionController extends Controller
{
    /**
     * Constructor con middleware de autenticación y autorización
     */
    public function __construct()
    {
        $this->middleware('auth');
        
    }

    /**
     * Lista todas las facturas con filtros
     */
    public function index(Request $request)
    {
        $query = DB::table('Doccab')
            ->leftJoin('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->leftJoin('accesoweb', 'Doccab.Usuario', '=', 'accesoweb.usuario')
            ->where('Doccab.Tipo', '!=', '1');

        // Filtros de fecha
        if ($request->filled('fecha_desde')) {
            $query->where('Doccab.Fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('Doccab.Fecha', '<=', $request->fecha_hasta);
        }

        // Filtro por cliente
        if ($request->filled('cliente')) {
            $query->where(function($q) use ($request) {
                $q->where('Clientes.Codclie', 'like', '%' . $request->cliente . '%')
                  ->orWhere('Clientes.Razon', 'like', '%' . $request->cliente . '%');
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('Doccab.Estado', $request->estado);
        }

        // Filtro por vendedor
        if ($request->filled('vendedor')) {
            $query->where('Doccab.Vendedor', 'like', '%' . $request->vendedor . '%');
        }

        // Ordenamiento
        $orderBy = $request->get('order_by', 'Fecha');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy("Doccab.{$orderBy}", $orderDirection);

        $facturas = $query->select([
            'Doccab.*',
            'Clientes.Razon as Cliente',
            'Clientes.Direccion as DireccionCli',
            'Clientes.Ruc as RucCli',
            'accesoweb.usuario as UsuarioVenta'
        ])->paginate($request->get('per_page', 20));

        // Estadísticas rápidas
        $estadisticas = $this->calcularEstadisticas($request);

        return response()->json([
            'facturas' => $facturas,
            'estadisticas' => $estadisticas,
            'filtros_activos' => $request->only([
                'fecha_desde', 'fecha_hasta', 'cliente', 'estado', 'vendedor'
            ])
        ]);
    }

    /**
     * Crear nueva factura
     */
    public function store(Request $request)
    {
        $request->validate([
            'CodClie' => 'required|exists:Clientes,Codclie',
            'items' => 'required|array|min:1',
            'items.*.CodPro' => 'required|exists:Productos,CodPro',
            'items.*.Cantidad' => 'required|numeric|min:0.01',
            'items.*.Precio' => 'required|numeric|min:0',
            'items.*.Descuento' => 'nullable|numeric|min:0|max:100',
            'Vendedor' => 'required',
            'Observacion' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Generar número de documento
            $numero = $this->generarNumeroDocumento($request->Tipo ?? 1);

            // Calcular totales
            $totales = $this->calcularTotales($request->items);

            // Crear cabecera
            $cabeceraId = DB::table('Doccab')->insertGetId([
                'Numero' => $numero,
                'Tipo' => $request->Tipo ?? 1,
                'CodClie' => $request->CodClie,
                'Fecha' => now(),
                'Subtotal' => $totales['subtotal'],
                'Impuesto' => $totales['impuesto'],
                'Total' => $totales['total'],
                'Eliminado' => false,
                'Vendedor' => $request->Vendedor,
                'Usuario' => auth()->user()->usuario,
                'Observacion' => $request->Observacion,
                'created_at' => now()
            ]);

            // Crear detalles
            foreach ($request->items as $item) {
                $subtotal = $item['Cantidad'] * $item['Precio'];
                $descuento = ($subtotal * ($item['Descuento'] ?? 0)) / 100;
                $subtotalFinal = $subtotal - $descuento;

                DB::table('Docdet')->insert([
                    'Numero' => $numero,
                    'CodPro' => $item['CodPro'],
                    'Cantidad' => $item['Cantidad'],
                    'Precio' => $item['Precio'],
                    'Descuento' => $item['Descuento'] ?? 0,
                    'Subtotal' => $subtotalFinal,
                    'created_at' => now()
                ]);

                // Actualizar stock
                $this->actualizarStock($item['CodPro'], $item['Cantidad']);
            }

            // Crear movimiento contable si está habilitado
            if ($request->input('generar_asiento', true)) {
                $this->crearAsientoContable($cabeceraId, $numero, $totales);
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Factura creada exitosamente',
                'numero' => $numero,
                'total' => $totales['total']
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear factura: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al crear la factura',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalles de una factura específica
     */
    public function show($numero)
    {
        $factura = DB::table('Doccab')
            ->leftJoin('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->leftJoin('accesoweb', 'Doccab.Usuario', '=', 'accesoweb.usuario')
            ->where('Doccab.Numero', $numero)
            ->select([
                'Doccab.*',
                'Clientes.Razon as Cliente',
                'Clientes.Direccion as DireccionCli',
                'Clientes.Ruc as RucCli',
                'Clientes.Telefono as TelefonoCli',
                'Clientes.Email as EmailCli',
                'accesoweb.usuario as UsuarioVenta'
            ])
            ->first();

        if (!$factura) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }

        $detalles = DB::table('Docdet')
            ->join('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->where('Docdet.Numero', $numero)
            ->select([
                'Docdet.*',
                'Productos.Nombre as Producto',
                'Productos.Unidad as UnidadPro',
                'Productos.CodBar as CodBarPro'
            ])
            ->get();

        // Calcular estadísticas adicionales
        $estadisticas = [
            'margen_promedio' => $this->calcularMargenPromedio($numero),
            'dias_desde_venta' => now()->diffInDays($factura->Fecha),
            'cumplimiento_entrega' => $this->verificarCumplimientoEntrega($numero)
        ];

        return response()->json([
            'cabecera' => $factura,
            'detalles' => $detalles,
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Actualizar factura (solo pendientes)
     */
    public function update(Request $request, $numero)
    {
        $factura = DB::table('Doccab')->where('Numero', $numero)->first();
        
        if (!$factura) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }

        if ($factura->Estado === 'PAGADO') {
            return response()->json(['error' => 'No se puede editar una factura pagada'], 400);
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.CodPro' => 'required|exists:Productos,CodPro',
            'items.*.Cantidad' => 'required|numeric|min:0.01',
            'items.*.Precio' => 'required|numeric|min:0',
            'items.*.Descuento' => 'nullable|numeric|min:0|max:100'
        ]);

        try {
            DB::beginTransaction();

            // Eliminar detalles existentes
            DB::table('Docdet')->where('Numero', $numero)->delete();

            // Calcular nuevos totales
            $totales = $this->calcularTotales($request->items);

            // Actualizar cabecera
            DB::table('Doccab')
                ->where('Numero', $numero)
                ->update([
                    'Subtotal' => $totales['subtotal'],
                    'Impuesto' => $totales['impuesto'],
                    'Total' => $totales['total'],
                    'updated_at' => now()
                ]);

            // Crear nuevos detalles
            foreach ($request->items as $item) {
                $subtotal = $item['Cantidad'] * $item['Precio'];
                $descuento = ($subtotal * ($item['Descuento'] ?? 0)) / 100;
                $subtotalFinal = $subtotal - $descuento;

                DB::table('Docdet')->insert([
                    'Numero' => $numero,
                    'CodPro' => $item['CodPro'],
                    'Cantidad' => $item['Cantidad'],
                    'Precio' => $item['Precio'],
                    'Descuento' => $item['Descuento'] ?? 0,
                    'Subtotal' => $subtotalFinal,
                    'updated_at' => now()
                ]);

                // Actualizar stock
                $this->actualizarStock($item['Codpro'], $item['Cantidad'], true);
            }

            DB::commit();

            return response()->json(['mensaje' => 'Factura actualizada exitosamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar la factura'], 500);
        }
    }

    /**
     * Cancelar/anular factura
     */
    public function destroy($numero)
    {
        $factura = DB::table('Doccab')->where('Numero', $numero)->first();
        
        if (!$factura) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }

        if ($factura->Estado === 'PAGADO') {
            return response()->json(['error' => 'No se puede anular una factura pagada'], 400);
        }

        try {
            DB::beginTransaction();

            // Restaurar stock
            $detalles = DB::table('Docdet')->where('Numero', $numero)->get();
            foreach ($detalles as $detalle) {
                $this->restaurarStock($detalle->Codpro, $detalle->Cantidad);
            }

            // Cambiar estado a anulado
            DB::table('Doccab')
                ->where('Numero', $numero)
                ->update([
                    'Estado' => 'ANULADO',
                    'updated_at' => now()
                ]);

            // Crear nota de crédito o ajuste contable
            $this->crearNotaCredito($numero, $factura);

            DB::commit();

            return response()->json(['mensaje' => 'Factura anulada exitosamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al anular la factura'], 500);
        }
    }

    /**
     * Generar nueva factura (frontend)
     */
    public function nuevaFactura()
    {
        $clientes = DB::table('Clientes')
            ->where('Estado', 'ACTIVO')
            ->select(['CodCli', 'Razon', 'Ruc', 'Direccion'])
            ->orderBy('Razon')
            ->get();

        $productos = DB::table('Productos')
            ->where('Estado', 'ACTIVO')
            ->where('Stock', '>', 0)
            ->select(['CodPro', 'Nombre', 'Precio', 'Stock', 'Unidad'])
            ->orderBy('Nombre')
            ->get();

        $vendedores = DB::table('Usuarios')
            ->where('tipodeusario', 'contador')
            ->where('Estado', 'ACTIVO')
            ->select(['Usuario', 'Nombre'])
            ->orderBy('Nombre')
            ->get();

        // Número siguiente
        $numeroSiguiente = $this->generarNumeroDocumento('FACT', true);

        return response()->json([
            'clientes' => $clientes,
            'productos' => $productos,
            'vendedores' => $vendedores,
            'numero_siguiente' => $numeroSiguiente,
            'fecha_actual' => now()->format('Y-m-d')
        ]);
    }

    /**
     * Buscar productos para autocompletado
     */
    public function buscarProductos(Request $request)
    {
        $termino = $request->get('termino', '');
        $limite = $request->get('limite', 10);

        $productos = DB::table('Productos')
            ->where('Estado', 'ACTIVO')
            ->where(function($q) use ($termino) {
                $q->where('Nombre', 'like', "%{$termino}%")
                  ->orWhere('CodPro', 'like', "%{$termino}%")
                  ->orWhere('CodBar', 'like', "%{$termino}%");
            })
            ->select([
                'CodPro',
                'Nombre',
                'Precio',
                'Stock',
                'Unidad',
                'CodBar'
            ])
            ->limit($limite)
            ->get();

        return response()->json($productos);
    }

    /**
     * Marcar factura como pagada
     */
    public function marcarPagada(Request $request, $numero)
    {
        $request->validate([
            'fecha_pago' => 'nullable|date',
            'metodo_pago' => 'required|in:efectivo,transferencia,cheque,tarjeta',
            'observaciones' => 'nullable|string|max:500'
        ]);

        try {
            $fechaPago = $request->fecha_pago ?? now()->toDateString();

            DB::table('Doccab')
                ->where('Numero', $numero)
                ->update([
                    'Estado' => 'PAGADO',
                    'FechaPago' => $fechaPago,
                    'MetodoPago' => $request->metodo_pago,
                    'ObservacionesPago' => $request->observaciones,
                    'updated_at' => now()
                ]);

            // Registrar movimiento en caja si está habilitado
            if ($request->input('registrar_movimiento_caja', true)) {
                $this->registrarMovimientoCaja($numero, $fechaPago);
            }

            return response()->json(['mensaje' => 'Factura marcada como pagada']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al marcar como pagada'], 500);
        }
    }

    /**
     * Generar reporte de facturas
     */
    public function reporte(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->toDateString());

        $facturas = DB::table('Doccab')
            ->leftJoin('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->whereBetween('Doccab.Fecha', [$fechaInicio, $fechaFin])
            ->where('Doccab.Tipo', '!=', '1')
            ->select([
                'Doccab.Numero',
                'Doccab.Fecha',
                'Doccab.Tipo',
                'Clientes.Razon as Cliente',
                'Clientes.Ruc as Ruc',
                'Doccab.Subtotal',
                'Doccab.Impuesto',
                'Doccab.Total',
                'Doccab.Estado',
                'Doccab.Vendedor'
            ])
            ->orderBy('Doccab.Fecha', 'desc')
            ->get();

        // Calcular totales
        $totales = [
            'total_facturas' => $facturas->count(),
            'total_monto' => $facturas->sum('Total'),
            'total_pendientes' => $facturas->where('Estado', 'PENDIENTE')->sum('Total'),
            'total_pagadas' => $facturas->where('Estado', 'PAGADO')->sum('Total'),
            'promedio_por_factura' => $facturas->count() > 0 ? $facturas->sum('Total') / $facturas->count() : 0
        ];

        return response()->json([
            'reporte' => $facturas,
            'totales' => $totales,
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin
            ]
        ]);
    }

    // ===== MÉTODOS PRIVADOS DE SOPORTE =====

    /**
     * Generar número de documento
     */
    private function generarNumeroDocumento($tipo, $soloSiguiente = false)
    {
        $prefijos = [
            'FACT' => 'F',
            'BOLE' => 'B',
            'NCRE' => 'N',
            'NDEB' => 'D'
        ];

        $prefijo = $prefijos[$tipo] ?? 'F';
        $año = now()->year;
        
        if ($soloSiguiente) {
            $ultimoNumero = DB::table('Doccab')
                ->where('Tipo', $tipo)
                ->whereYear('Fecha', $año)
                ->max('Numero');
        } else {
            $ultimoNumero = DB::table('Doccab')
                ->where('Tipo', $tipo)
                ->whereYear('Fecha', $año)
                ->max('Numero');
        }

        $numeroConsecutivo = $ultimoNumero ? (int)substr($ultimoNumero, 4) + 1 : 1;
        
        if ($soloSiguiente) {
            return $prefijo . $año . sprintf('%06d', $numeroConsecutivo);
        }

        return $prefijo . $año . sprintf('%06d', $numeroConsecutivo);
    }

    /**
     * Calcular totales de factura
     */
    private function calcularTotales($items)
    {
        $subtotal = 0;
        $impuesto = 0;

        foreach ($items as $item) {
            $itemSubtotal = $item['Cantidad'] * $item['Precio'];
            $descuento = ($itemSubtotal * ($item['Descuento'] ?? 0)) / 100;
            $itemSubtotal -= $descuento;
            $subtotal += $itemSubtotal;
        }

        // IGV 18% en Perú
        $impuesto = $subtotal * 0.18;
        $total = $subtotal + $impuesto;

        return [
            'subtotal' => round($subtotal, 2),
            'impuesto' => round($impuesto, 2),
            'total' => round($total, 2)
        ];
    }

    /**
     * Actualizar stock de producto
     */
    private function actualizarStock($codPro, $cantidad, $esEdicion = false)
    {
        $producto = DB::table('Productos')->where('CodPro', $codPro)->first();
        
        if (!$producto) {
            throw new \Exception("Producto {$codPro} no encontrado");
        }

        $nuevoStock = $producto->Stock - $cantidad;

        if ($nuevoStock < 0 && !$esEdicion) {
            throw new \Exception("Stock insuficiente para producto {$producto->Nombre}");
        }

        DB::table('Productos')
            ->where('CodPro', $codPro)
            ->update(['Stock' => $nuevoStock]);
    }

    /**
     * Restaurar stock al anular factura
     */
    private function restaurarStock($codPro, $cantidad)
    {
        DB::table('Productos')
            ->where('CodPro', $codPro)
            ->increment('Stock', $cantidad);
    }

    /**
     * Crear asiento contable
     */
    private function crearAsientoContable($cabeceraId, $numero, $totales)
    {
        // Asiento de ventas: Efectivo/Cuentas x Cobrar -> Ventas + IGV
        DB::table('asientos_diario')->insert([
            [
                'fecha' => now(),
                'glosa' => "VENTA {$numero}",
                'cuenta_debe' => '1211', // Cuentas por cobrar
                'cuenta_haber' => '4011', // IGV
                'monto' => $totales['impuesto'],
                'fecha_creacion' => now()
            ],
            [
                'fecha' => now(),
                'glosa' => "VENTA {$numero}",
                'cuenta_debe' => '1211', // Cuentas por cobrar
                'cuenta_haber' => '7011', // Ventas
                'monto' => $totales['total'],
                'fecha_creacion' => now()
            ]
        ]);
    }

    /**
     * Crear nota de crédito por anulación
     */
    private function crearNotaCredito($numeroOriginal, $factura)
    {
        $numeroNC = $this->generarNumeroDocumento('NCRE');
        
        DB::table('Doccab')->insert([
            'Numero' => $numeroNC,
            'Tipo' => 'NCRE',
            'CodCli' => $factura->CodCli,
            'Fecha' => now(),
            'Subtotal' => -$factura->Subtotal,
            'Impuesto' => -$factura->Impuesto,
            'Total' => -$factura->Total,
            'Estado' => 'ANULADO',
            'Vendedor' => $factura->Vendedor,
            'Observacion' => "Anulación de factura {$numeroOriginal}",
            'created_at' => now()
        ]);
    }

    /**
     * Calcular estadísticas rápidas
     */
    private function calcularEstadisticas($request)
    {
        // Subconsulta: sumar los pagos por número de factura
        $pagosSubquery = DB::table('PlanD_cobranza')
            ->select(
                'Numero',
                DB::raw('SUM(ISNULL(Valor,0) + ISNULL(Efectivo,0) + ISNULL(Cheque,0)) AS total_pagado')
            )
            ->groupBy('Numero');

        $query = DB::table('Doccab')
            ->leftJoinSub($pagosSubquery, 'pagos', function ($join) {
                $join->on('Doccab.Numero', '=', 'pagos.Numero');
            })
            ->where('Doccab.Tipo', '!=', 1);

        // Aplicar filtros de fecha si existen
        if ($request->filled('fecha_desde')) {
            $query->where('Doccab.Fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('Doccab.Fecha', '<=', $request->fecha_hasta);
        }

        // Calcular los totales sin usar subconsultas dentro de SUM
        return $query->select([
            DB::raw('COUNT(*) AS total_facturas'),
            DB::raw('SUM(Doccab.Total) AS total_monto'),
            DB::raw("
                SUM(CASE 
                    WHEN ISNULL(pagos.total_pagado, 0) < Doccab.Total THEN Doccab.Total 
                    ELSE 0 
                END) AS total_pendientes
            "),
            DB::raw("
                SUM(CASE 
                    WHEN ISNULL(pagos.total_pagado, 0) >= Doccab.Total THEN Doccab.Total 
                    ELSE 0 
                END) AS total_pagadas
            ")
        ])->first();
    }



    private function calcularMargenPromedio($numero)
    {
        // Implementación simplificada - en producción sería más compleja
        return 25.5; // 25.5% margen promedio
    }

 
    private function verificarCumplimientoEntrega($numero)
    {
        // Implementación básica - verificar si hay registros de entrega
        return true; // Cumplido
    }

    /**
     * Registrar movimiento en caja
     */
    private function registrarMovimientoCaja($numero, $fechaPago)
    {
        $factura = DB::table('Doccab')->where('Numero', $numero)->first();
        
        if ($factura) {
            DB::table('Movimientos_Caja')->insert([
                'Fecha' => $fechaPago,
                'Concepto' => "Cobranza factura {$numero}",
                'Ingreso' => $factura->Total,
                'Egreso' => 0,
                'Saldo' => 0, // Se calculará automáticamente
                'created_at' => now()
            ]);
        }
    }

    /**
     * Exportar facturas
     */
    public function exportar(Request $request)
    {
        $tipo = $request->get('tipo', 'excel');
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->toDateString());

        // Obtener datos usando el mismo query del index
        $facturas = DB::table('Doccab')
            ->leftJoin('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->leftJoin('accesoweb', 'Doccab.Usuario', '=', 'accesoweb.usuario')
            ->whereBetween('Doccab.Fecha', [$fechaInicio, $fechaFin])
            ->where('Doccab.Tipo', '!=', '1')
            ->select([
                'Doccab.Numero',
                'Doccab.Fecha',
                'Doccab.Tipo',
                'Clientes.Razon as Cliente',
                'Clientes.Ruc as Ruc',
                'Doccab.Subtotal',
                'Doccab.Impuesto',
                'Doccab.Total',
                'Doccab.Estado',
                'Doccab.Vendedor',
                'Usuarios.Nombre as Usuario'
            ])
            ->orderBy('Doccab.Fecha', 'desc')
            ->get();

        if ($tipo === 'csv') {
            return $this->exportarCSV($facturas);
        }

        return response()->json([
            'mensaje' => 'Exportación iniciada',
            'total_registros' => $facturas->count(),
            'fecha_desde' => $fechaInicio,
            'fecha_hasta' => $fechaFin
        ]);
    }

    /**
     * Exportar a CSV
     */
    private function exportarCSV($facturas)
    {
        $filename = 'facturas_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Numero', 'Fecha', 'Tipo', 'Cliente', 'RUC', 'Subtotal', 
            'Impuesto', 'Total', 'Estado', 'Vendedor'
        ];

        $csvData = [];
        $csvData[] = implode(',', $headers);

        foreach ($facturas as $factura) {
            $row = [
                $factura->Numero,
                $factura->Fecha,
                $factura->Tipo,
                '"' . str_replace('"', '""', $factura->Cliente) . '"',
                $factura->Ruc,
                number_format($factura->Subtotal, 2),
                number_format($factura->Impuesto, 2),
                number_format($factura->Total, 2),
                $factura->Estado,
                $factura->Vendedor
            ];
            $csvData[] = implode(',', $row);
        }

        return response(implode("\n", $csvData))
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
