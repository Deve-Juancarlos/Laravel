@extends('layouts.app')

@section('title', "Detalle de Cuentas - Balance de Comprobación")

@push('styles')
<link href="{{ asset('css/contabilidad/ver-detalle.css') }}" rel="stylesheet">
<style>
    .search-input {
        max-width: 300px;
        margin-bottom: 20px;
    }
</style>
@endpush
@section('sidebar-menu')
{{-- MENÚ PRINCIPAL --}}
<div class="nav-section">Dashboard</div>
<ul>
    <li><a href="{{ route('dashboard.contador') }}" class="nav-link active">
        <i class="fas fa-chart-pie"></i> Panel Principal
    </a></li>
</ul>

{{-- CONTABILIDAD --}}
<div class="nav-section">Contabilidad</div>
<ul>
    <li>
        <a href="{{ route('contador.libro-diario.index') }}" class="nav-link has-submenu">
            <i class="fas fa-book"></i> Libros Contables
        </a>
        <div class="nav-submenu">
            <a href="{{ route('contador.libro-diario.index') }}" class="nav-link"><i class="fas fa-file-alt"></i> Libro Diario</a>
            <a href="{{ route('contador.libro-mayor.index') }}" class="nav-link"><i class="fas fa-book-open"></i> Libro Mayor</a>
            <a href="{{route('contador.balance-comprobacion.index')}}" class="nav-link"><i class="fas fa-balance-scale"></i> Balance Comprobación</a>    
            <a href="{{ route('contador.estado-resultados.index') }}" class="nav-link"><i class="fas fa-chart-bar"></i> Estados Financieros</a>
        </div>
    </li>
    <li>
        <a href="#" class="nav-link has-submenu">
            <i class="fas fa-file-invoice"></i> Registros
        </a>
        <div class="nav-submenu">
            <a href="#" class="nav-link"><i class="fas fa-shopping-cart"></i> Compras</a>
            <a href="#" class="nav-link"><i class="fas fa-cash-register"></i> Ventas</a>
            <a href="#" class="nav-link"><i class="fas fa-university"></i> Bancos</a>
            <a href="#" class="nav-link"><i class="fas fa-money-bill-wave"></i> Caja</a>
        </div>
    </li>
</ul>

{{-- VENTAS Y COBRANZAS --}}
<div class="nav-section">Ventas & Cobranzas</div>
<ul>
    <li><a href="{{ route('contador.reportes.ventas') }}" class="nav-link">
        <i class="fas fa-chart-line"></i> Análisis Ventas
    </a></li>
    <li><a href="{{ route('contador.reportes.compras') }}" class="nav-link">
        <i class="fas fa-wallet"></i> Cartera
    </a></li>
    <li><a href="{{ route('contador.facturas.create') }}" class="nav-link">
        <i class="fas fa-clock"></i> Fact. Pendientes
    </a></li>
    <li><a href="{{ route('contador.facturas.index') }}" class="nav-link">
        <i class="fas fa-exclamation-triangle"></i> Fact. Vencidas
    </a></li>
</ul>

{{-- GESTIÓN --}}
<div class="nav-section">Gestión</div>
<ul>
    <li><a href="{{ route('contador.clientes') }}" class="nav-link">
        <i class="fas fa-users"></i> Clientes
    </a></li>
    <li><a href="{{ route('contador.reportes.medicamentos-controlados') }}" class="nav-link">
        <i class="fas fa-percentage"></i> Márgenes
    </a></li>
    <li><a href="{{ route('contador.reportes.inventario') }}" class="nav-link">
        <i class="fas fa-boxes"></i> Inventario
    </a></li>
</ul>

{{-- REPORTES SUNAT --}}
<div class="nav-section">SUNAT</div>
<ul>
    <li><a href="#" class="nav-link">
        <i class="fas fa-file-invoice-dollar"></i> PLE
    </a></li>
    <li><a href="#" class="nav-link">
        <i class="fas fa-percent"></i> IGV Mensual
    </a></li>
</ul>
@endsection

@section('content')
<div class="detalle-cuentas-view">
    <div class="container-fluid">
        <!-- Header -->
        <div class="detalle-header mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-list me-3"></i>Detalle de Cuentas</h1>
                <p class="mb-0">Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
            </div>
            <div class="text-end">
                <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Balance
                </a>
            </div>
        </div>

        <!-- Buscador de cuentas -->
        <input type="text" class="form-control search-input" id="searchCuenta" placeholder="Buscar cuenta...">

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
            <div class="card mb-5 cuenta-card" data-cuenta="{{ strtolower($cuenta) }}">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>Cuenta: {{ $cuenta }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-3">
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
                                @foreach($movs as $movimiento)
                                <tr>
                                    <td><strong>{{ $movimiento->numero }}</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                                    <td>{{ Str::limit($movimiento->concepto, 50) }}</td>
                                    <td class="text-end text-success">{{ $movimiento->debito > 0 ? 'S/ ' . number_format($movimiento->debito, 2) : '-' }}</td>
                                    <td class="text-end text-danger">{{ $movimiento->credito > 0 ? 'S/ ' . number_format($movimiento->credito, 2) : '-' }}</td>
                                    <td class="text-end {{ $movimiento->saldo_acumulado >= 0 ? 'text-success' : 'text-danger' }}">
                                        S/ {{ number_format($movimiento->saldo_acumulado, 2) }}
                                    </td>
                                    <td>{{ $movimiento->auxiliar ?: '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-info">
                                <tr>
                                    <td colspan="3"><strong>TOTALES</strong></td>
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
            <div class="text-center p-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h4>No hay cuentas registradas en el período seleccionado</h4>
            </div>
        @endif

        <!-- Botones de navegación -->
        <div class="row mt-4">
            <div class="col-md-12 text-center">
                <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Balance General
                </a>
                <a href="{{ route('contador.balance-comprobacion.verificar') }}" class="btn btn-warning ms-2">
                    <i class="fas fa-check-circle me-2"></i>Verificar Integridad
                </a>
            </div>
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
    @endforeach
});
</script>
@endpush
