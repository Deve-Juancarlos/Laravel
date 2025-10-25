@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('clientes.index') }}">Clientes</a></li>
                    <li class="breadcrumb-item active">Estado de Cuenta</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-invoice-dollar text-primary"></i> Estado de Cuenta
            </h1>
        </div>
        <div>
            <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Clientes
            </a>
            <button class="btn btn-outline-primary" onclick="imprimirEstadoCuenta()">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <button class="btn btn-outline-success" onclick="exportarPDF()">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </button>
        </div>
    </div>

    <!-- Información del Cliente -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Información del Cliente</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-building text-white"></i>
                            </div>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $cliente->nombre ?? 'Hospital Central S.A.' }}</h5>
                            <small class="text-muted">{{ $cliente->codigo ?? 'CLI-001' }} - {{ $cliente->tipo ?? 'Empresa' }}</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>RUC:</strong></div>
                        <div class="col-sm-8">{{ $cliente->ruc ?? '20123456789' }}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Dirección:</strong></div>
                        <div class="col-sm-8">{{ $cliente->direccion ?? 'Av. Principal 123, Lima' }}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Teléfono:</strong></div>
                        <div class="col-sm-8">{{ $cliente->telefono ?? '+51 999 888 777' }}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Email:</strong></div>
                        <div class="col-sm-8">{{ $cliente->email ?? 'contacto@hospitalcentral.com' }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-right">
                        <h6 class="text-muted mb-3">Resumen Financiero</h6>
                        <div class="row mb-2">
                            <div class="col-8 text-right"><strong>Saldo Actual:</strong></div>
                            <div class="col-4 text-right"><span class="text-danger font-weight-bold">S/ 45,678.90</span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-8 text-right"><strong>Total Facturado:</strong></div>
                            <div class="col-4 text-right">S/ 234,567.80</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-8 text-right"><strong>Total Pagado:</strong></div>
                            <div class="col-4 text-right text-success">S/ 188,888.90</div>
                        </div>
                        <div class="row">
                            <div class="col-8 text-right"><strong>Fecha Última Factura:</strong></div>
                            <div class="col-4 text-right">25/01/2024</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Consulta -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros de Consulta</h6>
        </div>
        <div class="card-body">
            <form id="filtrosForm" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha Desde</label>
                            <input type="date" class="form-control" name="fecha_desde" 
                                   value="{{ request('fecha_desde', date('Y-m-d', strtotime('-6 months'))) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha Hasta</label>
                            <input type="date" class="form-control" name="fecha_hasta" 
                                   value="{{ request('fecha_hasta', date('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipo de Documento</label>
                            <select class="form-control" name="tipo_documento">
                                <option value="">Todos</option>
                                <option value="factura" {{ request('tipo_documento') == 'factura' ? 'selected' : '' }}>Factura</option>
                                <option value="boleta" {{ request('tipo_documento') == 'boleta' ? 'selected' : '' }}>Boleta</option>
                                <option value="nota_credito" {{ request('tipo_documento') == 'nota_credito' ? 'selected' : '' }}>Nota de Crédito</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Estado</label>
                            <select class="form-control" name="estado">
                                <option value="">Todos</option>
                                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="pagado" {{ request('estado') == 'pagado' ? 'selected' : '' }}>Pagado</option>
                                <option value="vencido" {{ request('estado') == 'vencido' ? 'selected' : '' }}>Vencido</option>
                                <option value="anulado" {{ request('estado') == 'anulado' ? 'selected' : '' }}>Anulado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen de Saldos -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Saldo Vencido
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">S/ 23,456.78</div>
                            <div class="text-xs text-danger">
                                <i class="fas fa-exclamation-triangle"></i> Más de 30 días
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                Por Vencer
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">S/ 22,222.12</div>
                            <div class="text-xs text-warning">
                                <i class="fas fa-clock"></i> Próximos 30 días
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                Total Pagado
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">S/ 188,888.90</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-check"></i> Últimos 6 meses
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Facturado
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">S/ 234,567.80</div>
                            <div class="text-xs text-primary">
                                <i class="fas fa-chart-line"></i> Últimos 6 meses
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de Movimientos -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Detalle de Movimientos</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Opciones:</div>
                    <a class="dropdown-item" href="#" onclick="exportarExcel()">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                    <a class="dropdown-item" href="#" onclick="enviarPorEmail()">
                        <i class="fas fa-envelope"></i> Enviar por Email
                    </a>
                    <a class="dropdown-item" href="#" onclick="generarCronograma()">
                        <i class="fas fa-calendar"></i> Cronograma de Pagos
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="movimientosTable" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Documento</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Vencimiento</th>
                            <th>Debe</th>
                            <th>Haber</th>
                            <th>Saldo</th>
                            <th>Días</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>25/01/2024</td>
                            <td><strong>F001-0001234</strong></td>
                            <td>Medicamentos varios</td>
                            <td><span class="badge badge-info">Factura</span></td>
                            <td>24/02/2024</td>
                            <td class="text-right">S/ 5,678.90</td>
                            <td class="text-right">-</td>
                            <td class="text-right"><span class="text-danger font-weight-bold">S/ 5,678.90</span></td>
                            <td class="text-center">1</td>
                            <td><span class="badge badge-warning">Por Vencer</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verFactura(1234)" title="Ver Factura">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="registrarPago(1234)" title="Registrar Pago">
                                        <i class="fas fa-money-bill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>22/01/2024</td>
                            <td><strong>F001-0001233</strong></td>
                            <td>Dispositivos médicos</td>
                            <td><span class="badge badge-info">Factura</span></td>
                            <td>21/02/2024</td>
                            <td class="text-right">S/ 8,945.67</td>
                            <td class="text-right">-</td>
                            <td class="text-right"><span class="text-danger font-weight-bold">S/ 8,945.67</span></td>
                            <td class="text-center">-4</td>
                            <td><span class="badge badge-danger">Vencida</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verFactura(1233)" title="Ver Factura">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="registrarPago(1233)" title="Registrar Pago">
                                        <i class="fas fa-money-bill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>18/01/2024</td>
                            <td><strong>F001-0001232</strong></td>
                            <td>Laboratorio y reactivos</td>
                            <td><span class="badge badge-info">Factura</span></td>
                            <td>17/02/2024</td>
                            <td class="text-right">S/ 3,234.55</td>
                            <td class="text-right">-</td>
                            <td class="text-right"><span class="text-danger font-weight-bold">S/ 3,234.55</span></td>
                            <td class="text-center">-8</td>
                            <td><span class="badge badge-danger">Vencida</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verFactura(1232)" title="Ver Factura">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="registrarPago(1232)" title="Registrar Pago">
                                        <i class="fas fa-money-bill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>15/01/2024</td>
                            <td><strong>P001-0005678</strong></td>
                            <td>Pago a cuenta - Transferencia</td>
                            <td><span class="badge badge-success">Pago</span></td>
                            <td>-</td>
                            <td class="text-right">-</td>
                            <td class="text-right"><span class="text-success font-weight-bold">S/ 10,000.00</span></td>
                            <td class="text-right"><span class="text-warning font-weight-bold">S/ 27,303.32</span></td>
                            <td class="text-center">-</td>
                            <td><span class="badge badge-success">Aplicado</span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="verPago(5678)" title="Ver Pago">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>10/01/2024</td>
                            <td><strong>F001-0001231</strong></td>
                            <td>Medicamentos especializados</td>
                            <td><span class="badge badge-info">Factura</span></td>
                            <td>09/02/2024</td>
                            <td class="text-right">S/ 15,678.90</td>
                            <td class="text-right">-</td>
                            <td class="text-right"><span class="text-danger font-weight-bold">S/ 15,678.90</span></td>
                            <td class="text-center">-16</td>
                            <td><span class="badge badge-danger">Vencida</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verFactura(1231)" title="Ver Factura">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="registrarPago(1231)" title="Registrar Pago">
                                        <i class="fas fa-money-bill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>05/01/2024</td>
                            <td><strong>F001-0001230</strong></td>
                            <td>Equipos médicos</td>
                            <td><span class="badge badge-info">Factura</span></td>
                            <td>04/02/2024</td>
                            <td class="text-right">S/ 12,345.67</td>
                            <td class="text-right">-</td>
                            <td class="text-right"><span class="text-danger font-weight-bold">S/ 12,345.67</span></td>
                            <td class="text-center">-21</td>
                            <td><span class="badge badge-danger">Vencida</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verFactura(1230)" title="Ver Factura">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="registrarPago(1230)" title="Registrar Pago">
                                        <i class="fas fa-money-bill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>28/12/2023</td>
                            <td><strong>P001-0005677</strong></td>
                            <td>Pago total diciembre</td>
                            <td><span class="badge badge-success">Pago</span></td>
                            <td>-</td>
                            <td class="text-right">-</td>
                            <td class="text-right"><span class="text-success font-weight-bold">S/ 50,000.00</span></td>
                            <td class="text-right"><span class="text-primary font-weight-bold">S/ 45,678.90</span></td>
                            <td class="text-center">-</td>
                            <td><span class="badge badge-success">Aplicado</span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="verPago(5677)" title="Ver Pago">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th colspan="5">TOTALES</th>
                            <th class="text-right"><strong>S/ 45,903.69</strong></th>
                            <th class="text-right"><strong>S/ 60,000.00</strong></th>
                            <th class="text-right"><strong>S/ 45,678.90</strong></th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Información de Contacto para Pagos -->
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="text-primary"><i class="fas fa-info-circle"></i> Información para Pagos</h6>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Banco:</strong> Banco Continental<br>
                        <strong>Cuenta Corriente:</strong> 0011-0123-45-0000123456<br>
                        <strong>CCI:</strong> 011 123 0000123456 45
                    </div>
                    <div class="col-md-6">
                        <strong>Titular:</strong> SIFANO S.A.C.<br>
                        <strong>RUC:</strong> 20123456789<br>
                        <strong>Contacto:</strong> cuentas@sifano.com | +51 999 888 777
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cronograma de Pagos -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Cronograma de Pagos Sugerido</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Fecha Propuesta</th>
                            <th>Monto</th>
                            <th>Documentos Incluidos</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>05/02/2024</td>
                            <td>S/ 15,678.90</td>
                            <td>F001-0001231, F001-0001230</td>
                            <td><span class="badge badge-danger">Vencido</span></td>
                        </tr>
                        <tr>
                            <td>12/02/2024</td>
                            <td>S/ 3,234.55</td>
                            <td>F001-0001232</td>
                            <td><span class="badge badge-danger">Vencido</span></td>
                        </tr>
                        <tr>
                            <td>19/02/2024</td>
                            <td>S/ 8,945.67</td>
                            <td>F001-0001233</td>
                            <td><span class="badge badge-danger">Vencido</span></td>
                        </tr>
                        <tr>
                            <td>26/02/2024</td>
                            <td>S/ 5,678.90</td>
                            <td>F001-0001234</td>
                            <td><span class="badge badge-warning">Por Vencer</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function initializeDataTable() {
    $('#movimientosTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        columnDefs: [
            { targets: [5, 6, 7], className: 'text-right' },
            { targets: [8], className: 'text-center' }
        ]
    });
}

function limpiarFiltros() {
    document.getElementById('filtrosForm').reset();
    window.location.href = window.location.pathname;
}

function verFactura(numero) {
    window.open(`/facturas/${numero}`, '_blank');
}

function verPago(numero) {
    window.open(`/pagos/${numero}`, '_blank');
}

function registrarPago(numero) {
    Swal.fire({
        title: 'Registrar Pago',
        text: `¿Desea registrar un pago para la factura F001-${numero}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Simular registro de pago
            Swal.fire({
                title: 'Procesando...',
                text: 'Registrando pago',
                timer: 1500,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                Swal.fire({
                    title: '¡Pago Registrado!',
                    text: 'El pago ha sido registrado exitosamente.',
                    icon: 'success'
                }).then(() => {
                    window.location.reload();
                });
            });
        }
    });
}

function imprimirEstadoCuenta() {
    window.print();
}

function exportarPDF() {
    Swal.fire({
        title: 'Generando PDF...',
        text: 'Creando estado de cuenta en PDF',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Generado!', 'El estado de cuenta PDF ha sido generado.', 'success');
    });
}

function exportarExcel() {
    Swal.fire({
        title: 'Generando Excel...',
        text: 'Creando estado de cuenta en Excel',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Generado!', 'El archivo Excel ha sido generado.', 'success');
    });
}

function enviarPorEmail() {
    Swal.fire({
        title: 'Enviar por Email',
        text: '¿Desea enviar el estado de cuenta por email al cliente?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Enviando...',
                text: 'Procesando envío de email',
                timer: 1500,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                Swal.fire('¡Enviado!', 'El estado de cuenta ha sido enviado por email.', 'success');
            });
        }
    });
}

function generarCronograma() {
    Swal.fire({
        title: 'Cronograma de Pagos',
        text: '¿Desea generar un cronograma de pagos detallado?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, generar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Generando...',
                text: 'Creando cronograma de pagos',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                Swal.fire('¡Generado!', 'El cronograma de pagos ha sido generado.', 'success');
            });
        }
    });
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    initializeDataTable();
});
</script>
@endsection