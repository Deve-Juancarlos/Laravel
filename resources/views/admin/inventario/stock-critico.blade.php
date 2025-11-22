@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Stock Crítico')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/inventario/StockCritico.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('header-content')
<div>
    <h1 class="h3 mb-0">Productos con Stock Crítico</h1>
    <p class="text-muted mb-0">Productos con stock igual o menor a 10 unidades</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.inventario.index') }}">Inventario</a></li>
<li class="breadcrumb-item active">Stock Crítico</li>
@endsection

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0">
            <i class="fas fa-exclamation-circle me-2"></i>
            Productos con Stock Bajo ({{ $productos->count() }})
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Nombre del Producto</th>
                        <th>Laboratorio</th>
                        <th class="text-end">Costo Unit.</th>
                        <th class="text-end">Stock Actual</th>
                        <th class="text-end">Valorización</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    <tr class="{{ $producto->stock_total == 0 ? 'table-danger' : '' }}">
                        <td><code>{{ $producto->CodPro }}</code></td>
                        <td><strong>{{ $producto->Nombre }}</strong></td>
                        <td><small class="text-muted">{{ $producto->Laboratorio }}</small></td>
                        <td class="text-end">S/ {{ number_format($producto->Costo, 2) }}</td>
                        <td class="text-end">
                            <span class="badge bg-{{ $producto->stock_total == 0 ? 'danger' : 'warning' }} fs-6">
                                {{ number_format($producto->stock_total) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <strong>S/ {{ number_format($producto->valorizacion, 2) }}</strong>
                        </td>
                        <td class="text-center">
                            @if($producto->stock_total == 0)
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i>Agotado
                                </span>
                            @else
                                <span class="badge bg-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Crítico
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-success py-5">
                            <i class="fas fa-check-circle fa-4x mb-3 d-block"></i>
                            <h5>Excelente</h5>
                            <p class="text-muted">Todos los productos tienen stock suficiente</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($productos->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="text-end">Valorización Total de Productos Críticos:</th>
                        <th class="text-end">
                            <strong class="fs-5 text-danger">
                                S/ {{ number_format($productos->sum('valorizacion'), 2) }}
                            </strong>
                        </th>
                        <th></th>
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

@if($productos->where('stock_total', 0)->count() > 0)
<div class="alert alert-danger mt-4" role="alert">
    <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Productos Agotados</h5>
    <p class="mb-0">
        Hay <strong>{{ $productos->where('stock_total', 0)->count() }}</strong> 
        producto(s) sin stock. Se requiere reposición inmediata.
    </p>
</div>
@endif

@endsection
