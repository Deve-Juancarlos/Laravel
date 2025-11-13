@extends('layouts.app')

@section('title', 'Dashboard Contador')

@push('styles')
    {{-- 
        Este es el CSS que me pediste. Asumo que se llama así.
        Aquí pondremos los estilos para 'kpi-card', 'modern-card', etc. 
    --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard/contador.css') }}">
@endpush

{{-- 
    Esta es la nueva sección que creamos en el layout.
    Nos permite poner un título grande y un subtítulo.
--}}
@section('header-content')
    <div class="dashboard-header">
        <div class="header-content">
            <h1><i class="fas fa-tachometer-alt me-3"></i>Dashboard del Contador</h1>
            <p class="subtitle">Panel de control integral con métricas financieras y operativas</p>
        </div>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')

{{-- ¡Importante! El content NO debe llevar el <div class="container-fluid">
     porque ya está en el layout principal.
     Pero como tu CSS puede depender de él, lo pondré DENTRO del content.
     (Lo ideal es quitarlo de aquí y que el layout lo maneje)
--}}
<div class="container-fluid p-0"> {{-- p-0 para que no haya doble padding --}}

    <div class="p-4"> {{-- Añadimos padding interno --}}
        <div class="row mb-4">
            
            {{-- KPI 1: Ventas del Mes --}}
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
            
            {{-- KPI 2: Cuentas por Cobrar --}}
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

            {{-- KPI 3: Variación --}}
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
            
            {{-- KPI 4: Facturas Vencidas --}}
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

        <div class="row mb-4">
            
            {{-- Columna del Gráfico --}}
            <div class="col-lg-8">
                <div class="card modern-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                        <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Ventas vs Cobranzas (Últimos 6 Meses)</h6>
                        
                        <div class="mt-2 mt-md-0">
                            {{-- ¡EL BOTÓN QUE FALTABA PARA ARREGLAR EL CACHÉ! --}}
                            <button id="btnClearCache" class="btn btn-warning-soft" title="Forzar actualización de datos">
                                <span class="btn-text"><i class="fas fa-sync-alt me-1"></i> Limpiar Caché</span>
                                <span class="btn-spinner d-none"><i class="fas fa-spinner fa-spin"></i> Cargando...</span>
                            </button>
                            
                            <a href="{{ route('contador.reportes.ventas.flujo-comparativo') }}" target="_blank" class="btn-gradient ms-2">
                                <i class="fas fa-file-alt me-1"></i> Ver Reporte
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="ventasChart" 
                                data-labels='@json($mesesLabels)'
                                data-ventas='@json($ventasData)'
                                data-cobranzas='@json($cobranzasData)'>
                            </canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Columna de Alertas --}}
            <div class="col-lg-4">
                <div class="card modern-card h-100">
                    <div class="card-header">
                        <h6><i class="fas fa-bell me-2"></i>Centro de Alertas</h6>
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

        <div class="row mb-4">
            
            {{-- Columna Top Clientes --}}
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header">
                        <h6><i class="fas fa-trophy me-2"></i>Top 10 Clientes del Mes</h6>
                    </div>
                    <div class="card-body p-0"> 
                        <ul class="list-group list-group-flush">
                            @forelse($topClientes as $cliente)
                            <li class="list-group-item top-client-item">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="top-client-avatar" style="--avatar-bg: var(--color-{{ $cliente['avatar_color'] }});">
                                            {{ $cliente['initial'] }}
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
            
            {{-- Columna Ventas Recientes --}}
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header">
                        <h6><i class="fas fa-shopping-cart me-2"></i>Ventas Recientes</h6>
                    </div>
                    <div class="card-body p-0 table-scrollable-y">
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
                                        @php
                                            $icon = 'fa-question-circle'; // Icono por defecto
                                            if ($venta['estado'] == 'PAGADA') $icon = 'fa-check-circle';
                                            if ($venta['estado'] == 'VENCIDA') $icon = 'fa-exclamation-triangle';
                                            if ($venta['estado'] == 'PENDIENTE') $icon = 'fa-clock';
                                        @endphp
                                        <span class="status-badge status-{{ $venta['estado_class'] }}">
                                            <i class="fas {{ $icon }} me-1"></i>
                                            {{ $venta['estado'] }}
                                        </span>
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

        <div class="row">
            <div class="col-12">
                <div class="card modern-card">
                    <div class="card-header">
                        <h6><i class="fas fa-boxes me-2"></i>Gestión de Inventario Crítico</h6>Ticket Promedio
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="kpi-card">
                            <div class="kpi-icon {{ $margenBrutoMes >= 30 ? 'success' : ($margenBrutoMes >= 15 ? 'warning' : 'danger') }}">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="kpi-content">
                                <div class="kpi-label">Margen Bruto</div>
                                <div class="kpi-value">{{ number_format($margenBrutoMes, 2) }}%</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Columna Stock Bajo --}}
                            <div class="col-lg-6 inventory-section">
                                <h6 class="mb-3"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Productos con Stock Bajo</h6>
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
                                                    <div class="d-flex justify-content-end align-items-center mb-1">
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
                            
                            {{-- Columna Próximos a Vencer --}}
                            <div class="col-lg-6 inventory-section">
                                <h6 class="mb-3"><i class="fas fa-calendar-times text-warning me-2"></i>Productos Próximos a Vencer (90 días)</h6>
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
    </div> {{-- Fin del padding interno --}}

</div> {{-- Fin del .container-fluid --}}

@endsection

@push('scripts')
  
    {{-- 🚀 CORRECCIÓN: ¡El motor de gráficos! Esto es necesario para que 'new Chart' funcione. --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Este es tu script que USA la librería para dibujar ESE gráfico en particular -->
    <script src="{{ asset('js/dashboard/contador.js') }}"></script>
@endpush