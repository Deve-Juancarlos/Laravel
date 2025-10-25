{{-- ==========================================
     VISTA: GESTIÓN DE PRODUCTOS VENCIDOS
     MÓDULO: Control de Vencimientos - Vencidos
     DESARROLLADO POR: MiniMax Agent
     FECHA: 2025-10-25
     DESCRIPCIÓN: Gestión completa de productos vencidos con opciones de disposición,
                  devolución a proveedor, donación y destrucción según normativa DIGEMID
========================================== --}}

@extends('layouts.app')

@section('title', 'Productos Vencidos - Control de Vencimientos')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        Productos Vencidos
                    </h1>
                    <p class="text-muted mb-0">Gestión y disposición de productos farmacéuticos vencidos</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" onclick="exportExpiredProducts()">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="showDispositionModal()">
                        <i class="fas fa-trash"></i> Nueva Disposición
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tarjetas Resumen --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-times fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($expiredCount ?? 127) }}</h5>
                            <small>Total Vencidos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">S/ {{ number_format($totalValueExpired ?? 85430.50, 2) }}</h5>
                            <small>Valor en Riesgo</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-box-open fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($productsToReturn ?? 34) }}</h5>
                            <small>Para Devolución</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-trash fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($pendingDisposal ?? 18) }}</h5>
                            <small>Pendiente Disposición</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertas Importantes --}}
    @if(($criticalExpired ?? 8) > 0)
    <div class="alert alert-danger border-0 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
            <div>
                <h6 class="alert-heading mb-1">
                    <strong>Alerta Crítica:</strong> {{ $criticalExpired ?? 8 }} productos vencidos hace más de 90 días
                </h6>
                <p class="mb-0">Estos productos requieren disposición inmediata según normativa DIGEMID.</p>
            </div>
            <div class="ms-auto">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="showCriticalDisposalModal()">
                    Ver Detalles
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Filtros de Búsqueda --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="card-title mb-0">
                <i class="fas fa-filter"></i> Filtros de Búsqueda
            </h6>
        </div>
        <div class="card-body">
            <form id="filtersForm">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Producto</label>
                        <input type="text" class="form-control" id="productFilter" placeholder="Nombre o código del producto">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Fecha Vencimiento</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="dateFromFilter">
                            <span class="input-group-text">a</span>
                            <input type="date" class="form-control" id="dateToFilter">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" id="categoryFilter">
                            <option value="">Todas las categorías</option>
                            <option value="medicamentos">Medicamentos</option>
                            <option value="dispositivos">Dispositivos Médicos</option>
                            <option value="cosméticos">Cosméticos</option>
                            <option value="alimentos">Alimentos</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Estado</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="devolucion">Para Devolución</option>
                            <option value="donacion">Para Donación</option>
                            <option value="destruccion">Para Destrucción</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-12">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de Productos Vencidos --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="fas fa-table"></i> Productos Vencidos
                <span class="badge bg-secondary ms-2">{{ number_format($expiredProducts->count() ?? 127) }}</span>
            </h6>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-success" onclick="selectAllForReturn()">
                    <i class="fas fa-undo"></i> Seleccionar para Devolución
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="selectAllForDestruction()">
                    <i class="fas fa-trash"></i> Seleccionar para Destrucción
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="expiredProductsTable">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleAll()">
                            </th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Lote</th>
                            <th>Fecha Vencimiento</th>
                            <th>Días Vencido</th>
                            <th>Cantidad</th>
                            <th>Valor (S/)</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Ejemplo de productos vencidos --}}
                        <tr class="table-danger">
                            <td>
                                <input type="checkbox" class="form-check-input product-checkbox" value="1">
                            </td>
                            <td><code>MED001</code></td>
                            <td>
                                <div class="fw-bold">Paracetamol 500mg</div>
                                <small class="text-muted">Jarabe - 60ml</small>
                            </td>
                            <td>L2023-001</td>
                            <td>
                                <span class="badge bg-danger">15/03/2025</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">214 días</span>
                            </td>
                            <td>120 unidades</td>
                            <td class="text-end">S/ 240.00</td>
                            <td>
                                <span class="badge bg-warning">Pendiente</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewProductHistory(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="markForReturn(1)">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="markForDestruction(1)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-danger">
                            <td>
                                <input type="checkbox" class="form-check-input product-checkbox" value="2">
                            </td>
                            <td><code>DIS002</code></td>
                            <td>
                                <div class="fw-bold">Insulina NPH</div>
                                <small class="text-muted">Vial - 10ml</small>
                            </td>
                            <td>INS2023-045</td>
                            <td>
                                <span class="badge bg-danger">22/02/2025</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">237 días</span>
                            </td>
                            <td>45 viales</td>
                            <td class="text-end">S/ 2,925.00</td>
                            <td>
                                <span class="badge bg-success">Para Devolución</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewProductHistory(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="generateReturnNote(2)">
                                        <i class="fas fa-file-invoice"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="markForDestruction(2)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-danger">
                            <td>
                                <input type="checkbox" class="form-check-input product-checkbox" value="3">
                            </td>
                            <td><code>MED003</code></td>
                            <td>
                                <div class="fw-bold">Amoxicilina 250mg</div>
                                <small class="text-muted">Cápsulas - Blister x10</small>
                            </td>
                            <td>AMX2024-012</td>
                            <td>
                                <span class="badge bg-danger">08/01/2025</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">282 días</span>
                            </td>
                            <td>200 blisters</td>
                            <td class="text-end">S/ 1,800.00</td>
                            <td>
                                <span class="badge bg-info">Para Donación</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewProductHistory(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="processDonation(3)">
                                        <i class="fas fa-gift"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="markForDestruction(3)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-warning">
                            <td>
                                <input type="checkbox" class="form-check-input product-checkbox" value="4">
                            </td>
                            <td><code>COS004</code></td>
                            <td>
                                <div class="fw-bold">Protector Solar FPS 60</div>
                                <small class="text-muted">Frasco - 120ml</small>
                            </td>
                            <td>PRO2024-078</td>
                            <td>
                                <span class="badge bg-warning">30/09/2025</span>
                            </td>
                            <td>
                                <span class="badge bg-warning">25 días</span>
                            </td>
                            <td>85 frascos</td>
                            <td class="text-end">S/ 1,190.00</td>
                            <td>
                                <span class="badge bg-warning">Crítico</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewProductHistory(4)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="markForReturn(4)">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="markForDestruction(4)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-danger">
                            <td>
                                <input type="checkbox" class="form-check-input product-checkbox" value="5">
                            </td>
                            <td><code>MED005</code></td>
                            <td>
                                <div class="fw-bold">Dexametasona Inyectable</div>
                                <small class="text-muted">Ampolla - 4mg/2ml</small>
                            </td>
                            <td>DEX2023-156</td>
                            <td>
                                <span class="badge bg-danger">10/12/2024</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">319 días</span>
                            </td>
                            <td>300 ampollas</td>
                            <td class="text-end">S/ 450.00</td>
                            <td>
                                <span class="badge bg-dark">Para Destrucción</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewProductHistory(5)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="scheduleDestruction(5)">
                                        <i class="fas fa-calendar"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="viewDestructionReport(5)">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ==========================================
     MODALES
========================================== --}}

{{-- Modal de Nueva Disposición --}}
<div class="modal fade" id="dispositionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-trash"></i> Nueva Disposición de Productos Vencidos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="dispositionForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Tipo de Disposición *</label>
                            <select class="form-select" id="dispositionType" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="devolucion">Devolución a Proveedor</option>
                                <option value="donacion">Donación a Institución</option>
                                <option value="destruccion">Destrucción Controlada</option>
                                <option value="retiro_mercado">Retiro del Mercado</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Productos Seleccionados</label>
                            <div class="border rounded p-3 bg-light" id="selectedProducts">
                                <p class="text-muted mb-0">No hay productos seleccionados</p>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Institución/Proveedor *</label>
                            <input type="text" class="form-control" id="institution" placeholder="Nombre de la institución o proveedor" required>
                        </div>

                        <div class="col-12" id="supplierInfo" style="display: none;">
                            <label class="form-label">RUC del Proveedor</label>
                            <input type="text" class="form-control" id="supplierRuc" placeholder="12345678901">
                        </div>

                        <div class="col-12" id="institutionInfo" style="display: none;">
                            <label class="form-label">RUC de la Institución</label>
                            <input type="text" class="form-control" id="institutionRuc" placeholder="12345678901">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Motivo de Disposición *</label>
                            <textarea class="form-control" id="reason" rows="3" placeholder="Explicar el motivo de la disposición..." required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observations" rows="2" placeholder="Observaciones adicionales..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Fecha de Ejecución</label>
                            <input type="date" class="form-control" id="executionDate" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requireDigemidApproval" checked>
                                <label class="form-check-label" for="requireDigemidApproval">
                                    Requiere aprobación DIGEMID
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requireWitness" checked>
                                <label class="form-check-label" for="requireWitness">
                                    Requiere testigo para destrucción
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Disposición
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de Disposición Crítica --}}
<div class="modal fade" id="criticalDisposalModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Productos Vencidos Críticos (>90 días)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-info-circle"></i>
                    <strong>Atención:</strong> Los siguientes productos requieren disposición inmediata según normativa DIGEMID.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Lote</th>
                                <th>Vencimiento</th>
                                <th>Días</th>
                                <th>Cantidad</th>
                                <th>Valor</th>
                                <th>Acción Requerida</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Paracetamol 500mg</td>
                                <td>L2023-001</td>
                                <td>15/03/2025</td>
                                <td><span class="badge bg-danger">214 días</span></td>
                                <td>120 unid.</td>
                                <td>S/ 240.00</td>
                                <td><span class="badge bg-danger">Destrucción</span></td>
                            </tr>
                            <tr>
                                <td>Insulina NPH</td>
                                <td>INS2023-045</td>
                                <td>22/02/2025</td>
                                <td><span class="badge bg-danger">237 días</span></td>
                                <td>45 viales</td>
                                <td>S/ 2,925.00</td>
                                <td><span class="badge bg-warning">Verificar</span></td>
                            </tr>
                            <tr>
                                <td>Dexametasona Inyectable</td>
                                <td>DEX2023-156</td>
                                <td>10/12/2024</td>
                                <td><span class="badge bg-danger">319 días</span></td>
                                <td>300 amp.</td>
                                <td>S/ 450.00</td>
                                <td><span class="badge bg-danger">Destrucción</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="processCriticalDisposal()">
                    <i class="fas fa-trash"></i> Procesar Destrucción Inmediata
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Historial del Producto --}}
<div class="modal fade" id="productHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history"></i> Historial del Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="productHistoryContent">
                    {{-- Contenido dinámico --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable
    initializeDataTable();
    
    // Event listeners
    setupEventListeners();
});

function initializeDataTable() {
    $('#expiredProductsTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json'
        },
        pageLength: 25,
        order: [[5, 'desc']], // Ordenar por días vencido (descendente)
        columnDefs: [
            { orderable: false, targets: [0, 9] } // Deshabilitar orden en checkboxes y acciones
        ]
    });
}

function setupEventListeners() {
    // Mostrar campos adicionales según tipo de disposición
    $('#dispositionType').change(function() {
        const type = $(this).val();
        $('#supplierInfo, #institutionInfo').hide();
        
        if (type === 'devolucion') {
            $('#supplierInfo').show();
        } else if (type === 'donacion') {
            $('#institutionInfo').show();
        }
    });

    // Actualizar productos seleccionados en el modal
    $('#dispositionModal').on('show.bs.modal', function() {
        updateSelectedProducts();
    });
}

// Funciones principales
function toggleAll() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const selectAll = document.getElementById('selectAllCheckbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function applyFilters() {
    // Implementar lógica de filtros
    const product = $('#productFilter').val();
    const dateFrom = $('#dateFromFilter').val();
    const dateTo = $('#dateToFilter').val();
    const category = $('#categoryFilter').val();
    const status = $('#statusFilter').val();
    
    // Aquí se aplicaría la lógica de filtrado
    console.log('Aplicando filtros:', { product, dateFrom, dateTo, category, status });
}

function clearFilters() {
    $('#filtersForm')[0].reset();
    // Resetear DataTable
    $('#expiredProductsTable').DataTable().search('').draw();
}

function showDispositionModal() {
    const selectedProducts = getSelectedProducts();
    
    if (selectedProducts.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Seleccione productos',
            text: 'Debe seleccionar al menos un producto para crear una disposición.'
        });
        return;
    }
    
    $('#dispositionModal').modal('show');
}

function showCriticalDisposalModal() {
    $('#criticalDisposalModal').modal('show');
}

function updateSelectedProducts() {
    const selectedProducts = getSelectedProducts();
    const container = $('#selectedProducts');
    
    if (selectedProducts.length === 0) {
        container.html('<p class="text-muted mb-0">No hay productos seleccionados</p>');
        return;
    }
    
    let html = '<div class="row g-2">';
    selectedProducts.forEach(product => {
        html += `
            <div class="col-12">
                <div class="border rounded p-2 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${product.name}</strong><br>
                            <small class="text-muted">Lote: ${product.lote} - ${product.quantity} unidades</small>
                        </div>
                        <span class="badge bg-secondary">${product.value}</span>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    container.html(html);
}

function getSelectedProducts() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    const products = [];
    
    checkboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        products.push({
            id: checkbox.value,
            name: row.cells[2].textContent.trim(),
            lote: row.cells[3].textContent.trim(),
            quantity: row.cells[6].textContent.trim(),
            value: row.cells[7].textContent.trim()
        });
    });
    
    return products;
}

function selectAllForReturn() {
    // Seleccionar productos para devolución (donde el proveedor acepta devoluciones)
    const rows = document.querySelectorAll('#expiredProductsTable tbody tr');
    rows.forEach(row => {
        if (!row.classList.contains('table-warning')) { // Evitar productos críticos
            const checkbox = row.querySelector('.product-checkbox');
            if (checkbox) checkbox.checked = true;
        }
    });
}

function selectAllForDestruction() {
    // Seleccionar productos para destrucción (productos críticos o no retornables)
    const rows = document.querySelectorAll('#expiredProductsTable tbody tr.table-danger');
    rows.forEach(row => {
        const checkbox = row.querySelector('.product-checkbox');
        if (checkbox) checkbox.checked = true;
    });
}

// Funciones de acciones individuales
function viewProductHistory(productId) {
    // Simular carga de historial
    const content = `
        <div class="row g-3">
            <div class="col-12">
                <h6>Paracetamol 500mg - Lote L2023-001</h6>
                <p class="text-muted">Producto vencido el 15/03/2025</p>
            </div>
            <div class="col-12">
                <h6>Movimientos Recientes:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-plus text-success"></i> <strong>01/03/2025:</strong> Entrada - 120 unidades</li>
                    <li><i class="fas fa-minus text-danger"></i> <strong>15/03/2025:</strong> Vencimiento alcanzado</li>
                    <li><i class="fas fa-clock text-warning"></i> <strong>20/03/2025:</strong> Marca pendiente disposición</li>
                </ul>
            </div>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Información:</strong> Producto vencido hace 214 días. Requiere disposición inmediata.
                </div>
            </div>
        </div>
    `;
    
    $('#productHistoryContent').html(content);
    $('#productHistoryModal').modal('show');
}

function markForReturn(productId) {
    Swal.fire({
        title: 'Marcar para Devolución',
        text: '¿Desea marcar este producto para devolución al proveedor?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, marcar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Actualizar estado del producto
            showNotification('Producto marcado para devolución exitosamente', 'success');
        }
    });
}

function markForDestruction(productId) {
    Swal.fire({
        title: 'Marcar para Destrucción',
        text: '¿Está seguro de marcar este producto para destrucción? Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, marcar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Producto marcado para destrucción exitosamente', 'success');
        }
    });
}

function generateReturnNote(productId) {
    // Simular generación de nota de devolución
    Swal.fire({
        title: 'Nota de Devolución',
        text: 'Generando nota de devolución para el producto...',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        showNotification('Nota de devolución generada exitosamente', 'success');
    });
}

function processDonation(productId) {
    Swal.fire({
        title: 'Procesar Donación',
        html: `
            <form id="donationForm">
                <div class="mb-3">
                    <label class="form-label">Institución Beneficiaria</label>
                    <input type="text" class="form-control" placeholder="Nombre de la institución" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">RUC de la Institución</label>
                    <input type="text" class="form-control" placeholder="12345678901">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de Donación</label>
                    <select class="form-select">
                        <option value="hospital">Hospital Público</option>
                        <option value="clinica">Clínica Comunitaria</option>
                        <option value="ong">ONG de Salud</option>
                        <option value="escuela">Centro Educativo</option>
                    </select>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Procesar Donación',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const form = Swal.getPopup().querySelector('#donationForm');
            if (!form.checkValidity()) {
                Swal.showValidationMessage('Por favor complete todos los campos requeridos');
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Donación procesada exitosamente', 'success');
        }
    });
}

function scheduleDestruction(productId) {
    Swal.fire({
        title: 'Programar Destrucción',
        html: `
            <form id="destructionForm">
                <div class="mb-3">
                    <label class="form-label">Fecha de Destrucción</label>
                    <input type="date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Lugar de Destrucción</label>
                    <select class="form-select" required>
                        <option value="">Seleccionar lugar</option>
                        <option value="planta">Planta de Tratamiento</option>
                        <option value="incinerador">Incinerador Autorizado</option>
                        <option value="cementera">Cementera con Licencia</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Responsable de Destrucción</label>
                    <input type="text" class="form-control" placeholder="Nombre del responsable" required>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="requireDigemidWitness" checked>
                        <label class="form-check-label" for="requireDigemidWitness">
                            Requiere testigo DIGEMID
                        </label>
                    </div>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Programar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const form = Swal.getPopup().querySelector('#destructionForm');
            if (!form.checkValidity()) {
                Swal.showValidationMessage('Por favor complete todos los campos requeridos');
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Destrucción programada exitosamente', 'success');
        }
    });
}

function viewDestructionReport(productId) {
    // Simular visualización de reporte de destrucción
    window.open('/reports/destruction-report/' + productId, '_blank');
}

function exportExpiredProducts() {
    Swal.fire({
        title: 'Exportar Productos Vencidos',
        text: '¿Desea exportar la lista de productos vencidos?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Simular exportación
            showNotification('Exportando productos vencidos...', 'info');
            setTimeout(() => {
                showNotification('Productos vencidos exportados exitosamente', 'success');
            }, 2000);
        }
    });
}

function processCriticalDisposal() {
    Swal.fire({
        title: 'Procesar Disposición Crítica',
        text: 'Esta acción procesará la destrucción inmediata de productos vencidos críticos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Procesar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Disposición crítica procesada exitosamente', 'success');
            $('#criticalDisposalModal').modal('hide');
        }
    });
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
    
    Toast.fire({
        icon: type,
        title: message
    });
}

// Manejo del formulario de disposición
$('#dispositionForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        type: $('#dispositionType').val(),
        institution: $('#institution').val(),
        reason: $('#reason').val(),
        observations: $('#observations').val(),
        executionDate: $('#executionDate').val(),
        selectedProducts: getSelectedProducts()
    };
    
    // Validar que hay productos seleccionados
    if (formData.selectedProducts.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe seleccionar al menos un producto para la disposición.'
        });
        return;
    }
    
    // Simular guardado
    Swal.fire({
        title: 'Guardando...',
        text: 'Procesando la disposición de productos vencidos',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: 'Disposición de productos vencidos guardada exitosamente.',
            showConfirmButton: false,
            timer: 2000
        });
        
        $('#dispositionModal').modal('hide');
        
        // Limpiar formulario
        $('#dispositionForm')[0].reset();
        
        // Deseleccionar todos los checkboxes
        document.getElementById('selectAllCheckbox').checked = false;
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
        
    }, 2000);
});
</script>
@endsection

@section('styles')
<style>
/* Estilos específicos para productos vencidos */
.table-danger {
    --bs-table-bg: #f8d7da;
    --bs-table-striped-bg: #f5c6cb;
}

.table-warning {
    --bs-table-bg: #fff3cd;
    --bs-table-striped-bg: #ffeaa7;
}

.badge-critical {
    background-color: #dc3545 !important;
    color: white !important;
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

/* Animaciones para alertas */
.alert {
    animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Estilos para productos críticos */
.product-critical {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
}

/* Estilos para estados de disposición */
.disposition-pending {
    background-color: #ffc107;
    color: #212529;
}

.disposition-return {
    background-color: #28a745;
    color: white;
}

.disposition-donation {
    background-color: #17a2b8;
    color: white;
}

.disposition-destruction {
    background-color: #dc3545;
    color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.8rem;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
}
</style>
@endsection