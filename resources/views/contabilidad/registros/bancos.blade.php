@extends('layouts.contador')

@section('title', 'Conciliación Bancaria')

@section('additional_css')
<style>
    .bank-card {
        border-left: 4px solid #6f42c1;
        transition: all 0.3s ease;
    }
    .bank-card:hover {
        box-shadow: 0 4px 15px rgba(111, 66, 193, 0.2);
        transform: translateY(-2px);
    }
    
    .bank-summary {
        background: linear-gradient(135deg, #6f42c1 0%, #563d7c 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    
    .account-selector {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
    }
    
    .reconciliation-table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .reconciliation-table th {
        background: linear-gradient(135deg, #495057 0%, #343a40 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 15px 12px;
    }
    
    .reconciliation-table td {
        padding: 12px;
        vertical-align: middle;
        border-color: #f1f3f4;
    }
    
    .reconciliation-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .match-status {
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .status-matched {
        background: #d4edda;
        color: #155724;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-difference {
        background: #f8d7da;
        color: #721c24;
    }
    
    .amount-positive {
        color: #28a745;
        font-weight: 600;
    }
    
    .amount-negative {
        color: #dc3545;
        font-weight: 600;
    }
    
    .amount-neutral {
        color: #6c757d;
        font-weight: 600;
    }
    
    .difference-amount {
        background: #fff3cd;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
        color: #856404;
    }
    
    .chart-container {
        position: relative;
        height: 250px;
        margin-top: 15px;
    }
    
    .reconciliation-actions {
        background: #e9ecef;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .bank-balance {
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .ledger-balance {
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .difference-balance {
        font-size: 1.3rem;
        font-weight: 700;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-dark mb-1">Conciliación Bancaria</h2>
            <p class="text-muted mb-0">Control y conciliación de cuentas bancarias</p>
        </div>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#newReconciliationModal">
                <i class="fas fa-plus mr-2"></i>Nueva Conciliación
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bank-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Cuentas Bancarias</h6>
                            <h4 class="text-primary mb-0">8 cuentas</h4>
                            <small class="text-info">Activas</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-university fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bank-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Conciliaciones Pendientes</h6>
                            <h4 class="text-warning mb-0">3 cuentas</h4>
                            <small class="text-warning">Requieren atención</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bank-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Diferencias Totales</h6>
                            <h4 class="text-danger mb-0">S/ 2,450.00</h4>
                            <small class="text-danger">En revisión</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bank-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Última Conciliación</h6>
                            <h4 class="text-success mb-0">15/01/2025</h4>
                            <small class="text-success">Banco Continental</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Overview -->
    <div class="bank-summary">
        <div class="row text-center">
            <div class="col-md-4">
                <h5>Saldo Bancario</h5>
                <h3 class="mb-0" id="bankBalance">S/ 450,280.00</h3>
            </div>
            <div class="col-md-4">
                <h5>Saldo Contable</h5>
                <h3 class="mb-0" id="ledgerBalance">S/ 447,830.00</h3>
            </div>
            <div class="col-md-4">
                <h5>Diferencia</h5>
                <h3 class="mb-0 difference-balance" id="balanceDifference">S/ +2,450.00</h3>
            </div>
        </div>
    </div>

    <!-- Account Selection -->
    <div class="account-selector">
        <h6 class="mb-3"><i class="fas fa-university mr-2"></i>Seleccionar Cuenta Bancaria</h6>
        <div class="row">
            <div class="col-md-6">
                <select class="form-control" id="bankAccountSelect" onchange="loadAccountData()">
                    <option value="">Seleccionar cuenta bancaria</option>
                    <option value="bcop">Banco Continental - Soles (001-12345678-0-12)</option>
                    <option value="bbva">BBVA - Soles (001-98765432-0-01)</option>
                    <option value="interbank">Interbank - Dólares (100-55566677-0-21)</option>
                    <option value="scotia">ScotiaBank - Soles (001-44556677-0-55)</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="month" class="form-control" id="reconciliationPeriod" value="2025-01" onchange="loadAccountData()">
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-primary" onclick="loadAccountData()">
                    <i class="fas fa-sync mr-1"></i>Cargar Datos
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Reconciliation Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table mr-2"></i>Conciliación - 
                        <span id="selectedAccountName">Seleccione una cuenta</span>
                    </h5>
                    <div>
                        <button class="btn btn-light btn-sm" onclick="exportReconciliation()">
                            <i class="fas fa-file-excel mr-1"></i>Exportar
                        </button>
                        <button class="btn btn-light btn-sm" onclick="printReconciliation()">
                            <i class="fas fa-print mr-1"></i>Imprimir
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover reconciliation-table mb-0" id="reconciliationTable">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th>Débitos</th>
                                    <th>Créditos</th>
                                    <th>Saldo Banco</th>
                                    <th>Saldo Libro</th>
                                    <th>Diferencia</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>15/01/2025</td>
                                    <td>Depósito en efectivo</td>
                                    <td class="amount-neutral">-</td>
                                    <td class="amount-positive">S/ 5,000.00</td>
                                    <td class="bank-balance">S/ 445,000.00</td>
                                    <td class="ledger-balance">S/ 442,550.00</td>
                                    <td><span class="difference-amount">S/ +2,450.00</span></td>
                                    <td>
                                        <span class="match-status status-difference">Diferencia</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>14/01/2025</td>
                                    <td>Transferencia a proveedor</td>
                                    <td class="amount-negative">S/ 8,500.00</td>
                                    <td class="amount-neutral">-</td>
                                    <td class="bank-balance">S/ 440,000.00</td>
                                    <td class="ledger-balance">S/ 440,000.00</td>
                                    <td><span class="amount-neutral">S/ 0.00</span></td>
                                    <td>
                                        <span class="match-status status-matched">Conciliado</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>13/01/2025</td>
                                    <td>Cheque #12345</td>
                                    <td class="amount-negative">S/ 3,200.00</td>
                                    <td class="amount-neutral">-</td>
                                    <td class="bank-balance">S/ 448,500.00</td>
                                    <td class="ledger-balance">S/ 448,500.00</td>
                                    <td><span class="amount-neutral">S/ 0.00</span></td>
                                    <td>
                                        <span class="match-status status-matched">Conciliado</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>12/01/2025</td>
                                    <td>Depósito cliente ABC</td>
                                    <td class="amount-neutral">-</td>
                                    <td class="amount-positive">S/ 12,300.00</td>
                                    <td class="bank-balance">S/ 451,700.00</td>
                                    <td class="ledger-balance">S/ 451,700.00</td>
                                    <td><span class="amount-neutral">S/ 0.00</span></td>
                                    <td>
                                        <span class="match-status status-matched">Conciliado</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>11/01/2025</td>
                                    <td>Comisión bancaria</td>
                                    <td class="amount-negative">S/ 25.50</td>
                                    <td class="amount-neutral">-</td>
                                    <td class="bank-balance">S/ 439,400.00</td>
                                    <td class="ledger-balance">S/ 439,400.00</td>
                                    <td><span class="amount-neutral">S/ 0.00</span></td>
                                    <td>
                                        <span class="match-status status-pending">Pendiente</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>10/01/2025</td>
                                    <td>Pago servicios</td>
                                    <td class="amount-negative">S/ 1,850.00</td>
                                    <td class="amount-neutral">-</td>
                                    <td class="bank-balance">S/ 439,425.50</td>
                                    <td class="ledger-balance">S/ 439,425.50</td>
                                    <td><span class="amount-neutral">S/ 0.00</span></td>
                                    <td>
                                        <span class="match-status status-matched">Conciliado</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar with charts and reconciliation info -->
        <div class="col-md-4">
            <!-- Account Balance Chart -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-area mr-2"></i>Evolución de Saldos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="balanceChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Reconciliation Summary -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calculator mr-2"></i>Resumen de Conciliación
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Saldo según banco:</span>
                            <strong>S/ 445,000.00</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Depósitos no registrados en libros:</span>
                            <strong>S/ 5,000.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Saldo ajustado:</span>
                            <strong class="text-success">S/ 450,000.00</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Saldo según libros:</span>
                            <strong>S/ 447,830.00</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Cheques pendientes:</span>
                            <strong>S/ -2,170.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Saldo ajustado:</span>
                            <strong class="text-success">S/ 450,000.00</strong>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Conciliación Balanceada</strong><br>
                        Los saldos ajustados coinciden.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reconciliation Actions -->
    <div class="reconciliation-actions">
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-tools mr-2"></i>Acciones de Conciliación</h6>
                <button class="btn btn-outline-success mr-2 mb-2" onclick="autoReconcile()">
                    <i class="fas fa-magic mr-1"></i>Conciliación Automática
                </button>
                <button class="btn btn-outline-warning mr-2 mb-2" onclick="markAsReconciled()">
                    <i class="fas fa-check mr-1"></i>Marcar como Conciliado
                </button>
                <button class="btn btn-outline-danger mb-2" onclick="generateAdjustment()">
                    <i class="fas fa-plus mr-1"></i>Generar Asiento de Ajuste
                </button>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-chart-line mr-2"></i>Análisis y Reportes</h6>
                <button class="btn btn-outline-info mr-2 mb-2" onclick="generateReport()">
                    <i class="fas fa-file-pdf mr-1"></i>Reporte de Conciliación
                </button>
                <button class="btn btn-outline-primary mb-2" onclick="trendAnalysis()">
                    <i class="fas fa-chart-bar mr-1"></i>Análisis de Tendencias
                </button>
            </div>
        </div>
    </div>
</div>

<!-- New Reconciliation Modal -->
<div class="modal fade" id="newReconciliationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Nueva Conciliación Bancaria
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="newReconciliationForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cuenta Bancaria *</label>
                                <select class="form-control" name="bank_account" required>
                                    <option value="">Seleccionar cuenta</option>
                                    <option value="bcop">Banco Continental - Soles</option>
                                    <option value="bbva">BBVA - Soles</option>
                                    <option value="interbank">Interbank - Dólares</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Período *</label>
                                <input type="month" class="form-control" name="period" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Saldo Banco</label>
                                <input type="number" class="form-control" name="bank_balance" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Estado Cuenta</label>
                                <input type="date" class="form-control" name="statement_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea class="form-control" name="observations" rows="3" placeholder="Notas adicionales sobre esta conciliación..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveReconciliation()">Iniciar Conciliación</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional_js')
<script>
    // Global variables
    let currentAccountData = null;

    $(document).ready(function() {
        // Initialize DataTable
        $('#reconciliationTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 15,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            }
        });

        // Initialize Balance Chart
        initBalanceChart();
    });

    function initBalanceChart() {
        const ctx = document.getElementById('balanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Saldo Banco',
                    data: [425000, 440000, 435000, 445000, 450000, 445000],
                    borderColor: '#6f42c1',
                    backgroundColor: 'rgba(111, 66, 193, 0.1)',
                    tension: 0.4,
                    fill: false,
                    borderWidth: 2
                }, {
                    label: 'Saldo Libro',
                    data: [422000, 438000, 432000, 442830, 447830, 447830],
                    borderColor: '#fd7e14',
                    backgroundColor: 'rgba(253, 126, 20, 0.1)',
                    tension: 0.4,
                    fill: false,
                    borderWidth: 2
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
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + (value / 1000).toFixed(0) + 'K';
                            }
                        }
                    }
                }
            }
        });
    }

    function loadAccountData() {
        const accountSelect = document.getElementById('bankAccountSelect');
        const periodInput = document.getElementById('reconciliationPeriod');
        
        if (!accountSelect.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Seleccione una cuenta',
                text: 'Debe seleccionar una cuenta bancaria para cargar los datos',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        Swal.fire({
            title: 'Cargando datos...',
            text: 'Obteniendo información de conciliación',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        // Simulate loading
        setTimeout(() => {
            currentAccountData = {
                accountName: accountSelect.options[accountSelect.selectedIndex].text,
                period: periodInput.value,
                bankBalance: 445000.00,
                ledgerBalance: 447830.00,
                difference: -2830.00
            };

            updateAccountDisplay();
            Swal.close();
        }, 2000);
    }

    function updateAccountDisplay() {
        const accountNameSpan = document.getElementById('selectedAccountName');
        const bankBalanceSpan = document.getElementById('bankBalance');
        const ledgerBalanceSpan = document.getElementById('ledgerBalance');
        const differenceSpan = document.getElementById('balanceDifference');

        if (currentAccountData) {
            accountNameSpan.textContent = currentAccountData.accountName;
            bankBalanceSpan.textContent = `S/ ${currentAccountData.bankBalance.toLocaleString('es-PE', {minimumFractionDigits: 2})}`;
            ledgerBalanceSpan.textContent = `S/ ${currentAccountData.ledgerBalance.toLocaleString('es-PE', {minimumFractionDigits: 2})}`;
            
            const difference = currentAccountData.bankBalance - currentAccountData.ledgerBalance;
            const sign = difference >= 0 ? '+' : '';
            differenceSpan.textContent = `S/ ${sign}${difference.toLocaleString('es-PE', {minimumFractionDigits: 2})}`;
            
            if (difference >= 0) {
                differenceSpan.className = 'difference-balance text-success';
            } else {
                differenceSpan.className = 'difference-balance text-danger';
            }
        }
    }

    // Reconciliation actions
    function autoReconcile() {
        Swal.fire({
            title: 'Conciliación Automática',
            text: 'Analizando transacciones para conciliación automática...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Conciliación Completada',
                text: 'Se han conciliado 45 transacciones automáticamente. 6 requieren revisión manual.',
                timer: 4000,
                showConfirmButton: false
            });
        }, 3000);
    }

    function markAsReconciled() {
        Swal.fire({
            title: '¿Marcar como conciliado?',
            text: 'Esta acción marcará todas las transacciones como conciliadas',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, conciliar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Conciliando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Conciliación Exitosa',
                        text: 'La conciliación ha sido marcada como completada',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 2000);
            }
        });
    }

    function generateAdjustment() {
        Swal.fire({
            title: 'Generando Asiento de Ajuste',
            text: 'Creando asiento contable para diferencias...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'info',
                title: 'Asiento Generado',
                text: 'Se ha creado el asiento de ajuste #AJ-2025-001 por S/ 2,450.00',
                timer: 3000,
                showConfirmButton: false
            });
        }, 2500);
    }

    function generateReport() {
        Swal.fire({
            title: 'Generando Reporte',
            text: 'Creando reporte de conciliación bancaria...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Reporte Generado',
                text: 'El reporte de conciliación ha sido creado exitosamente',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function trendAnalysis() {
        Swal.fire({
            title: 'Análisis de Tendencias',
            html: `
                <div class="text-left">
                    <h6>Promedio mensual de diferencias:</h6>
                    <p><strong>S/ 1,850.00</strong></p>
                    <h6>Tendencia:</h6>
                    <p>📈 <span class="text-success">Incrementando 12.5%</span></p>
                    <h6>Recomendación:</h6>
                    <p>Revisar procesos de registro de transacciones</p>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Cerrar'
        });
    }

    function exportReconciliation() {
        Swal.fire({
            title: 'Exportando Conciliación',
            text: 'Generando archivo Excel con datos de conciliación...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Exportación Exitosa',
                text: 'El archivo de conciliación ha sido exportado a Excel',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function printReconciliation() {
        window.print();
    }

    function saveReconciliation() {
        const form = document.getElementById('newReconciliationForm');
        if (form.checkValidity()) {
            Swal.fire({
                title: 'Iniciando conciliación...',
                text: 'Procesando información',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
            
            setTimeout(() => {
                $('#newReconciliationModal').modal('hide');
                form.reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Conciliación Iniciada',
                    text: 'La nueva conciliación ha sido configurada exitosamente',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 2000);
        } else {
            form.reportValidity();
        }
    }

    // Auto-refresh data every 10 minutes
    setInterval(() => {
        console.log('Refreshing bank reconciliation data...');
        if (currentAccountData) {
            loadAccountData();
        }
    }, 600000);
</script>
@endsection 