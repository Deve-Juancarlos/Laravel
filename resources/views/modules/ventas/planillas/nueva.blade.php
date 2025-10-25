@extends('layouts.app')

@section('title', 'Nueva Planilla')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('ventas.dashboard.index') }}">Ventas</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('ventas.planillas.index') }}">Planillas</a></li>
                            <li class="breadcrumb-item active">Nueva Planilla</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-plus-circle text-success"></i>
                        Nueva Planilla de Comisiones
                    </h1>
                    <p class="text-muted mb-0">Crear una nueva planilla de comisiones para el equipo de ventas</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="guardarBorrador()">
                            <i class="fas fa-save"></i> Guardar Borrador
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="calcularComisiones()">
                            <i class="fas fa-calculator"></i> Calcular
                        </button>
                        <a href="{{ route('ventas.planillas.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="planillaForm" onsubmit="event.preventDefault(); guardarPlanilla();">
        <div class="row">
            <!-- Panel Principal -->
            <div class="col-lg-8 mb-4">
                <!-- Configuración de la Planilla -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary bg-opacity-10 border-0">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-cog me-2"></i>
                            Configuración de la Planilla
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Código de Planilla *</label>
                                <input type="text" class="form-control" id="codigoPlanilla" 
                                       value="PL-{{ date('Y-m') }}-{{ str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <input type="text" class="form-control" value="Borrador" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Período *</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="date" class="form-control" id="fechaInicio" value="{{ date('Y-m-01') }}" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="date" class="form-control" id="fechaFin" value="{{ date('Y-m-t') }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Creación</label>
                                <input type="date" class="form-control" id="fechaCreacion" value="{{ date('Y-m-d') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Responsable *</label>
                                <select class="form-select" id="responsable" required>
                                    <option value="">Seleccionar responsable...</option>
                                    <option value="ana_garcia" selected>Ana García - Gerente de Ventas</option>
                                    <option value="carlos_lopez">Carlos López - Supervisor</option>
                                    <option value="maria_rodriguez">María Rodríguez - Recursos Humanos</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" rows="2" placeholder="Observaciones adicionales..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selección de Empleados -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-info bg-opacity-10 border-0">
                        <h5 class="mb-0 text-info">
                            <i class="fas fa-users me-2"></i>
                            Selección de Empleados
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Buscar Empleado</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="busquedaEmpleado" placeholder="Nombre, código, área..." onkeyup="buscarEmpleado()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Área/Departamento</label>
                                <select class="form-select" id="area">
                                    <option value="">Todas las áreas</option>
                                    <option value="ventas">Ventas</option>
                                    <option value="farmacia">Farmacia</option>
                                    <option value="caja">Caja</option>
                                    <option value="administracion">Administración</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6>Empleados Seleccionados</h6>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="seleccionarTodos()">
                                        <i class="fas fa-check-double"></i> Seleccionar Todos
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarSeleccion()">
                                        <i class="fas fa-times"></i> Limpiar
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover" id="tablaEmpleados">
                                    <thead class="table-light">
                                        <tr>
                                            <th>
                                                <input type="checkbox" class="form-check-input" id="selectAllEmpleados">
                                            </th>
                                            <th>Empleado</th>
                                            <th>Cargo</th>
                                            <th>Área</th>
                                            <th>Comisión Base %</th>
                                            <th>Meta Mensual</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input empleado-checkbox" value="emp001" checked>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="Empleado">
                                                    <div>
                                                        <strong>Ana García</strong>
                                                        <br>
                                                        <small class="text-muted">Cód: EMP-001</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>Farmacéutica Senior</strong>
                                                <br>
                                                <small class="text-muted">Nivel: Senior</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">Farmacia</span>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm" value="3.5" min="0" max="10" step="0.1" 
                                                       onchange="actualizarComision('emp001', this.value)">
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="number" class="form-control form-control-sm" value="40000" min="0" step="100" 
                                                           onchange="actualizarMeta('emp001', this.value)">
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="configurarEmpleado('emp001')">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input empleado-checkbox" value="emp002" checked>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="Empleado">
                                                    <div>
                                                        <strong>Carlos López</strong>
                                                        <br>
                                                        <small class="text-muted">Cód: EMP-002</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>Vendedor</strong>
                                                <br>
                                                <small class="text-muted">Nivel: Intermedio</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Ventas</span>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm" value="3.0" min="0" max="10" step="0.1" 
                                                       onchange="actualizarComision('emp002', this.value)">
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="number" class="form-control form-control-sm" value="35000" min="0" step="100" 
                                                           onchange="actualizarMeta('emp002', this.value)">
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="configurarEmpleado('emp002')">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input empleado-checkbox" value="emp003">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="Empleado">
                                                    <div>
                                                        <strong>María Rodríguez</strong>
                                                        <br>
                                                        <small class="text-muted">Cód: EMP-003</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>Farmacéutica</strong>
                                                <br>
                                                <small class="text-muted">Nivel: Junior</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">Farmacia</span>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm" value="3.2" min="0" max="10" step="0.1" 
                                                       onchange="actualizarComision('emp003', this.value)">
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="number" class="form-control form-control-sm" value="30000" min="0" step="100" 
                                                           onchange="actualizarMeta('emp003', this.value)">
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="configurarEmpleado('emp003')">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Cálculo -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-success bg-opacity-10 border-0">
                        <h5 class="mb-0 text-success">
                            <i class="fas fa-calculator me-2"></i>
                            Configuración de Cálculo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6>Parámetros de Comisión</h6>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="incluirIGV" checked>
                                        <label class="form-check-label" for="incluirIGV">
                                            Incluir IGV en cálculo de ventas
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="incluirDevoluciones">
                                        <label class="form-check-label" for="incluirDevoluciones">
                                            Restar devoluciones de ventas
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="bonificacionCumplimiento" checked>
                                        <label class="form-check-label" for="bonificacionCumplimiento">
                                            Bonificación por cumplimiento de meta
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Descuentos y Deducciones</h6>
                                <div class="mb-3">
                                    <label class="form-label">Descuento Administrativo %</label>
                                    <input type="number" class="form-control" id="descuentoAdmin" value="0" min="0" max="100" step="0.1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Adelantos a descontar</label>
                                    <div class="input-group">
                                        <span class="input-group-text">S/</span>
                                        <input type="number" class="form-control" id="adelantos" value="0" min="0" step="0.01">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="text-center">
                            <button type="button" class="btn btn-success btn-lg" onclick="calcularComisiones()">
                                <i class="fas fa-calculator"></i> Calcular Comisiones
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="col-lg-4 mb-4">
                <!-- Resumen de Empleados -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary bg-opacity-10 border-0">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-chart-pie me-2"></i>
                            Resumen de Empleados
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Empleados Seleccionados:</span>
                                <strong id="empleadosSeleccionados">2</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Meta Total:</span>
                                <strong id="metaTotal">S/ 75,000</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Comisión Promedio:</span>
                                <strong id="comisionPromedio">3.25%</strong>
                            </div>
                        </div>

                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: 75%" id="progresoCompletitud"></div>
                        </div>
                        <small class="text-muted">Completitud de datos: 75%</small>
                    </div>
                </div>

                <!-- Cálculo Preliminar -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-success bg-opacity-10 border-0">
                        <h5 class="mb-0 text-success">
                            <i class="fas fa-calculator me-2"></i>
                            Cálculo Preliminar
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ventas Netas Totales:</span>
                                <strong id="ventasNetas">S/ 84,600.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Comisiones Base:</span>
                                <strong id="comisionesBase">S/ 2,766.40</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Bonificaciones:</span>
                                <strong id="bonificaciones">S/ 215.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Descuentos:</span>
                                <strong id="descuentos" class="text-danger">S/ 0.00</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span><strong>Total a Pagar:</strong></span>
                                <h4 class="text-success mb-0" id="totalAPagar">S/ 2,981.40</h4>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Cálculo aproximado basado en datos disponibles</small>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Bonificaciones -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-warning bg-opacity-10 border-0">
                        <h5 class="mb-0 text-warning">
                            <i class="fas fa-gift me-2"></i>
                            Bonificaciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Cumplimiento de Meta (%)</label>
                            <input type="number" class="form-control" id="cumplimientoMeta" value="100" min="50" max="150" step="1" 
                                   onchange="calcularBonificacionMeta()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bonificación por Meta (%)</label>
                            <input type="number" class="form-control" id="bonificacionMeta" value="3.0" min="0" max="20" step="0.1" 
                                   onchange="calcularBonificacionMeta()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ventas Extras (por encima del 110%)</label>
                            <input type="number" class="form-control" id="ventasExtras" value="2.0" min="0" max="10" step="0.1">
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-secondary bg-opacity-10 border-0">
                        <h5 class="mb-0 text-secondary">
                            <i class="fas fa-save me-2"></i>
                            Guardar Planilla
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar Planilla
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="guardarBorrador()">
                                <i class="fas fa-file-alt"></i> Guardar Borrador
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="vistaPrevia()">
                                <i class="fas fa-eye"></i> Vista Previa
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="exportarConfiguracion()">
                                <i class="fas fa-download"></i> Exportar Config.
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal de Configuración de Empleado -->
<div class="modal fade" id="modalConfigEmpleado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-cog me-2"></i>
                    Configurar Empleado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img src="https://via.placeholder.com/64" class="rounded-circle mb-2" alt="Empleado">
                    <h6 id="nombreEmpleadoModal">Ana García</h6>
                    <p class="text-muted" id="cargoEmpleadoModal">Farmacéutica Senior</p>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Comisión Base %</label>
                        <input type="number" class="form-control" id="comisionBaseModal" value="3.5" min="0" max="20" step="0.1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Meta Mensual</label>
                        <div class="input-group">
                            <span class="input-group-text">S/</span>
                            <input type="number" class="form-control" id="metaMensualModal" value="40000" min="0" step="100">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Meta Trimestral</label>
                        <div class="input-group">
                            <span class="input-group-text">S/</span>
                            <input type="number" class="form-control" id="metaTrimestralModal" value="120000" min="0" step="100">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Meta Anual</label>
                        <div class="input-group">
                            <span class="input-group-text">S/</span>
                            <input type="number" class="form-control" id="metaAnualModal" value="480000" min="0" step="100">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Especialidades/Productos</label>
                        <textarea class="form-control" id="especialidadesModal" rows="3" placeholder="Especialidades del empleado...">Farmacia general, Productos controlados</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observacionesModal" rows="2" placeholder="Observaciones específicas..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarConfiguracionEmpleado()">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let empleadosSeleccionados = new Set();
let empleadoConfigurado = '';

function buscarEmpleado() {
    const busqueda = document.getElementById('busquedaEmpleado').value.toLowerCase();
    const filas = document.querySelectorAll('#tablaEmpleados tbody tr');
    
    filas.forEach(fila => {
        const nombre = fila.cells[1].textContent.toLowerCase();
        if (nombre.includes(busqueda)) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}

function seleccionarTodos() {
    const checkboxes = document.querySelectorAll('.empleado-checkbox:not(:checked)');
    checkboxes.forEach(cb => cb.checked = true);
    actualizarContadores();
}

function limpiarSeleccion() {
    document.querySelectorAll('.empleado-checkbox:checked').forEach(cb => cb.checked = false);
    actualizarContadores();
}

function actualizarComision(codigoEmpleado, porcentaje) {
    console.log(`Actualizar comisión para ${codigoEmpleado}: ${porcentaje}%`);
    actualizarContadores();
}

function actualizarMeta(codigoEmpleado, meta) {
    console.log(`Actualizar meta para ${codigoEmpleado}: S/ ${meta}`);
    actualizarContadores();
}

function configurarEmpleado(codigoEmpleado) {
    empleadoConfigurado = codigoEmpleado;
    
    // Obtener datos del empleado
    const fila = document.querySelector(`input[value="${codigoEmpleado}"]`).closest('tr');
    const nombre = fila.cells[1].querySelector('strong').textContent;
    const cargo = fila.cells[2].querySelector('strong').textContent;
    
    // Llenar modal
    document.getElementById('nombreEmpleadoModal').textContent = nombre;
    document.getElementById('cargoEmpleadoModal').textContent = cargo;
    
    const modal = new bootstrap.Modal(document.getElementById('modalConfigEmpleado'));
    modal.show();
}

function guardarConfiguracionEmpleado() {
    const datos = {
        comisionBase: document.getElementById('comisionBaseModal').value,
        metaMensual: document.getElementById('metaMensualModal').value,
        metaTrimestral: document.getElementById('metaTrimestralModal').value,
        metaAnual: document.getElementById('metaAnualModal').value,
        especialidades: document.getElementById('especialidadesModal').value,
        observaciones: document.getElementById('observacionesModal').value
    };
    
    // Actualizar tabla
    const fila = document.querySelector(`input[value="${empleadoConfigurado}"]`).closest('tr');
    const inputs = fila.querySelectorAll('input[type="number"]');
    inputs[0].value = datos.comisionBase; // Comisión
    inputs[1].value = datos.metaMensual; // Meta
    
    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfigEmpleado'));
    modal.hide();
    
    Swal.fire({
        icon: 'success',
        title: 'Configuración guardada',
        text: 'La configuración del empleado ha sido actualizada'
    });
}

function calcularComisiones() {
    const empleadosSeleccionados = document.querySelectorAll('.empleado-checkbox:checked');
    
    if (empleadosSeleccionados.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay empleados seleccionados',
            text: 'Selecciona al menos un empleado para calcular'
        });
        return;
    }
    
    Swal.fire({
        title: 'Calculando comisiones...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });
    
    setTimeout(() => {
        // Simular cálculo
        const ventasNetas = Math.random() * 50000 + 70000;
        const comisionesBase = ventasNetas * 0.0325;
        const bonificaciones = comisionesBase * 0.08;
        const total = comisionesBase + bonificaciones;
        
        // Actualizar resumen
        document.getElementById('ventasNetas').textContent = 'S/ ' + ventasNetas.toFixed(2);
        document.getElementById('comisionesBase').textContent = 'S/ ' + comisionesBase.toFixed(2);
        document.getElementById('bonificaciones').textContent = 'S/ ' + bonificaciones.toFixed(2);
        document.getElementById('totalAPagar').textContent = 'S/ ' + total.toFixed(2);
        
        Swal.fire({
            icon: 'success',
            title: 'Cálculo completado',
            text: `Comisiones calculadas para ${empleadosSeleccionados.length} empleados`
        });
    }, 2000);
}

function calcularBonificacionMeta() {
    const cumplimiento = parseFloat(document.getElementById('cumplimientoMeta').value) || 100;
    const bonificacion = parseFloat(document.getElementById('bonificacionMeta').value) || 0;
    
    console.log('Calculando bonificación por meta:', { cumplimiento, bonificacion });
}

function actualizarContadores() {
    const seleccionados = document.querySelectorAll('.empleado-checkbox:checked');
    const metas = Array.from(seleccionados).map(cb => {
        const fila = cb.closest('tr');
        return parseFloat(fila.querySelector('input[type="number"]:last-child').value) || 0;
    });
    const comisiones = Array.from(seleccionados).map(cb => {
        const fila = cb.closest('tr');
        return parseFloat(fila.querySelector('input[type="number"]:first-child').value) || 0;
    });
    
    const metaTotal = metas.reduce((a, b) => a + b, 0);
    const comisionPromedio = comisiones.length > 0 ? 
        (comisiones.reduce((a, b) => a + b, 0) / comisiones.length).toFixed(2) : 0;
    
    document.getElementById('empleadosSeleccionados').textContent = seleccionados.length;
    document.getElementById('metaTotal').textContent = 'S/ ' + metaTotal.toLocaleString();
    document.getElementById('comisionPromedio').textContent = comisionPromedio + '%';
    
    // Actualizar barra de progreso
    const progreso = Math.min((seleccionados.length / 3) * 100, 100);
    document.getElementById('progresoCompletitud').style.width = progreso + '%';
}

function guardarPlanilla() {
    const formData = new FormData(document.getElementById('planillaForm'));
    
    // Validar campos requeridos
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    const responsable = document.getElementById('responsable').value;
    
    if (!fechaInicio || !fechaFin || !responsable) {
        Swal.fire({
            icon: 'error',
            title: 'Campos requeridos',
            text: 'Completa todos los campos obligatorios'
        });
        return;
    }
    
    // Verificar empleados seleccionados
    const empleadosSeleccionados = document.querySelectorAll('.empleado-checkbox:checked');
    if (empleadosSeleccionados.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Sin empleados',
            text: 'Selecciona al menos un empleado para la planilla'
        });
        return;
    }
    
    Swal.fire({
        title: 'Guardando planilla...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Planilla guardada',
            text: `La planilla ${document.getElementById('codigoPlanilla').value} ha sido creada exitosamente`,
            showCancelButton: true,
            confirmButtonText: 'Ver Planilla',
            cancelButtonText: 'Crear Otra'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/ventas/planillas/ver/${document.getElementById('codigoPlanilla').value}`;
            } else {
                window.location.href = '/ventas/planillas/nueva';
            }
        });
    }, 2000);
}

function guardarBorrador() {
    Swal.fire({
        icon: 'success',
        title: 'Borrador guardado',
        text: 'La planilla se ha guardado como borrador'
    });
}

function vistaPrevia() {
    window.open(`/ventas/planillas/vista-previa/${document.getElementById('codigoPlanilla').value}`, '_blank');
}

function exportarConfiguracion() {
    const config = {
        codigo: document.getElementById('codigoPlanilla').value,
        periodo: {
            inicio: document.getElementById('fechaInicio').value,
            fin: document.getElementById('fechaFin').value
        },
        responsable: document.getElementById('responsable').value,
        empleados: Array.from(document.querySelectorAll('.empleado-checkbox:checked')).map(cb => {
            const fila = cb.closest('tr');
            return {
                codigo: cb.value,
                nombre: fila.querySelector('strong').textContent,
                comision: parseFloat(fila.querySelector('input[type="number"]:first-child').value),
                meta: parseFloat(fila.querySelector('input[type="number"]:last-child').value)
            };
        })
    };
    
    const blob = new Blob([JSON.stringify(config, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `planilla_${config.codigo}_config.json`;
    a.click();
    URL.revokeObjectURL(url);
    
    Swal.fire({
        icon: 'success',
        title: 'Configuración exportada',
        text: 'El archivo de configuración se ha descargado'
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Select all empleados
    document.getElementById('selectAllEmpleados').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.empleado-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        actualizarContadores();
    });
    
    // Individual checkboxes
    document.querySelectorAll('.empleado-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', actualizarContadores);
    });
    
    // Initialize counters
    actualizarContadores();
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

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.progress {
    border-radius: 0.5rem;
}

.alert {
    margin-bottom: 1rem;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.modal-xl {
    max-width: 90%;
}

.input-group-text {
    font-size: 0.875rem;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
}
</style>
@endsection