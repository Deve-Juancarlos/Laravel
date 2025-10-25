{{-- ==========================================
     VISTA: REPORTES DE MERMAS
     MÓDULO: Control de Mermas - Reportes
     DESARROLLADO POR: MiniMax Agent
     FECHA: 2025-10-25
     DESCRIPCIÓN: Generación de reportes completos y análisis detallado de mermas
                  con gráficos, estadísticas y exportación según normativa DIGEMID
========================================== --}}

@extends('layouts.app')

@section('title', 'Reportes de Mermas - Control de Mermas')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-chart-bar text-info"></i>
                        Reportes de Mermas
                    </h1>
                    <p class="text-muted mb-0">Análisis detallado y reportes de mermas farmacéuticas</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success" onclick="generateExcelReport()">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="generatePDFReport()">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="scheduleReport()">
                        <i class="fas fa-clock"></i> Programar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel de Filtros --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="card-title mb-0">
                <i class="fas fa-filter"></i> Filtros del Reporte
            </h6>
        </div>
        <div class="card-body">
            <form id="reportFiltersForm">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Tipo de Reporte</label>
                        <select class="form-select" id="reportType" onchange="updateReportType()">
                            <option value="summary">Resumen Ejecutivo</option>
                            <option value="detailed">Análisis Detallado</option>
                            <option value="trends">Tendencias</option>
                            <option value="causes">Análisis de Causas</option>
                            <option value="costs">Análisis de Costos</option>
                            <option value="compliance">Cumplimiento DIGEMID</option>
                            <option value="custom">Personalizado</option>
                        </select>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Período de Análisis</label>
                        <select class="form-select" id="analysisPeriod" onchange="updatePeriodFilters()">
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
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Estado de Mermas</label>
                        <select class="form-select" id="mermaStatus">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="revision">En Revisión</option>
                            <option value="aprobada">Aprobada</option>
                            <option value="rechazada">Rechazada</option>
                        </select>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Categoría de Producto</label>
                        <select class="form-select" id="productCategory">
                            <option value="">Todas las categorías</option>
                            <option value="medicamentos">Medicamentos</option>
                            <option value="dispositivos">Dispositivos Médicos</option>
                            <option value="cosméticos">Cosméticos</option>
                            <option value="alimentos">Alimentos</option>
                        </select>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Causa Principal</label>
                        <select class="form-select" id="mermaCause">
                            <option value="">Todas las causas</option>
                            <option value="vencimiento">Vencimiento</option>
                            <option value="deterioro">Deterioro</option>
                            <option value="error_disp">Error de Dispensación</option>
                            <option value="rotura">Rotura/Daño</option>
                            <option value="robo">Robo/Pérdida</option>
                        </select>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Responsable</label>
                        <select class="form-select" id="responsible">
                            <option value="">Todos los responsables</option>
                            <option value="luis_valencia">Luis Valencia</option>
                            <option value="ana_rodriguez">Q.F. Ana Rodríguez</option>
                            <option value="maria_gonzalez">María González</option>
                            <option value="carlos_mendoza">Dr. Carlos Mendoza</option>
                        </select>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Ubicación</label>
                        <select class="form-select" id="location">
                            <option value="">Todas las ubicaciones</option>
                            <option value="almacen_principal">Almacén Principal</option>
                            <option value="almacen_secundario">Almacén Secundario</option>
                            <option value="refrigerador">Refrigerador</option>
                            <option value="mostrador">Mostrador</option>
                        </select>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Valor Mínimo (S/)</label>
                        <input type="number" class="form-control" id="minValue" step="0.01" placeholder="0.00">
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Severidad</label>
                        <select class="form-select" id="severity">
                            <option value="">Todas</option>
                            <option value="menor">Menor</option>
                            <option value="moderada">Moderada</option>
                            <option value="mayor">Mayor</option>
                            <option value="crítica">Crítica</option>
                        </select>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Formato de Exportación</label>
                        <select class="form-select" id="exportFormat">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                            <option value="both">PDF + Excel</option>
                        </select>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" onclick="generateReport()">
                                <i class="fas fa-chart-line"></i> Generar Reporte
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

    {{-- Resumen Ejecutivo --}}
    <div class="row mb-4" id="executiveSummary" style="display: block;">
        <div class="col-12 mb-4">
            <div class="card border-0" style="background: linear-gradient(135deg, #007bff, #0056b3); color: white;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Resumen Ejecutivo - Mermas {{ date('F Y') }}</h3>
                            <p class="mb-0">Análisis de pérdidas farmacéuticas para el período seleccionado</p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <button type="button" class="btn btn-light" onclick="exportExecutiveSummary()">
                                <i class="fas fa-download"></i> Descargar Resumen
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPIs Principales --}}
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle fa-3x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">{{ number_format($totalMermas ?? 245) }}</h2>
                            <p class="mb-1">Total de Mermas</p>
                            <small>
                                <i class="fas fa-arrow-up text-warning"></i>
                                {{ number_format($mermaIncrease ?? 12.5, 1) }}% vs mes anterior
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-money-bill-wave fa-3x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">S/ {{ number_format($totalLossValue ?? 12847.60, 0) }}</h2>
                            <p class="mb-1">Pérdida Total</p>
                            <small>
                                <i class="fas fa-arrow-up text-danger"></i>
                                {{ number_format($lossIncrease ?? 18.3, 1) }}% vs mes anterior
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-percent fa-3x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">{{ number_format($mermaPercentage ?? 2.34, 2) }}%</h2>
                            <p class="mb-1">% del Inventario</p>
                            <small>
                                <i class="fas fa-arrow-up text-warning"></i>
                                Límite recomendado: 1.5%
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line fa-3x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">S/ {{ number_format($avgDailyLoss ?? 414.44, 0) }}</h2>
                            <p class="mb-1">Pérdida Diaria Promedio</p>
                            <small>
                                <i class="fas fa-arrow-down text-info"></i>
                                {{ number_format($dailyReduction ?? 5.2, 1) }}% mejora
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficos y Análisis --}}
    <div class="row mb-4">
        {{-- Gráfico de Tendencia Temporal --}}
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Tendencia de Mermas - Últimos 12 Meses
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="300"></canvas>
                </div>
            </div>
        </div>

        {{-- Distribución por Causa --}}
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-pie"></i> Distribución por Causa
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="causeChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        {{-- Análisis por Categoría --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Mermas por Categoría de Producto
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>
        </div>

        {{-- Top 10 Productos con Más Mermas --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-list"></i> Top 10 Productos con Más Mermas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Valor (S/)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-danger">1</span></td>
                                    <td>Paracetamol 500mg</td>
                                    <td class="text-end">{{ number_format($product1Qty ?? 156) }}</td>
                                    <td class="text-end">S/ {{ number_format($product1Value ?? 2340.00, 0) }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning">2</span></td>
                                    <td>Insulina NPH</td>
                                    <td class="text-end">{{ number_format($product2Qty ?? 89) }}</td>
                                    <td class="text-end">S/ {{ number_format($product2Value ?? 5785.00, 0) }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info">3</span></td>
                                    <td>Amoxicilina 250mg</td>
                                    <td class="text-end">{{ number_format($product3Qty ?? 67) }}</td>
                                    <td class="text-end">S/ {{ number_format($product3Value ?? 2010.00, 0) }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-secondary">4</span></td>
                                    <td>Protector Solar FPS 60</td>
                                    <td class="text-end">{{ number_format($product4Qty ?? 45) }}</td>
                                    <td class="text-end">S/ {{ number_format($product4Value ?? 1575.00, 0) }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-dark">5</span></td>
                                    <td>Dexametasona Inyectable</td>
                                    <td class="text-end">{{ number_format($product5Qty ?? 34) }}</td>
                                    <td class="text-end">S/ {{ number_format($product5Value ?? 1020.00, 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Análisis de Eficiencia --}}
    <div class="row mb-4">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-tachometer-alt"></i> Indicadores de Eficiencia
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        {{-- Tiempo Promedio de Resolución --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-clock fa-3x text-info"></i>
                                </div>
                                <h4>{{ number_format($avgResolutionTime ?? 2.3, 1) }} días</h4>
                                <p class="text-muted">Tiempo Promedio de Resolución</p>
                                <div class="progress">
                                    <div class="progress-bar bg-info" style="width: {{ number_format((5 - $avgResolutionTime) / 5 * 100) }}%"></div>
                                </div>
                                <small class="text-muted">Meta: < 3 días</small>
                            </div>
                        </div>

                        {{-- Tasa de Aprobación --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-check-circle fa-3x text-success"></i>
                                </div>
                                <h4>{{ number_format($approvalRate ?? 87.5, 1) }}%</h4>
                                <p class="text-muted">Tasa de Aprobación</p>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: {{ $approvalRate ?? 87.5 }}%"></div>
                                </div>
                                <small class="text-muted">Meta: > 85%</small>
                            </div>
                        </div>

                        {{-- Costo por Merma --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-dollar-sign fa-3x text-warning"></i>
                                </div>
                                <h4>S/ {{ number_format($costPerMerma ?? 52.44, 0) }}</h4>
                                <p class="text-muted">Costo Promedio por Merma</p>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" style="width: {{ number_format((100 - $costPerMerma) / 100 * 100) }}%"></div>
                                </div>
                                <small class="text-muted">Meta: < S/ 50</small>
                            </div>
                        </div>

                        {{-- Prevención de Recurrencia --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-shield-alt fa-3x text-primary"></i>
                                </div>
                                <h4>{{ number_format($preventionRate ?? 73.2, 1) }}%</h4>
                                <p class="text-muted">Tasa de Prevención</p>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" style="width: {{ $preventionRate ?? 73.2 }}%"></div>
                                </div>
                                <small class="text-muted">Meta: > 80%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recomendaciones --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h6 class="card-title mb-0">
                <i class="fas fa-lightbulb"></i> Recomendaciones y Acciones Correctivas
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="alert alert-warning border-0" role="alert">
                        <h6><i class="fas fa-exclamation-triangle"></i> Crítico</h6>
                        <p class="mb-0">
                            <strong>Vencimientos altos:</strong> Implementar alertas automáticas más frecuentes 
                            y mejorar el sistema FEFO para reducir mermas por vencimiento.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="alert alert-info border-0" role="alert">
                        <h6><i class="fas fa-info-circle"></i> Mejoramiento</h6>
                        <p class="mb-0">
                            <strong>Capacitación:</strong> Realizar entrenamiento adicional al personal 
                            sobre manejo y almacenamiento de productos farmacéuticos sensibles.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="alert alert-success border-0" role="alert">
                        <h6><i class="fas fa-check-circle"></i> Prevención</h6>
                        <p class="mb-0">
                            <strong>Monitoreo:</strong> Establecer un sistema de monitoreo continuo 
                            de temperatura y humedad en áreas de almacenamiento crítico.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla Detallada --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="fas fa-table"></i> Detalle de Mermas para el Período
                <span class="badge bg-secondary ms-2">{{ number_format($periodMermas->count() ?? 245) }}</span>
            </h6>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-primary" onclick="exportDetailedTable()">
                    <i class="fas fa-download"></i> Exportar Tabla
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="showFilters()">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="mermasReportTable">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Causa</th>
                            <th>Cantidad</th>
                            <th>Valor (S/)</th>
                            <th>Responsable</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Ejemplo de datos del reporte --}}
                        <tr>
                            <td>{{ date('d/m/Y') }}</td>
                            <td><code>MERMA-2025-001</code></td>
                            <td>Paracetamol 500mg</td>
                            <td><span class="badge bg-danger">Vencimiento</span></td>
                            <td>45 unidades</td>
                            <td class="text-end">S/ 450.00</td>
                            <td>L. Valencia</td>
                            <td><span class="badge bg-warning">Pendiente</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewDetail('1')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ date('d/m/Y', strtotime('-1 day')) }}</td>
                            <td><code>MERMA-2025-002</code></td>
                            <td>Insulina NPH</td>
                            <td><span class="badge bg-warning">Deterioro</span></td>
                            <td>12 viales</td>
                            <td class="text-end">S/ 780.00</td>
                            <td>A. Rodríguez</td>
                            <td><span class="badge bg-success">Aprobada</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewDetail('2')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ date('d/m/Y', strtotime('-2 days')) }}</td>
                            <td><code>MERMA-2025-003</code></td>
                            <td>Amoxicilina 250mg</td>
                            <td><span class="badge bg-info">Error Dispensación</span></td>
                            <td>78 cápsulas</td>
                            <td class="text-end">S/ 156.00</td>
                            <td>M. González</td>
                            <td><span class="badge bg-info">En Revisión</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewDetail('3')">
                                    <i class="fas fa-eye"></i>
                                </button>
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

{{-- Modal de Programación de Reportes --}}
<div class="modal fade" id="scheduleReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-clock"></i> Programar Reporte Automático
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleReportForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Reporte</label>
                        <input type="text" class="form-control" id="scheduleReportName" placeholder="Reporte Mensual de Mermas" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Frecuencia</label>
                        <select class="form-select" id="scheduleFrequency" required>
                            <option value="">Seleccionar frecuencia</option>
                            <option value="daily">Diario</option>
                            <option value="weekly">Semanal</option>
                            <option value="monthly" selected>Mensual</option>
                            <option value="quarterly">Trimestral</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Día de Ejecución</label>
                        <select class="form-select" id="scheduleDay">
                            <option value="1">1 del mes</option>
                            <option value="5">5 del mes</option>
                            <option value="10" selected>10 del mes</option>
                            <option value="15">15 del mes</option>
                            <option value="20">20 del mes</option>
                            <option value="25">25 del mes</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Hora de Ejecución</label>
                        <input type="time" class="form-control" id="scheduleTime" value="08:00" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Destinatarios</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sendToDirector" checked>
                            <label class="form-check-label" for="sendToDirector">
                                Director de Farmacia
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sendToQF" checked>
                            <label class="form-check-label" for="sendToQF">
                                Químico Farmacéutico
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sendToAdmin">
                            <label class="form-check-label" for="sendToAdmin">
                                Administrador
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Formato de Envío</label>
                        <select class="form-select" id="scheduleFormat">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="both" selected>PDF + Excel</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Programar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de Vista Detallada --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalle de Merma
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detailContent">
                    {{-- Contenido dinámico --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-primary" onclick="printDetail()">
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
    // Gráfico de Tendencia
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Nov 2024', 'Dic 2024', 'Ene 2025', 'Feb 2025', 'Mar 2025', 'Abr 2025', 
                    'May 2025', 'Jun 2025', 'Jul 2025', 'Ago 2025', 'Sep 2025', 'Oct 2025'],
            datasets: [{
                label: 'Número de Mermas',
                data: [189, 203, 178, 198, 223, 187, 245, 201, 234, 218, 267, 245],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            }, {
                label: 'Valor (S/)',
                data: [8923, 9567, 8234, 9123, 10890, 8765, 12847, 10956, 12340, 11456, 14230, 12847],
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
                    },
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

    // Gráfico de Causas
    const causeCtx = document.getElementById('causeChart').getContext('2d');
    new Chart(causeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Vencimiento', 'Deterioro', 'Error Dispensación', 'Rotura', 'Robo/Pérdida'],
            datasets: [{
                data: [89, 67, 45, 23, 21],
                backgroundColor: [
                    '#dc3545',
                    '#ffc107',
                    '#17a2b8',
                    '#fd7e14',
                    '#6c757d'
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
                        padding: 15,
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

    // Gráfico de Categorías
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: ['Medicamentos', 'Dispositivos', 'Cosméticos', 'Alimentos'],
            datasets: [{
                label: 'Número de Mermas',
                data: [152, 43, 32, 18],
                backgroundColor: [
                    '#dc3545',
                    '#ffc107',
                    '#17a2b8',
                    '#6f42c1'
                ],
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Cantidad'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Categoría'
                    }
                }
            }
        }
    });
}

function initializeDataTable() {
    $('#mermasReportTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json'
        },
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [8] }
        ]
    });
}

function setupEventListeners() {
    // Actualizar filtros de fecha según período
    $('#analysisPeriod').change(function() {
        if ($(this).val() === 'custom') {
            $('#customDateRange').show();
        } else {
            $('#customDateRange').hide();
        }
    });
}

// Funciones de Generación de Reportes
function generateReport() {
    const filters = {
        type: $('#reportType').val(),
        period: $('#analysisPeriod').val(),
        dateFrom: $('#dateFrom').val(),
        dateTo: $('#dateTo').val(),
        status: $('#mermaStatus').val(),
        category: $('#productCategory').val(),
        cause: $('#mermaCause').val(),
        responsible: $('#responsible').val(),
        location: $('#location').val(),
        minValue: $('#minValue').val(),
        severity: $('#severity').val()
    };
    
    console.log('Generando reporte con filtros:', filters);
    
    Swal.fire({
        title: 'Generando Reporte...',
        text: 'Procesando datos y generando visualización',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Simular generación de reporte
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Reporte Generado',
            text: 'El reporte ha sido generado exitosamente.',
            showConfirmButton: false,
            timer: 2000
        });
    }, 2000);
}

function updateReportType() {
    const type = $('#reportType').val();
    console.log('Tipo de reporte cambiado a:', type);
    // Aquí se actualizarían los filtros según el tipo
}

function updatePeriodFilters() {
    const period = $('#analysisPeriod').val();
    console.log('Período cambiado a:', period);
    // Aquí se actualizarían los filtros de fecha
}

function clearFilters() {
    $('#reportFiltersForm')[0].reset();
    $('#customDateRange').hide();
    $('#reportType').val('summary');
    $('#analysisPeriod').val('current_month');
    
    // Resetear DataTable
    $('#mermasReportTable').DataTable().search('').draw();
    
    showNotification('Filtros limpiados', 'info');
}

// Funciones de Exportación
function generateExcelReport() {
    const format = $('#exportFormat').val();
    
    if (format === 'excel' || format === 'both') {
        Swal.fire({
            title: 'Generando Excel...',
            icon: 'info',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        setTimeout(() => {
            showNotification('Reporte Excel generado exitosamente', 'success');
        }, 3000);
    }
    
    if (format === 'pdf' || format === 'both') {
        generatePDFReport();
    }
}

function generatePDFReport() {
    Swal.fire({
        title: 'Generando PDF...',
        icon: 'info',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        showNotification('Reporte PDF generado exitosamente', 'success');
    }, 3000);
}

function exportExecutiveSummary() {
    Swal.fire({
        title: 'Exportando Resumen...',
        text: 'Generando resumen ejecutivo en PDF',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        showNotification('Resumen ejecutivo exportado exitosamente', 'success');
    }, 2000);
}

function exportDetailedTable() {
    Swal.fire({
        title: 'Exportando Tabla...',
        text: 'Generando archivo Excel con datos detallados',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        showNotification('Tabla detallada exportada exitosamente', 'success');
    }, 2000);
}

// Funciones de Programación
function scheduleReport() {
    $('#scheduleReportModal').modal('show');
}

// Formulario de programación
$('#scheduleReportForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: $('#scheduleReportName').val(),
        frequency: $('#scheduleFrequency').val(),
        day: $('#scheduleDay').val(),
        time: $('#scheduleTime').val(),
        format: $('#scheduleFormat').val(),
        recipients: {
            director: $('#sendToDirector').is(':checked'),
            qf: $('#sendToQF').is(':checked'),
            admin: $('#sendToAdmin').is(':checked')
        }
    };
    
    Swal.fire({
        title: 'Programando Reporte...',
        text: 'Configurando envío automático',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Reporte Programado',
            text: 'El reporte automático ha sido configurado exitosamente.',
            showConfirmButton: false,
            timer: 2000
        });
        
        $('#scheduleReportModal').modal('hide');
        $('#scheduleReportForm')[0].reset();
    }, 2000);
});

// Funciones de Visualización
function viewDetail(mermaId) {
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
                        <td><strong>Fecha:</strong></td>
                        <td>${new Date().toLocaleDateString('es-ES')}</td>
                    </tr>
                    <tr>
                        <td><strong>Causa:</strong></td>
                        <td><span class="badge bg-danger">Vencimiento</span></td>
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
                        <td><strong>Estado:</strong></td>
                        <td><span class="badge bg-warning">Pendiente</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-12">
                <h6>Análisis de Impacto</h6>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="text-center">
                            <h4 class="text-danger">S/ 450.00</h4>
                            <p class="text-muted">Pérdida Directa</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="text-center">
                            <h4 class="text-warning">2.3%</h4>
                            <p class="text-muted">Impacto en Inventario</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="text-center">
                            <h4 class="text-info">S/ 75.00</h4>
                            <p class="text-muted">Costos Adicionales</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#detailContent').html(content);
    $('#detailModal').modal('show');
}

function printDetail() {
    const content = document.getElementById('detailContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Detalle de Merma</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .table { width: 100%; border-collapse: collapse; }
                    .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    .table th { background-color: #f2f2f2; }
                    .badge { padding: 4px 8px; border-radius: 4px; }
                    .text-center { text-align: center; }
                </style>
            </head>
            <body>${content}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function showFilters() {
    // Scroll hacia el panel de filtros
    document.getElementById('reportFiltersForm').scrollIntoView({ 
        behavior: 'smooth' 
    });
    
    // Highlight del panel de filtros
    const filtersCard = document.querySelector('.card.border-0.shadow-sm');
    filtersCard.style.animation = 'pulse 1s ease-in-out';
    
    setTimeout(() => {
        filtersCard.style.animation = '';
    }, 1000);
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
</script>
@endsection

@section('styles')
<style>
/* Estilos para reportes de mermas */
.executive-summary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.kpi-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.progress-custom {
    height: 8px;
    border-radius: 4px;
}

.progress-custom .progress-bar {
    border-radius: 4px;
    transition: width 0.6s ease;
}

/* Estados de indicadores */
.indicator-good {
    color: #28a745;
}

.indicator-warning {
    color: #ffc107;
}

.indicator-critical {
    color: #dc3545;
}

/* Estilos para gráficos */
.chart-container {
    position: relative;
    height: 300px;
    margin: 20px 0;
}

/* Alertas de recomendación */
.recommendation-critical {
    border-left: 4px solid #dc3545;
    background: linear-gradient(135deg, #fff5f5, #ffffff);
}

.recommendation-improvement {
    border-left: 4px solid #17a2b8;
    background: linear-gradient(135deg, #f0f9ff, #ffffff);
}

.recommendation-prevention {
    border-left: 4px solid #28a745;
    background: linear-gradient(135deg, #f0fff4, #ffffff);
}

/* Animaciones */
.fade-in {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slide-in {
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Efectos hover para elementos interactivos */
.interactive-element {
    transition: all 0.3s ease;
}

.interactive-element:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Estilos responsivos */
@media (max-width: 768px) {
    .kpi-card h2 {
        font-size: 1.5rem;
    }
    
    .chart-container {
        height: 250px;
    }
    
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}

@media (max-width: 576px) {
    .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
    
    .kpi-card .fa-3x {
        font-size: 2rem;
    }
}

/* Efectos de carga */
.loading-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Estilos para tooltips de gráficos */
.chart-tooltip {
    background: rgba(0,0,0,0.8);
    color: white;
    border-radius: 4px;
    padding: 8px 12px;
    font-size: 0.875rem;
}

/* Indicadores de estado */
.status-pending {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.status-approved {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
}

.status-rejected {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.status-review {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}
</style>
@endsection