@extends('layouts.app')

@section('title', 'Facturación')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-file-invoice text-primary"></i>
                        Facturación
                    </h1>
                    <p class="text-muted mb-0">Gestión de facturas y comprobantes de venta</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <a href="{{ route('ventas.facturacion.rapida') }}" class="btn btn-success">
                            <i class="fas fa-bolt"></i> Factura Rápida
                        </a>
                        <a href="{{ route('ventas.facturacion.crear') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nueva Factura
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-file-invoice text-primary fs-1"></i>
                    </div>
                    <h4 class="text-primary mb-1">156</h4>
                    <p class="text-muted mb-0 small">Facturas Hoy</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-dollar-sign text-success fs-1"></i>
                    </div>
                    <h4 class="text-success mb-1">S/ 45,680</h4>
                    <p class="text-muted mb-0 small">Monto Total</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-check-circle text-info fs-1"></i>
                    </div>
                    <h4 class="text-info mb-1">142</h4>
                    <p class="text-muted mb-0 small">Completadas</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-clock text-warning fs-1"></i>
                    </div>
                    <h4 class="text-warning mb-1">12</h4>
                    <p class="text-muted mb-0 small">Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-ban text-danger fs-1"></i>
                    </div>
                    <h4 class="text-danger mb-1">2</h4>
                    <p class="text-muted mb-0 small">Anuladas</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-chart-line text-secondary fs-1"></i>
                    </div>
                    <h4 class="text-secondary mb-1">S/ 292.82</h4>
                    <p class="text-muted mb-0 small">Ticket Promedio</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form class="row g-3" id="filtrosForm">
                        <div class="col-md-3">
                            <label class="form-label">Buscar</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="busqueda" placeholder="Número, cliente, DNI...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" id="fechaDesde" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control" id="fechaHasta" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="estado">
                                <option value="">Todos los estados</option>
                                <option value="completada">Completada</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="anulada">Anulada</option>
                                <option value="borrador">Borrador</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Vendedor</label>
                            <select class="form-select" id="vendedor">
                                <option value="">Todos</option>
                                <option value="1">Ana García</option>
                                <option value="2">Carlos López</option>
                                <option value="3">María Rodríguez</option>
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

    <!-- Tabla de Facturas -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary"></i>
                        Lista de Facturas
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarFacturas()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="actualizarLista()">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaFacturas">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>Factura</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Vendedor</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" value="F-001245">
                                    </td>
                                    <td>
                                        <strong class="text-primary">F-001245</strong>
                                        <br>
                                        <small class="text-muted">Ticket: 001245</small>
                                    </td>
                                    <td>
                                        <span class="d-block">25/10/2025</span>
                                        <small class="text-muted">10:30 AM</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-primary bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <strong>Juan Pérez</strong>
                                                <br>
                                                <small class="text-muted">DNI: 12345678</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>S/ 156.80</strong>
                                        <br>
                                        <small class="text-muted">Subtotal: S/ 132.88</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Completada</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/24" class="rounded-circle me-2" alt="Vendedor">
                                            <span>Ana García</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('ventas.facturacion.ver', 'F-001245') }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="imprimirFactura('F-001245')" title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="enviarEmail('F-001245')" title="Enviar Email">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="anularFactura('F-001245')"><i class="fas fa-ban me-2"></i>Anular</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="duplicarFactura('F-001245')"><i class="fas fa-copy me-2"></i>Duplicar</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="convertirABoleta('F-001245')"><i class="fas fa-exchange-alt me-2"></i>Convertir a Boleta</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" value="F-001244">
                                    </td>
                                    <td>
                                        <strong class="text-primary">F-001244</strong>
                                        <br>
                                        <small class="text-muted">Ticket: 001244</small>
                                    </td>
                                    <td>
                                        <span class="d-block">25/10/2025</span>
                                        <small class="text-muted">10:15 AM</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-info bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user text-info"></i>
                                            </div>
                                            <div>
                                                <strong>Luisa Martínez</strong>
                                                <br>
                                                <small class="text-muted">DNI: 87654321</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>S/ 89.50</strong>
                                        <br>
                                        <small class="text-muted">Subtotal: S/ 75.85</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Completada</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/24" class="rounded-circle me-2" alt="Vendedor">
                                            <span>Carlos López</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('ventas.facturacion.ver', 'F-001244') }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="imprimirFactura('F-001244')" title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="enviarEmail('F-001244')" title="Enviar Email">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="anularFactura('F-001244')"><i class="fas fa-ban me-2"></i>Anular</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="duplicarFactura('F-001244')"><i class="fas fa-copy me-2"></i>Duplicar</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="convertirABoleta('F-001244')"><i class="fas fa-exchange-alt me-2"></i>Convertir a Boleta</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" value="F-001243">
                                    </td>
                                    <td>
                                        <strong class="text-primary">F-001243</strong>
                                        <br>
                                        <small class="text-muted">Ticket: 001243</small>
                                    </td>
                                    <td>
                                        <span class="d-block">25/10/2025</span>
                                        <small class="text-muted">09:45 AM</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-warning bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user text-warning"></i>
                                            </div>
                                            <div>
                                                <strong>Pedro Sánchez</strong>
                                                <br>
                                                <small class="text-muted">DNI: 11223344</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>S/ 234.20</strong>
                                        <br>
                                        <small class="text-muted">Subtotal: S/ 198.47</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Pendiente</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/24" class="rounded-circle me-2" alt="Vendedor">
                                            <span>María Rodríguez</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('ventas.facturacion.ver', 'F-001243') }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="imprimirFactura('F-001243')" title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="enviarEmail('F-001243')" title="Enviar Email">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="completarFactura('F-001243')"><i class="fas fa-check me-2"></i>Completar</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="anularFactura('F-001243')"><i class="fas fa-ban me-2"></i>Anular</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="duplicarFactura('F-001243')"><i class="fas fa-copy me-2"></i>Duplicar</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="convertirABoleta('F-001243')"><i class="fas fa-exchange-alt me-2"></i>Convertir a Boleta</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <span class="text-muted">Mostrando 1 a 3 de 156 registros</span>
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
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-info-circle me-2"></i>
                    <span id="seleccionadosCount">0</span> facturas seleccionadas
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="imprimirSeleccionadas()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="enviarEmailSeleccionadas()">
                        <i class="fas fa-envelope"></i> Enviar Email
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarSeleccionadas()">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="anularSeleccionadas()">
                        <i class="fas fa-ban"></i> Anular
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
$(document).ready(function() {
    // Inicializar DataTable
    $('#tablaFacturas').DataTable({
        responsive: true,
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json'
        },
        columnDefs: [
            { orderable: false, targets: [0, 7] },
            { searchable: false, targets: [0, 5, 6, 7] }
        ]
    });

    // Event listeners para selección
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('input[type="checkbox"]', '#tablaFacturas').each(function() {
            $(this).prop('checked', isChecked);
        });
        actualizarAccionesLote();
    });

    $('input[type="checkbox"]', '#tablaFacturas').not('#selectAll').on('change', function() {
        actualizarAccionesLote();
    });
});

function aplicarFiltros() {
    const filtros = {
        busqueda: $('#busqueda').val(),
        fechaDesde: $('#fechaDesde').val(),
        fechaHasta: $('#fechaHasta').val(),
        estado: $('#estado').val(),
        vendedor: $('#vendedor').val()
    };

    console.log('Aplicando filtros:', filtros);
    
    // Simular carga
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

function imprimirFactura(numero) {
    Swal.fire({
        title: 'Imprimir Factura',
        text: `¿Imprimir factura ${numero}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Imprimir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Simular impresión
            window.open(`/facturacion/${numero}/imprimir`, '_blank');
        }
    });
}

function enviarEmail(numero) {
    Swal.fire({
        title: 'Enviar por Email',
        html: `
            <div class="text-left">
                <div class="mb-3">
                    <label class="form-label">Email del cliente:</label>
                    <input type="email" class="form-control" id="emailCliente" placeholder="cliente@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Mensaje (opcional):</label>
                    <textarea class="form-control" id="mensajeEmail" rows="3" placeholder="Mensaje personalizado..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const email = document.getElementById('emailCliente').value;
            if (!email) {
                Swal.showValidationMessage('El email es requerido');
                return false;
            }
            return { email: email, mensaje: document.getElementById('mensajeEmail').value };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Email enviado',
                text: `Factura ${numero} enviada exitosamente a ${result.value.email}`
            });
        }
    });
}

function completarFactura(numero) {
    Swal.fire({
        title: 'Completar Factura',
        text: `¿Marcar como completada la factura ${numero}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Completar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Factura completada',
                text: `La factura ${numero} ha sido marcada como completada`
            });
        }
    });
}

function anularFactura(numero) {
    Swal.fire({
        title: 'Anular Factura',
        text: `¿Estás seguro de anular la factura ${numero}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Factura anulada',
                text: `La factura ${numero} ha sido anulada exitosamente`
            });
        }
    });
}

function duplicarFactura(numero) {
    Swal.fire({
        title: 'Duplicar Factura',
        text: `¿Crear una copia de la factura ${numero}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Duplicar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Factura duplicada',
                text: `Se ha creado una copia de la factura ${numero}`
            });
        }
    });
}

function convertirABoleta(numero) {
    Swal.fire({
        title: 'Convertir a Boleta',
        text: `¿Convertir factura ${numero} a boleta de venta?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Convertir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Conversión exitosa',
                text: `La factura ${numero} ha sido convertida a boleta`
            });
        }
    });
}

function exportarFacturas() {
    const opciones = ['Excel', 'PDF', 'CSV'];
    
    Swal.fire({
        title: 'Exportar Facturas',
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

function actualizarAccionesLote() {
    const seleccionados = $('input[type="checkbox"]:checked', '#tablaFacturas').not('#selectAll');
    const count = seleccionados.length;
    
    if (count > 0) {
        $('#seleccionadosCount').text(count);
        $('#accionesLote').show();
    } else {
        $('#accionesLote').hide();
    }
}

function imprimirSeleccionadas() {
    const seleccionados = $('input[type="checkbox"]:checked', '#tablaFacturas').not('#selectAll');
    Swal.fire({
        title: 'Imprimir Facturas',
        text: `¿Imprimir ${seleccionados.length} facturas seleccionadas?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Imprimir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('/facturacion/imprimir-seleccionadas', '_blank');
        }
    });
}

function enviarEmailSeleccionadas() {
    const seleccionados = $('input[type="checkbox"]:checked', '#tablaFacturas').not('#selectAll');
    
    Swal.fire({
        title: 'Enviar por Email',
        html: `
            <div class="text-left">
                <p>Enviar ${seleccionados.length} facturas por email:</p>
                <div class="mb-3">
                    <label class="form-label">Email:</label>
                    <input type="email" class="form-control" id="emailSeleccionadas" placeholder="cliente@email.com">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Emails enviados',
                text: `${seleccionados.length} facturas enviadas exitosamente`
            });
        }
    });
}

function exportarSeleccionadas() {
    const seleccionados = $('input[type="checkbox"]:checked', '#tablaFacturas').not('#selectAll');
    
    Swal.fire({
        title: 'Exportar Seleccionadas',
        text: `¿Exportar ${seleccionados.length} facturas seleccionadas?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Exportación completada',
                text: `${seleccionados.length} facturas exportadas exitosamente`
            });
        }
    });
}

function anularSeleccionadas() {
    const seleccionados = $('input[type="checkbox"]:checked', '#tablaFacturas').not('#selectAll');
    
    Swal.fire({
        title: 'Anular Facturas',
        text: `¿Anular ${seleccionados.length} facturas seleccionadas?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular todas',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Facturas anuladas',
                text: `${seleccionados.length} facturas han sido anuladas exitosamente`
            });
            $('#accionesLote').hide();
            $('#selectAll').prop('checked', false);
        }
    });
}

function limpiarSeleccion() {
    $('input[type="checkbox"]', '#tablaFacturas').prop('checked', false);
    $('#accionesLote').hide();
}
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

.avatar {
    font-size: 14px;
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
</style>
@endsection