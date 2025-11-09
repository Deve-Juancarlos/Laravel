{{-- 1. Hereda del layout de reportes --}}
@extends('reportes.auditoria.layout') 

@section('title', 'Reporte de Auditoría de Libro Diario')

@push('styles')
<style>
    /* Estilos para la tabla de cambios */
    .change-table {
        font-size: 0.8rem;
        background-color: #fff;
    }
    .change-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .change-table td {
        vertical-align: middle;
    }
    .change-table .field {
        font-weight: 500;
        color: #333;
    }
    .change-table .old-value {
        color: #dc3545; /* Rojo */
        text-decoration: line-through;
        max-width: 150px;
        word-wrap: break-word;
    }
    .change-table .new-value {
        color: #198754; /* Verde */
        font-weight: bold;
        max-width: 150px;
        word-wrap: break-word;
    }
</style>
@endpush

{{-- 2. Contenido del reporte --}}
{{-- ¡¡ESTA ES LA CORRECCIÓN!! Debe ser 'audit-content' --}}
@section('audit-content')

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom p-3">
        <h4 class="mb-0">
            <i class="fas fa-book me-2 text-info"></i>
            Auditoría de Libro Diario
        </h4>
        <small class="text-muted">
            Muestra todos los cambios (Creación, Edición, Eliminación) en los asientos contables.
        </small>
    </div>
    <div class="card-body">

        <!-- Formulario de Filtros -->
        <form method="GET" action="{{ route('contador.reportes.auditoria.libro_diario') }}" class="mb-4 p-3 border rounded bg-light">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio:</label>
                    <input type="date" class="form-control" name="fecha_inicio" value="{{ $filters['fecha_inicio'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label">Fecha Fin:</label>
                    <input type="date" class="form-control" name="fecha_fin" value="{{ $filters['fecha_fin'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label for="usuario" class="form-label">Usuario:</label>
                    <input type="text" class="form-control" name="usuario" value="{{ $filters['usuario'] ?? '' }}" placeholder="Nombre...">
                </div>
                <div class="col-md-2">
                    <label for="asiento_id" class="form-label">Asiento ID:</label>
                    <input type="number" class="form-control" name="asiento_id" value="{{ $filters['asiento_id'] ?? '' }}" placeholder="ID...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>

        <!-- TABLA DE RESULTADOS (MODIFICADA) -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Asiento</th>
                        <th>Detalle de Cambios</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reporte as $log)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($log->fecha_hora)->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->usuario }}</td>
                            <td>
                                @if($log->accion == 'CREAR')
                                    <span class="badge bg-success">{{ $log->accion }}</span>
                                @elseif($log->accion == 'MODIFICAR')
                                    <span class="badge bg-warning text-dark">{{ $log->accion }}</span>
                                @else
                                    <span class="badge bg-danger">{{ $log->accion }}</span>
                                @endif
                            </td>
                            <td>
                                {{-- Asegúrate de que esta ruta 'contador.libro-diario.show' exista --}}
                                <a href="{{ route('contador.libro-diario.show', $log->asiento_id) }}" target="_blank">
                                    ID: {{ $log->asiento_id }}
                                    <br>
                                    <small>{{ $log->AsientoNumero }}</small>
                                </a>
                            </td>
                            
                            {{-- ▼▼▼ ¡SECCIÓN DE CAMBIOS CORREGIDA! ▼▼▼ --}}
                            <td>
                                @if(!empty($log->cambios))
                                    <table class="table table-sm table-bordered change-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Campo</th>
                                                <th>Valor Anterior</th>
                                                <th>Valor Nuevo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($log->cambios as $cambio)
                                                <tr>
                                                    <td class="field">{{ $cambio['campo'] }}</td>
                                                    <td class="old-value">{!! $cambio['antes'] !!}</td>
                                                    <td class="new-value">{!! $cambio['despues'] !!}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <span class="text-muted">Sin cambios detallados o acción no reconocida.</span>
                                @endif
                            </td>
                            {{-- ▲▲▲ FIN DE SECCIÓN ▲▲▲ --}}

                            <td>{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-4">
                                <i class="fas fa-info-circle me-1"></i>
                                No se encontraron registros de auditoría para estos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="mt-3 d-flex justify-content-center">
            @if ($reporte instanceof \Illuminate\Pagination\LengthAwarePaginator)
                {{ $reporte->links() }}
            @endif
        </div>

    </div>
</div>
@endsection