@extends('layouts.contador')

@section('title', 'Balance General - SIFANO')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('estados-financieros') }}">Estados Financieros</a></li>
    <li class="breadcrumb-item active">Balance General</li>
@endsection

@section('contador-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-chart-pie text-success me-2"></i>
        Balance General
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success" onclick="exportarBalance()">
            <i class="fas fa-download me-2"></i>
            Exportar PDF
        </button>
        <button class="btn btn-outline-primary" onclick="imprimirBalance()">
            <i class="fas fa-print me-2"></i>
            Imprimir
        </button>
        <button class="btn btn-outline-info" onclick="compararConPeriodoAnterior()">
            <i class="fas fa-balance-scale me-2"></i>
            Comparar
        </button>
        <a href="{{ route('estados-financieros') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Volver
        </a>
    </div>
</div>

<!-- Filtros del Balance -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            Configuración del Balance
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Ejercicio</label>
                <select name="ejercicio" class="form-select">
                    @for($year = date('Y'); $year >= date('Y') - 3; $year--)
                        <option value="{{ $year }}" {{ request('ejercicio', date('Y')) == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha al</label>
                <input type="date" name="fecha_corte" class="form-control" 
                       value="{{ request('fecha_corte', date('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Moneda</label>
                <select name="moneda" class="form-select">
                    <option value="PEN" {{ request('moneda', 'PEN') === 'PEN' ? 'selected' : '' }}>Soles (PEN)</option>
                    <option value="USD" {{ request('moneda') === 'USD' ? 'selected' : '' }}>Dólares (USD)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Comparar con</label>
                <select name="comparar_con" class="form-select">
                    <option value="">Sin comparación</option>
                    <option value="periodo_anterior" {{ request('comparar_con') === 'periodo_anterior' ? 'selected' : '' }}>
                        Período Anterior
                    </option>
                    <option value="ejercicio_anterior" {{ request('comparar_con') === 'ejercicio_anterior' ? 'selected' : '' }}>
                        Ejercicio Anterior
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nivel de Detalle</label>
                <select name="nivel_detalle" class="form-select">
                    <option value="resumido" {{ request('nivel_detalle', 'resumido') === 'resumido' ? 'selected' : '' }}>
                        Resumido
                    </option>
                    <option value="detallado" {{ request('nivel_detalle') === 'detallado' ? 'selected' : '' }}>
                        Detallado
                    </option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-calculator me-2"></i>
                    Generar Balance
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Información del Balance -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3">
                <h5 class="text-muted mb-1">{{ $empresa ?? 'Mi Empresa S.A.C.' }}</h5>
                <p class="mb-0">BALANCE GENERAL</p>
                <small class="text-muted">Al {{ date('d', strtotime($fechaCorte ?? date('Y-m-d'))) }} 
                    de {{ date('F', strtotime($fechaCorte ?? date('Y-m-d'))) }} 
                    de {{ date('Y', strtotime($fechaCorte ?? date('Y-m-d'))) }}</small>
            </div>
            <div class="col-md-3">
                <p class="text-muted mb-1">RUC</p>
                <p class="fw-bold">{{ $ruc ?? '20123456789' }}</p>
            </div>
            <div class="col-md-3">
                <p class="text-muted mb-1">Moneda</p>
                <p class="fw-bold">{{ request('moneda', 'PEN') === 'PEN' ? 'Soles (PEN)' : 'Dólares (USD)' }}</p>
            </div>
            <div class="col-md-3">
                <p class="text-muted mb-1">Estado</p>
                <span class="badge bg-success">Balance Cuadrado</span>
            </div>
        </div>
    </div>
</div>

<!-- Balance General -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0 text-center">
            BALANCE GENERAL - {{ strtoupper($empresa ?? 'MI EMPRESA S.A.C.') }}
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 balance-table">
                <tbody>
                    <!-- ACTIVOS -->
                    <tr class="table-primary">
                        <td colspan="6" class="fw-bold fs-5">
                            <i class="fas fa-arrow-up me-2"></i>
                            ACTIVOS
                        </td>
                    </tr>
                    
                    <!-- Activos Corrientes -->
                    <tr class="table-light">
                        <td colspan="6" class="fw-bold">
                            <i class="fas fa-chevron-right me-2"></i>
                            ACTIVOS CORRIENTES
                        </td>
                    </tr>
                    
                    @forelse($activosCorrientes ?? [] as $cuenta)
                    <tr class="{{ $cuenta->nivel == 3 ? '' : 'fw-bold' }}">
                        <td style="width: 10%">{{ $cuenta->codigo }}</td>
                        <td style="width: 40%">{{ $cuenta->nombre }}</td>
                        <td class="text-end" style="width: 15%">
                            {{ $monedaSimbolo }}{{ number_format($cuenta->saldo_actual, 2) }}
                        </td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end text-muted" style="width: 15%">
                            {{ $monedaSimbolo }}{{ number_format($cuenta->saldo_anterior, 2) }}
                        </td>
                        <td class="text-end" style="width: 10%">
                            @php
                                $variacion = $cuenta->saldo_anterior > 0 ? 
                                    (($cuenta->saldo_actual - $cuenta->saldo_anterior) / $cuenta->saldo_anterior) * 100 : 0;
                            @endphp
                            <span class="{{ $variacion >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $variacion >= 0 ? '+' : '' }}{{ number_format($variacion, 1) }}%
                            </span>
                        </td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td class="text-end" style="width: 10%">
                            <button class="btn btn-sm btn-outline-info" onclick="verDetalleCuenta({{ $cuenta->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay activos corrientes</td>
                    </tr>
                    @endforelse
                    
                    <tr class="table-primary fw-bold">
                        <td colspan="2" class="text-end">TOTAL ACTIVOS CORRIENTES</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalActivosCorrientes ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalActivosCorrientesAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Activos No Corrientes -->
                    <tr class="table-light">
                        <td colspan="6" class="fw-bold">
                            <i class="fas fa-chevron-right me-2"></i>
                            ACTIVOS NO CORRIENTES
                        </td>
                    </tr>
                    
                    @forelse($activosNoCorrientes ?? [] as $cuenta)
                    <tr class="{{ $cuenta->nivel == 3 ? '' : 'fw-bold' }}">
                        <td>{{ $cuenta->codigo }}</td>
                        <td>{{ $cuenta->nombre }}</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($cuenta->saldo_actual, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end text-muted">{{ $monedaSimbolo }}{{ number_format($cuenta->saldo_anterior, 2) }}</td>
                        <td class="text-end">
                            @php
                                $variacion = $cuenta->saldo_anterior > 0 ? 
                                    (($cuenta->saldo_actual - $cuenta->saldo_anterior) / $cuenta->saldo_anterior) * 100 : 0;
                            @endphp
                            <span class="{{ $variacion >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $variacion >= 0 ? '+' : '' }}{{ number_format($variacion, 1) }}%
                            </span>
                        </td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-info" onclick="verDetalleCuenta({{ $cuenta->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay activos no corrientes</td>
                    </tr>
                    @endforelse
                    
                    <tr class="table-primary fw-bold">
                        <td colspan="2" class="text-end">TOTAL ACTIVOS NO CORRIENTES</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalActivosNoCorrientes ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalActivosNoCorrientesAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Total Activos -->
                    <tr class="table-dark fw-bold fs-5">
                        <td colspan="2" class="text-end">TOTAL ACTIVOS</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalActivos ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalActivosAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Separador -->
                    <tr><td colspan="6" class="p-4 border-0"></td></tr>
                    
                    <!-- PASIVOS Y PATRIMONIO -->
                    <tr class="table-warning">
                        <td colspan="6" class="fw-bold fs-5">
                            <i class="fas fa-arrow-down me-2"></i>
                            PASIVOS Y PATRIMONIO
                        </td>
                    </tr>
                    
                    <!-- Pasivos Corrientes -->
                    <tr class="table-light">
                        <td colspan="6" class="fw-bold">
                            <i class="fas fa-chevron-right me-2"></i>
                            PASIVOS CORRIENTES
                        </td>
                    </tr>
                    
                    @forelse($pasivosCorrientes ?? [] as $cuenta)
                    <tr class="{{ $cuenta->nivel == 3 ? '' : 'fw-bold' }}">
                        <td>{{ $cuenta->codigo }}</td>
                        <td>{{ $cuenta->nombre }}</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($cuenta->saldo_actual, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end text-muted">{{ $monedaSimbolo }}{{ number_format($cuenta->saldo_anterior, 2) }}</td>
                        <td class="text-end">
                            @php
                                $variacion = $cuenta->saldo_anterior > 0 ? 
                                    (($cuenta->saldo_actual - $cuenta->saldo_anterior) / $cuenta->saldo_anterior) * 100 : 0;
                            @endphp
                            <span class="{{ $variacion <= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $variacion >= 0 ? '+' : '' }}{{ number_format($variacion, 1) }}%
                            </span>
                        </td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-info" onclick="verDetalleCuenta({{ $cuenta->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay pasivos corrientes</td>
                    </tr>
                    @endforelse
                    
                    <tr class="table-warning fw-bold">
                        <td colspan="2" class="text-end">TOTAL PASIVOS CORRIENTES</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalPasivosCorrientes ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalPasivosCorrientesAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Pasivos No Corrientes -->
                    <tr class="table-light">
                        <td colspan="6" class="fw-bold">
                            <i class="fas fa-chevron-right me-2"></i>
                            PASIVOS NO CORRIENTES
                        </td>
                    </tr>
                    
                    @forelse($pasivosNoCorrientes ?? [] as $cuenta)
                    <tr class="{{ $cuenta->nivel == 3 ? '' : 'fw-bold' }}">
                        <td>{{ $cuenta->codigo }}</td>
                        <td>{{ $cuenta->nombre }}</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($cuenta->saldo_actual, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end text-muted">{{ $monedaSimbolo }}{{ number_format($cuenta->saldo_anterior, 2) }}</td>
                        <td class="text-end">
                            @php
                                $variacion = $cuenta->saldo_anterior > 0 ? 
                                    (($cuenta->saldo_actual - $cuenta->saldo_anterior) / $cuenta->saldo_anterior) * 100 : 0;
                            @endphp
                            <span class="{{ $variacion <= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $variacion >= 0 ? '+' : '' }}{{ number_format($variacion, 1) }}%
                            </span>
                        </td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-info" onclick="verDetalleCuenta({{ $cuenta->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay pasivos no corrientes</td>
                    </tr>
                    @endforelse
                    
                    <tr class="table-warning fw-bold">
                        <td colspan="2" class="text-end">TOTAL PASIVOS NO CORRIENTES</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalPasivosNoCorrientes ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalPasivosNoCorrientesAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Total Pasivos -->
                    <tr class="table-warning fw-bold">
                        <td colspan="2" class="text-end">TOTAL PASIVOS</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalPasivos ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalPasivosAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Patrimonio -->
                    <tr class="table-light">
                        <td colspan="6" class="fw-bold">
                            <i class="fas fa-chevron-right me-2"></i>
                            PATRIMONIO
                        </td>
                    </tr>
                    
                    @forelse($patrimonio ?? [] as $cuenta)
                    <tr class="{{ $cuenta->nivel == 3 ? '' : 'fw-bold' }}">
                        <td>{{ $cuenta->codigo }}</td>
                        <td>{{ $cuenta->nombre }}</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($cuenta->saldo_actual, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end text-muted">{{ $monedaSimbolo }}{{ number_format($cuenta->saldo_anterior, 2) }}</td>
                        <td class="text-end">
                            @php
                                $variacion = $cuenta->saldo_anterior > 0 ? 
                                    (($cuenta->saldo_actual - $cuenta->saldo_anterior) / $cuenta->saldo_anterior) * 100 : 0;
                            @endphp
                            <span class="{{ $variacion >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $variacion >= 0 ? '+' : '' }}{{ number_format($variacion, 1) }}%
                            </span>
                        </td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-info" onclick="verDetalleCuenta({{ $cuenta->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay patrimonio registrado</td>
                    </tr>
                    @endforelse
                    
                    <tr class="table-success fw-bold">
                        <td colspan="2" class="text-end">TOTAL PATRIMONIO</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalPatrimonio ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalPatrimonioAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Total Pasivos y Patrimonio -->
                    <tr class="table-dark fw-bold fs-5">
                        <td colspan="2" class="text-end">TOTAL PASIVOS Y PATRIMONIO</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalPasivosPatrimonio ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalPasivosPatrimonioAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Ratios Financieros -->
<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calculator me-2"></i>
                    Ratios Financieros Principales
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-center">
                            <h5 class="text-primary">{{ number_format($ratioLiquidez ?? 0, 2) }}</h5>
                            <p class="text-muted mb-2">Razón Corriente</p>
                            <small class="text-muted">
                                (Activos Corrientes / Pasivos Corrientes)
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h5 class="text-success">{{ number_format($ratioEndeudamiento ?? 0, 2) }}%</h5>
                            <p class="text-muted mb-2">Endeudamiento</p>
                            <small class="text-muted">
                                (Pasivos Totales / Activos Totales) × 100
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h5 class="text-info">{{ number_format($ratioPatrimonio ?? 0, 2) }}%</h5>
                            <p class="text-muted mb-2">Autonomía</p>
                            <small class="text-muted">
                                (Patrimonio / Activos Totales) × 100
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Estructura Patrimonial
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 200px;">
                    <canvas id="estructuraPatrimonialChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function exportarBalance() {
        const params = new URLSearchParams(window.location.search);
        const url = `/estados-financieros/balance/exportar-pdf?${params.toString()}`;
        
        showLoading();
        window.open(url, '_blank');
        hideLoading();
    }

    function imprimirBalance() {
        window.print();
    }

    function compararConPeriodoAnterior() {
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('comparar_con', 'periodo_anterior');
        window.location.href = currentUrl.toString();
    }

    function verDetalleCuenta(cuentaId) {
        const params = new URLSearchParams(window.location.search);
        params.set('cuenta_id', cuentaId);
        const url = `/libros-mayor/${cuentaId}?${params.toString()}`;
        
        window.open(url, '_blank');
    }

    // Gráfico de estructura patrimonial
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('estructuraPatrimonialChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Activos', 'Pasivos', 'Patrimonio'],
                    datasets: [{
                        data: [
                            {{ $totalActivos ?? 0 }},
                            {{ $totalPasivos ?? 0 }},
                            {{ $totalPatrimonio ?? 0 }}
                        ],
                        backgroundColor: [
                            '#3b82f6',
                            '#f59e0b',
                            '#10b981'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': S/ ' + context.parsed.toFixed(2) + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>

<style>
@media print {
    .btn, .card-header, .breadcrumb, .d-flex.justify-content-between {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 12px;
    }
    
    .fw-bold {
        font-weight: bold !important;
    }
}
</style>
@endsection