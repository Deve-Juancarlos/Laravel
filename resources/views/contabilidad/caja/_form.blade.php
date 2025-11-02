@csrf

{{-- 
    Este formulario es "inteligente". Sabe si está creando o editando.
    - Si existe $movimiento, estamos editando.
    - Si no existe $movimiento, estamos creando.
--}}

@php
    // DEFINICIÓN DEFENSIVA DE VARIABLES
    // Esto soluciona el error 'Undefined variable $detalles' en la vista 'create'
    // y también funciona para 'edit'.

    // Si estamos editando y hay un $movimiento, $detalles será la colección de sus movimientos
    $detalles = $movimiento->detalles ?? collect();

    // 1. Determinar la cuenta de caja (101)
    $detalleCaja = $detalles->firstWhere('cuenta_contable', 'LIKE', '101%');
    $cuentaCajaSeleccionada = old('cuenta_caja', $detalleCaja->cuenta_contable ?? '10101'); // Valor por defecto

    // 2. Determinar la cuenta de contrapartida (la que NO es 101)
    $detalleContrapartida = $detalles->firstWhere('cuenta_contable', 'NOT LIKE', '101%');
    $cuentaContrapartidaSeleccionada = old('cuenta_contrapartida', $detalleContrapartida->cuenta_contable ?? null);

    // 3. Determinar el monto (del movimiento de caja)
    $monto = old('monto', $movimiento->Monto ?? 0);

    // 4. Determinar el concepto (de la contrapartida)
    $concepto = old('concepto', $detalleContrapartida->concepto ?? '');

    // 5. Determinar el documento de referencia (de la contrapartida)
    $docRef = old('documento_referencia', $detalleContrapartida->documento_referencia ?? '');
    
    // 6. Determinar la glosa (del asiento)
    $glosa = old('glosa', $movimiento->glosa_diario ?? ($movimiento->Documento ?? ''));

    // 7. Determinar si el formulario es de solo lectura (porque ya está vinculado a un asiento)
    $isReadOnly = isset($movimiento) && $movimiento->asiento_id;
@endphp


<!-- Alerta de solo-lectura -->
@if($isReadOnly)
    <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="fas fa-info-circle fs-5 me-2"></i>
        <div>
            Este movimiento de caja está <strong>vinculado al Asiento Contable #{{ $movimiento->asiento_id }}</strong>. 
            <br>Para editar los montos o cuentas, por favor edite el asiento directamente: 
            <a href="{{ route('contador.libro-diario.edit', $movimiento->asiento_id) }}" class="btn btn-sm btn-info-soft ms-2">
                <i class="fas fa-edit me-1"></i> Ir al Asiento
            </a>
        </div>
    </div>
@endif

<!-- Fila 1: Fecha y Tipo -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label for="fecha" class="form-label">Fecha del Movimiento *</label>
        <input type="date" class="form-control @error('fecha') is-invalid @enderror" 
               id="fecha" name="fecha" 
               value="{{ old('fecha', isset($movimiento) ? \Carbon\Carbon::parse($movimiento->Fecha)->format('Y-m-d') : now()->format('Y-m-d')) }}" 
               required {{ $isReadOnly ? 'disabled' : '' }}>
    </div>
    <div class="col-md-6">
        <label for="tipo" class="form-label">Tipo de Movimiento *</label>
        <select class="form-select @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required {{ $isReadOnly ? 'disabled' : '' }}>
            @foreach($tiposMovimiento as $tipo)
                <option value="{{ $tipo->n_numero }}" 
                        {{ old('tipo', $movimiento->Tipo ?? '') == $tipo->n_numero ? 'selected' : '' }}>
                    {{ $tipo->c_describe }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<!-- Fila 2: Cuenta de Caja y Monto -->
<div class="row g-3 mb-3">
    <div class="col-md-8">
        <label for="cuenta_caja" class="form-label">Cuenta de Caja (101) *</label>
        <select class="form-select @error('cuenta_caja') is-invalid @enderror" id="cuenta_caja" name="cuenta_caja" required {{ $isReadOnly ? 'disabled' : '' }}>
            @foreach($cuentasCaja as $cuenta)
                <option value="{{ $cuenta->codigo }}" 
                        {{ $cuentaCajaSeleccionada == $cuenta->codigo ? 'selected' : '' }}>
                    {{ $cuenta->codigo }} - {{ $cuenta->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="monto" class="form-label">Monto (S/) *</label>
        <input type="number" class="form-control @error('monto') is-invalid @enderror" 
               id="monto" name="monto" 
               value="{{ $monto }}" 
               step="0.01" min="0.01" required {{ $isReadOnly ? 'disabled' : '' }}>
    </div>
</div>

<!-- Fila 3: Contrapartida (Gasto/Ingreso) -->
<div class="row g-3 mb-3">
    <div class="col-md-12">
        <label for="cuenta_contrapartida" class="form-label">Cuenta de Contrapartida (Gasto/Ingreso/etc.) *</label>
        <select class="form-select @error('cuenta_contrapartida') is-invalid @enderror" id="cuenta_contrapartida" name="cuenta_contrapartida" required {{ $isReadOnly ? 'disabled' : '' }}>
            <option value="">Seleccione la contrapartida...</option>
            @foreach($cuentasContrapartida as $cuenta)
                <option value="{{ $cuenta->codigo }}"
                        {{ $cuentaContrapartidaSeleccionada == $cuenta->codigo ? 'selected' : '' }}>
                    {{ $cuenta->codigo }} - {{ $cuenta->nombre }} ({{ $cuenta->tipo }})
                </option>
            @endforeach
        </select>
    </div>
</div>

<!-- Fila 4: Concepto y Doc. Referencia -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label for="concepto" class="form-label">Concepto Específico *</label>
        <input type="text" class="form-control @error('concepto') is-invalid @enderror" 
               id="concepto" name="concepto" 
               value="{{ $concepto }}" 
               placeholder="Ej: Pago recibo de luz, Venta efectivo, etc." required {{ $isReadOnly ? 'disabled' : '' }}>
    </div>
    <div class="col-md-6">
        <label for="documento_referencia" class="form-label">Documento de Referencia</label>
        <input type="text" class="form-control" 
               id="documento_referencia" name="documento_referencia" 
               value="{{ $docRef }}" 
               placeholder="Ej: N° Recibo 001-1234" {{ $isReadOnly ? 'disabled' : '' }}>
    </div>
</div>

<!-- Fila 5: Glosa -->
<div class="row g-3 mb-3">
    <div class="col-12">
        <label for="glosa" class="form-label">Glosa del Asiento (General) *</label>
        <textarea class="form-control @error('glosa') is-invalid @enderror" 
                  id="glosa" name="glosa" 
                  rows="2" 
                  placeholder="Glosa general que aparecerá en el Libro Diario. Ej: Pago de servicios básicos mes Octubre." 
                  required>{{ $glosa }}</textarea>
    </div>
</div>

<!-- Botones de Acción -->
<div class="row mt-4">
    <div class="col-12 d-flex justify-content-between">
        <a href="{{ route('contador.caja.index') }}" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i> Cancelar
        </a>
        
        {{-- Solo mostrar el botón de guardar si no es solo lectura --}}
        @if(!$isReadOnly)
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> 
            {{ isset($movimiento) ? 'Guardar Cambios' : 'Crear Movimiento' }}
        </button>
        @endif
    </div>
</div>

