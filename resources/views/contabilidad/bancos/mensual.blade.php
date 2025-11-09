@extends('layouts.app')

@section('title', 'Resumen Mensual de Bancos')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos/mensual.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-calendar-week me-2"></i>Resumen Mensual de Bancos</h1>
        <p class="text-muted">Consolidado de ingresos y egresos por mes.</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Resumen Mensual</li>
@endsection

@section('content')
<div class="mensual-content">

    <!-- Navegación del Módulo de Bancos con Gradiente Púrpura -->
    <nav class="nav nav-tabs eerr-subnav mb-4 banks-nav-gradient">
        <a class="nav-link" href="{{ route('contador.bancos.index') }}">
            <i class="fas fa-home me-1"></i> Dashboard Bancos
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.flujo-diario') }}">
            <i class="fas fa-calendar-day me-1"></i> Flujo Diario
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.diario') }}">
            <i class="fas fa-calendar-alt me-1"></i> Reporte Diario
        </a>
        <a class="nav-link active" href="{{ route('contador.bancos.mensual') }}">
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

    <!-- Filtros con Gradiente Púrpura -->
    <div class="card shadow-sm filters-card mb-4 filtros-gradient">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.mensual') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="mes">Mes</label>
                        <select name="mes" id="mes" class="form-select">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $mesSeleccionado == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m)->locale('es')->isoFormat('MMMM') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="anio">Año</label>
                        <select name="anio" id="anio" class="form-select">
                            @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ $anioSeleccionado == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 banks-btn-gradient">
                            <i class="fas fa-search me-1"></i> Generar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- KPIs Principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card income-card">
                <div class="kpi-icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="kpi-content">
                    <h3>S/ {{ number_format($totalesMes['total_ingresos'], 2) }}</h3>
                    <p>Total Ingresos</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card expense-card">
                <div class="kpi-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="kpi-content">
                    <h3>S/ {{ number_format($totalesMes['total_egresos'], 2) }}</h3>
                    <p>Total Egresos</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card net-card">
                <div class="kpi-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="kpi-content">
                    <h3>S/ {{ number_format($totalesMes['total_ingresos'] - $totalesMes['total_egresos'], 2) }}</h3>
                    <p>Flujo Neto</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card movements-card">
                <div class="kpi-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="kpi-content">
                    <h3>{{ $totalesMes['total_movimientos'] }}</h3>
                    <p>Movimientos Totales</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Resumen Mensual por Banco - Cards Individuales -->
    <div class="row">
        @forelse($resumenMensual as $resumen)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="bank-monthly-card">
                <div class="bank-header">
                    <div class="bank-logo">
                        {{ substr($resumen->Banco, 0, 2) }}
                    </div>
                    <div class="bank-info">
                        <h5>{{ $resumen->Banco }}</h5>
                        <p class="text-muted">Cuenta: {{ $resumen->Cuenta }}</p>
                    </div>
                </div>
                
                <div class="bank-metrics">
                    <div class="metric-row">
                        <div class="metric-item income">
                            <i class="fas fa-arrow-up"></i>
                            <div class="metric-content">
                                <span class="metric-label">Ingresos</span>
                                <span class="metric-value">S/ {{ number_format($resumen->ingresos_mes, 2) }}</span>
                            </div>
                        </div>
                        <div class="metric-item expense">
                            <i class="fas fa-arrow-down"></i>
                            <div class="metric-content">
                                <span class="metric-label">Egresos</span>
                                <span class="metric-value">S/ {{ number_format($resumen->egresos_mes, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="net-flow">
                        <div class="flow-item">
                            <i class="fas fa-balance-scale"></i>
                            <span>Flujo Neto:</span>
                            <span class="fw-bold {{ ($resumen->saldo_mes >= 0) ? 'text-success' : 'text-danger' }}">
                                S/ {{ number_format($resumen->saldo_mes, 2) }}
                            </span>
                        </div>
                        <div class="flow-item">
                            <i class="fas fa-list"></i>
                            <span>Movimientos:</span>
                            <span class="fw-bold">{{ $resumen->total_movimientos }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="bank-actions">
                    <a href="{{ route('contador.bancos.detalle', $resumen->Cuenta) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i> Ver Detalle
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>No hay datos para este mes.
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection