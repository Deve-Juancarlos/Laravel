@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Reportes de Bancos')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos/reporte.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div class="page-title-enhanced">
        <h1>
            <i class="fas fa-file-invoice me-3"></i>
            Reportes de Bancos
        </h1>
        <p>
            <i class="fas fa-chart-bar me-2"></i>
            Generación de reportes consolidados y de flujo
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
        <i class="fas fa-file-invoice me-1"></i>
        Reportes
    </li>
@endsection

@section('content')
<div class="reportes-conteiner">

    {{-- =========== NAVEGACIÓN DEL MÓDULO =========== --}}
    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link" href="{{ route('contador.bancos.index') }}">
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
        <a class="nav-link active" href="{{ route('contador.bancos.reporte') }}">
            <i class="fas fa-file-invoice me-2"></i>
            Reportes
        </a>
    </nav>
    {{-- =========== FIN NAVEGACIÓN =========== --}}

    {{-- =========== TIPOS DE REPORTES DISPONIBLES =========== --}}
    <div class="reports-grid mb-4">
        <div class="report-card general">
            <div class="report-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <h6>
                <i class="fas fa-list me-1"></i>
                Reporte General
            </h6>
            <p>Resumen completo de movimientos por cuenta bancaria con totales consolidados.</p>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Ideal para análisis generales
                </small>
            </div>
        </div>
        
        <div class="report-card flujo">
            <div class="report-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h6>
                <i class="fas fa-sort-amount-up me-1"></i>
                Reporte de Flujo
            </h6>
            <p>Análisis de los 10 principales movimientos y flujos más significativos del período.</p>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-trophy me-1"></i>
                    Top movimientos destacados
                </small>
            </div>
        </div>
        
        <div class="report-card comparativo">
            <div class="report-icon">
                <i class="fas fa-balance-scale"></i>
            </div>
            <h6>
                <i class="fas fa-exchange-alt me-1"></i>
                Reporte Comparativo
            </h6>
            <p>Comparación detallada de ingresos vs egresos con análisis de tendencias y variaciones.</p>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-chart-bar me-1"></i>
                    Análisis de rendimiento
                </small>
            </div>
        </div>
        
        <div class="report-card conciliacion">
            <div class="report-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <h6>
                <i class="fas fa-clipboard-check me-1"></i>
                Conciliación Bancaria
            </h6>
            <p>Reporte detallado para conciliación perfecta con estados de cuenta bancarios oficiales.</p>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-check-double me-1"></i>
                    Control y precisión
                </small>
            </div>
        </div>
    </div>

    {{-- =========== FILTROS =========== --}}
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.reporte') }}">
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
                    <div class="col-md-3">
                        <label class="form-label" for="cuenta">
                            <i class="fas fa-university me-1"></i>
                            Cuenta (Opcional)
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
                    <div class="col-md-3">
                        <label class="form-label" for="tipo_reporte">
                            <i class="fas fa-file-alt me-1"></i>
                            Tipo de Reporte
                        </label>
                        <select name="tipo_reporte" id="tipo_reporte" class="form-select">
                            <option value="general" {{ $tipoReporte == 'general' ? 'selected' : '' }}>
                                <i class="fas fa-file-alt me-1"></i>
                                General
                            </option>
                            <option value="flujo" {{ $tipoReporte == 'flujo' ? 'selected' : '' }}>
                                <i class="fas fa-chart-line me-1"></i>
                                Flujo (Top 10)
                            </option>
                            <option value="comparativo" {{ $tipoReporte == 'comparativo' ? 'selected' : '' }}>
                                <i class="fas fa-balance-scale me-1"></i>
                                Comparativo
                            </option>
                            <option value="conciliacion" {{ $tipoReporte == 'conciliacion' ? 'selected' : '' }}>
                                <i class="fas fa-tasks me-1"></i>
                                Conciliación
                            </option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>
                            Generar Reporte
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- =========== KPIs DEL REPORTE =========== --}}
    @if(isset($totalIngresos) && isset($totalEgresos))
        <div class="stats-grid mb-4">
            <div class="stat-card shadow-sm success">
                <div class="stat-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-label">
                        <i class="fas fa-coins me-1"></i>
                        Total Ingresos
                    </p>
                    <div class="stat-value">
                        S/ {{ number_format($totalIngresos, 2) }}
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
                        Total Egresos
                    </p>
                    <div class="stat-value">
                        S/ {{ number_format($totalEgresos, 2) }}
                    </div>
                </div>
            </div>
            
            <div class="stat-card shadow-sm {{ ($totalIngresos - $totalEgresos) >= 0 ? 'info' : 'warning' }}">
                <div class="stat-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-label">
                        <i class="fas fa-chart-area me-1"></i>
                        Flujo Neto
                    </p>
                    <div class="stat-value">
                        S/ {{ number_format($totalIngresos - $totalEgresos, 2) }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- =========== CONTENEDOR DEL REPORTE =========== --}}
    <div class="card shadow-sm">
        <div class="card-header bg-primary">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar me-2"></i>
                Resultados del Reporte: {{ ucfirst($tipoReporte) }}
            </h5>
        </div>
        <div class="card-body">
            {{--
                Aquí usamos vistas parciales (sub-vistas) para mantener el código limpio.
                El service pasa los datos correctos para cada una.
            --}}
            @if($tipoReporte == 'general' || $tipoReporte == 'comparativo')
                @include('contabilidad.bancos.reportes.general', ['datos' => $porBanco, 'totales' => ['ingresos' => $totalIngresos, 'egresos' => $totalEgresos]])
            @elseif($tipoReporte == 'flujo')
                @include('contabilidad.bancos.reportes._flujo', ['datos' => $datosReporte])
            @elseif($tipoReporte == 'conciliacion')
                @include('contabilidad.bancos.reportes._conciliacion', ['datos' => $datosReporte])
            @else
                <div class="text-center p-5">
                    <i class="fas fa-file-alt fa-4x mb-3 d-block text-muted"></i>
                    <h4 class="text-muted">Seleccione un tipo de reporte válido</h4>
                    <p class="text-muted">Elija el tipo de reporte que desea generar desde el formulario de filtros.</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection