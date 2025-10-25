@extends('layouts.app')

@section('title', 'Planillas de Venta')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-users text-primary"></i>
                        Planillas de Venta
                    </h1>
                    <p class="text-muted mb-0">Gestión de comisiones y planillas del equipo de ventas</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <a href="{{ route('ventas.planillas.nueva') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Nueva Planilla
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="calcularPlanillas()">
                            <i class="fas fa-calculator"></i> Calcular
                        </button>
                        <a href="{{ route('ventas.planillas.imprimir') }}" class="btn btn-info">
                            <i class="fas fa-print"></i> Imprimir
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs de Planillas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Total Empleados</p>
                            <h4 class="text-primary mb-0">24</h4>
                            <small class="text-primary">
                                <i class="fas fa-users"></i> Equipo activo
                            </small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-users text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Planilla Mes Actual</p>
                            <h4 class="text-success mb-0">S/ 45,680</h4>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> +8.2% vs mes anterior
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-money-bill-wave text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Comisiones Pendientes</p>
                            <h4 class="text-warning mb-0">S/ 8,450</h4>
                            <small class="text-warning">
                                <i class="fas fa-clock"></i> Por procesar
                            </small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-percentage text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Comisión Promedio</p>
                            <h4 class="text-info mb-0">S/ 1,902</h4>
                            <small class="text-info">
                                <i class="fas fa-chart-line"></i> Por empleado
                            </small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-chart-bar text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form class="row g-3" id="filtrosPlanillas">
                        <div class="col-md-3">
                            <label class="form-label">Período</label>
                            <select class="form-select" id="periodo">
                                <option value="">Todos los períodos</option>
                                <option value="2025-10">Octubre 2025</option>
                                <option value="2025-09">Septiembre 2025</option>
                                <option value="2025-08">Agosto 2025</option>
                                <option value="2025-07">Julio 2025</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="estadoPlanilla">
                                <option value="">Todos los estados</option>
                                <option value="borrador">Borrador</option>
                                <option value="calculada">Calculada</option>
                                <option value="aprobada">Aprobada</option>
                                <option value="pagada">Pagada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Empleado</label>
                            <select class="form-select" id="empleado">
                                <option value="">Todos los empleados</option>
                                <option value="ana_garcia">Ana García</option>
                                <option value="carlos_lopez">Carlos López</option>
                                <option value="maria_rodriguez">María Rodríguez</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Buscar</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="busqueda" placeholder="Nombre, código, período...">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Planillas -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary"></i>
                        Planillas de Comisiones
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarPlanillas()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="actualizarPlanillas()">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaPlanillas">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>Código</th>
                                    <th>Período</th>
                                    <th>Empleado</th>
                                    <th>Ventas Netas</th>
                                    <th>Comisión %</th>
                                    <th>Comisión</th>
                                    <th>Bonificaciones</th>
                                    <th>Total a Pagar</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" value="PL-2025-10-001">
                                    </td>
                                    <td>
                                        <strong class="text-primary">PL-2025-10-001</strong>
                                    </td>
                                    <td>
                                        <span class="d-block">Octubre 2025</span>
                                        <small class="text-muted">01/10 - 31/10</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="Empleado">
                                            <div>
                                                <strong>Ana García</strong>
                                                <br>
                                                <small class="text-muted">Farmacéutica Senior</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>S/ 45,680.00</strong>
                                        <br>
                                        <small class="text-muted">45 ventas</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-info me-1">3.5%</span>
                                            <small class="text-muted">Base</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>S/ 1,598.80</strong>
                                        <br>
                                        <small class="text-success">+S/ 120meta</small>
                                    </td>
                                    <td>
                                        <strong>S/ 450.00</strong>
                                        <br>
                                        <small class="text-muted">Ventas premio</small>
                                    </td>
                                    <td>
                                        <h5 class="text-success mb-0">S/ 2,048.80</h5>
                                        <small class="text-muted">Incluye bonificaciones</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Aprobada</span>
                                        <br>
                                        <small class="text-muted">24/10/2025</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('ventas.planillas.ver', 'PL-2025-10-001') }}" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="aprobarPlanilla('PL-2025-10-001')" title="Aprobar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="imprimirPlanilla('PL-2025-10-001')" title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="editarPlanilla('PL-2025-10-001')"><i class="fas fa-edit me-2"></i>Editar</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="duplicarPlanilla('PL-2025-10-001')"><i class="fas fa-copy me-2"></i>Duplicar</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="exportarPDF('PL-2025-10-001')"><i class="fas fa-file-pdf me-2"></i>Exportar PDF</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item" href="#" onclick="registrarPago('PL-2025-10-001')"><i class="fas fa-money-bill me-2"></i>Registrar Pago</a></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="anularPlanilla('PL-2025-10-001')"><i class="fas fa-ban me-2"></i>Anular</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-warning">
                                    <td>
                                        <input type="checkbox" class="form-check-input" value="PL-2025-10-002">
                                    </td>
                                    <td>
                                        <strong class="text-warning">PL-2025-10-002</strong>
                                    </td>
                                    <td>
                                        <span class="d-block">Octubre 2025</span>
                                        <small class="text-muted">01/10 - 31/10</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="Empleado">
                                            <div>
                                                <strong>Carlos López</strong>
                                                <br>
                                                <small class="text-muted">Vendedor</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>S/ 38,920.00</strong>
                                        <br>
                                        <small class="text-muted">38 ventas</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-warning me-1">3.0%</span>
                                            <small class="text-muted">Base</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>S/ 1,167.60</strong>
                                        <br>
                                        <small class="text-success">+S/ 95meta</small>
                                    </td>
                                    <td>
                                        <strong>S/ 0.00</strong>
                                        <br>
                                        <small class="text-muted">Sin bonificaciones</small>
                                    </td>
                                    <td>
                                        <h5 class="text-warning mb-0">S/ 1,167.60</h5>
                                        <small class="text-muted">Pendiente cálculo</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Calculada</span>
                                        <br>
                                        <small class="text-muted">23/10/2025</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('ventas.planillas.ver', 'PL-2025-10-002') }}" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="aprobarPlanilla('PL-2025-10-002')" title="Aprobar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="imprimirPlanilla('PL-2025-10-002')" title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="editarPlanilla('PL-2025-10-002')"><i class="fas fa-edit me-2"></i>Editar</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="calcularNuevamente('PL-2025-10-002')"><i class="fas fa-calculator me-2"></i>Recalcular</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="duplicarPlanilla('PL-2025-10-002')"><i class="fas fa-copy me-2"></i>Duplicar</a></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="anularPlanilla('PL-2025-10-002')"><i class="fas fa-ban me-2"></i>Anular</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" value="PL-2025-09-003">
                                    </td>
                                    <td>
                                        <strong class="text-info">PL-2025-09-003</strong>
                                    </td>
                                    <td>
                                        <span class="d-block">Septiembre 2025</span>
                                        <small class="text-muted">01/09 - 30/09</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="Empleado">
                                            <div>
                                                <strong>María Rodríguez</strong>
                                                <br>
                                                <small class="text-muted">Farmacéutica</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>S/ 32,450.00</strong>
                                        <br>
                                        <small class="text-muted">32 ventas</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-info me-1">3.2%</span>
                                            <small class="text-muted">Base</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>S/ 1,038.40</strong>
                                        <br>
                                        <small class="text-success">+S/ 85meta</small>
                                    </td>
                                    <td>
                                        <strong>S/ 350.00</strong>
                                        <br>
                                        <small class="text-muted">Producto estrella</small>
                                    </td>
                                    <td>
                                        <h5 class="text-success mb-0">S/ 1,388.40</h5>
                                        <small class="text-success">Pagada</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Pagada</span>
                                        <br>
                                        <small class="text-success">05/10/2025</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('ventas.planillas.ver', 'PL-2025-09-003') }}" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="imprimirPlanilla('PL-2025-09-003')" title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="verComprobantePago('PL-2025-09-003')"><i class="fas fa-receipt me-2"></i>Comprobante</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="duplicarPlanilla('PL-2025-09-003')"><i class="fas fa-copy me-2"></i>Duplicar</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="exportarPDF('PL-2025-09-003')"><i class="fas fa-file-pdf me-2"></i>Exportar PDF</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <span class="text-muted">Mostrando 1 a 3 de 24 planillas</span>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Anterior</a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Siguiente</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones en Lote -->
    <div class="row mt-3" id="accionesLote" style="display: none;">
        <div class="col-12">
            <div class="alert alert-primary d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-info-circle me-2"></i>
                    <span id="seleccionadosCount">0</span> planillas seleccionadas
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="aprobarSeleccionadas()">
                        <i class="fas fa-check"></i> Aprobar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="calcularSeleccionadas()">
                        <i class="fas fa-calculator"></i> Calcular
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="exportarSeleccionadas()">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="imprimirSeleccionadas()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="anularSeleccionadas()">
                        <i class="fas fa-ban"></i> Anular
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarSeleccion()">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let planillasSeleccionadas = [];

function aplicarFiltros() {
    const filtros = {
        periodo: document.getElementById('periodo').value,
        estado: document.getElementById('estadoPlanilla').value,
        empleado: document.getElementById('empleado').value,
        busqueda: document.getElementById('busqueda').value
    };

    console.log('Aplicando filtros:', filtros);
    
    Swal.fire({
        title: 'Aplicando filtros...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });

    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Filtros aplicados',
            showConfirmButton: false,
            timer: 1500
        });
    }, 1000);
}

function actualizarPlanillas() {
    Swal.fire({
        title: 'Actualizando planillas...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });

    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Planillas actualizadas',
            showConfirmButton: false,
            timer: 1500
        });
    }, 1500);
}

function calcularPlanillas() {
    Swal.fire({
        title: 'Calcular Planillas',
        html: `
            <div class="text-left">
                <div class="mb-3">
                    <label class="form-label">Período a calcular:</label>
                    <select class="form-select" id="periodoCalculo">
                        <option value="2025-10">Octubre 2025</option>
                        <option value="2025-09">Septiembre 2025</option>
                        <option value="2025-08">Agosto 2025</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Empleados a incluir:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="incluirTodos" checked>
                        <label class="form-check-label" for="incluirTodos">
                            Todos los empleados activos
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="empleadosEspecificos">
                        <label class="form-check-label" for="empleadosEspecificos">
                            Empleados específicos
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Opciones de cálculo:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="incluirBonificaciones" checked>
                        <label class="form-check-label" for="incluirBonificaciones">
                            Incluir bonificaciones automáticas
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="aplicarDescuentos">
                        <label class="form-check-label" for="aplicarDescuentos">
                            Aplicar descuentos (adelantos, etc.)
                        </label>
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Calcular Planillas',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({ calculated: true });
                }, 3000);
            });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value.calculated) {
            Swal.fire({
                icon: 'success',
                title: 'Cálculo completado',
                text: 'Las planillas han sido calculadas exitosamente'
            });
        }
    });
}

function aprobarPlanilla(codigoPlanilla) {
    Swal.fire({
        title: 'Aprobar Planilla',
        text: `¿Aprobar la planilla ${codigoPlanilla}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Planilla aprobada',
                text: `La planilla ${codigoPlanilla} ha sido aprobada exitosamente`
            });
        }
    });
}

function imprimirPlanilla(codigoPlanilla) {
    window.open(`/ventas/planillas/imprimir/${codigoPlanilla}`, '_blank');
}

function editarPlanilla(codigoPlanilla) {
    window.open(`/ventas/planillas/editar/${codigoPlanilla}`, '_blank');
}

function duplicarPlanilla(codigoPlanilla) {
    Swal.fire({
        title: 'Duplicar Planilla',
        text: `¿Crear una copia de la planilla ${codigoPlanilla}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Duplicar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Planilla duplicada',
                text: `Se ha creado una copia de la planilla ${codigoPlanilla}`
            });
        }
    });
}

function exportarPDF(codigoPlanilla) {
    window.open(`/ventas/planillas/exportar-pdf/${codigoPlanilla}`, '_blank');
}

function registrarPago(codigoPlanilla) {
    Swal.fire({
        title: 'Registrar Pago',
        html: `
            <div class="text-left">
                <div class="alert alert-success">
                    <strong>Planilla:</strong> ${codigoPlanilla}
                    <br>
                    <strong>Monto a pagar:</strong> S/ 2,048.80
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha de Pago:</label>
                    <input type="date" class="form-control" id="fechaPagoPlanilla" value="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Método de Pago:</label>
                    <select class="form-select" id="metodoPagoPlanilla">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia bancaria</option>
                        <option value="cheque">Cheque</option>
                        <option value="deposito">Depósito</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones:</label>
                    <textarea class="form-control" id="observacionesPagoPlanilla" rows="3" placeholder="Observaciones adicionales..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Registrar Pago',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Pago registrado',
                text: `El pago de la planilla ${codigoPlanilla} ha sido registrado`
            });
        }
    });
}

function calcularNuevamente(codigoPlanilla) {
    Swal.fire({
        title: 'Recalcular Planilla',
        text: `¿Recalcular la planilla ${codigoPlanilla}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Recalcular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({ recalculated: true });
                }, 2000);
            });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value.recalculated) {
            Swal.fire({
                icon: 'success',
                title: 'Planilla recalculada',
                text: `La planilla ${codigoPlanilla} ha sido recalculada exitosamente`
            });
        }
    });
}

function verComprobantePago(codigoPlanilla) {
    window.open(`/ventas/planillas/comprobante-pago/${codigoPlanilla}`, '_blank');
}

function anularPlanilla(codigoPlanilla) {
    Swal.fire({
        title: 'Anular Planilla',
        text: `¿Estás seguro de anular la planilla ${codigoPlanilla}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Planilla anulada',
                text: `La planilla ${codigoPlanilla} ha sido anulada exitosamente`
            });
        }
    });
}

function exportarPlanillas() {
    const opciones = ['Excel', 'PDF', 'CSV'];
    
    Swal.fire({
        title: 'Exportar Planillas',
        text: '¿En qué formato deseas exportar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Excel',
        cancelButtonText: 'Cancelar',
        showDenyButton: true,
        denyButtonText: 'PDF',
        denyButtonColor: '#dc3545'
    }).then((result) => {
        let formato = 'Excel';
        if (result.isDenied) formato = 'PDF';
        
        Swal.fire({
            icon: 'success',
            title: `Reporte ${formato} generado`,
            text: 'El archivo se ha descargado exitosamente'
        });
    });
}

function actualizarSeleccion() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked:not(#selectAll)');
    planillasSeleccionadas = Array.from(checkboxes).map(cb => cb.value);
    
    const count = planillasSeleccionadas.length;
    if (count > 0) {
        document.getElementById('seleccionadosCount').textContent = count;
        document.getElementById('accionesLote').style.display = 'block';
    } else {
        document.getElementById('accionesLote').style.display = 'none';
    }
}

function aprobarSeleccionadas() {
    if (planillasSeleccionadas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay planillas seleccionadas',
            text: 'Selecciona al menos una planilla para aprobar'
        });
        return;
    }
    
    Swal.fire({
        title: 'Aprobar Planillas',
        text: `¿Aprobar ${planillasSeleccionadas.length} planillas seleccionadas?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Aprobar Todas',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Planillas aprobadas',
                text: `${planillasSeleccionadas.length} planillas han sido aprobadas exitosamente`
            });
        }
    });
}

function calcularSeleccionadas() {
    if (planillasSeleccionadas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay planillas seleccionadas',
            text: 'Selecciona al menos una planilla para calcular'
        });
        return;
    }
    
    Swal.fire({
        title: 'Calcular Planillas',
        text: `¿Calcular ${planillasSeleccionadas.length} planillas seleccionadas?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Calcular Todas',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({ calculated: true });
                }, 3000);
            });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value.calculated) {
            Swal.fire({
                icon: 'success',
                title: 'Cálculo completado',
                text: `${planillasSeleccionadas.length} planillas han sido calculadas exitosamente`
            });
        }
    });
}

function exportarSeleccionadas() {
    if (planillasSeleccionadas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay planillas seleccionadas',
            text: 'Selecciona al menos una planilla para exportar'
        });
        return;
    }
    
    Swal.fire({
        title: 'Exportar Planillas Seleccionadas',
        text: `¿Exportar ${planillasSeleccionadas.length} planillas seleccionadas?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(`/ventas/planillas/exportar-seleccionadas?planillas=${planillasSeleccionadas.join(',')}`, '_blank');
        }
    });
}

function imprimirSeleccionadas() {
    if (planillasSeleccionadas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay planillas seleccionadas',
            text: 'Selecciona al menos una planilla para imprimir'
        });
        return;
    }
    
    window.open(`/ventas/planillas/imprimir-seleccionadas?planillas=${planillasSeleccionadas.join(',')}`, '_blank');
}

function anularSeleccionadas() {
    if (planillasSeleccionadas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay planillas seleccionadas',
            text: 'Selecciona al menos una planilla para anular'
        });
        return;
    }
    
    Swal.fire({
        title: 'Anular Planillas',
        text: `¿Anular ${planillasSeleccionadas.length} planillas seleccionadas?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular todas',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Planillas anuladas',
                text: `${planillasSeleccionadas.length} planillas han sido anuladas exitosamente`
            });
            limpiarSeleccion();
        }
    });
}

function limpiarSeleccion() {
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    planillasSeleccionadas = [];
    document.getElementById('accionesLote').style.display = 'none';
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]:not(#selectAll)');
        checkboxes.forEach(cb => cb.checked = this.checked);
        actualizarSeleccion();
    });
    
    // Individual checkboxes
    document.querySelectorAll('input[type="checkbox"]:not(#selectAll)').forEach(checkbox => {
        checkbox.addEventListener('change', actualizarSeleccion);
    });
    
    // Filter buttons
    document.querySelectorAll('#filtrosPlanillas button, #filtrosPlanillas select').forEach(element => {
        element.addEventListener('change', aplicarFiltros);
    });
});
</script>
@endsection

@section('styles')
<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}

.btn-group .btn {
    border-radius: 0.375rem;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.alert {
    margin-bottom: 0;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.pagination .page-link {
    color: #0d6efd;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

h5 {
    font-size: 1.1rem;
}
</style>
@endsection