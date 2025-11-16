@extends('layouts.admin')

@section('title', 'Movimientos Bancarios')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Movimientos de {{ $cuentaData->Banco }}</h1>
    <p class="text-muted mb-0">Cuenta: {{ $cuentaData->Cuenta }}</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.bancos.index') }}">Bancos</a></li>
<li class="breadcrumb-item active">Movimientos</li>
@endsection

@section('content')

<!-- Información de la Cuenta -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Saldo Actual</h6>
                <h3 class="mb-0 {{ ($cuentaData->saldoactual ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $cuentaData->Moneda == 'SOLES' ? 'S/' : '$' }} 
                    {{ number_format($cuentaData->saldoactual ?? 0, 2) }}
                </h3>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Ingresos</h6>
                <h3 class="mb-0 text-success">
                    {{ number_format($estadisticas['total_ingresos'], 2) }}
                </h3>
                <small class="text-muted">{{ $estadisticas['cantidad_ingresos'] }} movimientos</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Egresos</h6>
                <h3 class="mb-0 text-danger">
                    {{ number_format($estadisticas['total_egresos'], 2) }}
                </h3>
                <small class="text-muted">{{ $estadisticas['cantidad_egresos'] }} movimientos</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Flujo Neto</h6>
                <h3 class="mb-0 {{ ($estadisticas['total_ingresos'] - $estadisticas['total_egresos']) >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($estadisticas['total_ingresos'] - $estadisticas['total_egresos'], 2) }}
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" 
                       value="{{ $filtros['fecha_inicio'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" 
                       value="{{ $filtros['fecha_fin'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo de Movimiento</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" {{ ($filtros['tipo'] ?? '') == '1' ? 'selected' : '' }}>Ingresos</option>
                    <option value="2" {{ ($filtros['tipo'] ?? '') == '2' ? 'selected' : '' }}>Egresos</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-search me-2"></i>Buscar
                </button>
                <a href="{{ route('admin.bancos.exportar', [$cuentaData->Cuenta] + request()->all()) }}" 
                   class="btn btn-success">
                    <i class="fas fa-file-excel"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Movimientos -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list-alt me-2"></i>
            Movimientos ({{ $movimientos->count() }})
        </h5>
        <a href="{{ route('admin.bancos.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 500px; overflow-y:auto;">
            <table class="table table-hover mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th width="100">Fecha</th>
                        <th width="100">Tipo</th>
                        <th>Documento</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $mov)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($mov->Fecha)->format('d/m/Y') }}</td>
                        <td class="text-center">
                            @if($mov->Tipo == 1)
                                <span class="badge bg-success"><i class="fas fa-arrow-down me-1"></i>Ingreso</span>
                            @else
                                <span class="badge bg-danger"><i class="fas fa-arrow-up me-1"></i>Egreso</span>
                            @endif
                        </td>
                        <td>{{ $mov->Documento ?? '-' }}</td>
                        <td class="text-end {{ $mov->Tipo == 1 ? 'text-success' : 'text-danger' }}">
                            {{ $cuentaData->Moneda == 'SOLES' ? 'S/' : '$' }} {{ number_format($mov->Monto, 2) }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $mov->Estado == 'CONFIRMADO' ? 'success' : 'warning' }}">
                                {{ $mov->Estado ?? 'PENDIENTE' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No hay movimientos registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
