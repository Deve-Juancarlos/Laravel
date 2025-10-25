@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users text-primary"></i> Gestión de Clientes
        </h1>
        <div>
            <a href="{{ route('clientes.buscar') }}" class="btn btn-outline-info">
                <i class="fas fa-search"></i> Búsqueda Avanzada
            </a>
            <a href="{{ route('clientes.crear') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros de Búsqueda</h6>
        </div>
        <div class="card-body">
            <form id="filtrosForm" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Buscar</label>
                            <input type="text" class="form-control" name="buscar" 
                                   value="{{ request('buscar') }}" 
                                   placeholder="Nombre, RUC, email, teléfono">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Tipo</label>
                            <select class="form-control" name="tipo">
                                <option value="">Todos</option>
                                <option value="persona">Persona Natural</option>
                                <option value="empresa">Persona Jurídica</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Categoría</label>
                            <select class="form-control" name="categoria">
                                <option value="">Todas</option>
                                <option value="a">Categoría A - VIP</option>
                                <option value="b">Categoría B</option>
                                <option value="c">Categoría C</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Estado</label>
                            <select class="form-group" name="estado">
                                <option value="">Todos</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="suspendido">Suspendido</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Región</label>
                            <select class="form-control" name="region">
                                <option value="">Todas</option>
                                <option value="norte">Norte</option>
                                <option value="sur">Sur</option>
                                <option value="este">Este</option>
                                <option value="oeste">Oeste</option>
                                <option value="centro">Centro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Clientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2,847</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +12.5% este mes
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Clientes Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2,634</div>
                            <div class="text-xs text-success">
                                92.5% del total
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Nuevos Este Mes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">127</div>
                            <div class="text-xs text-info">
                                Últimos 30 días
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Clientes VIP
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">89</div>
                            <div class="text-xs text-warning">
                                Categoría A
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-crown fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Clientes -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Clientes</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Opciones:</div>
                    <a class="dropdown-item" href="#" onclick="exportarExcel()">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportarPDF()">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                    <a class="dropdown-item" href="#" onclick="importarClientes()">
                        <i class="fas fa-upload"></i> Importar Clientes
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="generarReporte()">
                        <i class="fas fa-chart-bar"></i> Generar Reporte
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="clientesTable" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" onchange="toggleAll()">
                            </th>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>RUC/DNI</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Categoría</th>
                            <th>Región</th>
                            <th>Estado</th>
                            <th>Última Compra</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="checkbox" class="cliente-checkbox" value="1"></td>
                            <td><strong>CLI-001</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-building text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong>Hospital Central S.A.</strong><br>
                                        <small class="text-muted">Empresa de Salud</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-info">Empresa</span></td>
                            <td>20123456789</td>
                            <td>Dr. Carlos Mendoza</td>
                            <td>+51 999 888 777</td>
                            <td>compras@hospitalcentral.com</td>
                            <td><span class="badge badge-warning">VIP</span></td>
                            <td><span class="badge badge-primary">Norte</span></td>
                            <td><span class="badge badge-success">Activo</span></td>
                            <td>15/01/2024</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verCliente(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarCliente(1)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="estadoCuenta(1)">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="cliente-checkbox" value="2"></td>
                            <td><strong>CLI-002</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong>Farmacia Bienestar</strong><br>
                                        <small class="text-muted">Farmacia</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-info">Empresa</span></td>
                            <td>20765432109</td>
                            <td>María González</td>
                            <td>+51 988 777 666</td>
                            <td>ventas@farmaciacompra.com</td>
                            <td><span class="badge badge-success">B</span></td>
                            <td><span class="badge badge-success">Sur</span></td>
                            <td><span class="badge badge-success">Activo</span></td>
                            <td>18/01/2024</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verCliente(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarCliente(2)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="estadoCuenta(2)">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="cliente-checkbox" value="3"></td>
                            <td><strong>CLI-003</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-info">
                                            <i class="fas fa-hospital text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong>Clínica San José</strong><br>
                                        <small class="text-muted">Centro Médico</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-info">Empresa</span></td>
                            <td>20111223344</td>
                            <td>Dr. Luis Rodríguez</td>
                                    <td>+51 977 666 555</td>
                            <td>admin@clinicasanjose.com</td>
                            <td><span class="badge badge-warning">VIP</span></td>
                            <td><span class="badge badge-info">Este</span></td>
                            <td><span class="badge badge-success">Activo</span></td>
                            <td>22/01/2024</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verCliente(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarCliente(3)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="estadoCuenta(3)">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="cliente-checkbox" value="4"></td>
                            <td><strong>CLI-004</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-flask text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong>Laboratorio Médico Plus</strong><br>
                                        <small class="text-muted">Laboratorio Clínico</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-info">Empresa</span></td>
                            <td>20555667788</td>
                            <td>Dra. Ana Martínez</td>
                            <td>+51 966 555 444</td>
                            <td>info@laboratorioplus.com</td>
                            <td><span class="badge badge-success">B</span></td>
                            <td><span class="badge badge-warning">Oeste</span></td>
                            <td><span class="badge badge-success">Activo</span></td>
                            <td>25/01/2024</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verCliente(4)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarCliente(4)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="estadoCuenta(4)">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="cliente-checkbox" value="5"></td>
                            <td><strong>CLI-005</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-secondary">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong>Dr. Roberto Silva</strong><br>
                                        <small class="text-muted">Médico Independiente</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-primary">Persona</span></td>
                            <td>12345678</td>
                            <td>Dr. Roberto Silva</td>
                            <td>+51 955 444 333</td>
                            <td>roberto.silva@email.com</td>
                            <td><span class="badge badge-secondary">C</span></td>
                            <td><span class="badge badge-secondary">Centro</span></td>
                            <td><span class="badge badge-success">Activo</span></td>
                            <td>28/01/2024</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verCliente(5)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarCliente(5)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="estadoCuenta(5)">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="cliente-checkbox" value="6"></td>
                            <td><strong>CLI-006</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-danger">
                                            <i class="fas fa-building text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong>Farmacia Salud Total</strong><br>
                                        <small class="text-muted">Cadena de Farmacias</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-info">Empresa</span></td>
                            <td>20234567890</td>
                            <td>Patricia López</td>
                            <td>+51 944 333 222</td>
                            <td>compras@farmaciasalud.com</td>
                            <td><span class="badge badge-warning">VIP</span></td>
                            <td><span class="badge badge-primary">Norte</span></td>
                            <td><span class="badge badge-success">Activo</span></td>
                            <td>30/01/2024</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verCliente(6)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarCliente(6)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="estadoCuenta(6)">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Acciones en lote -->
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success" onclick="activarSeleccionados()" disabled>
                        <i class="fas fa-check"></i> Activar
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="suspenderSeleccionados()" disabled>
                        <i class="fas fa-pause"></i> Suspender
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="eliminarSeleccionados()" disabled>
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
                <div>
                    <small class="text-muted">
                        Mostrando <span id="mostrando">1-6</span> de <span id="total">2,847</span> clientes
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Importar Clientes -->
<div class="modal fade" id="importarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importar Clientes</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importarForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Archivo Excel (.xlsx, .xls)</label>
                        <input type="file" class="form-control" name="archivo" accept=".xlsx,.xls" required>
                        <small class="form-text text-muted">
                            El archivo debe contener las columnas: Código, Nombre, Tipo, RUC/DNI, Teléfono, Email
                        </small>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="sobrescribir" value="1">
                            Sobrescribir clientes existentes
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="procesarImportacion()">Importar</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeDataTable();
    updateAccionesLote();
});

function initializeDataTable() {
    $('#clientesTable').DataTable({
        order: [[1, 'asc']],
        pageLength: 25,
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        columnDefs: [
            { targets: [0, 12], orderable: false },
            { targets: [4], className: 'text-center' },
            { targets: [10], className: 'text-center' },
            { targets: [11], className: 'text-center' }
        ]
    });
}

function toggleAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.cliente-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateAccionesLote();
}

function updateAccionesLote() {
    const checkboxes = document.querySelectorAll('.cliente-checkbox:checked');
    const botones = document.querySelectorAll('.btn-outline-success, .btn-outline-warning, .btn-outline-danger');
    
    if (checkboxes.length > 0) {
        botones.forEach(boton => {
            boton.disabled = false;
        });
    } else {
        botones.forEach(boton => {
            boton.disabled = true;
        });
    }
}

function verCliente(id) {
    window.location.href = `/clientes/${id}`;
}

function editarCliente(id) {
    window.location.href = `/clientes/${id}/editar`;
}

function estadoCuenta(id) {
    window.location.href = `/clientes/${id}/estado-cuenta`;
}

function activarSeleccionados() {
    const seleccionados = document.querySelectorAll('.cliente-checkbox:checked');
    if (seleccionados.length > 0) {
        Swal.fire({
            title: '¿Activar clientes?',
            text: `¿Desea activar ${seleccionados.length} cliente(s)?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, activar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('¡Activados!', 'Los clientes han sido activados.', 'success');
                updateAccionesLote();
            }
        });
    }
}

function suspenderSeleccionados() {
    const seleccionados = document.querySelectorAll('.cliente-checkbox:checked');
    if (seleccionados.length > 0) {
        Swal.fire({
            title: '¿Suspender clientes?',
            text: `¿Desea suspender ${seleccionados.length} cliente(s)?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, suspender',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('¡Suspendidos!', 'Los clientes han sido suspendidos.', 'warning');
                updateAccionesLote();
            }
        });
    }
}

function eliminarSeleccionados() {
    const seleccionados = document.querySelectorAll('.cliente-checkbox:checked');
    if (seleccionados.length > 0) {
        Swal.fire({
            title: '¿Eliminar clientes?',
            text: `¿Desea eliminar permanentemente ${seleccionados.length} cliente(s)?`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('¡Eliminados!', 'Los clientes han sido eliminados.', 'success');
                updateAccionesLote();
            }
        });
    }
}

function exportarExcel() {
    Swal.fire({
        title: 'Exportando...',
        text: 'Generando archivo Excel con todos los clientes',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Exportado!', 'El archivo Excel ha sido generado.', 'success');
    });
}

function exportarPDF() {
    Swal.fire({
        title: 'Exportando...',
        text: 'Generando archivo PDF con la lista de clientes',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Exportado!', 'El archivo PDF ha sido generado.', 'success');
    });
}

function importarClientes() {
    $('#importarModal').modal('show');
}

function procesarImportacion() {
    const form = document.getElementById('importarForm');
    const formData = new FormData(form);
    
    Swal.fire({
        title: 'Importando...',
        text: 'Procesando archivo de clientes',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Importado!', 'Los clientes han sido importados exitosamente.', 'success');
        $('#importarModal').modal('hide');
        form.reset();
    });
}

function generarReporte() {
    Swal.fire({
        title: 'Generando Reporte',
        text: 'Creando reporte completo de clientes',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Completado!', 'El reporte ha sido generado.', 'success');
    });
}

// Event listeners
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('cliente-checkbox')) {
        updateAccionesLote();
    }
});
</script>
@endsection