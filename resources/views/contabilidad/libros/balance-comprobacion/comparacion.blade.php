@extends('layouts.app')

@section('title', 'Comparación Balance - Balance de Comprobación')

@section('styles')
    <link href="{{ asset('css/contabilidad/comparacion-balance.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="comparison-header">
        <h1><i class="fas fa-balance-scale me-3"></i>Comparación de Balance</h1>
        <p class="mb-0">Análisis comparativo entre períodos contables</p>
    </div>

    <!-- Filtros de períodos -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-calendar-alt me-2"></i>Configuración de Períodos
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('contador.balance-comprobacion.comparacion') }}">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Período Actual - Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $periodoActual['inicio'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Período Actual - Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $periodoActual['fin'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Período Anterior - Inicio</label>
                        <input type="date" class="form-control" value="{{ $periodoAnterior['inicio'] }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Período Anterior - Fin</label>
                        <input type="date" class="form-control" value="{{ $periodoAnterior['fin'] }}" readonly>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-2"></i>Generar Comparación
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tarjetas de resumen por período -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="period-card">
                <div class="period-header-actual">
                    <h4>PERÍODO ACTUAL</h4>
                    <small>{{ \Carbon\Carbon::parse($periodoActual['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($periodoActual['fin'])->format('d/m/Y') }}</small>
                </div>
                <div class="period-body">
                    <div class="row text-center">
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
                            <small class="d-block text-muted mt-1">
                                Diferencia: S/ {{ number_format($balanceActual['diferencia'], 2) }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="period-card">
                <div class="period-header-anterior">
                    <h4>PERÍODO ANTERIOR</h4>
                    <small>{{ \Carbon\Carbon::parse($periodoAnterior['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($periodoAnterior['fin'])->format('d/m/Y') }}</small>
                </div>
                <div class="period-body">
                    <div class="row text-center">
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
                            <small class="d-block text-muted mt-1">
                                Diferencia: S/ {{ number_format($balanceAnterior['diferencia'], 2) }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de comparación detallada -->
    <div class="comparison-table">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>
                Comparación Detallada de Balances
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                                <span class="badge bg-warning">Revisar</span>
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
                                <span class="badge bg-warning">Revisar</span>
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
    <div class="chart-container">
        <h6 class="mb-3">
            <i class="fas fa-chart-bar me-2"></i>
            Evolución Comparativa de Balances
        </h6>
        <canvas id="comparisonChart" height="100"></canvas>
    </div>

    <!-- Análisis de variaciones -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Análisis de Variaciones Significativas
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $variaciones = [
                            'deudor' => [
                                'variacion' => $balanceActual['total_deudor'] - $balanceAnterior['total_deudor'],
                                'porcentaje' => $balanceAnterior['total_deudor'] > 0 ? (($balanceActual['total_deudor'] - $balanceAnterior['total_deudor']) / $balanceAnterior['total_deudor']) * 100 : 0,
                                'significado' => $balanceActual['total_deudor'] - $balanceAnterior['total_deudor'] > 0 ? 'Incremento en activos y gastos' : 'Reducción en activos y gastos'
                            ],
                            'acreedor' => [
                                'variacion' => $balanceActual['total_acreedor'] - $balanceAnterior['total_acreedor'],
                                'porcentaje' => $balanceAnterior['total_acreedor'] > 0 ? (($balanceActual['total_acreedor'] - $balanceAnterior['total_acreedor']) / $balanceAnterior['total_acreedor']) * 100 : 0,
                                'significado' => $balanceActual['total_acreedor'] - $balanceAnterior['total_acreedor'] > 0 ? 'Incremento en pasivos, patrimonio e ingresos' : 'Reducción en pasivos, patrimonio e ingresos'
                            ]
                        ];
                    @endphp
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="p-3 border rounded {{ $variaciones['deudor']['variacion'] >= 0 ? 'border-success' : 'border-danger' }}">
                                <h6>Variación Total Deudor</h6>
                                <p class="mb-1">
                                    <strong>{{ $variaciones['deudor']['variacion'] >= 0 ? '+' : '' }}S/ {{ number_format($variaciones['deudor']['variacion'], 2) }}</strong>
                                    ({{ $variaciones['deudor']['porcentaje'] >= 0 ? '+' : '' }}{{ number_format($variaciones['deudor']['porcentaje'], 1) }}%)
                                </p>
                                <small class="text-muted">{{ $variaciones['deudor']['significado'] }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded {{ $variaciones['acreedor']['variacion'] >= 0 ? 'border-success' : 'border-danger' }}">
                                <h6>Variación Total Acreedor</h6>
                                <p class="mb-1">
                                    <strong>{{ $variaciones['acreedor']['variacion'] >= 0 ? '+' : '' }}S/ {{ number_format($variaciones['acreedor']['variacion'], 2) }}</strong>
                                    ({{ $variaciones['acreedor']['porcentaje'] >= 0 ? '+' : '' }}{{ number_format($variaciones['acreedor']['porcentaje'], 1) }}%)
                                </p>
                                <small class="text-muted">{{ $variaciones['acreedor']['significado'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="row mt-4">
        <div class="col-md-12 text-center">
            <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Volver al Balance
            </a>
            <a href="{{ route('contador.balance-comprobacion.verificar') }}" class="btn btn-warning me-2">
                <i class="fas fa-check-circle me-2"></i>Verificar Integridad
            </a>
            <button class="btn btn-success" onclick="exportarComparacion()">
                <i class="fas fa-download me-2"></i>Exportar Comparación
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico comparativo
const ctx = document.getElementById('comparisonChart').getContext('2d');
const comparisonChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Total Deudor', 'Total Acreedor', 'Diferencia'],
        datasets: [
            {
                label: 'Período Actual',
                data: [{{ $balanceActual['total_deudor'] }}, {{ $balanceActual['total_acreedor'] }}, {{ $balanceActual['diferencia'] }}],
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            },
            {
                label: 'Período Anterior',
                data: [{{ $balanceAnterior['total_deudor'] }}, {{ $balanceAnterior['total_acreedor'] }}, {{ $balanceAnterior['diferencia'] }}],
                backgroundColor: 'rgba(107, 114, 128, 0.8)',
                borderColor: 'rgba(107, 114, 128, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'S/ ' + value.toLocaleString('es-PE');
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': S/ ' + context.parsed.y.toLocaleString('es-PE', {minimumFractionDigits: 2});
                    }
                }
            }
        }
    }
});

function exportarComparacion() {
    const params = new URLSearchParams({
        fecha_inicio_actual: '{{ $periodoActual['inicio'] }}',
        fecha_fin_actual: '{{ $periodoActual['fin'] }}',
        fecha_inicio_anterior: '{{ $periodoAnterior['inicio'] }}',
        fecha_fin_anterior: '{{ $periodoAnterior['fin'] }}',
        formato: 'comparacion'
    });
    window.location.href = `{{ route('contador.balance-comprobacion.exportar') }}?${params}`;
}
</script>
@endsection