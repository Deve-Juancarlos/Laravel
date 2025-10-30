@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-pills"></i> Servicios Profesionales Farmacéuticos</h2>
                <a href="{{ route('libro-honorarios') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('honorarios.farmaceuticos') }}" class="row g-3">
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

    <!-- Resumen por Categorías -->
    <div class="row mb-4">
        @foreach($totalesServicios as $categoria => $totales)
        <div class="col-md-4 mb-3">
            <div class="card h-100 
                @if($categoria == 'ASESOR_QUIMICO_FARMACEUTICO') border-primary
                @elseif($categoria == 'SERVICIOS_REGENCIA') border-success
                @elseif($categoria == 'CAPACITACION_ENTRENAMIENTO') border-info
                @elseif($categoria == 'ASESORIA_REGULATORIA') border-warning
                @else border-secondary
                @endif">
                <div class="card-header 
                    @if($categoria == 'ASESOR_QUIMICO_FARMACEUTICO') bg-primary text-white
                    @elseif($categoria == 'SERVICIOS_REGENCIA') bg-success text-white
                    @elseif($categoria == 'CAPACITACION_ENTRENAMIENTO') bg-info text-white
                    @elseif($categoria == 'ASESORIA_REGULATORIA') bg-warning
                    @else bg-secondary text-white
                    @endif">
                    <h6 class="mb-0">{{ str_replace('_', ' ', $categoria) }}</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="text-primary mb-0">{{ $totales['cantidad'] }}</h4>
                            <small class="text-muted">Documentos</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-success mb-0">{{ $totales['prestadores'] }}</h4>
                            <small class="text-muted">Prestadores</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-info mb-0">S/ {{ number_format($totales['total'], 0) }}</h4>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h5 class="mb-0 text-success">S/ {{ number_format($totales['total'], 2) }}</h5>
                        <small class="text-muted">Total Facturado</small>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Detalle por Categoría -->
    @foreach($clasificacionServicios as $categoria => $servicios)
    @if(count($servicios) > 0)
    <div class="card mb-4">
        <div class="card-header 
            @if($categoria == 'ASESOR_QUIMICO_FARMACEUTICO') bg-primary text-white
            @elseif($categoria == 'SERVICIOS_REGENCIA') bg-success text-white
            @elseif($categoria == 'CAPACITACION_ENTRENAMIENTO') bg-info text-white
            @elseif($categoria == 'ASESORIA_REGULATORIA') bg-warning
            @else bg-secondary text-white
            @endif">
            <h5 class="mb-0">
                <i class="fas fa-folder-open"></i> {{ str_replace('_', ' ', $categoria) }}
                <span class="badge bg-light text-dark float-end">{{ count($servicios) }} servicios</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Código</th>
                            <th>Profesional</th>
                            <th>Documento</th>
                            <th>Servicio</th>
                            <th>Laboratorio</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($servicios as $servicio)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($servicio->Fecha)->format('d/m/Y') }}</td>
                            <td><strong>{{ $servicio->CodClie }}</strong></td>
                            <td>{{ $servicio->Razon }}</td>
                            <td>{{ $servicio->Numero }}</td>
                            <td>{{ $servicio->producto_nombre ?? 'N/A' }}</td>
                            <td>{{ $servicio->laboratorio ?? 'N/A' }}</td>
                            <td class="text-end"><strong>S/ {{ number_format($servicio->Total, 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="6" class="text-end">SUBTOTAL {{ str_replace('_', ' ', $categoria) }}:</th>
                            <th class="text-end">S/ {{ number_format(collect($servicios)->sum('Total'), 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif
    @endforeach

    <!-- Resumen Total -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-calculator"></i> Resumen Total de Servicios Farmacéuticos</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h6 class="text-muted">Total Servicios</h6>
                        <h3 class="text-primary">{{ array_sum(array_column($totalesServicios, 'cantidad')) }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h6 class="text-muted">Profesionales Únicos</h6>
                        <h3 class="text-success">{{ array_sum(array_column($totalesServicios, 'prestadores')) }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h6 class="text-muted">Total Facturado</h6>
                        <h3 class="text-info">S/ {{ number_format(array_sum(array_column($totalesServicios, 'total')), 2) }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h6 class="text-muted">Promedio por Servicio</h6>
                        <h3 class="text-warning">
                            S/ {{ number_format(
                                array_sum(array_column($totalesServicios, 'total')) / 
                                (array_sum(array_column($totalesServicios, 'cantidad')) ?: 1), 
                                2
                            ) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection