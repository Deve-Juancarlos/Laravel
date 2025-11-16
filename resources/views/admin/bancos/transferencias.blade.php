@extends('layouts.admin')

@section('title', 'Transferencias Bancarias')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Transferencias Bancarias</h1>
    <p class="text-muted mb-0">Registro de transferencias entre cuentas</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.bancos.index') }}">Bancos</a></li>
<li class="breadcrumb-item active">Transferencias</li>
@endsection

@section('content')

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Transferencias</h6>
                <h3 class="mb-0 text-primary">{{ $transferencias->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Monto Total</h6>
                <h3 class="mb-0 text-success">
                    S/ {{ number_format($transferencias->sum('Monto'), 2) }}
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Pendientes</h6>
                <h3 class="mb-0 text-warning">
                    {{ $transferencias->where('Estado', 'PENDIENTE')->count() }}
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
                       value="{{ $filtros['fecha_inicio'] }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" 
                       value="{{ $filtros['fecha_fin'] }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="PENDIENTE" {{ $filtros['estado'] == 'PENDIENTE' ? 'selected' : '' }}>
                        Pendiente
                    </option>
                    <option value="CONFIRMADO" {{ $filtros['estado'] == 'CONFIRMADO' ? 'selected' : '' }}>
                        Confirmado
                    </option>
                    <option value="RECHAZADO" {{ $filtros['estado'] == 'RECHAZADO' ? 'selected' : '' }}>
                        Rechazado
                    </option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Buscar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Transferencias -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-exchange-alt me-2"></i>
            Listado de Transferencias
        </h5>
        <a href="{{ route('admin.bancos.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="100">Fecha</th>
                        <th>Banco</th>
                        <th>Cuenta Origen</th>
                        <th>Cuenta Destino</th>
                        <th>Referencia</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transferencias as $trans)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($trans->Fecha)->format('d/m/Y') }}</td>
                        <td>
                            <i class="fas fa-university text-primary me-2"></i>
                            <strong>{{ $trans->banco_origen }}</strong>
                        </td>
                        <td>
                            <code>{{ $trans->cuenta_origen }}</code>
                        </td>
                        <td>
                            <code>{{ $trans->cuenta_destino ?? '-' }}</code>
                        </td>
                        <td>
                            <small class="text-muted">{{ $trans->Documento ?? '-' }}</small>
                        </td>
                        <td class="text-end">
                            <strong class="text-primary fs-5">
                                S/ {{ number_format($trans->Monto, 2) }}
                            </strong>
                        </td>
                        <td class="text-center">
                            @if($trans->estado_transferencia == 'COMPLETA')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Confirmado
                                </span>
                            @else
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock me-1"></i>Pendiente
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{-- Solo si tienes Tipo definido en la vista, si no, quitar --}}
                            @if(isset($trans->Tipo) && $trans->Tipo == 1)
                                <span class="badge bg-success">
                                    <i class="fas fa-arrow-down me-1"></i>Entrada
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-arrow-up me-1"></i>Salida
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-exchange-alt fa-3x mb-3 d-block"></i>
                            <h5>No hay transferencias registradas</h5>
                            <p class="text-muted">No se encontraron transferencias con los filtros aplicados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($transferencias->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="text-end">TOTAL:</th>
                        <th class="text-end">
                            <strong class="text-primary fs-5">
                                S/ {{ number_format($transferencias->sum('Monto'), 2) }}
                            </strong>
                        </th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@endsection
