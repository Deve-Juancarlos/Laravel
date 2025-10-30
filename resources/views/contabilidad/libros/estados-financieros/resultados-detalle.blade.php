@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('contador.estado-resultados.index') }}">Estado de Resultados</a></li>
            <li class="breadcrumb-item active"><i class="fas fa-search"></i> Detalle Cuenta</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary">
                <i class="fas fa-search me-2"></i>
                Detalle de Cuenta -{{ $cuentaMostrar }}
            </h2>
            <p class="text-muted mb-0">Análisis detallado de movimientos contables</p>
        </div>
        <div class="text-end">
            <small class="text-muted">Período: <strong>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</strong></small>
        </div>
    </div>

    <!-- Información de la Cuenta -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Información de la Cuenta
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Código:</strong></td>
                            <td><span class="badge bg-primary fs-6">{{ $cuentaMostrar }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Clasificación:</strong></td>
                            <td>
                                <span class="badge {{ $clasificacion == 'INGRESO' ? 'bg-success' : 'bg-danger' }} fs-6">
                                    {{ $clasificacion }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Tipo:</strong></td>
                            <td>
                                @if($clasificacion == 'INGRESO')
                                    <i class="fas fa-plus-circle text-success me-1"></i>Cuenta de Ingresos (4xxx)
                                @else
                                    <i class="fas fa-minus-circle text-danger me-1"></i>Cuenta de Gastos (5xxx)
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Movimientos:</strong></td>
                            <td><span class="badge bg-info fs-6">{{ $movimientos->count() }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Total Débito:</strong></td>
                            <td class="text-success"><strong>S/. {{ number_format($movimientos->sum('debito'), 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Total Crédito:</strong></td>
                            <td class="text-danger"><strong>S/. {{ number_format($movimientos->sum('credito'), 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Saldos -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Total Débito</h5>
                            <h3 class="text-white">{{ number_format($movimientos->sum('debito'), 2) }}</h3>
                            <small>S/.</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-arrow-down fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Total Crédito</h5>
                            <h3 class="text-white">{{ number_format($movimientos->sum('credito'), 2) }}</h3>
                            <small>S/.</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-arrow-up fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Saldo Neto</h5>
                            @php
                                $saldoNeto = $movimientos->sum('debito') - $movimientos->sum('credito');
                            @endphp
                            <h3 class="text-white {{ $saldoNeto >= 0 ? 'text-white' : 'text-warning' }}">{{ number_format(abs($saldoNeto), 2) }}</h3>
                            <small>S/. ({{ $saldoNeto >= 0 ? 'Deudor' : 'Acreedor' }})</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-balance-scale fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Promedio Movimiento</h5>
                            @php
                                $promedio = $movimientos->count() > 0 ? ($movimientos->sum('debito') + $movimientos->sum('credito')) / ($movimientos->count() * 2) : 0;
                            @endphp
                            <h3 class="text-white">{{ number_format($promedio, 2) }}</h3>
                            <small>S/.</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-calculator fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.estado-resultados.detalle', ['cuenta' => $cuentaMostrar === 'Todas las Cuentas' ? 'all' : $cuentaMostrar]) }}">

                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Buscar por Concepto</label>
                        <input type="text" name="busqueda" class="form-control" placeholder="Buscar en descripción...">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('contador.estado-resultados.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Movimientos Detallados -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Movimientos Detallados - Cuenta {{ $cuentaMostrar }}:
            </h5>
            <div>
                <button class="btn btn-sm btn-light" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
                <button class="btn btn-sm btn-light" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($movimientos->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tablaMovimientos">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Número Asiento</th>
                            <th>Concepto</th>
                            <th>Auxiliar</th>
                            <th class="text-end">Débito</th>
                            <th class="text-end">Crédito</th>
                            <th class="text-end">Saldo Acumulado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $saldoAcumulado = 0;
                            $contador = 1;
                        @endphp
                        @foreach($movimientos as $movimiento)
                        @php
                            // Calcular saldo acumulado
                            $saldoAcumulado += ($movimiento->debito - $movimiento->credito);
                        @endphp
                        <tr>
                            <td>{{ $contador }}</td>
                            <td>{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                            <td><code>{{ $movimiento->numero }}</code></td>
                            <td>{{ Str::limit($movimiento->concepto, 50) }}</td>
                            <td>{{ $movimiento->auxiliar ?? 'N/A' }}</td>
                            <td class="text-end text-success">{{ $movimiento->debito > 0 ? number_format($movimiento->debito, 2) : '-' }}</td>
                            <td class="text-end text-danger">{{ $movimiento->credito > 0 ? number_format($movimiento->credito, 2) : '-' }}</td>
                            <td class="text-end {{ $saldoAcumulado >= 0 ? 'text-success' : 'text-danger' }}">
                                <strong>{{ number_format($saldoAcumulado, 2) }}</strong>
                            </td>
                        </tr>
                        @php $contador++; @endphp
                        @endforeach
                    </tbody>
                    <tfoot class="table-info">
                        <tr>
                            <th colspan="5">TOTALES</th>
                            <th class="text-end">{{ number_format($movimientos->sum('debito'), 2) }}</th>
                            <th class="text-end">{{ number_format($movimientos->sum('credito'), 2) }}</th>
                            <th class="text-end">{{ number_format($saldoAcumulado, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay movimientos para esta cuenta</h5>
                <p class="text-muted">No se encontraron registros en el período seleccionado.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Análisis de Patrones -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Distribución de Movimientos
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $totalMovimientos = $movimientos->count();
                        $movimientosConDebito = $movimientos->where('debito', '>', 0)->count();
                        $movimientosConCredito = $movimientos->where('credito', '>', 0)->count();
                        $movimientosAmbos = $movimientos->where('debito', '>', 0)->where('credito', '>', 0)->count();
                    @endphp
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Movimientos con Débito</span>
                            <strong>{{ $movimientosConDebito }}</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: {{ $totalMovimientos > 0 ? ($movimientosConDebito / $totalMovimientos) * 100 : 0 }}%"></div>
                        </div>
                        <small class="text-muted">{{ $totalMovimientos > 0 ? number_format(($movimientosConDebito / $totalMovimientos) * 100, 1) : 0 }}% del total</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Movimientos con Crédito</span>
                            <strong>{{ $movimientosConCredito }}</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-danger" style="width: {{ $totalMovimientos > 0 ? ($movimientosConCredito / $totalMovimientos) * 100 : 0 }}%"></div>
                        </div>
                        <small class="text-muted">{{ $totalMovimientos > 0 ? number_format(($movimientosConCredito / $totalMovimientos) * 100, 1) : 0 }}% del total</small>
                    </div>
                    
                    <div class="mb-0">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Movimientos Mixtos</span>
                            <strong>{{ $movimientosAmbos }}</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: {{ $totalMovimientos > 0 ? ($movimientosAmbos / $totalMovimientos) * 100 : 0 }}%"></div>
                        </div>
                        <small class="text-muted">{{ $totalMovimientos > 0 ? number_format(($movimientosAmbos / $totalMovimientos) * 100, 1) : 0 }}% del total</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Análisis Temporal
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $fechas = $movimientos->pluck('fecha')->map(function($fecha) {
                            return \Carbon\Carbon::parse($fecha)->format('Y-m');
                        })->countBy();
                        
                        $mesesActivos = $fechas->keys()->count();
                        $promedioPorMes = $mesesActivos > 0 ? $totalMovimientos / $mesesActivos : 0;
                        $periodoTotal = \Carbon\Carbon::parse($fechaInicio)->diffInMonths(\Carbon\Carbon::parse($fechaFin)) + 1;
                    @endphp
                    
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h4 class="text-primary">{{ $mesesActivos }}</h4>
                            <small class="text-muted">Meses Activos</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-success">{{ number_format($promedioPorMes, 1) }}</h4>
                            <small class="text-muted">Promedio/Mes</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info">{{ $periodoTotal }}</h4>
                            <small class="text-muted">Meses en Período</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning">{{ number_format(($mesesActivos / $periodoTotal) * 100, 0) }}%</h4>
                            <small class="text-muted">Actividad</small>
                        </div>
                    </div>
                    
                    @if($fechas->count() > 0)
                    <hr>
                    <h6>Distribución Mensual:</h6>
                    @foreach($fechas->take(6) as $mes => $cantidad)
                    <div class="d-flex justify-content-between">
                        <span>{{ \Carbon\Carbon::parse($mes . '-01')->format('M Y') }}</span>
                        <strong>{{ $cantidad }} mov.</strong>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('contador.estado-resultados.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Volver al Estado Principal
                    </a>
                    <a href="{{ route('contador.estado-resultados.periodos') }}" class="btn btn-outline-info ms-2">
                        <i class="fas fa-calendar-alt"></i> Ver Períodos
                    </a>
                </div>
                <div>
                    <a href="{{ route('contador.estado-resultados.comparativo') }}" class="btn btn-warning me-2">
                        <i class="fas fa-comparison"></i> Comparativo
                    </a>            
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#tablaMovimientos').DataTable({
        "pageLength": 25,
        "order": [[1, "asc"]], // Ordenar por fecha
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
        }
    });
});

function exportToExcel() {
    // Implementar exportación a Excel
    alert('Funcionalidad de exportación a Excel será implementada próximamente');
}


function printPage() {
    window.print();
}
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .table {
        font-size: 12px;
    }
}
</style>
@endsection