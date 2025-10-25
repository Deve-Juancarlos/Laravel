@extends('layouts.app')

@section('title', 'Cuentas por Cobrar')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-hand-holding-usd text-primary"></i>
                        Cuentas por Cobrar
                    </h1>
                    <p class="text-muted mb-0">Gestión de cobranzas y seguimiento de cuentas</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <a href="{{ route('ventas.cuentas-cobrar.recordatorios') }}" class="btn btn-warning">
                            <i class="fas fa-bell"></i> Recordatorios
                        </a>
                        <button type="button" class="btn btn-success" onclick="registrarPago()">
                            <i class="fas fa-money-bill"></i> Registrar Pago
                        </button>
                        <a href="{{ route('ventas.cuentas-cobrar.estado-cuenta') }}" class="btn btn-info">
                            <i class="fas fa-file-invoice"></i> Estado de Cuenta
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Principales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Total por Cobrar</p>
                            <h4 class="text-primary mb-0">S/ 45,680.00</h4>
                            <small class="text-primary">
                                <i class="fas fa-arrow-down"></i> -8.5% vs mes anterior
                            </small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-money-bill-wave text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Vencidas</p>
                            <h4 class="text-danger mb-0">S/ 12,450.00</h4>
                            <small class="text-danger">
                                <i class="fas fa-exclamation-triangle"></i> 8 facturas
                            </small>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="fas fa-exclamation-circle text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Por Vencer (7 días)</p>
                            <h4 class="text-warning mb-0">S/ 8,920.00</h4>
                            <small class="text-warning">
                                <i class="fas fa-clock"></i> 12 facturas
                            </small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-clock text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Efectividad</p>
                            <h4 class="text-success mb-0">92.5%</h4>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> +2.1% vs mes anterior
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-chart-line text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form class="row g-3" id="filtrosCobranza">
                        <div class="col-md-3">
                            <label class="form-label">Buscar</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="busqueda" placeholder="Cliente, número, DNI...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="estado">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="vencida">Vencida</option>
                                <option value="pago_parcial">Pago Parcial</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Vencimiento</label>
                            <select class="form-select" id="vencimiento">
                                <option value="">Todos</option>
                                <option value="hoy">Vence hoy</option>
                                <option value="semana">Esta semana</option>
                                <option value="mes">Este mes</option>
                                <option value="vencida">Ya vencidas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Monto</label>
                            <select class="form-select" id="monto">
                                <option value="">Todos</option>
                                <option value="menor_1000">Menos de S/ 1,000</option>
                                <option value="1000_5000">S/ 1,000 - S/ 5,000</option>
                                <option value="5000_10000">S/ 5,000 - S/ 10,000</option>
                                <option value="mayor_10000">Más de S/ 10,000</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Ordenar por</label>
                            <select class="form-select" id="orden">
                                <option value="fecha_vencimiento">Fecha Vencimiento</option>
                                <option value="monto_desc">Mayor Monto</option>
                                <option value="monto_asc">Menor Monto</option>
                                <option value="cliente">Cliente</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary" onclick="aplicarFiltros()">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas Importantes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-3 fs-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Atención Requerida</h5>
                        <p class="mb-2">
                            Tienes <strong>8 facturas vencidas</strong> por un total de <strong>S/ 12,450.00</strong> 
                            que requieren seguimiento inmediato.
                        </p>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-warning" onclick="enviarRecordatorios()">
                                <i class="fas fa-envelope"></i> Enviar Recordatorios
                            </button>
                            <a href="{{ route('ventas.cuentas-cobrar.cartera-vencida') }}" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-list"></i> Ver Detalle
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Cuentas por Cobrar -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary"></i>
                        Cuentas por Cobrar
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarReporte()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="actualizarLista()">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaCuentas">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>Factura</th>
                                    <th>Cliente</th>
                                    <th>Fecha Emisión</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Monto Total</th>
                                    <th>Saldo Pendiente</th>
                                    <th>Días Vencido</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-danger">
                                    <td>
                                        <input type="checkbox" class="form-check-input" value="F-001234">
                                    </td>
                                    <td>
                                        <strong class="text-danger">F-001234</strong>
                                        <br>
                                        <small class="text-muted">Ticket: 001234</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-danger bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user text-danger"></i>
                                            </div>
                                            <div>
                                                <strong>Empresa ABC S.A.C.</strong>
                                                <br>
                                                <small class="text-muted">RUC: 20123456789</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="d-block">15/10/2025</span>
                                        <small class="text-muted">15 días</small>
                                    </td>
                                    <td>
                                        <span class="d-block text-danger">15/11/2025</span>
                                        <small class="text-danger"><strong>10 días vencida</strong></small>
                                    </td>
                                    <td>
                                        <strong>S/ 8,500.00</strong>
                                        <br>
                                        <small class="text-muted">Subtotal: S/ 7,203.39</small>
                                    </td>
                                    <td>
                                        <strong class="text-danger">S/ 8,500.00</strong>
                                        <br>
                                        <small class="text-muted">Sin pagos</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">10 días</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">Vencida</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="registrarPagoIndividual('F-001234')" title="Registrar Pago">
                                                <i class="fas fa-money-bill"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="verDetalles('F-001234')" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="enviarRecordatorioIndividual('F-001234')" title="Enviar Recordatorio">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="reprogramarVencimiento('F-001234')"><i class="fas fa-calendar me-2"></i>Reprogramar</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="generarNotaCredito('F-001234')"><i class="fas fa-file-alt me-2"></i>Nota de Crédito</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="enviarNotificacion('F-001234')"><i class="fas fa-bell me-2"></i>Notificar Atraso</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="perdonarDeuda('F-001234')"><i class="fas fa-heart me-2"></i>Perdonar Deuda</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-warning">
                                    <td>
                                        <input type="checkbox" class="form-check-input" value="F-001233">
                                    </td>
                                    <td>
                                        <strong class="text-warning">F-001233</strong>
                                        <br>
                                        <small class="text-muted">Ticket: 001233</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-warning bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user text-warning"></i>
                                            </div>
                                            <div>
                                                <strong>Clínica Salud Total</strong>
                                                <br>
                                                <small class="text-muted">RUC: 20987654321</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="d-block">20/10/2025</span>
                                        <small class="text-muted">10 días</small>
                                    </td>
                                    <td>
                                        <span class="d-block text-warning">20/11/2025</span>
                                        <small class="text-warning"><strong>Vence en 3 días</strong></small>
                                    </td>
                                    <td>
                                        <strong>S/ 15,680.00</strong>
                                        <br>
                                        <small class="text-muted">Subtotal: S/ 13,288.14</small>
                                    </td>
                                    <td>
                                        <strong class="text-warning">S/ 15,680.00</strong>
                                        <br>
                                        <small class="text-muted">Sin pagos</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">-3 días</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Por Vencer</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="registrarPagoIndividual('F-001233')" title="Registrar Pago">
                                                <i class="fas fa-money-bill"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="verDetalles('F-001233')" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="enviarRecordatorioIndividual('F-001233')" title="Enviar Recordatorio">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="reprogramarVencimiento('F-001233')"><i class="fas fa-calendar me-2"></i>Reprogramar</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="generarNotaCredito('F-001233')"><i class="fas fa-file-alt me-2"></i>Nota de Crédito</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="confirmarPago('F-001233')"><i class="fas fa-check me-2"></i>Confirmar Pago</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" value="F-001232">
                                    </td>
                                    <td>
                                        <strong class="text-primary">F-001232</strong>
                                        <br>
                                        <small class="text-muted">Ticket: 001232</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-primary bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <strong>Farmacia La Salud</strong>
                                                <br>
                                                <small class="text-muted">RUC: 20111122233</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="d-block">25/10/2025</span>
                                        <small class="text-muted">5 días</small>
                                    </td>
                                    <td>
                                        <span class="d-block">25/11/2025</span>
                                        <small class="text-muted">En plazo</small>
                                    </td>
                                    <td>
                                        <strong>S/ 6,250.00</strong>
                                        <br>
                                        <small class="text-muted">Subtotal: S/ 5,296.61</small>
                                    </td>
                                    <td>
                                        <strong>S/ 3,125.00</strong>
                                        <br>
                                        <small class="text-success">50% pagado</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">0 días</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Pago Parcial</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="registrarPagoIndividual('F-001232')" title="Completar Pago">
                                                <i class="fas fa-money-bill"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="verDetalles('F-001232')" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="verHistorialPagos('F-001232')" title="Historial Pagos">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <span class="text-muted">Mostrando 1 a 3 de 156 cuentas por cobrar</span>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Anterior</a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Siguiente</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones en Lote -->
    <div class="row mt-3" id="accionesLote" style="display: none;">
        <div class="col-12">
            <div class="alert alert-primary d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-info-circle me-2"></i>
                    <span id="seleccionadosCount">0</span> cuentas seleccionadas
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="enviarRecordatoriosLote()">
                        <i class="fas fa-envelope"></i> Recordatorios
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="registrarPagoLote()">
                        <i class="fas fa-money-bill"></i> Registrar Pagos
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="exportarSeleccionadas()">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="generarReporteLote()">
                        <i class="fas fa-file-pdf"></i> Reporte
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="procesarMorosidadLote()">
                        <i class="fas fa-exclamation-triangle"></i> Morosidad
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarSeleccion()">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let cuentasSeleccionadas = [];

function aplicarFiltros() {
    const filtros = {
        busqueda: $('#busqueda').val(),
        estado: $('#estado').val(),
        vencimiento: $('#vencimiento').val(),
        monto: $('#monto').val(),
        orden: $('#orden').val()
    };

    console.log('Aplicando filtros:', filtros);
    
    Swal.fire({
        title: 'Aplicando filtros...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });

    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Filtros aplicados',
            showConfirmButton: false,
            timer: 1500
        });
    }, 1000);
}

function actualizarLista() {
    Swal.fire({
        title: 'Actualizando lista...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });

    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Lista actualizada',
            showConfirmButton: false,
            timer: 1500
        });
    }, 1500);
}

function registrarPago() {
    Swal.fire({
        title: 'Registrar Pago',
        html: `
            <div class="text-left">
                <div class="mb-3">
                    <label class="form-label">Factura:</label>
                    <select class="form-select" id="facturaPago">
                        <option value="">Seleccionar factura...</option>
                        <option value="F-001234">F-001234 - Empresa ABC S.A.C. (S/ 8,500.00)</option>
                        <option value="F-001233">F-001233 - Clínica Salud Total (S/ 15,680.00)</option>
                        <option value="F-001232">F-001232 - Farmacia La Salud (S/ 6,250.00)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Monto a Pagar:</label>
                    <input type="number" class="form-control" id="montoPago" placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">Método de Pago:</label>
                    <select class="form-select" id="metodoPago">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="cheque">Cheque</option>
                        <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha de Pago:</label>
                    <input type="date" class="form-control" id="fechaPago" value="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones:</label>
                    <textarea class="form-control" id="observacionesPago" rows="3" placeholder="Observaciones adicionales..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Registrar Pago',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const factura = document.getElementById('facturaPago').value;
            const monto = parseFloat(document.getElementById('montoPago').value);
            
            if (!factura || !monto || monto <= 0) {
                Swal.showValidationMessage('Complete todos los campos requeridos');
                return false;
            }
            
            return {
                factura: factura,
                monto: monto,
                metodo: document.getElementById('metodoPago').value,
                fecha: document.getElementById('fechaPago').value,
                observaciones: document.getElementById('observacionesPago').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Pago registrado',
                text: `Pago de S/ ${result.value.monto.toFixed(2)} registrado exitosamente`
            });
        }
    });
}

function registrarPagoIndividual(numeroFactura) {
    // Pre-llenar con la factura seleccionada
    registrarPago();
}

function verDetalles(numeroFactura) {
    window.open(`/ventas/cuentas-cobrar/detalles/${numeroFactura}`, '_blank');
}

function enviarRecordatorioIndividual(numeroFactura) {
    Swal.fire({
        title: 'Enviar Recordatorio',
        text: `¿Enviar recordatorio de pago para la factura ${numeroFactura}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        showDenyButton: true,
        denyButtonText: 'Email + SMS'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Recordatorio enviado',
                text: `Email enviado exitosamente para ${numeroFactura}`
            });
        } else if (result.isDenied) {
            Swal.fire({
                icon: 'success',
                title: 'Notificaciones enviadas',
                text: `Email y SMS enviados para ${numeroFactura}`
            });
        }
    });
}

function enviarRecordatorios() {
    Swal.fire({
        title: 'Enviar Recordatorios',
        text: '¿Enviar recordatorios a todas las facturas vencidas?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Enviar a Vencidas',
        cancelButtonText: 'Cancelar',
        showDenyButton: true,
        denyButtonText: 'Enviar a Todas'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Recordatorios enviados',
                text: 'Recordatorios enviados a 8 facturas vencidas'
            });
        } else if (result.isDenied) {
            Swal.fire({
                icon: 'success',
                title: 'Recordatorios enviados',
                text: 'Recordatorios enviados a 25 facturas'
            });
        }
    });
}

function verHistorialPagos(numeroFactura) {
    window.open(`/ventas/cuentas-cobrar/historial/${numeroFactura}`, '_blank');
}

function reprogramarVencimiento(numeroFactura) {
    Swal.fire({
        title: 'Reprogramar Vencimiento',
        html: `
            <div class="text-left">
                <p>Reprogramar fecha de vencimiento para ${numeroFactura}:</p>
                <div class="mb-3">
                    <label class="form-label">Nueva fecha de vencimiento:</label>
                    <input type="date" class="form-control" id="nuevaFechaVencimiento">
                </div>
                <div class="mb-3">
                    <label class="form-label">Motivo:</label>
                    <select class="form-select" id="motivoReprogramacion">
                        <option value="solicitud_cliente">Solicitud del cliente</option>
                        <option value="dificultades_pago">Dificultades de pago</option>
                        <option value="negociacion">Negociación</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Comentarios:</label>
                    <textarea class="form-control" id="comentariosReprogramacion" rows="3" placeholder="Comentarios adicionales..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Reprogramar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const fecha = document.getElementById('nuevaFechaVencimiento').value;
            if (!fecha) {
                Swal.showValidationMessage('La fecha es requerida');
                return false;
            }
            return {
                fecha: fecha,
                motivo: document.getElementById('motivoReprogramacion').value,
                comentarios: document.getElementById('comentariosReprogramacion').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Vencimiento reprogramado',
                text: `Nueva fecha: ${result.value.fecha}`
            });
        }
    });
}

function generarNotaCredito(numeroFactura) {
    Swal.fire({
        title: 'Generar Nota de Crédito',
        text: `¿Crear una nota de crédito para la factura ${numeroFactura}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Generar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(`/ventas/notas-credito/crear/${numeroFactura}`, '_blank');
        }
    });
}

function enviarNotificacion(numeroFactura) {
    Swal.fire({
        title: 'Enviar Notificación de Atraso',
        text: `¿Enviar notificación de atraso para ${numeroFactura}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Notificación enviada',
                text: 'Se ha enviado notificación de atraso'
            });
        }
    });
}

function confirmarPago(numeroFactura) {
    Swal.fire({
        title: 'Confirmar Pago Recibido',
        text: `¿Confirmar que se recibió el pago de la factura ${numeroFactura}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Pago confirmado',
                text: `Factura ${numeroFactura} marcada como pagada`
            });
        }
    });
}

function exportarReporte() {
    const opciones = ['Excel', 'PDF', 'CSV', 'XML'];
    
    Swal.fire({
        title: 'Exportar Reporte',
        text: '¿En qué formato deseas exportar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Excel',
        cancelButtonText: 'Cancelar',
        showDenyButton: true,
        denyButtonText: 'PDF',
        denyButtonColor: '#dc3545'
    }).then((result) => {
        let formato = 'Excel';
        if (result.isDenied) formato = 'PDF';
        
        Swal.fire({
            icon: 'success',
            title: `Reporte ${formato} generado`,
            text: 'El archivo se ha descargado exitosamente'
        });
    });
}

function actualizarSeleccion() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked:not(#selectAll)');
    cuentasSeleccionadas = Array.from(checkboxes).map(cb => cb.value);
    
    const count = cuentasSeleccionadas.length;
    if (count > 0) {
        document.getElementById('seleccionadosCount').textContent = count;
        document.getElementById('accionesLote').style.display = 'block';
    } else {
        document.getElementById('accionesLote').style.display = 'none';
    }
}

function enviarRecordatoriosLote() {
    Swal.fire({
        title: 'Enviar Recordatorios Lote',
        text: `¿Enviar recordatorios a ${cuentasSeleccionadas.length} cuentas seleccionadas?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Recordatorios enviados',
                text: `Recordatorios enviados a ${cuentasSeleccionadas.length} cuentas`
            });
        }
    });
}

function registrarPagoLote() {
    if (cuentasSeleccionadas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay cuentas seleccionadas',
            text: 'Selecciona al menos una cuenta para registrar pago'
        });
        return;
    }
    
    // Abrir modal de registro de pagos en lote
    window.open('/ventas/cuentas-cobrar/pagos-lote', '_blank');
}

function exportarSeleccionadas() {
    if (cuentasSeleccionadas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay cuentas seleccionadas',
            text: 'Selecciona al menos una cuenta para exportar'
        });
        return;
    }
    
    Swal.fire({
        title: 'Exportar Cuentas Seleccionadas',
        text: `¿Exportar ${cuentasSeleccionadas.length} cuentas seleccionadas?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Exportación completada',
                text: `${cuentasSeleccionadas.length} cuentas exportadas exitosamente`
            });
        }
    });
}

function generarReporteLote() {
    if (cuentasSeleccionadas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay cuentas seleccionadas',
            text: 'Selecciona al menos una cuenta para generar reporte'
        });
        return;
    }
    
    window.open(`/ventas/cuentas-cobrar/reporte-lote?cuentas=${cuentasSeleccionadas.join(',')}`, '_blank');
}

function procesarMorosidadLote() {
    if (cuentasSeleccionadas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay cuentas seleccionadas',
            text: 'Selecciona al menos una cuenta para procesar morosidad'
        });
        return;
    }
    
    Swal.fire({
        title: 'Procesar Morosidad',
        text: `¿Procesar ${cuentasSeleccionadas.length} cuentas por morosidad?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Procesar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Morosidad procesada',
                text: `${cuentasSeleccionadas.length} cuentas procesadas por morosidad`
            });
        }
    });
}

function limpiarSeleccion() {
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    cuentasSeleccionadas = [];
    document.getElementById('accionesLote').style.display = 'none';
}

function limpiarSeleccionCheck() {
    // Event listener para limpiar selección (para compatibilidad)
    limpiarSeleccion();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]:not(#selectAll)');
        checkboxes.forEach(cb => cb.checked = this.checked);
        actualizarSeleccion();
    });
    
    // Individual checkboxes
    document.querySelectorAll('input[type="checkbox"]:not(#selectAll)').forEach(checkbox => {
        checkbox.addEventListener('change', actualizarSeleccion);
    });
});
</script>
@endsection

@section('styles')
<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}

.btn-group .btn {
    border-radius: 0.375rem;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.alert {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #0d6efd;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.table-danger {
    background-color: rgba(220, 53, 69, 0.1);
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.avatar {
    font-size: 14px;
}

.fs-3 {
    font-size: 1.75rem !important;
}
</style>
@endsection