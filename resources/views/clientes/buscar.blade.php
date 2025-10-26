@extends('dashboard.contador')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-search text-primary"></i> Búsqueda Avanzada de Clientes
        </h1>
        <div>
            <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Clientes
            </a>
            <button class="btn btn-outline-info" onclick="limpiarBusqueda()">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
        </div>
    </div>

    <!-- Panel de Búsqueda Avanzada -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Criterios de Búsqueda</h6>
        </div>
        <div class="card-body">
            <form id="busquedaForm" method="GET">
                <!-- Búsqueda Rápida -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Búsqueda Rápida</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="q" 
                                       value="{{ request('q') }}" 
                                       placeholder="Nombre, RUC, DNI, email, teléfono...">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Búsqueda por Múltiples Criterios</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="busquedaAvanzada" 
                                       {{ request('avanzada') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="busquedaAvanzada">
                                    Activar búsqueda avanzada
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros Avanzados -->
                <div id="filtrosAvanzados" {{ request('avanzada') ? '' : 'style=display:none' }}>
                    <hr>
                    <h6 class="text-primary">Filtros Avanzados</h6>
                    
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-md-6">
                            <h6 class="text-secondary">Información Básica</h6>
                            <div class="form-group">
                                <label>Tipo de Cliente</label>
                                <select class="form-control" name="tipo">
                                    <option value="">Todos</option>
                                    <option value="persona" {{ request('tipo') == 'persona' ? 'selected' : '' }}>Persona Natural</option>
                                    <option value="empresa" {{ request('tipo') == 'empresa' ? 'selected' : '' }}>Persona Jurídica</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Categoría</label>
                                <select class="form-control" name="categoria">
                                    <option value="">Todas</option>
                                    <option value="a" {{ request('categoria') == 'a' ? 'selected' : '' }}>Categoría A - VIP</option>
                                    <option value="b" {{ request('categoria') == 'b' ? 'selected' : '' }}>Categoría B</option>
                                    <option value="c" {{ request('categoria') == 'c' ? 'selected' : '' }}>Categoría C</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Estado</label>
                                <select class="form-control" name="estado">
                                    <option value="">Todos</option>
                                    <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    <option value="suspendido" {{ request('estado') == 'suspendido' ? 'selected' : '' }}>Suspendido</option>
                                </select>
                            </div>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="col-md-6">
                            <h6 class="text-secondary">Información de Contacto</h6>
                            <div class="form-group">
                                <label>Región</label>
                                <select class="form-control" name="region">
                                    <option value="">Todas</option>
                                    <option value="norte" {{ request('region') == 'norte' ? 'selected' : '' }}>Norte</option>
                                    <option value="sur" {{ request('region') == 'sur' ? 'selected' : '' }}>Sur</option>
                                    <option value="este" {{ request('region') == 'este' ? 'selected' : '' }}>Este</option>
                                    <option value="oeste" {{ request('region') == 'oeste' ? 'selected' : '' }}>Oeste</option>
                                    <option value="centro" {{ request('region') == 'centro' ? 'selected' : '' }}>Centro</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Departamento</label>
                                <select class="form-control" name="departamento" id="busquedaDepartamento" onchange="cargarProvinciasBusqueda()">
                                    <option value="">Todos</option>
                                    <option value="lima" {{ request('departamento') == 'lima' ? 'selected' : '' }}>Lima</option>
                                    <option value="arequipa" {{ request('departamento') == 'arequipa' ? 'selected' : '' }}>Arequipa</option>
                                    <option value="cusco" {{ request('departamento') == 'cusco' ? 'selected' : '' }}>Cusco</option>
                                    <option value="la-libertad" {{ request('departamento') == 'la-libertad' ? 'selected' : '' }}>La Libertad</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Provincia</label>
                                <select class="form-control" name="provincia" id="busquedaProvincia">
                                    <option value="">Todas</option>
                                    @if(request('provincia'))
                                        <option value="{{ request('provincia') }}" selected>{{ request('provincia') }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Información Comercial -->
                        <div class="col-md-6">
                            <h6 class="text-secondary">Información Comercial</h6>
                            <div class="form-group">
                                <label>Giro del Negocio</label>
                                <select class="form-control" name="giro_negocio">
                                    <option value="">Todos</option>
                                    <option value="farmacia" {{ request('giro_negocio') == 'farmacia' ? 'selected' : '' }}>Farmacia</option>
                                    <option value="hospital" {{ request('giro_negocio') == 'hospital' ? 'selected' : '' }}>Hospital</option>
                                    <option value="clinica" {{ request('giro_negocio') == 'clinica' ? 'selected' : '' }}>Clínica</option>
                                    <option value="laboratorio" {{ request('giro_negocio') == 'laboratorio' ? 'selected' : '' }}>Laboratorio</option>
                                    <option value="medico" {{ request('giro_negocio') == 'medico' ? 'selected' : '' }}>Médico Independiente</option>
                                    <option value="otros" {{ request('giro_negocio') == 'otros' ? 'selected' : '' }}>Otros</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Forma de Pago</label>
                                <select class="form-control" name="forma_pago">
                                    <option value="">Todas</option>
                                    <option value="contado" {{ request('forma_pago') == 'contado' ? 'selected' : '' }}>Contado</option>
                                    <option value="credito" {{ request('forma_pago') == 'credito' ? 'selected' : '' }}>Crédito</option>
                                    <option value="mixto" {{ request('forma_pago') == 'mixto' ? 'selected' : '' }}>Mixto</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Días de Crédito</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="dias_credito_min" 
                                               placeholder="Mín" value="{{ request('dias_credito_min') }}">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="dias_credito_max" 
                                               placeholder="Máx" value="{{ request('dias_credito_max') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas -->
                        <div class="col-md-6">
                            <h6 class="text-secondary">Fechas</h6>
                            <div class="form-group">
                                <label>Fecha de Registro</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="date" class="form-control" name="fecha_registro_desde" 
                                               value="{{ request('fecha_registro_desde') }}" placeholder="Desde">
                                    </div>
                                    <div class="col-6">
                                        <input type="date" class="form-control" name="fecha_registro_hasta" 
                                               value="{{ request('fecha_registro_hasta') }}" placeholder="Hasta">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Última Compra</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="date" class="form-control" name="ultima_compra_desde" 
                                               value="{{ request('ultima_compra_desde') }}" placeholder="Desde">
                                    </div>
                                    <div class="col-6">
                                        <input type="date" class="form-control" name="ultima_compra_hasta" 
                                               value="{{ request('ultima_compra_hasta') }}" placeholder="Hasta">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="solo_con_compras" value="1" 
                                           {{ request('solo_con_compras') ? 'checked' : '' }}>
                                    Solo clientes con compras
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones de Búsqueda -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Buscar con Filtros
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="guardarBusqueda()">
                                        <i class="fas fa-bookmark"></i> Guardar Búsqueda
                                    </button>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-outline-info" onclick="exportarResultados()">
                                        <i class="fas fa-download"></i> Exportar Resultados
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" onclick="generarReporte()">
                                        <i class="fas fa-chart-bar"></i> Generar Reporte
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados de Búsqueda -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                Resultados de Búsqueda 
                <span class="badge badge-primary ml-2">{{ $resultados ?? '2,847' }}</span>
            </h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Opciones:</div>
                    <a class="dropdown-item" href="#" onclick="ordenarPor('nombre')">
                        <i class="fas fa-sort-alpha-down"></i> Ordenar por Nombre
                    </a>
                    <a class="dropdown-item" href="#" onclick="ordenarPor('fecha')">
                        <i class="fas fa-sort-numeric-down"></i> Ordenar por Fecha
                    </a>
                    <a class="dropdown-item" href="#" onclick="ordenarPor('region')">
                        <i class="fas fa-map-marker-alt"></i> Ordenar por Región
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="mostrarVista('tarjetas')">
                        <i class="fas fa-th"></i> Vista de Tarjetas
                    </a>
                    <a class="dropdown-item" href="#" onclick="mostrarVista('tabla')">
                        <i class="fas fa-table"></i> Vista de Tabla
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Vista de Tarjetas -->
            <div id="vistaTarjetas" class="row">
                @for($i = 1; $i <= 6; $i++)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        CLI-{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800 mb-2">
                                        @if($i == 1) Hospital Central S.A.
                                        @elseif($i == 2) Farmacia Bienestar
                                        @elseif($i == 3) Clínica San José
                                        @elseif($i == 4) Laboratorio Médico Plus
                                        @elseif($i == 5) Dr. Roberto Silva
                                        @else Farmacia Salud Total @endif
                                    </div>
                                    <div class="text-xs text-muted">
                                        <i class="fas fa-phone"></i> +51 999 888 777<br>
                                        <i class="fas fa-envelope"></i> cliente@email.com<br>
                                        <i class="fas fa-map-marker-alt"></i> 
                                        @if($i <= 3) Lima @elseif($i <= 4) Arequipa @else Cusco @endif
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-sm btn-outline-info mb-1" 
                                                onclick="verCliente('{{ $i }}')" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary mb-1" 
                                                onclick="editarCliente('{{ $i }}')" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="estadoCuenta('{{ $i }}')" title="Estado Cuenta">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            @if($i <= 2) <span class="badge badge-warning">VIP</span> 
                                            @elseif($i <= 4) <span class="badge badge-success">B</span>
                                            @else <span class="badge badge-secondary">C</span> @endif
                                        </small>
                                    </div>
                                    <div class="col-6 text-right">
                                        <small class="text-muted">
                                            <span class="badge badge-success">Activo</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>

            <!-- Vista de Tabla -->
            <div id="vistaTabla" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="resultadosTable" width="100%" cellspacing="0">
                        <thead class="thead-dark">
                            <tr>
                                <th>Código</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>RUC/DNI</th>
                                <th>Contacto</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Categoría</th>
                                <th>Región</th>
                                <th>Estado</th>
                                <th>Última Compra</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 1; $i <= 6; $i++)
                            <tr>
                                <td><strong>CLI-{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>
                                    @if($i == 1) Hospital Central S.A.
                                    @elseif($i == 2) Farmacia Bienestar
                                    @elseif($i == 3) Clínica San José
                                    @elseif($i == 4) Laboratorio Médico Plus
                                    @elseif($i == 5) Dr. Roberto Silva
                                    @else Farmacia Salud Total @endif
                                </td>
                                <td>
                                    @if($i <= 4) <span class="badge badge-info">Empresa</span>
                                    @else <span class="badge badge-primary">Persona</span> @endif
                                </td>
                                <td>{{ 20000000000 + $i * 1000000 }}</td>
                                <td>
                                    @if($i == 1) Dr. Carlos Mendoza
                                    @elseif($i == 2) María González
                                    @elseif($i == 3) Dr. Luis Rodríguez
                                    @elseif($i == 4) Dra. Ana Martínez
                                    @elseif($i == 5) Dr. Roberto Silva
                                    @else Patricia López @endif
                                </td>
                                <td>+51 999 888 777</td>
                                <td>cliente{{ $i }}@email.com</td>
                                <td>
                                    @if($i <= 2) <span class="badge badge-warning">VIP</span>
                                    @elseif($i <= 4) <span class="badge badge-success">B</span>
                                    @else <span class="badge badge-secondary">C</span> @endif
                                </td>
                                <td><span class="badge badge-primary">Norte</span></td>
                                <td><span class="badge badge-success">Activo</span></td>
                                <td>{{ date('d/m/Y', strtotime("-$i days")) }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="verCliente('{{ $i }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarCliente('{{ $i }}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="estadoCuenta('{{ $i }}')">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginación -->
            <div class="mt-4">
                <nav aria-label="Resultados de búsqueda">
                    <ul class="pagination justify-content-center">
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

    <!-- Búsquedas Guardadas -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Búsquedas Guardadas</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Clientes VIP Lima</h6>
                            <p class="small text-muted">Búsqueda: Categoría A, Región Norte</p>
                            <button class="btn btn-sm btn-outline-primary" onclick="cargarBusqueda('vip-lima')">
                                <i class="fas fa-play"></i> Ejecutar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Empresas Activas</h6>
                            <p class="small text-muted">Búsqueda: Tipo Empresa, Estado Activo</p>
                            <button class="btn btn-sm btn-outline-primary" onclick="cargarBusqueda('empresas-activas')">
                                <i class="fas fa-play"></i> Ejecutar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Clientes Recientes</h6>
                            <p class="small text-muted">Búsqueda: Registrados últimos 30 días</p>
                            <button class="btn btn-sm btn-outline-primary" onclick="cargarBusqueda('recientes')">
                                <i class="fas fa-play"></i> Ejecutar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function cargarProvinciasBusqueda() {
    const departamento = document.getElementById('busquedaDepartamento').value;
    const provinciaSelect = document.getElementById('busquedaProvincia');
    
    provinciaSelect.innerHTML = '<option value="">Todas</option>';
    
    if (departamento) {
        const provincias = {
            'lima': ['Lima', 'Callao', 'Cañete', 'Huarochirí'],
            'arequipa': ['Arequipa', 'Camaná', 'Caravelí', 'Castilla'],
            'cusco': ['Cusco', 'Acomayo', 'Anta', 'Calca'],
            'la-libertad': ['Trujillo', 'Ascope', 'Bolívar', 'Chepén']
        };
        
        provincias[departamento].forEach(provincia => {
            const option = document.createElement('option');
            option.value = provincia.toLowerCase().replace(' ', '-');
            option.textContent = provincia;
            provinciaSelect.appendChild(option);
        });
    }
}

function cambiarVista(vista) {
    const tarjetas = document.getElementById('vistaTarjetas');
    const tabla = document.getElementById('vistaTabla');
    
    if (vista === 'tarjetas') {
        tarjetas.style.display = 'block';
        tabla.style.display = 'none';
    } else {
        tarjetas.style.display = 'none';
        tabla.style.display = 'block';
        initializeDataTable();
    }
}

function initializeDataTable() {
    $('#resultadosTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        }
    });
}

function limpiarBusqueda() {
    document.getElementById('busquedaForm').reset();
    window.location.href = '{{ route("clientes.buscar") }}';
}

function guardarBusqueda() {
    Swal.fire({
        title: 'Guardar Búsqueda',
        text: 'Ingrese un nombre para esta búsqueda:',
        input: 'text',
        inputPlaceholder: 'Nombre de la búsqueda',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                title: '¡Guardada!',
                text: `La búsqueda "${result.value}" ha sido guardada.`,
                icon: 'success'
            });
        }
    });
}

function cargarBusqueda(nombre) {
    Swal.fire({
        title: 'Cargando búsqueda...',
        text: `Ejecutando búsqueda guardada: ${nombre}`,
        timer: 1500,
        timerProgressBar: true,
        showConfirmButton: false
    });
}

function exportarResultados() {
    Swal.fire({
        title: 'Exportar Resultados',
        text: '¿En qué formato desea exportar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Excel',
        cancelButtonText: 'PDF',
        showDenyButton: true,
        denyButtonText: 'CSV'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('¡Exportado!', 'Archivo Excel generado exitosamente.', 'success');
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire('¡Exportado!', 'Archivo PDF generado exitosamente.', 'success');
        } else if (result.isDenied) {
            Swal.fire('¡Exportado!', 'Archivo CSV generado exitosamente.', 'success');
        }
    });
}

function generarReporte() {
    Swal.fire({
        title: 'Generando Reporte...',
        text: 'Creando reporte detallado de clientes',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Completado!', 'El reporte ha sido generado exitosamente.', 'success');
    });
}

function ordenarPor(campo) {
    Swal.fire({
        title: 'Ordenando...',
        text: `Ordenando por ${campo}`,
        timer: 1000,
        timerProgressBar: true,
        showConfirmButton: false
    });
}

function verCliente(id) {
    window.location.href = `/clientes/${id}`;
}

function editarCliente(id) {
    window.location.href = `/clientes/${id}/editar`;
}

function estadoCuenta(id) {
    window.location.href = `/clientes/${id}/estado-cuenta`;
}

// Event listeners
document.getElementById('busquedaAvanzada').addEventListener('change', function() {
    const filtros = document.getElementById('filtrosAvanzados');
    if (this.checked) {
        filtros.style.display = 'block';
    } else {
        filtros.style.display = 'none';
    }
});

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    // Si hay parámetros de búsqueda avanzada, mostrar filtros
    const params = new URLSearchParams(window.location.search);
    if (params.has('avanzada') || params.has('tipo') || params.has('categoria')) {
        document.getElementById('busquedaAvanzada').checked = true;
        document.getElementById('filtrosAvanzados').style.display = 'block';
    }
});
</script>
@endsection