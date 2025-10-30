@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('contador.estado-resultados.index') }}">Estado de Resultados</a></li>
            <li class="breadcrumb-item active"><i class="fas fa-comparison"></i> Análisis Comparativo</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary">
                <i class="fas fa-comparison me-2"></i>
                Estado de Resultados Comparativo
            </h2>
            <p class="text-muted mb-0">Comparación de períodos contables</p>
        </div>
        <div class="text-end">
            <small class="text-muted">Análisis de variaciones y tendencias</small>
        </div>
    </div>

    <!-- Filtros de Períodos -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>
                Selección de Períodos
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('contador.estado-resultados.comparativo') }}">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Período Actual</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" value="{{ $periodos['actual_mensual']['inicio'] }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control" value="{{ $periodos['actual_mensual']['fin'] }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-secondary">Período Anterior</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" name="anterior_inicio" class="form-control" value="{{ $periodos['anterior_mensual']['inicio'] }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" name="anterior_fin" class="form-control" value="{{ $periodos['anterior_mensual']['fin'] }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Actualizar Comparación
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetFiltros()">
                        <i class="fas fa-undo"></i> Restablecer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen de Variaciones -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Variación Ventas</h5>
                            @php
                                $variacionVentas = isset($variaciones['mensual']) ? $variaciones['mensual']['ventas'] : 0;
                            @endphp
                            <h3 class="text-white">{{ $variacionVentas >= 0 ? '+' : '' }}{{ number_format($variacionVentas, 1) }}%</h3>
                            <small>{{ $variacionVentas >= 0 ? 'Incremento' : 'Decremento' }}</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
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
                            <h5 class="card-title mb-0">Variación Utilidad</h5>
                            @php
                                $variacionUtilidad = isset($variaciones['mensual']) ? $variaciones['mensual']['utilidad'] : 0;
                            @endphp
                            <h3 class="text-white">{{ $variacionUtilidad >= 0 ? '+' : '' }}{{ number_format($variacionUtilidad, 1) }}%</h3>
                            <small>{{ $variacionUtilidad >= 0 ? 'Mejora' : 'Deterioro' }}</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
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
                            <h5 class="card-title mb-0">Diferencia Ventas</h5>
                            @php
                                $diferenciaVentas = $periodos['actual_mensual']['resultados']['ventas_netas'] - $periodos['anterior_mensual']['resultados']['ventas_netas'];
                            @endphp
                            <h3 class="text-white">{{ number_format($diferenciaVentas, 0) }}</h3>
                            <small>S/. (Absoluta)</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-plus-minus fa-2x opacity-75"></i>
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
                            <h5 class="card-title mb-0">Diferencia Utilidad</h5>
                            @php
                                $diferenciaUtilidad = $periodos['actual_mensual']['resultados']['utilidad_operativa'] - $periodos['anterior_mensual']['resultados']['utilidad_operativa'];
                            @endphp
                            <h3 class="text-white">{{ number_format($diferenciaUtilidad, 0) }}</h3>
                            <small>S/. (Absoluta)</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-balance-scale fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico Comparativo -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar me-2"></i>
                Comparación Visual de Períodos
            </h5>
        </div>
        <div class="card-body">
            <canvas id="chartComparativo" height="100"></canvas>
        </div>
    </div>

    <!-- Tabla Comparativa Detallada -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>
                Análisis Detallado de Variaciones
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Concepto</th>
                            <th class="text-end">Período Anterior</th>
                            <th class="text-end">Período Actual</th>
                            <th class="text-end">Variación Absoluta</th>
                            <th class="text-end">Variación %</th>
                            <th class="text-center">Tendencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Ventas -->
                        <tr class="table-success">
                            <td colspan="6"><strong>INGRESOS</strong></td>
                        </tr>
                        <tr>
                            <td>Ventas Netas</td>
                            <td class="text-end">{{ number_format($periodos['anterior_mensual']['resultados']['ventas_netas'], 2) }}</td>
                            <td class="text-end">{{ number_format($periodos['actual_mensual']['resultados']['ventas_netas'], 2) }}</td>
                            <td class="text-end {{ $diferenciaVentas >= 0 ? 'text-success' : 'text-danger' }}">
                                <strong>{{ $diferenciaVentas >= 0 ? '+' : '' }}{{ number_format($diferenciaVentas, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <span class="badge {{ $variacionVentas >= 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $variacionVentas >= 0 ? '+' : '' }}{{ number_format($variacionVentas, 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <i class="fas fa-arrow-{{ $variacionVentas >= 0 ? 'up text-success' : 'down text-danger' }}"></i>
                            </td>
                        </tr>
                        
                        <!-- Costos -->
                        <tr class="table-warning">
                            <td colspan="6"><strong>COSTOS Y GASTOS</strong></td>
                        </tr>
                        <tr>
                            <td>Costo de Ventas</td>
                            <td class="text-end">{{ number_format($periodos['anterior_mensual']['resultados']['costo_ventas'], 2) }}</td>
                            <td class="text-end">{{ number_format($periodos['actual_mensual']['resultados']['costo_ventas'], 2) }}</td>
                            @php
                                $diferenciaCosto = $periodos['actual_mensual']['resultados']['costo_ventas'] - $periodos['anterior_mensual']['resultados']['costo_ventas'];
                                $variacionCosto = $periodos['anterior_mensual']['resultados']['costo_ventas'] > 0 ? 
                                    (($periodos['actual_mensual']['resultados']['costo_ventas'] - $periodos['anterior_mensual']['resultados']['costo_ventas']) / $periodos['anterior_mensual']['resultados']['costo_ventas']) * 100 : 0;
                            @endphp
                            <td class="text-end {{ $diferenciaCosto >= 0 ? 'text-danger' : 'text-success' }}">
                                <strong>{{ $diferenciaCosto >= 0 ? '+' : '' }}{{ number_format($diferenciaCosto, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <span class="badge {{ $diferenciaCosto >= 0 ? 'bg-danger' : 'bg-success' }}">
                                    {{ $diferenciaCosto >= 0 ? '+' : '' }}{{ number_format($variacionCosto, 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <i class="fas fa-arrow-{{ $diferenciaCosto >= 0 ? 'up text-danger' : 'down text-success' }}"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>Gastos Operativos</td>
                            <td class="text-end">{{ number_format($periodos['anterior_mensual']['resultados']['gastos_operativos'], 2) }}</td>
                            <td class="text-end">{{ number_format($periodos['actual_mensual']['resultados']['gastos_operativos'], 2) }}</td>
                            @php
                                $diferenciaGastos = $periodos['actual_mensual']['resultados']['gastos_operativos'] - $periodos['anterior_mensual']['resultados']['gastos_operativos'];
                                $variacionGastos = $periodos['anterior_mensual']['resultados']['gastos_operativos'] > 0 ? 
                                    (($periodos['actual_mensual']['resultados']['gastos_operativos'] - $periodos['anterior_mensual']['resultados']['gastos_operativos']) / $periodos['anterior_mensual']['resultados']['gastos_operativos']) * 100 : 0;
                            @endphp
                            <td class="text-end {{ $diferenciaGastos >= 0 ? 'text-danger' : 'text-success' }}">
                                <strong>{{ $diferenciaGastos >= 0 ? '+' : '' }}{{ number_format($diferenciaGastos, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <span class="badge {{ $diferenciaGastos >= 0 ? 'bg-danger' : 'bg-success' }}">
                                    {{ $diferenciaGastos >= 0 ? '+' : '' }}{{ number_format($variacionGastos, 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <i class="fas fa-arrow-{{ $diferenciaGastos >= 0 ? 'up text-danger' : 'down text-success' }}"></i>
                            </td>
                        </tr>
                        
                        <!-- Utilidades -->
                        <tr class="{{ $diferenciaUtilidad >= 0 ? 'table-success' : 'table-danger' }}">
                            <td><strong>UTILIDAD OPERATIVA</strong></td>
                            <td class="text-end">{{ number_format($periodos['anterior_mensual']['resultados']['utilidad_operativa'], 2) }}</td>
                            <td class="text-end">{{ number_format($periodos['actual_mensual']['resultados']['utilidad_operativa'], 2) }}</td>
                            <td class="text-end">
                                <strong>{{ $diferenciaUtilidad >= 0 ? '+' : '' }}{{ number_format($diferenciaUtilidad, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <span class="badge {{ $variacionUtilidad >= 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $variacionUtilidad >= 0 ? '+' : '' }}{{ number_format($variacionUtilidad, 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <i class="fas fa-arrow-{{ $variacionUtilidad >= 0 ? 'up text-success' : 'down text-danger' }}"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Comparación de Márgenes -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-percentage me-2"></i>
                        Evolución de Márgenes
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $margenAnterior = $periodos['anterior_mensual']['resultados']['ventas_netas'] > 0 ? 
                            ($periodos['anterior_mensual']['resultados']['utilidad_operativa'] / $periodos['anterior_mensual']['resultados']['ventas_netas']) * 100 : 0;
                        
                        $margenActual = $periodos['actual_mensual']['resultados']['ventas_netas'] > 0 ? 
                            ($periodos['actual_mensual']['resultados']['utilidad_operativa'] / $periodos['actual_mensual']['resultados']['ventas_netas']) * 100 : 0;
                        
                        $variacionMargen = $margenAnterior != 0 ? (($margenActual - $margenAnterior) / abs($margenAnterior)) * 100 : 0;
                    @endphp
                    
                    <div class="mb-4">
                        <h6>Margen Operativo</h6>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center p-3 border rounded">
                                    <h4 class="text-secondary">{{ number_format($margenAnterior, 1) }}%</h4>
                                    <small class="text-muted">Anterior</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 border rounded {{ $margenActual >= $margenAnterior ? 'border-success' : 'border-danger' }}">
                                    <h4 class="{{ $margenActual >= $margenAnterior ? 'text-success' : 'text-danger' }}">{{ number_format($margenActual, 1) }}%</h4>
                                    <small class="text-muted">Actual</small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-center">
                            <span class="badge {{ $variacionMargen >= 0 ? 'bg-success' : 'bg-danger' }} fs-6">
                                {{ $variacionMargen >= 0 ? '+' : '' }}{{ number_format($variacionMargen, 1) }}% de cambio
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-0">
                        <h6>Interpretación:</h6>
                        @if($variacionMargen > 5)
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Mejora significativa</strong> en el margen operativo del {{ number_format($variacionMargen, 1) }}%
                            </div>
                        @elseif($variacionMargen > 0)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Mejora leve</strong> en el margen operativo del {{ number_format($variacionMargen, 1) }}%
                            </div>
                        @elseif($variacionMargen < -5)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Deterioro significativo</strong> en el margen operativo del {{ number_format(abs($variacionMargen), 1) }}%
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Deterioro leve</strong> en el margen operativo del {{ number_format(abs($variacionMargen), 1) }}%
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Análisis de Desempeño
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $mejoraVentas = $variacionVentas > 0;
                        $mejoraUtilidad = $variacionUtilidad > 0;
                        $controlCostos = $variacionCosto < $variacionVentas; // Costos aumentan menos que ventas
                    @endphp
                    
                    <div class="mb-3">
                        <h6>Rendimiento de Ventas:</h6>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-{{ $mejoraVentas ? 'thumbs-up text-success' : 'thumbs-down text-danger' }} fa-2x me-3"></i>
                            <div>
                                <p class="mb-1">
                                    <strong>{{ $mejoraVentas ? 'Incremento' : 'Decremento' }} del {{ abs($variacionVentas) }}%</strong>
                                </p>
                                <small class="text-muted">
                                    @if($mejoraVentas)
                                        Las ventas muestran crecimiento, indicando buena gestión comercial
                                    @else
                                        Las ventas disminuyeron, requiere análisis de causas y estrategias
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Control de Costos:</h6>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-{{ $controlCostos ? 'check-circle text-success' : 'exclamation-triangle text-warning' }} fa-2x me-3"></i>
                            <div>
                                <p class="mb-1">
                                    <strong>{{ $controlCostos ? 'Controlado' : 'Descontrolado' }}</strong>
                                </p>
                                <small class="text-muted">
                                    @if($controlCostos)
                                        Los costos aumentaron menos que las ventas (buen control)
                                    @else
                                        Los costos aumentaron más que las ventas (revisar eficiencia)
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-0">
                        <h6>Desempeño General:</h6>
                        <div class="alert {{ $mejoraVentas && $mejoraUtilidad ? 'alert-success' : ($mejoraVentas ? 'alert-info' : 'alert-warning') }}">
                            <i class="fas fa-star"></i>
                            @if($mejoraVentas && $mejoraUtilidad)
                                <strong>Desempeño Excelente:</strong> Mejora en ventas y utilidad
                            @elseif($mejoraVentas)
                                <strong>Desempeño Positivo:</strong> Crecimiento en ventas aunque menor en utilidad
                            @elseif($mejoraUtilidad)
                                <strong>Desempeño Mixto:</strong> Eficiencia mejorada pero menor volumen
                            @else
                                <strong>Desempeño a Mejorar:</strong> Requiere atención en ambos aspectos
                            @endif
                        </div>
                    </div>
                </div>
            </div>
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
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos para el gráfico comparativo
    const labels = ['Ventas Netas', 'Costo Ventas', 'Utilidad Bruta', 'Gastos Operativos', 'Utilidad Operativa'];
    
    const anteriorData = [
        {{ $periodos['anterior_mensual']['resultados']['ventas_netas'] }},
        {{ $periodos['anterior_mensual']['resultados']['costo_ventas'] }},
        {{ $periodos['anterior_mensual']['resultados']['utilidad_bruta'] }},
        {{ $periodos['anterior_mensual']['resultados']['gastos_operativos'] }},
        {{ $periodos['anterior_mensual']['resultados']['utilidad_operativa'] }}
    ];
    
    const actualData = [
        {{ $periodos['actual_mensual']['resultados']['ventas_netas'] }},
        {{ $periodos['actual_mensual']['resultados']['costo_ventas'] }},
        {{ $periodos['actual_mensual']['resultados']['utilidad_bruta'] }},
        {{ $periodos['actual_mensual']['resultados']['gastos_operativos'] }},
        {{ $periodos['actual_mensual']['resultados']['utilidad_operativa'] }}
    ];

    // Gráfico de barras comparativo
    const ctx = document.getElementById('chartComparativo');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Período Anterior',
                    data: anteriorData,
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }, {
                    label: 'Período Actual',
                    data: actualData,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Comparación de Períodos - Estado de Resultados'
                    },
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/. ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});

function resetFiltros() {
    // Resetear filtros a valores por defecto
    const hoy = new Date();
    const mesAnterior = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
    const finMesAnterior = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
    
    document.querySelector('input[name="fecha_inicio"]').value = hoy.getFullYear() + '-' + 
        String(hoy.getMonth() + 1).padStart(2, '0') + '-01';
    document.querySelector('input[name="fecha_fin"]').value = hoy.getFullYear() + '-' + 
        String(hoy.getMonth() + 1).padStart(2, '0') + '-' + String(hoy.getDate()).padStart(2, '0');
    
    document.querySelector('input[name="anterior_inicio"]').value = mesAnterior.getFullYear() + '-' + 
        String(mesAnterior.getMonth() + 1).padStart(2, '0') + '-01';
    document.querySelector('input[name="anterior_fin"]').value = finMesAnterior.getFullYear() + '-' + 
        String(finMesAnterior.getMonth() + 1).padStart(2, '0') + '-' + String(finMesAnterior.getDate()).padStart(2, '0');
}
</script>
@endsection