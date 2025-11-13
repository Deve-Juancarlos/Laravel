@extends('layouts.app')

@section('title', 'Comparativo EERR')

@push('styles')
    <link href="{{ asset('css/contabilidad/estado-resultados/comparativo.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-exchange-alt me-2"></i>Comparativo EERR</h1>
        <p class="text-muted">Comparación del Estado de Resultados entre dos períodos.</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.estado-resultados.index') }}">Estado de Resultados</a></li>
    <li class="breadcrumb-item active" aria-current="page">Comparativo</li>
@endsection

@section('content')
<div class="comparativo-container">

    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link {{ request()->routeIs('contador.estado-resultados.index') ? 'active' : '' }}" href="{{ route('contador.estado-resultados.index') }}">
            <i class="fas fa-chart-line me-1"></i> Estado de Resultados
        </a>
        <a class="nav-link {{ request()->routeIs('contador.estado-resultados.periodos') ? 'active' : '' }}" href="{{ route('contador.estado-resultados.periodos') }}">
            <i class="fas fa-chart-bar me-1"></i> Resultados por Períodos
        </a>
        <a class="nav-link {{ request()->routeIs('contador.estado-resultados.comparativo') ? 'active' : '' }}" href="{{ route('contador.estado-resultados.comparativo') }}">
            <i class="fas fa-exchange-alt me-1"></i> Comparativo EERR
        </a>
        <a class="nav-link {{ request()->routeIs('contador.estado-resultados.balance-general') ? 'active' : '' }}" href="{{ route('contador.estado-resultados.balance-general') }}">
            <i class="fas fa-balance-scale-right me-1"></i> Balance General
        </a>
    </nav>
    <!-- Filtros -->
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.estado-resultados.comparativo') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_inicio">Período Actual Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $periodos['actual_mensual']['inicio'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="fecha_fin">Período Actual Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $periodos['actual_mensual']['fin'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="anterior_inicio">Período Anterior Inicio</label>
                        <input type="date" name="anterior_inicio" id="anterior_inicio" class="form-control" value="{{ $periodos['anterior_mensual']['inicio'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="anterior_fin">Período Anterior Fin</label>
                        <input type="date" name="anterior_fin" id="anterior_fin" class="form-control" value="{{ $periodos['anterior_mensual']['fin'] }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar Comparativo
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tarjetas de Período -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm er-card-periodo">
                <div class="card-header bg-primary text-white">
                    <strong>Período Actual:</strong> 
                    {{ \Carbon\Carbon::parse($periodos['actual_mensual']['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($periodos['actual_mensual']['fin'])->format('d/m/Y') }}
                </div>
                <div class="card-body">
                    <div class="er-line-item">
                        <span>Ventas Netas</span>
                        <span class="fw-bold">S/ {{ number_format($periodos['actual_mensual']['resultados']['ventas_netas'], 2) }}</span>
                    </div>
                    <div class="er-line-item">
                        <span>(-) Costo de Ventas</span>
                        <span class="fw-bold text-danger">(S/ {{ number_format($periodos['actual_mensual']['resultados']['costo_ventas'], 2) }})</span>
                    </div>
                    <div class="er-total">
                        <span>Utilidad Bruta</span>
                        <span class="fw-bold">S/ {{ number_format($periodos['actual_mensual']['resultados']['utilidad_bruta'], 2) }}</span>
                    </div>
                    <hr>
                    <div class="er-line-item">
                        <span>(-) Gastos Operativos</span>
                        <span class="fw-bold text-danger">(S/ {{ number_format($periodos['actual_mensual']['resultados']['gastos_operativos'], 2) }})</span>
                    </div>
                    <div class="er-grand-total">
                        <span>Utilidad Operativa</span>
                        <span class="fw-bold">S/ {{ number_format($periodos['actual_mensual']['resultados']['utilidad_operativa'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm er-card-periodo">
                <div class="card-header bg-secondary text-white">
                    <strong>Período Anterior:</strong> 
                    {{ \Carbon\Carbon::parse($periodos['anterior_mensual']['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($periodos['anterior_mensual']['fin'])->format('d/m/Y') }}
                </div>
                <div class="card-body">
                    <div class="er-line-item">
                        <span>Ventas Netas</span>
                        <span class="fw-bold">S/ {{ number_format($periodos['anterior_mensual']['resultados']['ventas_netas'], 2) }}</span>
                    </div>
                    <div class="er-line-item">
                        <span>(-) Costo de Ventas</span>
                        <span class="fw-bold text-danger">(S/ {{ number_format($periodos['anterior_mensual']['resultados']['costo_ventas'], 2) }})</span>
                    </div>
                    <div class="er-total">
                        <span>Utilidad Bruta</span>
                        <span class="fw-bold">S/ {{ number_format($periodos['anterior_mensual']['resultados']['utilidad_bruta'], 2) }}</span>
                    </div>
                    <hr>
                    <div class="er-line-item">
                        <span>(-) Gastos Operativos</span>
                        <span class="fw-bold text-danger">(S/ {{ number_format($periodos['anterior_mensual']['resultados']['gastos_operativos'], 2) }})</span>
                    </div>
                    <div class="er-grand-total">
                        <span>Utilidad Operativa</span>
                        <span class="fw-bold">S/ {{ number_format($periodos['anterior_mensual']['resultados']['utilidad_operativa'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Variaciones -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Resumen de Variaciones</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <h6 class="text-muted">Variación Ventas</h6>
                    @if($variaciones['mensual']['ventas'] >= 0)
                        <h4 class="text-success"><i class="fas fa-arrow-up me-1"></i> {{ number_format($variaciones['mensual']['ventas'], 2) }}%</h4>
                    @else
                        <h4 class="text-danger"><i class="fas fa-arrow-down me-1"></i> {{ number_format($variaciones['mensual']['ventas'], 2) }}%</h4>
                    @endif
                </div>
                <div class="col-md-4 text-center">
                    <h6 class="text-muted">Variación Utilidad Bruta</h6>
                    @if($comparativo['variacion']['utilidad_bruta'] >= 0)
                        <h4 class="text-success"><i class="fas fa-arrow-up me-1"></i> {{ number_format($comparativo['variacion']['utilidad_bruta'], 2) }}%</h4>
                    @else
                        <h4 class="text-danger"><i class="fas fa-arrow-down me-1"></i> {{ number_format($comparativo['variacion']['utilidad_bruta'], 2) }}%</h4>
                    @endif
                </div>
                <div class="col-md-4 text-center">
                    <h6 class="text-muted">Variación Utilidad Operativa</h6>
                    @if($variaciones['mensual']['utilidad'] >= 0)
                        <h4 class="text-success"><i class="fas fa-arrow-up me-1"></i> {{ number_format($variaciones['mensual']['utilidad'], 2) }}%</h4>
                    @else
                        <h4 class="text-danger"><i class="fas fa-arrow-down me-1"></i> {{ number_format($variaciones['mensual']['utilidad'], 2) }}%</h4>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
