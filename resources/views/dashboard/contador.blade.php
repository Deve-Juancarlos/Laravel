@extends('layouts.app')

@section('title', 'Dashboard Contador')

@push('head')
    <link href="{{ asset('css/dashboard-contador.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sidebar-menu.css') }}" rel="stylesheet">
@endpush


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
        <a href="{{ route('contador.libro-diario.index') }}" class="nav-link has-submenu">
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
    <li>
        <a href="{{ route(contador.honorarios.index) }}" class="nav-link has-submenu">
            <i class="fas fa-file-invoice"></i> Honorarios
        </a>
        <div class="nav-submenu">
            <a href="#" class="nav-link"><i class="fas fa-shopping-cart"></i>categorias</a>
            <a href="#" class="nav-link"><i class="fas fa-cash-register"></i>estado-cuenta</a>
            <a href="#" class="nav-link"><i class="fas fa-university"></i>impuesto</a>
            <a href="#" class="nav-link"><i class="fas fa-money-bill-wave"></i>mensual</a>
            <a href="#" class="nav-link"><i class="fas fa-money-bill-wave"></i>proyeccion</a>
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
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><i class="fas fa-home me-1"></i><a href="{{ route('dashboard.contador') }}">Inicio</a></li>
        <li class="breadcrumb-item active" aria-current="page">Dashboard Contador</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1 fw-bold text-dark">Dashboard Contador</h2>
        <p class="text-muted mb-0">Distribuidora de Fármacos - Panel de Control Financiero</p>
    </div>
    <div class="d-flex gap-2">
        <button id="btn-imprimir" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-print me-2"></i>Imprimir
        </button>
        <button id="btn-actualizar" class="btn btn-primary btn-sm">
            <i class="fas fa-sync-alt me-2"></i>Actualizar
        </button>
    </div>
</div>

<div class="mb-4">
    <div class="d-inline-block px-3 py-2 rounded bg-blue-50 text-blue-800" style="font-size: 0.875rem;">
        <i class="fas fa-calendar-day me-2"></i>
        <strong>{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</strong>
        <span class="mx-2">|</span>
        <i class="fas fa-clock me-2"></i>
        <strong id="reloj"></strong>
    </div>
</div>

{{-- Tarjetas resumen estandarizadas --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total cartera</h6>
                <h4 class="mb-0 fw-bold">S/ {{ number_format($totales['cartera_total'] ?? 0, 2, ',', '.') }}</h4>
                <small class="text-muted">Saldo total por cobrar</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted mb-1">Vencido</h6>
                <h4 class="mb-0 fw-bold text-danger">S/ {{ number_format($totales['vencido'] ?? 0, 2, ',', '.') }}</h4>
                <small class="text-muted">Facturas vencidas</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted mb-1">Ventas (mes)</h6>
                <h4 class="mb-0 fw-bold">S/ {{ number_format($totales['ventas_mes'] ?? 0, 2, ',', '.') }}</h4>
                <small class="text-muted">Total facturado mes actual</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted mb-1">Clientes con mayor saldo</h6>
                @forelse($topClientesSaldo as $c)
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ Str::limit($c->Razon, 20) }}</span>
                        <span class="fw-medium">S/ {{ number_format($c->saldo, 2, ',', '.') }}</span>
                    </div>
                @empty
                    <small class="text-muted">Sin datos</small>
                @endforelse
            </div>
        </div>
    </div>
</div>

<hr>

<div class="card mb-4">
    <div class="card-header bg-light py-2">
        <h5 class="mb-0 fw-bold">Últimas facturas (muestra)</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Documento</th>
                    <th class="text-end">Importe</th>
                    <th class="text-end">Saldo</th>
                    <th class="text-center">F. Emisión</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ultimasFacturas as $f)
                <tr>
                    <td>{{ $f->Documento }}</td>
                    <td class="text-end">S/ {{ number_format($f->Importe ?? 0, 2, ',', '.') }}</td>
                    <td class="text-end">S/ {{ number_format($f->Saldo ?? 0, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $f->FechaF ? \Carbon\Carbon::parse($f->FechaF)->format('d/m/Y') : 'N/A' }}</td>
                    <td>
                        @php
                            $estado = $f->Estado ?? ($f->Saldo > 0 ? 'Pendiente' : 'Pagada');
                            $badgeClass = $estado === 'Pagada' ? 'success' : ($f->Saldo <= 0 ? 'secondary' : 'warning');
                        @endphp
                        <span class="badge bg-{{ $badgeClass }} rounded-pill">{{ $estado }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-3 text-muted">No hay facturas recientes</td>
                </tr>
                @endforelse
            </tbody>
        </table>
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
                        <h3 class="mb-0" style="font-weight: 800; color: #2563eb;">S/ {{ number_format($ventasMes ?? 0, 2, ',', '.') }}</h3>
                    </div>
                    <div class="rounded-3 p-3" style="background: rgba(37, 99, 235, 0.1);">
                        <i class="fas fa-chart-line" style="font-size: 1.5rem; color: #2563eb;"></i>
                    </div>
                </div>
                @if(isset($variacionVentas) && $variacionVentas != 0)
                <div class="d-inline-block px-2 py-1 rounded-pill" style="background: {{ $variacionVentas > 0 ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $variacionVentas > 0 ? '#10b981' : '#ef4444' }}; font-size: 0.7rem; font-weight: 700;">
                    <i class="fas fa-{{ $variacionVentas > 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>{{ abs($variacionVentas) }}% vs mes anterior
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid {{ ($cuentasPorCobrarVencidas ?? 0) > (($cuentasPorCobrar ?? 0) * 0.3) ? '#ef4444' : '#f59e0b' }} !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 text-uppercase" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px;">Cuentas por Cobrar</p>
                        <h3 class="mb-0" style="font-weight: 800; color: {{ ($cuentasPorCobrarVencidas ?? 0) > (($cuentasPorCobrar ?? 0) * 0.3) ? '#ef4444' : '#f59e0b' }};">S/ {{ number_format($cuentasPorCobrar ?? 0, 2, ',', '.') }}</h3>
                    </div>
                    <div class="rounded-3 p-3" style="background: {{ ($cuentasPorCobrarVencidas ?? 0) > (($cuentasPorCobrar ?? 0) * 0.3) ? 'rgba(239, 68, 68, 0.1)' : 'rgba(245, 158, 11, 0.1)' }};">
                        <i class="fas fa-hand-holding-usd" style="font-size: 1.5rem; color: {{ ($cuentasPorCobrarVencidas ?? 0) > (($cuentasPorCobrar ?? 0) * 0.3) ? '#ef4444' : '#f59e0b' }};"></i>
                    </div>
                </div>
                @if(($cuentasPorCobrarVencidas ?? 0) > 0)
                <div class="d-inline-block px-2 py-1 rounded-pill" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; font-size: 0.7rem; font-weight: 700;">
                    <i class="fas fa-exclamation-triangle me-1"></i>S/ {{ number_format($cuentasPorCobrarVencidas ?? 0, 2, ',', '.') }} vencidas
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
                        <h3 class="mb-0" style="font-weight: 800; color: #10b981;">S/ {{ number_format($ticketPromedio ?? 0, 2, ',', '.') }}</h3>
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
                        <h3 class="mb-0" style="font-weight: 800; color: #06b6d4;">{{ number_format($margenBrutoMes ?? 0, 1) }}%</h3>
                    </div>
                    <div class="rounded-3 p-3" style="background: rgba(6, 182, 212, 0.1);">
                        <i class="fas fa-percentage" style="font-size: 1.5rem; color: #06b6d4;"></i>
                    </div>
                </div>
                <div class="d-inline-block px-2 py-1 rounded-pill" style="background: {{ ($margenBrutoMes ?? 0) > 15 ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ ($margenBrutoMes ?? 0) > 15 ? '#10b981' : '#ef4444' }}; font-size: 0.7rem; font-weight: 700;">
                    <i class="fas fa-{{ ($margenBrutoMes ?? 0) > 15 ? 'check-circle' : 'exclamation-circle' }} me-1"></i>{{ ($margenBrutoMes ?? 0) > 15 ? 'Saludable' : 'Bajo' }}
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
                        <h4 class="mb-0" style="font-weight: 700; color: #1f2937;">{{ number_format($clientesActivos ?? 0) }}</h4>
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
                        <h4 class="mb-0" style="font-weight: 700; color: #1f2937;">{{ number_format($facturasPendientes ?? 0) }}</h4>
                        <small class="text-{{ ($facturasVencidas ?? 0) > 0 ? 'danger' : 'success' }}" style="font-weight: 600; font-size: 0.75rem;">
                            <i class="fas fa-{{ ($facturasVencidas ?? 0) > 0 ? 'exclamation-triangle' : 'check-circle' }} me-1"></i>{{ $facturasVencidas ?? 0 }} vencidas
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
                        <h4 class="mb-0" style="font-weight: 700; color: #1f2937;">{{ $diasPromedioCobranza ?? 0 }} <small class="text-muted" style="font-size: 0.7rem;">días</small></h4>
                        <small class="text-{{ ($diasPromedioCobranza ?? 0) <= 30 ? 'success' : 'warning' }}" style="font-weight: 600; font-size: 0.75rem;">
                            <i class="fas fa-{{ ($diasPromedioCobranza ?? 0) <= 30 ? 'check' : 'clock' }} me-1"></i>{{ ($diasPromedioCobranza ?? 0) <= 30 ? 'Óptimo' : 'Mejorar' }}
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
                <h5 class="mb-0 fw-bold text-dark">
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
                <h5 class="mb-0 fw-bold text-dark">
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
                                <div style="font-weight: 600; font-size: 0.85rem; color: #1f2937;">{{ Str::limit($cliente['cliente'] ?? '', 25) }}</div>
                                <small class="text-muted">{{ $cliente['facturas'] ?? 0 }} facturas</small>
                            </td>
                            <td class="text-end"><strong style="color: #10b981; font-size: 0.9rem;">S/ {{ number_format($cliente['total'] ?? 0, 2, ',', '.') }}</strong></td>
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
@if($alertas ?? false)
<div class="mb-4">
    <h5 class="mb-3 fw-bold text-dark">
        <i class="fas fa-bell me-2" style="color: #f59e0b;"></i>Alertas y Notificaciones
    </h5>
    <div class="row g-3">
        @foreach($alertas as $alerta)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm border-start border-3" style="border-color: {{ $alerta['tipo'] == 'danger' ? '#ef4444' : ($alerta['tipo'] == 'warning' ? '#f59e0b' : '#06b6d4') }} !important;">
                <div class="card-body py-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: {{ $alerta['tipo'] == 'danger' ? 'rgba(239, 68, 68, 0.1)' : ($alerta['tipo'] == 'warning' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(6, 182, 212, 0.1)') }}; min-width: 40px; height: 40px;">
                            <i class="fas fa-{{ $alerta['icono'] ?? 'bell' }}" style="font-size: 1.1rem; color: {{ $alerta['tipo'] == 'danger' ? '#ef4444' : ($alerta['tipo'] == 'warning' ? '#f59e0b' : '#06b6d4') }};"></i>
                        </div>
                        <div>
                            <h6 class="mb-1" style="font-weight: 700; font-size: 0.85rem; color: #1f2937;">{{ $alerta['titulo'] ?? '' }}</h6>
                            <p class="text-muted mb-0" style="font-size: 0.8rem;">{{ $alerta['mensaje'] ?? '' }}</p>
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
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-file-invoice me-2" style="color: #2563eb;"></i>Ventas Recientes
                </h5>
                <a href="#" class="btn btn-sm btn-outline-primary">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-light">
                            <tr>
                                <th>DOC</th>
                                <th>CLIENTE</th>
                                <th>FECHA</th>
                                <th class="text-end">TOTAL</th>
                                <th>ESTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventasRecientes as $venta)
                            <tr>
                                <td>
                                    <strong>{{ $venta['numero'] ?? '' }}</strong><br>
                                    <small class="text-muted">{{ $venta['tipo'] ?? '' }}</small>
                                </td>
                                <td style="max-width: 150px;">{{ Str::limit($venta['cliente'] ?? '', 25) }}</td>
                                <td><small>{{ $venta['fecha'] ?? '' }}</small></td>
                                <td class="text-end">
                                    <strong>S/ {{ number_format($venta['total'] ?? 0, 2, ',', '.') }}</strong>
                                    @if(($venta['saldo'] ?? 0) > 0)
                                    <br><small class="text-danger">Saldo: S/ {{ number_format($venta['saldo'], 2, ',', '.') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $venta['estado_class'] ?? 'secondary' }}" style="font-size: 0.7rem;">{{ $venta['estado'] ?? 'N/A' }}</span>
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
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-box-open me-2" style="color: #f59e0b;"></i>Stock Bajo
                </h5>
                <a href="{{ route('contador.productos.index') }}" class="btn btn-sm btn-outline-warning">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-light">
                            <tr>
                                <th>PRODUCTO</th>
                                <th>LAB</th>
                                <th class="text-center">STOCK</th>
                                <th>NIVEL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productosStockBajo as $producto)
                            <tr>
                                <td>
                                    <strong>{{ $producto['codigo'] ?? '' }}</strong><br>
                                    <small>{{ Str::limit($producto['nombre'] ?? '', 30) }}</small>
                                </td>
                                <td><small>{{ Str::limit($producto['laboratorio'] ?? '', 15) }}</small></td>
                                <td class="text-center">
                                    <strong class="text-danger">{{ number_format($producto['stock'] ?? 0, 0, ',', '.') }}</strong> / {{ number_format($producto['minimo'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td style="min-width: 120px;">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-{{ ($producto['porcentaje'] ?? 0) < 30 ? 'danger' : (($producto['porcentaje'] ?? 0) < 70 ? 'warning' : 'success') }}" 
                                             style="width: {{ min($producto['porcentaje'] ?? 0, 100) }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $producto['porcentaje'] ?? 0 }}%</small>
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
</div>

{{-- Productos por Vencer --}}
@if($productosProximosVencer ?? false)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="fas fa-calendar-times me-2" style="color: #ef4444;"></i>Productos Próximos a Vencer (90 días)
        </h5>
        <a href="{{ route('contador.productos.index') }}" class="btn btn-sm btn-outline-danger">Ver todos</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                <thead class="bg-light">
                    <tr>
                        <th>CÓDIGO</th>
                        <th>PRODUCTO</th>
                        <th>LABORATORIO</th>
                        <th>LOTE</th>
                        <th>VENCIMIENTO</th>
                        <th class="text-center">STOCK</th>
                        <th class="text-center">DÍAS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productosProximosVencer as $producto)
                    <tr>
                        <td><strong>{{ $producto['codigo'] ?? '' }}</strong></td>
                        <td style="max-width: 200px;">{{ Str::limit($producto['nombre'] ?? '', 40) }}</td>
                        <td><small>{{ Str::limit($producto['laboratorio'] ?? '', 20) }}</small></td>
                        <td><small>{{ $producto['lote'] ?? '' }}</small></td>
                        <td><small>{{ $producto['vencimiento'] ?? '' }}</small></td>
                        <td class="text-center"><strong>{{ number_format($producto['stock'] ?? 0, 0, ',', '.') }}</strong></td>
                        <td class="text-center">
                            <span class="badge bg-{{ ($producto['dias'] ?? 0) <= 30 ? 'danger' : (($producto['dias'] ?? 0) <= 60 ? 'warning' : 'info') }}" style="font-size: 0.7rem;">
                                {{ $producto['dias'] ?? 0 }} días
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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

// Botones
document.getElementById('btn-imprimir')?.addEventListener('click', function() {
    Swal.fire({
        title: '¿Imprimir dashboard?',
        text: 'Se generará una vista optimizada para impresión.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, imprimir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) window.print();
    });
});

document.getElementById('btn-actualizar')?.addEventListener('click', () => {
    location.reload();
});

// Gráfico
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('ventasCobranzasChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($mesesLabels ?? []),
                datasets: [{
                    label: 'Ventas',
                    data: @json($ventasData ?? []),
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
                    data: @json($cobranzasData ?? []),
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
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, padding: 20 }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                return label + 'S/ ' + parseFloat(context.parsed.y).toLocaleString('es-PE', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
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
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
<script src="{{ asset('js/app.js') }}"></script>
@endpush