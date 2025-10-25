¿<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trazabilidad de Productos - SIFANO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .traceability-flow {
            background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
            border: 2px solid #2196f3;
            border-radius: 0.5rem;
        }
        .flow-step {
            background: white;
            border: 2px solid #2196f3;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 0.5rem;
            text-align: center;
            position: relative;
        }
        .flow-step::after {
            content: '→';
            position: absolute;
            right: -1.5rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5rem;
            color: #2196f3;
        }
        .flow-step:last-child::after {
            content: '';
        }
        .product-status {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: bold;
            font-size: 0.875rem;
        }
        .status-received { background-color: #d4edda; color: #155724; }
        .status-stored { background-color: #cce5ff; color: #004085; }
        .status-dispensed { background-color: #d1ecf1; color: #0c5460; }
        .status-delivered { background-color: #f8d7da; color: #721c24; }
        .alert-timeline {
            border-left: 4px solid #ffc107;
            background-color: #fffbf0;
        }
        .traceability-qr {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
        }
        .chain-link {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #6c757d, #495057);
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            color: white;
            margin: 0 5px;
        }
        .heatmap-cell {
            width: 20px;
            height: 20px;
            display: inline-block;
            margin: 1px;
            border-radius: 2px;
        }
        .heat-low { background-color: #28a745; }
        .heat-medium { background-color: #ffc107; }
        .heat-high { background-color: #fd7e14; }
        .heat-critical { background-color: #dc3545; }
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
                            <i class="fas fa-link me-2"></i>
                            Trazabilidad de Productos Farmacéuticos
                        </h2>
                        <p class="text-muted mb-0">Seguimiento completo desde proveedor hasta paciente</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary px-3 py-2">
                            <i class="fas fa-tracking me-2"></i>
                            Trazabilidad Integral
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-boxes text-success mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Lotes Rastreables</h5>
                        <h3 class="text-success">1,247</h3>
                        <small class="text-muted">Con trazabilidad completa</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-route text-info mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">En Proceso</h5>
                        <h3 class="text-info">89</h3>
                        <small class="text-muted">Productos en seguimiento</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle text-warning mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Alertas</h5>
                        <h3 class="text-warning">12</h3>
                        <small class="text-muted">Requieren atención</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line text-primary mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Cobertura</h5>
                        <h3 class="text-primary">98.5%</h3>
                        <small class="text-muted">Trazabilidad implementada</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-search me-2"></i>
                            Búsqueda de Productos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Buscar por:</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="searchType" id="product-name" checked>
                                    <label class="btn btn-outline-primary" for="product-name">Producto</label>
                                    
                                    <input type="radio" class="btn-check" name="searchType" id="lot-number">
                                    <label class="btn btn-outline-primary" for="lot-number">N° Lote</label>
                                    
                                    <input type="radio" class="btn-check" name="searchType" id="barcode">
                                    <label class="btn btn-outline-primary" for="barcode">Código Barras</label>
                                    
                                    <input type="radio" class="btn-check" name="searchType" id="supplier">
                                    <label class="btn btn-outline-primary" for="supplier">Proveedor</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Término de búsqueda:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchTerm" placeholder="Ingrese término de búsqueda">
                                    <button class="btn btn-primary" type="button" onclick="searchProduct()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-filter me-2"></i>
                            Filtros Rápidos
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-success btn-sm" onclick="filterByStatus('received')">
                                <i class="fas fa-inbox me-2"></i>Recibidos
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="filterByStatus('stored')">
                                <i class="fas fa-warehouse me-2"></i>En Almacén
                            </button>
                            <button class="btn btn-outline-warning btn-sm" onclick="filterByStatus('dispensed')">
                                <i class="fas fa-pills me-2"></i>Dispensados
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="filterByStatus('recalled')">
                                <i class="fas fa-exclamation-triangle me-2"></i>Recall
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Traceability Flow Visualization -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card traceability-flow">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-project-diagram me-2"></i>
                            Flujo de Trazabilidad - Producto: Ibuprofeno 400mg
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 flow-step">
                                <i class="fas fa-truck text-primary mb-2" style="font-size: 2rem;"></i>
                                <h6>Recepción</h6>
                                <small>15/10/2024 09:30</small><br>
                                <small class="text-muted">Lote: IBU240015</small><br>
                                <span class="badge bg-success">Completado</span>
                            </div>
                            <div class="col-md-2 flow-step">
                                <i class="fas fa-clipboard-check text-info mb-2" style="font-size: 2rem;"></i>
                                <h6>Inspección</h6>
                                <small>15/10/2024 10:15</small><br>
                                <small class="text-muted">Control Calidad</small><br>
                                <span class="badge bg-success">Aprobado</span>
                            </div>
                            <div class="col-md-2 flow-step">
                                <i class="fas fa-warehouse text-warning mb-2" style="font-size: 2rem;"></i>
                                <h6>Almacén</h6>
                                <small>15/10/2024 14:00</small><br>
                                <small class="text-muted">Ubicación: A-15-B</small><br>
                                <span class="badge bg-primary">Activo</span>
                            </div>
                            <div class="col-md-2 flow-step">
                                <i class="fas fa-shopping-cart text-success mb-2" style="font-size: 2rem;"></i>
                                <h6>Venta</h6>
                                <small>20/10/2024 16:45</small><br>
                                <small class="text-muted">Pedido: V-240156</small><br>
                                <span class="badge bg-success">Completado</span>
                            </div>
                            <div class="col-md-2 flow-step">
                                <i class="fas fa-user-md text-info mb-2" style="font-size: 2rem;"></i>
                                <h6>Dispensación</h6>
                                <small>21/10/2024 10:20</small><br>
                                <small class="text-muted">Farmacia Central</small><br>
                                <span class="badge bg-success">Entregado</span>
                            </div>
                            <div class="col-md-2 flow-step">
                                <i class="fas fa-home text-primary mb-2" style="font-size: 2rem;"></i>
                                <h6>Paciente</h6>
                                <small>21/10/2024 11:00</small><br>
                                <small class="text-muted">Entrega Final</small><br>
                                <span class="badge bg-success">Confirmado</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Product Tracking -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-box me-2"></i>
                            Seguimiento de Producto Actual
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Información del Producto</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Nombre:</strong></td>
                                        <td>Ibuprofeno 400mg</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Lote:</strong></td>
                                        <td>IBU240015</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Registro Sanitario:</strong></td>
                                        <td>RN2015001234</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Proveedor:</strong></td>
                                        <td>Laboratorios DELPE</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Fecha Vencimiento:</strong></td>
                                        <td>15/10/2026</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-success">Estado Actual</h6>
                                <div class="mb-3">
                                    <span class="product-status status-delivered">Entregado al Paciente</span>
                                </div>
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-check-circle me-2"></i>Trazabilidad Completa</h6>
                                    <p class="mb-0">Este producto ha completado exitosamente todo el proceso de trazabilidad desde el proveedor hasta el paciente final.</p>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-primary btn-sm me-2" onclick="viewTraceabilityDetails()">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Ver Detalles Completos
                                    </button>
                                    <button class="btn btn-success btn-sm" onclick="generateTraceabilityReport()">
                                        <i class="fas fa-file-export me-1"></i>
                                        Generar Reporte
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <!-- QR Code and Chain Information -->
                <div class="traceability-qr mb-3">
                    <h6><i class="fas fa-qrcode me-2"></i>Código QR de Trazabilidad</h6>
                    <div class="bg-white p-3 rounded mb-2">
                        <div style="width: 120px; height: 120px; background: #000; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
                            QR CODE<br>IBU240015
                        </div>
                    </div>
                    <small>Escanee para verificar trazabilidad</small>
                </div>

                <!-- Chain Links -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-link me-2"></i>
                            Enlaces de Cadena
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="chain-link"><i class="fas fa-truck"></i></div>
                        <div class="chain-link"><i class="fas fa-check"></i></div>
                        <div class="chain-link"><i class="fas fa-warehouse"></i></div>
                        <div class="chain-link"><i class="fas fa-shopping-cart"></i></div>
                        <div class="chain-link"><i class="fas fa-pills"></i></div>
                        <div class="chain-link"><i class="fas fa-user"></i></div>
                        <br><br>
                        <small class="text-muted">6 de 6 enlaces verificados</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>
                            Tabla de Productos con Trazabilidad
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="traceabilityTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Lote</th>
                                        <th>Proveedor</th>
                                        <th>Estado</th>
                                        <th>Ubicación</th>
                                        <th>Fecha Recepción</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Progreso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <strong>Ibuprofeno 400mg</strong><br>
                                            <small class="text-muted">RN2015001234</small>
                                        </td>
                                        <td>IBU240015</td>
                                        <td>Laboratorios DELPE</td>
                                        <td><span class="product-status status-delivered">Entregado</span></td>
                                        <td>Paciente Final</td>
                                        <td>15/10/2024</td>
                                        <td>15/10/2026</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" style="width: 100%">100%</div>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewProduct('IBU240015')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="trackProduct('IBU240015')">
                                                <i class="fas fa-route"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="generateReport('IBU240015')">
                                                <i class="fas fa-file-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong>Paracetamol 500mg</strong><br>
                                            <small class="text-muted">RN2015005678</small>
                                        </td>
                                        <td>PAR240089</td>
                                        <td>Farmacéutica ABC</td>
                                        <td><span class="product-status status-dispensed">Dispensado</span></td>
                                        <td>Farmacia Central</td>
                                        <td>18/10/2024</td>
                                        <td>18/10/2026</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-info" style="width: 85%">85%</div>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewProduct('PAR240089')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="trackProduct('PAR240089')">
                                                <i class="fas fa-route"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="generateReport('PAR240089')">
                                                <i class="fas fa-file-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong>Amoxicilina 500mg</strong><br>
                                            <small class="text-muted">RN2015009012</small>
                                        </td>
                                        <td>AMO240156</td>
                                        <td>Industrias XYZ</td>
                                        <td><span class="product-status status-stored">En Almacén</span></td>
                                        <td>A-12-C</td>
                                        <td>22/10/2024</td>
                                        <td>22/10/2026</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-primary" style="width: 40%">40%</div>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewProduct('AMO240156')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="trackProduct('AMO240156')">
                                                <i class="fas fa-route"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="generateReport('AMO240156')">
                                                <i class="fas fa-file-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong>Omeprazol 20mg</strong><br>
                                            <small class="text-muted">RN2015003456</small>
                                        </td>
                                        <td>OME240078</td>
                                        <td>Laboratorios DELPE</td>
                                        <td><span class="product-status status-received">Recibido</span></td>
                                        <td>Área Recepción</td>
                                        <td>24/10/2024</td>
                                        <td>24/10/2026</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-warning" style="width: 25%">25%</div>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewProduct('OME240078')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="trackProduct('OME240078')">
                                                <i class="fas fa-route"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="generateReport('OME240078')">
                                                <i class="fas fa-file-alt"></i>
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

        <!-- Alerts and Notifications -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card alert-timeline">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-bell me-2"></i>
                            Alertas de Trazabilidad
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Producto Pendiente de Inspección</h6>
                            <p class="mb-1"><strong>Lote: OME240078</strong> - Omeprazol 20mg</p>
                            <small>Tiempo transcurrido: 6 horas desde recepción</small>
                        </div>
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-clock me-2"></i>Productos Próximos a Vencer</h6>
                            <p class="mb-1"><strong>3 productos</strong> vencen en los próximos 30 días</p>
                            <small>Requieren priorización en dispensación</small>
                        </div>
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Recall de Fabricante</h6>
                            <p class="mb-1"><strong>Lote: LOT240123</strong> - Amoxicilina</p>
                            <small>Verificar inventario y contactar clientes</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-area me-2"></i>
                            Mapa de Calor - Actividad
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Últimos 7 días:</h6>
                        <div class="row text-center">
                            <div class="col-12 mb-2">
                                <small class="text-muted">Lun</small>
                                <div>
                                    <div class="heatmap-cell heat-high"></div>
                                    <div class="heatmap-cell heat-medium"></div>
                                    <div class="heatmap-cell heat-low"></div>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <small class="text-muted">Mar</small>
                                <div>
                                    <div class="heatmap-cell heat-critical"></div>
                                    <div class="heatmap-cell heat-high"></div>
                                    <div class="heatmap-cell heat-medium"></div>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <small class="text-muted">Mié</small>
                                <div>
                                    <div class="heatmap-cell heat-high"></div>
                                    <div class="heatmap-cell heat-high"></div>
                                    <div class="heatmap-cell heat-low"></div>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <small class="text-muted">Jue</small>
                                <div>
                                    <div class="heatmap-cell heat-medium"></div>
                                    <div class="heatmap-cell heat-low"></div>
                                    <div class="heatmap-cell heat-low"></div>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <small class="text-muted">Vie</small>
                                <div>
                                    <div class="heatmap-cell heat-high"></div>
                                    <div class="heatmap-cell heat-high"></div>
                                    <div class="heatmap-cell heat-medium"></div>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <small class="text-muted">Sáb</small>
                                <div>
                                    <div class="heatmap-cell heat-low"></div>
                                    <div class="heatmap-cell heat-low"></div>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <small class="text-muted">Dom</small>
                                <div>
                                    <div class="heatmap-cell heat-low"></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <span class="heatmap-cell heat-low"></span> Baja |
                                <span class="heatmap-cell heat-medium"></span> Media |
                                <span class="heatmap-cell heat-high"></span> Alta |
                                <span class="heatmap-cell heat-critical"></span> Crítica
                            </small>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#traceabilityTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json"
                },
                "order": [[5, "desc"]], // Sort by reception date
                "pageLength": 10
            });
        });

        function searchProduct() {
            const searchType = document.querySelector('input[name="searchType"]:checked').id;
            const searchTerm = document.getElementById('searchTerm').value;
            
            if (!searchTerm) {
                Swal.fire('Error', 'Por favor ingrese un término de búsqueda', 'error');
                return;
            }

            Swal.fire({
                title: 'Buscando...',
                text: 'Consultando base de datos de trazabilidad',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Producto Encontrado',
                    text: 'Información de trazabilidad cargada',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 2000);
        }

        function filterByStatus(status) {
            Swal.fire({
                icon: 'info',
                title: 'Filtrando Productos',
                text: `Mostrando productos con estado: ${status}`,
                timer: 1500,
                showConfirmButton: false
            });
        }

        function viewTraceabilityDetails() {
            Swal.fire({
                icon: 'info',
                title: 'Detalles de Trazabilidad',
                text: 'Abriendo vista detallada del producto...',
                timer: 2000,
                showConfirmButton: false
            });
        }

        function generateTraceabilityReport() {
            Swal.fire({
                title: 'Generar Reporte',
                text: '¿Qué tipo de reporte desea generar?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Completo',
                cancelButtonText: 'Resumen',
                showDenyButton: true,
                denyButtonText: 'Detallado'
            }).then((result) => {
                let reportType = '';
                if (result.isConfirmed) reportType = 'Completo';
                else if (result.isDenied) reportType = 'Detallado';
                else reportType = 'Resumen';

                Swal.fire({
                    title: 'Generando Reporte',
                    text: `Creando reporte ${reportType}...`,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reporte Generado',
                        text: `Reporte ${reportType} descargado exitosamente`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 3000);
            });
        }

        function viewProduct(lotNumber) {
            Swal.fire({
                icon: 'info',
                title: 'Ver Producto',
                text: `Mostrando detalles del lote ${lotNumber}...`,
                timer: 2000,
                showConfirmButton: false
            });
        }

        function trackProduct(lotNumber) {
            Swal.fire({
                title: 'Rastrear Producto',
                text: `Iniciando seguimiento del lote ${lotNumber}`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Seguimiento Activado',
                    text: 'Producto agregado a seguimiento en tiempo real',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 2500);
        }

        function generateReport(lotNumber) {
            Swal.fire({
                title: 'Generar Reporte Individual',
                text: `¿Generar reporte de trazabilidad para el lote ${lotNumber}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, generar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Generando reporte...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Reporte Generado',
                            text: 'Documento descargado exitosamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }, 2000);
                }
            });
        }
    </script>
</body>
</html>