@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-chart-pie"></i> Análisis por Categorías de Honorarios</h2>
                <a href="{{ route('libro-honorarios') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('honorarios.categorias') }}" class="row g-3">
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
        @foreach($totalesCategorias as $categoria => $totales)
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ str_replace('_', ' ', $categoria) }}</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <h3 class="text-primary mb-0">{{ $totales['cantidad_documentos'] }}</h3>
                            <small class="text-muted">Documentos</small>
                        </div>
                        <div class="col-6">
                            <h3 class="text-success mb-0">{{ $totales['prestadores_diferentes'] }}</h3>
                            <small class="text-muted">Prestadores</small>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Facturado:</span>
                        <strong class="text-success">S/ {{ number_format($totales['total_facturado'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Promedio por Doc:</span>
                        <strong>S/ {{ number_format($totales['promedio_documento'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Mayor Factura:</span>
                        <strong class="text-info">S/ {{ number_format($totales['mayor_factura'], 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Análisis de Crecimiento -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-chart-line"></i> Análisis de Crecimiento</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th class="text-end">Primera Mitad</th>
                            <th class="text-end">Segunda Mitad</th>
                            <th class="text-center">Crecimiento %</th>
                            <th class="text-center">Tendencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($crecimientoCategorias as $categoria => $datos)
                        <tr>
                            <td><strong>{{ str_replace('_', ' ', $categoria) }}</strong></td>
                            <td class="text-end">S/ {{ number_format($datos['primera_mitad'], 2) }}</td>
                            <td class="text-end">S/ {{ number_format($datos['segunda_mitad'], 2) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $datos['crecimiento_porcentual'] > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $datos['crecimiento_porcentual'] }}%
                                </span>
                            </td>
                            <td class="text-center">
                                @if($datos['tendencia'] == 'CRECIENTE')
                                    <span class="badge bg-success"><i class="fas fa-arrow-up"></i> {{ $datos['tendencia'] }}</span>
                                @elseif($datos['tendencia'] == 'DECRECIENTE')
                                    <span class="badge bg-danger"><i class="fas fa-arrow-down"></i> {{ $datos['tendencia'] }}</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-minus"></i> {{ $datos['tendencia'] }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detalle por Categoría -->
    @foreach($categorias as $categoria => $documentos)
    @if($documentos->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-folder-open"></i> {{ str_replace('_', ' ', $categoria) }}
                <span class="badge bg-primary">{{ $documentos->count() }} documentos</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Prestador</th>
                            <th>Documento</th>
                            <th>Servicio</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documentos->take(10) as $doc)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($doc->Fecha)->format('d/m/Y') }}</td>
                            <td>{{ $doc->Razon }}</td>
                            <td>{{ $doc->Numero }}</td>
                            <td>{{ $doc->producto_nombre ?? 'N/A' }}</td>
                            <td class="text-end">S/ {{ number_format($doc->Total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($documentos->count() > 10)
                <p class="text-muted text-center mb-0">
                    <small>Mostrando 10 de {{ $documentos->count() }} documentos</small>
                </p>
                @endif
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endsection