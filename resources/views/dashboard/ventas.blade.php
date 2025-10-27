@extends('layouts.app')

@section('title', 'Dashboard de Ventas - SIFANO')

@push('styles')
<style>
    .sales-dashboard-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        transition: transform 0.2s ease;
        height: 100%;
    }

    .sales-dashboard-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.15);
    }

    .sales-metric-card {
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

    .icon-revenue { background: linear-gradient(135deg, #10b981, #059669); }
    .icon-orders { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .icon-customers { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .icon-products { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .icon-growth { background: linear-gradient(135deg, #06b6d4, #0891b2); }
    .icon-target { background: linear-gradient(135deg, #ef4444, #dc2626); }

    .sales-alert {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
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

    .warning-alert {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .chart-container {
        position: relative;
        height: 350px;
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .sales-performance {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }

    .performance-bar {
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        margin: 0.5rem 0;
    }

    .performance-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        transition: width 0.3s ease;
    }

    .performance-fill.warning {
        background: linear-gradient(90deg, #f59e0b, #d97706);
    }

    .performance-fill.danger {
        background: linear-gradient(90deg, #ef4444, #dc2626);
    }

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
        border-color: #3b82f6;
        color: #3b82f6;
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

    .sales-table {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    .table-header {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        padding: 1rem 1.5rem;
        margin: 0;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-completed {
        background: #dcfce7;
        color: #166534;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .trend-indicator {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .trend-up {
        background: #dcfce7;
        color: #166534;
    }

    .trend-down {
        background: #fee2e2;
        color: #991b1b;
    }

    .trend-neutral {
        background: #f3f4f6;
        color: #374151;
    }

    .sales-goal {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
        text-align: center;
    }

    .goal-progress {
        width: 120px;
        height: 120px;
        margin: 0 auto;
        position: relative;
    }

    .goal-circle {
        transform: rotate(-90deg);
    }

    .goal-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .top-products-list {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .product-item {
        display: flex;
        justify-content: between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .product-item:last-child {
        border-bottom: none;
    }

    .product-rank {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #3b82f6;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 1rem;
    }

    .product-rank.rank-1 { background: #f59e0b; }
    .product-rank.rank-2 { background: #6b7280; }
    .product-rank.rank-3 { background: #cd7f32; }
</style>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
@endsection

@section('content')
<!-- Alertas de Rendimiento -->
@if(($metaVentasHoy ?? 0) >= 100)
<div class="success-alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-trophy me-3"></i>
        <div>
            <strong>¡Meta Alcanzada!</strong> Has superado tu objetivo de ventas diario. ¡Excelente trabajo!
        </div>
    </div>
</div>
@elseif(($metaVentasHoy ?? 0) >= 80)
<div class="warning-alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-chart-line me-3"></i>
        <div>
            <strong>¡Casi lo logras!</strong> Estás a un {{ 100 - ($metaVentasHoy ?? 0) }}% de alcanzar tu meta diaria.
        </div>
    </div>
</div>
@endif

@if(($productosAgotados ?? 0) > 0)
<div class="warning-alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle me-3"></i>
        <div>
            <strong>Stock Agotado:</strong> {{ $productosAgotados ?? 0 }} productos sin stock. 
            <a href="{{ route('productos.stock-agotado') }}" class="text-white fw-bold">Ver lista</a>
        </div>
    </div>
</div>
@endif

<!-- Métricas de Ventas -->
<div class="row g-4 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="sales-dashboard-card sales-metric-card">
            <div class="metric-icon icon-revenue">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <h3 class="mb-1">S/ {{ number_format($ventasHoy ?? 0, 2) }}</h3>
            <p class="text-muted mb-0">Ventas del Día</p>
            <small class="{{ ($crecimientoVentas ?? 0) >= 0 ? 'trend-up' : 'trend-down' }}">
                <i class="fas fa-arrow-{{ ($crecimientoVentas ?? 0) >= 0 ? 'up' : 'down' }} me-1"></i>
                {{ number_format(abs($crecimientoVentas ?? 0), 1) }}% vs ayer
            </small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="sales-dashboard-card sales-metric-card">
            <div class="metric-icon icon-orders">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h3 class="mb-1">{{ $pedidosHoy ?? 0 }}</h3>
            <p class="text-muted mb-0">Pedidos de Hoy</p>
            <small class="trend-up">
                <i class="fas fa-shopping-bag me-1"></i>
                {{ $ticketPromedio ?? 0 }} promedio
            </small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="sales-dashboard-card sales-metric-card">
            <div class="metric-icon icon-customers">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="mb-1">{{ $clientesAtendidos ?? 0 }}</h3>
            <p class="text-muted mb-0">Clientes Atendidos</p>
            <small class="{{ ($clientesNuevos ?? 0) > 0 ? 'trend-up' : 'trend-neutral' }}">
                <i class="fas fa-user-plus me-1"></i>
                {{ $clientesNuevos ?? 0 }} nuevos
            </small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="sales-dashboard-card sales-metric-card">
            <div class="metric-icon icon-products">
                <i class="fas fa-box"></i>
            </div>
            <h3 class="mb-1">{{ $productosVendidos ?? 0 }}</h3>
            <p class="text-muted mb-0">Productos Vendidos</p>
            <small class="text-info">
                <i class="fas fa-cubes me-1"></i>
                {{ $unidadesPromedio ?? 0 }} por pedido
            </small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="sales-dashboard-card sales-metric-card">
            <div class="metric-icon icon-growth">
                <i class="fas fa-percentage"></i>
            </div>
            <h3 class="mb-1">{{ number_format($margenGanancia ?? 0, 1) }}%</h3>
            <p class="text-muted mb-0">Margen de Ganancia</p>
            <small class="{{ ($margenGanancia ?? 0) >= 20 ? 'trend-up' : 'trend-down' }}">
                <i class="fas fa-chart-line me-1"></i>
                {{ ($margenGanancia ?? 0) >= 20 ? 'Excelente' : 'Mejorable' }}
            </small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="sales-dashboard-card sales-metric-card">
            <div class="metric-icon icon-target">
                <i class="fas fa-bullseye"></i>
            </div>
            <h3 class="mb-1">{{ number_format($metaVentasHoy ?? 0, 0) }}%</h3>
            <p class="text-muted mb-0">Meta Diaria</p>
            <small class="{{ ($metaVentasHoy ?? 0) >= 100 ? 'trend-up' : (($metaVentasHoy ?? 0) >= 80 ? 'trend-neutral' : 'trend-down') }}">
                <i class="fas fa-target me-1"></i>
                S/ {{ number_format($metaDiaria ?? 0, 2) }} objetivo
            </small>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Gráfico de Ventas -->
    <div class="col-lg-8">
        <div class="chart-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Ventas - Últimos 7 Días
                </h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary active" onclick="changeChartPeriod('7days')">7D</button>
                    <button type="button" class="btn btn-outline-primary" onclick="changeChartPeriod('30days')">30D</button>
                    <button type="button" class="btn btn-outline-primary" onclick="changeChartPeriod('90days')">90D</button>
                </div>
            </div>
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Meta y Performance -->
    <div class="col-lg-4">
        <div class="sales-goal">
            <h6 class="mb-3">Progreso Meta Diaria</h6>
            <div class="goal-progress">
                <svg class="goal-circle" width="120" height="120">
                    <circle cx="60" cy="60" r="50" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="8"/>
                    <circle cx="60" cy="60" r="50" fill="none" stroke="white" stroke-width="8"
                            stroke-dasharray="{{ 2 * 3.14159 * 50 }}"
                            stroke-dashoffset="{{ 2 * 3.14159 * 50 * (1 - ($metaVentasHoy ?? 0) / 100) }}"
                            class="progress-circle"/>
                </svg>
                <div class="goal-text">
                    <h4 class="mb-0">{{ number_format($metaVentasHoy ?? 0, 0) }}%</h4>
                    <small>de la meta</small>
                </div>
            </div>
            <p class="mb-0 mt-2">
                S/ {{ number_format($ventasHoy ?? 0, 2) }} de S/ {{ number_format($metaDiaria ?? 0, 2) }}
            </p>
        </div>

        <div class="sales-performance">
            <h6 class="mb-3">Performance Personal</h6>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <span>Ventas del Mes</span>
                    <span>{{ number_format($ventasMes ?? 0, 2) }}</span>
                </div>
                <div class="performance-bar">
                    <div class="performance-fill" style="width: {{ min(100, ($ventasMes ?? 0) / ($metaMensual ?? 1) * 100) }}%"></div>
                </div>
                <small class="text-muted">Meta: S/ {{ number_format($metaMensual ?? 0, 2) }}</small>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <span>Clientes del Mes</span>
                    <span>{{ $clientesMes ?? 0 }}</span>
                </div>
                <div class="performance-bar">
                    <div class="performance-fill {{ ($clientesMes ?? 0) >= ($metaClientes ?? 0) * 0.8 ? '' : 'warning' }}" 
                         style="width: {{ min(100, ($clientesMes ?? 0) / ($metaClientes ?? 1) * 100) }}%"></div>
                </div>
                <small class="text-muted">Meta: {{ $metaClientes ?? 0 }} clientes</small>
            </div>

            <div>
                <div class="d-flex justify-content-between">
                    <span>Tiempo Promedio</span>
                    <span>{{ $tiempoPromedioVenta ?? 0 }} min</span>
                </div>
                <div class="performance-bar">
                    <div class="performance-fill {{ ($tiempoPromedioVenta ?? 0) <= 5 ? '' : 'warning' }}" 
                         style="width: {{ min(100, (10 - ($tiempoPromedioVenta ?? 0)) / 10 * 100) }}%"></div>
                </div>
                <small class="text-muted">Objetivo: ≤ 5 minutos</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- Acciones Rápidas -->
    <div class="col-lg-3">
        <div class="sales-dashboard-card">
            <h5 class="mb-3">
                <i class="fas fa-bolt me-2"></i>
                Acciones Rápidas
            </h5>

            <a href="{{ route('contador.facturas.create') }}" class="quick-action-btn">
                <i class="fas fa-plus-circle" style="background: linear-gradient(135deg, #10b981, #059669);"></i>
                <div>
                    <strong>Nueva Factura</strong>
                    <small class="d-block text-muted">Crear venta rápida</small>
                </div>
            </a>

            <a href="{{ route('contador.clientes') }}" class="quick-action-btn">
                <i class="fas fa-user-plus" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);"></i>
                <div>
                    <strong>Nuevo Cliente</strong>
                    <small class="d-block text-muted">Registrar cliente</small>
                </div>
            </a>

            <a href="{{ route('contador.productos.index') }}" class="quick-action-btn">
                <i class="fas fa-search" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);"></i>
                <div>
                    <strong>Buscar Producto</strong>
                    <small class="d-block text-muted">Consulta rápida</small>
                </div>
            </a>

            <a href="{{ route('contador.caja.index') }}" class="quick-action-btn">
                <i class="fas fa-cash-register" style="background: linear-gradient(135deg, #f59e0b, #d97706);"></i>
                <div>
                    <strong>Control de Caja</strong>
                    <small class="d-block text-muted">Apertura/cierre</small>
                </div>
            </a>

            <a href="{{ route('reportes.ventas') }}" class="quick-action-btn">
                <i class="fas fa-chart-bar" style="background: linear-gradient(135deg, #06b6d4, #0891b2);"></i>
                <div>
                    <strong>Mis Reportes</strong>
                    <small class="d-block text-muted">Estadísticas personales</small>
                </div>
            </a>
        </div>
    </div>

    <!-- Top Productos -->
    <div class="col-lg-5">
        <div class="top-products-list">
            <h5 class="mb-3">
                <i class="fas fa-star me-2"></i>
                Productos Más Vendidos Hoy
            </h5>

            @forelse(($topProductosHoy ?? []) as $index => $producto)
            <div class="product-item">
                <div class="product-rank rank-{{ $index + 1 }}">
                    {{ $index + 1 }}
                </div>
                <div class="flex-grow-1">
                    <strong>{{ $producto->$nombre }}</strong>
                    <br>
                    <small class="text-muted">{{ $producto->codigo }}</small>
                </div>
                <div class="text-end">
                    <div class="fw-bold">{{ $producto->cantidad_vendida }}</div>
                    <small class="text-muted">unidades</small>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="fas fa-chart-bar fa-2x mb-2"></i>
                <p>No hay ventas registradas hoy</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Ventas Recientes -->
    <div class="col-lg-4">
        <div class="sales-dashboard-card">
            <h5 class="mb-3">
                <i class="fas fa-clock me-2"></i>
                Últimas Ventas
            </h5>

            @forelse(($ventasRecientes ?? []) as $venta)
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <strong>Factura #{{ $venta->numero }}</strong>
                    <br>
                    <small class="text-muted">{{ $venta->cliente_nombre ?? 'Cliente General' }}</small>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-success">S/ {{ number_format($venta->total, 2) }}</div>
                    <span class="status-badge status-{{ $venta->estado }}">
                        {{ ucfirst($venta->estado) }}
                    </span>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-3">
                <i class="fas fa-receipt fa-2x mb-2"></i>
                <p>No hay ventas recientes</p>
            </div>
            @endforelse

            <div class="text-center mt-3">
                <a href="{{ route('facturas.index') }}" class="btn btn-outline-primary btn-sm">
                    Ver Todas las Ventas
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Ventas del Día por Hora -->
<div class="row mt-4">
    <div class="col-12">
        <div class="chart-container">
            <h5 class="mb-3">
                <i class="fas fa-chart-bar me-2"></i>
                Distribución de Ventas por Hora - Hoy
            </h5>
            <canvas id="hourlySalesChart"></canvas>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Gráfico de ventas
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($labelsUltimos7Dias ?? []) !!},
                datasets: [{
                    label: 'Ventas Diarias',
                    data: {!! json_encode($ventasUltimos7Dias ?? []) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }, {
                    label: 'Meta Diaria',
                    data: {!! json_encode($metaDiariaArray ?? []) !!},
                    borderColor: '#10b981',
                    borderDash: [5, 5],
                    fill: false,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': S/ ' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toFixed(0);
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }

    // Gráfico de ventas por hora
    const hourlyCtx = document.getElementById('hourlySalesChart');
    if (hourlyCtx) {
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($horasDelDia ?? []) !!},
                datasets: [{
                    label: 'Ventas por Hora',
                    data: {!! json_encode($ventasPorHora ?? []) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Ventas: S/ ' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toFixed(0);
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Hora del Día'
                        }
                    }
                }
            }
        });
    }

    // Función para cambiar período del gráfico
    function changeChartPeriod(period) {
        // Remover clase active de todos los botones
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Agregar clase active al botón clickeado
        event.target.classList.add('active');
        
        // Aquí podrías hacer una llamada AJAX para cargar datos del período seleccionado
        showLoading();
        
        fetch(`/api/ventas/dashboard?period=${period}`)
            .then(response => response.json())
            .then(data => {
                // Actualizar gráfico con nuevos datos
                if (salesChart && data.salesData) {
                    salesChart.data.labels = data.labels;
                    salesChart.data.datasets[0].data = data.salesData;
                    salesChart.update();
                }
                hideLoading();
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoading();
            });
    }

    // Actualizar progreso del círculo de meta
    function updateGoalProgress(percentage) {
        const circle = document.querySelector('.progress-circle');
        if (circle) {
            const radius = 50;
            const circumference = 2 * Math.PI * radius;
            const offset = circumference - (percentage / 100) * circumference;
            circle.style.strokeDashoffset = offset;
        }
    }

    // Auto-refresh cada 30 segundos
    setInterval(function() {
        fetch('/api/ventas/dashboard/refresh')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar métricas en tiempo real
                    updateSalesMetrics(data.metrics);
                }
            })
            .catch(error => {
                console.error('Error refreshing sales data:', error);
            });
    }, 30000);

    function updateSalesMetrics(metrics) {
        // Actualizar elementos específicos con nuevos datos
        if (metrics.ventasHoy !== undefined) {
            document.querySelector('.sales-metric-card h3').textContent = 
                'S/ ' + metrics.ventasHoy.toFixed(2);
        }
        
        if (metrics.pedidosHoy !== undefined) {
            document.querySelectorAll('.sales-metric-card h3')[1].textContent = 
                metrics.pedidosHoy;
        }
    }

    // Inicializar dashboard
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar progreso de meta
        const metaPorcentaje = {{ $metaVentasHoy ?? 0 }};
        updateGoalProgress(metaPorcentaje);
        
        // Agregar animaciones a las métricas
        setTimeout(() => {
            document.querySelectorAll('.sales-metric-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, Math.random() * 500);
            });
        }, 100);
    });
</script>
@endsection