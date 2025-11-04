@extends('layouts.app')

@section('title', 'Resumen Mensual de Bancos')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-calendar-week me-2"></i>Resumen Mensual de Bancos</h1>
        <p class="text-muted">Consolidado de ingresos y egresos por mes.</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Resumen Mensual</li>
@endsection

@section('content')
<div class="container-fluid">

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

<style>
/* Estilos específicos para el Resumen Mensual de Bancos */
.banks-nav-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    padding: 0.5rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

.banks-nav-gradient .nav-link {
    color: rgba(255, 255, 255, 0.8);
    border: none;
    border-radius: 8px;
    margin: 0 2px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.banks-nav-gradient .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    transform: translateY(-2px);
}

.banks-nav-gradient .nav-link.active {
    background: white;
    color: #667eea;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.filtros-gradient {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    border: 1px solid rgba(102, 126, 234, 0.1);
    border-radius: 12px;
}

.banks-btn-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.banks-btn-gradient:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

/* KPI Cards */
.kpi-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 1rem;
    height: 100%;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
}

.income-card {
    border-left: 4px solid #28a745;
}

.expense-card {
    border-left: 4px solid #dc3545;
}

.net-card {
    border-left: 4px solid #6f42c1;
}

.movements-card {
    border-left: 4px solid #fd7e14;
}

.kpi-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.income-card .kpi-icon {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.expense-card .kpi-icon {
    background: linear-gradient(135deg, #dc3545, #e83e8c);
}

.net-card .kpi-icon {
    background: linear-gradient(135deg, #6f42c1, #e83e8c);
}

.movements-card .kpi-icon {
    background: linear-gradient(135deg, #fd7e14, #ffc107);
}

.kpi-content h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.kpi-content p {
    margin: 0;
    color: #6c757d;
    font-weight: 500;
}

/* Bank Monthly Cards */
.bank-monthly-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.bank-monthly-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
}

.bank-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.bank-logo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.bank-info h5 {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
    font-size: 1.1rem;
}

.bank-info p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.bank-metrics {
    flex-grow: 1;
}

.metric-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.metric-item {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 10px;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.metric-item:hover {
    transform: translateY(-2px);
}

.metric-item.income {
    border-left: 3px solid #28a745;
}

.metric-item.expense {
    border-left: 3px solid #dc3545;
}

.metric-item i {
    font-size: 1.1rem;
    color: #6c757d;
    width: 20px;
    text-align: center;
}

.metric-item.income i {
    color: #28a745;
}

.metric-item.expense i {
    color: #dc3545;
}

.metric-content {
    flex-grow: 1;
}

.metric-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.metric-value {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
}

.net-flow {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.flow-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.flow-item:last-child {
    margin-bottom: 0;
}

.flow-item i {
    color: #6f42c1;
    width: 16px;
    text-align: center;
}

.bank-actions {
    text-align: center;
    padding-top: 1rem;
    border-top: 1px solid #f0f0f0;
}

.bank-actions .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1.5rem;
    transition: all 0.3s ease;
}

.bank-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    .metric-row {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .bank-header {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
    
    .kpi-card {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
    
    .kpi-icon {
        width: 60px;
        height: 60px;
        font-size: 1.3rem;
    }
}
</style>
@endsection