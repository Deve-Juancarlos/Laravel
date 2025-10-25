@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calculator text-primary"></i> Reporte IGV Mensual SUNAT
        </h1>
        <div>
            <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Reportes
            </a>
            <button class="btn btn-outline-success" onclick="generarArchivoSUNAT()">
                <i class="fas fa-download"></i> Generar Archivo
            </button>
            <button class="btn btn-outline-primary" onclick="enviarSUNAT()">
                <i class="fas fa-paper-plane"></i> Enviar a SUNAT
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Configuración del Reporte</h6>
        </div>
        <div class="card-body">
            <form id="reporteForm" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Año</label>
                            <select class="form-control" name="año" id="año">
                                <option value="2024" {{ request('año', date('Y')) == '2024' ? 'selected' : '' }}>2024</option>
                                <option value="2023" {{ request('año', date('Y')) == '2023' ? 'selected' : '' }}>2023</option>
                                <option value="2022" {{ request('año', date('Y')) == '2022' ? 'selected' : '' }}>2022</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Mes</label>
                            <select class="form-control" name="mes" id="mes">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('mes', date('n')) == $i ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipo de Operación</label>
                            <select class="form-control" name="operacion">
                                <option value="todas" {{ request('operacion', 'todas') == 'todas' ? 'selected' : '' }}>Todas las Operaciones</option>
                                <option value="ventas" {{ request('operacion') == 'ventas' ? 'selected' : '' }}>Solo Ventas</option>
                                <option value="compras" {{ request('operacion') == 'compras' ? 'selected' : '' }}>Solo Compras</option>
                                <option value="exportacion" {{ request('operacion') == 'exportacion' ? 'selected' : '' }}>Solo Exportación</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Estado SUNAT</label>
                            <select class="form-control" name="estado_sunat">
                                <option value="">Todos</option>
                                <option value="enviado" {{ request('estado_sunat') == 'enviado' ? 'selected' : '' }}>Enviado</option>
                                <option value="pendiente" {{ request('estado_sunat') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="observado" {{ request('estado_sunat') == 'observado' ? 'selected' : '' }}>Observado</option>
                                <option value="aceptado" {{ request('estado_sunat') == 'aceptado' ? 'selected' : '' }}>Aceptado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calculator"></i> Generar Reporte
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="validarDatos()">
                            <i class="fas fa-check-circle"></i> Validar Datos
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen IGV -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                IGV Ventas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">S/ 89,456.78</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +12.5% vs mes anterior
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                                IGV Compras
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">S/ 34,789.12</div>
                            <div class="text-xs text-info">
                                Crédito fiscal
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
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
                                IGV por Pagar
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">S/ 54,667.66</div>
                            <div class="text-xs text-success">
                                Ventas - Compras
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
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
                                Documentos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">1,247</div>
                            <div class="text-xs text-info">
                                Procesados
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle del Reporte -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Detalle del Reporte IGV - {{ date('F Y') }}</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Opciones:</div>
                    <a class="dropdown-item" href="#" onclick="exportarExcel()">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportarPDF()">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                    <a class="dropdown-item" href="#" onclick="validacionCompleta()">
                        <i class="fas fa-clipboard-check"></i> Validación Completa
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Resumen por Tipos de Documento -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-primary">IGV por Tipo de Documento - Ventas</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tipo Doc.</th>
                                    <th>Cantidad</th>
                                    <th>Base Imponible</th>
                                    <th>IGV</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Factura</td>
                                    <td class="text-right">487</td>
                                    <td class="text-right">S/ 234,567.89</td>
                                    <td class="text-right">S/ 42,222.22</td>
                                    <td class="text-right">S/ 276,790.11</td>
                                </tr>
                                <tr>
                                    <td>Boleta</td>
                                    <td class="text-right">623</td>
                                    <td class="text-right">S/ 156,789.45</td>
                                    <td class="text-right">S/ 28,222.10</td>
                                    <td class="text-right">S/ 185,011.55</td>
                                </tr>
                                <tr>
                                    <td>Nota Crédito</td>
                                    <td class="text-right">23</td>
                                    <td class="text-right">-S/ 4,567.89</td>
                                    <td class="text-right">-S/ 822.22</td>
                                    <td class="text-right">-S/ 5,390.11</td>
                                </tr>
                                <tr class="table-info">
                                    <td><strong>TOTAL</strong></td>
                                    <td class="text-right"><strong>1,133</strong></td>
                                    <td class="text-right"><strong>S/ 386,789.45</strong></td>
                                    <td class="text-right"><strong>S/ 69,622.10</strong></td>
                                    <td class="text-right"><strong>S/ 456,411.55</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">IGV por Tipo de Documento - Compras</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tipo Doc.</th>
                                    <th>Cantidad</th>
                                    <th>Base Imponible</th>
                                    <th>IGV</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Factura</td>
                                    <td class="text-right">89</td>
                                    <td class="text-right">S/ 145,678.90</td>
                                    <td class="text-right">S/ 26,222.20</td>
                                    <td class="text-right">S/ 171,901.10</td>
                                </tr>
                                <tr>
                                    <td>Recibo por Honor.</td>
                                    <td class="text-right">25</td>
                                    <td class="text-right">S/ 23,456.78</td>
                                    <td class="text-right">S/ 4,222.22</td>
                                    <td class="text-right">S/ 27,679.00</td>
                                </tr>
                                <tr>
                                    <td>Ticket/BNV</td>
                                    <td class="text-right">0</td>
                                    <td class="text-right">S/ 0.00</td>
                                    <td class="text-right">S/ 0.00</td>
                                    <td class="text-right">S/ 0.00</td>
                                </tr>
                                <tr class="table-info">
                                    <td><strong>TOTAL</strong></td>
                                    <td class="text-right"><strong>114</strong></td>
                                    <td class="text-right"><strong>S/ 169,135.68</strong></td>
                                    <td class="text-right"><strong>S/ 30,444.42</strong></td>
                                    <td class="text-right"><strong>S/ 199,580.10</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tabla Detallada -->
            <h6 class="text-primary">Detalle de Comprobantes de Pago</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="igvTable" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha Emisión</th>
                            <th>Tipo Doc.</th>
                            <th>Número</th>
                            <th>RUC/DNI Proveedor</th>
                            <th>Razón Social</th>
                            <th>Base Imponible</th>
                            <th>IGV</th>
                            <th>Total</th>
                            <th>Estado SUNAT</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>25/01/2024</td>
                            <td><span class="badge badge-info">Factura</span></td>
                            <td><strong>F001-0001234</strong></td>
                            <td>20123456789</td>
                            <td>Cliente Ejemplo S.A.</td>
                            <td class="text-right">S/ 8,474.58</td>
                            <td class="text-right">S/ 1,525.42</td>
                            <td class="text-right">S/ 10,000.00</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="verComprobante('F001-0001234')" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>24/01/2024</td>
                            <td><span class="badge badge-info">Factura</span></td>
                            <td><strong>F001-0001233</strong></td>
                            <td>20765432109</td>
                            <td>Farmacia Bienestar</td>
                            <td class="text-right">S/ 5,084.75</td>
                            <td class="text-right">S/ 915.25</td>
                            <td class="text-right">S/ 6,000.00</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="verComprobante('F001-0001233')" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>23/01/2024</td>
                            <td><span class="badge badge-primary">Boleta</span></td>
                            <td><strong>B001-0005678</strong></td>
                            <td>12345678</td>
                            <td>Dr. Juan Pérez</td>
                            <td class="text-right">S/ 2,542.37</td>
                            <td class="text-right">S/ 457.63</td>
                            <td class="text-right">S/ 3,000.00</td>
                            <td><span class="badge badge-warning">Pendiente</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="verComprobante('B001-0005678')" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>22/01/2024</td>
                            <td><span class="badge badge-success">Recibo Hon.</span></td>
                            <td><strong>RH001-000123</strong></td>
                            <td>87654321</td>
                            <td>Dr. Carlos Mendoza</td>
                            <td class="text-right">S/ 1,694.92</td>
                            <td class="text-right">S/ 305.08</td>
                            <td class="text-right">S/ 2,000.00</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="verComprobante('RH001-000123')" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>21/01/2024</td>
                            <td><span class="badge badge-danger">Nota Crédito</span></td>
                            <td><strong>NC001-000045</strong></td>
                            <td>20123456789</td>
                            <td>Cliente Ejemplo S.A.</td>
                            <td class="text-right">-S/ 847.46</td>
                            <td class="text-right">-S/ 152.54</td>
                            <td class="text-right">-S/ 1,000.00</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="verComprobante('NC001-000045')" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Panel de Validación -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Validación y Estado de Envío SUNAT</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h6>Estado del Reporte</h6>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 95%"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="text-success">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                                <h6>1,133 Validados</h6>
                                <small class="text-muted">de 1,247 documentos</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="text-warning">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <h6>114 Pendientes</h6>
                                <small class="text-muted">Por validar</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="text-danger">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                </div>
                                <h6>0 Observados</h6>
                                <small class="text-muted">Con errores</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6>Acciones Disponibles</h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="generarArchivoSUNAT()">
                            <i class="fas fa-download"></i> Generar LE
                        </button>
                        <button class="btn btn-primary" onclick="enviarSUNAT()">
                            <i class="fas fa-paper-plane"></i> Enviar a SUNAT
                        </button>
                        <button class="btn btn-info" onclick="consultarEstado()">
                            <i class="fas fa-search"></i> Consultar Estado
                        </button>
                        <button class="btn btn-warning" onclick="generarFormato8()">
                            <i class="fas fa-file-excel"></i> Formato 8
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Envíos -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Historial de Envíos SUNAT</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Período</th>
                            <th>Fecha Envío</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                            <th>Ticket</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Diciembre 2023</td>
                            <td>15/01/2024</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>Envío exitoso</td>
                            <td>LE202312000123456</td>
                        </tr>
                        <tr>
                            <td>Noviembre 2023</td>
                            <td>15/12/2023</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>Envío exitoso</td>
                            <td>LE202311000123455</td>
                        </tr>
                        <tr>
                            <td>Octubre 2023</td>
                            <td>15/11/2023</td>
                            <td><span class="badge badge-success">Aceptado</span></td>
                            <td>Envío exitoso</td>
                            <td>LE202310000123454</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function limpiarFiltros() {
    document.getElementById('reporteForm').reset();
    window.location.href = window.location.pathname;
}

function validarDatos() {
    Swal.fire({
        title: 'Validando datos...',
        text: 'Verificando consistencia de la información',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Validación Completada!',
            text: 'Los datos están consistentes y listos para el envío a SUNAT.',
            icon: 'success'
        });
    });
}

function generarArchivoSUNAT() {
    const año = document.getElementById('año').value;
    const mes = document.getElementById('mes').value;
    
    Swal.fire({
        title: 'Generando Archivo...',
        text: `Creando LE para ${mes}/${año}`,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Archivo Generado!',
            text: 'El archivo LE ha sido generado y está listo para descarga.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Descargar',
            cancelButtonText: 'Cerrar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Simular descarga
                Swal.fire('Descargando...', 'El archivo se está descargando.', 'info');
            }
        });
    });
}

function enviarSUNAT() {
    const año = document.getElementById('año').value;
    const mes = document.getElementById('mes').value;
    
    Swal.fire({
        title: '¿Enviar a SUNAT?',
        text: `¿Desea enviar el reporte de IGV de ${mes}/${año} a SUNAT?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Enviando a SUNAT...',
                text: 'Procesando envío, esto puede tomar unos minutos',
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                Swal.fire({
                    title: '¡Enviado Exitosamente!',
                    text: 'El reporte ha sido enviado a SUNAT correctamente.',
                    icon: 'success',
                    confirmButtonText: 'Ver Estado'
                }).then(() => {
                    consultarEstado();
                });
            });
        }
    });
}

function consultarEstado() {
    Swal.fire({
        title: 'Consultando Estado...',
        text: 'Verificando estado del envío en SUNAT',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: 'Estado: Aceptado',
            text: 'El reporte ha sido aceptado por SUNAT sin observaciones.',
            icon: 'success'
        });
    });
}

function generarFormato8() {
    const año = document.getElementById('año').value;
    const mes = document.getElementById('mes').value;
    
    Swal.fire({
        title: 'Generando Formato 8...',
        text: `Creando Formato 8.1 para ${mes}/${año}`,
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Generado!', 'El Formato 8 ha sido generado exitosamente.', 'success');
    });
}

function validacionCompleta() {
    Swal.fire({
        title: 'Validación Completa...',
        text: 'Realizando validación exhaustiva de todos los datos',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Validación Completa!',
            html: `
                <div class="text-left">
                    <p><i class="fas fa-check text-success"></i> 1,247 documentos validados</p>
                    <p><i class="fas fa-check text-success"></i> Montos verificados</p>
                    <p><i class="fas fa-check text-success"></i> Formatos correctos</p>
                    <p><i class="fas fa-check text-success"></i> Listo para envío</p>
                </div>
            `,
            icon: 'success'
        });
    });
}

function exportarExcel() {
    Swal.fire({
        title: 'Exportando...',
        text: 'Generando archivo Excel del reporte IGV',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Exportado!', 'El archivo Excel ha sido generado.', 'success');
    });
}

function exportarPDF() {
    Swal.fire({
        title: 'Exportando...',
        text: 'Generando PDF del reporte IGV',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire('¡Exportado!', 'El archivo PDF ha sido generado.', 'success');
    });
}

function verComprobante(numero) {
    window.open(`/comprobantes/${numero}`, '_blank');
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    // Si hay parámetros, ejecutar automáticamente
    const params = new URLSearchParams(window.location.search);
    if (params.has('año') || params.has('mes')) {
        document.getElementById('reporteForm').submit();
    }
});
</script>
@endsection