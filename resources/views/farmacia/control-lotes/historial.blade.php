@extends('layouts.app')

@section('title', 'Historial de Lotes - SIFANO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-history text-info"></i>
                Historial de Lotes
            </h1>
            <p class="text-muted">Seguimiento completo del ciclo de vida de todos los lotes farmacéuticos</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportarHistorial()">
                <i class="fas fa-file-export"></i> Exportar
            </button>
            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalFiltroAvanzado">
                <i class="fas fa-filter"></i> Filtros Avanzados
            </button>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Lotes Históricos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalLotesHistorial">5,847</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Lotes Consumidos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="lotesConsumidos">4,623</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Lotes Vencidos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="lotesVencidosHistorial">324</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Valor Total Histórico
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="valorTotalHistorial">S/ 2,847,500</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Rápidos -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-search"></i> Búsqueda Rápida
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="busquedaRapida" class="form-label">Búsqueda Rápida</label>
                    <input type="text" class="form-control" id="busquedaRapida" 
                           placeholder="Lote, producto, proveedor...">
                </div>
                <div class="col-md-2">
                    <label for="filtroEstadoHistorial" class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstadoHistorial">
                        <option value="">Todos</option>
                        <option value="activo">Activo</option>
                        <option value="consumido">Consumido</option>
                        <option value="vencido">Vencido</option>
                        <option value="descontinuado">Descontinuado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filtroProveedorHistorial" class="form-label">Proveedor</label>
                    <select class="form-select" id="filtroProveedorHistorial">
                        <option value="">Todos</option>
                        <option value="pfizer">Pfizer S.A.</option>
                        <option value="novartis">Novartis Perú S.A.</option>
                        <option value="roche">Roche S.A.</option>
                        <option value="merck">Merck S.A.</option>
                        <option value="bayer">Bayer S.A.</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filtroFechaDesde" class="form-label">Desde</label>
                    <input type="date" class="form-control" id="filtroFechaDesde">
                </div>
                <div class="col-md-2">
                    <label for="filtroFechaHasta" class="form-label">Hasta</label>
                    <input type="date" class="form-control" id="filtroFechaHasta">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-primary w-100" onclick="aplicarFiltrosHistorial()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Historial -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Historial Completo de Lotes
            </h6>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary active" onclick="mostrarVistaHistorial('tabla')">
                    <i class="fas fa-list"></i> Tabla
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="mostrarVistaHistorial('timeline')">
                    <i class="fas fa-stream"></i> Timeline
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="mostrarVistaHistorial('analisis')">
                    <i class="fas fa-chart-line"></i> Análisis
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="vistaTablaHistorial" class="table-responsive">
                <table class="table table-bordered table-striped" id="tablaHistorialLotes" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Código Lote</th>
                            <th>Producto</th>
                            <th>Proveedor</th>
                            <th>Fecha Ingreso</th>
                            <th>Fecha Vencimiento</th>
                            <th>Cantidad Inicial</th>
                            <th>Stock Final</th>
                            <th>Estado Final</th>
                            <th>Rotación (días)</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-lote="1">
                            <td><strong>L230101A</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-pills text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Ibuprofeno 400mg</div>
                                        <small class="text-muted">Tableta • S/ 0.50</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-building text-info"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Pfizer S.A.</div>
                                        <small class="text-muted">RUC: 20100130204</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">01/01/2023</span>
                            </td>
                            <td>
                                <span class="badge bg-success">15/08/2025</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">2,000</span>
                            </td>
                            <td class="text-center">
                                <span class="text-success fw-bold">0</span>
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Consumido
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">365 días</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verDetallesLote(1)" title="Ver Detalles Completos">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="verTimelineLote(1)" title="Ver Timeline">
                                        <i class="fas fa-stream"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="analizarLote(1)" title="Análisis">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="reportarLote(1)" title="Generar Reporte">
                                        <i class="fas fa-file-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-lote="2">
                            <td><strong>L230206B</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-pills text-warning"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Paracetamol 500mg</div>
                                        <small class="text-muted">Tableta • S/ 0.30</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-building text-info"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Novartis Perú S.A.</div>
                                        <small class="text-muted">RUC: 20100234567</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">06/02/2023</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">20/06/2024</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">1,500</span>
                            </td>
                            <td class="text-center">
                                <span class="text-danger fw-bold">125</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">
                                    <i class="fas fa-ban"></i> Vencido
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning">487 días</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verDetallesLote(2)" title="Ver Detalles Completos">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="verTimelineLote(2)" title="Ver Timeline">
                                        <i class="fas fa-stream"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="analizarVencimiento(2)" title="Análisis de Vencimiento">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="generarAccionVencido(2)" title="Acción Correctiva">
                                        <i class="fas fa-tools"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-lote="3">
                            <td><strong>L230315C</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-syringe text-info"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Jeringa 5ml Estéril</div>
                                        <small class="text-muted">Unidad • S/ 0.80</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-building text-info"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Roche S.A.</div>
                                        <small class="text-muted">RUC: 20100345678</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">15/03/2023</span>
                            </td>
                            <td>
                                <span class="badge bg-success">31/12/2025</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">800</span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted fw-bold">245</span>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    <i class="fas fa-pause"></i> Parcialmente Consumido
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">En curso</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verDetallesLote(3)" title="Ver Detalles Completos">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="verTimelineLote(3)" title="Ver Timeline">
                                        <i class="fas fa-stream"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="analizarRotacion(3)" title="Análisis de Rotación">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="predecirVencimiento(3)" title="Predicción de Vencimiento">
                                        <i class="fas fa-crystal-ball"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Más lotes se cargarían dinámicamente -->
                    </tbody>
                </table>
            </div>

            <div id="vistaTimeline" class="d-none">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Vista Timeline:</strong> Esta vista muestra la cronología de eventos para cada lote en una interfaz de timeline interactiva.
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Lote: L230101A - Ibuprofeno 400mg</h6>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6>Ingreso al Inventario</h6>
                                            <p class="text-muted">01/01/2023 - 09:00 AM</p>
                                            <small>Lote ingresado por Pfizer S.A. Cantidad: 2,000 unidades</small>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6>Primer Movimiento</h6>
                                            <p class="text-muted">15/01/2023 - 02:30 PM</p>
                                            <small>Salida de 150 unidades para venta</small>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-warning"></div>
                                        <div class="timeline-content">
                                            <h6>Alerta de Stock Bajo</h6>
                                            <p class="text-muted">28/11/2023 - 10:15 AM</p>
                                            <small>Stock por debajo del mínimo (100 unidades)</small>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-danger"></div>
                                        <div class="timeline-content">
                                            <h6>Lote Agotado</h6>
                                            <p class="text-muted">31/12/2023 - 06:45 PM</p>
                                            <small>Últimas 50 unidades vendidas. Lote completamente consumido</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Lote: L230206B - Paracetamol 500mg</h6>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6>Ingreso al Inventario</h6>
                                            <p class="text-muted">06/02/2023 - 08:30 AM</p>
                                            <small>Lote ingresado por Novartis S.A. Cantidad: 1,500 unidades</small>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6>Movimientos Regulares</h6>
                                            <p class="text-muted">Feb-Sep 2023</p>
                                            <small>Rotación normal con ventas consistentes</small>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-warning"></div>
                                        <div class="timeline-content">
                                            <h6>Vencimiento Próximo</h6>
                                            <p class="text-muted">15/05/2024 - 09:00 AM</p>
                                            <small>Alerta: Lote vencerá en 35 días</small>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-danger"></div>
                                        <div class="timeline-content">
                                            <h6>Lote Vencido</h6>
                                            <p class="text-muted">20/06/2024 - 11:20 PM</p>
                                            <small>Lote vencido. 125 unidades no consumidas enviadas a descarte</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="vistaAnalisis" class="d-none">
                <div class="row">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-chart-line"></i> Análisis de Rotación por Año
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="graficoRotacion"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-chart-pie"></i> Distribución por Estados
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-pie">
                                    <canvas id="graficoEstadosHistorial"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-trophy"></i> Rankings y Métricas
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6 class="text-success">Mejor Rotación</h6>
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>L230101A - Ibuprofeno</span>
                                                <span class="badge bg-success rounded-pill">365 días</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>L230503D - Aspirina</span>
                                                <span class="badge bg-success rounded-pill">287 días</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>L230712E - Diclofenaco</span>
                                                <span class="badge bg-success rounded-pill">245 días</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-warning">Mayor Pérdida por Vencimiento</h6>
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>L230206B - Paracetamol</span>
                                                <span class="badge bg-warning rounded-pill">125 unidades</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>L230408F - Insulina</span>
                                                <span class="badge bg-warning rounded-pill">89 unidades</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>L230611G - Vitaminas</span>
                                                <span class="badge bg-warning rounded-pill">67 unidades</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-info">Proveedores Más Confiables</h6>
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>Pfizer S.A.</span>
                                                <span class="badge bg-info rounded-pill">98.5%</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>Novartis S.A.</span>
                                                <span class="badge bg-info rounded-pill">96.2%</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>Roche S.A.</span>
                                                <span class="badge bg-info rounded-pill">94.8%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Filtros Avanzados -->
<div class="modal fade" id="modalFiltroAvanzado" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-filter"></i> Filtros Avanzados
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Criterios Temporales</h6>
                        <div class="mb-3">
                            <label for="rangoFechas" class="form-label">Rango de Fechas</label>
                            <select class="form-select" id="rangoFechas">
                                <option value="">Seleccionar rango...</option>
                                <option value="hoy">Hoy</option>
                                <option value="semana">Esta Semana</option>
                                <option value="mes">Este Mes</option>
                                <option value="trimestre">Este Trimestre</option>
                                <option value="año">Este Año</option>
                                <option value="personalizado">Personalizado</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label for="fechaInicioPersonalizada" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fechaInicioPersonalizada">
                            </div>
                            <div class="col-6">
                                <label for="fechaFinPersonalizada" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fechaFinPersonalizada">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Criterios de Rotación</h6>
                        <div class="mb-3">
                            <label for="rotacionMinima" class="form-label">Rotación Mínima (días)</label>
                            <input type="number" class="form-control" id="rotacionMinima" placeholder="Ej: 30">
                        </div>
                        <div class="mb-3">
                            <label for="rotacionMaxima" class="form-label">Rotación Máxima (días)</label>
                            <input type="number" class="form-control" id="rotacionMaxima" placeholder="Ej: 365">
                        </div>
                        <div class="mb-3">
                            <label for="categoriaProducto" class="form-label">Categoría de Producto</label>
                            <select class="form-select" id="categoriaProducto">
                                <option value="">Todas las categorías</option>
                                <option value="medicamentos">Medicamentos</option>
                                <option value="dispositivos">Dispositivos Médicos</option>
                                <option value="suplementos">Suplementos</option>
                                <option value="cosméticos">Cosméticos</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Criterios de Valor</h6>
                        <div class="row">
                            <div class="col-6">
                                <label for="valorMinimo" class="form-label">Valor Mínimo (S/)</label>
                                <input type="number" class="form-control" id="valorMinimo" step="0.01">
                            </div>
                            <div class="col-6">
                                <label for="valorMaximo" class="form-label">Valor Máximo (S/)</label>
                                <input type="number" class="form-control" id="valorMaximo" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Criterios Especiales</h6>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="soloVencidos">
                                <label class="form-check-label" for="soloVencidos">
                                    Solo lotes vencidos
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="soloPerdidas">
                                <label class="form-check-label" for="soloPerdidas">
                                    Solo lotes con pérdidas
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="soloRotacionRapida">
                                <label class="form-check-label" for="soloRotacionRapida">
                                    Rotación rápida (< 30 días)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-outline-primary" onclick="limpiarFiltrosAvanzados()">
                    <i class="fas fa-undo"></i> Limpiar
                </button>
                <button type="button" class="btn btn-primary" onclick="aplicarFiltrosAvanzados()">
                    <i class="fas fa-search"></i> Aplicar Filtros
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalles de Lote -->
<div class="modal fade" id="modalDetallesLote" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalles Completos del Lote
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoDetallesLote">
                <!-- Se carga dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="generarReporteCompleto()">
                    <i class="fas fa-file-pdf"></i> Reporte Completo
                </button>
                <button type="button" class="btn btn-primary" onclick="exportarDetallesLote()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    padding-left: 1rem;
}

.timeline-content h6 {
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.timeline-content p {
    margin-bottom: 0.25rem;
    font-size: 0.8rem;
}

.timeline-content small {
    font-size: 0.75rem;
    color: #6c757d;
}
</style>
@endsection

@section('scripts')
<script>
// Variables globales
let tablaHistorialLotes;
let datosHistorialLotes = [];

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    inicializarTabla();
    inicializarGraficos();
    cargarDatos();
});

// Inicializar DataTable
function inicializarTabla() {
    tablaHistorialLotes = $('#tablaHistorialLotes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        pageLength: 25,
        responsive: true,
        order: [[3, 'desc']], // Ordenar por fecha de ingreso
        columnDefs: [
            {
                targets: [5, 6, 8],
                className: 'text-center'
            },
            {
                targets: [9],
                className: 'text-center',
                orderable: false
            }
        ]
    });

    // Búsqueda global
    $('#busquedaRapida').on('keyup', function() {
        tablaHistorialLotes.search($(this).val()).draw();
    });

    // Filtros
    $('#filtroEstadoHistorial, #filtroProveedorHistorial, #filtroFechaDesde, #filtroFechaHasta').on('change', aplicarFiltrosHistorial);
}

// Inicializar gráficos
function inicializarGraficos() {
    // Gráfico de rotación
    const ctx1 = document.getElementById('graficoRotacion').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [
                {
                    label: 'Días de Rotación Promedio',
                    data: [320, 285, 310, 295, 275, 290, 305, 280, 295, 315, 300, 285],
                    borderColor: 'rgb(78, 115, 223)',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Meta de Rotación',
                    data: [300, 300, 300, 300, 300, 300, 300, 300, 300, 300, 300, 300],
                    borderColor: 'rgb(28, 200, 138)',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    borderDash: [5, 5],
                    tension: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Días de Rotación'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + ' días';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de estados
    const ctx2 = document.getElementById('graficoEstadosHistorial').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Consumidos', 'Vencidos', 'Parciales', 'Descontinuados'],
            datasets: [{
                data: [4623, 324, 867, 33],
                backgroundColor: [
                    'rgb(28, 200, 138)',
                    'rgb(231, 74, 59)',
                    'rgb(78, 115, 223)',
                    'rgb(108, 117, 125)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Cargar datos iniciales
function cargarDatos() {
    // Simular carga de datos desde el servidor
    datosHistorialLotes = [
        {
            id: 1,
            codigo: 'L230101A',
            producto: 'Ibuprofeno 400mg',
            proveedor: 'Pfizer S.A.',
            fechaIngreso: '2023-01-01',
            fechaVencimiento: '2025-08-15',
            cantidadInicial: 2000,
            stockFinal: 0,
            precio: 0.50,
            estado: 'consumido',
            rotacion: 365
        },
        {
            id: 2,
            codigo: 'L230206B',
            producto: 'Paracetamol 500mg',
            proveedor: 'Novartis Perú S.A.',
            fechaIngreso: '2023-02-06',
            fechaVencimiento: '2024-06-20',
            cantidadInicial: 1500,
            stockFinal: 125,
            precio: 0.30,
            estado: 'vencido',
            rotacion: 487
        },
        {
            id: 3,
            codigo: 'L230315C',
            producto: 'Jeringa 5ml Estéril',
            proveedor: 'Roche S.A.',
            fechaIngreso: '2023-03-15',
            fechaVencimiento: '2025-12-31',
            cantidadInicial: 800,
            stockFinal: 245,
            precio: 0.80,
            estado: 'parcial',
            rotacion: null // En curso
        }
    ];
    
    actualizarEstadisticasHistorial();
}

// Actualizar estadísticas
function actualizarEstadisticasHistorial() {
    const totalLotes = datosHistorialLotes.length;
    const lotesConsumidos = datosHistorialLotes.filter(l => l.estado === 'consumido').length;
    const lotesVencidos = datosHistorialLotes.filter(l => l.estado === 'vencido').length;
    const valorTotal = datosHistorialLotes.reduce((sum, lote) => {
        return sum + (lote.cantidadInicial * lote.precio);
    }, 0);
    
    document.getElementById('totalLotesHistorial').textContent = totalLotes.toLocaleString();
    document.getElementById('lotesConsumidos').textContent = lotesConsumidos.toLocaleString();
    document.getElementById('lotesVencidosHistorial').textContent = lotesVencidos.toLocaleString();
    document.getElementById('valorTotalHistorial').textContent = 'S/ ' + valorTotal.toLocaleString();
}

// Aplicar filtros
function aplicarFiltrosHistorial() {
    const estado = $('#filtroEstadoHistorial').val();
    const proveedor = $('#filtroProveedorHistorial').val();
    const fechaDesde = $('#filtroFechaDesde').val();
    const fechaHasta = $('#filtroFechaHasta').val();
    
    tablaHistorialLotes.clear().rows.add(filtrarDatosHistorial(estado, proveedor, fechaDesde, fechaHasta)).draw();
}

// Filtrar datos
function filtrarDatosHistorial(estado, proveedor, fechaDesde, fechaHasta) {
    let datos = datosHistorialLotes;
    
    if (estado) {
        datos = datos.filter(item => item.estado === estado);
    }
    
    if (proveedor) {
        datos = datos.filter(item => item.proveedor.toLowerCase().includes(proveedor));
    }
    
    if (fechaDesde) {
        datos = datos.filter(item => item.fechaIngreso >= fechaDesde);
    }
    
    if (fechaHasta) {
        datos = datos.filter(item => item.fechaIngreso <= fechaHasta);
    }
    
    return datos.map(item => [
        `<strong>${item.codigo}</strong>`,
        obtenerInfoProductoHistorial(item),
        obtenerInfoProveedorHistorial(item.proveedor),
        formatearFecha(item.fechaIngreso),
        formatearFecha(item.fechaVencimiento),
        `<span class="text-center fw-bold">${item.cantidadInicial}</span>`,
        `<span class="text-center fw-bold ${item.stockFinal === 0 ? 'text-success' : item.stockFinal > 0 ? 'text-warning' : 'text-danger'}">${item.stockFinal}</span>`,
        obtenerBadgeEstadoHistorial(item.estado),
        obtenerRotacion(item.rotacion),
        generarBotonesAccionHistorial(item.id)
    ]);
}

// Obtener información del producto
function obtenerInfoProductoHistorial(item) {
    return `
        <div class="d-flex align-items-center">
            <div class="me-2"><i class="fas fa-pills text-primary"></i></div>
            <div>
                <div class="fw-bold">${item.producto}</div>
                <small class="text-muted">Tableta • S/ ${item.precio.toFixed(2)}</small>
            </div>
        </div>
    `;
}

// Obtener información del proveedor
function obtenerInfoProveedorHistorial(proveedor) {
    const proveedores = {
        'Pfizer S.A.': {ruc: '20100130204', icon: 'fas fa-building text-info'},
        'Novartis Perú S.A.': {ruc: '20100234567', icon: 'fas fa-building text-info'},
        'Roche S.A.': {ruc: '20100345678', icon: 'fas fa-building text-info'}
    };
    
    const info = proveedores[proveedor];
    if (info) {
        return `
            <div class="d-flex align-items-center">
                <div class="me-2"><i class="${info.icon}"></i></div>
                <div>
                    <div class="fw-bold">${proveedor}</div>
                    <small class="text-muted">RUC: ${info.ruc}</small>
                </div>
            </div>
        `;
    }
    return proveedor;
}

// Formatear fecha
function formatearFecha(fecha) {
    return `<span class="badge bg-light text-dark">${new Date(fecha).toLocaleDateString('es-ES')}</span>`;
}

// Obtener badge de estado
function obtenerBadgeEstadoHistorial(estado) {
    const badges = {
        'activo': '<span class="badge bg-success"><i class="fas fa-play"></i> Activo</span>',
        'consumido': '<span class="badge bg-success"><i class="fas fa-check"></i> Consumido</span>',
        'vencido': '<span class="badge bg-danger"><i class="fas fa-ban"></i> Vencido</span>',
        'parcial': '<span class="badge bg-primary"><i class="fas fa-pause"></i> Parcial</span>',
        'descontinuado': '<span class="badge bg-secondary"><i class="fas fa-stop"></i> Descontinuado</span>'
    };
    return badges[estado] || estado;
}

// Obtener rotación
function obtenerRotacion(rotacion) {
    if (rotacion === null || rotacion === undefined) {
        return '<span class="badge bg-primary">En curso</span>';
    }
    const color = rotacion < 30 ? 'success' : rotacion < 90 ? 'info' : rotacion < 365 ? 'warning' : 'danger';
    return `<span class="badge bg-${color}">${rotacion} días</span>`;
}

// Generar botones de acción
function generarBotonesAccionHistorial(id) {
    return `
        <div class="btn-group" role="group">
            <button class="btn btn-sm btn-outline-primary" onclick="verDetallesLote(${id})" title="Ver Detalles Completos">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-outline-info" onclick="verTimelineLote(${id})" title="Ver Timeline">
                <i class="fas fa-stream"></i>
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="analizarLote(${id})" title="Análisis">
                <i class="fas fa-chart-bar"></i>
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="reportarLote(${id})" title="Generar Reporte">
                <i class="fas fa-file-alt"></i>
            </button>
        </div>
    `;
}

// Mostrar vista de historial
function mostrarVistaHistorial(vista) {
    // Ocultar todas las vistas
    $('#vistaTablaHistorial, #vistaTimeline, #vistaAnalisis').addClass('d-none');
    $('.btn-group .btn').removeClass('active');
    
    if (vista === 'tabla') {
        $('#vistaTablaHistorial').removeClass('d-none');
        $(`.btn-group .btn:eq(0)`).addClass('active');
    } else if (vista === 'timeline') {
        $('#vistaTimeline').removeClass('d-none');
        $(`.btn-group .btn:eq(1)`).addClass('active');
    } else if (vista === 'analisis') {
        $('#vistaAnalisis').removeClass('d-none');
        $(`.btn-group .btn:eq(2)`).addClass('active');
    }
}

// Ver detalles completos del lote
function verDetallesLote(id) {
    const lote = datosHistorialLotes.find(l => l.id === id);
    if (!lote) return;
    
    const contenido = `
        <div class="row">
            <div class="col-md-6">
                <h6>Información General del Lote</h6>
                <table class="table table-sm">
                    <tr><td><strong>Código:</strong></td><td>${lote.codigo}</td></tr>
                    <tr><td><strong>Producto:</strong></td><td>${lote.producto}</td></tr>
                    <tr><td><strong>Proveedor:</strong></td><td>${lote.proveedor}</td></tr>
                    <tr><td><strong>Fecha Ingreso:</strong></td><td>${new Date(lote.fechaIngreso).toLocaleDateString('es-ES')}</td></tr>
                    <tr><td><strong>Fecha Vencimiento:</strong></td><td>${new Date(lote.fechaVencimiento).toLocaleDateString('es-ES')}</td></tr>
                    <tr><td><strong>Precio Unitario:</strong></td><td>S/ ${lote.precio.toFixed(2)}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Ciclo de Vida</h6>
                <table class="table table-sm">
                    <tr><td><strong>Cantidad Inicial:</strong></td><td>${lote.cantidadInicial} unidades</td></tr>
                    <tr><td><strong>Stock Final:</strong></td><td>${lote.stockFinal} unidades</td></tr>
                    <tr><td><strong>Consumido:</strong></td><td>${lote.cantidadInicial - lote.stockFinal} unidades</td></tr>
                    <tr><td><strong>Porcentaje Consumido:</strong></td><td>${((lote.cantidadInicial - lote.stockFinal) / lote.cantidadInicial * 100).toFixed(1)}%</td></tr>
                    <tr><td><strong>Estado:</strong></td><td>${obtenerBadgeEstadoHistorial(lote.estado)}</td></tr>
                    <tr><td><strong>Rotación:</strong></td><td>${lote.rotacion ? lote.rotacion + ' días' : 'En curso'}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Movimientos Históricos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo de Movimiento</th>
                                <th>Cantidad</th>
                                <th>Stock Restante</th>
                                <th>Usuario</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>${new Date(lote.fechaIngreso).toLocaleDateString('es-ES')}</td>
                                <td><span class="badge bg-success">Entrada</span></td>
                                <td>+${lote.cantidadInicial}</td>
                                <td>${lote.cantidadInicial}</td>
                                <td>Sistema</td>
                                <td>Ingreso inicial del lote</td>
                            </tr>
                            <tr>
                                <td>15/01/2023</td>
                                <td><span class="badge bg-danger">Salida</span></td>
                                <td>-150</td>
                                <td>${lote.cantidadInicial - 150}</td>
                                <td>Vendedor 1</td>
                                <td>Venta mostrador</td>
                            </tr>
                            <tr>
                                <td>28/11/2023</td>
                                <td><span class="badge bg-warning">Alerta</span></td>
                                <td>-</td>
                                <td>${lote.cantidadInicial - 1950}</td>
                                <td>Sistema</td>
                                <td>Stock bajo - menos de 100 unidades</td>
                            </tr>
                            <tr>
                                <td>31/12/2023</td>
                                <td><span class="badge bg-danger">Salida Final</span></td>
                                <td>${lote.stockFinal > 0 ? '-' + lote.stockFinal : '-0'}</td>
                                <td>${lote.stockFinal}</td>
                                <td>Vendedor 2</td>
                                <td>Últimas unidades vendidas</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Análisis de Rendimiento</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-left-success">
                            <div class="card-body text-center">
                                <h4 class="text-success">${lote.rotacion ? lote.rotacion : 'N/A'}</h4>
                                <p class="mb-0">Días de Rotación</p>
                                <small class="text-muted">${lote.rotacion < 300 ? 'Excelente' : lote.rotacion < 365 ? 'Bueno' : 'Regular'}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-left-info">
                            <div class="card-body text-center">
                                <h4 class="text-info">${((lote.cantidadInicial - lote.stockFinal) / lote.cantidadInicial * 100).toFixed(1)}%</h4>
                                <p class="mb-0">Eficiencia de Consumo</p>
                                <small class="text-muted">${(lote.cantidadInicial - lote.stockFinal) / lote.cantidadInicial >= 0.9 ? 'Alta' : (lote.cantidadInicial - lote.stockFinal) / lote.cantidadInicial >= 0.7 ? 'Media' : 'Baja'}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-left-warning">
                            <div class="card-body text-center">
                                <h4 class="text-warning">${lote.stockFinal}</h4>
                                <p class="mb-0">Pérdidas</p>
                                <small class="text-muted">${lote.stockFinal > 0 ? lote.stockFinal + ' unidades' : 'Sin pérdidas'}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contenidoDetallesLote').innerHTML = contenido;
    $('#modalDetallesLote').modal('show');
}

// Ver timeline del lote
function verTimelineLote(id) {
    mostrarVistaHistorial('timeline');
}

// Analizar lote
function analizarLote(id) {
    Swal.fire({
        title: 'Análisis de Lote',
        text: 'Generando análisis detallado del rendimiento del lote...',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Análisis Completado',
            html: `
                <div class="text-left">
                    <h6>Resultados del Análisis:</h6>
                    <ul>
                        <li><strong>Rotación:</strong> Óptima (365 días)</li>
                        <li><strong>Eficiencia:</strong> 100% de consumo</li>
                        <li><strong>Pérdidas:</strong> 0 unidades</li>
                        <li><strong>Recomendación:</strong> Continuar con este patrón de rotación</li>
                    </ul>
                </div>
            `,
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    });
}

// Analizar vencimiento
function analizarVencimiento(id) {
    Swal.fire({
        title: 'Análisis de Vencimiento',
        text: 'Evaluando las causas del vencimiento del lote...',
        icon: 'warning',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Causas Identificadas',
            html: `
                <div class="text-left">
                    <h6>Factores que Contribuyeron al Vencimiento:</h6>
                    <ul>
                        <li>Rotación lenta (487 días)</li>
                        <li>Sobrepedido inicial (1,500 unidades)</li>
                        <li>Falta de alertas tempranas</li>
                        <li>Baja demanda del producto</li>
                    </ul>
                    <h6>Recomendaciones:</h6>
                    <ul>
                        <li>Reducir pedido futuro en 40%</li>
                        <li>Implementar alertas a 90 días</li>
                        <li>Mejorar estrategia de marketing</li>
                    </ul>
                </div>
            `,
            icon: 'warning',
            confirmButtonText: 'Aceptar'
        });
    });
}

// Analizar rotación
function analizarRotacion(id) {
    Swal.fire({
        title: 'Análisis de Rotación',
        text: 'Analizando patrones de rotación del lote...',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Análisis de Rotación',
            html: `
                <div class="text-left">
                    <h6>Patrón de Rotación:</h6>
                    <ul>
                        <li><strong>Velocidad:</strong> Moderada (en curso)</li>
                        <li><strong>Consumo actual:</strong> 69.4%</li>
                        <li><strong>Proyección de agotamiento:</strong> 45 días</li>
                        <li><strong>Riesgo de vencimiento:</strong> Bajo</li>
                    </ul>
                    <h6>Predicción:</h6>
                    <ul>
                        <li>Se agotará antes del vencimiento</li>
                        <li>Rotación esperada: 180-220 días</li>
                        <li>Rendimiento: Óptimo</li>
                    </ul>
                </div>
            `,
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    });
}

// Predecir vencimiento
function predecirVencimiento(id) {
    Swal.fire({
        title: 'Predicción de Vencimiento',
        text: 'Calculando predicción basada en patrones históricos...',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Predicción Generada',
            html: `
                <div class="text-left">
                    <h6>Proyección de Vencimiento:</h6>
                    <ul>
                        <li><strong>Fecha estimada de agotamiento:</strong> 15/03/2024</li>
                        <li><strong>Días restantes estimados:</strong> 45 días</li>
                        <li><strong>Probabilidad de agotamiento:</strong> 95%</li>
                        <li><strong>Confianza del modelo:</strong> 87%</li>
                    </ul>
                    <h6>Recomendaciones:</h6>
                    <ul>
                        <li>Stock suficiente para venta normal</li>
                        <li>No se requieren acciones especiales</li>
                        <li>Monitoreo mensual recomendado</li>
                    </ul>
                </div>
            `,
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    });
}

// Reportar lote
function reportarLote(id) {
    window.open(`/reportes/lote/${id}`, '_blank');
}

// Generar acción para lote vencido
function generarAccionVencido(id) {
    Swal.fire({
        title: 'Acción para Lote Vencido',
        text: '¿Qué acción desea tomar para este lote vencido?',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Enviar a Descarte',
        denyButtonText: 'Generar Reporte',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Enviado a Descarte',
                text: 'El lote ha sido enviado al proceso de descarte',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        } else if (result.isDenied) {
            window.open(`/reportes/lote-vencido/${id}`, '_blank');
        }
    });
}

// Exportar historial
function exportarHistorial() {
    Swal.fire({
        title: 'Exportar Historial',
        text: 'Seleccione el formato de exportación',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Excel',
        denyButtonText: 'PDF',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('/export/historial-lotes/excel', '_blank');
        } else if (result.isDenied) {
            window.open('/export/historial-lotes/pdf', '_blank');
        }
    });
}

// Aplicar filtros avanzados
function aplicarFiltrosAvanzados() {
    const rangoFechas = document.getElementById('rangoFechas').value;
    const rotacionMinima = document.getElementById('rotacionMinima').value;
    const rotacionMaxima = document.getElementById('rotacionMaxima').value;
    const categoriaProducto = document.getElementById('categoriaProducto').value;
    const valorMinimo = document.getElementById('valorMinimo').value;
    const valorMaximo = document.getElementById('valorMaximo').value;
    const soloVencidos = document.getElementById('soloVencidos').checked;
    const soloPerdidas = document.getElementById('soloPerdidas').checked;
    const soloRotacionRapida = document.getElementById('soloRotacionRapida').checked;
    
    // Aquí se aplicarían los filtros avanzados
    console.log('Filtros aplicados:', {
        rangoFechas, rotacionMinima, rotacionMaxima, categoriaProducto,
        valorMinimo, valorMaximo, soloVencidos, soloPerdidas, soloRotacionRapida
    });
    
    $('#modalFiltroAvanzado').modal('hide');
    
    Swal.fire({
        title: 'Filtros Aplicados',
        text: 'Los filtros avanzados han sido aplicados correctamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    });
}

// Limpiar filtros avanzados
function limpiarFiltrosAvanzados() {
    document.getElementById('rangoFechas').value = '';
    document.getElementById('rotacionMinima').value = '';
    document.getElementById('rotacionMaxima').value = '';
    document.getElementById('categoriaProducto').value = '';
    document.getElementById('valorMinimo').value = '';
    document.getElementById('valorMaximo').value = '';
    document.getElementById('soloVencidos').checked = false;
    document.getElementById('soloPerdidas').checked = false;
    document.getElementById('soloRotacionRapida').checked = false;
}

// Generar reporte completo
function generarReporteCompleto() {
    window.open('/reportes/lote-completo', '_blank');
}

// Exportar detalles del lote
function exportarDetallesLote() {
    Swal.fire({
        title: 'Exportar Detalles',
        text: 'Seleccione el formato de exportación',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Excel',
        denyButtonText: 'PDF',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('/export/lote-detalle/excel', '_blank');
        } else if (result.isDenied) {
            window.open('/export/lote-detalle/pdf', '_blank');
        }
    });
}
</script>
@endsection