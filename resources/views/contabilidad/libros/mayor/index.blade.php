@extends('layouts.app')

@section('title', 'Libro Mayor - SEIMCORP')

@push('styles')
    {{-- Referencia a los estilos que crearemos --}}
    <link href="{{ asset('css/contabilidad/libro-mayor.css') }}" rel="stylesheet">
    
    {{-- Estilos inline mejorados --}}
    <style>
        /* === LIBRO MAYOR VIEW === */
        .libro-mayor-view {
            padding: 0;
        }

        /* === KPIS STATS GRID === */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .stat-card { 
            display: flex; 
            align-items: center; 
            background: #fff; 
            border-radius: 12px; 
            padding: 1.5rem; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-icon { 
            width: 50px; 
            height: 50px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin-right: 1.25rem; 
            font-size: 1.5rem; 
            color: #fff; 
        }

        .stat-card.success .stat-icon { background: #198754; }
        .stat-card.danger .stat-icon { background: #dc3545; }
        .stat-card.info .stat-icon { background: #0dcaf0; }
        .stat-card:not(.success):not(.danger):not(.info) .stat-icon { background: #6f42c1; }

        .stat-info .stat-label { 
            font-size: 0.875rem; 
            font-weight: 600; 
            color: #6c757d; 
            text-transform: uppercase; 
            margin-bottom: 0.25rem; 
            margin: 0;
        }

        .stat-info .stat-value { 
            font-size: 1.5rem; 
            font-weight: 700; 
            color: #343a40; 
            margin: 0;
        }

        /* === FILTERS CARD === */
        .filters-card {
            border: none;
            border-radius: 12px;
        }

        /* === TABLE IMPROVEMENTS === */
        .table-container {
            border: none;
            border-radius: 12px;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
            padding: 0.75rem;
        }

        /* === ACCOUNT LINK === */
        .account-link {
            text-decoration: none;
            color: #0d6efd;
            font-weight: 600;
        }

        .account-link:hover {
            color: #0a58ca;
            text-decoration: underline;
        }

        /* === SALDO CLASSES === */
        .saldo-deudor {
            color: #dc3545;
            font-weight: 600;
        }

        .saldo-acreedor {
            color: #198754;
            font-weight: 600;
        }

        .saldo-cero {
            color: #6c757d;
        }

        /* === BUTTONS === */
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .btn-soft {
            background: rgba(var(--bs-bg-color-rgb), 0.1);
            border: 1px solid rgba(var(--bs-border-color-rgb), 0.3);
        }

        /* === ALERTS === */
        .alert {
            border: none;
            border-radius: 12px;
        }

        /* === PAGINATION === */
        .pagination-wrapper {
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        /* === EMPTY STATE === */
        .empty-state {
            padding: 3rem 1rem;
        }

        /* === RESPONSIVE === */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
                margin-right: 1rem;
            }
            
            .stat-info .stat-value {
                font-size: 1.25rem;
            }
            
            .btn-group {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-group .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
@endpush

@section('page-title', 'Libro Mayor')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Libro Mayor</li>
@endsection

@section('content')
    <div class="libro-mayor-view">
       
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button 
                    type="button" 
                    class="btn-close" 
                    data-bs-dismiss="alert" 
                    aria-label="Close"
                ></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button 
                    type="button" 
                    class="btn-close" 
                    data-bs-dismiss="alert" 
                    aria-label="Close"
                ></button>
            </div>
        @endif

        {{-- === FILTROS === --}}
        <div class="card shadow-sm filters-card mb-4">
            <div class="card-body">
                <form 
                    method="GET" 
                    action="{{ route('contador.libro-mayor.index') }}" 
                    id="filterForm"
                >
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Fecha Inicio
                            </label>
                            <input 
                                type="date" 
                                id="fecha_inicio" 
                                name="fecha_inicio" 
                                value="{{ $fechaInicio }}" 
                                class="form-control"
                            >
                        </div>
                        
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label">
                                <i class="fas fa-calendar-check me-1"></i>
                                Fecha Fin
                            </label>
                            <input 
                                type="date" 
                                id="fecha_fin" 
                                name="fecha_fin" 
                                value="{{ $fechaFin }}" 
                                class="form-control"
                            >
                        </div>
                        
                        <div class="col-md-4">
                            <label for="cuenta" class="form-label">
                                <i class="fas fa-list me-1"></i>
                                Cuenta Contable
                            </label>
                            <input 
                                type="text" 
                                id="cuenta" 
                                name="cuenta" 
                                value="{{ $cuenta ?? '' }}" 
                                class="form-control" 
                                placeholder="Buscar por código o nombre..."
                            >
                        </div>
                        
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i>
                                <span class="ms-1">Aplicar</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- === ESTADÍSTICAS (KPIs) === --}}
        <div class="stats-grid mb-4">
            <div class="stat-card shadow-sm">
                <div class="stat-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-label">Cuentas Activas</p>
                    <div class="stat-value">
                        {{ number_format($totales->total_cuentas ?? 0) }}
                    </div>
                </div>
            </div>
            
            <div class="stat-card shadow-sm success">
                <div class="stat-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-label">Total Débito</p>
                    <div class="stat-value">
                        S/ {{ number_format($totales->total_debe ?? 0, 2) }}
                    </div>
                </div>
            </div>
            
            <div class="stat-card shadow-sm danger">
                <div class="stat-icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-label">Total Crédito</p>
                    <div class="stat-value">
                        S/ {{ number_format($totales->total_haber ?? 0, 2) }}
                    </div>
                </div>
            </div>
            
            <div class="stat-card shadow-sm info">
                <div class="stat-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-label">Diferencia</p>
                    <div class="stat-value">
                        S/ {{ number_format($totales->diferencia ?? 0, 2) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- === TABLA DE CUENTAS === --}}
        <div class="card shadow-sm table-container">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 card-title">
                    <i class="fas fa-list me-2"></i>
                    Resumen por Cuentas
                </h5>
                
                {{-- === BOTONES DE ACCIÓN === --}}
                <div class="d-flex flex-wrap gap-2">
                    {{-- Grupo 1: Otros Reportes (Navegación) --}}
                    <div class="btn-group">
                        <a 
                            href="{{ route('contador.libro-mayor.comparacion', request()->query()) }}" 
                            class="btn btn-outline-secondary btn-sm"
                        >
                            <i class="fas fa-exchange-alt me-1"></i>
                            Comparar Períodos
                        </a>
                        <a 
                            href="{{ route('contador.libro-mayor.movimientos', request()->query()) }}" 
                            class="btn btn-outline-secondary btn-sm"
                        >
                            <i class="fas fa-file-alt me-1"></i>
                            Ver Movimientos
                        </a>
                    </div>

                    {{-- Grupo 2: Exportar --}}
                    <div class="btn-group">
                        <a 
                            href="{{ route('contador.libro-mayor.exportar', array_merge(request()->query(), ['tipo' => 'resumen'])) }}"
                            class="btn btn-success btn-sm" 
                            title="Exportar resumen actual a Excel"
                        >
                            <i class="fas fa-file-excel me-1"></i>
                            Exportar Resumen
                        </a>
                        <a 
                            href="{{ route('contador.libro-mayor.exportar', array_merge(request()->query(), ['tipo' => 'detallado'])) }}"
                            class="btn btn-success btn-sm" 
                            title="Exportar todos los movimientos a Excel"
                        >
                            <i class="fas fa-file-csv me-1"></i>
                            Exportar Detalle
                        </a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <i class="fas fa-hashtag me-1"></i>
                                Cuenta
                            </th>
                            <th>
                                <i class="fas fa-signature me-1"></i>
                                Nombre
                            </th>
                            <th class="text-center">
                                <i class="fas fa-sort-numeric-up me-1"></i>
                                Mov.
                            </th>
                            <th class="text-end">
                                <i class="fas fa-arrow-down me-1"></i>
                                Débito (S/)
                            </th>
                            <th class="text-end">
                                <i class="fas fa-arrow-up me-1"></i>
                                Crédito (S/)
                            </th>
                            <th class="text-end">
                                <i class="fas fa-balance-scale me-1"></i>
                                Saldo (S/)
                            </th>
                            <th class="text-center">
                                <i class="fas fa-cogs me-1"></i>
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        @forelse($cuentas as $cuentaItem)
                            <tr>
                                <td>
                                    <a 
                                        href="{{ route('contador.libro-mayor.cuenta', ['cuenta' => $cuentaItem->cuenta, 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" 
                                        class="account-link"
                                    >
                                        {{ $cuentaItem->cuenta }}
                                    </a>
                                </td>
                                
                                <td>
                                    <span class="fw-medium">
                                        {{ $cuentaItem->cuenta_nombre ?? '—' }}
                                    </span>
                                </td>
                                
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">
                                        {{ number_format($cuentaItem->movimientos) }}
                                    </span>
                                </td>
                                
                                <td class="text-end text-success">
                                    <strong>
                                        S/ {{ number_format($cuentaItem->total_debe ?? 0, 2) }}
                                    </strong>
                                </td>
                                
                                <td class="text-end text-danger">
                                    <strong>
                                        S/ {{ number_format($cuentaItem->total_haber ?? 0, 2) }}
                                    </strong>
                                </td>
                                
                                <td class="text-end">
                                    @php
                                        $clase = $cuentaItem->saldo > 0 ? 'saldo-deudor' : ($cuentaItem->saldo < 0 ? 'saldo-acreedor' : 'saldo-cero');
                                        $texto = $cuentaItem->saldo != 0 ? ($cuentaItem->saldo > 0 ? 'Deudor' : 'Acreedor') : 'Saldo Cero';
                                    @endphp
                                    
                                    <div class="{{ $clase }}">
                                        <strong>
                                            S/ {{ number_format(abs($cuentaItem->saldo), 2) }}
                                        </strong>
                                    </div>
                                    
                                    @if($cuentaItem->saldo != 0)
                                        <small class="d-block text-muted mt-1">
                                            {{ $texto }}
                                        </small>
                                    @endif
                                </td>
                                
                                <td class="text-center">
                                    <a 
                                        href="{{ route('contador.libro-mayor.cuenta', ['cuenta' => $cuentaItem->cuenta, 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}"
                                        class="btn btn-sm btn-outline-primary" 
                                        title="Ver movimientos detallados"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center p-5">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h5 class="mb-1">No se encontraron cuentas</h5>
                                        <p class="text-muted">
                                            No hay movimientos que coincidan con los filtros seleccionados.
                                        </p>
                                        <a 
                                            href="{{ route('contador.libro-mayor.index') }}" 
                                            class="btn btn-sm btn-primary mt-2"
                                        >
                                            <i class="fas fa-redo me-1"></i>
                                            Limpiar Filtros
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- === PAGINACIÓN === --}}
            @if(method_exists($cuentas, 'links') && $cuentas->hasPages())
                <div class="card-footer pagination-wrapper">
                    {{ $cuentas->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filterForm');
            const cuentaInput = document.querySelector('input[name="cuenta"]');
            let searchTimeout;

            // Búsqueda con retardo al escribir en "Cuenta Contable"
            if (cuentaInput) {
                cuentaInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        form.submit();
                    }, 600); // 600ms de espera antes de buscar
                });
            }

            // Prevenir submit duplicado al presionar Enter en el input de cuenta
            form.addEventListener('submit', function() {
                clearTimeout(searchTimeout);
            });
        });
    </script>
@endpush