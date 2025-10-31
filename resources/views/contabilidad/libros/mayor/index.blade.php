@extends('layouts.app')

@section('title', 'Libro Mayor - SEIMCORP')

@push('styles')
    {{-- Referencia a los estilos que crearemos --}}
    <link href="{{ asset('css/contabilidad/libro-mayor.css') }}" rel="stylesheet">
@endpush

{{-- 1. Título de la Página --}}
@section('page-title', 'Libro Mayor')

{{-- 2. Breadcrumbs --}}
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item active" aria-current="page">Libro Mayor</li>
@endsection

{{-- 3. Contenido Principal --}}
@section('content')
<div class="libro-mayor-view">

    {{-- Alertas (vienen del redirect) --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Filtros --}}
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.libro-mayor.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="cuenta" class="form-label">Cuenta Contable</label>
                        <input type="text" id="cuenta" name="cuenta" value="{{ $cuenta ?? '' }}" class="form-control" placeholder="Buscar por código o nombre...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Aplicar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Estadísticas (KPIs) --}}
    <div class="stats-grid mb-4">
        <div class="stat-card shadow-sm">
            <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
            <div class="stat-info">
                <p class="stat-label">Cuentas Activas</p>
                <div class="stat-value">{{ number_format($totales->total_cuentas ?? 0) }}</div>
            </div>
        </div>
        <div class="stat-card shadow-sm success">
            <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
            <div class="stat-info">
                <p class="stat-label">Total Débito</p>
                <div class="stat-value">S/ {{ number_format($totales->total_debe ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="stat-card shadow-sm danger">
            <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
            <div class="stat-info">
                <p class="stat-label">Total Crédito</p>
                <div class="stat-value">S/ {{ number_format($totales->total_haber ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="stat-card shadow-sm info">
            <div class="stat-icon"><i class="fas fa-balance-scale"></i></div>
            <div class="stat-info">
                <p class="stat-label">Diferencia</p>
                <div class="stat-value">S/ {{ number_format($totales->diferencia ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Tabla de Cuentas --}}
    <div class="card shadow-sm table-container">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 card-title"><i class="fas fa-list me-2"></i>Resumen por Cuentas</h5>
            
            {{-- BOTONES AGREGADOS --}}
            <div class="d-flex flex-wrap gap-2">
                <!-- Grupo 1: Otros Reportes (Navegación) -->
                <div class="btn-group">
                    <a href="{{ route('contador.libro-mayor.comparacion', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-exchange-alt me-1"></i> Comparar Períodos
                    </a>
                    <a href="{{ route('contador.libro-mayor.movimientos', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-file-alt me-1"></i> Ver Movimientos
                    </a>
                </div>

                <!-- Grupo 2: Exportar -->
                <div class="btn-group">
                    <a href="{{ route('contador.libro-mayor.exportar', array_merge(request()->query(), ['tipo' => 'resumen'])) }}"
                       class="btn btn-success btn-sm" title="Exportar resumen actual a Excel">
                        <i class="fas fa-file-excel me-1"></i> Exportar Resumen
                    </a>
                    <a href="{{ route('contador.libro-mayor.exportar', array_merge(request()->query(), ['tipo' => 'detallado'])) }}"
                       class="btn btn-success-soft btn-sm" title="Exportar todos los movimientos a Excel">
                        <i class="fas fa-file-csv me-1"></i> Exportar Detalle
                    </a>
                </div>
            </div>
            {{-- FIN BOTONES AGREGADOS --}}

        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
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
                                <a href="{{ route('contador.libro-mayor.cuenta', ['cuenta' => $cuentaItem->cuenta, 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" class="account-link">
                                    {{ $cuentaItem->cuenta }}
                                </a>
                            </td>
                            <td>{{ $cuentaItem->cuenta_nombre ?? '—' }}</td>
                            <td class="text-center">{{ number_format($cuentaItem->movimientos) }}</td>
                            <td class="text-end text-success">{{ number_format($cuentaItem->total_debe ?? 0, 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($cuentaItem->total_haber ?? 0, 2) }}</td>
                            <td class="text-end">
                                @php
                                    $clase = $cuentaItem->saldo > 0 ? 'saldo-deudor' : ($cuentaItem->saldo < 0 ? 'saldo-acreedor' : 'saldo-cero');
                                    $texto = $cuentaItem->saldo != 0 ? ($cuentaItem->saldo > 0 ? 'Deudor' : 'Acreedor') : 'Saldo Cero';
                                @endphp
                                <span class="{{ $clase }}">{{ number_format(abs($cuentaItem->saldo), 2) }}</span>
                                @if($cuentaItem->saldo != 0)
                                    <small class="d-block text-muted mt-1">{{ $texto }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('contador.libro-mayor.cuenta', ['cuenta' => $cuentaItem->cuenta, 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}"
                                   class="btn btn-sm btn-outline-primary" title="Ver movimientos detallados">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center p-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="mb-1">No se encontraron cuentas</h5>
                                <p class="text-muted">No hay movimientos que coincidan con los filtros seleccionados.</p>
                                <a href="{{ route('contador.libro-mayor.index') }}" class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-redo me-1"></i> Limpiar Filtros
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
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

