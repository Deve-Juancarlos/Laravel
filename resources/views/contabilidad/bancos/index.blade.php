@extends('layouts.app')

@section('title', 'Gestión de Bancos')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos/bancos.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div class="page-title-enhanced">
        <h1>
            <i class="fas fa-university me-3"></i>
            Gestión de Bancos
        </h1>
        <p>
            <i class="fas fa-chart-line me-2"></i>
            Dashboard principal de movimientos y saldos bancarios
        </p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard.contador') }}">Contabilidad</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Bancos</li>
@endsection

@section('content')
    <div class="bancos-dashboard">
        {{-- =========== NAVEGACIÓN DEL MÓDULO =========== --}}
        <nav class="nav nav-tabs eerr-subnav mb-4">
            <a class="nav-link active" href="{{ route('contador.bancos.index') }}">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard Bancos
            </a>
            <a class="nav-link" href="{{ route('contador.bancos.flujo-diario') }}">
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
                <form method="GET" action="{{ route('contador.bancos.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label" for="fecha_inicio">
                                <i class="fas fa-calendar-plus me-1"></i>
                                Fecha Inicio
                            </label>
                            <input 
                                type="date" 
                                name="fecha_inicio" 
                                id="fecha_inicio" 
                                class="form-control" 
                                value="{{ $fechaInicio }}"
                            >
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label" for="fecha_fin">
                                <i class="fas fa-calendar-check me-1"></i>
                                Fecha Fin
                            </label>
                            <input 
                                type="date" 
                                name="fecha_fin" 
                                id="fecha_fin" 
                                class="form-control" 
                                value="{{ $fechaFin }}"
                            >
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label" for="cuenta">
                                <i class="fas fa-university me-1"></i>
                                Cuenta Bancaria
                            </label>
                            <select name="cuenta" id="cuenta" class="form-select">
                                <option value="">Todas las cuentas</option>
                                @foreach($listaBancos as $banco)
                                    <option 
                                        value="{{ $banco->Cuenta }}" 
                                        {{ $cuentaSeleccionada == $banco->Cuenta ? 'selected' : '' }}
                                    >
                                        {{ $banco->Banco }} - ({{ $banco->Cuenta }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i>
                                Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- =========== KPIs DEL PERÍODO =========== --}}
        <div class="stats-grid">
            <div class="stat-card shadow-sm success">
                <div class="stat-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-label">
                        <i class="fas fa-coins me-1"></i>
                        Ingresos del Período
                    </p>
                    <div class="stat-value">
                        S/ {{ number_format($totalesPeriodo->total_ingresos, 2) }}
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
                        Egresos del Período
                    </p>
                    <div class="stat-value">
                        S/ {{ number_format($totalesPeriodo->total_egresos, 2) }}
                    </div>
                </div>
            </div>
            
            <div class="stat-card shadow-sm info">
                <div class="stat-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-label">
                        <i class="fas fa-chart-area me-1"></i>
                        Flujo Neto del Período
                    </p>
                    <div class="stat-value">
                        S/ {{ number_format($totalesPeriodo->total_ingresos - $totalesPeriodo->total_egresos, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- =========== COLUMNA IZQUIERDA =========== --}}
            <div class="col-lg-4">
                {{-- Saldos Actuales --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-wallet me-2"></i>
                            Saldos Actuales (Total)
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($saldosActuales as $saldo)
                                <li class="list-group-item saldo-card">
                                    <a 
                                        href="{{ route('contador.bancos.detalle', $saldo->Cuenta) }}" 
                                        class="text-decoration-none"
                                    >
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold text-primary">
                                                    <i class="fas fa-university me-1"></i>
                                                    {{ $saldo->Banco }}
                                                </div>
                                            </div>
                                            <div class="saldo-value {{ $saldo->saldo_actual >= 0 ? 'text-success' : 'text-danger' }}">
                                                S/ {{ number_format($saldo->saldo_actual, 2) }}
                                            </div>
                                        </div>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <i class="fas fa-credit-card me-1"></i>
                                                {{ $saldo->Cuenta }} 
                                                <i class="fas fa-circle mx-2" style="font-size: 0.3rem;"></i>
                                                <i class="fas fa-sort-numeric-up me-1"></i>
                                                {{ $saldo->total_movimientos }} movs.
                                            </small>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center p-4">
                                    <i class="fas fa-university fa-2x mb-2 d-block text-muted"></i>
                                    No hay cuentas bancarias registradas.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="card-footer text-center fw-bold bg-success text-white">
                        <i class="fas fa-coins me-2"></i>
                        Total Disponible: S/ {{ number_format($saldosActuales->sum('saldo_actual'), 2) }}
                    </div>
                </div>

                {{-- Resumen por Cuenta (Período) --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>
                            Resumen del Período (Filtrado)
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <i class="fas fa-university me-1"></i>
                                            Banco
                                        </th>
                                        <th class="text-end">
                                            <i class="fas fa-arrow-down me-1 text-success"></i>
                                            Ingresos
                                        </th>
                                        <th class="text-end">
                                            <i class="fas fa-arrow-up me-1 text-danger"></i>
                                            Egresos
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($resumenCuentas as $resumen)
                                        <tr>
                                            <td>
                                                <a 
                                                    href="{{ route('contador.bancos.detalle', $resumen->Cuenta) }}" 
                                                    class="text-decoration-none fw-500"
                                                >
                                                    <i class="fas fa-building me-1"></i>
                                                    {{ $resumen->Banco }}
                                                </a>
                                            </td>
                                            <td class="text-end text-success">
                                                <strong>
                                                    S/ {{ number_format($resumen->total_ingresos, 2) }}
                                                </strong>
                                            </td>
                                            <td class="text-end text-danger">
                                                <strong>
                                                    S/ {{ number_format($resumen->total_egresos, 2) }}
                                                </strong>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center p-4 text-muted">
                                                <i class="fas fa-chart-line fa-2x mb-2 d-block text-muted"></i>
                                                No hay datos en el período.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========== COLUMNA DERECHA =========== --}}
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Últimos Movimientos (Período)
                        </h6>
                        @if($movimientosBancarios->hasPages())
                            <small class="text-muted">
                                <i class="fas fa-file-alt me-1"></i>
                                Pág. {{ $movimientosBancarios->currentPage() }} de {{ $movimientosBancarios->lastPage() }}
                            </small>
                        @endif
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <i class="fas fa-calendar me-1"></i>
                                            Fecha
                                        </th>
                                        <th>
                                            <i class="fas fa-university me-1"></i>
                                            Banco
                                        </th>
                                        <th>
                                            <i class="fas fa-file-text me-1"></i>
                                            Concepto
                                        </th>
                                        <th>
                                            <i class="fas fa-id-card me-1"></i>
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
                                    @forelse($movimientosBancarios as $mov)
                                        <tr>
                                            <td>
                                                <div class="fw-medium">
                                                    {{ \Carbon\Carbon::parse($mov->Fecha)->format('d/m/Y') }}
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <a 
                                                    href="{{ route('contador.bancos.detalle', $mov->Cuenta) }}" 
                                                    class="badge bg-primary-soft text-decoration-none"
                                                >
                                                    <i class="fas fa-building me-1"></i>
                                                    {{ $mov->Banco }}
                                                </a>
                                            </td>
                                            
                                            <td>
                                                <div class="fw-medium">
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
                                            <td colspan="6" class="text-center p-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3 d-block text-muted"></i>
                                                    <h5>No se encontraron movimientos</h5>
                                                    <p>No hay registros que coincidan con los filtros seleccionados.</p>
                                                </div>
                                            </td>
                                        </tr>
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