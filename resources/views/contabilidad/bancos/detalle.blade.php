@extends('layouts.app')

@section('title', 'Detalle Banco - ' . $infoCuenta->Banco)

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos/detalle.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-university me-2"></i>{{ $infoCuenta->Banco }}</h1>
        <p class="text-muted">Detalle de movimientos para la cuenta: {{ $infoCuenta->Cuenta }}</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $infoCuenta->Cuenta }}</li>
@endsection

@section('content')
<div class="bancos-detalle">
    
    <!-- Navegación del Módulo de Bancos con Gradiente Púrpura -->
    <nav class="nav nav-tabs eerr-subnav mb-4 banks-nav-gradient">
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
    
    <!-- Filtros con Gradiente Púrpura -->
    <div class="card shadow-sm filters-card mb-4 filtros-gradient">
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
                        <button type="submit" class="btn btn-primary w-100 banks-btn-gradient">
                            <i class="fas fa-filter me-1"></i> Filtrar Período
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs de la Cuenta con Cards Modernos -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card balance-card">
                <div class="kpi-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="kpi-content">
                    <h3>S/ {{ number_format($infoCuenta->saldo_actual, 2) }}</h3>
                    <p>Saldo Total Actual</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card previous-balance-card">
                <div class="kpi-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="kpi-content">
                    <h3>S/ {{ number_format($saldoAnterior, 2) }}</h3>
                    <p>Saldo Anterior ({{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }})</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card income-card">
                <div class="kpi-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="kpi-content">
                    <h3>S/ {{ number_format($totalesPeriodo->ingresos, 2) }}</h3>
                    <p>Ingresos (Período)</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card expense-card">
                <div class="kpi-icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="kpi-content">
                    <h3>S/ {{ number_format($totalesPeriodo->egresos, 2) }}</h3>
                    <p>Egresos (Período)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Movimientos con Tabla Mejorada -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Movimientos del Período</h6>
            @if($movimientos->hasPages())
                <small class="text-muted">Pág. {{ $movimientos->currentPage() }} de {{ $movimientos->lastPage() }}</small>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 modern-table">
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
                            <td>
                                <span class="date-badge">
                                    {{ \Carbon\Carbon::parse($mov->Fecha)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                @if($mov->Tipo == 1)
                                    <span class="type-badge type-income">
                                        <i class="fas fa-arrow-down me-1"></i>{{ $mov->tipo_descripcion }}
                                    </span>
                                @else
                                    <span class="type-badge type-expense">
                                        <i class="fas fa-arrow-up me-1"></i>{{ $mov->tipo_descripcion }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="clase-text">{{ $mov->clase_descripcion }}</span>
                            </td>
                            <td>
                                <span class="doc-text">{{ $mov->Documento }}</span>
                            </td>
                            <td class="text-end">
                                @if($mov->ingreso > 0)
                                    <span class="amount-positive">S/ {{ number_format($mov->ingreso, 2) }}</span>
                                @else
                                    <span class="amount-zero">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($mov->egreso > 0)
                                    <span class="amount-negative">S/ {{ number_format($mov->egreso, 2) }}</span>
                                @else
                                    <span class="amount-zero">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center p-5 text-muted">
                                <i class="fas fa-info-circle me-2"></i>No se encontraron movimientos en este período.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark fw-bold">
                        <tr>
                            <td colspan="4">TOTALES DEL PERÍODO</td>
                            <td class="text-end">S/ {{ number_format($totalesPeriodo->ingresos, 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totalesPeriodo->egresos, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">SALDO FINAL DEL PERÍODO</td>
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

    <!-- Resumen Mensual con Cards -->
    <div class="row">
        @forelse($resumenMensual as $resumen)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="monthly-summary-card">
                <div class="summary-header">
                    <h5>{{ $resumen->mes_nombre }} {{ $resumen->anio }}</h5>
                </div>
                <div class="summary-metrics">
                    <div class="summary-item income">
                        <div class="summary-icon">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="summary-content">
                            <span class="summary-label">Ingresos</span>
                            <span class="summary-value">S/ {{ number_format($resumen->ingresos_mes, 2) }}</span>
                        </div>
                    </div>
                    <div class="summary-item expense">
                        <div class="summary-icon">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div class="summary-content">
                            <span class="summary-label">Egresos</span>
                            <span class="summary-value">S/ {{ number_format($resumen->egresos_mes, 2) }}</span>
                        </div>
                    </div>
                    <div class="summary-net">
                        <div class="net-item">
                            <span>Flujo Neto:</span>
                            <span class="fw-bold {{ ($resumen->saldo_mes >= 0) ? 'text-success' : 'text-danger' }}">
                                S/ {{ number_format($resumen->saldo_mes, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>No hay datos suficientes para un resumen.
            </div>
        </div>
        @endforelse
    </div>

</div>
@endsection