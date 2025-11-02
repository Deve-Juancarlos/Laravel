@extends('layouts.app')

@section('title', 'Libro de Clientes')
@section('page-title', 'Libro de Clientes')

@section('breadcrumbs')
    <li class="breadcrumb-item">Contabilidad</li>
    <li class="breadcrumb-item active" aria-current="page">Libro de Clientes</li>
@endsection

@push('styles')
{{-- Estilos para los KPIs (los mismos de tu dashboard) --}}
<style>
.kpi-card { display: flex; align-items: center; background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
.kpi-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1.25rem; font-size: 1.5rem; color: #fff; }
.kpi-content .kpi-label { font-size: 0.875rem; font-weight: 600; color: #6c757d; text-transform: uppercase; margin-bottom: 0.25rem; }
.kpi-content .kpi-value { font-size: 1.5rem; font-weight: 700; color: #343a40; }
</style>
@endpush


@section('content')

<div class="row mb-3">
    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-body d-flex justify-content-around">
                
                {{-- 
                    ¡CORREGIDO! 
                    Ahora usa $resumenGeneral
                --}}
                <div class="text-center">
                    <h6 class="text-muted">Total Clientes</h6>
                    <h4 class="fw-bold">{{ $resumenGeneral['total_clientes'] }}</h4>
                </div>
                <div class="text-center">
                    <h6 class="text-muted">Clientes Activos</h6>
                    <h4 class="fw-bold text-success">{{ $resumenGeneral['clientes_activos'] }}</h4>
                </div>
                <div class="text-center">
                    <h6 class="text-muted">Total Cartera (Deuda)</h6>
                    <h4 class="fw-bold text-danger">S/ {{ number_format($resumenGeneral['total_cartera'], 2) }}</h4>
                </div>
                <div class="text-center">
                    <h6 class="text-muted">Mayor Deudor</h6>
                    {{-- Validamos si existe un mayor deudor --}}
                    @if($resumenGeneral['mayor_deudor'])
                        <h4 class="fw-bold" title="{{ $resumenGeneral['mayor_deudor']->Razon }} (S/ {{ number_format($resumenGeneral['mayor_deudor']->SaldoTotal, 0) }})">
                            {{ Str::limit($resumenGeneral['mayor_deudor']->Razon, 15) }}
                        </h4>
                    @else
                        <h4 class="fw-bold">N/A</h4>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 d-flex">
        {{-- Esta ruta SÍ existe y está correcta --}}
        <a href="{{ route('contador.clientes.crear') }}" class="btn btn-primary btn-lg w-100 d-flex flex-column justify-content-center align-items-center">
            <i class="fas fa-plus-circle fa-2x mb-2"></i>
            <span class="h5 mb-0">Nuevo Cliente</span>
        </a>
    </div>
</div>

<div class="card shadow">
    <div class="card-header">
        <h5 class="card-title m-0">Lista de Clientes Registrados</h5>
    </div>
    <div class="card-body">
        
        <form method="GET" action="{{ route('contador.clientes.index') }}" class="mb-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="busqueda" class="form-label">Buscar (Razón Social o RUC)</label>
                    <input type="text" class="form-control" name="busqueda" id="busqueda" value="{{ $filtros['busqueda'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('contador.clientes.index') }}" class="btn btn-secondary w-100">Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Razón Social</th>
                        <th>Documento (RUC/DNI)</th>
                        <th>Contacto</th>
                        <th>Vendedor</th>
                        <th class="text-end">Deuda Pendiente</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 
                        ¡CORREGIDO! 
                        Ahora usa $saldosPorCliente para la deuda
                    --}}
                    @forelse($clientes as $cli)
                        @php
                            $saldo = $saldosPorCliente[$cli->Codclie] ?? (object)['saldo_pendiente' => 0];
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $cli->Razon }}</strong>
                                <br><small class="text-muted">{{ $cli->Direccion ?? 'Sin dirección' }}</small>
                            </td>
                            <td>{{ $cli->Documento }}</td>
                            <td>
                                {{ $cli->Telefono1 ?? $cli->Celular }}
                                <br><small class="text-muted">{{ $cli->Email }}</small>
                            </td>
                            <td>{{ $cli->Vendedor ?? 'N/A' }}</td> {{-- Asumiendo que el join trae el nombre --}}
                            <td class="text-end fw-bold {{ $saldo->saldo_pendiente > 0 ? 'text-danger' : 'text-success' }}">
                                S/ {{ number_format($saldo->saldo_pendiente, 2) }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('contador.clientes.show', $cli->Codclie) }}" class="btn btn-sm btn-info" title="Ver Estado de Cuenta">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('contador.clientes.editar', $cli->Codclie) }}" class="btn btn-sm btn-warning" title="Editar Cliente">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-4 text-muted">No se encontraron clientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            {{ $clientes->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection