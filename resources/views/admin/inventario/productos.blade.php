@extends('layouts.admin')

@section('title', 'Productos en Inventario')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Productos en Inventario</h1>
    <p class="text-muted mb-0">Listado completo de productos con stock</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.inventario.index') }}">Inventario</a></li>
<li class="breadcrumb-item active">Productos</li>
@endsection

@section('content')

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Buscar Producto</label>
                <input type="text" name="buscar" class="form-control" 
                       placeholder="Nombre o código del producto..."
                       value="{{ $filtros['buscar'] }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Laboratorio</label>
                <select name="laboratorio" class="form-select">
                    <option value="">Todos</option>
                    @foreach($laboratorios as $lab)
                    <option value="{{ $lab }}" {{ $filtros['laboratorio'] == $lab ? 'selected' : '' }}>
                        {{ $lab }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Stock Menor a:</label>
                <input type="number" name="stock_minimo" class="form-control" 
                       placeholder="10" value="{{ $filtros['stock_minimo'] }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Buscar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Productos -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-boxes me-2"></i>
            Productos ({{ $productos->count() }})
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
                        <th class="text-end">Costo</th>
                        <th class="text-end">Precio</th>
                        <th class="text-end">Stock</th>
                        <th class="text-end">Valorización</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    <tr>
                        <td><code>{{ $producto->CodPro }}</code></td>
                        <td><strong>{{ $producto->Nombre }}</strong></td>
                        <td><small class="text-muted">{{ $producto->Laboratorio }}</small></td>
                        <td class="text-end">S/ {{ number_format($producto->Costo, 2) }}</td>
                        <td class="text-end">S/ {{ number_format($producto->Precio, 2) }}</td>
                        <td class="text-end">
                            <span class="badge bg-{{ ($producto->stock_total ?? 0) > 10 ? 'success' : (($producto->stock_total ?? 0) > 0 ? 'warning' : 'danger') }}">
                                {{ number_format($producto->stock_total ?? 0) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <strong>S/ {{ number_format(($producto->stock_total ?? 0) * $producto->Costo, 2) }}</strong>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.inventario.detalle', $producto->CodPro) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-box-open fa-3x mb-3 d-block"></i>
                            No se encontraron productos
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
