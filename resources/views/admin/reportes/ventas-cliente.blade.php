@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Reporte de Ventas por Cliente')

@push('styles')
    <link href="{{ asset('css/admin/reporte-clientes.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="reportes-clientes-container">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Reporte de Ventas por Cliente
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
                            <a href="{{ route('admin.reportes.ventas-cliente.export', request()->all()) }}" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </a>
                        </div>
                    </form>

                    <!-- Resumen -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Total Clientes</p>
                                <h4 class="mb-0 text-info">{{ number_format($ventas->count()) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Total Vendido</p>
                                <h4 class="mb-0 text-success">S/ {{ number_format($ventas->sum('total_vendido'), 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Ticket Promedio General</p>
                                <h4 class="mb-0 text-primary">S/ {{ number_format($ventas->avg('ticket_promedio'), 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Cliente</th>
                                    <th>Documento</th>
                                    <th>Zona</th>
                                    <th class="text-center">Docs</th>
                                    <th class="text-end">Total Vendido</th>
                                    <th class="text-end">Ticket Prom.</th>
                                    <th class="text-end">% Particip.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalGeneral = $ventas->sum('total_vendido'); @endphp
                                @forelse($ventas as $index => $cliente)
                                <tr>
                                    <td>
                                        <span class="badge bg-info">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $cliente->Razon }}</strong><br>
                                        <small class="text-muted">Código: {{ $cliente->Codclie }}</small>
                                    </td>
                                    <td>{{ $cliente->Documento }}</td>
                                    <td>{{ $cliente->Zona ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $cliente->total_documentos }}</td>
                                    <td class="text-end fw-bold text-success">
                                        S/ {{ number_format($cliente->total_vendido, 2) }}
                                    </td>
                                    <td class="text-end">S/ {{ number_format($cliente->ticket_promedio, 2) }}</td>
                                    <td class="text-end">
                                        {{ $totalGeneral > 0 ? number_format(($cliente->total_vendido / $totalGeneral) * 100, 2) : 0 }}%
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No se encontraron clientes en este período
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="5" class="text-end">TOTALES:</th>
                                    <th class="text-end">S/ {{ number_format($ventas->sum('total_vendido'), 2) }}</th>
                                    <th colspan="2"></th>
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
