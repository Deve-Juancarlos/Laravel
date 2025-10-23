@extends('layouts.admin')

@section('title', 'Auditoría del Sistema')

@section('content')
<div class="card">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h4><i class="fas fa-history"></i> Auditoría del Sistema</h4>
        <span class="badge bg-light text-dark">
            {{ $logs->total() }} registros
        </span>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form method="GET" class="row mb-4 g-3">
            <div class="col-md-3">
                <input type="text" name="usuario" class="form-control" 
                       placeholder="Usuario" value="{{ request('usuario') }}">
            </div>
            <div class="col-md-3">
                <select name="accion" class="form-select">
                    <option value="">Todas las acciones</option>
                    @foreach($acciones as $accion)
                        <option value="{{ $accion }}" {{ request('accion') == $accion ? 'selected' : '' }}>
                            {{ $accion }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="fecha_desde" class="form-control" 
                       value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="fecha_hasta" class="form-control" 
                       value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </form>

        <!-- Tabla de Auditoría -->
        <div class="table-responsive">
            <table class="table table-hover">
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
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($log->fecha)->format('d/m/Y H:i:s') }}</td>
                            <td><strong>{{ $log->usuario }}</strong></td>
                            <td>
                                <span class="badge bg-info">{{ $log->accion }}</span>
                            </td>
                            <td>{{ $log->tabla }}</td>
                            <td>{{ $log->detalle }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No hay registros de auditoría.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection