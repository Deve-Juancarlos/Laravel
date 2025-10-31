@extends('layouts.app')

@section('title', 'Balance General')

@push('styles')
    <link href="{{ asset('css/contabilidad/estado-resultados.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-balance-scale-right me-2"></i>Balance General</h1>
        <p class="text-muted">Estado de Situación Financiera (Activo = Pasivo + Patrimonio)</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item active" aria-current="page">Balance General</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filtros -->
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.balance-general.index') }}">
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

    <!-- Estado del Balance -->
    <div class="card shadow-sm mb-4 {{ $estaBalanceado ? 'bg-success-soft' : 'bg-danger-soft' }}">
        <div class="card-body text-center p-4">
            @if($estaBalanceado)
                <h4 class="text-success mb-0"><i class="fas fa-check-circle me-2"></i>¡Balance Cuadrado!</h4>
                <p class="mb-0 text-muted">Total Activos (S/ {{ number_format($totalActivos, 2) }}) = Total Pasivo + Patrimonio (S/ {{ number_format($totalPasivosPatrimonio, 2) }})</p>
            @else
                <h4 class="text-danger mb-0"><i class="fas fa-exclamation-triangle me-2"></i>¡Balance Descuadrado!</h4>
                <p class="mb-0 text-muted">Diferencia: S/ {{ number_format($diferenciaBalance, 2) }}</p>
            @endif
        </div>
    </div>


    <div class="row">
        <!-- Columna Izquierda: Activos -->
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
                        <li class="list-group-item er-line-item"><span>Inventarios (13)</span> <span>S/ {{ number_format($inventarios, 2) }}</span></li>
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

        <!-- Columna Derecha: Pasivos y Patrimonio -->
        <div class="col-lg-6">
            <!-- Pasivos -->
            <div class="card shadow-sm er-card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">PASIVOS</h5>
                    <h5 class="mb-0">S/ {{ number_format($totalPasivos, 2) }}</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item er-category">PASIVO CORRIENTE</li>
                        <li class="list-group-item er-line-item"><span>Cuentas por Pagar (42)</span> <span>S/ {{ number_format(abs($cuentasPorPagar), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Documentos por Pagar (40)</span> <span>S/ {{ number_format(abs($documentosPorPagar), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Préstamos a Corto Plazo (45)</span> <span>S/ {{ number_format(abs($prestamosCorto), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Impuestos (4017)</span> <span>S/ {{ number_format(abs($provisionImpuestos), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Otros Gastos por Pagar (41,44,46)</span> <span>S/ {{ number_format(abs($otrosGastosPagar), 2) }}</span></li>
                        <li class="list-group-item er-total">
                            <span>TOTAL PASIVO CORRIENTE</span>
                            <span class="fw-bold">S/ {{ number_format($totalPasivosCorrientes, 2) }}</span>
                        </li>
                        
                        <li class="list-group-item er-category">PASIVO NO CORRIENTE</li>
                        <li class="list-group-item er-line-item"><span>Préstamos a Largo Plazo (47)</span> <span>S/ {{ number_format(abs($prestamosLargo), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Provisiones (48, 49)</span> <span>S/ {{ number_format(abs($provisionBeneficios), 2) }}</span></li>
                        <li class="list-group-item er-total">
                            <span>TOTAL PASIVO NO CORRIENTE</span>
                            <span class="fw-bold">S/ {{ number_format($totalPasivosNoCorrientes, 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Patrimonio -->
            <div class="card shadow-sm er-card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">PATRIMONIO</h5>
                    <h5 class="mb-0">S/ {{ number_format($totalPatrimonio, 2) }}</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item er-line-item"><span>Capital Social (50)</span> <span>S/ {{ number_format(abs($capital), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Reservas (58)</span> <span>S/ {{ number_format(abs($reservas), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Resultados Acumulados (59)</span> <span>S/ {{ number_format(abs($resultadosAcum), 2) }}</span></li>
                        <li class="list-group-item er-line-item"><span>Resultado del Ejercicio</span> <span>S/ {{ number_format($resultadoEjercicio, 2) }}</span></li>
                        <li class="list-group-item er-grand-total">
                            <span>TOTAL PATRIMONIO</span>
                            <span class="fw-bold">S/ {{ number_format($totalPatrimonio, 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
