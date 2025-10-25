{{-- ==========================================
     VISTA: CONFIGURACIÓN DE ALERTAS DE VENCIMIENTO
     MÓDULO: Control de Vencimientos - Alertas
     DESARROLLADO POR: MiniMax Agent
     FECHA: 2025-10-25
     DESCRIPCIÓN: Configuración de alertas automáticas para productos próximos a vencer,
                  notificaciones por email, SMS y panel de control según normativa DIGEMID
========================================== --}}

@extends('layouts.app')

@section('title', 'Alertas de Vencimiento - Control de Vencimientos')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-bell text-warning"></i>
                        Alertas de Vencimiento
                    </h1>
                    <p class="text-muted mb-0">Configuración de alertas automáticas para productos próximos a vencer</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success" onclick="testAlertSystem()">
                        <i class="fas fa-vial"></i> Probar Alertas
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="exportAlertHistory()">
                        <i class="fas fa-file-export"></i> Exportar Historial
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
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ $activeAlerts ?? 24 }}</h5>
                            <small>Alertas Activas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ $pendingAlerts ?? 7 }}</h5>
                            <small>Alertas Pendientes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ $emailsSent ?? 156 }}</h5>
                            <small>Emails Enviados</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-sms fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ $smsSent ?? 43 }}</h5>
                            <small>SMS Enviados</small>
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
                <i class="fas fa-cogs"></i> Estado del Sistema de Alertas
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="badge bg-success p-3 rounded-circle">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Sistema de Alertas Activo</h6>
                            <p class="text-muted mb-0">Última ejecución: {{ $lastExecution ?? '2025-10-25 08:30:00' }}</p>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> Funcionando correctamente
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" onclick="toggleAlertSystem(true)" id="startSystemBtn">
                            <i class="fas fa-play"></i> Iniciar Sistema
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleAlertSystem(false)" id="stopSystemBtn" style="display: none;">
                            <i class="fas fa-stop"></i> Detener Sistema
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="runAlertCheck()">
                            <i class="fas fa-sync"></i> Ejecutar Verificación
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Configuración de Umbrales --}}
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Umbrales de Alerta
                    </h6>
                </div>
                <div class="card-body">
                    <form id="thresholdsForm">
                        <div class="mb-3">
                            <label class="form-label">Alerta Crítica (días antes del vencimiento)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="criticalThreshold" value="7" min="1" max="365">
                                <span class="input-group-text">días</span>
                            </div>
                            <small class="text-muted">Productos que vencen en 7 días o menos</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alerta de Advertencia (días antes del vencimiento)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="warningThreshold" value="30" min="1" max="365">
                                <span class="input-group-text">días</span>
                            </div>
                            <small class="text-muted">Productos que vencen en 30 días o menos</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alerta Informativa (días antes del vencimiento)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="infoThreshold" value="60" min="1" max="365">
                                <span class="input-group-text">días</span>
                            </div>
                            <small class="text-muted">Productos que vencen en 60 días o menos</small>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enableAutomaticActions" checked>
                                <label class="form-check-label" for="enableAutomaticActions">
                                    Acciones automáticas
                                </label>
                            </div>
                            <small class="text-muted">Aplicar acciones automáticas según los umbrales</small>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Umbrales
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-envelope"></i> Configuración de Notificaciones
                    </h6>
                </div>
                <div class="card-body">
                    <form id="notificationsForm">
                        <div class="mb-3">
                            <label class="form-label">Canales de Notificación</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enableEmail" checked>
                                <label class="form-check-label" for="enableEmail">
                                    <i class="fas fa-envelope text-primary"></i> Email
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enableSMS" checked>
                                <label class="form-check-label" for="enableSMS">
                                    <i class="fas fa-sms text-info"></i> SMS
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enablePush" checked>
                                <label class="form-check-label" for="enablePush">
                                    <i class="fas fa-bell text-warning"></i> Notificaciones Push
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enableDashboard" checked>
                                <label class="form-check-label" for="enableDashboard">
                                    <i class="fas fa-tachometer-alt text-success"></i> Panel de Control
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Frecuencia de Verificación</label>
                            <select class="form-select" id="checkFrequency">
                                <option value="hourly">Cada hora</option>
                                <option value="daily" selected>Diario</option>
                                <option value="weekly">Semanal</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Hora de Ejecución (si es diario)</label>
                            <input type="time" class="form-control" id="executionTime" value="08:00">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Destinatarios de Alertas --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="fas fa-users"></i> Destinatarios de Alertas
            </h6>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="showAddRecipientModal()">
                <i class="fas fa-plus"></i> Agregar Destinatario
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Cargo</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Alertas</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="fw-bold">Dr. Carlos Mendoza</div>
                                <small class="text-muted">Director de Farmacia</small>
                            </td>
                            <td>Director</td>
                            <td>carlos.mendoza@hospital.com</td>
                            <td>+51 999 888 777</td>
                            <td>
                                <span class="badge bg-danger">Críticas</span>
                                <span class="badge bg-warning">Advertencias</span>
                            </td>
                            <td>
                                <span class="badge bg-success">Activo</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editRecipient(1)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteRecipient(1)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <div class="fw-bold">Q.F. Ana Rodríguez</div>
                                <small class="text-muted">Químico Farmacéutico</small>
                            </td>
                            <td>Q.F. Principal</td>
                            <td>ana.rodriguez@hospital.com</td>
                            <td>+51 999 777 666</td>
                            <td>
                                <span class="badge bg-danger">Críticas</span>
                                <span class="badge bg-warning">Advertencias</span>
                                <span class="badge bg-info">Informativas</span>
                            </td>
                            <td>
                                <span class="badge bg-success">Activo</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editRecipient(2)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteRecipient(2)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <div class="fw-bold">Luis Valencia</div>
                                <small class="text-muted">Auxiliar de Farmacia</small>
                            </td>
                            <td>Auxiliar</td>
                            <td>luis.valencia@hospital.com</td>
                            <td>+51 999 666 555</td>
                            <td>
                                <span class="badge bg-danger">Críticas</span>
                            </td>
                            <td>
                                <span class="badge bg-success">Activo</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editRecipient(3)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteRecipient(3)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <div class="fw-bold">MaríaGonzález</div>
                                <small class="text-muted">Compras</small>
                            </td>
                            <td>Compras</td>
                            <td>maria.gonzalez@hospital.com</td>
                            <td>+51 999 555 444</td>
                            <td>
                                <span class="badge bg-info">Informativas</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">Inactivo</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editRecipient(4)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteRecipient(4)">
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

    {{-- Historial de Alertas --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="card-title mb-0">
                <i class="fas fa-history"></i> Historial de Alertas Enviadas
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="alertHistoryTable">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Destinatario</th>
                            <th>Canal</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div>{{ date('d/m/Y H:i') }}</div>
                                <small class="text-muted">Paracetamol 500mg</small>
                            </td>
                            <td>
                                <span class="badge bg-danger">Crítica</span>
                            </td>
                            <td>
                                <div class="fw-bold">Dr. Carlos Mendoza</div>
                                <small class="text-muted">carlos.mendoza@hospital.com</small>
                            </td>
                            <td>
                                <i class="fas fa-envelope text-primary"></i> Email
                            </td>
                            <td>
                                <span class="badge bg-success">Enviado</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewAlertDetails(1)">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <div>{{ date('d/m/Y H:i', strtotime('-1 hour')) }}</div>
                                <small class="text-muted">Insulina NPH</small>
                            </td>
                            <td>
                                <span class="badge bg-warning">Advertencia</span>
                            </td>
                            <td>
                                <div class="fw-bold">Q.F. Ana Rodríguez</div>
                                <small class="text-muted">ana.rodriguez@hospital.com</small>
                            </td>
                            <td>
                                <i class="fas fa-sms text-info"></i> SMS
                            </td>
                            <td>
                                <span class="badge bg-success">Enviado</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewAlertDetails(2)">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <div>{{ date('d/m/Y H:i', strtotime('-2 hours')) }}</div>
                                <small class="text-muted">Amoxicilina 250mg</small>
                            </td>
                            <td>
                                <span class="badge bg-info">Informativa</span>
                            </td>
                            <td>
                                <div class="fw-bold">Luis Valencia</div>
                                <small class="text-muted">luis.valencia@hospital.com</small>
                            </td>
                            <td>
                                <i class="fas fa-envelope text-primary"></i> Email
                            </td>
                            <td>
                                <span class="badge bg-warning">Pendiente</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewAlertDetails(3)">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <div>{{ date('d/m/Y H:i', strtotime('-3 hours')) }}</div>
                                <small class="text-muted">Protector Solar FPS 60</small>
                            </td>
                            <td>
                                <span class="badge bg-danger">Crítica</span>
                            </td>
                            <td>
                                <div class="fw-bold">Q.F. Ana Rodríguez</div>
                                <small class="text-muted">ana.rodriguez@hospital.com</small>
                            </td>
                            <td>
                                <i class="fas fa-bell text-warning"></i> Push
                            </td>
                            <td>
                                <span class="badge bg-success">Enviado</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewAlertDetails(4)">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <div>{{ date('d/m/Y H:i', strtotime('-4 hours')) }}</div>
                                <small class="text-muted">Dexametasona Inyectable</small>
                            </td>
                            <td>
                                <span class="badge bg-danger">Crítica</span>
                            </td>
                            <td>
                                <div class="fw-bold">Dr. Carlos Mendoza</div>
                                <small class="text-muted">carlos.mendoza@hospital.com</small>
                            </td>
                            <td>
                                <i class="fas fa-envelope text-primary"></i> Email
                            </td>
                            <td>
                                <span class="badge bg-danger">Error</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger" onclick="retryAlert(5)">
                                    <i class="fas fa-redo"></i> Reintentar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
                    <i class="fas fa-plus"></i> Nueva Alerta Personalizada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="newAlertForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre de la Alerta *</label>
                            <input type="text" class="form-control" id="alertName" placeholder="Ej: Alerta Productos Controles" required>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" id="alertDescription" rows="2" placeholder="Descripción de la alerta..."></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Condición de Activación *</label>
                            <select class="form-select" id="alertCondition" required>
                                <option value="">Seleccionar condición</option>
                                <option value="days_before">Días antes del vencimiento</option>
                                <option value="category">Categoría de producto</option>
                                <option value="supplier">Proveedor específico</option>
                                <option value="stock_level">Nivel de stock</option>
                                <option value="value_threshold">Valor de inventario</option>
                            </select>
                        </div>
                        
                        <div class="col-12" id="conditionValue" style="display: none;">
                            <label class="form-label">Valor de Condición</label>
                            <input type="text" class="form-control" id="conditionInput" placeholder="Ej: 30, medicamentos, proveedor123">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Destinatarios *</label>
                            <div class="border rounded p-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sendToDirector" checked>
                                    <label class="form-check-label" for="sendToDirector">
                                        Director de Farmacia
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sendToQF" checked>
                                    <label class="form-check-label" for="sendToQF">
                                        Químico Farmacéutico
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sendToAuxiliary">
                                    <label class="form-check-label" for="sendToAuxiliary">
                                        Auxiliar de Farmacia
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sendToPurchasing">
                                    <label class="form-check-label" for="sendToPurchasing">
                                        Departamento de Compras
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Canales de Envío *</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="alertEmail" checked>
                                <label class="form-check-label" for="alertEmail">
                                    <i class="fas fa-envelope text-primary"></i> Email
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="alertSMS">
                                <label class="form-check-label" for="alertSMS">
                                    <i class="fas fa-sms text-info"></i> SMS
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="alertPush" checked>
                                <label class="form-check-label" for="alertPush">
                                    <i class="fas fa-bell text-warning"></i> Notificación Push
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="alertActive" checked>
                                <label class="form-check-label" for="alertActive">
                                    Activar alerta inmediatamente
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Alerta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de Agregar Destinatario --}}
<div class="modal fade" id="addRecipientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Agregar Destinatario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addRecipientForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" id="recipientName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Cargo/Posición *</label>
                        <input type="text" class="form-control" id="recipientPosition" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" id="recipientEmail" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="recipientPhone" placeholder="+51 999 999 999">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipos de Alertas</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="recipientCritical" checked>
                            <label class="form-check-label" for="recipientCritical">
                                Alertas Críticas
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="recipientWarning" checked>
                            <label class="form-check-label" for="recipientWarning">
                                Alertas de Advertencia
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="recipientInfo">
                            <label class="form-check-label" for="recipientInfo">
                                Alertas Informativas
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="recipientActive" checked>
                        <label class="form-check-label" for="recipientActive">
                            Destinatario activo
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de Detalles de Alerta --}}
<div class="modal fade" id="alertDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalles de Alerta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="alertDetailsContent">
                    {{-- Contenido dinámico --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-primary" onclick="resendAlert()">
                    <i class="fas fa-redo"></i> Reenviar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable para historial
    $('#alertHistoryTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json'
        },
        pageLength: 25,
        order: [[0, 'desc']], // Ordenar por fecha (descendente)
        columnDefs: [
            { orderable: false, targets: [6] } // Deshabilitar orden en columna de acciones
        ]
    });
    
    // Event listeners
    setupEventListeners();
});

function setupEventListeners() {
    // Mostrar/ocultar campo de valor según condición
    $('#alertCondition').change(function() {
        const condition = $(this).val();
        if (condition) {
            $('#conditionValue').show();
        } else {
            $('#conditionValue').hide();
        }
    });
}

// Funciones del Sistema
function toggleAlertSystem(start) {
    if (start) {
        // Iniciar sistema
        Swal.fire({
            title: 'Iniciar Sistema de Alertas',
            text: '¿Está seguro de activar el sistema automático de alertas?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, iniciar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#startSystemBtn').hide();
                $('#stopSystemBtn').show();
                showNotification('Sistema de alertas activado exitosamente', 'success');
            }
        });
    } else {
        // Detener sistema
        Swal.fire({
            title: 'Detener Sistema de Alertas',
            text: '¿Está seguro de desactivar el sistema automático de alertas?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, detener',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#startSystemBtn').show();
                $('#stopSystemBtn').hide();
                showNotification('Sistema de alertas desactivado', 'info');
            }
        });
    }
}

function runAlertCheck() {
    Swal.fire({
        title: 'Ejecutando Verificación...',
        text: 'Verificando productos próximos a vencer',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Simular verificación
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Verificación Completada',
            text: 'Se encontraron 3 productos próximos a vencer.',
            showConfirmButton: false,
            timer: 3000
        });
    }, 2000);
}

function testAlertSystem() {
    Swal.fire({
        title: 'Probar Sistema de Alertas',
        text: 'Seleccione el tipo de alerta de prueba',
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
        text: 'Se enviará un SMS de prueba a los destinatarios configurados',
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

// Funciones de Configuración
$('#thresholdsForm').on('submit', function(e) {
    e.preventDefault();
    
    const critical = $('#criticalThreshold').val();
    const warning = $('#warningThreshold').val();
    const info = $('#infoThreshold').val();
    
    if (critical >= warning || warning >= info) {
        Swal.fire({
            icon: 'error',
            title: 'Error en Umbrales',
            text: 'Los umbrales deben estar en orden descendente: Crítico < Advertencia < Informativa'
        });
        return;
    }
    
    // Simular guardado
    Swal.fire({
        title: 'Guardando...',
        text: 'Actualizando umbrales de alerta',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Umbrales Actualizados',
            text: 'Los umbrales de alerta han sido guardados exitosamente.',
            showConfirmButton: false,
            timer: 2000
        });
    }, 1500);
});

$('#notificationsForm').on('submit', function(e) {
    e.preventDefault();
    
    // Simular guardado
    Swal.fire({
        title: 'Guardando...',
        text: 'Actualizando configuración de notificaciones',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Configuración Actualizada',
            text: 'La configuración de notificaciones ha sido guardada exitosamente.',
            showConfirmButton: false,
            timer: 2000
        });
    }, 1500);
});

// Modales
function showNewAlertModal() {
    $('#newAlertModal').modal('show');
}

function showAddRecipientModal() {
    $('#addRecipientModal').modal('show');
}

function viewAlertDetails(alertId) {
    // Simular carga de detalles
    const content = `
        <div class="row g-3">
            <div class="col-12">
                <h6>Detalles de la Alerta</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Producto:</strong></td>
                        <td>Paracetamol 500mg - Jarabe 60ml</td>
                    </tr>
                    <tr>
                        <td><strong>Tipo:</strong></td>
                        <td><span class="badge bg-danger">Crítica</span></td>
                    </tr>
                    <tr>
                        <td><strong>Destinatario:</strong></td>
                        <td>Dr. Carlos Mendoza (carlos.mendoza@hospital.com)</td>
                    </tr>
                    <tr>
                        <td><strong>Canal:</strong></td>
                        <td><i class="fas fa-envelope text-primary"></i> Email</td>
                    </tr>
                    <tr>
                        <td><strong>Enviado:</strong></td>
                        <td>${new Date().toLocaleString('es-ES')}</td>
                    </tr>
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td><span class="badge bg-success">Enviado</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-12">
                <h6>Mensaje Enviado:</h6>
                <div class="border rounded p-3 bg-light">
                    <p><strong>Asunto:</strong> ALERTA CRÍTICA - Producto próximo a vencer</p>
                    <p>Estimado Dr. Carlos Mendoza,</p>
                    <p>Se le notifica que el producto <strong>Paracetamol 500mg - Jarabe 60ml</strong> (Lote: L2023-001) vencerá el <strong>15/03/2025</strong>.</p>
                    <p>Días restantes: <strong>7 días</strong></p>
                    <p>Acción recomendada: Revisar opciones de disposición.</p>
                </div>
            </div>
        </div>
    `;
    
    $('#alertDetailsContent').html(content);
    $('#alertDetailsModal').modal('show');
}

function retryAlert(alertId) {
    Swal.fire({
        title: 'Reenviar Alerta',
        text: '¿Desea reenviar esta alerta?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reenviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Reenviando...',
                text: 'Intentando reenviar la alerta',
                icon: 'info',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            setTimeout(() => {
                showNotification('Alerta reenviada exitosamente', 'success');
            }, 2000);
        }
    });
}

// Gestión de Destinatarios
function editRecipient(recipientId) {
    // Simular edición
    Swal.fire({
        icon: 'info',
        title: 'Editar Destinatario',
        text: 'Funcionalidad de edición en desarrollo'
    });
}

function deleteRecipient(recipientId) {
    Swal.fire({
        title: 'Eliminar Destinatario',
        text: '¿Está seguro de eliminar este destinatario?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Destinatario eliminado exitosamente', 'success');
        }
    });
}

// Formulario de nueva alerta
$('#newAlertForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: $('#alertName').val(),
        description: $('#alertDescription').val(),
        condition: $('#alertCondition').val(),
        conditionValue: $('#conditionInput').val(),
        channels: {
            email: $('#alertEmail').is(':checked'),
            sms: $('#alertSMS').is(':checked'),
            push: $('#alertPush').is(':checked')
        },
        recipients: {
            director: $('#sendToDirector').is(':checked'),
            qf: $('#sendToQF').is(':checked'),
            auxiliary: $('#sendToAuxiliary').is(':checked'),
            purchasing: $('#sendToPurchasing').is(':checked')
        },
        active: $('#alertActive').is(':checked')
    };
    
    // Validar que al menos un canal está seleccionado
    if (!formData.channels.email && !formData.channels.sms && !formData.channels.push) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe seleccionar al menos un canal de envío.'
        });
        return;
    }
    
    // Validar que al menos un destinatario está seleccionado
    if (!formData.recipients.director && !formData.recipients.qf && 
        !formData.recipients.auxiliary && !formData.recipients.purchasing) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe seleccionar al menos un destinatario.'
        });
        return;
    }
    
    // Simular guardado
    Swal.fire({
        title: 'Guardando...',
        text: 'Creando nueva alerta personalizada',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Alerta Creada',
            text: 'La nueva alerta personalizada ha sido creada exitosamente.',
            showConfirmButton: false,
            timer: 2000
        });
        
        $('#newAlertModal').modal('hide');
        $('#newAlertForm')[0].reset();
    }, 2000);
});

// Formulario de agregar destinatario
$('#addRecipientForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: $('#recipientName').val(),
        position: $('#recipientPosition').val(),
        email: $('#recipientEmail').val(),
        phone: $('#recipientPhone').val(),
        alerts: {
            critical: $('#recipientCritical').is(':checked'),
            warning: $('#recipientWarning').is(':checked'),
            info: $('#recipientInfo').is(':checked')
        },
        active: $('#recipientActive').is(':checked')
    };
    
    // Simular guardado
    Swal.fire({
        title: 'Guardando...',
        text: 'Agregando nuevo destinatario',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Destinatario Agregado',
            text: 'El destinatario ha sido agregado exitosamente.',
            showConfirmButton: false,
            timer: 2000
        });
        
        $('#addRecipientModal').modal('hide');
        $('#addRecipientForm')[0].reset();
    }, 1500);
});

// Funciones de exportación
function exportAlertHistory() {
    Swal.fire({
        title: 'Exportar Historial',
        text: '¿Desea exportar el historial de alertas?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Exportando historial de alertas...', 'info');
            setTimeout(() => {
                showNotification('Historial de alertas exportado exitosamente', 'success');
            }, 2000);
        }
    });
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

function resendAlert() {
    showNotification('Reenviando alerta...', 'info');
    setTimeout(() => {
        showNotification('Alerta reenviada exitosamente', 'success');
    }, 2000);
}
</script>
@endsection

@section('styles')
<style>
/* Estilos para el sistema de alertas */
.alert-system-active {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.alert-system-inactive {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
}

.status-active {
    background-color: #28a745;
    animation: pulse 2s infinite;
}

.status-inactive {
    background-color: #dc3545;
}

.status-pending {
    background-color: #ffc107;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
    100% {
        opacity: 1;
    }
}

/* Estilos para tipos de alertas */
.alert-critical {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.alert-warning {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.alert-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

/* Estilos para canales de notificación */
.channel-email {
    color: #007bff;
}

.channel-sms {
    color: #17a2b8;
}

.channel-push {
    color: #ffc107;
}

.channel-dashboard {
    color: #28a745;
}

/* Animaciones para estados */
.notification-sent {
    animation: slideInRight 0.5s ease-out;
}

.notification-pending {
    animation: slideInLeft 0.5s ease-out;
}

.notification-error {
    animation: shake 0.5s ease-out;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideInLeft {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes shake {
    0%, 100% {
        transform: translateX(0);
    }
    10%, 30%, 50%, 70%, 90% {
        transform: translateX(-5px);
    }
    20%, 40%, 60%, 80% {
        transform: translateX(5px);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-columns {
        column-count: 1;
    }
    
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>
@endsection