@extends('layouts.app')

@section('title', 'Estado de Cuenta - ' . ($cliente->Razon ?? 'Cliente'))
@section('page-title', 'Estado de Cuenta del Cliente')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('contador.clientes.index') }}" class="text-decoration-none">
            <i class="fas fa-users"></i> Clientes
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('contador.clientes.show', $cliente->Codclie ?? 1) }}" class="text-decoration-none">
            {{ $cliente->Razon ?? 'Cliente' }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Estado de Cuenta</li>
@endsection

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        body { background: white; }
        .card { box-shadow: none; border: 1px solid #dee2e6; }
    }
    
    .client-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .balance-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        height: 100%;
        transition: transform 0.3s ease;
    }
    
    .balance-card:hover {
        transform: translateY(-5px);
    }
    
    .balance-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-bottom: 1rem;
    }
    
    .balance-label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .balance-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2c3e50;
    }
    
    .movement-row {
        border-bottom: 1px solid #e9ecef;
        transition: background 0.2s ease;
    }
    
    .movement-row:hover {
        background: #f8f9fa;
    }
    
    .movement-row:last-child {
        border-bottom: none;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 0.5rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: white;
        border: 3px solid;
    }
    
    .filter-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
</style>
@endpush

@section('content')

<!-- Información del Cliente -->
<div class="client-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3 class="mb-2">{{ $cliente->Razon ?? 'Hospital Central S.A.' }}</h3>
            <div class="d-flex gap-3 mb-2">
                <span><i class="fas fa-id-card me-2"></i>{{ $cliente->Documento ?? '20123456789' }}</span>
                <span><i class="fas fa-phone me-2"></i>{{ $cliente->Telefono1 ?? '+51 999 888 777' }}</span>
                <span><i class="fas fa-envelope me-2"></i>{{ $cliente->Email ?? 'contacto@hospital.com' }}</span>
            </div>
            <small class="opacity-75">
                <i class="fas fa-map-marker-alt me-2"></i>{{ $cliente->Direccion ?? 'Av. Principal 123, Lima' }}
            </small>
        </div>
        <div class="text-end no-print">
            <button class="btn btn-light me-2" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Imprimir
            </button>
            <button class="btn btn-light" onclick="exportarPDF()">
                <i class="fas fa-file-pdf me-1"></i>Exportar PDF
            </button>
        </div>
    </div>
</div>

<!-- Resumen Financiero -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="balance-card">
            <div class="balance-icon bg-danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="balance-label">Saldo Vencido</div>
            <div class="balance-value text-danger">S/ 23,456.78</div>
            <small class="text-muted">Más de 30 días</small>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="balance-card">
            <div class="balance-icon bg-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="balance-label">Por Vencer</div>
            <div class="balance-value text-warning">S/ 22,222.12</div>
            <small class="text-muted">Próximos 30 días</small>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="balance-card">
            <div class="balance-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="balance-label">Total Pagado</div>
            <div class="balance-value text-success">S/ 188,888.90</div>
            <small class="text-muted">Últimos 6 meses</small>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="balance-card">
            <div class="balance-icon bg-primary">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="balance-label">Total Facturado</div>
            <div class="balance-value text-primary">S/ 234,567.80</div>
            <small class="text-muted">Últimos 6 meses</small>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="filter-section no-print">
    <form id="filtrosForm" method="GET">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Fecha Desde</label>
                <input type="date" class="form-control" name="fecha_desde" 
                       value="{{ request('fecha_desde', date('Y-m-d', strtotime('-6 months'))) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Fecha Hasta</label>
                <input type="date" class="form-control" name="fecha_hasta" 
                       value="{{ request('fecha_hasta', date('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Tipo</label>
                <select class="form-select" name="tipo_documento">
                    <option value="">Todos</option>
                    <option value="factura">Facturas</option>
                    <option value="boleta">Boletas</option>
                    <option value="pago">Pagos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Estado</label>
                <select class="form-select" name="estado">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="pagado">Pagado</option>
                    <option value="vencido">Vencido</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filtrar
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Detalle de Movimientos -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-0">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-list-alt text-primary me-2"></i>Detalle de Movimientos
            </h6>
            <div class="no-print">
                <button class="btn btn-sm btn-outline-success" onclick="exportarExcel()">
                    <i class="fas fa-file-excel me-1"></i>Excel
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Documento</th>
                        <th>Descripción</th>
                        <th>Tipo</th>
                        <th>Vencimiento</th>
                        <th class="text-end">Debe</th>
                        <th class="text-end">Haber</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-center">Días</th>
                        <th>Estado</th>
                        <th class="text-center no-print">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Factura Reciente -->
                    <tr class="movement-row">
                        <td>25/01/2024</td>
                        <td><strong class="text-primary">F001-0001234</strong></td>
                        <td>Medicamentos varios</td>
                        <td><span class="badge bg-info">Factura</span></td>
                        <td>24/02/2024</td>
                        <td class="text-end fw-bold">S/ 5,678.90</td>
                        <td class="text-end">-</td>
                        <td class="text-end fw-bold text-danger">S/ 5,678.90</td>
                        <td class="text-center"><span class="badge bg-warning">1</span></td>
                        <td>
                            <span class="status-badge bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-clock"></i>Por Vencer
                            </span>
                        </td>
                        <td class="text-center no-print">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-success" title="Registrar Pago">
                                    <i class="fas fa-money-bill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Factura Vencida -->
                    <tr class="movement-row">
                        <td>22/01/2024</td>
                        <td><strong class="text-primary">F001-0001233</strong></td>
                        <td>Dispositivos médicos</td>
                        <td><span class="badge bg-info">Factura</span></td>
                        <td>21/02/2024</td>
                        <td class="text-end fw-bold">S/ 8,945.67</td>
                        <td class="text-end">-</td>
                        <td class="text-end fw-bold text-danger">S/ 8,945.67</td>
                        <td class="text-center"><span class="badge bg-danger">-4</span></td>
                        <td>
                            <span class="status-badge bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-exclamation-circle"></i>Vencida
                            </span>
                        </td>
                        <td class="text-center no-print">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-success" title="Registrar Pago">
                                    <i class="fas fa-money-bill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Pago Realizado -->
                    <tr class="movement-row bg-success bg-opacity-10">
                        <td>15/01/2024</td>
                        <td><strong class="text-success">P001-0005678</strong></td>
                        <td>Pago a cuenta - Transferencia</td>
                        <td><span class="badge bg-success">Pago</span></td>
                        <td>-</td>
                        <td class="text-end">-</td>
                        <td class="text-end fw-bold text-success">S/ 10,000.00</td>
                        <td class="text-end fw-bold">S/ 27,303.32</td>
                        <td class="text-center">-</td>
                        <td>
                            <span class="status-badge bg-success bg-opacity-10 text-success">
                                <i class="fas fa-check"></i>Aplicado
                            </span>
                        </td>
                        <td class="text-center no-print">
                            <button class="btn btn-sm btn-outline-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    
                    <!-- Más Facturas -->
                    <tr class="movement-row">
                        <td>10/01/2024</td>
                        <td><strong class="text-primary">F001-0001231</strong></td>
                        <td>Medicamentos especializados</td>
                        <td><span class="badge bg-info">Factura</span></td>
                        <td>09/02/2024</td>
                        <td class="text-end fw-bold">S/ 15,678.90</td>
                        <td class="text-end">-</td>
                        <td class="text-end fw-bold text-danger">S/ 15,678.90</td>
                        <td class="text-center"><span class="badge bg-danger">-16</span></td>
                        <td>
                            <span class="status-badge bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-exclamation-circle"></i>Vencida
                            </span>
                        </td>
                        <td class="text-center no-print">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-success" title="Registrar Pago">
                                    <i class="fas fa-money-bill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <th colspan="5">TOTALES</th>
                        <th class="text-end">S/ 45,903.69</th>
                        <th class="text-end">S/ 60,000.00</th>
                        <th class="text-end text-danger">S/ 45,678.90</th>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Información Bancaria -->
<div class="card shadow-sm mt-4 no-print">
    <div class="card-header bg-white border-0">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-university text-primary me-2"></i>Información para Pagos
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                    <div>
                        <strong>Banco Continental</strong><br>
                        <small class="text-muted">Cuenta Corriente Soles</small>
                    </div>
                </div>
                <div class="ms-5 ps-2">
                    <p class="mb-1"><strong>Cuenta:</strong> 0011-0123-45-0000123456</p>
                    <p class="mb-1"><strong>CCI:</strong> 011 123 0000123456 45</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                    <div>
                        <strong>SEIMCORP S.A.C.</strong><br>
                        <small class="text-muted">RUC: 20123456789</small>
                    </div>
                </div>
                <div class="ms-5 ps-2">
                    <p class="mb-1"><i class="fas fa-envelope me-2 text-primary"></i>cuentas@seimcorp.com</p>
                    <p class="mb-1"><i class="fas fa-phone me-2 text-primary"></i>+51 999 888 777</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function exportarPDF() {
    Swal.fire({
        title: 'Generando PDF...',
        text: 'Creando estado de cuenta',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    }).then(() => {
        Swal.fire({
            icon: 'success',
            title: '¡PDF Generado!',
            text: 'El estado de cuenta ha sido descargado.',
            showConfirmButton: false,
            timer: 1500
        });
    });
}

function exportarExcel() {
    Swal.fire({
        title: 'Generando Excel...',
        text: 'Exportando movimientos',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    }).then(() => {
        Swal.fire({
            icon: 'success',
            title: '¡Excel Generado!',
            text: 'El archivo ha sido descargado.',
            showConfirmButton: false,
            timer: 1500
        });
    });
}

// Animación de entrada
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.movement-row');
    rows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        setTimeout(() => {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, index * 50);
    });
});
</script>
@endpush