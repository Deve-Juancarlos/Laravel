@extends('layouts.contador')

@section('title', 'Dashboard Contable - SIFANO')

@push('styles')
<style>
    .dashboard-metric {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        transition: transform 0.2s ease;
    }

    .dashboard-metric:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.15);
    }

    .metric-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-bottom: 1rem;
    }

    .icon-success { background: linear-gradient(135deg, #10b981, #059669); }
    .icon-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .icon-info { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .icon-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }

    .chart-container {
        position: relative;
        height: 350px;
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .quick-actions {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .action-btn {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-radius: 0.75rem;
        text-decoration: none;
        color: #374151;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
        margin-bottom: 0.75rem;
    }

    .action-btn:hover {
        background: #f9fafb;
        border-color: #059669;
        color: #059669;
        text-decoration: none;
        transform: translateX(5px);
    }

    .action-btn i {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.25rem;
    }

    .sunat-status-card {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: white;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .sunat-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.5rem;
    }

    .indicator-green { background: #10b981; }
    .indicator-yellow { background: #f59e0b; }
    .indicator-red { background: #ef4444; }

    .financial-alert {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .financial-alert.critical {
        background: #fee2e2;
        border-color: #ef4444;
    }

    .trend-up { color: #10b981; }
    .trend-down { color: #ef4444; }
    .trend-neutral { color: #6b7280; }
</style>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
@endsection

@section('contador-content')
<div class="row g-4 mb-4">
    <!-- Ingresos del Día -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="dashboard-metric">
            <div class="d-flex align-items-center">
                <div class="metric-icon icon-success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="text-muted mb-1">Ingresos del Día</p>
                    <h3 class="mb-0">S/ {{ number_format($ingresosHoy ?? 0, 2) }}</h3>
                    <small class="{{ ($crecimientoIngresos ?? 0) >= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="fas fa-arrow-{{ ($crecimientoIngresos ?? 0) >= 0 ? 'up' : 'down' }} me-1"></i>
                        {{ number_format(abs($crecimientoIngresos ?? 0), 1) }}% vs ayer
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gastos del Día -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="dashboard-metric">
            <div class="d-flex align-items-center">
                <div class="metric-icon icon-warning">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="text-muted mb-1">Gastos del Día</p>
                    <h3 class="mb-0">S/ {{ number_format($gastosHoy ?? 0, 2) }}</h3>
                    <small class="{{ ($crecimientoGastos ?? 0) <= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="fas fa-{{ ($crecimientoGastos ?? 0) <= 0 ? 'down' : 'up' }} me-1"></i>
                        {{ number_format(abs($crecimientoGastos ?? 0), 1) }}% vs ayer
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Utilidad Neta -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="dashboard-metric">
            <div class="d-flex align-items-center">
                <div class="metric-icon icon-info">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="text-muted mb-1">Utilidad del Día</p>
                    <h3 class="mb-0 {{ ($utilidadHoy ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                        S/ {{ number_format($utilidadHoy ?? 0, 2) }}
                    </h3>
                    <small class="{{ ($margenUtilidadHoy ?? 0) >= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="fas fa-percentage me-1"></i>
                        {{ number_format($margenUtilidadHoy ?? 0, 1) }}% margen
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Ventas del Mes -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="dashboard-metric">
            <div class="d-flex align-items-center">
                <div class="metric-icon icon-danger">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="text-muted mb-1">Ventas del Mes</p>
                    <h3 class="mb-0">{{ $ventasDelMes ?? 0 }}</h3>
                    <small class="trend-up">
                        <i class="fas fa-arrow-up me-1"></i>
                        {{ number_format(($crecimientoVentas ?? 0), 1) }}% vs mes anterior
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estado SUNAT -->
<div class="sunat-status-card">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-2">
                <i class="fas fa-university me-2"></i>
                Estado de Libros Electrónicos SUNAT
            </h5>
            <p class="mb-0">Última sincronización: {{ $ultimaSyncSunat ?? 'Nunca' }}</p>
        </div>
        <div class="text-end">
            <span class="sunat-indicator {{ $estadoSunatColor ?? 'indicator-yellow' }}"></span>
            <strong>{{ $estadoSunat ?? 'Pendiente' }}</strong>
        </div>
    </div>
</div>

<!-- Alertas Financieras -->
@if(count($alertasFinancieras ?? []) > 0)
    @foreach($alertasFinancieras as $alerta)
        <div class="financial-alert {{ $alerta['nivel'] === 'critico' ? 'critical' : '' }}">
            <div class="d-flex align-items-center">
                <i class="fas fa-{{ $alerta['icono'] }} me-2"></i>
                <div>
                    <strong>{{ $alerta['titulo'] }}</strong>
                    <p class="mb-0">{{ $alerta['mensaje'] }}</p>
                </div>
            </div>
        </div>
    @endforeach
@endif

<div class="row g-4">
    <!-- Gráfico de Ingresos vs Gastos -->
    <div class="col-lg-8">
        <div class="chart-container">
            <h5 class="mb-3">
                <i class="fas fa-chart-line me-2"></i>
                Ingresos vs Gastos - Últimos 30 Días
            </h5>
            <canvas id="financialTrendChart"></canvas>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="col-lg-4">
        <div class="quick-actions">
            <h5 class="mb-3">
                <i class="fas fa-bolt me-2"></i>
                Acciones Rápidas
            </h5>
            
            <a href="{{ route('contador.reportes.financiero') }}" class="action-btn">
                <i class="fas fa-chart-bar text-primary"></i>
                <div>
                    <strong>Ver Reportes Financieros</strong>
                    <small class="d-block text-muted">Estado de resultados, balance general</small>
                </div>
            </a>

            <a href="{{ route('contador.libros-electronicos') }}" class="action-btn">
                <i class="fas fa-book text-success"></i>
                <div>
                    <strong>Libros Electrónicos</strong>
                    <small class="d-block text-muted">Ventas, compras, diario</small>
                </div>
            </a>

            <a href="{{ route('contador.facturas.index') }}" class="action-btn">
                <i class="fas fa-file-invoice text-info"></i>
                <div>
                    <strong>Gestionar Facturas</strong>
                    <small class="d-block text-muted">Ver, crear, editar facturas</small>
                </div>
            </a>

            <a href="{{ route('contador.reportes.exportar') }}" class="action-btn">
                <i class="fas fa-download text-warning"></i>
                <div>
                    <strong>Exportar Datos</strong>
                    <small class="d-block text-muted">Excel, CSV, PDF</small>
                </div>
            </a>

            <button onclick="syncWithSunat()" class="action-btn w-100 text-start border-0 bg-transparent">
                <i class="fas fa-sync text-danger"></i>
                <div>
                    <strong>Sincronizar con SUNAT</strong>
                    <small class="d-block text-muted">Enviar libros electrónicos</small>
                </div>
            </button>
        </div>
    </div>
</div>

<!-- Segunda fila de gráficos -->
<div class="row g-4 mt-2">
    <!-- Distribución de Gastos -->
    <div class="col-lg-6">
        <div class="chart-container">
            <h5 class="mb-3">
                <i class="fas fa-chart-pie me-2"></i>
                Distribución de Gastos del Mes
            </h5>
            <canvas id="expenseDistributionChart"></canvas>
        </div>
    </div>

    <!-- Comparativo Mensual -->
    <div class="col-lg-6">
        <div class="chart-container">
            <h5 class="mb-3">
                <i class="fas fa-chart-bar me-2"></i>
                Comparativo Mensual
            </h5>
            <canvas id="monthlyComparisonChart"></canvas>
        </div>
    </div>
</div>

<!-- Tabla de Movimientos Recientes -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Movimientos Financieros Recientes
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($movimientosRecientes ?? []) as $movimiento)
                            <tr>
                                <td>{{ date('d/m/Y H:i', strtotime($movimiento->$fecha)) }}</td>
                                <td>
                                    <span class="badge bg-{{ $movimiento->$tipo === 'ingreso' ? 'success' : 'warning' }}">
                                        {{ ucfirst($movimiento->$tipo) }}
                                    </span>
                                </td>
                                <td>{{ $movimiento->$concepto }}</td>
                                <td class="fw-bold {{ $movimiento->$tipo === 'ingreso' ? 'text-success' : 'text-warning' }}">
                                    {{ $movimiento->$tipo === 'ingreso' ? '+' : '-' }}S/ {{ number_format($movimiento->$monto, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $movimiento->$estado === 'confirmado' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($movimiento->$estado) }}
                                    </span>
                                </td>
                                <td>{{ $movimiento->$usuario_nombre ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No hay movimientos recientes
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Gráfico de tendencias financieras
    const financialTrendCtx = document.getElementById('financialTrendChart');
    if (financialTrendCtx) {
        new Chart(financialTrendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($labelsUltimos30Dias ?? []) !!},
                datasets: [{
                    label: 'Ingresos',
                    data: {!! json_encode($ingresos30Dias ?? []) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Gastos',
                    data: {!! json_encode($gastos30Dias ?? []) !!},
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Utilidad',
                    data: {!! json_encode($utilidad30Dias ?? []) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
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

    // Gráfico de distribución de gastos
    const expenseCtx = document.getElementById('expenseDistributionChart');
    if (expenseCtx) {
        new Chart(expenseCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($categoriasGastos ?? []) !!},
                datasets: [{
                    data: {!! json_encode($montosCategoriasGastos ?? []) !!},
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#06b6d4',
                        '#f97316'
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
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': S/ ' + context.parsed.toFixed(2) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico comparativo mensual
    const comparisonCtx = document.getElementById('monthlyComparisonChart');
    if (comparisonCtx) {
        new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($mesesComparativo ?? []) !!},
                datasets: [{
                    label: 'Ingresos',
                    data: {!! json_encode($ingresosComparativo ?? []) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 4
                }, {
                    label: 'Gastos',
                    data: {!! json_encode($gastosComparativo ?? []) !!},
                    backgroundColor: '#f59e0b',
                    borderRadius: 4
                }, {
                    label: 'Utilidad',
                    data: {!! json_encode($utilidadComparativo ?? []) !!},
                    backgroundColor: '#3b82f6',
                    borderRadius: 4
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
                }
            }
        });
    }

    // Auto-refresh cada 5 minutos
    setInterval(function() {
        location.reload();
    }, 300000);
</script>
@endsection