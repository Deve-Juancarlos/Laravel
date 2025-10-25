@extends('layouts.app')

@section('title', 'Control de Lotes - SIFANO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tags text-warning"></i>
                Control de Lotes
            </h1>
            <p class="text-muted">Gestión y seguimiento detallado de lotes por producto farmacéutico</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportarLotes()">
                <i class="fas fa-file-export"></i> Exportar
            </button>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalNuevoLote">
                <i class="fas fa-plus"></i> Nuevo Lote
            </button>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Lotes Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="lotesActivos">1,247</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
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
                                Lotes Próximos a Vencer
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="proximosVencer">23</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                Lotes Vencidos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="lotesVencidos">12</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
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
                                Valor Total Lotes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="valorTotalLotes">S/ 847,200</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas de Vencimiento -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4 border-left-danger">
                <div class="card-header py-3 bg-danger">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-exclamation-triangle"></i> Alertas de Vencimiento
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Lotes por Vencer:</strong> Hay 23 lotes que vencen en los próximos 30 días. Revise el inventario para rotación de productos.
                    </div>
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Lotes Vencidos:</strong> Se encontraron 12 lotes vencidos que requieren manejo inmediato.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filtros de Búsqueda
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="filtroProducto" class="form-label">Producto</label>
                    <select class="form-select" id="filtroProducto">
                        <option value="">Todos los productos</option>
                        <option value="ibuprofeno">Ibuprofeno 400mg</option>
                        <option value="paracetamol">Paracetamol 500mg</option>
                        <option value="amoxicilina">Amoxicilina 500mg</option>
                        <option value="loratadina">Loratadina 10mg</option>
                        <option value="insulina">Insulina NPH</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroEstado" class="form-label">Estado Vencimiento</label>
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="vigente">Vigente</option>
                        <option value="proximo">Próximo a Vencer</option>
                        <option value="vencido">Vencido</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroProveedor" class="form-label">Proveedor</label>
                    <select class="form-select" id="filtroProveedor">
                        <option value="">Todos los proveedores</option>
                        <option value="pfizer">Pfizer S.A.</option>
                        <option value="novartis">Novartis Perú S.A.</option>
                        <option value="roche">Roche S.A.</option>
                        <option value="merck">Merck S.A.</option>
                        <option value="bayer">Bayer S.A.</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="busqueda" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="busqueda" placeholder="Código, lote, producto...">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-4">
                    <label for="filtroFechaDesde" class="form-label">Fecha Vencimiento Desde</label>
                    <input type="date" class="form-control" id="filtroFechaDesde">
                </div>
                <div class="col-md-4">
                    <label for="filtroFechaHasta" class="form-label">Fecha Vencimiento Hasta</label>
                    <input type="date" class="form-control" id="filtroFechaHasta">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div>
                        <button class="btn btn-primary me-2" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <button class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-undo"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Lotes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Registro de Lotes
            </h6>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary active" onclick="mostrarVista('tabla')">
                    <i class="fas fa-list"></i> Tabla
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="mostrarVista('grid')">
                    <i class="fas fa-th"></i> Grid
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="mostrarVista('calendario')">
                    <i class="fas fa-calendar"></i> Calendario
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="vistaTabla" class="table-responsive">
                <table class="table table-bordered table-striped" id="tablaLotes" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Código Lote</th>
                            <th>Producto</th>
                            <th>Proveedor</th>
                            <th>Cantidad</th>
                            <th>Stock Actual</th>
                            <th>Fecha Ingreso</th>
                            <th>Fecha Vencimiento</th>
                            <th>Días Restantes</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-lote="1">
                            <td><strong>L240312A</strong></td>
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
                            <td class="text-center">
                                <span class="fw-bold">1,000</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: 85%"></div>
                                    </div>
                                    <span class="fw-bold">850</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">12/03/2024</span>
                            </td>
                            <td>
                                <span class="badge bg-success">15/08/2026</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">660 días</span>
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Vigente
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verLote(1)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editarLote(1)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="historialLote(1)" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="generarQR(1)">Generar QR</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="rotarLote(1)">Rotar Stock</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="desactivarLote(1)">Desactivar</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr data-lote="2">
                            <td><strong>L240325B</strong></td>
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
                            <td class="text-center">
                                <span class="fw-bold">500</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: 5%"></div>
                                    </div>
                                    <span class="fw-bold text-warning">25</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">25/03/2024</span>
                            </td>
                            <td>
                                <span class="badge bg-warning">20/06/2026</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning">239 días</span>
                            </td>
                            <td>
                                <span class="badge bg-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Próximo a Vencer
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verLote(2)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editarLote(2)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="historialLote(2)" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="marcarVencido(2)" title="Marcar como Vencido">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-lote="3">
                            <td><strong>L240401C</strong></td>
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
                            <td class="text-center">
                                <span class="fw-bold">200</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                        <div class="progress-bar bg-danger" style="width: 4%"></div>
                                    </div>
                                    <span class="fw-bold text-danger">8</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">01/04/2024</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">31/12/2024</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">-67 días</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">
                                    <i class="fas fa-ban"></i> Vencido
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verLote(3)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editarLote(3)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="historialLote(3)" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="descarteLote(3)" title="Enviar a Descarte">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Más lotes se cargarían dinámicamente -->
                    </tbody>
                </table>
            </div>

            <div id="vistaGrid" class="row d-none">
                <!-- Vista en Grid se llenará dinámicamente -->
            </div>

            <div id="vistaCalendario" class="d-none">
                <div class="row">
                    <div class="col-12">
                        <div id="calendarioVencimientos" class="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Lotes -->
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
                            <a class="dropdown-item" href="#" onclick="actualizarGrafico()">Actualizar</a>
                            <a class="dropdown-item" href="#" onclick="exportarGrafico()">Exportar</a>
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
                        <i class="fas fa-chart-pie"></i> Estados de Lotes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="graficoEstados"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lotes por Proveedor -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building"></i> Resumen por Proveedor
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Total Lotes</th>
                                    <th>Productos</th>
                                    <th>Valor Total</th>
                                    <th>Lotes Próximos</th>
                                    <th>Lotes Vencidos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building text-info me-2"></i>
                                            <div>
                                                <div class="fw-bold">Pfizer S.A.</div>
                                                <small class="text-muted">RUC: 20100130204</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-primary">15</span></td>
                                    <td>Ibuprofeno, Aspirina, Dolorflex</td>
                                    <td class="text-end fw-bold">S/ 125,400</td>
                                    <td class="text-center"><span class="badge bg-warning">3</span></td>
                                    <td class="text-center"><span class="badge bg-danger">1</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="verProveedor('pfizer')">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building text-info me-2"></i>
                                            <div>
                                                <div class="fw-bold">Novartis Perú S.A.</div>
                                                <small class="text-muted">RUC: 20100234567</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-primary">12</span></td>
                                    <td>Paracetamol, Amoxicilina, Loratadina</td>
                                    <td class="text-end fw-bold">S/ 89,600</td>
                                    <td class="text-center"><span class="badge bg-warning">2</span></td>
                                    <td class="text-center"><span class="badge bg-danger">0</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="verProveedor('novartis')">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building text-info me-2"></i>
                                            <div>
                                                <div class="fw-bold">Roche S.A.</div>
                                                <small class="text-muted">RUC: 20100345678</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-primary">8</span></td>
                                    <td>Insulina, Jeringas, Vacunas</td>
                                    <td class="text-end fw-bold">S/ 67,800</td>
                                    <td class="text-center"><span class="badge bg-warning">1</span></td>
                                    <td class="text-center"><span class="badge bg-danger">2</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="verProveedor('roche')">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Lote -->
<div class="modal fade" id="modalNuevoLote" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus text-warning"></i> Nuevo Lote
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNuevoLote">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="codigoLote" class="form-label">Código de Lote</label>
                                <input type="text" class="form-control" id="codigoLote" required>
                                <small class="form-text text-muted">Formato: YYMMDD + Código producto</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="producto" class="form-label">Producto</label>
                                <select class="form-select" id="producto" required onchange="cargarDatosProducto()">
                                    <option value="">Seleccionar producto...</option>
                                    <option value="med001" data-precio="0.50" data-categoria="Medicamentos">Ibuprofeno 400mg</option>
                                    <option value="med002" data-precio="0.30" data-categoria="Medicamentos">Paracetamol 500mg</option>
                                    <option value="dis001" data-precio="0.80" data-categoria="Dispositivos">Jeringa 5ml Estéril</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="proveedor" class="form-label">Proveedor</label>
                                <select class="form-select" id="proveedor" required>
                                    <option value="">Seleccionar proveedor...</option>
                                    <option value="pfizer">Pfizer S.A.</option>
                                    <option value="novartis">Novartis Perú S.A.</option>
                                    <option value="roche">Roche S.A.</option>
                                    <option value="merck">Merck S.A.</option>
                                    <option value="bayer">Bayer S.A.</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="factura" class="form-label">N° Factura</label>
                                <input type="text" class="form-control" id="factura" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cantidad" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="cantidad" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="precio" class="form-label">Precio Unitario</label>
                                <input type="number" class="form-control" id="precio" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <input type="text" class="form-control" id="categoria" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fechaIngreso" class="form-label">Fecha de Ingreso</label>
                                <input type="date" class="form-control" id="fechaIngreso" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fechaVencimiento" class="form-label">Fecha de Vencimiento</label>
                                <input type="date" class="form-control" id="fechaVencimiento" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" rows="3" placeholder="Observaciones adicionales sobre el lote"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Información:</strong> Los lotes vencidos serán automáticamente bloqueados para la venta.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Crear Lote
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Lote -->
<div class="modal fade" id="modalVerLote" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalles del Lote
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoVerLote">
                <!-- Se carga dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="editarLote()">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button type="button" class="btn btn-success" onclick="generarQR()">
                    <i class="fas fa-qrcode"></i> Generar QR
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Variables globales
let tablaLotes;
let datosLotes = [];

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    inicializarTabla();
    inicializarGraficos();
    cargarDatos();
    establecerFechaIngreso();
});

// Establecer fecha de ingreso actual
function establecerFechaIngreso() {
    document.getElementById('fechaIngreso').value = new Date().toISOString().split('T')[0];
}

// Inicializar DataTable
function inicializarTabla() {
    tablaLotes = $('#tablaLotes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        pageLength: 25,
        responsive: true,
        order: [[7, 'asc']], // Ordenar por días restantes
        columnDefs: [
            {
                targets: [3, 4, 7],
                className: 'text-center'
            },
            {
                targets: [9],
                className: 'text-center',
                orderable: false
            }
        ],
        createdRow: function(row, data, dataIndex) {
            // Aplicar clases según estado del lote
            const diasRestantes = parseInt(data[7]);
            const stockActual = parseInt(data[4]);
            
            if (diasRestantes < 0) {
                $(row).addClass('table-danger');
            } else if (diasRestantes <= 30) {
                $(row).addClass('table-warning');
            } else if (stockActual === 0) {
                $(row).addClass('table-secondary');
            }
        }
    });

    // Búsqueda global
    $('#busqueda').on('keyup', function() {
        tablaLotes.search($(this).val()).draw();
    });

    // Filtros
    $('#filtroProducto, #filtroEstado, #filtroProveedor, #filtroFechaDesde, #filtroFechaHasta').on('change', aplicarFiltros);
}

// Inicializar gráficos
function inicializarGraficos() {
    // Gráfico de vencimientos
    const ctx1 = document.getElementById('graficoVencimientos').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: ['Vencidos', '0-30 días', '31-90 días', '91-180 días', '181-365 días', 'Más de 365 días'],
            datasets: [{
                label: 'Número de Lotes',
                data: [12, 23, 45, 87, 156, 924],
                backgroundColor: [
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ],
                borderColor: [
                    'rgba(231, 74, 59, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(78, 115, 223, 1)',
                    'rgba(28, 200, 138, 1)',
                    'rgba(102, 126, 234, 1)',
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
                        text: 'Número de Lotes'
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
                            return 'Lotes: ' + context.parsed.y;
                        }
                    }
                }
            }
        }
    });

    // Gráfico de estados
    const ctx2 = document.getElementById('graficoEstados').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Vigentes', 'Próximos a Vencer', 'Vencidos'],
            datasets: [{
                data: [1212, 23, 12],
                backgroundColor: [
                    'rgb(28, 200, 138)',
                    'rgb(255, 193, 7)',
                    'rgb(231, 74, 59)'
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
    datosLotes = [
        {
            id: 1,
            codigo: 'L240312A',
            producto: 'Ibuprofeno 400mg',
            proveedor: 'Pfizer S.A.',
            cantidad: 1000,
            stock: 850,
            precio: 0.50,
            fechaIngreso: '2024-03-12',
            fechaVencimiento: '2026-08-15',
            diasRestantes: 660,
            estado: 'vigente'
        },
        {
            id: 2,
            codigo: 'L240325B',
            producto: 'Paracetamol 500mg',
            proveedor: 'Novartis Perú S.A.',
            cantidad: 500,
            stock: 25,
            precio: 0.30,
            fechaIngreso: '2024-03-25',
            fechaVencimiento: '2026-06-20',
            diasRestantes: 239,
            estado: 'proximo'
        },
        {
            id: 3,
            codigo: 'L240401C',
            producto: 'Jeringa 5ml Estéril',
            proveedor: 'Roche S.A.',
            cantidad: 200,
            stock: 8,
            precio: 0.80,
            fechaIngreso: '2024-04-01',
            fechaVencimiento: '2024-12-31',
            diasRestantes: -67,
            estado: 'vencido'
        }
    ];
    
    actualizarEstadisticas();
}

// Actualizar estadísticas
function actualizarEstadisticas() {
    const lotesVigentes = datosLotes.filter(lote => lote.diasRestantes > 30).length;
    const lotesProximos = datosLotes.filter(lote => lote.diasRestantes >= 0 && lote.diasRestantes <= 30).length;
    const lotesVencidos = datosLotes.filter(lote => lote.diasRestantes < 0).length;
    
    document.getElementById('lotesActivos').textContent = datosLotes.length.toLocaleString();
    document.getElementById('proximosVencer').textContent = lotesProximos;
    document.getElementById('lotesVencidos').textContent = lotesVencidos;
    
    const valorTotal = datosLotes.reduce((sum, lote) => sum + (lote.stock * lote.precio), 0);
    document.getElementById('valorTotalLotes').textContent = 'S/ ' + valorTotal.toLocaleString();
}

// Aplicar filtros
function aplicarFiltros() {
    const producto = $('#filtroProducto').val();
    const estado = $('#filtroEstado').val();
    const proveedor = $('#filtroProveedor').val();
    const fechaDesde = $('#filtroFechaDesde').val();
    const fechaHasta = $('#filtroFechaHasta').val();
    
    tablaLotes.clear().rows.add(filtrarDatos(producto, estado, proveedor, fechaDesde, fechaHasta)).draw();
}

// Filtrar datos
function filtrarDatos(producto, estado, proveedor, fechaDesde, fechaHasta) {
    let datos = datosLotes;
    
    if (producto) {
        datos = datos.filter(item => item.producto.toLowerCase().includes(producto));
    }
    
    if (estado) {
        datos = datos.filter(item => item.estado === estado);
    }
    
    if (proveedor) {
        datos = datos.filter(item => item.proveedor.toLowerCase().includes(proveedor));
    }
    
    if (fechaDesde) {
        datos = datos.filter(item => item.fechaVencimiento >= fechaDesde);
    }
    
    if (fechaHasta) {
        datos = datos.filter(item => item.fechaVencimiento <= fechaHasta);
    }
    
    return datos.map(item => [
        `<strong>${item.codigo}</strong>`,
        obtenerInfoProducto(item),
        obtenerInfoProveedor(item.proveedor),
        `<span class="text-center fw-bold">${item.cantidad}</span>`,
        obtenerInfoStock(item),
        formatearFecha(item.fechaIngreso),
        formatearFecha(item.fechaVencimiento),
        obtenerDiasRestantes(item.diasRestantes),
        obtenerBadgeEstado(item.estado, item.diasRestantes),
        generarBotonesAccion(item.id)
    ]);
}

// Obtener información del producto
function obtenerInfoProducto(item) {
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
function obtenerInfoProveedor(proveedor) {
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

// Obtener información del stock
function obtenerInfoStock(item) {
    const porcentaje = (item.stock / item.cantidad) * 100;
    const color = porcentaje > 50 ? 'success' : porcentaje > 20 ? 'warning' : 'danger';
    
    return `
        <div class="d-flex align-items-center">
            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                <div class="progress-bar bg-${color}" style="width: ${porcentaje}%"></div>
            </div>
            <span class="fw-bold">${item.stock}</span>
        </div>
    `;
}

// Formatear fecha
function formatearFecha(fecha) {
    return `<span class="badge bg-light text-dark">${new Date(fecha).toLocaleDateString('es-ES')}</span>`;
}

// Obtener días restantes
function obtenerDiasRestantes(dias) {
    const color = dias < 0 ? 'danger' : dias <= 30 ? 'warning' : 'success';
    const texto = dias < 0 ? `${Math.abs(dias)} días` : `${dias} días`;
    return `<span class="badge bg-${color}">${texto}</span>`;
}

// Obtener badge de estado
function obtenerBadgeEstado(estado, diasRestantes) {
    let badge = '';
    
    if (diasRestantes < 0) {
        badge = '<span class="badge bg-danger"><i class="fas fa-ban"></i> Vencido</span>';
    } else if (diasRestantes <= 30) {
        badge = '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle"></i> Próximo a Vencer</span>';
    } else {
        badge = '<span class="badge bg-success"><i class="fas fa-check"></i> Vigente</span>';
    }
    
    return badge;
}

// Generar botones de acción
function generarBotonesAccion(id) {
    return `
        <div class="btn-group" role="group">
            <button class="btn btn-sm btn-outline-primary" onclick="verLote(${id})" title="Ver Detalles">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="editarLote(${id})" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="historialLote(${id})" title="Historial">
                <i class="fas fa-history"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="generarQR(${id})">Generar QR</a></li>
                    <li><a class="dropdown-item" href="#" onclick="rotarLote(${id})">Rotar Stock</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="desactivarLote(${id})">Desactivar</a></li>
                </ul>
            </div>
        </div>
    `;
}

// Limpiar filtros
function limpiarFiltros() {
    $('#filtroProducto, #filtroEstado, #filtroProveedor, #filtroFechaDesde, #filtroFechaHasta').val('');
    $('#busqueda').val('');
    tablaLotes.search('').columns().search('').draw();
}

// Mostrar vista (tabla/grid/calendario)
function mostrarVista(vista) {
    // Ocultar todas las vistas
    $('#vistaTabla, #vistaGrid, #vistaCalendario').addClass('d-none');
    $('.btn-group .btn').removeClass('active');
    
    if (vista === 'tabla') {
        $('#vistaTabla').removeClass('d-none');
        $(`.btn-group .btn:eq(0)`).addClass('active');
    } else if (vista === 'grid') {
        $('#vistaGrid').removeClass('d-none');
        $(`.btn-group .btn:eq(1)`).addClass('active');
        cargarVistaGrid();
    } else if (vista === 'calendario') {
        $('#vistaCalendario').removeClass('d-none');
        $(`.btn-group .btn:eq(2)`).addClass('active');
        cargarVistaCalendario();
    }
}

// Cargar vista en grid
function cargarVistaGrid() {
    const grid = document.getElementById('vistaGrid');
    grid.innerHTML = '';
    
    datosLotes.forEach(lote => {
        const card = document.createElement('div');
        card.className = 'col-xl-3 col-lg-4 col-md-6 mb-4';
        
        const diasClase = lote.diasRestantes < 0 ? 'border-danger' : lote.diasRestantes <= 30 ? 'border-warning' : 'border-success';
        const badgeClase = lote.diasRestantes < 0 ? 'bg-danger' : lote.diasRestantes <= 30 ? 'bg-warning' : 'bg-success';
        const badgeTexto = lote.diasRestantes < 0 ? 'Vencido' : lote.diasRestantes <= 30 ? 'Próximo' : 'Vigente';
        
        card.innerHTML = `
            <div class="card h-100 ${diasClase}">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">${lote.codigo}</h6>
                    <span class="badge ${badgeClase}">${badgeTexto}</span>
                </div>
                <div class="card-body">
                    <h6 class="card-title">${lote.producto}</h6>
                    <p class="card-text">
                        <small class="text-muted">Proveedor: ${lote.proveedor}</small>
                    </p>
                    <div class="row text-center mb-2">
                        <div class="col">
                            <div class="text-sm font-weight-bold text-primary">${lote.stock}/${lote.cantidad}</div>
                            <div class="text-xs text-muted">Stock</div>
                        </div>
                        <div class="col">
                            <div class="text-sm font-weight-bold text-success">S/ ${(lote.stock * lote.precio).toFixed(2)}</div>
                            <div class="text-xs text-muted">Valor</div>
                        </div>
                    </div>
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar ${obtenerColorBarra(lote.diasRestantes)}" 
                             style="width: ${Math.max(5, (lote.stock / lote.cantidad) * 100)}%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> ${new Date(lote.fechaVencimiento).toLocaleDateString('es-ES')}
                        </small>
                        <small class="text-${lote.diasRestantes < 0 ? 'danger' : lote.diasRestantes <= 30 ? 'warning' : 'success'}">
                            ${lote.diasRestantes > 0 ? lote.diasRestantes : Math.abs(lote.diasRestantes)} días
                        </small>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="verLote(${lote.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="historialLote(${lote.id})">
                            <i class="fas fa-history"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="editarLote(${lote.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        grid.appendChild(card);
    });
}

// Obtener color de barra de progreso
function obtenerColorBarra(diasRestantes) {
    if (diasRestantes < 0) return 'bg-danger';
    if (diasRestantes <= 30) return 'bg-warning';
    if (diasRestantes <= 90) return 'bg-info';
    return 'bg-success';
}

// Cargar vista de calendario
function cargarVistaCalendario() {
    const calendario = document.getElementById('calendarioVencimientos');
    calendario.innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-calendar"></i>
            <strong>Vista de Calendario:</strong> Esta vista mostraría los vencimientos de lotes en un calendario interactivo.
            Los lotes se mostrarían con diferentes colores según su proximidad al vencimiento.
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card border-left-danger">
                    <div class="card-body">
                        <h6 class="text-danger">Vencidos (< 0 días)</h6>
                        <div class="text-2xl fw-bold text-danger">12</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-warning">
                    <div class="card-body">
                        <h6 class="text-warning">Próximos (0-30 días)</h6>
                        <div class="text-2xl fw-bold text-warning">23</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-success">
                    <div class="card-body">
                        <h6 class="text-success">Vigentes (> 30 días)</h6>
                        <div class="text-2xl fw-bold text-success">1,212</div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Cargar datos del producto
function cargarDatosProducto() {
    const select = document.getElementById('producto');
    const option = select.options[select.selectedIndex];
    
    if (option && option.value) {
        const precio = option.dataset.precio;
        const categoria = option.dataset.categoria;
        
        document.getElementById('precio').value = precio;
        document.getElementById('categoria').value = categoria;
        
        // Generar código de lote automáticamente
        const fecha = new Date();
        const fechaCodigo = fecha.getFullYear().toString().slice(-2) + 
                           String(fecha.getMonth() + 1).padStart(2, '0') + 
                           String(fecha.getDate()).padStart(2, '0');
        const codigoProducto = option.value.toUpperCase();
        document.getElementById('codigoLote').value = fechaCodigo + codigoProducto.replace('MED', '').replace('DIS', '');
    }
}

// Ver lote
function verLote(id) {
    const lote = datosLotes.find(l => l.id === id);
    if (!lote) return;
    
    const contenido = `
        <div class="row">
            <div class="col-md-6">
                <h6>Información del Lote</h6>
                <table class="table table-sm">
                    <tr><td><strong>Código:</strong></td><td>${lote.codigo}</td></tr>
                    <tr><td><strong>Producto:</strong></td><td>${lote.producto}</td></tr>
                    <tr><td><strong>Proveedor:</strong></td><td>${lote.proveedor}</td></tr>
                    <tr><td><strong>Cantidad Inicial:</strong></td><td>${lote.cantidad} unidades</td></tr>
                    <tr><td><strong>Stock Actual:</strong></td><td>${lote.stock} unidades</td></tr>
                    <tr><td><strong>Consumido:</strong></td><td>${lote.cantidad - lote.stock} unidades (${((lote.cantidad - lote.stock) / lote.cantidad * 100).toFixed(1)}%)</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Fechas y Estado</h6>
                <table class="table table-sm">
                    <tr><td><strong>Fecha Ingreso:</strong></td><td>${new Date(lote.fechaIngreso).toLocaleDateString('es-ES')}</td></tr>
                    <tr><td><strong>Fecha Vencimiento:</strong></td><td>${new Date(lote.fechaVencimiento).toLocaleDateString('es-ES')}</td></tr>
                    <tr><td><strong>Días Restantes:</strong></td><td><span class="${lote.diasRestantes < 0 ? 'text-danger' : lote.diasRestantes <= 30 ? 'text-warning' : 'text-success'} fw-bold">${lote.diasRestantes > 0 ? lote.diasRestantes : Math.abs(lote.diasRestantes)} días</span></td></tr>
                    <tr><td><strong>Estado:</strong></td><td>${obtenerBadgeEstado(lote.estado, lote.diasRestantes)}</td></tr>
                    <tr><td><strong>Valor Total:</strong></td><td>S/ ${(lote.stock * lote.precio).toFixed(2)}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Movimientos del Lote</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Stock Restante</th>
                                <th>Usuario</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>${new Date().toLocaleDateString('es-ES')}</td>
                                <td><span class="badge bg-success">Entrada</span></td>
                                <td>+${lote.cantidad}</td>
                                <td>${lote.cantidad}</td>
                                <td>Admin</td>
                                <td>Ingreso inicial del lote</td>
                            </tr>
                            <tr>
                                <td>${new Date(Date.now() - 86400000).toLocaleDateString('es-ES')}</td>
                                <td><span class="badge bg-danger">Salida</span></td>
                                <td>-${lote.cantidad - lote.stock}</td>
                                <td>${lote.stock}</td>
                                <td>Vendedor</td>
                                <td>Ventas varias</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contenidoVerLote').innerHTML = contenido;
    $('#modalVerLote').modal('show');
}

// Editar lote
function editarLote(id) {
    Swal.fire({
        title: 'Editar Lote',
        text: 'Función de edición en desarrollo',
        icon: 'info',
        confirmButtonText: 'Aceptar'
    });
}

// Historial del lote
function historialLote(id) {
    window.open(`/farmacia/control-lotes/historial/${id}`, '_blank');
}

// Generar QR
function generarQR(id) {
    const lote = datosLotes.find(l => l.id === id);
    if (!lote) return;
    
    Swal.fire({
        title: 'Generar Código QR',
        text: `Generar código QR para el lote ${lote.codigo}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Generar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Simular generación de QR
            const qrData = `LOTE:${lote.codigo}|PRODUTO:${lote.producto}|VENC:${lote.fechaVencimiento}`;
            Swal.fire({
                title: 'QR Generado',
                text: `Código QR generado para ${lote.codigo}. Escanee para verificar el lote.`,
                icon: 'success',
                confirmButtonText: 'Aceptar',
                footer: `Datos: ${qrData}`
            });
        }
    });
}

// Rotar stock
function rotarLote(id) {
    Swal.fire({
        title: 'Rotar Stock',
        text: '¿Desea priorizar este lote para la rotación de stock?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, rotar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Stock Rotado',
                text: 'El lote ha sido marcado para rotación prioritaria',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

// Desactivar lote
function desactivarLote(id) {
    const lote = datosLotes.find(l => l.id === id);
    if (!lote) return;
    
    Swal.fire({
        title: 'Desactivar Lote',
        text: `¿Está seguro de desactivar el lote ${lote.codigo}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Lote Desactivado',
                text: `El lote ${lote.codigo} ha sido desactivado`,
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Marcar como vencido
function marcarVencido(id) {
    const lote = datosLotes.find(l => l.id === id);
    if (!lote) return;
    
    Swal.fire({
        title: 'Marcar como Vencido',
        text: `¿Marcar el lote ${lote.codigo} como vencido manualmente?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, marcar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Lote Marcado',
                text: `El lote ${lote.codigo} ha sido marcado como vencido`,
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Enviar a descarte
function descarteLote(id) {
    const lote = datosLotes.find(l => l.id === id);
    if (!lote) return;
    
    Swal.fire({
        title: 'Enviar a Descarte',
        text: `¿Enviar el lote ${lote.codigo} al proceso de descarte?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, descartar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Enviado a Descarte',
                text: `El lote ${lote.codigo} ha sido enviado al proceso de descarte`,
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Ver proveedor
function verProveedor(proveedor) {
    window.open(`/farmacia/control-lotes/proveedor/${proveedor}`, '_blank');
}

// Exportar lotes
function exportarLotes() {
    Swal.fire({
        title: 'Exportar Lotes',
        text: 'Seleccione el formato de exportación',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Excel',
        denyButtonText: 'PDF',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('/export/lotes/excel', '_blank');
        } else if (result.isDenied) {
            window.open('/export/lotes/pdf', '_blank');
        }
    });
}

// Actualizar gráfico
function actualizarGrafico() {
    location.reload();
}

// Exportar gráfico
function exportarGrafico() {
    Swal.fire({
        title: 'Gráfico Exportado',
        text: 'El gráfico se ha exportado exitosamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    });
}

// Manejar formulario nuevo lote
document.getElementById('formNuevoLote').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const datos = {
        codigoLote: document.getElementById('codigoLote').value,
        producto: document.getElementById('producto').value,
        proveedor: document.getElementById('proveedor').value,
        factura: document.getElementById('factura').value,
        cantidad: document.getElementById('cantidad').value,
        precio: document.getElementById('precio').value,
        fechaIngreso: document.getElementById('fechaIngreso').value,
        fechaVencimiento: document.getElementById('fechaVencimiento').value,
        observaciones: document.getElementById('observaciones').value
    };
    
    // Validar fechas
    if (new Date(datos.fechaVencimiento) <= new Date(datos.fechaIngreso)) {
        Swal.fire({
            title: 'Error',
            text: 'La fecha de vencimiento debe ser posterior a la fecha de ingreso',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    
    // Aquí se enviarían los datos al servidor
    console.log('Nuevo lote:', datos);
    
    Swal.fire({
        title: 'Lote Creado',
        text: 'El lote se ha creado exitosamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        $('#modalNuevoLote').modal('hide');
        this.reset();
        // Recargar datos
        location.reload();
    });
});
</script>
@endsection