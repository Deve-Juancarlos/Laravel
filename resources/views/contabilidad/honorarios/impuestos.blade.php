@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-receipt"></i> Análisis de Retenciones por Honorarios</h2>
                <a href="{{ route('libro-honorarios') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('honorarios.impuestos') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h6 class="card-title">Total Honorarios</h6>
                    <h3 class="mb-0">S/ {{ number_format($totalHonorarios, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h6 class="card-title">Total Retenciones</h6>
                    <h3 class="mb-0">S/ {{ number_format($totalRetenciones, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="card-title">Neto a Pagar</h6>
                    <h3 class="mb-0">S/ {{ number_format($netoPagar, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h6 class="card-title">% Retención</h6>
                    <h3 class="mb-0">{{ $totalHonorarios > 0 ? number_format(($totalRetenciones / $totalHonorarios) * 100, 2) : 0 }}%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de Retenciones -->
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información sobre Retenciones</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Escala de Retenciones:</h6>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <strong>Hasta S/ 1,500:</strong> 8% de retención
                            <span class="badge bg-info float-end">
                                S/ {{ number_format($resumenRangos['hasta_1500'], 2) }}
                            </span>
                        </li>
                        <li class="list-group-item">
                            <strong>Más de S/ 1,500:</strong> S/ 120 + 10% del exceso
                            <span class="badge bg-warning float-end">
                                S/ {{ number_format($resumenRangos['mayor_1500'], 2) }}
                            </span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Distribución por Rangos:</h6>
                    <canvas id="chartRangos" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de Honorarios con Retenciones -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table"></i> Detalle de Retenciones</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaRetenciones">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Documento</th>
                            <th>Fecha</th>
                            <th class="text-end">Total Honorario</th>
                            <th class="text-end">IGV</th>
                            <th class="text-end">Retención (8-10%)</th>
                            <th class="text-end">Neto a Pagar</th>
                            <th class="text-center">Rango</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($honorariosConRetencion as $index => $honorario)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $honorario->Numero }}</td>
                            <td>{{ \Carbon\Carbon::parse($honorario->Fecha)->format('d/m/Y') }}</td>
                            <td class="text-end">S/ {{ number_format($honorario->Total, 2) }}</td>
                            <td class="text-end">S/ {{ number_format($honorario->Igv, 2) }}</td>
                            <td class="text-end text-danger">
                                <strong>S/ {{ number_format($honorario->retencion_estimada, 2) }}</strong>
                            </td>
                            <td class="text-end text-success">
                                <strong>S/ {{ number_format($honorario->Total - $honorario->retencion_estimada, 2) }}</strong>
                            </td>
                            <td class="text-center">
                                @if($honorario->Total <= 1500)
                                    <span class="badge bg-info">8%</span>
                                @else
                                    <span class="badge bg-warning">10%</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">TOTALES:</th>
                            <th class="text-end">S/ {{ number_format($totalHonorarios, 2) }}</th>
                            <th class="text-end">S/ {{ number_format($honorariosConRetencion->sum('Igv'), 2) }}</th>
                            <th class="text-end text-danger">S/ {{ number_format($totalRetenciones, 2) }}</th>
                            <th class="text-end text-success">S/ {{ number_format($netoPagar, 2) }}</th>
                            <th></th>
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
    // Gráfico de Rangos
    const ctx = document.getElementById('chartRangos').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Hasta S/ 1,500 (8%)', 'Mayor S/ 1,500 (10%)'],
            datasets: [{
                data: [{{ $resumenRangos['hasta_1500'] }}, {{ $resumenRangos['mayor_1500'] }}],
                backgroundColor: ['#17a2b8', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush
@endsection