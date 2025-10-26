@extends('layouts.app')

@push('styles')
<style>
    /* Estilos específicos para Contador */
    .sidebar {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    .sidebar-brand {
        background: rgba(255,255,255,0.1);
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }

    .nav-link:hover,
    .nav-link.active {
        background: rgba(255,255,255,0.15);
        border-left-color: #10b981;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .page-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
    }

    .page-title i {
        color: #10b981;
    }

    .page-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-export {
        background: white;
        border: 1px solid #e5e7eb;
        color: #10b981;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.2s;
    }

    .btn-export:hover {
        background: #f0fdf4;
        border-color: #10b981;
        color: #059669;
    }

    .btn-sync {
        background: #10b981;
        border: none;
        color: white;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.2s;
    }

    .btn-sync:hover {
        background: #059669;
        color: white;
    }

    .contador-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
    }

    .financial-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .summary-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        transition: transform 0.2s;
    }

    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .icon-green {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
    }

    .icon-orange {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
    }

    .icon-blue {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
    }

    .icon-purple {
        background: rgba(139, 92, 246, 0.1);
        color: #7c3aed;
    }

    .icon-red {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }

    .tax-alert {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-radius: 0.75rem;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .sunat-status {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .sunat-status.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .sunat-status.sent {
        background: #dcfce7;
        color: #166534;
    }

    .sunat-status.error {
        background: #fee2e2;
        color: #991b1b;
    }

    .chart-container {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }

    .chart-container h5 {
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .chart-wrapper {
        position: relative;
        height: 300px;
    }

    .quick-actions {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 0.5rem;
        text-decoration: none;
        color: #374151;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
        margin-bottom: 0.75rem;
        background: white;
    }

    .action-btn:hover {
        background: #f9fafb;
        border-color: #10b981;
        color: #059669;
        text-decoration: none;
        transform: translateX(5px);
    }

    .action-btn i {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .badge-role {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endpush

@section('sidebar-menu')
<div class="nav-section">MENÚ PRINCIPAL</div>
<a href="{{ route('dashboard.contador') }}" class="nav-link {{ request()->routeIs('dashboard.contador') ? 'active' : '' }}">
    <i class="fas fa-home"></i>
    <span>Dashboard</span>
</a>

<div class="nav-section">LIBROS CONTABLES</div>
<a href="{{ route('contabilidad.libros.diario.index') }}" class="nav-link {{ request()->routeIs('contabilidad.libros.diario*') ? 'active' : '' }}">
    <i class="fas fa-book"></i>
    <span>Libro Diario</span>
</a>
<a href="{{ route('contabilidad.libros.mayor.index') }}" class="nav-link {{ request()->routeIs('contabilidad.libros.mayor*') ? 'active' : '' }}">
    <i class="fas fa-book-open"></i>
    <span>Libro Mayor</span>
</a>
<a href="{{ route('contabilidad.libros.balance-comprobacion.index') }}" class="nav-link {{ request()->routeIs('contabilidad.libros.balance-comprobacion*') ? 'active' : '' }}">
    <i class="fas fa-balance-scale"></i>
    <span>Balance de Comprobación</span>
</a>

<div class="nav-section">ESTADOS FINANCIEROS</div>
<a href="{{ route('contabilidad.estados-financieros.balance') }}" class="nav-link {{ request()->routeIs('contabilidad.estados-financieros.balance') ? 'active' : '' }}">
    <i class="fas fa-file-invoice"></i>
    <span>Balance General</span>
</a>
<a href="{{ route('contabilidad.estados-financieros.resultados') }}" class="nav-link {{ request()->routeIs('contabilidad.estados-financieros.resultados') ? 'active' : '' }}">
    <i class="fas fa-chart-line"></i>
    <span>Estado de Resultados</span>
</a>
<a href="{{ route('contabilidad.estados-financieros.flujo-caja') }}" class="nav-link {{ request()->routeIs('contabilidad.estados-financieros.flujo-caja') ? 'active' : '' }}">
    <i class="fas fa-money-bill-wave"></i>
    <span>Flujo de Caja</span>
</a>

<div class="nav-section">REGISTROS</div>
<a href="{{ route('contabilidad.registros.compras') }}" class="nav-link {{ request()->routeIs('contabilidad.registros.compras') ? 'active' : '' }}">
    <i class="fas fa-shopping-cart"></i>
    <span>Registro de Compras</span>
</a>
<a href="{{ route('contabilidad.registros.ventas') }}" class="nav-link {{ request()->routeIs('contabilidad.registros.ventas') ? 'active' : '' }}">
    <i class="fas fa-cash-register"></i>
    <span>Registro de Ventas</span>
</a>
<a href="{{ route('contabilidad.registros.bancos') }}" class="nav-link {{ request()->routeIs('contabilidad.registros.bancos') ? 'active' : '' }}">
    <i class="fas fa-university"></i>
    <span>Bancos</span>
</a>
<a href="{{ route('contabilidad.registros.caja') }}" class="nav-link {{ request()->routeIs('contabilidad.registros.caja') ? 'active' : '' }}">
    <i class="fas fa-wallet"></i>
    <span>Caja</span>
</a>

<div class="nav-section">LIBROS AUXILIARES</div>
<a href="{{ route('contabilidad.auxiliares.clientes') }}" class="nav-link {{ request()->routeIs('contabilidad.auxiliares.clientes') ? 'active' : '' }}">
    <i class="fas fa-users"></i>
    <span>Clientes</span>
</a>
<a href="{{ route('contabilidad.auxiliares.proveedores') }}" class="nav-link {{ request()->routeIs('contabilidad.auxiliares.proveedores') ? 'active' : '' }}">
    <i class="fas fa-truck"></i>
    <span>Proveedores</span>
</a>
<a href="{{ route('contabilidad.auxiliares.honorarios') }}" class="nav-link {{ request()->routeIs('contabilidad.auxiliares.honorarios') ? 'active' : '' }}">
    <i class="fas fa-file-signature"></i>
    <span>Honorarios</span>
</a>

<div class="nav-section">REPORTES SUNAT</div>
<a href="{{ route('reportes.sunat.igv-mensual') }}" class="nav-link {{ request()->routeIs('reportes.sunat.igv-mensual') ? 'active' : '' }}">
    <i class="fas fa-file-alt"></i>
    <span>IGV Mensual</span>
</a>
<a href="{{ route('reportes.sunat.libros-electronicos') }}" class="nav-link {{ request()->routeIs('reportes.sunat.libros-electronicos') ? 'active' : '' }}">
    <i class="fas fa-laptop"></i>
    <span>Libros Electrónicos</span>
</a>
<a href="{{ route('reportes.sunat.plame') }}" class="nav-link {{ request()->routeIs('reportes.sunat.plame') ? 'active' : '' }}">
    <i class="fas fa-users-cog"></i>
    <span>PLAME</span>
</a>
@endsection

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title h3">
            <i class="fas fa-calculator"></i>
            @yield('page-title', 'Área Contable')
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Dashboard</a></li>
                @yield('breadcrumb')
                <li class="breadcrumb-item active">Contabilidad</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge-role">
            @hasrole('Administrador|Contador')
                {{ Auth::user()->tipousuario ?? 'Contador' }}
            @endhasrole
        </span>
        <div class="page-actions">
            @yield('page-actions')
            @hasrole('Administrador|Contador')
            <button class="btn btn-export" onclick="exportFinancialReport()">
                <i class="fas fa-download"></i>
                Exportar Reportes
            </button>
            <button class="btn btn-sync" onclick="syncWithSunat()">
                <i class="fas fa-sync"></i>
                Sincronizar con SUNAT
            </button>
            @endhasrole
        </div>
    </div>
</div>

@yield('contador-content')
@endsection

@push('scripts')
<script>
// Funciones específicas del contador
function exportFinancialReport() {
    showLoading();
    
    Swal.fire({
        title: 'Exportar Reporte',
        html: `
            <div class="mb-3">
                <label class="form-label">Tipo de Reporte</label>
                <select class="form-select" id="tipoReporte">
                    <option value="1">Estado de Resultados</option>
                    <option value="2">Balance General</option>
                    <option value="3">Flujo de Caja</option>
                    <option value="4">Reporte Completo</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Formato</label>
                <select class="form-select" id="formatoReporte">
                    <option value="excel">Excel (XLSX)</option>
                    <option value="pdf">PDF</option>
                    <option value="csv">CSV</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-download me-2"></i>Exportar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            return {
                tipo: document.getElementById('tipoReporte').value,
                formato: document.getElementById('formatoReporte').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const params = new URLSearchParams({
                tipo: result.value.tipo,
                formato: result.value.formato
            });
            window.open(`/api/reportes/exportar?${params.toString()}`, '_blank');
            
            Swal.fire({
                icon: 'success',
                title: 'Reporte generado',
                text: 'El archivo se está descargando...',
                timer: 2000,
                showConfirmButton: false
            });
        }
        hideLoading();
    });
}

function syncWithSunat() {
    Swal.fire({
        title: 'Sincronización con SUNAT',
        text: 'Esto puede tomar varios minutos. ¿Deseas continuar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, sincronizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();
            
            fetch('/api/sunat/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Sincronización completada exitosamente',
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error en la sincronización: ' + (data.message || 'Error desconocido'),
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión durante la sincronización',
                    confirmButtonColor: '#ef4444'
                });
            });
        }
    });
}

// Auto-refresh de métricas cada 5 minutos
function refreshFinancialMetrics() {
    fetch('/api/financial/metrics', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Métricas actualizadas:', data.data);
            // Actualizar UI con nuevas métricas si es necesario
        }
    })
    .catch(error => console.error('Error al actualizar métricas:', error));
}

// Ejecutar refresh cada 5 minutos
setInterval(refreshFinancialMetrics, 300000);
</script>
@endpush