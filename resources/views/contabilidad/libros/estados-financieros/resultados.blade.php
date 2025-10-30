@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active"><i class="fas fa-chart-line"></i> Estado de Resultados</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary">
                <i class="fas fa-chart-line me-2"></i>
                Estado de Resultados SUNAT
            </h2>
            <p class="text-muted mb-0">Análisis de ingresos, costos y gastos - Distribuidora de Fármacos</p>
        </div>
        <div class="text-end">
            <small class="text-muted">Período: <strong>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</strong></small>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.estado-resultados.index') }}">
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
                        <a href="{{ route('contador.estado-resultados.periodos') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-calendar-alt"></i> Ver Períodos
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards Estadísticas Principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Ventas Netas</h5>
                            <h3 class="text-white">{{ number_format($totalVentas, 2) }}</h3>
                            <small>S/.</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
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
                            <h5 class="card-title mb-0">Costo de Ventas</h5>
                            <h3 class="text-white">{{ number_format($totalCostoVentas, 2) }}</h3>
                            <small>S/.</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-truck fa-2x opacity-75"></i>
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
                            <h5 class="card-title mb-0">Utilidad Bruta</h5>
                            <h3 class="text-white">{{ number_format($utilidadBruta, 2) }}</h3>
                            <small>S/. ({{ number_format($margenBruto, 1) }}%)</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-chart-bar fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 {{ $utilidadNeta >= 0 ? 'bg-warning' : 'bg-danger' }} text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Utilidad Neta</h5>
                            <h3 class="text-white">{{ number_format($utilidadNeta, 2) }}</h3>
                            <small>S/. ({{ number_format($margenNeto, 1) }}%)</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen por Categorías -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>
                        Estado de Resultados Detallado
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Concepto</th>
                                    <th class="text-end">Importe (S/.)</th>
                                    <th class="text-end">% Ventas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- INGRESOS -->
                                <tr class="table-success">
                                    <td colspan="3"><strong>INGRESOS</strong></td>
                                </tr>
                                <tr>
                                    <td>Ventas Netas</td>
                                    <td class="text-end">{{ number_format($totalVentas, 2) }}</td>
                                    <td class="text-end">100.0%</td>
                                </tr>
                                <tr class="table-info">
                                    <td><strong>TOTAL INGRESOS</strong></td>
                                    <td class="text-end"><strong>{{ number_format($totalVentas, 2) }}</strong></td>
                                    <td class="text-end"><strong>100.0%</strong></td>
                                </tr>
                                
                                <!-- COSTOS -->
                                <tr class="table-warning">
                                    <td colspan="3"><strong>COSTOS Y GASTOS</strong></td>
                                </tr>
                                <tr>
                                    <td>Costo de Ventas</td>
                                    <td class="text-end">{{ number_format($totalCostoVentas, 2) }}</td>
                                    <td class="text-end">{{ number_format(($totalCostoVentas / $totalVentas) * 100, 1) }}%</td>
                                </tr>
                                <tr>
                                    <td>Gastos Operativos</td>
                                    <td class="text-end">{{ number_format($totalGastos, 2) }}</td>
                                    <td class="text-end">{{ number_format(($totalGastos / $totalVentas) * 100, 1) }}%</td>
                                </tr>
                                <tr class="table-warning">
                                    <td><strong>TOTAL COSTOS Y GASTOS</strong></td>
                                    <td class="text-end"><strong>{{ number_format($totalCostoVentas + $totalGastos, 2) }}</strong></td>
                                    <td class="text-end"><strong>{{ number_format((($totalCostoVentas + $totalGastos) / $totalVentas) * 100, 1) }}%</strong></td>
                                </tr>
                                
                                <!-- UTILIDAD -->
                                <tr class="{{ $utilidadNeta >= 0 ? 'table-success' : 'table-danger' }}">
                                    <td><strong>UTILIDAD NETA</strong></td>
                                    <td class="text-end"><strong>{{ number_format($utilidadNeta, 2) }}</strong></td>
                                    <td class="text-end"><strong>{{ number_format($margenNeto, 1) }}%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen por Categorías -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-pie-chart me-2"></i>
                        Resumen por Categorías
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-circle text-success me-2"></i>Ingresos</span>
                            <strong>{{ number_format($resumen['INGRESOS'], 2) }}</strong>
                        </div>
                        <div class="progress mt-1" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-circle text-danger me-2"></i>Costo Ventas</span>
                            <strong>{{ number_format($resumen['COSTO_VENTAS'], 2) }}</strong>
                        </div>
                        <div class="progress mt-1" style="height: 6px;">
                            <div class="progress-bar bg-danger" style="width: {{ ($resumen['COSTO_VENTAS'] / $resumen['INGRESOS']) * 100 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-circle text-warning me-2"></i>Utilidad Bruta</span>
                            <strong>{{ number_format($resumen['UTILIDAD_BRUTA'], 2) }}</strong>
                        </div>
                        <div class="progress mt-1" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: {{ ($resumen['UTILIDAD_BRUTA'] / $resumen['INGRESOS']) * 100 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-circle text-info me-2"></i>Gastos Operativos</span>
                            <strong>{{ number_format($resumen['GASTOS_OPERATIVOS'], 2) }}</strong>
                        </div>
                        <div class="progress mt-1" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: {{ ($resumen['GASTOS_OPERATIVOS'] / $resumen['INGRESOS']) * 100 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-0">
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-circle {{ $utilidadNeta >= 0 ? 'text-success' : 'text-danger' }} me-2"></i>Utilidad Operativa</span>
                            <strong>{{ number_format($resumen['UTILIDAD_OPERATIVA'], 2) }}</strong>
                        </div>
                        <div class="progress mt-1" style="height: 6px;">
                            <div class="progress-bar {{ $utilidadNeta >= 0 ? 'bg-success' : 'bg-danger' }}" style="width: {{ abs($resumen['UTILIDAD_OPERATIVA'] / $resumen['INGRESOS']) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Margenes -->
            <div class="card mt-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-percentage me-2"></i>
                        Márgenes Financieros
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h6 class="text-primary mb-1">{{ number_format($margenBruto, 1) }}%</h6>
                                <small class="text-muted">Margen Bruto</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h6 class="text-info mb-1">{{ number_format($margenOperativo, 1) }}%</h6>
                                <small class="text-muted">Margen Operativo</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h6 class="{{ $margenNeto >= 0 ? 'text-success' : 'text-danger' }} mb-1">{{ number_format($margenNeto, 1) }}%</h6>
                                <small class="text-muted">Margen Neto</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis de Cuentas -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Cuentas de Ingresos (4xxx)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Total (S/.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ingresos as $ingreso)
                                <tr>
                                    <td><code>{{ $ingreso->cuenta }}</code></td>
                                    <td>{{ $ingreso->descripcion }}</td>
                                    <td class="text-end">{{ number_format($ingreso->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-minus-circle me-2"></i>
                        Cuentas de Gastos (5xxx)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Total (S/.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gastos as $gasto)
                                <tr>
                                    <td><code>{{ $gasto->cuenta }}</code></td>
                                    <td>{{ $gasto->descripcion }}</td>
                                    <td class="text-end">{{ number_format($gasto->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparación con Período Anterior -->
    @if(isset($comparacion))
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>
                Comparación con Período Anterior
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Ventas</h6>
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar bg-success" style="width: 100%">
                            <span class="text-dark">Actual: {{ number_format($comparacion['actual']['ventas_netas'], 2) }}</span>
                        </div>
                    </div>
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar bg-secondary" style="width: {{ ($comparacion['anterior']['ventas_netas'] / $comparacion['actual']['ventas_netas']) * 100 }}%">
                            <span class="text-dark">Anterior: {{ number_format($comparacion['anterior']['ventas_netas'], 2) }}</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Variación: 
                            <span class="{{ $comparacion['variacion_ventas'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $comparacion['variacion_ventas'] >= 0 ? '+' : '' }}{{ number_format($comparacion['variacion_ventas'], 2) }}%
                            </span>
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6>Utilidad Operativa</h6>
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar bg-success" style="width: 100%">
                            <span class="text-dark">Actual: {{ number_format($comparacion['actual']['utilidad_operativa'], 2) }}</span>
                        </div>
                    </div>
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar bg-secondary" style="width: {{ abs($comparacion['anterior']['utilidad_operativa'] / $comparacion['actual']['utilidad_operativa']) * 100 }}%">
                            <span class="text-dark">Anterior: {{ number_format($comparacion['anterior']['utilidad_operativa'], 2) }}</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Variación: 
                            <span class="{{ $comparacion['variacion_utilidad'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $comparacion['variacion_utilidad'] >= 0 ? '+' : '' }}{{ number_format($comparacion['variacion_utilidad'], 2) }}%
                            </span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Botones de Acción -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('contador.estado-resultados.periodos') }}" class="btn btn-info me-2">
                                <i class="fas fa-calendar-alt"></i> Ver Períodos
                            </a>
                            <a href="{{ route('contador.estado-resultados.comparativo') }}" class="btn btn-warning me-2">
                                <i class="fas fa-comparison"></i> Comparativo
                            </a>
                            <a href="{{ route('contador.estado-resultados.farmaceutico') }}" class="btn btn-success">
                                <i class="fas fa-pills"></i> Análisis Farmacéutico
                            </a>
                        </div>
                        <div>
                            <a href="{{ route('contador.estado-resultados.detalle', ['cuenta' => 'all']) }}" class="btn btn-outline-primary me-2">
                                <i class="fas fa-search"></i> Ver Detalles
                            </a>
                            <a href="{{ route('contador.estado-resultados.exportar') }}" class="btn btn-primary">
                                <i class="fas fa-download"></i> Exportar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de barras para comparación de períodos
    const ctxComparacion = document.getElementById('chartComparacion');
    if (ctxComparacion) {
        new Chart(ctxComparacion, {
            type: 'bar',
            data: {
                labels: ['Ventas', 'Costo Ventas', 'Utilidad Bruta', 'Gastos', 'Utilidad Neta'],
                datasets: [{
                    label: 'Período Actual',
                    data: [
                        {{ $comparacion['actual']['ventas_netas'] ?? 0 }},
                        {{ $comparacion['actual']['costo_ventas'] ?? 0 }},
                        {{ $comparacion['actual']['utilidad_bruta'] ?? 0 }},
                        {{ $comparacion['actual']['gastos_operativos'] ?? 0 }},
                        {{ $comparacion['actual']['utilidad_operativa'] ?? 0 }}
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'Período Anterior',
                    data: [
                        {{ $comparacion['anterior']['ventas_netas'] ?? 0 }},
                        {{ $comparacion['anterior']['costo_ventas'] ?? 0 }},
                        {{ $comparacion['anterior']['utilidad_bruta'] ?? 0 }},
                        {{ $comparacion['anterior']['gastos_operativos'] ?? 0 }},
                        {{ $comparacion['anterior']['utilidad_operativa'] ?? 0 }}
                    ],
                    backgroundColor: 'rgba(153, 102, 255, 0.8)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/. ' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Comparación de Períodos'
                    }
                }
            }
        });
    }
});
</script>
@endsection