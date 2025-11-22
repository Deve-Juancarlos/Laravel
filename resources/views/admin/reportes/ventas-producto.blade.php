@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Reporte de Ventas por Producto')

@push('styles')
    <link href="{{ asset('css/admin/ventas-producto.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="ventas-producto-container">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-boxes me-2"></i>
                        Reporte de Ventas por Producto
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
                            <a href="{{ route('admin.reportes.ventas-producto.export', request()->all()) }}" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </a>
                        </div>
                    </form>

                    <!-- Resumen -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Total Productos</p>
                                <h4 class="mb-0 text-info">{{ number_format($productos->count()) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Total Vendido</p>
                                <h4 class="mb-0 text-success">S/ {{ number_format($productos->sum('total_vendido'), 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Costo Total</p>
                                <h4 class="mb-0 text-warning">S/ {{ number_format($productos->sum('costo_total'), 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Utilidad Total</p>
                                <h4 class="mb-0 text-primary">S/ {{ number_format($productos->sum('utilidad'), 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Laboratorio</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Total Venta</th>
                                    <th class="text-end">Costo</th>
                                    <th class="text-end">Utilidad</th>
                                    <th class="text-end">Margen %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productos as $index => $producto)
                                <tr>
                                    <td>
                                        <span class="badge bg-success">{{ $index + 1 }}</span>
                                    </td>
                                    <td>{{ $producto->CodPro }}</td>
                                    <td>{{ $producto->Nombre }}</td>
                                    <td>{{ $producto->laboratorio ?? 'N/A' }}</td>
                                    <td class="text-center">{{ number_format($producto->cantidad_vendida, 0) }}</td>
                                    <td class="text-end fw-bold">S/ {{ number_format($producto->total_vendido, 2) }}</td>
                                    <td class="text-end">S/ {{ number_format($producto->costo_total, 2) }}</td>
                                    <td class="text-end text-success">S/ {{ number_format($producto->utilidad, 2) }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $producto->margen_porcentaje > 30 ? 'success' : ($producto->margen_porcentaje > 15 ? 'warning' : 'danger') }}">
                                            {{ number_format($producto->margen_porcentaje, 2) }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        No se encontraron productos en este período
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="5" class="text-end">TOTALES:</th>
                                    <th class="text-end">S/ {{ number_format($productos->sum('total_vendido'), 2) }}</th>
                                    <th class="text-end">S/ {{ number_format($productos->sum('costo_total'), 2) }}</th>
                                    <th class="text-end">S/ {{ number_format($productos->sum('utilidad'), 2) }}</th>
                                    <th class="text-end">
                                        @php
                                            $costoTotal = $productos->sum('costo_total');
                                            $utilidadTotal = $productos->sum('utilidad');
                                            $margenPromedio = $costoTotal > 0 ? ($utilidadTotal / $costoTotal) * 100 : 0;
                                        @endphp
                                        {{ number_format($margenPromedio, 2) }}%
                                    </th>
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
