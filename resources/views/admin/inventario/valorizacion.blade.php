@extends('layouts.admin')

@section('title', 'Valorización de Inventario')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Valorización del Inventario</h1>
    <p class="text-muted mb-0">Análisis del valor total del inventario</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.inventario.index') }}">Inventario</a></li>
<li class="breadcrumb-item active">Valorización</li>
@endsection

@section('content')

<!-- Resumen General -->
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-info bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
                    <i class="fas fa-cubes fa-3x text-info"></i>
                </div>
                <h6 class="text-muted mb-2">Total Unidades</h6>
                <h2 class="mb-0">{{ number_format($valorizacion->unidades_totales) }}</h2>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
                    <i class="fas fa-dollar-sign fa-3x text-danger"></i>
                </div>
                <h6 class="text-muted mb-2">Costo Total</h6>
                <h2 class="mb-0 text-danger">S/ {{ number_format($valorizacion->costo_total, 2) }}</h2>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
                    <i class="fas fa-chart-line fa-3x text-success"></i>
                </div>
                <h6 class="text-muted mb-2">Precio Venta Total</h6>
                <h2 class="mb-0 text-success">S/ {{ number_format($valorizacion->precio_venta_total, 2) }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Margen Potencial -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @php
                    $margenPotencial = $valorizacion->precio_venta_total - $valorizacion->costo_total;
                    $porcentajeMargen = $valorizacion->costo_total > 0 
                        ? ($margenPotencial / $valorizacion->costo_total) * 100 
                        : 0;
                @endphp
                <div class="text-center">
                    <h5 class="text-muted mb-3">Margen Potencial del Inventario</h5>
                    <h1 class="display-4 text-primary mb-2">S/ {{ number_format($margenPotencial, 2) }}</h1>
                    <p class="text-muted">
                        Margen: <strong class="text-primary">{{ number_format($porcentajeMargen, 2) }}%</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Valorización por Laboratorio -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>
            Valorización por Laboratorio
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Laboratorio</th>
                        <th class="text-center">Cantidad de Productos</th>
                        <th class="text-end">Stock Total</th>
                        <th class="text-end">Valorización</th>
                        <th class="text-end">% del Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalValorizacion = $porLaboratorio->sum('valorizacion'); @endphp
                    @forelse($porLaboratorio as $lab)
                    <tr>
                        <td><strong>{{ $lab->Laboratorio ?? 'Sin Laboratorio' }}</strong></td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $lab->cantidad_productos }}</span>
                        </td>
                        <td class="text-end">{{ number_format($lab->stock_total) }}</td>
                        <td class="text-end">
                            <strong class="text-success">S/ {{ number_format($lab->valorizacion, 2) }}</strong>
                        </td>
                        <td class="text-end">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" 
                                     role="progressbar" 
                                     style="width: {{ $totalValorizacion > 0 ? ($lab->valorizacion / $totalValorizacion) * 100 : 0 }}%">
                                    {{ $totalValorizacion > 0 ? number_format(($lab->valorizacion / $totalValorizacion) * 100, 1) : 0 }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No hay datos de valorización disponibles
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($porLaboratorio->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <th>TOTAL</th>
                        <th class="text-center">{{ $porLaboratorio->sum('cantidad_productos') }}</th>
                        <th class="text-end">{{ number_format($porLaboratorio->sum('stock_total')) }}</th>
                        <th class="text-end">
                            <strong class="fs-5 text-success">
                                S/ {{ number_format($totalValorizacion, 2) }}
                            </strong>
                        </th>
                        <th class="text-end">100%</th>
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

@endsection
