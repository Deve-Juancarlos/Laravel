@use('Illuminate\Support\Str')
@extends('layouts.app')
@section('title', 'Nueva Nota de Crédito - Paso 2')
@section('page-title', 'Nueva Nota de Crédito')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.notas-credito.index') }}">Notas de Crédito</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.notas-credito.create') }}">Paso 1</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paso 2: Configurar</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-11 mx-auto">
        
        {{-- Breadcrumb Visual --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-center flex-fill">
                        <div class="badge bg-success rounded-circle p-3 mb-2">
                            <i class="fas fa-check fa-lg"></i>
                        </div>
                        <p class="mb-0 fw-bold text-success">Paso 1</p>
                        <small class="text-muted">Completado</small>
                    </div>
                    <div class="flex-fill"><hr class="border-2 border-success"></div>
                    <div class="text-center flex-fill">
                        <div class="badge bg-primary rounded-circle p-3 mb-2">
                            <i class="fas fa-edit fa-lg"></i>
                        </div>
                        <p class="mb-0 fw-bold text-primary">Paso 2</p>
                        <small class="text-muted">En Progreso</small>
                    </div>
                    <div class="flex-fill"><hr class="border-2"></div>
                    <div class="text-center flex-fill">
                        <div class="badge bg-secondary rounded-circle p-3 mb-2">
                            <i class="fas fa-save fa-lg"></i>
                        </div>
                        <p class="mb-0 text-muted">Paso 3</p>
                        <small class="text-muted">Pendiente</small>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('contador.notas-credito.store') }}" method="POST" id="formNotaCredito">
            @csrf
            
            {{-- DOCUMENTO ORIGINAL --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title m-0"><i class="fas fa-file-invoice me-2 text-primary"></i>Documento Original</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><div class="p-3 bg-light rounded">
                            <p class="mb-1 text-muted small"><i class="fas fa-user me-1"></i> Cliente:</p>
                            <p class="fw-bold mb-0">{{ $carrito['cliente']->Razon }}</p>
                        </div></div>
                        <div class="col-md-2"><div class="p-3 bg-light rounded">
                            <p class="mb-1 text-muted small"><i class="fas fa-id-card me-1"></i> RUC/DNI:</p>
                            <p class="fw-bold mb-0">{{ $carrito['cliente']->Documento }}</p>
                        </div></div>
                        <div class="col-md-2"><div class="p-3 bg-light rounded">
                            <p class="mb-1 text-muted small"><i class="fas fa-file-alt me-1"></i> Documento:</p>
                            <p class="fw-bold mb-0 text-primary">{{ $carrito['factura_original']->Numero }}</p>
                        </div></div>
                        <div class="col-md-2"><div class="p-3 bg-success bg-opacity-10 rounded">
                            <p class="mb-1 text-muted small"><i class="fas fa-dollar-sign me-1"></i> Total Fact.:</p>
                            <p class="fw-bold mb-0 text-success">S/ {{ number_format($carrito['factura_original']->Total, 2) }}</p>
                        </div></div>
                        {{-- ¡AQUÍ ESTÁ LA VALIDACIÓN DE SALDO! --}}
                        <div class="col-md-2"><div class="p-3 bg-danger bg-opacity-10 rounded">
                            <p class="mb-1 text-muted small"><i class="fas fa-exclamation-triangle me-1"></i> Saldo Pendiente:</p>
                            <p class="fw-bold mb-0 text-danger fs-6">S/ {{ number_format($saldo_maximo, 2) }}</p>
                        </div></div>
                    </div>
                    <div class="form-text text-danger mt-2">
                        <i class="fas fa-info-circle me-1"></i> El monto total de la Nota de Crédito no puede exceder el Saldo Pendiente.
                    </div>
                </div>
            </div>

            {{-- CONFIGURACIÓN DE LA NC --}}
            <div class="card shadow">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="card-title m-0 text-white"><i class="fas fa-cogs me-2"></i>Configuración de la Nota de Crédito</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="tipo_operacion" class="form-label fw-bold fs-5"><i class="fas fa-list-alt text-primary me-2"></i>Tipo de Operación <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg @error('tipo_operacion') is-invalid @enderror" id="tipo_operacion" name="tipo_operacion" required>
                                <option value="">-- Seleccione el motivo de la NC --</option>
                                <option value="devolucion" {{ old('tipo_operacion') == 'devolucion' ? 'selected' : '' }}>1. Devolución de Mercadería (Afecta Stock)</option>
                                <option value="descuento" {{ old('tipo_operacion') == 'descuento' ? 'selected' : '' }}>2. Descuento o Bonificación (No afecta Stock)</option>
                            </select>
                            @error('tipo_operacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="motivo_glosa" class="form-label fw-bold fs-5"><i class="fas fa-comment-alt text-warning me-2"></i>Glosa / Motivo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg @error('motivo_glosa') is-invalid @enderror" id="motivo_glosa" name="motivo_glosa" value="{{ old('motivo_glosa') }}" placeholder="Ej: Devolución por productos vencidos" maxlength="255" required>
                            @error('motivo_glosa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <hr class="my-4">

                    {{-- SECCIÓN DEVOLUCIÓN --}}
                    <div id="div_devolucion" style="display: none;">
                        <div class="alert alert-info border-info"><div class="d-flex align-items-start">
                            <i class="fas fa-box-open fa-2x me-3"></i>
                            <div><h6 class="alert-heading mb-2">Devolución de Mercadería</h6>
                                <p class="mb-0 small">• Ingrese la cantidad a devolver (el sistema incrementará el stock)<br>• Se calculará el IGV (18%) sobre el monto devuelto</p>
                            </div>
                        </div></div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 8%;" class="text-center">#</th>
                                        <th style="width: 40%;">Producto</th>
                                        <th class="text-center" style="width: 12%;">Cant. Orig.</th>
                                        <th class="text-end" style="width: 13%;">Precio Unit.</th>
                                        <th class="text-end" style="width: 13%;">Subtotal</th>
                                        <th style="width: 14%;">Devolver</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detalles as $item)
                                    <tr>
                                        <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                                        <td>
                                            <strong class="d-block">{{ $item->ProductoNombre }}</strong>
                                            <small class="text-muted"><span class="badge bg-dark">{{ $item->Codpro }}</span> <span class="ms-2">Lote: <strong>{{ $item->Lote }}</strong></span></small>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info fs-6 px-3">{{ number_format($item->Cantidad, 0) }}</span></td>
                                        <td class="text-end fw-semibold">S/ {{ number_format($item->Precio, 2) }}</td>
                                        <td class="text-end fw-bold">S/ {{ number_format($item->Subtotal, 2) }}</td>
                                        {{-- Hidden inputs --}}
                                        <input type="hidden" name="items[{{$loop->index}}][codpro]" value="{{ $item->Codpro }}">
                                        <input type="hidden" name="items[{{$loop->index}}][lote]" value="{{ $item->Lote }}">
                                        <input type="hidden" name="items[{{$loop->index}}][vencimiento]" value="{{ $item->Vencimiento }}">
                                        <input type="hidden" name="items[{{$loop->index}}][precio]" value="{{ $item->Precio }}">
                                        <input type="hidden" name="items[{{$loop->index}}][almacen]" value="{{ $item->Almacen ?? 1 }}">
                                        <td>
                                            <input type="number" name="items[{{$loop->index}}][cantidad]" class="form-control item-cantidad text-center fw-bold" value="{{ old('items.'.$loop->index.'.cantidad', 0) }}" step="1" min="0" max="{{ (int)$item->Cantidad }}" placeholder="0">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- SECCIÓN DESCUENTO --}}
                    <div id="div_descuento" style="display: none;">
                        <div class="alert alert-warning border-warning"><div class="d-flex align-items-start">
                            <i class="fas fa-percent fa-2x me-3"></i>
                            <div><h6 class="alert-heading mb-2">Descuento Comercial</h6>
                                <p class="mb-0 small">• Ingrese el monto <strong>total (con IGV)</strong> que se descontará<br>• Este descuento reducirá directamente la deuda y <strong>no afectará</strong> el inventario</p>
                            </div>
                        </div></div>
                        <div class="row">
                            <div class="col-md-5">
                                <label for="monto_descuento" class="form-label fw-bold fs-5"><i class="fas fa-money-bill-wave text-success me-2"></i>Monto Total del Descuento (con IGV) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light fw-bold">S/</span>
                                    {{-- ¡CORREGIDO! El name es 'monto_descuento' y el max es 'saldo_maximo' --}}
                                    <input type="number" 
                                           class="form-control @error('monto_descuento') is-invalid @enderror" 
                                           id="monto_descuento" 
                                           name="monto_descuento" 
                                           step="0.01" 
                                           min="0.01" 
                                           max="{{ $saldo_maximo }}"
                                           value="{{ old('monto_descuento', '') }}"
                                           placeholder="0.00">
                                    @error('monto_descuento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <small class="form-text text-muted">Máximo permitido: <strong>S/ {{ number_format($saldo_maximo, 2) }}</strong> (Saldo Pendiente)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('contador.notas-credito.create') }}" class="btn btn-secondary btn-lg"><i class="fas fa-times me-1"></i> Cancelar</a>
                        <button type="submit" class="btn btn-success btn-lg px-5"><i class="fas fa-save me-2"></i> Generar Nota de Crédito</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo_operacion');
    const divDevolucion = document.getElementById('div_devolucion');
    const divDescuento = document.getElementById('div_descuento');
    
    function actualizarVista() {
        const tipo = tipoSelect.value;
        
        if (tipo === 'devolucion') {
            divDevolucion.style.display = 'block';
            divDescuento.style.display = 'none';
            divDevolucion.querySelectorAll('input').forEach(el => el.disabled = false);
            divDescuento.querySelectorAll('input').forEach(el => el.disabled = true);
        } else if (tipo === 'descuento') {
            divDevolucion.style.display = 'none';
            divDescuento.style.display = 'block';
            divDevolucion.querySelectorAll('input').forEach(el => el.disabled = true);
            divDescuento.querySelectorAll('input').forEach(el => el.disabled = false);
        } else {
            divDevolucion.style.display = 'none';
            divDescuento.style.display = 'none';
            divDevolucion.querySelectorAll('input').forEach(el => el.disabled = true);
            divDescuento.querySelectorAll('input').forEach(el => el.disabled = true);
        }
    }
    
    tipoSelect.addEventListener('change', actualizarVista);
    actualizarVista();
    
    document.querySelector('form').addEventListener('submit', function(e) {
        const tipo = tipoSelect.value;
        
        if (tipo === 'devolucion') {
            const cantidades = Array.from(document.querySelectorAll('.item-cantidad')).map(input => parseFloat(input.value) || 0);
            if (cantidades.every(c => c === 0)) {
                e.preventDefault();
                alert('Debe especificar al menos una cantidad a devolver.');
                return false;
            }
        } else if (tipo === 'descuento') {
            const monto = parseFloat(document.getElementById('monto_descuento').value) || 0;
            const max = parseFloat(document.getElementById('monto_descuento').max) || 0;
            if (monto <= 0) {
                e.preventDefault();
                alert('Debe ingresar un monto válido para el descuento.');
                document.getElementById('monto_descuento').focus();
                return false;
            }
            if (monto > max) {
                 e.preventDefault();
                alert('El monto de descuento no puede ser mayor al saldo pendiente de la factura (S/ ' + max.toFixed(2) + ').');
                document.getElementById('monto_descuento').focus();
                return false;
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
    .badge.rounded-circle { width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; }
    .table tbody tr:hover { background-color: rgba(102, 126, 234, 0.05); }
    .item-cantidad { font-size: 1.1rem; border: 2px solid #dee2e6; transition: all 0.3s; }
    .item-cantidad:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
</style>
@endpush