@extends('layouts.app')

@section('title', 'Resultados por Períodos')

@push('styles')
    <link href="{{ asset('css/contabilidad/estado-resultados/periodos.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-chart-bar me-2"></i>Resultados por Períodos</h1>
        <p class="text-muted">Análisis mensual de Ganancias y Pérdidas para el año {{ $anio }}</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.estado-resultados.index') }}">Estado de Resultados</a></li>
    <li class="breadcrumb-item active" aria-current="page">Por Períodos</li>
@endsection

@section('content')
<div class="resultadosperiodos-page">

    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link {{ request()->routeIs('contador.estado-resultados.index') ? 'active' : '' }}" href="{{ route('contador.estado-resultados.index') }}">
            <i class="fas fa-chart-line me-1"></i> Estado de Resultados
        </a>
        <a class="nav-link {{ request()->routeIs('contador.estado-resultados.periodos') ? 'active' : '' }}" href="{{ route('contador.estado-resultados.periodos') }}">
            <i class="fas fa-chart-bar me-1"></i> Resultados por Períodos
        </a>
        <a class="nav-link {{ request()->routeIs('contador.estado-resultados.comparativo') ? 'active' : '' }}" href="{{ route('contador.estado-resultados.comparativo') }}">
            <i class="fas fa-exchange-alt me-1"></i> Comparativo EERR
        </a>
        <a class="nav-link {{ request()->routeIs('contador.estado-resultados.balance-general') ? 'active' : '' }}" href="{{ route('contador.estado-resultados.balance-general') }}">
            <i class="fas fa-balance-scale-right me-1"></i> Balance General
        </a>
    </nav>
    

    <!-- Filtros -->
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.estado-resultados.periodos') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="anio">Seleccionar Año</label>
                        <select name="anio" id="anio" class="form-select">
                            @for($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" {{ $y == $anio ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Generar Reporte
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs Anuales -->
    <div class="row mb-4 stats-grid">
        <div class="col-md-3">
            <div class="stat-card shadow-sm">
                <p class="stat-label">Ventas Netas ({{ $anio }})</p>
                <div class="stat-value">S/ {{ number_format(collect($resultadosMensuales)->sum('ventas_netas'), 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm">
                <p class="stat-label">Utilidad Bruta ({{ $anio }})</p>
                <div class="stat-value">S/ {{ number_format(collect($resultadosMensuales)->sum('utilidad_bruta'), 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm">
                <p class="stat-label">Utilidad Operativa ({{ $anio }})</p>
                <div class="stat-value">S/ {{ number_format(collect($resultadosMensuales)->sum('utilidad_operativa'), 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm">
                <p class="stat-label">Promedio Ventas / Mes</p>
                <div class="stat-value">S/ {{ number_format($tendencias['promedio_mensual_ventas'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Gráfico Anual -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-chart-area me-2"></i>Evolución Anual {{ $anio }}</h6>
        </div>
        <div class="card-body">
            <div style="height: 350px;">
                <canvas id="periodosChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabla de Períodos -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-table me-2"></i>Detalle Mensual</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mes</th>
                            <th class="text-end">Ventas Netas</th>
                            <th class="text-end">Costo Ventas</th>
                            <th class="text-end">Utilidad Bruta</th>
                            <th class="text-end">Gastos Oper.</th>
                            <th class="text-end">Utilidad Operativa</th>
                            <th class="text-end">Margen Bruto %</th>
                            <th class="text-end">Margen Oper. %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultadosMensuales as $res)
                        <tr>
                            <td><strong>{{ $res['mes'] }}</strong></td>
                            <td class="text-end">S/ {{ number_format($res['ventas_netas'], 2) }}</td>
                            <td class="text-end text-danger">(S/ {{ number_format($res['costo_ventas'], 2) }})</td>
                            <td class="text-end fw-bold">S/ {{ number_format($res['utilidad_bruta'], 2) }}</td>
                            <td class="text-end text-danger">(S/ {{ number_format($res['gastos_operativos'], 2) }})</td>
                            <td class="text-end fw-bold">S/ {{ number_format($res['utilidad_operativa'], 2) }}</td>
                            <td class="text-end">{{ number_format($res['margen_bruto'], 1) }}%</td>
                            <td class="text-end">{{ number_format($res['margen_operativo'], 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-dark fw-bold">
                        <tr>
                            <td>TOTAL {{ $anio }}</td>
                            <td class="text-end">S/ {{ number_format(collect($resultadosMensuales)->sum('ventas_netas'), 2) }}</td>
                            <td class="text-end">(S/ {{ number_format(collect($resultadosMensuales)->sum('costo_ventas'), 2) }})</td>
                            <td class="text-end">S/ {{ number_format(collect($resultadosMensuales)->sum('utilidad_bruta'), 2) }}</td>
                            <td class="text-end">(S/ {{ number_format(collect($resultadosMensuales)->sum('gastos_operativos'), 2) }})</td>
                            <td class="text-end">S/ {{ number_format(collect($resultadosMensuales)->sum('utilidad_operativa'), 2) }}</td>
                            <td class="text-end" colspan="2">---</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('periodosChart').getContext('2d');
        
        const labels = @json(collect($resultadosMensuales)->pluck('mes'));
        const ventasData = @json(collect($resultadosMensuales)->pluck('ventas_netas'));
        const utilidadData = @json(collect($resultadosMensuales)->pluck('utilidad_operativa'));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Ventas Netas',
                        data: ventasData,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Utilidad Operativa',
                        data: utilidadData,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString('es-PE');
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
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
                }
            }
        });
    });
</script>
@endpush
