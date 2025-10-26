<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| RUTAS WEB SIFANO - Sistema de Farmacia
|--------------------------------------------------------------------------
|
| Aquí están todas las rutas del sistema SIFANO organizadas por módulos:
| - Farmacia: Control temperatura, trazabilidad, inventario
| - Ventas: Facturación rápida, buscar cliente, KPIs dashboard
| - Contabilidad: Libros electrónicos, reportes SUNAT
| - Clientes: Gestión general de clientes
| - Componentes: Utilidades y componentes
|
*/

// ============================================================================
// RUTAS DE AUTENTICACIÓN Y SEGURIDAD
// ============================================================================

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
Route::get('/register', function () {
    return view('auth.register');
})->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Sistema de sesiones y seguridad
Route::post('/session/ping', function () {
    return response()->json([
        'status' => 'ok',
        'time' => now()->toDateTimeString()
    ]);
})->name('session.ping');

Route::middleware(['security'])->group(function () {
    
    // ============================================================================
    // DASHBOARD PRINCIPAL
    // ============================================================================
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // ============================================================================
    // MÓDULO FARMACIAS
    // ============================================================================
    Route::prefix('farmacia')->name('farmacia.')->middleware(['check.admin', 'check.vendedor'])->group(function () {
        
        // Dashboard de Farmacia
        Route::get('/', [App\Http\Controllers\Farmacia\DashboardController::class, 'index'])->name('dashboard');
        
        // Control de Temperatura
        Route::prefix('temperatura')->name('temperatura.')->group(function () {
            Route::get('/', [App\Http\Controllers\Farmacia\ControlTemperaturaController::class, 'index'])->name('index');
            Route::get('/sensores', [App\Http\Controllers\Farmacia\ControlTemperaturaController::class, 'sensores'])->name('sensores');
            Route::post('/sensores', [App\Http\Controllers\Farmacia\ControlTemperaturaController::class, 'storeSensor'])->name('sensores.store');
            Route::put('/sensores/{sensor}', [App\Http\Controllers\Farmacia\ControlTemperaturaController::class, 'updateSensor'])->name('sensores.update');
            Route::delete('/sensores/{sensor}', [App\Http\Controllers\Farmacia\ControlTemperaturaController::class, 'destroySensor'])->name('sensores.destroy');
            
            Route::get('/alertas', [App\Http\Controllers\Farmacia\ControlTemperaturaController::class, 'alertas'])->name('alertas');
            Route::post('/alertas/{alerta}/resolver', [App\Http\Controllers\Farmacia\ControlTemperaturaController::class, 'resolverAlerta'])->name('alertas.resolver');
            
            Route::get('/reportes', [App\Http\Controllers\Farmacia\ControlTemperaturaController::class, 'reportes'])->name('reportes');
            Route::get('/reportes/{sensor}/historial', [App\Http\Controllers\Farmacia\ControlTemperaturaController::class, 'historial'])->name('historial');
            
            Route::post('/umbrales', [App\Http\Controllers\Farmacia\ControlTemperaturaController::class, 'configurarUmbrales'])->name('umbrales');
            
            // API para datos en tiempo real
            Route::get('/api/datos-tiempo-real', [App\Http\Controllers\Farmacia\ControlTemperaturaController::class, 'getDatosTiempoReal'])->name('api.datos-tiempo-real');
            Route::get('/api/alertas-activas', [App\Http\Controllers\Farmacia\ControlTemperaturaController::class, 'getAlertasActivas'])->name('api.alertas-activas');
        });
        
        // Trazabilidad
        Route::prefix('trazabilidad')->name('trazabilidad.')->group(function () {
            Route::get('/', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'index'])->name('index');
            
            Route::get('/lotes', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'lotes'])->name('lotes');
            Route::post('/lotes', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'storeLote'])->name('lotes.store');
            Route::get('/lotes/{lote}', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'showLote'])->name('lotes.show');
            Route::put('/lotes/{lote}', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'updateLote'])->name('lotes.update');
            
            Route::get('/movimientos', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'movimientos'])->name('movimientos');
            Route::post('/movimientos', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'storeMovimiento'])->name('movimientos.store');
            Route::get('/movimientos/{movimiento}', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'showMovimiento'])->name('movimientos.show');
            
            Route::get('/scanner', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'scanner'])->name('scanner');
            Route::post('/scanner/procesar', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'procesarCodigo'])->name('scanner.procesar');
            
            Route::get('/buscar', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'buscar'])->name('buscar');
            Route::get('/reportes', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'reportes'])->name('reportes');
            Route::get('/reportes/{lote}/pdf', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'generarReporteLote'])->name('reportes.pdf');
            
            // API para scanner
            Route::post('/api/validar-codigo', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'validarCodigo'])->name('api.validar-codigo');
            Route::get('/api/buscar-producto/{codigo}', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'buscarProducto'])->name('api.buscar-producto');
        });
        
        // Inventario
        Route::prefix('inventario')->name('inventario.')->group(function () {
            Route::get('/', [App\Http\Controllers\Farmacia\InventarioController::class, 'index'])->name('index');
            Route::get('/productos', [App\Http\Controllers\Farmacia\InventarioController::class, 'productos'])->name('productos');
            Route::post('/productos', [App\Http\Controllers\Farmacia\InventarioController::class, 'storeProducto'])->name('productos.store');
            Route::get('/productos/{producto}', [App\Http\Controllers\Farmacia\InventarioController::class, 'showProducto'])->name('productos.show');
            Route::put('/productos/{producto}', [App\Http\Controllers\Farmacia\InventarioController::class, 'updateProducto'])->name('productos.update');
            Route::delete('/productos/{producto}', [App\Http\Controllers\Farmacia\InventarioController::class, 'destroyProducto'])->name('productos.destroy');
            
            Route::get('/stock', [App\Http\Controllers\Farmacia\InventarioController::class, 'stock'])->name('stock');
            Route::post('/ajustes', [App\Http\Controllers\Farmacia\InventarioController::class, 'ajustarStock'])->name('ajustes.store');
            Route::get('/alertas-stock', [App\Http\Controllers\Farmacia\InventarioController::class, 'alertasStock'])->name('alertas-stock');
            
            Route::get('/ordenes-compra', [App\Http\Controllers\Farmacia\InventarioController::class, 'ordenesCompra'])->name('ordenes-compra');
            Route::post('/ordenes-compra', [App\Http\Controllers\Farmacia\InventarioController::class, 'storeOrdenCompra'])->name('ordenes-compra.store');
            Route::get('/ordenes-compra/{orden}', [App\Http\Controllers\Farmacia\InventarioController::class, 'showOrdenCompra'])->name('ordenes-compra.show');
            Route::put('/ordenes-compra/{orden}/aprobar', [App\Http\Controllers\Farmacia\InventarioController::class, 'aprobarOrdenCompra'])->name('ordenes-compra.aprobar');
            
            Route::get('/reportes', [App\Http\Controllers\Farmacia\InventarioController::class, 'reportes'])->name('reportes');
            Route::get('/reportes/movimientos', [App\Http\Controllers\Farmacia\InventarioController::class, 'reportesMovimientos'])->name('reportes.movimientos');
            
            // API para inventario
            Route::get('/api/productos-disponibles', [App\Http\Controllers\Farmacia\InventarioController::class, 'getProductosDisponibles'])->name('api.productos-disponibles');
            Route::get('/api/stock-minimo', [App\Http\Controllers\Farmacia\InventarioController::class, 'getStockMinimo'])->name('api.stock-minimo');
            Route::post('/api/transferir-stock', [App\Http\Controllers\Farmacia\InventarioController::class, 'transferirStock'])->name('api.transferir-stock');
        });
    });
    
    // ============================================================================
    // MÓDULO VENTAS
    // ============================================================================
    Route::prefix('ventas')->name('ventas.')->middleware(['check.admin', 'check.vendedor'])->group(function () {
        
        // Dashboard de Ventas
        Route::get('/', [App\Http\Controllers\Ventas\DashboardController::class, 'index'])->name('dashboard');
        
        // Facturación Rápida
        Route::prefix('facturacion-rapida')->name('facturacion.')->group(function () {
            Route::get('/', [App\Http\Controllers\Ventas\FacturacionRapidaController::class, 'index'])->name('index');
            Route::post('/procesar', [App\Http\Controllers\Ventas\FacturacionRapidaController::class, 'procesarVenta'])->name('procesar');
            Route::post('/validar-prescripcion', [App\Http\Controllers\Ventas\FacturacionRapidaController::class, 'validarPrescripcion'])->name('validar-prescripcion');
            Route::post('/aplicar-descuento', [App\Http\Controllers\Ventas\FacturacionRapidaController::class, 'aplicarDescuento'])->name('aplicar-descuento');
            Route::post('/procesar-pago', [App\Http\Controllers\Ventas\FacturacionRapidaController::class, 'procesarPago'])->name('procesar-pago');
            Route::get('/comprobante/{venta}', [App\Http\Controllers\Ventas\FacturacionRapidaController::class, 'generarComprobante'])->name('comprobante');
            
            // API para facturación
            Route::get('/api/buscar-productos', [App\Http\Controllers\Ventas\FacturacionRapidaController::class, 'buscarProductos'])->name('api.buscar-productos');
            Route::get('/api/precios/{producto}', [App\Http\Controllers\Ventas\FacturacionRapidaController::class, 'getPrecios'])->name('api.precios');
            Route::post('/api/calcular-total', [App\Http\Controllers\Ventas\FacturacionRapidaController::class, 'calcularTotal'])->name('api.calcular-total');
        });
        
        // Buscar Cliente
        Route::prefix('clientes')->name('clientes.')->group(function () {
            Route::get('/', [App\Http\Controllers\Ventas\BuscarClienteController::class, 'index'])->name('index');
            Route::get('/buscar', [App\Http\Controllers\Ventas\BuscarClienteController::class, 'buscar'])->name('buscar');
            Route::get('/{cliente}', [App\Http\Controllers\Ventas\BuscarClienteController::class, 'show'])->name('show');
            Route::post('/', [App\Http\Controllers\Ventas\BuscarClienteController::class, 'store'])->name('store');
            Route::put('/{cliente}', [App\Http\Controllers\Ventas\BuscarClienteController::class, 'update'])->name('update');
            
            Route::get('/{cliente}/historial', [App\Http\Controllers\Ventas\BuscarClienteController::class, 'historial'])->name('historial');
            Route::get('/{cliente}/estadisticas', [App\Http\Controllers\Ventas\BuscarClienteController::class, 'estadisticas'])->name('estadisticas');
            
            Route::post('/exportar', [App\Http\Controllers\Ventas\BuscarClienteController::class, 'exportar'])->name('exportar');
            
            // API para búsqueda de clientes
            Route::get('/api/buscar', [App\Http\Controllers\Ventas\BuscarClienteController::class, 'apiBuscar'])->name('api.buscar');
            Route::get('/api/autocomplete/{tipo}', [App\Http\Controllers\Ventas\BuscarClienteController::class, 'autocomplete'])->name('api.autocomplete');
        });
        
        // KPIs Dashboard
        Route::prefix('kpis')->name('kpis.')->group(function () {
            Route::get('/', [App\Http\Controllers\Ventas\KpisDashboardController::class, 'index'])->name('index');
            Route::get('/ventas-tiempo-real', [App\Http\Controllers\Ventas\KpisDashboardController::class, 'ventasTiempoReal'])->name('ventas-tiempo-real');
            Route::get('/analisis-productos', [App\Http\Controllers\Ventas\KpisDashboardController::class, 'analisisProductos'])->name('analisis-productos');
            Route::get('/tendencias', [App\Http\Controllers\Ventas\KpisDashboardController::class, 'tendencias'])->name('tendencias');
            
            // API para KPIs
            Route::get('/api/metricas', [App\Http\Controllers\Ventas\KpisDashboardController::class, 'getMetricas'])->name('api.metricas');
            Route::get('/api/ventas-chart', [App\Http\Controllers\Ventas\KpisDashboardController::class, 'getVentasChart'])->name('api.ventas-chart');
            Route::get('/api/productos-top', [App\Http\Controllers\Ventas\KpisDashboardController::class, 'getProductosTop'])->name('api.productos-top');
            Route::post('/api/filtrar-periodo', [App\Http\Controllers\Ventas\KpisDashboardController::class, 'filtrarPeriodo'])->name('api.filtrar-periodo');
        });
    });
    
    // ============================================================================
    // MÓDULO CONTABILIDAD
    // ============================================================================
    Route::prefix('contabilidad')->name('contabilidad.')->middleware(['check.admin', 'check.contador'])->group(function () {
        
        // Dashboard Contabilidad
        Route::get('/', [App\Http\Controllers\Contabilidad\DashboardController::class, 'index'])->name('dashboard');
        
        // Libros Electrónicos
        Route::prefix('libros-electronicos')->name('libros.')->group(function () {
            Route::get('/', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'index'])->name('index');
            
            Route::get('/asientos', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'asientos'])->name('asientos');
            Route::post('/asientos', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'storeAsiento'])->name('asientos.store');
            Route::get('/asientos/{asiento}', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'showAsiento'])->name('asientos.show');
            Route::put('/asientos/{asiento}', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'updateAsiento'])->name('asientos.update');
            Route::delete('/asientos/{asiento}', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'destroyAsiento'])->name('asientos.destroy');
            
            Route::get('/libro-mayor', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'libroMayor'])->name('libro-mayor');
            Route::get('/balance-comprobacion', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'balanceComprobacion'])->name('balance-comprobacion');
            
            Route::get('/plan-cuentas', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'planCuentas'])->name('plan-cuentas');
            Route::post('/plan-cuentas', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'storeCuenta'])->name('plan-cuentas.store');
            
            Route::get('/reportes', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'reportes'])->name('reportes');
            Route::post('/exportar', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'exportar'])->name('exportar');
            
            // API para libros electrónicos
            Route::get('/api/cuentas-disponibles', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'getCuentasDisponibles'])->name('api.cuentas-disponibles');
            Route::post('/api/validar-asiento', [App\Http\Controllers\Contabilidad\LibrosElectronicosController::class, 'validarAsiento'])->name('api.validar-asiento');
        });
        
        // Reportes SUNAT
        Route::prefix('sunat')->name('sunat.')->group(function () {
            Route::get('/', [App\Http\Controllers\Contabilidad\ReportesSunatController::class, 'index'])->name('index');
            
            Route::get('/pdt', [App\Http\Controllers\Contabilidad\ReportesSunatController::class, 'pdt'])->name('pdt');
            Route::post('/pdt/generar', [App\Http\Controllers\Contabilidad\ReportesSunatController::class, 'generarPdt'])->name('pdt.generar');
            Route::get('/pdt/{pdt}/descargar', [App\Http\Controllers\Contabilidad\ReportesSunatController::class, 'descargarPdt'])->name('pdt.descargar');
            
            Route::get('/libros-electronicos', [App\Http\Controllers\Contabilidad\ReportesSunatController::class, 'librosElectronicos'])->name('libros-electronicos');
            Route::post('/libros-electronicos/exportar', [App\Http\Controllers\Contabilidad\ReportesSunatController::class, 'exportarLibros'])->name('libros-electronicos.exportar');
            
            Route::get('/formularios', [App\Http\Controllers\Contabilidad\ReportesSunatController::class, 'formularios'])->name('formularios');
            Route::post('/formularios/{formulario}/generar', [App\Http\Controllers\Contabilidad\ReportesSunatController::class, 'generarFormulario'])->name('formularios.generar');
            
            Route::get('/calendario-tributario', [App\Http\Controllers\Contabilidad\ReportesSunatController::class, 'calendarioTributario'])->name('calendario-tributario');
            
            Route::get('/validaciones', [App\Http\Controllers\Contabilidad\ReportesSunatController::class, 'validaciones'])->name('validaciones');
            
            // API para SUNAT
            Route::get('/api/deudas-pendientes', [App\Http\Controllers\Contabilidad\ReportesSunatController::class, 'getDeudasPendientes'])->name('api.deudas-pendientes');
            Route::post('/api/validar-rut', [App\Http\Controllers\Contabilidad\ReportesSunatController::class, 'validarRut'])->name('api.validar-rut');
            Route::get('/api/fechas-vigentes', [App\Http\Controllers\Contabilidad\ReportesSunatController::class, 'getFechasVigentes'])->name('api.fechas-vigentes');
        });
    });
    
    // ============================================================================
    // MÓDULO CLIENTES (GENERAL)
    // ============================================================================
    Route::prefix('clientes-general')->name('clientes-general.')->middleware(['check.admin'])->group(function () {
        Route::get('/', [App\Http\Controllers\Clientes\ClientesController::class, 'index'])->name('index');
        Route::get('/crear', [App\Http\Controllers\Clientes\ClientesController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Clientes\ClientesController::class, 'store'])->name('store');
        Route::get('/{cliente}', [App\Http\Controllers\Clientes\ClientesController::class, 'show'])->name('show');
        Route::get('/{cliente}/editar', [App\Http\Controllers\Clientes\ClientesController::class, 'edit'])->name('edit');
        Route::put('/{cliente}', [App\Http\Controllers\Clientes\ClientesController::class, 'update'])->name('update');
        Route::delete('/{cliente}', [App\Http\Controllers\Clientes\ClientesController::class, 'destroy'])->name('destroy');
        
        Route::get('/exportar/excel', [App\Http\Controllers\Clientes\ClientesController::class, 'exportarExcel'])->name('exportar.excel');
        Route::get('/exportar/pdf', [App\Http\Controllers\Clientes\ClientesController::class, 'exportarPdf'])->name('exportar.pdf');
    });
    

    
    // ============================================================================
    // RUTAS DE VENDEDOR
    // ============================================================================
    Route::prefix('vendedor')->name('vendedor.')->middleware(['check.vendedor'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Vendedor\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/mis-cobranzas', [App\Http\Controllers\Vendedor\DashboardController::class, 'misCobranzas'])->name('mis-cobranzas');
        Route::get('/metas', [App\Http\Controllers\Vendedor\DashboardController::class, 'verMetas'])->name('metas');
        Route::post('/actualizar-meta', [App\Http\Controllers\Vendedor\DashboardController::class, 'actualizarMeta'])->name('actualizar-meta');
    });
    
    // ============================================================================
    // COMPONENTES Y UTILIDADES
    // ============================================================================
    Route::prefix('componentes')->name('componentes.')->group(function () {
        Route::get('/modals', [App\Http\Controllers\ComponentController::class, 'modals'])->name('modals');
        Route::get('/tablas', [App\Http\Controllers\ComponentController::class, 'tablas'])->name('tablas');
        Route::get('/formularios', [App\Http\Controllers\ComponentController::class, 'formularios'])->name('formularios');
        Route::get('/graficos', [App\Http\Controllers\ComponentController::class, 'graficos'])->name('graficos');
        Route::get('/alertas', [App\Http\Controllers\ComponentController::class, 'alertas'])->name('alertas');
    });
    
    // ============================================================================
    // RUTAS DE PERFIL Y CONFIGURACIÓN
    // ============================================================================
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/', [App\Http\Controllers\ProfileController::class, 'show'])->name('show');
        Route::put('/', [App\Http\Controllers\ProfileController::class, 'update'])->name('update');
        Route::put('/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('password');
    });
    
    Route::prefix('configuracion')->name('configuracion.')->middleware(['check.admin'])->group(function () {
        Route::get('/', [App\Http\Controllers\ConfiguracionController::class, 'index'])->name('index');
        Route::put('/empresa', [App\Http\Controllers\ConfiguracionController::class, 'updateEmpresa'])->name('empresa');
        Route::put('/facturacion', [App\Http\Controllers\ConfiguracionController::class, 'updateFacturacion'])->name('facturacion');
        Route::put('/notificaciones', [App\Http\Controllers\ConfiguracionController::class, 'updateNotificaciones'])->name('notificaciones');
    });
    
});

// ============================================================================
// RUTAS DE ERRORES Y MAINTENANCE
// ============================================================================

Route::get('/maintenance', function () {
    return view('errors.maintenance');
})->name('maintenance');

// Rutas de error personalizadas
Route::get('/error-403', function () {
    return view('errors.403');
})->name('error.403');

Route::get('/error-404', function () {
    return view('errors.404');
})->name('error.404');

Route::get('/error-500', function () {
    return view('errors.500');
})->name('error.500');

Route::get('/acceso-denegado', function () {
    return view('errors.403');
})->name('access.denied');

// ============================================================================
// RUTAS DE DESARROLLO Y TESTING (solo en desarrollo)
// ============================================================================

if (app()->environment('local')) {
    Route::prefix('dev')->name('dev.')->group(function () {
        Route::get('/test-database', [App\Http\Controllers\DevController::class, 'testDatabase'])->name('test-database');
        Route::get('/test-mail', [App\Http\Controllers\DevController::class, 'testMail'])->name('test-mail');
        Route::get('/clear-cache', [App\Http\Controllers\DevController::class, 'clearCache'])->name('clear-cache');
    });
}