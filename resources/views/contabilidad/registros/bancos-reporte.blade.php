@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-chart-bar me-2"></i>Reportes Bancarios</h2>
                <div>
                    <button class="btn btn-success" onclick="exportarReporte()">
                        <i class="fas fa-file-excel me-2"></i>Exportar
                    </button>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('bancos.reporte') }}" id="formReporte">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Reporte *</label>
                        <select name="tipo_reporte" class="form-select" id="tipoReporte" required>
                            <option value="general" {{ request('tipo_reporte') == 'general' ? 'selected' : '' }}>Reporte General</option>
                            <option value="movimientos" {{ request('tipo_reporte') == 'movimientos' ? 'selected' : '' }}>Movimientos Detallados</option>
                            <option value="comparativo" {{ request('tipo_reporte') == 'comparativo' ? 'selected' : '' }}>Comparativo de Bancos</option>
                            <option value="flujo" {{ request('tipo_reporte') == 'flujo' ? 'selected' : '' }}>Flujo de Efectivo</option>
                            <option value="conciliacion" {{ request('tipo_reporte') == 'conciliacion' ? 'selected' : '' }}>Estado de Conciliación</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fecha Inicio *</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio', now()->startOfMonth()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fecha Fin *</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Banco</label>
                        <select name="banco_id" class="form-select">
                            <option value="">Todos los bancos</option>
                            @foreach($bancos as $banco)
                            <option value="{{ $banco->id }}" {{ request('banco_id') == $banco->id ? 'selected' : '' }}>
                                {{ $banco->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Generar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(request()->has('tipo_reporte'))
    
    @switch(request('tipo_reporte'))
        
        @case('general')
            <!-- Reporte General -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h6>Saldo Total</h6>
                            <h3>{{ number_format($datosReporte['saldo_total'], 2) }}</h3>
                            <small>Todos los bancos</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h6>Total Ingresos</h6>
                            <h3>{{ number_format($datosReporte['total_ingresos'], 2) }}</h3>
                            <small>Período seleccionado</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h6>Total Egresos</h6>
                            <h3>{{ number_format($datosReporte['total_egresos'], 2) }}</h3>
                            <small>Período seleccionado</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h6>Flujo Neto</h6>
                            <h3>{{ number_format($datosReporte['flujo_neto'], 2) }}</h3>
                            <small>Ingresos - Egresos</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfica de Saldos por Banco -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Distribución de Saldos</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="chartSaldos" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Movimientos del Período</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="chartMovimientos" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla Resumen por Banco -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Resumen por Banco</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Banco</th>
                                    <th>Tipo</th>
                                    <th>N° Cuenta</th>
                                    <th class="text-end">Saldo Inicial</th>
                                    <th class="text-end">Ingresos</th>
                                    <th class="text-end">Egresos</th>
                                    <th class="text-end">Saldo Actual</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($datosReporte['por_banco'] as $banco)
                                <tr>
                                    <td><strong>{{ $banco['nombre'] }}</strong></td>
                                    <td><span class="badge bg-secondary">{{ $banco['tipo'] }}</span></td>
                                    <td><code>{{ $banco['numero_cuenta'] }}</code></td>
                                    <td class="text-end">{{ number_format($banco['saldo_inicial'], 2) }}</td>
                                    <td class="text-end text-success">{{ number_format($banco['ingresos'], 2) }}</td>
                                    <td class="text-end text-danger">{{ number_format($banco['egresos'], 2) }}</td>
                                    <td class="text-end"><strong>{{ number_format($banco['saldo_actual'], 2) }}</strong></td>
                                    <td class="text-center">
                                        @if($banco['estado'] == 'activo')
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3">TOTALES</th>
                                    <th class="text-end">{{ number_format($datosReporte['por_banco']->sum('saldo_inicial'), 2) }}</th>
                                    <th class="text-end text-success">{{ number_format($datosReporte['por_banco']->sum('ingresos'), 2) }}</th>
                                    <th class="text-end text-danger">{{ number_format($datosReporte['por_banco']->sum('egresos'), 2) }}</th>
                                    <th class="text-end"><strong>{{ number_format($datosReporte['saldo_total'], 2) }}</strong></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @break

        @case('movimientos')
            <!-- Reporte de Movimientos Detallados -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Movimientos Detallados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="tablaMovimientos">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Banco</th>
                                    <th>Tipo</th>
                                    <th>Concepto</th>
                                    <th>Categoría</th>
                                    <th>Referencia</th>
                                    <th class="text-end">Monto</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($datosReporte['movimientos'] as $mov)
                                <tr>
                                    <td>{{ \\Carbon\\Carbon::parse($mov->fecha)->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge bg-secondary">{{ $mov->banco->nombre }}</span></td>
                                    <td>
                                        @if($mov->tipo == 'ingreso')
                                            <span class="badge bg-success"><i class="fas fa-arrow-up"></i> Ingreso</span>
                                        @else
                                            <span class="badge bg-danger"><i class="fas fa-arrow-down"></i> Egreso</span>
                                        @endif
                                    </td>
                                    <td>{{ $mov->concepto }}</td>
                                    <td>{{ $mov->categoria ?? '-' }}</td>
                                    <td>{{ $mov->referencia ?? '-' }}</td>
                                    <td class="text-end {{ $mov->tipo == 'ingreso' ? 'text-success' : 'text-danger' }}">
                                        <strong>{{ number_format($mov->monto, 2) }}</strong>
                                    </td>
                                    <td>{{ $mov->usuario->name ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $datosReporte['movimientos']->links() }}
                </div>
            </div>
        @break

        @case('comparativo')
            <!-- Reporte Comparativo -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Gráfica Comparativa</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartComparativo" height="100"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Comparativo por Banco</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Banco</th>
                                    <th class="text-center">Cantidad<br>Movimientos</th>
                                    <th class="text-end">Total<br>Ingresos</th>
                                    <th class="text-end">Total<br>Egresos</th>
                                    <th class="text-end">Saldo<br>Promedio</th>
                                    <th class="text-end">Saldo<br>Actual</th>
                                    <th class="text-center">% Part.<br>Ingresos</th>
                                    <th class="text-center">% Part.<br>Egresos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($datosReporte['comparativo'] as $comp)
                                <tr>
                                    <td><strong>{{ $comp['banco'] }}</strong></td>
                                    <td class="text-center">{{ $comp['cantidad'] }}</td>
                                    <td class="text-end text-success">{{ number_format($comp['ingresos'], 2) }}</td>
                                    <td class="text-end text-danger">{{ number_format($comp['egresos'], 2) }}</td>
                                    <td class="text-end">{{ number_format($comp['promedio'], 2) }}</td>
                                    <td class="text-end"><strong>{{ number_format($comp['saldo'], 2) }}</strong></td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ number_format($comp['porc_ingresos'], 1) }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">{{ number_format($comp['porc_egresos'], 1) }}%</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @break

        @case('flujo')
            <!-- Flujo de Efectivo -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Evolución del Flujo de Efectivo</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartFlujo" height="80"></canvas>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Top 10 Ingresos</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @foreach($datosReporte['top_ingresos'] as $ing)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $ing->concepto }}</strong><br>
                                            <small class="text-muted">{{ \\Carbon\\Carbon::parse($ing->fecha)->format('d/m/Y') }} - {{ $ing->banco->nombre }}</small>
                                        </div>
                                        <div class="text-end">
                                            <h5 class="text-success mb-0">{{ number_format($ing->monto, 2) }}</h5>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Top 10 Egresos</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @foreach($datosReporte['top_egresos'] as $egr)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $egr->concepto }}</strong><br>
                                            <small class="text-muted">{{ \\Carbon\\Carbon::parse($egr->fecha)->format('d/m/Y') }} - {{ $egr->banco->nombre }}</small>
                                        </div>
                                        <div class="text-end">
                                            <h5 class="text-danger mb-0">{{ number_format($egr->monto, 2) }}</h5>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @break

        @case('conciliacion')
            <!-- Estado de Conciliación -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Movimientos Conciliados</h6>
                            <h2 class="text-success">{{ $datosReporte['conciliados'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Movimientos Pendientes</h6>
                            <h2 class="text-warning">{{ $datosReporte['pendientes'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted">% Conciliación</h6>
                            <h2 class="text-primary">{{ number_format($datosReporte['porcentaje'], 1) }}%</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Estado por Banco</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Banco</th>
                                    <th class="text-center">Movimientos Totales</th>
                                    <th class="text-center">Conciliados</th>
                                    <th class="text-center">Pendientes</th>
                                    <th class="text-center">% Conciliación</th>
                                    <th class="text-end">Diferencia Pendiente</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($datosReporte['por_banco_conc'] as $bc)
                                <tr>
                                    <td><strong>{{ $bc['banco'] }}</strong></td>
                                    <td class="text-center">{{ $bc['total'] }}</td>
                                    <td class="text-center"><span class="badge bg-success">{{ $bc['conciliados'] }}</span></td>
                                    <td class="text-center"><span class="badge bg-warning">{{ $bc['pendientes'] }}</span></td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: {{ $bc['porcentaje'] }}%">
                                                {{ number_format($bc['porcentaje'], 0) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">{{ number_format($bc['diferencia'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @break

    @endswitch

    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function exportarReporte() {
    const form = document.getElementById('formReporte');
    const formData = new FormData(form);
    formData.append('exportar', 'excel');
    
    window.location.href = '{{ route("bancos.exportar-reporte") }}?' + new URLSearchParams(formData).toString();
}

// Inicializar gráficas según el tipo de reporte
@if(request('tipo_reporte') == 'general' && isset($datosReporte))
    // Gráfica de Saldos
    new Chart(document.getElementById('chartSaldos'), {
        type: 'pie',
         {
            labels: {!! json_encode($datosReporte['por_banco']->pluck('nombre')) !!},
            datasets: [{
                 {!! json_encode($datosReporte['por_banco']->pluck('saldo_actual')) !!},
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d']
            }]
        }
    });

    // Gráfica de Movimientos
    new Chart(document.getElementById('chartMovimientos'), {
        type: 'bar',
         {
            labels: {!! json_encode($datosReporte['por_banco']->pluck('nombre')) !!},
            datasets: [{
                label: 'Ingresos',
                 {!! json_encode($datosReporte['por_banco']->pluck('ingresos')) !!},
                backgroundColor: '#28a745'
            }, {
                label: 'Egresos',
                 {!! json_encode($datosReporte['por_banco']->pluck('egresos')) !!},
                backgroundColor: '#dc3545'
            }]
        }
    });
@endif
</script>
@endpush