@extends('layouts.app')

<!-- 1. Título de la Página -->
@section('title', 'Dashboard Contador')

<!-- 2. Título de la Cabecera -->
@section('page-title', 'Dashboard (Contador)')

<!-- 3. Breadcrumbs -->
@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

<!-- 4. Estilos CSS Específicos para este Dashboard -->
@push('styles')
<style>
    /* MEJORA: Nuevos estilos para las tarjetas de KPI.
      Más modernas, con iconos grandes y color.
    */
    .kpi-card {
        display: flex;
        align-items: center;
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
    }
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    }
    .kpi-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1.5rem;
        font-size: 1.75rem;
        color: #fff;
    }
    .kpi-content .kpi-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }
    .kpi-content .kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #343a40;
    }
    .kpi-content .kpi-delta {
        font-size: 0.875rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }

    /* MEJORA: Estilos para el centro de alertas */
    .alert-list .list-group-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.25rem;
        border-left-width: 5px;
        border-radius: 0.25rem;
        margin-bottom: 0.5rem;
    }
    .alert-list .alert-icon {
        font-size: 1.25rem;
        margin-right: 1rem;
    }
    .alert-list small {
        font-size: 0.85rem;
    }

    /* MEJORA: Estilos para la lista de Top Clientes */
    .top-client-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        margin-right: 1rem;
    }
    .top-client-name {
        font-weight: 600;
        color: #333;
    }
    .top-client-code {
        font-size: 0.8rem;
        color: #777;
    }
    .top-client-total {
        font-weight: 700;
        color: #0d6efd;
    }
    
    /* MEJORA: Estilos para las tablas de inventario */
    .inventory-table strong {
        font-weight: 600;
        color: #333;
    }
    .inventory-table .progress {
        height: 8px;
        border-radius: 4px;
        background: #e9ecef;
    }
</style>
@endpush


<!-- 5. Contenido Principal -->
@section('content')

<!-- Fila 1: KPIs Principales (Diseño Mejorado) -->
<div class="row">
    <!-- Ventas del Mes -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="kpi-card">
            <div class="kpi-icon bg-primary">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">Ventas (Mes)</div>
                <div class="kpi-value">S/ {{ number_format($ventasMes, 2) }}</div>
            </div>
        </div>
    </div>
    
    <!-- Cuentas por Cobrar -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="kpi-card">
            <div class="kpi-icon bg-success">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">Cuentas por Cobrar</div>
                <div class="kpi-value">S/ {{ number_format($cuentasPorCobrar, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Variación de Ventas -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="kpi-card">
            <div class="kpi-icon {{ $variacionVentas >= 0 ? 'bg-info' : 'bg-danger' }}">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">Variación (vs Mes Ant.)</div>
                <div class="kpi-value">
                    {{ number_format($variacionVentas, 2) }}%
                    @if($variacionVentas > 0)
                        <span class="kpi-delta text-success"><i class="fas fa-arrow-up"></i></span>
                    @elseif($variacionVentas < 0)
                        <span class="kpi-delta text-danger"><i class="fas fa-arrow-down"></i></span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Facturas Vencidas -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="kpi-card">
            <div class="kpi-icon bg-danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">Facturas Vencidas</div>
                <div class="kpi-value">{{ $facturasVencidas }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Fila 2: Gráficos Principales y Alertas -->
<div class="row">
    <!-- Gráfico de Ventas -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Ventas vs Cobranzas (Últimos 6 Meses)</h6>
                <!-- MEJORA: Botón de acción -->
                <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-alt me-1"></i> Ver Reporte</a>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 350px;">
                    <canvas id="ventasChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas (Diseño Mejorado) -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-danger">Centro de Alertas</h6>
            </div>
            <div class="card-body alert-list" style="height: 398px; overflow-y: auto;">
                @forelse($alertas as $alerta)
                    <a href="{{ $alerta['accion'] ?? '#' }}" class="list-group-item list-group-item-action list-group-item-{{ $alerta['tipo'] }}">
                        <div class="alert-icon text-{{ $alerta['tipo'] }}">
                            <i class="fas fa-{{ $alerta['icono'] }}"></i>
                        </div>
                        <div>
                            <strong>{{ $alerta['titulo'] }}</strong><br>
                            <small>{{ $alerta['mensaje'] }}</small>
                        </div>
                    </a>
                @empty
                    <div class="text-center text-muted p-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="h6">¡Todo en orden!</p>
                        <p>No hay alertas críticas.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Fila 3: Listados (Diseño Mejorado) -->
<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Top 10 Clientes del Mes</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($topClientes as $cliente)
                    <li class="list-group-item d-flex align-items-center justify-content-between p-3">
                        <div class="d-flex align-items-center">
                            <div class="top-client-avatar" style="background-color: {{ $cliente['avatar_color'] }};">
                                {{ substr($cliente['cliente'], 0, 1) }}
                            </div>
                            <div>
                                <div class="top-client-name">{{ $cliente['cliente'] }}</div>
                                <div class="top-client-code">Cód: {{ $cliente['codigo'] }}</div>
                            </div>
                        </div>
                        <span class="top-client-total">S/ {{ number_format($cliente['total'], 2) }}</span>
                    </li>
                    @empty
                    <li class="list-group-item text-muted text-center p-4">No hay ventas registradas este mes.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Ventas Recientes</h6>
            </div>
            <div class="card-body p-0" style="max-height: 485px; overflow-y: auto;">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventasRecientes as $venta)
                        <tr>
                            <td>{{ $venta['fecha'] }}</td>
                            <td>{{ $venta['cliente'] }}</td>
                            <td class="text-end">S/ {{ number_format($venta['total'], 2) }}</td>
                            <td><span class="badge bg-{{ $venta['estado_class'] }}">{{ $venta['estado'] }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted p-4">No hay ventas recientes.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Fila 4: Análisis de Inventario (Diseño Mejorado) -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">Gestión de Inventario Crítico</h6>
            </div>
            <div class="card-body">
                <div class="row inventory-table">
                    <!-- Stock Bajo -->
                    <div class="col-lg-6">
                        <h6>Productos con Stock Bajo</h6>
                        <table class="table table-sm align-middle">
                            <tbody>
                                @forelse($productosStockBajo as $p)
                                <tr class="border-bottom">
                                    <td class="w-50">
                                        <strong>{{ $p['nombre'] }}</strong>
                                        <small class="d-block text-muted">{{ $p['codigo'] }} | {{ $p['laboratorio'] }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end align-items-center">
                                            <span class="h6 mb-0 me-2">{{ $p['stock'] }}</span> / 
                                            <span class="text-muted ms-2">{{ $p['minimo'] }}</span>
                                        </div>
                                        <!-- MEJORA: Barra de Progreso -->
                                        <div class="progress mt-1">
                                            <div class="progress-bar bg-{{ $p['criticidad'] == 'crítica' ? 'danger' : 'warning' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $p['porcentaje'] }}%;" 
                                                 aria-valuenow="{{ $p['porcentaje'] }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td class="text-muted p-3">No hay productos con stock bajo.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Próximos a Vencer -->
                    <div class="col-lg-6">
                        <h6>Productos Próximos a Vencer (90 días)</h6>
                        <table class="table table-sm align-middle">
                             <tbody>
                                @forelse($productosProximosVencer as $p)
                                <!-- MEJORA: Fila coloreada según riesgo -->
                                <tr class="border-bottom {{ $p['riesgo'] == 'alto' ? 'table-danger' : ($p['riesgo'] == 'medio' ? 'table-warning' : '') }}">
                                    <td>
                                        <strong>{{ $p['nombre'] }}</strong>
                                        <small class="d-block text-muted">{{ $p['lote'] }} | {{ $p['laboratorio'] }}</Sall>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ $p['vencimiento'] }}</strong>
                                        <small class="d-block text-danger">({{ $p['dias'] }} días)</small>
                                    </td>
                                </tr>
                                @empty
                                <tr><td class="text-muted p-3">No hay productos próximos a vencer.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- 6. Scripts Específicos de la Página (Sin cambios) -->
@push('scripts')
<script>
    // Configuración CSRF para AJAX (si es necesario para limpiar caché, etc.)
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Gráfico de Ventas (Chart.js)
    const ctxVentas = document.getElementById('ventasChart');
    if (ctxVentas) {
        new Chart(ctxVentas, {
            type: 'line',
            data: {
                // Datos del controlador
                labels: @json($mesesLabels),
                datasets: [
                {
                    label: 'Ventas',
                    data: @json($ventasData),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#0d6efd'
                },
                {
                    label: 'Cobranzas',
                    data: @json($cobranzasData),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#198754'
                }
            ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return 'S/ ' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'S/ ' + context.parsed.y.toLocaleString('es-PE', { minimumFractionDigits: 2 });
                                }
                                return label;
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                }
            }
        });
    }
</script>
@endpush

