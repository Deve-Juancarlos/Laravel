@extends('layouts.app')

@section('title', 'Detalle Banco - ' . $infoCuenta->Banco)

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-university me-2"></i>{{ $infoCuenta->Banco }}</h1>
        <p class="text-muted">Detalle de movimientos para la cuenta: {{ $infoCuenta->Cuenta }}</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $infoCuenta->Cuenta }}</li>
@endsection

@section('content')
<div class="container-fluid bancos-detalle">
    
    <!-- Navegación del Módulo de Bancos -->
    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link" href="{{ route('contador.bancos.index') }}">
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
    
    <!-- Filtros -->
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.detalle', $infoCuenta->Cuenta) }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="fecha_fin">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filtrar Período
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs de la Cuenta -->
    <div class="row mb-4 stats-grid">
        <div class="col-md-3">
            <div class="stat-card shadow-sm info">
                <i class="fas fa-wallet"></i>
                <div class="stat-info">
                    <p class="stat-label">Saldo Total Actual</p>
                    <div class="stat-value">S/ {{ number_format($infoCuenta->saldo_actual, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm">
                <i class="fas fa-calendar-alt"></i>
                <div class="stat-info">
                    <p class="stat-label">Saldo Anterior (al {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }})</p>
                    <div class="stat-value">S/ {{ number_format($saldoAnterior, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm success">
                <i class="fas fa-arrow-down"></i>
                <div class="stat-info">
                    <p class="stat-label">Ingresos (Período)</p>
                    <div class="stat-value">S/ {{ number_format($totalesPeriodo->ingresos, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm danger">
                <i class="fas fa-arrow-up"></i>
                <div class="stat-info">
                    <p class="stat-label">Egresos (Período)</p>
                    <div class="stat-value">S/ {{ number_format($totalesPeriodo->egresos, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Movimientos -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Movimientos del Período</h6>
            @if($movimientos->hasPages())
                <small class="text-muted">Pág. {{ $movimientos->currentPage() }} de {{ $movimientos->lastPage() }}</small>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Clase</th>
                            <th>Documento</th>
                            <th class="text-end">Ingreso</th>
                            <th class="text-end">Egreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($mov->Fecha)->format('d/m/Y') }}</td>
                            <td>
                                @if($mov->Tipo == 1)
                                    <span class="badge bg-success-soft text-success">{{ $mov->tipo_descripcion }}</span>
                                @else
                                    <span class="badge bg-danger-soft text-danger">{{ $mov->tipo_descripcion }}</span>
                                @endif
                            </td>
                            <td>{{ $mov->clase_descripcion }}</td>
                            <td>{{ $mov->Documento }}</td>
                            <td class="text-end text-success">{{ $mov->ingreso > 0 ? number_format($mov->ingreso, 2) : '-' }}</td>
                            <td class="text-end text-danger">{{ $mov->egreso > 0 ? number_format($mov->egreso, 2) : '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center p-5 text-muted">No se encontraron movimientos en este período.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark fw-bold">
                        <tr>
                            <td colspan="4">TOTALES DEL PERÍODO</td>
                            <td class="text-end">S/ {{ number_format($totalesPeriodo->ingresos, 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totalesPeriodo->egresos, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">SALDO FINAL DEL PERÍODO (Saldo Ant. + Ingresos - Egresos)</td>
                            <td colspan="2" class="text-end fs-6">S/ {{ number_format($totalesPeriodo->saldo_final, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if($movimientos->hasPages())
            <div class="card-footer pagination-wrapper">
                {{ $movimientos->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Resumen Mensual -->
    <div class="card shadow-sm">
        <div class="card-header"><h6 class="mb-0">Resumen Mensual (Período Filtrado)</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mes/Año</th>
                            <th class="text-end">Ingresos</th>
                            <th class="text-end">Egresos</th>
                            <th class="text-end">Flujo Neto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resumenMensual as $resumen)
                        <tr>
                            <td><strong>{{ $resumen->mes_nombre }} {{ $resumen->anio }}</strong></td>
                            <td class="text-end text-success">S/ {{ number_format($resumen->ingresos_mes, 2) }}</td>
                            <td class="text-end text-danger">S/ {{ number_format($resumen->egresos_mes, 2) }}</td>
                            <td class="text-end fw-bold">S/ {{ number_format($resumen->saldo_mes, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center p-3 text-muted">No hay datos suficientes para un resumen.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

