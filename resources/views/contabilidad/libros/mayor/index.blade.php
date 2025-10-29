@extends('layouts.app')

@section('title', 'Libro Mayor - SIFANO Contabilidad')

@section('styles')
    <link href="{{ asset('css/contabilidad/libro-mayor.css') }}" rel="stylesheet">
@endsection

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
<div class="container-fluid">
    <div class="main-content-wrapper">
        {{-- Header --}}
        <div class="page-header">
            <div>
                <h1><i class="fas fa-book-open me-2"></i>Libro Mayor</h1>
                <p>Distribución de movimientos por cuentas contables - SIFANO</p>

                {{-- Contexto de filtros --}}
                @if(request()->filled(['fecha_inicio', 'fecha_fin']) || request('cuenta'))
                    <div class="filter-context">
                        <i class="fas fa-filter"></i>
                        <span>Resultados para:</span>
                        @if(request('fecha_inicio') && request('fecha_fin'))
                            <span class="badge">
                                {{ \Carbon\Carbon::parse(request('fecha_inicio'))->format('d/m/Y') }} →
                                {{ \Carbon\Carbon::parse(request('fecha_fin'))->format('d/m/Y') }}
                            </span>
                        @endif
                        @if(request('cuenta'))
                            <span class="badge">Cuenta: {{ request('cuenta') }}</span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Botón de retorno al dashboard --}}
            <a href="{{ route('dashboard.contador') }}" class="btn-back-dashboard" title="Volver al panel principal">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>

        {{-- Filtros --}}
        <div class="filters-card">
            <form method="GET" action="{{ route('contador.libro-mayor.index') }}" id="filterForm">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio }}">
                    </div>
                    <div class="filter-group">
                        <label for="fecha_fin">Fecha Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin }}">
                    </div>
                    <div class="filter-group">
                        <label for="cuenta">Cuenta Contable</label>
                        <input type="text" id="cuenta" name="cuenta" value="{{ $cuenta }}" placeholder="Código o nombre...">
                    </div>
                    <button type="submit" class="btn-apply">
                        <i class="fas fa-filter"></i> Aplicar
                    </button>
                </div>
            </form>
        </div>

        {{-- Estadísticas --}}
        <div class="content-wrapper">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="stat-value">{{ number_format($totales->total_cuentas ?? 0) }}</div>
                    <p class="stat-label">Cuentas Activas</p>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
                    <div class="stat-value">S/ {{ number_format($totales->total_debe ?? 0, 2) }}</div>
                    <p class="stat-label">Total Débito</p>
                </div>
                <div class="stat-card danger">
                    <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
                    <div class="stat-value">S/ {{ number_format($totales->total_haber ?? 0, 2) }}</div>
                    <p class="stat-label">Total Crédito</p>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon"><i class="fas fa-balance-scale"></i></div>
                    <div class="stat-value">S/ {{ number_format(($totales->total_debe ?? 0) - ($totales->total_haber ?? 0), 2) }}</div>
                    <p class="stat-label">Diferencia</p>
                </div>
            </div>

            {{-- Tabla de Cuentas --}}
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title"><i class="fas fa-list"></i> Resumen por Cuentas</h3>
                    <a href="{{ route('contador.libro-mayor.exportar') }}?fecha_inicio={{ urlencode($fechaInicio) }}&fecha_fin={{ urlencode($fechaFin) }}&cuenta={{ urlencode($cuenta) }}"
                       class="btn-export" title="Exportar resultados actuales a Excel">
                        <i class="fas fa-file-excel"></i> <span>Exportar Excel</span>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Cuenta</th>
                                <th>Nombre</th>
                                <th class="text-center">Mov.</th>
                                <th class="text-end">Débito (S/)</th>
                                <th class="text-end">Crédito (S/)</th>
                                <th class="text-end">Saldo (S/)</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cuentas as $cuentaItem)
                                <tr>
                                    <td>
                                        <a href="{{ route('contador.libro-mayor.cuenta', $cuentaItem->cuenta) }}" class="account-link">
                                            {{ $cuentaItem->cuenta }}
                                        </a>
                                    </td>
                                    <td>{{ $cuentaItem->cuenta_nombre ?? '—' }}</td>
                                    <td class="text-center">{{ number_format($cuentaItem->movimientos) }}</td>
                                    <td class="text-end">{{ number_format($cuentaItem->total_debe ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($cuentaItem->total_haber ?? 0, 2) }}</td>
                                    <td class="text-end">
                                        @php
                                            $saldo = ($cuentaItem->total_debe ?? 0) - ($cuentaItem->total_haber ?? 0);
                                            $clase = $saldo > 0 ? 'saldo-deudor' : ($saldo < 0 ? 'saldo-acreedor' : 'saldo-saldo');
                                            $texto = $saldo != 0 ? ($saldo > 0 ? 'Deudor' : 'Acreedor') : 'Saldo';
                                        @endphp
                                        <span class="{{ $clase }}">{{ number_format(abs($saldo), 2) }}</span>
                                        @if($saldo != 0)
                                            <small class="d-block text-muted mt-1">{{ $texto }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('contador.libro-mayor.cuenta', $cuentaItem->cuenta) }}"
                                           class="btn-action" title="Ver movimientos detallados">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <div>No se encontraron cuentas con movimientos en el período seleccionado.</div>
                                        @if(request('cuenta'))
                                            <a href="{{ route('contador.libro-mayor.index') }}?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}"
                                               class="btn btn-sm btn-outline-primary mt-2">
                                                <i class="fas fa-list"></i> Ver todas las cuentas
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación --}}
            @if(method_exists($cuentas, 'links'))
                <div class="pagination-wrapper">
                    {{ $cuentas->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    const cuentaInput = document.querySelector('input[name="cuenta"]');
    let searchTimeout;

    // Auto-submit al cambiar fechas
    ['fecha_inicio', 'fecha_fin'].forEach(name => {
        const input = document.querySelector(`input[name="${name}"]`);
        if (input) {
            input.addEventListener('change', () => form.submit());
        }
    });

    // Búsqueda con retardo
    if (cuentaInput) {
        cuentaInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => form.submit(), 600);
        });
    }
});
</script>
@endsection