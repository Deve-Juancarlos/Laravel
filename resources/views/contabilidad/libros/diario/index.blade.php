@extends('layouts.app')

@section('title', 'Libro Diario')

@push('styles')
    <link href="{{ asset('css/contabilidad/libro-diario.css') }}" rel="stylesheet">
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
    {{-- Header Moderno --}}
    <div class="page-header-modern">
        <div class="page-header-content">
            <div class="page-header-info">
                <h1>
                    <i class="fas fa-book"></i>
                    Libro Diario
                </h1>
                <p>Registro completo de asientos contables - SIFANO</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('contador.libro-diario.create') }}" class="btn">
                    <i class="fas fa-plus-circle"></i>
                    Nuevo Asiento
                </a>
            </div>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-times-circle"></i>
        <span>{{ session('error') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
    </div>
    @endif

    {{-- Estadísticas Modernas --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-icon primary">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-value">{{ number_format($totales['total_asientos'] ?? 0) }}</div>
                    <div class="stat-label">Total Asientos</div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i>
                        12% este mes
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-icon success">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-value">S/ {{ number_format($totales['total_debe'] ?? 0, 2) }}</div>
                    <div class="stat-label">Total Debe</div>
                    <div class="stat-trend up">
                        <i class="fas fa-check"></i>
                        Balanceado
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-icon danger">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-value">S/ {{ number_format($totales['total_haber'] ?? 0, 2) }}</div>
                    <div class="stat-label">Total Haber</div>
                    <div class="stat-trend up">
                        <i class="fas fa-check"></i>
                        Balanceado
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-icon info">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-value">S/ {{ number_format($totales['promedio_asiento'] ?? 0, 2) }}</div>
                    <div class="stat-label">Promedio Asiento</div>
                    <div class="stat-trend">
                        <i class="fas fa-equals"></i>
                        Último mes
                    </div>
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
                            <input type="text" class="form-control" name="numero_asiento" value="{{ request('numero_asiento') }}" placeholder="Ej: 2025001">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Cuenta Contable</label>
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
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <a href="{{ route('contador.libro-diario.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Limpiar Filtros
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla Moderna de Asientos --}}
    <div class="table-card">
        <div class="table-header">
            <h5 class="table-title">
                <i class="fas fa-list"></i>
                Asientos Contables
            </h5>
            <div class="table-actions">
                <button class="btn btn-success btn-sm" onclick="exportarExcel()">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn btn-danger btn-sm" onclick="exportarPDF()">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
            </div>
        </div>

        @if(isset($asientos) && $asientos->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Glosa</th>
                        <th style="text-align: right;">Total Debe</th>
                        <th style="text-align: right;">Total Haber</th>
                        <th style="text-align: center;">Estado</th>
                        <th style="text-align: center;">Acciones</th>
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
                        </td>
                        <td>
                            <span class="amount debe">S/ {{ number_format($asiento->total_debe, 2) }}</span>
                        </td>
                        <td>
                            <span class="amount haber">S/ {{ number_format($asiento->total_haber, 2) }}</span>
                        </td>
                        <td style="text-align: center;">
                            @if($asiento->balanceado ?? ($asiento->total_debe == $asiento->total_haber))
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i>
                                    Balanceado
                                </span>
                            @else
                                <span class="badge badge-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Pendiente
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('contador.libro-diario.show', $asiento->id) }}" class="btn btn-icon btn-outline-primary" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('contador.libro-diario.edit', $asiento->id) }}" class="btn btn-icon btn-outline-secondary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($asientos instanceof \Illuminate\Pagination\LengthAwarePaginator && $asientos->hasPages())
        <div class="pagination-wrapper" style="padding: 1.25rem; background: var(--gray-50); border-top: 1px solid var(--gray-200);">
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
// Auto-dismiss alertas
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'slideInUp 0.3s ease reverse';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Cerrar alertas manualmente
    document.querySelectorAll('.btn-close').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.alert').remove();
        });
    });
});

// Funciones de exportación
function exportarExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('formato', 'excel');
    window.location.href = `{{ route('contador.libro-diario.exportar') }}?${params}`;
}

function exportarPDF() {
    const params = new URLSearchParams(window.location.search);
    params.set('formato', 'pdf');
    window.location.href = `{{ route('contador.libro-diario.exportar') }}?${params}`;
}

// Animación de números al cargar
function animateValue(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        
        if (element.classList.contains('stat-value')) {
            const isDecimal = end.toString().includes('.');
            element.textContent = isDecimal ? 
                'S/ ' + current.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : 
                current.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    }, 16);
}

// Iniciar animaciones al cargar la página
window.addEventListener('load', function() {
    document.querySelectorAll('.stat-value').forEach(element => {
        const text = element.textContent.replace(/[^\d.]/g, '');
        const value = parseFloat(text) || 0;
        element.textContent = '0';
        animateValue(element, 0, value, 1000);
    });
});

// Tooltip para glosas largas
document.querySelectorAll('.asiento-glosa').forEach(element => {
    element.addEventListener('mouseenter', function() {
        if (this.scrollWidth > this.clientWidth) {
            this.style.whiteSpace = 'normal';
        }
    });
    
    element.addEventListener('mouseleave', function() {
        this.style.whiteSpace = 'nowrap';
    });
});

// Confirmación de eliminación (si la agregas)
function confirmarEliminacion(id) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    } else {
        if (confirm('¿Estás seguro de eliminar este asiento?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
}
</script>
@endpush