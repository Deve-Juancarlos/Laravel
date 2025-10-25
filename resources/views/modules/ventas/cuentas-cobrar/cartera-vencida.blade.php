@extends('layouts.app')

@section('title', 'Cartera Vencida')

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
                            <li class="breadcrumb-item active">Cartera Vencida</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        Cartera Vencida
                    </h1>
                    <p class="text-muted mb-0">Gestión especializada de cuentas vencidas y cobranza</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="generarReporteVencida()">
                            <i class="fas fa-file-pdf"></i> Reporte
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="asignarCobrador()">
                            <i class="fas fa-user-tie"></i> Asignar Cobrador
                        </button>
                        <a href="{{ route('ventas.cuentas-cobrar.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta Crítica -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-3 fs-2"></i>
                    <div class="flex-grow-1">
                        <h4 class="alert-heading mb-1">Atención Urgente</h4>
                        <p class="mb-2">
                            Tienes <strong class="fs-5">8 facturas vencidas</strong> por un total de <strong class="fs-4">S/ 12,450.00</strong> 
                            que requieren seguimiento inmediato de cobranza.
                        </p>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <small><strong>Más urgente:</strong> 10 días vencida</small>
                            </div>
                            <div class="col-md-4">
                                <small><strong>Monto más alto:</strong> S/ 8,500.00</small>
                            </div>
                            <div class="col-md-4">
                                <small><strong>Clientes en mora:</strong> 6 empresas</small>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-danger btn-lg" onclick="accionesMasivas()">
                            <i class="fas fa-bolt"></i> Acciones Masivas
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas por Antigüedad -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger bg-opacity-10 border-0">
                    <h5 class="mb-0 text-danger">
                        <i class="fas fa-chart-pie me-2"></i>
                        Distribución por Antigüedad de Vencimiento
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="fas fa-calendar-day text-warning fs-1"></i>
                                </div>
                                <h4 class="text-warning mb-1">S/ 3,250.00</h4>
                                <p class="text-muted mb-2">1-7 días vencidas</p>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: 26%"></div>
                                </div>
                                <small class="text-muted">3 facturas</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="fas fa-calendar-week text-info fs-1"></i>
                                </div>
                                <h4 class="text-info mb-1">S/ 4,850.00</h4>
                                <p class="text-muted mb-2">8-30 días vencidas</p>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: 39%"></div>
                                </div>
                                <small class="text-muted">3 facturas</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="fas fa-calendar-alt text-danger fs-1"></i>
                                </div>
                                <h4 class="text-danger mb-1">S/ 2,350.00</h4>
                                <p class="text-muted mb-2">31-90 días vencidas</p>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-danger" style="width: 19%"></div>
                                </div>
                                <small class="text-muted">1 factura</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="fas fa-calendar-times text-dark fs-1"></i>
                                </div>
                                <h4 class="text-dark mb-1">S/ 2,000.00</h4>
                                <p class="text-muted mb-2">Más de 90 días</p>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-dark" style="width: 16%"></div>
                                </div>
                                <small class="text-muted">1 factura</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Específicos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form class="row g-3" id="filtrosCarteraVencida">
                        <div class="col-md-4">
                            <label class="form-label">Rango de Vencimiento</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="rangoVencimiento" id="todas" value="todas" autocomplete="off" checked>
                                <label class="btn btn-outline-danger" for="todas">Todas</label>

                                <input type="radio" class="btn-check" name="rangoVencimiento" id="menos7" value="menos7" autocomplete="off">
                                <label class="btn btn-outline-warning" for="menos7">1-7 días</label>

                                <input type="radio" class="btn-check" name="rangoVencimiento" id="menos30" value="menos30" autocomplete="off">
                                <label class="btn btn-outline-info" for="menos30">8-30 días</label>

                                <input type="radio" class="btn-check" name="rangoVencimiento" id="mas30" value="mas30" autocomplete="off">
                                <label class="btn btn-outline-danger" for="mas30">+30 días</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Monto Mínimo</label>
                            <div class="input-group">
                                <span class="input-group-text">S/</span>
                                <input type="number" class="form-control" id="montoMinimo" placeholder="0.00" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cliente</label>
                            <select class="form-select" id="cliente">
                                <option value="">Todos los clientes</option>
                                <option value="empresa_abc">Empresa ABC S.A.C.</option>
                                <option value="clinica_salud">Clínica Salud Total</option>
                                <option value="farmacia_la_salud">Farmacia La Salud</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Acciones</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-danger" onclick="aplicarFiltrosVencida()">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Cuentas Vencidas -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-danger"></i>
                        Cuentas Vencidas por Cobrar
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportarCarteraVencida()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="programarLlamadas()">
                            <i class="fas fa-phone"></i> Programar Llamadas
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaCarteraVencida">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" class="form-check-input" id="selectAllVencida">
                                    </th>
                                    <th>Factura</th>
                                    <th>Cliente</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Días Vencido</th>
                                    <th>Monto</th>
                                    <th>Cobrador</th>
                                    <th>Último Contacto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-danger">
                                    <td>
                                        <input type="checkbox" class="form-check-input" value="F-001234">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                            <div>
                                                <strong class="text-danger">F-001234</strong>
                                                <br>
                                                <small class="text-muted">Ticket: 001234</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-danger bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-building text-danger"></i>
                                            </div>
                                            <div>
                                                <strong>Empresa ABC S.A.C.</strong>
                                                <br>
                                                <small class="text-muted">RUC: 20123456789</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="d-block">15/11/2025</span>
                                        <small class="text-muted">Venció hace 10 días</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger fs-6">10 días</span>
                                        <div class="progress mt-1" style="height: 4px;">
                                            <div class="progress-bar bg-danger" style="width: 33%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-danger fs-6">S/ 8,500.00</strong>
                                            <br>
                                            <small class="text-muted">Subtotal: S/ 7,203.39</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/24" class="rounded-circle me-2" alt="Cobrador">
                                            <span class="small">Ana García</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="d-block">22/10/2025</span>
                                        <small class="text-muted">Llamada - Sin respuesta</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-success" onclick="registrarPagoVencida('F-001234')" title="Registrar Pago">
                                                <i class="fas fa-money-bill"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" onclick="llamarCliente('F-001234')" title="Llamar Cliente">
                                                <i class="fas fa-phone"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info" onclick="enviarWhatsApp('F-001234')" title="WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </button>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="enviarCartaCobranza('F-001234')"><i class="fas fa-envelope me-2"></i>Carta de Cobranza</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="visitaPresencial('F-001234')"><i class="fas fa-map-marker-alt me-2"></i>Visita Presencial</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="derivacionCobranza('F-001234')"><i class="fas fa-user-tie me-2"></i>Derivar a Legal</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item" href="#" onclick="negociacionPago('F-001234')"><i class="fas fa-handshake me-2"></i>Negociar Pago</a></li>
                                                    <li><a class="dropdown-item text-success" href="#" onclick="compromisoPago('F-001234')"><i class="fas fa-calendar-check me-2"></i>Compromiso de Pago</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-warning">
                                    <td>
                                        <input type="checkbox" class="form-check-input" value="F-001229">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                            <div>
                                                <strong class="text-warning">F-001229</strong>
                                                <br>
                                                <small class="text-muted">Ticket: 001229</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-warning bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-building text-warning"></i>
                                            </div>
                                            <div>
                                                <strong>Clínica Salud Total</strong>
                                                <br>
                                                <small class="text-muted">RUC: 20987654321</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="d-block">20/11/2025</span>
                                        <small class="text-muted">Venció hace 5 días</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning fs-6">5 días</span>
                                        <div class="progress mt-1" style="height: 4px;">
                                            <div class="progress-bar bg-warning" style="width: 17%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-warning fs-6">S/ 3,250.00</strong>
                                            <br>
                                            <small class="text-muted">Subtotal: S/ 2,754.24</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/24" class="rounded-circle me-2" alt="Cobrador">
                                            <span class="small">Carlos López</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="d-block">23/10/2025</span>
                                        <small class="text-muted">Email - Respondió</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-success" onclick="registrarPagoVencida('F-001229')" title="Registrar Pago">
                                                <i class="fas fa-money-bill"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" onclick="llamarCliente('F-001229')" title="Llamar Cliente">
                                                <i class="fas fa-phone"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info" onclick="enviarWhatsApp('F-001229')" title="WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </button>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="enviarCartaCobranza('F-001229')"><i class="fas fa-envelope me-2"></i>Carta de Cobranza</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="visitaPresencial('F-001229')"><i class="fas fa-map-marker-alt me-2"></i>Visita Presencial</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="reprogramarVencimiento('F-001229')"><i class="fas fa-calendar me-2"></i>Reprogramar</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item" href="#" onclick="compromisoPago('F-001229')"><i class="fas fa-calendar-check me-2"></i>Compromiso de Pago</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Herramientas de Cobranza -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary bg-opacity-10 border-0">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-tools me-2"></i>
                        Herramientas de Cobranza
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <button type="button" class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4" onclick="accionesMasivas()">
                                <i class="fas fa-bolt mb-2 fs-2"></i>
                                <span>Acciones Masivas</span>
                                <small class="text-muted">Procesar múltiples facturas</small>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <button type="button" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4" onclick="programarLlamadas()">
                                <i class="fas fa-phone mb-2 fs-2"></i>
                                <span>Programar Llamadas</span>
                                <small class="text-muted">Agenda de cobranza</small>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <button type="button" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4" onclick="generarCartaCobranza()">
                                <i class="fas fa-file-alt mb-2 fs-2"></i>
                                <span>Cartas de Cobranza</span>
                                <small class="text-muted">Generar documentos</small>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <button type="button" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4" onclick="asignarCobrador()">
                                <i class="fas fa-user-tie mb-2 fs-2"></i>
                                <span>Asignar Cobradores</span>
                                <small class="text-muted">Gestión de territorio</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function aplicarFiltrosVencida() {
    const filtros = {
        rango: document.querySelector('input[name="rangoVencimiento"]:checked').value,
        montoMinimo: document.getElementById('montoMinimo').value,
        cliente: document.getElementById('cliente').value
    };

    console.log('Aplicando filtros de cartera vencida:', filtros);
    
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
            text: `Mostrando ${filtros.rango} vencidas${filtros.cliente ? ` de ${filtros.cliente}` : ''}`,
            showConfirmButton: false,
            timer: 2000
        });
    }, 1000);
}

function registrarPagoVencida(numeroFactura) {
    Swal.fire({
        title: 'Registrar Pago de Factura Vencida',
        html: `
            <div class="text-left">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Factura:</strong> ${numeroFactura}
                </div>
                <div class="mb-3">
                    <label class="form-label">Monto Total Pendiente:</label>
                    <input type="number" class="form-control" value="8500.00" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Monto a Pagar:</label>
                    <input type="number" class="form-control" id="montoPagoVencida" placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">Método de Pago:</label>
                    <select class="form-select" id="metodoPagoVencida">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="cheque">Cheque</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones de Cobranza:</label>
                    <textarea class="form-control" id="observacionesCobranza" rows="3" placeholder="Detalles del pago recibido..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Registrar Pago',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754',
        preConfirm: () => {
            const monto = parseFloat(document.getElementById('montoPagoVencida').value);
            if (!monto || monto <= 0) {
                Swal.showValidationMessage('El monto debe ser mayor a 0');
                return false;
            }
            return {
                monto: monto,
                metodo: document.getElementById('metodoPagoVencida').value,
                observaciones: document.getElementById('observacionesCobranza').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Pago registrado',
                text: `Pago de S/ ${result.value.monto.toFixed(2)} registrado exitosamente para ${numeroFactura}`
            });
        }
    });
}

function llamarCliente(numeroFactura) {
    Swal.fire({
        title: 'Llamada de Cobranza',
        html: `
            <div class="text-left">
                <div class="alert alert-warning">
                    <i class="fas fa-phone me-2"></i>
                    <strong>Factura:</strong> ${numeroFactura}
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de Contacto:</label>
                    <select class="form-select" id="tipoContacto">
                        <option value="llamada_primera">Primera llamada</option>
                        <option value="seguimiento">Llamada de seguimiento</option>
                        <option value="recordatorio">Recordatorio</option>
                        <option value="ultima_oportunidad">Última oportunidad</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Resultado de la Llamada:</label>
                    <select class="form-select" id="resultadoLlamada" onchange="mostrarOpcionesAdicionales()">
                        <option value="">Seleccionar...</option>
                        <option value="no_contesta">No contestan</option>
                        <option value="compromete_pago">Compromete pago</option>
                        <option value="solicita_prorroga">Solicita prórroga</option>
                        <option value="niega_deuda">Niega deuda</option>
                        <option value="promesa_incumplida">Promesa incumplida</option>
                    </select>
                </div>
                <div class="mb-3" id="fechaCompromiso" style="display: none;">
                    <label class="form-label">Fecha de Compromiso:</label>
                    <input type="date" class="form-control" id="fechaPagoCompromiso">
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones:</label>
                    <textarea class="form-control" id="observacionesLlamada" rows="3" placeholder="Detalles de la conversación..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Registrar Llamada',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Llamada registrada',
                text: `Llamada de cobranza registrada para ${numeroFactura}`
            });
        }
    });
}

function mostrarOpcionesAdicionales() {
    const resultado = document.getElementById('resultadoLlamada').value;
    const fechaCompromiso = document.getElementById('fechaCompromiso');
    
    if (resultado === 'compromete_pago' || resultado === 'solicita_prorroga') {
        fechaCompromiso.style.display = 'block';
    } else {
        fechaCompromiso.style.display = 'none';
    }
}

function enviarWhatsApp(numeroFactura) {
    const telefono = '+51987654321'; // Simulado
    const mensaje = `¡Hola! Le recordamos que la factura ${numeroFactura} por S/ 8,500.00 está vencida. ¿Podríamos coordinar el pago? Farmacia SIFANO.`;
    
    Swal.fire({
        title: 'Enviar WhatsApp',
        html: `
            <div class="text-left">
                <div class="alert alert-success">
                    <i class="fab fa-whatsapp me-2"></i>
                    <strong>Factura:</strong> ${numeroFactura}
                    <br>
                    <strong>Teléfono:</strong> ${telefono}
                </div>
                <div class="mb-3">
                    <label class="form-label">Mensaje Predefinido:</label>
                    <select class="form-select" id="plantillaWhatsApp" onchange="cargarPlantillaWhatsApp()">
                        <option value="recordatorio">Recordatorio de pago</option>
                        <option value="ultimo_recordatorio">Último recordatorio</option>
                        <option value="negociacion">Propuesta de negociación</option>
                        <option value="personalizado">Mensaje personalizado</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mensaje:</label>
                    <textarea class="form-control" id="mensajeWhatsApp" rows="4">${mensaje}</textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Enviar WhatsApp',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#25d366',
        preConfirm: () => {
            const mensaje = document.getElementById('mensajeWhatsApp').value;
            return { mensaje: mensaje, telefono: telefono };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Abrir WhatsApp Web
            window.open(`https://wa.me/${telefono.replace('+', '')}?text=${encodeURIComponent(result.value.mensaje)}`, '_blank');
        }
    });
}

function enviarCartaCobranza(numeroFactura) {
    Swal.fire({
        title: 'Generar Carta de Cobranza',
        text: `¿Generar carta de cobranza para la factura ${numeroFactura}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Generar Carta',
        cancelButtonText: 'Cancelar',
        showDenyButton: true,
        denyButtonText: 'Email + Carta',
        denyButtonColor: '#25d366'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(`/ventas/cuentas-cobrar/carta-cobranza/${numeroFactura}`, '_blank');
        } else if (result.isDenied) {
            Swal.fire({
                title: 'Enviando...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Carta y Email enviados',
                    text: 'Carta de cobranza generada y enviada por email'
                });
            }, 2000);
        }
    });
}

function visitaPresencial(numeroFactura) {
    Swal.fire({
        title: 'Programar Visita Presencial',
        html: `
            <div class="text-left">
                <div class="alert alert-warning">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    <strong>Factura:</strong> ${numeroFactura}
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha y Hora de Visita:</label>
                    <input type="datetime-local" class="form-control" id="fechaHoraVisita">
                </div>
                <div class="mb-3">
                    <label class="form-label">Cobrador Asignado:</label>
                    <select class="form-select" id="cobradorVisita">
                        <option value="">Seleccionar cobrador...</option>
                        <option value="ana_garcia">Ana García</option>
                        <option value="carlos_lopez">Carlos López</option>
                        <option value="maria_rodriguez">María Rodríguez</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dirección:</label>
                    <textarea class="form-control" id="direccionVisita" rows="2">Av. Principal 123, Lima, Perú</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Instrucciones Especiales:</label>
                    <textarea class="form-control" id="instruccionesVisita" rows="3" placeholder="Instrucciones para el cobrador..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Programar Visita',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#fd7e14'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Visita programada',
                text: `Visita presencial programada para ${numeroFactura}`
            });
        }
    });
}

function derivacionCobranza(numeroFactura) {
    Swal.fire({
        title: 'Derivar a Cobranza Legal',
        text: `¿Derivar la factura ${numeroFactura} al área legal para cobranza judicial?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Derivar a Legal',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({ derived: true });
                }, 2000);
            });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value.derived) {
            Swal.fire({
                icon: 'success',
                title: 'Derivación exitosa',
                text: `${numeroFactura} derivada al área legal para cobranza judicial`
            });
        }
    });
}

function negociacionPago(numeroFactura) {
    Swal.fire({
        title: 'Negociación de Pago',
        html: `
            <div class="text-left">
                <div class="alert alert-info">
                    <i class="fas fa-handshake me-2"></i>
                    <strong>Factura:</strong> ${numeroFactura} - S/ 8,500.00
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de Negociación:</label>
                    <select class="form-select" id="tipoNegociacion">
                        <option value="prorroga_pago">Prórroga de pago</option>
                        <option value="pago_parcial">Pago parcial</option>
                        <option value="descuento">Descuento por pronto pago</option>
                        <option value="plan_pagos">Plan de pagos</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Propuesta del Cliente:</label>
                    <textarea class="form-control" id="propuestaCliente" rows="3" placeholder="Detalles de la propuesta..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Respuesta de la Empresa:</label>
                    <select class="form-select" id="respuestaEmpresa">
                        <option value="aceptada">Aceptar propuesta</option>
                        <option value="contra_propuesta">Hacer contra-propuesta</option>
                        <option value="rechazada">Rechazar propuesta</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Detalles del Acuerdo:</label>
                    <textarea class="form-control" id="detallesAcuerdo" rows="3" placeholder="Detalles específicos del acuerdo..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Registrar Negociación',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Negociación registrada',
                text: `Negociación de pago registrada para ${numeroFactura}`
            });
        }
    });
}

function compromisoPago(numeroFactura) {
    Swal.fire({
        title: 'Registrar Compromiso de Pago',
        html: `
            <div class="text-left">
                <div class="alert alert-success">
                    <i class="fas fa-calendar-check me-2"></i>
                    <strong>Factura:</strong> ${numeroFactura}
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha de Compromiso:</label>
                    <input type="date" class="form-control" id="fechaCompromisoPago" min="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Monto Comprometido:</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="number" class="form-control" id="montoCompromiso" placeholder="8500.00" value="8500.00">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Forma de Pago Comprometida:</label>
                    <select class="form-select" id="formaPagoCompromiso">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia bancaria</option>
                        <option value="cheque">Cheque</option>
                        <option value="plan_pagos">Plan de pagos</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones:</label>
                    <textarea class="form-control" id="observacionesCompromiso" rows="3" placeholder="Detalles del compromiso..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Registrar Compromiso',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Compromiso registrado',
                text: `Compromiso de pago registrado para ${numeroFactura}`
            });
        }
    });
}

function reprogramarVencimiento(numeroFactura) {
    // Usar la misma función del módulo anterior
    window.location.href = `/ventas/cuentas-cobrar/reprogramar/${numeroFactura}`;
}

function accionesMasivas() {
    const seleccionadas = document.querySelectorAll('input[type="checkbox"]:checked:not(#selectAllVencida)');
    
    if (seleccionadas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay facturas seleccionadas',
            text: 'Selecciona al menos una factura para aplicar acciones masivas'
        });
        return;
    }
    
    Swal.fire({
        title: 'Acciones Masivas',
        html: `
            <div class="text-left">
                <p>${seleccionadas.length} facturas seleccionadas:</p>
                <div class="list-group">
                    ${Array.from(seleccionadas).map(cb => `<div class="list-group-item">${cb.value}</div>`).join('')}
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label">Acción a realizar:</label>
                    <select class="form-select" id="accionMasiva">
                        <option value="">Seleccionar acción...</option>
                        <option value="enviar_recordatorios">Enviar recordatorios</option>
                        <option value="generar_cartas">Generar cartas de cobranza</option>
                        <option value="programar_llamadas">Programar llamadas</option>
                        <option value="asignar_cobrador">Asignar cobrador</option>
                        <option value="derivar_legal">Derivar a área legal</option>
                        <option value="exportar_reporte">Exportar reporte</option>
                    </select>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Ejecutar Acción',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        preConfirm: () => {
            const accion = document.getElementById('accionMasiva').value;
            if (!accion) {
                Swal.showValidationMessage('Selecciona una acción');
                return false;
            }
            return { accion: accion, facturas: Array.from(seleccionadas).map(cb => cb.value) };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Acción ejecutada',
                text: `${result.value.accion} aplicada a ${result.value.facturas.length} facturas`
            });
        }
    });
}

function programarLlamadas() {
    window.open('/ventas/cuentas-cobrar/agenda-llamadas', '_blank');
}

function generarCartaCobranza() {
    const seleccionadas = document.querySelectorAll('input[type="checkbox"]:checked:not(#selectAllVencida)');
    
    if (seleccionadas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay facturas seleccionadas',
            text: 'Selecciona facturas para generar cartas de cobranza'
        });
        return;
    }
    
    window.open(`/ventas/cuentas-cobrar/cartas-cobranza?facturas=${Array.from(seleccionadas).map(cb => cb.value).join(',')}`, '_blank');
}

function asignarCobrador() {
    window.open('/ventas/cuentas-cobrar/asignacion-cobradores', '_blank');
}

function generarReporteVencida() {
    const opciones = ['PDF Completo', 'Excel Detallado', 'Resumen Ejecutivo', 'Carta para Clientes'];
    
    Swal.fire({
        title: 'Generar Reporte de Cartera Vencida',
        input: 'select',
        inputOptions: Object.fromEntries(opciones.map(item => [item, item])),
        inputPlaceholder: 'Selecciona tipo de reporte',
        showCancelButton: true,
        confirmButtonText: 'Generar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('/ventas/cuentas-cobrar/reporte-cartera-vencida', '_blank');
        }
    });
}

function exportarCarteraVencida() {
    Swal.fire({
        title: 'Exportar Cartera Vencida',
        text: '¿Exportar cartera vencida en Excel?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Exportar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('/ventas/cuentas-cobrar/exportar-cartera-vencida', '_blank');
        }
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    document.getElementById('selectAllVencida').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]:not(#selectAllVencida)');
        checkboxes.forEach(cb => cb.checked = this.checked);
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

.table-danger {
    background-color: rgba(220, 53, 69, 0.1);
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.avatar {
    font-size: 14px;
}

.btn-check:checked + .btn {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.fs-1 {
    font-size: 2.5rem !important;
}

.fs-2 {
    font-size: 2rem !important;
}

.fs-4 {
    font-size: 1.5rem !important;
}

.fs-6 {
    font-size: 0.875rem !important;
}
</style>
@endsection