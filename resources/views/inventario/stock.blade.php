@use('Illuminate\Support\Str')
@extends('layouts.app')
@section('title', 'Stock y Lotes')
@section('page-title', 'Reporte de Stock y Lotes')
@section('breadcrumbs')
    <li class="breadcrumb-item">Inventario</li>
    <li class="breadcrumb-item active" aria-current="page">Stock y Lotes</li>
@endsection

@push('styles')
<style>
    /* Variables de color */
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --info: #06b6d4;
        --light: #f8fafc;
        --dark: #1e293b;
        --border: #e2e8f0;
    }

    /* Animaciones */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    /* Contenedor principal */
    .stock-container {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }

    /* Header mejorado */
    .stock-header {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid var(--border);
        animation: fadeIn 0.5s ease;
    }

    .stock-header h5 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .stock-header p {
        color: #64748b;
        margin: 0.25rem 0 0 0;
        font-size: 0.875rem;
    }

    /* Tarjetas de estadísticas */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
        animation: slideIn 0.5s ease;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid var(--border);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }

    .stat-card.total::before {
        background: linear-gradient(90deg, var(--primary), var(--info));
    }

    .stat-card.warning::before {
        background: linear-gradient(90deg, var(--warning), #f97316);
    }

    .stat-card.danger::before {
        background: linear-gradient(90deg, var(--danger), #dc2626);
    }

    .stat-card.success::before {
        background: linear-gradient(90deg, var(--success), #059669);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }

    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .stat-card.total .stat-icon {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: var(--primary);
    }

    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: var(--warning);
    }

    .stat-card.danger .stat-icon {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: var(--danger);
    }

    .stat-card.success .stat-icon {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: var(--success);
    }

    .stat-card h6 {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0.5rem 0 0 0;
    }

    /* Contenedor de búsqueda */
    .search-container {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid var(--border);
        animation: fadeIn 0.6s ease;
    }

    .search-input-wrapper {
        position: relative;
    }

    .search-input-wrapper .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.1rem;
    }

    .search-input {
        padding: 0.875rem 1rem 0.875rem 3rem;
        border: 2px solid var(--border);
        border-radius: 12px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        width: 100%;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    /* Botones */
    .btn-filtrar {
        background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
        color: white;
        padding: 0.875rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        border: none;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn-filtrar:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        color: white;
    }

    .btn-limpiar {
        background: white;
        color: #64748b;
        padding: 0.875rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        border: 2px solid var(--border);
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn-limpiar:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
    }

    /* Tabla mejorada */
    .table-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid var(--border);
        overflow: hidden;
        animation: fadeIn 0.7s ease;
    }

    .table-stock {
        margin: 0;
    }

    .table-stock thead {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .table-stock thead th {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #475569;
        border-bottom: 2px solid var(--border);
        padding: 1.25rem 1rem;
    }

    .table-stock tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .table-stock tbody tr:hover {
        background: linear-gradient(90deg, #f8fafc 0%, white 100%);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .table-stock tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
    }

    /* Estados de fila según vencimiento */
    .row-expired {
        background: linear-gradient(90deg, #fef2f2 0%, #fee2e2 100%);
    }

    .row-expired:hover {
        background: linear-gradient(90deg, #fee2e2 0%, #fecaca 100%);
    }

    .row-expiring-soon {
        background: linear-gradient(90deg, #fffbeb 0%, #fef3c7 100%);
    }

    .row-expiring-soon:hover {
        background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
    }

    .row-good {
        background: white;
    }

    /* Código de producto */
    .product-code {
        display: inline-block;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        color: #475569;
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        font-family: 'Courier New', monospace;
        font-weight: 600;
        font-size: 0.85rem;
        border: 1px solid #cbd5e1;
    }

    /* Nombre de producto */
    .product-name {
        font-weight: 600;
        color: var(--dark);
        font-size: 0.95rem;
    }

    /* Badge de lote */
    .lote-badge {
        display: inline-block;
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: #4338ca;
        padding: 0.375rem 0.875rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        border: 1px solid #a5b4fc;
    }

    /* Fecha de vencimiento */
    .expiry-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #475569;
    }

    .expiry-icon {
        font-size: 1rem;
    }

    /* Badges de estado */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.875rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.expired {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #dc2626;
        border: 1px solid #fca5a5;
        animation: pulse 2s ease-in-out infinite;
    }

    .status-badge.expiring-soon {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #d97706;
        border: 1px solid #fcd34d;
    }

    .status-badge.good {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #059669;
        border: 1px solid #6ee7b7;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-badge.expired .status-dot {
        background: #dc2626;
        box-shadow: 0 0 8px rgba(220, 38, 38, 0.6);
    }

    .status-badge.expiring-soon .status-dot {
        background: #d97706;
        box-shadow: 0 0 8px rgba(217, 119, 6, 0.6);
    }

    .status-badge.good .status-dot {
        background: #059669;
        box-shadow: 0 0 8px rgba(5, 150, 105, 0.6);
    }

    /* Badge de almacén */
    .warehouse-badge {
        display: inline-block;
        background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
        color: #be185d;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        border: 1px solid #f9a8d4;
    }

    /* Saldo */
    .stock-amount {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--dark);
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 10px;
        display: inline-block;
        min-width: 80px;
        text-align: center;
    }

    /* Estado vacío */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        animation: fadeIn 0.5s ease;
    }

    .empty-state i {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1.5rem;
    }

    .empty-state h4 {
        color: var(--dark);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #64748b;
    }

    /* Paginación */
    .pagination {
        margin: 2rem 0 0 0;
    }

    .pagination .page-link {
        border: 2px solid var(--border);
        color: var(--dark);
        padding: 0.5rem 1rem;
        margin: 0 0.25rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .pagination .page-link:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        transform: translateY(-2px);
    }

    .pagination .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stock-header {
            text-align: center;
        }

        .stats-container {
            grid-template-columns: 1fr;
        }

        .table-container {
            overflow-x: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="stock-container">
    <div class="container-fluid">
        <!-- Header -->
        <div class="stock-header">
            <h5>Stock Detallado por Lote</h5>
            <p>Control y seguimiento de lotes con fechas de vencimiento</p>
        </div>

        <!-- Estadísticas -->
        <div class="stats-container">
            <div class="stat-card total">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h6>Total Lotes</h6>
                <p class="stat-value">{{ $lotes->total() }}</p>
            </div>

            <div class="stat-card danger">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h6>Lotes Vencidos</h6>
                <p class="stat-value">
                    {{ $lotes->filter(function($l) { 
                        return $l->vencimiento && \Carbon\Carbon::parse($l->vencimiento)->isPast(); 
                    })->count() }}
                </p>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h6>Por Vencer (90 días)</h6>
                <p class="stat-value">
                    {{ $lotes->filter(function($l) { 
                        if (!$l->vencimiento) return false;
                        $dias = \Carbon\Carbon::parse($l->vencimiento)->diffInDays(now(), false);
                        return $dias > -90 && $dias <= 0;
                    })->count() }}
                </p>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h6>Stock Total</h6>
                <p class="stat-value">{{ number_format($lotes->sum('saldo'), 0) }}</p>
            </div>
        </div>

        <!-- Búsqueda y Filtros -->
        <div class="search-container">
            <form method="GET" action="{{ route('contador.test.stock') }}">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   class="search-input" 
                                   name="q" 
                                   placeholder="Buscar por Producto, Código o Lote..." 
                                   value="{{ $filtros['q'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <button type="submit" class="btn-filtrar">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                    <div class="col-lg-3">
                        <a href="{{ route('contador.test.stock') }}" class="btn-limpiar">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de Lotes -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-stock">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Lote</th>
                            <th>Vencimiento</th>
                            <th class="text-center">Almacén</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lotes as $lote)
                            @php
                                $diasVencer = $lote->vencimiento ? \Carbon\Carbon::parse($lote->vencimiento)->diffInDays(now(), false) : null;
                                $claseVencido = 'row-good';
                                $estadoBadge = 'good';
                                $estadoTexto = 'Vigente';
                                
                                if ($diasVencer !== null) {
                                    if ($diasVencer > 0) {
                                        $claseVencido = 'row-expired';
                                        $estadoBadge = 'expired';
                                        $estadoTexto = 'Vencido';
                                    } elseif ($diasVencer > -90) {
                                        $claseVencido = 'row-expiring-soon';
                                        $estadoBadge = 'expiring-soon';
                                        $estadoTexto = 'Por vencer';
                                    }
                                }
                            @endphp
                            <tr class="{{ $claseVencido }}">
                                <td>
                                    <span class="product-code">{{ $lote->codpro }}</span>
                                </td>
                                <td>
                                    <span class="product-name">{{ $lote->Nombre }}</span>
                                </td>
                                <td>
                                    <span class="lote-badge">{{ $lote->lote }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="expiry-date">
                                            <i class="fas fa-calendar-alt expiry-icon text-muted"></i>
                                            <span>
                                                {{ $lote->vencimiento ? \Carbon\Carbon::parse($lote->vencimiento)->format('d/m/Y') : 'N/A' }}
                                            </span>
                                        </div>
                                        @if($lote->vencimiento)
                                            <span class="status-badge {{ $estadoBadge }}">
                                                <span class="status-dot"></span>
                                                {{ $estadoTexto }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="warehouse-badge">
                                        <i class="fas fa-warehouse me-1"></i>
                                        {{ $lote->almacen }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="stock-amount">{{ number_format($lote->saldo, 2) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <h4>No se encontró stock detallado</h4>
                                        <p>Intenta ajustar tus filtros de búsqueda</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($lotes->hasPages())
                <div class="px-4 pb-4">
                    <div class="d-flex justify-content-end">
                        {{ $lotes->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection