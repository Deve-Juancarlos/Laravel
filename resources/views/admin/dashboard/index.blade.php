@extends('layouts.admin') {{-- <-- ¡Usa el NUEVO layout de Admin! --}}

@section('title', 'Dashboard Gerencial')

@section('page-title')
    <div>
        <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard Gerencial</h1>
        <p class="text-muted">Resumen de indicadores clave de SEDIMCORP SAC.</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@push('styles')

    <link href="{{ asset('css/dashboard/admin.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    
    <!-- Fila 1: KPIs Principales -->
    <div class="row mb-4 stats-grid">
        <div class="col-md-4">
            <div class="stat-card shadow-sm success">
                <i class="fas fa-chart-line"></i>
                <div class="stat-info">
                    <p class="stat-label">Ventas del Mes</p>
                    <div class="stat-value">S/ {{ number_format($kpis['totalVentasMes'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card shadow-sm danger">
                <i class="fas fa-shopping-cart"></i>
                <div class="stat-info">
                    <p class="stat-label">Compras del Mes</p>
                    <div class="stat-value">S/ {{ number_format($kpis['totalComprasMes'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card shadow-sm primary">
                <i class="fas fa-boxes"></i>
                <div class="stat-info">
                    <p class="stat-label">Inventario Valorizado</p>
                    <div class="stat-value">S/ {{ number_format($kpis['stockValorizado'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Fila 2: KPIs de Cuentas -->
     <div class="row mb-4 stats-grid">
        <div class="col-md-4">
            <div class="stat-card shadow-sm warning">
                <i class="fas fa-hand-holding-usd"></i>
                <div class="stat-info">
                    <p class="stat-label">Total Cuentas por Cobrar</p>
                    <div class="stat-value">S/ {{ number_format($kpis['saldoTotalCxC'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card shadow-sm info">
                <i class="fas fa-money-bill-wave"></i>
                <div class="stat-info">
                    <p class="stat-label">Total Cuentas por Pagar</p>
                    <div class="stat-value">S/ {{ number_format($kpis['saldoTotalCxP'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card shadow-sm secondary">
                <i class="fas fa-truck-loading"></i>
                <div class="stat-info">
                    <p class="stat-label">O/C Pendientes de Recibir</p>
                    <div class="stat-value">{{ $kpis['ordenesPendientes'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fila 3: Gráficos (Próximamente) -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header">Reporte de Ventas vs Compras (Próximamente)</div>
                <div class="card-body" style="height: 300px;">
                    <p class="text-muted">Cargando gráfico...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection