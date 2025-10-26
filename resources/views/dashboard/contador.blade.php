@extends('layouts.contador')

@section('title', 'Dashboard Contable - SIFANO')

@section('page-title', 'Área Contable')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('contador-content')

<!-- Alerta Tributaria -->
<div class="tax-alert">
    <i class="fas fa-info-circle" style="font-size: 1.5rem;"></i>
    <div>
        <strong>Recordatorio Tributario:</strong> Los libros electrónicos del mes anterior deben ser enviados a SUNAT antes del día 15.
    </div>
</div>

@hasrole('Administrador|Contador')
<!-- Métricas Principales - Fila Superior -->
<div class="financial-summary">
    <div class="summary-card">
        <div class="summary-icon icon-green">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <h6 class="text-muted mb-1">Ingresos del Mes</h6>
        <h4 class="mb-0">S/ {{ number_format($ingresosMes ?? 0, 2) }}</h4>
        <small class="text-success">
            <i class="fas fa-arrow-up me-1"></i>
            +{{ number_format(abs($crecimientoIngresos ?? 0), 1) }}% vs mes anterior
        </small>
    </div>
    
    <div class="summary-card">
        <div class="summary-icon icon-orange">
            <i class="fas fa-credit-card"></i>
        </div>
        <h6 class="text-muted mb-1">Gastos del Mes</h6>
        <h4 class="mb-0">S/ {{ number_format($gastosMes ?? 0, 2) }}</h4>
        <small class="text-muted">
            <i class="fas fa-minus me-1"></i>
            S/ {{ number_format($gastosMesAnterior ?? 0, 2) }} mes anterior
        </small>
    </div>
    
    <div class="summary-card">
        <div class="summary-icon icon-blue">
            <i class="fas fa-chart-line"></i>
        </div>
        <h6 class="text-muted mb-1">Utilidad Neta</h6>
        <h4 class="mb-0 {{ ($utilidadNeta ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
            S/ {{ number_format($utilidadNeta ?? 0, 2) }}
        </h4>
        <small class="{{ ($utilidadNeta ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
            <i class="fas fa-percentage me-1"></i>
            {{ number_format(abs($margenUtilidad ?? 0), 1) }}% margen
        </small>
    </div>
    
    <div class="summary-card">
        <div class="summary-icon icon-purple">
            <i class="fas fa-balance-scale"></i>
        </div>
        <h6 class="text-muted mb-1">Balance General</h6>
        <h4 class="mb-0">S/ {{ number_format($balanceGeneral ?? 0, 2) }}</h4>
        <small class="text-muted">
            <i class="fas fa-calendar me-1"></i>
            Actualizado: {{ date('d/m/Y') }}
        </small>
    </div>
</div>

<!-- Métricas del Día - Segunda Fila -->
<div class="financial-summary">
    <div class="summary-card">
        <div class="summary-icon icon-green">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <h6 class="text-muted mb-1">Ingresos del Día</h6>
        <h4 class="mb-0">S/ {{ number_format($ingresosHoy ?? 0, 2) }}</h4>
        <small class="{{ ($crecimientoIngresosHoy ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
            <i class="fas fa-arrow-{{ ($crecimientoIngresosHoy ?? 0) >= 0 ? 'up' : 'down' }} me-1"></i>
            {{ number_format(abs($crecimientoIngresosHoy ?? 0), 1) }}% vs ayer
        </small>
    </div>

    <div class="summary-card">
        <div class="summary-icon icon-orange">
            <i class="fas fa-credit-card"></i>
        </div>
        <h6 class="text-muted mb-1">Gastos del Día</h6>
        <h4 class="mb-0">S/ {{ number_format($gastosHoy ?? 0, 2) }}</h4>
        <small class="text-muted">
            <i class="fas fa-minus me-1"></i>
            {{ number_format(abs($crecimientoGastosHoy ?? 0), 1) }}% vs ayer
        </small>
    </div>

    <div class="summary-card">
        <div class="summary-icon icon-blue">
            <i class="fas fa-chart-line"></i>
        </div>
        <h6 class="text-muted mb-1">Utilidad del Día</h6>
        <h4 class="mb-0 {{ ($utilidadHoy ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
            S/ {{ number_format($utilidadHoy ?? 0, 2) }}
        </h4>
        <small class="{{ ($margenUtilidadHoy ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
            <i class="fas fa-percentage me-1"></i>
            {{ number_format(abs($margenUtilidadHoy ?? 0), 1) }}% margen
        </small>
    </div>

    <div class="summary-card">
        <div class="summary-icon icon-red">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <h6 class="text-muted mb-1">Ventas del Mes</h6>
        <h4 class="mb-0">{{ $ventasDelMes ?? 0 }}</h4>
        <small class="text-success">
            <i class="fas fa-arrow-up me-1"></i>
            +{{ number_format(abs($crecimientoVentas ?? 0), 1) }}% vs mes anterior
        </small>
    </div>
</div>
@endhasrole

<!-- Estado SUNAT -->
<div class="chart-container" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none;">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-2 text-white">
                <i class="fas fa-university me-2"></i>
                Estado de Libros Electrónicos SUNAT
            </h5>
            <p class="mb-0 opacity-75">Última sincronización: {{ $ultimaSyncSunat ?? 'Nunca' }}</p>
        </div>
        <div class="text-end">
            <div class="d-inline-flex align-items-center gap-2">
         
            <span style="width: 12px; height: 12px; border-radius: 50%; background: {{ $estadoSunatColor === 'green' ? '#10b981' : '#f59e0b' }}; display: inline-block;"></span>

                <strong style="font-size: 1.1rem;">{{ $estadoSunat ?? 'Pendiente' }}</strong>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Gráfico Principal: Ingresos vs Gastos -->
    <div class="col-lg-8">
        <div class="chart-container">
            <h5>
                <i class="fas fa-chart-line text-primary"></i>
                Ingresos vs Gastos - Últimos 30 Días
            </h5>
            <div class="chart-wrapper">
                <canvas id="financialTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="col-lg-4">
        <div class="quick-actions">
            <h5 class="mb-3">
                <i class="fas fa-bolt me-2"></i>
                Acciones Rápidas
            </h5>
            
            <a href="{{ route('contador.estados-financieros.balance') }}" class="action-btn">
                <div style="background: rgba(59, 130, 246, 0.1);">
                    <i class="fas fa-chart-bar" style="color: #3b82f6;"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Reportes Financieros</strong>
                    <small class="d-block text-muted">Estado de resultados, balance</small>
                </div>
            </a>

            <a href="{{ route('reportes.sunat.libros-electronicos') }}" class="action-btn">
                <div style="background: rgba(16, 185, 129, 0.1);">
                    <i class="fas fa-book" style="color: #10b981;"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Libros Electrónicos</strong>
                    <small class="d-block text-muted">Ventas, compras, diario</small>
                </div>
            </a>

            <a href="{{ route('contador.registros.ventas') }}" class="action-btn">
                <div style="background: rgba(245, 158, 11, 0.1);">
                    <i class="fas fa-file-invoice" style="color: #f59e0b;"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Registro de Ventas</strong>
                    <small class="d-block text-muted">Ver y gestionar ventas</small>
                </div>
            </a>

            <button onclick="syncWithSunat()" class="action-btn w-100 text-start border-0 bg-transparent">
                <div style="background: rgba(239, 68, 68, 0.1);">
                    <i class="fas fa-sync" style="color: #ef4444;"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Sincronizar con SUNAT</strong>
                    <small class="d-block text-muted">Enviar libros electrónicos</small>
                </div>
            </button>
        </div>
    </div>
</div>

<!-- Segunda Fila de Gráficos -->
<div class="row g-3 mt-2">
    <!-- Distribución de Gastos -->
    <div class="col-lg-6">
        <div class="chart-container">
            <h5>
                <i class="fas fa-chart-pie text-warning"></i>
                Distribución de Gastos del Mes
            </h5>
            <div class="chart-wrapper">
                <canvas id="expenseDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Comparativo Mensual -->
    <div class="col-lg-6">
        <div class="chart-container">
            <h5>
                <i class="fas fa-chart-bar text-info"></i>
                Comparativo Mensual (Últimos 6 Meses)
            </h5>
            <div class="chart-wrapper">
                <canvas id="monthlyComparisonChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Movimientos Recientes -->
<div class="row mt-3">
    <div class="col-12">
        <div class="contador-card">
            <h5 class="mb-3">
                <i class="fas fa-list me-2"></i>
                Movimientos Financieros Recientes
            </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Documento</th>
                            <th>Concepto</th>
                            <th class="text-end">Monto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($movimientosRecientes ?? []) as $movimiento)
                        <tr>
                            <td>{{ date('d/m/Y H:i', strtotime($movimiento->$fecha ?? now())) }}</td>
                            <td>
                                <span class="badge bg-{{ $movimiento->$tipo === 'ingreso' ? 'success' : 'warning' }}">
                                    {{ ucfirst($movimiento->$tipo ?? 'N/A') }}
                                </span>
                            </td>
                            <td>{{ $movimiento->$documento ?? 'N/A' }}</td>
                            <td>{{ $movimiento->$concepto ?? 'Sin concepto' }}</td>
                            <td class="text-end fw-bold {{ $movimiento->$tipo === 'ingreso' ? 'text-success' : 'text-warning' }}"></td>
                                {{ $movimiento->$tipo === 'ingreso' ? '+' : '-' }}S/ {{ number_format($movimiento->$monto ?? 0, 2) }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $movimiento->$estado === 'confirmado' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($movimiento->$estado ?? 'Pendiente') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de Tendencias Financieras (Líneas)
    const financialTrendCtx = document.getElementById('financialTrendChart');
    if (financialTrendCtx) {
        new Chart(financialTrendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($labelsUltimos30Dias ?? array_map(fn($i) => date('d/m', strtotime("-$i days")), range(29, 0))) !!},
                datasets: [{
                    label: 'Ingresos',
                    data: {!! json_encode($ingresos30Dias ?? array_fill(0, 30, 0)) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                }, {
                    label: 'Gastos',
                    data: {!! json_encode($gastos30Dias ?? array_fill(0, 30, 0)) !!},
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                }, {
                    label: 'Utilidad',
                    data: {!! json_encode($utilidad30Dias ?? array_fill(0, 30, 0)) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
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
                                return 'S/ ' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    const expenseCtx = document.getElementById('expenseDistributionChart');
        if (expenseCtx) {
            new Chart(expenseCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($categoriasGastos ?? ['Operativos', 'Personal', 'Marketing', 'Otros']) !!},
                    datasets: [{
                        data: {!! json_encode($montosCategoriasGastos ?? [5000, 3000, 2000, 1000]) !!},
                        backgroundColor: [
                            '#3b82f6',
                            '#10b981',
                            '#f59e0b',
                            '#ef4444',
                            '#8b5cf6',
                            '#06b6d4'
                        ];
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        hoverOffset: 8,
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
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
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
        };

    // Gráfico Comparativo Mensual (Barras)
    const comparisonCtx = document.getElementById('monthlyComparisonChart');
    if (comparisonCtx) {
        new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($mesesComparativo ?? ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun']) !!},
                datasets: [{
                    label: 'Ingresos',
                    data: {!! json_encode($ingresosComparativo ?? [15000, 18000, 16000, 20000, 22000, 25000]) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                    borderSkipped: false
                }, {
                    label: 'Gastos',
                    data: {!! json_encode($gastosComparativo ?? [8000, 9000, 8500, 10000, 11000, 12000]) !!},
                    backgroundColor: '#f59e0b',
                    borderRadius: 6,
                    borderSkipped: false
                }, {
                    label: 'Utilidad',
                    data: {!! json_encode($utilidadComparativo ?? [7000, 9000, 7500, 10000, 11000, 13000]) !!},
                    backgroundColor: '#3b82f6',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': S/ ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush