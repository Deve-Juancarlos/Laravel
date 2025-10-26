<?php



use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Clientes\ClientesController;

use App\Http\Controllers\Admin\PlanillaController;
use App\Http\Controllers\Admin\BancoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\CuentaController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\DashboardController;

//RUTAS PÚBLICAS
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

//RUTAS GENERALES AUTENTICADAS
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/alerts', [DashboardController::class, 'getAlerts'])->name('dashboard.alerts');
    
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
        Route::get('comisiones-vendedores', [ReporteController::class, 'comisionesVendedores'])->name('comisiones-vendedores');
        
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

//RUTAS VENDEDOR
Route::middleware(['auth', 'check.vendedor'])->group(function () {
    Route::get('/dashboard/vendedor', [DashboardController::class, 'vendedorDashboard'])->name('dashboard.vendedor');
    Route::prefix('vendedor')->name('vendedor.')->group(function () {
        Route::get('mis-cobranzas', [DashboardController::class, 'misCobranzas'])->name('mis-cobranzas');
        Route::get('metas', [DashboardController::class, 'verMetas'])->name('metas');
        Route::post('actualizar-meta', [DashboardController::class, 'actualizarMeta'])->name('actualizar-meta');
    });
});

Route::middleware(['auth', 'check.contador'])->group(function () {
    
    Route::get('/contabilidad/dashboard', [DashboardController::class, 'contadorDashboard'])
    ->name('contabilidad.dashboard');

    Route::get('/reportes/financiero', [ReporteController::class, 'index'])
        ->name('reportes.financiero');

    Route::get('/contabilidad/libros-electronicos', [ReporteController::class, 'librosElectronicos'])
        ->name('libros-electronicos');

    Route::get('/contabilidad/facturas', [ReporteController::class, 'facturas'])
        ->name('facturas.index');

    Route::get('/contabilidad/reportes/exportar', [ReporteController::class, 'exportVentas'])
        ->name('reportes.exportar');

    // Clientes
     Route::get('/clientes', [ClientesController::class, 'vistaIndex'])->name('clientes.index');
    Route::get('/clientes/crear', [ClientesController::class, 'vistaCrear'])->name('clientes.crear');
    Route::get('/clientes/buscar', [ClientesController::class, 'vistaBuscar'])->name('clientes.buscar');
    Route::get('/clientes/{cliente}', [ClientesController::class, 'vistaShow'])->name('clientes.show');
    Route::get('/clientes/{cliente}/editar', [ClientesController::class, 'vistaEditar'])->name('clientes.editar');
    Route::get('/clientes/{cliente}/estado-cuenta', [ClientesController::class, 'vistaEstadoCuenta'])->name('clientes.estado-cuenta');



    

    // Productos
    Route::get('/productos', function () {
        return "Módulo de Productos en desarrollo";
    })->name('productos.index');

    Route::get('/productos/inventario', function () {
        return "Módulo de Inventario en desarrollo";
    })->name('productos.inventario');

    // ✅ Nuevo: Control de Vencimientos
    Route::get('/productos/vencimientos', function () {
        return "Módulo de Control de Vencimientos en desarrollo";
    })->name('productos.vencimientos');
    
      // ✅ Configuración
    Route::prefix('configuracion')->group(function () {
        Route::get('/usuarios', function () {
            return "Módulo de Configuración → Usuarios en desarrollo";
        })->name('configuracion.usuarios');

        Route::get('/parametros', function () {
            return "Módulo de Configuración → Parámetros en desarrollo";
        })->name('configuracion.parametros');
    });
    // ✅ Perfil del usuario
    Route::get('/perfil', function () {
        return "Página de perfil del usuario (en desarrollo)";
    })->name('perfil');
    
    Route::get('/configuracion/cambiar-password', function () {
    return 'configuracion.cambiar-password';
    })->name('configuracion.cambiar-password');

    Route::get('/facturas/export', [ReporteController::class, 'exportFacturas'])->name('facturas.export');

});


Route::get('/acceso-denegado', function () {
    return view('errors.403');
})->name('access.denied');

