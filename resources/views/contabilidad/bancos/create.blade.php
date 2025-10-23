{{-- resources/views/contabilidad/bancos/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Registrar Cuenta Bancaria')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">➕ Registrar Nueva Cuenta Bancaria</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('contabilidad.bancos.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="banco_codigo">Institución Financiera *</label>
                    <select name="banco_codigo" id="banco_codigo" class="form-control" required>
                        <option value="">-- Seleccione un banco --</option>
                        @foreach($bancosLista as $item)
                            <option value="{{ $item->codigo }}">{{ $item->descripcion }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="numero_cuenta">Número de Cuenta *</label>
                    <input type="text" 
                           name="numero_cuenta" 
                           class="form-control" 
                           placeholder="Ej. 191-1234567-0-12" 
                           required>
                </div>

                <div class="form-group">
                    <label>Moneda *</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="moneda_codigo" value="1" id="moneda_soles" checked>
                            <label class="form-check-label" for="moneda_soles">Soles (S/)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="moneda_codigo" value="2" id="moneda_dolares">
                            <label class="form-check-label" for="moneda_dolares">Dólares (US$)</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción (opcional)</label>
                    <input type="text" 
                           name="descripcion" 
                           class="form-control" 
                           placeholder="Ej. Cta. Principal - Ventas">
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Registrar Cuenta
                </button>
                <a href="{{ route('contabilidad.bancos.index') }}" class="btn btn-secondary">
                    ← Cancelar
                </a>
            </form>
        </div>
    </div>
</div>
@endsection