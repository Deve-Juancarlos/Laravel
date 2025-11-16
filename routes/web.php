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
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Auth; 

// El grupo 'web' carga la sesión y soluciona el error 'usuario_id: null'.
Route::middleware(['web'])->group(function () {

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

    // RUTA PARA CARD DE DESPEDIDA AL CERRAR SESION (Única ruta de logout)
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

    // RUTA PARA EL MENSAJE DE BIENVENIDA (Única ruta)
    Route::get('/welcome-message', function () {
        return view('auth.welcome-message');
    })->name('welcome.message');

    Route::middleware(['auth'])->group(function () {

        Route::prefix('admin')->name('admin.')->middleware(['role.admin'])->group(function () {
    
            // Dashboard Administrativo
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard'); // Ruta de dashboard unificada
            Route::get('/dashboard/graficos', [DashboardController::class, 'graficos'])->name('dashboard.graficos');
            Route::get('/dashboard/resumen-ejecutivo', [DashboardController::class, 'resumenEjecutivo'])->name('dashboard.resumen');
        
            // Sistema de sesiones y seguridad (movido aquí desde el grupo anterior)
            Route::post('/session/ping', function () {
                return response()->json([
                    'status' => 'ok',
                    'time' => now()->toDateTimeString()
                ]);
            })->name('session.ping');

            //MÓDULO: GESTIÓN BANCARIA
            Route::resource('bancos', BancoController::class)
                ->except(['show'])
                ->names('bancos');

            //MÓDULO: GESTIÓN DE USUARIOS
            Route::prefix('usuarios')->name('usuarios.')->group(function () {
                
                // Lista de usuarios
                Route::get('/', [UsuarioController::class, 'index'])->name('index');
                
                // Crear nuevo usuario
                Route::get('/create', [UsuarioController::class, 'create'])->name('create');
                Route::post('/store', [UsuarioController::class, 'store'])->name('store');
                
                // Editar usuario (cambiar empleado vinculado)
                Route::get('/{usuario}/edit', [UsuarioController::class, 'edit'])->name('edit');
                Route::put('/{usuario}/update', [UsuarioController::class, 'update'])->name('update');
                
                // Gestionar roles
                Route::get('/{usuario}/roles', [UsuarioController::class, 'roles'])->name('roles');
                Route::put('/{usuario}/rol', [UsuarioController::class, 'updateRol'])->name('updateRol');
                
                // Activar/Desactivar usuario
                Route::post('/{usuario}/activar', [UsuarioController::class, 'activar'])->name('activar');
                Route::post('/{usuario}/desactivar', [UsuarioController::class, 'desactivar'])->name('desactivar');
                
                // Historial de accesos
                Route::get('/{usuario}/historial', [UsuarioController::class, 'historial'])->name('historial');
                
                // Resetear contraseña
                Route::post('/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])->name('reset-password');
                
            });

            Route::prefix('solicitudes-asientos')->name('solicitudes.asiento.')->group(function () {
                Route::get('/', [SolicitudAsientoController::class, 'index'])->name('index');
                Route::post('/aprobar/{id}', [SolicitudAsientoController::class, 'aprobar'])->name('aprobar');
                Route::post('/rechazar/{id}', [SolicitudAsientoController::class, 'rechazar'])->name('rechazar');
            });

            //MÓDULO: CUENTAS CORRIENTES Y CARTERA
            Route::prefix('cuentas-corrientes')->name('cuentas-corrientes.')->group(function () {
                Route::get('/', [CuentaController::class, 'index'])->name('index');
                Route::get('/exportar', [CuentaController::class, 'exportar'])->name('exportar');
                Route::get('/{cliente}/detalle', [CuentaController::class, 'detalleCliente'])->name('detalle');
                Route::post('/{cliente}/ajustar', [CuentaController::class, 'ajustarSaldo'])->name('ajustar');
            });

                        //MÓDULO: REPORTES EJECUTIVOS
            Route::prefix('reportes')->name('reportes.')->group(function () {

                // === Reportes de Ventas ===
                Route::get('/ventas-periodo', [ReporteController::class, 'ventasPorPeriodo'])->name('ventas-periodo');
                Route::get('/ventas-periodo/export', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'ventas-periodo')->name('ventas-periodo.export');

                Route::get('/ventas-cliente', [ReporteController::class, 'ventasPorCliente'])->name('ventas-cliente');
                Route::get('/ventas-cliente/export', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'ventas-cliente')->name('ventas-cliente.export');

                Route::get('/ventas-producto', [ReporteController::class, 'ventasPorProducto'])->name('ventas-producto');
                Route::get('/ventas-producto/export', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'ventas-producto')->name('ventas-producto.export');

                Route::get('/ventas-vendedor', [ReporteController::class, 'ventasPorVendedor'])->name('ventas-vendedor');
                Route::get('/ventas-vendedor/export', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'ventas-vendedor')->name('ventas-vendedor.export');

                Route::get('/ventas-diarias', [ReporteController::class, 'ventasDiarias'])->name('ventas-diarias');
                Route::get('/ventas/export', [ReporteController::class, 'exportVentas'])->name('ventas.export');

                // === Reportes Comerciales ===
                Route::get('/comisiones', [ReporteController::class, 'comisiones'])->name('comisiones');

                // === Facturación (solo si los métodos existen en ReporteController) ===
                Route::get('/facturas', [ReporteController::class, 'facturas'])->name('facturas');
                Route::get('/facturas/export', [ReporteController::class, 'exportFacturas'])->name('facturas.export');

                // Route::get('/movimientos', [ReporteController::class, 'movimientos'])->name('movimientos'); // descomenta si existe
                // Route::get('/movimientos/export', [ReporteController::class, 'exportMovimientos'])->name('movimientos.export');

                // === Reportes Financieros ===
                Route::get('/cuentas-cobrar', [ReporteController::class, 'cuentasPorCobrar'])->name('cuentas-cobrar');
                Route::get('/cuentas-cobrar/export', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'cuentas-cobrar')->name('cuentas-cobrar.export');

                Route::get('/cuentas-pagar', [ReporteController::class, 'cuentasPorPagar'])->name('cuentas-pagar');
                Route::get('/cuentas-pagar/export', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'cuentas-pagar')->name('cuentas-pagar.export');

                // === Reportes de Inventario ===
                Route::get('/inventario-valorado', [ReporteController::class, 'inventarioValorado'])->name('inventario-valorado');
                Route::get('/inventario-valorado/export', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'inventario-valorado')->name('inventario-valorado.export');

                Route::get('/productos-vencer', [ReporteController::class, 'productosVencer'])->name('productos-vencer');
                Route::get('/productos-vencer/export', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'productos-vencer')->name('productos-vencer.export');

                Route::get('/kardex-producto', [ReporteController::class, 'kardexProducto'])->name('kardex-producto');
                Route::get('/kardex/buscar', [ReporteController::class, 'buscarKardex'])->name('kardex.buscar');
                Route::get('/kardex-producto/export', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'kardex-producto')->name('kardex-producto.export');

                // === Reportes SUNAT ===
                Route::get('/sunat-ventas', [ReporteController::class, 'registroVentasSunat'])->name('sunat-ventas');
                Route::get('/sunat-ventas/export', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'sunat-ventas')->name('sunat-ventas.export');
                Route::get('/sunat-ventas/txt', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'sunat-ventas-txt')->name('sunat-ventas.txt');

                Route::get('/sunat-compras', [ReporteController::class, 'registroComprasSunat'])->name('sunat-compras');
                Route::get('/sunat-compras/export', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'sunat-compras')->name('sunat-compras.export');
                Route::get('/sunat-compras/txt', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'sunat-compras-txt')->name('sunat-compras.txt');

                // === Reportes de Análisis ===
                Route::get('/rentabilidad-productos', [ReporteController::class, 'rentabilidadProductos'])->name('rentabilidad-productos');
                Route::get('/rentabilidad-productos/export', [ReporteController::class, 'exportarExcel'])
                    ->defaults('tipo', 'rentabilidad-productos')->name('rentabilidad-productos.export');
            });

            //MÓDULO: AUDITORÍA Y TRAZABILIDAD
            Route::prefix('auditoria')->name('auditoria.')->group(function () {
                Route::get('/', [AuditoriaController::class, 'index'])->name('index');
                Route::get('/exportar', [AuditoriaController::class, 'exportar'])->name('exportar');
                Route::post('/buscar', [AuditoriaController::class, 'buscar'])->name('buscar');
                Route::get('/{id}', [AuditoriaController::class, 'detalle'])->name('detalle');
            });

            //MÓDULO: NOTIFICACIONES
            Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\NotificacionController::class, 'index'])->name('index');
                Route::get('/count', [App\Http\Controllers\Admin\NotificacionController::class, 'countNoLeidas'])->name('count');
                Route::post('/marcar-todas-leidas', [App\Http\Controllers\Admin\NotificacionController::class, 'marcarTodasLeidas'])->name('marcar-todas');
                Route::delete('/limpiar/leidas', [App\Http\Controllers\Admin\NotificacionController::class, 'limpiarLeidas'])->name('limpiar');
                Route::post('/{id}/marcar-leida', [App\Http\Controllers\Admin\NotificacionController::class, 'marcarLeida'])->name('marcar-leida');
                Route::delete('/{id}', [App\Http\Controllers\Admin\NotificacionController::class, 'eliminar'])->name('eliminar');
            });

            //MÓDULO: PLANILLAS ADMINISTRATIVAS
            Route::prefix('planillas')->name('planillas.')->group(function () {
                Route::get('/', [PlanillaController::class, 'index'])->name('index');
                Route::get('/{serie}/{numero}', [PlanillaController::class, 'show'])->name('show');
                Route::get('/{serie}/{numero}/edit', [PlanillaController::class, 'edit'])->name('edit');
                Route::put('/{serie}/{numero}', [PlanillaController::class, 'update'])->name('update');
                Route::delete('/{serie}/{numero}', [PlanillaController::class, 'destroy'])->name('destroy');
                Route::post('/{serie}/{numero}/aprobar', [PlanillaController::class, 'aprobar'])->name('aprobar');
                Route::post('/{serie}/{numero}/rechazar', [PlanillaController::class, 'rechazar'])->name('rechazar');
            });

            // Rutas de Admin que estaban sueltas
            Route::get('/balance-general', [App\Http\Controllers\Contabilidad\BalanceGeneralController::class, 'index'])
                ->name('balance-general.index');
            Route::get('/cuentas-por-cobrar', [App\Http\Controllers\Contabilidad\CuentasPorCobrarController::class, 'index'])
                ->name('cxc.index');
            Route::get('/notificaciones', [App\Http\Controllers\Contabilidad\NotificacionController::class, 'index'])
                ->name('notificaciones.index');
        });              
        // --- RUTAS DE CONTADOR ---
       
        Route::middleware(['access.contador'])->prefix('contador')->name('contador.')->group(function () {

            Route::get('/dashboard/contador', [ContadorDashboardController::class, 'contadorDashboard'])->name('dashboard.contador');
            Route::get('/dashboard/get-chart-data', [ContadorDashboardController::class, 'getChartData']);
            Route::get('/api/dashboard/stats', [ContadorDashboardController::class, 'getStats'])->name('api.dashboard.stats'); 
            Route::get('/contador/api/clear-cache', [ContadorDashboardController::class, 'clearCache'])->name('contador.api.clear-cache');
            Route::post('/contador/api/clear-cache', [ContadorDashboardController::class, 'clearCache'])
            ->name('contador.clearCache');

            // Rutas "sueltas" 
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
                Route::get('/{id}', [ClientesController::class, 'show'])->name('show');
                Route::get('/{id}/editar', [ClientesController::class, 'editarVista'])->name('editar');
                Route::put('/{id}', [ClientesController::class, 'update'])->name('update');
            });                   
            // --- MÓDULO LIBRO DIARIO ---
            Route::prefix('libro-diario')->name('libro-diario.')->group(function () {
                Route::get('/', [LibroDiarioController::class, 'index'])->name('index');
                Route::get('create', [LibroDiarioController::class, 'create'])->name('create');
                Route::post('store', [LibroDiarioController::class, 'store'])->name('store');
                Route::get('exportar', [LibroDiarioController::class, 'exportar'])->name('exportar');
                Route::get('api/estadisticas', [LibroDiarioController::class, 'getEstadisticas'])->name('api.estadisticas');
                Route::get('api/busqueda-avanzada', [LibroDiarioController::class, 'getBusquedaAvanzada'])->name('api.busqueda-avanzada');
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
                Route::post('/enviar-recordatorio-cobranza/{clienteId}', [ReporteDashboardController::class, 'enviarRecordatorioEmail'])->name('enviarRecordatorioCobranza');
                Route::get('/ventas/flujo-comparativo', [ReporteVentasController::class, 'flujoVentasCobranzas'])->name('ventas.flujo-comparativo');
                Route::get('/ventas/flujo-comparativo/excel', [ReporteVentasController::class, 'exportarVentasCobranzasExcel'])->name('ventas.flujo-comparativo.excel');
            });            
            // --- MÓDULO REGISTRO DE COMPRA (FACTURA DE COMPRA) ---
            Route::prefix('registro-compra')->name('compras.registro.')->group(function () {
                Route::get('/crear', [RegistroCompraController::class, 'create'])->name('create');
                Route::post('/guardar', [RegistroCompraController::class, 'store'])->name('store');
            });
            // --- MÓDULO NOTIFICACIONES (Contador) ---
            Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
                Route::get('/', [App\Http\Controllers\Contabilidad\NotificacionController::class, 'index'])->name('index'); // contador.notificaciones.index
                Route::get('/crear', [App\Http\Controllers\Contabilidad\NotificacionController::class, 'create'])->name('create'); // contador.notificaciones.create
                Route::post('/guardar', [App\Http\Controllers\Contabilidad\NotificacionController::class, 'store'])->name('store'); // contador.notificaciones.store
                Route::post('/marcar-leida/{id}', [App\Http\Controllers\Contabilidad\NotificacionController::class, 'markAsRead'])->name('markAsRead'); // contador.notificaciones.markAsRead
            });
            // --- MÓDULO LETRAS EN DESCUENTO ---
            Route::prefix('letras-descuento')->name('letras_descuento.')->group(function () {
                Route::get('/', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'index'])->name('index');
                Route::get('/crear', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'create'])->name('create');
                Route::post('/guardar', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'store'])->name('store');
                Route::get('/api/buscar-letras', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'apiBuscarLetrasPendientes'])->name('api.buscarLetras');
                Route::post('/agregar-letra', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'agregarLetraPlanilla'])->name('api.agregarLetra');
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
                Route::get('/detalle/{cuenta}', [EstadoResultadosController::class, 'detalleCuenta'])->name('detalle');
            });
            
        }); 
    }); 
    
    Route::get('/acceso-denegado', function () {
        return view('errors.403');
    })->name('access.denied');

}); // --- FIN DEL GRUPO 'web' ---