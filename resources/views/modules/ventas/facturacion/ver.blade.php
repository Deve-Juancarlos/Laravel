@extends('layouts.app')

@section('title', 'Ver Factura')

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
                            <li class="breadcrumb-item"><a href="{{ route('ventas.facturacion.index') }}">Facturación</a></li>
                            <li class="breadcrumb-item active">Ver Factura</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-eye text-primary"></i>
                        Factura F-001245
                    </h1>
                    <p class="text-muted mb-0">Detalles completos de la factura</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="duplicarFactura()">
                            <i class="fas fa-copy"></i> Duplicar
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="convertirABoleta()">
                            <i class="fas fa-exchange-alt"></i> Boleta
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="anularFactura()">
                            <i class="fas fa-ban"></i> Anular
                        </button>
                        <a href="{{ route('ventas.facturacion.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Detalles de la Factura -->
        <div class="col-lg-8 mb-4">
            <!-- Estado y Metadatos -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-info-circle text-primary fs-1"></i>
                                </div>
                                <h6 class="text-muted">Estado</h6>
                                <span class="badge bg-success fs-6">Completada</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-calendar text-info fs-1"></i>
                                </div>
                                <h6 class="text-muted">Fecha Emisión</h6>
                                <strong>25/10/2025</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-calendar-check text-warning fs-1"></i>
                                </div>
                                <h6 class="text-muted">Fecha Vencimiento</h6>
                                <strong>24/11/2025</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-money-bill text-success fs-1"></i>
                                </div>
                                <h6 class="text-muted">Total</h6>
                                <h4 class="text-success mb-0">S/ 156.80</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de la Empresa -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary bg-opacity-10 border-0">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-building me-2"></i>
                        Información de la Empresa
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6>Farmacia SIFANO</h6>
                            <p class="mb-1">
                                <i class="fas fa-id-card me-2 text-muted"></i>
                                RUC: 20123456789
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                                Av. Principal 123, Lima, Perú
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-phone me-2 text-muted"></i>
                                Tel: (01) 234-5678
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-envelope me-2 text-muted"></i>
                                ventas@sifano.com
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Comprobante</h6>
                            <p class="mb-1">
                                <strong>Tipo:</strong> Factura
                            </p>
                            <p class="mb-1">
                                <strong>Número:</strong> F-001245
                            </p>
                            <p class="mb-1">
                                <strong>Serie:</strong> F001
                            </p>
                            <p class="mb-0">
                                <strong>Ticket:</strong> 001245
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Cliente -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info bg-opacity-10 border-0">
                    <h5 class="mb-0 text-info">
                        <i class="fas fa-user me-2"></i>
                        Información del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6>Juan Pérez García</h6>
                            <p class="mb-1">
                                <i class="fas fa-id-card me-2 text-muted"></i>
                                DNI: 12345678
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-phone me-2 text-muted"></i>
                                Tel: (01) 987-6543
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-envelope me-2 text-muted"></i>
                                juan.perez@email.com
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1">
                                <strong>Tipo:</strong> Persona Natural
                            </p>
                            <p class="mb-1">
                                <strong>Dirección:</strong> Calle Los Olivos 456, San Isidro
                            </p>
                            <p class="mb-1">
                                <strong>Distrito:</strong> San Isidro
                            </p>
                            <p class="mb-0">
                                <strong>Condición:</strong> Contado
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalle de Productos -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success bg-opacity-10 border-0">
                    <h5 class="mb-0 text-success">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Detalle de Productos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40%">Producto/Servicio</th>
                                    <th style="width: 10%">Cant.</th>
                                    <th style="width: 15%">P. Unit.</th>
                                    <th style="width: 10%">Desc.%</th>
                                    <th style="width: 25%">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>P001</strong>
                                            <br>
                                            <small class="text-muted">Paracetamol 500mg x20 tabletas</small>
                                        </div>
                                    </td>
                                    <td class="text-center">2</td>
                                    <td class="text-end">S/ 15.50</td>
                                    <td class="text-center">0%</td>
                                    <td class="text-end">
                                        <strong>S/ 31.00</strong>
                                        <br>
                                        <small class="text-muted">S/ 15.50 c/u</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>P002</strong>
                                            <br>
                                            <small class="text-muted">Ibuprofeno 400mg x30 cápsulas</small>
                                        </div>
                                    </td>
                                    <td class="text-center">1</td>
                                    <td class="text-end">S/ 22.80</td>
                                    <td class="text-center">5%</td>
                                    <td class="text-end">
                                        <strong>S/ 21.66</strong>
                                        <br>
                                        <small class="text-muted">Desc: S/ 1.14</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>P005</strong>
                                            <br>
                                            <small class="text-muted">Omeprazol 20mg x14 cápsulas</small>
                                        </div>
                                    </td>
                                    <td class="text-center">1</td>
                                    <td class="text-end">S/ 12.50</td>
                                    <td class="text-center">0%</td>
                                    <td class="text-end">
                                        <strong>S/ 12.50</strong>
                                        <br>
                                        <small class="text-muted">S/ 12.50 c/u</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <strong>S001</strong>
                                            <br>
                                            <small class="text-muted">Consulta farmacéutica</small>
                                        </div>
                                    </td>
                                    <td class="text-center">1</td>
                                    <td class="text-end">S/ 67.80</td>
                                    <td class="text-center">0%</td>
                                    <td class="text-end">
                                        <strong>S/ 67.80</strong>
                                        <br>
                                        <small class="text-muted">S/ 67.80 c/u</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning bg-opacity-10 border-0">
                    <h5 class="mb-0 text-warning">
                        <i class="fas fa-sticky-note me-2"></i>
                        Observaciones
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        Cliente frecuente. Productos para tratamiento de dolor de cabeza y malestar estomacal.
                        Se recomienda seguimiento médico para el uso prolongado de ibuprofeno.
                    </p>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4 mb-4">
            <!-- Totales -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success bg-opacity-10 border-0">
                    <h5 class="mb-0 text-success">
                        <i class="fas fa-calculator me-2"></i>
                        Resumen de Totales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal (Sin IGV):</span>
                        <strong>S/ 132.88</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Descuento Total:</span>
                        <strong class="text-danger">S/ 1.14</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>IGV (18%):</span>
                        <strong>S/ 23.92</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><strong>Total a Pagar:</strong></span>
                        <h4 class="text-success mb-0">S/ 156.80</h4>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Total en letras:</small>
                        </div>
                        <div class="alert alert-info py-2">
                            <small class="text-muted">CIENTO CINCUENTA Y SEIS CON 80/100 SOLES</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Vendedor -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary bg-opacity-10 border-0">
                    <h5 class="mb-0 text-secondary">
                        <i class="fas fa-user-tie me-2"></i>
                        Información del Vendedor
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://via.placeholder.com/50" class="rounded-circle me-3" alt="Vendedor">
                        <div>
                            <h6 class="mb-0">Ana García</h6>
                            <small class="text-muted">Farmacéutica Senior</small>
                        </div>
                    </div>
                    <p class="mb-2">
                        <strong>Punto de Venta:</strong> Farmacia Principal
                    </p>
                    <p class="mb-0">
                        <strong>Fecha de Registro:</strong> 25/10/2025 10:30 AM
                    </p>
                </div>
            </div>

            <!-- Métodos de Pago -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info bg-opacity-10 border-0">
                    <h5 class="mb-0 text-info">
                        <i class="fas fa-credit-card me-2"></i>
                        Método de Pago
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-money-bill-wave text-success" style="font-size: 3rem;"></i>
                        <h5 class="text-success mt-2">Efectivo</h5>
                    </div>
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <strong class="d-block">Recibido</strong>
                            <span class="text-success">S/ 200.00</span>
                        </div>
                        <div class="col-6">
                            <strong class="d-block">Cambio</strong>
                            <span class="text-info">S/ 43.20</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary bg-opacity-10 border-0">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-cogs me-2"></i>
                        Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="imprimirFactura()">
                            <i class="fas fa-print"></i> Imprimir Factura
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="descargarPDF()">
                            <i class="fas fa-file-pdf"></i> Descargar PDF
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="enviarEmail()">
                            <i class="fas fa-envelope"></i> Enviar por Email
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="exportarExcel()">
                            <i class="fas fa-file-excel"></i> Exportar a Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Historial -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-secondary bg-opacity-10 border-0">
                    <h5 class="mb-0 text-secondary">
                        <i class="fas fa-history me-2"></i>
                        Historial de la Factura
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Factura Creada</h6>
                                <small class="text-muted">25/10/2025 10:30 AM</small>
                                <p class="mb-0 small">Por: Ana García</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Pago Registrado</h6>
                                <small class="text-muted">25/10/2025 10:35 AM</small>
                                <p class="mb-0 small">Método: Efectivo S/ 200.00</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Factura Completada</h6>
                                <small class="text-muted">25/10/2025 10:35 AM</small>
                                <p class="mb-0 small">Estado: Pagada</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Email Enviado</h6>
                                <small class="text-muted">25/10/2025 11:00 AM</small>
                                <p class="mb-0 small">A: juan.perez@email.com</p>
                            </div>
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
function imprimirFactura() {
    Swal.fire({
        title: 'Imprimir Factura',
        text: '¿Deseas imprimir la factura F-001245?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Imprimir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            // Simular impresión
            window.open('/ventas/facturacion/imprimir/F-001245', '_blank');
        }
    });
}

function descargarPDF() {
    Swal.fire({
        title: 'Descargando PDF...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });

    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'PDF descargado',
            text: 'La factura F-001245.pdf se ha descargado exitosamente'
        });
    }, 2000);
}

function enviarEmail() {
    Swal.fire({
        title: 'Enviar por Email',
        html: `
            <div class="text-left">
                <div class="mb-3">
                    <label class="form-label">Email del cliente:</label>
                    <input type="email" class="form-control" id="emailFactura" value="juan.perez@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email adicional (opcional):</label>
                    <input type="email" class="form-control" id="emailAdicional" placeholder="otro@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Asunto:</label>
                    <input type="text" class="form-control" id="asuntoFactura" value="Factura F-001245 - Farmacia SIFANO">
                </div>
                <div class="mb-3">
                    <label class="form-label">Mensaje:</label>
                    <textarea class="form-control" id="mensajeFactura" rows="4" placeholder="Mensaje personalizado...">Estimado cliente, adjuntamos su factura por los productos adquiridos en Farmacia SIFANO. Gracias por su preferencia.</textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const email = document.getElementById('emailFactura').value;
            if (!email) {
                Swal.showValidationMessage('El email es requerido');
                return false;
            }
            return {
                email: email,
                adicional: document.getElementById('emailAdicional').value,
                asunto: document.getElementById('asuntoFactura').value,
                mensaje: document.getElementById('mensajeFactura').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Email enviado',
                text: `Factura enviada a ${result.value.email}`,
                timer: 3000,
                showConfirmButton: false
            });
        }
    });
}

function exportarExcel() {
    Swal.fire({
        title: 'Exportar a Excel',
        text: '¿Deseas exportar los datos de la factura a Excel?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Exportar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Exportación completada',
                text: 'El archivo F-001245.xlsx se ha descargado exitosamente'
            });
        }
    });
}

function duplicarFactura() {
    Swal.fire({
        title: 'Duplicar Factura',
        text: '¿Crear una nueva factura basada en F-001245?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Duplicar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/ventas/facturacion/duplicar/F-001245';
        }
    });
}

function convertirABoleta() {
    Swal.fire({
        title: 'Convertir a Boleta',
        text: '¿Convertir factura F-001245 a boleta de venta?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Convertir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({ converted: true });
                }, 2000);
            });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value.converted) {
            Swal.fire({
                icon: 'success',
                title: 'Conversión exitosa',
                text: 'La factura ha sido convertida a boleta B-001245'
            });
        }
    });
}

function anularFactura() {
    Swal.fire({
        title: 'Anular Factura',
        text: '¿Estás seguro de anular la factura F-001245?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({ cancelled: true });
                }, 2000);
            });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value.cancelled) {
            Swal.fire({
                icon: 'success',
                title: 'Factura anulada',
                text: 'La factura F-001245 ha sido anulada exitosamente'
            }).then(() => {
                window.location.href = '/ventas/facturacion/index';
            });
        }
    });
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

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
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

.alert {
    margin-bottom: 1rem;
}

.fs-1 {
    font-size: 2.5rem !important;
}

.fs-6 {
    font-size: 0.875rem !important;
}
</style>
@endsection