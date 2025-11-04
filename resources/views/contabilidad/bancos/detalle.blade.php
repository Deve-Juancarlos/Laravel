@extends('layouts.app')

@section('title', 'Detalle Banco - ' . $infoCuenta->Banco)

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-university me-2"></i>{{ $infoCuenta->Banco }}</h1>
        <p class="text-muted">Detalle de movimientos para la cuenta: {{ $infoCuenta->Cuenta }}</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $infoCuenta->Cuenta }}</li>
@endsection

@section('content')
<div class="container-fluid bancos-detalle">
    
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
    
    <!-- Filtros con Gradiente Púrpura -->
    <div class="card shadow-sm filters-card mb-4 filtros-gradient">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.detalle', $infoCuenta->Cuenta) }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="fecha_fin">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 banks-btn-gradient">
                            <i class="fas fa-filter me-1"></i> Filtrar Período
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs de la Cuenta con Cards Modernos -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card balance-card">
                <div class="kpi-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="kpi-content">
                    <h3>S/ {{ number_format($infoCuenta->saldo_actual, 2) }}</h3>
                    <p>Saldo Total Actual</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card previous-balance-card">
                <div class="kpi-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="kpi-content">
                    <h3>S/ {{ number_format($saldoAnterior, 2) }}</h3>
                    <p>Saldo Anterior ({{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }})</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card income-card">
                <div class="kpi-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="kpi-content">
                    <h3>S/ {{ number_format($totalesPeriodo->ingresos, 2) }}</h3>
                    <p>Ingresos (Período)</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card expense-card">
                <div class="kpi-icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="kpi-content">
                    <h3>S/ {{ number_format($totalesPeriodo->egresos, 2) }}</h3>
                    <p>Egresos (Período)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Movimientos con Tabla Mejorada -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Movimientos del Período</h6>
            @if($movimientos->hasPages())
                <small class="text-muted">Pág. {{ $movimientos->currentPage() }} de {{ $movimientos->lastPage() }}</small>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 modern-table">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
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
                            <td>
                                <span class="date-badge">
                                    {{ \Carbon\Carbon::parse($mov->Fecha)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                @if($mov->Tipo == 1)
                                    <span class="type-badge type-income">
                                        <i class="fas fa-arrow-down me-1"></i>{{ $mov->tipo_descripcion }}
                                    </span>
                                @else
                                    <span class="type-badge type-expense">
                                        <i class="fas fa-arrow-up me-1"></i>{{ $mov->tipo_descripcion }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="clase-text">{{ $mov->clase_descripcion }}</span>
                            </td>
                            <td>
                                <span class="doc-text">{{ $mov->Documento }}</span>
                            </td>
                            <td class="text-end">
                                @if($mov->ingreso > 0)
                                    <span class="amount-positive">S/ {{ number_format($mov->ingreso, 2) }}</span>
                                @else
                                    <span class="amount-zero">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($mov->egreso > 0)
                                    <span class="amount-negative">S/ {{ number_format($mov->egreso, 2) }}</span>
                                @else
                                    <span class="amount-zero">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center p-5 text-muted">
                                <i class="fas fa-info-circle me-2"></i>No se encontraron movimientos en este período.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark fw-bold">
                        <tr>
                            <td colspan="4">TOTALES DEL PERÍODO</td>
                            <td class="text-end">S/ {{ number_format($totalesPeriodo->ingresos, 2) }}</td>
                            <td class="text-end">S/ {{ number_format($totalesPeriodo->egresos, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">SALDO FINAL DEL PERÍODO</td>
                            <td colspan="2" class="text-end fs-6">S/ {{ number_format($totalesPeriodo->saldo_final, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if($movimientos->hasPages())
            <div class="card-footer pagination-wrapper">
                {{ $movimientos->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Resumen Mensual con Cards -->
    <div class="row">
        @forelse($resumenMensual as $resumen)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="monthly-summary-card">
                <div class="summary-header">
                    <h5>{{ $resumen->mes_nombre }} {{ $resumen->anio }}</h5>
                </div>
                <div class="summary-metrics">
                    <div class="summary-item income">
                        <div class="summary-icon">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="summary-content">
                            <span class="summary-label">Ingresos</span>
                            <span class="summary-value">S/ {{ number_format($resumen->ingresos_mes, 2) }}</span>
                        </div>
                    </div>
                    <div class="summary-item expense">
                        <div class="summary-icon">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div class="summary-content">
                            <span class="summary-label">Egresos</span>
                            <span class="summary-value">S/ {{ number_format($resumen->egresos_mes, 2) }}</span>
                        </div>
                    </div>
                    <div class="summary-net">
                        <div class="net-item">
                            <span>Flujo Neto:</span>
                            <span class="fw-bold {{ ($resumen->saldo_mes >= 0) ? 'text-success' : 'text-danger' }}">
                                S/ {{ number_format($resumen->saldo_mes, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>No hay datos suficientes para un resumen.
            </div>
        </div>
        @endforelse
    </div>

</div>

<style>
/* Estilos específicos para el Detalle de Banco */
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

.balance-card {
    border-left: 4px solid #6f42c1;
}

.previous-balance-card {
    border-left: 4px solid #17a2b8;
}

.income-card {
    border-left: 4px solid #28a745;
}

.expense-card {
    border-left: 4px solid #dc3545;
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

.balance-card .kpi-icon {
    background: linear-gradient(135deg, #6f42c1, #e83e8c);
}

.previous-balance-card .kpi-icon {
    background: linear-gradient(135deg, #17a2b8, #6c757d);
}

.income-card .kpi-icon {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.expense-card .kpi-icon {
    background: linear-gradient(135deg, #dc3545, #e83e8c);
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
    font-size: 0.9rem;
}

/* Modern Table Styles */
.modern-table {
    border-collapse: separate;
    border-spacing: 0;
}

.modern-table th {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    border: none;
    padding: 1rem 0.75rem;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #e9ecef;
}

.modern-table td {
    padding: 1rem 0.75rem;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}

.modern-table tbody tr {
    transition: all 0.3s ease;
}

.modern-table tbody tr:hover {
    background-color: #f8f9ff;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.date-badge {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    color: #1976d2;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.85rem;
}

.type-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.85rem;
    display: inline-block;
}

.type-income {
    background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
    color: #2e7d32;
}

.type-expense {
    background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 100%);
    color: #c2185b;
}

.clase-text, .doc-text {
    color: #495057;
    font-weight: 500;
}

.amount-positive {
    color: #28a745;
    font-weight: 600;
}

.amount-negative {
    color: #dc3545;
    font-weight: 600;
}

.amount-zero {
    color: #6c757d;
}

.modern-table tfoot {
    background: linear-gradient(135deg, #495057 0%, #343a40 100%);
    color: white;
}

.modern-table tfoot td {
    border: none;
    padding: 1rem 0.75rem;
    font-weight: 600;
}

/* Monthly Summary Cards */
.monthly-summary-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    height: 100%;
}

.monthly-summary-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
}

.summary-header {
    text-align: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.summary-header h5 {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
    font-size: 1.2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.summary-metrics {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.summary-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 10px;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.summary-item:hover {
    transform: translateY(-2px);
}

.summary-item.income {
    border-left: 3px solid #28a745;
}

.summary-item.expense {
    border-left: 3px solid #dc3545;
}

.summary-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: white;
    flex-shrink: 0;
}

.summary-item.income .summary-icon {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.summary-item.expense .summary-icon {
    background: linear-gradient(135deg, #dc3545, #e83e8c);
}

.summary-content {
    flex-grow: 1;
}

.summary-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.summary-value {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
}

.summary-net {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    border-radius: 10px;
    padding: 1rem;
    margin-top: 0.5rem;
}

.net-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
}

/* Pagination Styling */
.pagination-wrapper {
    background: #f8f9fa;
    border: none;
    padding: 1rem;
}

.pagination-wrapper .pagination {
    justify-content: center;
}

.pagination-wrapper .page-link {
    border: none;
    margin: 0 2px;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    color: #6c757d;
    transition: all 0.3s ease;
}

.pagination-wrapper .page-link:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-2px);
}

.pagination-wrapper .page-item.active .page-link {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

/* Responsive Design */
@media (max-width: 768px) {
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
    
    .summary-item {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .net-item {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
}
</style>
@endsection