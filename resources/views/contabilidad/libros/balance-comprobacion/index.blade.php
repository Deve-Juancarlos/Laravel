@extends('layouts.contador')

@section('title', 'Balance de Comprobación - SIFANO')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('balance-comprobacion') }}">Balance de Comprobación</a></li>
    <li class="breadcrumb-item active">Lista</li>
@endsection

@section('contador-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-balance-scale text-success me-2"></i>
        Balance de Comprobación
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success" onclick="exportarBalance()">
            <i class="fas fa-download me-2"></i>
            Exportar
        </button>
        <button class="btn btn-outline-primary" onclick="imprimirBalance()">
            <i class="fas fa-print me-2"></i>
            Imprimir
        </button>
        <button class="btn btn-outline-info" onclick="generarEstadosFinancieros()">
            <i class="fas fa-chart-line me-2"></i>
            Estados Financieros
        </button>
    </div>
</div>

<!-- Filtros y Configuración -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            Filtros y Configuración
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('balance-comprobacion') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Ejercicio</label>
                <select name="ejercicio" class="form-select">
                    @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                        <option value="{{ $year }}" {{ request('ejercicio') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Mes</label>
                <select name="mes" class="form-select">
                    @for($month = 1; $month <= 12; $month++)
                        <option value="{{ $month }}" {{ request('mes') == $month ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Período Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Período Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Nivel de Detalle</label>
                <select name="nivel_detalle" class="form-select">
                    <option value="resumido" {{ request('nivel_detalle') === 'resumido' ? 'selected' : '' }}>Resumido</option>
                    <option value="detallado" {{ request('nivel_detalle') === 'detallado' ? 'selected' : '' }}>Detallado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipos de Cuenta</label>
                <select name="tipo_cuenta" class="form-select">
                    <option value="">Todos los tipos</option>
                    <option value="activo" {{ request('tipo_cuenta') === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="pasivo" {{ request('tipo_cuenta') === 'pasivo' ? 'selected' : '' }}>Pasivo</option>
                    <option value="patrimonio" {{ request('tipo_cuenta') === 'patrimonio' ? 'selected' : '' }}>Patrimonio</option>
                    <option value="ingresos" {{ request('tipo_cuenta') === 'ingresos' ? 'selected' : '' }}>Ingresos</option>
                    <option value="gastos" {{ request('tipo_cuenta') === 'gastos' ? 'selected' : '' }}>Gastos</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Solo Cuentas con Movimiento</label>
                <select name="solo_movimiento" class="form-select">
                    <option value="no" {{ request('solo_movimiento') !== 'si' ? 'selected' : '' }}>No</option>
                    <option value="si" {{ request('solo_movimiento') === 'si' ? 'selected' : '' }}>Sí</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Fecha de Generación</label>
                <input type="text" class="form-control" value="{{ date('d/m/Y H:i:s') }}" readonly>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-calculator me-2"></i>
                    Generar Balance
                </button>
                <a href="{{ route('balance-comprobacion') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-eraser me-2"></i>
                    Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

@if(($cuentasBalance ?? [])->$count() > 0)
<!-- Resumen del Balance -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-primary mb-2">
                    <i class="fas fa-chart-pie fa-2x"></i>
                </div>
                <h5 class="text-primary">{{ $totalCuentas ?? 0 }}</h5>
                <p class="text-muted mb-0">Total Cuentas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-success mb-2">
                    <i class="fas fa-arrow-up fa-2x"></i>
                </div>
                <h5 class="text-success">S/ {{ number_format($totalDebe ?? 0, 2) }}</h5>
                <p class="text-muted mb-0">Total Debe</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-info mb-2">
                    <i class="fas fa-arrow-down fa-2x"></i>
                </div>
                <h5 class="text-info">S/ {{ number_format($totalHaber ?? 0, 2) }}</h5>
                <p class="text-muted mb-0">Total Haber</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-{{ ($totalDebe ?? 0) - ($totalHaber ?? 0) == 0 ? 'success' : 'danger' }} mb-2">
                    <i class="fas fa-balance-scale fa-2x"></i>
                </div>
                <h5 class="text-{{ ($totalDebe ?? 0) - ($totalHaber ?? 0) == 0 ? 'success' : 'danger' }}">
                    S/ {{ number_format(($totalDebe ?? 0) - ($totalHaber ?? 0), 2) }}
                </h5>
                <p class="text-muted mb-0">Diferencia</p>
                <small class="text-{{ ($totalDebe ?? 0) - ($totalHaber ?? 0) == 0 ? 'success' : 'danger' }}">
                    {{ ($totalDebe ?? 0) - ($totalHaber ?? 0) == 0 ? 'Balance Correcto' : 'Desbalanceado' }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Alerta de Balance -->
@if(($totalDebe ?? 0) - ($totalHaber ?? 0) != 0)
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Balance Desbalanceado:</strong> 
    La diferencia entre Total Debe y Total Haber es de S/ {{ number_format(abs(($totalDebe ?? 0) - ($totalHaber ?? 0)), 2) }}. 
    Revise los asientos contables.
</div>
@else
<div class="alert alert-success">
    <i class="fas fa-check-circle me-2"></i>
    <strong>Balance Correcto:</strong> 
    El total de Debe y Haber coinciden. El balance está correctamente cuadrado.
</div>
@endif

<!-- Balance de Comprobación -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-table me-2"></i>
            Balance de Comprobación - {{ $periodoSeleccionado ?? '' }}
        </h5>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary active" onclick="changeView('completo')" id="btnCompleto">
                <i class="fas fa-table me-1"></i> Completo
            </button>
            <button class="btn btn-outline-secondary" onclick="changeView('agrupado')" id="btnAgrupado">
                <i class="fas fa-layer-group me-1"></i> Agrupado
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <!-- Vista Completa -->
        <div id="viewCompleto">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th rowspan="2" class="text-center align-middle" style="width: 10%">Código</th>
                            <th rowspan="2" class="text-center align-middle" style="width: 25%">Nombre de Cuenta</th>
                            <th colspan="2" class="text-center">Saldos Anteriores</th>
                            <th colspan="2" class="text-center">Movimientos del Período</th>
                            <th colspan="2" class="text-center">Saldos Actuales</th>
                        </tr>
                        <tr>
                            <th class="text-end">Deudor</th>
                            <th class="text-end">Acreedor</th>
                            <th class="text-end">Debe</th>
                            <th class="text-end">Haber</th>
                            <th class="text-end">Deudor</th>
                            <th class="text-end">Acreedor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalSaldoAnteriorDeudor = 0;
                            $totalSaldoAnteriorAcreedor = 0;
                            $totalDebe = 0;
                            $totalHaber = 0;
                            $totalSaldoActualDeudor = 0;
                            $totalSaldoActualAcreedor = 0;
                        @endphp

                        @foreach($cuentasBalance ?? [] as $cuenta)
                        @php
                            // Calcular saldos según el tipo de cuenta
                            $saldoAnteriorDeudor = $cuenta->$saldo_anterior > 0 ? $cuenta->$saldo_anterior : 0;
                            $saldoAnteriorAcreedor = $cuenta->$saldo_anterior < 0 ? abs($cuenta->$saldo_anterior) : 0;
                            $saldoActualDeudor = $cuenta->$saldo_actual > 0 ? $cuenta->$saldo_actual : 0;
                            $saldoActualAcreedor = $cuenta->$saldo_actual < 0 ? abs($cuenta->$saldo_actual) : 0;
                            
                            $totalSaldoAnteriorDeudor += $saldoAnteriorDeudor;
                            $totalSaldoAnteriorAcreedor += $saldoAnteriorAcreedor;
                            $totalDebe += $cuenta->$total_debe;
                            $totalHaber += $cuenta->$total_haber;
                            $totalSaldoActualDeudor += $saldoActualDeudor;
                            $totalSaldoActualAcreedor += $saldoActualAcreedor;
                        @endphp
                        
                        <tr class="{{ $cuenta->$nivel == 1 ? 'table-light fw-bold' : '' }}">
                            <td>
                                <strong>{{ $cuenta->$codigo }}</strong>
                                @if($cuenta->$nivel == 1)
                                    <i class="fas fa-chevron-right text-muted ms-1"></i>
                                @endif
                            </td>
                            <td>{{ Str::padLeft('', ($cuenta->$nivel - 1) * 3, ' ') }}{{ $cuenta->$nombre }}</td>
                            <td class="text-end {{ $saldoAnteriorDeudor > 0 ? 'fw-bold' : '' }}">
                                @if($saldoAnteriorDeudor > 0)
                                    S/ {{ number_format($saldoAnteriorDeudor, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end {{ $saldoAnteriorAcreedor > 0 ? 'fw-bold' : '' }}">
                                @if($saldoAnteriorAcreedor > 0)
                                    S/ {{ number_format($saldoAnteriorAcreedor, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end text-danger {{ $cuenta->$total_debe > 0 ? 'fw-bold' : '' }}">
                                @if($cuenta->$total_debe > 0)
                                    S/ {{ number_format($cuenta->$total_debe, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end text-primary {{ $cuenta->$total_haber > 0 ? 'fw-bold' : '' }}">
                                @if($cuenta->$total_haber > 0)
                                    S/ {{ number_format($cuenta->$total_haber, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end {{ $saldoActualDeudor > 0 ? 'fw-bold text-success' : '' }}">
                                @if($saldoActualDeudor > 0)
                                    S/ {{ number_format($saldoActualDeudor, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end {{ $saldoActualAcreedor > 0 ? 'fw-bold text-info' : '' }}">
                                @if($saldoActualAcreedor > 0)
                                    S/ {{ number_format($saldoActualAcreedor, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        
                        <!-- Fila de Totales -->
                        <tr class="table-dark fw-bold">
                            <td colspan="2" class="text-center">TOTALES</td>
                            <td class="text-end">S/ {{ number_format($totalSaldoAnteriorDeudor, 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totalSaldoAnteriorAcreedor, 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totalDebe, 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totalHaber, 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totalSaldoActualDeudor, 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totalSaldoActualAcreedor, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vista Agrupada -->
        <div id="viewAgrupado" style="display: none;">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width: 15%">Tipo de Cuenta</th>
                            <th class="text-center" style="width: 10%">Cantidad</th>
                            <th class="text-center" style="width: 15%">Total Deudor</th>
                            <th class="text-center" style="width: 15%">Total Acreedor</th>
                            <th class="text-center" style="width: 15%">Saldo Neto</th>
                            <th class="text-center" style="width: 30%">Distribución</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resumenPorTipo ?? [] as $resumen)
                        <tr>
                            <td class="fw-bold text-center">
                                <span class="badge bg-{{ $resumen->$color }}">{{ strtoupper($resumen->$tipo) }}</span>
                            </td>
                            <td class="text-center">{{ $resumen->$cantidad_cuentas }}</td>
                            <td class="text-end fw-bold text-success">
                                S/ {{ number_format($resumen->$total_deudor, 2) }}
                            </td>
                            <td class="text-end fw-bold text-info">
                                S/ {{ number_format($resumen->$total_acreedor, 2) }}
                            </td>
                            <td class="text-end fw-bold {{ $resumen->$saldo_neto >= 0 ? 'text-success' : 'text-danger' }}">
                                S/ {{ number_format($resumen->$saldo_neto, 2) }}
                            </td>
                            <td>
                                <div class="progress">
                                    @php
                                        $porcentaje = $resumen->$total_cuentas > 0 ? ($resumen->$cantidad_cuentas / $resumen->$total_cuentas) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-{{ $resumen->$color }}" 
                                        style="width : {{ $porcentaje}}%">
                                        {{ number_format($porcentaje, 1) }}%
                                    </div>


                                </div>
                                <small class="text-muted">{{ $resumen->$porcentaje_del_total }}% del total</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No hay datos agrupados para mostrar
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Información Adicional -->
<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Información del Balance
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Empresa</label>
                        <p class="form-control-plaintext">{{ $empresa ?? 'Mi Empresa S.A.C.' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">RUC</label>
                        <p class="form-control-plaintext">{{ $ruc ?? '20123456789' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Período</label>
                        <p class="form-control-plaintext">{{ $periodoSeleccionado ?? date('F Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha de Generación</label>
                        <p class="form-control-plaintext">{{ date('d/m/Y H:i:s') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Generado por</label>
                        <p class="form-control-plaintext">{{ auth()->user()->nombre ?? 'Usuario' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Estado</label>
                        <p class="form-control-plaintext">
                            @if(($totalDebe ?? 0) - ($totalHaber ?? 0) == 0)
                                <span class="badge bg-success">Balance Correcto</span>
                            @else
                                <span class="badge bg-danger">Balance Desbalanceado</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Distribución por Tipo
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 250px;">
                    <canvas id="balanceDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@else
<!-- Sin datos -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-balance-scale fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">No hay datos para generar el Balance de Comprobación</h4>
        <p class="text-muted">Configure los filtros y haga clic en "Generar Balance" para continuar.</p>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    function exportarBalance() {
        const params = new URLSearchParams(window.location.search);
        const url = `/balance-comprobacion/exportar?${params.toString()}`;
        
        showLoading();
        window.open(url, '_blank');
        hideLoading();
    }

    function imprimirBalance() {
        window.print();
    }

    function generarEstadosFinancieros() {
        const params = new URLSearchParams(window.location.search);
        const url = `/estados-financieros?${params.toString()}`;
        
        window.open(url, '_blank');
    }

    function changeView(view) {
        const completo = document.getElementById('viewCompleto');
        const agrupado = document.getElementById('viewAgrupado');
        const btnCompleto = document.getElementById('btnCompleto');
        const btnAgrupado = document.getElementById('btnAgrupado');
        
        if (view === 'completo') {
            completo.style.display = 'block';
            agrupado.style.display = 'none';
            btnCompleto.classList.add('active');
            btnAgrupado.classList.remove('active');
        } else {
            completo.style.display = 'none';
            agrupado.style.display = 'block';
            btnCompleto.classList.remove('active');
            btnAgrupado.classList.add('active');
        }
    }

    // Gráfico de distribución del balance
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('balanceDistributionChart');
            if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: @json($nombresTipos ?? []),
                    datasets: [{
                        data: @json($saldosPorTipo ?? []),

                        backgroundColor: [
                            '#3b82f6',
                            '#f59e0b', 
                            '#10b981',
                            '#ef4444',
                            '#8b5cf6'
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

    // Auto-guardar configuraciones en localStorage
    document.querySelectorAll('select, input[type="date"]').forEach(element => {
        element.addEventListener('change', function() {
            localStorage.setItem('balance_filtros', JSON.stringify({
                ejercicio: document.querySelector('select[name="ejercicio"]').value,
                mes: document.querySelector('select[name="mes"]').value,
                nivel_detalle: document.querySelector('select[name="nivel_detalle"]').value,
                tipo_cuenta: document.querySelector('select[name="tipo_cuenta"]').value,
                solo_movimiento: document.querySelector('select[name="solo_movimiento"]').value
            }));
        });
    });

    // Cargar configuraciones guardadas
    const savedFilters = localStorage.getItem('balance_filtros');
    if (savedFilters) {
        const filters = JSON.parse(savedFilters);
        Object.keys(filters).forEach(key => {
            const element = document.querySelector(`[name="${key}"]`);
            if (element) {
                element.value = filters[key];
            }
        });
    }
</script>
@endsection