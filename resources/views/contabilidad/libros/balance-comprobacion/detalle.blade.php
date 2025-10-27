@extends('layouts.app')

@section('title', "Detalle Cuenta {$cuenta} - Balance de Comprobación")

@section('styles')
<style>
    .detalle-header {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 30px;
    }
    
    .movement-row {
        transition: background-color 0.2s ease;
    }
    
    .movement-row:hover {
        background-color: #f8fafc;
    }
    
    .saldo-acumulado {
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
    }
    
    .saldo-positivo {
        background: #d1fae5;
        color: #065f46;
    }
    
    .saldo-negativo {
        background: #fee2e2;
        color: #991b1b;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="detalle-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-list me-3"></i>Detalle de Cuenta: {{ $cuenta }}</h1>
                <p class="mb-0">Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
            </div>
            <div class="text-end">
                <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Balance
                </a>
            </div>
        </div>
    </div>

    <!-- Resumen de la cuenta -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary">Total Débitos</h5>
                    <h4 class="text-success">S/ {{ number_format($totales['total_debito'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary">Total Créditos</h5>
                    <h4 class="text-danger">S/ {{ number_format($totales['total_credito'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary">Saldo Final</h5>
                    <h4 class="{{ $totales['saldo_final'] >= 0 ? 'text-success' : 'text-danger' }}">
                        S/ {{ number_format($totales['saldo_final'], 2) }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary">Movimientos</h5>
                    <h4>{{ number_format($movimientos->count()) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de movimientos -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>
                Movimientos de la Cuenta {{ $cuenta }}
            </h5>
        </div>
        
        <div class="card-body p-0">
            @if($movimientos->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Asiento</th>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Débito</th>
                            <th>Crédito</th>
                            <th>Saldo Acumulado</th>
                            <th>Auxiliar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movimientos as $movimiento)
                        <tr class="movement-row">
                            <td><strong>{{ $movimiento->numero }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                            <td>{{ Str::limit($movimiento->concepto, 50) }}</td>
                            <td class="text-end text-success">
                                {{ $movimiento->debito > 0 ? 'S/ ' . number_format($movimiento->debito, 2) : '-' }}
                            </td>
                            <td class="text-end text-danger">
                                {{ $movimiento->credito > 0 ? 'S/ ' . number_format($movimiento->credito, 2) : '-' }}
                            </td>
                            <td class="text-end">
                                <span class="saldo-acumulado {{ $movimiento->saldo_acumulado >= 0 ? 'saldo-positivo' : 'saldo-negativo' }}">
                                    S/ {{ number_format($movimiento->saldo_acumulado, 2) }}
                                </span>
                            </td>
                            <td>{{ Str::limit($movimiento->auxiliar ?? '-', 20) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-info">
                        <tr>
                            <td colspan="3"><strong>TOTALES</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($totales['total_debito'], 2) }}</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($totales['total_credito'], 2) }}</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($totales['saldo_final'], 2) }}</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center p-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h4>No hay movimientos registrados</h4>
                <p class="text-muted">Esta cuenta no tiene movimientos en el período seleccionado.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Gráfico de evolución -->
    @if($movimientos->count() > 0)
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Evolución del Saldo Acumulado
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="saldoChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Navegación entre cuentas -->
    <div class="row mt-4">
        <div class="col-md-12 text-center">
            <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al Balance General
            </a>
            <a href="{{ route('contador.balance-comprobacion.verificar') }}" class="btn btn-warning ms-2">
                <i class="fas fa-check-circle me-2"></i>Verificar Balance
            </a>
        </div>
    </div>
</div>

@if($movimientos->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de evolución del saldo
const ctx = document.getElementById('saldoChart').getContext('2d');
const labels = @json($movimientos->pluck('numero'));
const saldos = @json($movimientos->pluck('saldo_acumulado'));

const saldoChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Saldo Acumulado',
            data: saldos,
            borderColor: '#059669',
            backgroundColor: 'rgba(5, 150, 105, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: false,
                ticks: {
                    callback: function(value) {
                        return 'S/ ' + value.toLocaleString('es-PE', {minimumFractionDigits: 2});
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Saldo: S/ ' + context.parsed.y.toLocaleString('es-PE', {minimumFractionDigits: 2});
                    }
                }
            }
        }
    }
});
</script>
@endif
@endsection