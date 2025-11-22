@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Rentabilidad por Producto')

@push('styles')
    <link href="{{ asset('css/admin/rentabilidad-productos.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="rentabilidad-productos-container">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Análisis de Rentabilidad por Producto
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
                            <button type="submit" class="btn btn-success me-2">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                            <a href="{{ route('admin.reportes.rentabilidad-productos.export', request()->all()) }}" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </a>
                        </div>
                    </form>

                    <!-- Resumen General -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Total Ventas</p>
                                <h4 class="mb-0 text-success fw-bold">
                                    S/ {{ number_format($rentabilidad->sum('total_vendido'), 2) }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Costo Total</p>
                                <h4 class="mb-0 text-warning fw-bold">
                                    S/ {{ number_format($rentabilidad->sum('costo_total'), 2) }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Utilidad Bruta</p>
                                <h4 class="mb-0 text-primary fw-bold">
                                    S/ {{ number_format($rentabilidad->sum('utilidad'), 2) }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted mb-1 small">Margen Promedio</p>
                                @php
                                    $costoTotal = $rentabilidad->sum('costo_total');
                                    $utilidadTotal = $rentabilidad->sum('utilidad');
                                    $margenPromedio = $costoTotal > 0 ? ($utilidadTotal / $costoTotal) * 100 : 0;
                                @endphp
                                <h4 class="mb-0 fw-bold" style="color: {{ $margenPromedio > 30 ? '#28a745' : ($margenPromedio > 15 ? '#ffc107' : '#dc3545') }}">
                                    {{ number_format($margenPromedio, 2) }}%
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros de Clasificación -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-success active" onclick="filtrarTabla('todos')">
                                    <i class="fas fa-list me-2"></i>Todos
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="filtrarTabla('alto')">
                                    <i class="fas fa-arrow-up me-2"></i>Margen Alto (+30%)
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="filtrarTabla('medio')">
                                    <i class="fas fa-minus me-2"></i>Margen Medio (15-30%)
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="filtrarTabla('bajo')">
                                    <i class="fas fa-arrow-down me-2"></i>Margen Bajo (-15%)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-sm" id="tablaRentabilidad">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Laboratorio</th>
                                    <th class="text-center">Cantidad Vendida</th>
                                    <th class="text-end">Ventas</th>
                                    <th class="text-end">Costo</th>
                                    <th class="text-end">Utilidad</th>
                                    <th class="text-end">Margen %</th>
                                    <th class="text-center">Clasificación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rentabilidad as $index => $producto)
                                <tr data-margen="{{ $producto->margen_porcentaje }}" 
                                    data-clasificacion="{{ $producto->margen_porcentaje > 30 ? 'alto' : ($producto->margen_porcentaje > 15 ? 'medio' : 'bajo') }}">
                                    <td>
                                        <span class="badge bg-success">{{ $index + 1 }}</span>
                                    </td>
                                    <td>{{ $producto->CodPro }}</td>
                                    <td>
                                        <strong>{{ $producto->Nombre }}</strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $producto->laboratorio ?? 'N/A' }}</small>
                                    </td>
                                    <td class="text-center">{{ number_format($producto->cantidad_vendida, 0) }}</td>
                                    <td class="text-end">S/ {{ number_format($producto->total_vendido, 2) }}</td>
                                    <td class="text-end">S/ {{ number_format($producto->costo_total, 2) }}</td>
                                    <td class="text-end fw-bold text-success">S/ {{ number_format($producto->utilidad, 2) }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $producto->margen_porcentaje > 30 ? 'success' : ($producto->margen_porcentaje > 15 ? 'warning' : 'danger') }}">
                                            {{ number_format($producto->margen_porcentaje, 2) }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($producto->margen_porcentaje > 30)
                                            <span class="badge bg-success">
                                                <i class="fas fa-arrow-up me-1"></i>Alto
                                            </span>
                                        @elseif($producto->margen_porcentaje > 15)
                                            <span class="badge bg-warning">
                                                <i class="fas fa-minus me-1"></i>Medio
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-arrow-down me-1"></i>Bajo
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                        No se encontraron productos en este período
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="5" class="text-end">TOTALES:</th>
                                    <th class="text-end">S/ {{ number_format($rentabilidad->sum('total_vendido'), 2) }}</th>
                                    <th class="text-end">S/ {{ number_format($rentabilidad->sum('costo_total'), 2) }}</th>
                                    <th class="text-end">S/ {{ number_format($rentabilidad->sum('utilidad'), 2) }}</th>
                                    <th class="text-end">{{ number_format($margenPromedio, 2) }}%</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Análisis Adicional -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-arrow-up fa-3x text-success mb-3"></i>
                                    <h5 class="text-success">Productos Alto Margen</h5>
                                    <h3 class="mb-0">
                                        {{ $rentabilidad->filter(function($p) { return $p->margen_porcentaje > 30; })->count() }}
                                    </h3>
                                    <small class="text-muted">Más del 30% de margen</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-minus fa-3x text-warning mb-3"></i>
                                    <h5 class="text-warning">Productos Margen Medio</h5>
                                    <h3 class="mb-0">
                                        {{ $rentabilidad->filter(function($p) { return $p->margen_porcentaje >= 15 && $p->margen_porcentaje <= 30; })->count() }}
                                    </h3>
                                    <small class="text-muted">Entre 15% y 30%</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <i class="fas fa-arrow-down fa-3x text-danger mb-3"></i>
                                    <h5 class="text-danger">Productos Bajo Margen</h5>
                                    <h3 class="mb-0">
                                        {{ $rentabilidad->filter(function($p) { return $p->margen_porcentaje < 15; })->count() }}
                                    </h3>
                                    <small class="text-muted">Menos del 15%</small>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function filtrarTabla(clasificacion) {
    const tabla = document.getElementById('tablaRentabilidad');
    const filas = tabla.querySelectorAll('tbody tr[data-clasificacion]');
    
    // Actualizar botones activos
    document.querySelectorAll('.btn-group button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.closest('button').classList.add('active');
    
    // Filtrar filas
    filas.forEach(fila => {
        if (clasificacion === 'todos') {
            fila.style.display = '';
        } else {
            if (fila.dataset.clasificacion === clasificacion) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        }
    });
}
</script>
@endpush
@endsection
