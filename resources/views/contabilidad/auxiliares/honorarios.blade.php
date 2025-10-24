@extends('layouts.contador')

@section('title', 'Registro de Honorarios')

@section('additional_css')
<style>
    .honorary-card {
        border-left: 4px solid #6c757d;
        transition: all 0.3s ease;
    }
    .honorary-card:hover {
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.2);
        transform: translateY(-2px);
    }
    
    .honorary-summary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    
    .service-selector {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
    }
    
    .honorary-table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .honorary-table th {
        background: linear-gradient(135deg, #495057 0%, #343a40 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 15px 12px;
    }
    
    .honorary-table td {
        padding: 12px;
        vertical-align: middle;
        border-color: #f1f3f4;
    }
    
    .honorary-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .service-type {
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .type-medical {
        background: #d4edda;
        color: #155724;
    }
    
    .type-legal {
        background: #cce5ff;
        color: #004085;
    }
    
    .type-consulting {
        background: #f8d7da;
        color: #721c24;
    }
    
    .type-advisory {
        background: #fff3cd;
        color: #856404;
    }
    
    .type-accounting {
        background: #e2e3e5;
        color: #383d41;
    }
    
    .payment-status {
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .status-paid {
        background: #d4edda;
        color: #155724;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-processing {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .status-overdue {
        background: #f8d7da;
        color: #721c24;
    }
    
    .amount-cell {
        font-weight: 600;
        text-align: right;
    }
    
    .amount-gross {
        color: #495057;
    }
    
    .amount-tax {
        color: #dc3545;
    }
    
    .amount-net {
        color: #28a745;
    }
    
    .action-buttons .btn {
        padding: 4px 8px;
        margin: 0 2px;
        font-size: 0.8rem;
    }
    
    .chart-container {
        position: relative;
        height: 250px;
        margin-top: 15px;
    }
    
    .service-providers {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .provider-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
    }
    
    .provider-item:last-child {
        border-bottom: none;
    }
    
    .export-buttons .btn {
        margin: 2px;
        width: calc(50% - 4px);
    }
    
    .remuneration-panel {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .calculation-details {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
        border: 1px solid #dee2e6;
    }
    
    .calculation-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        border-bottom: 1px solid #dee2e6;
    }
    
    .calculation-row:last-child {
        border-bottom: none;
        font-weight: 600;
        background: #e9ecef;
        margin: 5px -15px -15px;
        padding: 10px 15px;
        border-radius: 0 0 8px 8px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-dark mb-1">Registro de Honorarios</h2>
            <p class="text-muted mb-0">Control de pagos por servicios profesionales</p>
        </div>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#newHonoraryModal">
                <i class="fas fa-plus mr-2"></i>Nuevo Honorario
            </button>
        </div>
    </div>

    <!-- Remuneration Panel -->
    <div class="remuneration-panel">
        <div class="row text-center">
            <div class="col-md-3">
                <h6>Honorarios Brutos del Mes</h6>
                <h4 class="mb-0">S/ 42,800.00</h4>
            </div>
            <div class="col-md-3">
                <h6>Retenciones Aplicadas</h6>
                <h4 class="mb-0">S/ 4,710.00</h4>
            </div>
            <div class="col-md-3">
                <h6>Honorarios Netos</h6>
                <h4 class="mb-0">S/ 38,090.00</h4>
            </div>
            <div class="col-md-3">
                <h6>Pendientes de Pago</h6>
                <h4 class="mb-0">S/ 15,650.00</h4>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card honorary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Total Servicios</h6>
                            <h4 class="text-secondary mb-0">28 servicios</h4>
                            <small class="text-info">Este mes</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-tie fa-2x text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card honorary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Proveedores Activos</h6>
                            <h4 class="text-primary mb-0">12 profesionales</h4>
                            <small class="text-success">Registrados</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card honorary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Pagos Realizados</h6>
                            <h4 class="text-success mb-0">S/ 27,150.00</h4>
                            <small class="text-success">63.4% del total</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card honorary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Retenciones 4ta Cat.</h6>
                            <h4 class="text-warning mb-0">S/ 2,988.00</h4>
                            <small class="text-warning">Por pagar a SUNAT</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-invoice-dollar fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Overview -->
    <div class="honorary-summary">
        <div class="row text-center">
            <div class="col-md-4">
                <h5>Servicios Médicos</h5>
                <h3 class="mb-0">S/ 18,500.00</h3>
                <small>43.2% del total</small>
            </div>
            <div class="col-md-4">
                <h5>Servicios Legales</h5>
                <h3 class="mb-0">S/ 12,300.00</h3>
                <small>28.7% del total</small>
            </div>
            <div class="col-md-4">
                <h5>Otros Servicios</h5>
                <h3 class="mb-0">S/ 12,000.00</h3>
                <small>28.1% del total</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Service Type Filter -->
        <div class="col-12">
            <div class="service-selector">
                <h6 class="mb-3"><i class="fas fa-filter mr-2"></i>Filtrar por Tipo de Servicio</h6>
                <div class="row">
                    <div class="col-md-2">
                        <button class="btn btn-outline-success btn-block active" onclick="filterServiceType('all')">
                            <i class="fas fa-list mr-1"></i>Todos
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-success btn-block" onclick="filterServiceType('medical')">
                            <i class="fas fa-user-md mr-1"></i>Médicos
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-primary btn-block" onclick="filterServiceType('legal')">
                            <i class="fas fa-gavel mr-1"></i>Legales
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-danger btn-block" onclick="filterServiceType('consulting')">
                            <i class="fas fa-lightbulb mr-1"></i>Consultoría
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-warning btn-block" onclick="filterServiceType('advisory')">
                            <i class="fas fa-handshake mr-1"></i>Asesoría
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary btn-block" onclick="filterServiceType('accounting')">
                            <i class="fas fa-calculator mr-1"></i>Contables
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Honoraries Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table mr-2"></i>Registro de Honorarios
                    </h5>
                    <div class="export-buttons">
                        <button class="btn btn-light btn-sm" onclick="exportHonoraries()">
                            <i class="fas fa-file-excel mr-1"></i>Excel
                        </button>
                        <button class="btn btn-light btn-sm" onclick="exportReport()">
                            <i class="fas fa-file-pdf mr-1"></i>Reporte
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover honorary-table mb-0" id="honorariesTable">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Servicio</th>
                                    <th>Monto Bruto</th>
                                    <th>Retención</th>
                                    <th>Monto Neto</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-service="medical">
                                    <td>15/01/2025</td>
                                    <td>
                                        <div>
                                            <strong>Dr. Carlos Mendoza</strong><br>
                                            <small class="text-muted">Médico Consultor</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="service-type type-medical">Consultoría Médica</span>
                                    </td>
                                    <td class="amount-cell amount-gross">S/ 2,500.00</td>
                                    <td class="amount-cell amount-tax">S/ 275.00</td>
                                    <td class="amount-cell amount-net">S/ 2,225.00</td>
                                    <td>
                                        <span class="payment-status status-paid">Pagado</span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewHonorary(1)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editHonorary(1)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="generateReceipt(1)" title="Recibo">
                                            <i class="fas fa-receipt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr data-service="legal">
                                    <td>14/01/2025</td>
                                    <td>
                                        <div>
                                            <strong>Abog. María Fernández</strong><br>
                                            <small class="text-muted">Consultora Legal</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="service-type type-legal">Asesoría Legal</span>
                                    </td>
                                    <td class="amount-cell amount-gross">S/ 1,800.00</td>
                                    <td class="amount-cell amount-tax">S/ 198.00</td>
                                    <td class="amount-cell amount-net">S/ 1,602.00</td>
                                    <td>
                                        <span class="payment-status status-processing">Procesando</span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewHonorary(2)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editHonorary(2)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="generateReceipt(2)" title="Recibo">
                                            <i class="fas fa-receipt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr data-service="consulting">
                                    <td>13/01/2025</td>
                                    <td>
                                        <div>
                                            <strong>Ing. Roberto Silva</strong><br>
                                            <small class="text-muted">Consultor IT</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="service-type type-consulting">Consultoría IT</span>
                                    </td>
                                    <td class="amount-cell amount-gross">S/ 3,200.00</td>
                                    <td class="amount-cell amount-tax">S/ 352.00</td>
                                    <td class="amount-cell amount-net">S/ 2,848.00</td>
                                    <td>
                                        <span class="payment-status status-pending">Pendiente</span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewHonorary(3)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editHonorary(3)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="makePayment(3)" title="Pagar">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr data-service="advisory">
                                    <td>12/01/2025</td>
                                    <td>
                                        <div>
                                            <strong>Econ. Ana López</strong><br>
                                            <small class="text-muted">Asesora Financiera</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="service-type type-advisory">Asesoría Financiera</span>
                                    </td>
                                    <td class="amount-cell amount-gross">S/ 2,000.00</td>
                                    <td class="amount-cell amount-tax">S/ 220.00</td>
                                    <td class="amount-cell amount-net">S/ 1,780.00</td>
                                    <td>
                                        <span class="payment-status status-paid">Pagado</span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewHonorary(4)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editHonorary(4)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="generateReceipt(4)" title="Recibo">
                                            <i class="fas fa-receipt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr data-service="accounting">
                                    <td>11/01/2025</td>
                                    <td>
                                        <div>
                                            <strong>C.P.C. Juan Pérez</strong><br>
                                            <small class="text-muted">Contador Público</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="service-type type-accounting">Servicios Contables</span>
                                    </td>
                                    <td class="amount-cell amount-gross">S/ 1,500.00</td>
                                    <td class="amount-cell amount-tax">S/ 165.00</td>
                                    <td class="amount-cell amount-net">S/ 1,335.00</td>
                                    <td>
                                        <span class="payment-status status-overdue">Vencido</span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewHonorary(5)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editHonorary(5)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="urgentPayment(5)" title="Pago Urgente">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr data-service="medical">
                                    <td>10/01/2025</td>
                                    <td>
                                        <div>
                                            <strong>Dr. Luis García</strong><br>
                                            <small class="text-muted">Especialista Farmacia</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="service-type type-medical">Consulta Especializada</span>
                                    </td>
                                    <td class="amount-cell amount-gross">S/ 1,200.00</td>
                                    <td class="amount-cell amount-tax">S/ 132.00</td>
                                    <td class="amount-cell amount-net">S/ 1,068.00</td>
                                    <td>
                                        <span class="payment-status status-paid">Pagado</span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewHonorary(6)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editHonorary(6)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="generateReceipt(6)" title="Recibo">
                                            <i class="fas fa-receipt"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar with charts and service providers -->
        <div class="col-md-4">
            <!-- Service Distribution Chart -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie mr-2"></i>Distribución por Servicio
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="serviceChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Service Providers -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-star mr-2"></i>Top Proveedores
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="service-providers">
                        <div class="provider-item">
                            <div>
                                <strong>Dr. Carlos Mendoza</strong><br>
                                <small class="text-muted">S/ 8,500.00 | 5 servicios</small>
                            </div>
                            <span class="badge badge-success">19.9%</span>
                        </div>
                        <div class="provider-item">
                            <div>
                                <strong>Ing. Roberto Silva</strong><br>
                                <small class="text-muted">S/ 7,200.00 | 3 servicios</small>
                            </div>
                            <span class="badge badge-info">16.8%</span>
                        </div>
                        <div class="provider-item">
                            <div>
                                <strong>Abog. María Fernández</strong><br>
                                <small class="text-muted">S/ 6,800.00 | 4 servicios</small>
                            </div>
                            <span class="badge badge-warning">15.9%</span>
                        </div>
                        <div class="provider-item">
                            <div>
                                <strong>Econ. Ana López</strong><br>
                                <small class="text-muted">S/ 6,000.00 | 4 servicios</small>
                            </div>
                            <span class="badge badge-secondary">14.0%</span>
                        </div>
                        <div class="provider-item">
                            <div>
                                <strong>Dr. Luis García</strong><br>
                                <small class="text-muted">S/ 5,400.00 | 6 servicios</small>
                            </div>
                            <span class="badge badge-primary">12.6%</span>
                        </div>
                        <div class="provider-item">
                            <div>
                                <strong>Otros</strong><br>
                                <small class="text-muted">S/ 8,900.00 | 6 servicios</small>
                            </div>
                            <span class="badge badge-light">20.8%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calculation Details -->
    <div class="calculation-details">
        <h6><i class="fas fa-calculator mr-2"></i>Detalles de Retenciones (4ta Categoría)</h6>
        <div class="row">
            <div class="col-md-6">
                <div class="calculation-row">
                    <span>Honorarios brutos del período:</span>
                    <strong>S/ 42,800.00</strong>
                </div>
                <div class="calculation-row">
                    <span>Retención 4ta categoría (8%):</span>
                    <strong>S/ 3,424.00</strong>
                </div>
                <div class="calculation-row">
                    <span>Retenciones adicionales:</span>
                    <strong>S/ 564.00</strong>
                </div>
            </div>
            <div class="col-md-6">
                <div class="calculation-row">
                    <span>Total retenciones:</span>
                    <strong>S/ 3,988.00</strong>
                </div>
                <div class="calculation-row">
                    <span>Honorarios netos a pagar:</span>
                    <strong>S/ 38,812.00</strong>
                </div>
                <div class="calculation-row">
                    <span>Retenciones por declarar:</span>
                    <strong>S/ 3,988.00</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Panel -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools mr-2"></i>Acciones de Gestión
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-credit-card mr-2"></i>Pagos y Facturación</h6>
                            <button class="btn btn-outline-success mr-2 mb-2" onclick="bulkPayment()">
                                <i class="fas fa-money-bill-wave mr-1"></i>Pago Masivo
                            </button>
                            <button class="btn btn-outline-info mr-2 mb-2" onclick="generatePaymentOrders()">
                                <i class="fas fa-file-invoice mr-1"></i>Órdenes de Pago
                            </button>
                            <button class="btn btn-outline-primary mb-2" onclick="generateReceipts()">
                                <i class="fas fa-receipt mr-1"></i>Generar Recibos
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-file-invoice-dollar mr-2"></i>Retenciones y SUNAT</h6>
                            <button class="btn btn-outline-warning mr-2 mb-2" onclick="sunatDeclaration()">
                                <i class="fas fa-cloud-upload-alt mr-1"></i>Declaración SUNAT
                            </button>
                            <button class="btn btn-outline-danger mr-2 mb-2" onclick="paymentRetention()">
                                <i class="fas fa-percentage mr-1"></i>Pago Retenciones
                            </button>
                            <button class="btn btn-outline-secondary mb-2" onclick="retentionReport()">
                                <i class="fas fa-chart-bar mr-1"></i>Reporte Retenciones
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Honorary Modal -->
<div class="modal fade" id="newHonoraryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Nuevo Honorario
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="newHonoraryForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Proveedor *</label>
                                <select class="form-control select2" name="provider_id" required>
                                    <option value="">Seleccionar proveedor</option>
                                    <option value="1">Dr. Carlos Mendoza</option>
                                    <option value="2">Abog. María Fernández</option>
                                    <option value="3">Ing. Roberto Silva</option>
                                    <option value="4">Econ. Ana López</option>
                                    <option value="5">C.P.C. Juan Pérez</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Servicio *</label>
                                <select class="form-control" name="service_type" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="medical">Médico</option>
                                    <option value="legal">Legal</option>
                                    <option value="consulting">Consultoría</option>
                                    <option value="advisory">Asesoría</option>
                                    <option value="accounting">Contable</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Descripción del Servicio *</label>
                                <textarea class="form-control" name="service_description" rows="2" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Monto Bruto *</label>
                                <input type="number" class="form-control" name="gross_amount" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha Servicio *</label>
                                <input type="date" class="form-control" name="service_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Retención (%)</label>
                                <input type="number" class="form-control" name="retention_rate" value="8" step="0.1" min="0" max="100">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha Vencimiento</label>
                                <input type="date" class="form-control" name="due_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Monto Neto</label>
                                <input type="number" class="form-control" name="net_amount" readonly>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveHonorary()">Guardar Honorario</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional_js')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#honorariesTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 15,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            columnDefs: [
                { orderable: false, targets: [7] }
            ]
        });

        // Initialize Select2
        $('.select2').select2({
            width: '100%',
            placeholder: 'Seleccionar...'
        });

        // Initialize Service Chart
        initServiceChart();

        // Calculate net amount automatically
        $('input[name="gross_amount"], input[name="retention_rate"]').on('input', function() {
            calculateNetAmount();
        });
    });

    function initServiceChart() {
        const ctx = document.getElementById('serviceChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Médicos', 'Legales', 'Consultoría', 'Asesoría', 'Contables'],
                datasets: [{
                    data: [18500, 12300, 6800, 3500, 1700],
                    backgroundColor: ['#28a745', '#007bff', '#dc3545', '#ffc107', '#6c757d'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: S/ ${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function calculateNetAmount() {
        const grossAmount = parseFloat($('input[name="gross_amount"]').val()) || 0;
        const retentionRate = parseFloat($('input[name="retention_rate"]').val()) || 0;
        const netAmount = grossAmount * (1 - retentionRate / 100);
        $('input[name="net_amount"]').val(netAmount.toFixed(2));
    }

    function filterServiceType(type) {
        // Update button states
        $('.service-selector .btn').removeClass('active');
        $(`.service-selector .btn:contains('${getButtonText(type)}')`).addClass('active');

        // Filter table rows
        if (type === 'all') {
            $('#honorariesTable tbody tr').show();
        } else {
            $('#honorariesTable tbody tr').hide();
            $(`#honorariesTable tbody tr[data-service="${type}"]`).show();
        }

        Swal.fire({
            title: 'Filtrando servicios...',
            text: `Mostrando servicios de tipo: ${getButtonText(type)}`,
            timer: 1000,
            showConfirmButton: false
        });
    }

    function getButtonText(type) {
        const texts = {
            'all': 'Todos',
            'medical': 'Médicos',
            'legal': 'Legales',
            'consulting': 'Consultoría',
            'advisory': 'Asesoría',
            'accounting': 'Contables'
        };
        return texts[type] || 'Todos';
    }

    // Honorary actions
    function viewHonorary(id) {
        Swal.fire({
            title: 'Visualizando honorario',
            text: `Cargando detalles del honorario #${id}`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            Swal.fire({
                icon: 'info',
                title: 'Vista de Honorario',
                text: 'Esta función abriría el detalle completo del honorario',
                timer: 2000,
                showConfirmButton: false
            });
        }, 1500);
    }

    function editHonorary(id) {
        Swal.fire({
            title: 'Editando honorario',
            text: `Cargando formulario de edición para honorario #${id}`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            $('#newHonoraryModal').modal('show');
            Swal.close();
        }, 1500);
    }

    function makePayment(id) {
        Swal.fire({
            title: 'Registrar Pago',
            html: `
                <div class="text-left">
                    <p><strong>Proveedor:</strong> Ing. Roberto Silva</p>
                    <p><strong>Servicio:</strong> Consultoría IT</p>
                    <p><strong>Monto neto:</strong> S/ 2,848.00</p>
                    <hr>
                    <label>Método de pago:</label>
                    <select id="paymentMethod" class="form-control">
                        <option value="">Seleccionar método</option>
                        <option value="bank">Transferencia bancaria</option>
                        <option value="check">Cheque</option>
                        <option value="cash">Efectivo</option>
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Registrar Pago',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const method = document.getElementById('paymentMethod').value;
                if (!method) {
                    Swal.showValidationMessage('Seleccione el método de pago');
                    return false;
                }
                return method;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando pago...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pago Registrado',
                        text: `Se ha registrado el pago por ${result.value}`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 2000);
            }
        });
    }

    function urgentPayment(id) {
        Swal.fire({
            title: 'Pago Urgente',
            text: 'Enviando notificación de pago urgente...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'warning',
                title: 'Notificación Enviada',
                text: 'Se ha enviado notificación de pago urgente al proveedor',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function generateReceipt(id) {
        Swal.fire({
            title: 'Generando Recibo',
            text: 'Creando recibo por honorarios...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Recibo Generado',
                text: 'El recibo por honorarios ha sido generado exitosamente',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    // Bulk actions
    function bulkPayment() {
        Swal.fire({
            title: 'Pago Masivo de Honorarios',
            html: `
                <div class="text-left">
                    <p>Esta función permite pagar múltiples honorarios de diferentes proveedores.</p>
                    <p><strong>Servicios seleccionados:</strong> 8</p>
                    <p><strong>Total a pagar:</strong> S/ 15,650.00</p>
                    <p><strong>Retenciones incluidas:</strong> S/ 1,722.00</p>
                    <hr>
                    <p>¿Desea proceder con el pago masivo?</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, procesar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando pago masivo...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pago Masivo Completado',
                        text: 'Se han procesado 8 pagos por un total de S/ 15,650.00',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }, 3000);
            }
        });
    }

    function generatePaymentOrders() {
        Swal.fire({
            title: 'Generando Órdenes de Pago',
            text: 'Creando órdenes de pago para honorarios pendientes...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Órdenes Generadas',
                text: 'Se han generado 5 órdenes de pago por un total de S/ 12,840.00',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2500);
    }

    function generateReceipts() {
        Swal.fire({
            title: 'Generando Recibos',
            text: 'Creando recibos por honorarios...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Recibos Generados',
                text: 'Se han generado 12 recibos por honorarios',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function sunatDeclaration() {
        Swal.fire({
            title: 'Declaración SUNAT',
            html: `
                <div class="text-left">
                    <p>Declaración de retenciones de 4ta categoría:</p>
                    <p><strong>Período:</strong> Enero 2025</p>
                    <p><strong>Total honorarios:</strong> S/ 42,800.00</p>
                    <p><strong>Retenciones:</strong> S/ 3,424.00</p>
                    <p><strong>Declaración:</strong> Formulario 1683</p>
                    <hr>
                    <p>¿Desea enviar la declaración a SUNAT?</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Enviar a SUNAT',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Enviando a SUNAT...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Declaración Enviada',
                        text: 'La declaración de retenciones ha sido enviada exitosamente a SUNAT',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }, 3000);
            }
        });
    }

    function paymentRetention() {
        Swal.fire({
            title: 'Pago de Retenciones',
            html: `
                <div class="text-left">
                    <p>Detalles del pago de retenciones:</p>
                    <p><strong>Concepto:</strong> Retenciones 4ta categoría</p>
                    <p><strong>Período:</strong> Enero 2025</p>
                    <p><strong>Monto a pagar:</strong> S/ 3,424.00</p>
                    <p><strong>CuentaSUNAT:</strong> 000-123456789-0-12</p>
                    <hr>
                    <label>Fecha de pago:</label>
                    <input type="date" id="retentionPaymentDate" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Confirmar Pago',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const date = document.getElementById('retentionPaymentDate').value;
                if (!date) {
                    Swal.showValidationMessage('Seleccione la fecha de pago');
                    return false;
                }
                return date;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando pago...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pago Procesado',
                        text: `El pago de retenciones por S/ 3,424.00 ha sido procesado para el ${result.value}`,
                        timer: 3000,
                        showConfirmButton: false
                    });
                }, 2500);
            }
        });
    }

    function retentionReport() {
        Swal.fire({
            title: 'Reporte de Retenciones',
            text: 'Generando reporte detallado de retenciones...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Reporte Generado',
                text: 'El reporte de retenciones ha sido creado exitosamente',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    // Export functions
    function exportHonoraries() {
        Swal.fire({
            title: 'Exportando honorarios',
            text: 'Generando archivo Excel con registro de honorarios...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Exportación Exitosa',
                text: 'El registro de honorarios ha sido exportado a Excel',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function exportReport() {
        Swal.fire({
            title: 'Generando Reporte',
            text: 'Creando reporte completo de honorarios...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Reporte Generado',
                text: 'El reporte de honorarios ha sido creado exitosamente',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    // Save new honorary
    function saveHonorary() {
        const form = document.getElementById('newHonoraryForm');
        if (form.checkValidity()) {
            Swal.fire({
                title: 'Guardando honorario...',
                text: 'Procesando información',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
            
            setTimeout(() => {
                $('#newHonoraryModal').modal('hide');
                form.reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Honorario Registrado',
                    text: 'El nuevo honorario ha sido guardado exitosamente',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 2000);
        } else {
            form.reportValidity();
        }
    }

    // Auto-refresh data every 5 minutes
    setInterval(() => {
        console.log('Refreshing honorary data...');
        // In real implementation, this would fetch fresh data from server
    }, 300000);
</script>
@endsection