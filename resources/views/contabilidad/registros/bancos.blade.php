@extends('layouts.app')

@section('title', 'Libro de Bancos')
@section('page-title', 'Libro de Bancos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item active" aria-current="page">Bancos</li>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-line me-2"></i>Resumen de Saldos Bancarios</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($saldosActuales as $saldo)
                    <div class="col-md-4 mb-3">
                        <div class="kpi-card">
                            <div class="kpi-icon bg-{{ $saldo->Saldo > 0 ? 'success' : 'danger' }}">
                                <i class="fas fa-money-check-alt"></i>
                            </div>
                            <div class="kpi-content">
                                <div class="kpi-label">{{ $saldo->Banco }}</div>
                                <div class="kpi-value">S/ {{ number_format($saldo->Saldo, 2) }}</div>
                                <small class="text-muted">{{ $saldo->Moneda }} - Cta: {{ $saldo->Cuenta }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-bolt me-2"></i>Funcionalidades Rápidas</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="{{ route('contador.bancos.diarios') }}" class="list-group-item list-group-item-action"><i class="fas fa-calendar-day me-3 text-info"></i>Movimientos Diarios</a>
                    <a href="{{ route('contador.bancos.conciliacion') }}" class="list-group-item list-group-item-action"><i class="fas fa-balance-scale me-3 text-warning"></i>Realizar Conciliación</a>
                    <a href="{{ route('contador.bancos.transferencias') }}" class="list-group-item list-group-item-action"><i class="fas fa-exchange-alt me-3 text-primary"></i>Ver Transferencias</a>
                    <a href="{{ route('contador.bancos.flujoDiario') }}" class="list-group-item list-group-item-action"><i class="fas fa-chart-area me-3 text-success"></i>Flujo de Caja Proyectado</a>
                    <a href="{{ route('contador.bancos.reportes') }}" class="list-group-item list-group-item-action"><i class="fas fa-file-invoice-dollar me-3 text-danger"></i>Generar Reportes (Mensual, Detalle)</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Últimos Movimientos</h6>
                <a href="{{ route('contabilidad.bancos.detalle', ['cuenta' => 'TODAS']) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-list me-1"></i> Ver Todos</a>
            </div>
            <div class="card-body p-0">
                <p class="text-center p-4 text-muted">Tabla de últimos movimientos (Implementar similar a Ventas Recientes)</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@endpush