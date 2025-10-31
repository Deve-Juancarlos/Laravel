@extends('layouts.app')

@section('title', 'Registrar Cobranza - Paso 3')

@section('page-title', 'Asistente de Registro de Cobranzas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.flujo.cobranzas.paso1') }}">Paso 1</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.flujo.cobranzas.paso2') }}">Paso 2</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paso 3: Aplicar Pago</li>
@endsection

@section('content')

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
            
            <form action="{{ route('contador.flujo.cobranzas.handlePaso3') }}" method="POST" id="formPaso3">
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
                                        {{-- ¡CAMBIO CRÍTICO! El name ahora usa la composite_key --}}
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
                
                <div class="card-footer d-flex justify-content-between">
                    {{-- ... (Tus botones de 'Atrás' y 'Siguiente' se quedan igual) ... --}}
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    
    // Convertimos a número
    const totalPagado = parseFloat({{ $pago['monto_pagado'] }});
    let totalAplicado = 0;
    let restantePorAplicar = totalPagado;

    // Elementos del DOM
    const $inputs = $('.input-aplicar');
    const $totalAplicadoEl = $('#total_aplicado');
    const $restantePorAplicarEl = $('#restante_por_aplicar');
    const $btnSiguiente = $('#btn_siguiente_paso3');
    const $opcionAdelanto = $('#opcion_pago_adelantado');
    const $saldoAdelanto = $('#saldo_adelanto');

    function formatearMoneda(numero) {
        return 'S/ ' + numero.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

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
            
            // Validación: no aplicar más de lo que se pagó
            if (totalAplicado + valor > totalPagado) {
                valor = totalPagado - totalAplicado;
                $(this).val(valor.toFixed(2));
                // Deshabilitar el resto de inputs
                $inputs.not(this).filter(function() { return !this.value; }).prop('disabled', true);
            } else {
                 $inputs.prop('disabled', false);
            }
            
            totalAplicado += valor;
        });
        
        restantePorAplicar = totalPagado - totalAplicado;

        // Actualizar los KPIs
        $totalAplicadoEl.text(formatearMoneda(totalAplicado));
        $restantePorAplicarEl.text(formatearMoneda(restantePorAplicar));

        // Habilitar/Deshabilitar botón siguiente
        // Se puede pasar si se aplicó algo O si se va a guardar como adelanto
        if (totalAplicado > 0) {
            $btnSiguiente.prop('disabled', false);
        } else {
            $btnSiguiente.prop('disabled', true);
        }

        // Mostrar opción de pago adelantado si sobra dinero
        if (restantePorAplicar > 0.005) { // Usamos 0.005 por temas de redondeo
            $opcionAdelanto.show();
            $saldoAdelanto.text(formatearMoneda(restantePorAplicar));
            // Si el checkbox está marcado, también se puede pasar
            $btnSiguiente.prop('disabled', false);
        } else {
            $opcionAdelanto.hide();
            $('#guardar_como_adelanto').prop('checked', false);
        }
    }

    // Event Listeners
    $inputs.on('input', recalcularTotales);
    $('#guardar_como_adelanto').on('change', recalcularTotales);

    // Botón de Auto-Aplicar (FIFO - Primero en entrar, primero en salir)
    $('#btn_auto_aplicar').on('click', function() {
        let montoRestanteAuto = totalPagado;
        $inputs.prop('disabled', false);
        
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
        if (totalAplicado < 0.005 && !$('#guardar_como_adelanto').is(':checked')) {
            e.preventDefault();
            Swal.fire('Error', 'Debe aplicar el pago a al menos una factura o guardarlo como adelanto.', 'error');
            return;
        }

        if (Math.abs(totalAplicado + ($('#guardar_como_adelanto').is(':checked') ? restantePorAplicar : 0) - totalPagado) > 0.01) {
             e.preventDefault();
             Swal.fire('Error', 'El monto aplicado no coincide con el total pagado. Por favor, revise los montos.', 'error');
        }
    });

    // Recalcular al inicio (por si hay old data)
    recalcularTotales();

});
</script>
@endpush