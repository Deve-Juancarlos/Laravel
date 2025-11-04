@extends('layouts.app')

@section('title', 'Registrar Compra')
@section('page-title', 'Registrar Ingreso de Compra')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.compras.index') }}">Compras</a></li>
    <li class="breadcrumb-item active" aria-current="page">Registrar Compra</li>
@endsection

@push('styles')
<style>
    .item-grid { display: grid; grid-template-columns: 3fr 1fr 1fr 1.5fr 1.5fr; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid #eee; }
    .item-grid-header { font-weight: 600; font-size: 0.8rem; color: #666; }
    .item-grid-body { font-size: 0.9rem; }
</style>
@endpush

@section('content')
<form action="{{ route('contador.compras.registro.store') }}" method="POST" id="formRegistroCompra">
    
    {{-- ¡¡ESTA LÍNEA ES LA QUE EVITA EL ERROR 419!! --}}
    @csrf
    
    {{-- Datos ocultos que necesitamos enviar --}}
    <input type="hidden" name="orden_id" value="{{ $ordenCompra->Id }}">
    <input type="hidden" name="proveedor_id" value="{{ $proveedor->CodProv }}">
    <input type="hidden" name="subtotal" id="hidden_subtotal">
    <input type="hidden" name="igv" id="hidden_igv">
    <input type="hidden" name="total" id="hidden_total">

    <div class="row">
        {{-- Columna de la izquierda: Detalles del Ingreso --}}
        <div class="col-lg-8">
            
            {{-- 1. Información de la Factura (Ingresado por el usuario) --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title m-0"><i class="fas fa-file-invoice me-2"></i>Paso 1: Datos de la Factura del Proveedor</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nro_factura" class="form-label fw-bold">N° Factura / Guía del Proveedor</label>
                            <input type="text" class="form-control" id="nro_factura" name="nro_factura" 
                                   value="{{ old('nro_factura') }}" required>
                        </div>
                        <div class="col-md-6">
                             <label for="almacen_id" class="form-label fw-bold">Almacén de Destino</label>
                            <select class="form-select" id="almacen_id" name="almacen_id" required>
                                <option value="">Seleccione...</option>
                                <option value="1" selected>ALMACEN PRINCIPAL</option>
                                <option value="2">ALMACEN 2</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_emision" class="form-label fw-bold">Fecha de Emisión (Factura)</label>
                            <input type="date" class="form-control" id="fecha_emision" name="fecha_emision" 
                                   value="{{ old('fecha_emision', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_vencimiento" class="form-label fw-bold">Fecha de Vencimiento (Pago)</label>
                            <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" 
                                   value="{{ old('fecha_vencimiento', now()->addDays(30)->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Detalle de Productos (Cargado de la O/C) --}}
            <div class="card shadow mb-4">
                 <div class="card-header">
                    <h5 class="card-title m-0"><i class="fas fa-boxes me-2"></i>Paso 2: Items a Ingresar (Lote y Vencimiento)</h5>
                </div>
                <div class="card-body">
                    
                    {{-- Encabezado de la grilla --}}
                    <div class="item-grid item-grid-header d-none d-md-grid">
                        <div>Producto</div>
                        <div class="text-end">Cant.</div>
                        <div class="text-end">Costo U.</div>
                        <div>Lote</div>
                        <div>Fec. Vencimiento</div>
                    </div>

                    {{-- Cuerpo de la grilla --}}
                    @foreach($detalles as $index => $item)
                    <div class="item-grid item-grid-body">
                        {{-- Datos ocultos del item --}}
                        <input type="hidden" name="items[{{$index}}][codpro]" value="{{ $item->CodPro }}">
                        
                        <div>
                            <span class="d-md-none item-grid-header">Producto:</span>
                            <strong>{{ $item->Nombre }}</strong>
                        </div>
                        <div class="text-end">
                             <span class="d-md-none item-grid-header">Cantidad:</span>
                            <input type="number" class="form-control form-control-sm text-end item-calc" 
                                   name="items[{{$index}}][cantidad]" value="{{ $item->Cantidad }}" step="1" required>
                        </div>
                        <div class="text-end">
                            <span class="d-md-none item-grid-header">Costo U.:</span>
                            <input type="number" class="form-control form-control-sm text-end item-calc" 
                                   name="items[{{$index}}][costo]" value="{{ number_format($item->CostoUnitario, 4, '.', '') }}" step="0.0001" required>
                        </div>
                        <div>
                            <span class="d-md-none item-grid-header">Lote:</span>
                            <input type="text" class="form-control form-control-sm" 
                                   name="items[{{$index}}][lote]" placeholder="Lote" required>
                        </div>
                         <div>
                            <span class="d-md-none item-grid-header">Vencimiento:</span>
                            <input type="date" class="form-control form-control-sm" 
                                   name="items[{{$index}}][vencimiento]" placeholder="Vencimiento" required>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Columna de la derecha: Resumen --}}
        <div class="col-lg-4">
            <div class="card shadow sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h5 class="card-title m-0">Resumen</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>O/C de Referencia:</strong><br>
                        {{ $ordenCompra->Serie }}-{{ $ordenCompra->Numero }}
                    </div>
                    <div class="alert alert-secondary">
                        <strong>Proveedor:</strong><br>
                        {{ $proveedor->RazonSocial }}<br>
                        <small>{{ $proveedor->Ruc }}</small>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5 mb-2">
                        <span>SUBTOTAL:</span>
                        <span class="fw-bold" id="label_subtotal">S/ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between fs-5 mb-2">
                        <span>IGV (18%):</span>
                        <span class="fw-bold" id="label_igv">S/ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between fs-4 fw-bolder text-primary mt-2 pt-2 border-top">
                        <span>TOTAL FACTURA:</span>
                        <span id="label_total">S/ 0.00</span>
                    </div>
                </div>
                <div class="card-footer p-3">
                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-check-circle me-2"></i>
                        Confirmar y Registrar Compra
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
    
    function formatCurrency(value) {
        return 'S/ ' + parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function calcularTotales() {
        let subtotal = 0;
        $('.item-grid-body').each(function() {
            const cantidad = parseFloat($(this).find('input[name*="[cantidad]"]').val()) || 0;
            const costo = parseFloat($(this).find('input[name*="[costo]"]').val()) || 0;
            subtotal += cantidad * costo;
        });

        const igv = subtotal * 0.18;
        const total = subtotal + igv;

        $('#label_subtotal').text(formatCurrency(subtotal));
        $('#label_igv').text(formatCurrency(igv));
        $('#label_total').text(formatCurrency(total));

        $('#hidden_subtotal').val(subtotal.toFixed(4));
        $('#hidden_igv').val(igv.toFixed(4));
        $('#hidden_total').val(total.toFixed(4));
    }

    $('.item-calc').on('change keyup', function() {
        calcularTotales();
    });
    
    calcularTotales();

    $('input[name*="[vencimiento]"]').each(function() {
        $(this).attr('min', new Date().toISOString().split('T')[0]);
    });

});
</script>
@endpush