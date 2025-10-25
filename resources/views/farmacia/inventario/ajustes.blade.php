@extends('layouts.app')

@section('title', 'Ajustes de Inventario - SIFANO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-balance-scale text-info"></i>
                Ajustes de Inventario
            </h1>
            <p class="text-muted">Correcciones y ajustes en el inventario por diferencias físicas</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportarAjustes()">
                <i class="fas fa-file-export"></i> Exportar
            </button>
            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalNuevoAjuste">
                <i class="fas fa-plus"></i> Nuevo Ajuste
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
                                Ajustes Este Mes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="ajustesMes">18</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
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
                                Valor Ajustado Positivo
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="ajustePositivo">S/ 2,340</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
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
                                Valor Ajustado Negativo
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="ajusteNegativo">S/ 1,890</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
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
                                Diferencia Neta
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="diferenciaNeta">S/ 450</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-equals fa-2x text-gray-300"></i>
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
                    <label for="filtroTipo" class="form-label">Tipo de Ajuste</label>
                    <select class="form-select" id="filtroTipo">
                        <option value="">Todos los tipos</option>
                        <option value="positivo">Ajuste Positivo</option>
                        <option value="negativo">Ajuste Negativo</option>
                        <option value="contaje">Ajuste por Contaje</option>
                        <option value="inventario">Ajuste por Inventario</option>
                        <option value="auditoria">Ajuste por Auditoría</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroEstado" class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="rechazado">Rechazado</option>
                        <option value="procesado">Procesado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroSupervisor" class="form-label">Supervisor</label>
                    <select class="form-select" id="filtroSupervisor">
                        <option value="">Todos</option>
                        <option value="ana">Ana María</option>
                        <option value="carlos">Carlos Sánchez</option>
                        <option value="luis">Luis Rodríguez</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label for="busqueda" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="busqueda" placeholder="Número de ajuste, producto, motivo...">
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

    <!-- Lista de Ajustes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Registro de Ajustes
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
                <table class="table table-bordered table-striped" id="tablaAjustes" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>N° Ajuste</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Motivo</th>
                            <th>Productos</th>
                            <th>Stock Anterior</th>
                            <th>Stock Nuevo</th>
                            <th>Diferencia</th>
                            <th>Valor</th>
                            <th>Estado</th>
                            <th>Supervisor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-ajuste="1">
                            <td><strong>AJ-2024-001</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-calendar-alt text-info"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">25 Oct 2024</div>
                                        <small class="text-muted">10:45 AM</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-arrow-up"></i> Positivo
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-clipboard-check text-success"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Contaje Mensual</div>
                                        <small class="text-muted">Inventario general</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2">5</span>
                                    <div>
                                        <small>Ibuprofeno, Paracetamol...</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">1,247</td>
                            <td class="text-center">1,263</td>
                            <td class="text-center">
                                <span class="text-success fw-bold">+16</span>
                            </td>
                            <td class="text-end fw-bold text-success">S/ +45.60</td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Aprobado
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
                                        <small class="text-muted">Supervisor</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verAjuste(1)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editarAjuste(1)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="imprimirAjuste(1)" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="aprobarAjuste(1)">Aprobar</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="rechazarAjuste(1)">Rechazar</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="eliminarAjuste(1)">Eliminar</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr data-ajuste="2">
                            <td><strong>AJ-2024-002</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-calendar-alt text-warning"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">25 Oct 2024</div>
                                        <small class="text-muted">03:20 PM</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-danger">
                                    <i class="fas fa-arrow-down"></i> Negativo
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Merma No Registrada</div>
                                        <small class="text-muted">Productos vencidos</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning me-2">3</span>
                                    <div>
                                        <small>Insulina, Vacunas</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">850</td>
                            <td class="text-center">835</td>
                            <td class="text-center">
                                <span class="text-danger fw-bold">-15</span>
                            </td>
                            <td class="text-end fw-bold text-danger">S/ -240.00</td>
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
                                        <small class="text-muted">Supervisor</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verAjuste(2)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="aprobarAjuste(2)" title="Aprobar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="rechazarAjuste(2)" title="Rechazar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Más ajustes se cargarían dinámicamente -->
                    </tbody>
                </table>
            </div>

            <div id="vistaTarjetas" class="row d-none">
                <!-- Vista en tarjetas se llenará dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Gráficos de Ajustes -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area"></i> Tendencia de Ajustes (Últimos 6 meses)
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
                        <canvas id="graficoAjustes"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Distribución por Tipo
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

    <!-- Alertas de Discrepancias -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-header py-3 bg-warning">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-exclamation-triangle"></i> Discrepancias Detectadas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Stock Sistema</th>
                                    <th>Stock Físico</th>
                                    <th>Diferencia</th>
                                    <th>Valor Diferencia</th>
                                    <th>Último Ajuste</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>MED002</strong></td>
                                    <td>Paracetamol 500mg</td>
                                    <td><span class="text-info">25</span></td>
                                    <td><span class="text-warning">22</span></td>
                                    <td><span class="text-danger">-3</span></td>
                                    <td><span class="text-danger">S/ -0.90</span></td>
                                    <td>15/10/2024</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="generarAjuste('MED002')">
                                            <i class="fas fa-plus"></i> Ajustar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>DIS005</strong></td>
                                    <td>Guantes Desechables</td>
                                    <td><span class="text-info">200</span></td>
                                    <td><span class="text-success">215</span></td>
                                    <td><span class="text-success">+15</span></td>
                                    <td><span class="text-success">S/ +15.00</span></td>
                                    <td>20/09/2024</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="generarAjuste('DIS005')">
                                            <i class="fas fa-plus"></i> Ajustar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>MED008</strong></td>
                                    <td>Dexametasona 4mg</td>
                                    <td><span class="text-info">45</span></td>
                                    <td><span class="text-danger">38</span></td>
                                    <td><span class="text-danger">-7</span></td>
                                    <td><span class="text-danger">S/ -21.00</span></td>
                                    <td>Nunca</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="generarAjuste('MED008')">
                                            <i class="fas fa-plus"></i> Ajustar
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

<!-- Modal Nuevo Ajuste -->
<div class="modal fade" id="modalNuevoAjuste" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus text-info"></i> Nuevo Ajuste de Inventario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNuevoAjuste">
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
                                <label for="numeroAjuste" class="form-label">N° Ajuste</label>
                                <input type="text" class="form-control" id="numeroAjuste" value="AJ-2024-003" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="fechaAjuste" class="form-label">Fecha</label>
                                <input type="datetime-local" class="form-control" id="fechaAjuste" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="tipoAjuste" class="form-label">Tipo de Ajuste</label>
                                <select class="form-select" id="tipoAjuste" required onchange="cambiarTipoAjuste()">
                                    <option value="">Seleccionar...</option>
                                    <option value="positivo">Ajuste Positivo</option>
                                    <option value="negativo">Ajuste Negativo</option>
                                    <option value="contaje">Ajuste por Contaje</option>
                                    <option value="inventario">Ajuste por Inventario</option>
                                    <option value="auditoria">Ajuste por Auditoría</option>
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

                    <!-- Información de Contaje/Inventario -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-clipboard-check"></i> Información del Contaje
                            </h6>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="fechaContaje" class="form-label">Fecha del Contaje</label>
                                <input type="date" class="form-control" id="fechaContaje">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tipoContaje" class="form-label">Tipo de Contaje</label>
                                <select class="form-select" id="tipoContaje">
                                    <option value="">Seleccionar...</option>
                                    <option value="completo">Inventario Completo</option>
                                    <option value="parcial">Inventario Parcial</option>
                                    <option value="categoria">Por Categoría</option>
                                    <option value="ubicacion">Por Ubicación</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="supervisor" class="form-label">Supervisor</label>
                                <input type="text" class="form-control" id="supervisor" placeholder="Nombre del supervisor">
                            </div>
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-pills"></i> Productos a Ajustar
                            </h6>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tablaProductosAjuste">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th>Stock Sistema</th>
                                            <th>Stock Físico</th>
                                            <th>Diferencia</th>
                                            <th>Precio Unit.</th>
                                            <th>Valor Ajuste</th>
                                            <th>Lote</th>
                                            <th>Observaciones</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyProductosAjuste">
                                        <!-- Productos se agregan dinámicamente -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-end fw-bold">Total Ajustes Positivos:</td>
                                            <td colspan="2" id="totalPositivo" class="text-end fw-bold text-success">S/ 0.00</td>
                                            <td colspan="3"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-end fw-bold">Total Ajustes Negativos:</td>
                                            <td colspan="2" id="totalNegativo" class="text-end fw-bold text-danger">S/ 0.00</td>
                                            <td colspan="3"></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="5" class="text-end fw-bold">Total Neto:</td>
                                            <td colspan="2" id="totalNeto" class="text-end fw-bold fs-5">S/ 0.00</td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <button type="button" class="btn btn-outline-info" onclick="agregarProductoAjuste()">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </button>
                            <button type="button" class="btn btn-outline-warning ms-2" onclick="importarExcel()">
                                <i class="fas fa-file-excel"></i> Importar Excel
                            </button>
                        </div>
                    </div>

                    <!-- Justificación y Observaciones -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-comment"></i> Justificación y Observaciones
                            </h6>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="justificacion" class="form-label">Justificación del Ajuste</label>
                                <textarea class="form-control" id="justificacion" rows="3" required 
                                          placeholder="Explique detalladamente las razones de este ajuste..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones Adicionales</label>
                                <textarea class="form-control" id="observaciones" rows="2" 
                                          placeholder="Notas adicionales, condiciones especiales, etc."></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="adjuntos" class="form-label">Adjuntos (Opcional)</label>
                                <input type="file" class="form-control" id="adjuntos" multiple accept=".pdf,.jpg,.jpeg,.png">
                                <small class="form-text text-muted">Puede adjuntar fotos, documentos de soporte, etc.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Validaciones -->
                    <div class="alert alert-info d-none" id="alertInfo">
                        <i class="fas fa-info-circle"></i>
                        <strong>Información:</strong> <span id="mensajeInfo"></span>
                    </div>
                    <div class="alert alert-warning d-none" id="alertAdvertencia">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Advertencia:</strong> <span id="mensajeAdvertencia"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary" onclick="guardarBorradorAjuste()">
                        <i class="fas fa-save"></i> Guardar Borrador
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-check"></i> Enviar para Aprobación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Ajuste -->
<div class="modal fade" id="modalVerAjuste" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalles del Ajuste
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoVerAjuste">
                <!-- Se carga dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-info" onclick="imprimirAjuste()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button type="button" class="btn btn-primary" onclick="editarAjuste()">
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
let tablaAjustes;
let datosAjustes = [];
let contadorProductosAjuste = 0;

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
    document.getElementById('fechaAjuste').value = fechaFormateada;
}

// Inicializar DataTable
function inicializarTabla() {
    tablaAjustes = $('#tablaAjustes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        pageLength: 25,
        responsive: true,
        order: [[0, 'desc']],
        columnDefs: [
            {
                targets: [6, 7, 8, 9],
                className: 'text-center'
            },
            {
                targets: [8],
                className: 'text-end'
            },
            {
                targets: [11],
                className: 'text-center',
                orderable: false
            }
        ]
    });

    // Búsqueda global
    $('#busqueda').on('keyup', function() {
        tablaAjustes.search($(this).val()).draw();
    });

    // Filtros
    $('#filtroFecha, #filtroTipo, #filtroEstado, #filtroSupervisor').on('change', aplicarFiltros);
}

// Inicializar gráficos
function inicializarGraficos() {
    // Gráfico de tendencia de ajustes
    const ctx1 = document.getElementById('graficoAjustes').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct'],
            datasets: [
                {
                    label: 'Ajustes Positivos',
                    data: [12, 8, 15, 9, 14, 11],
                    borderColor: 'rgb(28, 200, 138)',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    tension: 0.1
                },
                {
                    label: 'Ajustes Negativos',
                    data: [8, 6, 10, 7, 9, 7],
                    borderColor: 'rgb(231, 74, 59)',
                    backgroundColor: 'rgba(231, 74, 59, 0.1)',
                    tension: 0.1
                },
                {
                    label: 'Valor Neto',
                    data: [1200, 850, 1800, 1050, 1650, 1300],
                    borderColor: 'rgb(78, 115, 223)',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
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
                        text: 'Mes'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Número de Ajustes'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Valor Neto (S/)'
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
                            if (context.dataset.label === 'Valor Neto') {
                                return context.dataset.label + ': S/ ' + context.parsed.y.toLocaleString();
                            }
                            return context.dataset.label + ': ' + context.parsed.y;
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
            labels: ['Ajuste Positivo', 'Ajuste Negativo', 'Por Contaje', 'Por Inventario', 'Por Auditoría'],
            datasets: [{
                data: [45, 35, 12, 6, 2],
                backgroundColor: [
                    'rgb(28, 200, 138)',
                    'rgb(231, 74, 59)',
                    'rgb(78, 115, 223)',
                    'rgb(255, 193, 7)',
                    'rgb(102, 126, 234)'
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
    datosAjustes = [
        {
            id: 1,
            numero: 'AJ-2024-001',
            fecha: '2024-10-25 10:45:00',
            tipo: 'positivo',
            motivo: 'Contaje Mensual',
            productos: 5,
            stockAnterior: 1247,
            stockNuevo: 1263,
            diferencia: 16,
            valor: 45.60,
            estado: 'aprobado',
            supervisor: 'Ana María'
        },
        {
            id: 2,
            numero: 'AJ-2024-002',
            fecha: '2024-10-25 15:20:00',
            tipo: 'negativo',
            motivo: 'Merma No Registrada',
            productos: 3,
            stockAnterior: 850,
            stockNuevo: 835,
            diferencia: -15,
            valor: -240.00,
            estado: 'pendiente',
            supervisor: 'Carlos Sánchez'
        }
    ];
    
    actualizarEstadisticas();
}

// Actualizar estadísticas
function actualizarEstadisticas() {
    const esteMes = new Date().getMonth();
    const esteAnio = new Date().getFullYear();
    
    // Filtrar ajustes de este mes
    const ajustesMes = datosAjustes.filter(ajuste => {
        const fecha = new Date(ajuste.fecha);
        return fecha.getMonth() === esteMes && fecha.getFullYear() === esteAnio;
    });
    
    // Actualizar contadores
    document.getElementById('ajustesMes').textContent = ajustesMes.length;
    
    // Calcular totales
    let valorPositivo = 0;
    let valorNegativo = 0;
    
    ajustesMes.forEach(ajuste => {
        if (ajuste.valor > 0) {
            valorPositivo += ajuste.valor;
        } else {
            valorNegativo += Math.abs(ajuste.valor);
        }
    });
    
    document.getElementById('ajustePositivo').textContent = 'S/ ' + valorPositivo.toLocaleString();
    document.getElementById('ajusteNegativo').textContent = 'S/ ' + valorNegativo.toLocaleString();
    document.getElementById('diferenciaNeta').textContent = 'S/ ' + (valorPositivo - valorNegativo).toLocaleString();
}

// Aplicar filtros
function aplicarFiltros() {
    const fecha = $('#filtroFecha').val();
    const tipo = $('#filtroTipo').val();
    const estado = $('#filtroEstado').val();
    const supervisor = $('#filtroSupervisor').val();
    
    tablaAjustes.clear().rows.add(filtrarDatos(fecha, tipo, estado, supervisor)).draw();
}

// Filtrar datos
function filtrarDatos(fecha, tipo, estado, supervisor) {
    let datos = datosAjustes;
    
    if (fecha) {
        datos = datos.filter(item => {
            const fechaAjuste = new Date(item.fecha).toISOString().split('T')[0];
            return fechaAjuste === fecha;
        });
    }
    
    if (tipo) {
        datos = datos.filter(item => item.tipo === tipo);
    }
    
    if (estado) {
        datos = datos.filter(item => item.estado === estado);
    }
    
    if (supervisor) {
        datos = datos.filter(item => 
            item.supervisor.toLowerCase().includes(supervisor)
        );
    }
    
    return datos.map(item => [
        `<strong>${item.numero}</strong>`,
        formatearFechaCompleta(item.fecha),
        obtenerBadgeTipo(item.tipo),
        obtenerInfoMotivo(item.motivo),
        `<span class="badge bg-primary">${item.productos}</span>`,
        `<span class="text-center fw-bold">${item.stockAnterior}</span>`,
        `<span class="text-center fw-bold">${item.stockNuevo}</span>`,
        `<span class="text-center fw-bold ${item.diferencia > 0 ? 'text-success' : 'text-danger'}">${item.diferencia > 0 ? '+' : ''}${item.diferencia}</span>`,
        `<span class="text-end fw-bold ${item.valor > 0 ? 'text-success' : 'text-danger'}">S/ ${item.valor > 0 ? '+' : ''}${item.valor.toFixed(2)}</span>`,
        `<span class="badge ${obtenerClaseEstado(item.estado)}">${obtenerTextoEstado(item.estado)}</span>`,
        obtenerInfoUsuario(item.supervisor),
        generarBotonesAccion(item.id)
    ]);
}

// Formatear fecha completa
function formatearFechaCompleta(fecha) {
    const fechaObj = new Date(fecha);
    return `
        <div class="d-flex align-items-center">
            <div class="me-2"><i class="fas fa-calendar-alt text-info"></i></div>
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
        'positivo': '<span class="badge bg-success"><i class="fas fa-arrow-up"></i> Positivo</span>',
        'negativo': '<span class="badge bg-danger"><i class="fas fa-arrow-down"></i> Negativo</span>',
        'contaje': '<span class="badge bg-primary"><i class="fas fa-clipboard-check"></i> Contaje</span>',
        'inventario': '<span class="badge bg-info"><i class="fas fa-warehouse"></i> Inventario</span>',
        'auditoria': '<span class="badge bg-warning"><i class="fas fa-search"></i> Auditoría</span>'
    };
    return badges[tipo] || tipo;
}

// Obtener info del motivo
function obtenerInfoMotivo(motivo) {
    const motivos = {
        'Contaje Mensual': {icon: 'fas fa-clipboard-check text-success', detalle: 'Inventario general'},
        'Merma No Registrada': {icon: 'fas fa-exclamation-triangle text-warning', detalle: 'Productos vencidos'}
    };
    
    const info = motivos[motivo];
    if (info) {
        return `
            <div class="d-flex align-items-center">
                <div class="me-2"><i class="${info.icon}"></i></div>
                <div>
                    <div class="fw-bold">${motivo}</div>
                    <small class="text-muted">${info.detalle}</small>
                </div>
            </div>
        `;
    }
    return motivo;
}

// Obtener clase para estado
function obtenerClaseEstado(estado) {
    const clases = {
        'pendiente': 'bg-warning',
        'aprobado': 'bg-success',
        'rechazado': 'bg-danger',
        'procesado': 'bg-info'
    };
    return clases[estado] || 'bg-secondary';
}

// Obtener texto para estado
function obtenerTextoEstado(estado) {
    const textos = {
        'pendiente': 'Pendiente',
        'aprobado': 'Aprobado',
        'rechazado': 'Rechazado',
        'procesado': 'Procesado'
    };
    return textos[estado] || estado;
}

// Obtener info del supervisor
function obtenerInfoUsuario(supervisor) {
    const iniciales = supervisor.split(' ').map(n => n[0]).join('');
    return `
        <div class="d-flex align-items-center">
            <div class="avatar-sm me-2">
                <div class="bg-info rounded-circle d-flex align-items-center justify-content-center text-white fw-bold">
                    ${iniciales}
                </div>
            </div>
            <div>
                <div class="fw-bold">${supervisor}</div>
                <small class="text-muted">Supervisor</small>
            </div>
        </div>
    `;
}

// Generar botones de acción
function generarBotonesAccion(id) {
    return `
        <div class="btn-group" role="group">
            <button class="btn btn-sm btn-outline-primary" onclick="verAjuste(${id})" title="Ver Detalles">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="editarAjuste(${id})" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="imprimirAjuste(${id})" title="Imprimir">
                <i class="fas fa-print"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="aprobarAjuste(${id})">Aprobar</a></li>
                    <li><a class="dropdown-item" href="#" onclick="rechazarAjuste(${id})">Rechazar</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="eliminarAjuste(${id})">Eliminar</a></li>
                </ul>
            </div>
        </div>
    `;
}

// Limpiar filtros
function limpiarFiltros() {
    $('#filtroFecha, #filtroTipo, #filtroEstado, #filtroSupervisor').val('');
    $('#busqueda').val('');
    tablaAjustes.search('').columns().search('').draw();
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
    
    datosAjustes.forEach(ajuste => {
        const card = document.createElement('div');
        card.className = 'col-xl-4 col-lg-6 col-md-12 mb-4';
        card.innerHTML = `
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">${ajuste.numero}</h6>
                    <span class="badge ${obtenerClaseEstado(ajuste.estado)}">${obtenerTextoEstado(ajuste.estado)}</span>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        ${obtenerBadgeTipo(ajuste.tipo)}
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-calendar-alt text-muted"></i>
                        ${new Date(ajuste.fecha).toLocaleDateString('es-ES')}
                        <small class="text-muted ms-2">
                            ${new Date(ajuste.fecha).toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})}
                        </small>
                    </div>
                    <div class="mb-2">
                        <strong>Motivo:</strong> ${ajuste.motivo}
                    </div>
                    <div class="mb-2">
                        <strong>Diferencia:</strong> 
                        <span class="${ajuste.diferencia > 0 ? 'text-success' : 'text-danger'} fw-bold">
                            ${ajuste.diferencia > 0 ? '+' : ''}${ajuste.diferencia}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary">${ajuste.productos} productos</span>
                        <span class="fw-bold ${ajuste.valor > 0 ? 'text-success' : 'text-danger'}">
                            S/ ${ajuste.valor > 0 ? '+' : ''}${ajuste.valor.toFixed(2)}
                        </span>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="verAjuste(${ajuste.id})">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="imprimirAjuste(${ajuste.id})">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

// Cambiar tipo de ajuste
function cambiarTipoAjuste() {
    const tipo = document.getElementById('tipoAjuste').value;
    
    if (tipo) {
        mostrarInfo(`Ha seleccionado un ${tipo === 'positivo' ? 'ajuste positivo' : tipo === 'negativo' ? 'ajuste negativo' : 'ajuste por ' + tipo}.`);
    } else {
        ocultarInfo();
    }
}

// Mostrar información
function mostrarInfo(mensaje) {
    const alert = document.getElementById('alertInfo');
    const mensajeEl = document.getElementById('mensajeInfo');
    mensajeEl.textContent = mensaje;
    alert.classList.remove('d-none');
}

// Ocultar información
function ocultarInfo() {
    document.getElementById('alertInfo').classList.add('d-none');
}

// Agregar producto al ajuste
function agregarProductoAjuste() {
    contadorProductosAjuste++;
    const tbody = document.getElementById('tbodyProductosAjuste');
    const fila = document.createElement('tr');
    fila.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm" id="codigo_${contadorProductosAjuste}" 
                   onchange="buscarProductoAjuste(${contadorProductosAjuste})" placeholder="Código">
        </td>
        <td>
            <select class="form-select form-select-sm" id="producto_${contadorProductosAjuste}" 
                    onchange="cargarProductoAjusteSeleccionado(${contadorProductosAjuste})">
                <option value="">Seleccionar producto...</option>
                <option value="med001" data-precio="0.50" data-stock="850">Ibuprofeno 400mg</option>
                <option value="med002" data-precio="0.30" data-stock="25">Paracetamol 500mg</option>
                <option value="med003" data-precio="1.20" data-stock="120">Amoxicilina 500mg</option>
            </select>
        </td>
        <td id="stockSistema_${contadorProductosAjuste}" class="text-center">
            <span class="badge bg-secondary">0</span>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" id="stockFisico_${contadorProductosAjuste}" 
                   min="0" value="0" onchange="calcularDiferencia(${contadorProductosAjuste}); calcularTotalesAjuste()">
        </td>
        <td id="diferencia_${contadorProductosAjuste}" class="text-center fw-bold">0</td>
        <td>
            <input type="number" class="form-control form-control-sm" id="precio_${contadorProductosAjuste}" 
                   step="0.01" min="0" readonly>
        </td>
        <td id="valorAjuste_${contadorProductosAjuste}" class="text-end fw-bold">S/ 0.00</td>
        <td>
            <input type="text" class="form-control form-control-sm" id="lote_${contadorProductosAjuste}" placeholder="Lote">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" id="observaciones_${contadorProductosAjuste}" placeholder="Observaciones">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarFilaAjuste(${contadorProductosAjuste})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(fila);
}

// Buscar producto por código
function buscarProductoAjuste(id) {
    const codigo = document.getElementById(`codigo_${id}`).value;
    if (codigo) {
        // Simular búsqueda de producto
        const productos = {
            'MED001': {nombre: 'Ibuprofeno 400mg', precio: 0.50, stock: 850},
            'MED002': {nombre: 'Paracetamol 500mg', precio: 0.30, stock: 25},
            'MED003': {nombre: 'Amoxicilina 500mg', precio: 1.20, stock: 120}
        };
        
        const producto = productos[codigo.toUpperCase()];
        if (producto) {
            const select = document.getElementById(`producto_${id}`);
            const option = Array.from(select.options).find(opt => opt.textContent.includes(producto.nombre));
            if (option) {
                select.value = option.value;
                cargarProductoAjusteSeleccionado(id);
            }
        }
    }
}

// Cargar producto seleccionado
function cargarProductoAjusteSeleccionado(id) {
    const select = document.getElementById(`producto_${id}`);
    const option = select.options[select.selectedIndex];
    
    if (option && option.value) {
        const precio = option.dataset.precio;
        const stock = option.dataset.stock;
        
        document.getElementById(`precio_${id}`).value = precio;
        document.getElementById(`stockSistema_${id}`).innerHTML = `<span class="badge bg-info">${stock}</span>`;
        
        // Sugerir stock físico basado en stock del sistema
        if (!document.getElementById(`stockFisico_${id}`).value || document.getElementById(`stockFisico_${id}`).value === '0') {
            document.getElementById(`stockFisico_${id}`).value = stock;
        }
        
        calcularDiferencia(id);
        calcularTotalesAjuste();
    }
}

// Calcular diferencia
function calcularDiferencia(id) {
    const stockSistema = parseInt(document.getElementById(`stockSistema_${id}`).querySelector('.badge').textContent);
    const stockFisico = parseInt(document.getElementById(`stockFisico_${id}`).value || 0);
    const diferencia = stockFisico - stockSistema;
    
    const diferenciaEl = document.getElementById(`diferencia_${id}`);
    diferenciaEl.textContent = diferencia;
    diferenciaEl.className = `text-center fw-bold ${diferencia > 0 ? 'text-success' : diferencia < 0 ? 'text-danger' : 'text-muted'}`;
    
    calcularValorAjuste(id);
}

// Calcular valor del ajuste
function calcularValorAjuste(id) {
    const diferencia = parseInt(document.getElementById(`diferencia_${id}`).textContent);
    const precio = parseFloat(document.getElementById(`precio_${id}`).value || 0);
    const valor = diferencia * precio;
    
    const valorEl = document.getElementById(`valorAjuste_${id}`);
    valorEl.textContent = `S/ ${valor.toFixed(2)}`;
    valorEl.className = `text-end fw-bold ${valor > 0 ? 'text-success' : valor < 0 ? 'text-danger' : 'text-muted'}`;
}

// Calcular totales
function calcularTotalesAjuste() {
    let totalPositivo = 0;
    let totalNegativo = 0;
    
    for (let i = 1; i <= contadorProductosAjuste; i++) {
        const valorText = document.getElementById(`valorAjuste_${i}`)?.textContent;
        if (valorText) {
            const valor = parseFloat(valorText.replace('S/ ', ''));
            if (valor > 0) {
                totalPositivo += valor;
            } else if (valor < 0) {
                totalNegativo += Math.abs(valor);
            }
        }
    }
    
    const totalNeto = totalPositivo - totalNegativo;
    
    document.getElementById('totalPositivo').textContent = `S/ ${totalPositivo.toFixed(2)}`;
    document.getElementById('totalNegativo').textContent = `S/ ${totalNegativo.toFixed(2)}`;
    document.getElementById('totalNeto').textContent = `S/ ${totalNeto.toFixed(2)}`;
    document.getElementById('totalNeto').className = `text-end fw-bold fs-5 ${totalNeto >= 0 ? 'text-success' : 'text-danger'}`;
}

// Eliminar fila de producto
function eliminarFilaAjuste(id) {
    document.getElementById(`codigo_${id}`).closest('tr').remove();
    calcularTotalesAjuste();
}

// Importar desde Excel
function importarExcel() {
    Swal.fire({
        title: 'Importar Excel',
        text: 'Seleccione un archivo Excel con el formato correcto',
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
                text: 'Los productos han sido importados correctamente',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

// Guardar como borrador
function guardarBorradorAjuste() {
    Swal.fire({
        title: 'Guardar Borrador',
        text: '¿Desea guardar este ajuste como borrador?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Guardado',
                text: 'El ajuste se ha guardado como borrador',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

// Ver ajuste
function verAjuste(id) {
    const ajuste = datosAjustes.find(a => a.id === id);
    if (!ajuste) return;
    
    const contenido = `
        <div class="row">
            <div class="col-md-6">
                <h6>Información General</h6>
                <table class="table table-sm">
                    <tr><td><strong>Número:</strong></td><td>${ajuste.numero}</td></tr>
                    <tr><td><strong>Fecha:</strong></td><td>${new Date(ajuste.fecha).toLocaleString('es-ES')}</td></tr>
                    <tr><td><strong>Tipo:</strong></td><td>${obtenerBadgeTipo(ajuste.tipo)}</td></tr>
                    <tr><td><strong>Motivo:</strong></td><td>${ajuste.motivo}</td></tr>
                    <tr><td><strong>Estado:</strong></td><td><span class="badge ${obtenerClaseEstado(ajuste.estado)}">${obtenerTextoEstado(ajuste.estado)}</span></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Detalles del Ajuste</h6>
                <table class="table table-sm">
                    <tr><td><strong>Productos Afectados:</strong></td><td>${ajuste.productos}</td></tr>
                    <tr><td><strong>Stock Anterior:</strong></td><td>${ajuste.stockAnterior}</td></tr>
                    <tr><td><strong>Stock Nuevo:</strong></td><td>${ajuste.stockNuevo}</td></tr>
                    <tr><td><strong>Diferencia:</strong></td><td><span class="${ajuste.diferencia > 0 ? 'text-success' : 'text-danger'} fw-bold">${ajuste.diferencia > 0 ? '+' : ''}${ajuste.diferencia}</span></td></tr>
                    <tr><td><strong>Valor del Ajuste:</strong></td><td><span class="${ajuste.valor > 0 ? 'text-success' : 'text-danger'} fw-bold">S/ ${ajuste.valor > 0 ? '+' : ''}${ajuste.valor.toFixed(2)}</span></td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Productos Ajustados</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Stock Sistema</th>
                                <th>Stock Físico</th>
                                <th>Diferencia</th>
                                <th>Precio</th>
                                <th>Valor Ajuste</th>
                                <th>Lote</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>MED001</td>
                                <td>Ibuprofeno 400mg</td>
                                <td>850</td>
                                <td>856</td>
                                <td class="text-success">+6</td>
                                <td>S/ 0.50</td>
                                <td class="text-success">S/ +3.00</td>
                                <td>L240312A</td>
                            </tr>
                            <tr>
                                <td>MED002</td>
                                <td>Paracetamol 500mg</td>
                                <td>25</td>
                                <td>23</td>
                                <td class="text-danger">-2</td>
                                <td>S/ 0.30</td>
                                <td class="text-danger">S/ -0.60</td>
                                <td>L240325B</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Justificación</h6>
                <p>Ajustes realizados durante el contaje mensual rutinario. Las diferencias se deben principalmente a errores en el registro de salidas y mermas no registradas oportunamente.</p>
            </div>
        </div>
    `;
    
    document.getElementById('contenidoVerAjuste').innerHTML = contenido;
    $('#modalVerAjuste').modal('show');
}

// Editar ajuste
function editarAjuste(id) {
    Swal.fire({
        title: 'Editar Ajuste',
        text: 'Función de edición en desarrollo',
        icon: 'info',
        confirmButtonText: 'Aceptar'
    });
}

// Imprimir ajuste
function imprimirAjuste(id) {
    window.print();
}

// Aprobar ajuste
function aprobarAjuste(id) {
    Swal.fire({
        title: 'Aprobar Ajuste',
        text: '¿Desea aprobar este ajuste de inventario?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Ajuste Aprobado',
                text: 'El ajuste ha sido aprobado y procesado exitosamente',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Rechazar ajuste
function rechazarAjuste(id) {
    Swal.fire({
        title: 'Rechazar Ajuste',
        text: '¿Por qué rechaza este ajuste?',
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
                title: 'Ajuste Rechazado',
                text: 'El ajuste ha sido rechazado',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Eliminar ajuste
function eliminarAjuste(id) {
    Swal.fire({
        title: 'Eliminar Ajuste',
        text: '¿Está seguro de eliminar este ajuste? Esta acción no se puede deshacer.',
        icon: 'error',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Eliminado',
                text: 'El ajuste ha sido eliminado',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Exportar ajustes
function exportarAjustes() {
    Swal.fire({
        title: 'Exportar Ajustes',
        text: 'Seleccione el formato de exportación',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Excel',
        denyButtonText: 'PDF',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('/export/ajustes/excel', '_blank');
        } else if (result.isDenied) {
            window.open('/export/ajustes/pdf', '_blank');
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

// Generar ajuste para producto específico
function generarAjuste(codigo) {
    // Pre-llenar el formulario con el producto
    $('#modalNuevoAjuste').modal('show');
    
    setTimeout(() => {
        agregarProductoAjuste();
        document.getElementById(`codigo_${contadorProductosAjuste}`).value = codigo;
        buscarProductoAjuste(contadorProductosAjuste);
    }, 500);
}

// Manejar formulario nuevo ajuste
document.getElementById('formNuevoAjuste').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const datos = {
        numero: document.getElementById('numeroAjuste').value,
        fecha: document.getElementById('fechaAjuste').value,
        tipo: document.getElementById('tipoAjuste').value,
        prioridad: document.getElementById('prioridad').value,
        justificacion: document.getElementById('justificacion').value,
        total: document.getElementById('totalNeto').textContent,
        observaciones: document.getElementById('observaciones').value
    };
    
    // Validar que hay productos
    if (contadorProductosAjuste === 0) {
        Swal.fire({
            title: 'Error',
            text: 'Debe agregar al menos un producto',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    
    // Validar justificación
    if (!datos.justificacion.trim()) {
        Swal.fire({
            title: 'Error',
            text: 'Debe ingresar la justificación del ajuste',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    
    // Aquí se enviarían los datos al servidor
    console.log('Nuevo ajuste:', datos);
    
    Swal.fire({
        title: 'Ajuste Enviado',
        text: 'El ajuste ha sido enviado para aprobación',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        $('#modalNuevoAjuste').modal('hide');
        this.reset();
        // Recargar datos
        location.reload();
    });
});
</script>
@endsection