@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-chart-area"></i> Proyección de Gastos en Honorarios</h2>
                <a href="{{ route('libro-honorarios') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Selector de Año -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('honorarios.proyeccion') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Año</label>
                    <select name="anio" class="form-select">
                        @for($i = now()->year; $i >= now()->year - 3; $i--)
                            <option value="{{ $i }}" {{ $anio == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calculator"></i> Calcular Proyección
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen de Proyección -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h6 class="card-title">Promedio Mensual Histórico</h6>
                    <h3 class="mb-0">S/ {{ number_format($promedioMensual, 2) }}</h3>
                    <small>Base de cálculo</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="card-title">Proyección Anual</h6>
                    <h3 class="mb-0">S/ {{ number_format($proyeccionAnual, 2) }}</h3>
                    <small>Estimado para todo el año</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h6 class="card-title">Variación vs Año Anterior</h6>
                    <h3 class="mb-0">
                        @php
                            $totalActual = array_sum($gastosHistoricos);
                            $mesesTranscurridos = count(array_filter($gastosHistoricos, fn($v) => $v > 0));
                            $variacion = $mesesTranscurridos > 0 ? (($promedioMensual * 12 - $totalActual) / $totalActual * 100) : 0;
                        @endphp
                        {{ number_format($variacion, 1) }}%
                    </h3>
                    <small>
                        @if($variacion > 0)
                            <i class="fas fa-arrow-up"></i> Incremento
                        @elseif($variacion < 0)
                            <i class="fas fa-arrow-down"></i> Reducción
                        @else
                            <i class="fas fa-minus"></i> Sin cambio
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas de Presupuesto -->
    @if(count($alertasPresupuesto) > 0)
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Alertas de Presupuesto</h5>
        </div>
        <div class="card-body">
            @foreach($alertasPresupuesto as $alerta)
            <div class="alert alert-{{ $alerta['color'] }} d-flex align-items-center" role="alert">
                <i class="fas fa-{{ $alerta['tipo'] == 'ALTO' ? 'arrow-up' : ($alerta['tipo'] == 'BAJO' ? 'arrow-down' : 'info-circle') }} me-3"></i>
                <div class="flex-grow-1">
                    <strong>{{ $alerta['mes'] }}:</strong> {{ $alerta['mensaje'] }}
                    @if($alerta['gasto'])
                        <br>
                        <small>
                            Gasto: S/ {{ number_format($alerta['gasto'], 2) }} | 
                            Promedio: S/ {{ number_format($alerta['promedio'], 2) }} | 
                            Diferencia: S/ {{ number_format($alerta['diferencia'], 2) }}
                        </small>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Gráfico de Gastos Históricos y Proyección -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-line"></i> Gastos Históricos y Proyección</h5>
        </div>
        <div class="card-body">
            <canvas id="chartProyeccion" height="100"></canvas>
        </div>
    </div>

    <!-- Tabla de Gastos Mensuales -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table"></i> Detalle Mensual - Año {{ $anio }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th class="text-end">Gasto Real</th>
                            <th class="text-end">Promedio Histórico</th>
                            <th class="text-end">Diferencia</th>
                            <th class="text-center">% vs Promedio</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gastosHistoricos as $mes => $gasto)
                        @php
                            $diferencia = $gasto - $promedioMensual;
                            $porcentaje = $promedioMensual > 0 ? ($diferencia / $promedioMensual * 100) : 0;
                        @endphp
                        <tr>
                            <td><strong>{{ \Carbon\Carbon::create($anio, $mes, 1)->format('F') }}</strong></td>
                            <td class="text-end">
                                @if($gasto > 0)
                                    S/ {{ number_format($gasto, 2) }}
                                @else
                                    <span class="text-muted">Sin datos</span>
                                @endif
                            </td>
                            <td class="text-end">S/ {{ number_format($promedioMensual, 2) }}</td>
                            <td class="text-end">
                                <span class="{{ $diferencia > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $diferencia >= 0 ? '+' : '' }}S/ {{ number_format($diferencia, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ abs($porcentaje) > 20 ? 'bg-warning' : 'bg-secondary' }}">
                                    {{ $porcentaje >= 0 ? '+' : '' }}{{ number_format($porcentaje, 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                @if($gasto == 0)
                                    <span class="badge bg-secondary">Sin actividad</span>
                                @elseif($porcentaje > 20)
                                    <span class="badge bg-danger"><i class="fas fa-arrow-up"></i> Sobre presupuesto</span>
                                @elseif($porcentaje < -20)
                                    <span class="badge bg-success"><i class="fas fa-arrow-down"></i> Bajo presupuesto</span>
                                @else
                                    <span class="badge bg-primary">Normal</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>TOTAL / PROMEDIO</th>
                            <th class="text-end">S/ {{ number_format(array_sum($gastosHistoricos), 2) }}</th>
                            <th class="text-end">S/ {{ number_format($promedioMensual * 12, 2) }}</th>
                            <th class="text-end">
                                S/ {{ number_format(array_sum($gastosHistoricos) - ($promedioMensual * 12), 2) }}
                            </th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Proyección por Categoría -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-sitemap"></i> Proyección por Categoría de Servicio</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th class="text-end">Promedio Mensual</th>
                            <th class="text-end">Proyección Anual</th>
                            <th class="text-center">Docs/Mes Estimado</th>
                            <th class="text-center">% del Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalProyeccion = array_sum(array_column($proyeccionesCategoria, 'proyeccion_anual')); @endphp
                        @foreach($proyeccionesCategoria as $categoria => $proyeccion)
                        <tr>
                            <td><strong>{{ str_replace('_', ' ', $categoria) }}</strong></td>
                            <td class="text-end">S/ {{ number_format($proyeccion['promedio_mensual'], 2) }}</td>
                            <td class="text-end"><strong>S/ {{ number_format($proyeccion['proyeccion_anual'], 2) }}</strong></td>
                            <td class="text-center">{{ $proyeccion['documentos_mes'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">
                                    {{ $totalProyeccion > 0 ? number_format(($proyeccion['proyeccion_anual'] / $totalProyeccion * 100), 1) : 0 }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>TOTAL</th>
                            <th class="text-end">
                                S/ {{ number_format(array_sum(array_column($proyeccionesCategoria, 'promedio_mensual')), 2) }}
                            </th>
                            <th class="text-end">
                                <strong>S/ {{ number_format($totalProyeccion, 2) }}</strong>
                            </th>
                            <th class="text-center">
                                {{ array_sum(array_column($proyeccionesCategoria, 'documentos_mes')) }}
                            </th>
                            <th class="text-center">100%</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartProyeccion').getContext('2d');
    
    const gastosReales = @json(array_values($gastosHistoricos));
    const promedioMensual = {{ $promedioMensual }};
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    
    // Crear línea de promedio
    const lineaPromedio = new Array(12).fill(promedioMensual);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Gasto Real',
                data: gastosReales,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Promedio Histórico',
                data: lineaPromedio,
                type: 'line',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                borderDash: [5, 5],
                fill: false,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Gastos Mensuales vs Promedio - Año {{ $anio }}'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += 'S/ ' + context.parsed.y.toLocaleString('es-PE', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Monto (S/)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + value.toLocaleString('es-PE');
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection