<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta de Medicamentos Controlados - SIFANO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .prescription-validation {
            border-left: 4px solid #dc3545;
            background-color: #fff5f5;
        }
        .controlled-substance-card {
            border: 2px solid #dc3545;
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.1);
        }
        .security-badge {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
        }
        .patient-verification {
            border: 2px solid #28a745;
            background-color: #f8fff9;
        }
        .prescription-status {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: bold;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .status-dispensed { background-color: #d1ecf1; color: #0c5460; }
        .alert-icon {
            font-size: 2rem;
            margin-right: 0.5rem;
        }
        .dispensing-progress {
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
        }
        .dispensing-progress-bar {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
        }
        .dual-custody-indicator {
            background: linear-gradient(90deg, #ffc107, #ffb300);
            color: #000;
            font-weight: bold;
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
                            <i class="fas fa-shield-alt me-2"></i>
                            Venta de Medicamentos Controlados
                        </h2>
                        <p class="text-muted mb-0">Proceso de dispensación con validación de recetas médicas y seguridad regulatoria</p>
                    </div>
                    <div class="text-end">
                        <span class="badge security-badge px-3 py-2">
                            <i class="fas fa-lock me-1"></i>
                            Sustancia Controlada
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-prescription-bottle-alt text-warning mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Recetas Pendientes</h5>
                        <h3 class="text-warning">12</h3>
                        <small class="text-muted">Por validar hoy</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Dispensaciones</h5>
                        <h3 class="text-success">8</h3>
                        <small class="text-muted">Completadas hoy</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle text-danger mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Alertas</h5>
                        <h3 class="text-danger">3</h3>
                        <small class="text-muted">Requieren atención</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-file-invoice text-info mb-2" style="font-size: 2rem;"></i>
                        <h5 class="card-title">Reportes DIGEMID</h5>
                        <h3 class="text-info">0</h3>
                        <small class="text-muted">Pendientes envío</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patient and Prescription Search -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-search me-2"></i>
                            Búsqueda de Paciente y Receta
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Buscar por:</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="searchType" id="dni" checked>
                                    <label class="btn btn-outline-primary" for="dni">DNI</label>
                                    
                                    <input type="radio" class="btn-check" name="searchType" id="name">
                                    <label class="btn btn-outline-primary" for="name">Nombre</label>
                                    
                                    <input type="radio" class="btn-check" name="searchType" id="prescription">
                                    <label class="btn btn-outline-primary" for="prescription">N° Receta</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Término de búsqueda:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchTerm" placeholder="Ingrese DNI, nombre o número de receta">
                                    <button class="btn btn-primary" type="button" onclick="searchPatient()">
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
                            <i class="fas fa-user-shield me-2"></i>
                            Estado de Verificación
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success me-2">
                                <i class="fas fa-check"></i>
                            </span>
                            <span>Farmacéutico Verificado</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-warning me-2">
                                <i class="fas fa-clock"></i>
                            </span>
                            <span>Receta Pendiente Validación</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-info me-2">
                                <i class="fas fa-lock"></i>
                            </span>
                            <span>Control Dual Activo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Transaction -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card controlled-substance-card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-prescription-bottle-alt me-2"></i>
                            Transacción Actual - Medicamento Controlado
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="patient-verification p-3 rounded mb-3">
                                    <h6 class="text-success">
                                        <i class="fas fa-user-check me-2"></i>
                                        Paciente Verificado
                                    </h6>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <strong>Nombre:</strong> Juan Pérez García<br>
                                            <strong>DNI:</strong> 12345678<br>
                                            <strong>Teléfono:</strong> +51 987 654 321
                                        </div>
                                        <div class="col-sm-6">
                                            <strong>Dirección:</strong> Av. Principal 123<br>
                                            <strong>Fecha Nacimiento:</strong> 15/03/1980<br>
                                            <strong>Edad:</strong> 43 años
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="prescription-validation p-3 rounded">
                                    <h6 class="text-danger">
                                        <i class="fas fa-file-medical me-2"></i>
                                        Receta Médica
                                    </h6>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <strong>N° Receta:</strong> RX-2024-001234<br>
                                            <strong>Médico:</strong> Dr. Carlos López<br>
                                            <strong>CMP:</strong> 12345
                                        </div>
                                        <div class="col-sm-6">
                                            <strong>Fecha:</strong> 24/10/2024<br>
                                            <strong>Válida hasta:</strong> 31/10/2024<br>
                                            <strong>Estado:</strong> <span class="prescription-status status-approved">Aprobada</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medication Details and Dispensing Process -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-pills me-2"></i>
                            Detalles del Medicamento Controlado
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Medication Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Concentración</th>
                                        <th>Stock</th>
                                        <th>Cant. Recetada</th>
                                        <th>Cant. Disp.</th>
                                        <th>Precio Unit.</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="fw-bold">Tramadol</div>
                                            <small class="text-muted">Categoría II</small>
                                        </td>
                                        <td>50mg</td>
                                        <td>
                                            <span class="badge bg-warning">25 unidades</span>
                                        </td>
                                        <td>20 cápsulas</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" value="20" min="1" max="20" onchange="updateDispensing()">
                                        </td>
                                        <td>S/ 2.50</td>
                                        <td class="fw-bold">S/ 50.00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Dispensing Progress -->
                        <div class="mb-3">
                            <label class="form-label">Progreso de Dispensación:</label>
                            <div class="dispensing-progress">
                                <div class="dispensing-progress-bar" style="width: 75%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Paso 3 de 4: Dispensación</small>
                                <small class="text-muted">75%</small>
                            </div>
                        </div>

                        <!-- Security Measures -->
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-shield-alt me-2"></i>Medidas de Seguridad Activadas</h6>
                            <ul class="mb-0">
                                <li><strong>Doble Custodia:</strong> Requiere aprobación de segundo farmacéutico</li>
                                <li><strong>Verificación Biométrica:</strong> Huella dactilar del paciente registrada</li>
                                <li><strong>Registro Audio:</strong> Conversación siendo grabada (solo audio)</li>
                                <li><strong>Fotografía:</strong> Foto del paciente al momento de entrega</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <!-- Dual Custody Verification -->
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-users me-2"></i>
                            Verificación Dual
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="dual-custody-indicator p-2 rounded">
                                <i class="fas fa-lock me-2"></i>
                                Control Dual Requerido
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Primer Farmacéutico:</label>
                            <select class="form-select form-select-sm">
                                <option value="">Seleccionar farmacéutico...</option>
                                <option value="1">Dr. Ana García (CFP: 67890)</option>
                                <option value="2">Dr. Luis Martínez (CFP: 54321)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Segundo Farmacéutico:</label>
                            <select class="form-select form-select-sm">
                                <option value="">Seleccionar segundo farmacéutico...</option>
                                <option value="3">Dra. Carmen Rodríguez (CFP: 11111)</option>
                                <option value="4">Dr. Pedro Sánchez (CFP: 22222)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <button class="btn btn-success w-100" onclick="requestDualApproval()">
                                <i class="fas fa-signature me-2"></i>
                                Solicitar Aprobación Dual
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Prescription Validity -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar-check me-2"></i>
                            Validez de Receta
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-12 mb-2">
                                <div class="border rounded p-2">
                                    <strong>Días restantes:</strong><br>
                                    <span class="text-warning h5">7 días</span>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <div class="border rounded p-2">
                                    <strong>Refills autorizados:</strong><br>
                                    <span class="text-info">0 de 0</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="border rounded p-2">
                                    <strong>Límite cantidad:</strong><br>
                                    <span class="text-danger">30 días de tratamiento</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Regulatory Compliance -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-gavel me-2"></i>
                            Cumplimiento Regulatorio DIGEMID
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Verificaciones Requeridas:</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="verification1" checked disabled>
                                    <label class="form-check-label" for="verification1">
                                        Receta válida y vigente
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="verification2" checked disabled>
                                    <label class="form-check-label" for="verification2">
                                        Paciente identificado correctamente
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="verification3" checked>
                                    <label class="form-check-label" for="verification3">
                                        Dosificación apropiada
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="verification4">
                                    <label class="form-check-label" for="verification4">
                                        Doble farmacéutico verificado
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6>Documentación Generada:</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="doc1" checked disabled>
                                    <label class="form-check-label" for="doc1">
                                        Registro de dispensación
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="doc2" checked disabled>
                                    <label class="form-check-label" for="doc2">
                                        Actualización inventario
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="doc3">
                                    <label class="form-check-label" for="doc3">
                                        Certificado entrega
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="doc4">
                                    <label class="form-check-label" for="doc4">
                                        Reporte DIGEMID
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6>Acciones Post-Dispensación:</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="action1" checked disabled>
                                    <label class="form-check-label" for="action1">
                                        Archivo receta original
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="action2" checked disabled>
                                    <label class="form-check-label" for="action2">
                                        Registro en libro control
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="action3">
                                    <label class="form-check-label" for="action3">
                                        Notificación al prescriptor
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="action4">
                                    <label class="form-check-label" for="action4">
                                        Seguimiento paciente
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Summary and Final Actions -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calculator me-2"></i>
                            Resumen de Transacción
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td>Subtotal Medicamentos:</td>
                                        <td class="text-end">S/ 50.00</td>
                                    </tr>
                                    <tr>
                                        <td>IGV (18%):</td>
                                        <td class="text-end">S/ 9.00</td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td><strong>Total a Pagar:</strong></td>
                                        <td class="text-end"><strong>S/ 59.00</strong></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Información Importante</h6>
                                    <ul class="mb-0">
                                        <li>Retenga receta por 2 años</li>
                                        <li>Informe efectos adversos</li>
                                        <li>No compartir medicamento</li>
                                        <li>Conservar en lugar seguro</li>
                                    </ul>
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
                            <i class="fas fa-cogs me-2"></i>
                            Acciones de Finalización
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-success" onclick="completeDispensing()" id="completeBtn" disabled>
                                <i class="fas fa-check-circle me-2"></i>
                                Completar Dispensación
                            </button>
                            <button class="btn btn-warning" onclick="printReceipt()">
                                <i class="fas fa-print me-2"></i>
                                Imprimir Comprobante
                            </button>
                            <button class="btn btn-info" onclick="generateReport()">
                                <i class="fas fa-file-export me-2"></i>
                                Generar Reporte DIGEMID
                            </button>
                            <button class="btn btn-secondary" onclick="saveTransaction()">
                                <i class="fas fa-save me-2"></i>
                                Guardar Borrador
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Controlled Dispensing History -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            Historial de Dispensaciones Controladas (Últimas 24 horas)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="controlledDispensingTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha/Hora</th>
                                        <th>Receta</th>
                                        <th>Paciente</th>
                                        <th>Medicamento</th>
                                        <th>Cantidad</th>
                                        <th>Farmacéuticos</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>24/10/2024 14:30</td>
                                        <td>RX-2024-001234</td>
                                        <td>Juan Pérez</td>
                                        <td>Tramadol 50mg</td>
                                        <td>20 caps</td>
                                        <td>
                                            <small>Ana G. + Luis M.</small>
                                        </td>
                                        <td><span class="badge bg-success">Dispensado</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewTransaction(1234)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="printTransaction(1234)">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>24/10/2024 11:15</td>
                                        <td>RX-2024-001233</td>
                                        <td>María López</td>
                                        <td>Clonazepam 2mg</td>
                                        <td>30 tabs</td>
                                        <td>
                                            <small>Luis M. + Carmen R.</small>
                                        </td>
                                        <td><span class="badge bg-success">Dispensado</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewTransaction(1233)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="printTransaction(1233)">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>24/10/2024 09:45</td>
                                        <td>RX-2024-001232</td>
                                        <td>Carlos Ruiz</td>
                                        <td>Morfina 10mg</td>
                                        <td>15 tabs</td>
                                        <td>
                                            <small>Carmen R. + Pedro S.</small>
                                        </td>
                                        <td><span class="badge bg-warning">Pendiente</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewTransaction(1232)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="completeTransaction(1232)">
                                                <i class="fas fa-play"></i>
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
            $('#controlledDispensingTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json"
                },
                "order": [[0, "desc"]],
                "pageLength": 10
            });

            // Real-time updates for dual custody verification
            checkDualCustodyStatus();
        });

        function searchPatient() {
            const searchType = document.querySelector('input[name="searchType"]:checked').id;
            const searchTerm = document.getElementById('searchTerm').value;
            
            if (!searchTerm) {
                Swal.fire('Error', 'Por favor ingrese un término de búsqueda', 'error');
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Buscando...',
                text: 'Validando paciente y recetas',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            // Simulate API call
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Paciente Encontrado',
                    text: 'Información cargada exitosamente',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 2000);
        }

        function updateDispensing() {
            // Update dispensing progress and totals
            const progressBar = document.querySelector('.dispensing-progress-bar');
            let currentProgress = parseInt(progressBar.style.width) || 75;
            
            // Check if all verifications are complete
            const allVerified = checkAllVerifications();
            
            if (allVerified) {
                progressBar.style.width = '100%';
                document.querySelector('.dispensing-progress + div small:last-child').textContent = '100%';
                document.querySelector('.dispensing-progress + div small:first-child').textContent = 'Completado';
                document.getElementById('completeBtn').disabled = false;
            }
        }

        function checkAllVerifications() {
            const verifications = [
                'verification3', // Dosificación apropiada
                'verification4'  // Doble farmacéutico verificado
            ];
            
            return verifications.every(id => document.getElementById(id).checked);
        }

        function checkDualCustodyStatus() {
            // Monitor dual custody selections
            const firstPharmacist = document.querySelectorAll('.form-select')[0];
            const secondPharmacist = document.querySelectorAll('.form-select')[1];
            
            function updateDualCustodyStatus() {
                const verification4 = document.getElementById('verification4');
                
                if (firstPharmacist.value && secondPharmacist.value && 
                    firstPharmacist.value !== secondPharmacist.value) {
                    verification4.checked = true;
                    updateDispensing();
                } else {
                    verification4.checked = false;
                    updateDispensing();
                }
            }
            
            firstPharmacist.addEventListener('change', updateDualCustodyStatus);
            secondPharmacist.addEventListener('change', updateDualCustodyStatus);
        }

        function requestDualApproval() {
            const firstPharmacist = document.querySelectorAll('.form-select')[0];
            const secondPharmacist = document.querySelectorAll('.form-select')[1];
            
            if (!firstPharmacist.value || !secondPharmacist.value) {
                Swal.fire('Error', 'Debe seleccionar ambos farmacéuticos', 'error');
                return;
            }
            
            if (firstPharmacist.value === secondPharmacist.value) {
                Swal.fire('Error', 'Debe seleccionar farmacéuticos diferentes', 'error');
                return;
            }

            Swal.fire({
                title: 'Solicitud de Aprobación Dual',
                text: 'Enviando solicitud al segundo farmacéutico...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            // Simulate dual approval process
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Aprobación Dual Confirmada',
                    text: 'Ambos farmacéuticos han aprobado la dispensación',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    document.getElementById('verification4').checked = true;
                    updateDispensing();
                });
            }, 3000);
        }

        function completeDispensing() {
            Swal.fire({
                title: '¿Completar Dispensación?',
                text: 'Esta acción finalizará la venta del medicamento controlado',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, completar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Completando dispensación...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    // Simulate completion process
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Dispensación Completada',
                            text: 'Medicamento controlado dispensado exitosamente',
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            // Reset form for next transaction
                            resetTransactionForm();
                        });
                    }, 2000);
                }
            });
        }

        function resetTransactionForm() {
            // Reset form fields
            document.getElementById('searchTerm').value = '';
            document.querySelectorAll('.form-check-input').forEach(cb => {
                if (!cb.disabled) cb.checked = false;
            });
            document.querySelectorAll('.form-select').forEach(select => {
                select.selectedIndex = 0;
            });
            
            // Reset progress
            const progressBar = document.querySelector('.dispensing-progress-bar');
            progressBar.style.width = '0%';
            document.querySelector('.dispensing-progress + div small:first-child').textContent = 'Paso 1 de 4: Validación';
            document.querySelector('.dispensing-progress + div small:last-child').textContent = '0%';
            
            // Disable complete button
            document.getElementById('completeBtn').disabled = true;
        }

        function printReceipt() {
            Swal.fire({
                icon: 'info',
                title: 'Generando Comprobante',
                text: 'Preparando documento para impresión...',
                timer: 2000,
                showConfirmButton: false
            });
        }

        function generateReport() {
            Swal.fire({
                title: 'Reporte DIGEMID',
                text: 'Generando reporte automático para autoridades...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Reporte Generado',
                    text: 'Reporte enviado a DIGEMID automáticamente',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 3000);
        }

        function saveTransaction() {
            Swal.fire({
                icon: 'success',
                title: 'Transacción Guardada',
                text: 'Borrador guardado correctamente',
                timer: 2000,
                showConfirmButton: false
            });
        }

        function viewTransaction(transactionId) {
            Swal.fire({
                icon: 'info',
                title: 'Ver Transacción',
                text: `Mostrando detalles de transacción ${transactionId}`,
                timer: 2000,
                showConfirmButton: false
            });
        }

        function printTransaction(transactionId) {
            Swal.fire({
                icon: 'info',
                title: 'Imprimir Transacción',
                text: `Preparando transacción ${transactionId} para impresión`,
                timer: 2000,
                showConfirmButton: false
            });
        }

        function completeTransaction(transactionId) {
            Swal.fire({
                title: 'Completar Transacción',
                text: `¿Continuar con la dispensación de la transacción ${transactionId}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Transacción Cargada',
                        text: 'Puede proceder con la dispensación',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
    </script>
</body>
</html>