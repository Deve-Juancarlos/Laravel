{{-- 1. Hereda del layout de reportes --}}
@extends('reportes.auditoria.layout') 

@section('title', 'Reporte de Auditoría General')

{{-- 2. Contenido del reporte --}}
@section('report-content')

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom p-3">
        <h4 class="mb-0">
            <i class="fas fa-history me-2 text-secondary"></i>
            Auditoría General del Sistema
        </h4>
        <small class="text-muted">
            Muestra un log de acciones generales registradas en el sistema.
        </small>
    </div>
    <div class="card-body">

        <!-- Formulario de Filtros -->
        <form method="GET" action="{{ route('contador.reportes.auditoria.sistema_general') }}" class="mb-4 p-3 border rounded bg-light">
            <div class="row g-3 align-items-end">
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
                    <label for="tabla" class="form-label">Tabla Afectada:</label>
                    <select name="tabla" class="form-select">
                        <option value="">-- Todas --</option>
                        @foreach($tablasDisponibles as $tabla)
                            <option value="{{ $tabla }}" {{ ($filters['tabla'] ?? '') == $tabla ? 'selected' : '' }}>
                                {{ $tabla }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>

        <!-- TABLA DE RESULTADOS -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Tabla</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reporte as $log)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($log->fecha)->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->usuario }}</td>
                            <td>{{ $log->accion }}</td>
                            <td><span class="badge bg-secondary">{{ $log->tabla }}</span></td>
                            <td style="max-width: 400px; word-break: break-word;">{{ $log->detalle }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-4">
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