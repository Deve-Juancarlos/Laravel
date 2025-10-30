@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-calendar-alt"></i> Resumen Mensual de Honorarios</h2>
                <a href="{{ route('libro-honorarios') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Selector de Año -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('honorarios.mensual') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Año</label>
                    <select name="anio" class="form-select">
                        @for($i = now()->year; $i >= now()->year - 5; $i--)
                            <option value="{{ $i }}" {{ $anio == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Consultar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen Anual -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h6 class="card-title">Total Anual</h6>
                    <h3 class="mb-0">S/ {{ number_format($tendencias['total_anual'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="card-title">Promedio Mensual</h6>
                    <h3 class="mb-0">S/ {{ number_format($tendencias['promedio_mensual'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h6 class="card-title">Tendencia</h6>
                    <h3 class="mb-0">
                        @if($tendencias['tendencia_general'] == 'CRECIENTE')
                            <i class="fas fa-arrow-up"></i> {{ $tendencias['variacion_promedio'] }}%
                        @elseif($tendencias['tendencia_general'] == 'DECRECIENTE')
                            <i class="fas fa-arrow-down"></i> {{ abs($tendencias['variacion_promedio']) }}%
                        @else
                            <i class="fas fa-minus"></i> Estable
                        @endif
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h6 class="card-title">Mes Mayor Actividad</h6>
                    <h4 class="mb-0">{{ ucfirst($tendencias['mes_mayor_actividad'] ?? 'N/A') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico Mensual -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-line"></i> Evolución Mensual {{ $anio }}</h5>
        </div>
        <div class="card-body">
            <canvas id="chartMensual" height="100"></canvas>
        </div>
    </div>

    <!-- Tabla Detalle Mensual -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table"></i> Detalle por Mes</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th class="text-center">Cantidad Documentos</th>
                            <th class="text-end">Total Facturado</th>
                            <th class="text-end">Promedio por Documento</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $acumulado = 0; @endphp
                        @foreach($resumenMensual as $mes => $datos)
                        @php $acumulado += $datos['total']; @endphp
                        <tr>
                            <td><strong>{{ ucfirst($datos['mes']) }}</strong></td>
                            <td class="text-center">{{ number_format($datos['cantidad']) }}</td>
                            <td class="text-end">S/ {{ number_format($datos['total'], 2) }}</td>
                            <td class="text-end">S/ {{ number_format($datos['promedio'], 2) }}</td>
                            <td class="text-center">
                                @if($datos['total'] > $tendencias['promedio_mensual'] * 1.2)
                                    <span class="badge bg-success"><i class="fas fa-arrow-up"></i> Alto</span>
                                @elseif($datos['total'] < $tendencias['promedio_mensual'] * 0.8)
                                    <span class="badge bg-warning"><i class="fas fa-arrow-down"></i> Bajo</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-minus"></i> Normal</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>TOTAL</th>
                            <th class="text-center">{{ array_sum(array_column($resumenMensual, 'cantidad')) }}</th>
                            <th class="text-end">S/ {{ number_format($tendencias['total_anual'], 2) }}</th>
                            <th class="text-end">S/ {{ number_format($tendencias['promedio_mensual'], 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Top 10 Prestadores -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-trophy"></i> Top 10 Prestadores del Año</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Prestador</th>
                            <th class="text-center">Documentos</th>
                            <th class="text-end">Total Facturado</th>
                            <th class="text-end">Promedio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topPrestadores as $index => $prestador)
                        <tr>
                            <td>
                                @if($index == 0)
                                    <i class="fas fa-trophy text-warning"></i>
                                @elseif($index == 1)
                                    <i class="fas fa-medal text-secondary"></i>
                                @elseif($index == 2)
                                    <i class="fas fa-award text-danger"></i>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td>{{ $prestador->CodClie }}</td>
                            <td>{{ $prestador->Razon }}</td>
                            <td class="text-center">{{ $prestador->cantidad }}</td>
                            <td class="text-end"><strong>S/ {{ number_format($prestador->total, 2) }}</strong></td>
                            <td class="text-end">S/ {{ number_format($prestador->total / $prestador->cantidad, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartMensual').getContext('2d');
    
    const meses = @json(array_column($resumenMensual, 'mes'));
    const totales = @json(array_column($resumenMensual, 'total'));
    const cantidades = @json(array_column($resumenMensual, 'cantidad'));
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: meses.map(m => m.substring(0, 3).toUpperCase()),
            datasets: [{
                label: 'Total Facturado (S/)',
                data: totales,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4,
                yAxisID: 'y'
            }, {
                label: 'Cantidad de Documentos',
                data: cantidades,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Honorarios Mensuales - Año {{ $anio }}'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Total (S/)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Cantidad'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection