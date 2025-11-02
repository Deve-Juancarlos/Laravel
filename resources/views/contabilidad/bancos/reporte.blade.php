@extends('layouts.app')

@section('title', 'Reportes de Bancos')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-file-invoice me-2"></i>Reportes de Bancos</h1>
        <p class="text-muted">Generación de reportes consolidados y de flujo.</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Reportes</li>
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
        <a class="nav-link" href="{{ route('contador.bancos.transferencias') }}">
            <i class="fas fa-exchange-alt me-1"></i> Transferencias
        </a>
        <a class="nav-link active" href="{{ route('contador.bancos.reporte') }}">
            <i class="fas fa-file-invoice me-1"></i> Reportes
        </a>
    </nav>

    <!-- Filtros -->
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.reporte') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_fin">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="cuenta">Cuenta (Opcional)</label>
                        <select name="cuenta" id="cuenta" class="form-select">
                            <option value="">Todas las cuentas</option>
                            @foreach($listaBancos as $banco)
                                {{-- CORRECCIÓN (Error 4): $cuentaSeleccionada existe ahora --}}
                                <option value="{{ $banco->Cuenta }}" {{ $cuentaSeleccionada == $banco->Cuenta ? 'selected' : '' }}>
                                    {{ $banco->Banco }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="tipo_reporte">Tipo de Reporte</label>
                        <select name="tipo_reporte" id="tipo_reporte" class="form-select">
                            <option value="general" {{ $tipoReporte == 'general' ? 'selected' : '' }}>General</option>
                            <option value="flujo" {{ $tipoReporte == 'flujo' ? 'selected' : '' }}>Flujo (Top 10)</option>
                            <option value="comparativo" {{ $tipoReporte == 'comparativo' ? 'selected' : '' }}>Comparativo</option>
                            <option value="conciliacion" {{ $tipoReporte == 'conciliacion' ? 'selected' : '' }}>Conciliación</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Generar Reporte
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Contenedor del Reporte -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Resultados del Reporte: {{ ucfirst($tipoReporte) }}</h5>
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
                <p>Seleccione un tipo de reporte válido.</p>
            @endif
        </div>
    </div>

</div>

@endsection

