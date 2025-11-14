<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\contabilidad\ContadorDashboardController;
use App\Http\Controllers\Admin\PlanillaController;
use App\Http\Controllers\Admin\BancoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\CuentaController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\Contabilidad\BalanceGeneralController;
use App\Http\Controllers\Contabilidad\LibroDiarioController;
use App\Http\Controllers\Contabilidad\EstadoResultadosController;
use App\Http\Controllers\Contabilidad\BancosController;
use App\Http\Controllers\Contabilidad\PlanCuentasController; 
use App\Http\Controllers\Contabilidad\FlujoCajaController; 
use App\Http\Controllers\Clientes\ClientesController; 
use App\Http\Controllers\Contabilidad\CajaController;
use App\Http\Controllers\Contabilidad\FlujoEgresoController;
use App\Http\Controllers\Contabilidad\CuentasPorPagarController;
use App\Http\Controllers\Compras\RegistroCompraController;
use App\Http\Controllers\Contabilidad\InventarioController;
use App\Http\Controllers\Reportes\ReporteVentasController;
use App\Http\Controllers\Reportes\ReporteInventarioController;
use App\Http\Controllers\Reportes\ReporteDashboardController;
use App\Http\Controllers\Reportes\ReporteAuditoriaController;
use App\Http\Controllers\Admin\SolicitudAsientoController; 


//RUTAS PÚBLICAS
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

//RUTA PARA CARD DE DESPEDIDA AL CERRAR SESION
Route::post('/logout', function () {
    $user = Auth::user(); 
    $nombre = $user->usuario ?? $user->name ?? 'usuario'; 

    Auth::logout();

    return redirect()->route('logout.message')
                     ->with('user_name', $nombre);
})->middleware('auth')->name('logout');

Route::get('/logout-message', function () {
    return view('auth.logout-message');
})->name('logout.message');

//RUTA PARA EL MENSAJE DE BIENVENIDA
Route::get('/welcome-message', function () {
    return view('auth.welcome-message');
})->name('welcome.message');

Route::get('/welcome-message', function () {
    return view('auth.welcome-message');
})->name('welcome.message');

//RUTAS GENERALES AUTENTICADAS
Route::middleware(['auth', 'role.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [ContadorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [ContadorDashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/alerts', [ContadorDashboardController::class, 'getAlerts'])->name('dashboard.alerts');
    
    // Sistema de sesiones y seguridad
    Route::post('/session/ping', function () {
        return response()->json([
            'status' => 'ok',
            'time' => now()->toDateTimeString()
        ]);
    })->name('session.ping');
});

//RUTAS ADMINISTRADOR
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    
    // Dashboard Administrativo
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

    //MÓDULO: GESTIÓN BANCARIA
    Route::resource('bancos', BancoController::class)
        ->except(['show'])
        ->names('bancos');

    //MÓDULO: GESTIÓN DE USUARIOS
    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        // Rutas con parámetros al final
        Route::get('/{usuario}/rol', [UsuarioController::class, 'roles'])->name('roles');
        Route::put('/{usuario}/rol', [UsuarioController::class, 'updateRol'])->name('updateRol');
        Route::post('/{usuario}/activar', [UsuarioController::class, 'activar'])->name('activar');
        Route::post('/{usuario}/desactivar', [UsuarioController::class, 'desactivar'])->name('desactivar');
    });

     Route::prefix('solicitudes-asientos')->name('solicitudes.asiento.')->group(function () {
        Route::get('/', [SolicitudAsientoController::class, 'index'])->name('index');
        // Rutas con parámetros al final
        Route::post('/aprobar/{id}', [SolicitudAsientoController::class, 'aprobar'])->name('aprobar');
        Route::post('/rechazar/{id}', [SolicitudAsientoController::class, 'rechazar'])->name('rechazar');
    });

    //MÓDULO: CUENTAS CORRIENTES Y CARTERA
    Route::prefix('cuentas-corrientes')->name('cuentas-corrientes.')->group(function () {
        Route::get('/', [CuentaController::class, 'index'])->name('index');
        Route::get('/exportar', [CuentaController::class, 'exportar'])->name('exportar');
        // Rutas con parámetros al final
        Route::get('/{cliente}/detalle', [CuentaController::class, 'detalleCliente'])->name('detalle');
        Route::post('/{cliente}/ajustar', [CuentaController::class, 'ajustarSaldo'])->name('ajustar');
    });

    //MÓDULO: REPORTES EJECUTIVOS
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('facturas', [ReporteController::class, 'facturas'])->name('facturas');
        Route::get('movimientos', [ReporteController::class, 'movimientos'])->name('movimientos'); 
        Route::get('ventas-diarias', [ReporteController::class, 'ventasDiarias'])->name('ventas-diarias');
        Route::get('comisiones', [ReporteController::class, 'comisiones'])->name('comisiones');
        Route::get('/facturas/export', [ReporteController::class, 'exportFacturas'])->name('facturas.export');
        Route::get('/movimientos/export', [ReporteController::class, 'exportMovimientos'])->name('movimientos.export');
        Route::get('/ventas/export', [ReporteController::class, 'exportVentas'])->name('ventas.export');
    });

    //MÓDULO: AUDITORÍA Y TRAZABILIDAD
    Route::prefix('auditoria')->name('auditoria.')->group(function () {
        Route::get('/', [AuditoriaController::class, 'index'])->name('index');
        Route::get('/exportar', [AuditoriaController::class, 'exportar'])->name('exportar');
        Route::post('/buscar', [AuditoriaController::class, 'buscar'])->name('buscar');
        // Rutas con parámetros al final
        Route::get('/{id}', [AuditoriaController::class, 'detalle'])->name('detalle');
    });

    //MÓDULO: NOTIFICACIONES
    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\NotificacionController::class, 'index'])->name('index');
        Route::get('/count', [App\Http\Controllers\Admin\NotificacionController::class, 'countNoLeidas'])->name('count');
        Route::post('/marcar-todas-leidas', [App\Http\Controllers\Admin\NotificacionController::class, 'marcarTodasLeidas'])->name('marcar-todas');
        Route::delete('/limpiar/leidas', [App\Http\Controllers\Admin\NotificacionController::class, 'limpiarLeidas'])->name('limpiar');
        // Rutas con parámetros al final
        Route::post('/{id}/marcar-leida', [App\Http\Controllers\Admin\NotificacionController::class, 'marcarLeida'])->name('marcar-leida');
        Route::delete('/{id}', [App\Http\Controllers\Admin\NotificacionController::class, 'eliminar'])->name('eliminar');
    });

    //MÓDULO: PLANILLAS ADMINISTRATIVAS
    Route::prefix('planillas')->name('planillas.')->group(function () {
        Route::get('/', [PlanillaController::class, 'index'])->name('index');
        // Rutas con parámetros al final
        Route::get('/{serie}/{numero}', [PlanillaController::class, 'show'])->name('show');
        Route::get('/{serie}/{numero}/edit', [PlanillaController::class, 'edit'])->name('edit');
        Route::put('/{serie}/{numero}', [PlanillaController::class, 'update'])->name('update');
        Route::delete('/{serie}/{numero}', [PlanillaController::class, 'destroy'])->name('destroy');
        Route::post('/{serie}/{numero}/aprobar', [PlanillaController::class, 'aprobar'])->name('aprobar');
        Route::post('/{serie}/{numero}/rechazar', [PlanillaController::class, 'rechazar'])->name('rechazar');
    });

    Route::get('/balance-general', [App\Http\Controllers\Contabilidad\BalanceGeneralController::class, 'index'])
         ->name('balance-general.index');
    Route::get('/cuentas-por-cobrar', [App\Http\Controllers\Contabilidad\CuentasPorCobrarController::class, 'index'])
         ->name('cxc.index');
    Route::get('/notificaciones', [App\Http\Controllers\Contabilidad\NotificacionController::class, 'index'])
         ->name('notificaciones.index');
});

//RUTAS CONTADOR
Route::middleware(['auth', 'access.contador'])->prefix('contador')->name('contador.')->group(function () {

    Route::get('/dashboard/contador', [ContadorDashboardController::class, 'contadorDashboard'])->name('dashboard.contador');
    Route::get('/dashboard/get-chart-data', [ContadorDashboardController::class, 'getChartData']);
    Route::get('/api/dashboard/stats', [ContadorDashboardController::class, 'getStats'])->name('api.dashboard.stats'); 
    Route::get('/contador/api/clear-cache', [ContadorDashboardController::class, 'clearCache'])->name('contador.api.clear-cache');
    Route::post('/contador/api/clear-cache', [ContadorDashboardController::class, 'clearCache'])
    ->name('contador.clearCache');


    
    // Rutas "sueltas" (Podrían agruparse mejor en el futuro)
    
    Route::get('configuracion/usuarios', [App\Http\Controllers\Admin\UsuarioController::class, 'index'])->name('configuracion.usuarios');
    Route::get('configuracion/parametros', [App\Http\Controllers\Admin\UsuarioController::class, 'index'])->name('configuracion.parametros');
    Route::get('configuracion/cambiar-password', [ContadorDashboardController::class, 'index'])->name('configuracion.cambiar-password');
    Route::get('perfil', [ContadorDashboardController::class, 'index'])->name('perfil');

    // --- MÓDULO CLIENTES ---
    Route::prefix('clientes')->name('clientes.')->group(function () {
        Route::get('/', [ClientesController::class, 'index'])->name('index'); 
        Route::get('/crear', [ClientesController::class, 'crearVista'])->name('crear'); 
        Route::post('/store', [ClientesController::class, 'store'])->name('store'); 
        Route::get('/buscar', [ClientesController::class, 'vistaBusqueda'])->name('buscar');
        Route::get('/api/consulta-documento/{documento}', [ClientesController::class, 'apiConsultaDocumento'])->name('api.consulta');
        // Rutas con {id} al final
        Route::get('/{id}', [ClientesController::class, 'show'])->name('show');
        Route::get('/{id}/editar', [ClientesController::class, 'editarVista'])->name('editar');
        Route::put('/{id}', [ClientesController::class, 'update'])->name('update');
    });
    
 
    // --- MÓDULO LIBRO DIARIO ---
    Route::prefix('libro-diario')->name('libro-diario.')->group(function () {
        // Rutas específicas ANTES de {id}
        Route::get('/', [LibroDiarioController::class, 'index'])->name('index');
        Route::get('create', [LibroDiarioController::class, 'create'])->name('create');
        Route::post('store', [LibroDiarioController::class, 'store'])->name('store');
        Route::get('exportar', [LibroDiarioController::class, 'exportar'])->name('exportar');
        Route::get('api/estadisticas', [LibroDiarioController::class, 'getEstadisticas'])->name('api.estadisticas');
        Route::get('api/busqueda-avanzada', [LibroDiarioController::class, 'getBusquedaAvanzada'])->name('api.busqueda-avanzada');
        
        // Rutas con {id} al final
        Route::get('{id}', [LibroDiarioController::class, 'show'])->name('show');
        Route::get('{id}/edit', [LibroDiarioController::class, 'edit'])->name('edit');
        Route::put('{id}', [LibroDiarioController::class, 'update'])->name('update');
        Route::delete('{id}', [LibroDiarioController::class, 'destroy'])->name('destroy'); // <-- Esta es la ruta de "Solicitar"
    });

    // --- MÓDULO ANULACIÓN COBRANZA ---
    Route::prefix('anulacion-cobranza')->name('anulacion.')->group(function () {
        Route::post('/store', [App\Http\Controllers\Contabilidad\AnulacionPlanillaController::class, 'store'])->name('store');
        Route::get('/{serie}/{numero}', [App\Http\Controllers\Contabilidad\AnulacionPlanillaController::class, 'show'])->name('show');
    });
    
    // --- MÓDULO CAJA ---
    Route::prefix('caja')->name('caja.')->group(function () {
        Route::get('/', [CajaController::class, 'index'])->name('index');
        Route::get('/create', [CajaController::class, 'create'])->name('create');
        Route::post('/', [CajaController::class, 'store'])->name('store');
        // Rutas con {id} al final
        Route::get('/{id}', [CajaController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CajaController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CajaController::class, 'update'])->name('update');
        Route::delete('/{id}', [CajaController::class, 'destroy'])->name('destroy');
    });

    // --- MÓDULO LIBRO MAYOR ---
    Route::prefix('libro-mayor')->name('libro-mayor.')->group(function () {
        Route::get('/', [App\Http\Controllers\Contabilidad\LibroMayorController::class, 'index'])->name('index');
        Route::get('/exportar', [App\Http\Controllers\Contabilidad\LibroMayorController::class, 'exportar'])->name('exportar');
        Route::get('/movimientos', [App\Http\Controllers\Contabilidad\LibroMayorController::class, 'movimientos'])->name('movimientos');
        Route::get('/comparacion', [App\Http\Controllers\Contabilidad\LibroMayorController::class, 'comparacionPeriodos'])->name('comparacion');
        // Rutas con {cuenta} al final
        Route::get('/exportar-cuenta/{cuenta}', [App\Http\Controllers\Contabilidad\LibroMayorController::class, 'exportarCuenta'])
            ->where('cuenta', '[0-9\.]+')->name('exportarCuenta');
        Route::get('/cuenta/{cuenta}', [App\Http\Controllers\Contabilidad\LibroMayorController::class, 'cuenta'])
            ->where('cuenta', '[0-9\.]+')->name('cuenta');
    });

    // --- MÓDULO BALANCE COMPROBACIÓN ---
    Route::prefix('balance-comprobacion')->name('balance-comprobacion.')->group(function () {
        Route::get('/', [App\Http\Controllers\Contabilidad\BalanceComprobacionController::class, 'index'])->name('index');
        Route::get('/detalle', [App\Http\Controllers\Contabilidad\BalanceComprobacionController::class, 'detalleCuenta'])->name('detalle');
        Route::get('/clases',[App\Http\Controllers\Contabilidad\BalanceComprobacionController::class, 'porClases'])->name('clases');
        Route::get('/comparacion', [App\Http\Controllers\Contabilidad\BalanceComprobacionController::class, 'comparacion'])->name('comparacion');
        Route::get('/verificar', [App\Http\Controllers\Contabilidad\BalanceComprobacionController::class, 'verificar'])->name('verificar');
        Route::get('/exportar', [App\Http\Controllers\Contabilidad\BalanceComprobacionController::class, 'exportar'])->name('exportar');
    });

    // --- MÓDULO PLAN DE CUENTAS ---
    Route::prefix('plan-cuentas')->name('plan-cuentas.')->group(function () {
        Route::get('/', [PlanCuentasController::class, 'index'])->name('index');
        Route::get('/create', [PlanCuentasController::class, 'create'])->name('create');
        Route::post('/', [PlanCuentasController::class, 'store'])->name('store');
        // Rutas con {codigo} al final
        Route::get('/{codigo}/edit', [PlanCuentasController::class, 'edit'])
            ->where('codigo', '[0-9\.]+')->name('edit');
        Route::put('/{codigo}', [PlanCuentasController::class, 'update'])
            ->where('codigo', '[0-9\.]+')->name('update');
        Route::delete('/{codigo}', [PlanCuentasController::class, 'destroy'])
            ->where('codigo', '[0-9\.]+')->name('destroy');
    });

    // --- MÓDULO BANCOS ---
    Route::prefix('bancos')->name('bancos.')->group(function () {
        Route::get('/', [BancosController::class, 'index'])->name('index');
        Route::get('/flujo-diario', [BancosController::class, 'flujoDiario'])->name('flujo-diario');
        Route::get('/diario', [BancosController::class, 'diario'])->name('diario');
        Route::get('/mensual', [BancosController::class, 'resumenMensual'])->name('mensual');
        Route::get('/conciliacion', [BancosController::class, 'conciliacion'])->name('conciliacion');
        Route::post('/conciliacion', [BancosController::class, 'storeConciliacion'])->name('conciliacion.store');
        Route::get('/transferencias', [BancosController::class, 'transferencias'])->name('transferencias');
        Route::get('/reporte', [BancosController::class, 'generarReporte'])->name('reporte');
        // Rutas con {cuenta} al final
        Route::get('/detalle/{cuenta}', [BancosController::class, 'detalle'])->name('detalle');
    });

    // --- MÓDULO FLUJO DE COBRANZAS ---
    Route::prefix('flujo/cobranzas')->name('flujo.cobranzas.')->group(function () {
        Route::get('/paso-1', [FlujoCajaController::class, 'showPaso1'])->name('paso1');
        Route::post('/paso-1', [FlujoCajaController::class, 'handlePaso1'])->name('handlePaso1');
        Route::get('/paso-2', [FlujoCajaController::class, 'showPaso2'])->name('paso2');
        Route::post('/paso-2', [FlujoCajaController::class, 'handlePaso2'])->name('handlePaso2');
        Route::get('/paso-3', [FlujoCajaController::class, 'showPaso3'])->name('paso3');
        Route::post('/paso-3', [FlujoCajaController::class, 'handlePaso3'])->name('handlePaso3');
        Route::get('/paso-4', [FlujoCajaController::class, 'showPaso4'])->name('paso4');
        Route::post('/procesar', [FlujoCajaController::class, 'procesar'])->name('procesar');
    });

    // --- MÓDULO CUENTAS POR COBRAR ---
    Route::get('cuentas-por-cobrar', [App\Http\Controllers\Contabilidad\CuentasPorCobrarController::class, 'index'])
        ->name('cxc.index');
    Route::get('/api/clientes/search', [ClientesController::class, 'search'])
        ->name('api.clientes.search');

    // --- MÓDULO INVENTARIO ---
    Route::prefix('inventario')->name('inventario.')->group(function () {
        Route::get('/', [InventarioController::class, 'index'])->name('index');
        Route::get('/crear', [InventarioController::class, 'create'])->name('create');
        Route::post('/', [InventarioController::class, 'store'])->name('store');
        Route::get('/laboratorios', [InventarioController::class, 'laboratorios'])->name('laboratorios');
        Route::get('/vencimientos', [InventarioController::class, 'vencimientos'])->name('vencimientos');
        // Rutas con {codPro} al final
        Route::get('/{codPro}', [InventarioController::class, 'show'])->name('show');
    });
    Route::get('/test-stock', [InventarioController::class, 'stockLotes'])->name('test.stock');

    // --- MÓDULO VENTAS ---
    Route::prefix('ventas')->name('facturas.')->group(function () {
        Route::get('/', [App\Http\Controllers\Ventas\FacturacionController::class, 'index'])->name('index');
        Route::get('/crear', [App\Http\Controllers\Ventas\FacturacionController::class, 'create'])->name('create');
        Route::post('/guardar', [App\Http\Controllers\Ventas\FacturacionController::class, 'store'])->name('store');
        Route::post('/carrito/agregar', [App\Http\Controllers\Ventas\FacturacionController::class, 'carritoAgregar'])->name('carrito.agregar');
        Route::post('/carrito/pago', [App\Http\Controllers\Ventas\FacturacionController::class, 'carritoActualizarPago'])->name('carrito.pago');
        Route::post('/carrito/iniciar', [App\Http\Controllers\Ventas\FacturacionController::class, 'carritoIniciar'])->name('carrito.iniciar');
        Route::get('/api/buscar-clientes', [App\Http\Controllers\Ventas\FacturacionController::class, 'buscarClientes'])->name('api.buscarClientes');
        Route::get('/api/buscar-productos', [App\Http\Controllers\Ventas\FacturacionController::class, 'buscarProductos'])->name('api.buscarProductos'); 
        // Rutas con parámetros al final
        Route::get('/mostrar/{numero}/{tipo}', [App\Http\Controllers\Ventas\FacturacionController::class, 'show'])->name('show');
        Route::delete('/carrito/eliminar/{itemId}', [App\Http\Controllers\Ventas\FacturacionController::class, 'carritoEliminar'])->name('carrito.eliminar');
        Route::get('/api/buscar-lotes/{codPro}', [App\Http\Controllers\Ventas\FacturacionController::class, 'buscarLotes'])->name('api.buscarLotes');
        Route::post('/enviar-email/{numero}/{tipo}', [App\Http\Controllers\Ventas\FacturacionController::class, 'enviarEmail'])->name('enviarEmail');
    });

    // --- MÓDULO PROVEEDORES ---
    Route::prefix('proveedores')->name('proveedores.')->group(function () {
        Route::get('/', [App\Http\Controllers\Contabilidad\ProveedoresController::class, 'index'])->name('index');
        Route::get('/crear', [App\Http\Controllers\Contabilidad\ProveedoresController::class, 'create'])->name('crear');
        Route::post('/store', [App\Http\Controllers\Contabilidad\ProveedoresController::class, 'store'])->name('store');
    });

    // --- MÓDULO CUENTAS POR PAGAR ---
    Route::prefix('cuentas-por-pagar')->name('cxp.')->group(function () {
        Route::get('/', [CuentasPorPagarController::class, 'index'])->name('index');
        // Rutas con {id} al final
        Route::get('/proveedor/{id}', [CuentasPorPagarController::class, 'showByProveedor'])->name('show');
    });

    // --- MÓDULO FLUJO DE EGRESOS ---
    Route::prefix('flujo/egresos')->name('flujo.egresos.')->group(function () {
        Route::get('/paso-1', [FlujoEgresoController::class, 'showPaso1'])->name('paso1'); 
        Route::post('/paso-1', [FlujoEgresoController::class, 'handlePaso1'])->name('handlePaso1'); 
        Route::get('/paso-2', [FlujoEgresoController::class, 'showPaso2'])->name('paso2'); 
        Route::post('/paso-2', [FlujoEgresoController::class, 'handlePaso2'])->name('handlePaso2');
        Route::get('/paso-3', [FlujoEgresoController::class, 'showPaso3'])->name('paso3'); 
        Route::post('/procesar', [FlujoEgresoController::class, 'procesar'])->name('procesar'); 
    });

    // --- MÓDULO COMPRAS (ORDEN DE COMPRA) ---
    Route::prefix('compras')->name('compras.')->group(function () {
        Route::get('/', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'index'])->name('index');
        Route::get('/crear', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'create'])->name('create');
        Route::post('/guardar', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'store'])->name('store');
        Route::post('/carrito/agregar', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'carritoAgregar'])->name('carrito.agregar');
        Route::post('/carrito/pago', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'carritoActualizarPago'])->name('carrito.pago');
        Route::get('/api/buscar-proveedores', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'buscarProveedores'])->name('api.buscarProveedores');
        Route::get('/api/buscar-productos', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'buscarProductos'])->name('api.buscarProductos');
        // Rutas con parámetros al final
        Route::delete('/carrito/eliminar/{itemId}', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'carritoEliminar'])->name('carrito.eliminar');
        Route::get('/show/{id}', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'show'])->name('show');
        Route::get('/api/buscar-lotes/{codPro}', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'buscarLotes'])->name('api.buscarLotes');
        Route::post('/enviar-email/{id}', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'enviarEmail'])->name('enviarEmail');
    });

    // --- MÓDULO NOTAS DE CRÉDITO ---
    Route::prefix('notas-credito')->name('notas-credito.')->group(function () {
        Route::get('/', [App\Http\Controllers\Contabilidad\NotaCreditoController::class, 'index'])->name('index');
        Route::get('/crear', [App\Http\Controllers\Contabilidad\NotaCreditoController::class, 'create'])->name('create');
        Route::post('/buscar-factura', [App\Http\Controllers\Contabilidad\NotaCreditoController::class, 'buscarFactura'])->name('buscarFactura');
        Route::get('/crear-paso-2', [App\Http\Controllers\Contabilidad\NotaCreditoController::class, 'showPaso2'])->name('showPaso2');
        Route::post('/guardar', [App\Http\Controllers\Contabilidad\NotaCreditoController::class, 'store'])->name('store');
        // Rutas con parámetros al final
        Route::get('/show/{numero}', [App\Http\Controllers\Contabilidad\NotaCreditoController::class, 'show'])->name('show');
    });

    // --- MÓDULO DE REPORTES (BI) ---
    Route::prefix('/reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReporteDashboardController::class, 'agingCartera'])->name('index');
        Route::get('/ventas/rentabilidad-cliente', [ReporteVentasController::class, 'rentabilidadCliente'])->name('ventas.rentabilidad');
        Route::get('/inventario/sugerencias-compra', [ReporteInventarioController::class, 'sugerenciasCompra'])->name('inventario.sugerencias');
        Route::get('/inventario/por-vencer', [ReporteInventarioController::class, 'productosPorVencer'])->name('inventario.vencimientos');
        Route::get('/auditoria/libro-diario', [ReporteAuditoriaController::class, 'libroDiario'])->name('auditoria.libro_diario');
        Route::get('/auditoria/sistema-general', [ReporteAuditoriaController::class, 'sistemaGeneral'])->name('auditoria.sistema_general');
        // Rutas con parámetros al final
        Route::post('/enviar-recordatorio-cobranza/{clienteId}', [ReporteDashboardController::class, 'enviarRecordatorioEmail'])->name('enviarRecordatorioCobranza');
        Route::get('/ventas/flujo-comparativo', [ReporteVentasController::class, 'flujoVentasCobranzas'])->name('ventas.flujo-comparativo');
        Route::get('/ventas/flujo-comparativo/excel', [ReporteVentasController::class, 'exportarVentasCobranzasExcel'])->name('ventas.flujo-comparativo.excel');
    });
    

    // --- MÓDULO REGISTRO DE COMPRA (FACTURA DE COMPRA) ---
    Route::prefix('registro-compra')->name('compras.registro.')->group(function () {
        Route::get('/crear', [RegistroCompraController::class, 'create'])->name('create');
        Route::post('/guardar', [RegistroCompraController::class, 'store'])->name('store');
    });

    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
            // Historial de Notificaciones
            Route::get('/', [App\Http\Controllers\Contabilidad\NotificacionController::class, 'index'])
                 ->name('index'); // contador.notificaciones.index
            
            // Formulario para crear (Contadora)
            Route::get('/crear', [App\Http\Controllers\Contabilidad\NotificacionController::class, 'create'])
                 ->name('create'); // contador.notificaciones.create
                 
            // Guardar la notificación
            Route::post('/guardar', [App\Http\Controllers\Contabilidad\NotificacionController::class, 'store'])
                 ->name('store'); // contador.notificaciones.store
            
            // Marcar como leída (AJAX)
            Route::post('/marcar-leida/{id}', [App\Http\Controllers\Contabilidad\NotificacionController::class, 'markAsRead'])
                 ->name('markAsRead'); // contador.notificaciones.markAsRead
        });

    // --- MÓDULO LETRAS EN DESCUENTO ---
    Route::prefix('letras-descuento')->name('letras_descuento.')->group(function () {
        Route::get('/', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'index'])->name('index');
        Route::get('/crear', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'create'])->name('create');
        Route::post('/guardar', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'store'])->name('store');
        Route::get('/api/buscar-letras', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'apiBuscarLetrasPendientes'])->name('api.buscarLetras');
        Route::post('/agregar-letra', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'agregarLetraPlanilla'])->name('api.agregarLetra');
        // Rutas con parámetros al final
        Route::get('/{serie}/{numero}', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'show'])->name('show');
        Route::delete('/quitar-letra/{id}', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'quitarLetraPlanilla'])->name('api.quitarLetra');
        Route::post('/{serie}/{numero}/procesar', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'procesarDescuento'])->name('procesar');
    });
    
    // --- MÓDULO ESTADO DE RESULTADOS ---
    Route::prefix('estado-resultados')->name('estado-resultados.')->group(function () {
        Route::get('/', [EstadoResultadosController::class, 'index'])->name('index');
        Route::get('/periodos', [EstadoResultadosController::class, 'porPeriodos'])->name('periodos');
        Route::get('/comparativo', [EstadoResultadosController::class, 'comparativo'])->name('comparativo');
        Route::get('/flujo-caja', [EstadoResultadosController::class, 'cashFlow'])->name('flujo-caja');
        Route::get('/balance-general', [BalanceGeneralController::class, 'index'])->name('balance-general');
        Route::get('/exportar', [EstadoResultadosController::class, 'exportar'])->name('exportar'); 
        Route::get('/api/clientes/search', [ClientesController::class, 'search'])->name('api.clientes.search');
        // Rutas con parámetros al final
        Route::get('/detalle/{cuenta}', [EstadoResultadosController::class, 'detalleCuenta'])->name('detalle');
    });
    
}); 


Route::get('/acceso-denegado', function () {
    return view('errors.403');
})->name('access.denied');