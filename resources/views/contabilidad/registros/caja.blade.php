@extends('layouts.contador')

@section('title', 'Registro de Caja')

@section('additional_css')
<style>
    .cash-card {
        border-left: 4px solid #fd7e14;
        transition: all 0.3s ease;
    }
    .cash-card:hover {
        box-shadow: 0 4px 15px rgba(253, 126, 20, 0.2);
        transform: translateY(-2px);
    }
    
    .cash-summary {
        background: linear-gradient(135deg, #fd7e14 0%, #e95a0b 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    
    .denomination-section {
        background: #fff;
        border: 2px solid #fd7e14;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .denomination-title {
        color: #fd7e14;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }
    
    .denomination-title i {
        margin-right: 8px;
    }
    
    .denomination-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 10px;
    }
    
    .denomination-item {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 8px;
        border: 1px solid #dee2e6;
    }
    
    .denomination-value {
        font-weight: 600;
        color: #495057;
        margin-right: 8px;
        min-width: 50px;
    }
    
    .denomination-count {
        flex: 1;
        text-align: center;
        font-weight: 600;
        color: #007bff;
    }
    
    .total-denomination {
        font-weight: 700;
        color: #28a745;
        text-align: right;
    }
    
    .cash-movements-table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .cash-movements-table th {
        background: linear-gradient(135deg, #495057 0%, #343a40 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 15px 12px;
    }
    
    .cash-movements-table td {
        padding: 12px;
        vertical-align: middle;
        border-color: #f1f3f4;
    }
    
    .cash-movements-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .movement-type {
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .type-income {
        background: #d4edda;
        color: #155724;
    }
    
    .type-expense {
        background: #f8d7da;
        color: #721c24;
    }
    
    .type-transfer {
        background: #cce5ff;
        color: #004085;
    }
    
    .amount-positive {
        color: #28a745;
        font-weight: 600;
    }
    
    .amount-negative {
        color: #dc3545;
        font-weight: 600;
    }
    
    .balance-alert {
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .balance-ok {
        background: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
    }
    
    .balance-difference {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
    }
    
    .chart-container {
        position: relative;
        height: 250px;
        margin-top: 15px;
    }
    
    .shift-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .quick-actions {
        background: #e9ecef;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .quick-actions .btn {
        margin: 2px;
        width: calc(33.33% - 4px);
    }
    
    .denomination-summary {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
        border: 1px solid #dee2e6;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-dark mb-1">Registro de Caja</h2>
            <p class="text-muted mb-0">Control de efectivo y movimientos de caja</p>
        </div>
        <div>
            <button class="btn btn-success" data-toggle="modal" data-target="#newCashMovementModal">
                <i class="fas fa-plus mr-2"></i>Nuevo Movimiento
            </button>
        </div>
    </div>

    <!-- Shift Information -->
    <div class="shift-info">
        <div class="row">
            <div class="col-md-4">
                <h6 class="mb-1">Turno Actual</h6>
                <h4 class="mb-0">01 - Matutino</h4>
            </div>
            <div class="col-md-4">
                <h6 class="mb-1">Cajero</h6>
                <h4 class="mb-0">Juan Pérez</h4>
            </div>
            <div class="col-md-4">
                <h6 class="mb-1">Apertura</h6>
                <h4 class="mb-0">08:00 - 15/01/2025</h4>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card cash-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Efectivo en Caja</h6>
                            <h4 class="text-warning mb-0">S/ 3,847.50</h4>
                            <small class="text-info">Conteo físico</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card cash-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Saldo Sistema</h6>
                            <h4 class="text-primary mb-0">S/ 3,920.00</h4>
                            <small class="text-muted">Según registros</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calculator fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card cash-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Diferencia</h6>
                            <h4 class="text-danger mb-0">S/ -72.50</h4>
                            <small class="text-danger">Faltante</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card cash-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Movimientos Hoy</h6>
                            <h4 class="text-success mb-0">47</h4>
                            <small class="text-success">Ingresos y egresos</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exchange-alt fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Alert -->
    <div class="balance-alert balance-difference">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
            <div>
                <h6 class="mb-1">Diferencia Detectada</h6>
                <p class="mb-0">El efectivo físico (S/ 3,847.50) no coincide con el saldo del sistema (S/ 3,920.00). Diferencia: S/ -72.50</p>
            </div>
            <button class="btn btn-outline-warning ml-auto" onclick="adjustCashBalance()">
                <i class="fas fa-sync mr-1"></i>Ajustar
            </button>
        </div>
    </div>

    <!-- Summary Overview -->
    <div class="cash-summary">
        <div class="row text-center">
            <div class="col-md-4">
                <h5>Ingresos del Día</h5>
                <h3 class="mb-0">S/ 12,450.00</h3>
                <small>Ventas y otros ingresos</small>
            </div>
            <div class="col-md-4">
                <h5>Egresos del Día</h5>
                <h3 class="mb-0">S/ 8,920.00</h5>
                <small>Pagos y gastos</small>
            </div>
            <div class="col-md-4">
                <h5>Movimiento Neto</h5>
                <h3 class="mb-0">S/ +3,530.00</h5>
                <small>Flujo de efectivo</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Cash Denominations -->
        <div class="col-md-6">
            <div class="denomination-section">
                <div class="denomination-title">
                    <i class="fas fa-coins mr-2"></i>Billetes
                </div>
                <div class="denomination-grid">
                    <div class="denomination-item">
                        <span class="denomination-value">S/ 200</span>
                        <input type="number" class="form-control denomination-count" value="3" min="0">
                        <span class="total-denomination">S/ 600</span>
                    </div>
                    <div class="denomination-item">
                        <span class="denomination-value">S/ 100</span>
                        <input type="number" class="form-control denomination-count" value="8" min="0">
                        <span class="total-denomination">S/ 800</span>
                    </div>
                    <div class="denomination-item">
                        <span class="denomination-value">S/ 50</span>
                        <input type="number" class="form-control denomination-count" value="6" min="0">
                        <span class="total-denomination">S/ 300</span>
                    </div>
                    <div class="denomination-item">
                        <span class="denomination-value">S/ 20</span>
                        <input type="number" class="form-control denomination-count" value="15" min="0">
                        <span class="total-denomination">S/ 300</span>
                    </div>
                    <div class="denomination-item">
                        <span class="denomination-value">S/ 10</span>
                        <input type="number" class="form-control denomination-count" value="20" min="0">
                        <span class="total-denomination">S/ 200</span>
                    </div>
                    <div class="denomination-item">
                        <span class="denomination-value">S/ 5</span>
                        <input type="number" class="form-control denomination-count" value="12" min="0">
                        <span class="total-denomination">S/ 60</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coins -->
        <div class="col-md-6">
            <div class="denomination-section">
                <div class="denomination-title">
                    <i class="fas fa-coins mr-2"></i>Monedas
                </div>
                <div class="denomination-grid">
                    <div class="denomination-item">
                        <span class="denomination-value">S/ 2</span>
                        <input type="number" class="form-control denomination-count" value="25" min="0">
                        <span class="total-denomination">S/ 50</span>
                    </div>
                    <div class="denomination-item">
                        <span class="denomination-value">S/ 1</span>
                        <input type="number" class="form-control denomination-count" value="45" min="0">
                        <span class="total-denomination">S/ 45</span>
                    </div>
                    <div class="denomination-item">
                        <span class="denomination-value">S/ 0.50</span>
                        <input type="number" class="form-control denomination-count" value="30" min="0">
                        <span class="total-denomination">S/ 15</span>
                    </div>
                    <div class="denomination-item">
                        <span class="denomination-value">S/ 0.20</span>
                        <input type="number" class="form-control denomination-count" value="28" min="0">
                        <span class="total-denomination">S/ 5.60</span>
                    </div>
                    <div class="denomination-item">
                        <span class="denomination-value">S/ 0.10</span>
                        <input type="number" class="form-control denomination-count" value="15" min="0">
                        <span class="total-denomination">S/ 1.50</span>
                    </div>
                    <div class="denomination-item">
                        <span class="denomination-value">S/ 0.05</span>
                        <input type="number" class="form-control denomination-count" value="20" min="0">
                        <span class="total-denomination">S/ 1.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Denomination Summary -->
    <div class="denomination-summary">
        <div class="row text-center">
            <div class="col-md-4">
                <h5>Total Billetes</h5>
                <h4 class="text-primary mb-0">S/ 2,260.00</h4>
            </div>
            <div class="col-md-4">
                <h5>Total Monedas</h5>
                <h4 class="text-info mb-0">S/ 118.10</h4>
            </div>
            <div class="col-md-4">
                <h5>Efectivo Total</h5>
                <h4 class="text-success mb-0">S/ 2,378.10</h4>
                <small class="text-muted">Contado manualmente</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Cash Movements Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exchange-alt mr-2"></i>Movimientos de Caja
                    </h5>
                    <div>
                        <button class="btn btn-light btn-sm" onclick="exportCashMovements()">
                            <i class="fas fa-file-excel mr-1"></i>Exportar
                        </button>
                        <button class="btn btn-light btn-sm" onclick="printCashReport()">
                            <i class="fas fa-print mr-1"></i>Imprimir
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover cash-movements-table mb-0" id="cashMovementsTable">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Tipo</th>
                                    <th>Concepto</th>
                                    <th>Responsable</th>
                                    <th>Monto</th>
                                    <th>Saldo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>14:30</td>
                                    <td><span class="movement-type type-expense">Egreso</span></td>
                                    <td>Pago proveedor ABC</td>
                                    <td>Juan Pérez</td>
                                    <td class="amount-negative">- S/ 150.00</td>
                                    <td>S/ 3,847.50</td>
                                    <td>
                                        <button class="btn btn-outline-info btn-sm" onclick="viewMovement(1)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editMovement(1)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>13:45</td>
                                    <td><span class="movement-type type-income">Ingreso</span></td>
                                    <td>Venta Farmacia #1234</td>
                                    <td>María García</td>
                                    <td class="amount-positive">+ S/ 85.50</td>
                                    <td>S/ 3,997.50</td>
                                    <td>
                                        <button class="btn btn-outline-info btn-sm" onclick="viewMovement(2)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editMovement(2)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>12:30</td>
                                    <td><span class="movement-type type-transfer">Transferencia</span></td>
                                    <td>Depósito a banco</td>
                                    <td>Juan Pérez</td>
                                    <td class="amount-negative">- S/ 1,000.00</td>
                                    <td>S/ 3,912.00</td>
                                    <td>
                                        <button class="btn btn-outline-info btn-sm" onclick="viewMovement(3)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editMovement(3)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>11:15</td>
                                    <td><span class="movement-type type-income">Ingreso</span></td>
                                    <td>Venta Medicamento Controlado</td>
                                    <td>Carmen López</td>
                                    <td class="amount-positive">+ S/ 250.00</td>
                                    <td>S/ 4,912.00</td>
                                    <td>
                                        <button class="btn btn-outline-info btn-sm" onclick="viewMovement(4)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editMovement(4)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>10:00</td>
                                    <td><span class="movement-type type-expense">Egreso</span></td>
                                    <td>Pago gastos operativos</td>
                                    <td>Juan Pérez</td>
                                    <td class="amount-negative">- S/ 200.00</td>
                                    <td>S/ 4,662.00</td>
                                    <td>
                                        <button class="btn btn-outline-info btn-sm" onclick="viewMovement(5)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editMovement(5)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>09:30</td>
                                    <td><span class="movement-type type-income">Ingreso</span></td>
                                    <td>Apertura de caja</td>
                                    <td>Sistema</td>
                                    <td class="amount-positive">+ S/ 4,862.00</td>
                                    <td>S/ 4,862.00</td>
                                    <td>
                                        <button class="btn btn-outline-info btn-sm" onclick="viewMovement(6)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editMovement(6)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar with charts and summary -->
        <div class="col-md-4">
            <!-- Cash Flow Chart -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-area mr-2"></i>Flujo de Efectivo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="cashFlowChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Quick Summary -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie mr-2"></i>Resumen del Turno
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Ventas en efectivo:</span>
                            <strong>S/ 8,650.00</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Otros ingresos:</span>
                            <strong>S/ 3,800.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span><strong>Total Ingresos:</strong></span>
                            <strong class="text-success">S/ 12,450.00</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Pagos a proveedores:</span>
                            <strong>S/ 5,200.00</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Gastos operativos:</span>
                            <strong>S/ 2,150.00</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Depósitos bancarios:</span>
                            <strong>S/ 1,000.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span><strong>Total Egresos:</strong></span>
                            <strong class="text-danger">S/ 8,350.00</strong>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <i class="fas fa-chart-line mr-2"></i>
                        <strong>Flujo Positivo</strong><br>
                        El turno generó S/ 4,100.00 adicionales.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-tools mr-2"></i>Acciones Rápidas</h6>
                <button class="btn btn-outline-success" onclick="closeCashRegister()">
                    <i class="fas fa-lock mr-1"></i>Cerrar Caja
                </button>
                <button class="btn btn-outline-info" onclick="generateCashReport()">
                    <i class="fas fa-file-pdf mr-1"></i>Reporte de Caja
                </button>
                <button class="btn btn-outline-warning" onclick="reconcileCash()">
                    <i class="fas fa-balance-scale mr-1"></i>Conciliar
                </button>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-money-bill-wave mr-2"></i>Gestión de Efectivo</h6>
                <button class="btn btn-outline-primary" onclick="makeDeposit()">
                    <i class="fas fa-university mr-1"></i>Depósito Bancario
                </button>
                <button class="btn btn-outline-danger" onclick="requestChange()">
                    <i class="fas fa-coins mr-1"></i>Solicitar Cambio
                </button>
                <button class="btn btn-outline-secondary" onclick="emergencyClose()">
                    <i class="fas fa-exclamation-triangle mr-1"></i>Cierre Emergencia
                </button>
            </div>
        </div>
    </div>
</div>

<!-- New Cash Movement Modal -->
<div class="modal fade" id="newCashMovementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Nuevo Movimiento de Caja
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="newCashMovementForm">
                    <div class="form-group">
                        <label>Tipo de Movimiento *</label>
                        <select class="form-control" name="movement_type" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="income">Ingreso</option>
                            <option value="expense">Egreso</option>
                            <option value="transfer">Transferencia</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Concepto *</label>
                        <input type="text" class="form-control" name="concept" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Monto *</label>
                        <input type="number" class="form-control" name="amount" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Responsable *</label>
                        <select class="form-control" name="responsible" required>
                            <option value="">Seleccionar responsable</option>
                            <option value="juan">Juan Pérez</option>
                            <option value="maria">María García</option>
                            <option value="carmen">Carmen López</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea class="form-control" name="observations" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="saveCashMovement()">Guardar Movimiento</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional_js')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#cashMovementsTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 15,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            columnDefs: [
                { orderable: false, targets: [6] }
            ]
        });

        // Initialize Cash Flow Chart
        initCashFlowChart();

        // Add event listeners to denomination counts
        $('.denomination-count').on('input', function() {
            updateDenominationTotal($(this));
        });
    });

    function initCashFlowChart() {
        const ctx = document.getElementById('cashFlowChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'],
                datasets: [{
                    label: 'Ingresos',
                    data: [4862, 1200, 850, 955, 1200, 450],
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: '#28a745',
                    borderWidth: 1
                }, {
                    label: 'Egresos',
                    data: [0, 200, 150, 1000, 300, 50],
                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                    borderColor: '#dc3545',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    function updateDenominationTotal(input) {
        const value = parseFloat(input.siblings('.denomination-value').text().replace('S/ ', ''));
        const count = parseInt(input.val()) || 0;
        const total = value * count;
        
        input.siblings('.total-denomination').text('S/ ' + total.toFixed(2));
        calculateTotalCash();
    }

    function calculateTotalCash() {
        let totalBills = 0;
        let totalCoins = 0;
        
        // Calculate bills (first denomination section)
        $('.denomination-section').first().find('.denomination-count').each(function() {
            const value = parseFloat($(this).siblings('.denomination-value').text().replace('S/ ', ''));
            const count = parseInt($(this).val()) || 0;
            totalBills += value * count;
        });
        
        // Calculate coins (second denomination section)
        $('.denomination-section').last().find('.denomination-count').each(function() {
            const value = parseFloat($(this).siblings('.denomination-value').text().replace('S/ ', ''));
            const count = parseInt($(this).val()) || 0;
            totalCoins += value * count;
        });
        
        const totalCash = totalBills + totalCoins;
        
        $('.denomination-summary .col-md-4:eq(0) h4').text('S/ ' + totalBills.toFixed(2));
        $('.denomination-summary .col-md-4:eq(1) h4').text('S/ ' + totalCoins.toFixed(2));
        $('.denomination-summary .col-md-4:eq(2) h4').text('S/ ' + totalCash.toFixed(2));
        
        // Update balance alert
        const systemBalance = 3920.00;
        const difference = totalCash - systemBalance;
        
        if (difference === 0) {
            $('.balance-alert').removeClass('balance-difference').addClass('balance-ok');
            $('.balance-alert').html(`
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fa-2x mr-3"></i>
                    <div>
                        <h6 class="mb-1">Conciliación Perfecta</h6>
                        <p class="mb-0">El efectivo físico coincide exactamente con el saldo del sistema.</p>
                    </div>
                </div>
            `);
        } else {
            const status = difference > 0 ? 'Sobrante' : 'Faltante';
            const color = difference > 0 ? 'text-success' : 'text-danger';
            $('.balance-alert').removeClass('balance-ok').addClass('balance-difference');
            $('.balance-alert').find('.card-title').next('p').html(`
                El efectivo físico (S/ ${totalCash.toFixed(2)}) ${difference > 0 ? 'supera' : 'no alcanza'} el saldo del sistema (S/ ${systemBalance.toFixed(2)}). Diferencia: <span class="${color}">S/ ${difference > 0 ? '+' : ''}${difference.toFixed(2)} ${status}</span>
            `);
        }
    }

    // Movement actions
    function viewMovement(id) {
        Swal.fire({
            title: 'Vista de Movimiento',
            text: `Cargando detalles del movimiento #${id}`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            Swal.fire({
                icon: 'info',
                title: 'Detalle del Movimiento',
                text: 'Esta función mostraría el detalle completo del movimiento',
                timer: 2000,
                showConfirmButton: false
            });
        }, 1500);
    }

    function editMovement(id) {
        Swal.fire({
            title: 'Editando Movimiento',
            text: `Cargando formulario de edición para movimiento #${id}`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            $('#newCashMovementModal').modal('show');
            Swal.close();
        }, 1500);
    }

    // Quick actions
    function adjustCashBalance() {
        Swal.fire({
            title: '¿Ajustar saldo de caja?',
            text: 'Esta acción creará un asiento de ajuste para corregir la diferencia',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#fd7e14',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, ajustar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Ajustando saldo...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Ajuste Realizado',
                        text: 'Se ha creado el asiento de ajuste por S/ 72.50',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 2000);
            }
        });
    }

    function closeCashRegister() {
        Swal.fire({
            title: '¿Cerrar caja?',
            text: 'Esta acción finalizará el turno actual y bloqueará la caja',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, cerrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Cerrando caja...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Caja Cerrada',
                        text: 'El turno ha sido cerrado exitosamente. Saldo final: S/ 3,847.50',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }, 2500);
            }
        });
    }

    function generateCashReport() {
        Swal.fire({
            title: 'Generando Reporte',
            text: 'Creando reporte completo de caja...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Reporte Generado',
                text: 'El reporte de caja ha sido creado exitosamente',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function reconcileCash() {
        Swal.fire({
            title: 'Conciliando Caja',
            text: 'Comparando efectivo físico vs. saldo del sistema...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'info',
                title: 'Conciliación Completada',
                text: 'Se detectaron diferencias que requieren ajuste manual',
                timer: 3000,
                showConfirmButton: false
            });
        }, 2500);
    }

    function makeDeposit() {
        Swal.fire({
            title: 'Depósito Bancario',
            html: `
                <div class="text-left">
                    <p>¿Cuánto desea depositar?</p>
                    <input type="number" id="depositAmount" class="form-control" placeholder="Monto a depositar" step="0.01">
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Confirmar Depósito',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const amount = document.getElementById('depositAmount').value;
                if (!amount || amount <= 0) {
                    Swal.showValidationMessage('Ingrese un monto válido');
                    return false;
                }
                return amount;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando depósito...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Depósito Procesado',
                        text: `Se ha registrado un depósito de S/ ${result.value}`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 2000);
            }
        });
    }

    function requestChange() {
        Swal.fire({
            title: 'Solicitar Cambio',
            html: `
                <div class="text-left">
                    <p>¿Qué denominación necesita?</p>
                    <select id="changeDenomination" class="form-control">
                        <option value="">Seleccionar denominación</option>
                        <option value="5">Billetes de S/ 5</option>
                        <option value="10">Billetes de S/ 10</option>
                        <option value="20">Billetes de S/ 20</option>
                        <option value="50">Billetes de S/ 50</option>
                    </select>
                    <br>
                    <input type="number" id="changeAmount" class="form-control" placeholder="Cantidad de billetes" min="1">
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Solicitar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const denomination = document.getElementById('changeDenomination').value;
                const amount = document.getElementById('changeAmount').value;
                if (!denomination || !amount || amount <= 0) {
                    Swal.showValidationMessage('Complete todos los campos');
                    return false;
                }
                return { denomination, amount };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Solicitud Enviada',
                    text: `Se ha registrado la solicitud de ${result.value.amount} billetes de S/ ${result.value.denomination}`,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    }

    function emergencyClose() {
        Swal.fire({
            title: '¿Cierre de Emergencia?',
            text: 'Esta acción cerrará inmediatamente la caja por seguridad',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, cierre emergencia',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Cierre de Emergencia',
                    text: 'La caja ha sido cerrada inmediatamente',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        });
    }

    // Export functions
    function exportCashMovements() {
        Swal.fire({
            title: 'Exportando Movimientos',
            text: 'Generando archivo Excel con movimientos de caja...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Exportación Exitosa',
                text: 'Los movimientos de caja han sido exportados a Excel',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function printCashReport() {
        window.print();
    }

    // Save new cash movement
    function saveCashMovement() {
        const form = document.getElementById('newCashMovementForm');
        if (form.checkValidity()) {
            Swal.fire({
                title: 'Guardando movimiento...',
                text: 'Procesando información',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
            
            setTimeout(() => {
                $('#newCashMovementModal').modal('hide');
                form.reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Movimiento Registrado',
                    text: 'El movimiento de caja ha sido guardado exitosamente',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 2000);
        } else {
            form.reportValidity();
        }
    }

    // Auto-refresh data every 2 minutes
    setInterval(() => {
        console.log('Refreshing cash register data...');
        // In real implementation, this would fetch fresh data from server
    }, 120000);
</script>
@endsection