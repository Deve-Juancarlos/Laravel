{{-- ==========================================
     VISTA: DASHBOARD CONTROL DE TEMPERATURA
     MÓDULO: Control de Temperatura - Dashboard
     DESARROLLADO POR: MiniMax Agent
     FECHA: 2025-10-25
     DESCRIPCIÓN: Dashboard principal para monitoreo de temperatura en tiempo real,
                  estado de equipos, alertas de temperatura y control de cadena de frío
========================================== --}}

@extends('layouts.app')

@section('title', 'Dashboard Control de Temperatura')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-thermometer-half text-info"></i>
                        Control de Temperatura
                    </h1>
                    <p class="text-muted mb-0">Monitoreo en tiempo real de temperatura y cadena de frío</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success" onclick="exportTemperatureReport()">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="showCalibrationModal()">
                        <i class="fas fa-cog"></i> Calibrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Estado General del Sistema --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0" style="background: linear-gradient(135deg, #28a745, #20c997); color: white;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h4 class="mb-2">
                                <i class="fas fa-shield-alt"></i> Sistema de Monitoreo Activo
                            </h4>
                            <p class="mb-0">Última actualización: {{ date('d/m/Y H:i:s') }} | Próxima verificación: {{ date('d/m/Y H:i', strtotime('+5 minutes')) }}</p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <div class="d-flex align-items-center justify-content-end">
                                <div class="me-3">
                                    <div class="badge bg-light text-dark fs-6">
                                        <i class="fas fa-wifi text-success"></i> Conectado
                                    </div>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-outline-light btn-sm" onclick="pauseMonitoring()">
                                        <i class="fas fa-pause"></i> Pausar
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
            <div class="card border-0 bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-thermometer-half fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($normalTemperatures ?? 18) }}</h5>
                            <small>Lecturas Normales (24h)</small>
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
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($temperatureAlerts ?? 3) }}</h5>
                            <small>Alertas Activas</small>
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
                            <i class="fas fa-snowflake fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($refrigeratorsActive ?? 8) }}/{{ number_format($refrigeratorsTotal ?? 10) }}</h5>
                            <small>Refrigeradores Activos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($complianceRate ?? 97.8, 1) }}%</h5>
                            <small>Tasa de Cumplimiento</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertas Activas --}}
    @if(($temperatureAlerts ?? 3) > 0)
    <div class="alert alert-warning border-0 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
            <div>
                <h6 class="alert-heading mb-1">
                    <strong>Alertas de Temperatura:</strong> {{ $temperatureAlerts ?? 3 }} alertas activas requieren atención
                </h6>
                <p class="mb-0">Se han detectado variaciones de temperatura que requieren revisión inmediata.</p>
            </div>
            <div class="ms-auto">
                <button type="button" class="btn btn-sm btn-outline-warning" onclick="viewActiveAlerts()">
                    Ver Alertas
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Monitoreo en Tiempo Real --}}
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Temperaturas en Tiempo Real - Últimas 24 Horas
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active" onclick="updateTimeRange('1h')">1H</button>
                        <button class="btn btn-outline-primary" onclick="updateTimeRange('6h')">6H</button>
                        <button class="btn btn-outline-primary" onclick="updateTimeRange('24h')">24H</button>
                        <button class="btn btn-outline-primary" onclick="updateTimeRange('7d')">7D</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="realtimeChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-thermometer-half"></i> Estado de Equipos
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Refrigerador Principal --}}
                        <div class="col-12">
                            <div class="border rounded p-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Refrigerador Principal</strong>
                                    <span class="badge bg-success">Normal</span>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 text-primary mb-0">4.2°C</div>
                                            <small class="text-muted">Actual</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 text-info mb-0">3.8-4.5°C</div>
                                            <small class="text-muted">Rango</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-check-circle"></i> Dentro del rango
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Congelador --}}
                        <div class="col-12">
                            <div class="border rounded p-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Congelador</strong>
                                    <span class="badge bg-success">Normal</span>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 text-primary mb-0">-18.5°C</div>
                                            <small class="text-muted">Actual</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 text-info mb-0">-15 a -25°C</div>
                                            <small class="text-muted">Rango</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-check-circle"></i> Dentro del rango
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Cámara Frigorífica --}}
                        <div class="col-12">
                            <div class="border rounded p-3 bg-warning">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Cámara Frigorífica</strong>
                                    <span class="badge bg-warning">Alerta</span>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 text-warning mb-0">6.8°C</div>
                                            <small class="text-muted">Actual</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h4 text-info mb-0">2-5°C</div>
                                            <small class="text-muted">Rango</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Temperatura elevada
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Historial de Eventos y Alertas --}}
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history"></i> Eventos Recientes
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="timeline">
                        {{-- Evento 1 --}}
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Calibración Completada</h6>
                                <p class="timeline-description">Refrigerador Principal - Calibración de sensor completada</p>
                                <small class="text-muted">{{ date('d/m/Y H:i') }} - Q.F. Ana Rodríguez</small>
                            </div>
                        </div>

                        {{-- Evento 2 --}}
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Alerta de Temperatura</h6>
                                <p class="timeline-description">Cámara Frigorífica - Temperatura subió a 6.8°C</p>
                                <small class="text-muted">{{ date('d/m/Y H:i', strtotime('-30 minutes')) }} - Sistema Automático</small>
                            </div>
                        </div>

                        {{-- Evento 3 --}}
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Revisión de Rutina</h6>
                                <p class="timeline-description">Inspección diaria de temperatura completada</p>
                                <small class="text-muted">{{ date('d/m/Y H:i', strtotime('-2 hours')) }} - L. Valencia</small>
                            </div>
                        </div>

                        {{-- Evento 4 --}}
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Mantención Preventiva</h6>
                                <p class="timeline-description">Mantenimiento programado del refrigerador de vacunas</p>
                                <small class="text-muted">{{ date('d/m/Y H:i', strtotime('-4 hours')) }} - Técnico de Mantenimiento</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bell"></i> Alertas Pendientes
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        {{-- Alerta 1 --}}
                        <div class="list-group-item list-group-item-warning">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-2">
                                    <i class="fas fa-thermometer-half text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Temperatura Elevada</h6>
                                    <p class="mb-1">Cámara Frigorífica: 6.8°C (Límite: 5°C)</p>
                                    <small class="text-muted">{{ date('d/m/Y H:i', strtotime('-30 minutes')) }}</small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="acknowledgeAlert(1)">Reconocer</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="escalateAlert(1)">Escalar</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="viewAlertDetails(1)">Ver Detalles</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Alerta 2 --}}
                        <div class="list-group-item list-group-item-info">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-2">
                                    <i class="fas fa-calendar text-info"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Calibración Vencida</h6>
                                    <p class="mb-1">Sensor Refrigerador Laboratorio - Vence: {{ date('d/m/Y', strtotime('+5 days')) }}</p>
                                    <small class="text-muted">{{ date('d/m/Y H:i', strtotime('-1 hour')) }}</small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="scheduleCalibration(2)">Programar Calibración</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="viewCalibrationHistory(2)">Ver Historial</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Alerta 3 --}}
                        <div class="list-group-item list-group-item-danger">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-2">
                                    <i class="fas fa-wifi text-danger"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Sensor Desconectado</h6>
                                    <p class="mb-1">Termómetro Nevera Vacunas - Sin comunicación</p>
                                    <small class="text-muted">{{ date('d/m/Y H:i', strtotime('-2 hours')) }}</small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-danger dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="restartSensor(3)">Reiniciar Sensor</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="contactTechnical(3)">Contactar Técnico</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Configuración de Rangos de Temperatura --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-sliders-h"></i> Configuración de Rangos de Temperatura
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        {{-- Rangos de Medicamentos --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center">
                                <h6 class="text-primary">Medicamentos Generales</h6>
                                <div class="temperature-range">
                                    <div class="range-bar bg-primary"></div>
                                    <div class="range-labels">
                                        <span>15°C</span>
                                        <span>25°C</span>
                                    </div>
                                </div>
                                <small class="text-muted">Rango: 15°C - 25°C</small>
                                <div class="mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-check-circle"></i> {{ number_format($generalMedsCompliance ?? 98.5, 1) }}% cumplimiento
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Rangos de Vacunas --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center">
                                <h6 class="text-info">Vacunas</h6>
                                <div class="temperature-range">
                                    <div class="range-bar bg-info"></div>
                                    <div class="range-labels">
                                        <span>2°C</span>
                                        <span>8°C</span>
                                    </div>
                                </div>
                                <small class="text-muted">Rango: 2°C - 8°C</small>
                                <div class="mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-check-circle"></i> {{ number_format($vaccinesCompliance ?? 96.2, 1) }}% cumplimiento
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Rangos de Insulinas --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center">
                                <h6 class="text-warning">Insulinas</h6>
                                <div class="temperature-range">
                                    <div class="range-bar bg-warning"></div>
                                    <div class="range-labels">
                                        <span>2°C</span>
                                        <span>8°C</span>
                                    </div>
                                </div>
                                <small class="text-muted">Rango: 2°C - 8°C</small>
                                <div class="mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-check-circle"></i> {{ number_format($insulinCompliance ?? 94.8, 1) }}% cumplimiento
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Rangos Congelados --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center">
                                <h6 class="text-secondary">Productos Congelados</h6>
                                <div class="temperature-range">
                                    <div class="range-bar bg-secondary"></div>
                                    <div class="range-labels">
                                        <span>-15°C</span>
                                        <span>-25°C</span>
                                    </div>
                                </div>
                                <small class="text-muted">Rango: -15°C - -25°C</small>
                                <div class="mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-check-circle"></i> {{ number_format($frozenCompliance ?? 99.1, 1) }}% cumplimiento
                                    </small>
                                </div>
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
                            <button type="button" class="btn btn-outline-primary w-100" onclick="startManualReading()">
                                <i class="fas fa-thermometer-half d-block mb-2"></i>
                                Lectura Manual
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button type="button" class="btn btn-outline-success w-100" onclick="exportData()">
                                <i class="fas fa-download d-block mb-2"></i>
                                Exportar Datos
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button type="button" class="btn btn-outline-info w-100" onclick="generateReport()">
                                <i class="fas fa-chart-bar d-block mb-2"></i>
                                Generar Reporte
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button type="button" class="btn btn-outline-warning w-100" onclick="showCalibrationModal()">
                                <i class="fas fa-cog d-block mb-2"></i>
                                Calibrar
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="configureAlerts()">
                                <i class="fas fa-bell d-block mb-2"></i>
                                Configurar Alertas
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="emergencyShutdown()">
                                <i class="fas fa-power-off d-block mb-2"></i>
                                Paro de Emergencia
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

{{-- Modal de Calibración --}}
<div class="modal fade" id="calibrationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cog"></i> Calibración de Sensores
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="calibrationForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Sensor a Calibrar</label>
                            <select class="form-select" id="sensorSelect" required>
                                <option value="">Seleccionar sensor</option>
                                <option value="refrigerator_main">Refrigerador Principal</option>
                                <option value="freezer_main">Congelador Principal</option>
                                <option value="cold_chamber">Cámara Frigorífica</option>
                                <option value="vaccine_fridge">Refrigerador de Vacunas</option>
                                <option value="lab_fridge">Refrigerador Laboratorio</option>
                            </select>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Temperatura de Referencia (°C)</label>
                            <input type="number" class="form-control" id="referenceTemp" step="0.1" placeholder="4.0" required>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Temperatura Actual del Sensor (°C)</label>
                            <input type="number" class="form-control" id="sensorTemp" step="0.1" placeholder="4.2" required>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="calibrationNotes" rows="3" placeholder="Observaciones sobre la calibración..."></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Técnico Responsable</label>
                            <select class="form-select" id="technician" required>
                                <option value="">Seleccionar técnico</option>
                                <option value="ana_rodriguez">Q.F. Ana Rodríguez</option>
                                <option value="luis_valencia">Luis Valencia</option>
                                <option value="carlos_mendoza">Dr. Carlos Mendoza</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requiresExternalVerification" checked>
                                <label class="form-check-label" for="requiresExternalVerification">
                                    Requiere verificación externa
                                </label>
                            </div>
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

{{-- Modal de Alertas Activas --}}
<div class="modal fade" id="activeAlertsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Alertas Activas de Temperatura
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Hora</th>
                                <th>Sensor</th>
                                <th>Ubicación</th>
                                <th>Temperatura</th>
                                <th>Límite</th>
                                <th>Severidad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-warning">
                                <td>{{ date('H:i', strtotime('-30 minutes')) }}</td>
                                <td>Sensor-003</td>
                                <td>Cámara Frigorífica</td>
                                <td><span class="badge bg-warning">6.8°C</span></td>
                                <td>5.0°C</td>
                                <td><span class="badge bg-warning">Media</span></td>
                                <td><span class="badge bg-warning">Activa</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="acknowledgeAlert(1)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="table-info">
                                <td>{{ date('H:i', strtotime('-1 hour')) }}</td>
                                <td>Sensor-005</td>
                                <td>Refrigerador Laboratorio</td>
                                <td><span class="badge bg-info">Cal. Pendiente</span></td>
                                <td>-</td>
                                <td><span class="badge bg-info">Baja</span></td>
                                <td><span class="badge bg-info">Pendiente</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" onclick="scheduleCalibration(2)">
                                        <i class="fas fa-calendar"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="table-danger">
                                <td>{{ date('H:i', strtotime('-2 hours')) }}</td>
                                <td>Sensor-007</td>
                                <td>Nevera Vacunas</td>
                                <td><span class="badge bg-danger">Sin señal</span></td>
                                <td>-</td>
                                <td><span class="badge bg-danger">Alta</span></td>
                                <td><span class="badge bg-danger">Crítica</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger" onclick="contactTechnical(3)">
                                        <i class="fas fa-phone"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-primary" onclick="exportAlerts()">
                    <i class="fas fa-file-export"></i> Exportar Alertas
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Lectura Manual --}}
<div class="modal fade" id="manualReadingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-thermometer-half"></i> Lectura Manual de Temperatura
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="manualReadingForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ubicación del Equipo</label>
                        <select class="form-select" id="equipmentLocation" required>
                            <option value="">Seleccionar ubicación</option>
                            <option value="refrigerator_main">Refrigerador Principal</option>
                            <option value="freezer_main">Congelador Principal</option>
                            <option value="cold_chamber">Cámara Frigorífica</option>
                            <option value="vaccine_fridge">Refrigerador de Vacunas</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Temperatura Leída (°C)</label>
                        <input type="number" class="form-control" id="manualTemp" step="0.1" placeholder="4.2" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" id="manualObservations" rows="3" placeholder="Observaciones sobre la lectura..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Responsable</label>
                        <select class="form-select" id="manualResponsible" required>
                            <option value="">Seleccionar responsable</option>
                            <option value="luis_valencia">Luis Valencia</option>
                            <option value="ana_rodriguez">Q.F. Ana Rodríguez</option>
                            <option value="maria_gonzalez">María González</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Registrar Lectura
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
    // Inicializar gráfico en tiempo real
    initializeRealtimeChart();
    
    // Configurar actualización automática
    setupAutoRefresh();
    
    // Event listeners
    setupEventListeners();
});

function initializeRealtimeChart() {
    const ctx = document.getElementById('realtimeChart').getContext('2d');
    
    // Generar datos de ejemplo para las últimas 24 horas
    const now = new Date();
    const labels = [];
    const tempsMain = [];
    const tempsVaccine = [];
    const tempsFreezer = [];
    
    for (let i = 23; i >= 0; i--) {
        const time = new Date(now.getTime() - (i * 60 * 60 * 1000));
        labels.push(time.getHours() + ':00');
        
        // Simular temperaturas con variaciones realistas
        tempsMain.push(4.2 + (Math.random() - 0.5) * 0.8);
        tempsVaccine.push(5.2 + (Math.random() - 0.5) * 1.2);
        tempsFreezer.push(-18.5 + (Math.random() - 0.5) * 2.0);
    }
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Refrigerador Principal',
                data: tempsMain,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: false
            }, {
                label: 'Refrigerador Vacunas',
                data: tempsVaccine,
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: false
            }, {
                label: 'Congelador',
                data: tempsFreezer,
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                tension: 0.4,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Hora'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Temperatura (°C)'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

function setupAutoRefresh() {
    // Actualizar cada 30 segundos
    setInterval(updateDashboardData, 30000);
}

function setupEventListeners() {
    // Event listeners para botones de rango de tiempo
    const timeRangeButtons = document.querySelectorAll('[onclick*="updateTimeRange"]');
    timeRangeButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover clase active de todos los botones
            timeRangeButtons.forEach(btn => btn.classList.remove('active'));
            // Agregar clase active al botón clickeado
            this.classList.add('active');
        });
    });
}

// Funciones de actualización
function updateTimeRange(range) {
    console.log('Actualizando rango de tiempo a:', range);
    
    Swal.fire({
        title: 'Actualizando...',
        text: 'Cargando datos para el rango seleccionado',
        icon: 'info',
        timer: 1000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Aquí se cargaría nueva data según el rango
    setTimeout(() => {
        showNotification('Datos actualizados para ' + range, 'success');
    }, 1000);
}

function updateDashboardData() {
    // Simular actualización de datos en tiempo real
    console.log('Actualizando datos del dashboard...');
    
    // Aquí se actualizarían los valores en tiempo real
    // Por ejemplo, actualizar temperaturas, alertas, etc.
}

// Funciones de acciones
function viewActiveAlerts() {
    $('#activeAlertsModal').modal('show');
}

function showCalibrationModal() {
    $('#calibrationModal').modal('show');
}

function startManualReading() {
    $('#manualReadingModal').modal('show');
}

function pauseMonitoring() {
    Swal.fire({
        title: 'Pausar Monitoreo',
        text: '¿Está seguro de pausar el monitoreo automático?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, pausar',
        cancelButtonText: 'Continuar monitoreo'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Monitoreo pausado. El sistema no registrará nuevas alertas.', 'warning');
        }
    });
}

function exportTemperatureReport() {
    Swal.fire({
        title: 'Exportar Reporte',
        text: '¿Desea exportar el reporte de temperatura?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Generando Reporte...',
                text: 'Procesando datos de temperatura',
                icon: 'info',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            setTimeout(() => {
                showNotification('Reporte de temperatura exportado exitosamente', 'success');
            }, 3000);
        }
    });
}

// Funciones de alertas
function acknowledgeAlert(alertId) {
    Swal.fire({
        title: 'Reconocer Alerta',
        text: '¿Está seguro de reconocer esta alerta?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reconocer',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Alerta reconocida exitosamente', 'success');
        }
    });
}

function escalateAlert(alertId) {
    Swal.fire({
        title: 'Escalar Alerta',
        text: '¿Desea escalar esta alerta al siguiente nivel?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, escalar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Alerta escalada al supervisor', 'warning');
        }
    });
}

function viewAlertDetails(alertId) {
    Swal.fire({
        title: 'Detalles de la Alerta',
        html: `
            <div class="text-start">
                <h6>Información de la Alerta</h6>
                <table class="table table-sm">
                    <tr><td><strong>Sensor:</strong></td><td>Sensor-003</td></tr>
                    <tr><td><strong>Ubicación:</strong></td><td>Cámara Frigorífica</td></tr>
                    <tr><td><strong>Temperatura:</strong></td><td>6.8°C</td></tr>
                    <tr><td><strong>Límite:</strong></td><td>5.0°C</td></tr>
                    <tr><td><strong>Fecha/Hora:</strong></td><td>${new Date().toLocaleString('es-ES')}</td></tr>
                    <tr><td><strong>Duración:</strong></td><td>30 minutos</td></tr>
                    <tr><td><strong>Severidad:</strong></td><td><span class="badge bg-warning">Media</span></td></tr>
                </table>
            </div>
        `,
        width: '600px'
    });
}

function scheduleCalibration(alertId) {
    showCalibrationModal();
}

function viewCalibrationHistory(sensorId) {
    Swal.fire({
        title: 'Historial de Calibración',
        text: 'Mostrando historial de calibración del sensor',
        icon: 'info'
    });
}

function restartSensor(sensorId) {
    Swal.fire({
        title: 'Reiniciar Sensor',
        text: '¿Desea reiniciar la conexión del sensor?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reiniciar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Reiniciando sensor...', 'info');
            setTimeout(() => {
                showNotification('Sensor reiniciado exitosamente', 'success');
            }, 2000);
        }
    });
}

function contactTechnical(sensorId) {
    Swal.fire({
        title: 'Contactar Técnico',
        text: 'Llamando al técnico de mantenimiento...',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

// Funciones de acciones rápidas
function exportData() {
    exportTemperatureReport();
}

function generateReport() {
    Swal.fire({
        title: 'Generando Reporte...',
        text: 'Creando reporte de temperatura y cumplimiento',
        icon: 'info',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        showNotification('Reporte generado exitosamente', 'success');
    }, 3000);
}

function configureAlerts() {
    Swal.fire({
        title: 'Configurar Alertas',
        text: 'Abriendo configuración de alertas de temperatura',
        icon: 'info'
    });
}

function emergencyShutdown() {
    Swal.fire({
        title: 'PARO DE EMERGENCIA',
        text: '¿Está seguro de ejecutar un paro de emergencia? Esta acción detendrá todos los equipos de refrigeración.',
        icon: 'error',
        showCancelButton: true,
        confirmButtonText: 'SÍ, EJECUTAR PARO',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        showDenyButton: true,
        denyButtonText: 'Solo Refrigerador Principal',
        denyButtonColor: '#ffc107'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('PARO DE EMERGENCIA EJECUTADO - TODOS LOS EQUIPOS DETENIDOS', 'error');
        } else if (result.isDenied) {
            showNotification('Refrigerador principal detenido', 'warning');
        }
    });
}

// Formularios
$('#calibrationForm').on('submit', function(e) {
    e.preventDefault();
    
    const sensor = $('#sensorSelect').val();
    const referenceTemp = $('#referenceTemp').val();
    const sensorTemp = $('#sensorTemp').val();
    const notes = $('#calibrationNotes').val();
    const technician = $('#technician').val();
    
    if (!sensor || !referenceTemp || !sensorTemp || !technician) {
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
        $('#calibrationForm')[0].reset();
    }, 2000);
});

$('#manualReadingForm').on('submit', function(e) {
    e.preventDefault();
    
    const location = $('#equipmentLocation').val();
    const temp = $('#manualTemp').val();
    const observations = $('#manualObservations').val();
    const responsible = $('#manualResponsible').val();
    
    if (!location || !temp || !responsible) {
        showNotification('Por favor complete todos los campos requeridos', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Registrando Lectura...',
        text: 'Guardando lectura manual de temperatura',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Lectura Registrada',
            text: 'La lectura manual ha sido registrada exitosamente.',
            showConfirmButton: false,
            timer: 2000
        });
        
        $('#manualReadingModal').modal('hide');
        $('#manualReadingForm')[0].reset();
    }, 2000);
});

// Funciones de utilidad
function exportAlerts() {
    showNotification('Exportando alertas activas...', 'info');
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
/* Estilos para dashboard de temperatura */
.system-active {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.system-inactive {
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

/* Timeline para eventos */
.timeline {
    position: relative;
    padding: 1rem 0;
}

.timeline-item {
    position: relative;
    padding-left: 3rem;
    padding-bottom: 2rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.timeline-title {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
}

.timeline-description {
    margin-bottom: 0.5rem;
    font-size: 0.85rem;
    color: #6c757d;
}

/* Rangos de temperatura */
.temperature-range {
    position: relative;
    height: 20px;
    background: linear-gradient(to right, #17a2b8, #007bff, #ffc107, #dc3545);
    border-radius: 10px;
    margin: 1rem 0;
}

.range-bar {
    position: absolute;
    height: 100%;
    background: rgba(255,255,255,0.3);
    border-radius: 10px;
    top: 0;
}

.range-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

/* Estados de equipos */
.equipment-normal {
    border-left: 4px solid #28a745;
}

.equipment-warning {
    border-left: 4px solid #ffc107;
}

.equipment-critical {
    border-left: 4px solid #dc3545;
}

.equipment-offline {
    border-left: 4px solid #6c757d;
}

/* Temperatura en tiempo real */
.temp-current {
    font-size: 1.5rem;
    font-weight: bold;
}

.temp-range {
    font-size: 0.9rem;
    color: #6c757d;
}

.temp-status {
    font-size: 0.8rem;
    margin-top: 0.5rem;
}

/* Alertas activas */
.alert-item {
    border-left: 4px solid;
    padding: 1rem;
    margin-bottom: 0;
}

.alert-warning {
    border-left-color: #ffc107;
    background-color: #fff8e1;
}

.alert-info {
    border-left-color: #17a2b8;
    background-color: #e3f2fd;
}

.alert-danger {
    border-left-color: #dc3545;
    background-color: #ffebee;
}

/* Acciones rápidas */
.quick-action-btn {
    transition: all 0.2s ease;
    border-radius: 0.5rem;
    padding: 1rem;
    text-align: center;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.quick-action-btn i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .kpi-card h5 {
        font-size: 1.2rem;
    }
    
    .timeline-item {
        padding-left: 2rem;
    }
    
    .timeline-marker {
        width: 0.75rem;
        height: 0.75rem;
    }
    
    .quick-action-btn {
        padding: 0.75rem;
    }
    
    .quick-action-btn i {
        font-size: 1.2rem;
    }
}

@media (max-width: 576px) {
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .temperature-range {
        height: 15px;
    }
    
    .temp-current {
        font-size: 1.2rem;
    }
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

/* Estados de conexión */
.connection-online {
    color: #28a745;
}

.connection-offline {
    color: #dc3545;
}

.connection-warning {
    color: #ffc107;
}
</style>
@endsection