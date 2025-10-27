@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('contador.estado-resultados.index') }}">Estado de Resultados</a></li>
            <li class="breadcrumb-item active"><i class="fas fa-pills"></i> Análisis Farmacéutico</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary">
                <i class="fas fa-pills me-2"></i>
                Análisis Farmacéutico SIFANO
            </h2>
            <p class="text-muted mb-0">Análisis específico de la distribuidora de fármacos</p>
        </div>
        <div class="text-end">
            <small class="text-muted">Período: <strong>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</strong></small>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.estado-resultados.farmaceutico') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i> Aplicar Filtros
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetFiltros()">
                            <i class="fas fa-undo"></i> Restablecer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards KPIs Farmacéuticos -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Líneas Farmacéuticas</h5>
                            <h3 class="text-white">{{ $ventasPorLinea->count() }}</h3>
                            <small>Categorías activas</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-layer-group fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Productos Top</h5>
                            <h3 class="text-white">{{ $costosFarmaceuticos->count() }}</h3>
                            <small>Con mayor costo</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-star fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Rentabilidad</h5>
                            <h3 class="text-white">{{ number_format($rentabilidad->avg('margen') ?? 0, 1) }}%</h3>
                            <small>Margen promedio</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-percentage fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Clientes Activos</h5>
                            <h3 class="text-white">{{ $ventasPorLinea->unique('CodClie')->count() }}</h3>
                            <small>En el período</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ventas por Línea Farmacéutica -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-chart-pie me-2"></i>
                Distribución por Líneas Farmacéuticas
            </h5>
        </div>
        <div class="card-body">
            @if($ventasPorLinea->count() > 0)
            <div class="row">
                <div class="col-lg-8">
                    <canvas id="chartLineasFarmaceuticas" height="100"></canvas>
                </div>
                <div class="col-lg-4">
                    <h6>Ranking de Vendedores:</h6>
                    @php
                        $vendedoresTop = $ventasPorLinea->groupBy('Vendedor')->map->count()->sortDesc()->take(5);
                    @endphp
                    @foreach($vendedoresTop as $vendedor => $cantidad)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                        <div>
                            <strong>{{ $vendedor }}</strong>
                            <br><small class="text-muted">{{ $cantidad }} registros</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary">{{ $cantidad }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-pills fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay datos de líneas farmacéuticas</h5>
                <p class="text-muted">No se encontraron registros en el período seleccionado.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Top 10 Productos con Mayor Costo -->
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">
                <i name="chartProductos" class="fas fa-dollar-sign me-2"></i>
                Top 10 Productos - Mayor Costo
            </h5>
        </div>
        <div class="card-body">
            @if($costosFarmaceuticos->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th class="text-end">Costo Total (S/.)</th>
                            <th class="text-end">Cantidad Total</th>
                            <th class="text-end">Costo Promedio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rank = 1; @endphp
                        @foreach($costosFarmaceuticos as $producto)
                        <tr>
                            <td>
                                @if($rank <= 3)
                                    <span class="badge bg-{{ $rank == 1 ? 'warning' : ($rank == 2 ? 'secondary' : 'dark') }} fs-6">
                                        #{{ $rank }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark">{{ $rank }}</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $producto->Nombre }}</strong>
                                <br><small class="text-muted">Código: {{ $producto->CodPro ?? 'N/A' }}</small>
                            </td>
                            <td class="text-end text-danger">
                                <strong>{{ number_format($producto->costo_total, 2) }}</strong>
                            </td>
                            <td class="text-end">{{ number_format($producto->cantidad_total, 0) }}</td>
                            <td class="text-end">
                                {{ number_format($producto->costo_total / $producto->cantidad_total, 2) }}
                            </td>
                        </tr>
                        @php $rank++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-3">
                <p class="text-muted">No se encontraron costos de productos en el período</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Análisis de Rentabilidad por Producto -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>
                Análisis de Rentabilidad por Producto
            </h5>
        </div>
        <div class="card-body">
            @if($rentabilidad->count() > 0)
            <div class="row">
                <div class="col-lg-8">
                    <canvas id="chartRentabilidad" height="100"></canvas>
                </div>
                <div class="col-lg-4">
                    <h6>Productos Más Rentables:</h6>
                    @php
                        $productosRentables = $rentabilidad->sortByDesc('margen')->take(5);
                    @endphp
                    @foreach($productosRentables as $producto)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                        <div>
                            <strong>{{ Str::limit($producto->Nombre, 20) }}</strong>
                            <br>
                            <small class="text-muted">
                                Ventas: {{ number_format($producto->ventas_total, 0) }}
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-{{ $producto->margen >= 30 ? 'success' : ($producto->margen >= 20 ? 'warning' : 'danger') }}">
                                {{ number_format($producto->margen, 1) }}%
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="text-center py-3">
                <p class="text-muted">No se encontraron datos de rentabilidad</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Resumen por Categorías Farmacéuticas -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Resumen Financiero
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $totalVentasFarmaceuticas = $rentabilidad->sum('ventas_total');
                        $totalCostosFarmaceuticos = $rentabilidad->sum('costo_total');
                        $utilidadFarmaceutica = $totalVentasFarmaceuticas - $totalCostosFarmaceuticos;
                        $margenPromedio = $totalVentasFarmaceuticas > 0 ? ($utilidadFarmaceutica / $totalVentasFarmaceuticas) * 100 : 0;
                    @endphp
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Total Ventas:</strong></span>
                            <strong class="text-success">{{ number_format($totalVentasFarmaceuticas, 2) }}</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Total Costos:</strong></span>
                            <strong class="text-danger">{{ number_format($totalCostosFarmaceuticos, 2) }}</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Utilidad Bruta:</strong></span>
                            <strong class="{{ $utilidadFarmaceutica >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($utilidadFarmaceutica, 2) }}
                            </strong>
                        </div>
                    </div>
                    
                    <div class="border-top pt-2">
                        <div class="d-flex justify-content-between">
                            <span><strong>Margen Promedio:</strong></span>
                            <span class="badge bg-{{ $margenPromedio >= 30 ? 'success' : ($margenPromedio >= 20 ? 'warning' : 'danger') }} fs-6">
                                {{ number_format($margenPromedio, 1) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Recomendaciones Farmacéuticas
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $productosAltaRentabilidad = $rentabilidad->where('margen', '>=', 30)->count();
                        $productosMediaRentabilidad = $rentabilidad->whereBetween('margen', [20, 29.9])->count();
                        $productosBajaRentabilidad = $rentabilidad->where('margen', '<', 20)->count();
                    @endphp
                    
                    <div class="mb-3">
                        <h6>Rendimiento de Productos:</h6>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: {{ ($productosAltaRentabilidad / $rentabilidad->count()) * 100 }}%">
                                Alta (≥30%)
                            </div>
                        </div>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar bg-warning" style="width: {{ ($productosMediaRentabilidad / $rentabilidad->count()) * 100 }}%">
                                Media (20-29%)
                            </div>
                        </div>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar bg-danger" style="width: {{ ($productosBajaRentabilidad / $rentabilidad->count()) * 100 }}%">
                                Baja (<20%)
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tip Farmacéutico:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Promocionar productos de alta rentabilidad</li>
                            <li>Revisar costos de productos de baja rentabilidad</li>
                            <li>Optimizar inventario de productos más vendidos</li>
                            <li>Negociar mejores precios con proveedores</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Detallada de Rentabilidad -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>
                Detalle de Rentabilidad por Producto
            </h5>
        </div>
        <div class="card-body">
            @if($rentabilidad->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Producto</th>
                            <th class="text-end">Ventas (S/.)</th>
                            <th class="text-end">Costos (S/.)</th>
                            <th class="text-end">Utilidad (S/.)</th>
                            <th class="text-end">Margen (%)</th>
                            <th class="text-center">Clasificación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rentabilidad->sortByDesc('ventas_total') as $producto)
                        <tr>
                            <td>
                                <strong>{{ Str::limit($producto->Nombre, 40) }}</strong>
                            </td>
                            <td class="text-end text-success">
                                {{ number_format($producto->ventas_total, 2) }}
                            </td>
                            <td class="text-end text-danger">
                                {{ number_format($producto->costo_total, 2) }}
                            </td>
                            <td class="text-end {{ $producto->ventas_total - $producto->costo_total >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($producto->ventas_total - $producto->costo_total, 2) }}
                            </td>
                            <td class="text-end">
                                <span class="badge bg-{{ $producto->margen >= 30 ? 'success' : ($producto->margen >= 20 ? 'warning' : 'danger') }}">
                                    {{ number_format($producto->margen, 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                @if($producto->margen >= 30)
                                    <i class="fas fa-star text-success" title="Alta Rentabilidad"></i>
                                @elseif($producto->margen >= 20)
                                    <i class="fas fa-thumbs-up text-warning" title="Rentabilidad Media"></i>
                                @else
                                    <i class="fas fa-exclamation-triangle text-danger" title="Baja Rentabilidad"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-info">
                        <tr>
                            <th>TOTALES</th>
                            <th class="text-end">{{ number_format($totalVentasFarmaceuticas, 2) }}</th>
                            <th class="text-end">{{ number_format($totalCostosFarmaceuticos, 2) }}</th>
                            <th class="text-end">{{ number_format($utilidadFarmaceutica, 2) }}</th>
                            <th class="text-end">{{ number_format($margenPromedio, 1) }}%</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-3">
                <p class="text-muted">No se encontraron datos de rentabilidad por producto</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Navegación -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('contador.estado-resultados.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Volver al Estado Principal
                    </a>
                    <a href="{{ route('contador.estado-resultados.periodos') }}" class="btn btn-outline-info ms-2">
                        <i class="fas fa-calendar-alt"></i> Ver Períodos
                    </a>
                </div>
                <div>
                    <a href="{{ route('contador.estado-resultados.comparativo') }}" class="btn btn-warning me-2">
                        <i class="fas fa-comparison"></i> Comparativo
                    </a>
                    <button class="btn btn-success" onclick="generarReporteFarmaceutico()">
                        <i class="fas fa-file-pdf"></i> Generar Reporte
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de líneas farmacéuticas
    const ctxLineas = document.getElementById('chartLineasFarmaceuticas');
    if (ctxLineas) {
        new Chart(ctxLineas, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($ventasPorLinea->groupBy('Vendedor')->keys()->toArray()) !!},
                datasets: [{
                    data: {!! json_encode($ventasPorLinea->groupBy('Vendedor')->map->count()->values()->toArray()) !!},
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribución por Vendedores'
                    },
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    // Gráfico de rentabilidad
    const ctxRentabilidad = document.getElementById('chartRentabilidad');
    if (ctxRentabilidad) {
        new Chart(ctxRentabilidad, {
            type: 'bar',
            data: {
                labels: {!! json_encode($rentabilidad->sortByDesc('ventas_total')->take(10)->pluck('Nombre')->map(function($nombre) { return Str::limit($nombre, 15); })->toArray()) !!},
                datasets: [{
                    label: 'Margen %',
                    data: {!! json_encode($rentabilidad->sortByDesc('ventas_total')->take(10)->pluck('margen')->toArray()) !!},
                    backgroundColor: function(context) {
                        const value = context.parsed.y;
                        return value >= 30 ? '#28a745' : value >= 20 ? '#ffc107' : '#dc3545';
                    },
                    borderColor: '#333',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Margen de Rentabilidad - Top 10 Productos'
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }
});

function resetFiltros() {
    document.querySelector('input[name="fecha_inicio"]').value = '{{ \Carbon\Carbon::now()->startOfYear()->format("Y-m-d") }}';
    document.querySelector('input[name="fecha_fin"]').value = '{{ \Carbon\Carbon::now()->endOfYear()->format("Y-m-d") }}';
}

function generarReporteFarmaceutico() {
    alert('Funcionalidad de generación de reporte PDF será implementada próximamente');
}
</script>
@endsection