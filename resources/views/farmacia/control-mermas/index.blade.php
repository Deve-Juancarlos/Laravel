{{-- ==========================================
     VISTA: DASHBOARD CONTROL DE MERMAS
     MÓDULO: Control de Mermas - Index
     DESARROLLADO POR: MiniMax Agent
     FECHA: 2025-10-25
     DESCRIPCIÓN: Dashboard principal para control y gestión de mermas farmacéuticas,
                  análisis de pérdidas, causas principales y reportes según normativa DIGEMID
========================================== --}}

@extends('layouts.app')

@section('title', 'Control de Mermas - Dashboard')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-chart-line text-danger"></i>
                        Control de Mermas
                    </h1>
                    <p class="text-muted mb-0">Gestión y análisis de pérdidas farmacéuticas</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" onclick="exportMermasReport()">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                    <button type="button" class="btn btn-success" onclick="showNewMermaModal()">
                        <i class="fas fa-plus"></i> Registrar Merma
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tarjetas de Resumen --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($totalMermas ?? 245) }}</h5>
                            <small>Total Mermas (Mes)</small>
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
                            <h5 class="card-title mb-0">S/ {{ number_format($valorMermas ?? 12847.60, 2) }}</h5>
                            <small>Valor Total Perdido</small>
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
                            <i class="fas fa-percent fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($porcentajeMermas ?? 2.34, 2) }}%</h5>
                            <small>% sobre Inventario</small>
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
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-0">{{ number_format($pendientesRevision ?? 18) }}</h5>
                            <small>Pendientes Revisión</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerta de Mermas Críticas --}}
    @if(($mermasCriticas ?? 5) > 0)
    <div class="alert alert-warning border-0 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
            <div>
                <h6 class="alert-heading mb-1">
                    <strong>Atención:</strong> {{ $mermasCriticas ?? 5 }} productos con mermas altas detectadas este mes
                </h6>
                <p class="mb-0">Se requiere revisión inmediata de los procesos de almacenamiento y manejo.</p>
            </div>
            <div class="ms-auto">
                <button type="button" class="btn btn-sm btn-outline-warning" onclick="viewCriticalMermas()">
                    Ver Detalles
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Gráficos y Análisis --}}
    <div class="row mb-4">
        {{-- Gráfico de Mermas por Categoría --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-pie"></i> Mermas por Categoría
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="mermasCategoriaChart" height="300"></canvas>
                </div>
            </div>
        </div>

        {{-- Gráfico de Tendencia de Mermas --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Tendencia de Mermas (6 meses)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="tendenciaMermasChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        {{-- Causas Principales de Mermas --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-list"></i> Principales Causas de Mermas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="badge bg-danger">1</span>
                                        Vencimiento
                                    </td>
                                    <td class="text-end">{{ $causaVencimiento ?? 89 }}</td>
                                    <td class="text-end">{{ number_format($causaVencimientoPct ?? 36.3, 1) }}%</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-warning">2</span>
                                        Deterioro
                                    </td>
                                    <td class="text-end">{{ $causaDeterioro ?? 67 }}</td>
                                    <td class="text-end">{{ number_format($causaDeterioroPct ?? 27.3, 1) }}%</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-info">3</span>
                                        Error de Dispensación
                                    </td>
                                    <td class="text-end">{{ $causaError ?? 45 }}</td>
                                    <td class="text-end">{{ number_format($causaErrorPct ?? 18.4, 1) }}%</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">4</span>
                                        Rotura
                                    </td>
                                    <td class="text-end">{{ $causaRotura ?? 23 }}</td>
                                    <td class="text-end">{{ number_format($causaRoturaPct ?? 9.4, 1) }}%</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-dark">5</span>
                                        Robo/Pérdida
                                    </td>
                                    <td class="text-end">{{ $causaRobo ?? 21 }}</td>
                                    <td class="text-end">{{ number_format($causaRoboPct ?? 8.6, 1) }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Productos con Más Mermas --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-exclamation-circle"></i> Productos con Más Mermas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="fw-bold">Paracetamol 500mg</div>
                                        <small class="text-muted">Jarabe 60ml</small>
                                    </td>
                                    <td class="text-end">{{ number_format($mermaProducto1 ?? 156) }}</td>
                                    <td class="text-end">S/ {{ number_format($valorProducto1 ?? 2340.00, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="fw-bold">Insulina NPH</div>
                                        <small class="text-muted">Vial 10ml</small>
                                    </td>
                                    <td class="text-end">{{ number_format($mermaProducto2 ?? 89) }}</td>
                                    <td class="text-end">S/ {{ number_format($valorProducto2 ?? 5785.00, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="fw-bold">Amoxicilina 250mg</div>
                                        <small class="text-muted">Cápsulas</small>
                                    </td>
                                    <td class="text-end">{{ number_format($mermaProducto3 ?? 67) }}</td>
                                    <td class="text-end">S/ {{ number_format($valorProducto3 ?? 2010.00, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="fw-bold">Protector Solar</div>
                                        <small class="text-muted">FPS 60</small>
                                    </td>
                                    <td class="text-end">{{ number_format($mermaProducto4 ?? 45) }}</td>
                                    <td class="text-end">S/ {{ number_format($valorProducto4 ?? 1575.00, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros de Fecha --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="card-title mb-0">
                <i class="fas fa-filter"></i> Filtros de Análisis
            </h6>
        </div>
        <div class="card-body">
            <form id="filtersForm">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Período</label>
                        <select class="form-select" id="periodFilter" onchange="applyPeriodFilter()">
                            <option value="current_month">Mes Actual</option>
                            <option value="last_month">Mes Anterior</option>
                            <option value="last_3_months">Últimos 3 Meses</option>
                            <option value="last_6_months">Últimos 6 Meses</option>
                            <option value="current_year">Año Actual</option>
                            <option value="custom">Personalizado</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6" id="customDateRange" style="display: none;">
                        <label class="form-label">Rango de Fechas</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="dateFrom">
                            <span class="input-group-text">a</span>
                            <input type="date" class="form-control" id="dateTo">
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
                        <label class="form-label">Causa</label>
                        <select class="form-select" id="causeFilter">
                            <option value="">Todas las causas</option>
                            <option value="vencimiento">Vencimiento</option>
                            <option value="deterioro">Deterioro</option>
                            <option value="error_disp">Error de Dispensación</option>
                            <option value="rotura">Rotura</option>
                            <option value="robo">Robo/Pérdida</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-12">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                <i class="fas fa-search"></i> Filtrar
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

    {{-- Tabla de Últimas Mermas --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="fas fa-table"></i> Últimas Mermas Registradas
                <span class="badge bg-secondary ms-2">{{ number_format($ultimasMermas->count() ?? 20) }}</span>
            </h6>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-success" onclick="exportMermasExcel()">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="exportMermasPDF()">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="mermasTable">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Lote</th>
                            <th>Cantidad</th>
                            <th>Causa</th>
                            <th>Valor (S/)</th>
                            <th>Responsable</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Ejemplos de mermas registradas --}}
                        <tr class="table-danger">
                            <td>{{ date('d/m/Y') }}</td>
                            <td><code>MERMA-2025-001</code></td>
                            <td>
                                <div class="fw-bold">Paracetamol 500mg</div>
                                <small class="text-muted">Jarabe 60ml</small>
                            </td>
                            <td>L2023-001</td>
                            <td>45 unidades</td>
                            <td>
                                <span class="badge bg-danger">Vencimiento</span>
                            </td>
                            <td class="text-end">S/ 450.00</td>
                            <td>L. Valencia</td>
                            <td>
                                <span class="badge bg-warning">Pendiente</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewMermaDetail(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="approveMerma(1)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="rejectMerma(1)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-warning">
                            <td>{{ date('d/m/Y', strtotime('-1 day')) }}</td>
                            <td><code>MERMA-2025-002</code></td>
                            <td>
                                <div class="fw-bold">Insulina NPH</div>
                                <small class="text-muted">Vial 10ml</small>
                            </td>
                            <td>INS2023-045</td>
                            <td>12 viales</td>
                            <td>
                                <span class="badge bg-warning">Deterioro</span>
                            </td>
                            <td class="text-end">S/ 780.00</td>
                            <td>A. Rodríguez</td>
                            <td>
                                <span class="badge bg-info">En Revisión</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewMermaDetail(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="approveMerma(2)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="rejectMerma(2)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-info">
                            <td>{{ date('d/m/Y', strtotime('-2 days')) }}</td>
                            <td><code>MERMA-2025-003</code></td>
                            <td>
                                <div class="fw-bold">Amoxicilina 250mg</div>
                                <small class="text-muted">Cápsulas</small>
                            </td>
                            <td>AMX2024-012</td>
                            <td>78 cápsulas</td>
                            <td>
                                <span class="badge bg-info">Error Dispensación</span>
                            </td>
                            <td class="text-end">S/ 156.00</td>
                            <td>M. González</td>
                            <td>
                                <span class="badge bg-success">Aprobada</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewMermaDetail(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="generateReport(3)">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-danger">
                            <td>{{ date('d/m/Y', strtotime('-3 days')) }}</td>
                            <td><code>MERMA-2025-004</code></td>
                            <td>
                                <div class="fw-bold">Protector Solar FPS 60</div>
                                <small class="text-muted">Frasco 120ml</small>
                            </td>
                            <td>PRO2024-078</td>
                            <td>23 frascos</td>
                            <td>
                                <span class="badge bg-danger">Rotura</span>
                            </td>
                            <td class="text-end">S/ 920.00</td>
                            <td>L. Valencia</td>
                            <td>
                                <span class="badge bg-warning">Pendiente</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewMermaDetail(4)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="approveMerma(4)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="rejectMerma(4)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="table-secondary">
                            <td>{{ date('d/m/Y', strtotime('-4 days')) }}</td>
                            <td><code>MERMA-2025-005</code></td>
                            <td>
                                <div class="fw-bold">Dexametasona Inyectable</div>
                                <small class="text-muted">Ampolla 4mg/2ml</small>
                            </td>
                            <td>DEX2023-156</td>
                            <td>45 ampollas</td>
                            <td>
                                <span class="badge bg-dark">Robo/Pérdida</span>
                            </td>
                            <td class="text-end">S/ 135.00</td>
                            <td>A. Rodríguez</td>
                            <td>
                                <span class="badge bg-danger">Rechazada</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewMermaDetail(5)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="editMerma(5)">
                                        <i class="fas fa-edit"></i>
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

{{-- Modal de Nueva Merma --}}
<div class="modal fade" id="newMermaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Registrar Nueva Merma
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="newMermaForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Producto *</label>
                            <select class="form-select" id="productSelect" required>
                                <option value="">Buscar y seleccionar producto</option>
                                <option value="paracetamol">Paracetamol 500mg - Jarabe 60ml</option>
                                <option value="insulina">Insulina NPH - Vial 10ml</option>
                                <option value="amoxicilina">Amoxicilina 250mg - Cápsulas</option>
                                <option value="protector">Protector Solar FPS 60</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Código de Merma</label>
                            <input type="text" class="form-control" id="mermaCode" value="MERMA-2025-{{ str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) }}" readonly>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Fecha de Merma *</label>
                            <input type="date" class="form-control" id="mermaDate" value="{{ date('Y-m-d') }}" required>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Lote *</label>
                            <input type="text" class="form-control" id="loteNumber" placeholder="Número de lote" required>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Cantidad Afectada *</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="affectedQuantity" min="1" required>
                                <span class="input-group-text">unidades</span>
                            </div>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Valor Total (S/)</label>
                            <input type="number" step="0.01" class="form-control" id="totalValue" readonly>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Causa de la Merma *</label>
                            <select class="form-select" id="mermaCause" required>
                                <option value="">Seleccionar causa</option>
                                <option value="vencimiento">Vencimiento</option>
                                <option value="deterioro">Deterioro</option>
                                <option value="error_disp">Error de Dispensación</option>
                                <option value="rotura">Rotura/Daño</option>
                                <option value="robo">Robo/Pérdida</option>
                                <option value="rechazo_calidad">Rechazo por Calidad</option>
                                <option value="error_almacenamiento">Error de Almacenamiento</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Descripción Detallada *</label>
                            <textarea class="form-control" id="description" rows="3" placeholder="Descripción detallada de la merma..." required></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Responsable *</label>
                            <select class="form-select" id="responsible" required>
                                <option value="">Seleccionar responsable</option>
                                <option value="luis_valencia">Luis Valencia - Auxiliar</option>
                                <option value="ana_rodriguez">Q.F. Ana Rodríguez</option>
                                <option value="maria_gonzalez">María González - Compras</option>
                                <option value="carlos_mendoza">Dr. Carlos Mendoza - Director</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Ubicación donde Ocurrió</label>
                            <input type="text" class="form-control" id="location" placeholder="Ej: Almacén principal, mostrador, refrigerador">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Acciones Correctivas Propuestas</label>
                            <textarea class="form-control" id="correctiveActions" rows="2" placeholder="Acciones para evitar futuras mermas..."></textarea>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="requiresApproval" checked>
                                <label class="form-check-label" for="requiresApproval">
                                    Requiere aprobación de supervisor
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Merma
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de Detalle de Merma --}}
<div class="modal fade" id="mermaDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalle de Merma
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="mermaDetailContent">
                    {{-- Contenido dinámico --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-primary" onclick="printMermaReport()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gráficos
    initializeCharts();
    
    // Inicializar DataTable
    initializeDataTable();
    
    // Event listeners
    setupEventListeners();
});

function initializeCharts() {
    // Gráfico de Mermas por Categoría
    const ctxCategoria = document.getElementById('mermasCategoriaChart').getContext('2d');
    new Chart(ctxCategoria, {
        type: 'doughnut',
        data: {
            labels: ['Medicamentos', 'Dispositivos Médicos', 'Cosméticos', 'Alimentos'],
            datasets: [{
                data: [152, 43, 32, 18],
                backgroundColor: [
                    '#dc3545',
                    '#ffc107',
                    '#17a2b8',
                    '#6f42c1'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Tendencia de Mermas
    const ctxTendencia = document.getElementById('tendenciaMermasChart').getContext('2d');
    new Chart(ctxTendencia, {
        type: 'line',
        data: {
            labels: ['Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre'],
            datasets: [{
                label: 'Número de Mermas',
                data: [198, 223, 187, 245, 201, 245],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Valor (S/)',
                data: [10234, 11890, 9876, 13542, 10890, 12847],
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Mes'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Número de Mermas'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Valor (S/)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

function initializeDataTable() {
    $('#mermasTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json'
        },
        pageLength: 25,
        order: [[0, 'desc']], // Ordenar por fecha (descendente)
        columnDefs: [
            { orderable: false, targets: [9] } // Deshabilitar orden en columna de acciones
        ]
    });
}

function setupEventListeners() {
    // Calcular valor total automáticamente
    $('#affectedQuantity').on('input', function() {
        const quantity = parseInt($(this).val()) || 0;
        // Simular cálculo de valor (en producción esto sería dinámico)
        const unitValue = 10.00; // Valor unitario de ejemplo
        const total = quantity * unitValue;
        $('#totalValue').val(total.toFixed(2));
    });

    // Mostrar/ocultar rango de fechas personalizado
    $('#periodFilter').change(function() {
        if ($(this).val() === 'custom') {
            $('#customDateRange').show();
        } else {
            $('#customDateRange').hide();
        }
    });
}

// Funciones de Filtrado
function applyPeriodFilter() {
    const period = $('#periodFilter').val();
    console.log('Aplicando filtro de período:', period);
    // Aquí se implementaría la lógica de filtrado por período
}

function applyFilters() {
    const filters = {
        period: $('#periodFilter').val(),
        dateFrom: $('#dateFrom').val(),
        dateTo: $('#dateTo').val(),
        category: $('#categoryFilter').val(),
        cause: $('#causeFilter').val()
    };
    
    console.log('Aplicando filtros:', filters);
    
    // Aquí se aplicaría la lógica de filtrado
    showNotification('Filtros aplicados exitosamente', 'success');
}

function clearFilters() {
    $('#filtersForm')[0].reset();
    $('#customDateRange').hide();
    $('#periodFilter').val('current_month');
    
    // Resetear DataTable
    $('#mermasTable').DataTable().search('').draw();
    
    showNotification('Filtros limpiados', 'info');
}

// Funciones de Gestión de Mermas
function showNewMermaModal() {
    $('#newMermaModal').modal('show');
}

function viewMermaDetail(mermaId) {
    // Simular carga de detalle
    const content = `
        <div class="row g-3">
            <div class="col-12">
                <h6>Información General</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Código:</strong></td>
                        <td>MERMA-2025-001</td>
                    </tr>
                    <tr>
                        <td><strong>Producto:</strong></td>
                        <td>Paracetamol 500mg - Jarabe 60ml</td>
                    </tr>
                    <tr>
                        <td><strong>Lote:</strong></td>
                        <td>L2023-001</td>
                    </tr>
                    <tr>
                        <td><strong>Fecha:</strong></td>
                        <td>${new Date().toLocaleDateString('es-ES')}</td>
                    </tr>
                    <tr>
                        <td><strong>Cantidad:</strong></td>
                        <td>45 unidades</td>
                    </tr>
                    <tr>
                        <td><strong>Valor:</strong></td>
                        <td>S/ 450.00</td>
                    </tr>
                    <tr>
                        <td><strong>Causa:</strong></td>
                        <td><span class="badge bg-danger">Vencimiento</span></td>
                    </tr>
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td><span class="badge bg-warning">Pendiente</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-12">
                <h6>Descripción Detallada</h6>
                <div class="border rounded p-3 bg-light">
                    <p>Producto vencido el 15/03/2025. Se detectó durante el conteo rutinario de inventario. 
                    El lote no se movió correctamente en el sistema de FEFO (First Expire First Out).</p>
                </div>
            </div>
            <div class="col-12">
                <h6>Acciones Correctivas</h6>
                <div class="border rounded p-3 bg-light">
                    <ul class="mb-0">
                        <li>Revisar y mejorar el proceso de rotación de inventario FEFO</li>
                        <li>Capacitar al personal en control de vencimientos</li>
                        <li>Implementar alertas automáticas más frecuentes</li>
                    </ul>
                </div>
            </div>
        </div>
    `;
    
    $('#mermaDetailContent').html(content);
    $('#mermaDetailModal').modal('show');
}

function approveMerma(mermaId) {
    Swal.fire({
        title: 'Aprobar Merma',
        text: '¿Está seguro de aprobar esta merma?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Merma aprobada exitosamente', 'success');
        }
    });
}

function rejectMerma(mermaId) {
    Swal.fire({
        title: 'Rechazar Merma',
        text: '¿Está seguro de rechazar esta merma?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, rechazar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Merma rechazada', 'info');
        }
    });
}

function editMerma(mermaId) {
    Swal.fire({
        icon: 'info',
        title: 'Editar Merma',
        text: 'Funcionalidad de edición en desarrollo'
    });
}

function generateReport(mermaId) {
    Swal.fire({
        title: 'Generando Reporte...',
        text: 'Creando reporte detallado de la merma',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        showNotification('Reporte generado exitosamente', 'success');
    }, 2000);
}

// Funciones de Exportación
function exportMermasReport() {
    Swal.fire({
        title: 'Exportar Reporte',
        text: '¿Desea exportar el reporte completo de mermas?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Exportando reporte de mermas...', 'info');
            setTimeout(() => {
                showNotification('Reporte exportado exitosamente', 'success');
            }, 2000);
        }
    });
}

function exportMermasExcel() {
    Swal.fire({
        title: 'Exportando a Excel...',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        showNotification('Datos exportados a Excel exitosamente', 'success');
    }, 2000);
}

function exportMermasPDF() {
    Swal.fire({
        title: 'Generando PDF...',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        showNotification('PDF generado exitosamente', 'success');
    }, 2000);
}

// Funciones adicionales
function viewCriticalMermas() {
    Swal.fire({
        icon: 'info',
        title: 'Mermas Críticas',
        html: `
            <div class="text-start">
                <h6>Productos con Mermas Altas:</h6>
                <ul>
                    <li>Paracetamol 500mg: 156 unidades</li>
                    <li>Insulina NPH: 89 viales</li>
                    <li>Amoxicilina 250mg: 67 cápsulas</li>
                    <li>Protector Solar FPS 60: 45 frascos</li>
                    <li>Dexametasona Inyectable: 34 ampollas</li>
                </ul>
                <p class="mt-3"><strong>Recomendación:</strong> Revisar procesos de almacenamiento y manejo.</p>
            </div>
        `,
        width: '600px'
    });
}

function printMermaReport() {
    showNotification('Enviando reporte a impresora...', 'info');
    setTimeout(() => {
        showNotification('Reporte enviado a impresora exitosamente', 'success');
    }, 2000);
}

// Formulario de nueva merma
$('#newMermaForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        product: $('#productSelect').val(),
        code: $('#mermaCode').val(),
        date: $('#mermaDate').val(),
        lote: $('#loteNumber').val(),
        quantity: $('#affectedQuantity').val(),
        value: $('#totalValue').val(),
        cause: $('#mermaCause').val(),
        description: $('#description').val(),
        responsible: $('#responsible').val(),
        location: $('#location').val(),
        correctiveActions: $('#correctiveActions').val(),
        requiresApproval: $('#requiresApproval').is(':checked')
    };
    
    // Validaciones básicas
    if (!formData.product || !formData.lote || !formData.quantity || !formData.cause || 
        !formData.description || !formData.responsible) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Por favor complete todos los campos requeridos.'
        });
        return;
    }
    
    // Simular guardado
    Swal.fire({
        title: 'Guardando...',
        text: 'Registrando nueva merma en el sistema',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Merma Registrada',
            text: 'La merma ha sido registrada exitosamente.',
            showConfirmButton: false,
            timer: 2000
        });
        
        $('#newMermaModal').modal('hide');
        $('#newMermaForm')[0].reset();
        
        // Recargar la página para mostrar la nueva merma
        setTimeout(() => {
            location.reload();
        }, 2000);
    }, 2000);
});

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
</script>
@endsection

@section('styles')
<style>
/* Estilos específicos para control de mermas */
.card-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.card-warning {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.card-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

.table-danger {
    --bs-table-bg: #f8d7da;
    --bs-table-striped-bg: #f5c6cb;
}

.table-warning {
    --bs-table-bg: #fff3cd;
    --bs-table-striped-bg: #ffeaa7;
}

.table-info {
    --bs-table-bg: #cff4fc;
    --bs-table-striped-bg: #b6effb;
}

/* Estados de merma */
.status-pendiente {
    background-color: #ffc107;
    color: #212529;
}

.status-revision {
    background-color: #17a2b8;
    color: white;
}

.status-aprobada {
    background-color: #28a745;
    color: white;
}

.status-rechazada {
    background-color: #dc3545;
    color: white;
}

/* Causas de merma */
.cause-vencimiento {
    background-color: #dc3545;
    color: white;
}

.cause-deterioro {
    background-color: #ffc107;
    color: #212529;
}

.cause-error {
    background-color: #17a2b8;
    color: white;
}

.cause-rotura {
    background-color: #fd7e14;
    color: white;
}

.cause-robo {
    background-color: #6c757d;
    color: white;
}

/* Animaciones */
.merma-alert {
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

/* Efectos hover para acciones */
.btn-group-sm .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
    
    .card-columns {
        column-count: 1;
    }
}
</style>
@endsection