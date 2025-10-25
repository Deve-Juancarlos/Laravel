@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users text-primary"></i> Reporte PLAME SUNAT
        </h1>
        <div>
            <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Reportes
            </a>
            <button class="btn btn-outline-success" onclick="generarPLAME()">
                <i class="fas fa-file-excel"></i> Generar PLAME
            </button>
            <button class="btn btn-outline-primary" onclick="enviarPLAME()">
                <i class="fas fa-cloud-upload-alt"></i> Enviar a SUNAT
            </button>
        </div>
    </div>

    <!-- Configuración PLAME -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Configuración del Reporte PLAME</h6>
        </div>
        <div class="card-body">
            <form id="plameForm" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Período</label>
                            <select class="form-control" name="periodo" id="periodo">
                                <option value="2024-01" {{ request('periodo', date('Y-m')) == '2024-01' ? 'selected' : '' }}>Enero 2024</option>
                                <option value="2023-12" {{ request('periodo') == '2023-12' ? 'selected' : '' }}>Diciembre 2023</option>
                                <option value="2023-11" {{ request('periodo') == '2023-11' ? 'selected' : '' }}>Noviembre 2023</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tipo de Reporte</label>
                            <select class="form-control" name="tipo_reporte">
                                <option value="mensual" selected>Reporte Mensual</option>
                                <option value="regularizacion">Reporte de Regularización</option>
                                <option value="sustitutorio">Reporte Sustitutorio</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Modalidad</label>
                            <select class="form-control" name="modalidad">
                                <option value="sujeto" selected>Sujeto a retención</option>
                                <option value="agente">Agente de retención</option>
                                <option value="ambos">Ambos</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Opción de Envío</label>
                            <select class="form-control" name="opcion_envio">
                                <option value="T" selected>T - Por-generar</option>
                                <option value="E">E - Enviado</option>
                                <option value="O">O - Observado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Código de Moneda</label>
                            <select class="form-control" name="moneda">
                                <option value="PEN" selected>PEN - Soles</option>
                                <option value="USD">USD - Dólares</option>
                                <option value="EUR">EUR - Euros</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Información Adicional</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="incluirDetalle" checked>
                                <label class="custom-control-label" for="incluirDetalle">
                                    Incluir detalle de conceptos
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-cog"></i> Configurar PLAME
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="validarPLAME()">
                            <i class="fas fa-check-circle"></i> Validar Datos
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="vistaPreviaPLAME()">
                            <i class="fas fa-eye"></i> Vista Previa
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen PLAME -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Trabajadores
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">45</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +2 vs mes anterior
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Remuneración Total
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">S/ 156,789.45</div>
                            <div class="text-xs text-info">
                                Ingresos gravados
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
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
                                Retención Aplicada
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">S/ 15,678.95</div>
                            <div class="text-xs text-warning">
                                10% de retención
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
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
                                Estado SUNAT
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Enviado</div>
                            <div class="text-xs text-success">
                                Ticket: PL202401000123
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle PLAME -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Detalle del Reporte PLAME - Enero 2024</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Opciones:</div>
                    <a class="dropdown-item" href="#" onclick="exportarExcelPLAME()">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportarPDFPLAME()">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                    <a class="dropdown-item" href="#" onclick="validacionCompletaPLAME()">
                        <i class="fas fa-clipboard-check"></i> Validación Completa
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Resumen por Conceptos -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-primary">Ingresos por Categoría</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Concepto</th>
                                    <th>Cantidad</th>
                                    <th>Monto</th>
                                    <th>Retención</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Honorarios Profesionales</td>
                                    <td class="text-right">12</td>
                                    <td class="text-right">S/ 45,678.90</td>
                                    <td class="text-right">S/ 4,567.89</td>
                                </tr>
                                <tr>
                                    <td>Servicios de Consultoría</td>
                                    <td class="text-right">8</td>
                                    <td class="text-right">S/ 34,567.12</td>
                                    <td class="text-right">S/ 3,456.71</td>
                                </tr>
                                <tr>
                                    <td>Servicios Técnicos</td>
                                    <td class="text-right">15</td>
                                    <td class="text-right">S/ 56,789.34</td>
                                    <td class="text-right">S/ 5,678.93</td>
                                </tr>
                                <tr>
                                    <td>Servicios Profesionales</td>
                                    <td class="text-right">10</td>
                                    <td class="text-right">S/ 19,754.09</td>
                                    <td class="text-right">S/ 1,975.41</td>
                                </tr>
                                <tr class="table-info">
                                    <td><strong>TOTAL</strong></td>
                                    <td class="text-right"><strong>45</strong></td>
                                    <td class="text-right"><strong>S/ 156,789.45</strong></td>
                                    <td class="text-right"><strong>S/ 15,678.94</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">Resumen por Tipo de Régimen</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Régimen</th>
                                    <th>Trabajadores</th>
                                    <th>Monto</th>
                                    <th>Retención</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Regimen General</td>
                                    <td class="text-right">28</td>
                                    <td class="text-right">S/ 98,567.23</td>
                                    <td class="text-right">S/ 9,856.72</td>
                                </tr>
                                <tr>
                                    <td>Régimen Especial</td>
                                    <td class="text-right">12</td>
                                    <td class="text-right">S/ 34,567.89</td>
                                    <td class="text-right">S/ 3,456.79</td>
                                </tr>
                                <tr>
                                    <td>Régimen MYPE Tributario</td>
                                    <td class="text-right">5</td>
                                    <td class="text-right">S/ 23,654.33</td>
                                    <td class="text-right">S/ 2,365.43</td>
                                </tr>
                                <tr class="table-info">
                                    <td><strong>TOTAL</strong></td>
                                    <td class="text-right"><strong>45</strong></td>
                                    <td class="text-right"><strong>S/ 156,789.45</strong></td>
                                    <td class="text-right"><strong>S/ 15,678.94</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tabla Detallada de Trabajadores -->
            <h6 class="text-primary">Detalle de Trabajadores</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="plameTable" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>N°</th>
                            <th>Doc. Identidad</th>
                            <th>Apellidos y Nombres</th>
                            <th>Tipo Doc.</th>
                            <th>Régimen</th>
                            <th>Total Ingresos</th>
                            <th>Días Laborados</th>
                            <th>Horas Extra</th>
                            <th>Retención</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">1</td>
                            <td><strong>12345678</strong></td>
                            <td>GARCIA LOPEZ, MARIA CARMEN</td>
                            <td>DNI</td>
                            <td>Reg. General</td>
                            <td class="text-right">S/ 8,500.00</td>
                            <td class="text-center">30</td>
                            <td class="text-right">8</td>
                            <td class="text-right">S/ 850.00</td>
                            <td><span class="badge badge-success">Procesado</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="verTrabajador('12345678')" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">2</td>
                            <td><strong>87654321</strong></td>
                            <td>MARTINEZ SILVA, CARLOS ANDRES</td>
                            <td>DNI</td>
                            <td>Reg. General</td>
                            <td class="text-right">S/ 7,200.00</td>
                            <td class="text-center">30</td>
                            <td class="text-right">12</td>
                            <td class="text-right">S/ 720.00</td>
                            <td><span class="badge badge-success">Procesado</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="verTrabajador('87654321')" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">3</td>
                            <td><strong>20123456789</strong></td>
                            <td>CONSULTORA MEDICA S.A.C.</td>
                            <td>RUC</td>
                            <td>Régimen Especial</td>
                            <td class="text-right">S/ 12,000.00</td>
                            <td class="text-center">-</td>
                            <td class="text-right">-</td>
                            <td class="text-right">S/ 1,200.00</td>
                            <td><span class="badge badge-success">Procesado</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="verTrabajador('20123456789')" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">4</td>
                            <td><strong>11223344</strong></td>
                            <td>RODRIGUEZ PEREZ, ANA LUCIA</td>
                            <td>DNI</td>
                            <td>Régimen Especial</td>
                            <td class="text-right">S/ 6,500.00</td>
                            <td class="text-center">30</td>
                            <td class="text-right">4</td>
                            <td class="text-right">S/ 650.00</td>
                            <td><span class="badge badge-success">Procesado</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="verTrabajador('11223344')" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">5</td>
                            <td><strong>55667788</strong></td>
                            <td>FERNANDEZ GUTIERREZ, LUIS ALBERTO</td>
                            <td>DNI</td>
                            <td>Régimen General</td>
                            <td class="text-right">S/ 9,800.00</td>
                            <td class="text-center">30</td>
                            <td class="text-right">20</td>
                            <td class="text-right">S/ 980.00</td>
                            <td><span class="badge badge-warning">Pendiente</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="verTrabajador('55667788')" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-3">
                <nav aria-label="Detalle PLAME">
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

    <!-- Panel de Validación y Envío -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Validación y Estado de Envío</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h6>Estado del Reporte PLAME</h6>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 98%"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="text-success">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                                <h6>45 Validados</h6>
                                <small class="text-muted">de 45 trabajadores</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="text-warning">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <h6>1 Pendiente</h6>
                                <small class="text-muted">Por validar</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="text-success">
                                    <i class="fas fa-cloud-upload-alt fa-2x"></i>
                                </div>
                                <h6>Enviado</h6>
                                <small class="text-muted">A SUNAT</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6>Acciones PLAME</h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="generarPLAME()">
                            <i class="fas fa-file-excel"></i> Generar PLAME
                        </button>
                        <button class="btn btn-primary" onclick="enviarPLAME()">
                            <i class="fas fa-cloud-upload-alt"></i> Enviar a SUNAT
                        </button>
                        <button class="btn btn-info" onclick="consultarEstadoPLAME()">
                            <i class="fas fa-search"></i> Consultar Estado
                        </button>
                        <button class="btn btn-warning" onclick="generarPDT()">
                            <i class="fas fa-file-code"></i> Generar PDT
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Envíos PLAME -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Historial de Envíos PLAME</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Período</th>
                            <th>Fecha Envío</th>
                            <th>Trabajadores</th>
                            <th>Monto Retención</th>
                            <th>Estado SUNAT</th>
                            <th>Ticket</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Enero 2024</td>
                            <td>25/01/2024 16:30</td>
                            <td class="text-right">45</td>
                            <td class="text-right">S/ 15,678.95</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>PL202401000123</td>
                        </tr>
                        <tr>
                            <td>Diciembre 2023</td>
                            <td>15/12/2023 14:20</td>
                            <td class="text-right">43</td>
                            <td class="text-right">S/ 14,567.89</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>PL202312000122</td>
                        </tr>
                        <tr>
                            <td>Noviembre 2023</td>
                            <td>15/11/2023 11:45</td>
                            <td class="text-right">41</td>
                            <td class="text-right">S/ 13,456.78</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>PL202311000121</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function validarPLAME() {
    Swal.fire({
        title: 'Validando datos PLAME...',
        text: 'Verificando consistencia de información laboral',
        timer: 2500,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Validación Exitosa!',
            html: `
                <div class="text-left">
                    <p><i class="fas fa-check text-success"></i> 45 trabajadores validados</p>
                    <p><i class="fas fa-check text-success"></i> Montos de retención verificados</p>
                    <p><i class="fas fa-check text-success"></i> Documentos de identidad correctos</p>
                    <p><i class="fas fa-check text-success"></i> Listos para generar PLAME</p>
                </div>
            `,
            icon: 'success'
        });
    });
}

function vistaPreviaPLAME() {
    Swal.fire({
        title: 'Generando Vista Previa...',
        text: 'Creando vista previa del reporte PLAME',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Vista Previa PLAME',
            html: `
                <div class="text-left">
                    <h6>Resumen del Reporte:</h6>
                    <ul>
                        <li>Trabajadores: 45</li>
                        <li>Remuneración total: S/ 156,789.45</li>
                        <li>Retención aplicada: S/ 15,678.95</li>
                        <li>Régimen General: 28 trabajadores</li>
                        <li>Régimen Especial: 12 trabajadores</li>
                        <li>MYPE: 5 trabajadores</li>
                    </ul>
                </div>
            `,
            icon: 'info'
        });
    });
}

function generarPLAME() {
    const periodo = document.getElementById('periodo').value;
    
    Swal.fire({
        title: 'Generando PLAME...',
        text: `Creando reporte PLAME para ${periodo}`,
        timer: 4000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡PLAME Generado!',
            text: 'El reporte PLAME ha sido generado exitosamente.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Descargar',
            cancelButtonText: 'Cerrar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Descargando...', 'El archivo PLAME se está descargando.', 'info');
            }
        });
    });
}

function enviarPLAME() {
    const periodo = document.getElementById('periodo').value;
    
    Swal.fire({
        title: '¿Enviar PLAME a SUNAT?',
        text: `¿Desea enviar el reporte PLAME de ${periodo} a SUNAT?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Enviando a SUNAT...',
                text: 'Subiendo reporte PLAME, esto puede tomar varios minutos',
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                Swal.fire({
                    title: '¡Enviado Exitosamente!',
                    text: 'El reporte PLAME ha sido enviado a SUNAT correctamente.',
                    icon: 'success'
                });
            });
        }
    });
}

function consultarEstadoPLAME() {
    Swal.fire({
        title: 'Consultando Estado...',
        text: 'Verificando estado del envío en SUNAT',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Estado: Aceptado',
            html: `
                <div class="text-left">
                    <p><strong>Ticket:</strong> PL202401000123</p>
                    <p><strong>Estado:</strong> Aceptado</p>
                    <p><strong>Fecha:</strong> 25/01/2024 16:30</p>
                    <p><strong>Observaciones:</strong> Ninguna</p>
                </div>
            `,
            icon: 'success'
        });
    });
}

function generarPDT() {
    Swal.fire({
        title: 'Generando PDT...',
        text: 'Creando archivo PDT para PLAME',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡PDT Generado!', 'El archivo PDT ha sido generado exitosamente.', 'success');
    });
}

function verTrabajador(documento) {
    Swal.fire({
        title: 'Detalle del Trabajador',
        html: `
            <div class="text-left">
                <p><strong>Documento:</strong> ${documento}</p>
                <p><strong>Información completa del trabajador</strong></p>
                <p>Remuneraciones, descuentos, retenciones aplicadas...</p>
            </div>
        `,
        icon: 'info',
        width: '500px'
    });
}

function exportarExcelPLAME() {
    Swal.fire({
        title: 'Exportando PLAME...',
        text: 'Generando archivo Excel del reporte PLAME',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Exportado!', 'El archivo Excel ha sido generado.', 'success');
    });
}

function exportarPDFPLAME() {
    Swal.fire({
        title: 'Exportando PLAME...',
        text: 'Generando PDF del reporte PLAME',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Exportado!', 'El archivo PDF ha sido generado.', 'success');
    });
}

function validacionCompletaPLAME() {
    Swal.fire({
        title: 'Validación Completa...',
        text: 'Realizando validación exhaustiva del reporte PLAME',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Validación Completa!',
            html: `
                <div class="text-left">
                    <p><i class="fas fa-check text-success"></i> 45 trabajadores validados</p>
                    <p><i class="fas fa-check text-success"></i> Montos de retención verificados</p>
                    <p><i class="fas fa-check text-success"></i> Régimen tributario correcto</p>
                    <p><i class="fas fa-check text-success"></i> Formatos SUNAT validados</p>
                    <p><i class="fas fa-check text-success"></i> Listo para envío</p>
                </div>
            `,
            icon: 'success'
        });
    });
}

// Inicializar DataTable cuando sea necesario
function initializeDataTable() {
    $('#plameTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        columnDefs: [
            { targets: [0, 3, 4, 7, 8], className: 'text-center' },
            { targets: [5, 6], className: 'text-right' }
        ]
    });
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    initializeDataTable();
});
</script>
@endsection