@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Productos por Vencer')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/inventario/por-vencer.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('header-content')
<div>
    <h1 class="h3 mb-0">Productos Próximos a Vencer</h1>
    <p class="text-muted mb-0">Alerta de productos con fecha de vencimiento cercana</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.inventario.index') }}">Inventario</a></li>
<li class="breadcrumb-item active">Por Vencer</li>
@endsection

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Productos Próximos a Vencer ({{ $productos->count() }})
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Almacén</th>
                        <th>Lote</th>
                        <th>Vencimiento</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-end">Días Restantes</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    <tr class="{{ $producto->DiasParaVencer <= 7 ? 'table-danger' : ($producto->DiasParaVencer <= 15 ? 'table-warning' : '') }}">
                        <td><code>{{ $producto->CodPro }}</code></td>
                        <td><strong>{{ $producto->Nombre }}</strong></td>
                        <td>{{ $producto->Almacen }}</td>
                        <td><code>{{ $producto->Lote }}</code></td>
                        <td>{{ \Carbon\Carbon::parse($producto->Vencimiento)->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <span class="badge bg-secondary">{{ number_format($producto->Stock) }}</span>
                        </td>
                        <td class="text-end">
                            <span class="badge bg-{{ $producto->DiasParaVencer <= 7 ? 'danger' : ($producto->DiasParaVencer <= 15 ? 'warning' : 'info') }}">
                                {{ $producto->DiasParaVencer }} días
                            </span>
                        </td>
                        <td class="text-center">
                            @if($producto->DiasParaVencer <= 0)
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i>Vencido
                                </span>
                            @elseif($producto->DiasParaVencer <= 7)
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-circle me-1"></i>Urgente
                                </span>
                            @elseif($producto->DiasParaVencer <= 15)
                                <span class="badge bg-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Crítico
                                </span>
                            @else
                                <span class="badge bg-info">
                                    <i class="fas fa-clock me-1"></i>Normal
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-success py-5">
                            <i class="fas fa-check-circle fa-4x mb-3 d-block"></i>
                            <h5>Excelente</h5>
                            <p class="text-muted">No hay productos próximos a vencer</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <a href="{{ route('admin.inventario.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
        </a>
    </div>
</div>

@if($productos->where('dias_para_vencer', '<=', 7)->count() > 0)
<div class="alert alert-danger mt-4" role="alert">
    <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Atención Urgente</h5>
    <p class="mb-0">
        Hay <strong>{{ $productos->where('dias_para_vencer', '<=', 7)->count() }}</strong> 
        producto(s) que vencen en menos de 7 días. Se recomienda tomar acción inmediata.
    </p>
</div>
@endif

@endsection
