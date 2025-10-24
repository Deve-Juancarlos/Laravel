@extends('layouts.contador')

@section('title', 'Registro de Compras')

@section('additional_css')
<style>
    .purchase-card {
        border-left: 4px solid #17a2b8;
        transition: all 0.3s ease;
    }
    .purchase-card:hover {
        box-shadow: 0 4px 15px rgba(23, 162, 184, 0.2);
        transform: translateY(-2px);
    }
    
    .purchase-summary {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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
    
    .purchase-table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .purchase-table th {
        background: linear-gradient(135deg, #495057 0%, #343a40 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 15px 12px;
    }
    
    .purchase-table td {
        padding: 12px;
        vertical-align: middle;
        border-color: #f1f3f4;
    }
    
    .purchase-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .doc-type-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: 500;
    }
    
    .sunat-status {
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .status-sent {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-error {
        background: #f8d7da;
        color: #721c24;
    }
    
    .amount-cell {
        font-weight: 600;
        text-align: right;
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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-dark mb-1">Registro de Compras</h2>
            <p class="text-muted mb-0">Control y seguimiento de compras para SUNAT</p>
        </div>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#newPurchaseModal">
                <i class="fas fa-plus mr-2"></i>Nueva Compra
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card purchase-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Total Compras</h6>
                            <h4 class="text-info mb-0">S/ 328,450.00</h4>
                            <small class="text-success">+15.3% vs mes anterior</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shopping-cart fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card purchase-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">IGV Compras</h6>
                            <h4 class="text-success mb-0">S/ 59,121.00</h4>
                            <small class="text-info">18% del total</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-receipt fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card purchase-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">SUNAT Enviado</h6>
                            <h4 class="text-warning mb-0">285 documentos</h4>
                            <small class="text-success">98.6% enviados</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-cloud-upload-alt fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card purchase-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted">Pendientes</h6>
                            <h4 class="text-danger mb-0">4 documentos</h4>
                            <small class="text-danger">Requieren envío</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Overview -->
    <div class="purchase-summary">
        <div class="row text-center">
            <div class="col-md-3">
                <h5>Base Imponible</h5>
                <h3 class="mb-0">S/ 269,329.00</h3>
            </div>
            <div class="col-md-3">
                <h5>IGV Total</h5>
                <h3 class="mb-0">S/ 59,121.00</h3>
            </div>
            <div class="col-md-3">
                <h5>Documentos</h5>
                <h3 class="mb-0">289</h3>
            </div>
            <div class="col-md-3">
                <h5>Proveedores</h5>
                <h3 class="mb-0">45</h3>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Filters -->
        <div class="col-12">
            <div class="filter-section">
                <h6 class="mb-3"><i class="fas fa-filter mr-2"></i>Filtros de Búsqueda</h6>
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label">Fecha Desde</label>
                        <input type="date" class="form-control" id="dateFrom">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fecha Hasta</label>
                        <input type="date" class="form-control" id="dateTo">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Proveedor</label>
                        <select class="form-control select2" id="supplierFilter">
                            <option value="">Todos los proveedores</option>
                            <option value="1">Farmacia Central</option>
                            <option value="2">Distribuidora Medica</option>
                            <option value="3">Lab. Farmacéutico S.A.</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipo Doc.</label>
                        <select class="form-control" id="docTypeFilter">
                            <option value="">Todos</option>
                            <option value="01">Factura</option>
                            <option value="03">Boleta</option>
                            <option value="12">Ticket/Nota de Venta</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Estado SUNAT</label>
                        <select class="form-control" id="sunatFilter">
                            <option value="">Todos</option>
                            <option value="sent">Enviado</option>
                            <option value="pending">Pendiente</option>
                            <option value="error">Error</option>
                        </select>
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
        <!-- Purchases Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table mr-2"></i>Lista de Compras
                    </h5>
                    <div class="export-buttons">
                        <button class="btn btn-light btn-sm" onclick="exportToExcel()">
                            <i class="fas fa-file-excel mr-1"></i>Excel
                        </button>
                        <button class="btn btn-light btn-sm" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf mr-1"></i>PDF
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover purchase-table mb-0" id="purchasesTable">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Documento</th>
                                    <th>Base Imp.</th>
                                    <th>IGV</th>
                                    <th>Total</th>
                                    <th>Estado SUNAT</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>15/01/2025</td>
                                    <td>Farmacia Central S.A.</td>
                                    <td>
                                        <span class="doc-type-badge badge-primary">Factura</span><br>
                                        <small>F001-12345</small>
                                    </td>
                                    <td class="amount-cell">S/ 8,500.00</td>
                                    <td class="amount-cell">S/ 1,530.00</td>
                                    <td class="amount-cell">S/ 10,030.00</td>
                                    <td>
                                        <span class="sunat-status status-sent">Enviado</span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewPurchase(1)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editPurchase(1)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="syncSUNAT(1)" title="Sincronizar SUNAT">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>14/01/2025</td>
                                    <td>Distribuidora Médica</td>
                                    <td>
                                        <span class="doc-type-badge badge-primary">Factura</span><br>
                                        <small>F001-12344</small>
                                    </td>
                                    <td class="amount-cell">S/ 12,300.00</td>
                                    <td class="amount-cell">S/ 2,214.00</td>
                                    <td class="amount-cell">S/ 14,514.00</td>
                                    <td>
                                        <span class="sunat-status status-sent">Enviado</span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewPurchase(2)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editPurchase(2)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="syncSUNAT(2)" title="Sincronizar SUNAT">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>13/01/2025</td>
                                    <td>Lab. Farmacéutico S.A.</td>
                                    <td>
                                        <span class="doc-type-badge badge-info">Boleta</span><br>
                                        <small>B001-6789</small>
                                    </td>
                                    <td class="amount-cell">S/ 2,150.00</td>
                                    <td class="amount-cell">S/ 387.00</td>
                                    <td class="amount-cell">S/ 2,537.00</td>
                                    <td>
                                        <span class="sunat-status status-pending">Pendiente</span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewPurchase(3)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editPurchase(3)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="syncSUNAT(3)" title="Sincronizar SUNAT">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>12/01/2025</td>
                                    <td>Equipo Médico S.A.C.</td>
                                    <td>
                                        <span class="doc-type-badge badge-primary">Factura</span><br>
                                        <small>F001-12343</small>
                                    </td>
                                    <td class="amount-cell">S/ 5,600.00</td>
                                    <td class="amount-cell">S/ 1,008.00</td>
                                    <td class="amount-cell">S/ 6,608.00</td>
                                    <td>
                                        <span class="sunat-status status-error">Error</span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewPurchase(4)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editPurchase(4)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="syncSUNAT(4)" title="Sincronizar SUNAT">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>11/01/2025</td>
                                    <td>Insumos Químicos</td>
                                    <td>
                                        <span class="doc-type-badge badge-primary">Factura</span><br>
                                        <small>F001-12342</small>
                                    </td>
                                    <td class="amount-cell">S/ 3,250.00</td>
                                    <td class="amount-cell">S/ 585.00</td>
                                    <td class="amount-cell">S/ 3,835.00</td>
                                    <td>
                                        <span class="sunat-status status-sent">Enviado</span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewPurchase(5)" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editPurchase(5)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="syncSUNAT(5)" title="Sincronizar SUNAT">
                                            <i class="fas fa-sync"></i>
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
            <!-- Monthly Trend Chart -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line mr-2"></i>Tendencia Mensual
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Suppliers -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users mr-2"></i>Top Proveedores
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="top-suppliers">
                        <div class="supplier-item">
                            <div>
                                <strong>Farmacia Central S.A.</strong><br>
                                <small class="text-muted">S/ 85,400.00</small>
                            </div>
                            <span class="badge badge-success">26%</span>
                        </div>
                        <div class="supplier-item">
                            <div>
                                <strong>Distribuidora Médica</strong><br>
                                <small class="text-muted">S/ 72,300.00</small>
                            </div>
                            <span class="badge badge-info">22%</span>
                        </div>
                        <div class="supplier-item">
                            <div>
                                <strong>Lab. Farmacéutico S.A.</strong><br>
                                <small class="text-muted">S/ 68,900.00</small>
                            </div>
                            <span class="badge badge-warning">21%</span>
                        </div>
                        <div class="supplier-item">
                            <div>
                                <strong>Equipo Médico S.A.C.</strong><br>
                                <small class="text-muted">S/ 45,600.00</small>
                            </div>
                            <span class="badge badge-secondary">14%</span>
                        </div>
                        <div class="supplier-item">
                            <div>
                                <strong>Insumos Químicos</strong><br>
                                <small class="text-muted">S/ 28,400.00</small>
                            </div>
                            <span class="badge badge-primary">9%</span>
                        </div>
                        <div class="supplier-item">
                            <div>
                                <strong>Otros</strong><br>
                                <small class="text-muted">S/ 27,850.00</small>
                            </div>
                            <span class="badge badge-light">8%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Purchase Modal -->
<div class="modal fade" id="newPurchaseModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Nueva Compra
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="newPurchaseForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Proveedor *</label>
                                <select class="form-control select2" name="supplier_id" required>
                                    <option value="">Seleccionar proveedor</option>
                                    <option value="1">Farmacia Central S.A.</option>
                                    <option value="2">Distribuidora Médica</option>
                                    <option value="3">Lab. Farmacéutico S.A.</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo Documento *</label>
                                <select class="form-control" name="document_type" required>
                                    <option value="">Seleccionar</option>
                                    <option value="01">Factura</option>
                                    <option value="03">Boleta</option>
                                    <option value="12">Ticket/Nota de Venta</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Número *</label>
                                <input type="text" class="form-control" name="document_number" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha Emisión *</label>
                                <input type="date" class="form-control" name="issue_date" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha Vencimiento</label>
                                <input type="date" class="form-control" name="due_date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Moneda</label>
                                <select class="form-control" name="currency">
                                    <option value="PEN">Soles (PEN)</option>
                                    <option value="USD">Dólares (USD)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo Cambio</label>
                                <input type="number" class="form-control" name="exchange_rate" step="0.0001" value="1.0000">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="savePurchase()">Guardar Compra</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional_js')
<script>
    // Initialize charts and DataTables
    $(document).ready(function() {
        // Initialize DataTable
        $('#purchasesTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
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

        // Initialize Monthly Trend Chart
        initMonthlyTrendChart();
    });

    function initMonthlyTrendChart() {
        const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Compras',
                    data: [280000, 310000, 285000, 328450, 340000, 355000],
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
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

    // Filter functions
    function applyFilters() {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        const supplier = $('#supplierFilter').val();
        const docType = $('#docTypeFilter').val();
        const sunatStatus = $('#sunatFilter').val();

        Swal.fire({
            title: 'Aplicando filtros...',
            text: 'Buscando compras con los criterios seleccionados',
            timer: 1000,
            showConfirmButton: false
        });
    }

    function clearFilters() {
        $('#dateFrom, #dateTo, #supplierFilter, #docTypeFilter, #sunatFilter').val('');
        $('#supplierFilter').select2('val', '');
    }

    // Action functions
    function viewPurchase(id) {
        Swal.fire({
            title: 'Visualizando compra',
            text: `Cargando detalles de la compra #${id}`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            Swal.fire({
                icon: 'info',
                title: 'Vista de Compra',
                text: 'Esta función abriría el detalle completo de la compra',
                timer: 2000,
                showConfirmButton: false
            });
        }, 1500);
    }

    function editPurchase(id) {
        Swal.fire({
            title: 'Editando compra',
            text: `Cargando formulario de edición para compra #${id}`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            $('#newPurchaseModal').modal('show');
            Swal.close();
        }, 1500);
    }

    function syncSUNAT(id) {
        Swal.fire({
            title: 'Sincronizando con SUNAT',
            text: 'Enviando documento al servidor de SUNAT...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Sincronización Exitosa',
                text: 'El documento ha sido enviado correctamente a SUNAT',
                timer: 3000,
                showConfirmButton: false
            });
        }, 3000);
    }

    // Export functions
    function exportToExcel() {
        Swal.fire({
            title: 'Exportando a Excel',
            text: 'Generando archivo de compras...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Excel Generado',
                text: 'El archivo de compras ha sido exportado exitosamente',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function exportToPDF() {
        Swal.fire({
            title: 'Generando PDF',
            text: 'Creando reporte de compras...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
        
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'PDF Generado',
                text: 'El reporte de compras ha sido creado exitosamente',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    // Save new purchase
    function savePurchase() {
        const form = document.getElementById('newPurchaseForm');
        if (form.checkValidity()) {
            Swal.fire({
                title: 'Guardando compra...',
                text: 'Procesando información',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
            
            setTimeout(() => {
                $('#newPurchaseModal').modal('hide');
                form.reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Compra Registrada',
                    text: 'La nueva compra ha sido guardada exitosamente',
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
        console.log('Refreshing purchase data...');
        // In real implementation, this would fetch fresh data from server
    }, 300000);
</script>
@endsection