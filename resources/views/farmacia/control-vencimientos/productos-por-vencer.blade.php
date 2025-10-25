@extends('layouts.app')

@section('title', 'Productos por Vencer - SIFANO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-exclamation text-warning"></i>
                Productos por Vencer
            </h1>
            <p class="text-muted">Monitoreo proactivo de productos próximos a su fecha de vencimiento</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportarPorVencer()">
                <i class="fas fa-file-export"></i> Exportar
            </button>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalConfiguracionAlertas">
                <i class="fas fa-cog"></i> Configurar Alertas
            </button>
            <button class="btn btn-success" onclick="generarPlanRotacion()">
                <i class="fas fa-sync"></i> Plan de Rotación
            </button>
        </div>
    </div>

    <!-- Estadísticas de Alerta -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Críticos (< 7 días)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="criticos">12</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                Alertas (7-15 días)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="alertas">28</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Advertencias (15-30 días)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="advertencias">45</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-info-circle fa-2x text-gray-300"></i>
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
                                Valor en Riesgo
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="valorRiesgo">S/ 18,450</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta Principal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning border-left-warning" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning me-3"></i>
                    <div>
                        <h5 class="alert-heading">Atención: Productos Próximos a Vencer</h5>
                        <p class="mb-2">
                            Se han identificado <strong>12 productos críticos</strong> que vencen en menos de 7 días. 
                            Se requiere acción inmediata para minimizar pérdidas.
                        </p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-danger btn-sm" onclick="accionesCriticos()">
                                <i class="fas fa-exclamation-triangle"></i> Ver Críticos
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="generarReportes()">
                                <i class="fas fa-file-alt"></i> Generar Reportes
                            </button>
                            <button class="btn btn-success btn-sm" onclick="iniciarRotacion()">
                                <i class="fas fa-sync"></i> Iniciar Rotación
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filtros de Monitoreo
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="filtroPrioridad" class="form-label">Prioridad</label>
                    <select class="form-select" id="filtroPrioridad">
                        <option value="">Todas las prioridades</option>
                        <option value="critico">Crítico</option>
                        <option value="alerta">Alerta</option>
                        <option value="advertencia">Advertencia</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroCategoria" class="form-label">Categoría</label>
                    <select class="form-select" id="filtroCategoria">
                        <option value="">Todas las categorías</option>
                        <option value="medicamentos">Medicamentos</option>
                        <option value="dispositivos">Dispositivos Médicos</option>
                        <option value="suplementos">Suplementos</option>
                        <option value="cosméticos">Cosméticos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroRangoVencimiento" class="form-label">Rango de Días</label>
                    <select class="form-select" id="filtroRangoVencimiento">
                        <option value="">Todos los rangos</option>
                        <option value="0-7">0-7 días (Crítico)</option>
                        <option value="8-15">8-15 días (Alerta)</option>
                        <option value="16-30">16-30 días (Advertencia)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="busquedaProducto" class="form-label">Buscar Producto</label>
                    <input type="text" class="form-control" id="busquedaProducto" placeholder="Nombre o código...">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-4">
                    <label for="filtroValorMinimo" class="form-label">Valor Mínimo (S/)</label>
                    <input type="number" class="form-control" id="filtroValorMinimo" step="0.01" placeholder="0.00">
                </div>
                <div class="col-md-4">
                    <label for="filtroStockMinimo" class="form-label">Stock Mínimo</label>
                    <input type="number" class="form-control" id="filtroStockMinimo" placeholder="1">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div>
                        <button class="btn btn-primary me-2" onclick="aplicarFiltrosPorVencer()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <button class="btn btn-outline-secondary" onclick="limpiarFiltrosPorVencer()">
                            <i class="fas fa-undo"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Productos por Vencer -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Productos por Vencer
            </h6>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary active" onclick="mostrarVistaPorVencer('tabla')">
                    <i class="fas fa-list"></i> Tabla
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="mostrarVistaPorVencer('tarjetas')">
                    <i class="fas fa-th-large"></i> Tarjetas
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="mostrarVistaPorVencer('cronologica')">
                    <i class="fas fa-calendar"></i> Cronológica
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="vistaTablaPorVencer" class="table-responsive">
                <table class="table table-bordered table-striped" id="tablaProductosPorVencer" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Lote</th>
                            <th>Stock Actual</th>
                            <th>Valor Stock</th>
                            <th>Fecha Vencimiento</th>
                            <th>Días Restantes</th>
                            <th>Prioridad</th>
                            <th>Proveedor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-producto="1" class="table-danger">
                            <td><strong>MED001</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-pills text-danger"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Ibuprofeno 400mg</div>
                                        <small class="text-muted">Tableta • S/ 0.50</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-tag text-warning me-2"></i>
                                    <span class="fw-bold">L240326A</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">45</span>
                            </td>
                            <td class="text-end fw-bold">S/ 22.50</td>
                            <td>
                                <span class="badge bg-danger">
                                    <i class="fas fa-calendar-times"></i>
                                    27 Oct 2024
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">2 días</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-triangle"></i> Crítico
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-building text-info me-2"></i>
                                    <div>
                                        <div class="fw-bold">Pfizer S.A.</div>
                                        <small class="text-muted">RUC: 20100130204</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verDetalleProducto(1)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="accionesRotacion(1)" title="Acción Rotación">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="generarDescuento(1)" title="Generar Descuento">
                                        <i class="fas fa-percentage"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="marcarVencido(1)">Marcar como Vencido</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="generarAlerta(1)">Enviar Alerta</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-primary" href="#" onclick="transferirProducto(1)">Transferir</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr data-producto="2" class="table-warning">
                            <td><strong>DIS001</strong></td>
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
                                    <i class="fas fa-tag text-warning me-2"></i>
                                    <span class="fw-bold">L240325B</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning">78</span>
                            </td>
                            <td class="text-end fw-bold">S/ 62.40</td>
                            <td>
                                <span class="badge bg-warning">
                                    <i class="fas fa-exclamation-circle"></i>
                                    02 Nov 2024
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning">8 días</span>
                            </td>
                            <td>
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock"></i> Alerta
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-building text-info me-2"></i>
                                    <div>
                                        <div class="fw-bold">Roche S.A.</div>
                                        <small class="text-muted">RUC: 20100345678</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verDetalleProducto(2)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="accionesRotacion(2)" title="Acción Rotación">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="generarDescuento(2)" title="Generar Descuento">
                                        <i class="fas fa-percentage"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="agendarRevision(2)" title="Agendar Revisión">
                                        <i class="fas fa-calendar-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-producto="3" class="table-info">
                            <td><strong>MED005</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-pills text-success"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Loratadina 10mg</div>
                                        <small class="text-muted">Tableta • S/ 0.35</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-tag text-warning me-2"></i>
                                    <span class="fw-bold">L240320C</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">156</span>
                            </td>
                            <td class="text-end fw-bold">S/ 54.60</td>
                            <td>
                                <span class="badge bg-info">
                                    <i class="fas fa-info-circle"></i>
                                    15 Nov 2024
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">21 días</span>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <i class="fas fa-info"></i> Advertencia
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-building text-info me-2"></i>
                                    <div>
                                        <div class="fw-bold">Novartis S.A.</div>
                                        <small class="text-muted">RUC: 20100234567</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verDetalleProducto(3)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="monitorearRotacion(3)" title="Monitorear">
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="optimizarRotacion(3)" title="Optimizar">
                                        <i class="fas fa-tachometer-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="configurarSeguimiento(3)" title="Configurar">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Más productos se cargarían dinámicamente -->
                    </tbody>
                </table>
            </div>

            <div id="vistaTarjetasPorVencer" class="row d-none">
                <!-- Vista en tarjetas se llenará dinámicamente -->
            </div>

            <div id="vistaCronologica" class="d-none">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Vista Cronológica:</strong> Esta vista muestra los productos organizados por fecha de vencimiento en una línea de tiempo.
                </div>
                <div class="timeline-container">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <div class="card border-left-danger">
                                    <div class="card-body">
                                        <h6>27 Oct 2024 - Crítico (2 días)</h6>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <strong>Ibuprofeno 400mg</strong> - Lote L240326A
                                                <br><small class="text-muted">45 unidades • S/ 22.50 • Pfizer S.A.</small>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <button class="btn btn-sm btn-danger" onclick="accionInmediata('MED001')">
                                                    Acción Inmediata
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <div class="card border-left-warning">
                                    <div class="card-body">
                                        <h6>02 Nov 2024 - Alerta (8 días)</h6>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <strong>Jeringa 5ml Estéril</strong> - Lote L240325B
                                                <br><small class="text-muted">78 unidades • S/ 62.40 • Roche S.A.</small>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <button class="btn btn-sm btn-warning" onclick="agendarAccion('DIS001')">
                                                    Agendar Acción
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <div class="card border-left-info">
                                    <div class="card-body">
                                        <h6>15 Nov 2024 - Advertencia (21 días)</h6>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <strong>Loratadina 10mg</strong> - Lote L240320C
                                                <br><small class="text-muted">156 unidades • S/ 54.60 • Novartis S.A.</small>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <button class="btn btn-sm btn-info" onclick="monitorearRotacion('MED005')">
                                                    Monitorear
                                                </button>
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

    <!-- Gráficos de Vencimientos -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area"></i> Distribución de Vencimientos
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <div class="dropdown-header">Opciones:</div>
                            <a class="dropdown-item" href="#" onclick="actualizarGraficoPorVencer()">Actualizar</a>
                            <a class="dropdown-item" href="#" onclick="exportarGraficoPorVencer()">Exportar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="graficoVencimientos"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Productos por Categoría
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="graficoCategoriasPorVencer"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Acciones -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks"></i> Resumen de Acciones Recomendadas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border-left-danger">
                                <div class="card-body text-center">
                                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                                    <h5 class="text-danger">Acción Inmediata</h5>
                                    <p class="mb-3">12 productos requieren acción en las próximas 48 horas</p>
                                    <button class="btn btn-danger" onclick="accionesCriticos()">
                                        <i class="fas fa-bolt"></i> Ver Críticos
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-left-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-sync fa-3x text-warning mb-3"></i>
                                    <h5 class="text-warning">Plan de Rotación</h5>
                                    <p class="mb-3">28 productos pueden optimizarse con rotación proactiva</p>
                                    <button class="btn btn-warning" onclick="generarPlanRotacion()">
                                        <i class="fas fa-calendar-alt"></i> Crear Plan
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-left-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                                    <h5 class="text-info">Monitoreo Continuo</h5>
                                    <p class="mb-3">45 productos bajo vigilancia de rotación</p>
                                    <button class="btn btn-info" onclick="configurarMonitoreo()">
                                        <i class="fas fa-cog"></i> Configurar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Configuración de Alertas -->
<div class="modal fade" id="modalConfiguracionAlertas" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cog"></i> Configuración de Alertas de Vencimiento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formConfiguracionAlertas">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Umbrales de Alerta</h6>
                            <div class="mb-3">
                                <label for="umbralCritico" class="form-label">Umbral Crítico (días)</label>
                                <input type="number" class="form-control" id="umbralCritico" value="7" min="1" max="30">
                                <small class="form-text text-muted">Productos que vencen en menos de este número de días</small>
                            </div>
                            <div class="mb-3">
                                <label for="umbralAlerta" class="form-label">Umbral de Alerta (días)</label>
                                <input type="number" class="form-control" id="umbralAlerta" value="15" min="1" max="60">
                                <small class="form-text text-muted">Productos que vencen en este rango</small>
                            </div>
                            <div class="mb-3">
                                <label for="umbralAdvertencia" class="form-label">Umbral de Advertencia (días)</label>
                                <input type="number" class="form-control" id="umbralAdvertencia" value="30" min="1" max="90">
                                <small class="form-text text-muted">Productos que vencen en este rango</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Configuración de Notificaciones</h6>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notificacionEmail" checked>
                                    <label class="form-check-label" for="notificacionEmail">
                                        Notificación por Email
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notificacionSMS" checked>
                                    <label class="form-check-label" for="notificacionSMS">
                                        Notificación por SMS
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notificacionDashboard" checked>
                                    <label class="form-check-label" for="notificacionDashboard">
                                        Notificación en Dashboard
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="frecuenciaRevision" class="form-label">Frecuencia de Revisión</label>
                                <select class="form-select" id="frecuenciaRevision">
                                    <option value="diario">Diario</option>
                                    <option value="semanal" selected>Semanal</option>
                                    <option value="quincenal">Quincenal</option>
                                    <option value="mensual">Mensual</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6>Destinatarios de Alertas</h6>
                            <div class="mb-3">
                                <label for="emailDestinatarios" class="form-label">Emails (separados por comas)</label>
                                <input type="text" class="form-control" id="emailDestinatarios" 
                                       value="admin@sifano.com, supervisor@sifano.com, almacen@sifano.com">
                            </div>
                            <div class="mb-3">
                                <label for="telefonosDestinatarios" class="form-label">Teléfonos SMS (separados por comas)</label>
                                <input type="text" class="form-control" id="telefonosDestinatarios" 
                                       value="+51987654321, +51987654322, +51987654323">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary" onclick="probarAlertas()">
                        <i class="fas fa-paper-plane"></i> Probar Alertas
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalle de Producto -->
<div class="modal fade" id="modalDetalleProducto" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalle del Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoDetalleProducto">
                <!-- Se carga dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-warning" onclick="generarPlanIndividual()">
                    <i class="fas fa-calendar-alt"></i> Plan Individual
                </button>
                <button type="button" class="btn btn-success" onclick="generarReporteProducto()">
                    <i class="fas fa-file-pdf"></i> Reporte
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
.timeline-container {
    max-height: 600px;
    overflow-y: auto;
}

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
    top: 0.5rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    padding-left: 1rem;
}

.table-danger {
    background-color: rgba(220, 53, 69, 0.1);
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.table-info {
    background-color: rgba(23, 162, 184, 0.1);
}
</style>
@endsection

@section('scripts')
<script>
// Variables globales
let tablaProductosPorVencer;
let datosProductosPorVencer = [];

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    inicializarTabla();
    inicializarGraficos();
    cargarDatos();
    configurarEventos();
});

// Inicializar DataTable
function inicializarTabla() {
    tablaProductosPorVencer = $('#tablaProductosPorVencer').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        pageLength: 25,
        responsive: true,
        order: [[6, 'asc']], // Ordenar por días restantes
        columnDefs: [
            {
                targets: [3, 4, 6],
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
    $('#busquedaProducto').on('keyup', function() {
        tablaProductosPorVencer.search($(this).val()).draw();
    });

    // Filtros
    $('#filtroPrioridad, #filtroCategoria, #filtroRangoVencimiento, #filtroValorMinimo, #filtroStockMinimo').on('change', aplicarFiltrosPorVencer);
}

// Inicializar gráficos
function inicializarGraficos() {
    // Gráfico de vencimientos
    const ctx1 = document.getElementById('graficoVencimientos').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: ['0-7 días', '8-15 días', '16-30 días', '31-60 días', '61-90 días'],
            datasets: [{
                label: 'Número de Productos',
                data: [12, 28, 45, 67, 23],
                backgroundColor: [
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ],
                borderColor: [
                    'rgba(231, 74, 59, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(23, 162, 184, 1)',
                    'rgba(78, 115, 223, 1)',
                    'rgba(108, 117, 125, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Número de Productos'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Productos: ' + context.parsed.y;
                        }
                    }
                }
            }
        }
    });

    // Gráfico de categorías
    const ctx2 = document.getElementById('graficoCategoriasPorVencer').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Medicamentos', 'Dispositivos', 'Suplementos', 'Cosméticos'],
            datasets: [{
                data: [68, 45, 32, 28],
                backgroundColor: [
                    'rgb(231, 74, 59)',
                    'rgb(255, 193, 7)',
                    'rgb(23, 162, 184)',
                    'rgb(78, 115, 223)'
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
    datosProductosPorVencer = [
        {
            id: 1,
            codigo: 'MED001',
            producto: 'Ibuprofeno 400mg',
            lote: 'L240326A',
            stock: 45,
            precio: 0.50,
            valor: 22.50,
            fechaVencimiento: '2024-10-27',
            diasRestantes: 2,
            prioridad: 'critico',
            categoria: 'medicamentos',
            proveedor: 'Pfizer S.A.'
        },
        {
            id: 2,
            codigo: 'DIS001',
            producto: 'Jeringa 5ml Estéril',
            lote: 'L240325B',
            stock: 78,
            precio: 0.80,
            valor: 62.40,
            fechaVencimiento: '2024-11-02',
            diasRestantes: 8,
            prioridad: 'alerta',
            categoria: 'dispositivos',
            proveedor: 'Roche S.A.'
        },
        {
            id: 3,
            codigo: 'MED005',
            producto: 'Loratadina 10mg',
            lote: 'L240320C',
            stock: 156,
            precio: 0.35,
            valor: 54.60,
            fechaVencimiento: '2024-11-15',
            diasRestantes: 21,
            prioridad: 'advertencia',
            categoria: 'medicamentos',
            proveedor: 'Novartis S.A.'
        }
    ];
    
    actualizarEstadisticas();
}

// Actualizar estadísticas
function actualizarEstadisticas() {
    const criticos = datosProductosPorVencer.filter(p => p.diasRestantes <= 7).length;
    const alertas = datosProductosPorVencer.filter(p => p.diasRestantes > 7 && p.diasRestantes <= 15).length;
    const advertencias = datosProductosPorVencer.filter(p => p.diasRestantes > 15 && p.diasRestantes <= 30).length;
    const valorRiesgo = datosProductosPorVencer.reduce((sum, p) => sum + p.valor, 0);
    
    document.getElementById('criticos').textContent = criticos;
    document.getElementById('alertas').textContent = alertas;
    document.getElementById('advertencias').textContent = advertencias;
    document.getElementById('valorRiesgo').textContent = 'S/ ' + valorRiesgo.toLocaleString();
}

// Configurar eventos
function configurarEventos() {
    // Auto-refresh cada 30 minutos
    setInterval(() => {
        cargarDatos();
        tablaProductosPorVencer.clear().rows.add(filtrarDatosPorVencer()).draw();
    }, 30 * 60 * 1000);
}

// Aplicar filtros
function aplicarFiltrosPorVencer() {
    const prioridad = $('#filtroPrioridad').val();
    const categoria = $('#filtroCategoria').val();
    const rango = $('#filtroRangoVencimiento').val();
    const valorMinimo = parseFloat($('#filtroValorMinimo').value || 0);
    const stockMinimo = parseInt($('#filtroStockMinimo').value || 0);
    
    tablaProductosPorVencer.clear().rows.add(filtrarDatosPorVencer(prioridad, categoria, rango, valorMinimo, stockMinimo)).draw();
}

// Filtrar datos
function filtrarDatosPorVencer(prioridad = '', categoria = '', rango = '', valorMinimo = 0, stockMinimo = 0) {
    let datos = datosProductosPorVencer;
    
    if (prioridad) {
        datos = datos.filter(item => item.prioridad === prioridad);
    }
    
    if (categoria) {
        datos = datos.filter(item => item.categoria === categoria);
    }
    
    if (rango) {
        const [min, max] = rango.split('-').map(Number);
        datos = datos.filter(item => item.diasRestantes >= min && item.diasRestantes <= max);
    }
    
    if (valorMinimo > 0) {
        datos = datos.filter(item => item.valor >= valorMinimo);
    }
    
    if (stockMinimo > 0) {
        datos = datos.filter(item => item.stock >= stockMinimo);
    }
    
    return datos.map(item => [
        `<strong>${item.codigo}</strong>`,
        obtenerInfoProducto(item),
        obtenerInfoLote(item.lote),
        obtenerInfoStock(item),
        `S/ ${item.valor.toFixed(2)}`,
        formatearFechaVencimiento(item.fechaVencimiento, item.diasRestantes),
        obtenerDiasRestantes(item.diasRestantes),
        obtenerBadgePrioridad(item.prioridad),
        obtenerInfoProveedor(item.proveedor),
        generarBotonesAccion(item.id, item.prioridad)
    ]);
}

// Obtener información del producto
function obtenerInfoProducto(item) {
    const colores = {
        'medicamentos': 'fas fa-pills text-danger',
        'dispositivos': 'fas fa-syringe text-info',
        'suplementos': 'fas fa-capsules text-success',
        'cosméticos': 'fas fa-seedling text-warning'
    };
    
    return `
        <div class="d-flex align-items-center">
            <div class="me-2">
                <i class="${colores[item.categoria]}"></i>
            </div>
            <div>
                <div class="fw-bold">${item.producto}</div>
                <small class="text-muted">Tableta • S/ ${item.precio.toFixed(2)}</small>
            </div>
        </div>
    `;
}

// Obtener información del lote
function obtenerInfoLote(lote) {
    return `
        <div class="d-flex align-items-center">
            <i class="fas fa-tag text-warning me-2"></i>
            <span class="fw-bold">${lote}</span>
        </div>
    `;
}

// Obtener información del stock
function obtenerInfoStock(item) {
    const color = item.prioridad === 'critico' ? 'bg-danger' : 
                 item.prioridad === 'alerta' ? 'bg-warning' : 'bg-info';
    return `<span class="badge ${color}">${item.stock}</span>`;
}

// Formatear fecha de vencimiento
function formatearFechaVencimiento(fecha, dias) {
    const fechaObj = new Date(fecha);
    const icono = dias <= 7 ? 'fa-calendar-times' : dias <= 15 ? 'fa-exclamation-circle' : 'fa-info-circle';
    const color = dias <= 7 ? 'bg-danger' : dias <= 15 ? 'bg-warning' : 'bg-info';
    
    return `
        <span class="badge ${color}">
            <i class="fas ${icono}"></i>
            ${fechaObj.toLocaleDateString('es-ES')}
        </span>
    `;
}

// Obtener días restantes
function obtenerDiasRestantes(dias) {
    const color = dias <= 7 ? 'bg-danger' : dias <= 15 ? 'bg-warning' : 'bg-info';
    return `<span class="badge ${color}">${dias} días</span>`;
}

// Obtener badge de prioridad
function obtenerBadgePrioridad(prioridad) {
    const badges = {
        'critico': '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Crítico</span>',
        'alerta': '<span class="badge bg-warning"><i class="fas fa-clock"></i> Alerta</span>',
        'advertencia': '<span class="badge bg-info"><i class="fas fa-info"></i> Advertencia</span>'
    };
    return badges[prioridad];
}

// Obtener información del proveedor
function obtenerInfoProveedor(proveedor) {
    const proveedores = {
        'Pfizer S.A.': {ruc: '20100130204', icon: 'fas fa-building text-info'},
        'Roche S.A.': {ruc: '20100345678', icon: 'fas fa-building text-info'},
        'Novartis S.A.': {ruc: '20100234567', icon: 'fas fa-building text-info'}
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

// Generar botones de acción
function generarBotonesAccion(id, prioridad) {
    let botones = `
        <div class="btn-group" role="group">
            <button class="btn btn-sm btn-outline-primary" onclick="verDetalleProducto(${id})" title="Ver Detalles">
                <i class="fas fa-eye"></i>
            </button>
    `;
    
    if (prioridad === 'critico') {
        botones += `
            <button class="btn btn-sm btn-outline-warning" onclick="accionesRotacion(${id})" title="Acción Rotación">
                <i class="fas fa-sync"></i>
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="generarDescuento(${id})" title="Generar Descuento">
                <i class="fas fa-percentage"></i>
            </button>
        `;
    } else if (prioridad === 'alerta') {
        botones += `
            <button class="btn btn-sm btn-outline-warning" onclick="accionesRotacion(${id})" title="Acción Rotación">
                <i class="fas fa-sync"></i>
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="generarDescuento(${id})" title="Generar Descuento">
                <i class="fas fa-percentage"></i>
            </button>
            <button class="btn btn-sm btn-outline-info" onclick="agendarRevision(${id})" title="Agendar Revisión">
                <i class="fas fa-calendar-plus"></i>
            </button>
        `;
    } else {
        botones += `
            <button class="btn btn-sm btn-outline-info" onclick="monitorearRotacion(${id})" title="Monitorear">
                <i class="fas fa-chart-line"></i>
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="optimizarRotacion(${id})" title="Optimizar">
                <i class="fas fa-tachometer-alt"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="configurarSeguimiento(${id})" title="Configurar">
                <i class="fas fa-cog"></i>
            </button>
        `;
    }
    
    botones += '</div>';
    return botones;
}

// Limpiar filtros
function limpiarFiltrosPorVencer() {
    $('#filtroPrioridad, #filtroCategoria, #filtroRangoVencimiento, #filtroValorMinimo, #filtroStockMinimo').val('');
    $('#busquedaProducto').val('');
    tablaProductosPorVencer.search('').columns().search('').draw();
}

// Mostrar vista por vencer
function mostrarVistaPorVencer(vista) {
    // Ocultar todas las vistas
    $('#vistaTablaPorVencer, #vistaTarjetasPorVencer, #vistaCronologica').addClass('d-none');
    $('.btn-group .btn').removeClass('active');
    
    if (vista === 'tabla') {
        $('#vistaTablaPorVencer').removeClass('d-none');
        $(`.btn-group .btn:eq(0)`).addClass('active');
    } else if (vista === 'tarjetas') {
        $('#vistaTarjetasPorVencer').removeClass('d-none');
        $(`.btn-group .btn:eq(1)`).addClass('active');
        cargarVistaTarjetasPorVencer();
    } else if (vista === 'cronologica') {
        $('#vistaCronologica').removeClass('d-none');
        $(`.btn-group .btn:eq(2)`).addClass('active');
    }
}

// Cargar vista en tarjetas
function cargarVistaTarjetasPorVencer() {
    const container = document.getElementById('vistaTarjetasPorVencer');
    container.innerHTML = '';
    
    datosProductosPorVencer.forEach(item => {
        const color = item.prioridad === 'critico' ? 'border-danger' : 
                     item.prioridad === 'alerta' ? 'border-warning' : 'border-info';
        const badgeColor = item.prioridad === 'critico' ? 'bg-danger' : 
                          item.prioridad === 'alerta' ? 'bg-warning' : 'bg-info';
        
        const card = document.createElement('div');
        card.className = 'col-xl-4 col-lg-6 col-md-12 mb-4';
        card.innerHTML = `
            <div class="card h-100 ${color}">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">${item.codigo}</h6>
                    <span class="badge ${badgeColor}">${item.prioridad.toUpperCase()}</span>
                </div>
                <div class="card-body">
                    <h6 class="card-title">${item.producto}</h6>
                    <p class="card-text">
                        <small class="text-muted">Lote: ${item.lote}</small>
                    </p>
                    <div class="row text-center mb-2">
                        <div class="col">
                            <div class="text-sm font-weight-bold text-primary">${item.stock}</div>
                            <div class="text-xs text-muted">Stock</div>
                        </div>
                        <div class="col">
                            <div class="text-sm font-weight-bold text-success">S/ ${item.valor.toFixed(2)}</div>
                            <div class="text-xs text-muted">Valor</div>
                        </div>
                        <div class="col">
                            <div class="text-sm font-weight-bold ${item.diasRestantes <= 7 ? 'text-danger' : item.diasRestantes <= 15 ? 'text-warning' : 'text-info'}">${item.diasRestantes}</div>
                            <div class="text-xs text-muted">Días</div>
                        </div>
                    </div>
                    <div class="text-center mb-2">
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> Vence: ${new Date(item.fechaVencimiento).toLocaleDateString('es-ES')}
                        </small>
                    </div>
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar ${badgeColor.replace('bg-', 'bg-')}" 
                             style="width: ${Math.max(10, (item.diasRestantes / 30) * 100)}%"></div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="verDetalleProducto(${item.id})">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="accionesRotacion(${item.id})">
                            <i class="fas fa-sync"></i> Rotar
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="generarDescuento(${item.id})">
                            <i class="fas fa-percentage"></i> Descuento
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

// Acciones para productos críticos
function accionesCriticos() {
    Swal.fire({
        title: 'Productos Críticos',
        text: 'Se han identificado productos que vencen en menos de 7 días',
        icon: 'error',
        showCancelButton: true,
        confirmButtonText: 'Ver Detalles',
        cancelButtonText: 'Generar Plan de Acción'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#filtroPrioridad').val('critico');
            aplicarFiltrosPorVencer();
            // Scroll a la tabla
            document.getElementById('tablaProductosPorVencer').scrollIntoView({ behavior: 'smooth' });
        } else {
            generarPlanAccionCriticos();
        }
    });
}

// Generar plan de acción para críticos
function generarPlanAccionCriticos() {
    Swal.fire({
        title: 'Generando Plan de Acción',
        text: 'Creando plan de acción para productos críticos...',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Plan de Acción Generado',
            html: `
                <div class="text-left">
                    <h6>Acciones Recomendadas:</h6>
                    <ul>
                        <li><strong>Inmediata:</strong> Aplicar descuentos del 30-50%</li>
                        <li><strong>Promociones:</strong> Ofertas especiales por volumen</li>
                        <li><strong>Transferencia:</strong> Enviar a otras sucursales</li>
                        <li><strong>Alianzas:</strong> Contactar otros establecimientos</li>
                    </ul>
                    <h6>Productos Afectados:</h6>
                    <ul>
                        <li>Ibuprofeno 400mg - 45 unidades (S/ 22.50)</li>
                        <li>Paracetamol 500mg - 23 unidades (S/ 6.90)</li>
                        <li>Otros 10 productos...</li>
                    </ul>
                </div>
            `,
            icon: 'success',
            confirmButtonText: 'Implementar Plan'
        }).then(() => {
            // Aquí se ejecutaría la implementación del plan
            console.log('Implementando plan de acción para críticos');
        });
    });
}

// Ver detalle del producto
function verDetalleProducto(id) {
    const producto = datosProductosPorVencer.find(p => p.id === id);
    if (!producto) return;
    
    const contenido = `
        <div class="row">
            <div class="col-md-6">
                <h6>Información del Producto</h6>
                <table class="table table-sm">
                    <tr><td><strong>Código:</strong></td><td>${producto.codigo}</td></tr>
                    <tr><td><strong>Producto:</strong></td><td>${producto.producto}</td></tr>
                    <tr><td><strong>Lote:</strong></td><td>${producto.lote}</td></tr>
                    <tr><td><strong>Stock Actual:</strong></td><td>${producto.stock} unidades</td></tr>
                    <tr><td><strong>Precio Unitario:</strong></td><td>S/ ${producto.precio.toFixed(2)}</td></tr>
                    <tr><td><strong>Valor Total:</strong></td><td>S/ ${producto.valor.toFixed(2)}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Información de Vencimiento</h6>
                <table class="table table-sm">
                    <tr><td><strong>Fecha Vencimiento:</strong></td><td>${new Date(producto.fechaVencimiento).toLocaleDateString('es-ES')}</td></tr>
                    <tr><td><strong>Días Restantes:</strong></td><td><span class="badge bg-danger">${producto.diasRestantes} días</span></td></tr>
                    <tr><td><strong>Prioridad:</strong></td><td><span class="badge bg-danger">Crítico</span></td></tr>
                    <tr><td><strong>Categoría:</strong></td><td>${producto.categoria}</td></tr>
                    <tr><td><strong>Proveedor:</strong></td><td>${producto.proveedor}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Análisis de Riesgo</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-left-danger">
                            <div class="card-body text-center">
                                <h4 class="text-danger">Alto</h4>
                                <p class="mb-0">Nivel de Riesgo</p>
                                <small class="text-muted">Vence en 2 días</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-left-warning">
                            <div class="card-body text-center">
                                <h4 class="text-warning">S/ 22.50</h4>
                                <p class="mb-0">Pérdida Potencial</p>
                                <small class="text-muted">Si no se vende</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-left-success">
                            <div class="card-body text-center">
                                <h4 class="text-success">85%</h4>
                                <p class="mb-0">Velocidad de Rotación</p>
                                <small class="text-muted">Comparado con histórico</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contenidoDetalleProducto').innerHTML = contenido;
    $('#modalDetalleProducto').modal('show');
}

// Acciones de rotación
function accionesRotacion(id) {
    const producto = datosProductosPorVencer.find(p => p.id === id);
    if (!producto) return;
    
    Swal.fire({
        title: 'Acciones de Rotación',
        text: `¿Qué acción desea tomar para ${producto.producto}?`,
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Aplicar Descuento',
        denyButtonText: 'Transferir',
        cancelButtonText: 'Agendar Seguimiento'
    }).then((result) => {
        if (result.isConfirmed) {
            generarDescuento(id);
        } else if (result.isDenied) {
            transferirProducto(id);
        }
    });
}

// Generar descuento
function generarDescuento(id) {
    const producto = datosProductosPorVencer.find(p => p.id === id);
    if (!producto) return;
    
    Swal.fire({
        title: 'Generar Descuento',
        text: `Configurar descuento para ${producto.producto}`,
        icon: 'question',
        input: 'range',
        inputAttributes: {
            min: 10,
            max: 70,
            step: 5,
            value: 30
        },
        inputLabel: 'Porcentaje de Descuento',
        showCancelButton: true,
        confirmButtonText: 'Aplicar Descuento',
        cancelButtonText: 'Cancelar',
        preConfirm: (porcentaje) => {
            return Swal.fire({
                title: 'Confirmar Descuento',
                text: `¿Aplicar descuento del ${porcentaje}% a ${producto.producto}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, aplicar',
                cancelButtonText: 'No'
            }).then((confirmResult) => {
                if (confirmResult.isConfirmed) {
                    // Aquí se aplicaría el descuento
                    Swal.fire({
                        title: 'Descuento Aplicado',
                        text: `Se ha aplicado un descuento del ${porcentaje}%`,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }
    });
}

// Transferir producto
function transferirProducto(id) {
    const producto = datosProductosPorVencer.find(p => p.id === id);
    if (!producto) return;
    
    Swal.fire({
        title: 'Transferir Producto',
        html: `
            <div class="text-left">
                <p>Transferir <strong>${producto.producto}</strong> a otra sucursal:</p>
                <div class="mb-3">
                    <label class="form-label">Sucursal Destino:</label>
                    <select class="form-select" id="sucursalDestino">
                        <option value="">Seleccionar sucursal...</option>
                        <option value="sucursal_norte">Sucursal Norte</option>
                        <option value="sucursal_sur">Sucursal Sur</option>
                        <option value="sucursal_centro">Sucursal Centro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cantidad a Transferir:</label>
                    <input type="number" class="form-control" id="cantidadTransferir" value="${producto.stock}" max="${producto.stock}">
                    <small class="form-text text-muted">Stock disponible: ${producto.stock} unidades</small>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Transferir',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const sucursal = document.getElementById('sucursalDestino').value;
            const cantidad = document.getElementById('cantidadTransferir').value;
            
            if (!sucursal) {
                Swal.showValidationMessage('Debe seleccionar una sucursal destino');
                return false;
            }
            
            if (!cantidad || cantidad <= 0) {
                Swal.showValidationMessage('Debe especificar una cantidad válida');
                return false;
            }
            
            return { sucursal, cantidad };
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                title: 'Transferencia Iniciada',
                text: `Se ha iniciado la transferencia de ${result.value.cantidad} unidades a ${result.value.sucursal}`,
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

// Agendar revisión
function agendarRevision(id) {
    const producto = datosProductosPorVencer.find(p => p.id === id);
    if (!producto) return;
    
    Swal.fire({
        title: 'Agendar Revisión',
        html: `
            <div class="text-left">
                <p>Programar revisión para <strong>${producto.producto}</strong>:</p>
                <div class="mb-3">
                    <label class="form-label">Fecha de Revisión:</label>
                    <input type="datetime-local" class="form-control" id="fechaRevision">
                </div>
                <div class="mb-3">
                    <label class="form-label">Responsable:</label>
                    <select class="form-select" id="responsableRevision">
                        <option value="">Seleccionar responsable...</option>
                        <option value="ana_maria">Ana María (Supervisor)</option>
                        <option value="carlos_sanchez">Carlos Sánchez (Cajero)</option>
                        <option value="luis_rodriguez">Luis Rodríguez (Almacén)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de Revisión:</label>
                    <select class="form-select" id="tipoRevision">
                        <option value="rotacion">Revisión de Rotación</option>
                        <option value="descuento">Evaluación de Descuento</option>
                        <option value="transferencia">Evaluación de Transferencia</option>
                        <option value="vencimiento">Revisión de Vencimiento</option>
                    </select>
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Agendar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const fecha = document.getElementById('fechaRevision').value;
            const responsable = document.getElementById('responsableRevision').value;
            const tipo = document.getElementById('tipoRevision').value;
            
            if (!fecha || !responsable || !tipo) {
                Swal.showValidationMessage('Debe completar todos los campos');
                return false;
            }
            
            return { fecha, responsable, tipo };
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                title: 'Revisión Agendada',
                text: `Se ha programado la ${result.value.tipo} para el ${new Date(result.value.fecha).toLocaleString('es-ES')}`,
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

// Monitorear rotación
function monitorearRotacion(id) {
    Swal.fire({
        title: 'Monitoreo de Rotación',
        text: 'Activando monitoreo continuo para este producto...',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Monitoreo Activado',
            text: 'El producto ha sido agregado al monitoreo de rotación',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    });
}

// Optimizar rotación
function optimizarRotacion(id) {
    Swal.fire({
        title: 'Optimización de Rotación',
        text: 'Analizando oportunidades de optimización...',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Optimización Completada',
            html: `
                <div class="text-left">
                    <h6>Oportunidades Identificadas:</h6>
                    <ul>
                        <li>Reducir pedido futuro en 15%</li>
                        <li>Ajustar precio de venta en +5%</li>
                        <li>Mejorar visibilidad en exhibidor</li>
                        <li>Crear promoción específica</li>
                    </ul>
                    <p><strong>Impacto estimado:</strong> Reducción del 25% en riesgo de vencimiento</p>
                </div>
            `,
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    });
}

// Configurar seguimiento
function configurarSeguimiento(id) {
    Swal.fire({
        title: 'Configurar Seguimiento',
        text: 'Configurando alertas personalizadas para este producto...',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Seguimiento Configurado',
            text: 'Se han configurado alertas personalizadas para este producto',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    });
}

// Marcar como vencido
function marcarVencido(id) {
    const producto = datosProductosPorVencer.find(p => p.id === id);
    if (!producto) return;
    
    Swal.fire({
        title: 'Marcar como Vencido',
        text: `¿Confirmar que ${producto.producto} (${producto.stock} unidades) ha vencido?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, marcar como vencido',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Producto Vencido',
                text: `${producto.producto} ha sido marcado como vencido y enviado a descarte`,
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                // Aquí se actualizaría el estado del producto
                location.reload();
            });
        }
    });
}

// Generar alerta
function generarAlerta(id) {
    Swal.fire({
        title: 'Generar Alerta',
        text: 'Enviando alerta personalizada para este producto...',
        icon: 'info',
        timer: 1500,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Alerta Enviada',
            text: 'Se ha enviado una alerta personalizada al equipo responsable',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    });
}

// Generar plan de rotación
function generarPlanRotacion() {
    Swal.fire({
        title: 'Plan de Rotación',
        text: 'Generando plan completo de rotación para todos los productos...',
        icon: 'info',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Plan de Rotación Generado',
            html: `
                <div class="text-left">
                    <h6>Plan de Rotación Creado:</h6>
                    <ul>
                        <li><strong>Acción Inmediata:</strong> 12 productos (Críticos)</li>
                        <li><strong>Promociones:</strong> 28 productos (Alertas)</li>
                        <li><strong>Monitoreo:</strong> 45 productos (Advertencias)</li>
                    </ul>
                    <h6>Acciones Recomendadas:</h6>
                    <ul>
                        <li>Descuentos progresivos según proximidad</li>
                        <li>Promociones especiales por categoría</li>
                        <li>Transferencias inter-sucursales</li>
                        <li>Alianzas con otros establecimientos</li>
                    </ul>
                </div>
            `,
            icon: 'success',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            // Aquí se descargaría el plan completo
            window.open('/reportes/plan-rotacion-completo.pdf', '_blank');
        });
    });
}

// Iniciar rotación
function iniciarRotacion() {
    Swal.fire({
        title: 'Iniciar Rotación Masiva',
        text: 'Esta acción iniciará la rotación automática de todos los productos en riesgo',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, iniciar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Rotación Iniciada',
                text: 'Se ha iniciado el proceso de rotación masiva',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

// Generar reportes
function generarReportes() {
    Swal.fire({
        title: 'Generar Reportes',
        text: 'Seleccione el tipo de reporte a generar',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Reporte Ejecutivo',
        denyButtonText: 'Reporte Detallado',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('/reportes/productos-vencer-ejecutivo', '_blank');
        } else if (result.isDenied) {
            window.open('/reportes/productos-vencer-detallado', '_blank');
        }
    });
}

// Configurar monitoreo
function configurarMonitoreo() {
    $('#modalConfiguracionAlertas').modal('show');
}

// Exportar por vencer
function exportarPorVencer() {
    Swal.fire({
        title: 'Exportar Productos por Vencer',
        text: 'Seleccione el formato de exportación',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Excel',
        denyButtonText: 'PDF',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('/export/productos-vencer/excel', '_blank');
        } else if (result.isDenied) {
            window.open('/export/productos-vencer/pdf', '_blank');
        }
    });
}

// Actualizar gráfico
function actualizarGraficoPorVencer() {
    location.reload();
}

// Exportar gráfico
function exportarGraficoPorVencer() {
    Swal.fire({
        title: 'Gráfico Exportado',
        text: 'El gráfico se ha exportado exitosamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    });
}

// Probar alertas
function probarAlertas() {
    Swal.fire({
        title: 'Probando Alertas',
        text: 'Enviando notificación de prueba...',
        icon: 'info',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Prueba Exitosa',
            text: 'Se han enviado alertas de prueba a todos los destinatarios',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    });
}

// Generar plan individual
function generarPlanIndividual() {
    window.open('/reportes/plan-individual', '_blank');
}

// Generar reporte del producto
function generarReporteProducto() {
    window.open('/reportes/producto-individual', '_blank');
}

// Acción inmediata
function accionInmediata(codigo) {
    Swal.fire({
        title: 'Acción Inmediata Requerida',
        text: `El producto ${codigo} requiere acción inmediata`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonText: 'Aplicar Descuento del 50%',
        cancelButtonText: 'Transferir Urgente'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Descuento Aplicado',
                text: `Se ha aplicado un descuento del 50% al producto ${codigo}`,
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        } else {
            transferirProducto(codigo);
        }
    });
}

// Agendar acción
function agendarAccion(codigo) {
    agendarRevision(codigo);
}

// Manejar formulario de configuración
document.getElementById('formConfiguracionAlertas').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const configuracion = {
        umbralCritico: document.getElementById('umbralCritico').value,
        umbralAlerta: document.getElementById('umbralAlerta').value,
        umbralAdvertencia: document.getElementById('umbralAdvertencia').value,
        notificacionEmail: document.getElementById('notificacionEmail').checked,
        notificacionSMS: document.getElementById('notificacionSMS').checked,
        notificacionDashboard: document.getElementById('notificacionDashboard').checked,
        frecuenciaRevision: document.getElementById('frecuenciaRevision').value,
        emailDestinatarios: document.getElementById('emailDestinatarios').value,
        telefonosDestinatarios: document.getElementById('telefonosDestinatarios').value
    };
    
    // Aquí se guardarían los datos de configuración
    console.log('Configuración guardada:', configuracion);
    
    Swal.fire({
        title: 'Configuración Guardada',
        text: 'La configuración de alertas se ha guardado exitosamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        $('#modalConfiguracionAlertas').modal('hide');
        this.reset();
    });
});
</script>
@endsection