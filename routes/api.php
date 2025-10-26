<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API SIFANO - Sistema de Farmacia
|--------------------------------------------------------------------------
|
| API Routes para el sistema SIFANO organizados por módulos:
| - Farmacia: Control temperatura, trazabilidad, inventario
| - Ventas: Facturación, búsqueda clientes, KPIs
| - Contabilidad: Libros electrónicos, reportes SUNAT
| - Clientes: Gestión general
|
*/

// ============================================================================
// RUTAS DE AUTENTICACIÓN API
// ============================================================================

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login'])->name('login');
    Route::post('logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
    Route::get('me', [App\Http\Controllers\Api\AuthController::class, 'me'])->middleware('auth:sanctum')->name('me');
    Route::post('refresh', [App\Http\Controllers\Api\AuthController::class, 'refresh'])->middleware('auth:sanctum')->name('refresh');
});

// ============================================================================
// RUTAS PROTEGIDAS CON SANCTUM
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {

    // ============================================================================
    // API DASHBOARD GENERAL
    // ============================================================================
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('resumen', [App\Http\Controllers\Api\DashboardController::class, 'resumen'])->name('resumen');
        Route::get('metricas-tiempo-real', [App\Http\Controllers\Api\DashboardController::class, 'metricasTiempoReal'])->name('metricas-tiempo-real');
        Route::get('alertas-activas', [App\Http\Controllers\Api\DashboardController::class, 'alertasActivas'])->name('alertas-activas');
        Route::get('notificaciones', [App\Http\Controllers\Api\DashboardController::class, 'notificaciones'])->name('notificaciones');
        Route::post('marcar-notificacion-leida/{id}', [App\Http\Controllers\Api\DashboardController::class, 'marcarLeida'])->name('marcar-leida');
    });

    // ============================================================================
    // API MÓDULO FARMACIA
    // ============================================================================
    Route::prefix('farmacia')->name('farmacia.')->group(function () {
        
        // Control de Temperatura
        Route::prefix('temperatura')->name('temperatura.')->group(function () {
            Route::get('sensores', [App\Http\Controllers\Api\Farmacia\ControlTemperaturaApiController::class, 'getSensores'])->name('sensores');
            Route::post('sensores', [App\Http\Controllers\Api\Farmacia\ControlTemperaturaApiController::class, 'createSensor'])->name('sensores.create');
            Route::put('sensores/{sensor}', [App\Http\Controllers\Api\Farmacia\ControlTemperaturaApiController::class, 'updateSensor'])->name('sensores.update');
            Route::delete('sensores/{sensor}', [App\Http\Controllers\Api\Farmacia\ControlTemperaturaApiController::class, 'deleteSensor'])->name('sensores.delete');
            
            Route::get('lecturas-tiempo-real', [App\Http\Controllers\Api\Farmacia\ControlTemperaturaApiController::class, 'getLecturasTiempoReal'])->name('lecturas-tiempo-real');
            Route::get('alertas', [App\Http\Controllers\Api\Farmacia\ControlTemperaturaApiController::class, 'getAlertas'])->name('alertas');
            Route::post('resolver-alerta/{alerta}', [App\Http\Controllers\Api\Farmacia\ControlTemperaturaApiController::class, 'resolverAlerta'])->name('resolver-alerta');
            
            Route::get('historial/{sensor}', [App\Http\Controllers\Api\Farmacia\ControlTemperaturaApiController::class, 'getHistorial'])->name('historial');
            Route::post('configurar-umbrales', [App\Http\Controllers\Api\Farmacia\ControlTemperaturaApiController::class, 'configurarUmbrales'])->name('umbrales');
            
            Route::get('reportes/{tipo}', [App\Http\Controllers\Api\Farmacia\ControlTemperaturaApiController::class, 'generarReporte'])->name('reportes');
        });
        
        // Trazabilidad
        Route::prefix('trazabilidad')->name('trazabilidad.')->group(function () {
            Route::get('lotes', [App\Http\Controllers\Api\Farmacia\TrazabilidadApiController::class, 'getLotes'])->name('lotes');
            Route::post('lotes', [App\Http\Controllers\Api\Farmacia\TrazabilidadApiController::class, 'createLote'])->name('lotes.create');
            Route::get('lotes/{lote}', [App\Http\Controllers\Api\Farmacia\TrazabilidadApiController::class, 'getLote'])->name('lotes.show');
            Route::put('lotes/{lote}', [App\Http\Controllers\Api\Farmacia\TrazabilidadApiController::class, 'updateLote'])->name('lotes.update');
            
            Route::get('movimientos', [App\Http\Controllers\Api\Farmacia\TrazabilidadApiController::class, 'getMovimientos'])->name('movimientos');
            Route::post('movimientos', [App\Http\Controllers\Api\Farmacia\TrazabilidadApiController::class, 'createMovimiento'])->name('movimientos.create');
            
            Route::post('scanner/validar', [App\Http\Controllers\Api\Farmacia\TrazabilidadApiController::class, 'validarCodigo'])->name('scanner.validar');
            Route::get('scanner/producto/{codigo}', [App\Http\Controllers\Api\Farmacia\TrazabilidadApiController::class, 'buscarProducto'])->name('scanner.producto');
            
            Route::get('buscar', [App\Http\Controllers\Api\Farmacia\TrazabilidadApiController::class, 'buscar'])->name('buscar');
            Route::get('reportes/{lote}', [App\Http\Controllers\Api\Farmacia\TrazabilidadApiController::class, 'generarReporteLote'])->name('reportes.lote');
        });
        
        // Inventario
        Route::prefix('inventario')->name('inventario.')->group(function () {
            Route::get('productos', [App\Http\Controllers\Api\Farmacia\InventarioApiController::class, 'getProductos'])->name('productos');
            Route::post('productos', [App\Http\Controllers\Api\Farmacia\InventarioApiController::class, 'createProducto'])->name('productos.create');
            Route::get('productos/{producto}', [App\Http\Controllers\Api\Farmacia\InventarioApiController::class, 'getProducto'])->name('productos.show');
            Route::put('productos/{producto}', [App\Http\Controllers\Api\Farmacia\InventarioApiController::class, 'updateProducto'])->name('productos.update');
            Route::delete('productos/{producto}', [App\Http\Controllers\Api\Farmacia\InventarioApiController::class, 'deleteProducto'])->name('productos.delete');
            
            Route::get('stock', [App\Http\Controllers\Api\Farmacia\InventarioApiController::class, 'getStock'])->name('stock');
            Route::post('ajustar-stock', [App\Http\Controllers\Api\Farmacia\InventarioApiController::class, 'ajustarStock'])->name('ajustar-stock');
            Route::get('alertas-stock', [App\Http\Controllers\Api\Farmacia\InventarioApiController::class, 'getAlertasStock'])->name('alertas-stock');
            
            Route::get('ordenes-compra', [App\Http\Controllers\Api\Farmacia\InventarioApiController::class, 'getOrdenesCompra'])->name('ordenes-compra');
            Route::post('ordenes-compra', [App\Http\Controllers\Api\Farmacia\InventarioApiController::class, 'createOrdenCompra'])->name('ordenes-compra.create');
            Route::put('ordenes-compra/{orden}/aprobar', [App\Http\Controllers\Api\Farmacia\InventarioApiController::class, 'aprobarOrden'])->name('ordenes-compra.aprobar');
            
            Route::get('reportes/movimientos', [App\Http\Controllers\Api\Farmacia\InventarioApiController::class, 'getReportesMovimientos'])->name('reportes.movimientos');
        });
    });

    // ============================================================================
    // API MÓDULO VENTAS
    // ============================================================================
    Route::prefix('ventas')->name('ventas.')->group(function () {
        
        // Facturación Rápida
        Route::prefix('facturacion')->name('facturacion.')->group(function () {
            Route::post('procesar', [App\Http\Controllers\Api\Ventas\FacturacionApiController::class, 'procesarVenta'])->name('procesar');
            Route::post('validar-prescripcion', [App\Http\Controllers\Api\Ventas\FacturacionApiController::class, 'validarPrescripcion'])->name('validar-prescripcion');
            Route::post('aplicar-descuento', [App\Http\Controllers\Api\Ventas\FacturacionApiController::class, 'aplicarDescuento'])->name('aplicar-descuento');
            Route::post('procesar-pago', [App\Http\Controllers\Api\Ventas\FacturacionApiController::class, 'procesarPago'])->name('procesar-pago');
            
            Route::get('buscar-productos', [App\Http\Controllers\Api\Ventas\FacturacionApiController::class, 'buscarProductos'])->name('buscar-productos');
            Route::get('precios/{producto}', [App\Http\Controllers\Api\Ventas\FacturacionApiController::class, 'getPrecios'])->name('precios');
            Route::post('calcular-total', [App\Http\Controllers\Api\Ventas\FacturacionApiController::class, 'calcularTotal'])->name('calcular-total');
            
            Route::get('comprobante/{venta}', [App\Http\Controllers\Api\Ventas\FacturacionApiController::class, 'generarComprobante'])->name('comprobante');
        });
        
        // Buscar Cliente
        Route::prefix('clientes')->name('clientes.')->group(function () {
            Route::get('buscar', [App\Http\Controllers\Api\Ventas\ClientesApiController::class, 'buscar'])->name('buscar');
            Route::get('{cliente}', [App\Http\Controllers\Api\Ventas\ClientesApiController::class, 'show'])->name('show');
            Route::post('/', [App\Http\Controllers\Api\Ventas\ClientesApiController::class, 'store'])->name('store');
            Route::put('{cliente}', [App\Http\Controllers\Api\Ventas\ClientesApiController::class, 'update'])->name('update');
            
            Route::get('{cliente}/historial', [App\Http\Controllers\Api\Ventas\ClientesApiController::class, 'historial'])->name('historial');
            Route::get('{cliente}/estadisticas', [App\Http\Controllers\Api\Ventas\ClientesApiController::class, 'estadisticas'])->name('estadisticas');
            
            Route::get('autocomplete/{tipo}', [App\Http\Controllers\Api\Ventas\ClientesApiController::class, 'autocomplete'])->name('autocomplete');
            Route::post('exportar', [App\Http\Controllers\Api\Ventas\ClientesApiController::class, 'exportar'])->name('exportar');
        });
        
        // KPIs Dashboard
        Route::prefix('kpis')->name('kpis.')->group(function () {
            Route::get('metricas', [App\Http\Controllers\Api\Ventas\KpisApiController::class, 'getMetricas'])->name('metricas');
            Route::get('ventas-chart', [App\Http\Controllers\Api\Ventas\KpisApiController::class, 'getVentasChart'])->name('ventas-chart');
            Route::get('productos-top', [App\Http\Controllers\Api\Ventas\KpisApiController::class, 'getProductosTop'])->name('productos-top');
            Route::get('ventas-tiempo-real', [App\Http\Controllers\Api\Ventas\KpisApiController::class, 'getVentasTiempoReal'])->name('ventas-tiempo-real');
            
            Route::post('filtrar-periodo', [App\Http\Controllers\Api\Ventas\KpisApiController::class, 'filtrarPeriodo'])->name('filtrar-periodo');
            Route::get('analisis-productos', [App\Http\Controllers\Api\Ventas\KpisApiController::class, 'analisisProductos'])->name('analisis-productos');
            Route::get('tendencias', [App\Http\Controllers\Api\Ventas\KpisApiController::class, 'tendencias'])->name('tendencias');
        });
    });

    // ============================================================================
    // API MÓDULO CONTABILIDAD
    // ============================================================================
    Route::prefix('contabilidad')->name('contabilidad.')->group(function () {
        
        // Libros Electrónicos
        Route::prefix('libros')->name('libros.')->group(function () {
            Route::get('asientos', [App\Http\Controllers\Api\Contabilidad\LibrosApiController::class, 'getAsientos'])->name('asientos');
            Route::post('asientos', [App\Http\Controllers\Api\Contabilidad\LibrosApiController::class, 'createAsiento'])->name('asientos.create');
            Route::get('asientos/{asiento}', [App\Http\Controllers\Api\Contabilidad\LibrosApiController::class, 'getAsiento'])->name('asientos.show');
            Route::put('asientos/{asiento}', [App\Http\Controllers\Api\Contabilidad\LibrosApiController::class, 'updateAsiento'])->name('asientos.update');
            Route::delete('asientos/{asiento}', [App\Http\Controllers\Api\Contabilidad\LibrosApiController::class, 'deleteAsiento'])->name('asientos.delete');
            
            Route::get('libro-mayor', [App\Http\Controllers\Api\Contabilidad\LibrosApiController::class, 'getLibroMayor'])->name('libro-mayor');
            Route::get('balance-comprobacion', [App\Http\Controllers\Api\Contabilidad\LibrosApiController::class, 'getBalanceComprobacion'])->name('balance-comprobacion');
            
            Route::get('plan-cuentas', [App\Http\Controllers\Api\Contabilidad\LibrosApiController::class, 'getPlanCuentas'])->name('plan-cuentas');
            Route::post('plan-cuentas', [App\Http\Controllers\Api\Contabilidad\LibrosApiController::class, 'createCuenta'])->name('plan-cuentas.create');
            
            Route::get('cuentas-disponibles', [App\Http\Controllers\Api\Contabilidad\LibrosApiController::class, 'getCuentasDisponibles'])->name('cuentas-disponibles');
            Route::post('validar-asiento', [App\Http\Controllers\Api\Contabilidad\LibrosApiController::class, 'validarAsiento'])->name('validar-asiento');
        });
        
        // Reportes SUNAT
        Route::prefix('sunat')->name('sunat.')->group(function () {
            Route::get('pdt', [App\Http\Controllers\Api\Contabilidad\SunatApiController::class, 'getPdt'])->name('pdt');
            Route::post('pdt/generar', [App\Http\Controllers\Api\Contabilidad\SunatApiController::class, 'generarPdt'])->name('pdt.generar');
            Route::get('pdt/{pdt}/descargar', [App\Http\Controllers\Api\Contabilidad\SunatApiController::class, 'descargarPdt'])->name('pdt.descargar');
            
            Route::get('libros-electronicos', [App\Http\Controllers\Api\Contabilidad\SunatApiController::class, 'getLibrosElectronicos'])->name('libros-electronicos');
            Route::post('libros-electronicos/exportar', [App\Http\Controllers\Api\Contabilidad\SunatApiController::class, 'exportarLibros'])->name('libros-electronicos.exportar');
            
            Route::get('formularios', [App\Http\Controllers\Api\Contabilidad\SunatApiController::class, 'getFormularios'])->name('formularios');
            Route::post('formularios/{formulario}/generar', [App\Http\Controllers\Api\Contabilidad\SunatApiController::class, 'generarFormulario'])->name('formularios.generar');
            
            Route::get('calendario-tributario', [App\Http\Controllers\Api\Contabilidad\SunatApiController::class, 'getCalendarioTributario'])->name('calendario-tributario');
            Route::get('validaciones', [App\Http\Controllers\Api\Contabilidad\SunatApiController::class, 'getValidaciones'])->name('validaciones');
            
            Route::get('deudas-pendientes', [App\Http\Controllers\Api\Contabilidad\SunatApiController::class, 'getDeudasPendientes'])->name('deudas-pendientes');
            Route::post('validar-rut', [App\Http\Controllers\Api\Contabilidad\SunatApiController::class, 'validarRut'])->name('validar-rut');
            Route::get('fechas-vigentes', [App\Http\Controllers\Api\Contabilidad\SunatApiController::class, 'getFechasVigentes'])->name('fechas-vigentes');
        });
    });

    // ============================================================================
    // API MÓDULO CLIENTES GENERAL
    // ============================================================================
    Route::prefix('clientes-general')->name('clientes-general.')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\Clientes\ClientesApiController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Api\Clientes\ClientesApiController::class, 'store'])->name('store');
        Route::get('{cliente}', [App\Http\Controllers\Api\Clientes\ClientesApiController::class, 'show'])->name('show');
        Route::put('{cliente}', [App\Http\Controllers\Api\Clientes\ClientesApiController::class, 'update'])->name('update');
        Route::delete('{cliente}', [App\Http\Controllers\Api\Clientes\ClientesApiController::class, 'destroy'])->name('destroy');
        
        Route::get('exportar/excel', [App\Http\Controllers\Api\Clientes\ClientesApiController::class, 'exportarExcel'])->name('exportar.excel');
        Route::get('exportar/pdf', [App\Http\Controllers\Api\Clientes\ClientesApiController::class, 'exportarPdf'])->name('exportar.pdf');
    });

    // ============================================================================
    // API ENDPOINTS UTILITARIOS
    // ============================================================================
    Route::prefix('utilidades')->name('utilidades.')->group(function () {
        Route::post('calcular-precio', function (Request $request) {
            return response()->json([
                'precio_base' => $request->input('precio', 0),
                'descuento' => $request->input('descuento', 0),
                'impuesto' => $request->input('impuesto', 0),
                'precio_final' => $request->input('precio', 0) - $request->input('descuento', 0) + $request->input('impuesto', 0)
            ]);
        })->name('calcular-precio');

        Route::post('generar-numero', function (Request $request) {
            $tipo = $request->input('tipo', 'FACTURA');
            $numero = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            return response()->json([
                'numero' => $numero,
                'tipo' => $tipo,
                'fecha' => now()->format('Y-m-d'),
                'serie' => 'F001'
            ]);
        })->name('generar-numero');

        Route::post('validar-documento', function (Request $request) {
            $documento = $request->input('documento', '');
            $tipo = $request->input('tipo', 'DNI');
            
            $valido = match($tipo) {
                'DNI' => strlen($documento) === 8 && is_numeric($documento),
                'RUC' => strlen($documento) === 11 && is_numeric($documento),
                default => false
            };
            
            return response()->json([
                'valido' => $valido,
                'documento' => $documento,
                'tipo' => $tipo
            ]);
        })->name('validar-documento');

        Route::post('upload-file', [App\Http\Controllers\Api\Utilidades\UploadController::class, 'upload'])->name('upload-file');
    });
});

// ============================================================================
// ENDPOINTS PÚBLICOS (SIN AUTENTICACIÓN)
// ============================================================================

Route::prefix('publico')->name('publico.')->group(function () {
    Route::get('productos', function () {
        // Lista pública de productos para consulta móvil
        return response()->json([
            'productos' => \App\Models\Producto::select('CodPro', 'Nombre', 'Precio', 'Stock', 'Imagen')->get()
        ]);
    })->name('productos');
    
    Route::get('productos/{codpro}', function ($codpro) {
        // Detalle público de producto
        $producto = \App\Models\Producto::where('CodPro', $codpro)->first();
        
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
        
        return response()->json([
            'producto' => $producto
        ]);
    })->name('producto.detalle');
    
    Route::get('salud-precios', function () {
        return response()->json([
            'mensaje' => 'Información disponible en el sistema principal'
        ]);
    })->name('salud-precios');
    
    Route::get('farmacias-cercanas', function () {
        return response()->json([
            'farmacias' => [
                [
                    'nombre' => 'Farmacia SIFANO Principal',
                    'direccion' => 'Av. Principal 123',
                    'telefono' => '+51 999 999 999',
                    'horarios' => '24 horas'
                ]
            ]
        ]);
    })->name('farmacias-cercanas');
});

// ============================================================================
// ENDPOINT PARA USUARIO AUTENTICADO
// ============================================================================

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});