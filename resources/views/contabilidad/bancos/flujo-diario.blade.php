@extends('layouts.app')

@section('title', 'Flujo de Caja Diario')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-calendar-day me-2"></i>Flujo de Caja Diario</h1>
        <p class="text-muted">Reporte de saldos diarios (Saldos Iniciales + Ingresos - Egresos = Saldos Finales)</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Flujo Diario</li>
@endsection

@section('content')
<div class="container-fluid flujo-diario-view">

    <!-- Navegación del Módulo de Bancos -->
    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link" href="{{ route('contador.bancos.index') }}">
            <i class="fas fa-home me-1"></i> Dashboard Bancos
        </a>
        <a class="nav-link active" href="{{ route('contador.bancos.flujo-diario') }}">
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
            <form method="GET" action="{{ route('contador.bancos.flujo-diario') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="fecha">Fecha del Reporte</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" value="{{ $fecha }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="banco_id">Filtrar por Banco (Opcional)</label>
                        <select name="banco_id" id="banco_id" class="form-select">
                            <option value="">Todos los bancos</option>
                            @foreach($listaBancos as $banco)
                                <option value="{{ $banco->Cuenta }}" {{ $bancoSeleccionado == $banco->Cuenta ? 'selected' : '' }}>
                                    {{ $banco->Banco }} - ({{ $banco->Cuenta }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Generar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs Totales -->
    <div class="row mb-4 stats-grid">
        <div class="col-md-3">
            <div class="stat-card shadow-sm bg-light">
                <div class="stat-info">
                    <p class="stat-label">Saldo Inicial Total</p>
                    <div class="stat-value">S/ {{ number_format($totalesGenerales['saldo_inicial_total'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm success">
                <div class="stat-info">
                    <p class="stat-label">Ingresos del Día</p>
                    <div class="stat-value">S/ {{ number_format($totalesGenerales['ingresos_total'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm danger">
                <div class="stat-info">
                    <p class="stat-label">Egresos del Día</p>
                    <div class="stat-value">S/ {{ number_format($totalesGenerales['egresos_total'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm info">
                <div class="stat-info">
                    <p class="stat-label">Saldo Final Total</p>
                    <div class="stat-value">S/ {{ number_format($totalesGenerales['saldo_final_total'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flujo por Banco (SP) -->
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h6 class="mb-0">Flujo por Banco (al {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }})</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Cuenta</th>
                            <th>Banco</th>
                            <th class="text-end">Saldo Inicial</th>
                            <th class="text-end">Ingresos</th>
                            <th class="text-end">Egresos</th>
                            <th class="text-end">Saldo Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($flujoCaja as $flujo)
                        <tr>
                            <td>{{ $flujo->Cuenta }}</td>
                            <td>{{ $flujo->Banco }}</td>
                            <td class="text-end">S/ {{ number_format($flujo->saldo_inicial, 2) }}</td>
                            <td class="text-end text-success">{{ $flujo->ingresos_dia > 0 ? 'S/ '.number_format($flujo->ingresos_dia, 2) : '-' }}</td>
                            <td class="text-end text-danger">{{ $flujo->egresos_dia > 0 ? 'S/ '.number_format($flujo->egresos_dia, 2) : '-' }}</td>
                            <td class="text-end fw-bold">S/ {{ number_format($flujo->saldo_final, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center p-5 text-muted">No se encontraron datos de flujo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detalle de Movimientos del Día -->
    <div class="card shadow-sm">
        <div class="card-header"><h6 class="mb-0">Detalle de Movimientos del Día</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Banco</th>
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
                            <td>{{ $mov->Banco }}</td>
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
                        <tr><td colspan="6" class="text-center p-5 text-muted">No se encontraron movimientos en esta fecha.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

