@extends('layouts.contador')

@section('title', 'Cuenta Mayor - SIFANO')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('libros-mayor') }}">Libro Mayor</a></li>
    <li class="breadcrumb-item active">{{ $cuenta->codigo ?? '' }}</li>
@endsection

@section('contador-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-book-open text-success me-2"></i>
            Cuenta Mayor: {{ $cuenta->codigo ?? '' }}
        </h1>
        <p class="text-muted mb-0">{{ $cuenta->nombre ?? '' }}</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success" onclick="exportarCuenta()">
            <i class="fas fa-download me-2"></i>
            Exportar
        </button>
        <button class="btn btn-outline-primary" onclick="imprimirCuenta()">
            <i class="fas fa-print me-2"></i>
            Imprimir
        </button>
        <a href="{{ route('libros-mayor') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Volver al Mayor
        </a>
    </div>
</div>

<div class="row">
    <!-- Información de la Cuenta -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Información de la Cuenta
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Código</label>
                    <p class="form-control-plaintext">{{ $cuenta->codigo ?? '' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre</label>
                    <p class="form-control-plaintext">{{ $cuenta->nombre ?? '' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tipo</label>
                    <p class="form-control-plaintext">
                        <span class="badge bg-{{ $cuenta->tipo_color ?? 'secondary' }}">
                            {{ $cuenta->tipo ?? '' }}
                        </span>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nivel</label>
                    <p class="form-control-plaintext">{{ $cuenta->nivel ?? '' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Cuenta Padre</label>
                    <p class="form-control-plaintext">
                        @if($cuenta->cuenta_padre ?? false)
                            {{ $cuenta->cuenta_padre->codigo }} - {{ Str::limit($cuenta->cuenta_padre->nombre, 30) }}
                        @else
                            <span class="text-muted">Cuenta principal</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Resumen de Saldos -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calculator me-2"></i>
                    Resumen de Saldos
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Saldo Anterior:</span>
                        <strong class="{{ ($cuenta->saldo_anterior ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            S/ {{ number_format($cuenta->saldo_anterior ?? 0, 2) }}
                        </strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Total Debe:</span>
                        <strong class="text-danger">
                            S/ {{ number_format($cuenta->total_debe ?? 0, 2) }}
                        </strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Total Haber:</span>
                        <strong class="text-primary">
                            S/ {{ number_format($cuenta->total_haber ?? 0, 2) }}
                        </strong>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span><strong>Saldo Actual:</strong></span>
                        <strong class="fs-5 {{ ($cuenta->saldo_actual ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            S/ {{ number_format($cuenta->saldo_actual ?? 0, 2) }}
                        </strong>
                    </div>
                </div>
                
                <!-- Indicador de naturaleza del saldo -->
                <div class="text-center mt-3">
                    @if($cuenta->tipo ?? '' === 'Activo')
                        @if(($cuenta->saldo_actual ?? 0) >= 0)
                            <span class="badge bg-success">Saldo Normal (Deudor)</span>
                        @else
                            <span class="badge bg-danger">Saldo Anormal (Acreedor)</span>
                        @endif
                    @elseif($cuenta->tipo ?? '' === 'Pasivo')
                        @if(($cuenta->saldo_actual ?? 0) >= 0)
                            <span class="badge bg-success">Saldo Normal (Acreedor)</span>
                        @else
                            <span class="badge bg-danger">Saldo Anormal (Deudor)</span>
                        @endif
                    @elseif($cuenta->tipo ?? '' === 'Patrimonio')
                        @if(($cuenta->saldo_actual ?? 0) >= 0)
                            <span class="badge bg-success">Saldo Normal (Acreedor)</span>
                        @else
                            <span class="badge bg-danger">Saldo Anormal (Deudor)</span>
                        @endif
                    @elseif($cuenta->tipo ?? '' === 'Ingresos')
                        @if(($cuenta->saldo_actual ?? 0) >= 0)
                            <span class="badge bg-success">Saldo Normal (Acreedor)</span>
                        @else
                            <span class="badge bg-danger">Saldo Anormal (Deudor)</span>
                        @endif
                    @elseif($cuenta->tipo ?? '' === 'Gastos')
                        @if(($cuenta->saldo_actual ?? 0) >= 0)
                            <span class="badge bg-success">Saldo Normal (Deudor)</span>
                        @else
                            <span class="badge bg-danger">Saldo Anormal (Acreedor)</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Gráfico de Movimientos -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Evolución de Saldos
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 200px;">
                    <canvas id="saldoEvolutionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Movimientos de la Cuenta -->
    <div class="col-lg-8">
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Concepto</label>
                        <input type="text" name="concepto" class="form-control" placeholder="Buscar concepto..." 
                               value="{{ request('concepto') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Monto Mínimo</label>
                        <input type="number" name="monto_min" class="form-control" step="0.01" 
                               value="{{ request('monto_min') }}">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>
                            Filtrar
                        </button>
                        <a href="{{ request()->url() }}" class="btn btn-outline-secondary">
                            <i class="fas fa-eraser me-2"></i>
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Movimientos -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Movimientos de la Cuenta
                </h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active" onclick="toggleView('tabla')" id="btnTabla">
                        <i class="fas fa-table me-1"></i> Tabla
                    </button>
                    <button class="btn btn-outline-secondary" onclick="toggleView('estado')" id="btnEstado">
                        <i class="fas fa-chart-bar me-1"></i> Estado Cuenta
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Vista Tabla -->
                <div id="viewTabla">
                    <div class="table-responsive">
                        <table class="table table-striped data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Número Asiento</th>
                                    <th>Descripción</th>
                                    <th>Debe</th>
                                    <th>Haber</th>
                                    <th>Saldo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movimientos ?? [] as $movimiento)
                                <tr>
                                    <td>{{ date('d/m/Y', strtotime($movimiento->fecha)) }}</td>
                                    <td>
                                        <a href="{{ route('libros-diario.show', $movimiento->asiento_id) }}" class="text-decoration-none">
                                            <strong>#{{ $movimiento->numero_asiento }}</strong>
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            {{ $movimiento->descripcion_asiento }}
                                            @if($movimiento->descripcion_partida)
                                                <br><small class="text-muted">{{ $movimiento->descripcion_partida }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end text-danger">
                                        @if($movimiento->debe > 0)
                                            <strong>S/ {{ number_format($movimiento->debe, 2) }}</strong>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end text-primary">
                                        @if($movimiento->haber > 0)
                                            <strong>S/ {{ number_format($movimiento->haber, 2) }}</strong>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold {{ $movimiento->saldo_acumulado >= 0 ? 'text-success' : 'text-danger' }}">
                                        S/ {{ number_format($movimiento->saldo_acumulado, 2) }}
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('libros-diario.show', $movimiento->asiento_id) }}" 
                                               class="btn btn-outline-info" title="Ver Asiento">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>No hay movimientos en el período seleccionado</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if(($movimientos ?? [])->count() > 0)
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Mostrando {{ ($movimientos ?? [])->firstItem() ?? 0 }} a {{ ($movimientos ?? [])->lastItem() ?? 0 }} 
                            de {{ ($movimientos ?? [])->total() ?? 0 }} resultados
                        </div>
                        <div>
                            {{ ($movimientos ?? [])->links() }}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Vista Estado de Cuenta -->
                <div id="viewEstado" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Estado de Cuenta</strong> - Vista con saldos corridos y totales consolidados
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th colspan="6" class="text-center">
                                        ESTADO DE CUENTA - {{ $cuenta->codigo }} {{ $cuenta->nombre }}
                                    </th>
                                </tr>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Detalle</th>
                                    <th>Debe</th>
                                    <th>Haber</th>
                                    <th>Saldo Deudor</th>
                                    <th>Saldo Acreedor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Saldo anterior -->
                                <tr class="table-light">
                                    <td colspan="2">
                                        <strong>SALDO ANTERIOR</strong>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    @if(($cuenta->saldo_anterior ?? 0) > 0)
                                        <td class="text-end fw-bold">S/ {{ number_format($cuenta->saldo_anterior, 2) }}</td>
                                        <td></td>
                                    @else
                                        <td></td>
                                        <td class="text-end fw-bold">S/ {{ number_format(abs($cuenta->saldo_anterior ?? 0), 2) }}</td>
                                    @endif
                                </tr>
                                
                                <!-- Movimientos del período -->
                                @forelse($movimientos ?? [] as $movimiento)
                                <tr>
                                    <td>{{ date('d/m/Y', strtotime($movimiento->fecha)) }}</td>
                                    <td>
                                        <div>
                                            <strong>#{{ $movimiento->numero_asiento }}</strong>
                                            <br>
                                            <small>{{ Str::limit($movimiento->descripcion_asiento, 50) }}</small>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        @if($movimiento->debe > 0)
                                            S/ {{ number_format($movimiento->debe, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($movimiento->haber > 0)
                                            S/ {{ number_format($movimiento->haber, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($movimiento->saldo_deudor > 0)
                                            S/ {{ number_format($movimiento->saldo_deudor, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($movimiento->saldo_acreedor > 0)
                                            S/ {{ number_format($movimiento->saldo_acreedor, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        No hay movimientos en el período
                                    </td>
                                </tr>
                                @endforelse
                                
                                <!-- Totales -->
                                <tr class="table-light fw-bold">
                                    <td colspan="2">TOTALES</td>
                                    <td class="text-end text-danger">
                                        S/ {{ number_format($movimientos->sum('debe') ?? 0, 2) }}
                                    </td>
                                    <td class="text-end text-primary">
                                        S/ {{ number_format($movimientos->sum('haber') ?? 0, 2) }}
                                    </td>
                                    <td class="text-end">
                                        @if(($cuenta->saldo_actual ?? 0) > 0)
                                            S/ {{ number_format($cuenta->saldo_actual, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(($cuenta->saldo_actual ?? 0) < 0)
                                            S/ {{ number_format(abs($cuenta->saldo_actual), 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function exportarCuenta() {
        const params = new URLSearchParams(window.location.search);
        const url = `/libros-mayor/{{ $cuenta->id ?? 0 }}/exportar?${params.toString()}`;
        
        showLoading();
        window.open(url, '_blank');
        hideLoading();
    }

    function imprimirCuenta() {
        window.print();
    }

    function toggleView(view) {
        const tabla = document.getElementById('viewTabla');
        const estado = document.getElementById('viewEstado');
        const btnTabla = document.getElementById('btnTabla');
        const btnEstado = document.getElementById('btnEstado');
        
        if (view === 'tabla') {
            tabla.style.display = 'block';
            estado.style.display = 'none';
            btnTabla.classList.add('active');
            btnEstado.classList.remove('active');
        } else {
            tabla.style.display = 'none';
            estado.style.display = 'block';
            btnTabla.classList.remove('active');
            btnEstado.classList.add('active');
        }
    }

    // Gráfico de evolución de saldos
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('saldoEvolutionChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($fechasSaldo ?? []) !!},
                    datasets: [{
                        label: 'Saldo de la Cuenta',
                        data: {!! json_encode($saldosEvolucion ?? []) !!},
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Saldo: S/ ' + context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: {
                                callback: function(value) {
                                    return 'S/ ' + value.toFixed(0);
                                }
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }
    });
</script>
@endsection