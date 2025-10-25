<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda Avanzada de Trazabilidad - SIFANO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .advanced-search-panel {
            background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
            border: 2px solid #2196f3;
            border-radius: 0.5rem;
        }
        .search-tabs {
            border-bottom: 2px solid #dee2e6;
        }
        .search-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
        }
        .search-tabs .nav-link.active {
            color: #2196f3;
            border-bottom: 3px solid #2196f3;
            background-color: transparent;
        }
        .result-card {
            border-left: 5px solid #28a745;
            background-color: #f8fff9;
            margin-bottom: 1rem;
        }
        .traceability-step {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin: 0.5rem 0;
            position: relative;
        }
        .traceability-step::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translateY(-50%);
            width: 12px;
            height: 12px;
            background: #28a745;
            border-radius: 50%;
            border: 2px solid white;
        }
        .traceability-step.pending::before {
            background: #ffc107;
        }
        .traceability-step.failed::before {
            background: #dc3545;
        }
        .batch-timeline {
            border-left: 3px solid #007bff;
            padding-left: 1rem;
            margin-left: 1rem;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.25rem;
            top: 0.25rem;
            width: 10px;
            height: 10px;
            background: #007bff;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #007bff;
        }
        .location-map {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .alert-similarity {
            background: linear-gradient(135deg, #fff3cd 0%, #ffffff 100%);
            border: 2px solid #ffc107;
        }
        .search-stats {
            background: linear-gradient(135deg, #d4edda 0%, #ffffff 100%);
            border: 2px solid #28a745;
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
                            <i class="fas fa-search-plus me-2"></i>
                            Búsqueda Avanzada de Trazabilidad
                        </h2>
                        <p class="text-muted mb-0">Herramientas especializadas para localización y seguimiento de productos</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-info px-3 py-2">
                            <i class="fas fa-microscope me-2"></i>
                            Búsqueda Forense
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-database text-success mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Registros Totales</h5>
                        <h3 class="text-success">45,678</h3>
                        <small class="text-muted">En base de datos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-search text-primary mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Búsquedas Hoy</h5>
                        <h3 class="text-primary">127</h3>
                        <small class="text-muted">Consultas realizadas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-clock text-warning mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Tiempo Promedio</h5>
                        <h3 class="text-warning">2.3s</h3>
                        <small class="text-muted">Respuesta de consulta</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-bullseye text-info mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Precisión</h5>
                        <h3 class="text-info">99.2%</h3>
                        <small class="text-muted">Resultados exactos</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Search Panel -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card advanced-search-panel">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>
                            Panel de Búsqueda Avanzada
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Search Tabs -->
                        <ul class="nav nav-tabs search-tabs mb-4" id="searchTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="product-tab" data-bs-toggle="tab" data-bs-target="#product-search" type="button" role="tab">
                                    <i class="fas fa-pills me-2"></i>Producto
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="batch-tab" data-bs-toggle="tab" data-bs-target="#batch-search" type="button" role="tab">
                                    <i class="fas fa-barcode me-2"></i>Lote
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="supplier-tab" data-bs-toggle="tab" data-bs-target="#supplier-search" type="button" role="tab">
                                    <i class="fas fa-truck me-2"></i>Proveedor
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="location-tab" data-bs-toggle="tab" data-bs-target="#location-search" type="button" role="tab">
                                    <i class="fas fa-map-marker-alt me-2"></i>Ubicación
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="date-tab" data-bs-toggle="tab" data-bs-target="#date-search" type="button" role="tab">
                                    <i class="fas fa-calendar me-2"></i>Fecha
                                </button>
                            </li>
                        </ul>

                        <!-- Search Content -->
                        <div class="tab-content" id="searchTabsContent">
                            <!-- Product Search -->
                            <div class="tab-pane fade show active" id="product-search" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Nombre del Producto:</label>
                                        <input type="text" class="form-control" id="productName" placeholder="Ej: Ibuprofeno">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Registro Sanitario:</label>
                                        <input type="text" class="form-control" id="regSanitario" placeholder="Ej: RN2015001234">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Concentración:</label>
                                        <select class="form-select" id="concentration">
                                            <option value="">Todas las concentraciones</option>
                                            <option value="400mg">400mg</option>
                                            <option value="500mg">500mg</option>
                                            <option value="1g">1g</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Batch Search -->
                            <div class="tab-pane fade" id="batch-search" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Número de Lote:</label>
                                        <input type="text" class="form-control" id="batchNumber" placeholder="Ej: IBU240015">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Código de Barras:</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="barcode" placeholder="Escanear o ingresar código">
                                            <button class="btn btn-outline-secondary" type="button" onclick="scanBarcode()">
                                                <i class="fas fa-camera"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Supplier Search -->
                            <div class="tab-pane fade" id="supplier-search" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Proveedor:</label>
                                        <select class="form-select" id="supplier">
                                            <option value="">Todos los proveedores</option>
                                            <option value="laboratorios-delpe">Laboratorios DELPE</option>
                                            <option value="farmaceutica-abc">Farmacéutica ABC</option>
                                            <option value="industrias-xyz">Industrias XYZ</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">País de Origen:</label>
                                        <select class="form-select" id="country">
                                            <option value="">Todos los países</option>
                                            <option value="peru">Perú</option>
                                            <option value="argentina">Argentina</option>
                                            <option value="colombia">Colombia</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Location Search -->
                            <div class="tab-pane fade" id="location-search" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Almacén:</label>
                                        <select class="form-select" id="warehouse">
                                            <option value="">Todos los almacenes</option>
                                            <option value="principal">Almacén Principal</option>
                                            <option value="secundario">Almacén Secundario</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Zona:</label>
                                        <select class="form-select" id="zone">
                                            <option value="">Todas las zonas</option>
                                            <option value="a">Zona A</option>
                                            <option value="b">Zona B</option>
                                            <option value="c">Zona C</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Pasillo:</label>
                                        <select class="form-select" id="aisle">
                                            <option value="">Todos los pasillos</option>
                                            <option value="1">Pasillo 1</option>
                                            <option value="2">Pasillo 2</option>
                                            <option value="3">Pasillo 3</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Date Search -->
                            <div class="tab-pane fade" id="date-search" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Tipo de Fecha:</label>
                                        <select class="form-select" id="dateType">
                                            <option value="reception">Fecha Recepción</option>
                                            <option value="expiry">Fecha Vencimiento</option>
                                            <option value="sale">Fecha Venta</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Fecha Inicio:</label>
                                        <input type="date" class="form-control" id="dateFrom">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Fecha Fin:</label>
                                        <input type="date" class="form-control" id="dateTo">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Rango Rápido:</label>
                                        <select class="form-select" id="quickRange">
                                            <option value="">Seleccionar...</option>
                                            <option value="today">Hoy</option>
                                            <option value="week">Esta Semana</option>
                                            <option value="month">Este Mes</option>
                                            <option value="quarter">Este Trimestre</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Search Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <button class="btn btn-primary me-2" onclick="performSearch()">
                                    <i class="fas fa-search me-2"></i>
                                    Buscar
                                </button>
                                <button class="btn btn-outline-secondary me-2" onclick="clearSearch()">
                                    <i class="fas fa-eraser me-2"></i>
                                    Limpiar
                                </button>
                                <button class="btn btn-outline-info me-2" onclick="saveSearch()">
                                    <i class="fas fa-save me-2"></i>
                                    Guardar Búsqueda
                                </button>
                                <button class="btn btn-outline-success" onclick="exportResults()">
                                    <i class="fas fa-download me-2"></i>
                                    Exportar Resultados
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div class="row mb-4" id="searchResults" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Resultados de Búsqueda
                            <span class="badge bg-primary ms-2" id="resultsCount">0 resultados</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="resultsContainer">
                            <!-- Results will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Traceability View -->
        <div class="row mb-4" id="traceabilityDetails" style="display: none;">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-route me-2"></i>
                            Detalle de Trazabilidad - <span id="productTitle"></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="batch-timeline">
                            <div class="timeline-item">
                                <h6><i class="fas fa-truck text-primary me-2"></i>Recepción</h6>
                                <p><strong>Fecha:</strong> 15/10/2024 09:30</p>
                                <p><strong>Proveedor:</strong> Laboratorios DELPE</p>
                                <p><strong>Documento:</strong> Guía de Remisión 001-12345</p>
                                <p><strong>Cantidad:</strong> 5,000 unidades</p>
                                <p><strong>Estado:</strong> <span class="badge bg-success">Aceptado</span></p>
                            </div>
                            <div class="timeline-item">
                                <h6><i class="fas fa-clipboard-check text-info me-2"></i>Inspección de Calidad</h6>
                                <p><strong>Fecha:</strong> 15/10/2024 10:15</p>
                                <p><strong>Inspector:</strong> Ing. María González</p>
                                <p><strong>Resultado:</strong> Conforme especificaciones</p>
                                <p><strong>Certificado:</strong> CC-2024-5678</p>
                                <p><strong>Estado:</strong> <span class="badge bg-success">Aprobado</span></p>
                            </div>
                            <div class="timeline-item">
                                <h6><i class="fas fa-warehouse text-warning me-2"></i>Almacenamiento</h6>
                                <p><strong>Fecha:</strong> 15/10/2024 14:00</p>
                                <p><strong>Ubicación:</strong> Almacén Principal - Zona A - Pasillo 1</p>
                                <p><strong>Condiciones:</strong> 18-22°C, 45-65% HR</p>
                                <p><strong>Cantidad Almacenada:</strong> 5,000 unidades</p>
                                <p><strong>Estado:</strong> <span class="badge bg-primary">Almacenado</span></p>
                            </div>
                            <div class="timeline-item">
                                <h6><i class="fas fa-shopping-cart text-success me-2"></i>Movimiento a Venta</h6>
                                <p><strong>Fecha:</strong> 20/10/2024 16:45</p>
                                <p><strong>Motivo:</strong> Pedido de venta</p>
                                <p><strong>Cantidad Movida:</strong> 100 unidades</p>
                                <p><strong>Destino:</strong> Área de Dispensación</p>
                                <p><strong>Estado:</strong> <span class="badge bg-info">En Tránsito</span></p>
                            </div>
                            <div class="timeline-item">
                                <h6><i class="fas fa-pills text-primary me-2"></i>Dispensación</h6>
                                <p><strong>Fecha:</strong> 21/10/2024 10:20</p>
                                <p><strong>Farmacéutico:</strong> Dr. Carlos López</p>
                                <p><strong>Cliente:</strong> Farmacia Central</p>
                                <p><strong>Cantidad Dispensada:</strong> 100 unidades</p>
                                <p><strong>Estado:</strong> <span class="badge bg-success">Dispensado</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <!-- Location Map -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-map me-2"></i>
                            Mapa de Ubicación
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="location-map">
                            <i class="fas fa-map-marked-alt text-muted mb-2" style="font-size: 3rem;"></i>
                            <p class="text-muted">Vista interactiva del almacén</p>
                            <small class="text-muted">Almacén Principal - Zona A</small>
                        </div>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Información del Producto
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Lote:</strong></td>
                                <td>IBU240015</td>
                            </tr>
                            <tr>
                                <td><strong>Registro Sanitario:</strong></td>
                                <td>RN2015001234</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha Vencimiento:</strong></td>
                                <td>15/10/2026</td>
                            </tr>
                            <tr>
                                <td><strong>Cantidad Total:</strong></td>
                                <td>5,000 unidades</td>
                            </tr>
                            <tr>
                                <td><strong>Dispensado:</strong></td>
                                <td>100 unidades</td>
                            </tr>
                            <tr>
                                <td><strong>En Stock:</strong></td>
                                <td>4,900 unidades</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Similar Products Alert -->
        <div class="row mb-4" id="similarProducts" style="display: none;">
            <div class="col-12">
                <div class="card alert-similarity">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-search me-2"></i>
                            Productos Similares Encontrados
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="similarProductsContainer">
                            <!-- Similar products will be populated here -->
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.form-select').select2({
                placeholder: "Seleccionar...",
                allowClear: true
            });

            // Set default dates
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
            document.getElementById('dateFrom').value = thirtyDaysAgo.toISOString().split('T')[0];
            document.getElementById('dateTo').value = today.toISOString().split('T')[0];

            // Quick range selector
            document.getElementById('quickRange').addEventListener('change', function() {
                const range = this.value;
                const today = new Date();
                let fromDate = new Date();

                switch(range) {
                    case 'today':
                        fromDate = today;
                        break;
                    case 'week':
                        fromDate = new Date(today.getTime() - (7 * 24 * 60 * 60 * 1000));
                        break;
                    case 'month':
                        fromDate = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
                        break;
                    case 'quarter':
                        fromDate = new Date(today.getTime() - (90 * 24 * 60 * 60 * 1000));
                        break;
                }

                if (range) {
                    document.getElementById('dateFrom').value = fromDate.toISOString().split('T')[0];
                    document.getElementById('dateTo').value = today.toISOString().split('T')[0];
                }
            });
        });

        function scanBarcode() {
            Swal.fire({
                title: 'Escanear Código de Barras',
                text: 'Active la cámara para escanear el código',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Activar Cámara',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Código Escaneado',
                        text: 'IBU240015 - Producto detectado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        document.getElementById('barcode').value = 'IBU240015';
                    });
                }
            });
        }

        function performSearch() {
            const activeTab = document.querySelector('.nav-link.active').textContent.trim();
            
            Swal.fire({
                title: 'Realizando Búsqueda...',
                text: `Consultando base de datos por ${activeTab.toLowerCase()}...`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Búsqueda Completada',
                    text: 'Resultados encontrados y mostrados a continuación',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    displaySearchResults();
                });
            }, 3000);
        }

        function displaySearchResults() {
            document.getElementById('searchResults').style.display = 'block';
            document.getElementById('resultsCount').textContent = '3 resultados';
            
            const resultsContainer = document.getElementById('resultsContainer');
            resultsContainer.innerHTML = `
                <div class="result-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5>Ibuprofeno 400mg</h5>
                                <p class="text-muted">Lote: IBU240015 | Registro: RN2015001234</p>
                                <p><strong>Proveedor:</strong> Laboratorios DELPE | <strong>Fecha Recepción:</strong> 15/10/2024</p>
                                <p><strong>Ubicación:</strong> Almacén Principal - Zona A - Pasillo 1 | <strong>Stock:</strong> 4,900 unidades</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-success mb-2">Trazabilidad Completa</span><br>
                                <button class="btn btn-primary btn-sm" onclick="showTraceabilityDetails('IBU240015')">
                                    <i class="fas fa-route me-1"></i>
                                    Ver Trazabilidad
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="result-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5>Paracetamol 500mg</h5>
                                <p class="text-muted">Lote: PAR240089 | Registro: RN2015005678</p>
                                <p><strong>Proveedor:</strong> Farmacéutica ABC | <strong>Fecha Recepción:</strong> 18/10/2024</p>
                                <p><strong>Ubicación:</strong> Almacén Principal - Zona B - Pasillo 3 | <strong>Stock:</strong> 2,350 unidades</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-success mb-2">Trazabilidad Completa</span><br>
                                <button class="btn btn-primary btn-sm" onclick="showTraceabilityDetails('PAR240089')">
                                    <i class="fas fa-route me-1"></i>
                                    Ver Trazabilidad
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="result-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5>Amoxicilina 500mg</h5>
                                <p class="text-muted">Lote: AMO240156 | Registro: RN2015009012</p>
                                <p><strong>Proveedor:</strong> Industrias XYZ | <strong>Fecha Recepción:</strong> 22/10/2024</p>
                                <p><strong>Ubicación:</strong> Almacén Principal - Zona C - Pasillo 2 | <strong>Stock:</strong> 1,800 unidades</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-warning mb-2">Trazabilidad Parcial</span><br>
                                <button class="btn btn-primary btn-sm" onclick="showTraceabilityDetails('AMO240156')">
                                    <i class="fas fa-route me-1"></i>
                                    Ver Trazabilidad
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function showTraceabilityDetails(lotNumber) {
            document.getElementById('productTitle').textContent = `Lote: ${lotNumber}`;
            document.getElementById('traceabilityDetails').style.display = 'block';
            document.getElementById('traceabilityDetails').scrollIntoView({ behavior: 'smooth' });
        }

        function clearSearch() {
            document.querySelectorAll('.form-control, .form-select').forEach(input => {
                if (input.type === 'text' || input.type === 'date') {
                    input.value = '';
                } else {
                    input.selectedIndex = 0;
                }
            });
            
            // Reset date defaults
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
            document.getElementById('dateFrom').value = thirtyDaysAgo.toISOString().split('T')[0];
            document.getElementById('dateTo').value = today.toISOString().split('T')[0];

            Swal.fire({
                icon: 'success',
                title: 'Búsqueda Limpiada',
                text: 'Todos los campos han sido restablecidos',
                timer: 1500,
                showConfirmButton: false
            });
        }

        function saveSearch() {
            Swal.fire({
                title: 'Guardar Búsqueda',
                text: '¿Desea guardar esta configuración de búsqueda?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Nombre de la Búsqueda',
                        input: 'text',
                        inputPlaceholder: 'Ingrese un nombre para esta búsqueda',
                        showCancelButton: true,
                        confirmButtonText: 'Guardar',
                        cancelButtonText: 'Cancelar',
                        preConfirm: (name) => {
                            if (!name) {
                                Swal.showValidationMessage('Debe ingresar un nombre');
                                return false;
                            }
                            return name;
                        }
                    }).then((nameResult) => {
                        if (nameResult.isConfirmed) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Búsqueda Guardada',
                                text: `La búsqueda "${nameResult.value}" ha sido guardada exitosamente`,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });
        }

        function exportResults() {
            Swal.fire({
                title: 'Exportar Resultados',
                text: '¿En qué formato desea exportar los resultados?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Excel',
                cancelButtonText: 'PDF',
                showDenyButton: true,
                denyButtonText: 'CSV'
            }).then((result) => {
                let format = '';
                if (result.isConfirmed) format = 'Excel';
                else if (result.isDenied) format = 'CSV';
                else format = 'PDF';

                Swal.fire({
                    title: 'Exportando...',
                    text: `Generando archivo ${format}...`,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Exportación Completada',
                        text: `Archivo ${format} descargado exitosamente`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 3000);
            });
        }
    </script>
</body>
</html>