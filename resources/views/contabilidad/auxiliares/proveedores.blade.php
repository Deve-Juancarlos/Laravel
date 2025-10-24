@extends('layouts.contador')

@section('title', 'Auxiliar de Proveedores')

@section('additional_css')
<style>
    .supplier-card {
        border-left: 4px solid #e83e8c;
        transition: all 0.3s ease;
    }
    .supplier-card:hover {
        box-shadow: 0 4px 15px rgba(232, 62, 140, 0.2);
        transform: translateY(-2px);
    }
    
    .supplier-summary {
        background: linear-gradient(135deg, #e83e8c 0%, #d63384 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    
    .filter-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
    }
    
    .supplier-table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .supplier-table th {
        background: linear-gradient(135deg, #495057 0%, #343a40 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 15px 12px;
    }
    
    .supplier-table td {
        padding: 12px;
        vertical-align: middle;
        border-color: #f1f3f4;
    }
    
    .supplier-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .supplier-status {
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .status-current {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .status-overdue {
        background: #f8d7da;
        color: #721c24;
    }
    
    .status-upcoming {
        background: #fff3cd;
        color: #856404;
    }
    
    .amount-cell {
        font-weight: 600;
        text-align: right;
    }
    
    .amount-payable {
        color: #dc3545;
    }
    
    .amount-receivable {
        color: #28a745;
    }
    
    .aging-bar {
        height: 20px;
        border-radius: 10px;
        overflow: hidden;
        background: #e9ecef;
        position: relative;
    }
    
    .aging-segment {
        height: 100%;
        float: left;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
    }
    
    .aging-0-30 { background: #28a745; }
    .aging-31-60 { background: #ffc107; color: #000; }
    .aging-61-90 { background: #fd7e14; }
    .aging-90 { background: #dc3545; }
    
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
    
    .top-suppliers {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .supplier-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
    }
    
    .supplier-item:last-child {
        border-bottom: none;
    }
    
    .export-buttons .btn {
        margin: 2px;
        width: calc(50% - 4px);
    }
    
    .payment-reminder {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        border: none;
        color: #000;
    }
    
    .supplier-header {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-dark mb-1">Auxiliar de Proveedores</h2>
            <p class="text-muted mb-0">Control de cuentas por pagar y gestión de proveedores</p>
        </div>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#newSupplierModal">
                <i class="fas fa-plus mr-2"></i>Nuevo Proveedor
            </button>
        </div>
    </div>

    <!-- Payment Reminder -->
    <div class="payment-reminder">
        <div class="d-flex align-items-center">
            <i class="fas fa-bell fa-2x mr-3"></i>
            <div>
                <h6 class="mb-1">Recordatorio de Pagos</h6>
                <p class="mb-0">Tiene 5 pagos pendientes por un total de S/ 45,800.00. Los 3 más urgentes vencen en los próximos 7 días.</p>
            </div>
            <button class="btn btn-outline-dark ml-auto" onclick="viewPaymentSchedule()">
                <i class="fas fa-calendar mr-1"></i>Ver Agenda
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card supplier-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Total Proveedores</h6>
                            <h4 class="text-info mb-0">45 proveedores</h4>
                            <small class="text-success">+2 nuevos este mes</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card supplier-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Cuentas por Pagar</h6>
                            <h4 class="text-danger mb-0">S/ 285,600.00</h4>
                            <small class="text-danger">Total pendiente</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card supplier-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Vencidos</h6>
                            <h4 class="text-warning mb-0">S/ 12,450.00</h4>
                            <small class="text-warning">Requieren atención</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card supplier-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Pagos del Mes</h6>
                            <h4 class="text-success mb-0">S/ 125,890.00</h4>
                            <small class="text-success">Realizados</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Overview -->
    <div class="supplier-summary">
        <div class="row text-center">
            <div class="col-md-4">
                <h5>Vencimientos 0-30 días</h5>
                <h3 class="mb-0">S/ 198,450.00</h5>
                <small>69.5% del total</small>
            </div>
            <div class="col-md-4">
                <h5>Vencimientos 31-60 días</h5>
                <h3 class="mb-0">S/ 74,700.00</h3>
                <small>26.1% del total</small>
            </div>
            <div class="col-md-4">
                <h5>Vencimientos +60 días</h5>
                <h3 class="mb-0">S/ 12,450.00</h3>
                <small>4.4% del total</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Filters -->
        <div class="col-12">
            <div class="filter-section">
                <h6 class="mb-3"><i class="fas fa-filter mr-2"></i>Filtros de Búsqueda</h6>
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Buscar Proveedor</label>
                        <input type="text" class="form-control" id="supplierSearch" placeholder="Nombre o RUC">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select class="form-control" id="statusFilter">
                            <option value="">Todos</option>
                            <option value="current">Al día</option>
                            <option value="overdue">Vencidos</option>
                            <option value="upcoming">Próximos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Vencimiento</label>
                        <select class="form-control" id="agingFilter">
                            <option value="">Todos</option>
                            <option value="0-30">0-30 días</option>
                            <option value="31-60">31-60 días</option>
                            <option value="61-90">61-90 días</option>
                            <option value="90+">+90 días</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Monto Mínimo</label>
                        <input type="number" class="form-control" id="minAmount" placeholder="S/ 0.00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button class="btn btn-outline-primary btn-sm mr-2" onclick="applyFilters()">
                                <i class="fas fa-search mr-1"></i>Buscar
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                                <i class="fas fa-undo mr-1"></i>Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Suppliers Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table mr-2"></i>Lista de Proveedores
                    </h5>
                    <div class="export-buttons">
                        <button class="btn btn-light btn-sm" onclick="exportSuppliers()">
                            <i class="fas fa-file-excel mr-1"></i>Excel
                        </button>
                        <button class="btn btn-light btn-sm" onclick="exportReport()">
                            <i class="fas fa-file-pdf mr-1"></i>Reporte
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover supplier-table mb-0" id="suppliersTable">
                            <thead>
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Contacto</th>
                                    <th>Saldo Actual</th>
                                    <th>Próximo Vencimiento</th>
                                    <th>Estado</th>
                                    <th>Aging</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>Farmacia Central S.A.</strong><br>
                                            <small class="text-muted">RUC: 20123456789</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>María González</strong><br>
                                            <small>mgonzalez@farmaciacentral.com</small><br>
                                            <small>+51 987 654 321</small>
                                        </div>
                                    </td>
                                    <td class="amount-cell amount-payable">S/ 85,400.00</td>
                                    <td>25/01/2025</td>
                                    <td>
                                        <span class="supplier-status status-current">Al día</span>
                                    </td>
                                    <td>
                                        <div class="aging-bar">
                                            <div class="aging-segment aging-0-30" style="width: 80%;">S/ 68,320</div>
                                            <div class="aging-segment aging-31-60" style="width: 20%;">S/ 17,080</div>
                                        </div>
                                        <small class="text-muted">0-30: 80% | 31-60: 20%</small>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewSupplier(1)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editSupplier(1)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="makePayment(1)" title="Pagar">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" onclick="sendReminder(1)" title="Recordatorio">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>Distribuidora Médica SAC</strong><br>
                                            <small class="text-muted">RUC: 20876543210</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>Carlos Mendoza</strong><br>
                                            <small>cmendoza@distmedica.com</small><br>
                                            <small>+51 976 543 210</small>
                                        </div>
                                    </td>
                                    <td class="amount-cell amount-payable">S/ 72,300.00</td>
                                    <td>22/01/2025</td>
                                    <td>
                                        <span class="supplier-status status-upcoming">Próximo</span>
                                    </td>
                                    <td>
                                        <div class="aging-bar">
                                            <div class="aging-segment aging-0-30" style="width: 100%;">S/ 72,300</div>
                                        </div>
                                        <small class="text-muted">0-30: 100%</small>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewSupplier(2)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editSupplier(2)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="makePayment(2)" title="Pagar">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" onclick="sendReminder(2)" title="Recordatorio">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>Laboratorios farmacéuticos SA</strong><br>
                                            <small class="text-muted">RUC: 20111222333</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>Ana Rodríguez</strong><br>
                                            <small>arodriguez@labfarm.com</small><br>
                                            <small>+51 965 432 109</small>
                                        </div>
                                    </td>
                                    <td class="amount-cell amount-payable">S/ 68,900.00</td>
                                    <td>18/01/2025</td>
                                    <td>
                                        <span class="supplier-status status-overdue">Vencido</span>
                                    </td>
                                    <td>
                                        <div class="aging-bar">
                                            <div class="aging-segment aging-61-90" style="width: 60%;">S/ 41,340</div>
                                            <div class="aging-segment aging-90" style="width: 40%;">S/ 27,560</div>
                                        </div>
                                        <small class="text-muted">61-90: 60% | +90: 40%</small>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewSupplier(3)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editSupplier(3)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="makePayment(3)" title="Pagar">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="urgentReminder(3)" title="Recordatorio Urgente">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>Equipo Médico S.A.C.</strong><br>
                                            <small class="text-muted">RUC: 20444555666</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>Roberto Silva</strong><br>
                                            <small>rsilva@equipomedico.com</small><br>
                                            <small>+51 954 321 098</small>
                                        </div>
                                    </td>
                                    <td class="amount-cell amount-payable">S/ 45,600.00</td>
                                    <td>30/01/2025</td>
                                    <td>
                                        <span class="supplier-status status-current">Al día</span>
                                    </td>
                                    <td>
                                        <div class="aging-bar">
                                            <div class="aging-segment aging-0-30" style="width: 100%;">S/ 45,600</div>
                                        </div>
                                        <small class="text-muted">0-30: 100%</small>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewSupplier(4)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editSupplier(4)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="makePayment(4)" title="Pagar">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" onclick="sendReminder(4)" title="Recordatorio">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>Insumos Químicos SAC</strong><br>
                                            <small class="text-muted">RUC: 20777888999</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>Patricia Vega</strong><br>
                                            <small>pvega@insumosq.com</small><br>
                                            <small>+51 943 210 987</small>
                                        </div>
                                    </td>
                                    <td class="amount-cell amount-payable">S/ 13,400.00</td>
                                    <td>28/01/2025</td>
                                    <td>
                                        <span class="supplier-status status-current">Al día</span>
                                    </td>
                                    <td>
                                        <div class="aging-bar">
                                            <div class="aging-segment aging-0-30" style="width: 100%;">S/ 13,400</div>
                                        </div>
                                        <small class="text-muted">0-30: 100%</small>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewSupplier(5)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editSupplier(5)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="makePayment(5)" title="Pagar">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" onclick="sendReminder(5)" title="Recordatorio">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar with charts and top suppliers -->
        <div class="col-md-4">
            <!-- Aging Chart -->
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie mr-2"></i>Análisis por Vencimiento
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="agingChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Suppliers by Amount -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-trophy mr-2"></i>Top Proveedores
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="top-suppliers">
                        <div class="supplier-item">
                            <div>
                                <strong>Farmacia Central S.A.</strong><br>
                                <small class="text-muted">S/ 85,400.00</small>
                            </div>
                            <span class="badge badge-danger">29.9%</span>
                        </div>
                        <div class="supplier-item">
                            <div>
                                <strong>Distribuidora Médica</strong><br>
                                <small class="text-muted">S/ 72,300.00</small>
                            </div>
                            <span class="badge badge-warning">25.3%</span>
                        </div>
                        <div class="supplier-item">
                            <div>
                                <strong>Lab. Farmacéuticos SA</strong><br>
                                <small class="text-muted">S/ 68,900.00</small>
                            </div>
                            <span class="badge badge-info">24.1%</span>
                        </div>
                        <div class="supplier-item">
                            <div>
                                <strong>Equipo Médico S.A.C.</strong><br>
                                <small class="text-muted">S/ 45,600.00</small>
                            </div>
                            <span class="badge badge-secondary">16.0%</span>
                        </div>
                        <div class="supplier-item">
                            <div>
                                <strong>Insumos Químicos</strong><br>
                                <small class="text-muted">S/ 13,400.00</small>
                            </div>
                            <span class="badge badge-light">4.7%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Panel -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
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
                            <button class="btn btn-outline-info mr-2 mb-2" onclick="paymentSchedule()">
                                <i class="fas fa-calendar-alt mr-1"></i>Agenda de Pagos
                            </button>
                            <button class="btn btn-outline-primary mb-2" onclick="generatePaymentOrders()">
                                <i class="fas fa-file-invoice mr-1"></i>Órdenes de Pago
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-envelope mr-2"></i>Comunicación</h6>
                            <button class="btn btn-outline-warning mr-2 mb-2" onclick="sendBulkReminders()">
                                <i class="fas fa-paper-plane mr-1"></i>Recordatorios
                            </button>
                            <button class="btn btn-outline-danger mr-2 mb-2" onclick="urgentFollowUp()">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Seguimiento Urgente
                            </button>
                            <button class="btn btn-outline-secondary mb-2" onclick="supplierStatement()">
                                <i class="fas fa-file-alt mr-1"></i>Estados de Cuenta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Supplier Modal -->
<div class="modal fade" id="newSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Nuevo Proveedor
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="newSupplierForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Razón Social *</label>
                                <input type="text" class="form-control" name="business_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>RUC *</label>
                                <input type="text" class="form-control" name="ruc" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contacto Principal *</label>
                                <input type="text" class="form-control" name="contact_person" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" class="form-control" name="phone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Plazo de Pago (días)</label>
                                <input type="number" class="form-control" name="payment_terms" value="30" min="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Dirección</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveSupplier()">Guardar Proveedor</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional_js')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#suppliersTable').DataTable({
            order: [[2, 'desc']],
            pageLength: 25,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            columnDefs: [
                { orderable: false, targets: [6] }
            ]
        });

        // Initialize Aging Chart
        initAgingChart();
    });

    function initAgingChart() {
        const ctx = document.getElementById('agingChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['0-30 días', '31-60 días', '61-90 días', '+90 días'],
                datasets: [{
                    data: [198450, 74700, 8220, 4230],
                    backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545'],
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

    // Filter functions
    function applyFilters() {
        const search = $('#supplierSearch').val();
        const status = $('#statusFilter').val();
        const aging = $('#agingFilter').val();
        const minAmount = $('#minAmount').val();

        Swal.fire({
            title: 'Aplicando filtros...',
            text: 'Buscando proveedores con los criterios seleccionados',
            timer: 1000,
            showConfirmButton: false
        });
    }

    function clearFilters() {
        $('#supplierSearch, #statusFilter, #agingFilter, #minAmount').val('');
        $('#supplierSearch').focus();
    }

    // Supplier actions
    function viewSupplier(id) {
        Swal.fire({
            title: 'Visualizando proveedor',
            text: `Cargando detalles del proveedor #${id}`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            Swal.fire({
                icon: 'info',
                title: 'Vista de Proveedor',
                text: 'Esta función abriría el detalle completo del proveedor',
                timer: 2000,
                showConfirmButton: false
            });
        }, 1500);
    }

    function editSupplier(id) {
        Swal.fire({
            title: 'Editando proveedor',
            text: `Cargando formulario de edición para proveedor #${id}`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            $('#newSupplierModal').modal('show');
            Swal.close();
        }, 1500);
    }

    function makePayment(id) {
        Swal.fire({
            title: 'Registrar Pago',
            html: `
                <div class="text-left">
                    <p><strong>Proveedor:</strong> Farmacia Central S.A.</p>
                    <p><strong>Saldo actual:</strong> S/ 85,400.00</p>
                    <hr>
                    <label>Monto a pagar:</label>
                    <input type="number" id="paymentAmount" class="form-control" placeholder="S/ 0.00" step="0.01">
                    <br>
                    <label>Método de pago:</label>
                    <select id="paymentMethod" class="form-control">
                        <option value="">Seleccionar método</option>
                        <option value="cash">Efectivo</option>
                        <option value="bank">Transferencia bancaria</option>
                        <option value="check">Cheque</option>
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Registrar Pago',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const amount = document.getElementById('paymentAmount').value;
                const method = document.getElementById('paymentMethod').value;
                if (!amount || amount <= 0 || !method) {
                    Swal.showValidationMessage('Complete todos los campos');
                    return false;
                }
                return { amount, method };
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
                        text: `Se ha registrado un pago de S/ ${result.value.amount} por ${result.value.method}`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 2000);
            }
        });
    }

    function sendReminder(id) {
        Swal.fire({
            title: 'Enviando recordatorio',
            text: 'Enviando recordatorio de pago al proveedor...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Recordatorio Enviado',
                text: 'El recordatorio ha sido enviado exitosamente',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function urgentReminder(id) {
        Swal.fire({
            title: 'Recordatorio Urgente',
            text: 'Enviando recordatorio urgente por pago vencido...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'warning',
                title: 'Recordatorio Urgente Enviado',
                text: 'Se ha enviado un recordatorio urgente al proveedor',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    // Bulk actions
    function bulkPayment() {
        Swal.fire({
            title: 'Pago Masivo',
            html: `
                <div class="text-left">
                    <p>Esta función permite pagar múltiples facturas de diferentes proveedores.</p>
                    <p><strong>Proveedores seleccionados:</strong> 5</p>
                    <p><strong>Total a pagar:</strong> S/ 285,600.00</p>
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
                        text: 'Se han procesado 5 pagos por un total de S/ 285,600.00',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }, 3000);
            }
        });
    }

    function paymentSchedule() {
        Swal.fire({
            title: 'Agenda de Pagos',
            text: 'Generando calendario de vencimientos...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'info',
                title: 'Agenda Generada',
                text: 'Se ha generado la agenda de pagos para los próximos 30 días',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function generatePaymentOrders() {
        Swal.fire({
            title: 'Generando Órdenes de Pago',
            text: 'Creando órdenes de pago para facturas pendientes...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Órdenes Generadas',
                text: 'Se han generado 8 órdenes de pago por un total de S/ 156,800.00',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2500);
    }

    function sendBulkReminders() {
        Swal.fire({
            title: 'Recordatorios Masivos',
            html: `
                <div class="text-left">
                    <p>Esta acción enviará recordatorios a todos los proveedores con pagos pendientes.</p>
                    <p><strong>Proveedores a notificar:</strong> 12</p>
                    <p><strong>Total pendiente:</strong> S/ 145,600.00</p>
                    <hr>
                    <label>Tipo de recordatorio:</label>
                    <select id="reminderType" class="form-control">
                        <option value="gentle">Recordatorio amigable</option>
                        <option value="formal">Recordatorio formal</option>
                        <option value="urgent">Recordatorio urgente</option>
                    </select>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Enviar Recordatorios',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const type = document.getElementById('reminderType').value;
                if (!type) {
                    Swal.showValidationMessage('Seleccione el tipo de recordatorio');
                    return false;
                }
                return type;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Enviando recordatorios...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Recordatorios Enviados',
                        text: `Se han enviado ${12} recordatorios de tipo ${result.value}`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 2500);
            }
        });
    }

    function urgentFollowUp() {
        Swal.fire({
            title: 'Seguimiento Urgente',
            text: 'Iniciando seguimiento para cuentas vencidas...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'warning',
                title: 'Seguimiento Iniciado',
                text: 'Se ha activado el seguimiento urgente para 3 proveedores vencidos',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function supplierStatement() {
        Swal.fire({
            title: 'Estados de Cuenta',
            html: `
                <div class="text-left">
                    <p>Seleccione el proveedor para generar su estado de cuenta:</p>
                    <select id="statementSupplier" class="form-control">
                        <option value="">Seleccionar proveedor</option>
                        <option value="1">Farmacia Central S.A.</option>
                        <option value="2">Distribuidora Médica SAC</option>
                        <option value="3">Laboratorios farmacéuticos SA</option>
                        <option value="4">Equipo Médico S.A.C.</option>
                        <option value="5">Insumos Químicos SAC</option>
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Generar Estado',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const supplierId = document.getElementById('statementSupplier').value;
                if (!supplierId) {
                    Swal.showValidationMessage('Seleccione un proveedor');
                    return false;
                }
                return supplierId;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Generando estado de cuenta...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Estado Generado',
                        text: 'El estado de cuenta ha sido generado y enviado por email',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 2000);
            }
        });
    }

    function viewPaymentSchedule() {
        Swal.fire({
            title: 'Agenda de Pagos',
            html: `
                <div class="text-left">
                    <h6>Próximos vencimientos:</h6>
                    <p><strong>22/01/2025:</strong> Distribuidora Médica - S/ 72,300.00</p>
                    <p><strong>25/01/2025:</strong> Farmacia Central - S/ 85,400.00</p>
                    <p><strong>28/01/2025:</strong> Insumos Químicos - S/ 13,400.00</p>
                    <p><strong>30/01/2025:</strong> Equipo Médico - S/ 45,600.00</p>
                    <hr>
                    <h6>Total próximos 7 días: S/ 216,700.00</h6>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Cerrar'
        });
    }

    // Export functions
    function exportSuppliers() {
        Swal.fire({
            title: 'Exportando proveedores',
            text: 'Generando archivo Excel con lista de proveedores...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Exportación Exitosa',
                text: 'La lista de proveedores ha sido exportada a Excel',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function exportReport() {
        Swal.fire({
            title: 'Generando Reporte',
            text: 'Creando reporte completo de proveedores...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Reporte Generado',
                text: 'El reporte de proveedores ha sido creado exitosamente',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    // Save new supplier
    function saveSupplier() {
        const form = document.getElementById('newSupplierForm');
        if (form.checkValidity()) {
            Swal.fire({
                title: 'Guardando proveedor...',
                text: 'Procesando información',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
            
            setTimeout(() => {
                $('#newSupplierModal').modal('hide');
                form.reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Proveedor Registrado',
                    text: 'El nuevo proveedor ha sido guardado exitosamente',
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
        console.log('Refreshing supplier data...');
        // In real implementation, this would fetch fresh data from server
    }, 300000);
</script>
@endsection action-buttons