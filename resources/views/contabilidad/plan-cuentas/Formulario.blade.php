@csrf

<div class="row g-3">
    <!-- Código -->
    <div class="col-md-4">
        <label for="codigo" class="form-label">Código *</label>
        <input type="text" class="form-control @error('codigo') is-invalid @enderror" 
               id="codigo" name="codigo" 
               value="{{ old('codigo', $cuenta->codigo ?? '') }}" 
               {{ ($cuenta ?? null) ? 'readonly' : 'required' }}
               placeholder="Ej: 101.1">
        @error('codigo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if($cuenta ?? null)
            <small class="text-muted">El código no se puede modificar.</small>
        @endif
    </div>

    <!-- Nombre -->
    <div class="col-md-8">
        <label for="nombre" class="form-label">Nombre de la Cuenta *</label>
        <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
               id="nombre" name="nombre" 
               value="{{ old('nombre', $cuenta->nombre ?? '') }}" 
               required placeholder="Ej: Caja M/N">
        @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Tipo -->
    <div class="col-md-4">
        <label for="tipo" class="form-label">Tipo *</label>
        <select id="tipo" name="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
            <option value="" disabled {{ old('tipo', $cuenta->tipo ?? '') == '' ? 'selected' : '' }}>Seleccione un tipo...</option>
            @foreach($tipos as $tipo)
                <option value="{{ $tipo }}" {{ (old('tipo', $cuenta->tipo ?? '') == $tipo) ? 'selected' : '' }}>
                    {{ $tipo }}
                </option>
            @endforeach
        </select>
        @error('tipo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Subtipo -->
    <div class="col-md-4">
        <label for="subtipo" class="form-label">Subtipo (Opcional)</label>
        <input type="text" class="form-control @error('subtipo') is-invalid @enderror" 
               id="subtipo" name="subtipo" 
               value="{{ old('subtipo', $cuenta->subtipo ?? '') }}" 
               placeholder="Ej: Bancos, Caja">
        @error('subtipo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Nivel -->
    <div class="col-md-4">
        <label for="nivel" class="form-label">Nivel *</label>
        <input type="number" class="form-control @error('nivel') is-invalid @enderror" 
               id="nivel" name="nivel" 
               value="{{ old('nivel', $cuenta->nivel ?? '1') }}" 
               required min="1" max="10">
        @error('nivel')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Cuenta Padre -->
    <div class="col-md-8">
        <label for="cuenta_padre" class="form-label">Cuenta Padre (Opcional)</label>
        <select id="cuenta_padre" name="cuenta_padre" class="form-select @error('cuenta_padre') is-invalid @enderror">
            <option value="">Ninguna (Es cuenta principal)</option>
            @foreach($cuentasPadre as $codigoPadre => $nombrePadre)
                <option value="{{ $codigoPadre }}" {{ (old('cuenta_padre', $cuenta->cuenta_padre ?? '') == $codigoPadre) ? 'selected' : '' }}>
                    {{ $codigoPadre }} - {{ $nombrePadre }}
                </option>
            @endforeach
        </select>
        @error('cuenta_padre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Activo -->
    <div class="col-md-4">
        <div class="form-check form-switch mt-4 pt-3">
            <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" 
                   {{ old('activo', $cuenta->activo ?? 1) ? 'checked' : '' }}>
            <label class="form-check-label" for="activo">Cuenta Activa</label>
        </div>
    </div>
</div>

<div class="mt-4 pt-3 border-top">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> Guardar Cuenta
    </button>
    <a href="{{ route('contador.plan-cuentas.index') }}" class="btn btn-secondary">
        <i class="fas fa-times me-1"></i> Cancelar
    </a>
</div>
