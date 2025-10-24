@extends('layouts.farmacia')

@section('title', 'Dashboard Farmacéutico - SIFANO')

@push('styles')
<style>
    .pharmacy-dashboard-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        transition: transform 0.2s ease;
        height: 100%;
    }

    .pharmacy-dashboard-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.15);
    }

    .metric-card {
        text-align: center;
        padding: 1.5rem;
    }

    .metric-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: white;
        margin: 0 auto 1rem auto;
    }

    .icon-inventory { background: linear-gradient(135deg, #0891b2, #0e7490); }
    .icon-controlled { background: linear-gradient(135deg, #dc2626, #b91c1c); }
    .icon-expiration { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .icon-temperature { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .icon-alerts { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .icon-sales { background: linear-gradient(135deg, #10b981, #059669); }

    .critical-alert {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
        animation: pulse 2s infinite;
    }

    .warning-alert {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .success-alert {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }

    .temperature-monitor {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }

    .sensor-reading {
        text-align: center;
        padding: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        margin-bottom: 1rem;
    }

    .sensor-reading.normal {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .sensor-reading.warning {
        background: #fffbeb;
        border-color: #fed7aa;
    }

    .sensor-reading.critical {
        background: #fef2f2;
        border-color: #fecaca;
    }

    .temperature-value {
        font-size: 2rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }

    .temperature-value.normal { color: #059669; }
    .temperature-value.warning { color: #d97706; }
    .temperature-value.critical { color: #dc2626; }

    .controlled-medication-card {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        border: 1px solid #fca5a5;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .expiration-timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
        border-left: 2px solid #e5e7eb;
        padding-left: 1.5rem;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #d1d5db;
    }

    .timeline-item.critical::before { background: #dc2626; }
    .timeline-item.warning::before { background: #f59e0b; }
    .timeline-item.normal::before { background: #10b981; }

    .stock-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 0.5rem;
    }

    .stock-high { background: #10b981; }
    .stock-medium { background: #f59e0b; }
    .stock-low { background: #ef4444; }
    .stock-out { background: #6b7280; }

    .quick-action-btn {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-radius: 0.75rem;
        text-decoration: none;
        color: #374151;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
        margin-bottom: 0.75rem;
        background: white;
    }

    .quick-action-btn:hover {
        background: #f9fafb;
        border-color: #7c3aed;
        color: #7c3aed;
        text-decoration: none;
        transform: translateX(5px);
    }

    .quick-action-btn i {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.25rem;
        color: white;
    }

    .chart-container {
        position: relative;
        height: 300px;
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }
</style>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
@endsection

@section('farmacia-content')
<!-- Alertas Críticas -->
@if(($alertasCriticas ?? [])->$count() > 0)
<div class="critical-alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle me-3"></i>
        <div>
            <strong>Alertas Críticas Activas:</strong>
            <span id="criticalCount">{{ ($alertasCriticas ?? [])->$count() }}</span> alertas requieren atención inmediata
            <button class="btn btn-sm btn-light ms-3" onclick="showCriticalAlerts()">
                Ver Detalles
            </button>
        </div>
    </div>
</div>
@endif

@if(($alertasAdvertencia ?? [])->$count() > 0)
<div class="warning-alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-circle me-3"></i>
        <div>
            <strong>Advertencias:</strong>
            <span id="warningCount">{{ ($alertasAdvertencia ?? [])->$count() }}</span> elementos requieren atención
            <button class="btn btn-sm btn-light ms-3" onclick="showWarningAlerts()">
                Ver Lista
            </button>
        </div>
    </div>
</div>
@endif

<!-- Métricas Principales -->
<div class="row g-4 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="pharmacy-dashboard-card metric-card">
            <div class="metric-icon icon-inventory">
                <i class="fas fa-boxes"></i>
            </div>
            <h3 class="mb-1">{{ $totalProductos ?? 0 }}</h3>
            <p class="text-muted mb-0">Total Productos</p>
            <small class="text-success">
                <i class="fas fa-check me-1"></i>
                {{ $productosDisponibles ?? 0 }} disponibles
            </small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="pharmacy-dashboard-card metric-card">
            <div class="metric-icon icon-controlled">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3 class="mb-1">{{ $medicamentosControlados ?? 0 }}</h3>
            <p class="text-muted mb-0">Controlados</p>
            <small class="text-danger">
                <i class="fas fa-clock me-1"></i>
                {{ $ventasControladasHoy ?? 0 }} vendidas hoy
            </small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="pharmacy-dashboard-card metric-card">
            <div class="metric-icon icon-expiration">
                <i class="fas fa-clock"></i>
            </div>
            <h3 class="mb-1">{{ $productosVencer30Dias ?? 0 }}</h3>
            <p class="text-muted mb-0">Próximos a Vencer</p>
            <small class="{{ ($productosVencer7Dias ?? 0) > 0 ? 'text-danger' : 'text-warning' }}">
                <i class="fas fa-calendar me-1"></i>
                {{ $productosVencer7Dias ?? 0 }} en 7 días
            </small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="pharmacy-dashboard-card metric-card">
            <div class="metric-icon icon-temperature">
                <i class="fas fa-thermometer-half"></i>
            </div>
            <h3 class="mb-1">{{ number_format($temperaturaPromedio ?? 0, 1) }}°C</h3>
            <p class="text-muted mb-0">Temp. Promedio</p>
            <small class="{{ (($temperaturaPromedio ?? 0) >= 15 && ($temperaturaPromedio ?? 0) <= 25) ? 'text-success' : 'text-danger' }}">
                <i class="fas fa-thermometer me-1"></i>
                {{ (($temperaturaPromedio ?? 0) >= 15 && ($temperaturaPromedio ?? 0) <= 25) ? 'Normal' : 'Alerta' }}
            </small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="pharmacy-dashboard-card metric-card">
            <div class="metric-icon icon-alerts">
                <i class="fas fa-bell"></i>
            </div>
            <h3 class="mb-1">{{ ($totalAlertas ?? 0) }}</h3>
            <p class="text-muted mb-0">Alertas Activas</p>
            <small class="text-danger">
                <i class="fas fa-exclamation me-1"></i>
                {{ $alertasCriticasCount ?? 0 }} críticas
            </small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="pharmacy-dashboard-card metric-card">
            <div class="metric-icon icon-sales">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h3 class="mb-1">{{ $ventasFarmaciaHoy ?? 0 }}</h3>
            <p class="text-muted mb-0">Ventas de Hoy</p>
            <small class="text-success">
                <i class="fas fa-dollar-sign me-1"></i>
                S/ {{ number_format($montoVentasHoy ?? 0, 2) }}
            </small>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Control de Temperaturas -->
    <div class="col-lg-6">
        <div class="temperature-monitor">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="fas fa-thermometer-half me-2"></i>
                    Monitoreo de Temperaturas
                </h5>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshTemperatureData()">
                    <i class="fas fa-sync me-1"></i>
                    Actualizar
                </button>
            </div>

            <div class="row" id="temperatureSensors">
                @foreach(($sensoresTemperatura ?? []) as $sensor)
                <div class="col-md-6 mb-3">
                    <div class="sensor-reading {{ $sensor->$estado_css }}">
                        <h6>{{ $sensor->$nombre }}</h6>
                        <div class="temperature-value {{ $sensor->$estado_css }}">
                            {{ number_format($sensor->$temperatura_actual, 1) }}°C
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Rango: {{ $sensor->$rango_min }}° - {{ $sensor->$rango_max }}°C</small>
                            <span class="badge bg-{{ $sensor->$estado_color }}">
                                {{ $sensor->$estado }}
                            </span>
                        </div>
                        <small class="text-muted d-block mt-1">
                            Última: {{ date('d/m H:i', strtotime($sensor->$ultima_lectura)) }}
                        </small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="col-lg-6">
        <div class="pharmacy-dashboard-card">
            <h5 class="mb-3">
                <i class="fas fa-bolt me-2"></i>
                Acciones Rápidas
            </h5>

            <button onclick="openControlledMedicationModal()" class="quick-action-btn w-100 text-start border-0 bg-transparent">
                <i class="fas fa-shield-alt" style="background: linear-gradient(135deg, #dc2626, #b91c1c);"></i>
                <div>
                    <strong>Registrar Medicamento Controlado</strong>
                    <small class="d-block text-muted">Venta con receta médica</small>
                </div>
            </button>

            <a href="{{ route('productos.vencimientos') }}" class="quick-action-btn">
                <i class="fas fa-clock" style="background: linear-gradient(135deg, #f59e0b, #d97706);"></i>
                <div>
                    <strong>Control de Vencimientos</strong>
                    <small class="d-block text-muted">Ver productos próximos a vencer</small>
                </div>
            </a>

            <a href="{{ route('productos.inventario') }}" class="quick-action-btn">
                <i class="fas fa-boxes" style="background: linear-gradient(135deg, #0891b2, #0e7490);"></i>
                <div>
                    <strong>Gestionar Inventario</strong>
                    <small class="d-block text-muted">Stock, entradas, salidas</small>
                </div>
            </a>

            <button onclick="checkExpirations()" class="quick-action-btn w-100 text-start border-0 bg-transparent">
                <i class="fas fa-search" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);"></i>
                <div>
                    <strong>Verificar Vencimientos</strong>
                    <small class="d-block text-muted">Escanear productos próximos a vencer</small>
                </div>
            </button>

            <a href="{{ route('reportes.inventario') }}" class="quick-action-btn">
                <i class="fas fa-chart-bar" style="background: linear-gradient(135deg, #10b981, #059669);"></i>
                <div>
                    <strong>Reportes de Inventario</strong>
                    <small class="d-block text-muted">Análisis y estadísticas</small>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Segunda fila -->
<div class="row g-4 mt-2">
    <!-- Productos por Vencer -->
    <div class="col-lg-6">
        <div class="pharmacy-dashboard-card">
            <h5 class="mb-3">
                <i class="fas fa-clock me-2"></i>
                Productos Próximos a Vencer
            </h5>
            
            @forelse(($productosVencerRecientes ?? []) as $producto)
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <strong>{{ $producto->$nombre }}</strong>
                    <br>
                    <small class="text-muted">{{ $producto->$codigo }}</small>
                </div>
                <div class="text-end">
                    <div class="badge bg-{{ $producto->$dias_para_vencer <= 7 ? 'danger' : ($producto->$dias_para_vencer <= 30 ? 'warning' : 'info') }}">
                        {{ $producto->$dias_para_vencer }} días
                    </div>
                    <br>
                    <small class="text-muted">{{ date('d/m/Y', strtotime($producto->$fecha_vencimiento)) }}</small>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-3">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <p>No hay productos próximos a vencer</p>
            </div>
            @endforelse

            <div class="text-center mt-3">
                <a href="{{ route('productos.vencimientos') }}" class="btn btn-outline-primary btn-sm">
                    Ver Todos los Vencimientos
                </a>
            </div>
        </div>
    </div>

    <!-- Stock Bajo -->
    <div class="col-lg-6">
        <div class="pharmacy-dashboard-card">
            <h5 class="mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Productos con Stock Bajo
            </h5>
            
            @forelse(($productosStockBajo ?? []) as $producto)
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <strong>{{ $producto->$nombre }}</strong>
                    <br>
                    <small class="text-muted">{{ $producto->$codigo }}</small>
                </div>
                <div class="text-end">
                    <div class="d-flex align-items-center">
                        <span class="stock-indicator {{ $producto->$stock_actual <= 0 ? 'stock-out' : ($producto->$stock_actual <= $producto->$stock_minimo ? 'stock-low' : 'stock-medium') }}"></span>
                        <span class="fw-bold">{{ $producto->$stock_actual }}</span>
                    </div>
                    <small class="text-muted">Mín: {{ $producto->$stock_minimo }}</small>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-3">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <p>Stock en niveles normales</p>
            </div>
            @endforelse

            <div class="text-center mt-3">
                <a href="{{ route('productos.stock-bajo') }}" class="btn btn-outline-warning btn-sm">
                    Ver Lista Completa
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tercera fila - Medicamentos Controlados Recientes -->
<div class="row mt-4">
    <div class="col-12">
        <div class="pharmacy-dashboard-card">
            <h5 class="mb-3">
                <i class="fas fa-shield-alt me-2"></i>
                Ventas de Medicamentos Controlados - Últimas 24 Horas
            </h5>
            
            @forelse(($ventasControladasRecientes ?? []) as $venta)
            <div class="controlled-medication-card">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <strong>{{ $venta->$producto_nombre }}</strong>
                        <br>
                        <small class="text-muted">{{ $venta->$producto_codigo }}</small>
                    </div>
                    <div class="col-md-2">
                        <span class="badge bg-danger">Cant: {{ $venta->$cantidad }}</span>
                    </div>
                    <div class="col-md-3">
                        <small><strong>Cliente:</strong> {{ $venta->$cliente_nombre }}</small>
                        <br>
                        <small><strong>DNI:</strong> {{ $venta->$cliente_dni }}</small>
                    </div>
                    <div class="col-md-2">
                        <small><strong>Receta:</strong> {{ $venta->$numero_receta }}</small>
                    </div>
                    <div class="col-md-2 text-end">
                        <small class="text-muted">{{ date('d/m/Y H:i', strtotime($venta->$fecha_venta)) }}</small>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="fas fa-shield-alt fa-2x mb-2"></i>
                <p>No hay ventas de medicamentos controlados en las últimas 24 horas</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Gráfico de ventas de medicamentos controlados
    const controlledSalesCtx = document.getElementById('controlledSalesChart');
    if (controlledSalesCtx) {
        new Chart(controlledSalesCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($labelsVentasControladas ?? []) !!},
                datasets: [{
                    label: 'Ventas Controladas',
                    data: {!! json_encode($ventasControladasData ?? []) !!},
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Funciones específicas del dashboard farmacéutico
    function showCriticalAlerts() {
        const alerts = {!! json_encode($alertasCriticas ?? []) !!};
        
        let html = '<div class="text-start">';
        alerts.forEach(alert => {
            html += `
                <div class="mb-3 p-3 border rounded">
                    <strong>${alert.titulo}</strong>
                    <p class="mb-1">${alert.mensaje}</p>
                    <small class="text-muted">${alert.fecha}</small>
                </div>
            `;
        });
        html += '</div>';

        Swal.fire({
            title: 'Alertas Críticas',
            html: html,
            icon: 'error',
            width: '600px'
        });
    }

    function showWarningAlerts() {
        const alerts = {!! json_encode($alertasAdvertencia ?? []) !!};
        
        let html = '<div class="text-start">';
        alerts.forEach(alert => {
            html += `
                <div class="mb-3 p-3 border rounded">
                    <strong>${alert.titulo}</strong>
                    <p class="mb-1">${alert.mensaje}</p>
                    <small class="text-muted">${alert.fecha}</small>
                </div>
            `;
        });
        html += '</div>';

        Swal.fire({
            title: 'Advertencias',
            html: html,
            icon: 'warning',
            width: '600px'
        });
    }

    function refreshTemperatureData() {
        showLoading();
        
        fetch('/api/farmacia/temperaturas/refresh')
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    location.reload();
                } else {
                    Swal.fire('Error', 'Error actualizando temperaturas', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                Swal.fire('Error', 'Error de conexión', 'error');
            });
    }

    // Auto-refresh cada 2 minutos para datos críticos
    setInterval(function() {
        // Solo refresh temperatura y alertas críticas
        fetch('/api/farmacia/alertas/temperatura')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateTemperatureReadings(data.sensores);
                }
            })
            .catch(error => {
                console.error('Error refreshing temperature:', error);
            });
    }, 120000);

    function updateTemperatureReadings(sensores) {
        sensores.forEach(sensor => {
            const element = document.querySelector(`[data-sensor="${sensor.id}"] .temperature-value`);
            if (element) {
                element.textContent = sensor.temperatura.toFixed(1) + '°C';
                element.className = `temperature-value ${sensor.estado_css}`;
            }
        });
    }

    // Inicializar dashboard
    document.addEventListener('DOMContentLoaded', function() {
        // Marcar elementos que necesitan actualización automática
        document.querySelectorAll('.sensor-reading').forEach(element => {
            element.setAttribute('data-sensor', element.dataset.sensorId);
        });
    });
</script>
@endsection