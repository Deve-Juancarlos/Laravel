@extends('layouts.app')

@section('title', 'Registrar Cobranza - Paso 3')

@section('page-title', 'Asistente de Registro de Cobranzas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.flujo.cobranzas.paso1') }}">Paso 1</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.flujo.cobranzas.paso2') }}">Paso 2</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paso 3: Aplicar Pago</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        
        {{-- @include('cobranzas.flujo.partials._wizard_steps', ['paso_actual' => 3]) --}}

        <div class="card shadow">
            <div class="card-header">
                 <h5 class="card-title m-0">
                    <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>
                    Paso 3: Aplicar el Pago
                </h5>
            </div>
            
            <form action="{{ route('contador.flujo.cobranzas.paso3') }}" method="POST" id="formPaso3">
                @csrf
                <div class="card-body">
                    <p class="text-muted">
                        Selecciona las facturas pendientes de <strong>{{ $cliente->Razon }}</strong> a las que se aplicará el pago.
                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="btn_auto_aplicar">
                            <i class="fas fa-magic me-1"></i> Aplicar Automáticamente (Antiguas primero)
                        </button>
                    </p>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover table-bordered">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Documento</th>
                                    <th>Tipo</th>
                                    <th>Fecha Emisión</th>
                                    <th>Fecha Venc.</th>
                                    <th class="text-end">Total Deuda</th>
                                    <th class="text-end">Saldo Pendiente</th>
                                    <th style="width: 200px;" class="text-end">Monto a Aplicar (S/)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($facturasPendientes as $factura)
                                <tr>
                                    <td><strong>{{ $factura->Documento }}</strong></td>
                                    <td>{{ $factura->Tipo }}</td>
                                    <td>{{ $factura->FechaEmision->format('d/m/Y') }}</td>
                                    <td class="text-danger">{{ $factura->FechaVencimiento->format('d/m/Y') }}</td>
                                    <td class="text-end">S/ {{ number_format($factura->Importe, 2) }}</td>
                                    <td class="text-end fw-bold">S/ {{ number_format($factura->Saldo, 2) }}</td>
                                    <td>
                                        <input type="number"
                                               name="aplicaciones[{{ $factura->composite_key }}]"
                                               class="form-control form-control-sm text-end input-aplicar"
                                               data-saldo-max="{{ $factura->Saldo }}"
                                               step="0.01"
                                               min="0"
                                               max="{{ $factura->Saldo }}"
                                               placeholder="0.00">
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted p-4">
                                        Este cliente no tiene deudas pendientes en CtaCliente.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer">
                    <!-- Botones de navegación -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('contador.flujo.cobranzas.paso2') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Atrás
                        </a>
                        <button type="submit" class="btn btn-primary" id="btn_siguiente_paso3" disabled>
                            Siguiente <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    
    // Convertimos a número desde el data-attribute (más seguro)
    const totalPagado = parseFloat({{ $pago['monto_pagado'] }});
    let totalAplicado = 0;
    let restantePorAplicar = totalPagado;

    console.log('Total Pagado:', totalPagado); // Debug

    // Elementos del DOM
    const $inputs = $('.input-aplicar');
    const $btnSiguiente = $('#btn_siguiente_paso3');

    // Función principal para recalcular
    function recalcularTotales() {
        totalAplicado = 0;
        
        $inputs.each(function() {
            let valor = parseFloat($(this).val()) || 0;
            const saldoMax = parseFloat($(this).data('saldo-max')) || 0;

            // Validación: no aplicar más del saldo de la factura
            if (valor > saldoMax) {
                valor = saldoMax;
                $(this).val(saldoMax.toFixed(2));
            }
            
            totalAplicado += valor;
        });
        
        // Si el total aplicado excede el pago, mostrar advertencia
        if (totalAplicado > totalPagado) {
            Swal.fire({
                icon: 'warning',
                title: 'Monto excedido',
                text: `El total aplicado (S/ ${totalAplicado.toFixed(2)}) excede el monto pagado (S/ ${totalPagado.toFixed(2)}). Ajuste los montos.`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
        
        restantePorAplicar = totalPagado - totalAplicado;

        // Solo habilitar el botón si coincide exactamente (con tolerancia de 1 centavo)
        if (Math.abs(totalAplicado - totalPagado) <= 0.01 && totalAplicado > 0) {
            $btnSiguiente.prop('disabled', false);
        } else {
            $btnSiguiente.prop('disabled', true);
        }
    }

    // Event Listeners
    $inputs.on('input', recalcularTotales);

    // Botón de Auto-Aplicar (FIFO - Primero en entrar, primero en salir)
    $('#btn_auto_aplicar').on('click', function() {
        let montoRestanteAuto = totalPagado;
        
        $inputs.each(function() {
            if (montoRestanteAuto <= 0.005) {
                $(this).val('');
                return;
            }
            
            const saldoFactura = parseFloat($(this).data('saldo-max')) || 0;
            
            if (montoRestanteAuto >= saldoFactura) {
                // Paga la factura completa
                $(this).val(saldoFactura.toFixed(2));
                montoRestanteAuto -= saldoFactura;
            } else {
                // Paga lo que queda
                $(this).val(montoRestanteAuto.toFixed(2));
                montoRestanteAuto = 0;
            }
        });
        
        // Disparamos el recálculo manual
        recalcularTotales();
    });

    // Validación final antes de enviar
    $('#formPaso3').on('submit', function(e) {
        // Recalcular antes de enviar
        recalcularTotales();
        
        // Validación 1: Debe haber aplicado algo
        if (totalAplicado < 0.01) {
            e.preventDefault();
            Swal.fire('Error', 'Debe aplicar el pago a al menos una factura.', 'error');
            return false;
        }

        // Validación 2: No puede aplicar más de lo que pagó
        if (totalAplicado > totalPagado + 0.01) {
            e.preventDefault();
            Swal.fire('Error', `El monto total aplicado (S/ ${totalAplicado.toFixed(2)}) excede el monto pagado (S/ ${totalPagado.toFixed(2)}).`, 'error');
            return false;
        }

        // Validación 3: Debe aplicar EXACTAMENTE lo que pagó
        if (Math.abs(totalAplicado - totalPagado) > 0.01) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Monto no coincide',
                html: `Debe aplicar <strong>exactamente</strong> el monto pagado.<br><br>
                       Pagó: <strong>S/ ${totalPagado.toFixed(2)}</strong><br>
                       Aplicó: <strong>S/ ${totalAplicado.toFixed(2)}</strong><br>
                       ${restantePorAplicar > 0 ? 'Falta: <strong class="text-danger">S/ ' + restantePorAplicar.toFixed(2) + '</strong>' : 
                         'Sobra: <strong class="text-danger">S/ ' + Math.abs(restantePorAplicar).toFixed(2) + '</strong>'}`,
                confirmButtonText: 'Entendido'
            });
            return false;
        }

        // Todo OK
        return true;
    });

    // Recalcular al inicio (por si hay old data)
    recalcularTotales();

});
</script>
@endpush