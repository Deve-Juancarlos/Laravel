@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Dashboard de Inventario')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/inventario/index.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('header-content')
<div>
    <h1 class="h3 mb-0">Dashboard Ejecutivo de Inventario</h1>
    <p class="text-muted mb-0">Vista general del inventario y stock</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item active">Inventario</li>
@endsection

@section('content')

<!-- Estadísticas Principales -->
<div class="row mb-4">
    <div class="col-lg-2 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                    <i class="fas fa-boxes fa-2x text-primary"></i>
                </div>
                <h6 class="text-muted mb-1">Total Productos</h6>
                <h3 class="mb-0">{{ number_format($estadisticas['total_productos']) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
                <h6 class="text-muted mb-1">Con Stock</h6>
                <h3 class="mb-0">{{ number_format($estadisticas['productos_con_stock']) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                </div>
                <h6 class="text-muted mb-1">Sin Stock</h6>
                <h3 class="mb-0">{{ number_format($estadisticas['productos_sin_stock']) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-info bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                    <i class="fas fa-cubes fa-2x text-info"></i>
                </div>
                <h6 class="text-muted mb-1">Unidades</h6>
                <h3 class="mb-0">{{ number_format($estadisticas['stock_total']) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-warning bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                </div>
                <h6 class="text-muted mb-1">Por Vencer</h6>
                <h3 class="mb-0">{{ number_format($estadisticas['productos_por_vencer']) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                    <i class="fas fa-dollar-sign fa-2x text-success"></i>
                </div>
                <h6 class="text-muted mb-1">Valorización</h6>
                <h3 class="mb-0 small">S/ {{ number_format($estadisticas['valorizacion_total'], 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Accesos Rápidos -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.inventario.productos') }}" class="btn btn-primary">
                        <i class="fas fa-list me-2"></i>Ver Todos los Productos
                    </a>
                    <a href="{{ route('admin.inventario.stock-critico') }}" class="btn btn-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>Stock Crítico
                    </a>
                    <a href="{{ route('admin.inventario.por-vencer') }}" class="btn btn-warning">
                        <i class="fas fa-clock me-2"></i>Productos por Vencer
                    </a>
                    <a href="{{ route('admin.inventario.valorizacion') }}" class="btn btn-success">
                        <i class="fas fa-chart-pie me-2"></i>Valorización
                    </a>
                    <a href="{{ route('admin.inventario.rotacion') }}" class="btn btn-info">
                        <i class="fas fa-sync-alt me-2"></i>Rotación de Inventario
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Productos con Stock Crítico -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Stock Crítico (≤ 10 unidades)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Producto</th>
                                <th>Laboratorio</th>
                                <th class="text-end">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productosCriticos as $producto)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.inventario.detalle', $producto->CodPro) }}" class="text-decoration-none">
                                        {{ $producto->Nombre }}
                                    </a>
                                </td>
                                <td><small class="text-muted">{{ $producto->Laboratorio }}</small></td>
                                <td class="text-end">
                                    <span class="badge bg-danger">{{ number_format($producto->stock_total) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-success py-4">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <p class="mb-0">No hay productos con stock crítico</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($productosCriticos->count() > 0)
            <div class="card-footer bg-white">
                <a href="{{ route('admin.inventario.stock-critico') }}" class="btn btn-sm btn-danger w-100">
                    Ver Todos los Productos Críticos
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Productos por Vencer -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Productos Próximos a Vencer (≤ 30 días)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Producto</th>
                                <th>Lote</th>
                                <th>Vencimiento</th>
                                <th class="text-end">Días</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productosVencer as $producto)
                            <tr>
                                <td>{{ $producto->Nombre }}</td>
                                <td><code class="small">{{ $producto->Lote }}</code></td>
                                <td>{{ \Carbon\Carbon::parse($producto->Vencimiento)->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <span class="badge bg-{{ $producto->DiasParaVencer <= 7 ? 'danger' : 'warning' }}">
                                        {{ $producto->DiasParaVencer }} días
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-success py-4">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <p class="mb-0">No hay productos próximos a vencer</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($productosVencer->count() > 0)
            <div class="card-footer bg-white">
                <a href="{{ route('admin.inventario.por-vencer') }}" class="btn btn-sm btn-warning w-100">
                    Ver Todos los Productos por Vencer
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
