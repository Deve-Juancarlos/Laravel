@extends('layouts.app')

@section('title', 'Entrada de Lotes - SIFANO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-arrow-down text-success"></i>
                Entrada de Lotes
            </h1>
            <p class="text-muted">Registro y validación de nuevos lotes de productos farmacéuticos</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportarEntradas()">
                <i class="fas fa-file-export"></i> Exportar
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaEntradaLote">
                <i class="fas fa-plus"></i> Nueva Entrada
            </button>
        </div>
    </div>

    <!-- Estadísticas del Día -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Entradas Hoy
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="entradasHoy">5</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Lotes Ingresados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="lotesIngresados">23</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
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
                                Valor Total
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="valorTotal">S/ 45,680</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                Pendientes Validación
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pendientesValidacion">2</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas de Validación -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-header py-3 bg-warning">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-exclamation-triangle"></i> Alertas de Validación
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-2">
                        <i class="fas fa-clock"></i>
                        <strong>Lotes Pendientes:</strong> Hay 2 entradas que requieren validación antes de ser procesadas.
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i>
                        <strong>Importante:</strong> Todos los lotes deben ser validados antes de ingresar al inventario.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filtros de Búsqueda
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="filtroFecha" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="filtroFecha">
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
                    <label for="filtroEstado" class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="validando">Validando</option>
                        <option value="validado">Validado</option>
                        <option value="rechazado">Rechazado</option>
                        <option value="procesado">Procesado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="busqueda" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="busqueda" placeholder="Número, lote, producto...">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label for="filtroTipoProducto" class="form-label">Tipo de Producto</label>
                    <select class="form-select" id="filtroTipoProducto">
                        <option value="">Todos</option>
                        <option value="medicamentos">Medicamentos</option>
                        <option value="dispositivos">Dispositivos Médicos</option>
                        <option value="suplementos">Suplementos</option>
                        <option value="cosméticos">Cosméticos</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
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

    <!-- Lista de Entradas -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Registro de Entradas de Lotes
            </h6>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary active" onclick="mostrarVista('lista')">
                    <i class="fas fa-list"></i> Lista
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="mostrarVista('tarjetas')">
                    <i class="fas fa-th-large"></i> Tarjetas
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="vistaLista" class="table-responsive">
                <table class="table table-bordered table-striped" id="tablaEntradasLotes" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>N° Entrada</th>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Factura</th>
                            <th>Lotes</th>
                            <th>Productos</th>
                            <th>Cantidad Total</th>
                            <th>Valor Total</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-entrada="1">
                            <td><strong>EL-2024-001</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-calendar-alt text-success"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">25 Oct 2024</div>
                                        <small class="text-muted">09:30 AM</small>
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
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-pdf text-danger me-2"></i>
                                    <div>
                                        <div class="fw-bold">FC01-000123</div>
                                        <small class="text-muted">25 Oct 2024</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning me-2">5</span>
                                    <div>
                                        <small>L240312A, L240313B...</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2">3</span>
                                    <div>
                                        <small>Ibuprofeno, Paracetamol</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">2,500</span>
                            </td>
                            <td class="text-end fw-bold">S/ 8,450.00</td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Procesado
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white fw-bold">
                                            AM
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Ana María</div>
                                        <small class="text-muted">Almacén</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verEntradaLote(1)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editarEntradaLote(1)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="validarEntradaLote(1)" title="Validar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="imprimirEntradaLote(1)">Imprimir</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="generarReporteLote(1)">Generar Reporte</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="rechazarEntradaLote(1)">Rechazar</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr data-entrada="2">
                            <td><strong>EL-2024-002</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-calendar-alt text-warning"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">25 Oct 2024</div>
                                        <small class="text-muted">11:45 AM</small>
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
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-pdf text-danger me-2"></i>
                                    <div>
                                        <div class="fw-bold">FC05-000789</div>
                                        <small class="text-muted">25 Oct 2024</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning me-2">2</span>
                                    <div>
                                        <small>L240325C, L240326D</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2">1</span>
                                    <div>
                                        <small>Insulina NPH</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">500</span>
                            </td>
                            <td class="text-end fw-bold">S/ 12,500.00</td>
                            <td>
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock"></i> Validando
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold">
                                            LR
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Luis Rodríguez</div>
                                        <small class="text-muted">Controlador</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verEntradaLote(2)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="aprobarEntradaLote(2)" title="Aprobar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="rechazarEntradaLote(2)" title="Rechazar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Más entradas se cargarían dinámicamente -->
                    </tbody>
                </table>
            </div>

            <div id="vistaTarjetas" class="row d-none">
                <!-- Vista en tarjetas se llenará dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Gráfico de Entradas -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area"></i> Evolución de Entradas de Lotes
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
                        <canvas id="graficoEntradasLotes"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Entradas por Proveedor
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="graficoProveedores"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Validación Rápida -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clipboard-check"></i> Validación Rápida de Lotes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <label for="codigoLoteValidar" class="form-label">Código de Lote</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="codigoLoteValidar" 
                                       placeholder="Escanee o ingrese el código del lote...">
                                <button class="btn btn-outline-secondary" type="button" onclick="escanearLote()">
                                    <i class="fas fa-qrcode"></i> Escanear
                                </button>
                                <button class="btn btn-primary" type="button" onclick="validarLoteRapido()">
                                    <i class="fas fa-check"></i> Validar
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="w-100">
                                <label class="form-label">Estado de Validación</label>
                                <div id="estadoValidacion" class="alert alert-secondary mb-0">
                                    <i class="fas fa-info-circle"></i> Ingrese un código para validar
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Entrada de Lote -->
<div class="modal fade" id="modalNuevaEntradaLote" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus text-success"></i> Nueva Entrada de Lotes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNuevaEntradaLote">
                <div class="modal-body">
                    <!-- Información General -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-info-circle"></i> Información General
                            </h6>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="numeroEntrada" class="form-label">N° Entrada</label>
                                <input type="text" class="form-control" id="numeroEntrada" value="EL-2024-003" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="fechaEntrada" class="form-label">Fecha</label>
                                <input type="datetime-local" class="form-control" id="fechaEntrada" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="tipoEntrada" class="form-label">Tipo de Entrada</label>
                                <select class="form-select" id="tipoEntrada" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="compra">Compra</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="devolucion">Devolución</option>
                                    <option value="consignacion">Consignación</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="prioridad" class="form-label">Prioridad</label>
                                <select class="form-select" id="prioridad">
                                    <option value="normal">Normal</option>
                                    <option value="alta">Alta</option>
                                    <option value="urgente">Urgente</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Proveedor -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-building"></i> Información del Proveedor
                            </h6>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="proveedor" class="form-label">Proveedor</label>
                                <select class="form-select" id="proveedor" onchange="cargarDatosProveedor()" required>
                                    <option value="">Seleccionar proveedor...</option>
                                    <option value="pfizer" data-ruc="20100130204" data-direccion="Av. Larco 345, Miraflores">Pfizer S.A.</option>
                                    <option value="novartis" data-ruc="20100234567" data-direccion="Av. Camino Real 348, San Isidro">Novartis Perú S.A.</option>
                                    <option value="roche" data-ruc="20100345678" data-direccion="Av. Javier Prado 310, San Borja">Roche S.A.</option>
                                    <option value="merck" data-ruc="20100456789" data-direccion="Av. El Derby 254, Surco">Merck S.A.</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="rucProveedor" class="form-label">RUC</label>
                                <input type="text" class="form-control" id="rucProveedor" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="factura" class="form-label">N° Factura</label>
                                <input type="text" class="form-control" id="factura" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="fechaFactura" class="form-label">Fecha Factura</label>
                                <input type="date" class="form-control" id="fechaFactura" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="direccionProveedor" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccionProveedor" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Lotes -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-tags"></i> Lotes
                            </h6>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tablaLotesEntrada">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Código Lote</th>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unit.</th>
                                            <th>Total</th>
                                            <th>Fecha Vencimiento</th>
                                            <th>Estado Validación</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyLotesEntrada">
                                        <!-- Lotes se agregan dinámicamente -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                            <td id="subtotal" class="text-end fw-bold">S/ 0.00</td>
                                            <td colspan="3"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">IGV (18%):</td>
                                            <td id="igv" class="text-end fw-bold">S/ 0.00</td>
                                            <td colspan="3"></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="4" class="text-end fw-bold">Total:</td>
                                            <td id="total" class="text-end fw-bold fs-5">S/ 0.00</td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-success" onclick="agregarLote()">
                                    <i class="fas fa-plus"></i> Agregar Lote
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="validarTodosLotes()">
                                    <i class="fas fa-check-double"></i> Validar Todos
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="importarExcel()">
                                    <i class="fas fa-file-excel"></i> Importar Excel
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Validaciones y Documentos -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-file-alt"></i> Documentos y Validaciones
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="certificadoCalidad" class="form-label">Certificado de Calidad</label>
                                <input type="file" class="form-control" id="certificadoCalidad" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="registroSanitario" class="form-label">Registro Sanitario</label>
                                <input type="file" class="form-control" id="registroSanitario" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="documentosTransporte" class="form-label">Documentos de Transporte</label>
                                <input type="file" class="form-control" id="documentosTransporte" accept=".pdf">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="facturaDigital" class="form-label">Factura Digital</label>
                                <input type="file" class="form-control" id="facturaDigital" accept=".pdf,.xml">
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-comment"></i> Observaciones
                            </h6>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" rows="3" 
                                          placeholder="Observaciones sobre la entrada, condiciones especiales, etc."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Alertas de Validación -->
                    <div class="alert alert-warning d-none" id="alertValidacion">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atención:</strong> <span id="mensajeValidacion"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary" onclick="guardarBorrador()">
                        <i class="fas fa-save"></i> Guardar Borrador
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Enviar para Validación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Entrada de Lote -->
<div class="modal fade" id="modalVerEntradaLote" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalles de Entrada de Lotes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoVerEntradaLote">
                <!-- Se carga dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="imprimirEntradaLote()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button type="button" class="btn btn-primary" onclick="editarEntradaLote()">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Variables globales
let tablaEntradasLotes;
let datosEntradasLotes = [];
let contadorLotes = 0;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    inicializarTabla();
    inicializarGraficos();
    cargarDatos();
    establecerFechaActual();
});

// Establecer fecha actual
function establecerFechaActual() {
    const ahora = new Date();
    const fechaFormateada = ahora.toISOString().slice(0, 16);
    document.getElementById('fechaEntrada').value = fechaFormateada;
    document.getElementById('fechaFactura').value = ahora.toISOString().split('T')[0];
}

// Inicializar DataTable
function inicializarTabla() {
    tablaEntradasLotes = $('#tablaEntradasLotes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        pageLength: 25,
        responsive: true,
        order: [[0, 'desc']],
        columnDefs: [
            {
                targets: [7],
                className: 'text-end'
            },
            {
                targets: [10],
                className: 'text-center',
                orderable: false
            }
        ]
    });

    // Búsqueda global
    $('#busqueda').on('keyup', function() {
        tablaEntradasLotes.search($(this).val()).draw();
    });

    // Filtros
    $('#filtroFecha, #filtroProveedor, #filtroEstado, #filtroTipoProducto').on('change', aplicarFiltros);
}

// Inicializar gráficos
function inicializarGraficos() {
    // Gráfico de evolución de entradas
    const ctx1 = document.getElementById('graficoEntradasLotes').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['Sep 26', 'Sep 27', 'Sep 28', 'Sep 29', 'Sep 30', 'Oct 1', 'Oct 2', 'Oct 3', 'Oct 4', 'Oct 5'],
            datasets: [
                {
                    label: 'Número de Entradas',
                    data: [3, 5, 2, 7, 4, 6, 3, 9, 5, 7],
                    borderColor: 'rgb(78, 115, 223)',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.1,
                    yAxisID: 'y'
                },
                {
                    label: 'Lotes Ingresados',
                    data: [15, 28, 12, 35, 22, 31, 18, 42, 25, 33],
                    borderColor: 'rgb(28, 200, 138)',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    tension: 0.1,
                    yAxisID: 'y'
                },
                {
                    label: 'Valor Total (S/)',
                    data: [12500, 23800, 9800, 35600, 21800, 28700, 19300, 42100, 25400, 33100],
                    borderColor: 'rgb(255, 193, 7)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.1,
                    yAxisID: 'y1'
                }
            ]
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
                        text: 'Fecha'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Cantidad'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Valor Total (S/)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === 'Valor Total (S/)') {
                                return context.dataset.label + ': S/ ' + context.parsed.y.toLocaleString();
                            }
                            return context.dataset.label + ': ' + context.parsed.y;
                        }
                    }
                }
            }
        }
    });

    // Gráfico de proveedores
    const ctx2 = document.getElementById('graficoProveedores').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Pfizer', 'Novartis', 'Roche', 'Merck', 'Bayer', 'Otros'],
            datasets: [{
                data: [35, 25, 20, 12, 5, 3],
                backgroundColor: [
                    'rgb(28, 200, 138)',
                    'rgb(78, 115, 223)',
                    'rgb(255, 193, 7)',
                    'rgb(102, 126, 234)',
                    'rgb(108, 117, 125)',
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
    datosEntradasLotes = [
        {
            id: 1,
            numero: 'EL-2024-001',
            fecha: '2024-10-25 09:30:00',
            proveedor: 'Pfizer S.A.',
            factura: 'FC01-000123',
            lotes: 5,
            productos: 3,
            cantidad: 2500,
            valor: 8450.00,
            estado: 'procesado',
            usuario: 'Ana María'
        },
        {
            id: 2,
            numero: 'EL-2024-002',
            fecha: '2024-10-25 11:45:00',
            proveedor: 'Roche S.A.',
            factura: 'FC05-000789',
            lotes: 2,
            productos: 1,
            cantidad: 500,
            valor: 12500.00,
            estado: 'validando',
            usuario: 'Luis Rodríguez'
        }
    ];
    
    actualizarEstadisticas();
}

// Actualizar estadísticas
function actualizarEstadisticas() {
    const hoy = new Date().toDateString();
    
    // Filtrar entradas de hoy
    const entradasHoy = datosEntradasLotes.filter(entrada => {
        return new Date(entrada.fecha).toDateString() === hoy;
    });
    
    // Actualizar contadores
    document.getElementById('entradasHoy').textContent = entradasHoy.length;
    document.getElementById('lotesIngresados').textContent = entradasHoy.reduce((sum, e) => sum + e.lotes, 0);
    document.getElementById('valorTotal').textContent = 'S/ ' + entradasHoy.reduce((sum, e) => sum + e.valor, 0).toLocaleString();
    
    // Pendientes de validación
    const pendientes = datosEntradasLotes.filter(e => e.estado === 'pendiente' || e.estado === 'validando').length;
    document.getElementById('pendientesValidacion').textContent = pendientes;
}

// Aplicar filtros
function aplicarFiltros() {
    const fecha = $('#filtroFecha').val();
    const proveedor = $('#filtroProveedor').val();
    const estado = $('#filtroEstado').val();
    const tipoProducto = $('#filtroTipoProducto').val();
    
    tablaEntradasLotes.clear().rows.add(filtrarDatos(fecha, proveedor, estado, tipoProducto)).draw();
}

// Filtrar datos
function filtrarDatos(fecha, proveedor, estado, tipoProducto) {
    let datos = datosEntradasLotes;
    
    if (fecha) {
        datos = datos.filter(item => {
            const fechaEntrada = new Date(item.fecha).toISOString().split('T')[0];
            return fechaEntrada === fecha;
        });
    }
    
    if (proveedor) {
        datos = datos.filter(item => 
            item.proveedor.toLowerCase().includes(proveedor)
        );
    }
    
    if (estado) {
        datos = datos.filter(item => item.estado === estado);
    }
    
    if (tipoProducto) {
        // Implementar filtro por tipo de producto cuando esté disponible
    }
    
    return datos.map(item => [
        `<strong>${item.numero}</strong>`,
        formatearFechaCompleta(item.fecha),
        obtenerInfoProveedor(item.proveedor),
        obtenerInfoFactura(item.factura),
        `<span class="badge bg-warning">${item.lotes}</span>`,
        `<span class="badge bg-primary">${item.productos}</span>`,
        `<span class="text-center fw-bold">${item.cantidad}</span>`,
        `S/ ${item.valor.toFixed(2)}`,
        `<span class="badge ${obtenerClaseEstado(item.estado)}">${obtenerTextoEstado(item.estado)}</span>`,
        obtenerInfoUsuario(item.usuario),
        generarBotonesAccion(item.id)
    ]);
}

// Formatear fecha completa
function formatearFechaCompleta(fecha) {
    const fechaObj = new Date(fecha);
    return `
        <div class="d-flex align-items-center">
            <div class="me-2"><i class="fas fa-calendar-alt text-success"></i></div>
            <div>
                <div class="fw-bold">${fechaObj.toLocaleDateString('es-ES')}</div>
                <small class="text-muted">${fechaObj.toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})}</small>
            </div>
        </div>
    `;
}

// Obtener info del proveedor
function obtenerInfoProveedor(proveedor) {
    const proveedores = {
        'Pfizer S.A.': {ruc: '20100130204', icon: 'fas fa-building text-info'},
        'Roche S.A.': {ruc: '20100345678', icon: 'fas fa-building text-info'},
        'Novartis Perú S.A.': {ruc: '20100234567', icon: 'fas fa-building text-info'}
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

// Obtener info de factura
function obtenerInfoFactura(factura) {
    return `
        <div class="d-flex align-items-center">
            <i class="fas fa-file-pdf text-danger me-2"></i>
            <div>
                <div class="fw-bold">${factura}</div>
                <small class="text-muted">${new Date().toLocaleDateString('es-ES')}</small>
            </div>
        </div>
    `;
}

// Obtener clase para estado
function obtenerClaseEstado(estado) {
    const clases = {
        'pendiente': 'bg-warning',
        'validando': 'bg-info',
        'validado': 'bg-success',
        'rechazado': 'bg-danger',
        'procesado': 'bg-primary'
    };
    return clases[estado] || 'bg-secondary';
}

// Obtener texto para estado
function obtenerTextoEstado(estado) {
    const textos = {
        'pendiente': 'Pendiente',
        'validando': 'Validando',
        'validado': 'Validado',
        'rechazado': 'Rechazado',
        'procesado': 'Procesado'
    };
    return textos[estado] || estado;
}

// Obtener info del usuario
function obtenerInfoUsuario(usuario) {
    const iniciales = usuario.split(' ').map(n => n[0]).join('');
    return `
        <div class="d-flex align-items-center">
            <div class="avatar-sm me-2">
                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white fw-bold">
                    ${iniciales}
                </div>
            </div>
            <div>
                <div class="fw-bold">${usuario}</div>
                <small class="text-muted">Usuario</small>
            </div>
        </div>
    `;
}

// Generar botones de acción
function generarBotonesAccion(id) {
    return `
        <div class="btn-group" role="group">
            <button class="btn btn-sm btn-outline-primary" onclick="verEntradaLote(${id})" title="Ver Detalles">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="editarEntradaLote(${id})" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="validarEntradaLote(${id})" title="Validar">
                <i class="fas fa-check"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="imprimirEntradaLote(${id})">Imprimir</a></li>
                    <li><a class="dropdown-item" href="#" onclick="generarReporteLote(${id})">Generar Reporte</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="rechazarEntradaLote(${id})">Rechazar</a></li>
                </ul>
            </div>
        </div>
    `;
}

// Limpiar filtros
function limpiarFiltros() {
    $('#filtroFecha, #filtroProveedor, #filtroEstado, #filtroTipoProducto').val('');
    $('#busqueda').val('');
    tablaEntradasLotes.search('').columns().search('').draw();
}

// Mostrar vista (lista/tarjetas)
function mostrarVista(vista) {
    if (vista === 'lista') {
        $('#vistaLista').removeClass('d-none');
        $('#vistaTarjetas').addClass('d-none');
        $('.btn-group .btn').removeClass('active');
        $(`.btn-group .btn:eq(0)`).addClass('active');
    } else {
        $('#vistaLista').addClass('d-none');
        $('#vistaTarjetas').removeClass('d-none');
        $('.btn-group .btn').removeClass('active');
        $(`.btn-group .btn:eq(1)`).addClass('active');
        cargarVistaTarjetas();
    }
}

// Cargar vista en tarjetas
function cargarVistaTarjetas() {
    const container = document.getElementById('vistaTarjetas');
    container.innerHTML = '';
    
    datosEntradasLotes.forEach(entrada => {
        const card = document.createElement('div');
        card.className = 'col-xl-4 col-lg-6 col-md-12 mb-4';
        card.innerHTML = `
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">${entrada.numero}</h6>
                    <span class="badge ${obtenerClaseEstado(entrada.estado)}">${obtenerTextoEstado(entrada.estado)}</span>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <i class="fas fa-calendar-alt text-muted"></i>
                        ${new Date(entrada.fecha).toLocaleDateString('es-ES')}
                        <small class="text-muted ms-2">
                            ${new Date(entrada.fecha).toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})}
                        </small>
                    </div>
                    <div class="mb-2">
                        <strong>Proveedor:</strong> ${entrada.proveedor}
                    </div>
                    <div class="mb-2">
                        <strong>Factura:</strong> ${entrada.factura}
                    </div>
                    <div class="row text-center mb-2">
                        <div class="col">
                            <div class="text-sm font-weight-bold text-primary">${entrada.lotes}</div>
                            <div class="text-xs text-muted">Lotes</div>
                        </div>
                        <div class="col">
                            <div class="text-sm font-weight-bold text-success">${entrada.productos}</div>
                            <div class="text-xs text-muted">Productos</div>
                        </div>
                        <div class="col">
                            <div class="text-sm font-weight-bold text-info">${entrada.cantidad}</div>
                            <div class="text-xs text-muted">Cantidad</div>
                        </div>
                    </div>
                    <div class="text-center">
                        <span class="fw-bold text-success fs-5">S/ ${entrada.valor.toFixed(2)}</span>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="verEntradaLote(${entrada.id})">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="validarEntradaLote(${entrada.id})">
                            <i class="fas fa-check"></i> Validar
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

// Cargar datos del proveedor
function cargarDatosProveedor() {
    const select = document.getElementById('proveedor');
    const option = select.options[select.selectedIndex];
    
    if (option && option.value) {
        document.getElementById('rucProveedor').value = option.dataset.ruc || '';
        document.getElementById('direccionProveedor').value = option.dataset.direccion || '';
    } else {
        document.getElementById('rucProveedor').value = '';
        document.getElementById('direccionProveedor').value = '';
    }
}

// Agregar lote
function agregarLote() {
    contadorLotes++;
    const tbody = document.getElementById('tbodyLotesEntrada');
    const fila = document.createElement('tr');
    fila.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm" id="codigoLote_${contadorLotes}" 
                   placeholder="Código de lote" required>
        </td>
        <td>
            <select class="form-select form-select-sm" id="producto_${contadorLotes}" 
                    onchange="cargarProductoLote(${contadorLotes})" required>
                <option value="">Seleccionar producto...</option>
                <option value="med001" data-precio="0.50">Ibuprofeno 400mg</option>
                <option value="med002" data-precio="0.30">Paracetamol 500mg</option>
                <option value="med003" data-precio="1.20">Amoxicilina 500mg</option>
                <option value="med004" data-precio="0.80">Loratadina 10mg</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" id="cantidad_${contadorLotes}" 
                   min="1" value="1" onchange="calcularTotalesLotes()">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" id="precio_${contadorLotes}" 
                   step="0.01" min="0" onchange="calcularTotalesLotes()">
        </td>
        <td id="totalLote_${contadorLotes}" class="text-end fw-bold">S/ 0.00</td>
        <td>
            <input type="date" class="form-control form-control-sm" id="fechaVencimiento_${contadorLotes}" required>
        </td>
        <td id="estadoValidacion_${contadorLotes}" class="text-center">
            <span class="badge bg-secondary">Pendiente</span>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-info" onclick="validarLoteIndividual(${contadorLotes})">
                <i class="fas fa-check"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarLote(${contadorLotes})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(fila);
}

// Cargar producto
function cargarProductoLote(id) {
    const select = document.getElementById(`producto_${id}`);
    const option = select.options[select.selectedIndex];
    
    if (option && option.value) {
        const precio = option.dataset.precio;
        document.getElementById(`precio_${id}`).value = precio;
        calcularTotalesLotes();
        
        // Sugerir fecha de vencimiento (ejemplo: 2 años)
        const fechaVencimiento = new Date();
        fechaVencimiento.setFullYear(fechaVencimiento.getFullYear() + 2);
        document.getElementById(`fechaVencimiento_${id}`).value = fechaVencimiento.toISOString().split('T')[0];
    }
}

// Calcular totales
function calcularTotalesLotes() {
    let subtotal = 0;
    
    for (let i = 1; i <= contadorLotes; i++) {
        const cantidad = parseFloat(document.getElementById(`cantidad_${i}`)?.value || 0);
        const precio = parseFloat(document.getElementById(`precio_${i}`)?.value || 0);
        const total = cantidad * precio;
        
        if (total > 0) {
            subtotal += total;
            document.getElementById(`totalLote_${i}`).textContent = `S/ ${total.toFixed(2)}`;
        }
    }
    
    const igv = subtotal * 0.18;
    const total = subtotal + igv;
    
    document.getElementById('subtotal').textContent = `S/ ${subtotal.toFixed(2)}`;
    document.getElementById('igv').textContent = `S/ ${igv.toFixed(2)}`;
    document.getElementById('total').textContent = `S/ ${total.toFixed(2)}`;
}

// Eliminar lote
function eliminarLote(id) {
    document.getElementById(`codigoLote_${id}`).closest('tr').remove();
    calcularTotalesLotes();
}

// Validar lote individual
function validarLoteIndividual(id) {
    const codigoLote = document.getElementById(`codigoLote_${id}`).value;
    const producto = document.getElementById(`producto_${id}`).value;
    const cantidad = document.getElementById(`cantidad_${id}`).value;
    const fechaVencimiento = document.getElementById(`fechaVencimiento_${id}`).value;
    
    if (!codigoLote || !producto || !cantidad || !fechaVencimiento) {
        mostrarValidacion('Complete todos los campos del lote antes de validar.');
        return;
    }
    
    // Simular validación
    document.getElementById(`estadoValidacion_${id}`).innerHTML = '<span class="badge bg-success">Válido</span>';
    mostrarValidacion('Lote validado correctamente.', 'success');
}

// Validar todos los lotes
function validarTodosLotes() {
    let lotesValidos = 0;
    
    for (let i = 1; i <= contadorLotes; i++) {
        const codigoLote = document.getElementById(`codigoLote_${i}`)?.value;
        if (codigoLote) {
            document.getElementById(`estadoValidacion_${i}`).innerHTML = '<span class="badge bg-success">Válido</span>';
            lotesValidos++;
        }
    }
    
    if (lotesValidos > 0) {
        mostrarValidacion(`${lotesValidos} lote(s) validado(s) correctamente.`, 'success');
    } else {
        mostrarValidacion('No hay lotes para validar.');
    }
}

// Importar desde Excel
function importarExcel() {
    Swal.fire({
        title: 'Importar Excel',
        text: 'Seleccione un archivo Excel con la lista de lotes',
        icon: 'question',
        input: 'file',
        inputAttributes: {
            accept: '.xlsx,.xls'
        },
        showCancelButton: true,
        confirmButtonText: 'Importar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            Swal.fire({
                title: 'Importación Exitosa',
                text: 'Los lotes han sido importados correctamente',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

// Mostrar validación
function mostrarValidacion(mensaje, tipo = 'warning') {
    const alert = document.getElementById('alertValidacion');
    const mensajeEl = document.getElementById('mensajeValidacion');
    mensajeEl.textContent = mensaje;
    alert.className = `alert alert-${tipo === 'success' ? 'success' : 'warning'}`;
    alert.classList.remove('d-none');
}

// Guardar como borrador
function guardarBorrador() {
    Swal.fire({
        title: 'Guardar Borrador',
        text: '¿Desea guardar esta entrada como borrador?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Guardado',
                text: 'La entrada se ha guardado como borrador',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

// Ver entrada de lote
function verEntradaLote(id) {
    const entrada = datosEntradasLotes.find(e => e.id === id);
    if (!entrada) return;
    
    const contenido = `
        <div class="row">
            <div class="col-md-6">
                <h6>Información General</h6>
                <table class="table table-sm">
                    <tr><td><strong>Número:</strong></td><td>${entrada.numero}</td></tr>
                    <tr><td><strong>Fecha:</strong></td><td>${new Date(entrada.fecha).toLocaleString('es-ES')}</td></tr>
                    <tr><td><strong>Proveedor:</strong></td><td>${entrada.proveedor}</td></tr>
                    <tr><td><strong>Factura:</strong></td><td>${entrada.factura}</td></tr>
                    <tr><td><strong>Estado:</strong></td><td><span class="badge ${obtenerClaseEstado(entrada.estado)}">${obtenerTextoEstado(entrada.estado)}</span></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Detalles</h6>
                <table class="table table-sm">
                    <tr><td><strong>Lotes:</strong></td><td>${entrada.lotes}</td></tr>
                    <tr><td><strong>Productos:</strong></td><td>${entrada.productos}</td></tr>
                    <tr><td><strong>Cantidad Total:</strong></td><td>${entrada.cantidad}</td></tr>
                    <tr><td><strong>Valor Total:</strong></td><td>S/ ${entrada.valor.toFixed(2)}</td></tr>
                    <tr><td><strong>Usuario:</strong></td><td>${entrada.usuario}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Lotes</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Código Lote</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Total</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>L240312A</td>
                                <td>Ibuprofeno 400mg</td>
                                <td>1000</td>
                                <td>S/ 0.50</td>
                                <td>S/ 500.00</td>
                                <td>15/08/2026</td>
                                <td><span class="badge bg-success">Válido</span></td>
                            </tr>
                            <tr>
                                <td>L240313B</td>
                                <td>Paracetamol 500mg</td>
                                <td>1500</td>
                                <td>S/ 0.30</td>
                                <td>S/ 450.00</td>
                                <td>20/06/2026</td>
                                <td><span class="badge bg-success">Válido</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contenidoVerEntradaLote').innerHTML = contenido;
    $('#modalVerEntradaLote').modal('show');
}

// Editar entrada de lote
function editarEntradaLote(id) {
    Swal.fire({
        title: 'Editar Entrada de Lote',
        text: 'Función de edición en desarrollo',
        icon: 'info',
        confirmButtonText: 'Aceptar'
    });
}

// Validar entrada de lote
function validarEntradaLote(id) {
    Swal.fire({
        title: 'Validar Entrada de Lotes',
        text: '¿Ha verificado que todos los lotes y documentos están correctos?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, validar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Entrada Validada',
                text: 'La entrada de lotes ha sido validada exitosamente',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Aprobar entrada de lote
function aprobarEntradaLote(id) {
    Swal.fire({
        title: 'Aprobar Entrada de Lotes',
        text: '¿Desea aprobar esta entrada de lotes?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Entrada Aprobada',
                text: 'La entrada de lotes ha sido aprobada y procesada',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Rechazar entrada de lote
function rechazarEntradaLote(id) {
    Swal.fire({
        title: 'Rechazar Entrada de Lotes',
        text: '¿Por qué rechaza esta entrada?',
        icon: 'question',
        input: 'textarea',
        inputPlaceholder: 'Ingrese el motivo del rechazo...',
        showCancelButton: true,
        confirmButtonText: 'Rechazar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        preConfirm: (motivo) => {
            if (!motivo) {
                Swal.showValidationMessage('Debe ingresar un motivo para el rechazo');
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Entrada Rechazada',
                text: 'La entrada de lotes ha sido rechazada',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Imprimir entrada de lote
function imprimirEntradaLote(id) {
    window.print();
}

// Generar reporte de lote
function generarReporteLote(id) {
    Swal.fire({
        title: 'Generar Reporte',
        text: '¿Desea generar un reporte detallado de esta entrada?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Generar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(`/reportes/entrada-lotes/${id}`, '_blank');
        }
    });
}

// Exportar entradas
function exportarEntradas() {
    Swal.fire({
        title: 'Exportar Entradas de Lotes',
        text: 'Seleccione el formato de exportación',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Excel',
        denyButtonText: 'PDF',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('/export/entradas-lotes/excel', '_blank');
        } else if (result.isDenied) {
            window.open('/export/entradas-lotes/pdf', '_blank');
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

// Escanear lote
function escanearLote() {
    Swal.fire({
        title: 'Escanear Código QR',
        text: 'Active la cámara para escanear el código QR del lote',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Activar Cámara',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Simular escaneo
            setTimeout(() => {
                document.getElementById('codigoLoteValidar').value = 'L240312A';
                Swal.fire({
                    title: 'Código Escaneado',
                    text: 'L240312A - Ibuprofeno 400mg',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                });
            }, 2000);
        }
    });
}

// Validar lote rápido
function validarLoteRapido() {
    const codigo = document.getElementById('codigoLoteValidar').value;
    
    if (!codigo) {
        document.getElementById('estadoValidacion').className = 'alert alert-danger mb-0';
        document.getElementById('estadoValidacion').innerHTML = '<i class="fas fa-exclamation-triangle"></i> Ingrese un código de lote';
        return;
    }
    
    // Simular validación
    document.getElementById('estadoValidacion').className = 'alert alert-success mb-0';
    document.getElementById('estadoValidacion').innerHTML = '<i class="fas fa-check"></i> Lote válido - ' + codigo;
}

// Manejar formulario nueva entrada de lote
document.getElementById('formNuevaEntradaLote').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const datos = {
        numero: document.getElementById('numeroEntrada').value,
        fecha: document.getElementById('fechaEntrada').value,
        tipo: document.getElementById('tipoEntrada').value,
        prioridad: document.getElementById('prioridad').value,
        proveedor: document.getElementById('proveedor').value,
        factura: document.getElementById('factura').value,
        total: document.getElementById('total').textContent,
        observaciones: document.getElementById('observaciones').value
    };
    
    // Validar que hay lotes
    if (contadorLotes === 0) {
        Swal.fire({
            title: 'Error',
            text: 'Debe agregar al menos un lote',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    
    // Validar que todos los lotes están validados
    let lotesValidos = 0;
    for (let i = 1; i <= contadorLotes; i++) {
        if (document.getElementById(`codigoLote_${i}`)?.value) {
            lotesValidos++;
        }
    }
    
    if (lotesValidos === 0) {
        mostrarValidacion('Debe validar al menos un lote antes de enviar.');
        return;
    }
    
    // Aquí se enviarían los datos al servidor
    console.log('Nueva entrada de lotes:', datos);
    
    Swal.fire({
        title: 'Entrada Enviada',
        text: 'La entrada de lotes ha sido enviada para validación',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        $('#modalNuevaEntradaLote').modal('hide');
        this.reset();
        // Recargar datos
        location.reload();
    });
});
</script>
@endsection