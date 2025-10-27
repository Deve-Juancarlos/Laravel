@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('contador.estado-resultados.index') }}">Estado de Resultados</a></li>
            <li class="breadcrumb-item active"><i class="fas fa-calendar-alt"></i> Análisis por Períodos</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary">
                <i class="fas fa-calendar-alt me-2"></i>
                Estado de Resultados por Períodos
            </h2>
            <p class="text-muted mb-0">Análisis mensual y tendencias - Año {{ $anio }}</p>
        </div>
        <div class="text-end">
            <form method="GET" class="d-inline">
                <select name="anio" class="form-select d-inline w-auto" onchange="this.form.submit()">
                    @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                        <option value="{{ $year }}" {{ $year == $anio ? 'selected' : '' }}>{{ $year }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    <!-- Resumen de Tendencias -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Crecimiento Ventas</h5>
                            <h3 class="text-white">{{ number_format($tendencias['crecimiento_ventas'], 1) }}%</h3>
                            <small>En el período</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Promedio Mensual</h5>
                            <h3 class="text-white">{{ number_format($tendencias['promedio_mensual_ventas'], 0) }}</h3>
                            <small>S/. ventas/mes</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-calculator fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Mejor Mes Ventas</h5>
                            <h3 class="text-white">{{ $resultadosMensuales[$tendencias['mes_mayor_venta']]['mes'] ?? 'N/A' }}</h3>
                            <small>Mes con mayores ventas</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-trophy fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Mejor Mes Utilidad</h5>
                            <h3 class="text-white">{{ $resultadosMensuales[$tendencias['mes_mayor_utilidad']]['mes'] ?? 'N/A' }}</h3>
                            <small>Mes con mayor utilidad</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-star fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Tendencias -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
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
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
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
                        <tr>
                            <td><strong>{{ $data['mes'] }}</strong></td>
                            <td class="text-end">{{ number_format($data['ventas_netas'], 2) }}</td>
                            <td class="text-end">{{ number_format($data['costo_ventas'], 2) }}</td>
                            <td class="text-end {{ $data['utilidad_bruta'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($data['utilidad_bruta'], 2) }}
                            </td>
                            <td class="text-end">{{ number_format($data['gastos_operativos'], 2) }}</td>
                            <td class="text-end {{ $data['utilidad_operativa'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($data['utilidad_operativa'], 2) }}
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
                            <th class="text-end">{{ number_format(collect($resultadosMensuales)->sum('ventas_netas'), 2) }}</th>
                            <th class="text-end">{{ number_format(collect($resultadosMensuales)->sum('costo_ventas'), 2) }}</th>
                            <th class="text-end">{{ number_format(collect($resultadosMensuales)->sum('utilidad_bruta'), 2) }}</th>
                            <th class="text-end">{{ number_format(collect($resultadosMensuales)->sum('gastos_operativos'), 2) }}</th>
                            <th class="text-end">{{ number_format(collect($resultadosMensuales)->sum('utilidad_operativa'), 2) }}</th>
                            <th class="text-end">
                                {{ number_format((collect($resultadosMensuales)->sum('utilidad_bruta') / collect($resultadosMensuales)->sum('ventas_netas')) * 100, 1) }}%
                            </th>
                            <th class="text-end">
                                {{ number_format((collect($resultadosMensuales)->sum('utilidad_operativa') / collect($resultadosMensuales)->sum('ventas_netas')) * 100, 1) }}%
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Análisis de Variaciones Mensuales -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar me-2"></i>
                Análisis de Variaciones Mensuales
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @php
                    $meses = array_keys($resultadosMensuales);
                    $totalMeses = count($meses);
                @endphp
                
                @for($i = 1; $i < $totalMeses; $i++)
                @php
                    $mesActual = $resultadosMensuales[$meses[$i]];
                    $mesAnterior = $resultadosMensuales[$meses[$i-1]];
                    
                    $variacionVentas = $mesAnterior['ventas_netas'] > 0 ? 
                        (($mesActual['ventas_netas'] - $mesAnterior['ventas_netas']) / $mesAnterior['ventas_netas']) * 100 : 0;
                        
                    $variacionUtilidad = $mesAnterior['utilidad_operativa'] != 0 ? 
                        (($mesActual['utilidad_operativa'] - $mesAnterior['utilidad_operativa']) / abs($mesAnterior['utilidad_operativa'])) * 100 : 0;
                @endphp
                
                <div class="col-lg-6 col-xl-4 mb-3">
                    <div class="card border">
                        <div class="card-header">
                            <h6 class="mb-0">{{ $mesActual['mes'] }} vs {{ $resultadosMensuales[$meses[$i-1]]['mes'] }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted">Ventas:</small>
                                <div class="d-flex justify-content-between">
                                    <span>{{ number_format($mesAnterior['ventas_netas'], 0) }}</span>
                                    <span>{{ number_format($mesActual['ventas_netas'], 0) }}</span>
                                </div>
                                <div class="progress mt-1" style="height: 20px;">
                                    <div class="progress-bar {{ $variacionVentas >= 0 ? 'bg-success' : 'bg-danger' }}" 
                                         style="width: {{ min(100, abs($variacionVentas)) }}%">
                                        <small>{{ $variacionVentas >= 0 ? '+' : '' }}{{ number_format($variacionVentas, 1) }}%</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <small class="text-muted">Utilidad:</small>
                                <div class="d-flex justify-content-between">
                                    <span>{{ number_format($mesAnterior['utilidad_operativa'], 0) }}</span>
                                    <span>{{ number_format($mesActual['utilidad_operativa'], 0) }}</span>
                                </div>
                                <div class="progress mt-1" style="height: 20px;">
                                    <div class="progress-bar {{ $variacionUtilidad >= 0 ? 'bg-success' : 'bg-danger' }}" 
                                         style="width: {{ min(100, abs($variacionUtilidad)) }}%">
                                        <small>{{ $variacionUtilidad >= 0 ? '+' : '' }}{{ number_format($variacionUtilidad, 1) }}%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Top y Bottom Performers -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>
                        Top 3 Meses - Ventas
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $topVentas = collect($resultadosMensuales)->sortByDesc('ventas_netas')->take(3);
                        $rank = 1;
                    @endphp
                    @foreach($topVentas as $mes => $data)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded {{ $rank == 1 ? 'bg-warning text-dark' : 'bg-light' }}">
                        <div>
                            <strong>#{{ $rank }}</strong> - {{ $data['mes'] }}
                        </div>
                        <div class="text-end">
                            <div><strong>S/. {{ number_format($data['ventas_netas'], 0) }}</strong></div>
                            <small>Utilidad: {{ number_format($data['utilidad_operativa'], 0) }}</small>
                        </div>
                    </div>
                    @php $rank++; @endphp
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Top 3 Meses - Utilidad
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $topUtilidad = collect($resultadosMensuales)->sortByDesc('utilidad_operativa')->take(3);
                        $rank = 1;
                    @endphp
                    @foreach($topUtilidad as $mes => $data)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded {{ $rank == 1 ? 'bg-warning text-dark' : 'bg-light' }}">
                        <div>
                            <strong>#{{ $rank }}</strong> - {{ $data['mes'] }}
                        </div>
                        <div class="text-end">
                            <div><strong>S/. {{ number_format($data['utilidad_operativa'], 0) }}</strong></div>
                            <small>Ventas: {{ number_format($data['ventas_netas'], 0) }}</small>
                        </div>
                    </div>
                    @php $rank++; @endphp
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Navegación -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('contador.estado-resultados.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Volver al Estado Principal
                    </a>
                </div>
                <div>
                    <a href="{{ route('contador.estado-resultados.comparativo') }}" class="btn btn-info me-2">
                        <i class="fas fa-comparison"></i> Ver Comparativo
                    </a>
                    <a href="{{ route('contador.estado-resultados.farmaceutico') }}" class="btn btn-success">
                        <i class="fas fa-pills"></i> Análisis Farmacéutico
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos para el gráfico de tendencias
    const meses = {!! json_encode(collect($resultadosMensuales)->pluck('mes')->toArray()) !!};
    const ventas = {!! json_encode(collect($resultadosMensuales)->pluck('ventas_netas')->toArray()) !!};
    const utilidad = {!! json_encode(collect($resultadosMensuales)->pluck('utilidad_operativa')->toArray()) !!};

    // Gráfico de líneas para tendencias
    const ctx = document.getElementById('chartTendencias');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Ventas Netas',
                    data: ventas,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Utilidad Operativa',
                    data: utilidad,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Evolución Mensual de Ventas y Utilidad - {{ $anio }}'
                    },
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/. ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection