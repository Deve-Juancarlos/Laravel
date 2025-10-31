@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Transferencias Bancarias</h4>
                <div class="page-title-right">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaTransferencia">
                        <i class="fas fa-plus me-1"></i> Nueva Transferencia
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form id="formFiltros" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" name="fecha_desde" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control" name="fecha_hasta" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Banco Origen</label>
                            <select class="form-select" name="banco_origen">
                                <option value="">Todos</option>
                                @foreach($bancos as $banco)
                                <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="procesada">Procesada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Filtrar
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Transferido</h6>
                            <h3 class="mb-0">$<span id="totalTransferido">0.00</span></h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-white bg-opacity-25 rounded-3">
                                <i class="fas fa-exchange-alt fa-2x"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Procesadas</h6>
                            <h3 class="mb-0"><span id="totalProcesadas">0</span></h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-white bg-opacity-25 rounded-3">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Pendientes</h6>
                            <h3 class="mb-0"><span id="totalPendientes">0</span></h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-white bg-opacity-25 rounded-3">
                                <i class="fas fa-clock fa-2x"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Transferencias -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="tablaTransferencias">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Banco Origen</th>
                                    <th>Banco Destino</th>
                                    <th>Monto</th>
                                    <th>Concepto</th>
                                    <th>Estado</th>
                                    <th>Usuario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transferencias as $transferencia)
                                <tr>
                                    <td>{{ $transferencia->id }}</td>
                                    <td>{{ $transferencia->fecha->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <strong>{{ $transferencia->bancoOrigen->nombre }}</strong><br>
                                        <small class="text-muted">{{ $transferencia->cuentaOrigen->numero }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $transferencia->bancoDestino->nombre }}</strong><br>
                                        <small class="text-muted">{{ $transferencia->cuentaDestino->numero }}</small>
                                    </td>
                                    <td class="text-end">
                                        <strong>${{ number_format($transferencia->monto, 2) }}</strong>
                                    </td>
                                    <td>{{ $transferencia->concepto }}</td>
                                    <td>
                                        @if($transferencia->estado == 'procesada')
                                        <span class="badge bg-success">Procesada</span>
                                        @elseif($transferencia->estado == 'pendiente')
                                        <span class="badge bg-warning">Pendiente</span>
                                        @else
                                        <span class="badge bg-danger">Cancelada</span>
                                        @endif
                                    </td>
                                    <td>{{ $transferencia->usuario->name }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info" onclick="verDetalle({{ $transferencia->id }})" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($transferencia->estado == 'pendiente')
                                            <button type="button" class="btn btn-sm btn-success" onclick="procesarTransferencia({{ $transferencia->id }})" title="Procesar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="cancelarTransferencia({{ $transferencia->id }})" title="Cancelar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="imprimirComprobante({{ $transferencia->id }})" title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted">No hay transferencias registradas</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $transferencias->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Transferencia -->
<div class="modal fade" id="modalNuevaTransferencia" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt me-2"></i>Nueva Transferencia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNuevaTransferencia">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="fecha" value="{{ date('Y-m-d\\TH:i') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monto <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="monto" step="0.01" min="0.01" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Cuenta Origen:</strong> De dónde sale el dinero
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Banco Origen <span class="text-danger">*</span></label>
                            <select class="form-select" name="banco_origen_id" id="bancoOrigen" required>
                                <option value="">Seleccione...</option>
                                @foreach($bancos as $banco)
                                <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cuenta Origen <span class="text-danger">*</span></label>
                            <select class="form-select" name="cuenta_origen_id" id="cuentaOrigen" required disabled>
                                <option value="">Primero seleccione banco</option>
                            </select>
                            <small class="text-muted">Saldo disponible: $<span id="saldoOrigen">0.00</span></small>
                        </div>

                        <div class="col-12">
                            <div class="alert alert-success">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Cuenta Destino:</strong> A dónde llega el dinero
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Banco Destino <span class="text-danger">*</span></label>
                            <select class="form-select" name="banco_destino_id" id="bancoDestino" required>
                                <option value="">Seleccione...</option>
                                @foreach($bancos as $banco)
                                <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cuenta Destino <span class="text-danger">*</span></label>
                            <select class="form-select" name="cuenta_destino_id" id="cuentaDestino" required disabled>
                                <option value="">Primero seleccione banco</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Concepto <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="concepto" rows="2" required placeholder="Describe el motivo de la transferencia"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Referencia/Número de Operación</label>
                            <input type="text" class="form-control" name="referencia" placeholder="Opcional">
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="procesar_inmediato" id="procesarInmediato" checked>
                                <label class="form-check-label" for="procesarInmediato">
                                    Procesar inmediatamente (desmarcar para dejar pendiente)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Registrar Transferencia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detalle de Transferencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido">
                <!-- Contenido cargado dinámicamente -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Cargar cuentas al seleccionar banco origen
    $('#bancoOrigen').on('change', function() {
        const bancoId = $(this).val();
        cargarCuentas(bancoId, '#cuentaOrigen', true);
    });

    // Cargar cuentas al seleccionar banco destino
    $('#bancoDestino').on('change', function() {
        const bancoId = $(this).val();
        cargarCuentas(bancoId, '#cuentaDestino', false);
    });

    // Mostrar saldo al seleccionar cuenta origen
    $('#cuentaOrigen').on('change', function() {
        const cuentaId = $(this).val();
        if (cuentaId) {
            $.get(`/api/cuentas/${cuentaId}/saldo`, function(data) {
                $('#saldoOrigen').text(data.saldo.toFixed(2));
            });
        }
    });

    // Validar monto contra saldo
    $('input[name="monto"]').on('blur', function() {
        const monto = parseFloat($(this).val());
        const saldo = parseFloat($('#saldoOrigen').text());
        
        if (monto > saldo) {
            Swal.fire({
                icon: 'warning',
                title: 'Saldo insuficiente',
                text: 'El monto a transferir excede el saldo disponible'
            });
        }
    });

    // Enviar formulario
    $('#formNuevaTransferencia').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("contador.bancos.transferencias") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Transferencia registrada!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Ocurrió un error al procesar la transferencia'
                });
            }
        });
    });

    // Filtros
    $('#formFiltros').on('submit', function(e) {
        e.preventDefault();
        const params = $(this).serialize();
        window.location.href = `{{ route('contador.bancos.transferencias') }}?${params}`;
    });

    // Actualizar resumen
    actualizarResumen();
});

function cargarCuentas(bancoId, selector, mostrarSaldo) {
    if (!bancoId) {
        $(selector).html('<option value="">Primero seleccione banco</option>').prop('disabled', true);
        return;
    }

    $.get(`/api/bancos/${bancoId}/cuentas`, function(data) {
        let options = '<option value="">Seleccione cuenta...</option>';
        data.forEach(cuenta => {
            const saldoText = mostrarSaldo ? ` - Saldo: $${cuenta.saldo.toFixed(2)}` : '';
            options += `<option value="${cuenta.id}">${cuenta.numero} - ${cuenta.tipo}${saldoText}</option>`;
        });
        $(selector).html(options).prop('disabled', false);
    });
}

function verDetalle(id) {
    $.get(`/transferencias/${id}`, function(data) {
        let html = `
            <div class="row g-3">
                <div class="col-12">
                    <div class="alert alert-${data.estado === 'procesada' ? 'success' : 'warning'}">
                        <strong>Estado:</strong> ${data.estado.toUpperCase()}
                    </div>
                </div>
                <div class="col-6"><strong>ID:</strong></div>
                <div class="col-6">${data.id}</div>
                <div class="col-6"><strong>Fecha:</strong></div>
                <div class="col-6">${data.fecha}</div>
                <div class="col-6"><strong>Monto:</strong></div>
                <div class="col-6"><strong>$${data.monto}</strong></div>
                <div class="col-12"><hr></div>
                <div class="col-12"><strong>Origen:</strong></div>
                <div class="col-12">${data.banco_origen} - ${data.cuenta_origen}</div>
                <div class="col-12"><strong>Destino:</strong></div>
                <div class="col-12">${data.banco_destino} - ${data.cuenta_destino}</div>
                <div class="col-12"><hr></div>
                <div class="col-12"><strong>Concepto:</strong></div>
                <div class="col-12">${data.concepto}</div>
                ${data.referencia ? `<div class="col-12"><strong>Referencia:</strong> ${data.referencia}</div>` : ''}
                <div class="col-12"><hr></div>
                <div class="col-12"><small class="text-muted">Registrado por: ${data.usuario}</small></div>
            </div>
        `;
        $('#detalleContenido').html(html);
        new bootstrap.Modal(document.getElementById('modalDetalle')).show();
    });
}

function procesarTransferencia(id) {
    Swal.fire({
        title: '¿Procesar transferencia?',
        text: 'Esta acción actualizará los saldos de las cuentas',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, procesar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`/transferencias/${id}/procesar`, {_token: '{{ csrf_token() }}'}, function(response) {
                Swal.fire('¡Procesada!', response.message, 'success').then(() => location.reload());
            });
        }
    });
}

function cancelarTransferencia(id) {
    Swal.fire({
        title: '¿Cancelar transferencia?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`/transferencias/${id}/cancelar`, {_token: '{{ csrf_token() }}'}, function(response) {
                Swal.fire('¡Cancelada!', response.message, 'success').then(() => location.reload());
            });
        }
    });
}

function imprimirComprobante(id) {
    window.open(`/transferencias/${id}/comprobante`, '_blank');
}

function actualizarResumen() {
    $.get('{{ route("contador.bancos.transferencias") }}', function(data) {
        $('#totalTransferido').text(data.total_transferido.toFixed(2));
        $('#totalProcesadas').text(data.total_procesadas);
        $('#totalPendientes').text(data.total_pendientes);
    });
}
</script>
@endpush
