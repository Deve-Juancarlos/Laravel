@extends('layouts.admin')

@section('title', 'Rotación de Inventario')


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/inventario/rotacion.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('header-content')
<div>
    <h1 class="h3 mb-0">Rotación de Inventario</h1>
    <p class="text-muted mb-0">Productos más vendidos del año {{ date('Y') }}</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.inventario.index') }}">Inventario</a></li>
<li class="breadcrumb-item active">Rotación</li>
@endsection

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-sync-alt me-2"></i>
            Top 50 Productos con Mayor Rotación ({{ date('Y') }})
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Código</th>
                        <th>Nombre del Producto</th>
                        <th>Laboratorio</th>
                        <th class="text-end">Total Entradas</th>
                        <th class="text-end">Total Salidas</th>
                        <th class="text-end">Rotación</th>
                        <th class="text-center">Indicador</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rotacion as $index => $item)
                    <tr>
                        <td>
                            @if($index < 3)
                                <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'danger') }} fs-6">
                                    {{ $index == 0 ? '🥇' : ($index == 1 ? '🥈' : '🥉') }}
                                </span>
                            @else
                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td><code>{{ $item->CodPro }}</code></td>
                        <td><strong>{{ $item->Nombre }}</strong></td>
                        <td><small class="text-muted">{{ $item->Laboratorio }}</small></td>
                        <td class="text-end">
                            <span class="badge bg-success">{{ number_format($item->total_entradas) }}</span>
                        </td>
                        <td class="text-end">
                            <span class="badge bg-danger">{{ number_format($item->total_salidas) }}</span>
                        </td>
                        <td class="text-end">
                            <strong class="fs-5 text-primary">{{ number_format($item->total_salidas) }}</strong>
                        </td>
                        <td class="text-center">
                            @if($item->total_salidas > 1000)
                                <span class="badge bg-success">
                                    <i class="fas fa-fire me-1"></i>Alto
                                </span>
                            @elseif($item->total_salidas > 500)
                                <span class="badge bg-primary">
                                    <i class="fas fa-arrow-up me-1"></i>Medio
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-equals me-1"></i>Bajo
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-chart-line fa-3x mb-3 d-block"></i>
                            No hay datos de rotación disponibles para este año
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rotacion->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="4" class="text-end">TOTALES:</th>
                        <th class="text-end">
                            <strong>{{ number_format($rotacion->sum('total_entradas')) }}</strong>
                        </th>
                        <th class="text-end">
                            <strong>{{ number_format($rotacion->sum('total_salidas')) }}</strong>
                        </th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <a href="{{ route('admin.inventario.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
        </a>
    </div>
</div>

<!-- Análisis de Rotación -->
@if($rotacion->count() > 0)
<div class="row mt-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
                    <i class="fas fa-fire fa-2x text-success"></i>
                </div>
                <h6 class="text-muted mb-2">Alta Rotación (>1000)</h6>
                <h3 class="mb-0">{{ $rotacion->where('total_salidas', '>', 1000)->count() }}</h3>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
                    <i class="fas fa-arrow-up fa-2x text-primary"></i>
                </div>
                <h6 class="text-muted mb-2">Media Rotación (500-1000)</h6>
                <h3 class="mb-0">{{ $rotacion->whereBetween('total_salidas', [500, 1000])->count() }}</h3>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-secondary bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
                    <i class="fas fa-equals fa-2x text-secondary"></i>
                </div>
                <h6 class="text-muted mb-2">Baja Rotación (<500)</h6>
                <h3 class="mb-0">{{ $rotacion->where('total_salidas', '<', 500)->count() }}</h3>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
