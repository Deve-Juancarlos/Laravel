@extends('layouts.app')

@section('title', 'Recordatorios de Cobranza')

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
                            <li class="breadcrumb-item"><a href="{{ route('ventas.cuentas-cobrar.index') }}">Cuentas por Cobrar</a></li>
                            <li class="breadcrumb-item active">Recordatorios</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-bell text-warning"></i>
                        Recordatorios de Cobranza
                    </h1>
                    <p class="text-muted mb-0">Gestión de recordatorios automáticos y manuales de cobranza</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-success" onclick="configurarRecordatorios()">
                            <i class="fas fa-cog"></i> Configurar
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="programarRecordatorio()">
                            <i class="fas fa-calendar-plus"></i> Programar
                        </button>
                        <a href="{{ route('ventas.cuentas-cobrar.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Recordatorios -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-2">Enviados Hoy</p>
                            <h4 class="text-success mb-0">23</h4>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> +5 vs ayer
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-paper-plane text-success fs-4"></i>
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
                            <p class="text-muted mb-2">Programados</p>
                            <h4 class="text-info mb-0">47</h4>
                            <small class="text-info">
                                <i class="fas fa-clock"></i> Próximas 24h
                            </small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-calendar text-info fs-4"></i>
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
                            <p class="text-muted mb-2">Tasa de Apertura</p>
                            <h4 class="text-warning mb-0">68%</h4>
                            <small class="text-warning">
                                <i class="fas fa-arrow-up"></i> +3% esta semana
                            </small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-envelope-open text-warning fs-4"></i>
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
                            <p class="text-muted mb-2">Efectividad</p>
                            <h4 class="text-primary mb-0">45%</h4>
                            <small class="text-primary">
                                <i class="fas fa-arrow-up"></i> Recordatorios convertidos
                            </small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-percentage text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuración de Recordatorios Automáticos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary bg-opacity-10 border-0">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-cogs me-2"></i>
                        Configuración de Recordatorios Automáticos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-calendar-day text-warning fs-1 mb-3"></i>
                                <h6 class="text-muted mb-2">Recordatorio a 7 días</h6>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="recordatorio7dias" checked>
                                    <label class="form-check-label" for="recordatorio7dias">
                                        <span class="badge bg-warning">Activo</span>
                                    </label>
                                </div>
                                <p class="small text-muted mb-0">Envío 7 días antes del vencimiento</p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-calendar-week text-info fs-1 mb-3"></i>
                                <h6 class="text-muted mb-2">Recordatorio a 3 días</h6>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="recordatorio3dias" checked>
                                    <label class="form-check-label" for="recordatorio3dias">
                                        <span class="badge bg-info">Activo</span>
                                    </label>
                                </div>
                                <p class="small text-muted mb-0">Envío 3 días antes del vencimiento</p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-exclamation-triangle text-danger fs-1 mb-3"></i>
                                <h6 class="text-muted mb-2">Recordatorio Vencido</h6>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="recordatorioVencido" checked>
                                    <label class="form-check-label" for="recordatorioVencido">
                                        <span class="badge bg-danger">Activo</span>
                                    </label>
                                </div>
                                <p class="small text-muted mb-0">Envío diario después del vencimiento</p>
                            </div>
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
                    <form class="row g-3" id="filtrosRecordatorios">
                        <div class="col-md-3">
                            <label class="form-label">Buscar</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="busqueda" placeholder="Cliente, factura, email...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="estadoRecordatorio">
                                <option value="">Todos</option>
                                <option value="enviado">Enviado</option>
                                <option value="programado">Programado</option>
                                <option value="fallido">Fallido</option>
                                <option value="abierto">Abierto</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" id="tipoRecordatorio">
                                <option value="">Todos</option>
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="llamada">Llamada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" id="fechaDesde" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control" id="fechaHasta" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary" onclick="aplicarFiltrosRecordatorios()">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Recordatorios -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary"></i>
                        Historial de Recordatorios
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarRecordatorios()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="envioMasivo()">
                            <i class="fas fa-bolt"></i> Envío Masivo
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="actualizarLista()">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaRecordatorios">
                            <thead>
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Cliente</th>
                                    <th>Factura</th>
                                    <th>Tipo</th>
                                    <th>Asunto/Mensaje</th>
                                    <th>Estado</th>
                                    <th>Respuesta</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="d-block">25/10/2025</span>
                                        <small class="text-muted">10:30 AM</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-primary bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-building text-primary"></i>
                                            </div>
                                            <div>
                                                <strong>Empresa ABC S.A.C.</strong>
                                                <br>
                                                <small class="text-muted">RUC: 20123456789</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>F-001234</strong>
                                        <br>
                                        <small class="text-muted">S/ 8,500.00</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">Email Urgente</span>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;">
                                            <strong>Recordatorio Final - Factura Vencida</strong>
                                            <br>
                                            <small class="text-muted">Su factura F-001234 por S/ 8,500.00...</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Enviado</span>
                                        <br>
                                        <small class="text-success">
                                            <i class="fas fa-check-circle"></i> Entregado
                                        </small>
                                    </td>
                                    <td>
                                        <span class="text-muted">-</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="verDetalleRecordatorio('R-001245')" title="Ver Detalle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="reenviarRecordatorio('R-001245')" title="Reenviar">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="registrarRespuesta('R-001245')" title="Registrar Respuesta">
                                                <i class="fas fa-reply"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="d-block">25/10/2025</span>
                                        <small class="text-muted">09:15 AM</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-success bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-building text-success"></i>
                                            </div>
                                            <div>
                                                <strong>Clínica Salud Total</strong>
                                                <br>
                                                <small class="text-muted">RUC: 20987654321</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>F-001233</strong>
                                        <br>
                                        <small class="text-muted">S/ 3,250.00</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Email</span>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;">
                                            <strong>Recordatorio de Vencimiento</strong>
                                            <br>
                                            <small class="text-muted">Su factura F-001233 vence en 3 días...</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Enviado</span>
                                        <br>
                                        <small class="text-success">
                                            <i class="fas fa-envelope-open"></i> Abierto
                                        </small>
                                    </td>
                                    <td>
                                        <span class="text-info">
                                            <i class="fas fa-reply"></i> Respuesta
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="verDetalleRecordatorio('R-001244')" title="Ver Detalle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="llamarClienteRecordatorio('R-001244')" title="Llamar">
                                                <i class="fas fa-phone"></i>
                                            </button>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="reenviarRecordatorio('R-001244')"><i class="fas fa-redo me-2"></i>Reenviar</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="programarLlamada('R-001244')"><i class="fas fa-phone me-2"></i>Programar Llamada</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="enviarWhatsAppRecordatorio('R-001244')"><i class="fab fa-whatsapp me-2"></i>WhatsApp</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="cancelarRecordatorio('R-001244')"><i class="fas fa-times me-2"></i>Cancelar</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-warning">
                                    <td>
                                        <span class="d-block">25/10/2025</span>
                                        <small class="text-muted">08:00 AM</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-warning bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user text-warning"></i>
                                            </div>
                                            <div>
                                                <strong>Juan Pérez García</strong>
                                                <br>
                                                <small class="text-muted">DNI: 12345678</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>F-001245</strong>
                                        <br>
                                        <small class="text-muted">S/ 156.80</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">SMS</span>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;">
                                            <strong>SMS Recordatorio</strong>
                                            <br>
                                            <small class="text-muted">Estimado cliente, le recordamos...</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Programado</span>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> Pendiente
                                        </small>
                                    </td>
                                    <td>
                                        <span class="text-muted">-</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="enviarAhora('R-001243')" title="Enviar Ahora">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="cancelarRecordatorio('R-001243')" title="Cancelar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarRecordatorio('R-001243')" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <span class="text-muted">Mostrando 1 a 3 de 47 recordatorios</span>
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
</div>
@endsection

@section('scripts')
<script>
function aplicarFiltrosRecordatorios() {
    const filtros = {
        busqueda: document.getElementById('busqueda').value,
        estado: document.getElementById('estadoRecordatorio').value,
        tipo: document.getElementById('tipoRecordatorio').value,
        fechaDesde: document.getElementById('fechaDesde').value,
        fechaHasta: document.getElementById('fechaHasta').value
    };

    console.log('Aplicando filtros de recordatorios:', filtros);
    
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

function actualizarLista() {
    Swal.fire({
        title: 'Actualizando lista...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });

    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Lista actualizada',
            showConfirmButton: false,
            timer: 1500
        });
    }, 1500);
}

function configurarRecordatorios() {
    Swal.fire({
        title: 'Configuración de Recordatorios',
        html: `
            <div class="text-left">
                <div class="mb-3">
                    <label class="form-label">Recordatorio 7 días antes del vencimiento:</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="config7dias" checked>
                        <label class="form-check-label" for="config7dias">Activo</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Recordatorio 3 días antes del vencimiento:</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="config3dias" checked>
                        <label class="form-check-label" for="config3dias">Activo</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Recordatorio diario después del vencimiento:</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="configVencido" checked>
                        <label class="form-check-label" for="configVencido">Activo</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Horario de envío:</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="time" class="form-control" id="horaInicio" value="09:00">
                        </div>
                        <div class="col-6">
                            <input type="time" class="form-control" id="horaFin" value="18:00">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Plantilla de Email por Defecto:</label>
                    <select class="form-select" id="plantillaDefault">
                        <option value="recordatorio_7">Recordatorio 7 días</option>
                        <option value="recordatorio_3">Recordatorio 3 días</option>
                        <option value="vencido">Factura vencida</option>
                        <option value="urgente">Recordatorio urgente</option>
                    </select>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar Configuración',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Configuración guardada',
                text: 'La configuración de recordatorios se ha actualizado'
            });
        }
    });
}

function programarRecordatorio() {
    Swal.fire({
        title: 'Programar Recordatorio Manual',
        html: `
            <div class="text-left">
                <div class="mb-3">
                    <label class="form-label">Cliente:</label>
                    <select class="form-select" id="clienteRecordatorio">
                        <option value="">Seleccionar cliente...</option>
                        <option value="empresa_abc">Empresa ABC S.A.C.</option>
                        <option value="clinica_salud">Clínica Salud Total</option>
                        <option value="farmacia_la_salud">Farmacia La Salud</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Factura:</label>
                    <select class="form-select" id="facturaRecordatorio">
                        <option value="">Seleccionar factura...</option>
                        <option value="F-001234">F-001234 - S/ 8,500.00</option>
                        <option value="F-001233">F-001233 - S/ 3,250.00</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de Recordatorio:</label>
                    <select class="form-select" id="tipoRecordatorioProgramar">
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="llamada">Llamada</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha y Hora:</label>
                    <input type="datetime-local" class="form-control" id="fechaHoraRecordatorio" min="${new Date().toISOString().slice(0,16)}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Plantilla:</label>
                    <select class="form-select" id="plantillaRecordatorio">
                        <option value="recordatorio_general">Recordatorio General</option>
                        <option value="urgente">Recordatorio Urgente</option>
                        <option value="personalizado">Mensaje Personalizado</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mensaje Personalizado:</label>
                    <textarea class="form-control" id="mensajePersonalizado" rows="3" placeholder="Escribir mensaje personalizado..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Programar Recordatorio',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Recordatorio programado',
                text: 'El recordatorio se ha programado exitosamente'
            });
        }
    });
}

function verDetalleRecordatorio(idRecordatorio) {
    window.open(`/ventas/cuentas-cobrar/recordatorio-detalle/${idRecordatorio}`, '_blank');
}

function reenviarRecordatorio(idRecordatorio) {
    Swal.fire({
        title: 'Reenviar Recordatorio',
        text: `¿Reenviar el recordatorio ${idRecordatorio}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Reenviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Recordatorio reenviado',
                text: `El recordatorio ${idRecordatorio} ha sido reenviado exitosamente`
            });
        }
    });
}

function registrarRespuesta(idRecordatorio) {
    Swal.fire({
        title: 'Registrar Respuesta del Cliente',
        html: `
            <div class="text-left">
                <div class="alert alert-info">
                    <strong>Recordatorio:</strong> ${idRecordatorio}
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de Respuesta:</label>
                    <select class="form-select" id="tipoRespuesta">
                        <option value="">Seleccionar...</option>
                        <option value="compromete_pago">Compromete pago</option>
                        <option value="solicita_prorroga">Solicita prórroga</option>
                        <option value="niega_deuda">Niega deuda</option>
                        <option value="no_disponible">No disponible</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Detalles de la Respuesta:</label>
                    <textarea class="form-control" id="detallesRespuesta" rows="4" placeholder="Detalles de la respuesta del cliente..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha Compromiso (si aplica):</label>
                    <input type="date" class="form-control" id="fechaCompromisoRespuesta" min="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Monto Comprometido (si aplica):</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="number" class="form-control" id="montoComprometido" placeholder="0.00" min="0" step="0.01">
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Registrar Respuesta',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754',
        preConfirm: () => {
            const tipo = document.getElementById('tipoRespuesta').value;
            if (!tipo) {
                Swal.showValidationMessage('Selecciona el tipo de respuesta');
                return false;
            }
            return {
                tipo: tipo,
                detalles: document.getElementById('detallesRespuesta').value,
                fechaCompromiso: document.getElementById('fechaCompromisoRespuesta').value,
                montoComprometido: document.getElementById('montoComprometido').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Respuesta registrada',
                text: `La respuesta del cliente para ${idRecordatorio} ha sido registrada`
            });
        }
    });
}

function llamarClienteRecordatorio(idRecordatorio) {
    Swal.fire({
        title: 'Llamar Cliente',
        text: `¿Iniciar llamada para el recordatorio ${idRecordatorio}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Llamar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            // Simular llamada
            window.open('tel:+51987654321', '_self');
            
            // Registrar en historial
            Swal.fire({
                icon: 'info',
                title: 'Llamada iniciada',
                text: 'Registrando llamada en el sistema...',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

function programarLlamada(idRecordatorio) {
    Swal.fire({
        title: 'Programar Llamada',
        html: `
            <div class="text-left">
                <div class="alert alert-info">
                    <strong>Recordatorio:</strong> ${idRecordatorio}
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha y Hora:</label>
                    <input type="datetime-local" class="form-control" id="fechaHoraLlamada" min="${new Date().toISOString().slice(0,16)}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Asunto de la Llamada:</label>
                    <select class="form-select" id="asuntoLlamada">
                        <option value="recordatorio_pago">Recordatorio de Pago</option>
                        <option value="seguimiento_compromiso">Seguimiento de Compromiso</option>
                        <option value="negacion_deuda">Clarificar Negación de Deuda</option>
                        <option value="negociacion">Negociación de Pago</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notas:</label>
                    <textarea class="form-control" id="notasLlamada" rows="3" placeholder="Notas para la llamada..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Programar Llamada',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#fd7e14'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Llamada programada',
                text: `Llamada programada para ${idRecordatorio}`
            });
        }
    });
}

function enviarWhatsAppRecordatorio(idRecordatorio) {
    const telefono = '+51987654321'; // Simulado
    const mensaje = 'Recordatorio: Su factura está próxima a vencer. ¡Gracias por su preferencia! Farmacia SIFANO';
    
    Swal.fire({
        title: 'Enviar WhatsApp',
        html: `
            <div class="text-left">
                <div class="alert alert-success">
                    <i class="fab fa-whatsapp me-2"></i>
                    <strong>Recordatorio:</strong> ${idRecordatorio}
                    <br>
                    <strong>Teléfono:</strong> ${telefono}
                </div>
                <div class="mb-3">
                    <label class="form-label">Mensaje:</label>
                    <textarea class="form-control" id="mensajeWhatsAppRecordatorio" rows="4">${mensaje}</textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Enviar WhatsApp',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#25d366',
        preConfirm: () => {
            const mensaje = document.getElementById('mensajeWhatsAppRecordatorio').value;
            return { mensaje: mensaje, telefono: telefono };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(`https://wa.me/${telefono.replace('+', '')}?text=${encodeURIComponent(result.value.mensaje)}`, '_blank');
        }
    });
}

function enviarAhora(idRecordatorio) {
    Swal.fire({
        title: 'Enviar Recordatorio Ahora',
        text: `¿Enviar inmediatamente el recordatorio ${idRecordatorio}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Enviar Ahora',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({ sent: true });
                }, 2000);
            });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value.sent) {
            Swal.fire({
                icon: 'success',
                title: 'Recordatorio enviado',
                text: `El recordatorio ${idRecordatorio} ha sido enviado exitosamente`
            });
        }
    });
}

function cancelarRecordatorio(idRecordatorio) {
    Swal.fire({
        title: 'Cancelar Recordatorio',
        text: `¿Estás seguro de cancelar el recordatorio ${idRecordatorio}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No, mantener',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Recordatorio cancelado',
                text: `El recordatorio ${idRecordatorio} ha sido cancelado`
            });
        }
    });
}

function editarRecordatorio(idRecordatorio) {
    window.open(`/ventas/cuentas-cobrar/editar-recordatorio/${idRecordatorio}`, '_blank');
}

function envioMasivo() {
    Swal.fire({
        title: 'Envío Masivo de Recordatorios',
        html: `
            <div class="text-left">
                <div class="mb-3">
                    <label class="form-label">Criterio de Selección:</label>
                    <select class="form-select" id="criterioMasivo">
                        <option value="vencidas_hoy">Facturas vencidas hoy</option>
                        <option value="vencidas_semana">Facturas vencidas esta semana</option>
                        <option value="vencer_semana">Facturas que vencen esta semana</option>
                        <option value="personalizado">Criterio personalizado</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de Envío:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="emailMasivo" checked>
                        <label class="form-check-label" for="emailMasivo">Email</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="smsMasivo">
                        <label class="form-check-label" for="smsMasivo">SMS</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="whatsappMasivo">
                        <label class="form-check-label" for="whatsappMasivo">WhatsApp</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Plantilla:</label>
                    <select class="form-select" id="plantillaMasivo">
                        <option value="recordatorio_general">Recordatorio General</option>
                        <option value="vencido">Factura Vencida</option>
                        <option value="urgente">Recordatorio Urgente</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha de Envío:</label>
                    <select class="form-select" id="fechaEnvioMasivo">
                        <option value="ahora">Ahora</option>
                        <option value="programada">Programar fecha y hora</option>
                    </select>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Ejecutar Envío Masivo',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        preConfirm: () => {
            const criterio = document.getElementById('criterioMasivo').value;
            const tipos = [];
            if (document.getElementById('emailMasivo').checked) tipos.push('email');
            if (document.getElementById('smsMasivo').checked) tipos.push('sms');
            if (document.getElementById('whatsappMasivo').checked) tipos.push('whatsapp');
            
            if (tipos.length === 0) {
                Swal.showValidationMessage('Selecciona al menos un tipo de envío');
                return false;
            }
            
            return { criterio: criterio, tipos: tipos };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Ejecutando envío masivo...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
            
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Envío masivo completado',
                    text: `${result.value.tipos.length} tipos de recordatorio enviados a múltiples clientes`
                });
            }, 3000);
        }
    });
}

function exportarRecordatorios() {
    const opciones = ['Excel', 'PDF', 'CSV'];
    
    Swal.fire({
        title: 'Exportar Recordatorios',
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

// Event listeners para switches de configuración
document.addEventListener('DOMContentLoaded', function() {
    const switches = document.querySelectorAll('.form-check-input[type="checkbox"]');
    switches.forEach(sw => {
        sw.addEventListener('change', function() {
            console.log(`Switch ${this.id} changed to: ${this.checked}`);
        });
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
    margin-bottom: 1rem;
}

.avatar {
    font-size: 14px;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.form-switch .form-check-input {
    width: 2em;
    height: 1em;
}

.pagination .page-link {
    color: #0d6efd;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.fs-1 {
    font-size: 2.5rem !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>
@endsection