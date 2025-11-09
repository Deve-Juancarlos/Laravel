<?php

//RUTAS MEJORADAS PARA CONTABILIDAD FARMACÉUTICA
//Pensando como un contador farmacéutico profesional

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
use App\Http\Controllers\Contabilidad\ReportesSunatController;
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
//RUTAS PÚBLICAS
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

//RUTAS GENERALES AUTENTICADAS
Route::middleware('auth')->group(function () {
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
Route::middleware(['auth', 'check.admin'])->group(function () {
    
    // Dashboard Administrativo
    Route::get('/admin/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard.admin');

    //MÓDULO: GESTIÓN BANCARIA
    Route::resource('admin/bancos', BancoController::class)
        ->except(['show'])
        ->names('admin.bancos');

    //MÓDULO: GESTIÓN DE USUARIOS
    Route::prefix('admin/usuarios')->name('admin.usuarios.')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::get('/{usuario}/rol', [UsuarioController::class, 'roles'])->name('roles');
        Route::put('/{usuario}/rol', [UsuarioController::class, 'updateRol'])->name('updateRol');
        Route::post('/{usuario}/activar', [UsuarioController::class, 'activar'])->name('activar');
        Route::post('/{usuario}/desactivar', [UsuarioController::class, 'desactivar'])->name('desactivar');
    });

    //MÓDULO: CUENTAS CORRIENTES Y CARTERA
    Route::prefix('admin/cuentas-corrientes')->name('admin.cuentas-corrientes.')->group(function () {
        Route::get('/', [CuentaController::class, 'index'])->name('index');
        Route::get('/exportar', [CuentaController::class, 'exportar'])->name('exportar');
        Route::get('/{cliente}/detalle', [CuentaController::class, 'detalleCliente'])->name('detalle');
        Route::post('/{cliente}/ajustar', [CuentaController::class, 'ajustarSaldo'])->name('ajustar');
    });

    //MÓDULO: REPORTES EJECUTIVOS
    Route::prefix('admin/reportes')->name('admin.reportes.')->group(function () {
        Route::get('facturas', [ReporteController::class, 'facturas'])->name('facturas');
        Route::get('movimientos', [ReporteController::class, 'movimientos'])->name('movimientos'); 
        Route::get('ventas-diarias', [ReporteController::class, 'ventasDiarias'])->name('ventas-diarias');
        Route::get('comisiones', [ReporteController::class, 'comisiones'])->name('comisiones');
        
        // Exportaciones
        Route::get('/facturas/export', [ReporteController::class, 'exportFacturas'])->name('facturas.export');
        Route::get('/movimientos/export', [ReporteController::class, 'exportMovimientos'])->name('movimientos.export');
        Route::get('/ventas/export', [ReporteController::class, 'exportVentas'])->name('ventas.export');
    });

    //MÓDULO: AUDITORÍA Y TRAZABILIDAD
    Route::prefix('admin/auditoria')->name('admin.auditoria.')->group(function () {
        Route::get('/', [AuditoriaController::class, 'index'])->name('index');
        Route::get('/{id}', [AuditoriaController::class, 'detalle'])->name('detalle');
        Route::get('/exportar', [AuditoriaController::class, 'exportar'])->name('exportar');
        Route::post('/buscar', [AuditoriaController::class, 'buscar'])->name('buscar');
    });

    //MÓDULO: NOTIFICACIONES
    Route::prefix('admin/notificaciones')->name('admin.notificaciones.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\NotificacionController::class, 'index'])->name('index');
        Route::get('/count', [App\Http\Controllers\Admin\NotificacionController::class, 'countNoLeidas'])->name('count');
        Route::post('/{id}/marcar-leida', [App\Http\Controllers\Admin\NotificacionController::class, 'marcarLeida'])->name('marcar-leida');
        Route::post('/marcar-todas-leidas', [App\Http\Controllers\Admin\NotificacionController::class, 'marcarTodasLeidas'])->name('marcar-todas');
        Route::delete('/{id}', [App\Http\Controllers\Admin\NotificacionController::class, 'eliminar'])->name('eliminar');
        Route::delete('/limpiar/leidas', [App\Http\Controllers\Admin\NotificacionController::class, 'limpiarLeidas'])->name('limpiar');
    });

    //MÓDULO: PLANILLAS ADMINISTRATIVAS
    Route::prefix('admin/planillas')->name('admin.planillas.')->group(function () {
        Route::get('/', [PlanillaController::class, 'index'])->name('index');
        Route::get('/{serie}/{numero}', [PlanillaController::class, 'show'])->name('show');
        Route::get('/{serie}/{numero}/edit', [PlanillaController::class, 'edit'])->name('edit');
        Route::put('/{serie}/{numero}', [PlanillaController::class, 'update'])->name('update');
        Route::delete('/{serie}/{numero}', [PlanillaController::class, 'destroy'])->name('destroy');
        Route::post('/{serie}/{numero}/aprobar', [PlanillaController::class, 'aprobar'])->name('aprobar');
        Route::post('/{serie}/{numero}/rechazar', [PlanillaController::class, 'rechazar'])->name('rechazar');
    });
});

//RUTAS CONTADOR
Route::middleware(['auth', 'check.contador'])->group(function () {
    Route::get('/dashboard/contador', [ContadorDashboardController::class, 'contadorDashboard'])->name('dashboard.contador');
    Route::prefix('contador')->name('contador.')->group(function () {

        Route::get('/api/dashboard/stats', [ContadorDashboardController::class, 'getStats'])->name('api.dashboard.stats'); 
        Route::post('/api/dashboard/clear-cache', [ContadorDashboardController::class, 'clearCache'])->name('api.dashboard.clearCache');     
        Route::get('facturas', [App\Http\Controllers\Ventas\FacturacionController::class, 'index'])->name('facturas.index');
        Route::get('facturas/create', [App\Http\Controllers\Ventas\FacturacionController::class, 'create'])->name('facturas.create');
        Route::post('facturas', [App\Http\Controllers\Ventas\FacturacionController::class, 'store'])->name('facturas.store');
        Route::get('facturas/export', [App\Http\Controllers\Ventas\FacturacionController::class, 'exportar'])->name('facturas.export');
        Route::get('configuracion/usuarios', [App\Http\Controllers\Admin\UsuarioController::class, 'index'])->name('configuracion.usuarios');
        Route::get('configuracion/parametros', [App\Http\Controllers\Admin\UsuarioController::class, 'index'])->name('configuracion.parametros');
        Route::get('configuracion/cambiar-password', [ContadorDashboardController::class, 'index'])->name('configuracion.cambiar-password');               
        Route::get('perfil', [ContadorDashboardController::class, 'index'])->name('perfil');

        Route::prefix('clientes')->name('clientes.')->group(function () {
            Route::get('/', [ClientesController::class, 'index'])->name('index'); 
            Route::get('/crear', [ClientesController::class, 'crearVista'])->name('crear'); 
            Route::post('/store', [ClientesController::class, 'store'])->name('store'); 
            Route::get('/buscar', [ClientesController::class, 'vistaBusqueda'])->name('buscar');
            Route::get('/{id}', [ClientesController::class, 'show'])->name('show');
            Route::get('/{id}/editar', [ClientesController::class, 'editarVista'])->name('editar');     
            Route::put('/{id}', [ClientesController::class, 'update'])->name('update');     
            Route::get('/api/consulta-documento/{documento}', [ClientesController::class, 'apiConsultaDocumento']) 
                 ->name('api.consulta');    
        });
        
        Route::prefix('libro-diario')->name('libro-diario.')->group(function () {
            
            
            Route::get('/', [LibroDiarioController::class, 'index'])->name('index');      // contador.libro-diario.index
            Route::get('create', [LibroDiarioController::class, 'create'])->name('create'); // contador.libro-diario.create
            Route::post('store', [LibroDiarioController::class, 'store'])->name('store');  // contador.libro-diario.store   
            Route::get('{id}', [LibroDiarioController::class, 'show'])->name('show');       // contador.libro-diario.show
            Route::get('{id}/edit', [LibroDiarioController::class, 'edit'])->name('edit'); // contador.libro-diario.edit
            Route::put('{id}', [LibroDiarioController::class, 'update'])->name('update');  // contador.libro-diario.update
            Route::delete('{id}', [LibroDiarioController::class, 'destroy'])->name('destroy'); // contador.libro-diario.destroy           
            Route::get('exportar', [LibroDiarioController::class, 'exportar'])->name('exportar');// contador.libro-diario.exportar
            Route::get('api/estadisticas', [LibroDiarioController::class, 'getEstadisticas'])->name('api.estadisticas'); // contador.libro-diario.api.estadisticas
            Route::get('api/busqueda-avanzada', [LibroDiarioController::class, 'getBusquedaAvanzada'])->name('api.busqueda-avanzada'); // contador.libro-diario.api.busqueda-avanzada
        });

        Route::prefix('anulacion-cobranza')->name('anulacion.')->group(function () {
            
            Route::get('/{serie}/{numero}', [App\Http\Controllers\Contabilidad\AnulacionPlanillaController::class, 'show'])->name('show'); // contador.libro-diario.show           
            Route::post('/store', [App\Http\Controllers\Contabilidad\AnulacionPlanillaController::class, 'store'])->name('store');  // contador.libro-diario.store
        });
        
        Route::prefix('caja')->name('caja.')->group(function () {
            Route::get('/', [CajaController::class, 'index'])->name('index');
            Route::get('/create', [CajaController::class, 'create'])->name('create');
            Route::post('/', [CajaController::class, 'store'])->name('store');
            Route::get('/{id}', [CajaController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [CajaController::class, 'edit'])->name('edit');
            Route::put('/{id}', [CajaController::class, 'update'])->name('update');
            Route::delete('/{id}', [CajaController::class, 'destroy'])->name('destroy');
        });


        Route::prefix('libro-mayor')->name('libro-mayor.')->group(function () {

                
                Route::get('/exportar', [App\Http\Controllers\Contabilidad\LibroMayorController::class, 'exportar'])->name('exportar');

                
                Route::get('/exportar-cuenta/{cuenta}', [App\Http\Controllers\Contabilidad\LibroMayorController::class, 'exportarCuenta'])
                    ->where('cuenta', '[0-9\.]+') // Permite números y puntos
                    ->name('exportarCuenta'); // Nombre corregido

                Route::get('/movimientos', [App\Http\Controllers\Contabilidad\LibroMayorController::class, 'movimientos'])->name('movimientos');
           
                Route::get('/comparacion', [App\Http\Controllers\Contabilidad\LibroMayorController::class, 'comparacionPeriodos'])->name('comparacion');

                Route::get('/cuenta/{cuenta}', [App\Http\Controllers\Contabilidad\LibroMayorController::class, 'cuenta'])
                    ->where('cuenta', '[0-9\.]+') // Permite números y puntos
                    ->name('cuenta');

                Route::get('/', [App\Http\Controllers\Contabilidad\LibroMayorController::class, 'index'])->name('index');
            });

         Route::prefix('balance-comprobacion')->name('balance-comprobacion.')->group(function () {
                Route::get('/', [App\Http\Controllers\Contabilidad\BalanceComprobacionController::class, 'index'])->name('index');
                Route::get('/detalle', [App\Http\Controllers\Contabilidad\BalanceComprobacionController::class, 'detalleCuenta'])->name('detalle');
                 Route::get('/clases',[App\Http\Controllers\Contabilidad\BalanceComprobacionController::class, 'porClases'])->name('clases');
                Route::get('/comparacion', [App\Http\Controllers\Contabilidad\BalanceComprobacionController::class, 'comparacion'])->name('comparacion');
                Route::get('/verificar', [App\Http\Controllers\Contabilidad\BalanceComprobacionController::class, 'verificar'])->name('verificar');
                Route::get('/exportar', [App\Http\Controllers\Contabilidad\BalanceComprobacionController::class, 'exportar'])->name('exportar');
            });

        Route::prefix('plan-cuentas')->name('plan-cuentas.')->group(function () {
            Route::get('/', [PlanCuentasController::class, 'index'])->name('index');
            Route::get('/create', [PlanCuentasController::class, 'create'])->name('create');
            Route::post('/', [PlanCuentasController::class, 'store'])->name('store');
            
            Route::get('/{codigo}/edit', [PlanCuentasController::class, 'edit'])
                ->where('codigo', '[0-9\.]+')
                ->name('edit');
            Route::put('/{codigo}', [PlanCuentasController::class, 'update'])
                ->where('codigo', '[0-9\.]+')
                ->name('update');
            Route::delete('/{codigo}', [PlanCuentasController::class, 'destroy'])
                ->where('codigo', '[0-9\.]+')
                ->name('destroy');
        });

        Route::prefix('bancos')->name('bancos.')->group(function () {
            Route::get('/', [BancosController::class, 'index'])->name('index');
            Route::get('/detalle/{cuenta}', [BancosController::class, 'detalle'])->name('detalle');
            Route::get('/flujo-diario', [BancosController::class, 'flujoDiario'])->name('flujo-diario');
            Route::get('/diario', [BancosController::class, 'diario'])->name('diario');
            Route::get('/mensual', [BancosController::class, 'resumenMensual'])->name('mensual');
            Route::get('/conciliacion', [BancosController::class, 'conciliacion'])->name('conciliacion');
            Route::post('/conciliacion', [BancosController::class, 'storeConciliacion'])->name('conciliacion.store');
            Route::get('/transferencias', [BancosController::class, 'transferencias'])->name('transferencias');
            Route::get('/reporte', [BancosController::class, 'generarReporte'])->name('reporte');
        });

        Route::prefix('flujo/cobranzas')->group(function () {
    
            Route::get('/paso-1', [FlujoCajaController::class, 'showPaso1'])
                ->name('flujo.cobranzas.paso1');

            Route::post('/paso-1', [FlujoCajaController::class, 'handlePaso1'])
                ->name('flujo.cobranzas.handlePaso1');
          
            Route::get('/paso-2', [FlujoCajaController::class, 'showPaso2'])
                ->name('flujo.cobranzas.paso2');

            
            Route::post('/paso-2', [FlujoCajaController::class, 'handlePaso2'])
            ->name('flujo.cobranzas.handlePaso2');

            Route::get('/paso-3', [FlujoCajaController::class, 'showPaso3'])
                ->name('flujo.cobranzas.paso3');

            Route::post('/paso-3', [FlujoCajaController::class, 'handlePaso3'])
                ->name('flujo.cobranzas.handlePaso3');

            Route::get('/paso-4', [FlujoCajaController::class, 'showPaso4'])
                ->name('flujo.cobranzas.paso4');

            Route::post('/procesar', [FlujoCajaController::class, 'procesar'])
                ->name('flujo.cobranzas.procesar');

        });

        Route::get('cuentas-por-cobrar', [App\Http\Controllers\Contabilidad\CuentasPorCobrarController::class, 'index'])
             ->name('cxc.index');

        Route::get('/api/clientes/search', [ClientesController::class, 'search'])
             ->name('api.clientes.search');


        Route::prefix('inventario')->name('inventario.')->group(function () {
            Route::get('/', [InventarioController::class, 'index'])->name('index');
            Route::get('/crear', [InventarioController::class, 'create'])->name('create');
            Route::post('/', [InventarioController::class, 'store'])->name('store');
            Route::get('/{codPro}', [InventarioController::class, 'show'])->name('show');
            Route::get('/laboratorios', [InventarioController::class, 'laboratorios'])->name('laboratorios');
            Route::get('/vencimientos', [InventarioController::class, 'vencimientos'])->name('vencimientos');
        });

        Route::get('/test-stock', [InventarioController::class, 'stockLotes'])->name('test.stock');


        Route::prefix('ventas')->name('facturas.')->group(function () {
            
            Route::get('/', [App\Http\Controllers\Ventas\FacturacionController::class, 'index'])
                 ->name('index'); // contador.facturas.index
            
            Route::get('/crear', [App\Http\Controllers\Ventas\FacturacionController::class, 'create'])
                 ->name('create'); // contador.facturas.create
            
            Route::get('/mostrar/{numero}/{tipo}', [App\Http\Controllers\Ventas\FacturacionController::class, 'show'])
                 ->name('show'); // contador.facturas.show
                 
            Route::post('/guardar', [App\Http\Controllers\Ventas\FacturacionController::class, 'store'])
                 ->name('store');
            
            Route::post('/carrito/agregar', [App\Http\Controllers\Ventas\FacturacionController::class, 'carritoAgregar'])
                 ->name('carrito.agregar');
            
            Route::delete('/carrito/eliminar/{itemId}', [App\Http\Controllers\Ventas\FacturacionController::class, 'carritoEliminar'])
                 ->name('carrito.eliminar');
                 
            Route::post('/carrito/pago', [App\Http\Controllers\Ventas\FacturacionController::class, 'carritoActualizarPago'])
                 ->name('carrito.pago');
            
            Route::get('/api/buscar-clientes', [App\Http\Controllers\Ventas\FacturacionController::class, 'buscarClientes'])
                 ->name('api.buscarClientes');
                 
            Route::get('/api/buscar-productos', [App\Http\Controllers\Ventas\FacturacionController::class, 'buscarProductos'])
                 ->name('api.buscarProductos'); 
                 
            Route::get('/api/buscar-lotes/{codPro}', [App\Http\Controllers\Ventas\FacturacionController::class, 'buscarLotes'])
                 ->name('api.buscarLotes');     
        });

        Route::prefix('proveedores')->name('proveedores.')->group(function () {
            Route::get('/', [App\Http\Controllers\Contabilidad\ProveedoresController::class, 'index'])
                 ->name('index'); // contador.proveedores.index
                 
            Route::get('/crear', [App\Http\Controllers\Contabilidad\ProveedoresController::class, 'create'])
                 ->name('crear'); // contador.proveedores.crear
                 
            Route::post('/store', [App\Http\Controllers\Contabilidad\ProveedoresController::class, 'store'])
                 ->name('store'); // contador.proveedores.store
            
           
        });

        Route::prefix('cuentas-por-pagar')->name('cxp.')->group(function () {
            Route::get('/', [CuentasPorPagarController::class, 'index'])->name('index'); // contador.cxp.index
            Route::get('/proveedor/{id}', [CuentasPorPagarController::class, 'showByProveedor'])->name('show'); // contador.cxp.show
        });

        Route::prefix('flujo/egresos')->name('flujo.egresos.')->group(function () {
            Route::get('/paso-1', [FlujoEgresoController::class, 'showPaso1'])->name('paso1');  
            Route::post('/paso-1', [FlujoEgresoController::class, 'handlePaso1'])->name('handlePaso1'); 
            Route::get('/paso-2', [FlujoEgresoController::class, 'showPaso2'])->name('paso2'); 
            Route::post('/paso-2', [FlujoEgresoController::class, 'handlePaso2'])->name('handlePaso2');
            Route::get('/paso-3', [FlujoEgresoController::class, 'showPaso3'])->name('paso3');  
            Route::post('/procesar', [FlujoEgresoController::class, 'procesar'])->name('procesar'); 
        });

        


        Route::prefix('compras')->name('compras.')->group(function () {
            Route::get('/', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'index'])
                 ->name('index'); // contador.compras.index
                 
            Route::get('/crear', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'create'])
                 ->name('create'); // contador.compras.registro.create
                 
            Route::post('/guardar', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'store'])
                 ->name('store'); // contador.compras.store
                 
            Route::post('/carrito/agregar', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'carritoAgregar'])
                 ->name('carrito.agregar'); // contador.compras.carrito.agregar
            
            Route::delete('/carrito/eliminar/{itemId}', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'carritoEliminar'])
                 ->name('carrito.eliminar'); // contador.compras.carrito.eliminar
                 
            Route::post('/carrito/pago', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'carritoActualizarPago'])
                 ->name('carrito.pago'); // contador.compras.carrito.pago
            
            Route::get('/show/{id}', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'show'])
                ->name('show'); // contador.compras.show
            
            Route::get('/api/buscar-proveedores', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'buscarProveedores'])
                 ->name('api.buscarProveedores'); // contador.compras.api.buscarProveedores


            Route::get('/api/buscar-productos', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'buscarProductos'])
                 ->name('api.buscarProductos'); // contador.compras.api.buscarProductos

            Route::get('/api/buscar-lotes/{codPro}', [App\Http\Controllers\Contabilidad\OrdenCompraController::class, 'buscarLotes'])
                 ->name('api.buscarLotes'); // contador.compras.api.buscarLotes
        });

        Route::prefix('notas-credito')->name('notas-credito.')->group(function () {
            
            Route::get('/', [App\Http\Controllers\Contabilidad\NotaCreditoController::class, 'index'])
                 ->name('index');  // contador.notas_credito.index
            
            Route::get('/crear', [App\Http\Controllers\Contabilidad\NotaCreditoController::class, 'create'])
                 ->name('create'); // contador.notas_credito.create
            
            Route::post('/buscar-factura', [App\Http\Controllers\Contabilidad\NotaCreditoController::class, 'buscarFactura'])
                 ->name('buscarFactura'); // contador.notas_credito.buscarFactura
                 
            Route::get('/crear-paso-2', [App\Http\Controllers\Contabilidad\NotaCreditoController::class, 'showPaso2'])
                 ->name('showPaso2'); // contador.notas_credito.showPaso2
                 
            Route::post('/guardar', [App\Http\Controllers\Contabilidad\NotaCreditoController::class, 'store'])
                 ->name('store');   // contador.notas_credito.store
                 
        
            Route::get('/show/{numero}', [App\Http\Controllers\Contabilidad\NotaCreditoController::class, 'show'])
                 ->name('show'); // contador.notas_credito.show
        });


        Route::prefix('registro-compra')->name('compras.registro.')->group(function () {
            Route::get('/crear', [RegistroCompraController::class, 'create'])->name('create');
            Route::post('/guardar', [RegistroCompraController::class, 'store'])->name('store');
        });

       
        Route::prefix('letras-descuento')->name('letras_descuento.')->group(function () {
            
           
            Route::get('/', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'index'])
                 ->name('index'); // contador.letras_descuento.index
            
            
            Route::get('/crear', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'create'])
                 ->name('create'); // contador.letras_descuento.create
                 
            Route::post('/guardar', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'store'])
                 ->name('store'); // contador.letras_descuento.store
            
            Route::get('/{serie}/{numero}', action: [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'show'])
                 ->name('show'); // contador.letras_descuento.show

            Route::get('/api/buscar-letras', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'apiBuscarLetrasPendientes'])
                 ->name('api.buscarLetras');    // contador.letras_descuento.api.buscarLetras
                 
            Route::post('/agregar-letra', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'agregarLetraPlanilla'])
                 ->name('api.agregarLetra'); // contador.letras_descuento.api.agregarLetra
                 
            Route::delete('/quitar-letra/{id}', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'quitarLetraPlanilla'])
                 ->name('api.quitarLetra'); // contador.letras_descuento.api.quitarLetra

            Route::post('/{serie}/{numero}/procesar', [App\Http\Controllers\Contabilidad\PlanillaLetrasController::class, 'procesarDescuento'])
                 ->name('procesar'); // contador.letras_descuento.procesar
        });
        


        
        Route::prefix('estado-resultados')->name('estado-resultados.')->group(function () {
                Route::get('/', [EstadoResultadosController::class, 'index'])->name('index'); // contador.estado_resultados.index
                Route::get('/periodos', [EstadoResultadosController::class, 'porPeriodos'])->name('periodos'); // contador.estado_resultados.periodos
                Route::get('/detalle/{cuenta}', [EstadoResultadosController::class, 'detalleCuenta'])->name('detalle'); // contador.estado_resultados.detalle
                Route::get('/comparativo', [EstadoResultadosController::class, 'comparativo'])->name('comparativo'); // contador.estado_resultados.comparativo
                Route::get('/flujo-caja', [EstadoResultadosController::class, 'cashFlow'])->name('flujo-caja'); // contador.estado_resultados.flujo-caja
                Route::get('/balance-general', [BalanceGeneralController::class, 'index'])->name('balance-general'); // contador.estado_resultados.balance-general
                Route::get('/exportar', [EstadoResultadosController::class, 'exportar'])->name('exportar');     // contador.estado_resultados.exportar             
               Route::get('/api/clientes/search', [ClientesController::class, 'search'])->name('api.clientes.search');      // contador.estado_resultados.api.clientes.search
            });
        });
    });



Route::get('/acceso-denegado', function () {
    return view('errors.403');
})->name('access.denied');

