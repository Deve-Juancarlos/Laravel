@extends('layouts.app')

@section('title', 'Transferencias Bancarias')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos/transferencia.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div class="transferencias-gradient-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <i class="fas fa-exchange-alt me-3" style="font-size: 2rem;"></i>
                <div>
                    <h1 class="mb-1">Transferencias Bancarias</h1>
                    <p class="mb-0 opacity-75">Registro de transferencias entre cuentas propias</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Transferencias</li>
@endsection

@section('content')
<div class="transferencias-conteiner">

    <!-- Navegación del Módulo de Bancos -->
    <nav class="nav nav-tabs transferencias-nav">
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
        <a class="nav-link active" href="{{ route('contador.bancos.transferencias') }}">
            <i class="fas fa-exchange-alt me-1"></i> Transferencias
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.reporte') }}">
            <i class="fas fa-file-invoice me-1"></i> Reportes
        </a>
    </nav>

    <!-- KPIs de Transferencias -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon primary">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="kpi-value">{{ $resumenTransferencias['total_transferencias'] }}</div>
                <p class="kpi-label">Total Transferencias</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon success">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="kpi-value">S/ {{ number_format($resumenTransferencias['monto_total'], 2) }}</div>
                <p class="kpi-label">Monto Total</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="kpi-value">{{ $resumenTransferencias['transferencias_completas'] ?? 0 }}</div>
                <p class="kpi-label">Transferencias Completas</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="kpi-value">{{ $resumenTransferencias['transferencias_pendientes'] ?? 0 }}</div>
                <p class="kpi-label">Transferencias Pendientes</p>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm filters-card">
         <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.transferencias') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="fecha_fin">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-gradient w-100">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Resumen -->
    <div class="alert resumen-alert" role="alert">
        <h5 class="alert-heading">
            <i class="fas fa-info-circle me-2"></i>Resumen de Transferencias
        </h5>
        <p>
            Se encontraron <strong>{{ $resumenTransferencias['total_transferencias'] }}</strong> transferencias por un monto total de <strong>S/ {{ number_format($resumenTransferencias['monto_total'], 2) }}</strong>.
        </p>
    </div>

    <!-- Tabla de Transferencias -->
    <div class="card transferencias-table">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Detalle de Transferencias
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><i class="fas fa-calendar me-1"></i>Fecha</th>
                            <th><i class="fas fa-file-alt me-1"></i>Documento</th>
                            <th><i class="fas fa-arrow-right me-1"></i>Cuenta Origen</th>
                            <th><i class="fas fa-arrow-left me-1"></i>Cuenta Destino</th>
                            <th class="text-end"><i class="fas fa-dollar-sign me-1"></i>Monto</th>
                            <th class="text-center"><i class="fas fa-check-circle me-1"></i>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transferencias as $trf)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="date-badge me-2">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                    </div>
                                    <span class="fw-600">{{ \Carbon\Carbon::parse($trf->Fecha)->format('d/m/Y') }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="document-badge">
                                    <i class="fas fa-file-invoice me-1"></i>
                                    {{ $trf->Documento }}
                                </span>
                            </td>
                            <td>
                                <div class="account-info">
                                    <div class="fw-600 text-primary">{{ $trf->banco_origen }}</div>
                                    <small class="text-muted">({{ $trf->cuenta_origen }})</small>
                                </div>
                            </td>
                            <td>
                                <div class="account-info">
                                    <div class="fw-600 text-success">{{ $trf->banco_destino }}</div>
                                    <small class="text-muted">({{ $trf->cuenta_destino }})</small>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="monto-amount">S/ {{ number_format($trf->Monto, 2) }}</span>
                            </td>
                            <td class="text-center">
                                @if($trf->estado_transferencia == 'COMPLETA')
                                    <span class="badge badge-estado badge-completa">
                                        <i class="fas fa-check me-1"></i>Completa
                                    </span>
                                @else
                                    <span class="badge badge-estado badge-pendiente">
                                        <i class="fas fa-clock me-1"></i>Pendiente
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-exchange-alt"></i>
                                <h5>No hay transferencias</h5>
                                <p>No se encontraron transferencias en este período.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transferencias->hasPages())
            <div class="card-footer pagination-wrapper">
                {{ $transferencias->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection