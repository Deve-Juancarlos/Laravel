@extends('layouts.contador')

@section('title', 'Estado de Resultados - SIFANO')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('estados-financieros') }}">Estados Financieros</a></li>
    <li class="breadcrumb-item active">Estado de Resultados</li>
@endsection

@section('contador-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-chart-line text-success me-2"></i>
        Estado de Resultados
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success" onclick="exportarEstado()">
            <i class="fas fa-download me-2"></i>
            Exportar PDF
        </button>
        <button class="btn btn-outline-primary" onclick="imprimirEstado()">
            <i class="fas fa-print me-2"></i>
            Imprimir
        </button>
        <button class="btn btn-outline-info" onclick="verAnálisisVertical()">
            <i class="fas fa-percentage me-2"></i>
            Análisis Vertical
        </button>
        <a href="{{ route('estados-financieros') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Volver
        </a>
    </div>
</div>

<!-- Filtros del Estado de Resultados -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            Configuración del Estado
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
                <label class="form-label">Período Desde</label>
                <input type="date" name="fecha_desde" class="form-control" 
                       value="{{ request('fecha_desde', date('Y-01-01')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Período Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" 
                       value="{{ request('fecha_hasta', date('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
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
            <div class="col-md-2">
                <label class="form-label">Mostrar Por</label>
                <select name="tipo_periodo" class="form-select">
                    <option value="acumulado" {{ request('tipo_periodo', 'acumulado') === 'acumulado' ? 'selected' : '' }}>
                        Período Acumulado
                    </option>
                    <option value="mensual" {{ request('tipo_periodo') === 'mensual' ? 'selected' : '' }}>
                        Último Mes
                    </option>
                    <option value="trimestral" {{ request('tipo_periodo') === 'trimestral' ? 'selected' : '' }}>
                        Trimestre Actual
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Nivel Detalle</label>
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
                    Generar Estado
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Información del Estado -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4">
                <h5 class="text-muted mb-1">{{ $empresa ?? 'Mi Empresa S.A.C.' }}</h5>
                <p class="mb-0">ESTADO DE RESULTADOS</p>
                <small class="text-muted">
                    Del {{ date('d', strtotime($fechaDesde ?? date('Y-01-01'))) }} 
                    de {{ date('F', strtotime($fechaDesde ?? date('Y-01-01'))) }} 
                    al {{ date('d', strtotime($fechaHasta ?? date('Y-m-d'))) }} 
                    de {{ date('F', strtotime($fechaHasta ?? date('Y-m-d'))) }} 
                    de {{ date('Y', strtotime($fechaHasta ?? date('Y-m-d'))) }}
                </small>
            </div>
            <div class="col-md-4">
                <p class="text-muted mb-1">Moneda</p>
                <p class="fw-bold">{{ request('moneda', 'PEN') === 'PEN' ? 'Soles (PEN)' : 'Dólares (USD)' }}</p>
            </div>
            <div class="col-md-4">
                <p class="text-muted mb-1">Utilidad del Período</p>
                <h4 class="{{ ($utilidadNeta ?? 0) >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                    {{ $monedaSimbolo }}{{ number_format($utilidadNeta ?? 0, 2) }}
                </h4>
                <small class="{{ ($utilidadNeta ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ ($utilidadNeta ?? 0) >= 0 ? 'Utilidad' : 'Pérdida' }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Estado de Resultados -->
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0 text-center">
            ESTADO DE RESULTADOS - {{ strtoupper($empresa ?? 'MI EMPRESA S.A.C.') }}
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 estado-resultados-table">
                <tbody>
                    <!-- INGRESOS -->
                    <tr class="table-success">
                        <td colspan="6" class="fw-bold fs-5">
                            <i class="fas fa-arrow-up me-2"></i>
                            INGRESOS
                        </td>
                    </tr>
                    
                    <!-- Ingresos Operacionales -->
                    <tr class="table-light">
                        <td colspan="6" class="fw-bold">
                            <i class="fas fa-chevron-right me-2"></i>
                            INGRESOS OPERACIONALES
                        </td>
                    </tr>
                    
                    @forelse($ingresosOperacionales ?? [] as $cuenta)
                    <tr class="{{ $cuenta->nivel == 3 ? '' : 'fw-bold' }}">
                        <td style="width: 10%">{{ $cuenta->codigo }}</td>
                        <td style="width: 40%">{{ $cuenta->nombre }}</td>
                        <td class="text-end" style="width: 15%">
                            {{ $monedaSimbolo }}{{ number_format($cuenta->total_periodo, 2) }}
                        </td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end text-muted" style="width: 15%">
                            {{ $monedaSimbolo }}{{ number_format($cuenta->total_anterior, 2) }}
                        </td>
                        <td class="text-end" style="width: 10%">
                            @php
                                $variacion = $cuenta->total_anterior > 0 ? 
                                    (($cuenta->total_periodo - $cuenta->total_anterior) / $cuenta->total_anterior) * 100 : 0;
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
                        <td colspan="6" class="text-center text-muted">No hay ingresos operacionales</td>
                    </tr>
                    @endforelse
                    
                    <tr class="table-success fw-bold">
                        <td colspan="2" class="text-end">TOTAL INGRESOS OPERACIONALES</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalIngresosOperacionales ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalIngresosOperacionalesAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Ingresos No Operacionales -->
                    <tr class="table-light">
                        <td colspan="6" class="fw-bold">
                            <i class="fas fa-chevron-right me-2"></i>
                            INGRESOS NO OPERACIONALES
                        </td>
                    </tr>
                    
                    @forelse($ingresosNoOperacionales ?? [] as $cuenta)
                    <tr class="{{ $cuenta->nivel == 3 ? '' : 'fw-bold' }}">
                        <td>{{ $cuenta->codigo }}</td>
                        <td>{{ $cuenta->nombre }}</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($cuenta->total_periodo, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end text-muted">{{ $monedaSimbolo }}{{ number_format($cuenta->total_anterior, 2) }}</td>
                        <td class="text-end">
                            @php
                                $variacion = $cuenta->total_anterior > 0 ? 
                                    (($cuenta->total_periodo - $cuenta->total_anterior) / $cuenta->total_anterior) * 100 : 0;
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
                        <td colspan="6" class="text-center text-muted">No hay ingresos no operacionales</td>
                    </tr>
                    @endforelse
                    
                    <tr class="table-success fw-bold">
                        <td colspan="2" class="text-end">TOTAL INGRESOS NO OPERACIONALES</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalIngresosNoOperacionales ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalIngresosNoOperacionalesAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Total Ingresos -->
                    <tr class="table-dark fw-bold fs-5">
                        <td colspan="2" class="text-end">TOTAL INGRESOS</td>
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalIngresos ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalIngresosAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Separador -->
                    <tr><td colspan="6" class="p-3 border-0"></td></tr>
                    
                    <!-- GASTOS -->
                    <tr class="table-danger">
                        <td colspan="6" class="fw-bold fs-5">
                            <i class="fas fa-arrow-down me-2"></i>
                            GASTOS
                        </td>
                    </tr>
                    
                    <!-- Gastos Operacionales -->
                    <tr class="table-light">
                        <td colspan="6" class="fw-bold">
                            <i class="fas fa-chevron-right me-2"></i>
                            GASTOS OPERACIONALES
                        </td>
                    </tr>
                    
                    @forelse($gastosOperacionales ?? [] as $cuenta)
                    <tr class="{{ $cuenta->nivel == 3 ? '' : 'fw-bold' }}">
                        <td>{{ $cuenta->codigo }}</td>
                        <td>{{ $cuenta->nombre }}</td>
                        <td class="text-end text-danger">{{ $monedaSimbolo }}{{ number_format($cuenta->total_periodo, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end text-muted">{{ $monedaSimbolo }}{{ number_format($cuenta->total_anterior, 2) }}</td>
                        <td class="text-end">
                            @php
                                $variacion = $cuenta->total_anterior > 0 ? 
                                    (($cuenta->total_periodo - $cuenta->total_anterior) / $cuenta->total_anterior) * 100 : 0;
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
                        <td colspan="6" class="text-center text-muted">No hay gastos operacionales</td>
                    </tr>
                    @endforelse
                    
                    <tr class="table-danger fw-bold">
                        <td colspan="2" class="text-end">TOTAL GASTOS OPERACIONALES</td>
                        <td class="text-end text-danger">{{ $monedaSimbolo }}{{ number_format($totalGastosOperacionales ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalGastosOperacionalesAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Gastos No Operacionales -->
                    <tr class="table-light">
                        <td colspan="6" class="fw-bold">
                            <i class="fas fa-chevron-right me-2"></i>
                            GASTOS NO OPERACIONALES
                        </td>
                    </tr>
                    
                    @forelse($gastosNoOperacionales ?? [] as $cuenta)
                    <tr class="{{ $cuenta->nivel == 3 ? '' : 'fw-bold' }}">
                        <td>{{ $cuenta->codigo }}</td>
                        <td>{{ $cuenta->nombre }}</td>
                        <td class="text-end text-danger">{{ $monedaSimbolo }}{{ number_format($cuenta->total_periodo, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end text-muted">{{ $monedaSimbolo }}{{ number_format($cuenta->total_anterior, 2) }}</td>
                        <td class="text-end">
                            @php
                                $variacion = $cuenta->total_anterior > 0 ? 
                                    (($cuenta->total_periodo - $cuenta->total_anterior) / $cuenta->total_anterior) * 100 : 0;
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
                        <td colspan="6" class="text-center text-muted">No hay gastos no operacionales</td>
                    </tr>
                    @endforelse
                    
                    <tr class="table-danger fw-bold">
                        <td colspan="2" class="text-end">TOTAL GASTOS NO OPERACIONALES</td>
                        <td class="text-end text-danger">{{ $monedaSimbolo }}{{ number_format($totalGastosNoOperacionales ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalGastosNoOperacionalesAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Total Gastos -->
                    <tr class="table-danger fw-bold">
                        <td colspan="2" class="text-end">TOTAL GASTOS</td>
                        <td class="text-end text-danger">{{ $monedaSimbolo }}{{ number_format($totalGastos ?? 0, 2) }}</td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($totalGastosAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Separador -->
                    <tr><td colspan="6" class="p-3 border-0"></td></tr>
                    
                    <!-- RESULTADOS -->
                    <tr class="table-info fw-bold fs-5">
                        <td colspan="2" class="text-end">UTILIDAD (PÉRDIDA) OPERACIONAL</td>
                        <td class="text-end {{ ($utilidadOperacional ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $monedaSimbolo }}{{ number_format($utilidadOperacional ?? 0, 2) }}
                        </td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($utilidadOperacionalAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <tr class="table-info fw-bold fs-5">
                        <td colspan="2" class="text-end">UTILIDAD (PÉRDIDA) BRUTA</td>
                        <td class="text-end {{ ($utilidadBruta ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $monedaSimbolo }}{{ number_format($utilidadBruta ?? 0, 2) }}
                        </td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($utilidadBrutaAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <tr class="table-primary fw-bold fs-4">
                        <td colspan="2" class="text-end">UTILIDAD (PÉRDIDA) NETA</td>
                        <td class="text-end {{ ($utilidadNeta ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $monedaSimbolo }}{{ number_format($utilidadNeta ?? 0, 2) }}
                        </td>
                        @if($periodoAnterior ?? false)
                        <td class="text-end">{{ $monedaSimbolo }}{{ number_format($utilidadNetaAnterior ?? 0, 2) }}</td>
                        <td></td>
                        @else
                        <td colspan="2"></td>
                        @endif
                        <td></td>
                    </tr>
                    
                    <!-- Márgenes -->
                    <tr class="table-light">
                        <td colspan="6">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <strong>Margen Bruto:</strong>
                                    <span class="{{ ($margenBruto ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($margenBruto ?? 0, 1) }}%
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Margen Operacional:</strong>
                                    <span class="{{ ($margenOperacional ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($margenOperacional ?? 0, 1) }}%
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Margen Neto:</strong>
                                    <span class="{{ ($margenNeto ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($margenNeto ?? 0, 1) }}%
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <strong>EBITDA:</strong>
                                    <span class="{{ ($ebitda ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $monedaSimbolo }}{{ number_format($ebitda ?? 0, 2) }}
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Análisis de Rentabilidad -->
<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Evolución de Ingresos y Gastos
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="ingresosGastosChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-percentage me-2"></i>
                    Distribución de Gastos
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="distribucionGastosChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function exportarEstado() {
        const params = new URLSearchParams(window.location.search);
        const url = `/estados-financieros/resultados/exportar-pdf?${params.toString()}`;
        
        showLoading();
        window.open(url, '_blank');
        hideLoading();
    }

    function imprimirEstado() {
        window.print();
    }

    function verAnálisisVertical() {
        const params = new URLSearchParams(window.location.search);
        params.set('analisis', 'vertical');
        const url = `/estados-financieros/resultados?${params.toString()}`;
        
        window.location.href = url;
    }

    function verDetalleCuenta(cuentaId) {
        const params = new URLSearchParams(window.location.search);
        params.set('cuenta_id', cuentaId);
        const url = `/libros-mayor/${cuentaId}?${params.toString()}`;
        
        window.open(url, '_blank');
    }

    // Gráfico de evolución de ingresos y gastos
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de líneas
        const ctx1 = document.getElementById('ingresosGastosChart');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: {!! json_encode($labelsEvolucion ?? []) !!},
                    datasets: [{
                        label: 'Ingresos',
                        data: {!! json_encode($ingresosEvolucion ?? []) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Gastos',
                        data: {!! json_encode($gastosEvolucion ?? []) !!},
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Utilidad Neta',
                        data: {!! json_encode($utilidadEvolucion ?? []) !!},
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '{{ $monedaSimbolo ?? "S/" }}' + value.toFixed(0);
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de distribución de gastos
        const ctx2 = document.getElementById('distribucionGastosChart');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($categoriasGastos ?? []) !!},
                    datasets: [{
                        data: {!! json_encode($montosGastos ?? []) !!},
                        backgroundColor: [
                            '#ef4444',
                            '#f59e0b',
                            '#3b82f6',
                            '#8b5cf6',
                            '#06b6d4'
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
                                    return context.label + ': {{ $monedaSimbolo ?? "S/" }}' + context.parsed.toFixed(2) + ' (' + percentage + '%)';
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
        font-size: 11px;
    }
    
    .fw-bold {
        font-weight: bold !important;
    }
}
</style>
@endsection