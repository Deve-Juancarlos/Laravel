@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Registrar Pago a Proveedor - Paso 2')
@section('page-title', 'Flujo de Egreso: Aplicar Pago')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.cxp.index') }}">Cuentas por Pagar</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paso 2: Aplicar Pago</li>
@endsection

@section('content')
<form action="{{ route('contador.flujo.egresos.handlePaso2') }}" method="POST" id="formAplicarPago">
    @csrf
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title m-0">Paso 2: Aplicar Pago a Facturas</h5>
                </div>
                <div class="card-body">
                    
                    <div class="alert alert-success">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Pagando a:</strong>
                                <h5 class="m-0">{{ $proveedor->RazonSocial }}</h5>
                            </div>
                            <div class="col-md-3">
                                <strong>Monto Pagado:</strong>
                                <h5 class="m-0" id="totalPagado">S/ {{ number_format($pago['monto_pagado'], 2) }}</h5>
                            </div>
                             <div class="col-md-3">
                                <strong>Restante por Aplicar:</strong>
                                <h5 class="m-0 text-success" id="totalRestante">S/ {{ number_format($pago['monto_pagado'], 2) }}</h5>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-muted">Seleccione las facturas que está pagando:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Factura N°</th>
                                    <th>Emisión</th>
                                    <th>Vencimiento</th>
                                    <th class="text-end">Saldo Pendiente</th>
                                    <th class="text-end" style="width: 200px;">Monto a Aplicar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($facturasPendientes as $factura)
                                <tr>
                                    <td>{{ $factura->Documento }}</td>
                                    <td>{{ $factura->FechaEmision->format('d/m/Y') }}</td>
                                    <td class="fw-bold">{{ $factura->FechaV->format('d/m/Y') }}</td>
                                    <td class="text-end">S/ {{ number_format($factura->Saldo, 2) }}</td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-end monto-aplicar" 
                                               name="aplicaciones[{{ $factura->composite_key }}]"
                                               placeholder="0.00" 
                                               step="0.01" 
                                               min="0" 
                                               max="{{ $factura->Saldo }}">
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted p-4">Este proveedor no tiene facturas pendientes de pago.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div id="alerta-diferencia" class="alert alert-warning" style="display: none;">
                        <strong>Atención:</strong> El monto pagado (S/ <span id="montoPagadoAlert"></span>) no coincide con el total aplicado (S/ <span id="montoAplicadoAlert"></span>).
                        La diferencia de S/ <span id="montoDiferenciaAlert"></span> no se guardará.
                    </div>

                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('contador.flujo.egresos.paso1', ['proveedor_id' => $proveedor->CodProv]) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver (Paso 1)
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        Siguiente: Resumen <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const totalPagado = parseFloat($('#totalPagado').text().replace('S/ ', '').replace(',', ''));
    const $totalRestante = $('#totalRestante');
    const $alerta = $('#alerta-diferencia');

    function calcularRestante() {
        let totalAplicado = 0;
        $('.monto-aplicar').each(function() {
            totalAplicado += parseFloat($(this).val()) || 0;
        });
        
        const restante = totalPagado - totalAplicado;
        $totalRestante.text('S/ ' + restante.toFixed(2));

        if (restante < 0) {
            $totalRestante.removeClass('text-success').addClass('text-danger');
        } else {
            $totalRestante.removeClass('text-danger').addClass('text-success');
        }
       
        if (Math.abs(restante) > 0.01) {
            $('#montoPagadoAlert').text(totalPagado.toFixed(2));
            $('#montoAplicadoAlert').text(totalAplicado.toFixed(2));
            $('#montoDiferenciaAlert').text(restante.toFixed(2));
            $alerta.show();
        } else {
            $alerta.hide();
        }
    }

    $('.monto-aplicar').on('keyup change', calcularRestante);
    calcularRestante();
});
</script>
@endpush