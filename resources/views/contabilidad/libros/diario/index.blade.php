@extends('layouts.app')

@section('title', 'Libro Diario')

<!-- 1. Título de la Cabecera -->
@section('page-title', 'Libro Diario')

<!-- 2. Breadcrumbs -->
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item active" aria-current="page">Libro Diario</li>
@endsection

<!-- 3. Estilos CSS de esta página -->
@push('styles')
    <!-- Hacemos referencia al CSS que define el look moderno de esta página -->
    <link href="{{ asset('css/contabilidad/libro-diario.css') }}" rel="stylesheet">
@endpush


<!-- 4. Contenido Principal -->
@section('content')
<div class="libro-diario-view">

    {{-- Header Moderno --}}
    <div class="page-header-modern">
        <div class="page-header-content">
            <div class="page-header-info">
                <h1>
                    <i class="fas fa-book"></i>
                    Libro Diario
                </h1>
                <p>Registro completo de asientos contables de SEIMCORP.</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('contador.libro-diario.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    Nuevo Asiento
                </a>
            </div>
        </div>
    </div>

    {{-- Alertas (Están en el layout, pero las dejamos por si se mueven) --}}
    {{-- Las alertas del layout app.blade.php ya manejan 'success' y 'error' --}}


    {{-- Estadísticas Modernas --}}
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

    {{-- Filtros Modernos --}}
    <div class="filters-card">
        <div class="filters-header">
            <h5 class="filters-title">
                <i class="fas fa-filter"></i>
                Filtros de Búsqueda
            </h5>
        </div>
        <div class="filters-body">
            <form method="GET" action="{{ route('contador.libro-diario.index') }}">
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
                            {{-- 
                            // Opcional: Si tienes pocas cuentas, un Select es mejor.
                            // Si tienes miles, un input de texto es más rápido.
                            <select class="form-select" name="cuenta_contable">
                                <option value="">Todas las cuentas</option>
                                @if(isset($cuentasContables))
                                    @foreach($cuentasContables as $cuenta)
                                        <option value="{{ $cuenta->codigo }}" {{ request('cuenta_contable') == $cuenta->codigo ? 'selected' : '' }}>
                                            {{ $cuenta->codigo }} - {{ $cuenta->nombre }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            --}}
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 d-flex justify-content-between">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <a href="{{ route('contador.libro-diario.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i> Limpiar
                            </a>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-success" onclick="exportarExcel()">
                                <i class="fas fa-file-excel"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-danger" onclick="exportarPDF()">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

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
                                <span class="badge bg-success-light text-success">
                                    <i class="fas fa-check-circle"></i>
                                    Balanceado
                                </span>
                            @else
                                <span class="badge bg-warning-light text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Descuadrado
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <a href="{{ route('contador.libro-diario.show', $asiento->id) }}" class="btn btn-icon btn-outline-primary" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('contador.libro-diario.edit', $asiento->id) }}" class="btn btn-icon btn-outline-secondary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- MEJORA: Botón de Eliminar añadido -->
                                <button type="button" class="btn btn-icon btn-outline-danger" 
                                        title="Eliminar"
                                        onclick="confirmarEliminacion({{ $asiento->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <form id="delete-form-{{ $asiento->id }}" action="{{ route('contador.libro-diario.destroy', $asiento->id) }}" method="POST" style="display: none;">
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
        {{-- Estado Vacío Mejorado --}}
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <h5>No se encontraron asientos contables</h5>
            <p>Los asientos aparecerán aquí según los filtros seleccionados o puedes crear el primer asiento ahora.</p>
            <a href="{{ route('contador.libro-diario.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Primer Asiento
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Funciones de exportación (requieren los parámetros de filtro)
function getFilterParams() {
    return new URLSearchParams(window.location.search);
}

function exportarExcel() {
    const params = getFilterParams();
    params.set('formato', 'excel');
    window.location.href = `{{ route('contador.libro-diario.exportar') }}?${params.toString()}`;
}

function exportarPDF() {
    const params = getFilterParams();
    params.set('formato', 'pdf');
    window.location.href = `{{ route('contador.libro-diario.exportar') }}?${params.toString()}`;
}

// Animación de números al cargar
function animateValue(element, start, end, duration) {
    const range = end - start;
    if (range === 0) {
        element.textContent = (end.toString().includes('.')) ? 'S/ ' + end.toFixed(2) : end.toString();
        return;
    }
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        
        const isDecimal = current.toString().includes('.') || end.toString().includes('.');
        const isCurrency = element.textContent.startsWith('S/ ');

        let formattedValue = isDecimal ? 
            current.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : 
            current.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        
        element.textContent = isCurrency ? 'S/ ' + formattedValue : formattedValue;
    }, 16);
}

window.addEventListener('load', function() {
    document.querySelectorAll('.stat-value').forEach(element => {
        const value = parseFloat(element.getAttribute('data-value')) || 0;
        const startValue = 0;
        
        if (element.textContent.startsWith('S/ ')) {
            element.textContent = 'S/ 0.00';
            animateValue(element, startValue, value, 1000);
        } else {
            element.textContent = '0';
            animateValue(element, startValue, value, 1000);
        }
    });
});


// Confirmación de eliminación (SweetAlert2)
function confirmarEliminacion(id) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción eliminará el asiento contable permanentemente. No se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Muestra el overlay de carga
                if(typeof showLoading === 'function') showLoading();
                document.getElementById('delete-form-' + id).submit();
            }
        });
    } else {
        // Fallback si SweetAlert no carga
        if (confirm('¿Estás seguro de eliminar este asiento? Esta acción no se puede deshacer.')) {
            if(typeof showLoading === 'function') showLoading();
            document.getElementById('delete-form-' + id).submit();
        }
    }
}
</script>
@endpush
