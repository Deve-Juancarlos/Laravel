{{-- resources/views/contabilidad/libros/diario/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Asiento ' . $asiento->numero)

@section('content')
<div class="container-fluid p-0">
    {{-- Header con navegación --}}
    <div class="d-flex justify-content-between align-items-center p-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white;">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-edit"></i> Editar Asiento #{{ $asiento->numero }}
            </h1>
            <p class="mb-0 opacity-75">Modifica los detalles del asiento contable</p>
        </div>
        <div>
            <a href="{{ route('contador.libro-diario.show', $asiento->id) }}" class="btn btn-light">
                <i class="fas fa-eye"></i> Ver Asiento
            </a>
        </div>
    </div>

    {{-- Formulario de Edición --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-alt"></i> Información General
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('contador.libro-diario.update', $asiento->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Número de Asiento *</label>
                        <input type="text" class="form-control" name="numero" value="{{ $asiento->numero }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha *</label>
                        <input type="date" class="form-control" name="fecha" value="{{ $asiento->fecha }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" value="{{ $asiento->usuario_nombre ?? 'Contador' }}" readonly>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label">Glosa Principal *</label>
                    <textarea class="form-control" name="glosa" rows="3" required>{{ $asiento->glosa }}</textarea>
                </div>

                <div class="mt-3">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-control" name="observaciones" rows="2">{{ $asiento->observaciones }}</textarea>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('contador.libro-diario.show', $asiento->id) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Detalles del Asiento (solo lectura por ahora, podrías hacerlos editables después) --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list-alt"></i> Detalles del Asiento
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Cuenta</th>
                            <th>Nombre Cuenta</th>
                            <th>Concepto</th>
                            <th>Debe</th>
                            <th>Haber</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detalles as $detalle)
                        <tr>
                            <td><strong>{{ $detalle->cuenta_contable }}</strong></td>
                            <td>{{ $detalle->cuenta_nombre ?? 'Cuenta no encontrada' }}</td>
                            <td>{{ $detalle->concepto }}</td>
                            <td class="text-end">{{ number_format($detalle->debe, 2) }}</td>
                            <td class="text-end">{{ number_format($detalle->haber, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Acciones adicionales --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="{{ route('contador.libro-diario.index') }}" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Volver al Libro
                </a>
                <a href="{{ route('contador.libro-diario.show', $asiento->id) }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> Ver Asiento
                </a>
                <button onclick="eliminarAsiento()" class="btn btn-warning">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function eliminarAsiento() {
    if (confirm('¿Está seguro que desea eliminar este asiento? Esta acción no se puede deshacer.')) {
        fetch(`{{ route('contador.libro-diario.destroy', $asiento->id) }}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = `{{ route('contador.libro-diario.index') }}`;
            } else {
                alert('Error al eliminar el asiento: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el asiento');
        });
    }
}
</script>

@endsection