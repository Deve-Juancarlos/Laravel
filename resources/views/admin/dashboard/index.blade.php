@extends('layouts.admin')

@section('title', 'Dashboard Administrativo')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Header con título y fecha -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0 text-dark fw-bold">Dashboard Administrativo</h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        {{ \Carbon\Carbon::now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.dashboard.resumen') }}" class="btn btn-primary">
                        <i class="fas fa-file-pdf me-2"></i>Resumen Ejecutivo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Principales -->
    <div class="row g-3 mb-4">
        <!-- Ventas del Día -->
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Ventas del Día</p>
                            <h3 class="mb-0 fw-bold text-primary">
                                S/ {{ number_format($data['kpis']['ventas_hoy'], 2) }}
                            </h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-shopping-cart fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ventas del Mes -->
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Ventas del Mes</p>
                            <h3 class="mb-0 fw-bold text-success">
                                S/ {{ number_format($data['kpis']['ventas_mes'], 2) }}
                            </h3>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-chart-line fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cuentas por Cobrar -->
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Por Cobrar</p>
                            <h3 class="mb-0 fw-bold text-warning">
                                S/ {{ number_format($data['kpis']['total_por_cobrar'], 2) }}
                            </h3>
                            <small class="text-danger">
                                Vencido: S/ {{ number_format($data['kpis']['cuentas_vencidas'], 2) }}
                            </small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-hand-holding-usd fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cuentas por Pagar -->
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Por Pagar</p>
                            <h3 class="mb-0 fw-bold text-danger">
                                S/ {{ number_format($data['kpis']['total_por_pagar'], 2) }}
                            </h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-money-bill-wave fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda Fila de KPIs -->
    <div class="row g-3 mb-4">
        <!-- Inventario -->
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Valor Inventario</p>
                            <h4 class="mb-0 fw-bold text-info">
                                S/ {{ number_format($data['kpis']['valor_inventario'], 2) }}
                            </h4>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-boxes fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bancos -->
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Saldo Bancos</p>
                            <h4 class="mb-0 fw-bold text-primary">
                                S/ {{ number_format($data['kpis']['saldo_bancos'], 2) }}
                            </h4>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-university fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Caja -->
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Caja del Día</p>
                            <h4 class="mb-0 fw-bold text-success">
                                S/ {{ number_format($data['kpis']['saldo_caja'], 2) }}
                            </h4>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-cash-register fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Utilidad -->
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Utilidad del Mes</p>
                            <h4 class="mb-0 fw-bold text-success">
                                S/ {{ number_format($data['kpis']['utilidad_mes'], 2) }}
                            </h4>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-chart-pie fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas Críticas -->
    @if(count($data['alertasCriticas']) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Alertas Críticas ({{ count($data['alertasCriticas']) }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($data['alertasCriticas'] as $alerta)
                        @if(isset($alerta['url']))
                            <a href="{{ $alerta['url'] }}" class="btn btn-sm btn-outline-{{ $alerta['tipo'] }}">Ver Detalles</a>
                        @endif

                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-{{ $alerta['icono'] }} fa-2x text-{{ $alerta['tipo'] }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 fw-bold">{{ $alerta['mensaje'] }}</p>
                                </div>
                                <div>
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Gráficos y Análisis -->
    <div class="row g-3 mb-4">
        <!-- Gráfico de Ventas Mensuales -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area me-2 text-primary"></i>
                        Ventas Últimos 12 Meses
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="ventasMesChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Aging de Cartera -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2 text-warning"></i>
                        Aging Cartera
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="agingChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Productos y Clientes -->
    <div class="row g-3 mb-4">
        <!-- Top Productos -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2 text-warning"></i>
                        Top 10 Productos del Mes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Producto</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['topProductos'] as $index => $producto)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $producto->Nombre }}</strong><br>
                                        <small class="text-muted">{{ $producto->laboratorio ?? 'N/A' }}</small>
                                    </td>
                                    <td class="text-end">{{ number_format($producto->cantidad_vendida, 0) }}</td>
                                    <td class="text-end fw-bold text-success">
                                        S/ {{ number_format($producto->total_vendido, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Clientes -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2 text-info"></i>
                        Top 10 Clientes del Mes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Cliente</th>
                                    <th class="text-center">Docs</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['topClientes'] as $index => $cliente)
                                <tr>
                                    <td>
                                        <span class="badge bg-info">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $cliente->Razon }}</strong><br>
                                        <small class="text-muted">Zona: {{ $cliente->Zona ?? 'N/A' }}</small>
                                    </td>
                                    <td class="text-center">{{ $cliente->total_documentos }}</td>
                                    <td class="text-end fw-bold text-success">
                                        S/ {{ number_format($cliente->total_comprado, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ventas del Día y Stock Crítico -->
    <div class="row g-3">
        <!-- Ventas del Día -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2 text-primary"></i>
                        Ventas del Día ({{ $data['ventasHoy']->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>N° Doc</th>
                                    <th>Cliente</th>
                                    <th>Hora</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['ventasHoy'] as $venta)
                                <tr>
                                    <td>
                                        <strong>{{ $venta->Tipo }}-{{ $venta->Numero }}</strong>
                                    </td>
                                    <td>{{ Str::limit($venta->cliente_nombre, 30) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($venta->Fecha)->format('H:i') }}</td>
                                    <td class="text-end fw-bold">S/ {{ number_format($venta->Total, 2) }}</td>
                                    <td class="text-center">
                                        @if($venta->estado_sunat == 'ACEPTADO')
                                            <span class="badge bg-success">Aceptado</span>
                                        @elseif($venta->estado_sunat == 'PENDIENTE')
                                            <span class="badge bg-warning">Pendiente</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $venta->estado_sunat ?? 'N/A' }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No hay ventas registradas hoy
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Crítico -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-circle me-2 text-danger"></i>
                        Stock Mínimo
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($data['stockCritico'] as $producto)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <strong class="d-block">{{ Str::limit($producto->Nombre, 30) }}</strong>
                                    <small class="text-muted">{{ $producto->laboratorio ?? 'N/A' }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger">{{ number_format($producto->stock_actual, 0) }}</span>
                                    <small class="text-muted d-block">Min: {{ $producto->stock_minimo }}</small>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted">
                            No hay productos con stock crítico
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Obtener datos para gráfico de ventas mensuales
    fetch("{{ route('admin.dashboard.graficos', ['tipo' => 'ventas_mes']) }}")
        .then(response => response.json())
        .then(data => {
            const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            const labels = data.map(item => meses[item.mes - 1] + ' ' + item.anio);
            const valores = data.map(item => parseFloat(item.total));

            new Chart(document.getElementById('ventasMesChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Ventas Mensuales',
                        data: valores,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'S/ ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });

    // Gráfico Aging Cartera
    fetch("{{ route('admin.dashboard.graficos', ['tipo' => 'aging_cartera']) }}")
        .then(response => response.json())
        .then(data => {
            new Chart(document.getElementById('agingChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Vigente', '1-30 días', '31-60 días', '61-90 días', '+90 días'],
                    datasets: [{
                        data: [
                            parseFloat(data.vigente || 0),
                            parseFloat(data.dias_1_30 || 0),
                            parseFloat(data.dias_31_60 || 0),
                            parseFloat(data.dias_61_90 || 0),
                            parseFloat(data.dias_mas_90 || 0)
                        ],
                        backgroundColor: [
                            '#28a745',
                            '#ffc107',
                            '#fd7e14',
                            '#dc3545',
                            '#6c757d'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
});
</script>
@endpush
@endsection
