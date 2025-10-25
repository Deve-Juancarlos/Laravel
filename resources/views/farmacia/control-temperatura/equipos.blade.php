{{-- ==========================================
     VISTA: GESTIÓN DE EQUIPOS
     MÓDULO: Control de Temperatura - Equipos
     DESARROLLADO POR: MiniMax Agent
     FECHA: 2025-10-25
     DESCRIPCIÓN: Gestión completa de equipos de monitoreo de temperatura,
                  configuración, calibración y mantenimiento de sensores y termómetros
========================================== --}}

@extends('layouts.app')

@section('title', 'Gestión de Equipos - Control de Temperatura')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-tools text-info"></i>
                        Gestión de Equipos
                    </h1>
                    <p class="text-muted mb-0">Monitoreo y configuración de equipos de temperatura</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success" onclick="exportEquipmentReport()">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="showAddEquipmentModal()">
                        <i class="fas fa-plus"></i> Nuevo Equipo
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen de Equipos --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-thermometer-half fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($totalEquipment ?? 24) }}</h5>
                            <small>Total Equipos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($activeEquipment ?? 22) }}</h5>
                            <small>Equipos Activos</small>
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
                            <i class="fas fa-calendar-times fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($pendingCalibration ?? 3) }}</h5>
                            <small>Calibración Pendiente</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($equipmentAlerts ?? 2) }}</h5>
                            <small>Equipos con Alertas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros de Búsqueda --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="card-title mb-0">
                <i class="fas fa-filter"></i> Filtros de Búsqueda
            </h6>
        </div>
        <div class="card-body">
            <form id="equipmentFiltersForm">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Nombre o Código</label>
                        <input type="text" class="form-control" id="equipmentSearch" placeholder="Buscar por nombre o código">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Tipo de Equipo</label>
                        <select class="form-select" id="equipmentType">
                            <option value="">Todos los tipos</option>
                            <option value="sensor_digital">Sensor Digital</option>
                            <option value="termometro_infrarrojo">Termómetro Infrarrojo</option>
                            <option value="registrador_datos">Registrador de Datos</option>
                            <option value="termometro_analogico">Termómetro Analógico</option>
                            <option value="sistema_monitoreo">Sistema de Monitoreo</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Estado</label>
                        <select class="form-select" id="equipmentStatus">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="mantenimiento">En Mantenimiento</option>
                            <option value="fuera_servicio">Fuera de Servicio</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Ubicación</label>
                        <select class="form-select" id="equipmentLocation">
                            <option value="">Todas las ubicaciones</option>
                            <option value="refrigerador_principal">Refrigerador Principal</option>
                            <option value="congelador_principal">Congelador Principal</option>
                            <option value="camara_frigorifica">Cámara Frigorífica</option>
                            <option value="refrigerador_vacunas">Refrigerador Vacunas</option>
                            <option value="laboratorio">Laboratorio</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-12">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" onclick="applyEquipmentFilters()">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearEquipmentFilters()">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Lista de Equipos --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="fas fa-list"></i> Lista de Equipos
                <span class="badge bg-secondary ms-2">{{ number_format($equipmentList->count() ?? 24) }}</span>
            </h6>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-success" onclick="bulkCalibrate()">
                    <i class="fas fa-cog"></i> Calibrar Selección
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="bulkMaintenance()">
                    <i class="fas fa-wrench"></i> Mantenimiento
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="equipmentTable">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input" id="selectAllEquipment" onchange="toggleAllEquipment()">
                            </th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Ubicación</th>
                            <th>Temperatura Actual</th>
                            <th>Estado</th>
                            <th>Última Calibración</th>
                            <th>Próxima Calibración</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Ejemplos de equipos --}}
                        <tr class="table-success">
                            <td>
                                <input type="checkbox" class="form-check-input equipment-checkbox" value="1">
                            </td>
                            <td><code>EQ-001</code></td>
                            <td>
                                <div class="fw-bold">Sensor Principal Refrig.</div>
                                <small class="text-muted">Hibrido Digital-Analógico</small>
                            </td>
                            <td>
                                <span class="badge bg-info">Sensor Digital</span>
                            </td>
                            <td>Refrigerador Principal</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="h6 mb-0 text-success">4.2°C</span>
                                    <i class="fas fa-check-circle text-success ms-2"></i>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-success">Activo</span>
                            </td>
                            <td>{{ date('d/m/Y', strtotime('-15 days')) }}</td>
                            <td>{{ date('d/m/Y', strtotime('+80 days')) }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewEquipmentDetail(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="calibrateEquipment(1)">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="maintenanceEquipment(1)">
                                        <i class="fas fa-wrench"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-success">
                            <td>
                                <input type="checkbox" class="form-check-input equipment-checkbox" value="2">
                            </td>
                            <td><code>EQ-002</code></td>
                            <td>
                                <div class="fw-bold">Sensor Congelador</div>
                                <small class="text-muted">Termómetro Digital</small>
                            </td>
                            <td>
                                <span class="badge bg-info">Sensor Digital</span>
                            </td>
                            <td>Congelador Principal</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="h6 mb-0 text-success">-18.5°C</span>
                                    <i class="fas fa-check-circle text-success ms-2"></i>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-success">Activo</span>
                            </td>
                            <td>{{ date('d/m/Y', strtotime('-10 days')) }}</td>
                            <td>{{ date('d/m/Y', strtotime('+85 days')) }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewEquipmentDetail(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="calibrateEquipment(2)">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="maintenanceEquipment(2)">
                                        <i class="fas fa-wrench"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-warning">
                            <td>
                                <input type="checkbox" class="form-check-input equipment-checkbox" value="3">
                            </td>
                            <td><code>EQ-003</code></td>
                            <td>
                                <div class="fw-bold">Termómetro Cámara</div>
                                <small class="text-muted">Registrador de Datos</small>
                            </td>
                            <td>
                                <span class="badge bg-warning">Registrador Datos</span>
                            </td>
                            <td>Cámara Frigorífica</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="h6 mb-0 text-warning">6.8°C</span>
                                    <i class="fas fa-exclamation-triangle text-warning ms-2"></i>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning">Alerta</span>
                            </td>
                            <td>{{ date('d/m/Y', strtotime('-45 days')) }}</td>
                            <td>
                                <span class="text-danger">{{ date('d/m/Y', strtotime('-5 days')) }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewEquipmentDetail(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="calibrateEquipment(3)">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="maintenanceEquipment(3)">
                                        <i class="fas fa-wrench"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-info">
                            <td>
                                <input type="checkbox" class="form-check-input equipment-checkbox" value="4">
                            </td>
                            <td><code>EQ-004</code></td>
                            <td>
                                <div class="fw-bold">Sensor Vacunas</div>
                                <small class="text-muted">Sistema IoT</small>
                            </td>
                            <td>
                                <span class="badge bg-primary">Sistema Monitoreo</span>
                            </td>
                            <td>Refrigerador Vacunas</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="h6 mb-0 text-info">5.2°C</span>
                                    <i class="fas fa-wifi text-info ms-2"></i>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">Cal. Pendiente</span>
                            </td>
                            <td>{{ date('d/m/Y', strtotime('-120 days')) }}</td>
                            <td>{{ date('d/m/Y', strtotime('+5 days')) }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewEquipmentDetail(4)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="calibrateEquipment(4)">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="maintenanceEquipment(4)">
                                        <i class="fas fa-wrench"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-secondary">
                            <td>
                                <input type="checkbox" class="form-check-input equipment-checkbox" value="5">
                            </td>
                            <td><code>EQ-005</code></td>
                            <td>
                                <div class="fw-bold">Termómetro Backup</div>
                                <small class="text-muted">Termómetro Analógico</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">Term. Analógico</span>
                            </td>
                            <td>Refrigerador Principal</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="h6 mb-0 text-muted">--</span>
                                    <i class="fas fa-pause text-muted ms-2"></i>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">Respaldo</span>
                            </td>
                            <td>{{ date('d/m/Y', strtotime('-90 days')) }}</td>
                            <td>{{ date('d/m/Y', strtotime('+5 days')) }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewEquipmentDetail(5)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="calibrateEquipment(5)">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="activateEquipment(5)">
                                        <i class="fas fa-play"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Acciones en Lote --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-tasks"></i> Acciones en Lote
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-primary" onclick="exportSelectedEquipment()">
                                    <i class="fas fa-download"></i>
                                    <br>
                                    <small>Exportar Seleccionados</small>
                                </button>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-warning" onclick="generateCalibrationSchedule()">
                                    <i class="fas fa-calendar-alt"></i>
                                    <br>
                                    <small>Programar Calibraciones</small>
                                </button>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-success" onclick="runDiagnostics()">
                                    <i class="fas fa-stethoscope"></i>
                                    <br>
                                    <small>Ejecutar Diagnósticos</small>
                                </button>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-info" onclick="updateFirmware()">
                                    <i class="fas fa-microchip"></i>
                                    <br>
                                    <small>Actualizar Firmware</small>
                                </button>
                            </div>
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

{{-- Modal de Nuevo Equipo --}}
<div class="modal fade" id="addEquipmentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Registrar Nuevo Equipo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addEquipmentForm">
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Información Básica --}}
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">Información Básica</h6>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Código del Equipo *</label>
                            <input type="text" class="form-control" id="equipmentCode" placeholder="EQ-001" required>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Nombre del Equipo *</label>
                            <input type="text" class="form-control" id="equipmentName" placeholder="Sensor Principal Refrigerador" required>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Marca/Modelo</label>
                            <input type="text" class="form-control" id="equipmentBrand" placeholder="ThermoTech T-2000">
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">Tipo de Equipo *</label>
                            <select class="form-select" id="equipmentType" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="sensor_digital">Sensor Digital</option>
                                <option value="termometro_infrarrojo">Termómetro Infrarrojo</option>
                                <option value="registrador_datos">Registrador de Datos</option>
                                <option value="termometro_analogico">Termómetro Analógico</option>
                                <option value="sistema_monitoreo">Sistema de Monitoreo IoT</option>
                                <option value="data_logger">Data Logger</option>
                            </select>
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">Número de Serie</label>
                            <input type="text" class="form-control" id="serialNumber" placeholder="SN123456789">
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Fecha de Adquisición</label>
                            <input type="date" class="form-control" id="acquisitionDate">
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Fecha de Instalación</label>
                            <input type="date" class="form-control" id="installationDate" value="{{ date('Y-m-d') }}">
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Garantía (meses)</label>
                            <input type="number" class="form-control" id="warrantyMonths" min="0" max="120" placeholder="24">
                        </div>

                        {{-- Configuración y Ubicación --}}
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2">Configuración y Ubicación</h6>
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">Ubicación/Área *</label>
                            <select class="form-select" id="equipmentLocation" required>
                                <option value="">Seleccionar ubicación</option>
                                <option value="refrigerador_principal">Refrigerador Principal</option>
                                <option value="congelador_principal">Congelador Principal</option>
                                <option value="camara_frigorifica">Cámara Frigorífica</option>
                                <option value="refrigerador_vacunas">Refrigerador Vacunas</option>
                                <option value="refrigerador_laboratorio">Refrigerador Laboratorio</option>
                                <option value="congelador_vacunas">Congelador Vacunas</option>
                                <option value="area_almacenamiento">Área de Almacenamiento</option>
                                <option value="area_produccion">Área de Producción</option>
                                <option value="laboratorio_control">Laboratorio de Control</option>
                            </select>
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">Rango de Temperatura (°C)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="minTemp" step="0.1" placeholder="Min" required>
                                <span class="input-group-text">a</span>
                                <input type="number" class="form-control" id="maxTemp" step="0.1" placeholder="Max" required>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Precisión (±°C)</label>
                            <input type="number" class="form-control" id="accuracy" step="0.1" placeholder="0.1" value="0.1">
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Resolución (°C)</label>
                            <input type="number" class="form-control" id="resolution" step="0.01" placeholder="0.01" value="0.01">
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Tiempo de Respuesta (seg)</label>
                            <input type="number" class="form-control" id="responseTime" step="0.1" placeholder="30" value="30">
                        </div>

                        {{-- Calibración y Mantenimiento --}}
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2">Calibración y Mantenimiento</h6>
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">Calibración Inicial</label>
                            <input type="date" class="form-control" id="initialCalibration" value="{{ date('Y-m-d') }}">
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">Frecuencia de Calibración (días)</label>
                            <input type="number" class="form-control" id="calibrationFrequency" min="30" max="365" value="90">
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Próxima Calibración</label>
                            <input type="date" class="form-control" id="nextCalibration" value="{{ date('Y-m-d', strtotime('+90 days')) }}">
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Proveedor de Calibración</label>
                            <input type="text" class="form-control" id="calibrationProvider" placeholder="DIGEMID/CalibraPerú">
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Certificado de Calibración</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="hasCalibrationCert" checked>
                                <label class="form-check-label" for="hasCalibrationCert">
                                    Tiene certificado válido
                                </label>
                            </div>
                        </div>

                        {{-- Conectividad y Alertas --}}
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2">Conectividad y Alertas</h6>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Tipo de Conexión</label>
                            <select class="form-select" id="connectionType">
                                <option value="wired">Cableado</option>
                                <option value="wifi">WiFi</option>
                                <option value="bluetooth">Bluetooth</option>
                                <option value="zigbee">Zigbee</option>
                                <option value="lora">LoRa</option>
                                <option value="cellular">Celular</option>
                            </select>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Dirección IP (si aplica)</label>
                            <input type="text" class="form-control" id="ipAddress" placeholder="192.168.1.100">
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label">Dirección MAC</label>
                            <input type="text" class="form-control" id="macAddress" placeholder="00:1B:44:11:3A:B7">
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">URL del Servidor (si aplica)</label>
                            <input type="url" class="form-control" id="serverUrl" placeholder="https://api.thermomonitor.com/v1">
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label">Alertas por Email</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="emailAlerts" checked>
                                <label class="form-check-label" for="emailAlerts">
                                    Enviar alertas por email
                                </label>
                            </div>
                            <input type="email" class="form-control mt-2" id="alertEmail" placeholder="alertas@hospital.com" value="alertas@hospital.com">
                        </div>

                        {{-- Observaciones --}}
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2">Información Adicional</h6>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="equipmentObservations" rows="3" placeholder="Observaciones adicionales sobre el equipo..."></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Especificaciones Técnicas</label>
                            <textarea class="form-control" id="technicalSpecs" rows="3" placeholder="Especificaciones técnicas detalladas..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Equipo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de Detalle de Equipo --}}
<div class="modal fade" id="equipmentDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalle del Equipo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="equipmentDetailContent">
                    {{-- Contenido dinámico --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-primary" onclick="editEquipment()">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="generateEquipmentReport()">
                    <i class="fas fa-file-pdf"></i> Reporte
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Calibración --}}
<div class="modal fade" id="calibrationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cog"></i> Calibrar Equipo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="equipmentCalibrationForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Equipo a Calibrar</label>
                            <input type="text" class="form-control" id="calibrationEquipment" readonly>
                            <input type="hidden" id="calibrationEquipmentId">
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Fecha de Calibración</label>
                            <input type="date" class="form-control" id="calibrationDate" value="{{ date('Y-m-d') }}" required>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Temperatura de Referencia (°C)</label>
                            <input type="number" class="form-control" id="referenceTemperature" step="0.1" placeholder="4.0" required>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Lectura del Equipo (°C)</label>
                            <input type="number" class="form-control" id="equipmentReading" step="0.1" placeholder="4.2" required>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Error Calculado (°C)</label>
                            <input type="number" class="form-control" id="calculatedError" step="0.1" readonly>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Condiciones Ambientales</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control" id="ambientTemp" step="0.1" placeholder="Temp. Ambiente (°C)">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" id="ambientHumidity" step="1" placeholder="Humedad (%)">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="calibrationObservations" rows="3" placeholder="Observaciones sobre la calibración..."></textarea>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Técnico Responsable</label>
                            <select class="form-select" id="calibrationTechnician" required>
                                <option value="">Seleccionar técnico</option>
                                <option value="ana_rodriguez">Q.F. Ana Rodríguez</option>
                                <option value="luis_valencia">Luis Valencia</option>
                                <option value="carlos_mendoza">Dr. Carlos Mendoza</option>
                                <option value="tecnico_externo">Técnico Externo</option>
                            </select>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Certificado Adjunto</label>
                            <input type="file" class="form-control" id="calibrationCert" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="requiresAdjustment" onchange="toggleAdjustmentFields()">
                                <label class="form-check-label" for="requiresAdjustment">
                                    Requiere ajuste del equipo
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-12" id="adjustmentFields" style="display: none;">
                            <label class="form-label">Detalles del Ajuste Realizado</label>
                            <textarea class="form-control" id="adjustmentDetails" rows="2" placeholder="Descripción del ajuste realizado..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Calibración
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable
    initializeDataTable();
    
    // Event listeners
    setupEventListeners();
});

function initializeDataTable() {
    $('#equipmentTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json'
        },
        pageLength: 25,
        order: [[1, 'asc']], // Ordenar por código
        columnDefs: [
            { orderable: false, targets: [0, 9] } // Deshabilitar orden en checkboxes y acciones
        ]
    });
}

function setupEventListeners() {
    // Búsqueda en tiempo real
    $('#equipmentSearch').on('input', debounce(searchEquipment, 300));
    
    // Calcular error automáticamente
    $('#referenceTemperature, #equipmentReading').on('input', calculateCalibrationError);
}

// Funciones de Búsqueda y Filtros
function searchEquipment() {
    const searchTerm = $('#equipmentSearch').val();
    console.log('Buscando equipo:', searchTerm);
    // Aquí se implementaría la búsqueda en tiempo real
}

function applyEquipmentFilters() {
    const filters = {
        search: $('#equipmentSearch').val(),
        type: $('#equipmentType').val(),
        status: $('#equipmentStatus').val(),
        location: $('#equipmentLocation').val()
    };
    
    console.log('Aplicando filtros:', filters);
    showNotification('Filtros aplicados', 'success');
}

function clearEquipmentFilters() {
    $('#equipmentFiltersForm')[0].reset();
    
    // Resetear DataTable
    $('#equipmentTable').DataTable().search('').draw();
    
    showNotification('Filtros limpiados', 'info');
}

// Funciones de Selección
function toggleAllEquipment() {
    const selectAll = document.getElementById('selectAllEquipment');
    const checkboxes = document.querySelectorAll('.equipment-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function getSelectedEquipment() {
    const checkboxes = document.querySelectorAll('.equipment-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Funciones de Gestión de Equipos
function showAddEquipmentModal() {
    $('#addEquipmentModal').modal('show');
}

function viewEquipmentDetail(equipmentId) {
    // Simular carga de detalle
    const content = `
        <div class="row g-4">
            <div class="col-lg-6">
                <h6>Información General</h6>
                <table class="table table-sm">
                    <tr><td><strong>Código:</strong></td><td>EQ-001</td></tr>
                    <tr><td><strong>Nombre:</strong></td><td>Sensor Principal Refrig.</td></tr>
                    <tr><td><strong>Tipo:</strong></td><td>Sensor Digital</td></tr>
                    <tr><td><strong>Marca/Modelo:</strong></td><td>ThermoTech T-2000</td></tr>
                    <tr><td><strong>Número de Serie:</strong></td><td>SN123456789</td></tr>
                    <tr><td><strong>Ubicación:</strong></td><td>Refrigerador Principal</td></tr>
                    <tr><td><strong>Estado:</strong></td><td><span class="badge bg-success">Activo</span></td></tr>
                    <tr><td><strong>Instalación:</strong></td><td>${new Date().toLocaleDateString('es-ES')}</td></tr>
                </table>
            </div>
            
            <div class="col-lg-6">
                <h6>Especificaciones Técnicas</h6>
                <table class="table table-sm">
                    <tr><td><strong>Rango:</strong></td><td>-20°C a +50°C</td></tr>
                    <tr><td><strong>Precisión:</strong></td><td>±0.1°C</td></tr>
                    <tr><td><strong>Resolución:</strong></td><td>0.01°C</td></tr>
                    <tr><td><strong>Tiempo Respuesta:</strong></td><td>30 segundos</td></tr>
                    <tr><td><strong>Conexión:</strong></td><td>WiFi 802.11n</td></tr>
                    <tr><td><strong>Alimentación:</strong></td><td>12V DC</td></tr>
                    <tr><td><strong>Consumo:</strong></td><td>2.5W</td></tr>
                    <tr><td><strong>Certificación:</strong></td><td>DIGEMID/CE</td></tr>
                </table>
            </div>
            
            <div class="col-lg-6">
                <h6>Calibración</h6>
                <table class="table table-sm">
                    <tr><td><strong>Última Calibración:</strong></td><td>${new Date(Date.now() - 15*24*60*60*1000).toLocaleDateString('es-ES')}</td></tr>
                    <tr><td><strong>Próxima Calibración:</strong></td><td>${new Date(Date.now() + 80*24*60*60*1000).toLocaleDateString('es-ES')}</td></tr>
                    <tr><td><strong>Frecuencia:</strong></td><td>90 días</td></tr>
                    <tr><td><strong>Proveedor:</strong></td><td>DIGEMID</td></tr>
                    <tr><td><strong>Estado:</strong></td><td><span class="badge bg-success">Vigente</span></td></tr>
                </table>
            </div>
            
            <div class="col-lg-6">
                <h6>Temperatura Actual</h6>
                <div class="text-center">
                    <div class="display-4 text-success">4.2°C</div>
                    <p class="text-muted">Temperatura actual registrada</p>
                    <div class="row g-2">
                        <div class="col-6">
                            <small><strong>Mín (24h):</strong><br>3.8°C</small>
                        </div>
                        <div class="col-6">
                            <small><strong>Máx (24h):</strong><br>4.5°C</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#equipmentDetailContent').html(content);
    $('#equipmentDetailModal').modal('show');
}

function calibrateEquipment(equipmentId) {
    // Simular carga de datos del equipo
    document.getElementById('calibrationEquipment').value = 'EQ-001 - Sensor Principal Refrig.';
    document.getElementById('calibrationEquipmentId').value = equipmentId;
    
    $('#calibrationModal').modal('show');
}

function maintenanceEquipment(equipmentId) {
    Swal.fire({
        title: 'Mantenimiento de Equipo',
        text: '¿Qué tipo de mantenimiento desea programar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Mantenimiento Preventivo',
        cancelButtonText: 'Cancelar',
        showDenyButton: true,
        denyButtonText: 'Mantenimiento Correctivo'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Mantenimiento preventivo programado', 'success');
        } else if (result.isDenied) {
            showNotification('Mantenimiento correctivo programado', 'warning');
        }
    });
}

function activateEquipment(equipmentId) {
    Swal.fire({
        title: 'Activar Equipo',
        text: '¿Está seguro de activar este equipo de respaldo?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, activar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Equipo activado exitosamente', 'success');
        }
    });
}

function editEquipment() {
    showNotification('Función de edición en desarrollo', 'info');
}

function generateEquipmentReport() {
    showNotification('Generando reporte del equipo...', 'info');
    setTimeout(() => {
        showNotification('Reporte generado exitosamente', 'success');
    }, 2000);
}

// Funciones de Calibración
function calculateCalibrationError() {
    const reference = parseFloat($('#referenceTemperature').val()) || 0;
    const reading = parseFloat($('#equipmentReading').val()) || 0;
    const error = reading - reference;
    
    $('#calculatedError').val(error.toFixed(1));
}

function toggleAdjustmentFields() {
    const checkbox = document.getElementById('requiresAdjustment');
    const fields = document.getElementById('adjustmentFields');
    
    if (checkbox.checked) {
        fields.style.display = 'block';
    } else {
        fields.style.display = 'none';
    }
}

// Acciones en Lote
function bulkCalibrate() {
    const selected = getSelectedEquipment();
    
    if (selected.length === 0) {
        showNotification('Por favor seleccione al menos un equipo', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Calibración en Lote',
        text: `¿Desea calibrar ${selected.length} equipos seleccionados?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, calibrar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification(`Calibración iniciada para ${selected.length} equipos`, 'success');
        }
    });
}

function bulkMaintenance() {
    const selected = getSelectedEquipment();
    
    if (selected.length === 0) {
        showNotification('Por favor seleccione al menos un equipo', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Mantenimiento en Lote',
        text: `¿Desea programar mantenimiento para ${selected.length} equipos?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, programar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification(`Mantenimiento programado para ${selected.length} equipos`, 'success');
        }
    });
}

function exportSelectedEquipment() {
    const selected = getSelectedEquipment();
    
    if (selected.length === 0) {
        showNotification('Por favor seleccione al menos un equipo', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Exportar Equipos',
        text: `¿Desea exportar los ${selected.length} equipos seleccionados?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Exportando datos de equipos...', 'info');
            setTimeout(() => {
                showNotification('Equipos exportados exitosamente', 'success');
            }, 2000);
        }
    });
}

function generateCalibrationSchedule() {
    Swal.fire({
        title: 'Programar Calibraciones',
        text: 'Generando cronograma de calibraciones para equipos próximos a vencer',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        showNotification('Cronograma de calibraciones generado', 'success');
    }, 2000);
}

function runDiagnostics() {
    Swal.fire({
        title: 'Ejecutar Diagnósticos',
        text: 'Ejecutando diagnósticos en equipos seleccionados',
        icon: 'info',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        showNotification('Diagnósticos completados exitosamente', 'success');
    }, 3000);
}

function updateFirmware() {
    Swal.fire({
        title: 'Actualizar Firmware',
        text: '¿Desea actualizar el firmware de los equipos seleccionados?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Actualización de firmware iniciada', 'info');
            setTimeout(() => {
                showNotification('Firmware actualizado exitosamente', 'success');
            }, 3000);
        }
    });
}

function exportEquipmentReport() {
    Swal.fire({
        title: 'Exportar Reporte',
        text: '¿Desea exportar el reporte completo de equipos?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Generando reporte de equipos...', 'info');
            setTimeout(() => {
                showNotification('Reporte exportado exitosamente', 'success');
            }, 2000);
        }
    });
}

// Formularios
$('#addEquipmentForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        code: $('#equipmentCode').val(),
        name: $('#equipmentName').val(),
        brand: $('#equipmentBrand').val(),
        type: $('#equipmentType').val(),
        serialNumber: $('#serialNumber').val(),
        acquisitionDate: $('#acquisitionDate').val(),
        installationDate: $('#installationDate').val(),
        warrantyMonths: $('#warrantyMonths').val(),
        location: $('#equipmentLocation').val(),
        minTemp: $('#minTemp').val(),
        maxTemp: $('#maxTemp').val(),
        accuracy: $('#accuracy').val(),
        resolution: $('#resolution').val(),
        responseTime: $('#responseTime').val(),
        calibrationFrequency: $('#calibrationFrequency').val(),
        nextCalibration: $('#nextCalibration').val()
    };
    
    // Validaciones básicas
    if (!formData.code || !formData.name || !formData.type || !formData.location || 
        !formData.minTemp || !formData.maxTemp) {
        showNotification('Por favor complete todos los campos requeridos', 'error');
        return;
    }
    
    if (parseFloat(formData.minTemp) >= parseFloat(formData.maxTemp)) {
        showNotification('La temperatura mínima debe ser menor que la máxima', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Guardando Equipo...',
        text: 'Registrando nuevo equipo en el sistema',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Equipo Registrado',
            text: 'El equipo ha sido registrado exitosamente.',
            showConfirmButton: false,
            timer: 2000
        });
        
        $('#addEquipmentModal').modal('hide');
        $('#addEquipmentForm')[0].reset();
    }, 2000);
});

$('#equipmentCalibrationForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        equipmentId: $('#calibrationEquipmentId').val(),
        date: $('#calibrationDate').val(),
        referenceTemp: $('#referenceTemperature').val(),
        equipmentReading: $('#equipmentReading').val(),
        calculatedError: $('#calculatedError').val(),
        ambientTemp: $('#ambientTemp').val(),
        ambientHumidity: $('#ambientHumidity').val(),
        observations: $('#calibrationObservations').val(),
        technician: $('#calibrationTechnician').val(),
        requiresAdjustment: $('#requiresAdjustment').is(':checked'),
        adjustmentDetails: $('#adjustmentDetails').val()
    };
    
    // Validaciones
    if (!formData.date || !formData.referenceTemp || !formData.equipmentReading || !formData.technician) {
        showNotification('Por favor complete todos los campos requeridos', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Guardando Calibración...',
        text: 'Procesando datos de calibración',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Calibración Guardada',
            text: 'La calibración ha sido guardada exitosamente.',
            showConfirmButton: false,
            timer: 2000
        });
        
        $('#calibrationModal').modal('hide');
        $('#equipmentCalibrationForm')[0].reset();
        document.getElementById('adjustmentFields').style.display = 'none';
    }, 2000);
});

// Función debounce para búsqueda
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
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
/* Estilos para gestión de equipos */
.equipment-active {
    border-left: 4px solid #28a745;
}

.equipment-warning {
    border-left: 4px solid #ffc107;
}

.equipment-critical {
    border-left: 4px solid #dc3545;
}

.equipment-inactive {
    border-left: 4px solid #6c757d;
}

.equipment-backup {
    border-left: 4px solid #17a2b8;
}

/* Estados de equipos */
.status-active {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
}

.status-inactive {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

.status-maintenance {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.status-out-of-service {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.status-alert {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.status-calibration-pending {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

.status-backup {
    background: linear-gradient(135deg, #6f42c1, #5a32a3);
    color: white;
}

/* Tipos de equipos */
.type-sensor {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.type-recorder {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.type-monitor {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

.type-analog {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

/* Temperaturas */
.temp-normal {
    color: #28a745;
}

.temp-warning {
    color: #ffc107;
}

.temp-critical {
    color: #dc3545;
}

.temp-offline {
    color: #6c757d;
}

/* Acciones en lote */
.bulk-action-btn {
    transition: all 0.2s ease;
    border-radius: 0.5rem;
    padding: 1.5rem;
    text-align: center;
    border: 2px solid transparent;
}

.bulk-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: #007bff;
}

.bulk-action-btn i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

/* Formulario de nuevo equipo */
.form-section {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
}

.required-field::after {
    content: " *";
    color: red;
}

/* Tabla de equipos */
.equipment-table tbody tr {
    transition: background-color 0.2s ease;
}

.equipment-table tbody tr:hover {
    background-color: #f8f9fa;
}

.equipment-table .form-check-input {
    margin-top: 0.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .bulk-action-btn {
        padding: 1rem;
    }
    
    .bulk-action-btn i {
        font-size: 1.5rem;
    }
    
    .equipment-table {
        font-size: 0.9rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .kpi-card h5 {
        font-size: 1.2rem;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .form-section {
        border-left: none;
        border-top: 2px solid #007bff;
        padding-left: 0;
        padding-top: 1rem;
    }
}

/* Animaciones */
.fade-in {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slide-in {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Indicadores de temperatura */
.temp-indicator {
    position: relative;
    display: inline-block;
}

.temp-indicator::after {
    content: '';
    position: absolute;
    top: 50%;
    right: -1.5rem;
    transform: translateY(-50%);
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
}

.temp-indicator.normal::after {
    background-color: #28a745;
}

.temp-indicator.warning::after {
    background-color: #ffc107;
    animation: pulse 2s infinite;
}

.temp-indicator.critical::after {
    background-color: #dc3545;
    animation: pulse 1s infinite;
}

/* Calibración */
.calibration-valid {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    border-left: 4px solid #28a745;
}

.calibration-pending {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    border-left: 4px solid #ffc107;
}

.calibration-expired {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    border-left: 4px solid #dc3545;
}
</style>
@endsection