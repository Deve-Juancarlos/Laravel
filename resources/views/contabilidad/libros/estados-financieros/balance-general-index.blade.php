@extends('layouts.app')

@section('title', 'Balance General')

@push('styles')
    {{-- (Reutilizamos el CSS que ya debes tener) --}}
    <link href="{{ asset('css/contabilidad/estado-resultados/general.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-balance-scale-right me-2"></i>Balance General</h1>
        <p class="text-muted">Estado de Situación Financiera (Activo = Pasivo + Patrimonio)</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">Contabilidad</a></li>
    <li class="breadcrumb-item active" aria-current="page">Balance General</li>
@endsection

@section('content')
<div class="general-container">

    {{-- Sub-navegación CORREGIDA --}}
    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link" href=" {{ route('contador.estado-resultados.index') }}">
            <i class="fas fa-chart-line me-1"></i> Estado de Resultados
        </a>
        <a class="nav-link" href="{{ route('contador.estado-resultados.periodos') }}">
            <i class="fas fa-chart-bar me-1"></i> Resultados por Períodos
        </a>
        <a class="nav-link" href="{{ route('contador.estado-resultados.comparativo') }} "> 
            <i class="fas fa-exchange-alt me-1"></i> Comparativo EERR
        </a>
        <a class="nav-link active" href="{{ route('contador.estado-resultados.balance-general') }}">
            <i class="fas fa-balance-scale-right me-1"></i> Balance General
        </a>
    </nav>
    
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.estado-resultados.balance-general') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="fecha">Balance al:</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" value="{{ $fecha }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Generar Balance
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-4 {{ $estaBalanceado ? 'bg-success-soft' : 'bg-danger-soft' }}">
        <div class="card-body text-center p-4">
            @if($estaBalanceado)
                <h4 class="text-success mb-0"><i class="fas fa-check-circle me-2"></i>¡Balance Cuadrado!</h4>
                <p class="mb-0 text-muted">Total Activos (S/ {{ number_format($totalActivos, 2) }}) = Total Pasivo + Patrimonio (S/ {{ number_format(abs($totalPasivosPatrimonio), 2) }})</p>
                <small class="text-muted">(Diferencia: S/ {{ number_format($diferenciaBalance, 4) }})</small>
            @else
                <h4 class="text-danger mb-0"><i class="fas fa-exclamation-triangle me-2"></i>¡Balance Descuadrado!</h4>
                <p class="mb-0 text-muted">Diferencia (Activo + Pasivo + Patrimonio): S/ {{ number_format($diferenciaBalance, 2) }}</p>
            @endif
        </div>
    </div>


    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm er-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">ACTIVOS</h5>
                    <h5 class="mb-0">S/ {{ number_format($totalActivos, 2) }}</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item er-category">ACTIVO CORRIENTE</li>
                        <li class="list-group-item er-line-item"><span>Efectivo y Equivalentes (10)</span> <span>S/ {{ number_format($efectivo, 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Cuentas por Cobrar (12)</span> <span>S/ {{ number_format($cuentasPorCobrar, 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Inventarios (20)</span> <span>S/ {{ number_format($inventarios, 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Gastos Pagados por Adelantado (18)</span> <span>S/ {{ number_format($gastosAdelantado, 2) }}</span></li>
                        <li class="list-group-item er-total">
                            <span>TOTAL ACTIVO CORRIENTE</span>
                            <span class="fw-bold">S/ {{ number_format($totalActivosCorrientes, 2) }}</span>
                        </li>
                        
                        <li class="list-group-item er-category">ACTIVO NO CORRIENTE</li>
                        <li class="list-group-item er-line-item"><span>Propiedad, Planta y Equipo (33)</span> <span>S/ {{ number_format($propiedadPlanta, 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>(-) Depreciación Acumulada (39)</span> <span class="text-danger">(S/ {{ number_format(abs($depreciacion), 2) }})</span></li>
                        <li class="list-group-item er-line-item"><span>Intangibles (34)</span> <span>S/ {{ number_format($intangibles, 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Otros Activos (35-38)</span> <span>S/ {{ number_format($otrosActivos, 2) }}</span></li>
                        <li class="list-group-item er-total">
                            <span>TOTAL ACTIVO NO CORRIENTE</span>
                            <span class="fw-bold">S/ {{ number_format($totalActivosNoCorrientes, 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm er-card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">PASIVOS</h5>
                    <h5 class="mb-0">S/ {{ number_format(abs($totalPasivos), 2) }}</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item er-category">PASIVO CORRIENTE</li>
                        <li class="list-group-item er-line-item"><span>Tributos por Pagar (40)</span> <span>S/ {{ number_format(abs($tributosPorPagar), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Remuneraciones por Pagar (41)</span> <span>S/ {{ number_format(abs($remuneracionesPorPagar), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Cuentas por Pagar (42)</span> <span>S/ {{ number_format(abs($cuentasPorPagar), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Otras Cuentas por Pagar (44-46)</span> <span>S/ {{ number_format(abs($otrasCtasPagar), 2) }}</span></li>
                        <li class="list-group-item er-total">
                            <span>TOTAL PASIVO CORRIENTE</span>
                            <span class="fw-bold">S/ {{ number_format(abs($totalPasivosCorrientes), 2) }}</span>
                        </li>
                        
                        <li class="list-group-item er-category">PASIVO NO CORRIENTE</li>
                        <li class="list-group-item er-line-item"><span>Préstamos a Largo Plazo (47)</span> <span>S/ {{ number_format(abs($prestamosLargo), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Provisiones (48, 49)</span> <span>S/ {{ number_format(abs($provisionBeneficios), 2) }}</span></li>
                        <li class="list-group-item er-total">
                            <span>TOTAL PASIVO NO CORRIENTE</span>
                            <span class="fw-bold">S/ {{ number_format(abs($totalPasivosNoCorrientes), 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm er-card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">PATRIMONIO</h5>
                    <h5 class="mb-0">S/ {{ number_format(abs($totalPatrimonio), 2) }}</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item er-line-item"><span>Capital Social (50)</span> <span>S/ {{ number_format(abs($capital), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Reservas (58)</span> <span>S/ {{ number_format(abs($reservas), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Resultados Acumulados (59)</span> <span>S/ {{ number_format(abs($resultadosAcum), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Resultado del Ejercicio</span> <span>S/ {{ number_format($resultadoEjercicio, 2) }}</span></li>
                        <li class="list-group-item er-grand-total">
                            <span>TOTAL PATRIMONIO</span>
                            <span class="fw-bold">S/ {{ number_format(abs($totalPatrimonio), 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection