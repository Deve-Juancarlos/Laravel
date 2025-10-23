@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header bg-success text-white">
        <h4><i class="fas fa-plus-circle"></i> Registrar Nueva Cuenta Bancaria</h4>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.bancos.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Número de Cuenta <span class="text-danger">*</span></label>
                <input type="text" name="Cuenta" class="form-control" value="{{ old('Cuenta') }}" maxlength="20" required>
                <div class="form-text">Máximo 20 caracteres (sin espacios)</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre del Banco <span class="text-danger">*</span></label>
                <input type="text" name="Banco" class="form-control" value="{{ old('Banco') }}" maxlength="50" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Moneda <span class="text-danger">*</span></label>
                <select name="Moneda" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option value="1" {{ old('Moneda') == 1 ? 'selected' : '' }}>Soles (PEN)</option>
                    <option value="2" {{ old('Moneda') == 2 ? 'selected' : '' }}>Dólares (USD)</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar Cuenta
                </button>
                <a href="{{ route('admin.bancos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection