@extends('layouts.app')

@section('title', 'Salida de Mercancía - SIFANO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-arrow-up text-danger"></i>
                Salida de Mercancía
            </h1>
            <p class="text-muted">Registro y control de egresos de productos del inventario</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportarSalidas()">
                <i class="fas fa-file-export"></i> Exportar
            </button>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalNuevaSalida">
                <i class="fas fa-plus"></i> Nueva Salida
            </button>
        </div>
    </div>

    <!-- Estadísticas del Día -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Salidas Hoy
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="salidasHoy">12</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="valorHoy">S/ 3,680</div>
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
                                Mermas Hoy
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="mermasHoy">5</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                Productos Salidos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="productosSalidos">89</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
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
                    <label for="filtroTipo" class="form-label">Tipo de Salida</label>
                    <select class="form-select" id="filtroTipo">
                        <option value="">Todos los tipos</option>
                        <option value="venta">Venta</option>
                        <option value="merma">Merma</option>
                        <option value="vencido">Producto Vencido</option>
                        <option value="ajuste">Ajuste Negativo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="robo">Robo/Pérdida</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroDestino" class="form-label">Destino</label>
                    <select class="form-select" id="filtroDestino">
                        <option value="">Todos los destinos</option>
                        <option value="mostrador">Mostrador</option>
                        <option value="cliente">Cliente</option>
                        <option value="descarte">Descarte</option>
                        <option value="almacen">Almacén</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroEstado" class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="procesado">Procesado</option>
                        <option value="cancelado">Cancelado</option>
                        <option value="revisado">Revisado</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label for="busqueda" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="busqueda" placeholder="Número de salida, producto, motivo...">
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

    <!-- Lista de Salidas -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Registro de Salidas
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
                <table class="table table-bordered table-striped" id="tablaSalidas" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>N° Salida</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Destino</th>
                            <th>Productos</th>
                            <th>Cantidad Total</th>
                            <th>Valor Total</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-salida="1">
                            <td><strong>SAL-2024-001</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-calendar-alt text-danger"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">25 Oct 2024</div>
                                        <small class="text-muted">02:30 PM</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-cash-register"></i> Venta
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-store text-success"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Mostrador</div>
                                        <small class="text-muted">Ticket: #1234</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2">8</span>
                                    <div>
                                        <small>Ibuprofeno, Paracetamol, Aspirina</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">24</span>
                            </td>
                            <td class="text-end fw-bold">S/ 45.60</td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Procesado
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
                                    <button class="btn btn-sm btn-outline-primary" onclick="verSalida(1)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editarSalida(1)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="imprimirSalida(1)" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="duplicarSalida(1)">Duplicar</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="revertirSalida(1)">Revertir</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="eliminarSalida(1)">Eliminar</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr data-salida="2">
                            <td><strong>SAL-2024-002</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-calendar-alt text-warning"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">25 Oct 2024</div>
                                        <small class="text-muted">04:15 PM</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Merma
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-trash text-warning"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Descarte</div>
                                        <small class="text-muted">Por temperatura</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning me-2">2</span>
                                    <div>
                                        <small>Insulina, Vacunas</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">15</span>
                            </td>
                            <td class="text-end fw-bold text-warning">S/ 240.00</td>
                            <td>
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock"></i> Pendiente
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="bg-info rounded-circle d-flex align-items-center justify-content-center text-white fw-bold">
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
                                    <button class="btn btn-sm btn-outline-primary" onclick="verSalida(2)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="aprobarMerma(2)" title="Aprobar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="rechazarMerma(2)" title="Rechazar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Más salidas se cargarían dinámicamente -->
                    </tbody>
                </table>
            </div>

            <div id="vistaTarjetas" class="row d-none">
                <!-- Vista en tarjetas se llenará dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Gráfico de Salidas -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area"></i> Evolución de Salidas (Últimos 30 días)
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
                        <canvas id="graficoSalidas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Salidas por Tipo
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

    <!-- Alertas de Stock Crítico -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-header py-3 bg-warning">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-exclamation-triangle"></i> Alertas de Stock Crítico
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Stock Actual</th>
                                    <th>Stock Mínimo</th>
                                    <th>Diferencia</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>MED002</strong></td>
                                    <td>Paracetamol 500mg</td>
                                    <td><span class="text-danger fw-bold">25</span></td>
                                    <td>50</td>
                                    <td><span class="text-danger">-25</span></td>
                                    <td><span class="badge bg-danger">Crítico</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="solicitarReposicion('MED002')">
                                            <i class="fas fa-shopping-cart"></i> Solicitar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>DIS001</strong></td>
                                    <td>Jeringa 5ml Estéril</td>
                                    <td><span class="text-danger fw-bold">8</span></td>
                                    <td>50</td>
                                    <td><span class="text-danger">-42</span></td>
                                    <td><span class="badge bg-danger">Crítico</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="solicitarReposicion('DIS001')">
                                            <i class="fas fa-shopping-cart"></i> Solicitar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>MED005</strong></td>
                                    <td>Loratadina 10mg</td>
                                    <td><span class="text-warning fw-bold">12</span></td>
                                    <td>30</td>
                                    <td><span class="text-warning">-18</span></td>
                                    <td><span class="badge bg-warning">Bajo</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="solicitarReposicion('MED005')">
                                            <i class="fas fa-shopping-cart"></i> Solicitar
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

<!-- Modal Nueva Salida -->
<div class="modal fade" id="modalNuevaSalida" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus text-danger"></i> Nueva Salida de Mercancía
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNuevaSalida">
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
                                <label for="numeroSalida" class="form-label">N° Salida</label>
                                <input type="text" class="form-control" id="numeroSalida" value="SAL-2024-003" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="fechaSalida" class="form-label">Fecha</label>
                                <input type="datetime-local" class="form-control" id="fechaSalida" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="tipoSalida" class="form-label">Tipo de Salida</label>
                                <select class="form-select" id="tipoSalida" required onchange="cambiarTipoSalida()">
                                    <option value="">Seleccionar...</option>
                                    <option value="venta">Venta</option>
                                    <option value="merma">Merma</option>
                                    <option value="vencido">Producto Vencido</option>
                                    <option value="ajuste">Ajuste Negativo</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="robo">Robo/Pérdida</option>
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

                    <!-- Información del Destino -->
                    <div class="row mb-4" id="seccionVenta">
                        <div class="col-12">
                            <h6 class="text-success border-bottom pb-2 mb-3">
                                <i class="fas fa-store"></i> Información de Venta
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cliente" class="form-label">Cliente (Opcional)</label>
                                <input type="text" class="form-control" id="cliente" placeholder="Nombre del cliente">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="dniCliente" class="form-label">DNI Cliente</label>
                                <input type="text" class="form-control" id="dniCliente" maxlength="8">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="ticketVenta" class="form-label">N° Ticket</label>
                                <input type="text" class="form-control" id="ticketVenta">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4 d-none" id="seccionMerma">
                        <div class="col-12">
                            <h6 class="text-warning border-bottom pb-2 mb-3">
                                <i class="fas fa-exclamation-triangle"></i> Detalle de Merma
                            </h6>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tipoMerma" class="form-label">Tipo de Merma</label>
                                <select class="form-select" id="tipoMerma">
                                    <option value="">Seleccionar...</option>
                                    <option value="temperatura">Por Temperatura</option>
                                    <option value="vencimiento">Por Vencimiento</option>
                                    <option value="calidad">Por Calidad</option>
                                    <option value="robo">Robo/Pérdida</option>
                                    <option value="accidente">Accidente</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="lugarMerma" class="form-label">Lugar del Incidente</label>
                                <input type="text" class="form-control" id="lugarMerma" placeholder="Almacén, mostrador, etc.">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="supervisor" class="form-label">Supervisor</label>
                                <input type="text" class="form-control" id="supervisor" placeholder="Nombre del supervisor">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4 d-none" id="seccionGeneral">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-building"></i> Información General
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="destino" class="form-label">Destino</label>
                                <select class="form-select" id="destino">
                                    <option value="">Seleccionar destino...</option>
                                    <option value="mostrador">Mostrador</option>
                                    <option value="almacen">Almacén</option>
                                    <option value="descarte">Descarte</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="responsable" class="form-label">Responsable</label>
                                <input type="text" class="form-control" id="responsable" placeholder="Nombre del responsable">
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
                                <table class="table table-bordered" id="tablaProductosSalida">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th>Stock Disp.</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unit.</th>
                                            <th>Total</th>
                                            <th>Lote</th>
                                            <th>Vencimiento</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyProductosSalida">
                                        <!-- Productos se agregan dinámicamente -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-end fw-bold">Total:</td>
                                            <td id="totalSalida" class="text-end fw-bold fs-5">S/ 0.00</td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <button type="button" class="btn btn-outline-danger" onclick="agregarProductoSalida()">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </button>
                        </div>
                    </div>

                    <!-- Motivo/Observaciones -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-comment"></i> Motivo y Observaciones
                            </h6>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="motivo" class="form-label">Motivo de la Salida</label>
                                <textarea class="form-control" id="motivo" rows="3" required 
                                          placeholder="Explique el motivo de esta salida..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones Adicionales</label>
                                <textarea class="form-control" id="observaciones" rows="2" 
                                          placeholder="Notas adicionales, condiciones especiales, etc."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Validaciones -->
                    <div class="alert alert-warning d-none" id="alertValidacion">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atención:</strong> <span id="mensajeValidacion"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary" onclick="guardarBorradorSalida()">
                        <i class="fas fa-save"></i> Guardar Borrador
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-arrow-up"></i> Confirmar Salida
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Salida -->
<div class="modal fade" id="modalVerSalida" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalles de Salida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoVerSalida">
                <!-- Se carga dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="imprimirSalida()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button type="button" class="btn btn-primary" onclick="editarSalida()">
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
let tablaSalidas;
let datosSalidas = [];
let contadorProductosSalida = 0;

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
    document.getElementById('fechaSalida').value = fechaFormateada;
}

// Inicializar DataTable
function inicializarTabla() {
    tablaSalidas = $('#tablaSalidas').DataTable({
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
        tablaSalidas.search($(this).val()).draw();
    });

    // Filtros
    $('#filtroFecha, #filtroTipo, #filtroDestino, #filtroEstado').on('change', aplicarFiltros);
}

// Inicializar gráficos
function inicializarGraficos() {
    // Gráfico de evolución de salidas
    const ctx1 = document.getElementById('graficoSalidas').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['Sep 26', 'Sep 27', 'Sep 28', 'Sep 29', 'Sep 30', 'Oct 1', 'Oct 2', 'Oct 3', 'Oct 4', 'Oct 5'],
            datasets: [
                {
                    label: 'Número de Salidas',
                    data: [8, 12, 6, 15, 9, 11, 7, 18, 10, 14],
                    borderColor: 'rgb(231, 74, 59)',
                    backgroundColor: 'rgba(231, 74, 59, 0.1)',
                    tension: 0.1,
                    yAxisID: 'y'
                },
                {
                    label: 'Valor Total (S/)',
                    data: [2800, 4200, 1950, 5600, 3300, 4100, 2450, 6300, 3680, 4900],
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
                        text: 'Número de Salidas'
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
            labels: ['Ventas', 'Mermas', 'Vencidos', 'Ajustes', 'Transferencias', 'Robo/Pérdida'],
            datasets: [{
                data: [70, 15, 8, 4, 2, 1],
                backgroundColor: [
                    'rgb(28, 200, 138)',
                    'rgb(255, 193, 7)',
                    'rgb(231, 74, 59)',
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
    datosSalidas = [
        {
            id: 1,
            numero: 'SAL-2024-001',
            fecha: '2024-10-25 14:30:00',
            tipo: 'venta',
            destino: 'mostrador',
            productos: 8,
            cantidad: 24,
            valor: 45.60,
            estado: 'procesado',
            usuario: 'Carlos Sánchez'
        },
        {
            id: 2,
            numero: 'SAL-2024-002',
            fecha: '2024-10-25 16:15:00',
            tipo: 'merma',
            destino: 'descarte',
            productos: 2,
            cantidad: 15,
            valor: 240.00,
            estado: 'pendiente',
            usuario: 'Ana María'
        }
    ];
    
    actualizarEstadisticas();
}

// Actualizar estadísticas
function actualizarEstadisticas() {
    const hoy = new Date().toDateString();
    
    // Filtrar salidas de hoy
    const salidasHoy = datosSalidas.filter(salida => {
        return new Date(salida.fecha).toDateString() === hoy;
    });
    
    // Actualizar contadores
    document.getElementById('salidasHoy').textContent = salidasHoy.length;
    document.getElementById('valorHoy').textContent = 'S/ ' + salidasHoy.reduce((sum, s) => sum + s.valor, 0).toLocaleString();
    document.getElementById('productosSalidos').textContent = salidasHoy.reduce((sum, s) => sum + s.cantidad, 0);
    
    // Mermas de hoy
    const mermasHoy = salidasHoy.filter(s => s.tipo === 'merma').length;
    document.getElementById('mermasHoy').textContent = mermasHoy;
}

// Aplicar filtros
function aplicarFiltros() {
    const fecha = $('#filtroFecha').val();
    const tipo = $('#filtroTipo').val();
    const destino = $('#filtroDestino').val();
    const estado = $('#filtroEstado').val();
    
    tablaSalidas.clear().rows.add(filtrarDatos(fecha, tipo, destino, estado)).draw();
}

// Filtrar datos
function filtrarDatos(fecha, tipo, destino, estado) {
    let datos = datosSalidas;
    
    if (fecha) {
        datos = datos.filter(item => {
            const fechaSalida = new Date(item.fecha).toISOString().split('T')[0];
            return fechaSalida === fecha;
        });
    }
    
    if (tipo) {
        datos = datos.filter(item => item.tipo === tipo);
    }
    
    if (destino) {
        datos = datos.filter(item => item.destino === destino);
    }
    
    if (estado) {
        datos = datos.filter(item => item.estado === estado);
    }
    
    return datos.map(item => [
        `<strong>${item.numero}</strong>`,
        formatearFechaCompleta(item.fecha),
        obtenerBadgeTipo(item.tipo),
        obtenerInfoDestino(item.destino),
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
            <div class="me-2"><i class="fas fa-calendar-alt text-danger"></i></div>
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
        'venta': '<span class="badge bg-success"><i class="fas fa-cash-register"></i> Venta</span>',
        'merma': '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle"></i> Merma</span>',
        'vencido': '<span class="badge bg-danger"><i class="fas fa-calendar-times"></i> Vencido</span>',
        'ajuste': '<span class="badge bg-primary"><i class="fas fa-edit"></i> Ajuste</span>',
        'transferencia': '<span class="badge bg-info"><i class="fas fa-exchange-alt"></i> Transferencia</span>',
        'robo': '<span class="badge bg-dark"><i class="fas fa-user-secret"></i> Robo/Pérdida</span>'
    };
    return badges[tipo] || tipo;
}

// Obtener info del destino
function obtenerInfoDestino(destino) {
    const destinos = {
        'mostrador': {
            icon: 'fas fa-store text-success',
            nombre: 'Mostrador',
            detalle: 'Ticket: #1234'
        },
        'descarte': {
            icon: 'fas fa-trash text-warning',
            nombre: 'Descarte',
            detalle: 'Por temperatura'
        },
        'almacen': {
            icon: 'fas fa-warehouse text-info',
            nombre: 'Almacén',
            detalle: 'Transferencia'
        }
    };
    
    const info = destinos[destino];
    if (info) {
        return `
            <div class="d-flex align-items-center">
                <div class="me-2"><i class="${info.icon}"></i></div>
                <div>
                    <div class="fw-bold">${info.nombre}</div>
                    <small class="text-muted">${info.detalle}</small>
                </div>
            </div>
        `;
    }
    return 'N/A';
}

// Obtener clase para estado
function obtenerClaseEstado(estado) {
    const clases = {
        'pendiente': 'bg-warning',
        'procesado': 'bg-success',
        'cancelado': 'bg-danger',
        'revisado': 'bg-info'
    };
    return clases[estado] || 'bg-secondary';
}

// Obtener texto para estado
function obtenerTextoEstado(estado) {
    const textos = {
        'pendiente': 'Pendiente',
        'procesado': 'Procesado',
        'cancelado': 'Cancelado',
        'revisado': 'Revisado'
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
            <button class="btn btn-sm btn-outline-primary" onclick="verSalida(${id})" title="Ver Detalles">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="editarSalida(${id})" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="imprimirSalida(${id})" title="Imprimir">
                <i class="fas fa-print"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="duplicarSalida(${id})">Duplicar</a></li>
                    <li><a class="dropdown-item" href="#" onclick="revertirSalida(${id})">Revertir</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="eliminarSalida(${id})">Eliminar</a></li>
                </ul>
            </div>
        </div>
    `;
}

// Limpiar filtros
function limpiarFiltros() {
    $('#filtroFecha, #filtroTipo, #filtroDestino, #filtroEstado').val('');
    $('#busqueda').val('');
    tablaSalidas.search('').columns().search('').draw();
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
    
    datosSalidas.forEach(salida => {
        const card = document.createElement('div');
        card.className = 'col-xl-4 col-lg-6 col-md-12 mb-4';
        card.innerHTML = `
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">${salida.numero}</h6>
                    <span class="badge ${obtenerClaseEstado(salida.estado)}">${obtenerTextoEstado(salida.estado)}</span>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        ${obtenerBadgeTipo(salida.tipo)}
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-calendar-alt text-muted"></i>
                        ${new Date(salida.fecha).toLocaleDateString('es-ES')}
                        <small class="text-muted ms-2">
                            ${new Date(salida.fecha).toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})}
                        </small>
                    </div>
                    <div class="mb-2">
                        <strong>Destino:</strong> ${salida.destino}
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary">${salida.cantidad} productos</span>
                        <span class="fw-bold ${salida.tipo === 'merma' ? 'text-warning' : 'text-success'}">S/ ${salida.valor.toFixed(2)}</span>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="verSalida(${salida.id})">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="imprimirSalida(${salida.id})">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

// Cambiar tipo de salida
function cambiarTipoSalida() {
    const tipo = document.getElementById('tipoSalida').value;
    const seccionVenta = document.getElementById('seccionVenta');
    const seccionMerma = document.getElementById('seccionMerma');
    const seccionGeneral = document.getElementById('seccionGeneral');
    
    // Ocultar todas las secciones
    seccionVenta.classList.add('d-none');
    seccionMerma.classList.add('d-none');
    seccionGeneral.classList.add('d-none');
    
    // Mostrar sección correspondiente
    if (tipo === 'venta') {
        seccionVenta.classList.remove('d-none');
    } else if (tipo === 'merma' || tipo === 'vencido') {
        seccionMerma.classList.remove('d-none');
    } else if (tipo) {
        seccionGeneral.classList.remove('d-none');
    }
}

// Agregar producto a la salida
function agregarProductoSalida() {
    contadorProductosSalida++;
    const tbody = document.getElementById('tbodyProductosSalida');
    const fila = document.createElement('tr');
    fila.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm" id="codigo_${contadorProductosSalida}" 
                   onchange="buscarProductoSalida(${contadorProductosSalida})" placeholder="Código">
        </td>
        <td>
            <select class="form-select form-select-sm" id="producto_${contadorProductosSalida}" 
                    onchange="cargarProductoSalidaSeleccionado(${contadorProductosSalida})">
                <option value="">Seleccionar producto...</option>
                <option value="med001" data-precio="0.50" data-stock="850" data-lote="L240312A" data-vencimiento="2026-08-15">Ibuprofeno 400mg</option>
                <option value="med002" data-precio="0.30" data-stock="25" data-lote="L240325B" data-vencimiento="2026-06-20">Paracetamol 500mg</option>
                <option value="med003" data-precio="1.20" data-stock="120" data-lote="L240401C" data-vencimiento="2026-12-31">Amoxicilina 500mg</option>
            </select>
        </td>
        <td id="stock_${contadorProductosSalida}" class="text-center">
            <span class="badge bg-secondary">0</span>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" id="cantidad_${contadorProductosSalida}" 
                   min="1" value="1" onchange="validarStock(${contadorProductosSalida}); calcularTotalesSalida()">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" id="precio_${contadorProductosSalida}" 
                   step="0.01" min="0" onchange="calcularTotalesSalida()">
        </td>
        <td id="total_${contadorProductosSalida}" class="text-end fw-bold">S/ 0.00</td>
        <td>
            <input type="text" class="form-control form-control-sm" id="lote_${contadorProductosSalida}" placeholder="Lote">
        </td>
        <td>
            <input type="date" class="form-control form-control-sm" id="vencimiento_${contadorProductosSalida}">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarFilaSalida(${contadorProductosSalida})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(fila);
}

// Buscar producto por código
function buscarProductoSalida(id) {
    const codigo = document.getElementById(`codigo_${id}`).value;
    if (codigo) {
        // Simular búsqueda de producto
        const productos = {
            'MED001': {nombre: 'Ibuprofeno 400mg', precio: 0.50, stock: 850, lote: 'L240312A', vencimiento: '2026-08-15'},
            'MED002': {nombre: 'Paracetamol 500mg', precio: 0.30, stock: 25, lote: 'L240325B', vencimiento: '2026-06-20'},
            'MED003': {nombre: 'Amoxicilina 500mg', precio: 1.20, stock: 120, lote: 'L240401C', vencimiento: '2026-12-31'}
        };
        
        const producto = productos[codigo.toUpperCase()];
        if (producto) {
            const select = document.getElementById(`producto_${id}`);
            const option = Array.from(select.options).find(opt => opt.textContent.includes(producto.nombre));
            if (option) {
                select.value = option.value;
                cargarProductoSalidaSeleccionado(id);
            }
        }
    }
}

// Cargar producto seleccionado
function cargarProductoSalidaSeleccionado(id) {
    const select = document.getElementById(`producto_${id}`);
    const option = select.options[select.selectedIndex];
    
    if (option && option.value) {
        const precio = option.dataset.precio;
        const stock = option.dataset.stock;
        const lote = option.dataset.lote;
        const vencimiento = option.dataset.vencimiento;
        
        document.getElementById(`precio_${id}`).value = precio;
        document.getElementById(`stock_${id}`).innerHTML = `<span class="badge bg-${stock < 50 ? 'danger' : 'success'}">${stock}</span>`;
        document.getElementById(`lote_${id}`).value = lote;
        document.getElementById(`vencimiento_${id}`).value = vencimiento;
        
        // Establecer máximo de cantidad según stock
        document.getElementById(`cantidad_${id}`).max = stock;
        calcularTotalesSalida();
    }
}

// Validar stock disponible
function validarStock(id) {
    const cantidad = parseInt(document.getElementById(`cantidad_${id}`).value || 0);
    const stockBadge = document.getElementById(`stock_${id}`).querySelector('.badge');
    const stock = parseInt(stockBadge.textContent);
    
    if (cantidad > stock) {
        document.getElementById(`cantidad_${id}`).value = stock;
        mostrarValidacion(`La cantidad no puede ser mayor al stock disponible (${stock}).`);
        return false;
    } else if (stock < 50) {
        mostrarValidacion(`Advertencia: Stock bajo para este producto (${stock} unidades).`);
    } else {
        ocultarValidacion();
    }
    return true;
}

// Calcular totales
function calcularTotalesSalida() {
    let total = 0;
    
    for (let i = 1; i <= contadorProductosSalida; i++) {
        const cantidad = parseFloat(document.getElementById(`cantidad_${i}`)?.value || 0);
        const precio = parseFloat(document.getElementById(`precio_${i}`)?.value || 0);
        const totalProducto = cantidad * precio;
        
        if (totalProducto > 0) {
            total += totalProducto;
            document.getElementById(`total_${i}`).textContent = `S/ ${totalProducto.toFixed(2)}`;
        }
    }
    
    document.getElementById('totalSalida').textContent = `S/ ${total.toFixed(2)}`;
}

// Eliminar fila de producto
function eliminarFilaSalida(id) {
    document.getElementById(`codigo_${id}`).closest('tr').remove();
    calcularTotalesSalida();
}

// Mostrar validación
function mostrarValidacion(mensaje) {
    const alert = document.getElementById('alertValidacion');
    const mensajeEl = document.getElementById('mensajeValidacion');
    mensajeEl.textContent = mensaje;
    alert.classList.remove('d-none');
}

// Ocultar validación
function ocultarValidacion() {
    document.getElementById('alertValidacion').classList.add('d-none');
}

// Guardar como borrador
function guardarBorradorSalida() {
    Swal.fire({
        title: 'Guardar Borrador',
        text: '¿Desea guardar esta salida como borrador?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Guardado',
                text: 'La salida se ha guardado como borrador',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

// Ver salida
function verSalida(id) {
    const salida = datosSalidas.find(s => s.id === id);
    if (!salida) return;
    
    const contenido = `
        <div class="row">
            <div class="col-md-6">
                <h6>Información General</h6>
                <table class="table table-sm">
                    <tr><td><strong>Número:</strong></td><td>${salida.numero}</td></tr>
                    <tr><td><strong>Fecha:</strong></td><td>${new Date(salida.fecha).toLocaleString('es-ES')}</td></tr>
                    <tr><td><strong>Tipo:</strong></td><td>${obtenerBadgeTipo(salida.tipo)}</td></tr>
                    <tr><td><strong>Destino:</strong></td><td>${salida.destino}</td></tr>
                    <tr><td><strong>Estado:</strong></td><td><span class="badge ${obtenerClaseEstado(salida.estado)}">${obtenerTextoEstado(salida.estado)}</span></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Detalles</h6>
                <table class="table table-sm">
                    <tr><td><strong>Cantidad Total:</strong></td><td>${salida.cantidad}</td></tr>
                    <tr><td><strong>Productos:</strong></td><td>${salida.productos}</td></tr>
                    <tr><td><strong>Valor Total:</strong></td><td>S/ ${salida.valor.toFixed(2)}</td></tr>
                    <tr><td><strong>Usuario:</strong></td><td>${salida.usuario}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Productos Salidos</h6>
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
                                <td>10</td>
                                <td>S/ 0.50</td>
                                <td>S/ 5.00</td>
                                <td>L240312A</td>
                                <td>15/08/2026</td>
                            </tr>
                            <tr>
                                <td>MED002</td>
                                <td>Paracetamol 500mg</td>
                                <td>14</td>
                                <td>S/ 0.30</td>
                                <td>S/ 4.20</td>
                                <td>L240325B</td>
                                <td>20/06/2026</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contenidoVerSalida').innerHTML = contenido;
    $('#modalVerSalida').modal('show');
}

// Editar salida
function editarSalida(id) {
    Swal.fire({
        title: 'Editar Salida',
        text: 'Función de edición en desarrollo',
        icon: 'info',
        confirmButtonText: 'Aceptar'
    });
}

// Imprimir salida
function imprimirSalida(id) {
    window.print();
}

// Duplicar salida
function duplicarSalida(id) {
    Swal.fire({
        title: 'Duplicar Salida',
        text: '¿Desea crear una nueva salida basada en esta?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Duplicar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#modalVerSalida').modal('hide');
            $('#modalNuevaSalida').modal('show');
        }
    });
}

// Revertir salida
function revertirSalida(id) {
    Swal.fire({
        title: 'Revertir Salida',
        text: '¿Desea revertir esta salida? El stock será restituido.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, revertir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Revertida',
                text: 'La salida ha sido revertida exitosamente',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Eliminar salida
function eliminarSalida(id) {
    Swal.fire({
        title: 'Eliminar Salida',
        text: '¿Está seguro de eliminar esta salida? Esta acción no se puede deshacer.',
        icon: 'error',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Eliminada',
                text: 'La salida ha sido eliminada',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Exportar salidas
function exportarSalidas() {
    Swal.fire({
        title: 'Exportar Salidas',
        text: 'Seleccione el formato de exportación',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Excel',
        denyButtonText: 'PDF',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('/export/salidas/excel', '_blank');
        } else if (result.isDenied) {
            window.open('/export/salidas/pdf', '_blank');
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

// Solicitar reposición
function solicitarReposicion(codigo) {
    Swal.fire({
        title: 'Solicitar Reposición',
        text: `¿Desea generar una solicitud de reposición para ${codigo}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Solicitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Solicitud Generada',
                text: 'La solicitud de reposición ha sido generada',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

// Aprobar merma
function aprobarMerma(id) {
    Swal.fire({
        title: 'Aprobar Merma',
        text: '¿Ha verificado la merma y desea aprobarla?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Merma Aprobada',
                text: 'La merma ha sido aprobada exitosamente',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Rechazar merma
function rechazarMerma(id) {
    Swal.fire({
        title: 'Rechazar Merma',
        text: '¿Por qué rechaza esta merma?',
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
                title: 'Merma Rechazada',
                text: 'La merma ha sido rechazada',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Manejar formulario nueva salida
document.getElementById('formNuevaSalida').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const datos = {
        numero: document.getElementById('numeroSalida').value,
        fecha: document.getElementById('fechaSalida').value,
        tipo: document.getElementById('tipoSalida').value,
        prioridad: document.getElementById('prioridad').value,
        motivo: document.getElementById('motivo').value,
        total: document.getElementById('totalSalida').textContent,
        observaciones: document.getElementById('observaciones').value
    };
    
    // Validar que hay productos
    if (contadorProductosSalida === 0) {
        Swal.fire({
            title: 'Error',
            text: 'Debe agregar al menos un producto',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    
    // Validar motivo
    if (!datos.motivo.trim()) {
        Swal.fire({
            title: 'Error',
            text: 'Debe ingresar el motivo de la salida',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    
    // Aquí se enviarían los datos al servidor
    console.log('Nueva salida:', datos);
    
    Swal.fire({
        title: 'Salida Registrada',
        text: 'La salida de mercancía se ha registrado exitosamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        $('#modalNuevaSalida').modal('hide');
        this.reset();
        // Recargar datos
        location.reload();
    });
});
</script>
@endsection