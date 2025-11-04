@extends('layouts.app')

@section('title', 'Dashboard Contador')

@push('styles')
<style>
    /* Header Púrpura con Gradiente */
    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2.5rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 1.5rem 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        50% { transform: translate(-20px, -20px) rotate(180deg); }
    }

    .dashboard-header .header-content {
        position: relative;
        z-index: 2;
    }

    .dashboard-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .dashboard-header .subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        font-weight: 300;
    }

    /* KPI Cards */
    .kpi-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .kpi-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        margin-bottom: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .kpi-icon.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .kpi-icon.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .kpi-icon.info {
        background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
    }

    .kpi-icon.danger {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    }

    .kpi-content {
        flex: 1;
    }

    .kpi-label {
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .kpi-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .kpi-delta {
        font-size: 0.9rem;
        margin-left: 0.5rem;
    }

    /* Modern Cards */
    .modern-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: none;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .modern-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .modern-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: none;
        padding: 1.5rem 2rem;
        position: relative;
    }

    .modern-card .card-header h6 {
        color: #2c3e50;
        font-weight: 700;
        font-size: 1.1rem;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .modern-card .card-header h6 i {
        margin-right: 0.5rem;
        color: #667eea;
    }

    .modern-card .card-body {
        padding: 2rem;
    }

    .modern-card .card-body.p-0 {
        padding: 0 !important;
    }

    /* Chart Area */
    .chart-area {
        height: 350px;
        position: relative;
    }

    /* Alert List */
    .alert-list {
        max-height: 398px;
        overflow-y: auto;
    }

    .alert-item {
        border: none;
        border-radius: 0.75rem;
        margin-bottom: 0.75rem;
        padding: 1rem;
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }

    .alert-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .alert-item-primary {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-left: 4px solid #667eea;
    }

    .alert-item-success {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(32, 201, 151, 0.1) 100%);
        border-left: 4px solid #28a745;
    }

    .alert-item-warning {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(253, 126, 20, 0.1) 100%);
        border-left: 4px solid #ffc107;
    }

    .alert-item-danger {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(253, 126, 20, 0.1) 100%);
        border-left: 4px solid #dc3545;
    }

    .alert-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 14px;
        color: white;
    }

    .alert-icon-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .alert-icon-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .alert-icon-warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }

    .alert-icon-danger {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    }

    .alert-content {
        flex: 1;
    }

    .alert-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }

    .alert-message {
        color: #6c757d;
        font-size: 0.9rem;
        margin: 0;
    }

    /* Top Clients */
    .top-client-item {
        border: none;
        border-radius: 0.75rem;
        margin-bottom: 0.5rem;
        padding: 1rem;
        transition: all 0.3s ease;
    }

    .top-client-item:hover {
        background: rgba(102, 126, 234, 0.05);
        transform: translateX(5px);
    }

    .top-client-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
        font-size: 16px;
        margin-right: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .top-client-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    .top-client-code {
        color: #6c757d;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }

    .top-client-total {
        font-weight: 700;
        color: #28a745;
        font-size: 1rem;
    }

    /* Modern Table */
    .modern-table {
        margin-bottom: 0;
    }

    .modern-table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 1rem;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .modern-table tbody td {
        padding: 1rem;
        border-color: #f1f3f4;
        vertical-align: middle;
    }

    .modern-table tbody tr:hover {
        background: rgba(102, 126, 234, 0.05);
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pagado {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .status-pendiente {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        color: white;
    }

    .status-vencido {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        color: white;
    }

    /* Inventory Tables */
    .inventory-section {
        margin-bottom: 2rem;
    }

    .inventory-section h6 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
    }

    .inventory-section h6 i {
        margin-right: 0.5rem;
        color: #667eea;
    }

    .inventory-table {
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .inventory-table th,
    .inventory-table td {
        padding: 0.75rem;
        border-color: #f1f3f4;
    }

    .inventory-table th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .inventory-table .product-name {
        font-weight: 600;
        color: #2c3e50;
    }

    .inventory-table .product-code {
        color: #6c757d;
        font-size: 0.8rem;
    }

    .progress-custom {
        height: 8px;
        border-radius: 10px;
        background: #f1f3f4;
        overflow: hidden;
    }

    .progress-custom .progress-bar {
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .expiry-warning {
        color: #dc3545;
        font-weight: 600;
    }

    /* Empty States */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1rem;
    }

    /* Buttons */
    .btn-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .btn-gradient:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 2rem 0;
        }

        .dashboard-header h1 {
            font-size: 2rem;
        }

        .kpi-card {
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .kpi-icon {
            width: 60px;
            height: 60px;
            font-size: 20px;
        }

        .kpi-value {
            font-size: 1.4rem;
        }

        .modern-card .card-header {
            padding: 1rem 1.5rem;
        }

        .modern-card .card-body {
            padding: 1.5rem;
        }

        .chart-area {
            height: 300px;
        }
    }
</style>
@endpush

@section('page-title')
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="header-content">
                <h1><i class="fas fa-tachometer-alt me-3"></i>Dashboard del Contador</h1>
                <p class="subtitle">Panel de control integral con métricas financieras y operativas</p>
            </div>
        </div>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')

<div class="container-fluid">

    <!-- KPIs Section -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon primary">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Ventas del Mes</div>
                    <div class="kpi-value">S/ {{ number_format($ventasMes, 2) }}</div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon success">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Cuentas por Cobrar</div>
                    <div class="kpi-value">S/ {{ number_format($cuentasPorCobrar, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon {{ $variacionVentas >= 0 ? 'success' : 'danger' }}">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Variación vs Mes Anterior</div>
                    <div class="kpi-value">
                        {{ number_format($variacionVentas, 2) }}%
                        @if($variacionVentas > 0)
                            <i class="fas fa-arrow-up kpi-delta text-success"></i>
                        @elseif($variacionVentas < 0)
                            <i class="fas fa-arrow-down kpi-delta text-danger"></i>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Facturas Vencidas</div>
                    <div class="kpi-value">{{ $facturasVencidas }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart and Alerts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card modern-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6><i class="fas fa-chart-line"></i>Ventas vs Cobranzas (Últimos 6 Meses)</h6>
                    <a href="#" class="btn-gradient"><i class="fas fa-file-alt me-1"></i> Ver Reporte</a>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="ventasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card modern-card">
                <div class="card-header">
                    <h6><i class="fas fa-bell"></i>Centro de Alertas</h6>
                </div>
                <div class="card-body alert-list">
                    @forelse($alertas as $alerta)
                        <a href="{{ $alerta['accion'] ?? '#' }}" class="list-group-item list-group-item-action alert-item alert-item-{{ $alerta['tipo'] }}">
                            <div class="d-flex align-items-center">
                                <div class="alert-icon alert-icon-{{ $alerta['tipo'] }}">
                                    <i class="fas fa-{{ $alerta['icono'] }}"></i>
                                </div>
                                <div class="alert-content">
                                    <div class="alert-title">{{ $alerta['titulo'] }}</div>
                                    <p class="alert-message">{{ $alerta['mensaje'] }}</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <h5>¡Todo en orden!</h5>
                            <p>No hay alertas críticas.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Clients and Recent Sales -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card modern-card">
                <div class="card-header">
                    <h6><i class="fas fa-trophy"></i>Top 10 Clientes del Mes</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($topClientes as $cliente)
                        <li class="list-group-item top-client-item">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="top-client-avatar" style="background: linear-gradient(135deg, {{ $cliente['avatar_color'] }});">
                                        {{ substr($cliente['cliente'], 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="top-client-name">{{ $cliente['cliente'] }}</div>
                                        <div class="top-client-code">Cód: {{ $cliente['codigo'] }}</div>
                                    </div>
                                </div>
                                <span class="top-client-total">S/ {{ number_format($cliente['total'], 2) }}</span>
                            </div>
                        </li>
                        @empty
                        <li class="list-group-item empty-state">
                            <i class="fas fa-users"></i>
                            <h5>No hay ventas registradas</h5>
                            <p>No hay datos de clientes este mes.</p>
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card modern-card">
                <div class="card-header">
                    <h6><i class="fas fa-shopping-cart"></i>Ventas Recientes</h6>
                </div>
                <div class="card-body p-0" style="max-height: 485px; overflow-y: auto;">
                    <table class="table table-hover modern-table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><i class="fas fa-calendar me-1"></i>Fecha</th>
                                <th><i class="fas fa-user me-1"></i>Cliente</th>
                                <th class="text-end"><i class="fas fa-dollar-sign me-1"></i>Total</th>
                                <th class="text-center"><i class="fas fa-check-circle me-1"></i>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventasRecientes as $venta)
                            <tr>
                                <td>{{ $venta['fecha'] }}</td>
                                <td>{{ $venta['cliente'] }}</td>
                                <td class="text-end fw-600">S/ {{ number_format($venta['total'], 2) }}</td>
                                <td class="text-center">
                                    @if($venta['estado'] == 'Pagado')
                                        <span class="status-badge status-pagado">
                                            <i class="fas fa-check me-1"></i>{{ $venta['estado'] }}
                                        </span>
                                    @elseif($venta['estado'] == 'Pendiente')
                                        <span class="status-badge status-pendiente">
                                            <i class="fas fa-clock me-1"></i>{{ $venta['estado'] }}
                                        </span>
                                    @else
                                        <span class="status-badge status-vencido">
                                            <i class="fas fa-exclamation me-1"></i>{{ $venta['estado'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="fas fa-receipt"></i>
                                    <h5>No hay ventas recientes</h5>
                                    <p>No se encontraron ventas recientes.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Management -->
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h6><i class="fas fa-boxes"></i>Gestión de Inventario Crítico</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 inventory-section">
                            <h6><i class="fas fa-exclamation-triangle"></i>Productos con Stock Bajo</h6>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle inventory-table">
                                    <tbody>
                                        @forelse($productosStockBajo as $p)
                                        <tr class="border-bottom">
                                            <td class="w-50">
                                                <div class="product-name">{{ $p['nombre'] }}</div>
                                                <div class="product-code">{{ $p['codigo'] }} | {{ $p['laboratorio'] }}</div>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-end align-items-center mb-2">
                                                    <span class="fw-600 me-2">{{ $p['stock'] }}</span>
                                                    <span class="text-muted">/ {{ $p['minimo'] }}</span>
                                                </div>
                                                <div class="progress-custom">
                                                    <div class="progress-bar bg-{{ $p['criticidad'] == 'crítica' ? 'danger' : 'warning' }}" 
                                                         style="width: {{ $p['porcentaje'] }}%;">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="empty-state">
                                                <i class="fas fa-boxes"></i>
                                                <h5>Stock en orden</h5>
                                                <p>No hay productos con stock bajo.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-lg-6 inventory-section">
                            <h6><i class="fas fa-calendar-times"></i>Productos Próximos a Vencer (90 días)</h6>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle inventory-table">
                                    <tbody>
                                        @forelse($productosProximosVencer as $p)
                                        <tr class="border-bottom {{ $p['riesgo'] == 'alto' ? 'table-danger' : ($p['riesgo'] == 'medio' ? 'table-warning' : '') }}">
                                            <td>
                                                <div class="product-name">{{ $p['nombre'] }}</div>
                                                <div class="product-code">{{ $p['lote'] }} | {{ $p['laboratorio'] }}</div>
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-600">{{ $p['vencimiento'] }}</div>
                                                <div class="expiry-warning">({{ $p['dias'] }} días)</div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="empty-state">
                                                <i class="fas fa-calendar-check"></i>
                                                <h5>Productos en fecha</h5>
                                                <p>No hay productos próximos a vencer.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
{{-- Chart.js debe estar cargado en layouts.app --}}
{{-- Si no lo está, descomenta: <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}

<script>
    // CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Gráfico de Ventas con Chart.js
    const ctxVentas = document.getElementById('ventasChart');
    if (ctxVentas) {
        new Chart(ctxVentas, {
            type: 'line',
            data: {
                labels: @json($mesesLabels),
                datasets: [
                {
                    label: 'Ventas',
                    data: @json($ventasData),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                },
                {
                    label: 'Cobranzas',
                    data: @json($cobranzasData),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }
            ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return 'S/ ' + value.toLocaleString();
                            },
                            color: '#6c757d'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#6c757d'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                weight: 600
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#667eea',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'S/ ' + context.parsed.y.toLocaleString('es-PE', { minimumFractionDigits: 2 });
                                }
                                return label;
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                }
            }
        });
    }

    // Animaciones de entrada para KPIs
    document.addEventListener('DOMContentLoaded', function() {
        const kpiCards = document.querySelectorAll('.kpi-card');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                }
            });
        });

        kpiCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });
    });
</script>
@endpush