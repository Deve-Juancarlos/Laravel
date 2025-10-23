<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\PlanillaController;
use App\Http\Controllers\Admin\BancoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\CuentaController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\Contabilidad\ClienteController;    
use App\Http\Controllers\Contabilidad\DashboardContador;
use App\Http\Controllers\Contabilidad\ReniecController;
use App\Http\Controllers\Contabilidad\FacturaController;
use App\Http\Controllers\Contabilidad\PlanillaCobranzaController;

// Rutas Públicas (Login / Registro)

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard Genérico (para cualquier usuario autenticado)

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/alerts', [DashboardController::class, 'getAlerts'])->name('dashboard.alerts');
});

// Rutas  del ADMINISTRADOR

// Rutas del ADMINISTRADOR
Route::middleware(['auth', 'check.admin'])->group(function () {
    // Dashboard del administrador
    Route::get('/admin/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard.admin');

    // Módulo: Planillas de Cobranza
    Route::prefix('admin/planillas')->name('admin.planillas.')->group(function () {
        Route::get('/', [PlanillaController::class, 'index'])->name('index');
        Route::get('/{serie}/{numero}', [PlanillaController::class, 'show'])->name('show');
        Route::get('/{serie}/{numero}/edit', [PlanillaController::class, 'edit'])->name('edit');
        Route::put('/{serie}/{numero}', [PlanillaController::class, 'update'])->name('update');
        Route::delete('/{serie}/{numero}', [PlanillaController::class, 'destroy'])->name('destroy');
    });

    // Módulo: Bancos
    Route::resource('admin/bancos', BancoController::class)
        ->except(['show'])
        ->names('admin.bancos');

    // Módulo: Usuarios y Roles
    Route::get('/admin/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios.index');
    Route::get('/admin/usuarios/{usuario}/rol', [UsuarioController::class, 'roles'])->name('admin.usuarios.roles');
    Route::put('/admin/usuarios/{usuario}/rol', [UsuarioController::class, 'updateRol'])->name('admin.usuarios.updateRol');

    // Módulo: Cuentas Corrientes
    Route::get('/admin/cuentas-corrientes', [CuentaController::class, 'index'])->name('admin.cuentas-corrientes.index');
    Route::get('/cuentas-corrientes/exportar', [CuentaController::class, 'exportar'])->name('admin.cuentas-corrientes.exportar');

    // Módulo: Reportes
    Route::get('/admin/reportes/facturas', [ReporteController::class, 'facturas'])->name('admin.reportes.facturas');
    Route::get('/admin/reportes/movimientos', [ReporteController::class, 'movimientos'])->name('admin.reportes.movimientos');    
    Route::get('/facturas/export', [ReporteController::class, 'exportFacturas'])->name('facturas.export');
    Route::get('/movimientos/export', [ReporteController::class, 'exportMovimientos'])->name('movimientos.export');

    // Módulo: Auditoría
    Route::get('/admin/auditoria', [AuditoriaController::class, 'index'])->name('admin.auditoria.index');

    // Módulo: Notificaciones ⬅️ CORREGIDO
    Route::prefix('admin/notificaciones')->name('admin.notificaciones.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\NotificacionController::class, 'index'])
            ->name('index');
        
        Route::get('/count', [App\Http\Controllers\Admin\NotificacionController::class, 'countNoLeidas'])
            ->name('count');
        
        Route::post('/{id}/marcar-leida', [App\Http\Controllers\Admin\NotificacionController::class, 'marcarLeida'])
            ->name('marcar-leida');
        
        Route::post('/marcar-todas-leidas', [App\Http\Controllers\Admin\NotificacionController::class, 'marcarTodasLeidas'])
            ->name('marcar-todas');
        
        Route::delete('/{id}', [App\Http\Controllers\Admin\NotificacionController::class, 'eliminar'])
            ->name('eliminar');
        
        Route::delete('/limpiar/leidas', [App\Http\Controllers\Admin\NotificacionController::class, 'limpiarLeidas'])
            ->name('limpiar');
    });
});

//  Rutas del VENDEDOR

Route::middleware(['auth', 'check.vendedor'])->group(function () {
    Route::get('/dashboard/vendedor', [DashboardController::class, 'vendedorDashboard'])->name('dashboard.vendedor');
});



// Página de Acceso Denegado

Route::get('/acceso-denegado', function () {
    return view('errors.403');
})->name('access.denied');

Route::post('/session/ping', function () {
    
    return response()->json([
        'status' => 'ok',
        'time' => now()->toDateTimeString()
    ]);
})->name('session.ping')->middleware('auth');

// Rutas para gestión de clientes
Route::prefix('contabilidad/clientes')
    ->name('contabilidad.clientes.')
    ->middleware(['auth']) // <- esto es lo clave
    ->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('index');
        Route::get('/crear', [ClienteController::class, 'create'])->name('create');
        Route::post('/', [ClienteController::class, 'store'])->name('store');
        Route::get('/buscar', [ClienteController::class, 'buscar'])->name('buscar');
        Route::post('/consultar', [ClienteController::class, 'consultar'])->name('consultar');
        Route::post('/consultar-reniec', [ClienteController::class, 'consultarReniec'])->name('consultar-reniec');
        Route::post('/crear-desde-reniec', [ClienteController::class, 'crearDesdeReniec'])->name('crear-desde-reniec');
        Route::get('/{id}', [ClienteController::class, 'show'])->name('show');
        Route::get('/{id}/editar', [ClienteController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ClienteController::class, 'update'])->name('update');
        Route::delete('/{id}', [ClienteController::class, 'destroy'])->name('destroy');
    });


 Route::middleware(['auth', 'check.contador'])->group(function () {
    Route::get('dashboard', [DashboardContador::class, 'index'])
            ->name('contabilidad.dashboard');
     
 
    
    Route::get('libro-mayor', [DashboardContador::class, 'libroMayor'])
        ->name('libro-mayor');
    
    Route::post('libro-mayor', [DashboardContador::class, 'buscarLibroMayor'])
        ->name('libro-mayor.buscar');
    
    Route::get('balance-general', [DashboardContador::class, 'balanceGeneral'])
        ->name('balance-general');
    
    Route::post('balance-general', [DashboardContador::class, 'generarBalanceGeneral'])
        ->name('balance-general.generar');
    
    Route::get('estado-resultados', [DashboardContador::class, 'estadoResultados'])
        ->name('estado-resultados');
    
    Route::post('estado-resultados', [DashboardContador::class, 'generarEstadoResultados'])
        ->name('estado-resultados.generar');
    
    
    Route::get('analisis-cartera', [DashboardContador::class, 'analisisCartera'])
        ->name('analisis-cartera');
    
    Route::post('analisis-cartera/filtrar', [DashboardContador::class, 'filtrarCartera'])
        ->name('analisis-cartera.filtrar');
    
    Route::get('cartera/detalle/{cliente}', [DashboardContador::class, 'detalleCarteraCliente'])
        ->name('cartera.detalle-cliente');
    
    
    Route::get('control-farmaceutico', [DashboardContador::class, 'controlFarmaceutico'])
        ->name('control-farmaceutico');
    
    Route::post('control-farmaceutico/alertas', [DashboardContador::class, 'configurarAlertasFarmaceuticas'])
        ->name('control-farmaceutico.alertas');
    
    Route::get('productos-vencidos', [DashboardContador::class, 'productosVencidos'])
        ->name('productos-vencidos');
    
    Route::get('productos-temperatura', [DashboardContador::class, 'productosTemperatura'])
        ->name('productos-temperatura');
    
    
    
    Route::get('reportes-tributarios', [DashboardContador::class, 'reportesTributarios'])
        ->name('reportes-tributarios');
    
    Route::post('reportes-tributarios/libros-electronicos', [DashboardContador::class, 'generarLibrosElectronicos'])
        ->name('reportes-tributarios.libros');
    
    Route::get('reportes-tributarios/igv-mensual', [DashboardContador::class, 'igvMensual'])
        ->name('reportes-tributarios.igv');
    

    
    Route::prefix('reniec')->name('reniec.')->group(function() {
        
        Route::post('consultar-dni', [ReniecController::class, 'consultarDNI'])
            ->name('consultar-dni');
        
        Route::post('consultar-ruc', [ReniecController::class, 'consultarRUC'])
            ->name('consultar-ruc');
        
        Route::post('buscar-cliente', [ReniecController::class, 'buscarCliente'])
            ->name('buscar-cliente');
        
        Route::post('crear-cliente', [ReniecController::class, 'crearCliente'])
            ->name('crear-cliente');
        
        Route::get('modal-busqueda', [ReniecController::class, 'modalBusqueda'])
            ->name('modal-busqueda');
    });
    
 
    
    Route::prefix('facturacion')->name('facturacion.')->group(function() {
        
        Route::get('nueva-factura', [FacturaController::class, 'nuevaFactura'])
            ->name('nueva');
        
        Route::post('buscar-cliente', [FacturaController::class, 'buscarCliente'])
            ->name('buscar-cliente');
        
        Route::post('buscar-productos', [FacturaController::class, 'buscarProductos'])
            ->name('buscar-productos');
        
        Route::post('validar-stock', [FacturaController::class, 'validarStock'])
            ->name('validar-stock');
        
        Route::post('calcular-totales', [FacturaController::class, 'calcularTotales'])
            ->name('calcular-totales');
        
        Route::post('guardar-factura', [FacturaController::class, 'guardarFactura'])
            ->name('guardar');
        
        Route::get('facturas', [FacturaController::class, 'listarFacturas'])
            ->name('listar');
        
        Route::get('factura/{numero}', [FacturaController::class, 'verFactura'])
            ->name('ver');
        
        Route::get('factura/{numero}/pdf', [FacturaController::class, 'generarPDF'])
            ->name('pdf');
        
        Route::get('factura/{numero}/xml', [FacturaController::class, 'generarXML'])
            ->name('xml');
        
        // Modal de búsqueda de cliente para facturación
        Route::get('modal-cliente', [FacturaController::class, 'modalCliente'])
            ->name('modal-cliente');
    });
    
  
    
    Route::prefix('planillas-cobranza')->name('planillas-cobranza.')->group(function() {
        
        Route::get('/', [PlanillaCobranzaController::class, 'index'])
            ->name('index');
        
        Route::get('nueva', [PlanillaCobranzaController::class, 'nuevaPlanilla'])
            ->name('nueva');
        
        Route::post('generar', [PlanillaCobranzaController::class, 'generarPlanilla'])
            ->name('generar');
        
        Route::get('planilla/{serie}/{numero}', [PlanillaCobranzaController::class, 'verPlanilla'])
            ->name('ver');
        
        Route::post('planilla/{serie}/{numero}/agregar-cliente', [PlanillaCobranzaController::class, 'agregarCliente'])
            ->name('agregar-cliente');
        
        Route::post('planilla/{serie}/{numero}/registrar-pago', [PlanillaCobranzaController::class, 'registrarPago'])
            ->name('registrar-pago');
        
        Route::post('planilla/{serie}/{numero}/confirmar', [PlanillaCobranzaController::class, 'confirmarPlanilla'])
            ->name('confirmar');
        
        Route::delete('planilla/{serie}/{numero}', [PlanillaCobranzaController::class, 'eliminarPlanilla'])
            ->name('eliminar');
        
        // Reportes de planillas
        Route::get('reporte-efectividad', [PlanillaCobranzaController::class, 'reporteEfectividad'])
            ->name('reporte-efectividad');
        
        Route::get('reporte-vendedor/{vendedor}', [PlanillaCobranzaController::class, 'reportePorVendedor'])
            ->name('reporte-vendedor');
    });
    
 
    
    Route::prefix('api')->name('api.')->group(function() {
        
        Route::get('dashboard/datos', [DashboardContador::class, 'datosTiempoReal'])
            ->name('dashboard-datos');
        
        Route::get('cuentas/buscar', [DashboardContador::class, 'buscarCuentas'])
            ->name('cuentas-buscar');
        
        Route::get('productos/buscar', [DashboardContador::class, 'buscarProductos'])
            ->name('productos-buscar');
        
        Route::get('clientes/buscar', [DashboardContador::class, 'buscarClientes'])
            ->name('clientes-buscar');
        
        Route::post('validaciones/asientos', [DashboardContador::class, 'validarAsientos'])
            ->name('validaciones-asientos');
    });
    
   
    
    Route::prefix('reportes')->name('reportes.')->group(function() {
        
        Route::get('ventas-periodo', [DashboardContador::class, 'reporteVentasPeriodo'])
            ->name('ventas-periodo');
        
        Route::get('comisiones-vendedores', [DashboardContador::class, 'reporteComisiones'])
            ->name('comisiones-vendedores');
        
        Route::get('inventario-valorizado', [DashboardContador::class, 'reporteInventarioValorizado'])
            ->name('inventario-valorizado');
        
        Route::get('flujo-caja', [DashboardContador::class, 'reporteFlujoCaja'])
            ->name('flujo-caja');
        
        // Exportaciones
        Route::post('exportar/excel', [DashboardContador::class, 'exportarExcel'])
            ->name('exportar.excel');
        
        Route::post('exportar/pdf', [DashboardContador::class, 'exportarPDF'])
            ->name('exportar.pdf');
    });
    
    
    
    Route::prefix('configuracion')->name('configuracion.')->group(function() {
        
        Route::get('cuentas-contables', [DashboardContador::class, 'configurarCuentas'])
            ->name('cuentas-contables');
        
        Route::post('cuentas-contables', [DashboardContador::class, 'guardarCuentas'])
            ->name('cuentas-contables.guardar');
        
        Route::get('parametros-farmacia', [DashboardContador::class, 'configurarParametrosFarmacia'])
            ->name('parametros-farmacia');
        
        Route::post('parametros-farmacia', [DashboardContador::class, 'guardarParametrosFarmacia'])
            ->name('parametros-farmacia.guardar');
        
        Route::get('integracion-sunat', [DashboardContador::class, 'configurarIntegracionSUNAT'])
            ->name('integracion-sunat');
        
        Route::post('integracion-sunat', [DashboardContador::class, 'guardarIntegracionSUNAT'])
            ->name('integracion-sunat.guardar');
    });
});


Route::post('api/reniec/consulta-publica', [ReniecController::class, 'consultaPublica'])
    ->name('api.reniec.consulta-publica');


Route::fallback([DashboardContador::class, 'error404'])
    ->name('contabilidad.error404');



?>



