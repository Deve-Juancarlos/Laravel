@extends('layouts.app')
@section('title', 'Lista de Productos')
@section('page-title', 'Catálogo de Productos')
@section('breadcrumbs')
    <li class="breadcrumb-item">Inventario</li>
    <li class="breadcrumb-item active" aria-current="page">Productos</li>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h5 class="card-title m-0">Lista de Productos</h5>
        <a href="{{ route('contador.inventario.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus-circle"></i> Nuevo Producto
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('contador.inventario.index') }}" class="mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="q" placeholder="Buscar por Nombre, Código o Laboratorio..." value="{{ $filtros['q'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filtrar</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('contador.inventario.index') }}" class="btn btn-secondary w-100">Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Laboratorio</th>
                        <th class="text-end">Stock Total</th>
                        <th class="text-end">Costo</th>
                        <th class="text-end">P. Venta</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                        <tr>
                            <td><code>{{ $producto->CodPro }}</code></td>
                            <td><strong>{{ $producto->Nombre }}</strong></td>
                            <td>{{ $producto->Laboratorio }}</td>
                            <td class="text-end fw-bold">{{ number_format($producto->Stock, 2) }}</td>
                            <td class="text-end text-danger">S/ {{ number_format($producto->Costo, 2) }}</td>
                            <td class="text-end text-success">S/ {{ number_format($producto->PventaMa, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('contador.inventario.show', $producto->CodPro) }}" class="btn btn-sm btn-info" title="Ver Lotes y Stock">
                                    <i class="fas fa-eye"></i> Ver Lotes
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center p-4 text-muted">No se encontraron productos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end">
            {{ $productos->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection