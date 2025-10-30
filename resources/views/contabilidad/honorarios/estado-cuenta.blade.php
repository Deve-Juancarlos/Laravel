@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-user-tie"></i> Estado de Cuenta - Prestador</h2>
                <a href="{{ route('libro-honorarios') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Información del Prestador -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-id-card"></i> Información del Prestador</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Código:</strong></td>
                            <td>{{ $prestador->Codclie }}</td>
                        </tr>
                        <tr>
                            <td><strong>Razón Social:</strong></td>
                            <td>{{ $prestador->Razon }}</td>
                        </tr>
                        <tr>
                            <td><strong>Documento:</strong></td>
                            <td>{{ $prestador->Documento }}</td>
                        </tr>
                        <tr>
                            <td><strong>Dirección:</strong></td>
                            <td>{{ $prestador->Direccion }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Teléfono:</strong></td>
                            <td>{{ $prestador->Telefono1 }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $prestador->Email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Zona:</strong></td>
                            <td>{{ $prestador->zona_nombre ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('honorarios.estado-cuenta', $prestador->Codclie) }}" class="row g-3">
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

    <!-- Resumen de Totales -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h6 class="card-title text-white">Total Facturado</h6>
                    <h2 class="mb-0">S/ {{ number_format($totales['total_honorarios'], 2) }}</h2>
                    <small>En el período</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h6 class="card-title text-white">Total Documentos</h6>
                    <h2 class="mb-0">{{ $totales['total_documentos'] }}</h2>
                    <small>Emitidos</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                    <h6 class="card-title text-white">Promedio</h6>
                    <h2 class="mb-0">S/ {{ number_format($totales['promedio_honorario'], 2) }}</h2>
                    <small>Por documento</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Clasificación de Documentos -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-sitemap"></i> Clasificación de Documentos</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($clasificacionDocumentos as $tipo => $documentos)
                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h6 class="text-muted">{{ str_replace('_', ' ', $tipo) }}</h6>
                            <h2 class="text-primary">{{ count($documentos) }}</h2>
                            <p class="mb-0">
                                <strong>S/ {{ number_format(collect($documentos)->sum('Total'), 2) }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Resumen Mensual -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Resumen Mensual</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Promedio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resumenMensual as $mes => $datos)
                        <tr>
                            <td><strong>{{ $mes }}</strong></td>
                            <td class="text-center">{{ $datos['cantidad'] }}</td>
                            <td class="text-end">S/ {{ number_format($datos['total'], 2) }}</td>
                            <td class="text-end">S/ {{ number_format($datos['promedio'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detalle de Documentos -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Detalle de Documentos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Número</th>
                            <th>Producto/Servicio</th>
                            <th>Laboratorio</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documentosHonorarios as $documento)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($documento->Fecha)->format('d/m/Y') }}</td>
                            <td>
                                <strong>{{ $documento->Numero }}</strong>
                                @if(str_starts_with($documento->Numero, 'HON'))
                                    <span class="badge bg-primary">HON</span>
                                @elseif(str_starts_with($documento->Numero, 'CBO'))
                                    <span class="badge bg-info">CBO</span>
                                @elseif(str_starts_with($documento->Numero, 'REC'))
                                    <span class="badge bg-success">REC</span>
                                @endif
                            </td>
                            <td>{{ $documento->producto_nombre ?? 'N/A' }}</td>
                            <td>{{ $documento->laboratorio ?? 'N/A' }}</td>
                            <td class="text-center">{{ $documento->Cantidad ?? 0 }}</td>
                            <td class="text-end">S/ {{ number_format($documento->Precio ?? 0, 2) }}</td>
                            <td class="text-end">S/ {{ number_format($documento->Subtotal ?? 0, 2) }}</td>
                            <td class="text-end"><strong>S/ {{ number_format($documento->Total, 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No hay documentos en el período seleccionado
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="7" class="text-end">TOTAL:</th>
                            <th class="text-end">S/ {{ number_format($totales['total_honorarios'], 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection