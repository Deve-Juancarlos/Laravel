@extends('layouts.app')

@section('title', 'Medicamentos Controlados - Dashboard')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-shield-alt text-danger"></i>
                        Medicamentos Controlados
                    </h1>
                    <p class="text-muted mb-0">Control especial de medicamentos según normativa DIGEMID</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success" onclick="exportControlledReport()">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="showNewControlledSaleModal()">
                        <i class="fas fa-prescription-bottle-alt"></i> Nueva Venta Controlada
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Estado del Sistema de Control --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h4 class="mb-2">
                                <i class="fas fa-lock"></i> Sistema de Control de Medicamentos Especiales Activo
                            </h4>
                            <p class="mb-0">Monitoreo en tiempo real según Decreto Supremo N° 018-97-SA | Última actualización: {{ date('d/m/Y H:i:s') }}</p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <div class="d-flex align-items-center justify-content-end">
                                <div class="me-3">
                                    <div class="badge bg-light text-dark fs-6">
                                        <i class="fas fa-certificate text-warning"></i> DIGEMID
                                    </div>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-outline-light btn-sm" onclick="showRegulatoryInfo()">
                                        <i class="fas fa-info-circle"></i> Info Legal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Indicadores Clave --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-pills fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($controlledMedications ?? 89) }}</h5>
                            <small>Medicamentos Controlados</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-prescription fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($controlledSalesToday ?? 12) }}</h5>
                            <small>Ventas de Hoy</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($pendingAudits ?? 3) }}</h5>
                            <small>Auditorías Pendientes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-secondary text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($expiredPrescriptions ?? 1) }}</h5>
                            <small>Recetas Vencidas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertas de Control --}}
    @if(($controlledAlerts ?? 2) > 0)
    <div class="alert alert-danger border-0 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
            <div>
                <h6 class="alert-heading mb-1">
                    <strong>Alerta de Control:</strong> {{ $controlledAlerts ?? 2 }} situaciones requieren atención inmediata
                </h6>
                <p class="mb-0">Se han detectado irregularidades en el manejo de medicamentos controlados.</p>
            </div>
            <div class="ms-auto">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="viewControlledAlerts()">
                    Ver Alertas
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Clasificación de Medicamentos Controlados --}}
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-list"></i> Clasificación de Medicamentos Controlados
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Lista F1 - Estupefacientes --}}
                        <div class="col-lg-6">
                            <div class="border rounded p-3 bg-danger bg-opacity-10">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-danger">
                                        <i class="fas fa-exclamation-triangle"></i> Lista F1 - Estupefacientes
                                    </h6>
                                    <span class="badge bg-danger">{{ $listaF1Count ?? 15 }}</span>
                                </div>
                                <p class="text-muted small mb-2">
                                    Morfina, Fentanilo, Oxicodona, etc.
                                </p>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $listaF1Usage ?? 75 }}%"></div>
                                </div>
                                <small class="text-muted">Stock utilizado: {{ $listaF1Usage ?? 75 }}%</small>
                            </div>
                        </div>

                        {{-- Lista F2 - Psicotrópicos --}}
                        <div class="col-lg-6">
                            <div class="border rounded p-3 bg-warning bg-opacity-10">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-warning">
                                        <i class="fas fa-brain"></i> Lista F2 - Psicotrópicos
                                    </h6>
                                    <span class="badge bg-warning">{{ $listaF2Count ?? 23 }}</span>
                                </div>
                                <p class="text-muted small mb-2">
                                    Diazepam, Alprazolam, Fenobarbital, etc.
                                </p>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $listaF2Usage ?? 45 }}%"></div>
                                </div>
                                <small class="text-muted">Stock utilizado: {{ $listaF2Usage ?? 45 }}%</small>
                            </div>
                        </div>

                        {{-- Lista F3 - Otros Psicotrópicos --}}
                        <div class="col-lg-6">
                            <div class="border rounded p-3 bg-info bg-opacity-10">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-info">
                                        <i class="fas fa-pills"></i> Lista F3 - Otros Psicotrópicos
                                    </h6>
                                    <span class="badge bg-info">{{ $listaF3Count ?? 31 }}</span>
                                </div>
                                <p class="text-muted small mb-2">
                                    Tramadol, Zolpidem, Fluoxetina, etc.
                                </p>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: {{ $listaF3Usage ?? 60 }}%"></div>
                                </div>
                                <small class="text-muted">Stock utilizado: {{ $listaF3Usage ?? 60 }}%</small>
                            </div>
                        </div>

                        {{-- Lista F4 - Sustancias Sujetas a Fiscalización --}}
                        <div class="col-lg-6">
                            <div class="border rounded p-3 bg-secondary bg-opacity-10">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-secondary">
                                        <i class="fas fa-shield-alt"></i> Lista F4 - Fiscalización
                                    </h6>
                                    <span class="badge bg-secondary">{{ $listaF4Count ?? 20 }}</span>
                                </div>
                                <p class="text-muted small mb-2">
                                    Anfetaminas, Prednisona, etc.
                                </p>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-secondary" style="width: {{ $listaF4Usage ?? 30 }}%"></div>
                                </div>
                                <small class="text-muted">Stock utilizado: {{ $listaF4Usage ?? 30 }}%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfico de Ventas Controladas --}}
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Ventas por Categoría (30 días)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="controlledSalesChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Inventario de Medicamentos Controlados --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="fas fa-warehouse"></i> Inventario de Medicamentos Controlados
                <span class="badge bg-secondary ms-2">{{ number_format($controlledInventory->count() ?? 89) }}</span>
            </h6>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-success" onclick="exportInventoryReport()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="scheduleInventoryAudit()">
                    <i class="fas fa-calendar-check"></i> Programar Auditoría
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="controlledInventoryTable">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Medicamento</th>
                            <th>Lista</th>
                            <th>Stock Actual</th>
                            <th>Mín. Requerido</th>
                            <th>Estado</th>
                            <th>Última Venta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Ejemplos de medicamentos controlados --}}
                        <tr class="table-danger">
                            <td><code>MC-001</code></td>
                            <td>
                                <div class="fw-bold">Morfina Sulfato 10mg</div>
                                <small class="text-muted">Inyectable - Ampollas</small>
                            </td>
                            <td>
                                <span class="badge bg-danger">F1</span>
                            </td>
                            <td>24 unidades</td>
                            <td>50 unidades</td>
                            <td>
                                <span class="badge bg-danger">Stock Bajo</span>
                            </td>
                            <td>{{ date('d/m/Y', strtotime('-2 days')) }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewMedicationDetail(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="requestRestock(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="generateReport(1)">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-warning">
                            <td><code>MC-002</code></td>
                            <td>
                                <div class="fw-bold">Diazepam 5mg</div>
                                <small class="text-muted">Tabletas</small>
                            </td>
                            <td>
                                <span class="badge bg-warning">F2</span>
                            </td>
                            <td>156 unidades</td>
                            <td>100 unidades</td>
                            <td>
                                <span class="badge bg-success">Stock Normal</span>
                            </td>
                            <td>{{ date('d/m/Y', strtotime('-1 day')) }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewMedicationDetail(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="newControlledSale(2)">
                                        <i class="fas fa-prescription"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="generateReport(2)">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-info">
                            <td><code>MC-003</code></td>
                            <td>
                                <div class="fw-bold">Tramadol 50mg</div>
                                <small class="text-muted">Cápsulas</small>
                            </td>
                            <td>
                                <span class="badge bg-info">F3</span>
                            </td>
                            <td>89 unidades</td>
                            <td>75 unidades</td>
                            <td>
                                <span class="badge bg-success">Stock Normal</span>
                            </td>
                            <td>{{ date('d/m/Y', strtotime('-3 hours')) }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewMedicationDetail(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="newControlledSale(3)">
                                        <i class="fas fa-prescription"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="generateReport(3)">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-success">
                            <td><code>MC-004</code></td>
                            <td>
                                <div class="fw-bold">Alprazolam 0.5mg</div>
                                <small class="text-muted">Tabletas</small>
                            </td>
                            <td>
                                <span class="badge bg-warning">F2</span>
                            </td>
                            <td>234 unidades</td>
                            <td>150 unidades</td>
                            <td>
                                <span class="badge bg-success">Stock Normal</span>
                            </td>
                            <td>{{ date('d/m/Y', strtotime('-6 hours')) }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewMedicationDetail(4)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="newControlledSale(4)">
                                        <i class="fas fa-prescription"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="generateReport(4)">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-danger">
                            <td><code>MC-005</code></td>
                            <td>
                                <div class="fw-bold">Fentanilo 25mcg</div>
                                <small class="text-muted">Parches Transdérmicos</small>
                            </td>
                            <td>
                                <span class="badge bg-danger">F1</span>
                            </td>
                            <td>8 unidades</td>
                            <td>20 unidades</td>
                            <td>
                                <span class="badge bg-danger">Stock Crítico</span>
                            </td>
                            <td>{{ date('d/m/Y', strtotime('-1 week')) }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewMedicationDetail(5)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="urgentRestock(5)">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="generateReport(5)">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Ventas Recientes Controladas --}}
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history"></i> Ventas Controladas Recientes
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        {{-- Venta 1 --}}
                        <div class="list-group-item list-group-item-danger">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-2">
                                    <i class="fas fa-prescription-bottle-alt text-danger"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1">Morfina Sulfato 10mg</h6>
                                        <small class="text-muted">{{ date('d/m/Y H:i') }}</small>
                                    </div>
                                    <p class="mb-1">
                                        <strong>Receta:</strong> Dr. Carlos Mendoza | <strong>Paciente:</strong> Juan Pérez
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-danger me-2">Lista F1</span>
                                        <span class="badge bg-info me-2">2 ampollas</span>
                                        <small class="text-muted">S/ 45.60</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-danger dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="viewSaleDetail(1)">Ver Detalle</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="printPrescription(1)">Imprimir Receta</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="generateLegalReport(1)">Reporte Legal</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Venta 2 --}}
                        <div class="list-group-item list-group-item-warning">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-2">
                                    <i class="fas fa-pills text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1">Diazepam 5mg</h6>
                                        <small class="text-muted">{{ date('d/m/Y H:i', strtotime('-2 hours')) }}</small>
                                    </div>
                                    <p class="mb-1">
                                        <strong>Receta:</strong> Dra. Ana Rodríguez | <strong>Paciente:</strong> María García
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-warning me-2">Lista F2</span>
                                        <span class="badge bg-info me-2">30 tabletas</span>
                                        <small class="text-muted">S/ 18.50</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="viewSaleDetail(2)">Ver Detalle</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="printPrescription(2)">Imprimir Receta</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="generateLegalReport(2)">Reporte Legal</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Venta 3 --}}
                        <div class="list-group-item list-group-item-info">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-2">
                                    <i class="fas fa-capsules text-info"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1">Tramadol 50mg</h6>
                                        <small class="text-muted">{{ date('d/m/Y H:i', strtotime('-4 hours')) }}</small>
                                    </div>
                                    <p class="mb-1">
                                        <strong>Receta:</strong> Dr. Luis Valencia | <strong>Paciente:</strong> Pedro Sánchez
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-info me-2">Lista F3</span>
                                        <span class="badge bg-info me-2">20 cápsulas</span>
                                        <small class="text-muted">S/ 32.00</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="viewSaleDetail(3)">Ver Detalle</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="printPrescription(3)">Imprimir Receta</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="generateLegalReport(3)">Reporte Legal</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Venta 4 --}}
                        <div class="list-group-item list-group-item-warning">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-2">
                                    <i class="fas fa-tablet text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1">Alprazolam 0.5mg</h6>
                                        <small class="text-muted">{{ date('d/m/Y H:i', $strtotate('-6 hours')) }}</small>
                                    </div>
                                    <p class="mb-1">
                                        <strong>Receta:</strong> Dr. Carlos Mendoza | <strong>Paciente:</strong> Carmen López
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-warning me-2">Lista F2</span>
                                        <span class="badge bg-info me-2">15 tabletas</span>
                                        <small class="text-muted">S/ 22.50</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="viewSaleDetail(4)">Ver Detalle</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="printPrescription(4)">Imprimir Receta</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="generateLegalReport(4)">Reporte Legal</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Auditorías y Reportes --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-search"></i> Auditorías y Reportes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="text-center">
                                <h4 class="text-success">{{ number_format($complianceRate ?? 98.5, 1) }}%</h4>
                                <p class="text-muted">Tasa de Cumplimiento Legal</p>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" style="width: {{ $complianceRate ?? 98.5 }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="text-center border rounded p-3">
                                <h5 class="text-info">{{ number_format($auditsThisMonth ?? 4) }}</h5>
                                <small class="text-muted">Auditorías Este Mes</small>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="text-center border rounded p-3">
                                <h5 class="text-warning">{{ number_format($reportsGenerated ?? 12) }}</h5>
                                <small class="text-muted">Reportes Generados</small>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" onclick="generateMonthlyReport()">
                                    <i class="fas fa-calendar-alt"></i> Reporte Mensual DIGEMID
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="scheduleInternalAudit()">
                                    <i class="fas fa-search"></i> Auditoría Interna
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="exportLegalCompliance()">
                                    <i class="fas fa-file-export"></i> Exportar Cumplimiento
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Acciones Rápidas --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="showNewControlledSaleModal()">
                                <i class="fas fa-prescription-bottle-alt d-block mb-2"></i>
                                Nueva Venta Controlada
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button type="button" class="btn btn-outline-success w-100" onclick="searchPrescription()">
                                <i class="fas fa-search d-block mb-2"></i>
                                Validar Receta
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button type="button" class="btn btn-outline-info w-100" onclick="generateReports()">
                                <i class="fas fa-chart-bar d-block mb-2"></i>
                                Generar Reportes
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button type="button" class="btn btn-outline-warning w-100" onclick="scheduleAudit()">
                                <i class="fas fa-calendar-check d-block mb-2"></i>
                                Programar Auditoría
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="emergencyAlert()">
                                <i class="fas fa-exclamation-triangle d-block mb-2"></i>
                                Alerta de Emergencia
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="viewRegulations()">
                                <i class="fas fa-book d-block mb-2"></i>
                                Ver Regulaciones
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ==========================================
     MODALES
========================================== --}}

{{-- Modal de Nueva Venta Controlada --}}
<div class="modal fade" id="newControlledSaleModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-prescription-bottle-alt"></i> Nueva Venta de Medicamento Controlado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="controlledSaleForm">
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Información de la Receta --}}
                        <div class="col-12">
                            <h6 class="text-danger border-bottom pb-2">Información de la Receta Médica</h6>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Número de Receta *</label>
                            <input type="text" class="form-control" id="prescriptionNumber" placeholder="Ej: REC-2025-001234" required>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Médico Prescriptor *</label>
                            <select class="form-select" id="prescribingDoctor" required>
                                <option value="">Seleccionar médico</option>
                                <option value="carlos_mendoza">Dr. Carlos Mendoza - CMP 12345</option>
                                <option value="ana_rodriguez">Dra. Ana Rodríguez - CMP 23456</option>
                                <option value="luis_valencia">Dr. Luis Valencia - CMP 34567</option>
                            </select>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Fecha de Prescripción</label>
                            <input type="date" class="form-control" id="prescriptionDate" value="{{ date('Y-m-d') }}">
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">Nombre del Médico *</label>
                            <input type="text" class="form-control" id="doctorName" placeholder="Dr. Carlos Mendoza" required>
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">CMP del Médico *</label>
                            <input type="text" class="form-control" id="doctorCMP" placeholder="12345" required>
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">Institución Médica</label>
                            <input type="text" class="form-control" id="medicalInstitution" placeholder="Hospital Nacional">
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">Especialidad</label>
                            <input type="text" class="form-control" id="medicalSpecialty" placeholder="Medicina Interna">
                        </div>

                        {{-- Información del Paciente --}}
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2">Información del Paciente</h6>
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">Nombre Completo del Paciente *</label>
                            <input type="text" class="form-control" id="patientName" placeholder="Juan Pérez García" required>
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">DNI del Paciente *</label>
                            <input type="text" class="form-control" id="patientDNI" placeholder="12345678" required>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Edad</label>
                            <input type="number" class="form-control" id="patientAge" min="0" max="120" placeholder="45">
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Sexo</label>
                            <select class="form-select" id="patientGender">
                                <option value="">Seleccionar</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="patientBirthDate">
                        </div>

                        {{-- Medicamento Controlado --}}
                        <div class="col-12 mt-4">
                            <h6 class="text-warning border-bottom pb-2">Medicamento Controlado</h6>
                        </div>
                        
                        <div class="col-lg-8">
                            <label class="form-label">Medicamento *</label>
                            <select class="form-select" id="controlledMedication" required>
                                <option value="">Seleccionar medicamento</option>
                                <option value="morfina">Morfina Sulfato 10mg - Inyectable</option>
                                <option value="diazepam">Diazepam 5mg - Tabletas</option>
                                <option value="tramadol">Tramadol 50mg - Cápsulas</option>
                                <option value="alprazolam">Alprazolam 0.5mg - Tabletas</option>
                                <option value="fentanilo">Fentanilo 25mcg - Parches</option>
                            </select>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Lista de Control *</label>
                            <select class="form-select" id="controlList" required>
                                <option value="">Seleccionar lista</option>
                                <option value="F1">Lista F1 - Estupefacientes</option>
                                <option value="F2">Lista F2 - Psicotrópicos</option>
                                <option value="F3">Lista F3 - Otros Psicotrópicos</option>
                                <option value="F4">Lista F4 - Fiscalización</option>
                            </select>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Cantidad Prescrita *</label>
                            <input type="number" class="form-control" id="prescribedQuantity" min="1" required>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Unidad de Medida</label>
                            <select class="form-select" id="unitOfMeasure">
                                <option value="unidades">Unidades</option>
                                <option value="ampollas">Ampollas</option>
                                <option value="tabletas">Tabletas</option>
                                <option value="cápsulas">Cápsulas</option>
                                <option value="frascos">Frascos</option>
                                <option value="parches">Parches</option>
                            </select>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Precio Unitario (S/)</label>
                            <input type="number" class="form-control" id="unitPrice" step="0.01" placeholder="22.80">
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Dosis Indicada</label>
                            <input type="text" class="form-control" id="prescribedDose" placeholder="1 tableta cada 8 horas">
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Duración del Tratamiento (días)</label>
                            <input type="number" class="form-control" id="treatmentDuration" min="1" value="7">
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Fecha de Vencimiento de Receta</label>
                            <input type="date" class="form-control" id="prescriptionExpiry" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                        </div>

                        {{-- Validaciones --}}
                        <div class="col-12 mt-4">
                            <h6 class="text-info border-bottom pb-2">Validaciones Requeridas</h6>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="validatePrescription" checked>
                                <label class="form-check-label" for="validatePrescription">
                                    Receta médica válida y vigente
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="validateDoctor" checked>
                                <label class="form-check-label" for="validateDoctor">
                                    Médico autorizado para prescribir
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="validateQuantity">
                                <label class="form-check-label" for="validateQuantity">
                                    Cantidad dentro de límites legales
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="validateStock" checked>
                                <label class="form-check-label" for="validateStock">
                                    Stock disponible en farmacia
                                </label>
                            </div>
                        </div>

                        {{-- Observaciones --}}
                        <div class="col-12">
                            <label class="form-label">Observaciones Adicionales</label>
                            <textarea class="form-control" id="saleObservations" rows="3" placeholder="Observaciones sobre la venta controlada..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save"></i> Procesar Venta Controlada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de Información Regulatoria --}}
<div class="modal fade" id="regulatoryInfoModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-gavel"></i> Marco Legal - Medicamentos Controlados
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-12">
                        <h6 class="text-danger">Marco Normativo Aplicable</h6>
                        <div class="border rounded p-3 bg-light">
                            <ul class="mb-0">
                                <li><strong>Decreto Supremo N° 018-97-SA:</strong> Reglamento de Establecimientos Farmacéuticos</li>
                                <li><strong>Resolución Ministerial N° 507-2001-SA/DM:</strong> Lista de Medicamentos Sujetos a Control Especial</li>
                                <li><strong>Ley N° 27444:</strong> Estatuto del Procedimiento Administrativo General</li>
                                <li><strong>Decreto Supremo N° 013-2006-SA:</strong> Reglamento de la Ley de Control de Medicamentos</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <h6>Listas de Control</h6>
                        <div class="border rounded p-3">
                            <p><strong>F1 - Estupefacientes:</strong> Morfina, Fentanilo, Oxicodona, etc.</p>
                            <p><strong>F2 - Psicotrópicos:</strong> Diazepam, Alprazolam, Fenobarbital, etc.</p>
                            <p><strong>F3 - Otros Psicotrópicos:</strong> Tramadol, Zolpidem, Fluoxetina, etc.</p>
                            <p><strong>F4 - Fiscalización:</strong> Sustancias sujetas a fiscalización especial</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <h6>Requisitos Obligatorios</h6>
                        <div class="border rounded p-3">
                            <ul class="mb-0">
                                <li>Receta médica con firma y sello del prescriptor</li>
                                <li>Registro obligatorio de todas las ventas</li>
                                <li>Validación de identidad del paciente</li>
                                <li>Límites de cantidad por receta</li>
                                <li>Archivo de recetas por 3 años</li>
                                <li>Reportes mensuales a DIGEMID</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-primary" onclick="downloadRegulations()">
                    <i class="fas fa-download"></i> Descargar Normativa
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gráfico
    initializeChart();
    
    // Inicializar DataTable
    initializeDataTable();
    
    // Event listeners
    setupEventListeners();
});

function initializeChart() {
    const ctx = document.getElementById('controlledSalesChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Lista F1', 'Lista F2', 'Lista F3', 'Lista F4'],
            datasets: [{
                data: [25, 45, 20, 10],
                backgroundColor: [
                    '#dc3545',
                    '#ffc107',
                    '#17a2b8',
                    '#6c757d'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} unidades (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function initializeDataTable() {
    $('#controlledInventoryTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json'
        },
        pageLength: 25,
        order: [[2, 'asc']], // Ordenar por lista
        columnDefs: [
            { orderable: false, targets: [7] } // Deshabilitar orden en columna de acciones
        ]
    });
}

function setupEventListeners() {
    // Event listeners para formularios
    $('#controlledSaleForm').on('submit', handleControlledSale);
    
    // Validación de medicamentos según lista
    $('#controlledMedication').change(updateControlList);
}

// Funciones de Gestión de Ventas
function showNewControlledSaleModal() {
    $('#newControlledSaleModal').modal('show');
}

function newControlledSale(medicationId) {
    // Preseleccionar medicamento y mostrar modal
    $('#controlledMedication').val(medicationId);
    updateControlList();
    showNewControlledSaleModal();
}

function viewMedicationDetail(medicationId) {
    // Simular vista de detalle del medicamento
    Swal.fire({
        title: 'Detalle del Medicamento Controlado',
        html: `
            <div class="text-start">
                <h6>Información General</h6>
                <table class="table table-sm">
                    <tr><td><strong>Nombre:</strong></td><td>Morfina Sulfato 10mg</td></tr>
                    <tr><td><strong>Lista:</strong></td><td><span class="badge bg-danger">F1 - Estupefacientes</span></td></tr>
                    <tr><td><strong>Stock Actual:</strong></td><td>24 unidades</td></tr>
                    <tr><td><strong>Stock Mínimo:</strong></td><td>50 unidades</td></tr>
                    <tr><td><strong>Precio:</strong></td><td>S/ 22.80 por ampolla</td></tr>
                    <tr><td><strong>Proveedor:</strong></td><td>PharmaCorp S.A.</td></tr>
                    <tr><td><strong>Vencimiento:</strong></td><td>15/12/2026</td></tr>
                </table>
            </div>
        `,
        width: '600px'
    });
}

function requestRestock(medicationId) {
    Swal.fire({
        title: 'Solicitar Reposición',
        text: '¿Desea solicitar reposición de stock para este medicamento controlado?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, solicitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Solicitud de reposición enviada a DIGEMID', 'success');
        }
    });
}

function urgentRestock(medicationId) {
    Swal.fire({
        title: 'Reposición Urgente',
        text: '¿Desea solicitar reposición URGENTE de este medicamento? Esto requiere autorización especial.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, solicitar urgente',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Solicitud urgente enviada. DIGEMID será contactado.', 'warning');
        }
    });
}

// Funciones de Ventas
function viewSaleDetail(saleId) {
    Swal.fire({
        title: 'Detalle de Venta Controlada',
        html: `
            <div class="text-start">
                <h6>Información de la Venta</h6>
                <table class="table table-sm">
                    <tr><td><strong>Medicamento:</strong></td><td>Morfina Sulfato 10mg</td></tr>
                    <tr><td><strong>Lista:</strong></td><td><span class="badge bg-danger">F1</span></td></tr>
                    <tr><td><strong>Cantidad:</strong></td><td>2 ampollas</td></tr>
                    <tr><td><strong>Total:</strong></td><td>S/ 45.60</td></tr>
                    <tr><td><strong>Fecha:</strong></td><td>${new Date().toLocaleDateString('es-ES')}</td></tr>
                    <tr><td><strong>Médico:</strong></td><td>Dr. Carlos Mendoza</td></tr>
                    <tr><td><strong>Paciente:</strong></td><td>Juan Pérez</td></tr>
                    <tr><td><strong>DNI:</strong></td><td>12345678</td></tr>
                </table>
            </div>
        `,
        width: '600px'
    });
}

function printPrescription(saleId) {
    showNotification('Imprimiendo receta médica...', 'info');
    setTimeout(() => {
        showNotification('Receta enviada a impresora', 'success');
    }, 2000);
}

function generateLegalReport(saleId) {
    showNotification('Generando reporte legal para DIGEMID...', 'info');
    setTimeout(() => {
        showNotification('Reporte legal generado exitosamente', 'success');
    }, 3000);
}

// Funciones de Auditoría y Reportes
function scheduleInventoryAudit() {
    Swal.fire({
        title: 'Programar Auditoría de Inventario',
        html: `
            <form id="auditForm">
                <div class="mb-3">
                    <label class="form-label">Tipo de Auditoría</label>
                    <select class="form-select">
                        <option value="internal">Auditoría Interna</option>
                        <option value="digemid">Auditoría DIGEMID</option>
                        <option value="external">Auditoría Externa</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha</label>
                    <input type="date" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-control" rows="2" placeholder="Observaciones sobre la auditoría..."></textarea>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Programar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Auditoría de inventario programada exitosamente', 'success');
        }
    });
}

function generateMonthlyReport() {
    Swal.fire({
        title: 'Generar Reporte Mensual DIGEMID',
        text: '¿Desea generar el reporte mensual para DIGEMID?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, generar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Generando Reporte...',
                text: 'Procesando datos para reporte mensual DIGEMID',
                icon: 'info',
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            setTimeout(() => {
                showNotification('Reporte mensual DIGEMID generado exitosamente', 'success');
            }, 5000);
        }
    });
}

function scheduleInternalAudit() {
    Swal.fire({
        title: 'Programar Auditoría Interna',
        text: '¿Desea programar una auditoría interna de medicamentos controlados?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, programar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Auditoría interna programada para la próxima semana', 'success');
        }
    });
}

function exportLegalCompliance() {
    Swal.fire({
        title: 'Exportar Cumplimiento Legal',
        text: '¿Desea exportar el reporte de cumplimiento legal?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Exportando reporte de cumplimiento legal...', 'info');
            setTimeout(() => {
                showNotification('Reporte exportado exitosamente', 'success');
            }, 2000);
        }
    });
}

function generateReport(medicationId) {
    showNotification('Generando reporte detallado...', 'info');
    setTimeout(() => {
        showNotification('Reporte generado exitosamente', 'success');
    }, 3000);
}

// Funciones de Alertas
function viewControlledAlerts() {
    Swal.fire({
        title: 'Alertas de Medicamentos Controlados',
        html: `
            <div class="text-start">
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> Stock Bajo</h6>
                    <p class="mb-0">Morfina Sulfato 10mg - Stock: 24 unidades (Mín: 50)</p>
                </div>
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle"></i> Stock Crítico</h6>
                    <p class="mb-0">Fentanilo 25mcg - Stock: 8 unidades (Mín: 20)</p>
                </div>
            </div>
        `,
        width: '600px'
    });
}

function showRegulatoryInfo() {
    $('#regulatoryInfoModal').modal('show');
}

// Acciones Rápidas
function searchPrescription() {
    Swal.fire({
        title: 'Validar Receta Médica',
        html: `
            <form id="prescriptionValidationForm">
                <div class="mb-3">
                    <label class="form-label">Número de Receta</label>
                    <input type="text" class="form-control" placeholder="REC-2025-001234">
                </div>
                <div class="mb-3">
                    <label class="form-label">DNI del Paciente</label>
                    <input type="text" class="form-control" placeholder="12345678">
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Validar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Receta validada exitosamente', 'success');
        }
    });
}

function generateReports() {
    Swal.fire({
        title: 'Generar Reportes',
        text: '¿Qué tipo de reporte desea generar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Reporte Mensual',
        cancelButtonText: 'Cancelar',
        showDenyButton: true,
        denyButtonText: 'Reporte de Ventas'
    }).then((result) => {
        if (result.isConfirmed) {
            generateMonthlyReport();
        } else if (result.isDenied) {
            exportControlledReport();
        }
    });
}

function scheduleAudit() {
    scheduleInventoryAudit();
}

function emergencyAlert() {
    Swal.fire({
        title: 'ALERTA DE EMERGENCIA',
        text: '¿Desea enviar una alerta de emergencia por irregularidad en medicamentos controlados?',
        icon: 'error',
        showCancelButton: true,
        confirmButtonText: 'SÍ, ENVIAR ALERTA',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        showDenyButton: true,
        denyButtonText: 'Solo Notificar Internamente',
        denyButtonColor: '#ffc107'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('ALERTA DE EMERGENCIA ENVIADA A DIGEMID', 'error');
        } else if (result.isDenied) {
            showNotification('Notificación interna enviada', 'warning');
        }
    });
}

function viewRegulations() {
    showRegulatoryInfo();
}

function downloadRegulations() {
    showNotification('Descargando normativa completa...', 'info');
    setTimeout(() => {
        showNotification('Normativa descargada exitosamente', 'success');
    }, 2000);
}

// Funciones de Exportación
function exportControlledReport() {
    Swal.fire({
        title: 'Exportar Reporte Controlado',
        text: '¿Desea exportar el reporte completo de medicamentos controlados?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Exportando reporte de medicamentos controlados...', 'info');
            setTimeout(() => {
                showNotification('Reporte exportado exitosamente', 'success');
            }, 3000);
        }
    });
}

function exportInventoryReport() {
    Swal.fire({
        title: 'Exportar Inventario',
        text: '¿Desea exportar el inventario de medicamentos controlados?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Exportando inventario controlado...', 'info');
            setTimeout(() => {
                showNotification('Inventario exportado exitosamente', 'success');
            }, 2000);
        }
    });
}

// Funciones auxiliares
function updateControlList() {
    const medication = $('#controlledMedication').val();
    let controlList = '';
    
    switch(medication) {
        case 'morfina':
        case 'fentanilo':
            controlList = 'F1';
            break;
        case 'diazepam':
        case 'alprazolam':
            controlList = 'F2';
            break;
        case 'tramadol':
            controlList = 'F3';
            break;
        default:
            controlList = '';
    }
    
    $('#controlList').val(controlList);
}

// Manejo del formulario de venta controlada
function handleControlledSale(e) {
    e.preventDefault();
    
    const formData = {
        prescriptionNumber: $('#prescriptionNumber').val(),
        prescribingDoctor: $('#prescribingDoctor').val(),
        doctorName: $('#doctorName').val(),
        doctorCMP: $('#doctorCMP').val(),
        medicalInstitution: $('#medicalInstitution').val(),
        patientName: $('#patientName').val(),
        patientDNI: $('#patientDNI').val(),
        controlledMedication: $('#controlledMedication').val(),
        controlList: $('#controlList').val(),
        prescribedQuantity: $('#prescribedQuantity').val(),
        unitPrice: $('#unitPrice').val(),
        saleObservations: $('#saleObservations').val()
    };
    
    // Validaciones críticas
    if (!formData.prescriptionNumber || !formData.doctorName || !formData.doctorCMP || 
        !formData.patientName || !formData.patientDNI || !formData.controlledMedication || 
        !formData.prescribedQuantity) {
        showNotification('Por favor complete todos los campos requeridos', 'error');
        return;
    }
    
    if (formData.controlList === 'F1' && parseInt(formData.prescribedQuantity) > 30) {
        showNotification('Cantidad excede límite legal para Lista F1', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Procesando Venta Controlada...',
        text: 'Validando información y registrando venta',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Simular procesamiento
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Venta Procesada',
            text: 'La venta controlada ha sido registrada exitosamente. Reporte enviado a DIGEMID.',
            showConfirmButton: false,
            timer: 4000
        });
        
        $('#newControlledSaleModal').modal('hide');
        $('#controlledSaleForm')[0].reset();
    }, 3000);
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
    
    Toast.fire({
        icon: type,
        title: message
    });
}
</script>
@endsection

@section('styles')
<style>
/* Estilos para medicamentos controlados */
.controlled-system-active {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.kpi-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* Estados de medicamentos */
.medication-stock-normal {
    border-left: 4px solid #28a745;
}

.medication-stock-low {
    border-left: 4px solid #ffc107;
}

.medication-stock-critical {
    border-left: 4px solid #dc3545;
    animation: pulse 2s infinite;
}

/* Clasificación por listas */
.list-f1 {
    border-left: 4px solid #dc3545;
    background: linear-gradient(135deg, #fff5f5, #ffffff);
}

.list-f2 {
    border-left: 4px solid #ffc107;
    background: linear-gradient(135deg, #fff8e1, #ffffff);
}

.list-f3 {
    border-left: 4px solid #17a2b8;
    background: linear-gradient(135deg, #f0f9ff, #ffffff);
}

.list-f4 {
    border-left: 4px solid #6c757d;
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
}

/* Badges de listas */
.badge-f1 {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.badge-f2 {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.badge-f3 {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

.badge-f4 {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

/* Estados de stock */
.stock-normal {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
}

.stock-low {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    color: #856404;
}

.stock-critical {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
    animation: flash 1s ease-in-out;
}

/* Ventas controladas */
.controlled-sale {
    border-left: 4px solid;
}

.sale-f1 {
    border-left-color: #dc3545;
    background: linear-gradient(135deg, #fff5f5, #ffffff);
}

.sale-f2 {
    border-left-color: #ffc107;
    background: linear-gradient(135deg, #fff8e1, #ffffff);
}

.sale-f3 {
    border-left-color: #17a2b8;
    background: linear-gradient(135deg, #f0f9ff, #ffffff);
}

/* Auditorías */
.audit-pending {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    border-left: 4px solid #ffc107;
}

.audit-completed {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    border-left: 4px solid #28a745;
}

.audit-failed {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    border-left: 4px solid #dc3545;
}

/* Botones de acciones rápidas */
.quick-action-btn {
    transition: all 0.2s ease;
    border-radius: 0.5rem;
    padding: 1.5rem 1rem;
    text-align: center;
    border: 2px solid transparent;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: #007bff;
}

.quick-action-btn i {
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
}

/* Modal de información regulatoria */
.regulatory-info {
    background: linear-gradient(135deg, #fff5f5, #ffffff);
    border: 2px solid #dc3545;
}

/* Formulario de venta controlada */
.controlled-sale-form {
    border-left: 4px solid #dc3545;
    padding-left: 1rem;
}

.required-field::after {
    content: " *";
    color: red;
}

/* Animaciones */
.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
    100% {
        opacity: 1;
    }
}

.flash {
    animation: flash 1s ease-in-out;
}

@keyframes flash {
    0%, 100% {
        background-color: transparent;
    }
    50% {
        background-color: rgba(220, 53, 69, 0.1);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .kpi-card h5 {
        font-size: 1.2rem;
    }
    
    .quick-action-btn {
        padding: 1rem 0.5rem;
    }
    
    .quick-action-btn i {
        font-size: 1.5rem;
    }
    
    .list-group-item {
        padding: 0.75rem 1rem;
    }
    
    .dropdown-menu {
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.8rem;
    }
}

/* Indicadores de cumplimiento */
.compliance-indicator {
    position: relative;
    display: inline-block;
}

.compliance-indicator::after {
    content: '';
    position: absolute;
    top: 50%;
    right: -1.5rem;
    transform: translateY(-50%);
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
}

.compliance-high::after {
    background-color: #28a745;
}

.compliance-medium::after {
    background-color: #ffc107;
    animation: pulse 2s infinite;
}

.compliance-low::after {
    background-color: #dc3545;
    animation: pulse 1s infinite;
}

/* Estados de alertas */
.alert-active {
    border-left: 4px solid #dc3545;
    background: linear-gradient(135deg, #fff5f5, #ffffff);
}

.alert-resolved {
    border-left: 4px solid #28a745;
    background: linear-gradient(135deg, #f0fff4, #ffffff);
}

.alert-pending {
    border-left: 4px solid #ffc107;
    background: linear-gradient(135deg, #fff8e1, #ffffff);
}
</style>
@endsection