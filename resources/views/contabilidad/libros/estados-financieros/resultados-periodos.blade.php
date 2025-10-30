@extends('layouts.app')
@php
   use Carbon\Carbon; 
@endphp

@push('styles')
<style>
    .estado-resultados-view {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 2rem 0;
    }
    
    .breadcrumb {
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }
    
    .breadcrumb-item a {
        color: #2563eb;
        text-decoration: none;
    }
    
    .breadcrumb-item a:hover {
        color: #1d4ed8;
    }
    
    .breadcrumb-item.active {
        color: #64748b;
    }
    
    .page-header {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border-left: 4px solid #2563eb;
    }
    
    .page-header h2 {
        font-size: 1.875rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    
    .page-header p {
        color: #64748b;
        font-size: 0.95rem;
        margin: 0;
    }
    
    .stat-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        height: 100%;
        transition: all 0.2s ease;
    }
    
    .stat-card:hover {
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .stat-card.bg-info {
        background: #0ea5e9 !important;
        border-color: #0ea5e9;
    }
    
    .stat-card.bg-success {
        background: #10b981 !important;
        border-color: #10b981;
    }
    
    .stat-card.bg-warning {
        background: #f59e0b !important;
        border-color: #f59e0b;
    }
    
    .stat-card.bg-primary {
        background: #2563eb !important;
        border-color: #2563eb;
    }
    
    .stat-card h5 {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        opacity: 0.95;
    }
    
    .stat-card h3 {
        font-size: 1.875rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .stat-card small {
        font-size: 0.8125rem;
        opacity: 0.9;
    }
    
    .card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .card-header {
        background: #1e293b;
        color: white;
        padding: 1rem 1.5rem;
        border: none;
        border-radius: 8px 8px 0 0;
    }
    
    .card-header.bg-primary {
        background: #2563eb !important;
    }
    
    .card-header.bg-secondary {
        background: #64748b !important;
    }
    
    .card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .table {
        margin: 0;
    }
    
    .table thead {
        background: #f8fafc;
    }
    
    .table thead th {
        color: #475569;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 0.875rem 1rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .table-dark {
        background: #1e293b !important;
    }
    
    .table-dark th {
        color: white !important;
        background: #1e293b !important;
    }
    
    .table tbody tr {
        border-bottom: 1px solid #f1f5f9;
    }
    
    .table tbody tr:hover {
        background: #f8fafc;
    }
    
    .table tbody td {
        padding: 0.875rem 1rem;
        vertical-align: middle;
        color: #334155;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background: #fafafa;
    }
    
    .table tfoot {
        background: #f8fafc;
        border-top: 2px solid #e2e8f0;
    }
    
    .table-info {
        background: #eff6ff !important;
    }
    
    .table tfoot th {
        padding: 1rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .form-select {
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        padding: 0.625rem 0.875rem;
        font-size: 0.9375rem;
    }
    
    .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    
    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 4px;
        font-weight: 500;
        font-size: 0.8125rem;
    }
    
    .text-success {
        color: #10b981 !important;
    }
    
    .text-danger {
        color: #ef4444 !important;
    }
    
    .opacity-75 {
        opacity: 0.75;
    }
</style>
@endpush

@section('content')
<div class="estado-resultados-view">
    <div class="container-fluid">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('contador.estado-resultados.index') }}">Estado de Resultados</a></li>
                <li class="breadcrumb-item active"><i class="fas fa-calendar-alt"></i> Análisis por Períodos</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>
                        <i class="fas fa-calendar-alt me-2"></i>
                        Estado de Resultados por Períodos
                    </h2>
                    <p>Análisis mensual y tendencias - Año {{ $anio }}</p>
                </div>
                <div class="text-end">
                    <form method="GET" class="d-inline">
                        <label class="form-label mb-1 d-block text-muted small">Seleccionar Año</label>
                        <select name="anio" class="form-select d-inline w-auto" onchange="this.form.submit()">
                            @for($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" {{ $anio == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Resumen de Tendencias -->
        @php
            $mesesEspanol = [
                'January' => 'Enero',
                'February' => 'Febrero',
                'March' => 'Marzo',
                'April' => 'Abril',
                'May' => 'Mayo',
                'June' => 'Junio',
                'July' => 'Julio',
                'August' => 'Agosto',
                'September' => 'Septiembre',
                'October' => 'Octubre',
                'November' => 'Noviembre',
                'December' => 'Diciembre'
            ];
        @endphp
        <div class="row mb-4 g-3">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-info text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5>Crecimiento Ventas</h5>
                            <h3>{{ number_format($tendencias['crecimiento_ventas'] ?? 0, 1) }}%</h3>
                            <small>En el período</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-success text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5>Promedio Mensual</h5>
                            <h3>{{ number_format($tendencias['promedio_mensual_ventas'] ?? 0, 0) }}</h3>
                            <small>S/ ventas/mes</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-calculator fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-warning text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5>Mejor Mes Ventas</h5>
                            <h3>
                                @php
                                    $mesMayorVenta = isset($tendencias['mes_mayor_venta'], $resultadosMensuales[$tendencias['mes_mayor_venta']]) 
                                        ? $resultadosMensuales[$tendencias['mes_mayor_venta']]['mes'] 
                                        : 'N/A';
                                    $mesMayorVentaEs = $mesesEspanol[$mesMayorVenta] ?? $mesMayorVenta;
                                @endphp
                                {{ $mesMayorVentaEs }}
                            </h3>
                            <small>Mes con mayores ventas</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-trophy fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5>Mejor Mes Utilidad</h5>
                            <h3>
                                @php
                                    $mesMayorUtilidad = isset($tendencias['mes_mayor_utilidad'], $resultadosMensuales[$tendencias['mes_mayor_utilidad']]) 
                                        ? $resultadosMensuales[$tendencias['mes_mayor_utilidad']]['mes'] 
                                        : 'N/A';
                                    $mesMayorUtilidadEs = $mesesEspanol[$mesMayorUtilidad] ?? $mesMayorUtilidad;
                                @endphp
                                {{ $mesMayorUtilidadEs }}
                            </h3>
                            <small>Mes con mayor utilidad</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-star fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Tendencias -->
        <div class="card mb-4">
            <div class="card-header bg-primary">
                <h5>
                    <i class="fas fa-chart-line me-2"></i>
                    Tendencias Mensuales {{ $anio }}
                </h5>
            </div>
            <div class="card-body">
                <canvas id="chartTendencias" height="80"></canvas>
            </div>
        </div>

        <!-- Tabla de Resultados Mensuales -->
        <div class="card mb-4">
            <div class="card-header bg-secondary">
                <h5>
                    <i class="fas fa-table me-2"></i>
                    Resultados por Mes - {{ $anio }}
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Mes</th>
                                <th class="text-end">Ventas Netas</th>
                                <th class="text-end">Costo Ventas</th>
                                <th class="text-end">Utilidad Bruta</th>
                                <th class="text-end">Gastos Operativos</th>
                                <th class="text-end">Utilidad Operativa</th>
                                <th class="text-end">Margen Bruto</th>
                                <th class="text-end">Margen Operativo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resultadosMensuales as $mes => $data)
                            @php
                                $mesesEspanol = [
                                    'January' => 'Enero',
                                    'February' => 'Febrero',
                                    'March' => 'Marzo',
                                    'April' => 'Abril',
                                    'May' => 'Mayo',
                                    'June' => 'Junio',
                                    'July' => 'Julio',
                                    'August' => 'Agosto',
                                    'September' => 'Septiembre',
                                    'October' => 'Octubre',
                                    'November' => 'Noviembre',
                                    'December' => 'Diciembre'
                                ];
                                $mesTraducido = $mesesEspanol[$data['mes']] ?? $data['mes'];
                            @endphp
                            <tr>
                                <td><strong>{{ $mesTraducido }}</strong></td>
                                <td class="text-end">S/ {{ number_format($data['ventas_netas'], 2) }}</td>
                                <td class="text-end">S/ {{ number_format($data['costo_ventas'], 2) }}</td>
                                <td class="text-end {{ $data['utilidad_bruta'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    S/ {{ number_format($data['utilidad_bruta'], 2) }}
                                </td>
                                <td class="text-end">S/ {{ number_format($data['gastos_operativos'], 2) }}</td>
                                <td class="text-end {{ $data['utilidad_operativa'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    S/ {{ number_format($data['utilidad_operativa'], 2) }}
                                </td>
                                <td class="text-end">
                                    <span class="badge {{ $data['margen_bruto'] >= 30 ? 'bg-success' : ($data['margen_bruto'] >= 20 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ number_format($data['margen_bruto'], 1) }}%
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="badge {{ $data['margen_operativo'] >= 15 ? 'bg-success' : ($data['margen_operativo'] >= 10 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ number_format($data['margen_operativo'], 1) }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-info">
                            <tr>
                                <th>TOTAL AÑO</th>
                                @php
                                    $totalVentas = collect($resultadosMensuales)->sum('ventas_netas');
                                    $totalCosto = collect($resultadosMensuales)->sum('costo_ventas');
                                    $totalBruta = collect($resultadosMensuales)->sum('utilidad_bruta');
                                    $totalGastos = collect($resultadosMensuales)->sum('gastos_operativos');
                                    $totalOperativa = collect($resultadosMensuales)->sum('utilidad_operativa');
                                    $margenBruto = $totalVentas > 0 ? ($totalBruta / $totalVentas) * 100 : 0;
                                    $margenOperativo = $totalVentas > 0 ? ($totalOperativa / $totalVentas) * 100 : 0;
                                @endphp
                                <th class="text-end">S/ {{ number_format($totalVentas, 2) }}</th>
                                <th class="text-end">S/ {{ number_format($totalCosto, 2) }}</th>
                                <th class="text-end">S/ {{ number_format($totalBruta, 2) }}</th>
                                <th class="text-end">S/ {{ number_format($totalGastos, 2) }}</th>
                                <th class="text-end">S/ {{ number_format($totalOperativa, 2) }}</th>
                                <th class="text-end">{{ number_format($margenBruto, 1) }}%</th>
                                <th class="text-end">{{ number_format($margenOperativo, 1) }}%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Traducir meses al español
    const mesesOriginales = {!! json_encode(collect($resultadosMensuales)->pluck('mes')->toArray()) !!};
    const traduccionMeses = {
        'January': 'Enero',
        'February': 'Febrero',
        'March': 'Marzo',
        'April': 'Abril',
        'May': 'Mayo',
        'June': 'Junio',
        'July': 'Julio',
        'August': 'Agosto',
        'September': 'Septiembre',
        'October': 'Octubre',
        'November': 'Noviembre',
        'December': 'Diciembre'
    };
    
    const meses = mesesOriginales.map(mes => traduccionMeses[mes] || mes);
    const ventas = {!! json_encode(collect($resultadosMensuales)->pluck('ventas_netas')->toArray()) !!};
    const utilidad = {!! json_encode(collect($resultadosMensuales)->pluck('utilidad_operativa')->toArray()) !!};

    const ctx = document.getElementById('chartTendencias');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Ventas Netas',
                    data: ventas,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Utilidad Operativa',
                    data: utilidad,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: { 
                    mode: 'index', 
                    intersect: false 
                },
                plugins: {
                    title: { 
                        display: true, 
                        text: 'Evolución Mensual de Ventas y Utilidad - {{ $anio }}',
                        font: {
                            size: 16,
                            weight: '600'
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    },
                    legend: { 
                        position: 'top',
                        labels: {
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': S/ ' + context.parsed.y.toLocaleString('es-PE', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: { 
                            callback: function(value) { 
                                return 'S/ ' + value.toLocaleString('es-PE'); 
                            }
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
@endsection