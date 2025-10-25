{{-- ==========================================
     VISTA: GESTIÓN DE ALERTAS DE TEMPERATURA
     MÓDULO: Control de Temperatura - Alertas
     DESARROLLADO POR: MiniMax Agent
     FECHA: 2025-10-25
     DESCRIPCIÓN: Configuración completa de alertas de temperatura,
                  umbrales, notificaciones y escalamiento según normativa DIGEMID
========================================== --}}

@extends('layouts.app')

@section('title', 'Alertas de Temperatura - Control de Temperatura')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-bell text-warning"></i>
                        Alertas de Temperatura
                    </h1>
                    <p class="text-muted mb-0">Configuración y gestión de alertas del sistema de monitoreo</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success" onclick="testAlertSystem()">
                        <i class="fas fa-vial"></i> Probar Alertas
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="exportAlertHistory()">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="showNewAlertModal()">
                        <i class="fas fa-plus"></i> Nueva Alerta
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Estado del Sistema de Alertas --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-play-circle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($activeAlerts ?? 5) }}</h5>
                            <small>Alertas Activas</small>
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
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($pendingAlerts ?? 3) }}</h5>
                            <small>Pendientes</small>
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
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($notificationsSent ?? 47) }}</h5>
                            <small>Notificaciones Enviadas</small>
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
                            <i class="fas fa-cog fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($configuredRules ?? 12) }}</h5>
                            <small>Reglas Configuradas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estado del Sistema --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="card-title mb-0">
                <i class="fas fa-shield-alt"></i> Estado del Sistema de Alertas
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="badge bg-success p-3 rounded-circle">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Sistema de Alertas Operativo</h6>
                            <p class="text-muted mb-0">Última verificación: {{ date('d/m/Y H:i:s') }}</p>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> Funcionando correctamente - 24/7
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" onclick="toggleAlertSystem(true)" id="startSystemBtn">
                            <i class="fas fa-play"></i> Activar Sistema
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleAlertSystem(false)" id="stopSystemBtn" style="display: none;">
                            <i class="fas fa-stop"></i> Desactivar Sistema
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="runAlertTest()">
                            <i class="fas fa-sync"></i> Ejecutar Prueba
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Configuración de Reglas de Alerta --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-list"></i> Reglas de Alerta Configuradas
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="showNewRuleModal()">
                        <i class="fas fa-plus"></i> Nueva Regla
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="alertRulesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre de la Regla</th>
                                    <th>Equipo/Área</th>
                                    <th>Umbral Mín</th>
                                    <th>Umbral Máx</th>
                                    <th>Severidad</th>
                                    <th>Canales</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Ejemplos de reglas --}}
                                <tr class="table-success">
                                    <td>
                                        <div class="fw-bold">Refrigerador Principal</div>
                                        <small class="text-muted">Alerta por temperatura fuera de rango</small>
                                    </td>
                                    <td>EQ-001 - Refrig. Principal</td>
                                    <td>2.0°C</td>
                                    <td>8.0°C</td>
                                    <td>
                                        <span class="badge bg-danger">Crítica</span>
                                    </td>
                                    <td>
                                        <i class="fas fa-envelope text-primary"></i>
                                        <i class="fas fa-sms text-info"></i>
                                        <i class="fas fa-bell text-warning"></i>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Activa</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editAlertRule(1)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" onclick="testAlertRule(1)">
                                                <i class="fas fa-vial"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteAlertRule(1)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="table-success">
                                    <td>
                                        <div class="fw-bold">Congelador Vacunas</div>
                                        <small class="text-muted">Control de cadena de frío</small>
                                    </td>
                                    <td>EQ-002 - Congelador</td>
                                    <td>-25.0°C</td>
                                    <td>-15.0°C</td>
                                    <td>
                                        <span class="badge bg-danger">Crítica</span>
                                    </td>
                                    <td>
                                        <i class="fas fa-envelope text-primary"></i>
                                        <i class="fas fa-phone text-success"></i>
                                        <i class="fas fa-bell text-warning"></i>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Activa</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editAlertRule(2)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" onclick="testAlertRule(2)">
                                                <i class="fas fa-vial"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteAlertRule(2)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="table-warning">
                                    <td>
                                        <div class="fw-bold">Cámara Frigorífica</div>
                                        <small class="text-muted">Alerta por temperatura elevada</small>
                                    </td>
                                    <td>EQ-003 - Cámara Frig.</td>
                                    <td>0.0°C</td>
                                    <td>5.0°C</td>
                                    <td>
                                        <span class="badge bg-warning">Advertencia</span>
                                    </td>
                                    <td>
                                        <i class="fas fa-envelope text-primary"></i>
                                        <i class="fas fa-bell text-warning"></i>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Activa</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editAlertRule(3)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" onclick="testAlertRule(3)">
                                                <i class="fas fa-vial"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteAlertRule(3)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="table-info">
                                    <td>
                                        <div class="fw-bold">Laboratorio</div>
                                        <small class="text-muted">Control de temperatura ambiente</small>
                                    </td>
                                    <td>EQ-004 - Lab. Control</td>
                                    <td>18.0°C</td>
                                    <td>25.0°C</td>
                                    <td>
                                        <span class="badge bg-info">Información</span>
                                    </td>
                                    <td>
                                        <i class="fas fa-envelope text-primary"></i>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Activa</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editAlertRule(4)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" onclick="testAlertRule(4)">
                                                <i class="fas fa-vial"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteAlertRule(4)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="table-secondary">
                                    <td>
                                        <div class="fw-bold">Sensor Desconectado</div>
                                        <small class="text-muted">Alerta por pérdida de comunicación</small>
                                    </td>
                                    <td>EQ-005 - Backup Sensor</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>
                                        <span class="badge bg-secondary">Sistema</span>
                                    </td>
                                    <td>
                                        <i class="fas fa-envelope text-primary"></i>
                                        <i class="fas fa-phone text-success"></i>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">Inactiva</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editAlertRule(5)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-success" onclick="activateAlertRule(5)">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteAlertRule(5)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertas Recientes --}}
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history"></i> Alertas Recientes
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        {{-- Alerta 1 --}}
                        <div class="list-group-item list-group-item-danger">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-2">
                                    <i class="fas fa-thermometer-full text-danger"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1">Temperatura Crítica</h6>
                                        <small class="text-muted">{{ date('d/m/Y H:i') }}</small>
                                    </div>
                                    <p class="mb-1">
                                        <strong>Cámara Frigorífica:</strong> Temperatura subió a 6.8°C 
                                        (Límite: 5.0°C)
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-danger me-2">Crítica</span>
                                        <small class="text-muted">
                                            Duración: 45 minutos | Equipo: EQ-003
                                        </small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-danger dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="acknowledgeAlert(1)">Reconocer</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="escalateAlert(1)">Escalar</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="resolveAlert(1)">Resolver</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Alerta 2 --}}
                        <div class="list-group-item list-group-item-warning">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-2">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1">Temperatura Elevada</h6>
                                        <small class="text-muted">{{ date('d/m/Y H:i', strtotime('-30 minutes')) }}</small>
                                    </div>
                                    <p class="mb-1">
                                        <strong>Refrigerador Vacunas:</strong> Temperatura en 7.2°C 
                                        (Alerta: > 7°C)
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-warning me-2">Advertencia</span>
                                        <small class="text-muted">
                                            Duración: 30 minutos | Equipo: EQ-004
                                        </small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="acknowledgeAlert(2)">Reconocer</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="ignoreAlert(2)">Ignorar</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="configureAutoResponse(2)">Configurar Auto</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Alerta 3 --}}
                        <div class="list-group-item list-group-item-info">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-2">
                                    <i class="fas fa-calendar-times text-info"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1">Calibración Próxima</h6>
                                        <small class="text-muted">{{ date('d/m/Y H:i', strtotime('-1 hour')) }}</small>
                                    </div>
                                    <p class="mb-1">
                                        <strong>Refrigerador Laboratorio:</strong> Calibración vence en 5 días
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-info me-2">Información</span>
                                        <small class="text-muted">
                                            Equipo: EQ-005 | Próxima: {{ date('d/m/Y', strtotime('+5 days')) }}
                                        </small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="scheduleCalibration(3)">Programar</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="extendCalibration(3)">Extender</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="viewCalibrationHistory(3)">Ver Historial</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Alerta 4 --}}
                        <div class="list-group-item list-group-item-secondary">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-2">
                                    <i class="fas fa-wifi text-secondary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1">Sensor Desconectado</h6>
                                        <small class="text-muted">{{ date('d/m/Y H:i', strtotime('-2 hours')) }}</small>
                                    </div>
                                    <p class="mb-1">
                                        <strong>Nevera Vacunas Backup:</strong> Pérdida de comunicación con sensor
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-secondary me-2">Sistema</span>
                                        <small class="text-muted">
                                            Equipo: EQ-006 | Sin señal por 2 horas
                                        </small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="restartConnection(4)">Reiniciar</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="contactTechnical(4)">Contactar Técnico</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="switchToBackup(4)">Usar Backup</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-tachometer-alt"></i> Estadísticas de Alertas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="text-center">
                                <h3 class="text-danger">{{ number_format($alertsLast24h ?? 12) }}</h3>
                                <p class="text-muted">Alertas Últimas 24h</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-center">
                                <h3 class="text-warning">{{ number_format($avgResponseTime ?? 8) }} min</h3>
                                <p class="text-muted">Tiempo Respuesta Promedio</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-center">
                                <h3 class="text-success">{{ number_format($resolutionRate ?? 94.2, 1) }}%</h3>
                                <p class="text-muted">Tasa de Resolución</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-center">
                                <h3 class="text-info">{{ number_format($falsePositives ?? 3) }}%</h3>
                                <p class="text-muted">Falsos Positivos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Configuración de Canales de Notificación --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="card-title mb-0">
                <i class="fas fa-paper-plane"></i> Configuración de Notificaciones
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-4">
                {{-- Email --}}
                <div class="col-lg-4">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6><i class="fas fa-envelope text-primary"></i> Notificaciones por Email</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Servidor SMTP</label>
                                <input type="text" class="form-control" value="smtp.hospital.com" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Puerto</label>
                                <input type="text" class="form-control" value="587" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Destinatarios</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="emailDirector" checked>
                                    <label class="form-check-label" for="emailDirector">Director de Farmacia</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="emailQF" checked>
                                    <label class="form-check-label" for="emailQF">Q.F. Principal</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="emailTechnician">
                                    <label class="form-check-label" for="emailTechnician">Técnico de Mantenimiento</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SMS --}}
                <div class="col-lg-4">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6><i class="fas fa-sms text-info"></i> Notificaciones por SMS</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="smsNotifications" checked>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Proveedor SMS</label>
                                <input type="text" class="form-control" value="Twilio API" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Saldo</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="S/ 45.00" readonly>
                                    <span class="input-group-text">
                                        <i class="fas fa-credit-card text-success"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Números de Emergencia</label>
                                <input type="text" class="form-control" value="+51 999 888 777" readonly>
                                <small class="text-muted">Director (Críticas)</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Push/Desktop --}}
                <div class="col-lg-4">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6><i class="fas fa-bell text-warning"></i> Notificaciones Push</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="pushNotifications" checked>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sonido</label>
                                <select class="form-select">
                                    <option value="critical" selected>Solo Críticas</option>
                                    <option value="all">Todas las Alertas</option>
                                    <option value="none">Silencioso</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dispositivos Conectados</label>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-desktop text-primary me-2"></i>
                                    <span class="small">PC Principal (Activo)</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-mobile-alt text-info me-2"></i>
                                    <span class="small">Móvil Q.F. Ana (Activo)</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-tablet-alt text-success me-2"></i>
                                    <span class="small">Tablet Mantenimiento (Inactivo)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Programación de Alertas --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-clock"></i> Programación de Alertas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <h6>Horarios Activos</h6>
                            <div class="border rounded p-3">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="24hours" checked>
                                    <label class="form-check-label" for="24hours">
                                        Monitoreo 24/7 (Recomendado)
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="businessHours">
                                    <label class="form-check-label" for="businessHours">
                                        Solo horario de oficina (08:00 - 18:00)
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="customSchedule">
                                    <label class="form-check-label" for="customSchedule">
                                        Programación personalizada
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <h6>Frecuencia de Verificación</h6>
                            <div class="border rounded p-3">
                                <div class="mb-3">
                                    <label class="form-label">Intervalo de Monitoreo</label>
                                    <select class="form-select">
                                        <option value="1" selected>Cada 1 minuto</option>
                                        <option value="5">Cada 5 minutos</option>
                                        <option value="10">Cada 10 minutos</option>
                                        <option value="30">Cada 30 minutos</option>
                                    </select>
                                    <small class="text-muted">Intervalo entre lecturas de temperatura</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Tiempo de Respuesta</label>
                                    <select class="form-select">
                                        <option value="30">30 segundos</option>
                                        <option value="60" selected>1 minuto</option>
                                        <option value="120">2 minutos</option>
                                        <option value="300">5 minutos</option>
                                    </select>
                                    <small class="text-muted">Tiempo para enviar alerta después de detectar problema</small>
                                </div>
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

{{-- Modal de Nueva Alerta --}}
<div class="modal fade" id="newAlertModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Nueva Regla de Alerta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="newAlertForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre de la Regla *</label>
                            <input type="text" class="form-control" id="ruleName" placeholder="Ej: Temperatura Refrigerador Principal" required>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" id="ruleDescription" rows="2" placeholder="Descripción de la regla de alerta..."></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Equipo/Sensor *</label>
                            <select class="form-select" id="ruleEquipment" required>
                                <option value="">Seleccionar equipo</option>
                                <option value="EQ-001">EQ-001 - Sensor Principal Refrig.</option>
                                <option value="EQ-002">EQ-002 - Sensor Congelador</option>
                                <option value="EQ-003">EQ-003 - Termómetro Cámara</option>
                                <option value="EQ-004">EQ-004 - Sensor Vacunas</option>
                                <option value="EQ-005">EQ-005 - Termómetro Backup</option>
                            </select>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Temperatura Mínima (°C)</label>
                            <input type="number" class="form-control" id="minTemperature" step="0.1" placeholder="-10.0">
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Temperatura Máxima (°C)</label>
                            <input type="number" class="form-control" id="maxTemperature" step="0.1" placeholder="10.0">
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Severidad *</label>
                            <select class="form-select" id="severityLevel" required>
                                <option value="">Seleccionar severidad</option>
                                <option value="critical">Crítica</option>
                                <option value="warning">Advertencia</option>
                                <option value="info">Información</option>
                                <option value="system">Sistema</option>
                            </select>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Duración Mínima (minutos)</label>
                            <input type="number" class="form-control" id="minDuration" min="1" value="5">
                            <small class="text-muted">Tiempo que debe persistir la condición</small>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Canales de Notificación</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notifyEmail" checked>
                                        <label class="form-check-label" for="notifyEmail">
                                            <i class="fas fa-envelope text-primary"></i> Email
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notifySMS" checked>
                                        <label class="form-check-label" for="notifySMS">
                                            <i class="fas fa-sms text-info"></i> SMS
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notifyPush" checked>
                                        <label class="form-check-label" for="notifyPush">
                                            <i class="fas fa-bell text-warning"></i> Push
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Mensaje de Alerta</label>
                            <textarea class="form-control" id="alertMessage" rows="3" placeholder="Ej: {EQUIPMENT}: Temperatura {TEMPERATURE}°C fuera del rango ({MIN}-{MAX}°C)"></textarea>
                            <small class="text-muted">Variables disponibles: {EQUIPMENT}, {TEMPERATURE}, {MIN}, {MAX}, {TIME}</small>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ruleActive" checked>
                                <label class="form-check-label" for="ruleActive">
                                    Activar regla inmediatamente
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="autoEscalation">
                                <label class="form-check-label" for="autoEscalation">
                                    Habilitar escalamiento automático
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-12" id="escalationFields" style="display: none;">
                            <label class="form-label">Tiempo de Escalamiento (minutos)</label>
                            <input type="number" class="form-control" id="escalationTime" min="5" value="30">
                            <small class="text-muted">Tiempo antes de escalar a siguiente nivel</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Regla
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de Edición de Regla --}}
<div class="modal fade" id="editRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Regla de Alerta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Funcionalidad de edición en desarrollo</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
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
    $('#alertRulesTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json'
        },
        pageLength: 25,
        order: [[0, 'asc']], // Ordenar por nombre
        columnDefs: [
            { orderable: false, targets: [7] } // Deshabilitar orden en columna de acciones
        ]
    });
}

function setupEventListeners() {
    // Mostrar/ocultar campos de escalamiento
    document.getElementById('autoEscalation').addEventListener('change', function() {
        const escalationFields = document.getElementById('escalationFields');
        if (this.checked) {
            escalationFields.style.display = 'block';
        } else {
            escalationFields.style.display = 'none';
        }
    });
    
    // Configurar toggles de notificaciones
    const notificationToggles = ['emailNotifications', 'smsNotifications', 'pushNotifications'];
    notificationToggles.forEach(id => {
        document.getElementById(id).addEventListener('change', function() {
            console.log(`${id} ${this.checked ? 'activado' : 'desactivado'}`);
        });
    });
}

// Funciones del Sistema
function toggleAlertSystem(start) {
    if (start) {
        Swal.fire({
            title: 'Activar Sistema de Alertas',
            text: '¿Está seguro de activar el sistema automático de alertas?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, activar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('startSystemBtn').style.display = 'none';
                document.getElementById('stopSystemBtn').style.display = 'inline-block';
                showNotification('Sistema de alertas activado exitosamente', 'success');
            }
        });
    } else {
        Swal.fire({
            title: 'Desactivar Sistema de Alertas',
            text: '¿Está seguro de desactivar el sistema automático de alertas?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('startSystemBtn').style.display = 'inline-block';
                document.getElementById('stopSystemBtn').style.display = 'none';
                showNotification('Sistema de alertas desactivado', 'info');
            }
        });
    }
}

function runAlertTest() {
    Swal.fire({
        title: 'Ejecutando Prueba de Sistema...',
        text: 'Verificando funcionamiento de alertas y notificaciones',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Simular prueba del sistema
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Prueba Completada',
            text: 'Sistema funcionando correctamente. Todas las alertas operativas.',
            showConfirmButton: false,
            timer: 3000
        });
    }, 3000);
}

function testAlertSystem() {
    Swal.fire({
        title: 'Probar Sistema de Alertas',
        text: 'Seleccione el tipo de notificación de prueba',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Email de Prueba',
        denyButtonText: 'SMS de Prueba',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            sendTestEmail();
        } else if (result.isDenied) {
            sendTestSMS();
        }
    });
}

function sendTestEmail() {
    Swal.fire({
        title: 'Enviando Email de Prueba...',
        text: 'Se enviará un email de prueba a los destinatarios configurados',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        showNotification('Email de prueba enviado exitosamente', 'success');
    }, 2000);
}

function sendTestSMS() {
    Swal.fire({
        title: 'Enviando SMS de Prueba...',
        text: 'Se enviará un SMS de prueba a los números configurados',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        showNotification('SMS de prueba enviado exitosamente', 'success');
    }, 2000);
}

// Funciones de Gestión de Reglas
function showNewAlertModal() {
    $('#newAlertModal').modal('show');
}

function showNewRuleModal() {
    showNewAlertModal();
}

function editAlertRule(ruleId) {
    $('#editRuleModal').modal('show');
}

function testAlertRule(ruleId) {
    Swal.fire({
        title: 'Probar Regla',
        text: '¿Desea ejecutar una prueba de esta regla de alerta?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, probar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Ejecutando prueba de regla...', 'info');
            setTimeout(() => {
                showNotification('Prueba completada exitosamente', 'success');
            }, 2000);
        }
    });
}

function activateAlertRule(ruleId) {
    Swal.fire({
        title: 'Activar Regla',
        text: '¿Desea activar esta regla de alerta?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, activar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Regla activada exitosamente', 'success');
        }
    });
}

function deleteAlertRule(ruleId) {
    Swal.fire({
        title: 'Eliminar Regla',
        text: '¿Está seguro de eliminar esta regla de alerta?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Regla eliminada exitosamente', 'success');
        }
    });
}

// Funciones de Alertas
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

function resolveAlert(alertId) {
    Swal.fire({
        title: 'Resolver Alerta',
        text: '¿Desea marcar esta alerta como resuelta?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, resolver',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Alerta marcada como resuelta', 'success');
        }
    });
}

function ignoreAlert(alertId) {
    Swal.fire({
        title: 'Ignorar Alerta',
        text: '¿Desea ignorar temporalmente esta alerta?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, ignorar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Alerta ignorada temporalmente', 'info');
        }
    });
}

function configureAutoResponse(alertId) {
    Swal.fire({
        title: 'Configurar Respuesta Automática',
        text: 'Configurando respuesta automática para futuras alertas similares',
        icon: 'info'
    });
}

function scheduleCalibration(alertId) {
    showNotification('Redirigiendo a programación de calibración...', 'info');
    setTimeout(() => {
        window.location.href = '/farmacia/control-temperatura/equipos';
    }, 1000);
}

function extendCalibration(alertId) {
    Swal.fire({
        title: 'Extender Calibración',
        text: '¿Desea extender el plazo de calibración?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, extender',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Calibración extendida exitosamente', 'success');
        }
    });
}

function viewCalibrationHistory(alertId) {
    Swal.fire({
        title: 'Historial de Calibración',
        text: 'Mostrando historial de calibraciones del equipo',
        icon: 'info'
    });
}

function restartConnection(alertId) {
    Swal.fire({
        title: 'Reiniciar Conexión',
        text: '¿Desea reiniciar la conexión del sensor?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reiniciar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Reiniciando conexión del sensor...', 'info');
            setTimeout(() => {
                showNotification('Conexión restaurada exitosamente', 'success');
            }, 3000);
        }
    });
}

function contactTechnical(alertId) {
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
    
    setTimeout(() => {
        showNotification('Técnico contactado exitosamente', 'success');
    }, 2000);
}

function switchToBackup(alertId) {
    Swal.fire({
        title: 'Activar Sensor Backup',
        text: '¿Desea activar el sensor de respaldo?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, activar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Sensor de respaldo activado', 'success');
        }
    });
}

// Funciones de Exportación
function exportAlertHistory() {
    Swal.fire({
        title: 'Exportar Historial',
        text: '¿Desea exportar el historial completo de alertas?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Exportando historial de alertas...', 'info');
            setTimeout(() => {
                showNotification('Historial exportado exitosamente', 'success');
            }, 2000);
        }
    });
}

// Formulario de nueva alerta
$('#newAlertForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: $('#ruleName').val(),
        description: $('#ruleDescription').val(),
        equipment: $('#ruleEquipment').val(),
        minTemp: $('#minTemperature').val(),
        maxTemp: $('#maxTemperature').val(),
        severity: $('#severityLevel').val(),
        duration: $('#minDuration').val(),
        message: $('#alertMessage').val(),
        active: $('#ruleActive').is(':checked'),
        autoEscalation: $('#autoEscalation').is(':checked'),
        escalationTime: $('#escalationTime').val(),
        channels: {
            email: $('#notifyEmail').is(':checked'),
            sms: $('#notifySMS').is(':checked'),
            push: $('#notifyPush').is(':checked')
        }
    };
    
    // Validaciones
    if (!formData.name || !formData.equipment || !formData.severity) {
        showNotification('Por favor complete todos los campos requeridos', 'error');
        return;
    }
    
    if (formData.minTemp && formData.maxTemp && parseFloat(formData.minTemp) >= parseFloat(formData.maxTemp)) {
        showNotification('La temperatura mínima debe ser menor que la máxima', 'error');
        return;
    }
    
    if (!formData.minTemp && !formData.maxTemp) {
        showNotification('Debe especificar al menos una temperatura (mínima o máxima)', 'error');
        return;
    }
    
    if (!formData.channels.email && !formData.channels.sms && !formData.channels.push) {
        showNotification('Debe seleccionar al menos un canal de notificación', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Guardando Regla...',
        text: 'Creando nueva regla de alerta',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Regla Creada',
            text: 'La regla de alerta ha sido creada exitosamente.',
            showConfirmButton: false,
            timer: 2000
        });
        
        $('#newAlertModal').modal('hide');
        $('#newAlertForm')[0].reset();
        document.getElementById('escalationFields').style.display = 'none';
    }, 2000);
});

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
/* Estilos para alertas de temperatura */
.alert-system-active {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.alert-system-inactive {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

/* Estados de reglas */
.rule-active {
    border-left: 4px solid #28a745;
}

.rule-inactive {
    border-left: 4px solid #6c757d;
}

.rule-testing {
    border-left: 4px solid #ffc107;
    animation: pulse 2s infinite;
}

/* Severidades */
.severity-critical {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.severity-warning {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.severity-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

.severity-system {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

/* Alertas activas */
.alert-active {
    background: linear-gradient(135deg, #fff5f5, #ffffff);
    border-left: 4px solid #dc3545;
}

.alert-resolved {
    background: linear-gradient(135deg, #f0fff4, #ffffff);
    border-left: 4px solid #28a745;
}

.alert-acknowledged {
    background: linear-gradient(135deg, #fff3cd, #ffffff);
    border-left: 4px solid #ffc107;
}

.alert-ignored {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-left: 4px solid #6c757d;
}

/* Canales de notificación */
.channel-email {
    color: #007bff;
}

.channel-sms {
    color: #17a2b8;
}

.channel-push {
    color: #ffc107;
}

.channel-phone {
    color: #28a745;
}

/* Estadísticas */
.stat-card {
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

/* Notificaciones push */
.notification-settings {
    border-radius: 0.5rem;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.device-connected {
    color: #28a745;
}

.device-disconnected {
    color: #dc3545;
}

.device-inactive {
    color: #6c757d;
}

/* Programación */
.schedule-active {
    border-left: 4px solid #28a745;
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
}

.schedule-inactive {
    border-left: 4px solid #dc3545;
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
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
    .stat-card h3 {
        font-size: 1.5rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.8rem;
    }
    
    .notification-settings {
        margin-bottom: 1rem;
    }
}

@media (max-width: 576px) {
    .kpi-card h5 {
        font-size: 1.2rem;
    }
    
    .list-group-item {
        padding: 0.75rem 1rem;
    }
    
    .dropdown-menu {
        font-size: 0.9rem;
    }
}

/* Tabla de reglas */
.alert-rules-table tbody tr {
    transition: background-color 0.2s ease;
}

.alert-rules-table tbody tr:hover {
    background-color: #f8f9fa;
}

/* Formulario de nueva alerta */
.form-section {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
}

.required-field::after {
    content: " *";
    color: red;
}

.validation-message {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Toggle switches */
.form-switch .form-check-input {
    width: 2.5rem;
    height: 1.25rem;
}

.form-switch .form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

/* Duración y tiempos */
.duration-indicator {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-radius: 0.5rem;
    padding: 0.5rem;
    text-align: center;
    font-size: 0.875rem;
}

/* Escalamiento */
.escalation-level {
    position: relative;
    padding-left: 2rem;
}

.escalation-level::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    width: 0.5rem;
    height: 0.5rem;
    background-color: currentColor;
    border-radius: 50%;
}

.escalation-level-1 {
    color: #ffc107;
}

.escalation-level-2 {
    color: #fd7e14;
}

.escalation-level-3 {
    color: #dc3545;
}
</style>
@endsection