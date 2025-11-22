@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Consolidado de Saldos')

@section('header-content')
<div>
    <h1 class="h3 mb-0">Consolidado de Saldos Bancarios</h1>
    <p class="text-muted mb-0">Vista general de liquidez por banco y moneda</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.bancos.index') }}">Bancos</a></li>
<li class="breadcrumb-item active">Saldos</li>
@endsection

@section('content')

<!-- Resumen de Liquidez -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-tint me-2"></i>
                    Liquidez Disponible
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="text-muted mb-1">Disponible Inmediato</p>
                        <h3 class="text-success mb-0">
                            S/ {{ number_format($liquidez['disponible_inmediato'], 2) }}
                        </h3>
                        <small class="text-muted">Cuentas corrientes</small>
                    </div>
                    <div class="col-6">
                        <p class="text-muted mb-1">Ahorros</p>
                        <h3 class="text-info mb-0">
                           {{-- S/ {{ number_format($liquidez['ahorros'], 2) }} --}} 
                        </h3>
                        <small class="text-muted">Cuentas de ahorro</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-coins me-2"></i>
                    Saldos por Moneda
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($saldosPorMoneda as $moneda)
                    <div class="col-6">
                        <p class="text-muted mb-1">{{ $moneda->Moneda }}</p>
                        <h3 class="mb-0 {{ $moneda->saldo_total >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $moneda->Moneda == 'SOLES' ? 'S/' : '$' }} 
                            {{ number_format($moneda->saldo_total, 2) }}
                        </h3>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Saldos por Banco -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-university me-2"></i>
            Saldos Consolidados por Banco
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Banco</th>
                        <th class="text-center">Cantidad de Cuentas</th>
                        <th class="text-end">Saldo Total</th>
                        <th class="text-end">% Participación</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalGeneral = $saldosPorBanco->sum('saldo_total'); @endphp
                    @forelse($saldosPorBanco as $index => $banco)
                    <tr>
                        <td>
                            <span class="badge bg-primary">{{ $index + 1 }}</span>
                        </td>
                        <td>
                            <i class="fas fa-university text-primary me-2"></i>
                            <strong>{{ $banco->Banco }}</strong>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $banco->cantidad_cuentas }}</span>
                        </td>
                        <td class="text-end">
                            <strong class="fs-5 {{ $banco->saldo_total >= 0 ? 'text-success' : 'text-danger' }}">
                                S/ {{ number_format($banco->saldo_total, 2) }}
                            </strong>
                        </td>
                        <td class="text-end">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-primary" 
                                     role="progressbar" 
                                     style="width: {{ $totalGeneral > 0 ? ($banco->saldo_total / $totalGeneral) * 100 : 0 }}%"
                                     aria-valuenow="{{ $banco->saldo_total }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="{{ $totalGeneral }}">
                                    {{ $totalGeneral > 0 ? number_format(($banco->saldo_total / $totalGeneral) * 100, 1) : 0 }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No hay datos disponibles
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">TOTAL GENERAL:</th>
                        <th class="text-end">
                            <strong class="fs-5 text-success">
                                S/ {{ number_format($totalGeneral, 2) }}
                            </strong>
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Botón Volver -->
<div class="text-center">
    <a href="{{ route('admin.bancos.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver al Listado
    </a>
</div>

@endsection
