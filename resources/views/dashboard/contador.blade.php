@extends('layouts.app')

@section('title', 'Dashboard Contador - Distribuidora')

@section('styles')
<style>
    :root {
        --primary: #2563eb;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #06b6d4;
        --dark: #1f2937;
        --light: #f3f4f6;
    }

    .dashboard-contador {
        background: #f9fafb;
        min-height: 100vh;
        padding: 2rem 0;
    }

    .metric-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border-left: 4px solid var(--primary);
        transition: all 0.3s ease;
        height: 100%;
    }

    .metric-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    .metric-card.success { border-left-color: var(--success); }
    .metric-card.warning { border-left-color: var(--warning); }
    .metric-card.danger { border-left-color: var(--danger); }
    .metric-card.info { border-left-color: var(--info); }

    .metric-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .metric-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .metric-icon.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
    .metric-icon.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
    .metric-icon.warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
    .metric-icon.danger { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
    .metric-icon.info { background: rgba(6, 182, 212, 0.1); color: var(--info); }

    .metric-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0.5rem 0;
    }

    .metric-label {
        color: #6b7280;
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .metric-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .metric-badge.up {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .metric-badge.down {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }

    .chart-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 1.5rem;
    }

    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .table-header {
        padding: 1.25rem 1.5rem;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--dark);
    }

    .custom-table {
        width: 100%;
        font-size: 0.875rem;
    }

    .custom-table thead {
        background: #f9fafb;
    }

    .custom-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .custom-table td {
        padding: 1rem;
        border-top: 1px solid #f3f4f6;
    }

    .custom-table tbody tr:hover {
        background: #f9fafb;
    }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge.success {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .status-badge.warning {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning);
    }

    .status-badge.danger {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .status-badge.secondary {
        background: rgba(107, 114, 128, 0.1);
        color: #6b7280;
    }

    .alert-item {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 0.75rem;
    }

    .alert-item.danger { background: rgba(239, 68, 68, 0.05); border-left: 3px solid var(--danger); }
    .alert-item.warning { background: rgba(245, 158, 11, 0.05); border-left: 3px solid var(--warning); }
    .alert-item.info { background: rgba(6, 182, 212, 0.05); border-left: 3px solid var(--info); }

    .alert-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .alert-icon.danger { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
    .alert-icon.warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
    .alert-icon.info { background: rgba(6, 182, 212, 0.1); color: var(--info); }

    .alert-content h6 {
        font-size: 0.875rem;
        font-weight: 600;
        margin: 0 0 0.25rem 0;
        color: var(--dark);
    }

    .alert-content p {
        font-size: 0.8rem;
        color: #6b7280;
        margin: 0;
    }

    .progress-bar-custom {
        height: 8px;
        background: #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .progress-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease;
    }

    .progress-fill.danger { background: var(--danger); }
    .progress-fill.warning { background: var(--warning); }
    .progress-fill.success { background: var(--success); }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .page-header {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0 0 0.5rem 0;
    }

    .page-header p {
        color: #6b7280;
        margin: 0;
    }

    @media (max-width: 768px) {
        .metric-value { font-size: 1.5rem; }
        .dashboard-contador { padding: 1rem 0; }
    }
</style>
@endsection

@section('content')
<div class="dashboard-contador">
    <div class="container-fluid px-4">
        
        {{-- Header --}}
        <div class="page-header">
            <h1>📊 Dashboard Contador</h1>
            <p>Distribuidora de Fármacos - Panel de Control Financiero</p>
        </div>

        {{-- Métricas Principales --}}
        <div class="row g-3 mb-4">
            {{-- Ventas del Mes --}}
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-card primary">
                    <div class="metric-header">
                        <div>
                            <div class="metric-label">Ventas del Mes</div>
                            <div class="metric-value">S/ {{ number_format($ventasMes, 2) }}</div>
                            @if($variacionVentas != 0)
                                <span class="metric-badge {{ $variacionVentas > 0 ? 'up' : 'down' }}">
                                    <i class="fas fa-{{ $variacionVentas > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                    {{ abs($variacionVentas) }}% vs mes anterior
                                </span>
                            @endif
                        </div>
                        <div class="metric-icon primary">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cuentas por Cobrar --}}
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-card {{ $cuentasPorCobrarVencidas > ($cuentasPorCobrar * 0.3) ? 'danger' : 'warning' }}">
                    <div class="metric-header">
                        <div>
                            <div class="metric-label">Cuentas por Cobrar</div>
                            <div class="metric-value">S/ {{ number_format($cuentasPorCobrar, 2) }}</div>
                            @if($cuentasPorCobrarVencidas > 0)
                                <span class="metric-badge down">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    S/ {{ number_format($cuentasPorCobrarVencidas, 2) }} vencidas
                                </span>
                            @endif
                        </div>
                        <div class="metric-icon {{ $cuentasPorCobrarVencidas > ($cuentasPorCobrar * 0.3) ? 'danger' : 'warning' }}">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ticket Promedio --}}
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-card success">
                    <div class="metric-header">
                        <div>
                            <div class="metric-label">Ticket Promedio</div>
                            <div class="metric-value">S/ {{ number_format($ticketPromedio, 2) }}</div>
                            <span class="metric-badge up">
                                <i class="fas fa-shopping-cart"></i>
                                Valor por venta
                            </span>
                        </div>
                        <div class="metric-icon success">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Margen Bruto --}}
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-card info">
                    <div class="metric-header">
                        <div>
                            <div class="metric-label">Margen Bruto</div>
                            <div class="metric-value">{{ number_format($margenBrutoMes, 1) }}%</div>
                            <span class="metric-badge {{ $margenBrutoMes > 15 ? 'up' : 'down' }}">
                                <i class="fas fa-{{ $margenBrutoMes > 15 ? 'check-circle' : 'exclamation-circle' }}"></i>
                                {{ $margenBrutoMes > 15 ? 'Saludable' : 'Bajo' }}
                            </span>
                        </div>
                        <div class="metric-icon info">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Indicadores Secundarios --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="metric-card">
                    <div class="metric-header">
                        <div>
                            <div class="metric-label">Clientes Activos</div>
                            <div class="metric-value">{{ number_format($clientesActivos) }}</div>
                        </div>
                        <div class="metric-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="metric-card">
                    <div class="metric-header">
                        <div>
                            <div class="metric-label">Facturas Pendientes</div>
                            <div class="metric-value">{{ number_format($facturasPendientes) }}</div>
                            <span class="metric-badge {{ $facturasVencidas > 0 ? 'down' : 'up' }}">
                                {{ $facturasVencidas }} vencidas
                            </span>
                        </div>
                        <div class="metric-icon warning">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="metric-card">
                    <div class="metric-header">
                        <div>
                            <div class="metric-label">Días Prom. Cobranza</div>
                            <div class="metric-value">{{ $diasPromedioCobranza }}</div>
                            <span class="metric-badge {{ $diasPromedioCobranza <= 30 ? 'up' : 'down' }}">
                                <i class="fas fa-{{ $diasPromedioCobranza <= 30 ? 'check' : 'clock' }}"></i>
                                {{ $diasPromedioCobranza <= 30 ? 'Óptimo' : 'Mejorar' }}
                            </span>
                        </div>
                        <div class="metric-icon info">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráficos --}}
        <div class="row g-3 mb-4">
            {{-- Gráfico Ventas y Cobranzas --}}
            <div class="col-12 col-lg-8">
                <div class="chart-card">
                    <h3 class="chart-title">📈 Ventas y Cobranzas (Últimos 6 Meses)</h3>
                    <canvas id="ventasCobranzasChart" height="80"></canvas>
                </div>
            </div>

            {{-- Top Clientes --}}
            <div class="col-12 col-lg-4">
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">🏆 Top 10 Clientes del Mes</h3>
                    </div>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topClientes as $index => $cliente)
                                    <tr>
                                        <td><strong>{{ $index + 1 }}</strong></td>
                                        <td>
                                            <div style="font-weight: 600; font-size: 0.85rem;">
                                                {{ Str::limit($cliente['cliente'], 30) }}
                                            </div>
                                            <small class="text-muted">{{ $cliente['facturas'] }} facturas</small>
                                        </td>
                                        <td class="text-end">
                                            <strong style="color: var(--success);">S/ {{ number_format($cliente['total'], 2) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No hay datos disponibles</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alertas --}}
        @if(count($alertas) > 0)
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="section-title">
                    <i class="fas fa-bell"></i> Alertas y Notificaciones
                </h2>
                <div class="row g-3">
                    @foreach($alertas as $alerta)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="alert-item {{ $alerta['tipo'] }}">
                            <div class="alert-icon {{ $alerta['tipo'] }}">
                                <i class="fas fa-{{ $alerta['icono'] }}"></i>
                            </div>
                            <div class="alert-content">
                                <h6>{{ $alerta['titulo'] }}</h6>
                                <p>{{ $alerta['mensaje'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Tablas de Datos --}}
        <div class="row g-3 mb-4">
            {{-- Ventas Recientes --}}
            <div class="col-12 col-xl-6">
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">📋 Ventas Recientes</h3>
                        <a href="#" class="btn btn-sm btn-outline-primary">Ver todas</a>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventasRecientes as $venta)
                                    <tr>
                                        <td>
                                            <strong>{{ $venta['numero'] }}</strong><br>
                                            <small class="text-muted">{{ $venta['tipo'] }}</small>
                                        </td>
                                        <td>{{ Str::limit($venta['cliente'], 25) }}</td>
                                        <td>{{ $venta['fecha'] }}</td>
                                        <td class="text-end">
                                            <strong>S/ {{ number_format($venta['total'], 2) }}</strong>
                                            @if($venta['saldo'] > 0)
                                                <br><small class="text-danger">Saldo: S/ {{ number_format($venta['saldo'], 2) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="status-badge {{ $venta['estado_class'] }}">
                                                {{ $venta['estado'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay ventas recientes</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Productos Stock Bajo --}}
            <div class="col-12 col-xl-6">
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">📦 Productos con Stock Bajo</h3>
                        <a href="#" class="btn btn-sm btn-outline-warning">Ver todos</a>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Laboratorio</th>
                                    <th class="text-center">Stock</th>
                                    <th>Nivel</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productosStockBajo as $producto)
                                    <tr>
                                        <td>
                                            <strong>{{ $producto['codigo'] }}</strong><br>
                                            <small>{{ Str::limit($producto['nombre'], 30) }}</small>
                                        </td>
                                        <td>{{ Str::limit($producto['laboratorio'], 20) }}</td>
                                        <td class="text-center">
                                            <strong>{{ number_format($producto['stock'], 0) }}</strong> / {{ number_format($producto['minimo'], 0) }}
                                        </td>
                                        <td>
                                            <div class="progress-bar-custom">
                                                <div class="progress-fill {{ $producto['porcentaje'] < 30 ? 'danger' : ($producto['porcentaje'] < 70 ? 'warning' : 'success') }}" 
                                                     style="width: {{ min($producto['porcentaje'], 100) }}%">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $producto['porcentaje'] }}%</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Todos los productos tienen stock adecuado</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Productos Próximos a Vencer --}}
        @if(count($productosProximosVencer) > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">⏰ Productos Próximos a Vencer (90 días)</h3>
                        <a href="#" class="btn btn-sm btn-outline-danger">Ver todos</a>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Laboratorio</th>
                                    <th>Lote</th>
                                    <th>Vencimiento</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center">Días</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productosProximosVencer as $producto)
                                    <tr>
                                        <td><strong>{{ $producto['codigo'] }}</strong></td>
                                        <td>{{ Str::limit($producto['nombre'], 40) }}</td>
                                        <td>{{ Str::limit($producto['laboratorio'], 20) }}</td>
                                        <td>{{ $producto['lote'] }}</td>
                                        <td>{{ $producto['vencimiento'] }}</td>
                                        <td class="text-center">{{ number_format($producto['stock'], 0) }}</td>
                                        <td class="text-center">
                                            <span class="status-badge {{ $producto['dias'] <= 30 ? 'danger' : ($producto['dias'] <= 60 ? 'warning' : 'info') }}">
                                                {{ $producto['dias'] }} días
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico Ventas y Cobranzas
        const ctx = document.getElementById('ventasCobranzasChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($mesesLabels),
                    datasets: [
                        {
                            label: 'Ventas',
                            data: @json($ventasData),
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        },
                        {
                            label: 'Cobranzas',
                            data: @json($cobranzasData),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += 'S/ ' + context.parsed.y.toLocaleString('es-PE', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'S/ ' + value.toLocaleString('es-PE');
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

        // Auto-refresh cada 5 minutos
        setTimeout(function() {
            location.reload();
        }, 300000);
    });
</script>

@endsection
