{{-- 
    Este formulario es "inteligente". Sabe si está creando o editando.
    - Si existe $movimiento (pasado desde edit.blade.php), estamos editando.
    - Si no existe $movimiento (pasado desde create.blade.php), estamos creando.
--}}

@php
    // DEFINICIÓN DEFENSIVA DE VARIABLES
    $isCreating = !isset($movimiento);
    
    // Si estamos editando...
    if (!$isCreating) {
        $detalles = $movimiento->detalles ?? collect();
        $detalleCaja = $detalles->firstWhere('cuenta_contable', 'LIKE', '101%');
        $detalleContrapartida = $detalles->firstWhere('cuenta_contable', 'NOT LIKE', '101%');

        $cuentaCajaSeleccionada = old('cuenta_caja', $detalleCaja->cuenta_contable ?? '10101');
        $cuentaContrapartidaSeleccionada = old('cuenta_contrapartida', $detalleContrapartida->cuenta_contable ?? null);
        $monto = old('monto', $movimiento->Monto ?? 0);
        $concepto = old('concepto', $detalleContrapartida->concepto ?? '');
        $docRef = old('documento_referencia', $detalleContrapartida->documento_referencia ?? '');
        $glosa = old('glosa', $asiento->glosa ?? ($movimiento->Documento ?? ''));
        $fecha = old('fecha', isset($movimiento) ? \Carbon\Carbon::parse($movimiento->Fecha)->format('Y-m-d') : now()->format('Y-m-d'));
        $tipoMov = old('tipo', $movimiento->Tipo ?? '');

    } else {
        // Si estamos creando...
        $cuentaCajaSeleccionada = old('cuenta_caja', '10101');
        $cuentaContrapartidaSeleccionada = old('cuenta_contrapartida');
        $monto = old('monto', 0);
        $concepto = old('concepto');
        $docRef = old('documento_referencia');
        $glosa = old('glosa');
        $fecha = old('fecha', now()->format('Y-m-d'));
        $tipoMov = old('tipo');
    }
    
    // El formulario está bloqueado si estamos editando Y ya tiene un asiento.
    $isReadOnly = !$isCreating && $movimiento->asiento_id;
@endphp


@if($isReadOnly)
    <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="fas fa-info-circle fs-5 me-2"></i>
        <div>
            Este movimiento de caja está <strong>vinculado al Asiento Contable #{{ $movimiento->asiento_id }}</strong>. 
            <br>Para editar los montos o cuentas, debe editar el asiento. 
            <a href="{{ route('contador.libro-diario.edit', $movimiento->asiento_id) }}" class="btn btn-sm btn-info-soft ms-2">
                <i class="fas fa-edit me-1"></i> Ir al Asiento
            </a>
        </div>
    </div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label for="fecha" class="form-label">Fecha del Movimiento *</label>
        <input type="date" class="form-control @error('fecha') is-invalid @enderror" 
               id="fecha" name="fecha" 
               value="{{ $fecha }}" 
               required {{ $isReadOnly ? 'disabled' : '' }}>
        @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="tipo" class="form-label">Tipo de Movimiento *</label>
        <select class="form-select @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required {{ $isReadOnly ? 'disabled' : '' }}>
            @foreach($tiposMovimiento as $tipo)
                <option value="{{ $tipo->n_numero }}" {{ $tipoMov == $tipo->n_numero ? 'selected' : '' }}>
                    {{ $tipo->c_describe }}
                </option>
            @endforeach
        </select>
        @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-8">
        <label for="cuenta_caja" class="form-label">Cuenta de Caja (101) *</label>
        <select class="form-select @error('cuenta_caja') is-invalid @enderror" id="cuenta_caja" name="cuenta_caja" required {{ $isReadOnly ? 'disabled' : '' }}>
            @foreach($cuentasCaja as $cuenta)
                <option value="{{ $cuenta->codigo }}" {{ $cuentaCajaSeleccionada == $cuenta->codigo ? 'selected' : '' }}>
                    {{ $cuenta->codigo }} - {{ $cuenta->nombre }}
                </option>
            @endforeach
        </select>
        @error('cuenta_caja') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="monto" class="form-label">Monto (S/) *</label>
        <input type="number" class="form-control @error('monto') is-invalid @enderror" 
               id="monto" name="monto" 
               value="{{ $monto }}" 
               step="0.01" min="0.01" required {{ $isReadOnly ? 'disabled' : '' }}>
        @error('monto') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-12">
        <label for="razon_id" class="form-label">Cuenta de Contrapartida (Gasto/Ingreso/etc.) *</label>
        <select class="form-select @error('razon_id') is-invalid @enderror" id="razon_id" name="razon_id" required {{ $isReadOnly ? 'disabled' : '' }}>
            <option value="">Seleccione la contrapartida...</option>
            @foreach($cuentasContrapartida as $tipo => $cuentas)
                <optgroup label="{{ strtoupper($tipo) }}">
                    @foreach($cuentas as $cuenta)
                        <option value="{{ $cuenta->codigo }}"
                                {{ $cuentaContrapartidaSeleccionada == $cuenta->codigo ? 'selected' : '' }}>
                            {{ $cuenta->codigo }} - {{ $cuenta->nombre }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('razon_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label for="glosa" class="form-label">Glosa / Concepto *</label>
        <input type="text" class="form-control @error('glosa') is-invalid @enderror" 
               id="glosa" name="glosa" 
               value="{{ $glosa }}" 
               placeholder="Ej: Pago recibo de luz, Venta efectivo, etc." required>
        @error('glosa') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="documento" class="form-label">Documento de Referencia</label>
        <input type="text" class="form-control" 
               id="documento" name="documento" 
               value="{{ $docRef }}" 
               placeholder="Ej: N° Recibo 001-1234">
    </div>
</div>

<div class="row g-3 mb-3" id="campo_clase_egreso" style="{{ $tipoMov == 2 ? '' : 'display: none;' }}">
    <div class="col-md-6">
        <label for="clase" class="form-label">Clase de Egreso *</label>
        <select class="form-select @error('clase') is-invalid @enderror" id="clase" name="clase" {{ $isReadOnly ? 'disabled' : '' }}>
            @foreach($clasesOperacion as $clase)
                <option value="{{ $clase->n_numero }}"
                        {{ old('clase', $movimiento->Clase ?? '1') == $clase->n_numero ? 'selected' : '' }}>
                    {{ $clase->c_describe }}
                </option>
            @endforeach
        </select>
        @error('clase') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>


<div class="row mt-4">
    <div class="col-12 d-flex justify-content-between">
        <a href="{{ route('contador.caja.index') }}" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i> Cancelar
        </a>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> 
            {{ $isCreating ? 'Crear Movimiento' : 'Guardar Cambios' }}
        </button>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo');
    const campoClase = document.getElementById('campo_clase_egreso');

    function toggleClaseEgreso() {
        if (tipoSelect.value == '2') { // 2 = Egreso
            campoClase.style.display = 'block';
        } else {
            campoClase.style.display = 'none';
        }
    }

    tipoSelect.addEventListener('change', toggleClaseEgreso);
    toggleClaseEgreso(); // Ejecutar al cargar la página
});
</script>
@endpush