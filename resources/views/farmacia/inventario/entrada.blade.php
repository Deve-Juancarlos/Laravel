@extends('layouts.app')

@section('title', 'Entrada de Mercancía - SIFANO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-arrow-down text-success"></i>
                Entrada de Mercancía
            </h1>
            <p class="text-muted">Registro y control de ingresos de productos al inventario</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportarEntradas()">
                <i class="fas fa-file-export"></i> Exportar
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaEntrada">
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="entradasHoy">8</div>
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
                                Valor Total Hoy
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="valorHoy">S/ 15,240</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                Total Productos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="productosHoy">247</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
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
                                Facturas Pendientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="facturasPend">3</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
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
                    <label for="filtroTipo" class="form-label">Tipo de Entrada</label>
                    <select class="form-select" id="filtroTipo">
                        <option value="">Todos los tipos</option>
                        <option value="compra">Compra</option>
                        <option value="devolucion">Devolución</option>
                        <option value="ajuste">Ajuste Positivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="consignacion">Consignación</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroProveedor" class="form-label">Proveedor</label>
                    <select class="form-select" id="filtroProveedor">
                        <option value="">Todos los proveedores</option>
                        <option value="pfizer">Pfizer</option>
                        <option value="novartis">Novartis</option>
                        <option value="roche">Roche</option>
                        <option value="merck">Merck</option>
                        <option value="bayer">Bayer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroEstado" class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="recibido">Recibido</option>
                        <option value="verificado">Verificado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label for="busqueda" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="busqueda" placeholder="Número de entrada, factura, producto...">
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
                <i class="fas fa-list"></i> Registro de Entradas
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
                <table class="table table-bordered table-striped" id="tablaEntradas" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>N° Entrada</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Proveedor</th>
                            <th>Factura</th>
                            <th>Productos</th>
                            <th>Valor Total</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-entrada="1">
                            <td><strong>ENT-2024-001</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">25 Oct 2024</div>
                                        <small class="text-muted">09:30 AM</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-shopping-cart"></i> Compra
                                </span>
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
                                        <small class="text-muted">S/ 8,450.00</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2">12</span>
                                    <div>
                                        <small>Ibuprofeno, Paracetamol, Amoxicilina</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end fw-bold">S/ 8,450.00</td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Verificado
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold">
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
                                    <button class="btn btn-sm btn-outline-primary" onclick="verEntrada(1)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editarEntrada(1)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="imprimirEntrada(1)" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="duplicarEntrada(1)">Duplicar</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="cancelarEntrada(1)">Cancelar</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="eliminarEntrada(1)">Eliminar</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr data-entrada="2">
                            <td><strong>ENT-2024-002</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">25 Oct 2024</div>
                                        <small class="text-muted">11:15 AM</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <i class="fas fa-undo"></i> Devolución
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-building text-info"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Cliente: Juan Pérez</div>
                                        <small class="text-muted">DNI: 12345678</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-invoice text-warning me-2"></i>
                                    <div>
                                        <div class="fw-bold">VTA-001456</div>
                                        <small class="text-muted">S/ 125.50</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning me-2">2</span>
                                    <div>
                                        <small>Paracetamol 500mg x2</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end fw-bold">S/ 125.50</td>
                            <td>
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock"></i> Pendiente
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white fw-bold">
                                            CS
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Carlos Sánchez</div>
                                        <small class="text-muted">Cajero</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verEntrada(2)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="verificarEntrada(2)" title="Verificar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="rechazarEntrada(2)" title="Rechazar">
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
                        <i class="fas fa-chart-area"></i> Evolución de Entradas (Últimos 30 días)
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
                        <canvas id="graficoEntradas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Entradas por Tipo
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="graficoTipos"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Entrada -->
<div class="modal fade" id="modalNuevaEntrada" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus text-success"></i> Nueva Entrada de Mercancía
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNuevaEntrada">
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
                                <input type="text" class="form-control" id="numeroEntrada" value="ENT-2024-003" readonly>
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
                                <select class="form-select" id="tipoEntrada" required onchange="cambiarTipo()">
                                    <option value="">Seleccionar...</option>
                                    <option value="compra">Compra</option>
                                    <option value="devolucion">Devolución</option>
                                    <option value="ajuste">Ajuste Positivo</option>
                                    <option value="transferencia">Transferencia</option>
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

                    <!-- Información del Proveedor/Cliente -->
                    <div class="row mb-4" id="seccionProveedor">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-building"></i> Información del Proveedor
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="proveedor" class="form-label">Proveedor</label>
                                <select class="form-select" id="proveedor" onchange="cargarDatosProveedor()">
                                    <option value="">Seleccionar proveedor...</option>
                                    <option value="pfizer" data-ruc="20100130204" data-direccion="Av. Larco 345, Miraflores">Pfizer S.A.</option>
                                    <option value="novartis" data-ruc="20100234567" data-direccion="Av. Camino Real 348, San Isidro">Novartis Perú S.A.</option>
                                    <option value="roche" data-ruc="20100345678" data-direccion="Av. Javier Prado 310, San Borja">Roche S.A.</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="rucProveedor" class="form-label">RUC</label>
                                <input type="text" class="form-control" id="rucProveedor" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="facturaProveedor" class="form-label">N° Factura</label>
                                <input type="text" class="form-control" id="facturaProveedor">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="direccionProveedor" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccionProveedor" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4 d-none" id="seccionCliente">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-user"></i> Información del Cliente
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cliente" class="form-label">Cliente</label>
                                <input type="text" class="form-control" id="cliente" placeholder="Nombre del cliente">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="dniCliente" class="form-label">DNI</label>
                                <input type="text" class="form-control" id="dniCliente" maxlength="8">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="ticketVenta" class="form-label">N° Ticket Venta</label>
                                <input type="text" class="form-control" id="ticketVenta">
                            </div>
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-pills"></i> Productos
                            </h6>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tablaProductos">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unit.</th>
                                            <th>Total</th>
                                            <th>Lote</th>
                                            <th>Vencimiento</th>
                                            <th>Estado</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyProductos">
                                        <!-- Productos se agregan dinámicamente -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                            <td id="subtotal" class="text-end fw-bold">S/ 0.00</td>
                                            <td colspan="4"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">IGV (18%):</td>
                                            <td id="igv" class="text-end fw-bold">S/ 0.00</td>
                                            <td colspan="4"></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="4" class="text-end fw-bold">Total:</td>
                                            <td id="total" class="text-end fw-bold fs-5">S/ 0.00</td>
                                            <td colspan="4"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <button type="button" class="btn btn-outline-primary" onclick="agregarProducto()">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </button>
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
                                <label for="observaciones" class="form-label">Notas Adicionales</label>
                                <textarea class="form-control" id="observaciones" rows="3" placeholder="Observaciones, condiciones especiales, etc."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary" onclick="guardarBorrador()">
                        <i class="fas fa-save"></i> Guardar Borrador
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirmar Entrada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Entrada -->
<div class="modal fade" id="modalVerEntrada" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalles de Entrada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoVerEntrada">
                <!-- Se carga dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="imprimirEntrada()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button type="button" class="btn btn-primary" onclick="editarEntrada()">
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
let tablaEntradas;
let datosEntradas = [];
let contadorProductos = 0;

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
}

// Inicializar DataTable
function inicializarTabla() {
    tablaEntradas = $('#tablaEntradas').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        pageLength: 25,
        responsive: true,
        order: [[0, 'desc']],
        columnDefs: [
            {
                targets: [6],
                className: 'text-end'
            },
            {
                targets: [9],
                className: 'text-center',
                orderable: false
            }
        ]
    });

    // Búsqueda global
    $('#busqueda').on('keyup', function() {
        tablaEntradas.search($(this).val()).draw();
    });

    // Filtros
    $('#filtroFecha, #filtroTipo, #filtroProveedor, #filtroEstado').on('change', aplicarFiltros);
}

// Inicializar gráficos
function inicializarGraficos() {
    // Gráfico de evolución de entradas
    const ctx1 = document.getElementById('graficoEntradas').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['Sep 26', 'Sep 27', 'Sep 28', 'Sep 29', 'Sep 30', 'Oct 1', 'Oct 2', 'Oct 3', 'Oct 4', 'Oct 5'],
            datasets: [
                {
                    label: 'Número de Entradas',
                    data: [5, 8, 3, 12, 7, 9, 6, 15, 8, 11],
                    borderColor: 'rgb(78, 115, 223)',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.1,
                    yAxisID: 'y'
                },
                {
                    label: 'Valor Total (S/)',
                    data: [15200, 23800, 9800, 35600, 21800, 28700, 19300, 42100, 25400, 33100],
                    borderColor: 'rgb(28, 200, 138)',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
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
                        text: 'Número de Entradas'
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
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Gráfico de tipos
    const ctx2 = document.getElementById('graficoTipos').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Compras', 'Devoluciones', 'Ajustes', 'Transferencias', 'Consignación'],
            datasets: [{
                data: [65, 15, 10, 7, 3],
                backgroundColor: [
                    'rgb(28, 200, 138)',
                    'rgb(255, 193, 7)',
                    'rgb(78, 115, 223)',
                    'rgb(102, 126, 234)',
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
    datosEntradas = [
        {
            id: 1,
            numero: 'ENT-2024-001',
            fecha: '2024-10-25 09:30:00',
            tipo: 'compra',
            proveedor: 'Pfizer S.A.',
            factura: 'FC01-000123',
            valor: 8450.00,
            productos: 12,
            estado: 'verificado',
            usuario: 'Ana María'
        },
        {
            id: 2,
            numero: 'ENT-2024-002',
            fecha: '2024-10-25 11:15:00',
            tipo: 'devolucion',
            cliente: 'Juan Pérez',
            factura: 'VTA-001456',
            valor: 125.50,
            productos: 2,
            estado: 'pendiente',
            usuario: 'Carlos Sánchez'
        }
    ];
    
    actualizarEstadisticas();
}

// Actualizar estadísticas
function actualizarEstadisticas() {
    const hoy = new Date().toDateString();
    
    // Filtrar entradas de hoy
    const entradasHoy = datosEntradas.filter(entrada => {
        return new Date(entrada.fecha).toDateString() === hoy;
    });
    
    // Actualizar contadores
    document.getElementById('entradasHoy').textContent = entradasHoy.length;
    document.getElementById('valorHoy').textContent = 'S/ ' + entradasHoy.reduce((sum, e) => sum + e.valor, 0).toLocaleString();
    document.getElementById('productosHoy').textContent = entradasHoy.reduce((sum, e) => sum + e.productos, 0);
    
    // Facturas pendientes (simplificado)
    const pendientes = datosEntradas.filter(e => e.estado === 'pendiente').length;
    document.getElementById('facturasPend').textContent = pendientes;
}

// Aplicar filtros
function aplicarFiltros() {
    const fecha = $('#filtroFecha').val();
    const tipo = $('#filtroTipo').val();
    const proveedor = $('#filtroProveedor').val();
    const estado = $('#filtroEstado').val();
    
    tablaEntradas.clear().rows.add(filtrarDatos(fecha, tipo, proveedor, estado)).draw();
}

// Filtrar datos
function filtrarDatos(fecha, tipo, proveedor, estado) {
    let datos = datosEntradas;
    
    if (fecha) {
        datos = datos.filter(item => {
            const fechaEntrada = new Date(item.fecha).toISOString().split('T')[0];
            return fechaEntrada === fecha;
        });
    }
    
    if (tipo) {
        datos = datos.filter(item => item.tipo === tipo);
    }
    
    if (proveedor) {
        datos = datos.filter(item => 
            item.proveedor && item.proveedor.toLowerCase().includes(proveedor)
        );
    }
    
    if (estado) {
        datos = datos.filter(item => item.estado === estado);
    }
    
    return datos.map(item => [
        `<strong>${item.numero}</strong>`,
        formatearFechaCompleta(item.fecha),
        obtenerBadgeTipo(item.tipo),
        obtenerInfoProveedor(item),
        obtenerInfoFactura(item.factura, item.valor),
        `<span class="badge bg-primary">${item.productos}</span>`,
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
            <div class="me-2"><i class="fas fa-calendar-alt text-primary"></i></div>
            <div>
                <div class="fw-bold">${fechaObj.toLocaleDateString('es-ES')}</div>
                <small class="text-muted">${fechaObj.toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})}</small>
            </div>
        </div>
    `;
}

// Obtener badge de tipo
function obtenerBadgeTipo(tipo) {
    const badges = {
        'compra': '<span class="badge bg-success"><i class="fas fa-shopping-cart"></i> Compra</span>',
        'devolucion': '<span class="badge bg-info"><i class="fas fa-undo"></i> Devolución</span>',
        'ajuste': '<span class="badge bg-warning"><i class="fas fa-edit"></i> Ajuste</span>',
        'transferencia': '<span class="badge bg-primary"><i class="fas fa-exchange-alt"></i> Transferencia</span>',
        'consignacion': '<span class="badge bg-secondary"><i class="fas fa-handshake"></i> Consignación</span>'
    };
    return badges[tipo] || tipo;
}

// Obtener info del proveedor
function obtenerInfoProveedor(item) {
    if (item.proveedor) {
        return `
            <div class="d-flex align-items-center">
                <div class="me-2"><i class="fas fa-building text-info"></i></div>
                <div>
                    <div class="fw-bold">${item.proveedor}</div>
                    <small class="text-muted">RUC: 20100130204</small>
                </div>
            </div>
        `;
    } else if (item.cliente) {
        return `
            <div class="d-flex align-items-center">
                <div class="me-2"><i class="fas fa-user text-info"></i></div>
                <div>
                    <div class="fw-bold">Cliente: ${item.cliente}</div>
                    <small class="text-muted">DNI: 12345678</small>
                </div>
            </div>
        `;
    }
    return 'N/A';
}

// Obtener info de factura
function obtenerInfoFactura(factura, valor) {
    return `
        <div class="d-flex align-items-center">
            <i class="fas fa-file-pdf text-danger me-2"></i>
            <div>
                <div class="fw-bold">${factura}</div>
                <small class="text-muted">S/ ${valor.toFixed(2)}</small>
            </div>
        </div>
    `;
}

// Obtener clase para estado
function obtenerClaseEstado(estado) {
    const clases = {
        'pendiente': 'bg-warning',
        'recibido': 'bg-info',
        'verificado': 'bg-success',
        'cancelado': 'bg-danger'
    };
    return clases[estado] || 'bg-secondary';
}

// Obtener texto para estado
function obtenerTextoEstado(estado) {
    const textos = {
        'pendiente': 'Pendiente',
        'recibido': 'Recibido',
        'verificado': 'Verificado',
        'cancelado': 'Cancelado'
    };
    return textos[estado] || estado;
}

// Obtener info del usuario
function obtenerInfoUsuario(usuario) {
    const iniciales = usuario.split(' ').map(n => n[0]).join('');
    return `
        <div class="d-flex align-items-center">
            <div class="avatar-sm me-2">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold">
                    ${iniciales}
                </div>
            </div>
            <div>
                <div class="fw-bold">${usuario}</div>
                <small class="text-muted">Almacén</small>
            </div>
        </div>
    `;
}

// Generar botones de acción
function generarBotonesAccion(id) {
    return `
        <div class="btn-group" role="group">
            <button class="btn btn-sm btn-outline-primary" onclick="verEntrada(${id})" title="Ver Detalles">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="editarEntrada(${id})" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="imprimirEntrada(${id})" title="Imprimir">
                <i class="fas fa-print"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="duplicarEntrada(${id})">Duplicar</a></li>
                    <li><a class="dropdown-item" href="#" onclick="cancelarEntrada(${id})">Cancelar</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="eliminarEntrada(${id})">Eliminar</a></li>
                </ul>
            </div>
        </div>
    `;
}

// Limpiar filtros
function limpiarFiltros() {
    $('#filtroFecha, #filtroTipo, #filtroProveedor, #filtroEstado').val('');
    $('#busqueda').val('');
    tablaEntradas.search('').columns().search('').draw();
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
    
    datosEntradas.forEach(entrada => {
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
                        <strong>${entrada.proveedor || entrada.cliente}</strong>
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-calendar-alt text-muted"></i>
                        ${new Date(entrada.fecha).toLocaleDateString('es-ES')}
                        <small class="text-muted ms-2">
                            ${new Date(entrada.fecha).toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})}
                        </small>
                    </div>
                    <div class="mb-2">
                        ${obtenerBadgeTipo(entrada.tipo)}
                    </div>
                    <div class="mb-2">
                        <strong>Factura:</strong> ${entrada.factura}
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary">${entrada.productos} productos</span>
                        <span class="fw-bold text-success">S/ ${entrada.valor.toFixed(2)}</span>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="verEntrada(${entrada.id})">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="imprimirEntrada(${entrada.id})">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

// Cambiar tipo de entrada
function cambiarTipo() {
    const tipo = document.getElementById('tipoEntrada').value;
    const seccionProveedor = document.getElementById('seccionProveedor');
    const seccionCliente = document.getElementById('seccionCliente');
    
    if (tipo === 'devolucion') {
        seccionProveedor.classList.add('d-none');
        seccionCliente.classList.remove('d-none');
    } else {
        seccionProveedor.classList.remove('d-none');
        seccionCliente.classList.add('d-none');
    }
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

// Agregar producto a la entrada
function agregarProducto() {
    contadorProductos++;
    const tbody = document.getElementById('tbodyProductos');
    const fila = document.createElement('tr');
    fila.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm" id="codigo_${contadorProductos}" 
                   onchange="buscarProducto(${contadorProductos})" placeholder="Código">
        </td>
        <td>
            <select class="form-select form-select-sm" id="producto_${contadorProductos}" 
                    onchange="cargarProductoSeleccionado(${contadorProductos})">
                <option value="">Seleccionar producto...</option>
                <option value="med001" data-precio="0.50">Ibuprofeno 400mg</option>
                <option value="med002" data-precio="0.30">Paracetamol 500mg</option>
                <option value="med003" data-precio="1.20">Amoxicilina 500mg</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" id="cantidad_${contadorProductos}" 
                   min="1" value="1" onchange="calcularTotales()">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" id="precio_${contadorProductos}" 
                   step="0.01" min="0" onchange="calcularTotales()">
        </td>
        <td id="total_${contadorProductos}" class="text-end fw-bold">S/ 0.00</td>
        <td>
            <input type="text" class="form-control form-control-sm" id="lote_${contadorProductos}" placeholder="Lote">
        </td>
        <td>
            <input type="date" class="form-control form-control-sm" id="vencimiento_${contadorProductos}">
        </td>
        <td>
            <span class="badge bg-success">OK</span>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarFila(${contadorProductos})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(fila);
}

// Buscar producto por código
function buscarProducto(id) {
    const codigo = document.getElementById(`codigo_${id}`).value;
    if (codigo) {
        // Simular búsqueda de producto
        const productos = {
            'MED001': {nombre: 'Ibuprofeno 400mg', precio: 0.50},
            'MED002': {nombre: 'Paracetamol 500mg', precio: 0.30},
            'MED003': {nombre: 'Amoxicilina 500mg', precio: 1.20}
        };
        
        const producto = productos[codigo.toUpperCase()];
        if (producto) {
            const select = document.getElementById(`producto_${id}`);
            const option = Array.from(select.options).find(opt => opt.textContent.includes(producto.nombre));
            if (option) {
                select.value = option.value;
                document.getElementById(`precio_${id}`).value = producto.precio;
                calcularTotales();
            }
        }
    }
}

// Cargar producto seleccionado
function cargarProductoSeleccionado(id) {
    const select = document.getElementById(`producto_${id}`);
    const option = select.options[select.selectedIndex];
    
    if (option && option.value) {
        const precio = option.dataset.precio;
        document.getElementById(`precio_${id}`).value = precio;
        calcularTotales();
    }
}

// Calcular totales
function calcularTotales() {
    let subtotal = 0;
    
    for (let i = 1; i <= contadorProductos; i++) {
        const cantidad = parseFloat(document.getElementById(`cantidad_${i}`)?.value || 0);
        const precio = parseFloat(document.getElementById(`precio_${i}`)?.value || 0);
        const total = cantidad * precio;
        
        if (total > 0) {
            subtotal += total;
            document.getElementById(`total_${i}`).textContent = `S/ ${total.toFixed(2)}`;
        }
    }
    
    const igv = subtotal * 0.18;
    const total = subtotal + igv;
    
    document.getElementById('subtotal').textContent = `S/ ${subtotal.toFixed(2)}`;
    document.getElementById('igv').textContent = `S/ ${igv.toFixed(2)}`;
    document.getElementById('total').textContent = `S/ ${total.toFixed(2)}`;
}

// Eliminar fila de producto
function eliminarFila(id) {
    document.getElementById(`codigo_${id}`).closest('tr').remove();
    calcularTotales();
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

// Ver entrada
function verEntrada(id) {
    const entrada = datosEntradas.find(e => e.id === id);
    if (!entrada) return;
    
    const contenido = `
        <div class="row">
            <div class="col-md-6">
                <h6>Información General</h6>
                <table class="table table-sm">
                    <tr><td><strong>Número:</strong></td><td>${entrada.numero}</td></tr>
                    <tr><td><strong>Fecha:</strong></td><td>${new Date(entrada.fecha).toLocaleString('es-ES')}</td></tr>
                    <tr><td><strong>Tipo:</strong></td><td>${obtenerBadgeTipo(entrada.tipo)}</td></tr>
                    <tr><td><strong>Proveedor:</strong></td><td>${entrada.proveedor || entrada.cliente}</td></tr>
                    <tr><td><strong>Estado:</strong></td><td><span class="badge ${obtenerClaseEstado(entrada.estado)}">${obtenerTextoEstado(entrada.estado)}</span></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Detalles Financieros</h6>
                <table class="table table-sm">
                    <tr><td><strong>Factura:</strong></td><td>${entrada.factura}</td></tr>
                    <tr><td><strong>Valor Total:</strong></td><td>S/ ${entrada.valor.toFixed(2)}</td></tr>
                    <tr><td><strong>Productos:</strong></td><td>${entrada.productos}</td></tr>
                    <tr><td><strong>Usuario:</strong></td><td>${entrada.usuario}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Productos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Total</th>
                                <th>Lote</th>
                                <th>Vencimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>MED001</td>
                                <td>Ibuprofeno 400mg</td>
                                <td>100</td>
                                <td>S/ 0.50</td>
                                <td>S/ 50.00</td>
                                <td>L240312A</td>
                                <td>15/08/2026</td>
                            </tr>
                            <tr>
                                <td>MED002</td>
                                <td>Paracetamol 500mg</td>
                                <td>50</td>
                                <td>S/ 0.30</td>
                                <td>S/ 15.00</td>
                                <td>L240325B</td>
                                <td>20/06/2026</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contenidoVerEntrada').innerHTML = contenido;
    $('#modalVerEntrada').modal('show');
}

// Editar entrada
function editarEntrada(id) {
    Swal.fire({
        title: 'Editar Entrada',
        text: 'Función de edición en desarrollo',
        icon: 'info',
        confirmButtonText: 'Aceptar'
    });
}

// Imprimir entrada
function imprimirEntrada(id) {
    window.print();
}

// Duplicar entrada
function duplicarEntrada(id) {
    Swal.fire({
        title: 'Duplicar Entrada',
        text: '¿Desea crear una nueva entrada basada en esta?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Duplicar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Cerrar modal actual y abrir nueva entrada
            $('#modalVerEntrada').modal('hide');
            $('#modalNuevaEntrada').modal('show');
        }
    });
}

// Cancelar entrada
function cancelarEntrada(id) {
    Swal.fire({
        title: 'Cancelar Entrada',
        text: '¿Está seguro de cancelar esta entrada?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No, mantener',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Cancelada',
                text: 'La entrada ha sido cancelada',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

// Eliminar entrada
function eliminarEntrada(id) {
    Swal.fire({
        title: 'Eliminar Entrada',
        text: '¿Está seguro de eliminar esta entrada? Esta acción no se puede deshacer.',
        icon: 'error',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Eliminada',
                text: 'La entrada ha sido eliminada',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Exportar entradas
function exportarEntradas() {
    Swal.fire({
        title: 'Exportar Entradas',
        text: 'Seleccione el formato de exportación',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Excel',
        denyButtonText: 'PDF',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('/export/entradas/excel', '_blank');
        } else if (result.isDenied) {
            window.open('/export/entradas/pdf', '_blank');
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

// Verificar entrada (solo para devoluciones)
function verificarEntrada(id) {
    Swal.fire({
        title: 'Verificar Entrada',
        text: '¿Ha verificado que todos los productos están correctos?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, verificar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Entrada Verificada',
                text: 'La entrada ha sido verificada exitosamente',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Rechazar entrada
function rechazarEntrada(id) {
    Swal.fire({
        title: 'Rechazar Entrada',
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
                text: 'La entrada ha sido rechazada',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Manejar formulario nueva entrada
document.getElementById('formNuevaEntrada').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const datos = {
        numero: document.getElementById('numeroEntrada').value,
        fecha: document.getElementById('fechaEntrada').value,
        tipo: document.getElementById('tipoEntrada').value,
        prioridad: document.getElementById('prioridad').value,
        proveedor: document.getElementById('proveedor').value,
        cliente: document.getElementById('cliente').value,
        total: document.getElementById('total').textContent,
        observaciones: document.getElementById('observaciones').value
    };
    
    // Validar que hay productos
    if (contadorProductos === 0) {
        Swal.fire({
            title: 'Error',
            text: 'Debe agregar al menos un producto',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    
    // Aquí se enviarían los datos al servidor
    console.log('Nueva entrada:', datos);
    
    Swal.fire({
        title: 'Entrada Registrada',
        text: 'La entrada de mercancía se ha registrado exitosamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        $('#modalNuevaEntrada').modal('hide');
        this.reset();
        // Recargar datos
        location.reload();
    });
});
</script>
@endsection