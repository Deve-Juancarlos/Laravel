@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-book text-primary"></i> Libros Electrónicos SUNAT
        </h1>
        <div>
            <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Reportes
            </a>
            <button class="btn btn-outline-success" onclick="generarLE()">
                <i class="fas fa-file-excel"></i> Generar LE
            </button>
            <button class="btn btn-outline-primary" onclick="enviarLibros()">
                <i class="fas fa-cloud-upload-alt"></i> Enviar a SUNAT
            </button>
        </div>
    </div>

    <!-- Configuración -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Configuración de Libros Electrónicos</h6>
        </div>
        <div class="card-body">
            <form id="librosForm" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Período Contable</label>
                            <select class="form-control" name="periodo" id="periodo">
                                <option value="2024-01" {{ request('periodo', date('Y-m')) == '2024-01' ? 'selected' : '' }}>Enero 2024</option>
                                <option value="2023-12" {{ request('periodo') == '2023-12' ? 'selected' : '' }}>Diciembre 2023</option>
                                <option value="2023-11" {{ request('periodo') == '2023-11' ? 'selected' : '' }}>Noviembre 2023</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Libros a Generar</label>
                            <select class="form-control" name="libros" multiple>
                                <option value="ventas" selected>Libro de Ventas</option>
                                <option value="compras" selected>Libro de Compras</option>
                                <option value="diario" selected>Libro Diario</option>
                                <option value="mayor" selected>Libro Mayor</option>
                                <option value="inventarios">Libro de Inventarios</option>
                            </select>
                            <small class="form-text text-muted">Mantener Ctrl/Cmd presionado para seleccionar múltiples</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Formato de Salida</label>
                            <select class="form-control" name="formato">
                                <option value="xlsx" selected>Excel (.xlsx)</option>
                                <option value="txt">Texto (.txt)</option>
                                <option value="csv">CSV (.csv)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Opción de Envío</label>
                            <select class="form-control" name="opcion_envio">
                                <option value="PLE" selected>PLE (Plataforma de Libros Electrónicos)</option>
                                <option value="SEE">SEE (Sistema de Envío Electrónico)</option>
                                <option value="ambos">Ambos</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-cog"></i> Configurar Libros
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="validarInformacion()">
                            <i class="fas fa-check-circle"></i> Validar Información
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="vistaPrevia()">
                            <i class="fas fa-eye"></i> Vista Previa
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estado de Generación -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Libros Generados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">4</div>
                            <div class="text-xs text-success">
                                Listos para envío
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Registros Totales
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">15,847</div>
                            <div class="text-xs text-primary">
                                Procesados este período
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list-alt fa-2x text-gray-300"></i>
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
                                Tamaño Archivos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2.4 MB</div>
                            <div class="text-xs text-info">
                                Total generado
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hdd fa-2x text-gray-300"></i>
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
                                Estado Envío
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Enviado</div>
                            <div class="text-xs text-success">
                                Ticket: LE202401000123
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Libros -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Libros Electrónicos Generados</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Opciones:</div>
                    <a class="dropdown-item" href="#" onclick="exportarSeleccionados()">
                        <i class="fas fa-download"></i> Exportar Seleccionados
                    </a>
                    <a class="dropdown-item" href="#" onclick="validarTodos()">
                        <i class="fas fa-check"></i> Validar Todos
                    </a>
                    <a class="dropdown-item" href="#" onclick="reenviarLibros()">
                        <i class="fas fa-redo"></i> Reenviar a SUNAT
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="librosTable" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAllLibros" onchange="toggleAllLibros()">
                            </th>
                            <th>Libro</th>
                            <th>Código</th>
                            <th>Período</th>
                            <th>Registros</th>
                            <th>Tamaño</th>
                            <th>Estado</th>
                            <th>Última Actualización</th>
                            <th>Estado SUNAT</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="checkbox" class="libro-checkbox" value="ventas" checked></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-shopping-cart text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong>Libro de Ventas</strong><br>
                                        <small class="text-muted">Registro de ventas e ingresos</small>
                                    </div>
                                </div>
                            </td>
                            <td><strong>LE202401VE01</strong></td>
                            <td>Enero 2024</td>
                            <td class="text-right">8,456</td>
                            <td class="text-right">1.2 MB</td>
                            <td><span class="badge badge-success">Generado</span></td>
                            <td>25/01/2024 14:30</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verLibro('ventas')" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="descargarLibro('ventas')" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="enviarLibro('ventas')" title="Enviar">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="libro-checkbox" value="compras" checked></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-truck text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong>Libro de Compras</strong><br>
                                        <small class="text-muted">Registro de compras y gastos</small>
                                    </div>
                                </div>
                            </td>
                            <td><strong>LE202401CO01</strong></td>
                            <td>Enero 2024</td>
                            <td class="text-right">5,234</td>
                            <td class="text-right">856 KB</td>
                            <td><span class="badge badge-success">Generado</span></td>
                            <td>25/01/2024 14:25</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verLibro('compras')" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="descargarLibro('compras')" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="enviarLibro('compras')" title="Enviar">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="libro-checkbox" value="diario" checked></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-book text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong>Libro Diario</strong><br>
                                        <small class="text-muted">Registro de asientos contables</small>
                                    </div>
                                </div>
                            </td>
                            <td><strong>LE202401DI01</strong></td>
                            <td>Enero 2024</td>
                            <td class="text-right">1,847</td>
                            <td class="text-right">234 KB</td>
                            <td><span class="badge badge-success">Generado</span></td>
                            <td>25/01/2024 14:20</td>
                            <td><span class="badge badge-warning">Pendiente</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verLibro('diario')" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="descargarLibro('diario')" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="enviarLibro('diario')" title="Enviar">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="libro-checkbox" value="mayor" checked></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-info">
                                            <i class="fas fa-calculator text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong>Libro Mayor</strong><br>
                                        <small class="text-muted">Cuentas por auxiliares</small>
                                    </div>
                                </div>
                            </td>
                            <td><strong>LE202401MA01</strong></td>
                            <td>Enero 2024</td>
                            <td class="text-right">310</td>
                            <td class="text-right">156 KB</td>
                            <td><span class="badge badge-success">Generado</span></td>
                            <td>25/01/2024 14:15</td>
                            <td><span class="badge badge-warning">Pendiente</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="verLibro('mayor')" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="descargarLibro('mayor')" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="enviarLibro('mayor')" title="Enviar">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="libro-checkbox" value="inventarios"></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-secondary">
                                            <i class="fas fa-boxes text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong>Libro de Inventarios</strong><br>
                                        <small class="text-muted">Balance de inventarios</small>
                                    </div>
                                </div>
                            </td>
                            <td><strong>LE202401IN01</strong></td>
                            <td>Enero 2024</td>
                            <td class="text-right">-</td>
                            <td class="text-right">-</td>
                            <td><span class="badge badge-secondary">No Generado</span></td>
                            <td>-</td>
                            <td><span class="badge badge-secondary">Pendiente</span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="generarLibro('inventarios')" title="Generar">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Acciones en lote -->
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success" onclick="validarSeleccionados()" disabled>
                        <i class="fas fa-check"></i> Validar
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="enviarSeleccionados()" disabled>
                        <i class="fas fa-paper-plane"></i> Enviar a SUNAT
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="descargarSeleccionados()" disabled>
                        <i class="fas fa-download"></i> Descargar
                    </button>
                </div>
                <div>
                    <small class="text-muted">
                        <span id="librosSeleccionados">0</span> libros seleccionados
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle del Libro Seleccionado -->
    <div class="card shadow mt-4" id="detalleLibro" style="display: none;">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary" id="tituloDetalle">Detalle del Libro</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="tablaDetalle">
                    <thead class="thead-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Comprobante</th>
                            <th>Glosa</th>
                            <th>Debe</th>
                            <th>Haber</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="contenidoDetalle">
                        <!-- Contenido dinámico -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Historial de Envíos -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Historial de Envíos a SUNAT</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Fecha Envío</th>
                            <th>Período</th>
                            <th>Libros Enviados</th>
                            <th>Ticket SUNAT</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>25/01/2024 14:45</td>
                            <td>Enero 2024</td>
                            <td>Ventas, Compras, Diario, Mayor</td>
                            <td>LE202401000123456</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>Envío exitoso</td>
                        </tr>
                        <tr>
                            <td>15/12/2023 10:20</td>
                            <td>Diciembre 2023</td>
                            <td>Ventas, Compras, Diario, Mayor</td>
                            <td>LE202312000123455</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>Envío exitoso</td>
                        </tr>
                        <tr>
                            <td>15/11/2023 16:30</td>
                            <td>Noviembre 2023</td>
                            <td>Ventas, Compras, Diario, Mayor</td>
                            <td>LE202311000123454</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>Envío exitoso</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function toggleAllLibros() {
    const selectAll = document.getElementById('selectAllLibros');
    const checkboxes = document.querySelectorAll('.libro-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateAccionesLibros();
}

function updateAccionesLibros() {
    const checkboxes = document.querySelectorAll('.libro-checkbox:checked');
    const botones = document.querySelectorAll('.btn-outline-success, .btn-outline-primary, .btn-outline-info');
    
    if (checkboxes.length > 0) {
        botones.forEach(boton => {
            boton.disabled = false;
        });
        document.getElementById('librosSeleccionados').textContent = checkboxes.length;
    } else {
        botones.forEach(boton => {
            boton.disabled = true;
        });
        document.getElementById('librosSeleccionados').textContent = '0';
    }
}

function validarInformacion() {
    Swal.fire({
        title: 'Validando información...',
        text: 'Verificando consistencia de los datos contables',
        timer: 2500,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Validación Exitosa!',
            html: `
                <div class="text-left">
                    <p><i class="fas fa-check text-success"></i> 15,847 registros validados</p>
                    <p><i class="fas fa-check text-success"></i> Balance cuadrado</p>
                    <p><i class="fas fa-check text-success"></i> Formatos correctos</p>
                    <p><i class="fas fa-check text-success"></i> Listos para generar LE</p>
                </div>
            `,
            icon: 'success'
        });
    });
}

function vistaPrevia() {
    Swal.fire({
        title: 'Generando Vista Previa...',
        text: 'Creando vista previa de los libros electrónicos',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Vista Previa',
            html: `
                <div class="text-left">
                    <h6>Libros Generados:</h6>
                    <ul>
                        <li>Libro de Ventas: 8,456 registros</li>
                        <li>Libro de Compras: 5,234 registros</li>
                        <li>Libro Diario: 1,847 registros</li>
                        <li>Libro Mayor: 310 registros</li>
                    </ul>
                </div>
            `,
            icon: 'info'
        });
    });
}

function generarLE() {
    const libros = Array.from(document.querySelectorAll('select[name="libros"] option:checked')).map(opt => opt.value);
    
    Swal.fire({
        title: 'Generando Libros Electrónicos...',
        text: `Creando ${libros.length} libros en formato Excel`,
        timer: 4000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Libros Generados!',
            text: 'Los libros electrónicos han sido generados exitosamente.',
            icon: 'success'
        });
    });
}

function enviarLibros() {
    const libros = Array.from(document.querySelectorAll('.libro-checkbox:checked')).map(cb => cb.value);
    
    if (libros.length === 0) {
        Swal.fire({
            title: 'Sin selección',
            text: 'Seleccione al menos un libro para enviar.',
            icon: 'warning'
        });
        return;
    }
    
    Swal.fire({
        title: '¿Enviar a SUNAT?',
        text: `¿Desea enviar ${libros.length} libro(s) a SUNAT?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Enviando a SUNAT...',
                text: 'Subiendo libros, esto puede tomar varios minutos',
                timer: 6000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                Swal.fire({
                    title: '¡Enviado Exitosamente!',
                    text: 'Los libros han sido enviados a SUNAT correctamente.',
                    icon: 'success'
                });
            });
        }
    });
}

function verLibro(tipo) {
    // Ocultar otros detalles
    document.querySelectorAll('#detalleLibro').forEach(el => el.style.display = 'none');
    
    // Mostrar detalle
    const detalle = document.getElementById('detalleLibro');
    const titulo = document.getElementById('tituloDetalle');
    const contenido = document.getElementById('contenidoDetalle');
    
    titulo.textContent = `Detalle - Libro de ${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`;
    
    // Generar contenido de ejemplo
    contenido.innerHTML = generarContenidoDetalle(tipo);
    
    detalle.style.display = 'block';
    detalle.scrollIntoView({ behavior: 'smooth' });
}

function generarContenidoDetalle(tipo) {
    const datos = {
        ventas: [
            { fecha: '25/01/2024', comprobante: 'F001-0001234', glosa: 'Venta medicamentos', debe: '10,000.00', haber: '0.00', estado: 'OK' },
            { fecha: '24/01/2024', comprobante: 'F001-0001233', glosa: 'Venta equipos médicos', debe: '6,000.00', haber: '0.00', estado: 'OK' },
            { fecha: '23/01/2024', comprobante: 'B001-0005678', glosa: 'Boleta venta', debe: '3,000.00', haber: '0.00', estado: 'OK' }
        ],
        compras: [
            { fecha: '22/01/2024', comprobante: 'F002-0001234', glosa: 'Compra medicamentos', debe: '0.00', haber: '8,500.00', estado: 'OK' },
            { fecha: '21/01/2024', comprobante: 'F002-0001233', glosa: 'Compra suministros', debe: '0.00', haber: '2,340.00', estado: 'OK' }
        ],
        diario: [
            { fecha: '25/01/2024', comprobante: 'AS-0001234', glosa: 'Asiento ventas enero', debe: '15,230.00', haber: '15,230.00', estado: 'OK' },
            { fecha: '24/01/2024', comprobante: 'AS-0001233', glosa: 'Asiento compras enero', debe: '10,840.00', haber: '10,840.00', estado: 'OK' }
        ],
        mayor: [
            { fecha: '31/01/2024', comprobante: 'CC-001', glosa: 'Caja Banco', debe: '45,678.90', haber: '23,456.78', estado: 'OK' },
            { fecha: '31/01/2024', comprobante: 'CC-002', glosa: 'Clientes', debe: '67,890.12', haber: '45,123.45', estado: 'OK' }
        ]
    };
    
    return datos[tipo]?.map(item => `
        <tr>
            <td>${item.fecha}</td>
            <td><strong>${item.comprobante}</strong></td>
            <td>${item.glosa}</td>
            <td class="text-right">S/ ${item.debe}</td>
            <td class="text-right">S/ ${item.haber}</td>
            <td><span class="badge badge-success">${item.estado}</span></td>
        </tr>
    `).join('') || '<tr><td colspan="6">No hay datos disponibles</td></tr>';
}

function descargarLibro(tipo) {
    Swal.fire({
        title: 'Descargando...',
        text: `Descargando libro de ${tipo}`,
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Descarga Completada!', 'El libro ha sido descargado.', 'success');
    });
}

function enviarLibro(tipo) {
    Swal.fire({
        title: 'Enviando...',
        text: `Enviando libro de ${tipo} a SUNAT`,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Enviado!', 'El libro ha sido enviado a SUNAT.', 'success');
    });
}

function generarLibro(tipo) {
    Swal.fire({
        title: 'Generando...',
        text: `Creando libro de ${tipo}`,
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Generado!', 'El libro ha sido generado exitosamente.', 'success');
    });
}

function validarSeleccionados() {
    const seleccionados = document.querySelectorAll('.libro-checkbox:checked');
    Swal.fire({
        title: 'Validando...',
        text: `Validando ${seleccionados.length} libro(s)`,
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Validados!', 'Los libros seleccionados son válidos.', 'success');
    });
}

function enviarSeleccionados() {
    const seleccionados = document.querySelectorAll('.libro-checkbox:checked');
    Swal.fire({
        title: 'Enviando...',
        text: `Enviando ${seleccionados.length} libro(s) a SUNAT`,
        timer: 4000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Enviados!', 'Los libros han sido enviados a SUNAT.', 'success');
    });
}

function descargarSeleccionados() {
    const seleccionados = document.querySelectorAll('.libro-checkbox:checked');
    Swal.fire({
        title: 'Descargando...',
        text: `Descargando ${seleccionados.length} libro(s)`,
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Descarga Completa!', 'Los libros han sido descargados.', 'success');
    });
}

function exportarSeleccionados() {
    const seleccionados = document.querySelectorAll('.libro-checkbox:checked');
    Swal.fire({
        title: 'Exportando...',
        text: `Exportando ${seleccionados.length} libro(s) en Excel`,
        timer: 2500,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Exportados!', 'Los libros han sido exportados exitosamente.', 'success');
    });
}

function validarTodos() {
    Swal.fire({
        title: 'Validando todos los libros...',
        text: 'Verificando consistencia de toda la información contable',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Validación Completa!', 'Todos los libros han sido validados exitosamente.', 'success');
    });
}

function reenviarLibros() {
    Swal.fire({
        title: 'Reenviando libros...',
        text: 'Intentando reenvío a SUNAT',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Reenviados!', 'Los libros han sido reenviados a SUNAT.', 'success');
    });
}

// Event listeners
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('libro-checkbox')) {
        updateAccionesLibros();
    }
});

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    updateAccionesLibros();
});
</script>
@endsection