@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Libro Diario')

@push('styles')
    
    <link href="{{ asset('css/contabilidad/libro-diario.css') }}" rel="stylesheet">
@endpush

{{-- 
    MEJORA DE COHERENCIA:
    Usamos la sección 'header-content' que definimos en 'layouts.app'
    para poner el título, subtítulo y botón de acción.
--}}
@section('header-content')
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="page-title-info">
                <h1 class="page-title">Libro Diario</h1>
                <p class="page-subtitle">Registro completo de asientos contables de SEIMCORP</p>
            </div>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="window.location.href='{{ route('contador.libro-diario.create') }}'">
                <i class="fas fa-plus"></i> Nuevo Asiento
            </button>
        </div>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item active" aria-current="page">Libro Diario</li>
@endsection


@section('content')
<div class="libro-diario-view">

    {{-- Ya no necesitamos el 'page-header-modern' aquí, porque lo maneja el layout --}}

    {{-- Estadísticas Modernas (Tu código está perfecto) --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-icon primary">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-value" data-value="{{ $totales['total_asientos'] ?? 0 }}">{{ number_format($totales['total_asientos'] ?? 0) }}</div>
                    <div class="stat-label">Total Asientos (Filtro)</div>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-icon success">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-value" data-value="{{ $totales['total_debe'] ?? 0 }}">S/ {{ number_format($totales['total_debe'] ?? 0, 2) }}</div>
                    <div class="stat-label">Total Debe</div>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-icon danger">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-value" data-value="{{ $totales['total_haber'] ?? 0 }}">S/ {{ number_format($totales['total_haber'] ?? 0, 2) }}</div>
                    <div class="stat-label">Total Haber</div>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-icon {{ ($totales['balance'] ?? 0) == 0 ? 'info' : 'warning' }}">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-value" data-value="{{ $totales['balance'] ?? 0 }}">S/ {{ number_format($totales['balance'] ?? 0, 2) }}</div>
                    <div class="stat-label">Balance (Debe - Haber)</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros Modernos (Tu código está perfecto) --}}
    <div class="filters-card">
        <div class="filters-header">
            <h5 class="filters-title">
                <i class="fas fa-filter"></i>
                Filtros de Búsqueda
            </h5>
        </div>
        <div class="filters-body">
            {{-- 
                Usamos un 'id' en el form para que los botones de exportar 
                puedan leer los valores de los filtros fácilmente.
            --}}
            <form id="filter-form" method="GET" action="{{ route('contador.libro-diario.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" value="{{ $fechaInicio ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" name="fecha_fin" value="{{ $fechaFin ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Número Asiento</label>
                            <input type="text" class="form-control" name="numero_asiento" value="{{ request('numero_asiento') }}" placeholder="Ej: 2025-0001">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Cuenta Contable</label>
                            <input type="text" class="form-control" name="cuenta_contable" value="{{ request('cuenta_contable') }}" placeholder="Ej: 10411">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 d-flex justify-content-between">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Buscar
                            </button>
                            <a href="{{ route('contador.libro-diario.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Limpiar
                            </a>
                        </div>
                        <div class="btn-group">
                            {{-- Estos botones ahora llaman a las funciones JS externas --}}
                            <button type="button" class="btn btn-success" onclick="exportarExcel()">
                                <i class="fas fa-file-excel me-1"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-danger" onclick="exportarPDF()">
                                <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla Moderna de Asientos (Tu lógica @if está bien para este caso) --}}
    {{-- Tabla Moderna de Asientos --}}
<div class="table-card">
    @if(isset($asientos) && $asientos->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Glosa</th>
                        <th class="text-end">Total Debe</th>
                        <th class="text-end">Total Haber</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asientos as $asiento)
                    <tr>
                        <td>
                            <span class="asiento-numero">{{ $asiento->numero }}</span>
                        </td>
                        <td>
                            <span class="asiento-fecha">{{ \Carbon\Carbon::parse($asiento->fecha)->format('d/m/Y') }}</span>
                        </td>
                        <td>
                            <span class="asiento-glosa" title="{{ $asiento->glosa }}">
                                {{ Str::limit($asiento->glosa, 50) }}
                            </span>
                            @if($asiento->usuario_nombre)
                            <small class="d-block text-muted">Por: {{ $asiento->usuario_nombre }}</small>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="amount debe">S/ {{ number_format($asiento->total_debe, 2) }}</span>
                        </td>
                        <td class="text-end">
                            <span class="amount haber">S/ {{ number_format($asiento->total_haber, 2) }}</span>
                        </td>
                        <td class="text-center">
                            @if($asiento->balanceado)
                                <span class="badge bg-success-light">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Balanceado
                                </span>
                            @else
                                <span class="badge bg-warning-light">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Descuadrado
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <a href="{{ route('contador.libro-diario.show', $asiento->id) }}" 
                                   class="btn btn-icon btn-outline-primary" 
                                   title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('contador.libro-diario.edit', $asiento->id) }}" 
                                   class="btn btn-icon btn-outline-secondary" 
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-icon btn-outline-danger" 
                                        title="Eliminar"
                                        onclick="confirmarEliminacion({{ $asiento->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <form id="delete-form-{{ $asiento->id }}" 
                                      action="{{ route('contador.libro-diario.destroy', $asiento->id) }}" 
                                      method="POST" 
                                      style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($asientos instanceof \Illuminate\Pagination\LengthAwarePaginator && $asientos->hasPages())
        <div class="pagination-wrapper">
            {{ $asientos->appends(request()->query())->links() }}
        </div>
        @endif

    @else
        {{-- Estado Vacío --}}
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <h5>No se encontraron asientos contables</h5>
            <p>Los asientos aparecerán aquí según los filtros seleccionados o puedes crear el primer asiento ahora.</p>
            <a href="{{ route('contador.libro-diario.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Crear Primer Asiento
            </a>
        </div>
    @endif
</div>

</div>
@endsection

@push('scripts')

    <script src="{{ asset('js/contabilidad/libro-diario.js') }}"></script>
@endpush