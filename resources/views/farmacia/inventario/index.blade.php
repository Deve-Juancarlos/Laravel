@extends('layouts.app')

@section('title', 'Control de Inventario - SIFANO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-boxes text-primary"></i>
                Control de Inventario Farmacéutico
            </h1>
            <p class="text-muted">Gestión completa de medicamentos, productos y suministros</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportarInventario()">
                <i class="fas fa-file-export"></i> Exportar
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
                <i class="fas fa-plus"></i> Nuevo Producto
            </button>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Productos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalProductos">2,847</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pills fa-2x text-gray-300"></i>
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
                                Stock Disponible
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stockDisponible">$1,247,680</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-warehouse fa-2x text-gray-300"></i>
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
                                Stock Crítico
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stockCritico">23</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                Próximos a Vencer
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="proxVencer">47</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                        </div>
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
                    <label for="filtroCategoria" class="form-label">Categoría</label>
                    <select class="form-select" id="filtroCategoria">
                        <option value="">Todas las categorías</option>
                        <option value="medicamentos">Medicamentos</option>
                        <option value="dispositivos">Dispositivos Médicos</option>
                        <option value="cosméticos">Cosméticos</option>
                        <option value="suplementos">Suplementos</option>
                        <option value="insumos">Insumos Médicos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroLaboratorio" class="form-label">Laboratorio</label>
                    <select class="form-select" id="filtroLaboratorio">
                        <option value="">Todos los laboratorios</option>
                        <option value="pfizer">Pfizer</option>
                        <option value="novartis">Novartis</option>
                        <option value="roche">Roche</option>
                        <option value="merck">Merck</option>
                        <option value="bayer">Bayer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroEstado" class="form-label">Estado Stock</label>
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="normal">Normal</option>
                        <option value="bajo">Stock Bajo</option>
                        <option value="critico">Stock Crítico</option>
                        <option value="agotado">Agotado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="busqueda" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="busqueda" placeholder="Código, nombre o lote...">
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary" onclick="aplicarFiltros()">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <button class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                    <i class="fas fa-undo"></i> Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla de Inventario -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Inventario Actual
            </h6>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary active" onclick="mostrarVista('tabla')">
                    <i class="fas fa-list"></i> Tabla
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="mostrarVista('grid')">
                    <i class="fas fa-th"></i> Grid
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="vistaTabla" class="table-responsive">
                <table class="table table-bordered table-striped" id="tablaInventario" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Precio</th>
                            <th>Lote</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-producto="1">
                            <td><strong>MED001</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-pills text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Ibuprofeno 400mg</div>
                                        <small class="text-muted">Tableta</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-primary">Medicamentos</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: 85%"></div>
                                    </div>
                                    <span class="fw-bold">850</span>
                                </div>
                            </td>
                            <td>100</td>
                            <td class="text-end">S/ 0.50</td>
                            <td>L240312A</td>
                            <td>
                                <span class="badge bg-success">15/08/2026</span>
                            </td>
                            <td>
                                <span class="badge bg-success">Normal</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verProducto(1)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editarProducto(1)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="entradaStock(1)" title="Entrada">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="salidaStock(1)" title="Salida">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-producto="2">
                            <td><strong>MED002</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-pills text-warning"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Paracetamol 500mg</div>
                                        <small class="text-muted">Tableta</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-primary">Medicamentos</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: 25%"></div>
                                    </div>
                                    <span class="fw-bold text-warning">25</span>
                                </div>
                            </td>
                            <td>50</td>
                            <td class="text-end">S/ 0.30</td>
                            <td>L240325B</td>
                            <td>
                                <span class="badge bg-warning">20/06/2026</span>
                            </td>
                            <td>
                                <span class="badge bg-warning">Stock Bajo</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verProducto(2)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editarProducto(2)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="entradaStock(2)" title="Entrada">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="salidaStock(2)" title="Salida">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-producto="3">
                            <td><strong>DIS001</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-syringe text-info"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Jeringa 5ml Estéril</div>
                                        <small class="text-muted">Unidad</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-info">Dispositivos</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                        <div class="progress-bar bg-danger" style="width: 8%"></div>
                                    </div>
                                    <span class="fw-bold text-danger">8</span>
                                </div>
                            </td>
                            <td>50</td>
                            <td class="text-end">S/ 0.80</td>
                            <td>L240401C</td>
                            <td>
                                <span class="badge bg-danger">Vencido</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">Crítico</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verProducto(3)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editarProducto(3)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="entradaStock(3)" title="Entrada">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="salidaStock(3)" title="Salida">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Más filas se cargarían dinámicamente -->
                    </tbody>
                </table>
            </div>

            <div id="vistaGrid" class="row d-none">
                <!-- Vista en Grid se llenará dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Gráficos de Inventario -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area"></i> Evolución de Inventario
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
                        <canvas id="graficoInventario"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Distribución por Categoría
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="graficoCategorias"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Producto -->
<div class="modal fade" id="modalNuevoProducto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNuevoProducto">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="codigo" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Producto</label>
                                <input type="text" class="form-control" id="nombre" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="medicamentos">Medicamentos</option>
                                    <option value="dispositivos">Dispositivos Médicos</option>
                                    <option value="cosméticos">Cosméticos</option>
                                    <option value="suplementos">Suplementos</option>
                                    <option value="insumos">Insumos Médicos</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="presentacion" class="form-label">Presentación</label>
                                <input type="text" class="form-control" id="presentacion" placeholder="Tableta, jarabe, crema...">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="concentracion" class="form-label">Concentración</label>
                                <input type="text" class="form-control" id="concentracion" placeholder="400mg, 500ml...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="stockMinimo" class="form-label">Stock Mínimo</label>
                                <input type="number" class="form-control" id="stockMinimo" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="precio" class="form-label">Precio Unitario</label>
                                <input type="number" class="form-control" id="precio" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="laboratorio" class="form-label">Laboratorio</label>
                        <input type="text" class="form-control" id="laboratorio">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Producto -->
<div class="modal fade" id="modalVerProducto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalles del Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoVerProducto">
                <!-- Se carga dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="editarProductoDesdeModal()">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Entrada de Stock -->
<div class="modal fade" id="modalEntradaStock" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-arrow-up text-success"></i> Entrada de Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEntradaStock">
                <div class="modal-body">
                    <input type="hidden" id="productoEntradaId">
                    <div class="mb-3">
                        <label class="form-label">Producto</label>
                        <input type="text" class="form-control" id="productoEntradaNombre" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cantidadEntrada" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="cantidadEntrada" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="precioEntrada" class="form-label">Precio Unitario</label>
                                <input type="number" class="form-control" id="precioEntrada" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="loteEntrada" class="form-label">Lote</label>
                        <input type="text" class="form-control" id="loteEntrada" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fechaVencimientoEntrada" class="form-label">Fecha Vencimiento</label>
                                <input type="date" class="form-control" id="fechaVencimientoEntrada" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipoEntrada" class="form-label">Tipo</label>
                                <select class="form-select" id="tipoEntrada" required>
                                    <option value="compra">Compra</option>
                                    <option value="devolucion">Devolución</option>
                                    <option value="ajuste">Ajuste Positivo</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="observacionesEntrada" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observacionesEntrada" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-arrow-up"></i> Registrar Entrada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Salida de Stock -->
<div class="modal fade" id="modalSalidaStock" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-arrow-down text-danger"></i> Salida de Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSalidaStock">
                <div class="modal-body">
                    <input type="hidden" id="productoSalidaId">
                    <div class="mb-3">
                        <label class="form-label">Producto</label>
                        <input type="text" class="form-control" id="productoSalidaNombre" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="cantidadSalida" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="cantidadSalida" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipoSalida" class="form-label">Tipo</label>
                        <select class="form-select" id="tipoSalida" required>
                            <option value="venta">Venta</option>
                            <option value="merma">Merma</option>
                            <option value="vencido">Producto Vencido</option>
                            <option value="ajuste">Ajuste Negativo</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="motivoSalida" class="form-label">Motivo</label>
                        <textarea class="form-control" id="motivoSalida" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="observacionesSalida" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observacionesSalida" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-arrow-down"></i> Registrar Salida
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Variables globales
let tablaInventario;
let datosInventario = [];

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    inicializarTabla();
    inicializarGraficos();
    cargarDatos();
});

// Inicializar DataTable
function inicializarTabla() {
    tablaInventario = $('#tablaInventario').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        pageLength: 25,
        responsive: true,
        order: [[1, 'asc']],
        columnDefs: [
            {
                targets: [3, 5],
                className: 'text-end'
            },
            {
                targets: [6, 7, 8],
                className: 'text-center'
            },
            {
                targets: [9],
                className: 'text-center',
                orderable: false
            }
        ],
        createdRow: function(row, data, dataIndex) {
            // Aplicar clases según estado del stock
            const stockActual = parseInt(data[3]);
            const stockMinimo = parseInt(data[4]);
            
            if (stockActual === 0) {
                $(row).addClass('table-danger');
            } else if (stockActual <= stockMinimo * 0.3) {
                $(row).addClass('table-warning');
            } else if (stockActual <= stockMinimo) {
                $(row).addClass('table-info');
            }
        }
    });

    // Búsqueda global
    $('#busqueda').on('keyup', function() {
        tablaInventario.search($(this).val()).draw();
    });

    // Filtros
    $('#filtroCategoria, #filtroLaboratorio, #filtroEstado').on('change', aplicarFiltros);
}

// Inicializar gráficos
function inicializarGraficos() {
    // Gráfico de evolución de inventario
    const ctx1 = document.getElementById('graficoInventario').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'Valor Total de Inventario',
                data: [950000, 1020000, 980000, 1100000, 1050000, 1200000, 1180000, 1247680, 1270000, 1300000, 0, 0],
                borderColor: 'rgb(78, 115, 223)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Valor: S/ ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Gráfico de categorías
    const ctx2 = document.getElementById('graficoCategorias').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Medicamentos', 'Dispositivos', 'Cosméticos', 'Suplementos', 'Insumos'],
            datasets: [{
                data: [45, 25, 15, 10, 5],
                backgroundColor: [
                    'rgb(78, 115, 223)',
                    'rgb(28, 200, 138)',
                    'rgb(255, 193, 7)',
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
    datosInventario = [
        {
            id: 1,
            codigo: 'MED001',
            nombre: 'Ibuprofeno 400mg',
            categoria: 'Medicamentos',
            stock: 850,
            stockMinimo: 100,
            precio: 0.50,
            lote: 'L240312A',
            vencimiento: '2026-08-15',
            estado: 'normal'
        },
        {
            id: 2,
            codigo: 'MED002',
            nombre: 'Paracetamol 500mg',
            categoria: 'Medicamentos',
            stock: 25,
            stockMinimo: 50,
            precio: 0.30,
            lote: 'L240325B',
            vencimiento: '2026-06-20',
            estado: 'bajo'
        },
        {
            id: 3,
            codigo: 'DIS001',
            nombre: 'Jeringa 5ml Estéril',
            categoria: 'Dispositivos',
            stock: 8,
            stockMinimo: 50,
            precio: 0.80,
            lote: 'L240401C',
            vencimiento: '2024-12-31',
            estado: 'critico'
        }
    ];
    
    actualizarEstadisticas();
}

// Actualizar estadísticas
function actualizarEstadisticas() {
    // Total productos
    document.getElementById('totalProductos').textContent = datosInventario.length.toLocaleString();
    
    // Stock disponible
    const valorTotal = datosInventario.reduce((sum, item) => sum + (item.stock * item.precio), 0);
    document.getElementById('stockDisponible').textContent = 'S/ ' + valorTotal.toLocaleString();
    
    // Stock crítico
    const stockCritico = datosInventario.filter(item => item.stock <= item.stockMinimo * 0.3 || item.stock === 0).length;
    document.getElementById('stockCritico').textContent = stockCritico;
    
    // Próximos a vencer
    const fechaActual = new Date();
    const proximosAVencer = datosInventario.filter(item => {
        const fechaVencimiento = new Date(item.vencimiento);
        const diasVencimiento = (fechaVencimiento - fechaActual) / (1000 * 60 * 60 * 24);
        return diasVencimiento <= 30 && diasVencimiento > 0;
    }).length;
    document.getElementById('proxVencer').textContent = proximosAVencer;
}

// Aplicar filtros
function aplicarFiltros() {
    const categoria = $('#filtroCategoria').val();
    const laboratorio = $('#filtroLaboratorio').val();
    const estado = $('#filtroEstado').val();
    
    tablaInventario.clear().rows.add(filtrarDatos(categoria, laboratorio, estado)).draw();
}

// Filtrar datos
function filtrarDatos(categoria, laboratorio, estado) {
    let datos = datosInventario;
    
    if (categoria) {
        datos = datos.filter(item => item.categoria === categoria);
    }
    
    if (laboratorio) {
        // Implementar filtro por laboratorio cuando esté disponible
    }
    
    if (estado) {
        datos = datos.filter(item => item.estado === estado);
    }
    
    return datos.map(item => [
        `<strong>${item.codigo}</strong>`,
        `<div class="d-flex align-items-center">
            <div class="me-2"><i class="fas fa-pills text-primary"></i></div>
            <div>
                <div class="fw-bold">${item.nombre}</div>
                <small class="text-muted">Tableta</small>
            </div>
        </div>`,
        `<span class="badge bg-primary">${item.categoria}</span>`,
        `${item.stock}`,
        item.stockMinimo,
        `S/ ${item.precio.toFixed(2)}`,
        item.lote,
        `<span class="badge ${obtenerClaseVencimiento(item.vencimiento)}">${formatearFecha(item.vencimiento)}</span>`,
        `<span class="badge ${obtenerClaseEstado(item.estado)}">${obtenerTextoEstado(item.estado)}</span>`,
        generarBotonesAccion(item.id)
    ]);
}

// Obtener clase CSS para vencimiento
function obtenerClaseVencimiento(fechaVencimiento) {
    const fecha = new Date(fechaVencimiento);
    const hoy = new Date();
    const diasVencimiento = (fecha - hoy) / (1000 * 60 * 60 * 24);
    
    if (diasVencimiento < 0) return 'bg-danger';
    if (diasVencimiento <= 30) return 'bg-warning';
    return 'bg-success';
}

// Obtener clase CSS para estado
function obtenerClaseEstado(estado) {
    const clases = {
        'normal': 'bg-success',
        'bajo': 'bg-warning',
        'critico': 'bg-danger',
        'agotado': 'bg-dark'
    };
    return clases[estado] || 'bg-secondary';
}

// Obtener texto para estado
function obtenerTextoEstado(estado) {
    const textos = {
        'normal': 'Normal',
        'bajo': 'Stock Bajo',
        'critico': 'Crítico',
        'agotado': 'Agotado'
    };
    return textos[estado] || estado;
}

// Formatear fecha
function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-ES');
}

// Generar botones de acción
function generarBotonesAccion(id) {
    return `
        <div class="btn-group" role="group">
            <button class="btn btn-sm btn-outline-primary" onclick="verProducto(${id})" title="Ver Detalles">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="editarProducto(${id})" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="entradaStock(${id})" title="Entrada">
                <i class="fas fa-arrow-up"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="salidaStock(${id})" title="Salida">
                <i class="fas fa-arrow-down"></i>
            </button>
        </div>
    `;
}

// Limpiar filtros
function limpiarFiltros() {
    $('#filtroCategoria, #filtroLaboratorio, #filtroEstado').val('');
    $('#busqueda').val('');
    tablaInventario.search('').columns().search('').draw();
}

// Mostrar vista (tabla/grid)
function mostrarVista(vista) {
    if (vista === 'tabla') {
        $('#vistaTabla').removeClass('d-none');
        $('#vistaGrid').addClass('d-none');
        $('.btn-group .btn').removeClass('active');
        $(`.btn-group .btn:eq(0)`).addClass('active');
    } else {
        $('#vistaTabla').addClass('d-none');
        $('#vistaGrid').removeClass('d-none');
        $('.btn-group .btn').removeClass('active');
        $(`.btn-group .btn:eq(1)`).addClass('active');
        cargarVistaGrid();
    }
}

// Cargar vista en grid
function cargarVistaGrid() {
    const grid = document.getElementById('vistaGrid');
    grid.innerHTML = '';
    
    datosInventario.forEach(item => {
        const card = document.createElement('div');
        card.className = 'col-xl-3 col-lg-4 col-md-6 mb-4';
        card.innerHTML = `
            <div class="card h-100 ${obtenerClaseFila(item)}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title mb-0">${item.nombre}</h6>
                        <span class="badge ${obtenerClaseEstado(item.estado)}">${obtenerTextoEstado(item.estado)}</span>
                    </div>
                    <p class="text-muted mb-2">${item.codigo}</p>
                    <div class="row text-center mb-3">
                        <div class="col">
                            <div class="text-sm font-weight-bold text-primary">${item.stock}</div>
                            <div class="text-xs text-muted">Stock</div>
                        </div>
                        <div class="col">
                            <div class="text-sm font-weight-bold text-success">S/ ${item.precio.toFixed(2)}</div>
                            <div class="text-xs text-muted">Precio</div>
                        </div>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar ${obtenerClaseProgreso(item)}" style="width: ${(item.stock / (item.stockMinimo * 2)) * 100}%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Lote: ${item.lote}</small>
                        <small class="text-muted">${formatearFecha(item.vencimiento)}</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="verProducto(${item.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="entradaStock(${item.id})">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="salidaStock(${item.id})">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        grid.appendChild(card);
    });
}

// Obtener clase para fila según stock
function obtenerClaseFila(item) {
    if (item.stock === 0) return 'border-danger';
    if (item.stock <= item.stockMinimo * 0.3) return 'border-warning';
    return 'border-primary';
}

// Obtener clase para barra de progreso
function obtenerClaseProgreso(item) {
    if (item.stock === 0) return 'bg-danger';
    if (item.stock <= item.stockMinimo * 0.3) return 'bg-warning';
    if (item.stock <= item.stockMinimo) return 'bg-info';
    return 'bg-success';
}

// Ver producto
function verProducto(id) {
    const producto = datosInventario.find(p => p.id === id);
    if (!producto) return;
    
    const contenido = `
        <div class="row">
            <div class="col-md-6">
                <h6>Información General</h6>
                <table class="table table-sm">
                    <tr><td><strong>Código:</strong></td><td>${producto.codigo}</td></tr>
                    <tr><td><strong>Nombre:</strong></td><td>${producto.nombre}</td></tr>
                    <tr><td><strong>Categoría:</strong></td><td>${producto.categoria}</td></tr>
                    <tr><td><strong>Stock Actual:</strong></td><td>${producto.stock} unidades</td></tr>
                    <tr><td><strong>Stock Mínimo:</strong></td><td>${producto.stockMinimo} unidades</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Detalles de Stock</h6>
                <table class="table table-sm">
                    <tr><td><strong>Precio Unitario:</strong></td><td>S/ ${producto.precio.toFixed(2)}</td></tr>
                    <tr><td><strong>Valor Total:</strong></td><td>S/ ${(producto.stock * producto.precio).toFixed(2)}</td></tr>
                    <tr><td><strong>Lote:</strong></td><td>${producto.lote}</td></tr>
                    <tr><td><strong>Vencimiento:</strong></td><td>${formatearFecha(producto.vencimiento)}</td></tr>
                    <tr><td><strong>Estado:</strong></td><td><span class="badge ${obtenerClaseEstado(producto.estado)}">${obtenerTextoEstado(producto.estado)}</span></td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Historial de Movimientos</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Motivo</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>${new Date().toLocaleDateString('es-ES')}</td>
                                <td><span class="badge bg-success">Entrada</span></td>
                                <td>+100</td>
                                <td>Compra a laboratorio</td>
                                <td>Admin</td>
                            </tr>
                            <tr>
                                <td>${new Date(Date.now() - 86400000).toLocaleDateString('es-ES')}</td>
                                <td><span class="badge bg-danger">Salida</span></td>
                                <td>-25</td>
                                <td>Venta mostrador</td>
                                <td>Vendedor</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contenidoVerProducto').innerHTML = contenido;
    $('#modalVerProducto').modal('show');
}

// Editar producto
function editarProducto(id) {
    // Implementar edición
    Swal.fire({
        title: 'Editar Producto',
        text: 'Función de edición en desarrollo',
        icon: 'info',
        confirmButtonText: 'Aceptar'
    });
}

// Entrada de stock
function entradaStock(id) {
    const producto = datosInventario.find(p => p.id === id);
    if (!producto) return;
    
    document.getElementById('productoEntradaId').value = id;
    document.getElementById('productoEntradaNombre').value = `${producto.codigo} - ${producto.nombre}`;
    document.getElementById('precioEntrada').value = producto.precio;
    
    $('#modalEntradaStock').modal('show');
}

// Salida de stock
function salidaStock(id) {
    const producto = datosInventario.find(p => p.id === id);
    if (!producto) return;
    
    document.getElementById('productoSalidaId').value = id;
    document.getElementById('productoSalidaNombre').value = `${producto.codigo} - ${producto.nombre}`;
    document.getElementById('cantidadSalida').max = producto.stock;
    
    $('#modalSalidaStock').modal('show');
}

// Editar desde modal
function editarProductoDesdeModal() {
    $('#modalVerProducto').modal('hide');
    const id = document.getElementById('productoEntradaId').value;
    if (id) {
        editarProducto(parseInt(id));
    }
}

// Exportar inventario
function exportarInventario() {
    Swal.fire({
        title: 'Exportar Inventario',
        text: 'Seleccione el formato de exportación',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Excel',
        denyButtonText: 'PDF',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Exportar a Excel
            window.open('/export/inventario/excel', '_blank');
        } else if (result.isDenied) {
            // Exportar a PDF
            window.open('/export/inventario/pdf', '_blank');
        }
    });
}

// Actualizar gráfico
function actualizarGrafico() {
    // Recargar datos del gráfico
    location.reload();
}

// Exportar gráfico
function exportarGrafico() {
    // Implementar exportación de gráfico
    Swal.fire({
        title: 'Gráfico Exportado',
        text: 'El gráfico se ha exportado exitosamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    });
}

// Manejar formulario nuevo producto
document.getElementById('formNuevoProducto').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validar y enviar datos
    const datos = {
        codigo: document.getElementById('codigo').value,
        nombre: document.getElementById('nombre').value,
        categoria: document.getElementById('categoria').value,
        presentacion: document.getElementById('presentacion').value,
        concentracion: document.getElementById('concentracion').value,
        stockMinimo: document.getElementById('stockMinimo').value,
        precio: document.getElementById('precio').value,
        laboratorio: document.getElementById('laboratorio').value
    };
    
    // Aquí se enviarían los datos al servidor
    console.log('Nuevo producto:', datos);
    
    // Mostrar éxito y cerrar modal
    Swal.fire({
        title: 'Producto Creado',
        text: 'El producto ha sido registrado exitosamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        $('#modalNuevoProducto').modal('hide');
        this.reset();
        // Recargar tabla
        location.reload();
    });
});

// Manejar formulario entrada de stock
document.getElementById('formEntradaStock').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const datos = {
        productoId: document.getElementById('productoEntradaId').value,
        cantidad: document.getElementById('cantidadEntrada').value,
        precio: document.getElementById('precioEntrada').value,
        lote: document.getElementById('loteEntrada').value,
        fechaVencimiento: document.getElementById('fechaVencimientoEntrada').value,
        tipo: document.getElementById('tipoEntrada').value,
        observaciones: document.getElementById('observacionesEntrada').value
    };
    
    // Aquí se enviarían los datos al servidor
    console.log('Entrada de stock:', datos);
    
    Swal.fire({
        title: 'Entrada Registrada',
        text: 'La entrada de stock se ha registrado exitosamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        $('#modalEntradaStock').modal('hide');
        this.reset();
        // Recargar datos
        location.reload();
    });
});

// Manejar formulario salida de stock
document.getElementById('formSalidaStock').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const datos = {
        productoId: document.getElementById('productoSalidaId').value,
        cantidad: document.getElementById('cantidadSalida').value,
        tipo: document.getElementById('tipoSalida').value,
        motivo: document.getElementById('motivoSalida').value,
        observaciones: document.getElementById('observacionesSalida').value
    };
    
    // Validar stock disponible
    const producto = datosInventario.find(p => p.id === parseInt(datos.productoId));
    if (producto && parseInt(datos.cantidad) > producto.stock) {
        Swal.fire({
            title: 'Stock Insuficiente',
            text: `No hay suficiente stock disponible. Stock actual: ${producto.stock}`,
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    
    // Aquí se enviarían los datos al servidor
    console.log('Salida de stock:', datos);
    
    Swal.fire({
        title: 'Salida Registrada',
        text: 'La salida de stock se ha registrado exitosamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        $('#modalSalidaStock').modal('hide');
        this.reset();
        // Recargar datos
        location.reload();
    });
});
</script>
@endsection