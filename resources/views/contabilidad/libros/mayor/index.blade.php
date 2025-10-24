@extends('layouts.contador')

@section('title', 'Libro Mayor - SIFANO')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('libros-mayor') }}">Libro Mayor</a></li>
    <li class="breadcrumb-item active">Lista</li>
@endsection

@section('contador-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-book-open text-success me-2"></i>
        Libro Mayor
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success" onclick="exportLibroMayor()">
            <i class="fas fa-download me-2"></i>
            Exportar
        </button>
        <button class="btn btn-outline-primary" onclick="generarBalance()">
            <i class="fas fa-balance-scale me-2"></i>
            Generar Balance
        </button>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('libros-mayor') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Período</label>
                <select name="periodo" class="form-select" onchange="changePeriodo(this.value)">
                    <option value="">Seleccionar período</option>
                    <option value="actual" {{ request('periodo') === 'actual' ? 'selected' : '' }}>Mes Actual</option>
                    <option value="anterior" {{ request('periodo') === 'anterior' ? 'selected' : '' }}>Mes Anterior</option>
                    <option value="personalizado" {{ request('periodo') === 'personalizado' ? 'selected' : '' }}>Personalizado</option>
                </select>
            </div>
            <div class="col-md-3" id="fechaDesdeGroup" style="display: none;">
                <label class="form-label">Fecha Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-3" id="fechaHastaGroup" style="display: none;">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cuenta</label>
                <select name="cuenta_id" class="form-select select2">
                    <option value="">Todas las cuentas</option>
                    @foreach($cuentas ?? [] as $cuenta)
                        <option value="{{ $cuenta->id }}" {{ request('cuenta_id') == $cuenta->id ? 'selected' : '' }}>
                            {{ $cuenta->codigo }} - {{ $cuenta->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nivel de Cuenta</label>
                <select name="nivel_cuenta" class="form-select">
                    <option value="">Todos los niveles</option>
                    <option value="1" {{ request('nivel_cuenta') == '1' ? 'selected' : '' }}>Nivel 1 (Principales)</option>
                    <option value="2" {{ request('nivel_cuenta') == '2' ? 'selected' : '' }}>Nivel 2 (Subcuentas)</option>
                    <option value="3" {{ request('nivel_cuenta') == '3' ? 'selected' : '' }}>Nivel 3 (Detalle)</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>
                    Filtrar
                </button>
                <a href="{{ route('libros-mayor') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-eraser me-2"></i>
                    Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Resumen por Tipo de Cuenta -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-primary mb-2">
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
                <h5 class="text-primary">{{ $totalActivos ?? 0 }}</h5>
                <p class="text-muted mb-0">Cuentas de Activo</p>
                <small class="text-success">S/ {{ number_format($saldoActivos ?? 0, 2) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-warning mb-2">
                    <i class="fas fa-chart-pie fa-2x"></i>
                </div>
                <h5 class="text-warning">{{ $totalPasivos ?? 0 }}</h5>
                <p class="text-muted mb-0">Cuentas de Pasivo</p>
                <small class="text-success">S/ {{ number_format($saldoPasivos ?? 0, 2) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-info mb-2">
                    <i class="fas fa-coins fa-2x"></i>
                </div>
                <h5 class="text-info">{{ $totalPatrimonio ?? 0 }}</h5>
                <p class="text-muted mb-0">Cuentas de Patrimonio</p>
                <small class="text-success">S/ {{ number_format($saldoPatrimonio ?? 0, 2) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-success mb-2">
                    <i class="fas fa-arrow-up fa-2x"></i>
                </div>
                <h5 class="text-success">{{ $totalIngresos ?? 0 }}</h5>
                <p class="text-muted mb-0">Cuentas de Ingresos</p>
                <small class="text-success">S/ {{ number_format($saldoIngresos ?? 0, 2) }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Cuentas del Mayor -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Cuentas del Mayor
        </h5>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary active" onclick="changeView('resumen')" id="btnResumen">
                <i class="fas fa-list me-1"></i> Resumen
            </button>
            <button class="btn btn-outline-secondary" onclick="changeView('detalle')" id="btnDetalle">
                <i class="fas fa-table me-1"></i> Detalle
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Vista Resumen -->
        <div id="viewResumen">
            <div class="table-responsive">
                <table class="table table-striped data-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre de Cuenta</th>
                            <th>Tipo</th>
                            <th>Nivel</th>
                            <th>Saldo Anterior</th>
                            <th>Debe</th>
                            <th>Haber</th>
                            <th>Saldo Actual</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cuentasMayor ?? [] as $cuenta)
                        <tr class="{{ $cuenta->nivel == 1 ? 'table-light' : '' }}">
                            <td>
                                <strong>{{ $cuenta->codigo }}</strong>
                                @if($cuenta->nivel == 1)
                                    <i class="fas fa-chevron-right text-muted ms-1"></i>
                                @endif
                            </td>
                            <td>{{ Str::padLeft('', ($cuenta->nivel - 1) * 2, ' ') }}{{ $cuenta->nombre }}</td>
                            <td>
                                <span class="badge bg-{{ $cuenta->tipo_color }}">
                                    {{ $cuenta->tipo }}
                                </span>
                            </td>
                            <td class="text-center">{{ $cuenta->nivel }}</td>
                            <td class="text-end {{ $cuenta->saldo_anterior >= 0 ? 'text-success' : 'text-danger' }}">
                                S/ {{ number_format($cuenta->saldo_anterior, 2) }}
                            </td>
                            <td class="text-end text-danger">
                                S/ {{ number_format($cuenta->total_debe, 2) }}
                            </td>
                            <td class="text-end text-primary">
                                S/ {{ number_format($cuenta->total_haber, 2) }}
                            </td>
                            <td class="text-end fw-bold {{ $cuenta->saldo_actual >= 0 ? 'text-success' : 'text-danger' }}">
                                S/ {{ number_format($cuenta->saldo_actual, 2) }}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('libros-mayor.cuenta', $cuenta->id) }}" class="btn btn-outline-info" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-success" onclick="exportarCuenta({{ $cuenta->id }})" title="Exportar">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No hay cuentas en el período seleccionado</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vista Detalle (Oculta por defecto) -->
        <div id="viewDetalle" style="display: none;">
            @forelse($cuentasMayor ?? [] as $cuenta)
            @if($cuenta->nivel > 1 || request('cuenta_id'))
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <strong>{{ $cuenta->codigo }}</strong> - {{ $cuenta->nombre }}
                        <span class="badge bg-{{ $cuenta->tipo_color }} ms-2">{{ $cuenta->tipo }}</span>
                    </h6>
                    <div class="text-end">
                        <small class="text-muted">Saldo Actual:</small>
                        <strong class="{{ $cuenta->saldo_actual >= 0 ? 'text-success' : 'text-danger' }}">
                            S/ {{ number_format($cuenta->saldo_actual, 2) }}
                        </strong>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(($cuenta->movimientos ?? [])->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Asiento</th>
                                    <th>Descripción</th>
                                    <th>Debe</th>
                                    <th>Haber</th>
                                    <th>Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cuenta->movimientos ?? [] as $movimiento)
                                <tr>
                                    <td>{{ date('d/m/Y', strtotime($movimiento->fecha)) }}</td>
                                    <td>
                                        <a href="{{ route('libros-diario.show', $movimiento->asiento_id) }}" class="text-decoration-none">
                                            #{{ $movimiento->numero_asiento }}
                                        </a>
                                    </td>
                                    <td>{{ Str::limit($movimiento->descripcion_asiento, 40) }}</td>
                                    <td class="text-end text-danger">
                                        @if($movimiento->debe > 0)
                                            S/ {{ number_format($movimiento->debe, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end text-primary">
                                        @if($movimiento->haber > 0)
                                            S/ {{ number_format($movimiento->haber, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold {{ $movimiento->saldo_acumulado >= 0 ? 'text-success' : 'text-danger' }}">
                                        S/ {{ number_format($movimiento->saldo_acumulado, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-inbox mb-2"></i>
                        <p>No hay movimientos en el período</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            @empty
            <div class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p>No hay datos para mostrar</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Gráfico de Distribución por Tipo de Cuenta -->
<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Distribución de Saldos por Tipo de Cuenta
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="saldoDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Top 5 Cuentas por Saldo
                </h5>
            </div>
            <div class="card-body">
                @forelse($topCuentas ?? [] as $index => $cuenta)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-primary rounded-circle me-3" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <strong>{{ $cuenta->codigo }}</strong>
                            <br>
                            <small class="text-muted">{{ Str::limit($cuenta->nombre, 20) }}</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold {{ $cuenta->saldo_actual >= 0 ? 'text-success' : 'text-danger' }}">
                            S/ {{ number_format($cuenta->saldo_actual, 2) }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-3">
                    <i class="fas fa-chart-bar mb-2"></i>
                    <p>No hay datos para mostrar</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function changePeriodo(valor) {
        const fechaDesdeGroup = document.getElementById('fechaDesdeGroup');
        const fechaHastaGroup = document.getElementById('fechaHastaGroup');
        
        if (valor === 'personalizado') {
            fechaDesdeGroup.style.display = 'block';
            fechaHastaGroup.style.display = 'block';
        } else {
            fechaDesdeGroup.style.display = 'none';
            fechaHastaGroup.style.display = 'none';
        }
    }

    function changeView(view) {
        const resumen = document.getElementById('viewResumen');
        const detalle = document.getElementById('viewDetalle');
        const btnResumen = document.getElementById('btnResumen');
        const btnDetalle = document.getElementById('btnDetalle');
        
        if (view === 'resumen') {
            resumen.style.display = 'block';
            detalle.style.display = 'none';
            btnResumen.classList.add('active');
            btnDetalle.classList.remove('active');
        } else {
            resumen.style.display = 'none';
            detalle.style.display = 'block';
            btnResumen.classList.remove('active');
            btnDetalle.classList.add('active');
        }
    }

    function exportLibroMayor() {
        const params = new URLSearchParams(window.location.search);
        const url = `/libros-mayor/exportar?${params.toString()}`;
        
        showLoading();
        window.open(url, '_blank');
        hideLoading();
    }

    function exportarCuenta(cuentaId) {
        const params = new URLSearchParams(window.location.search);
        params.set('cuenta_id', cuentaId);
        const url = `/libros-mayor/exportar?${params.toString()}`;
        
        showLoading();
        window.open(url, '_blank');
        hideLoading();
    }

    function generarBalance() {
        const params = new URLSearchParams(window.location.search);
        const url = `/balance-comprobacion?${params.toString()}`;
        
        window.open(url, '_blank');
    }

    // Gráfico de distribución de saldos
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('saldoDistributionChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($tiposCuenta ?? []) !!},
                    datasets: [{
                        data: {!! json_encode($saldosPorTipo ?? []) !!},
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
                            position: 'right'
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

        // Inicializar período seleccionado
        const periodoSelect = document.querySelector('select[name="periodo"]');
        if (periodoSelect && periodoSelect.value === 'personalizado') {
            changePeriodo('personalizado');
        }
    });
</script>
@endsection