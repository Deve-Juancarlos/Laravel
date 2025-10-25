@extends('layouts.app')

@section('title', 'Ver Planilla')

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
                            <li class="breadcrumb-item active">Ver Planilla</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-eye text-primary"></i>
                        Planilla PL-2025-10-001
                    </h1>
                    <p class="text-muted mb-0">Detalles completos de la planilla de comisiones</p>
                </div>
                <div class="text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-success" onclick="aprobarPlanilla()">
                            <i class="fas fa-check"></i> Aprobar
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="imprimirPlanilla()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="registrarPago()">
                            <i class="fas fa-money-bill"></i> Pagar
                        </button>
                        <a href="{{ route('ventas.planillas.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado y Metadatos -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-info-circle text-primary fs-1"></i>
                    </div>
                    <h6 class="text-muted">Estado</h6>
                    <span class="badge bg-success fs-6">Aprobada</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-calendar text-info fs-1"></i>
                    </div>
                    <h6 class="text-muted">Período</h6>
                    <strong>Octubre 2025</strong>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-users text-success fs-1"></i>
                    </div>
                    <h6 class="text-muted">Empleados</h6>
                    <strong>24 empleados</strong>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-money-bill text-success fs-1"></i>
                    </div>
                    <h6 class="text-muted">Total</h6>
                    <h4 class="text-success mb-0">S/ 45,680.00</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Detalle de la Planilla -->
        <div class="col-lg-8 mb-4">
            <!-- Información General -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary bg-opacity-10 border-0">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        Información General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label fw-bold">Código:</label>
                                    <p class="mb-2">PL-2025-10-001</p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">Estado:</label>
                                    <p class="mb-2"><span class="badge bg-success">Aprobada</span></p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">Fecha Inicio:</label>
                                    <p class="mb-2">01/10/2025</p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">Fecha Fin:</label>
                                    <p class="mb-2">31/10/2025</p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">Fecha Creación:</label>
                                    <p class="mb-2">23/10/2025</p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">Última Modificación:</label>
                                    <p class="mb-2">24/10/2025</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Responsable:</label>
                                    <p class="mb-2">Ana García - Gerente de Ventas</p>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Observaciones:</label>
                                    <p class="mb-0">Planilla calculada con base en ventas reales del período. Se incluyen bonificaciones por cumplimiento de metas y productos estrella.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalle por Empleados -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-users text-success"></i>
                        Detalle de Comisiones por Empleado
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaDetallePlanilla">
                            <thead class="table-light">
                                <tr>
                                    <th>Empleado</th>
                                    <th>Cargo</th>
                                    <th>Ventas Netas</th>
                                    <th>Meta</th>
                                    <th>% Cumplimiento</th>
                                    <th>Comisión Base</th>
                                    <th>Bonificaciones</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="Empleado">
                                            <div>
                                                <strong>Ana García</strong>
                                                <br>
                                                <small class="text-muted">EMP-001</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>Farmacéutica Senior</strong>
                                        <br>
                                        <small class="text-muted">3.5% comisión</small>
                                    </td>
                                    <td>
                                        <strong>S/ 45,680.00</strong>
                                        <br>
                                        <small class="text-muted">45 ventas</small>
                                    </td>
                                    <td>
                                        <strong>S/ 40,000.00</strong>
                                        <br>
                                        <small class="text-muted">Meta mensual</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success me-1">114%</span>
                                            <small class="text-success">Superada</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>S/ 1,598.80</strong>
                                        <br>
                                        <small class="text-muted">3.5% de ventas</small>
                                    </td>
                                    <td>
                                        <strong>S/ 450.00</strong>
                                        <br>
                                        <small class="text-muted">Meta: S/ 120, Producto: S/ 330</small>
                                    </td>
                                    <td>
                                        <h5 class="text-success mb-0">S/ 2,048.80</h5>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="Empleado">
                                            <div>
                                                <strong>Carlos López</strong>
                                                <br>
                                                <small class="text-muted">EMP-002</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>Vendedor</strong>
                                        <br>
                                        <small class="text-muted">3.0% comisión</small>
                                    </td>
                                    <td>
                                        <strong>S/ 38,920.00</strong>
                                        <br>
                                        <small class="text-muted">38 ventas</small>
                                    </td>
                                    <td>
                                        <strong>S/ 35,000.00</strong>
                                        <br>
                                        <small class="text-muted">Meta mensual</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success me-1">111%</span>
                                            <small class="text-success">Superada</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>S/ 1,167.60</strong>
                                        <br>
                                        <small class="text-muted">3.0% de ventas</small>
                                    </td>
                                    <td>
                                        <strong>S/ 95.00</strong>
                                        <br>
                                        <small class="text-muted">Meta: S/ 95</small>
                                    </td>
                                    <td>
                                        <h5 class="text-success mb-0">S/ 1,262.60</h5>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="Empleado">
                                            <div>
                                                <strong>María Rodríguez</strong>
                                                <br>
                                                <small class="text-muted">EMP-003</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>Farmacéutica</strong>
                                        <br>
                                        <small class="text-muted">3.2% comisión</small>
                                    </td>
                                    <td>
                                        <strong>S/ 32,450.00</strong>
                                        <br>
                                        <small class="text-muted">32 ventas</small>
                                    </td>
                                    <td>
                                        <strong>S/ 30,000.00</strong>
                                        <br>
                                        <small class="text-muted">Meta mensual</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success me-1">108%</span>
                                            <small class="text-success">Superada</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>S/ 1,038.40</strong>
                                        <br>
                                        <small class="text-muted">3.2% de ventas</small>
                                    </td>
                                    <td>
                                        <strong>S/ 350.00</strong>
                                        <br>
                                        <small class="text-muted">Producto estrella: S/ 350</small>
                                    </td>
                                    <td>
                                        <h5 class="text-success mb-0">S/ 1,388.40</h5>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Gráficos de Análisis -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar text-info"></i>
                        Análisis de Comisiones
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <h6>Distribución de Comisiones</h6>
                            <div class="chart-container" style="height: 300px;">
                                <canvas id="comisionesChart"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h6>Cumplimiento de Metas</h6>
                            <div class="chart-container" style="height: 300px;">
                                <canvas id="metasChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4 mb-4">
            <!-- Resumen Financiero -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success bg-opacity-10 border-0">
                    <h5 class="mb-0 text-success">
                        <i class="fas fa-calculator me-2"></i>
                        Resumen Financiero
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Ventas Netas Totales:</span>
                            <strong>S/ 116,950.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Comisiones Base (3.2%):</span>
                            <strong>S/ 3,742.40</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Bonificaciones:</span>
                            <strong>S/ 895.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Descuentos:</span>
                            <strong class="text-danger">S/ 0.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span><strong>Total a Pagar:</strong></span>
                            <h4 class="text-success mb-0">S/ 4,637.40</h4>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Promedio por empleado: S/ 1,545.80</small>
                    </div>
                </div>
            </div>

            <!-- Historial de Estados -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-secondary bg-opacity-10 border-0">
                    <h5 class="mb-0 text-secondary">
                        <i class="fas fa-history me-2"></i>
                        Historial de Estados
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Planilla Aprobada</h6>
                                <small class="text-muted">24/10/2025 10:30 AM</small>
                                <p class="mb-0 small">Por: Ana García</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Comisiones Calculadas</h6>
                                <small class="text-muted">23/10/2025 04:15 PM</small>
                                <p class="mb-0 small">Sistema automático</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Planilla Creada</h6>
                                <small class="text-muted">23/10/2025 02:45 PM</small>
                                <p class="mb-0 small">Por: Ana García</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-primary bg-opacity-10 border-0">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-cogs me-2"></i>
                        Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" onclick="aprobarPlanilla()">
                            <i class="fas fa-check"></i> Aprobar Planilla
                        </button>
                        <button type="button" class="btn btn-info" onclick="imprimirPlanilla()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                        <button type="button" class="btn btn-warning" onclick="registrarPago()">
                            <i class="fas fa-money-bill"></i> Registrar Pago
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="editarPlanilla()">
                            <i class="fas fa-edit"></i> Editar Planilla
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="exportarPDF()">
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="enviarEmail()">
                            <i class="fas fa-envelope"></i> Enviar Email
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alertas y Notificaciones -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-warning bg-opacity-10 border-0">
                    <h5 class="mb-0 text-warning">
                        <i class="fas fa-bell me-2"></i>
                        Alertas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success border-0">
                        <div class="d-flex">
                            <i class="fas fa-check-circle me-2 mt-1"></i>
                            <div>
                                <strong>Planilla Aprobada</strong>
                                <p class="mb-0">La planilla ha sido aprobada y está lista para pago</p>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info border-0">
                        <div class="d-flex">
                            <i class="fas fa-info-circle me-2 mt-1"></i>
                            <div>
                                <strong>Pago Pendiente</strong>
                                <p class="mb-0">Esperando registro de pago por parte de RRHH</p>
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
function aprobarPlanilla() {
    Swal.fire({
        title: 'Aprobar Planilla',
        text: '¿Estás seguro de aprobar la planilla PL-2025-10-001?',
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
                text: 'La planilla ha sido aprobada exitosamente'
            });
        }
    });
}

function imprimirPlanilla() {
    window.open('/ventas/planillas/imprimir/PL-2025-10-001', '_blank');
}

function registrarPago() {
    Swal.fire({
        title: 'Registrar Pago',
        html: `
            <div class="text-left">
                <div class="alert alert-success">
                    <strong>Planilla:</strong> PL-2025-10-001
                    <br>
                    <strong>Total a pagar:</strong> S/ 4,637.40
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha de Pago:</label>
                    <input type="date" class="form-control" id="fechaPagoPlanilla" value="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Método de Pago:</label>
                    <select class="form-select" id="metodoPagoPlanilla">
                        <option value="transferencia">Transferencia bancaria</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="cheque">Cheque</option>
                        <option value="deposito">Depósito</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Número de Comprobante:</label>
                    <input type="text" class="form-control" id="comprobantePago" placeholder="Número de transferencia, cheque, etc.">
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
                text: 'El pago de la planilla ha sido registrado exitosamente'
            });
        }
    });
}

function editarPlanilla() {
    window.open('/ventas/planillas/editar/PL-2025-10-001', '_blank');
}

function exportarPDF() {
    window.open('/ventas/planillas/exportar-pdf/PL-2025-10-001', '_blank');
}

function enviarEmail() {
    const destinatarios = [
        'ana.garcia@sifano.com',
        'rrhh@sifano.com',
        'gerencia@sifano.com'
    ];
    
    Swal.fire({
        title: 'Enviar Planilla por Email',
        html: `
            <div class="text-left">
                <div class="mb-3">
                    <label class="form-label">Destinatarios:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="emailAna" checked>
                        <label class="form-check-label" for="emailAna">ana.garcia@sifano.com (Responsable)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="emailRRHH" checked>
                        <label class="form-check-label" for="emailRRHH">rrhh@sifano.com (Recursos Humanos)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="emailGerencia">
                        <label class="form-check-label" for="emailGerencia">gerencia@sifano.com (Gerencia)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="emailPersonalizado">
                        <label class="form-check-label" for="emailPersonalizado">Agregar email personalizado</label>
                    </div>
                </div>
                <div class="mb-3" id="emailPersonalizadoContainer" style="display: none;">
                    <label class="form-label">Email adicional:</label>
                    <input type="email" class="form-control" id="emailAdicional" placeholder="email@ejemplo.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Asunto:</label>
                    <input type="text" class="form-control" id="asuntoEmail" value="Planilla de Comisiones PL-2025-10-001 - Octubre 2025">
                </div>
                <div class="mb-3">
                    <label class="form-label">Mensaje:</label>
                    <textarea class="form-control" id="mensajeEmail" rows="4">Estimados, adjuntamos la planilla de comisiones del mes de octubre 2025. La planilla ha sido aprobada y está lista para el pago correspondiente.

Resumen:
- Total empleados: 24
- Total a pagar: S/ 4,637.40
- Estado: Aprobada

Quedamos a su disposición para cualquier consulta.

Saludos cordiales,
Sistema SIFANO</textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Enviar Email',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd',
        preConfirm: () => {
            const emails = [];
            if (document.getElementById('emailAna').checked) emails.push('ana.garcia@sifano.com');
            if (document.getElementById('emailRRHH').checked) emails.push('rrhh@sifano.com');
            if (document.getElementById('emailGerencia').checked) emails.push('gerencia@sifano.com');
            
            const emailPersonalizado = document.getElementById('emailPersonalizado').checked;
            const emailAdicional = document.getElementById('emailAdicional').value;
            if (emailPersonalizado && emailAdicional) {
                emails.push(emailAdicional);
            }
            
            if (emails.length === 0) {
                Swal.showValidationMessage('Selecciona al menos un destinatario');
                return false;
            }
            
            return {
                destinatarios: emails,
                asunto: document.getElementById('asuntoEmail').value,
                mensaje: document.getElementById('mensajeEmail').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Email enviado',
                text: `Planilla enviada a ${result.value.destinatarios.length} destinatarios`
            });
        }
    });
}

// Inicializar gráficos
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de distribución de comisiones
    const comisionesCtx = document.getElementById('comisionesChart').getContext('2d');
    new Chart(comisionesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Ana García', 'Carlos López', 'María Rodríguez', 'Otros'],
            datasets: [{
                data: [2048.80, 1262.60, 1388.40, 937.60],
                backgroundColor: [
                    '#0d6efd',
                    '#198754',
                    '#fd7e14',
                    '#6f42c1'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Gráfico de cumplimiento de metas
    const metasCtx = document.getElementById('metasChart').getContext('2d');
    new Chart(metasCtx, {
        type: 'bar',
        data: {
            labels: ['Ana García', 'Carlos López', 'María Rodríguez'],
            datasets: [{
                label: 'Meta vs Ventas',
                data: [114, 111, 108],
                backgroundColor: [
                    '#198754',
                    '#198754', 
                    '#198754'
                ]
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
                    max: 120,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
    
    // Event listener para email personalizado
    document.getElementById('emailPersonalizado').addEventListener('change', function() {
        const container = document.getElementById('emailPersonalizadoContainer');
        if (this.checked) {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
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

.alert {
    margin-bottom: 1rem;
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

.chart-container {
    position: relative;
    height: 100%;
    width: 100%;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.fs-1 {
    font-size: 2.5rem !important;
}

.fw-bold {
    font-weight: 600;
}
</style>
@endsection