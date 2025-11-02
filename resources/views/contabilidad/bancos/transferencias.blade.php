@extends('layouts.app')

@section('title', 'Transferencias Bancarias')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-exchange-alt me-2"></i>Transferencias</h1>
        <p class="text-muted">Registro de transferencias entre cuentas propias.</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Transferencias</li>
@endsection

@section('content')
<div class="container-fluid">

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
        <a class="nav-link active" href="{{ route('contador.bancos.transferencias') }}">
            <i class="fas fa-exchange-alt me-1"></i> Transferencias
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.reporte') }}">
            <i class="fas fa-file-invoice me-1"></i> Reportes
        </a>
    </nav>

    <!-- Filtros -->
    <div class="card shadow-sm filters-card mb-4">
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
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Resumen -->
    <div class="alert alert-info" role="alert">
        <h5 class="alert-heading">Resumen de Transferencias</h5>
        <p>
            Se encontraron <strong>{{ $resumenTransferencias['total_transferencias'] }}</strong> transferencias por un monto total de <strong>S/ {{ number_format($resumenTransferencias['monto_total'], 2) }}</strong>.
        </p>
    </div>

    <!-- Tabla de Transferencias -->
    <div class="card shadow-sm">
        <div class="card-header"><h6 class="mb-0">Detalle de Transferencias</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Documento</th>
                            <th>Cuenta Origen</th>
                            <th>Cuenta Destino</th>
                            <th class="text-end">Monto</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transferencias as $trf)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($trf->Fecha)->format('d/m/Y') }}</td>
                            <td>{{ $trf->Documento }}</td>
                            <td>{{ $trf->banco_origen }} ({{ $trf->cuenta_origen }})</td>
                            <td>{{ $trf->banco_destino }} ({{ $trf->cuenta_destino }})</td>
                            <td class="text-end fw-bold">S/ {{ number_format($trf->Monto, 2) }}</td>
                            <td class="text-center">
                                @if($trf->estado_transferencia == 'COMPLETA')
                                    <span class="badge bg-success">Completa</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center p-5 text-muted">No se encontraron transferencias en este período.</td></tr>
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
