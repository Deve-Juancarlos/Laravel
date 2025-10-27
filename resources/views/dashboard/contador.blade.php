@extends('layouts.app')

@section('title', 'Dashboard Contador - SIFANO')

@section('sidebar-menu')
{{-- MENÚ PRINCIPAL --}}
<div class="nav-section">Dashboard</div>
<ul>
    <li><a href="{{ route('dashboard.contador') }}" class="nav-link active">
        <i class="fas fa-chart-pie"></i> Panel Principal
    </a></li>
</ul>

{{-- CONTABILIDAD --}}
<div class="nav-section">Contabilidad</div>
<ul>
    <li>
        <a href="#" class="nav-link has-submenu">
            <i class="fas fa-book"></i> Libros Contables
        </a>
        <div class="nav-submenu">
            <a href="{{ route('contador.libro-diario.index') }}" class="nav-link"><i class="fas fa-file-alt"></i> Libro Diario</a>
            <a href="{{ route('contador.libro-mayor.index') }}" class="nav-link"><i class="fas fa-book-open"></i> Libro Mayor</a>
            <a href="{{route('contador.balance-comprobacion.index')}}" class="nav-link"><i class="fas fa-balance-scale"></i> Balance Comprobación</a>    
            <a href="{{ route('contador.estado-resultados.index') }}" class="nav-link"><i class="fas fa-chart-bar"></i> Estados Financieros</a>
        </div>
    </li>
    <li>
        <a href="#" class="nav-link has-submenu">
            <i class="fas fa-file-invoice"></i> Registros
        </a>
        <div class="nav-submenu">
            <a href="#" class="nav-link"><i class="fas fa-shopping-cart"></i> Compras</a>
            <a href="#" class="nav-link"><i class="fas fa-cash-register"></i> Ventas</a>
            <a href="#" class="nav-link"><i class="fas fa-university"></i> Bancos</a>
            <a href="#" class="nav-link"><i class="fas fa-money-bill-wave"></i> Caja</a>
        </div>
    </li>
</ul>

{{-- VENTAS Y COBRANZAS --}}
<div class="nav-section">Ventas & Cobranzas</div>
<ul>
    <li><a href="{{ route('contador.reportes.ventas') }}" class="nav-link">
        <i class="fas fa-chart-line"></i> Análisis Ventas
    </a></li>
    <li><a href="{{ route('contador.reportes.compras') }}" class="nav-link">
        <i class="fas fa-wallet"></i> Cartera
    </a></li>
    <li><a href="{{ route('contador.facturas.create') }}" class="nav-link">
        <i class="fas fa-clock"></i> Fact. Pendientes
    </a></li>
    <li><a href="{{ route('contador.facturas.index') }}" class="nav-link">
        <i class="fas fa-exclamation-triangle"></i> Fact. Vencidas
    </a></li>
</ul>

{{-- GESTIÓN --}}
<div class="nav-section">Gestión</div>
<ul>
    <li><a href="{{ route('contador.clientes') }}" class="nav-link">
        <i class="fas fa-users"></i> Clientes
    </a></li>
    <li><a href="{{ route('contador.reportes.medicamentos-controlados') }}" class="nav-link">
        <i class="fas fa-percentage"></i> Márgenes
    </a></li>
    <li><a href="{{ route('contador.reportes.inventario') }}" class="nav-link">
        <i class="fas fa-boxes"></i> Inventario
    </a></li>
</ul>

{{-- REPORTES SUNAT --}}
<div class="nav-section">SUNAT</div>
<ul>
    <li><a href="#" class="nav-link">
        <i class="fas fa-file-invoice-dollar"></i> PLE
    </a></li>
    <li><a href="#" class="nav-link">
        <i class="fas fa-percent"></i> IGV Mensual
    </a></li>
</ul>
@endsection

@section('content')
{{-- Breadcrumb --}}
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><i class="fas fa-home me-1"></i><a href="{{ route('dashboard.contador') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Dashboard Contador</li>
    </ol>
</nav>

{{-- Page Title --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1" style="font-weight: 700; color: #1f2937;">Dashboard Contador</h2>
        <p class="text-muted mb-0">Distribuidora de Fármacos - Panel de Control Financiero</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Imprimir
        </button>
        <button class="btn btn-primary btn-sm" onclick="location.reload()">
            <i class="fas fa-sync-alt me-2"></i>Actualizar
        </button>
    </div>
</div>

{{-- Fecha y Hora --}}
<div class="mb-4">
    <div class="d-inline-block px-3 py-2 rounded" style="background: #eff6ff; color: #1e40af; font-size: 0.875rem;">
        <i class="fas fa-calendar-day me-2"></i>
        <strong>{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</strong>
        <span class="mx-2">|</span>
        <i class="fas fa-clock me-2"></i>
        <strong id="reloj"></strong>
    </div>
</div>

{{-- Métricas Principales --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #2563eb !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 text-uppercase" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px;">Ventas del Mes</p>
                        <h3 class="mb-0" style="font-weight: 800; color: #2563eb;">S/ {{ number_format($ventasMes, 2) }}</h3>
                    </div>
                    <div class="rounded-3 p-3" style="background: rgba(37, 99, 235, 0.1);">
                        <i class="fas fa-chart-line" style="font-size: 1.5rem; color: #2563eb;"></i>
                    </div>
                </div>
                @if($variacionVentas != 0)
                <div class="d-inline-block px-2 py-1 rounded-pill" style="background: {{ $variacionVentas > 0 ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $variacionVentas > 0 ? '#10b981' : '#ef4444' }}; font-size: 0.7rem; font-weight: 700;">
                    <i class="fas fa-{{ $variacionVentas > 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>{{ abs($variacionVentas) }}% vs mes anterior
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid {{ $cuentasPorCobrarVencidas > ($cuentasPorCobrar * 0.3) ? '#ef4444' : '#f59e0b' }} !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 text-uppercase" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px;">Cuentas por Cobrar</p>
                        <h3 class="mb-0" style="font-weight: 800; color: {{ $cuentasPorCobrarVencidas > ($cuentasPorCobrar * 0.3) ? '#ef4444' : '#f59e0b' }};">S/ {{ number_format($cuentasPorCobrar, 2) }}</h3>
                    </div>
                    <div class="rounded-3 p-3" style="background: {{ $cuentasPorCobrarVencidas > ($cuentasPorCobrar * 0.3) ? 'rgba(239, 68, 68, 0.1)' : 'rgba(245, 158, 11, 0.1)' }};">
                        <i class="fas fa-hand-holding-usd" style="font-size: 1.5rem; color: {{ $cuentasPorCobrarVencidas > ($cuentasPorCobrar * 0.3) ? '#ef4444' : '#f59e0b' }};"></i>
                    </div>
                </div>
                @if($cuentasPorCobrarVencidas > 0)
                <div class="d-inline-block px-2 py-1 rounded-pill" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; font-size: 0.7rem; font-weight: 700;">
                    <i class="fas fa-exclamation-triangle me-1"></i>S/ {{ number_format($cuentasPorCobrarVencidas, 2) }} vencidas
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 text-uppercase" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px;">Ticket Promedio</p>
                        <h3 class="mb-0" style="font-weight: 800; color: #10b981;">S/ {{ number_format($ticketPromedio, 2) }}</h3>
                    </div>
                    <div class="rounded-3 p-3" style="background: rgba(16, 185, 129, 0.1);">
                        <i class="fas fa-receipt" style="font-size: 1.5rem; color: #10b981;"></i>
                    </div>
                </div>
                <div class="d-inline-block px-2 py-1 rounded-pill" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.7rem; font-weight: 700;">
                    <i class="fas fa-shopping-cart me-1"></i>Por venta
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #06b6d4 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 text-uppercase" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px;">Margen Bruto</p>
                        <h3 class="mb-0" style="font-weight: 800; color: #06b6d4;">{{ number_format($margenBrutoMes, 1) }}%</h3>
                    </div>
                    <div class="rounded-3 p-3" style="background: rgba(6, 182, 212, 0.1);">
                        <i class="fas fa-percentage" style="font-size: 1.5rem; color: #06b6d4;"></i>
                    </div>
                </div>
                <div class="d-inline-block px-2 py-1 rounded-pill" style="background: {{ $margenBrutoMes > 15 ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $margenBrutoMes > 15 ? '#10b981' : '#ef4444' }}; font-size: 0.7rem; font-weight: 700;">
                    <i class="fas fa-{{ $margenBrutoMes > 15 ? 'check-circle' : 'exclamation-circle' }} me-1"></i>{{ $margenBrutoMes > 15 ? 'Saludable' : 'Bajo' }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- KPIs Secundarios --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 0.75rem; font-weight: 600;">CLIENTES ACTIVOS</p>
                        <h4 class="mb-0" style="font-weight: 700; color: #1f2937;">{{ number_format($clientesActivos) }}</h4>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(37, 99, 235, 0.1);">
                        <i class="fas fa-users" style="font-size: 1.25rem; color: #2563eb;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 0.75rem; font-weight: 600;">FACTURAS PENDIENTES</p>
                        <h4 class="mb-0" style="font-weight: 700; color: #1f2937;">{{ number_format($facturasPendientes) }}</h4>
                        <small class="text-{{ $facturasVencidas > 0 ? 'danger' : 'success' }}" style="font-weight: 600; font-size: 0.75rem;">
                            <i class="fas fa-{{ $facturasVencidas > 0 ? 'exclamation-triangle' : 'check-circle' }} me-1"></i>{{ $facturasVencidas }} vencidas
                        </small>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1);">
                        <i class="fas fa-file-invoice-dollar" style="font-size: 1.25rem; color: #f59e0b;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 0.75rem; font-weight: 600;">DÍAS PROM. COBRANZA</p>
                        <h4 class="mb-0" style="font-weight: 700; color: #1f2937;">{{ $diasPromedioCobranza }} <small class="text-muted" style="font-size: 0.7rem;">días</small></h4>
                        <small class="text-{{ $diasPromedioCobranza <= 30 ? 'success' : 'warning' }}" style="font-weight: 600; font-size: 0.75rem;">
                            <i class="fas fa-{{ $diasPromedioCobranza <= 30 ? 'check' : 'clock' }} me-1"></i>{{ $diasPromedioCobranza <= 30 ? 'Óptimo' : 'Mejorar' }}
                        </small>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(6, 182, 212, 0.1);">
                        <i class="fas fa-calendar-alt" style="font-size: 1.25rem; color: #06b6d4;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Gráficos --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="font-weight: 700; color: #1f2937;">
                    <i class="fas fa-chart-line me-2" style="color: #2563eb;"></i>Ventas y Cobranzas (Últimos 6 Meses)
                </h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary active">6M</button>
                    <button class="btn btn-outline-primary">12M</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="ventasCobranzasChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0" style="font-weight: 700; color: #1f2937;">
                    <i class="fas fa-trophy me-2" style="color: #f59e0b;"></i>Top 10 Clientes
                </h5>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover mb-0">
                    <thead class="bg-light sticky-top">
                        <tr>
                            <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">#</th>
                            <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">CLIENTE</th>
                            <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;" class="text-end">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topClientes as $index => $cliente)
                        <tr>
                            <td style="font-weight: 700; color: #2563eb;">{{ $index + 1 }}</td>
                            <td>
                                <div style="font-weight: 600; font-size: 0.85rem; color: #1f2937;">{{ Str::limit($cliente['cliente'], 25) }}</div>
                                <small class="text-muted">{{ $cliente['facturas'] }} facturas</small>
                            </td>
                            <td class="text-end"><strong style="color: #10b981; font-size: 0.9rem;">S/ {{ number_format($cliente['total'], 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No hay datos disponibles</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Alertas --}}
@if(count($alertas) > 0)
<div class="mb-4">
    <h5 class="mb-3" style="font-weight: 700; color: #1f2937;">
        <i class="fas fa-bell me-2" style="color: #f59e0b;"></i>Alertas y Notificaciones
    </h5>
    <div class="row g-3">
        @foreach($alertas as $alerta)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm border-start border-3" style="border-color: {{ $alerta['tipo'] == 'danger' ? '#ef4444' : ($alerta['tipo'] == 'warning' ? '#f59e0b' : '#06b6d4') }} !important;">
                <div class="card-body py-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: {{ $alerta['tipo'] == 'danger' ? 'rgba(239, 68, 68, 0.1)' : ($alerta['tipo'] == 'warning' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(6, 182, 212, 0.1)') }}; min-width: 40px; height: 40px;">
                            <i class="fas fa-{{ $alerta['icono'] }}" style="font-size: 1.1rem; color: {{ $alerta['tipo'] == 'danger' ? '#ef4444' : ($alerta['tipo'] == 'warning' ? '#f59e0b' : '#06b6d4') }};"></i>
                        </div>
                        <div>
                            <h6 class="mb-1" style="font-weight: 700; font-size: 0.85rem; color: #1f2937;">{{ $alerta['titulo'] }}</h6>
                            <p class="text-muted mb-0" style="font-size: 0.8rem;">{{ $alerta['mensaje'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Tablas --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="font-weight: 700; color: #1f2937;">
                    <i class="fas fa-file-invoice me-2" style="color: #2563eb;"></i>Ventas Recientes
                </h5>
                <a href="#" class="btn btn-sm btn-outline-primary">Ver todas</a>
            </div>
            <div class="card-body p-0" style="overflow-x: auto;">
                <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                    <thead class="bg-light">
                        <tr>
                            <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">DOC</th>
                            <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">CLIENTE</th>
                            <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">FECHA</th>
                            <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;" class="text-end">TOTAL</th>
                            <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventasRecientes as $venta)
                        <tr>
                            <td>
                                <strong style="color: #1f2937;">{{ $venta['numero'] }}</strong><br>
                                <small class="text-muted">{{ $venta['tipo'] }}</small>
                            </td>
                            <td style="max-width: 150px;">{{ Str::limit($venta['cliente'], 25) }}</td>
                            <td><small>{{ $venta['fecha'] }}</small></td>
                            <td class="text-end">
                                <strong style="color: #1f2937;">S/ {{ number_format($venta['total'], 2) }}</strong>
                                @if($venta['saldo'] > 0)
                                <br><small class="text-danger">Saldo: S/ {{ number_format($venta['saldo'], 2) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $venta['estado_class'] }}" style="font-size: 0.7rem;">{{ $venta['estado'] }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay ventas recientes</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="font-weight: 700; color: #1f2937;">
                    <i class="fas fa-box-open me-2" style="color: #f59e0b;"></i>Stock Bajo
                </h5>
                <a href="{{ route('contador.productos.index') }}" class="btn btn-sm btn-outline-warning">Ver todos</a>
            </div>
            <div class="card-body p-0" style="overflow-x: auto;">
                <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                    <thead class="bg-light">
                        <tr>
                            <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">PRODUCTO</th>
                            <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">LAB</th>
                            <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;" class="text-center">STOCK</th>
                            <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">NIVEL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productosStockBajo as $producto)
                        <tr>
                            <td>
                                <strong style="color: #1f2937;">{{ $producto['codigo'] }}</strong><br>
                                <small>{{ Str::limit($producto['nombre'], 30) }}</small>
                            </td>
                            <td><small>{{ Str::limit($producto['laboratorio'], 15) }}</small></td>
                            <td class="text-center">
                                <strong style="color: #ef4444;">{{ number_format($producto['stock'], 0) }}</strong> / {{ number_format($producto['minimo'], 0) }}
                            </td>
                            <td style="min-width: 120px;">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $producto['porcentaje'] < 30 ? 'danger' : ($producto['porcentaje'] < 70 ? 'warning' : 'success') }}" 
                                         style="width: {{ min($producto['porcentaje'], 100) }}%"></div>
                                </div>
                                <small class="text-muted">{{ $producto['porcentaje'] }}%</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Stock adecuado</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Productos por Vencer --}}
@if(count($productosProximosVencer) > 0)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0" style="font-weight: 700; color: #1f2937;">
            <i class="fas fa-calendar-times me-2" style="color: #ef4444;"></i>Productos Próximos a Vencer (90 días)
        </h5>
        <a href="{{ route('contador.productos.index') }}" class="btn btn-sm btn-outline-danger">Ver todos</a>
    </div>
    <div class="card-body p-0" style="overflow-x: auto;">
        <table class="table table-hover mb-0" style="font-size: 0.85rem;">
            <thead class="bg-light">
                <tr>
                    <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">CÓDIGO</th>
                    <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">PRODUCTO</th>
                    <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">LABORATORIO</th>
                    <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">LOTE</th>
                    <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;">VENCIMIENTO</th>
                    <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;" class="text-center">STOCK</th>
                    <th style="font-size: 0.7rem; font-weight: 700; color: #6b7280;" class="text-center">DÍAS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productosProximosVencer as $producto)
                <tr>
                    <td><strong style="color: #1f2937;">{{ $producto['codigo'] }}</strong></td>
                    <td style="max-width: 200px;">{{ Str::limit($producto['nombre'], 40) }}</td>
                    <td><small>{{ Str::limit($producto['laboratorio'], 20) }}</small></td>
                    <td><small>{{ $producto['lote'] }}</small></td>
                    <td><small>{{ $producto['vencimiento'] }}</small></td>
                    <td class="text-center"><strong>{{ number_format($producto['stock'], 0) }}</strong></td>
                    <td class="text-center">
                        <span class="badge bg-{{ $producto['dias'] <= 30 ? 'danger' : ($producto['dias'] <= 60 ? 'warning' : 'info') }}" style="font-size: 0.7rem;">
                            {{ $producto['dias'] }} días
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// Reloj en tiempo real
function actualizarReloj() {
    const ahora = new Date();
    const horas = String(ahora.getHours()).padStart(2, '0');
    const minutos = String(ahora.getMinutes()).padStart(2, '0');
    const segundos = String(ahora.getSeconds()).padStart(2, '0');
    document.getElementById('reloj').textContent = `${horas}:${minutos}:${segundos}`;
}

setInterval(actualizarReloj, 1000);
actualizarReloj();

// Gráfico de Ventas y Cobranzas
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('ventasCobranzasChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($mesesLabels),
                datasets: [{
                    label: 'Ventas',
                    data: @json($ventasData),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }, {
                    label: 'Cobranzas',
                    data: @json($cobranzasData),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 13,
                                weight: '600',
                                family: "'Segoe UI', sans-serif"
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { 
                            size: 14, 
                            weight: 'bold',
                            family: "'Segoe UI', sans-serif"
                        },
                        bodyFont: { 
                            size: 13,
                            family: "'Segoe UI', sans-serif"
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'S/ ' + context.parsed.y.toLocaleString('es-PE', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString('es-PE');
                            },
                            font: {
                                size: 11,
                                family: "'Segoe UI', sans-serif"
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11,
                                weight: '600',
                                family: "'Segoe UI', sans-serif"
                            }
                        }
                    }
                }
            }
        });
    }
});

// Animación suave al cargar números
function animateValue(element, start, end, duration) {
    if (!element) return;
    
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = value.toLocaleString('es-PE');
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Auto-actualización cada 5 minutos (opcional)
// setInterval(() => location.reload(), 300000);

// Confirmación antes de imprimir
document.querySelector('button[onclick="window.print()"]')?.addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Imprimir Dashboard',
        text: '¿Desea imprimir el dashboard actual?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, imprimir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.print();
        }
    });
});
</script>
@endpush