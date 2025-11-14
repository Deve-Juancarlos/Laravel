@extends('layouts.app')
@section('title', 'Lista de Productos')
@section('page-title', 'Catálogo de Productos')
@section('breadcrumbs')
    <li class="breadcrumb-item">Inventario</li>
    <li class="breadcrumb-item active" aria-current="page">Productos</li>
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

    /* Contenedor principal */
    .inventory-container {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }

    /* Header mejorado */
    .inventory-header {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        animation: fadeIn 0.5s ease;
    }

    .inventory-header h5 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .inventory-header p {
        color: #64748b;
        margin: 0.25rem 0 0 0;
        font-size: 0.875rem;
    }

    /* Botón de nuevo producto mejorado */
    .btn-nuevo-producto {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        border: none;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-nuevo-producto:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        color: white;
    }

    /* Tarjetas de estadísticas */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        background: linear-gradient(90deg, var(--primary), var(--info));
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

    .stat-card.primary .stat-icon {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: var(--primary);
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
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 600;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0.5rem 0 0 0;
    }

    /* Contenedor de búsqueda mejorado */
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

    /* Botones mejorados */
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

    /* Toggle de vista */
    .view-toggle {
        display: flex;
        gap: 0.5rem;
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 0.25rem;
        background: white;
    }

    .view-toggle button {
        padding: 0.5rem 1rem;
        border: none;
        background: transparent;
        border-radius: 8px;
        color: #64748b;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .view-toggle button.active {
        background: var(--primary);
        color: white;
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

    .table-modern {
        margin: 0;
    }

    .table-modern thead {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .table-modern thead th {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #475569;
        border-bottom: 2px solid var(--border);
        padding: 1.25rem 1rem;
    }

    .table-modern tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid #f1f5f9;
}

    .table-modern tbody tr:hover {
        background: linear-gradient(90deg, #f8fafc 0%, white 100%);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);  /* Sombra suave en lugar de scale */
    }

    .table-modern tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
    }

    /* Código de producto con estilo */
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

    /* Badge de stock */
    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .stock-badge.stock-low {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #dc2626;
    }

    .stock-badge.stock-medium {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #d97706;
    }

    .stock-badge.stock-high {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #059669;
    }

    .stock-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .stock-low .stock-indicator {
        background: #dc2626;
        box-shadow: 0 0 8px rgba(220, 38, 38, 0.5);
    }

    .stock-medium .stock-indicator {
        background: #d97706;
        box-shadow: 0 0 8px rgba(217, 119, 6, 0.5);
    }

    .stock-high .stock-indicator {
        background: #059669;
        box-shadow: 0 0 8px rgba(5, 150, 105, 0.5);
    }

    /* Precios */
    .price-cost {
        color: var(--danger);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .price-sale {
        color: var(--success);
        font-weight: 700;
        font-size: 1rem;
    }

    /* Botón ver lotes mejorado */
    .btn-ver-lotes {
        background: linear-gradient(135deg, var(--info) 0%, #0891b2 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        box-shadow: 0 2px 8px rgba(6, 182, 212, 0.3);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }

    .btn-ver-lotes:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.4);
        color: white;
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

    /* Paginación mejorada */
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
        .inventory-header {
            flex-direction: column;
            gap: 1rem;
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
<div class="inventory-container">
    <div class="container-fluid">
        <!-- Header -->
        <div class="inventory-header">
            <div>
                <h5>Lista de Productos</h5>
                <p>Gestión y control de inventario</p>
            </div>
            <a href="{{ route('contador.inventario.create') }}" class="btn-nuevo-producto">
                <i class="fas fa-plus-circle"></i> Nuevo Producto
            </a>
        </div>

        <!-- Estadísticas -->
        <div class="stats-container">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h6>Total Productos</h6>
                <p class="stat-value">{{ $productos->total() }}</p>
            </div>

            <div class="stat-card danger">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h6>Stock Bajo</h6>
                <p class="stat-value">{{ $productos->filter(function($p) { return $p->Stock < 50; })->count() }}</p>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h6>Valor Total Inventario</h6>
                <p class="stat-value">S/ {{ number_format($productos->sum(function($p) { return $p->Stock * $p->Costo; }), 2) }}</p>
            </div>
        </div>

        <!-- Búsqueda y Filtros -->
        <div class="search-container">
            <form method="GET" action="{{ route('contador.inventario.index') }}">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   class="search-input" 
                                   name="q" 
                                   placeholder="Buscar por Nombre, Código o Laboratorio..." 
                                   value="{{ $filtros['q'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <button type="submit" class="btn-filtrar">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                    <div class="col-lg-3">
                        <a href="{{ route('contador.inventario.index') }}" class="btn-limpiar">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de Productos -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Laboratorio</th>
                            <th class="text-center">Stock Total</th>
                            <th class="text-end">Costo</th>
                            <th class="text-end">P. Venta</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productos as $producto)
                            @php
                                $stockClass = 'stock-high';
                                if ($producto->Stock < 50) $stockClass = 'stock-low';
                                elseif ($producto->Stock < 100) $stockClass = 'stock-medium';
                            @endphp
                            <tr>
                                <td>
                                    <span class="product-code">{{ $producto->CodPro }}</span>
                                </td>
                                <td>
                                    <span class="product-name">{{ $producto->Nombre }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $producto->Laboratorio }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="stock-badge {{ $stockClass }}">
                                        <span class="stock-indicator"></span>
                                        {{ number_format($producto->Stock, 2) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="price-cost">S/ {{ number_format($producto->Costo, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="price-sale">S/ {{ number_format($producto->PventaMa, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('contador.inventario.show', $producto->CodPro) }}" 
                                       class="btn-ver-lotes" 
                                       title="Ver Lotes y Stock">
                                        <i class="fas fa-eye"></i> Ver Lotes
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <h4>No se encontraron productos</h4>
                                        <p>Intenta ajustar tus filtros de búsqueda</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($productos->hasPages())
                <div class="px-4 pb-4">
                    <div class="d-flex justify-content-end">
                        {{ $productos->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection