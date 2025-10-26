<?php

//RUTAS MEJORADAS PARA CONTABILIDAD FARMACÉUTICA
//Pensando como un contador farmacéutico profesional

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ContadorDashboardController;
use App\Http\Controllers\Admin\PlanillaController;
use App\Http\Controllers\Admin\BancoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\CuentaController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\Ventas\PlanillasController;
use App\Http\Controllers\Contabilidad\ReportesSunatController;


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
        Route::get('ventas', [App\Http\Controllers\Ventas\DashboardVentasController::class, 'index'])->name('ventas');
        Route::get('facturacion', [App\Http\Controllers\Ventas\FacturacionController::class, 'index'])->name('facturacion');
        Route::get('cuentas-cobrar', [App\Http\Controllers\Ventas\CuentasCobrarController::class, 'index'])->name('cuentas-cobrar');
        Route::get('inventario', [App\Http\Controllers\Farmacia\InventarioController::class, 'index'])->name('inventario');
        Route::get('clientes', [App\Http\Controllers\Clientes\ClientesController::class, 'index'])->name('clientes');
        Route::get('estado-cuenta', [App\Http\Controllers\Clientes\EstadoCuentaController::class, 'index'])->name('estado-cuenta');
        Route::get('planillas', [App\Http\Controllers\Ventas\PlanillasController::class, 'index'])->name('planillas');
        Route::get('analytics', [App\Http\Controllers\Reportes\AnalyticsController::class, 'index'])->name('analytics');
        Route::get('kpis', [App\Http\Controllers\Reportes\KpiController::class, 'index'])->name('kpis');
        
        // Reportes Financieros y Libros Electrónicos
        Route::get('reportes/financiero', [ReportesSunatController::class, 'index'])->name('reportes.financiero');
        Route::get('reportes/ventas', [ReportesSunatController::class, 'registroVentas'])->name('reportes.ventas');
        Route::get('reportes/compras', [ReportesSunatController::class, 'registroCompras'])->name('reportes.compras');
        Route::get('libros-electronicos', [ReportesSunatController::class, 'librosElectronicos'])->name('libros-electronicos');
        Route::get('reportes/declaracion', [ReportesSunatController::class, 'declaracionJurada'])->name('reportes.declaracion');
        
        // Rutas de Facturas
        Route::get('facturas', [App\Http\Controllers\Ventas\FacturacionController::class, 'index'])->name('facturas.index');
        Route::get('facturas/create', [App\Http\Controllers\Ventas\FacturacionController::class, 'index'])->name('facturas.create');
        Route::post('facturas', [App\Http\Controllers\Ventas\FacturacionController::class, 'store'])->name('facturas.store');
        Route::get('facturas/export', [App\Http\Controllers\Ventas\FacturacionController::class, 'exportar'])->name('facturas.export');
        
        // Productos e Inventario
        Route::get('productos', [App\Http\Controllers\Farmacia\InventarioController::class, 'index'])->name('productos.index');
        Route::get('productos/inventario', [App\Http\Controllers\Farmacia\InventarioController::class, 'index'])->name('productos.inventario');
        Route::get('productos/vencimientos', [App\Http\Controllers\Farmacia\InventarioController::class, 'index'])->name('productos.vencimientos');
        
        // Reportes adicionales
        Route::get('reportes/inventario', [ReportesSunatController::class, 'index'])->name('reportes.inventario');
        Route::get('reportes/medicamentos-controlados', [ReportesSunatController::class, 'index'])->name('reportes.medicamentos-controlados');
        Route::get('reportes/exportar', [ReportesSunatController::class, 'index'])->name('reportes.exportar');
        
        // Trazabilidad
        Route::get('trazabilidad', [App\Http\Controllers\Farmacia\TrazabilidadController::class, 'index'])->name('trazabilidad.index');
        
        // Configuración
        Route::get('configuracion/usuarios', [App\Http\Controllers\Admin\UsuarioController::class, 'index'])->name('configuracion.usuarios');
        Route::get('configuracion/parametros', [App\Http\Controllers\Admin\UsuarioController::class, 'index'])->name('configuracion.parametros');
        Route::get('configuracion/cambiar-password', [ContadorDashboardController::class, 'index'])->name('configuracion.cambiar-password');
        
        // Perfil
        Route::get('perfil', [ContadorDashboardController::class, 'index'])->name('perfil');
    });
});


Route::get('/acceso-denegado', function () {
    return view('errors.403');
})->name('access.denied');

