@extends('layouts.app')

@section('title', 'Estado de Cuenta')

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
                            <li class="breadcrumb-item active">Estado de Cuenta</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-file-invoice-dollar text-info"></i>
                        Estado de Cuenta
                    </h1>
                    <p class="text-muted mb-0">Consultar y generar estados de cuenta por cliente</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="imprimirEstadoCuenta()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="enviarEstadoCuenta()">
                            <i class="fas fa-envelope"></i> Enviar Email
                        </button>
                        <a href="{{ route('ventas.cuentas-cobrar.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Búsqueda de Cliente -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info bg-opacity-10 border-0">
                    <h5 class="mb-0 text-info">
                        <i class="fas fa-search me-2"></i>
                        Búsqueda de Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Buscar Cliente</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="busquedaCliente" 
                                       placeholder="Buscar por nombre, DNI, RUC o email..." 
                                       onkeyup="buscarClienteEstado(event)">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de Documento</label>
                            <select class="form-select" id="tipoDocumento">
                                <option value="">Todos</option>
                                <option value="dni">DNI</option>
                                <option value="ruc">RUC</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Acciones</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-info" onclick="buscarClienteEstado()">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados de Búsqueda -->
    <div class="row mb-4" id="resultadosBusqueda" style="display: none;">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-info"></i>
                        Resultados de Búsqueda
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Documento</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Saldo Pendiente</th>
                                    <th>Última Compra</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="listaClientes">
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-primary bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-building text-primary"></i>
                                            </div>
                                            <div>
                                                <strong>Empresa ABC S.A.C.</strong>
                                                <br>
                                                <small class="text-muted">Farmacia</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>RUC: 20123456789</td>
                                    <td>ventas@empresaabc.com</td>
                                    <td>(01) 234-5678</td>
                                    <td>
                                        <strong class="text-danger">S/ 8,500.00</strong>
                                    </td>
                                    <td>15/10/2025</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="seleccionarCliente('20123456789')">
                                            <i class="fas fa-eye"></i> Ver Estado
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-success bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-building text-success"></i>
                                            </div>
                                            <div>
                                                <strong>Clínica Salud Total</strong>
                                                <br>
                                                <small class="text-muted">Clínica</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>RUC: 20987654321</td>
                                    <td>admin@clinicasalud.com</td>
                                    <td>(01) 345-6789</td>
                                    <td>
                                        <strong class="text-warning">S/ 3,250.00</strong>
                                    </td>
                                    <td>20/10/2025</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="seleccionarCliente('20987654321')">
                                            <i class="fas fa-eye"></i> Ver Estado
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

    <!-- Estado de Cuenta del Cliente -->
    <div class="row" id="estadoCuenta" style="display: none;">
        <!-- Información del Cliente -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary bg-opacity-10 border-0">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-user me-2"></i>
                        Información del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar bg-primary bg-opacity-10 rounded-circle mx-auto mb-2" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-building text-primary fs-2"></i>
                        </div>
                        <h5 id="nombreCliente">Empresa ABC S.A.C.</h5>
                        <p class="text-muted mb-2" id="documentoCliente">RUC: 20123456789</p>
                        <p class="text-muted mb-0" id="categoriaCliente">Cliente Corporativo</p>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6>Contacto</h6>
                        <p class="mb-1">
                            <i class="fas fa-envelope me-2 text-muted"></i>
                            <span id="emailCliente">ventas@empresaabc.com</span>
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-phone me-2 text-muted"></i>
                            <span id="telefonoCliente">(01) 234-5678</span>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                            <span id="direccionCliente">Av. Principal 123, Lima</span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6>Datos Comerciales</h6>
                        <p class="mb-1">
                            <strong>Límite de Crédito:</strong> S/ 50,000.00
                        </p>
                        <p class="mb-1">
                            <strong>Plazo de Pago:</strong> 30 días
                        </p>
                        <p class="mb-0">
                            <strong>Estado:</strong> <span class="badge bg-success">Activo</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Resumen Financiero -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-success bg-opacity-10 border-0">
                    <h5 class="mb-0 text-success">
                        <i class="fas fa-chart-pie me-2"></i>
                        Resumen Financiero
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Saldo Total:</span>
                            <strong class="text-danger" id="saldoTotal">S/ 8,500.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Por Vencer:</span>
                            <strong class="text-warning">S/ 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Vencidas:</span>
                            <strong class="text-danger">S/ 8,500.00</strong>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6>Historial de Pagos (Últimos 6 meses)</h6>
                        <div class="chart-container" style="height: 200px;">
                            <canvas id="pagosChart"></canvas>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h6>Indicadores</h6>
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <div class="h5 text-primary mb-0">92%</div>
                                    <small class="text-muted">Puntualidad</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <div class="h5 text-success mb-0">S/ 45,800</div>
                                    <small class="text-muted">Compras Mes</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle de Facturas -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice text-info"></i>
                        Estado de Cuenta Detallado
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarExcel()">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="generarPDF()">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Factura</th>
                                    <th>Vencimiento</th>
                                    <th>Total</th>
                                    <th>Abonado</th>
                                    <th>Saldo</th>
                                    <th>Días Venc.</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-danger">
                                    <td>
                                        <span class="d-block">15/10/2025</span>
                                        <small class="text-muted">Emisión</small>
                                    </td>
                                    <td>
                                        <strong>F-001234</strong>
                                        <br>
                                        <small class="text-muted">Ticket: 001234</small>
                                    </td>
                                    <td>
                                        <span class="d-block">15/11/2025</span>
                                        <small class="text-danger"><strong>10 días vencida</strong></small>
                                    </td>
                                    <td>
                                        <strong>S/ 8,500.00</strong>
                                        <br>
                                        <small class="text-muted">Subtotal: S/ 7,203.39</small>
                                    </td>
                                    <td>
                                        <span class="text-muted">S/ 0.00</span>
                                    </td>
                                    <td>
                                        <strong class="text-danger">S/ 8,500.00</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">10 días</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">Vencida</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="verFactura('F-001234')" title="Ver Factura">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="registrarPagoEstado('F-001234')" title="Registrar Pago">
                                                <i class="fas fa-money-bill"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="descargarFactura('F-001234')" title="Descargar">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detalle de Movimientos -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt text-warning"></i>
                        Historial de Movimientos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">Factura Emitida</h6>
                                        <p class="mb-1 text-muted">F-001234 - Paracetamol, Ibuprofeno</p>
                                        <small class="text-muted">15/10/2025 10:30 AM</small>
                                    </div>
                                    <div class="text-end">
                                        <strong class="text-danger">-S/ 8,500.00</strong>
                                        <br>
                                        <small class="text-muted">Saldo: S/ 8,500.00</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">Recordatorio Enviado</h6>
                                        <p class="mb-1 text-muted">Email automático por vencimiento</p>
                                        <small class="text-muted">22/10/2025 09:00 AM</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-warning">Notificación</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">Llamada de Cobranza</h6>
                                        <p class="mb-1 text-muted">Sin respuesta - Dejar mensaje</p>
                                        <small class="text-muted">22/10/2025 02:30 PM</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-info">Contacto</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promedio de Pago -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-clock text-primary"></i>
                        Análisis de Comportamiento de Pago
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-calendar-check text-success fs-1"></i>
                                </div>
                                <h4 class="text-success mb-1">28 días</h4>
                                <p class="text-muted mb-2">Promedio de Pago</p>
                                <div class="progress mb-2" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: 93%"></div>
                                </div>
                                <small class="text-success">Dentro del plazo (30 días)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-percentage text-info fs-1"></i>
                                </div>
                                <h4 class="text-info mb-1">92%</h4>
                                <p class="text-muted mb-2">Puntualidad</p>
                                <div class="progress mb-2" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: 92%"></div>
                                </div>
                                <small class="text-info">Pagos dentro de plazo</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6>Tendencia de Pagos (Últimos 6 meses)</h6>
                        <div class="chart-container" style="height: 150px;">
                            <canvas id="tendenciasPagoChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensaje de Estado Vacío -->
    <div class="row" id="estadoVacio">
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-search fs-1 text-muted mb-3"></i>
                <h4 class="text-muted">Buscar Cliente</h4>
                <p class="text-muted">Ingresa el nombre, DNI, RUC o email del cliente para consultar su estado de cuenta</p>
                <button type="button" class="btn btn-info" onclick="mostrarBusqueda()">
                    <i class="fas fa-search"></i> Buscar Cliente
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function buscarClienteEstado(event) {
    if (event && event.key === 'Enter') {
        mostrarResultadosBusqueda();
    }
}

function mostrarResultadosBusqueda() {
    const busqueda = document.getElementById('busquedaCliente').value;
    
    if (busqueda.length < 2) {
        Swal.fire({
            icon: 'warning',
            title: 'Búsqueda muy corta',
            text: 'Ingresa al menos 2 caracteres para buscar'
        });
        return;
    }
    
    // Mostrar resultados
    document.getElementById('resultadosBusqueda').style.display = 'block';
    document.getElementById('estadoVacio').style.display = 'none';
    
    // Simular búsqueda
    console.log('Buscando cliente:', busqueda);
}

function seleccionarCliente(documento) {
    // Datos simulados del cliente
    const cliente = {
        documento: documento,
        nombre: documento === '20123456789' ? 'Empresa ABC S.A.C.' : 'Clínica Salud Total',
        email: documento === '20123456789' ? 'ventas@empresaabc.com' : 'admin@clinicasalud.com',
        telefono: documento === '20123456789' ? '(01) 234-5678' : '(01) 345-6789',
        direccion: documento === '20123456789' ? 'Av. Principal 123, Lima' : 'Calle Salud 456, Lima',
        saldo: documento === '20123456789' ? '8500.00' : '3250.00'
    };
    
    // Llenar información del cliente
    document.getElementById('nombreCliente').textContent = cliente.nombre;
    document.getElementById('documentoCliente').textContent = (cliente.documento.length === 11 ? 'RUC: ' : 'DNI: ') + cliente.documento;
    document.getElementById('emailCliente').textContent = cliente.email;
    document.getElementById('telefonoCliente').textContent = cliente.telefono;
    document.getElementById('direccionCliente').textContent = cliente.direccion;
    document.getElementById('saldoTotal').textContent = 'S/ ' + parseFloat(cliente.saldo).toFixed(2);
    
    // Mostrar estado de cuenta
    document.getElementById('estadoCuenta').style.display = 'block';
    document.getElementById('resultadosBusqueda').style.display = 'none';
    
    // Inicializar gráficos
    inicializarGraficos();
}

function inicializarGraficos() {
    // Gráfico de pagos históricos
    const pagosCtx = document.getElementById('pagosChart').getContext('2d');
    new Chart(pagosCtx, {
        type: 'bar',
        data: {
            labels: ['May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct'],
            datasets: [{
                label: 'Pagos (S/)',
                data: [12000, 15000, 18000, 16000, 22000, 8500],
                backgroundColor: 'rgba(13, 110, 253, 0.7)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + (value/1000) + 'k';
                        }
                    }
                }
            }
        }
    });
    
    // Gráfico de tendencias de pago
    const tendenciasCtx = document.getElementById('tendenciasPagoChart').getContext('2d');
    new Chart(tendenciasCtx, {
        type: 'line',
        data: {
            labels: ['May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct'],
            datasets: [{
                label: 'Días Promedio de Pago',
                data: [25, 28, 30, 26, 29, 35],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 20,
                    max: 40,
                    ticks: {
                        callback: function(value) {
                            return value + ' días';
                        }
                    }
                }
            }
        }
    });
}

function mostrarBusqueda() {
    document.getElementById('busquedaCliente').focus();
}

function verFactura(numeroFactura) {
    window.open(`/ventas/facturacion/ver/${numeroFactura}`, '_blank');
}

function registrarPagoEstado(numeroFactura) {
    Swal.fire({
        title: 'Registrar Pago',
        html: `
            <div class="text-left">
                <div class="alert alert-info">
                    <strong>Factura:</strong> ${numeroFactura}
                    <br>
                    <strong>Saldo Pendiente:</strong> S/ 8,500.00
                </div>
                <div class="mb-3">
                    <label class="form-label">Monto a Pagar:</label>
                    <input type="number" class="form-control" id="montoEstadoPago" placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha de Pago:</label>
                    <input type="date" class="form-control" id="fechaEstadoPago" value="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Método de Pago:</label>
                    <select class="form-select" id="metodoEstadoPago">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="cheque">Cheque</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones:</label>
                    <textarea class="form-control" id="observacionesEstadoPago" rows="3" placeholder="Observaciones adicionales..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Registrar Pago',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754',
        preConfirm: () => {
            const monto = parseFloat(document.getElementById('montoEstadoPago').value);
            if (!monto || monto <= 0) {
                Swal.showValidationMessage('El monto debe ser mayor a 0');
                return false;
            }
            return {
                monto: monto,
                fecha: document.getElementById('fechaEstadoPago').value,
                metodo: document.getElementById('metodoEstadoPago').value,
                observaciones: document.getElementById('observacionesEstadoPago').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Pago registrado',
                text: `Pago de S/ ${result.value.monto.toFixed(2)} registrado exitosamente`
            });
        }
    });
}

function descargarFactura(numeroFactura) {
    window.open(`/ventas/facturacion/descargar/${numeroFactura}`, '_blank');
}

function imprimirEstadoCuenta() {
    const cliente = document.getElementById('nombreCliente').textContent;
    if (cliente === 'Empresa ABC S.A.C.' || cliente === 'Clínica Salud Total') {
        window.print();
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Cliente no seleccionado',
            text: 'Selecciona un cliente para imprimir el estado de cuenta'
        });
    }
}

function enviarEstadoCuenta() {
    const cliente = document.getElementById('nombreCliente').textContent;
    if (cliente === 'Empresa ABC S.A.C.' || cliente === 'Clínica Salud Total') {
        const email = document.getElementById('emailCliente').textContent;
        
        Swal.fire({
            title: 'Enviar Estado de Cuenta',
            html: `
                <div class="text-left">
                    <div class="alert alert-info">
                        <strong>Cliente:</strong> ${cliente}
                        <br>
                        <strong>Email:</strong> ${email}
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email del cliente:</label>
                        <input type="email" class="form-control" id="emailEstadoCuenta" value="${email}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Asunto:</label>
                        <input type="text" class="form-control" id="asuntoEstadoCuenta" value="Estado de Cuenta - ${cliente}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mensaje:</label>
                        <textarea class="form-control" id="mensajeEstadoCuenta" rows="4">Estimado cliente, adjuntamos su estado de cuenta actual. Quedamos a su disposición para cualquier consulta. Saludos cordiales, Farmacia SIFANO.</textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Enviar Email',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0d6efd',
            preConfirm: () => {
                const email = document.getElementById('emailEstadoCuenta').value;
                if (!email) {
                    Swal.showValidationMessage('El email es requerido');
                    return false;
                }
                return {
                    email: email,
                    asunto: document.getElementById('asuntoEstadoCuenta').value,
                    mensaje: document.getElementById('mensajeEstadoCuenta').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Estado de cuenta enviado',
                    text: `Estado de cuenta enviado a ${result.value.email}`
                });
            }
        });
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Cliente no seleccionado',
            text: 'Selecciona un cliente para enviar el estado de cuenta'
        });
    }
}

function exportarExcel() {
    const cliente = document.getElementById('nombreCliente').textContent;
    if (cliente === 'Empresa ABC S.A.C.' || cliente === 'Clínica Salud Total') {
        window.open(`/ventas/cuentas-cobrar/exportar-excel/${cliente.replace(/\s+/g, '_')}`, '_blank');
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Cliente no seleccionado',
            text: 'Selecciona un cliente para exportar'
        });
    }
}

function generarPDF() {
    const cliente = document.getElementById('nombreCliente').textContent;
    if (cliente === 'Empresa ABC S.A.C.' || cliente === 'Clínica Salud Total') {
        window.open(`/ventas/cuentas-cobrar/generar-pdf/${cliente.replace(/\s+/g, '_')}`, '_blank');
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Cliente no seleccionado',
            text: 'Selecciona un cliente para generar PDF'
        });
    }
}
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

.avatar {
    font-size: 14px;
}

.table-danger {
    background-color: rgba(220, 53, 69, 0.1);
}

.chart-container {
    position: relative;
    height: 100%;
    width: 100%;
}

.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -15px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid white;
}

.timeline-content {
    padding-left: 15px;
}

.timeline-content h6 {
    margin-bottom: 0.25rem;
}

.fs-1 {
    font-size: 2.5rem !important;
}

.fs-2 {
    font-size: 2rem !important;
}

.alert {
    margin-bottom: 1rem;
}
</style>
@endsection