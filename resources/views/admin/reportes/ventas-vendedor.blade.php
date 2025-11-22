@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Reporte de Ventas por Vendedor')

@push('styles')
    <link href="{{ asset('css/admin/ventas-vendedor.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="ventas-vendedor-container">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-tie me-2"></i>
                        Reporte de Ventas por Vendedor
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
                            <a href="{{ route('admin.reportes.ventas-vendedor.export', request()->all()) }}" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </a>
                        </div>
                    </form>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Vendedor</th>
                                    <th class="text-center">Total Ventas</th>
                                    <th class="text-end">Total Vendido</th>
                                    <th class="text-end">Ticket Promedio</th>
                                    <th class="text-center">Clientes Atendidos</th>
                                    <th class="text-end">% Participación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalGeneral = $vendedores->sum('total_vendido'); @endphp
                                @forelse($vendedores as $index => $vendedor)
                                <tr>
                                    <td>
                                        @if($index == 0)
                                            <span class="badge bg-warning">🥇</span>
                                        @elseif($index == 1)
                                            <span class="badge bg-secondary">🥈</span>
                                        @elseif($index == 2)
                                            <span class="badge bg-danger">🥉</span>
                                        @else
                                            <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $vendedor->Nombre }}</strong><br>
                                        <small class="text-muted">Código: {{ $vendedor->Codemp }}</small>
                                    </td>
                                    <td class="text-center">{{ $vendedor->total_ventas }}</td>
                                    <td class="text-end fw-bold text-success">
                                        S/ {{ number_format($vendedor->total_vendido, 2) }}
                                    </td>
                                    <td class="text-end">S/ {{ number_format($vendedor->ticket_promedio, 2) }}</td>
                                    <td class="text-center">{{ $vendedor->clientes_atendidos }}</td>
                                    <td class="text-end">
                                        {{ $totalGeneral > 0 ? number_format(($vendedor->total_vendido / $totalGeneral) * 100, 2) : 0 }}%
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No se encontraron vendedores en este período
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">TOTALES:</th>
                                    <th class="text-end">S/ {{ number_format($vendedores->sum('total_vendido'), 2) }}</th>
                                    <th colspan="3"></th>
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
