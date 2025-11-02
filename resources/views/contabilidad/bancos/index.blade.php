@extends('layouts.app')

@section('title', 'Gestión de Bancos')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-university me-2"></i>Gestión de Bancos</h1>
        <p class="text-muted">Dashboard principal de movimientos y saldos bancarios.</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item active" aria-current="page">Bancos</li>
@endsection

@section('content')
<div class="container-fluid bancos-dashboard">

    <!-- =========== NAVEGACIÓN DEL MÓDULO =========== -->
    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link active" href="{{ route('contador.bancos.index') }}">
            <i class="fas fa-home me-1"></i> Dashboard Bancos
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.flujo-diario') }}">
            <i class="fas fa-calendar-day me-1"></i> Flujo Diario
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.diario') }}">
            <i class="fas fa-calendar-alt me-1"></i> Reporte Diario
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.mensual') }}">
            <i class="fas fa-calendar-week me-1"></i> Resumen Mensual
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.conciliacion') }}">
            <i class="fas fa-tasks me-1"></i> Conciliación
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.transferencias') }}">
            <i class="fas fa-exchange-alt me-1"></i> Transferencias
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.reporte') }}">
            <i class="fas fa-file-invoice me-1"></i> Reportes
        </a>
    </nav>
    <!-- =========== FIN NAVEGACIÓN =========== -->


    <!-- Filtros -->
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_fin">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="cuenta">Cuenta Bancaria</label>
                        <select name="cuenta" id="cuenta" class="form-select">
                            <option value="">Todas las cuentas</option>
                            @foreach($listaBancos as $banco)
                                <option value="{{ $banco->Cuenta }}" {{ $cuentaSeleccionada == $banco->Cuenta ? 'selected' : '' }}>
                                    {{ $banco->Banco }} - ({{ $banco->Cuenta }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs del Período -->
    <div class="row mb-4 stats-grid">
        <div class="col-md-4">
            <div class="stat-card shadow-sm success">
                <i class="fas fa-arrow-down"></i>
                <div class="stat-info">
                    <p class="stat-label">Ingresos del Período</p>
                    <div class="stat-value">S/ {{ number_format($totalesPeriodo->total_ingresos, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card shadow-sm danger">
                <i class="fas fa-arrow-up"></i>
                <div class="stat-info">
                    <p class="stat-label">Egresos del Período</p>
                    <div class="stat-value">S/ {{ number_format($totalesPeriodo->total_egresos, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card shadow-sm info">
                <i class="fas fa-balance-scale"></i>
                <div class="stat-info">
                    <p class="stat-label">Flujo Neto del Período</p>
                    <div class="stat-value">S/ {{ number_format($totalesPeriodo->total_ingresos - $totalesPeriodo->total_egresos, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Saldos y Resumen -->
        <div class="col-lg-4">
            <!-- Saldos Actuales -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-wallet me-2"></i>Saldos Actuales (Total)</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($saldosActuales as $saldo)
                        <li class="list-group-item saldo-card">
                            <a href="{{ route('contador.bancos.detalle', $saldo->Cuenta) }}" class="text-decoration-none">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold text-primary">{{ $saldo->Banco }}</span>
                                    <span class="saldo-value {{ $saldo->saldo_actual >= 0 ? 'text-success' : 'text-danger' }}">
                                        S/ {{ number_format($saldo->saldo_actual, 2) }}
                                    </span>
                                </div>
                                <small class="text-muted">{{ $saldo->Cuenta }} | {{ $saldo->total_movimientos }} movs.</small>
                            </a>
                        </li>
                        @empty
                        <li class="list-group-item text-muted text-center p-3">No hay cuentas bancarias registradas.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer text-center fw-bold">
                    Total Disponible: S/ {{ number_format($saldosActuales->sum('saldo_actual'), 2) }}
                </div>
            </div>

            <!-- Resumen por Cuenta (Período) -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Resumen del Período (Filtrado)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Banco</th>
                                    <th class="text-end">Ingresos</th>
                                    <th class="text-end">Egresos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($resumenCuentas as $resumen)
                                <tr>
                                    <td>
                                        <a href="{{ route('contador.bancos.detalle', $resumen->Cuenta) }}" class="text-decoration-none fw-500">
                                            {{ $resumen->Banco }}
                                        </a>
                                    </td>
                                    <td class="text-end text-success">S/ {{ number_format($resumen->total_ingresos, 2) }}</td>
                                    <td class="text-end text-danger">S/ {{ number_format($resumen->total_egresos, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center p-3 text-muted">No hay datos en el período.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Movimientos -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>Últimos Movimientos (Período)</h6>
                    @if($movimientosBancarios->hasPages())
                        <small class="text-muted">Pág. {{ $movimientosBancarios->currentPage() }} de {{ $movimientosBancarios->lastPage() }}</small>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Banco</th>
                                    <th>Concepto</th>
                                    <th>Documento</th>
                                    <th class="text-end">Ingreso</th>
                                    <th class="text-end">Egreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movimientosBancarios as $mov)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($mov->Fecha)->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('contador.bancos.detalle', $mov->Cuenta) }}" class="badge bg-primary-soft text-primary text-decoration-none">
                                            {{ $mov->Banco }}
                                        </a>
                                    </td>
                                    <td>{{ $mov->clase_descripcion }}</td>
                                    <td>{{ $mov->Documento }}</td>
                                    <td class="text-end text-success">{{ $mov->ingreso > 0 ? number_format($mov->ingreso, 2) : '-' }}</td>
                                    <td class="text-end text-danger">{{ $mov->egreso > 0 ? number_format($mov->egreso, 2) : '-' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center p-5 text-muted">No se encontraron movimientos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($movimientosBancarios->hasPages())
                    <div class="card-footer pagination-wrapper">
                        {{ $movimientosBancarios->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

