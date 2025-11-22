@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Conciliación Bancaria')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Conciliación Bancaria: {{ $cuentaData->Banco }}</h1>
    <p class="text-muted mb-0">Cuenta: {{ $cuentaData->Cuenta }}</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.bancos.index') }}">Bancos</a></li>
<li class="breadcrumb-item active">Conciliación</li>
@endsection

@section('content')

<!-- Información de la Cuenta -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Saldo en Sistema</h6>
                <h3 class="mb-0 text-primary">
                    {{ $cuentaData->Moneda == 'SOLES' ? 'S/' : '$' }} 
                    {{ number_format($cuentaData->saldoactual ?? 0, 2) }}
                </h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Movimientos Sin Conciliar</h6>
                <h3 class="mb-0 text-warning">
                    {{ $movimientosSinConciliar->count() }}
                </h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Última Conciliación</h6>
                <h3 class="mb-0 text-info">
                    @if($historialConciliaciones->count() > 0)
                        {{ $historialConciliaciones->first()->fecha_conciliacion ?? '-' }}
                    @else
                        -
                    @endif
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- Formulario de Conciliación -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-balance-scale me-2"></i>
            Registrar Nueva Conciliación
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.bancos.guardar-conciliacion', $cuentaData->Cuenta) }}">
            @csrf
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Fecha de Conciliación</label>
                    <input type="date" name="fecha" class="form-control" value="{{ $fecha }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Saldo Según Banco</label>
                    <input type="number" 
                           name="saldo_bancario" 
                           class="form-control" 
                           step="0.01" 
                           placeholder="0.00" 
                           required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Diferencia</label>
                    <input type="text" 
                           class="form-control bg-light" 
                           value="Se calculará automáticamente" 
                           readonly>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="3" 
                          placeholder="Notas sobre la conciliación..."></textarea>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.bancos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Guardar Conciliación
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Movimientos Sin Conciliar -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Movimientos Pendientes de Conciliar
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">
                            <input type="checkbox" class="form-check-input">
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientosSinConciliar as $mov)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($mov->Fecha)->format('d/m/Y') }}</td>
                        <td>
                            @if($mov->Tipo == 1)
                                <span class="badge bg-success">Ingreso</span>
                            @else
                                <span class="badge bg-danger">Egreso</span>
                            @endif
                        </td>
                        <td>{{ $mov->Descripcion ?? $mov->Concepto ?? '-' }}</td>
                        <td class="text-end">
                            {{ $cuentaData->Moneda == 'SOLES' ? 'S/' : '$' }} 
                            {{ number_format($mov->Monto, 2) }}
                        </td>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input" value="{{ $mov->id }}">
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-success py-4">
                            <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                            Todos los movimientos están conciliados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
