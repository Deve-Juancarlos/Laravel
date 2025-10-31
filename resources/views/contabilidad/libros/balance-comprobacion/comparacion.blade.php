@extends('layouts.app')

@section('title', 'Comparación Balance - Balance de Comprobación')

@push('styles')
    {{-- Referencia al CSS que crearemos --}}
    <link href="{{ asset('css/contabilidad/comparacion-balance.css') }}" rel="stylesheet">
@endpush

{{-- 1. Título de la Página --}}
@section('page-title')
    <div>
        <h1><i class="fas fa-exchange-alt me-2"></i>Comparación de Balance</h1>
        <p class="text-muted">Análisis comparativo entre períodos contables</p>
    </div>
@endsection

{{-- 2. Breadcrumbs --}}
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.balance-comprobacion.index') }}">Balance de Comprobación</a></li>
    <li class="breadcrumb-item active" aria-current="page">Comparación</li>
@endsection

{{-- 3. Contenido Principal --}}
@section('content')
<div class="comparacion-balance-view">
    
    <!-- Filtros de períodos -->
    <div class="card mb-4 shadow-sm filters-card">
        <div class="card-header">
            <h6><i class="fas fa-calendar-alt me-2"></i>Configuración de Períodos</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('contador.balance-comprobacion.comparacion') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Período Actual - Inicio</label>
                        {{-- CORRECCIÓN: El name debe ser 'fecha_inicio_actual' como espera el Service --}}
                        <input type="date" name="fecha_inicio_actual" class="form-control" value="{{ $periodoActual['inicio'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Período Actual - Fin</label>
                         {{-- CORRECCIÓN: El name debe ser 'fecha_fin_actual' como espera el Service --}}
                        <input type="date" name="fecha_fin_actual" class="form-control" value="{{ $periodoActual['fin'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Período Anterior - Inicio</label>
                         {{-- CORRECCIÓN: El name debe ser 'fecha_inicio_anterior' como espera el Service --}}
                        <input type="date" name="fecha_inicio_anterior" class="form-control" value="{{ $periodoAnterior['inicio'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Período Anterior - Fin</label>
                         {{-- CORRECCIÓN: El name debe ser 'fecha_fin_anterior' como espera el Service --}}
                        <input type="date" name="fecha_fin_anterior" class="form-control" value="{{ $periodoAnterior['fin'] }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-2"></i>Generar Comparación
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tarjetas de resumen por período -->
    <div class="row mb-4 g-3">
        <div class="col-md-6">
            <div class="period-card shadow-sm">
                <div class="period-header-actual">
                    <h4>PERÍODO ACTUAL</h4>
                    <small>{{ \Carbon\Carbon::parse($periodoActual['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($periodoActual['fin'])->format('d/m/Y') }}</small>
                </div>
                <div class="period-body">
                    <div class="row text-center g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <h6 class="text-primary">TOTAL DEUDOR</h6>
                                <h4 class="text-success">S/ {{ number_format($balanceActual['total_deudor'], 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <h6 class="text-primary">TOTAL ACREEDOR</h6>
                                <h4 class="text-danger">S/ {{ number_format($balanceActual['total_acreedor'], 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12 text-center">
                            @if($balanceActual['cuadra'])
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>CUADRA
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>NO CUADRA
                                </span>
                            @endif
                            <small class="d-block text-muted mt-2">
                                Diferencia: S/ {{ number_format($balanceActual['diferencia'], 2) }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="period-card shadow-sm">
                <div class="period-header-anterior">
                    <h4>PERÍODO ANTERIOR</h4>
                    <small>{{ \Carbon\Carbon::parse($periodoAnterior['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($periodoAnterior['fin'])->format('d/m/Y') }}</small>
                </div>
                <div class="period-body">
                    <div class="row text-center g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <h6 class="text-secondary">TOTAL DEUDOR</h6>
                                <h4 class="text-success">S/ {{ number_format($balanceAnterior['total_deudor'], 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <h6 class="text-secondary">TOTAL ACREEDOR</h6>
                                <h4 class="text-danger">S/ {{ number_format($balanceAnterior['total_acreedor'], 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12 text-center">
                            @if($balanceAnterior['cuadra'])
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>CUADRA
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>NO CUADRA
                                </span>
                            @endif
                            <small class="d-block text-muted mt-2">
                                Diferencia: S/ {{ number_format($balanceAnterior['diferencia'], 2) }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de comparación detallada -->
    <div class="comparison-table card shadow-sm">
        <div class="card-header">
            <h5><i class="fas fa-table me-2"></i>Comparación Detallada de Balances</h5>
        </div>
        
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th class="text-center">Período Actual</th>
                        <th class="text-center">Período Anterior</th>
                        <th class="text-center">Variación</th>
                        <th class="text-center">% Cambio</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="total-actual">
                        <td><strong>Total Deudor</strong></td>
                        <td class="text-end"><strong>S/ {{ number_format($balanceActual['total_deudor'], 2) }}</strong></td>
                        <td class="text-end total-anterior">S/ {{ number_format($balanceAnterior['total_deudor'], 2) }}</td>
                        <td class="text-end">
                            @php
                                $variacionDeudor = $balanceActual['total_deudor'] - $balanceAnterior['total_deudor'];
                                $porcentajeDeudor = $balanceAnterior['total_deudor'] > 0 ? ($variacionDeudor / $balanceAnterior['total_deudor']) * 100 : 0;
                            @endphp
                            <strong>{{ $variacionDeudor >= 0 ? '+' : '' }}S/ {{ number_format($variacionDeudor, 2) }}</strong>
                        </td>
                        <td class="text-end">
                            <strong>{{ $porcentajeDeudor >= 0 ? '+' : '' }}{{ number_format($porcentajeDeudor, 1) }}%</strong>
                        </td>
                        <td class="text-center">
                            @if($balanceActual['cuadra'] && $balanceAnterior['cuadra'])
                                <span class="badge bg-success">Cuadran Ambos</span>
                            @else
                                <span class="badge bg-warning text-dark">Revisar</span>
                            @endif
                        </td>
                    </tr>
                    
                    <tr>
                        <td><strong>Total Acreedor</strong></td>
                        <td class="text-end"><strong>S/ {{ number_format($balanceActual['total_acreedor'], 2) }}</strong></td>
                        <td class="text-end total-anterior">S/ {{ number_format($balanceAnterior['total_acreedor'], 2) }}</td>
                        <td class="text-end">
                            @php
                                $variacionAcreedor = $balanceActual['total_acreedor'] - $balanceAnterior['total_acreedor'];
                                $porcentajeAcreedor = $balanceAnterior['total_acreedor'] > 0 ? ($variacionAcreedor / $balanceAnterior['total_acreedor']) * 100 : 0;
                            @endphp
                            <strong>{{ $variacionAcreedor >= 0 ? '+' : '' }}S/ {{ number_format($variacionAcreedor, 2) }}</strong>
                        </td>
                        <td class="text-end">
                            <strong>{{ $porcentajeAcreedor >= 0 ? '+' : '' }}{{ number_format($porcentajeAcreedor, 1) }}%</strong>
                        </td>
                        <td class="text-center">
                            @if($balanceActual['cuadra'] && $balanceAnterior['cuadra'])
                                <span class="badge bg-success">Cuadran Ambos</span>
                            @else
                                <span class="badge bg-warning text-dark">Revisar</span>
                            @endif
                        </td>
                    </tr>
                    
                    <tr class="total-actual">
                        <td><strong>Diferencia</strong></td>
                        <td class="text-end"><strong>S/ {{ number_format($balanceActual['diferencia'], 2) }}</strong></td>
                        <td class="text-end total-anterior">S/ {{ number_format($balanceAnterior['diferencia'], 2) }}</td>
                        <td class="text-end">
                            @php
                                $variacionDiff = $balanceActual['diferencia'] - $balanceAnterior['diferencia'];
                                $porcentajeDiff = $balanceAnterior['diferencia'] > 0 ? ($variacionDiff / $balanceAnterior['diferencia']) * 100 : 0;
                            @endphp
                            <strong>{{ $variacionDiff >= 0 ? '+' : '' }}S/ {{ number_format($variacionDiff, 2) }}</strong>
                        </td>
                        <td class="text-end">
                            <strong>{{ $porcentajeDiff >= 0 ? '+' : '' }}{{ number_format($porcentajeDiff, 1) }}%</strong>
                        </td>
                        <td class="text-center">
                            @if($balanceActual['cuadra'] && $balanceAnterior['cuadra'])
                                <span class="badge bg-success">Perfecto</span>
                            @else
                                <span class="badge bg-danger">Error</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Gráfico comparativo -->
    <div class="chart-container card shadow-sm">
        <div class="card-header">
             <h6><i class="fas fa-chart-bar me-2"></i>Evolución Comparativa de Balances</h6>
        </div>
        <div class="card-body">
            <canvas id="comparisonChart" height="100"></canvas>
        </div>
    </div>

    <!-- Acciones -->
    <div class="row mt-4">
        <div class="col-md-12 text-center">
            <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Volver al Balance
            </a>
            <a href="{{ route('contador.balance-comprobacion.verificar', request()->query()) }}" class="btn btn-warning me-2">
                <i class="fas fa-check-circle me-2"></i>Verificar Integridad
            </a>
            <button class="btn btn-success" onclick="exportarComparacion()">
                <i class="fas fa-download me-2"></i>Exportar Comparación
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('comparisonChart').getContext('2d');
const comparisonChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Total Deudor', 'Total Acreedor', 'Diferencia'],
        datasets: [
            {
                label: 'Período Actual',
                data: [{{ $balanceActual['total_deudor'] }}, {{ $balanceActual['total_acreedor'] }}, {{ $balanceActual['diferencia'] }}],
                backgroundColor: '#2563eb',
                borderColor: '#2563eb',
                borderWidth: 1
            },
            {
                label: 'Período Anterior',
                data: [{{ $balanceAnterior['total_deudor'] }}, {{ $balanceAnterior['total_acreedor'] }}, {{ $balanceAnterior['diferencia'] }}],
                backgroundColor: '#64748b',
                borderColor: '#64748b',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                },
                ticks: {
                    callback: function(value) {
                        return 'S/ ' + value.toLocaleString('es-PE');
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 10,
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': S/ '' + context.parsed.y.toLocaleString('es-PE', {minimumFractionDigits: 2});
                    }
                }
            }
        }
    }
});

function exportarComparacion() {
    const params = new URLSearchParams({
        // Corrección: Usar los nombres de filtro del formulario
        fecha_inicio_actual: '{{ $periodoActual['inicio'] }}',
        fecha_fin_actual: '{{ $periodoActual['fin'] }}',
        fecha_inicio_anterior: '{{ $periodoAnterior['inicio'] }}',
        fecha_fin_anterior: '{{ $periodoAnterior['fin'] }}',
        formato: 'comparacion' // ¡Necesitamos agregar esto al servicio!
    });
    // ¡Aún no hemos implementado 'formato=comparacion' en el servicio!
    // Por ahora, lo dejaremos como alerta.
    // window.location.href = `{{ route('contador.balance-comprobacion.exportar') }}?${params}`;
    
    if (typeof Swal !== 'undefined') {
        Swal.fire('Exportación Pendiente', 'La lógica de exportación para "Comparación" aún debe implementarse en el servicio.', 'info');
    } else {
        alert('La lógica de exportación para "Comparación" aún debe implementarse en el servicio.');
    }
}
</script>
@endpush
