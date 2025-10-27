@extends('layouts.app')

@section('title', 'Libro Mayor - SIFANO Contabilidad')

@section('styles')
<style>
    :root {
        --primary: #0d3b66;
        --secondary: #1a5276;
        --success: #27ae60;
        --danger: #e74c3c;
        --warning: #f39c12;
        --info: #3498db;
        --light: #ecf0f1;
        --dark: #2c3e50;
        --gray-600: #7f8c8d;
        --border: #bdc3c7;
        --shadow: 0 2px 8px rgba(0,0,0,0.08);
        --transition: all 0.25s ease;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f7fa;
    }

    .container-fluid {
        padding: 0;
    }

    .main-content-wrapper {
        margin-left: 0;
        padding: 0;
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        position: relative;
    }

    .btn-back-dashboard {
        position: absolute;
        top: 1.25rem;
        right: 2rem;
        background: rgba(255,255,255,0.2);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        transition: var(--transition);
    }

    .btn-back-dashboard:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-1px);
    }

    .page-header h1 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .page-header p {
        margin: 0.25rem 0 0;
        opacity: 0.9;
        font-size: 0.95rem;
    }

    .filter-context {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-context .badge {
        background: rgba(255,255,255,0.2);
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.8rem;
    }

    .filters-card {
        background: white;
        border-radius: 8px;
        box-shadow: var(--shadow);
        margin-bottom: 1.5rem;
        padding: 1.25rem;
    }

    .filter-row {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .filter-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--dark);
    }

    .filter-group input,
    .filter-group select {
        padding: 0.5rem;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 0.9rem;
        transition: var(--transition);
    }

    .filter-group input:focus,
    .filter-group select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(13, 59, 102, 0.1);
    }

    .btn-apply {
        background: var(--primary);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-apply:hover {
        background: var(--secondary);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        box-shadow: var(--shadow);
        border-left: 4px solid var(--primary);
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    }

    .stat-card.success { border-left-color: var(--success); }
    .stat-card.danger { border-left-color: var(--danger); }
    .stat-card.warning { border-left-color: var(--warning); }
    .stat-card.info { border-left-color: var(--info); }

    .stat-icon {
        font-size: 1.5rem;
        color: var(--dark);
        opacity: 0.7;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0.25rem 0;
        color: var(--dark);
    }

    .stat-label {
        font-size: 0.85rem;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .table-container {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: var(--shadow);
        margin-top: 1rem;
    }

    .table-header {
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--light);
        border-bottom: 1px solid var(--border);
    }

    .table-title {
        font-size: 1.15rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--dark);
    }

    .btn-export {
        background: var(--success);
        border: none;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-export:hover {
        background: #218838;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(39, 174, 96, 0.3);
    }

    .table {
        margin: 0;
        font-size: 0.92rem;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table th {
        background: var(--light);
        font-weight: 600;
        padding: 0.75rem 1rem;
        color: var(--dark);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        white-space: nowrap;
        border: none;
    }

    .table td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        border-top: 1px solid var(--border);
        border-right: 1px solid var(--border);
        border-left: 1px solid var(--border);
    }

    .table tbody tr:hover {
        background-color: #fafafa;
    }

    .saldo-deudor { color: var(--danger); font-weight: 600; }
    .saldo-acreedor { color: var(--success); font-weight: 600; }
    .saldo-saldo { color: var(--gray-600); font-weight: 600; }

    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: var(--info);
        color: white;
        text-decoration: none;
        transition: var(--transition);
        font-size: 0.85rem;
    }

    .btn-action:hover {
        background: #117a8b;
        transform: scale(1.05);
    }

    .account-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
    }

    .account-link:hover {
        text-decoration: underline;
        color: var(--secondary);
    }

    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--gray-600);
        font-size: 0.95rem;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .pagination-wrapper {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 1rem;
        }
        .btn-back-dashboard {
            position: static;
            margin-top: 1rem;
            width: 100%;
            justify-content: center;
        }
        .page-header h1 {
            font-size: 1.4rem;
        }
        .filter-row {
            flex-direction: column;
            gap: 0.75rem;
        }
        .btn-apply {
            width: 100%;
        }
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .table-responsive {
            font-size: 0.85rem;
        }
        .btn-export span {
            display: none;
        }
    }
</style>
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