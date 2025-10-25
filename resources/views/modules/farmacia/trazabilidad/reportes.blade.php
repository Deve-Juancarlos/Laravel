<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Trazabilidad - SIFANO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .report-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }
        .compliance-section {
            background: linear-gradient(135deg, #d4edda 0%, #ffffff 100%);
            border: 2px solid #28a745;
            border-radius: 0.5rem;
        }
        .traceability-metric {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin: 0.5rem 0;
            text-align: center;
        }
        .chain-integrity {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 0.75rem;
            border-radius: 0.5rem;
            text-align: center;
        }
        .break-point {
            background: #dc3545;
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: bold;
        }
        .audit-trail {
            border-left: 4px solid #007bff;
            background-color: #f8f9fa;
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
        .export-panel {
            background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
            border: 2px solid #2196f3;
        }
        .performance-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .excellent { background-color: #28a745; }
        .good { background-color: #20c997; }
        .warning { background-color: #ffc107; }
        .critical { background-color: #dc3545; }
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
                            Reportes de Trazabilidad
                        </h2>
                        <p class="text-muted mb-0">Análisis integral y cumplimiento regulatorio de trazabilidad farmacéutica</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success px-3 py-2">
                            <i class="fas fa-chart-line me-2"></i>
                            KPIs Trazabilidad
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Performance Indicators -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-link text-success mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Integridad de Cadena</h5>
                        <h3 class="text-success">99.2%</h3>
                        <small class="text-muted">Eslabones completos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-clock text-primary mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Tiempo Promedio</h5>
                        <h3 class="text-primary">2.1s</h3>
                        <small class="text-muted">Consultas de trazabilidad</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-database text-info mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Registros Activos</h5>
                        <h3 class="text-info">45,678</h3>
                        <small class="text-muted">Con trazabilidad completa</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle text-warning mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Brechas Detectadas</h5>
                        <h3 class="text-warning">3</h3>
                        <small class="text-muted">Requieren corrección</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Generation Panel -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header report-header">
                        <h5 class="mb-0">
                            <i class="fas fa-plus-circle me-2"></i>
                            Generar Nuevo Reporte de Trazabilidad
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="reportForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Reporte:</label>
                                    <select class="form-select" id="reportType" required>
                                        <option value="">Seleccionar tipo...</option>
                                        <option value="integrity">Reporte de Integridad de Cadena</option>
                                        <option value="performance">Análisis de Rendimiento</option>
                                        <option value="compliance">Cumplimiento Regulatorio</option>
                                        <option value="audit">Pista de Auditoría</option>
                                        <option value="break-analysis">Análisis de Brechas</option>
                                        <option value="summary">Resumen Ejecutivo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Período de Análisis:</label>
                                    <select class="form-select" id="analysisPeriod" required>
                                        <option value="">Seleccionar período...</option>
                                        <option value="daily">Últimas 24 horas</option>
                                        <option value="weekly">Última semana</option>
                                        <option value="monthly">Último mes</option>
                                        <option value="quarterly">Último trimestre</option>
                                        <option value="annually">Último año</option>
                                        <option value="custom">Período personalizado</option>
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
                                    <label class="form-label">Nivel de Detalle:</label>
                                    <select class="form-select" id="detailLevel">
                                        <option value="summary">Resumen</option>
                                        <option value="detailed">Detallado</option>
                                        <option value="comprehensive">Completo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Formato de Salida:</label>
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
                                            <input class="form-check-input" type="checkbox" id="formatCSV">
                                            <label class="form-check-label" for="formatCSV">CSV</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="formatJSON">
                                            <label class="form-check-label" for="formatJSON">JSON</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary" onclick="generateTraceabilityReport()">
                                        <i class="fas fa-cog me-2"></i>
                                        Generar Reporte
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="previewReport()">
                                        <i class="fas fa-eye me-2"></i>
                                        Vista Previa
                                    </button>
                                    <button type="button" class="btn btn-success" onclick="scheduleReport()">
                                        <i class="fas fa-clock me-2"></i>
                                        Programar Generación
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <!-- Compliance Status -->
                <div class="card compliance-section">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            Estado de Cumplimiento
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="performance-indicator excellent"></span>
                                <span><strong>DIGEMID:</strong> Cumpliendo</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="performance-indicator excellent"></span>
                                <span><strong>ISO 9001:</strong> Conforme</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="performance-indicator good"></span>
                                <span><strong>Buenas Prácticas:</strong> Activo</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="performance-indicator warning"></span>
                                <span><strong>USP <797>:</strong> Revisión requerida</span>
                            </div>
                        </div>
                        <div class="alert alert-success">
                            <small>
                                <i class="fas fa-check-circle me-1"></i>
                                Sistema de trazabilidad cumple con estándares internacionales
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
                            <i class="fas fa-chart-line me-2"></i>
                            Rendimiento de Trazabilidad
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-pie-chart me-2"></i>
                            Distribución de Estados
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Traceability Metrics -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-metrics me-2"></i>
                            Métricas Detalladas de Trazabilidad
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="traceability-metric">
                                    <h6>Completitud de Datos</h6>
                                    <h4 class="text-success">98.7%</h4>
                                    <small class="text-muted">Registros completos</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="traceability-metric">
                                    <h6>Precisión de Ubicación</h6>
                                    <h4 class="text-primary">99.1%</h4>
                                    <small class="text-muted">Ubicaciones exactas</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="traceability-metric">
                                    <h6>Velocidad de Consulta</h6>
                                    <h4 class="text-info">1.8s</h4>
                                    <small class="text-muted">Tiempo promedio</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="traceability-metric">
                                    <h6>Disponibilidad</h6>
                                    <h4 class="text-success">99.9%</h4>
                                    <small class="text-muted">Uptime del sistema</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chain Integrity Details -->
                        <div class="mt-4">
                            <h6>Detalle de Integridad por Eslabón:</h6>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="chain-integrity">
                                        <i class="fas fa-truck mb-2"></i>
                                        <h6>Recepción</h6>
                                        <h5>100%</h5>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="chain-integrity">
                                        <i class="fas fa-clipboard-check mb-2"></i>
                                        <h6>Inspección</h6>
                                        <h5>99.8%</h5>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="chain-integrity">
                                        <i class="fas fa-warehouse mb-2"></i>
                                        <h6>Almacén</h6>
                                        <h5>98.9%</h5>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="chain-integrity">
                                        <i class="fas fa-shopping-cart mb-2"></i>
                                        <h6>Venta</h6>
                                        <h5>99.5%</h5>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="chain-integrity">
                                        <i class="fas fa-pills mb-2"></i>
                                        <h6>Dispensación</h6>
                                        <h5>99.2%</h5>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="break-point">
                                        <i class="fas fa-exclamation-triangle mb-2"></i>
                                        <h6>Entrega Final</h6>
                                        <h5>87.3%</h5>
                                        <small>Punto crítico</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <!-- Audit Trail -->
                <div class="card audit-trail">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            Pista de Auditoría Reciente
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item mb-3">
                                <small class="text-muted">24/10/2024 14:30</small><br>
                                <strong>Reporte generado:</strong> Integridad de Cadena<br>
                                <small class="text-info">Por: Sistema automático</small>
                            </div>
                            <div class="timeline-item mb-3">
                                <small class="text-muted">24/10/2024 10:15</small><br>
                                <strong>Brecha detectada:</strong> Lote AMO240156<br>
                                <small class="text-warning">Pendiente corrección</small>
                            </div>
                            <div class="timeline-item mb-3">
                                <small class="text-muted">23/10/2024 16:45</small><br>
                                <strong>Validación exitosa:</strong> Auditoría DIGEMID<br>
                                <small class="text-success">Sin observaciones</small>
                            </div>
                            <div class="timeline-item mb-3">
                                <small class="text-muted">23/10/2024 09:00</small><br>
                                <strong>Actualización:</strong> Sistema de trazabilidad<br>
                                <small class="text-info">Versión 2.4.1</small>
                            </div>
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
                            Historial de Reportes de Trazabilidad
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="traceabilityReportsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha Generación</th>
                                        <th>Tipo Reporte</th>
                                        <th>Período Analizado</th>
                                        <th>Registros</th>
                                        <th>Integridad</th>
                                        <th>Estado</th>
                                        <th>Tamaño</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>24/10/2024 14:30</td>
                                        <td>Integridad de Cadena</td>
                                        <td>Octubre 2024</td>
                                        <td>1,247</td>
                                        <td>99.2%</td>
                                        <td><span class="badge bg-success">Completado</span></td>
                                        <td>2.3 MB</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewReport(1234)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="downloadReport(1234)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="shareReport(1234)">
                                                <i class="fas fa-share"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>23/10/2024 16:15</td>
                                        <td>Análisis de Rendimiento</td>
                                        <td>Q3 2024</td>
                                        <td>3,892</td>
                                        <td>98.7%</td>
                                        <td><span class="badge bg-success">Completado</span></td>
                                        <td>4.7 MB</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewReport(1233)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="downloadReport(1233)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="shareReport(1233)">
                                                <i class="fas fa-share"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>22/10/2024 11:45</td>
                                        <td>Cumplimiento Regulatorio</td>
                                        <td>Septiembre 2024</td>
                                        <td>2,156</td>
                                        <td>97.9%</td>
                                        <td><span class="badge bg-warning">Con Observaciones</span></td>
                                        <td>1.8 MB</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewReport(1232)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="downloadReport(1232)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="reviewObservations(1232)">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>20/10/2024 09:30</td>
                                        <td>Pista de Auditoría</td>
                                        <td>Semana 42-2024</td>
                                        <td>567</td>
                                        <td>99.8%</td>
                                        <td><span class="badge bg-success">Completado</span></td>
                                        <td>0.9 MB</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewReport(1231)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="downloadReport(1231)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="shareReport(1231)">
                                                <i class="fas fa-share"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>18/10/2024 15:20</td>
                                        <td>Análisis de Brechas</td>
                                        <td>Septiembre 2024</td>
                                        <td>-</td>
                                        <td>85.2%</td>
                                        <td><span class="badge bg-danger">Crítico</span></td>
                                        <td>1.2 MB</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewReport(1230)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="downloadReport(1230)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="urgentAction(1230)">
                                                <i class="fas fa-exclamation"></i>
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
                <div class="card export-panel">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-download me-2"></i>
                            Exportación y Respaldo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Exportaciones Automáticas:</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="autoDaily" checked>
                                    <label class="form-check-label" for="autoDaily">
                                        Respaldo diario (23:00)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="autoWeekly">
                                    <label class="form-check-label" for="autoWeekly">
                                        Reporte semanal
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="autoMonthly" checked>
                                    <label class="form-check-label" for="autoMonthly">
                                        Análisis mensual
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Acciones Manuales:</h6>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary btn-sm" onclick="exportAllData()">
                                        <i class="fas fa-database me-2"></i>
                                        Exportar Todo
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" onclick="createBackup()">
                                        <i class="fas fa-save me-2"></i>
                                        Crear Respaldo
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="syncExternal()">
                                        <i class="fas fa-sync me-2"></i>
                                        Sincronizar Externo
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
                            Alertas y Notificaciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Crítico</h6>
                            <p class="mb-1"><strong>Brecha detectada:</strong> Lote AMO240156</p>
                            <small>Integridad de cadena comprometida - Acción requerida</small>
                        </div>
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-clock me-2"></i>Advertencia</h6>
                            <p class="mb-1"><strong>Reporte programado:</strong> Análisis mensual</p>
                            <small>Se generará automáticamente en 2 días</small>
                        </div>
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle me-2"></i>Información</h6>
                            <p class="mb-1"><strong>Respaldos automáticos:</strong> Funcionando correctamente</p>
                            <small>Último respaldo: 23/10/2024 23:00</small>
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
            $('#traceabilityReportsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json"
                },
                "order": [[0, "desc"]],
                "pageLength": 10
            });

            // Initialize Select2
            $('.form-select').select2({
                placeholder: "Seleccionar...",
                allowClear: true
            });

            // Show/hide custom period
            $('#analysisPeriod').change(function() {
                if ($(this).val() === 'custom') {
                    $('#customPeriod').show();
                } else {
                    $('#customPeriod').hide();
                }
            });

            // Initialize Charts
            initializeCharts();
        });

        function initializeCharts() {
            // Performance Chart
            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            new Chart(performanceCtx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct'],
                    datasets: [{
                        label: 'Integridad de Cadena (%)',
                        data: [95.2, 96.8, 97.1, 97.5, 98.2, 98.5, 98.9, 99.1, 99.0, 99.2],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Velocidad de Consulta (segundos)',
                        data: [3.2, 2.9, 2.7, 2.5, 2.3, 2.1, 2.0, 1.9, 1.8, 1.8],
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Evolución del Rendimiento 2024'
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Integridad (%)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Tiempo (segundos)'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });

            // Status Distribution Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Completos', 'Parciales', 'Pendientes', 'Con Brechas'],
                    datasets: [{
                        data: [42567, 2890, 156, 65],
                        backgroundColor: [
                            '#28a745',
                            '#ffc107',
                            '#17a2b8',
                            '#dc3545'
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
                            text: 'Estado de Registros de Trazabilidad'
                        }
                    }
                }
            });
        }

        function generateTraceabilityReport() {
            const reportType = document.getElementById('reportType').value;
            const analysisPeriod = document.getElementById('analysisPeriod').value;
            
            if (!reportType || !analysisPeriod) {
                Swal.fire('Error', 'Por favor complete todos los campos requeridos', 'error');
                return;
            }

            Swal.fire({
                title: 'Generando Reporte de Trazabilidad',
                text: 'Analizando datos y generando reporte integral...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Reporte Generado',
                    text: 'El reporte de trazabilidad se ha generado exitosamente',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }, 4000);
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
                title: 'Programar Generación',
                text: 'Seleccione la frecuencia para la generación automática:',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Diario',
                cancelButtonText: 'Cancelar',
                showDenyButton: true,
                denyButtonText: 'Semanal'
            }).then((result) => {
                let frequency = '';
                if (result.isConfirmed) frequency = 'diario';
                else if (result.isDenied) frequency = 'semanal';

                if (frequency) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reporte Programado',
                        text: `Generación ${frequency} configurada exitosamente`,
                        timer: 2000,
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
                denyButtonText: 'JSON'
            }).then((result) => {
                let format = '';
                if (result.isConfirmed) format = 'PDF';
                else if (result.isDenied) format = 'JSON';
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

        function shareReport(reportId) {
            Swal.fire({
                title: 'Compartir Reporte',
                text: '¿Cómo desea compartir este reporte?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Email',
                cancelButtonText: 'Cancelar',
                showDenyButton: true,
                denyButtonText: 'Enlace'
            }).then((result) => {
                let method = '';
                if (result.isConfirmed) method = 'email';
                else if (result.isDenied) method = 'enlace';

                if (method) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reporte Compartido',
                        text: `Reporte compartido por ${method} exitosamente`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }

        function reviewObservations(reportId) {
            Swal.fire({
                icon: 'warning',
                title: 'Observaciones del Reporte',
                text: 'El reporte contiene observaciones que requieren revisión y corrección',
                timer: 3000,
                showConfirmButton: false
            });
        }

        function urgentAction(reportId) {
            Swal.fire({
                title: 'Acción Urgente Requerida',
                text: 'Este reporte indica problemas críticos que requieren atención inmediata',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Ver Detalles',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Detalles del Problema',
                        text: 'Abriendo vista detallada de las brechas detectadas',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }

        function exportAllData() {
            Swal.fire({
                title: 'Exportar Todos los Datos',
                text: 'Esta operación puede tomar varios minutos. ¿Desea continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, exportar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Exportando datos...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Exportación Completada',
                            text: 'Todos los datos de trazabilidad han sido exportados',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }, 8000);
                }
            });
        }

        function createBackup() {
            Swal.fire({
                title: 'Crear Respaldo',
                text: 'Creando respaldo completo de la base de datos de trazabilidad...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Respaldo Creado',
                    text: 'Respaldo completado y almacenado securely',
                    timer: 3000,
                    showConfirmButton: false
                });
            }, 5000);
        }

        function syncExternal() {
            Swal.fire({
                title: 'Sincronización Externa',
                text: 'Iniciando sincronización con sistemas externos...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Sincronización Completada',
                    text: 'Datos sincronizados con todos los sistemas externos',
                    timer: 3000,
                    showConfirmButton: false
                });
            }, 4000);
        }
    </script>
</body>
</html>