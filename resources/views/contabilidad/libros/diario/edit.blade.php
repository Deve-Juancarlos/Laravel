
@use('Illuminate\Support\Str')
{{-- resources/views/contabilidad/libros/diario/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Asiento ' . $asiento->numero)

<!-- 1. Título de la Cabecera -->
@section('page-title')
    Editar Asiento #{{ $asiento->numero }}
@endsection

<!-- 2. Breadcrumbs -->
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.libro-diario.index') }}">Libro Diario</a></li>
    <li class="breadcrumb-item active" aria-current="page">Editar</li>
@endsection

<!-- 3. Estilos CSS de esta página -->
@push('styles')
    <link href="{{ asset('css/contabilidad/asiento-form.css') }}" rel="stylesheet">
@endpush

<!-- 4. Contenido Principal -->
@section('content')
<div class="container-fluid">
    {{-- Formulario de Edición --}}
    <div class="form-card">
        <div class="form-card-header">
            <h5 class="form-card-title">
                <i class="fas fa-file-alt"></i> Información General
            </h5>
            <p class="form-card-subtitle">
                Solo se puede modificar la cabecera (fecha, glosa, observaciones). Los detalles del asiento no son editables.
            </p>
        </div>
        <div class="form-card-body">
            <form action="{{ route('contador.libro-diario.update', $asiento->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Número de Asiento</label>
                        <input type="text" class="form-control" name="numero" value="{{ $asiento->numero }}" readonly disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha *</label>
                        <input type="date" class="form-control @error('fecha') is-invalid @enderror" name="fecha" value="{{ old('fecha', \Carbon\Carbon::parse($asiento->fecha)->format('Y-m-d')) }}" required>
                        @error('fecha')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" value="{{ $asiento->usuario_nombre ?? 'Sistema' }}" readonly disabled>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Glosa Principal *</label>
                    <textarea class="form-control @error('glosa') is-invalid @enderror" name="glosa" rows="3" required>{{ old('glosa', $asiento->glosa) }}</textarea>
                    @error('glosa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-3">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-control" name="observaciones" rows="2">{{ old('observaciones', $asiento->observaciones) }}</textarea>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('contador.libro-diario.show', $asiento->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Detalles del Asiento (solo lectura) --}}
    <div class="table-card mt-4">
         <div class="table-header">
            <h5 class="table-title">
                <i class="fas fa-list-alt"></i> Detalles del Asiento (Solo Lectura)
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cuenta</th>
                        <th>Nombre Cuenta</th>
                        <th>Concepto</th>
                        <th class="text-end">Debe</th>
                        <th class="text-end">Haber</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detalles as $detalle)
                    <tr>
                        <td><strong>{{ $detalle->cuenta_contable }}</strong></td>
                        <td>{{ $detalle->cuenta_nombre ?? 'Cuenta no encontrada' }}</td>
                        <td>{{ $detalle->concepto }}</td>
                        <td class="text-end text-success fw-bold">{{ $detalle->debe > 0 ? 'S/ ' . number_format($detalle->debe, 2) : '-' }}</td>
                        <td class="text-end text-danger fw-bold">{{ $detalle->haber > 0 ? 'S/ ' . number_format($detalle->haber, 2) : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                 <tfoot class="table-active">
                    <tr>
                        <td colspan="3" class="text-end"><strong>TOTALES</strong></td>
                        <td class="text-end">
                            <strong class="text-success">S/ {{ number_format($detalles->sum('debe'), 2) }}</strong>
                        </td>
                        <td class="text-end">
                            <strong class="text-danger">S/ {{ number_format($detalles->sum('haber'), 2) }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Acciones adicionales --}}
    <div class="card my-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-cogs text-primary me-2"></i> Acciones Adicionales</h6>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('contador.libro-diario.show', $asiento->id) }}" class="btn btn-info text-white">
                    <i class="fas fa-eye"></i> Ver Asiento
                </a>
                 <button typeA="button" onclick="eliminarAsiento()" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Eliminar Asiento
                </button>
            </div>
            
            <!-- Formulario de eliminación oculto -->
            <form id="delete-form-{{ $asiento->id }}" action="{{ route('contador.libro-diario.destroy', $asiento->id) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Estandarizamos la función de borrado para usar SweetAlert y el Formulario
function eliminarAsiento() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción eliminará el asiento #{{ $asiento->numero }} permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                if(typeof showLoading === 'function') showLoading();
                document.getElementById('delete-form-{{ $asiento->id }}').submit();
            }
        });
    } else {
        // Fallback si SweetAlert no carga
        if (confirm('¿Estás seguro de eliminar este asiento? Esta acción no se puede deshacer.')) {
            if(typeof showLoading === 'function') showLoading();
            document.getElementById('delete-form-{{ $asiento->id }}').submit();
        }
    }
}
</script>
@endpush
