@extends('layouts.admin')

@section('title', 'Cheques Pendientes')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Cheques Pendientes de Cobro</h1>
    <p class="text-muted mb-0">Control de cheques emitidos pendientes</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.bancos.index') }}">Bancos</a></li>
<li class="breadcrumb-item active">Cheques Pendientes</li>
@endsection

@section('content')

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Cheques Pendientes</h6>
                <h3 class="mb-0 text-warning">{{ $cheques->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Monto Total Pendiente</h6>
                <h3 class="mb-0 text-danger">
                    S/ {{ number_format($cheques->sum('Monto'), 2) }}
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Cheques Vencidos</h6>
                <h3 class="mb-0 text-danger">
                    {{ $cheques->filter(function($ch) { 
                        return \Carbon\Carbon::parse($ch->Fecha)->isPast(); 
                    })->count() }}
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Cheques -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-money-check me-2"></i>
            Listado de Cheques Pendientes
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
                        <th>Fecha Emisión</th>
                        <th>Número de Cheque</th>
                        <th>Banco</th>
                        <th>Cuenta</th>
                        <th>Beneficiario</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Días</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cheques as $cheque)
                    @php
                        $fechaEmision = \Carbon\Carbon::parse($cheque->Fecha);
                        $diasTranscurridos = $fechaEmision->diffInDays(now());
                        $esVencido = $fechaEmision->isPast() && $diasTranscurridos > 30;
                    @endphp
                    <tr class="{{ $esVencido ? 'table-danger' : '' }}">
                        <td>{{ $fechaEmision->format('d/m/Y') }}</td>
                        <td>
                            odede class="fs-6">{{ $cheque->NumeroCheque ?? $cheque->Referencia ?? '-' }}</code>
                        </td>
                        <td>
                            <i class="fas fa-university text-primary me-2"></i>
                            {{ $cheque->Banco }}
                        </td>
                        <td>
                            <small class="text-muted">{{ $cheque->Cuenta }}</small>
                        </td>
                        <td>
                            <strong>{{ $cheque->Beneficiario ?? $cheque->Descripcion ?? '-' }}</strong>
                        </td>
                        <td class="text-end">
                            <strong class="text-danger">S/ {{ number_format($cheque->Monto, 2) }}</strong>
                        </td>
                        <td class="text-center">
                            @if($esVencido)
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-circle me-1"></i>Vencido
                                </span>
                            @elseif($diasTranscurridos > 20)
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock me-1"></i>Por Vencer
                                </span>
                            @else
                                <span class="badge bg-info">
                                    <i class="fas fa-hourglass-half me-1"></i>Pendiente
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $esVencido ? 'danger' : ($diasTranscurridos > 20 ? 'warning' : 'secondary') }}">
                                {{ $diasTranscurridos }} días
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-success py-5">
                            <i class="fas fa-check-circle fa-3x mb-3 d-block"></i>
                            <h5>No hay cheques pendientes</h5>
                            <p class="text-muted">Todos los cheques han sido cobrados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($cheques->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="text-end">TOTAL PENDIENTE:</th>
                        <th class="text-end">
                            <strong class="text-danger fs-5">
                                S/ {{ number_format($cheques->sum('Monto'), 2) }}
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

<!-- Alertas -->
@if($cheques->filter(function($ch) { 
    return \Carbon\Carbon::parse($ch->Fecha)->isPast() && 
           \Carbon\Carbon::parse($ch->Fecha)->diffInDays(now()) > 30; 
})->count() > 0)
<div class="alert alert-danger mt-4" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Atención:</strong> Hay cheques vencidos que requieren seguimiento inmediato.
</div>
@endif

@endsection
