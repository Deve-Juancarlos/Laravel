@extends('layouts.app')

@section('title', "Detalle de Cuentas - Balance de Comprobación")

@push('styles')
    {{-- Referencia al CSS que creamos --}}
    <link href="{{ asset('css/contabilidad/ver-detalle.css') }}" rel="stylesheet">
@endpush

{{-- 1. Título de la Página --}}
@section('page-title')
    <div>
        <h1><i class="fas fa-list me-2"></i>Detalle de Cuentas</h1>
        <p class="text-muted">Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
    </div>
    <div class="text-end">
        <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver al Balance
        </a>
    </div>
@endsection

{{-- 2. Breadcrumbs --}}
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.balance-comprobacion.index') }}">Balance de Comprobación</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detalle de Cuentas</li>
@endsection

{{-- 3. Contenido Principal --}}
@section('content')
<div class="detalle-cuentas-view">

    {{-- Buscador de cuentas --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control search-input" id="searchCuenta" placeholder="Buscar cuenta...">
        </div>
    </div>

    @if($movimientos->count() > 0)
        @php
            $cuentas = $movimientos->groupBy('cuenta');
        @endphp

        @foreach($cuentas as $cuenta => $movs)
        @php
            $totalDebito = $movs->sum('debito');
            $totalCredito = $movs->sum('credito');
            $saldoFinal = $totalDebito - $totalCredito;
            $canvasId = 'saldoChart_' . Str::slug($cuenta);
        @endphp

        <!-- Bloque de cada cuenta -->
        <div class="card mb-4 cuenta-card shadow-sm" data-cuenta="{{ strtolower($cuenta) }} {{ strtolower($movs->first()->nombre_cuenta ?? '') }}">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>Cuenta: {{ $cuenta }} - {{ $movs->first()->nombre_cuenta ?? 'Sin Nombre' }}</h5>
                <a href="{{ route('contador.libro-mayor.cuenta', $cuenta) }}" class="btn btn-sm btn-light" target="_blank" title="Ver en Libro Mayor">
                    <i class="fas fa-book-open me-1"></i> Ver Mayor
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive mb-3">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Asiento</th>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th class="text-end">Débito</th>
                                <th class="text-end">Crédito</th>
                                <th class="text-end">Saldo Acumulado</th>
                                <th>Auxiliar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($movs as $movimiento)
                            <tr>
                                <td>
                                    <a href="{{ route('contador.libro-diario.show', $movimiento->asiento_id) }}" target="_blank" class="badge bg-primary-soft text-primary text-decoration-none">
                                        {{ $movimiento->numero }}
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                                <td>{{ Str::limit($movimiento->concepto, 50) }}</td>
                                <td class="text-end text-success">{{ $movimiento->debito > 0 ? 'S/ ' . number_format($movimiento->debito, 2) : '-' }}</td>
                                <td class="text-end text-danger">{{ $movimiento->credito > 0 ? 'S/ ' . number_format($movimiento->credito, 2) : '-' }}</td>
                                <td class="text-end fw-bold {{ $movimiento->saldo_acumulado >= 0 ? 'text-dark' : 'text-danger' }}">
                                    S/ {{ number_format($movimiento->saldo_acumulado, 2) }}
                                </td>
                                <td>{{ $movimiento->auxiliar ?: '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-dark">
                            <tr class="fw-bold">
                                <td colspan="3"><strong>TOTALES DE LA CUENTA</strong></td>
                                <td class="text-end"><strong>S/ {{ number_format($totalDebito, 2) }}</strong></td>
                                <td class="text-end"><strong>S/ {{ number_format($totalCredito, 2) }}</strong></td>
                                <td class="text-end"><strong>S/ {{ number_format($saldoFinal, 2) }}</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Gráfico de evolución del saldo -->
                <div class="chart-container mb-3">
                    <canvas id="{{ $canvasId }}" height="100"></canvas>
                </div>
            </div>
        </div>
        @endforeach

    @else
        <div class="text-center p-5 card shadow-sm">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <h4>No hay cuentas registradas en el período seleccionado</h4>
        </div>
    @endif

    <!-- Botones de navegación -->
    <div class="row mt-4">
        <div class="col-md-12 text-center">
            <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Volver al Balance
            </a>
            <a href="{{ route('contador.balance-comprobacion.verificar') }}" class="btn btn-warning ms-2">
                <i class="fas fa-check-circle me-2"></i>Verificar Integridad
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Buscador por cuenta
    const searchInput = document.getElementById('searchCuenta');
    searchInput.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('.cuenta-card').forEach(card => {
            const cuenta = card.getAttribute('data-cuenta');
            card.style.display = cuenta.includes(filter) ? '' : 'none';
        });
    });

    @foreach($cuentas as $cuenta => $movs)
        const ctx_{{ Str::slug($cuenta) }} = document.getElementById('saldoChart_{{ Str::slug($cuenta) }}').getContext('2d');
        const labels_{{ Str::slug($cuenta) }} = @json($movs->pluck('numero'));
        const saldos_{{ Str::slug($cuenta) }} = @json($movs->pluck('saldo_acumulado'));

        new Chart(ctx_{{ Str::slug($cuenta) }}, {
            type: 'line',
            data: {
                labels: labels_{{ Str::slug($cuenta) }},
                datasets: [{
                    label: 'Saldo Acumulado',
                    data: saldos_{{ Str::slug($cuenta) }},
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
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
    @endforeach
});
</script>
@endpush
