<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Medicamentos Controlados - SIFANO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .report-card {
            border-left: 5px solid #dc3545;
            background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        }
        .compliance-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: bold;
        }
        .digemid-header {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
        }
        .report-status {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: bold;
            font-size: 0.875rem;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-generated { background-color: #cce5ff; color: #004085; }
        .status-sent { background-color: #d4edda; color: #155724; }
        .status-approved { background-color: #d1ecf1; color: #0c5460; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
        .alert-calendar {
            border: 2px solid #ffc107;
            background-color: #fffbf0;
        }
        .compliance-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .compliant { background-color: #28a745; }
        .warning { background-color: #ffc107; }
        .critical { background-color: #dc3545; }
        .export-section {
            background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
            border: 2px solid #2196f3;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="text-primary">
                            <i class="fas fa-file-medical-alt me-2"></i>
                            Reportes Medicamentos Controlados
                        </h2>
                        <p class="text-muted mb-0">Generación y envío de reportes regulatorios a DIGEMID</p>
                    </div>
                    <div class="text-end">
                        <span class="compliance-badge">
                            <i class="fas fa-shield-alt me-2"></i>
                            Cumplimiento DIGEMID
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-check text-success mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Reportes al Día</h5>
                        <h3 class="text-success">8/8</h3>
                        <small class="text-muted">Enviados este mes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-clock text-warning mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Pendientes</h5>
                        <h3 class="text-warning">2</h3>
                        <small class="text-muted">Por generar/enviar</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line text-info mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Dispensaciones</h5>
                        <h3 class="text-info">156</h3>
                        <small class="text-muted">Este mes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle text-danger mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Alertas</h5>
                        <h3 class="text-danger">5</h3>
                        <small class="text-muted">Requieren atención</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Generation Form -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card report-card">
                    <div class="card-header digemid-header">
                        <h5 class="mb-0">
                            <i class="fas fa-plus-circle me-2"></i>
                            Generar Nuevo Reporte DIGEMID
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="reportForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Reporte:</label>
                                    <select class="form-select" id="reportType" required>
                                        <option value="">Seleccionar tipo...</option>
                                        <option value="monthly">Reporte Mensual de Dispensaciones</option>
                                        <option value="inventory">Reporte de Inventario de Controlados</option>
                                        <option value="discrepancy">Reporte de Discrepancias</option>
                                        <option value="theft">Reporte de Sustracciones/Pérdidas</option>
                                        <option value="destruction">Reporte de Destrucción</option>
                                        <option value="emergency">Reporte de Situaciones de Emergencia</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Período:</label>
                                    <select class="form-select" id="reportPeriod" required>
                                        <option value="">Seleccionar período...</option>
                                        <option value="current-month">Mes Actual (Octubre 2024)</option>
                                        <option value="previous-month">Mes Anterior (Septiembre 2024)</option>
                                        <option value="quarter">Trimestre Actual (Q4 2024)</option>
                                        <option value="custom">Período Personalizado</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="customPeriod" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Inicio:</label>
                                    <input type="date" class="form-control" id="startDate">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Fin:</label>
                                    <input type="date" class="form-control" id="endDate">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Categorías de Sustancias:</label>
                                    <select class="form-select" multiple id="substanceCategories">
                                        <option value="cat1">Categoría I (Sin uso médico)</option>
                                        <option value="cat2" selected>Categoría II (Alto potencial abuso)</option>
                                        <option value="cat3" selected>Categoría III (Potencial abuso moderado)</option>
                                        <option value="cat4" selected>Categoría IV (Bajo potencial abuso)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Formato de Exportación:</label>
                                    <div class="mt-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="formatPDF" checked>
                                            <label class="form-check-label" for="formatPDF">PDF</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="formatExcel" checked>
                                            <label class="form-check-label" for="formatExcel">Excel</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="formatXML">
                                            <label class="form-check-label" for="formatXML">XML DIGEMID</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary" onclick="generateReport()">
                                        <i class="fas fa-cog me-2"></i>
                                        Generar Reporte
                                    </button>
                                    <button type="button" class="btn btn-success" onclick="previewReport()">
                                        <i class="fas fa-eye me-2"></i>
                                        Vista Previa
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="scheduleReport()">
                                        <i class="fas fa-calendar-plus me-2"></i>
                                        Programar Envío
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <!-- Compliance Calendar -->
                <div class="card alert-calendar">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Calendario de Cumplimiento
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="compliance-indicator compliant"></span>
                                <span><strong>5 Nov:</strong> Reporte mensual</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="compliance-indicator warning"></span>
                                <span><strong>15 Nov:</strong> Inventario trimestral</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="compliance-indicator compliant"></span>
                                <span><strong>30 Nov:</strong> Reporte anual</span>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Los reportes deben enviarse dentro de los primeros 5 días hábiles del mes siguiente.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Dashboard -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>
                            Dispensaciones por Categoría
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            Tendencia Mensual de Dispensaciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report History -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            Historial de Reportes DIGEMID
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="reportsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha Generación</th>
                                        <th>Tipo Reporte</th>
                                        <th>Período</th>
                                        <th>Dispensaciones</th>
                                        <th>Estado</th>
                                        <th>Enviado a DIGEMID</th>
                                        <th>Respuesta</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>24/10/2024 16:45</td>
                                        <td>Mensual Dispensaciones</td>
                                        <td>Septiembre 2024</td>
                                        <td>142</td>
                                        <td><span class="report-status status-sent">Enviado</span></td>
                                        <td>24/10/2024 17:00</td>
                                        <td><span class="text-success"><i class="fas fa-check"></i> Aprobado</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewReport(1234)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="downloadReport(1234)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>23/10/2024 14:30</td>
                                        <td>Inventario Controlados</td>
                                        <td>Trimestre Q3 2024</td>
                                        <td>-</td>
                                        <td><span class="report-status status-approved">Aprobado</span></td>
                                        <td>23/10/2024 15:00</td>
                                        <td><span class="text-success"><i class="fas fa-check"></i> Conforme</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewReport(1233)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="downloadReport(1233)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>20/10/2024 10:15</td>
                                        <td>Discrepancias</td>
                                        <td>Octubre 2024</td>
                                        <td>3</td>
                                        <td><span class="report-status status-generated">Generado</span></td>
                                        <td>No enviado</td>
                                        <td><span class="text-warning"><i class="fas fa-clock"></i> Pendiente</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewReport(1232)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="sendToDigemid(1232)">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="downloadReport(1232)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>15/10/2024 09:00</td>
                                        <td>Sustracciones</td>
                                        <td>Septiembre 2024</td>
                                        <td>1</td>
                                        <td><span class="report-status status-rejected">Rechazado</span></td>
                                        <td>16/10/2024 08:30</td>
                                        <td><span class="text-danger"><i class="fas fa-times"></i> Requiere更多信息</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewReport(1231)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="resubmitReport(1231)">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>10/10/2024 15:45</td>
                                        <td>Destrucción</td>
                                        <td>Agosto 2024</td>
                                        <td>5</td>
                                        <td><span class="report-status status-sent">Enviado</span></td>
                                        <td>11/10/2024 09:00</td>
                                        <td><span class="text-success"><i class="fas fa-check"></i> Aprobado</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewReport(1230)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="downloadReport(1230)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export and Integration -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card export-section">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-download me-2"></i>
                            Exportar Datos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Reportes Individuales:</h6>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary btn-sm" onclick="exportDispensations()">
                                        <i class="fas fa-pills me-2"></i>
                                        Todas las Dispensaciones
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" onclick="exportInventory()">
                                        <i class="fas fa-warehouse me-2"></i>
                                        Inventario Controlado
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" onclick="exportDiscrepancies()">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Discrepancias
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Integraciones:</h6>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-success btn-sm" onclick="syncWithDigemid()">
                                        <i class="fas fa-sync me-2"></i>
                                        Sincronizar con DIGEMID
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="configureApi()">
                                        <i class="fas fa-cog me-2"></i>
                                        Configurar API
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="backupData()">
                                        <i class="fas fa-database me-2"></i>
                                        Respaldo de Datos
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bell me-2"></i>
                            Notificaciones y Alertas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-clock me-2"></i>Próximos Vencimientos</h6>
                            <ul class="mb-0">
                                <li><strong>Reporte mensual:</strong> Vence en 2 días</li>
                                <li><strong>Inventario trimestral:</strong> Vence en 12 días</li>
                            </ul>
                        </div>
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Recordatorios</h6>
                            <ul class="mb-0">
                                <li>Reporte diario de dispensaciones</li>
                                <li>Actualización de inventario semanal</li>
                                <li>Revisión mensual de discrepancias</li>
                            </ul>
                        </div>
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle me-2"></i>Actividades Completadas</h6>
                            <ul class="mb-0">
                                <li>Reporte de destrucción enviado</li>
                                <li>Inventario Q3 validado</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#reportsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json"
                },
                "order": [[0, "desc"]],
                "pageLength": 10
            });

            // Initialize Select2
            $('#substanceCategories').select2({
                placeholder: "Seleccionar categorías...",
                allowClear: true
            });

            // Initialize Charts
            initializeCharts();

            // Show/hide custom period
            $('#reportPeriod').change(function() {
                if ($(this).val() === 'custom') {
                    $('#customPeriod').show();
                } else {
                    $('#customPeriod').hide();
                }
            });
        });

        function initializeCharts() {
            // Category Pie Chart
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Categoría II', 'Categoría III', 'Categoría IV'],
                    datasets: [{
                        data: [65, 25, 10],
                        backgroundColor: [
                            '#dc3545',
                            '#ffc107',
                            '#28a745'
                        ],
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
                        title: {
                            display: true,
                            text: 'Distribución por Categoría de Sustancia'
                        }
                    }
                }
            });

            // Trend Line Chart
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct'],
                    datasets: [{
                        label: 'Dispensaciones',
                        data: [120, 135, 128, 142, 138, 145, 152, 148, 156, 142],
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'Tendencia de Dispensaciones 2024'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function generateReport() {
            const reportType = document.getElementById('reportType').value;
            const reportPeriod = document.getElementById('reportPeriod').value;
            
            if (!reportType || !reportPeriod) {
                Swal.fire('Error', 'Por favor complete todos los campos requeridos', 'error');
                return;
            }

            Swal.fire({
                title: 'Generando Reporte',
                text: 'Procesando datos y generando reporte DIGEMID...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            // Simulate report generation
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Reporte Generado',
                    text: 'El reporte se ha generado exitosamente',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    // Refresh table or update UI
                    location.reload();
                });
            }, 3000);
        }

        function previewReport() {
            Swal.fire({
                icon: 'info',
                title: 'Vista Previa',
                text: 'Generando vista previa del reporte...',
                timer: 2000,
                showConfirmButton: false
            });
        }

        function scheduleReport() {
            Swal.fire({
                title: 'Programar Envío',
                text: 'Seleccione la fecha y hora para el envío automático:',
                html: `
                    <div class="mb-3">
                        <label class="form-label">Fecha:</label>
                        <input type="date" class="form-control" id="scheduleDate" value="${new Date().toISOString().split('T')[0]}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hora:</label>
                        <input type="time" class="form-control" id="scheduleTime" value="08:00">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Programar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const date = document.getElementById('scheduleDate').value;
                    const time = document.getElementById('scheduleTime').value;
                    if (!date || !time) {
                        Swal.showValidationMessage('Por favor complete la fecha y hora');
                        return false;
                    }
                    return { date, time };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reporte Programado',
                        text: `Envío programado para ${result.value.date} a las ${result.value.time}`,
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            });
        }

        function viewReport(reportId) {
            Swal.fire({
                icon: 'info',
                title: 'Ver Reporte',
                text: `Abriendo reporte ${reportId}...`,
                timer: 2000,
                showConfirmButton: false
            });
        }

        function downloadReport(reportId) {
            Swal.fire({
                title: 'Descargar Reporte',
                text: '¿En qué formato desea descargar?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'PDF',
                cancelButtonText: 'Excel',
                showDenyButton: true,
                denyButtonText: 'XML'
            }).then((result) => {
                let format = '';
                if (result.isConfirmed) format = 'PDF';
                else if (result.isDenied) format = 'XML';
                else format = 'Excel';

                Swal.fire({
                    icon: 'success',
                    title: 'Descarga Iniciada',
                    text: `Descargando reporte en formato ${format}...`,
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        }

        function sendToDigemid(reportId) {
            Swal.fire({
                title: 'Enviar a DIGEMID',
                text: '¿Está seguro que desea enviar este reporte a DIGEMID?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Enviando a DIGEMID...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Enviado Exitosamente',
                            text: 'El reporte ha sido enviado a DIGEMID',
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }, 2000);
                }
            });
        }

        function resubmitReport(reportId) {
            Swal.fire({
                title: 'Reenviar Reporte',
                text: '¿Desea reenviar el reporte corregido a DIGEMID?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, reenviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reporte Reenviado',
                        text: 'El reporte corregido ha sido enviado exitosamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }

        function exportDispensations() {
            Swal.fire({
                icon: 'info',
                title: 'Exportar Dispensaciones',
                text: 'Generando archivo Excel con todas las dispensaciones...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Exportación Completada',
                    text: 'Archivo descargado exitosamente',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 3000);
        }

        function exportInventory() {
            Swal.fire({
                icon: 'info',
                title: 'Exportar Inventario',
                text: 'Preparando inventario de sustancias controladas...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Inventario Exportado',
                    text: 'Reporte de inventario descargado',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 2500);
        }

        function exportDiscrepancies() {
            Swal.fire({
                icon: 'info',
                title: 'Exportar Discrepancias',
                text: 'Generando reporte de discrepancias...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Discrepancias Exportadas',
                    text: 'Reporte de discrepancias descargado',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 2000);
        }

        function syncWithDigemid() {
            Swal.fire({
                title: 'Sincronización con DIGEMID',
                text: 'Iniciando sincronización con el sistema DIGEMID...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Sincronización Completada',
                    text: 'Datos sincronizados exitosamente con DIGEMID',
                    timer: 3000,
                    showConfirmButton: false
                });
            }, 4000);
        }

        function configureApi() {
            Swal.fire({
                icon: 'info',
                title: 'Configuración API',
                text: 'Abriendo panel de configuración de API DIGEMID...',
                timer: 2000,
                showConfirmButton: false
            });
        }

        function backupData() {
            Swal.fire({
                title: 'Crear Respaldo',
                text: '¿Desea crear un respaldo completo de los datos?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, crear respaldo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Creando respaldo...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Respaldo Creado',
                            text: 'Respaldo de datos completado exitosamente',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }, 5000);
                }
            });
        }
    </script>
</body>
</html>