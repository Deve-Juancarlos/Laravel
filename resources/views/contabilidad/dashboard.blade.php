@extends('layouts.contador')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid py-4">
    {{-- Header del Dashboard --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-1">Panel de Control</h2>
                    <p class="text-muted mb-0">Resumen general de tu sistema contable</p>
                </div>
                <div class="text-end">
                    <div class="h5 mb-1" id="currentTime"></div>
                    <small class="text-muted" id="currentDate"></small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tarjetas de Estadísticas Principales --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Clientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <span class="counter" data-target="1247">0</span>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-success">
                                    <i class="fas fa-arrow-up"></i> +12%
                                </span>
                                <small class="text-muted ms-1">este mes</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-users text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Facturas del Mes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <span class="counter" data-target="89">0</span>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-info">
                                    <i class="fas fa-clock"></i> 23 pendientes
                                </span>
                                <small class="text-muted ms-1">de cobro</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-success">
                                <i class="fas fa-file-invoice-dollar text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Ingresos del Mes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                S/ <span class="counter" data-target="45832">0</span>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-warning">
                                    <i class="fas fa-exclamation-triangle"></i> S/ 8,450
                                </span>
                                <small class="text-muted ms-1">por cobrar</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-info">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Planillas Activas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <span class="counter" data-target="12">0</span>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation"></i> 3 vencen hoy
                                </span>
                                <small class="text-muted ms-1">revisar</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-warning">
                                <i class="fas fa-hand-holding-usd text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido Principal --}}
    <div class="row">
        {{-- Panel Principal Izquierdo --}}
        <div class="col-lg-8">
            {{-- Acciones Rápidas --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('contabilidad.clientes.create') }}" 
                               class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-user-plus fa-2x d-block mb-2"></i>
                                <strong>Nuevo Cliente</strong>
                                <br><small class="text-muted">Registrar cliente</small>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('contabilidad.facturas.demo') }}" 
                               class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-file-invoice fa-2x d-block mb-2"></i>
                                <strong>Nueva Factura</strong>
                                <br><small class="text-muted">Crear factura</small>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('contabilidad.planillas.create') }}" 
                               class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-file-alt fa-2x d-block mb-2"></i>
                                <strong>Nueva Planilla</strong>
                                <br><small class="text-muted">Crear planilla</small>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('contabilidad.bancos.create') }}" 
                               class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-university fa-2x d-block mb-2"></i>
                                <strong>Nueva Cuenta</strong>
                                <br><small class="text-muted">Cuenta bancaria</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráfico de Ingresos (placeholder) --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Tendencia de Ingresos
                    </h5>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Últimos 6 meses
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Último mes</a></li>
                            <li><a class="dropdown-item" href="#">Últimos 3 meses</a></li>
                            <li><a class="dropdown-item" href="#">Últimos 6 meses</a></li>
                            <li><a class="dropdown-item" href="#">Este año</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-placeholder" style="height: 300px; background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%), linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f8f9fa 75%), linear-gradient(-45deg, transparent 75%, #f8f9fa 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px; display: flex; align-items: center; justify-content: center; color: #6c757d; font-weight: 500;">
                        <div class="text-center">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <div>Gráfico de Ingresos</div>
                            <small class="text-muted">Integrar con Chart.js o similar</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actividad Reciente --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history text-info me-2"></i>
                        Actividad Reciente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Factura #F-2024-001234 creada</h6>
                                <p class="timeline-text">Cliente: Empresa ABC S.A.C. - S/ 2,450.00</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>Hace 15 minutos
                                </small>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Nuevo cliente registrado</h6>
                                <p class="timeline-text">DNI: 12345678 - Juan Pérez García</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>Hace 1 hora
                                </small>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Planilla de servicios vencida</h6>
                                <p class="timeline-text">Servicio: Consultoría Contable - Cliente: XYZ Ltda.</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>Hace 2 horas
                                </small>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Pago recibido</h6>
                                <p class="timeline-text">Banco: BCP - Factura #F-2024-001230 - S/ 1,875.00</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>Hace 3 horas
                                </small>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker bg-secondary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Respaldo del sistema completado</h6>
                                <p class="timeline-text">Base de datos respaldada exitosamente</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>Hace 6 horas
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel Lateral Derecho --}}
        <div class="col-lg-4">
            {{-- Estado del Sistema --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-server text-primary me-2"></i>
                        Estado del Sistema
                    </h6>
                </div>
                <div class="card-body">
                    <div class="system-status">
                        <div class="status-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small">Base de Datos</span>
                                <span class="badge bg-success">Online</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div class="status-item mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small">API RENIEC</span>
                                <span class="badge bg-success">Conectado</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: 95%"></div>
                            </div>
                        </div>
                        
                        <div class="status-item mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small">Sistema de Respaldo</span>
                                <span class="badge bg-success">Activo</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div class="status-item mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small">Almacenamiento</span>
                                <span class="badge bg-warning">67%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: 67%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alertas y Notificaciones --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Alertas Importantes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger border-0 mb-3">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                            <div>
                                <strong>3 planillas vencen hoy</strong>
                                <br><small>Revisar y procesar cobranzas pendientes</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning border-0 mb-3">
                        <div class="d-flex">
                            <i class="fas fa-clock me-2 mt-1"></i>
                            <div>
                                <strong>23 facturas por vencer</strong>
                                <br><small>Vencen en los próximos 7 días</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info border-0">
                        <div class="d-flex">
                            <i class="fas fa-info-circle me-2 mt-1"></i>
                            <div>
                                <strong>Actualización disponible</strong>
                                <br><small>Versión 2.1.3 lista para instalar</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Estadísticas Rápidas --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-pie text-success me-2"></i>
                        Resumen del Mes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h4 mb-1 text-success">89</div>
                                <div class="small text-muted">Facturas</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-1 text-primary">156</div>
                            <div class="small text-muted">Clientes</div>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h4 mb-1 text-info">S/ 45.8K</div>
                                <div class="small text-muted">Ingresos</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-1 text-warning">S/ 8.4K</div>
                            <div class="small text-muted">Pendientes</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Próximas Tareas --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-tasks text-info me-2"></i>
                        Próximas Tareas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="task-item">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="task1">
                            <label class="form-check-label" for="task1">
                                <small>Revisar facturas vencidas</small>
                            </label>
                        </div>
                        <small class="text-muted d-block ms-4">Vence hoy</small>
                    </div>
                    
                    <div class="task-item mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="task2">
                            <label class="form-check-label" for="task2">
                                <small>Generar reporte mensual</small>
                            </label>
                        </div>
                        <small class="text-muted d-block ms-4">Vence mañana</small>
                    </div>
                    
                    <div class="task-item mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="task3">
                            <label class="form-check-label" for="task3">
                                <small>Actualizar datos de clientes</small>
                            </label>
                        </div>
                        <small class="text-muted d-block ms-4">Esta semana</small>
                    </div>
                    
                    <div class="task-item mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="task4">
                            <label class="form-check-label" for="task4">
                                <small>Backup de la base de datos</small>
                            </label>
                        </div>
                        <small class="text-muted d-block ms-4">Domingo</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos del Dashboard */
.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #e9ecef, #dee2e6);
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.5rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border-left: 3px solid #dee2e6;
}

.timeline-title {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    color: #495057;
}

.timeline-text {
    margin-bottom: 0.25rem;
    font-size: 0.8rem;
    color: #6c757d;
}

.status-item {
    margin-bottom: 1rem;
}

.task-item {
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #dee2e6;
}

/* Animaciones */
.counter {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}

/* Responsive */
@media (max-width: 768px) {
    .timeline {
        padding-left: 1.5rem;
    }
    
    .timeline-item {
        margin-bottom: 1.5rem;
    }
    
    .col-xl-3 {
        margin-bottom: 1rem;
    }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reloj en tiempo real
    function updateClock() {
        const now = new Date();
        const timeElement = document.getElementById('currentTime');
        const dateElement = document.getElementById('currentDate');
        
        if (timeElement && dateElement) {
            const timeString = now.toLocaleTimeString('es-PE', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            
            const dateString = now.toLocaleDateString('es-PE', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            timeElement.textContent = timeString;
            dateElement.textContent = dateString.charAt(0).toUpperCase() + dateString.slice(1);
        }
    }
    
    updateClock();
    setInterval(updateClock, 1000);

    // Animación de contadores
    function animateCounter(element, target, duration = 2000) {
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current).toLocaleString();
        }, 16);
    }
    
    // Iniciar animación de contadores cuando sean visibles
    const counters = document.querySelectorAll('.counter');
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));
                animateCounter(counter, target);
                counterObserver.unobserve(counter);
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => {
        counterObserver.observe(counter);
    });

    // Marcar tareas como completadas
    const taskCheckboxes = document.querySelectorAll('.task-item input[type="checkbox"]');
    taskCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const taskItem = this.closest('.task-item');
            if (this.checked) {
                taskItem.style.opacity = '0.6';
                taskItem.style.textDecoration = 'line-through';
                taskItem.style.background = '#d4edda';
            } else {
                taskItem.style.opacity = '1';
                taskItem.style.textDecoration = 'none';
                taskItem.style.background = '#f8f9fa';
            }
        });
    });

    // Simular carga de datos dinámicos
    function refreshDashboard() {
        // Aquí puedes implementar la lógica para actualizar datos
        console.log('Actualizando dashboard...');
        
        // Simular actualización de contadores
        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            const current = parseInt(counter.textContent.replace(/,/g, ''));
            const target = parseInt(counter.getAttribute('data-target'));
            
            // Pequeña variación aleatoria
            const variation = Math.floor(Math.random() * 3) - 1;
            const newTarget = Math.max(0, target + variation);
            
            counter.setAttribute('data-target', newTarget);
        });
    }
    
    // Actualizar cada 30 segundos
    setInterval(refreshDashboard, 30000);

    // Efecto hover en tarjetas
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Tooltips para elementos importantes
    const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipElements.forEach(element => {
        new bootstrap.Tooltip(element);
    });

    // Alertas de sistema (ejemplo)
    setTimeout(() => {
        if (Math.random() > 0.7) { // 30% de probabilidad
            showToast('Recordatorio: Tiene facturas pendientes de revisión', 'warning');
        }
    }, 5000);
});

// Función global para mostrar toast
window.showToast = function(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
};

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Simular actualizaciones en tiempo real
setInterval(() => {
    const activityItems = document.querySelectorAll('.timeline-item');
    if (activityItems.length > 0 && Math.random() > 0.8) {
        // Simular nueva actividad cada cierto tiempo
        const lastItem = activityItems[0];
        const timeElement = lastItem.querySelector('.text-muted i + *');
        if (timeElement) {
            timeElement.textContent = 'Hace 1 minuto';
        }
    }
}, 10000);
</script>
@endpush
@endsection