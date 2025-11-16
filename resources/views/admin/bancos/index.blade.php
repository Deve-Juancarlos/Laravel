@extends('layouts.admin')

@section('title', 'Gestión de Bancos')

@push('styles')
    <link href="{{ asset('css/admin/gestion-bancos.css') }}" rel="stylesheet">
@endpush

@section('header-content')
<div>
    <h1 class="h3 mb-0">Gestión de Cuentas Bancarias</h1>
    <p class="text-muted mb-0">Control de saldos y movimientos bancarios</p>
</div>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item active">Bancos</li>
@endsection

@section('content')

<!-- Resumen General -->
<div class="gestion-bancos">
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fas fa-university fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Cuentas</h6>
                            <h3 class="mb-0">{{ $resumen['total_cuentas'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Saldo Total S/</h6>
                            <h3 class="mb-0">{{ number_format($resumen['saldo_total_soles'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded p-3">
                                <i class="fas fa-dollar-sign fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Saldo Total $</h6>
                            <h3 class="mb-0">{{ number_format($resumen['saldo_total_dolares'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded p-3">
                                <i class="fas fa-chart-line fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Flujo del Mes</h6>
                            <h3 class="mb-0 {{ ($resumen['total_ingresos_mes'] - $resumen['total_egresos_mes']) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($resumen['total_ingresos_mes'] - $resumen['total_egresos_mes'], 2) }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.bancos.estadisticas') }}" class="btn btn-primary">
                            <i class="fas fa-chart-bar me-2"></i>Estadísticas
                        </a>
                        <a href="{{ route('admin.bancos.saldos') }}" class="btn btn-info">
                            <i class="fas fa-balance-scale me-2"></i>Consolidado de Saldos
                        </a>
                        <a href="{{ route('admin.bancos.cheques-pendientes') }}" class="btn btn-warning">
                            <i class="fas fa-money-check me-2"></i>Cheques Pendientes
                        </a>
                        <a href="{{ route('admin.bancos.transferencias') }}" class="btn btn-secondary">
                            <i class="fas fa-exchange-alt me-2"></i>Transferencias
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Cuentas Bancarias -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Listado de Cuentas Bancarias
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Banco</th>
                            <th>Número de Cuenta</th>
                            <th>Tipo</th>
                            <th>Moneda</th>
                            <th class="text-end">Saldo Actual</th>
                            <th class="text-center">Estado</th>
                            <th>Última Actualización</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cuentas as $cuenta)
                        <tr>
                            <td>
                                <i class="fas fa-university text-primary me-2"></i>
                                <strong>{{ $cuenta->Banco }}</strong>
                            </td>
                            <td>
                                <code>{{ $cuenta->Cuenta }}</code>
                            </td>
                            <td>
                                {{-- <span class="badge bg-secondary">{{ $cuenta->TipoCuenta }}</span> --}}

                            </td>
                            <td>
                                @if($cuenta->Moneda == 'SOLES')
                                    <span class="badge bg-success">S/</span>
                                @else
                                    <span class="badge bg-info">$</span>
                                @endif
                                {{ $cuenta->Moneda }}
                            </td>
                            <td class="text-end">
                                <strong class="fs-5 {{ ($cuenta->saldoactual ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $cuenta->Moneda == 'SOLES' ? 'S/' : '$' }} 
                                    {{ number_format($cuenta->saldoactual ?? 0, 2) }}
                                </strong>
                            </td>
                            <td class="text-center">
                                {{-- Si quieres, podrías mostrar estado calculado o eliminar esta columna --}}
                                <span class="badge bg-secondary">
                                    <i class="fas fa-info-circle me-1"></i> N/A
                                </span>
                            </td>
                            <td>
                                @if($cuenta->ultimaactualizacion)
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($cuenta->ultimaactualizacion)->format('d/m/Y H:i') }}
                                    </small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.bancos.movimientos', $cuenta->Cuenta) }}" 
                                    class="btn btn-outline-primary" 
                                    title="Ver Movimientos">
                                        <i class="fas fa-list-alt"></i>
                                    </a>
                                    <a href="{{ route('admin.bancos.conciliacion', $cuenta->Cuenta) }}" 
                                    class="btn btn-outline-info" 
                                    title="Conciliación">
                                        <i class="fas fa-balance-scale"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-university fa-3x mb-3 d-block"></i>
                                No hay cuentas bancarias registradas
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
