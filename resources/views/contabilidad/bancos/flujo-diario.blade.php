@extends('layouts.app')

@section('title', 'Flujo de Caja Diario')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos/flujo-diario.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div class="page-title-enhanced">
        <h1>
            <i class="fas fa-calendar-day me-3"></i>
            Flujo de Caja Diario
        </h1>
        <p>
            <i class="fas fa-chart-line me-2"></i>
            Reporte de saldos diarios (Saldos Iniciales + Ingresos - Egresos = Saldos Finales)
        </p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('contador.dashboard.contador') }}">
            <i class="fas fa-calculator me-1"></i>
            Contabilidad
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('contador.bancos.index') }}">
            <i class="fas fa-university me-1"></i>
            Bancos
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-calendar-day me-1"></i>
        Flujo Diario
    </li>
@endsection

@section('content')
<div class="container-fluid flujo-diario-view">

    {{-- =========== NAVEGACIÓN DEL MÓDULO =========== --}}
    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link" href="{{ route('contador.bancos.index') }}">
            <i class="fas fa-tachometer-alt me-2"></i>
            Dashboard Bancos
        </a>
        <a class="nav-link active" href="{{ route('contador.bancos.flujo-diario') }}">
            <i class="fas fa-calendar-day me-2"></i>
            Flujo Diario
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.diario') }}">
            <i class="fas fa-calendar-alt me-2"></i>
            Reporte Diario
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.mensual') }}">
            <i class="fas fa-calendar-week me-2"></i>
            Resumen Mensual
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.conciliacion') }}">
            <i class="fas fa-tasks me-2"></i>
            Conciliación
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.transferencias') }}">
            <i class="fas fa-exchange-alt me-2"></i>
            Transferencias
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.reporte') }}">
            <i class="fas fa-file-invoice me-2"></i>
            Reportes
        </a>
    </nav>
    {{-- =========== FIN NAVEGACIÓN =========== --}}

    {{-- =========== FILTROS =========== --}}
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.flujo-diario') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="fecha">
                            <i class="fas fa-calendar-plus me-1"></i>
                            Fecha del Reporte
                        </label>
                        <input 
                            type="date" 
                            name="fecha" 
                            id="fecha" 
                            class="form-control" 
                            value="{{ $fecha }}"
                        >
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="banco_id">
                            <i class="fas fa-university me-1"></i>
                            Filtrar por Banco (Opcional)
                        </label>
                        <select name="banco_id" id="banco_id" class="form-select">
                            <option value="">Todos los bancos</option>
                            @foreach($listaBancos as $banco)
                                <option 
                                    value="{{ $banco->Cuenta }}" 
                                    {{ $bancoSeleccionado == $banco->Cuenta ? 'selected' : '' }}
                                >
                                    {{ $banco->Banco }} - ({{ $banco->Cuenta }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>
                            Generar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- =========== KPIs TOTALES =========== --}}
    <div class="stats-grid">
        <div class="stat-card shadow-sm primary">
            <div class="stat-icon">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="stat-info">
                <p class="stat-label">
                    <i class="fas fa-flag-checkered me-1"></i>
                    Saldo Inicial Total
                </p>
                <div class="stat-value">
                    S/ {{ number_format($totalesGenerales['saldo_inicial_total'], 2) }}
                </div>
            </div>
        </div>
        
        <div class="stat-card shadow-sm success">
            <div class="stat-icon">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stat-info">
                <p class="stat-label">
                    <i class="fas fa-coins me-1"></i>
                    Ingresos del Día
                </p>
                <div class="stat-value">
                    S/ {{ number_format($totalesGenerales['ingresos_total'], 2) }}
                </div>
            </div>
        </div>
        
        <div class="stat-card shadow-sm danger">
            <div class="stat-icon">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="stat-info">
                <p class="stat-label">
                    <i class="fas fa-money-bill-wave me-1"></i>
                    Egresos del Día
                </p>
                <div class="stat-value">
                    S/ {{ number_format($totalesGenerales['egresos_total'], 2) }}
                </div>
            </div>
        </div>
        
        <div class="stat-card shadow-sm info">
            <div class="stat-icon">
                <i class="fas fa-stop-circle"></i>
            </div>
            <div class="stat-info">
                <p class="stat-label">
                    <i class="fas fa-chart-area me-1"></i>
                    Saldo Final Total
                </p>
                <div class="stat-value">
                    S/ {{ number_format($totalesGenerales['saldo_final_total'], 2) }}
                </div>
            </div>
        </div>
    </div>

    {{-- =========== FLUJO POR BANCO =========== --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-chart-pie me-2"></i>
                Flujo por Banco (al {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }})
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <i class="fas fa-credit-card me-1"></i>
                                Cuenta
                            </th>
                            <th>
                                <i class="fas fa-university me-1"></i>
                                Banco
                            </th>
                            <th class="text-end">
                                <i class="fas fa-play-circle me-1 text-primary"></i>
                                Saldo Inicial
                            </th>
                            <th class="text-end">
                                <i class="fas fa-arrow-down me-1 text-success"></i>
                                Ingresos
                            </th>
                            <th class="text-end">
                                <i class="fas fa-arrow-up me-1 text-danger"></i>
                                Egresos
                            </th>
                            <th class="text-end">
                                <i class="fas fa-stop-circle me-1 text-info"></i>
                                Saldo Final
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($flujoCaja as $flujo)
                        <tr>
                            <td>
                                <strong>
                                    <i class="fas fa-hashtag me-1 text-muted"></i>
                                    {{ $flujo->Cuenta }}
                                </strong>
                            </td>
                            <td>
                                <div class="fw-medium">
                                    <i class="fas fa-building me-1 text-primary"></i>
                                    {{ $flujo->Banco }}
                                </div>
                            </td>
                            <td class="text-end fw-bold">
                                S/ {{ number_format($flujo->saldo_inicial, 2) }}
                            </td>
                            <td class="text-end text-success">
                                <strong>
                                    {{ $flujo->ingresos_dia > 0 ? 'S/ '.number_format($flujo->ingresos_dia, 2) : '-' }}
                                </strong>
                            </td>
                            <td class="text-end text-danger">
                                <strong>
                                    {{ $flujo->egresos_dia > 0 ? 'S/ '.number_format($flujo->egresos_dia, 2) : '-' }}
                                </strong>
                            </td>
                            <td class="text-end fw-bold text-info">
                                S/ {{ number_format($flujo->saldo_final, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center p-5 text-muted">
                                <i class="fas fa-chart-line fa-3x mb-3 d-block text-muted"></i>
                                <h5>No se encontraron datos de flujo</h5>
                                <p>No hay información disponible para la fecha seleccionada.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- =========== DETALLE DE MOVIMIENTOS DEL DÍA =========== --}}
    <div class="card shadow-sm">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-list-alt me-2"></i>
                Detalle de Movimientos del Día
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <i class="fas fa-university me-1"></i>
                                Banco
                            </th>
                            <th>
                                <i class="fas fa-tags me-1"></i>
                                Tipo
                            </th>
                            <th>
                                <i class="fas fa-layer-group me-1"></i>
                                Clase
                            </th>
                            <th>
                                <i class="fas fa-file-alt me-1"></i>
                                Documento
                            </th>
                            <th class="text-end">
                                <i class="fas fa-arrow-down me-1 text-success"></i>
                                Ingreso
                            </th>
                            <th class="text-end">
                                <i class="fas fa-arrow-up me-1 text-danger"></i>
                                Egreso
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                        <tr>
                            <td>
                                <div class="fw-medium">
                                    <i class="fas fa-building me-1 text-primary"></i>
                                    {{ $mov->Banco }}
                                </div>
                            </td>
                            <td>
                                @if($mov->Tipo == 1)
                                    <span class="badge bg-success-soft">
                                        <i class="fas fa-arrow-down me-1"></i>
                                        {{ $mov->tipo_descripcion }}
                                    </span>
                                @else
                                    <span class="badge bg-danger-soft">
                                        <i class="fas fa-arrow-up me-1"></i>
                                        {{ $mov->tipo_descripcion }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium">
                                    <i class="fas fa-layer-group me-1 text-muted"></i>
                                    {{ $mov->clase_descripcion }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-file-invoice me-1"></i>
                                    {{ $mov->Documento }}
                                </span>
                            </td>
                            <td class="text-end text-success">
                                <strong>
                                    {{ $mov->ingreso > 0 ? number_format($mov->ingreso, 2) : '-' }}
                                </strong>
                            </td>
                            <td class="text-end text-danger">
                                <strong>
                                    {{ $mov->egreso > 0 ? number_format($mov->egreso, 2) : '-' }}
                                </strong>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center p-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block text-muted"></i>
                                <h5>No se encontraron movimientos</h5>
                                <p>No hay movimientos registrados para esta fecha.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection