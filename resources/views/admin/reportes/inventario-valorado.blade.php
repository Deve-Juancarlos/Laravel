@extends('layouts.admin')

@section('title', 'Inventario Valorizado')

@section('content')
<div class="container-fluid py-4">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-warehouse me-2"></i>
                        Inventario Valorizado
                    </h5>
                </div>
                <div class="card-body">
                    
                    <!-- Filtros -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Agrupar por</label>
                            <select name="agrupar" class="form-select">
                                <option value="laboratorio" {{ $tipoAgrupacion == 'laboratorio' ? 'selected' : '' }}>
                                    Por Laboratorio
                                </option>
                                <option value="producto" {{ $tipoAgrupacion == 'producto' ? 'selected' : '' }}>
                                    Por Producto
                                </option>
                                <option value="almacen" {{ $tipoAgrupacion == 'almacen' ? 'selected' : '' }}>
                                    Por Almacén
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-info me-2">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                            <a href="{{ route('admin.reportes.inventario-valorado.export', request()->all()) }}" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </a>
                        </div>
                    </form>

                    <!-- Resumen -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Valor Total Inventario</p>
                                <h4 class="mb-0 text-success">
                                    S/ {{ number_format($inventario->sum('valor_total'), 2) }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Stock Total</p>
                                <h4 class="mb-0 text-info">
                                    {{ number_format($inventario->sum('stock_total'), 0) }}
                                </h4>
                            </div>
                        </div>
                        @if($tipoAgrupacion != 'almacen')
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Total {{ $tipoAgrupacion == 'laboratorio' ? 'Laboratorios' : 'Productos' }}</p>
                                <h4 class="mb-0 text-primary">{{ number_format($inventario->count()) }}</h4>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    @if($tipoAgrupacion == 'laboratorio')
                                        <th>Código</th>
                                        <th>Laboratorio</th>
                                        <th class="text-center">Productos</th>
                                        <th class="text-end">Stock Total</th>
                                        <th class="text-end">Valor Total</th>
                                    @elseif($tipoAgrupacion == 'producto')
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Laboratorio</th>
                                        <th class="text-end">Stock</th>
                                        <th class="text-end">Costo Unit.</th>
                                        <th class="text-end">Valor Total</th>
                                    @else
                                        <th>Almacén</th>
                                        <th class="text-center">Productos</th>
                                        <th class="text-end">Valor Total</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inventario as $item)
                                <tr>
                                    @if($tipoAgrupacion == 'laboratorio')
                                        <td>{{ $item->CodLab }}</td>
                                        <td><strong>{{ $item->laboratorio }}</strong></td>
                                        <td class="text-center">{{ number_format($item->total_productos) }}</td>
                                        <td class="text-end">{{ number_format($item->stock_total, 0) }}</td>
                                        <td class="text-end fw-bold text-success">
                                            S/ {{ number_format($item->valor_total, 2) }}
                                        </td>
                                    @elseif($tipoAgrupacion == 'producto')
                                        <td>{{ $item->CodPro }}</td>
                                        <td><strong>{{ $item->Nombre }}</strong></td>
                                        <td>{{ $item->laboratorio ?? 'N/A' }}</td>
                                        <td class="text-end">{{ number_format($item->stock_total, 0) }}</td>
                                        <td class="text-end">S/ {{ number_format($item->Costo, 2) }}</td>
                                        <td class="text-end fw-bold text-success">
                                            S/ {{ number_format($item->valor_total, 2) }}
                                        </td>
                                    @else
                                        <td><strong>{{ $item->almacen }}</strong></td>
                                        <td class="text-center">{{ number_format($item->total_productos) }}</td>
                                        <td class="text-end fw-bold text-success">
                                            S/ {{ number_format($item->valor_total, 2) }}
                                        </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No se encontró inventario
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    @if($tipoAgrupacion == 'laboratorio')
                                        <th colspan="4" class="text-end">TOTAL:</th>
                                        <th class="text-end">S/ {{ number_format($inventario->sum('valor_total'), 2) }}</th>
                                    @elseif($tipoAgrupacion == 'producto')
                                        <th colspan="5" class="text-end">TOTAL:</th>
                                        <th class="text-end">S/ {{ number_format($inventario->sum('valor_total'), 2) }}</th>
                                    @else
                                        <th colspan="2" class="text-end">TOTAL:</th>
                                        <th class="text-end">S/ {{ number_format($inventario->sum('valor_total'), 2) }}</th>
                                    @endif
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
