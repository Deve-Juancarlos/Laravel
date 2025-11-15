@extends('layouts.admin')

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
    {{-- (Reutilizamos el CSS de Caja Chica para los KPIs) --}}
    <link href="{{ asset('css/contabilidad/caja.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="admindashboard-container">
    
    <div class="row mb-4 stats-grid">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card shadow-sm success">
                <i class="fas fa-chart-line"></i>
                <div class="stat-info">
                    <p class="stat-label">Ventas del Mes</p>
                    <div class="stat-value">S/ {{ number_format($kpis['totalVentasMes'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card shadow-sm info">
                <i class="fas fa-piggy-bank"></i>
                <div class="stat-info">
                    <p class="stat-label">Utilidad Bruta (Mes)</p>
                    <div class="stat-value">S/ {{ number_format($kpis['utilidadBrutaMes'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card shadow-sm danger">
                <i class="fas fa-shopping-cart"></i>
                <div class="stat-info">
                    <p class="stat-label">Compras del Mes</p>
                    <div class="stat-value">S/ {{ number_format($kpis['totalComprasMes'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card shadow-sm primary">
                <i class="fas fa-landmark"></i>
                <div class="stat-info">
                    <p class="stat-label">Liquidez Total (Bancos)</p>
                    <div class="stat-value">S/ {{ number_format($kpis['liquidezTotal'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4 stats-grid">
        <div class="col-lg-4 col-md-6">
            <div class="stat-card shadow-sm warning">
                <i class="fas fa-hand-holding-usd"></i>
                <div class="stat-info">
                    <p class="stat-label">Total Cuentas por Cobrar</p>
                    <div class="stat-value">S/ {{ number_format($kpis['saldoTotalCxC'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card shadow-sm warning-dark">
                <i class="fas fa-money-bill-wave"></i>
                <div class="stat-info">
                    <p class="stat-label">Total Cuentas por Pagar</p>
                    <div class="stat-value">S/ {{ number_format($kpis['saldoTotalCxP'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="stat-card shadow-sm secondary">
                <i class="fas fa-boxes"></i>
                <div class="stat-info">
                    <p class="stat-label">Inventario Valorizado</p>
                    <div class="stat-value">S/ {{ number_format($kpis['stockValorizado'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">Ventas vs. Compras (Últimos 6 Meses)</div>
                <div class="card-body">
                    <canvas id="chartVentasCompras" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">Top 5 Clientes Deudores (CxC)</div>
                <div class="card-body">
                    <canvas id="chartTopCxC" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">Top 5 Proveedores (CxP)</div>
                <div class="card-body">
                    <canvas id="chartTopCxP" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- ¡Cargamos la librería de Gráficos! --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // 1. Gráfico Ventas vs Compras
    const dataVentasCompras = @json($charts['ventasCompras']);
    new Chart(document.getElementById('chartVentasCompras'), {
        type: 'bar',
        data: {
            labels: dataVentasCompras.labels,
            datasets: [
                {
                    label: 'Ventas (S/)',
                    data: dataVentasCompras.ventas,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)', // Verde
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Compras (S/)',
                    data: dataVentasCompras.compras,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)', // Rojo
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: { scales: { y: { beginAtZero: true } } }
    });

    // 2. Gráfico Top 5 Clientes (CxC)
    const dataCxC = @json($charts['topCxC']);
    new Chart(document.getElementById('chartTopCxC'), {
        type: 'doughnut',
        data: {
            labels: dataCxC.map(c => c.Razon),
            datasets: [{
                label: 'Deuda (S/)',
                data: dataCxC.map(c => c.total_deuda),
                backgroundColor: ['#ffc107', '#fd7e14', '#dc3545', '#d63384', '#6f42c1']
            }]
        },
        options: { responsive: true, maintainAspectRatio: true }
    });

    // 3. Gráfico Top 5 Proveedores (CxP)
    const dataCxP = @json($charts['topCxP']);
    new Chart(document.getElementById('chartTopCxP'), {
        type: 'doughnut',
        data: {
            labels: dataCxP.map(p => p.RazonSocial),
            datasets: [{
                label: 'Deuda (S/)',
                data: dataCxP.map(p => p.total_deuda),
                backgroundColor: ['#0d6efd', '#198754', '#0dcaf0', '#6610f2', '#20c997']
            }]
        },
        options: { responsive: true, maintainAspectRatio: true }
    });

});
</script>
@endpush