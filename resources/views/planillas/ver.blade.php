<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planilla {{ $planilla->CodPlanilla }} - Sistema Farmacéutico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .header-planilla {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            border-left: 4px solid #007bff;
        }
        .documento-row {
            border-left: 4px solid #28a745;
            transition: all 0.3s ease;
        }
        .documento-row:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        .estado-pendiente { border-left-color: #ffc107 !important; }
        .estado-proceso { border-left-color: #17a2b8 !important; }
        .estado-pagado { border-left-color: #28a745 !important; }
        .accion-rapida {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .resumen-estado {
            text-align: center;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="header-planilla">
            <div class="row">
                <div class="col-md-8">
                    <h2><i class="fas fa-file-invoice"></i> Planilla {{ $planilla->CodPlanilla }}</h2>
                    <p class="mb-0">Gestión y seguimiento de cobranza</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <a href="{{ route('planillas-cobranza.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        @if($planilla->Estado == 1)
                        <button type="button" class="btn btn-warning" onclick="confirmarPlanilla()">
                            <i class="fas fa-check"></i> Confirmar
                        </button>
                        @endif
                        <button type="button" class="btn btn-outline-light" onclick="imprimirPlanilla()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Información de la Planilla -->
            <div class="col-md-4">
                <div class="info-card">
                    <h5><i class="fas fa-info-circle text-primary"></i> Información General</h5>
                    
                    <div class="mb-3">
                        <strong>Estado:</strong><br>
                        @if($planilla->Estado == 1)
                            <span class="badge bg-warning">Pendiente</span>
                        @elseif($planilla->Estado == 2)
                            <span class="badge bg-info">En Proceso</span>
                        @elseif($planilla->Estado == 3)
                            <span class="badge bg-success">Completada</span>
                        @else
                            <span class="badge bg-danger">Cancelada</span>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <strong>Cliente:</strong><br>
                        {{ $planilla->cliente_nombre }}<br>
                        <small class="text-muted">{{ $planilla->RucDni }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Vendedor:</strong><br>
                        {{ $planilla->vendedor_nombre }}
                    </div>
                    
                    <div class="mb-3">
                        <strong>Fecha Creación:</strong><br>
                        {{ \Carbon\Carbon::parse($planilla->Fecha)->format('d/m/Y H:i') }}
                    </div>
                    
                    <div class="mb-3">
                        <strong>Fecha Vencimiento:</strong><br>
                        {{ \Carbon\Carbon::parse($planilla->FechaVence)->format('d/m/Y') }}
                        @if(\Carbon\Carbon::parse($planilla->FechaVence)->isPast())
                            <span class="badge bg-danger">Vencida</span>
                        @elseif(\Carbon\Carbon::parse($planilla->FechaVence)->diffInDays() <= 7)
                            <span class="badge bg-warning">Por vencer</span>
                        @endif
                    </div>
                    
                    @if($planilla->Observaciones)
                    <div class="mb-3">
                        <strong>Observaciones:</strong><br>
                        <div class="alert alert-info">
                            {{ $planilla->Observaciones }}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Resumen de Estado -->
                <div class="info-card mt-3">
                    <h5><i class="fas fa-chart-pie text-success"></i> Resumen</h5>
                    @php
                        $totalDocumentos = $detalles->count();
                        $documentosPagados = $detalles->where('Estado', 3)->count();
                        $montoTotal = $detalles->sum('Importe');
                        $montoCobrado = $detalles->where('Estado', 3)->sum('Importe');
                        $porcentajeCobro = $montoTotal > 0 ? round(($montoCobrado / $montoTotal) * 100, 1) : 0;
                    @endphp
                    
                    <div class="resumen-estado">
                        <h3>{{ $porcentajeCobro }}%</h3>
                        <p class="text-muted">Cobrado</p>
                        <div class="progress mb-2">
                            <div class="progress-bar" style="width: {{ $porcentajeCobro }}%"></div>
                        </div>
                        <small>S/ {{ number_format($montoCobrado, 2) }} de S/ {{ number_format($montoTotal, 2) }}</small>
                    </div>
                </div>
            </div>

            <!-- Documentos de la Planilla -->
            <div class="col-md-8">
                <!-- Acciones Rápidas -->
                @if($planilla->Estado == 1)
                <div class="accion-rapida">
                    <h6><i class="fas fa-bolt text-primary"></i> Acciones Rápidas</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="mostrarModalAgregarCliente()">
                                <i class="fas fa-user-plus"></i> Agregar Cliente
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-success w-100" onclick="procesarPlanilla()">
                                <i class="fas fa-play"></i> Procesar
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="eliminarPlanilla()">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-list text-primary"></i> Documentos en la Planilla</h5>
                            <span class="badge bg-primary">{{ $totalDocumentos }} documentos</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($detalles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fas fa-file-alt"></i> Documento</th>
                                        <th><i class="fas fa-calendar"></i> Fecha</th>
                                        <th><i class="fas fa-dollar-sign"></i> Importe</th>
                                        <th><i class="fas fa-balance-scale"></i> Saldo</th>
                                        <th><i class="fas fa-info-circle"></i> Estado</th>
                                        <th><i class="fas fa-cogs"></i> Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detalles as $detalle)
                                    <tr class="documento-row estado-{{ $detalle->Estado == 3 ? 'pagado' : ($detalle->Estado == 2 ? 'proceso' : 'pendiente') }}">
                                        <td>
                                            <strong>{{ $detalle->NumDoc }}</strong>
                                        </td>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($detalle->FechaDoc)->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <strong>S/ {{ number_format($detalle->Importe, 2) }}</strong>
                                        </td>
                                        <td>
                                            <strong class="text-{{ $detalle->Saldo > 0 ? 'danger' : 'success' }}">
                                                S/ {{ number_format($detalle->Saldo, 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            @if($detalle->Estado == 1)
                                                <span class="badge bg-warning">Pendiente</span>
                                            @elseif($detalle->Estado == 2)
                                                <span class="badge bg-info">Parcial</span>
                                            @elseif($detalle->Estado == 3)
                                                <span class="badge bg-success">Pagado</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($detalle->Estado != 3 && $planilla->Estado == 1)
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="mostrarModalPago('{{ $detalle->NumDoc }}', {{ $detalle->Saldo }})"
                                                    title="Registrar Pago">
                                                <i class="fas fa-money-bill"></i>
                                            </button>
                                            @endif
                                            <a href="#" class="btn btn-sm btn-outline-primary" title="Ver Documento">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2">TOTALES:</th>
                                        <th>S/ {{ number_format($montoTotal, 2) }}</th>
                                        <th>S/ {{ number_format($detalles->sum('Saldo'), 2) }}</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay documentos</h5>
                            <p class="text-muted">Esta planilla no tiene documentos asociados</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Cliente -->
    <div class="modal fade" id="modalAgregarCliente" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Cliente a la Planilla</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAgregarCliente">
                        <div class="mb-3">
                            <label class="form-label">Buscar Cliente</label>
                            <input type="text" class="form-control" id="buscarClienteModal" placeholder="Nombre o DNI">
                        </div>
                        <div id="resultadosClienteModal"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Pago -->
    <div class="modal fade" id="modalPago" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formPago">
                        <input type="hidden" id="documentoPago">
                        <div class="mb-3">
                            <label class="form-label">Documento</label>
                            <input type="text" class="form-control" id="documentoPagoDisplay" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Importe a Pagar</label>
                            <input type="number" class="form-control" id="importePago" step="0.01" min="0.01">
                            <small class="text-muted">Saldo disponible: S/ <span id="saldoDisponible"></span></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de Pago</label>
                            <input type="date" class="form-control" id="fechaPago" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Método de Pago</label>
                            <select class="form-select" id="metodoPago">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Depósito">Depósito</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="registrarPago()">
                        <i class="fas fa-check"></i> Registrar Pago
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function mostrarModalAgregarCliente() {
            new bootstrap.Modal(document.getElementById('modalAgregarCliente')).show();
        }

        function mostrarModalPago(documento, saldo) {
            $('#documentoPago').val(documento);
            $('#documentoPagoDisplay').val(documento);
            $('#saldoDisponible').text(saldo.toFixed(2));
            $('#importePago').attr('max', saldo);
            new bootstrap.Modal(document.getElementById('modalPago')).show();
        }

        function registrarPago() {
            const documento = $('#documentoPago').val();
            const importe = parseFloat($('#importePago').val());
            const fecha = $('#fechaPago').val();
            const metodo = $('#metodoPago').val();

            if (!importe || importe <= 0) {
                alert('Ingrese un importe válido');
                return;
            }

            $.ajax({
                url: `/contabilidad/planillas-cobranza/planilla/{{ $planilla->CodPlanilla }}/registrar-pago`,
                method: 'POST',
                data: {
                    num_doc: documento,
                    importe_pago: importe,
                    fecha_pago: fecha,
                    metodo_pago: metodo,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error al registrar el pago');
                }
            });
        }

        function confirmarPlanilla() {
            if (confirm('¿Está seguro de confirmar esta planilla? Solo se puede hacer si todos los documentos están pagados.')) {
                $.ajax({
                    url: `/contabilidad/planillas-cobranza/planilla/{{ $planilla->CodPlanilla }}/confirmar`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error al confirmar la planilla');
                    }
                });
            }
        }

        function procesarPlanilla() {
            if (confirm('¿Desea procesar esta planilla?')) {
                window.location.href = `/contabilidad/planillas-cobranza/planilla/{{ $planilla->CodPlanilla }}/procesar`;
            }
        }

        function eliminarPlanilla() {
            if (confirm('¿Está seguro de eliminar esta planilla? Esta acción no se puede deshacer.')) {
                $.ajax({
                    url: `/contabilidad/planillas-cobranza/planilla/{{ $planilla->CodPlanilla }}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.href = '{{ route("planillas-cobranza.index") }}';
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error al eliminar la planilla');
                    }
                });
            }
        }

        function imprimirPlanilla() {
            window.print();
        }

        // Búsqueda de clientes en modal
        $('#buscarClienteModal').on('input', function() {
            const termino = $(this).val();
            if (termino.length >= 2) {
                $.ajax({
                    url: '/contabilidad/facturacion/buscar-cliente',
                    method: 'POST',
                    data: {
                        termino: termino,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success && response.clientes.length > 0) {
                            let html = '<div class="list-group">';
                            response.clientes.forEach(function(cliente) {
                                html += `
                                    <a href="#" class="list-group-item list-group-item-action" onclick="seleccionarClienteModal('${cliente.Codclie}')">
                                        <strong>${cliente.Razon}</strong><br>
                                        <small>${cliente.RucDni || 'Sin documento'}</small>
                                    </a>
                                `;
                            });
                            html += '</div>';
                            $('#resultadosClienteModal').html(html);
                        } else {
                            $('#resultadosClienteModal').html('<p class="text-muted">No se encontraron clientes</p>');
                        }
                    }
                });
            } else {
                $('#resultadosClienteModal').empty();
            }
        });

        function seleccionarClienteModal(clienteId) {
            $.ajax({
                url: `/contabilidad/planillas-cobranza/planilla/{{ $planilla->CodPlanilla }}/agregar-cliente`,
                method: 'POST',
                data: {
                    cliente_id: clienteId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error al agregar cliente');
                }
            });
        }
    </script>

    <style>
        @media print {
            .btn, .modal, .accion-rapida, .header-planilla .btn-group {
                display: none !important;
            }
            .container-fluid {
                padding: 0;
            }
            .card {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</body>
</html>