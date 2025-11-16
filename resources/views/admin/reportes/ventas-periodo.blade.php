@extends('layouts.admin')

@section('title', 'Reporte de Ventas por Período')

@section('content')
<div class="container-fluid py-4">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Reporte de Ventas por Período
                    </h5>
                </div>
                <div class="card-body">
                    
                    <!-- Filtros -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" 
                                   value="{{ request('fecha_inicio', $fechaInicio->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" 
                                   value="{{ request('fecha_fin', $fechaFin->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                            <a href="{{ route('admin.reportes.ventas-periodo.export', request()->all()) }}" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </a>
                        </div>
                    </form>

                    <!-- Resumen -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Total Ventas</p>
                                <h4 class="mb-0 text-success">S/ {{ number_format($ventas->sum('Total'), 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Cantidad Documentos</p>
                                <h4 class="mb-0 text-primary">{{ number_format($ventas->count()) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Ticket Promedio</p>
                                <h4 class="mb-0 text-info">
                                    S/ {{ number_format($ventas->avg('Total'), 2) }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">IGV Recaudado</p>
                                <h4 class="mb-0 text-warning">S/ {{ number_format($ventas->sum('Igv'), 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Ventas -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Documento</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">IGV</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Estado SUNAT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventas as $venta)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($venta->Fecha)->format('d/m/Y') }}</td>
                                    <td>
                                        <strong>{{ $venta->Tipo }}-{{ $venta->Numero }}</strong><br>
                                        <small class="text-muted">{{ $venta->tipo_doc ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $venta->cliente }}</td>
                                    <td>{{ $venta->vendedor }}</td>
                                    <td class="text-end">S/ {{ number_format($venta->Subtotal, 2) }}</td>
                                    <td class="text-end">S/ {{ number_format($venta->Igv, 2) }}</td>
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
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No se encontraron ventas en este período
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">TOTALES:</th>
                                    <th class="text-end">S/ {{ number_format($ventas->sum('Subtotal'), 2) }}</th>
                                    <th class="text-end">S/ {{ number_format($ventas->sum('Igv'), 2) }}</th>
                                    <th class="text-end">S/ {{ number_format($ventas->sum('Total'), 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection
