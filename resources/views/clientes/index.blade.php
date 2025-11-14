@extends('layouts.app')

@section('title', 'Libro de Clientes')
@section('page-title', 'Libro de Clientes')

@section('breadcrumbs')
    <li class="breadcrumb-item">Contabilidad</li>
    <li class="breadcrumb-item active" aria-current="page">Libro de Clientes</li>
@endsection

@push('styles')
    <link href="{{ asset('css/contabilidad/clientes/index.css') }}" rel="stylesheet">
@endpush

@section('content')
    {{-- === DASHBOARD RESUMEN === --}}
<div class="clientes-container">
    {{-- === HEADER CON BOTÓN === --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-address-book me-2 text-primary"></i>
                Gestión de Clientes
            </h2>
            <p class="text-muted mb-0">Administra tu cartera de clientes</p>
        </div>
        <a href="{{ route('contador.clientes.crear') }}" class="btn btn-primary btn-lg btn-nuevo-cliente-header shadow-sm">
            <i class="fas fa-plus-circle me-2"></i>
            Nuevo Cliente
        </a>
    </div>

    {{-- === RESUMEN DE ESTADÍSTICAS === --}}
    <div class="row mb-4 g-3">
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body resumen-card p-4">
                    <div class="resumen-item">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper bg-primary-light me-3">
                                <i class="fas fa-users text-primary"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase">Total Clientes</small>
                                <h3 class="fw-bold mb-0">{{ $resumenGeneral['total_clientes'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body resumen-card p-4">
                    <div class="resumen-item">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper bg-success-light me-3">
                                <i class="fas fa-user-check text-success"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase">Clientes Activos</small>
                                <h3 class="fw-bold mb-0 text-success">{{ $resumenGeneral['clientes_activos'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body resumen-card p-4">
                    <div class="resumen-item">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper bg-danger-light me-3">
                                <i class="fas fa-wallet text-danger"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase">Total Cartera</small>
                                <h3 class="fw-bold mb-0 text-danger">S/ {{ number_format($resumenGeneral['total_cartera'], 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body resumen-card p-4">
                    <div class="resumen-item">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper bg-warning-light me-3">
                                <i class="fas fa-crown text-warning"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase">Mayor Comprador</small>
                                @if($resumenGeneral['mayor_deudor'])
                                    <h6 class="fw-bold mb-0" title="{{ $resumenGeneral['mayor_deudor']->Razon }} (S/ {{ number_format($resumenGeneral['mayor_deudor']->SaldoTotal, 0) }})">
                                        {{ Str::limit($resumenGeneral['mayor_deudor']->Razon, 15) }}
                                    </h6>
                                @else
                                    <h6 class="fw-bold mb-0">N/A</h6>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- === TABLA DE CLIENTES === --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title m-0 fw-bold">
                <i class="fas fa-address-book me-2 text-primary"></i>
                Lista de Clientes Registrados
            </h5>
        </div>
        
        <div class="card-body p-4">
            {{-- === FILTROS === --}}
            <form method="GET" action="{{ route('contador.clientes.index') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="busqueda" class="form-label fw-medium">
                            <i class="fas fa-search me-1"></i>
                            Buscar Cliente
                        </label>
                        <input 
                            type="text" 
                            class="form-control form-control-lg" 
                            name="busqueda" 
                            id="busqueda" 
                            value="{{ $filtros['busqueda'] ?? '' }}"
                            placeholder="Escriba la razón social o documento del cliente..."
                        >
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-filter me-1"></i>
                            Filtrar
                        </button>
                    </div>
                    
                    <div class="col-md-2">
                        <a href="{{ route('contador.clientes.index') }}" class="btn btn-outline-secondary btn-lg w-100">
                            <i class="fas fa-eraser me-1"></i>
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>

            {{-- === TABLA === --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle tabla-clientes">
                    <thead>
                        <tr>
                            <th class="border-0">
                                <i class="fas fa-building me-1"></i>
                                Razón Social
                            </th>
                            <th class="border-0">
                                <i class="fas fa-id-card me-1"></i>
                                Documento
                            </th>
                            <th class="border-0">
                                <i class="fas fa-phone me-1"></i>
                                Contacto
                            </th>
                            <th class="border-0">
                                <i class="fas fa-user-tie me-1"></i>
                                Vendedor
                            </th>
                            <th class="text-end border-0">
                                <i class="fas fa-money-bill-wave me-1"></i>
                                Deuda Pendiente
                            </th>
                            <th class="text-center border-0">
                                <i class="fas fa-cogs me-1"></i>
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        @forelse($clientes as $cli)
                            @php
                                $saldo = $saldosPorCliente[$cli->Codclie] ?? (object)['saldo_pendiente' => 0];
                            @endphp
                            
                            <tr>
                                <td>
                                    <div class="fw-bold mb-1">{{ $cli->Razon }}</div>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $cli->Direccion ?? 'Sin dirección' }}
                                    </small>
                                </td>
                                
                                <td>
                                    <span class="badge bg-light text-dark border fw-medium">
                                        <i class="fas fa-id-badge me-1"></i>
                                        {{ $cli->Documento }}
                                    </span>
                                </td>
                                
                                <td>
                                    <div class="mb-1">
                                        <i class="fas fa-phone me-1 text-success"></i>
                                        <span>{{ $cli->Telefono1 ?? $cli->Celular ?? 'Sin teléfono' }}</span>
                                    </div>
                                    <div>
                                        <i class="fas fa-envelope me-1 text-info"></i>
                                        <small class="text-muted">{{ $cli->Email ?? 'Sin email' }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="badge bg-warning-light text-warning fw-medium">
                                        <i class="fas fa-user me-1"></i>
                                        {{ $cli->Vendedor ?? 'N/A' }}
                                    </span>
                                </td>
                                
                                <td class="text-end">
                                    <span class="badge {{ $saldo->saldo_pendiente > 0 ? 'bg-danger-light text-danger' : 'bg-success-light text-success' }} fs-6 fw-bold px-3 py-2">
                                        <i class="fas fa-{{ $saldo->saldo_pendiente > 0 ? 'exclamation-triangle' : 'check-circle' }} me-1"></i>
                                        S/ {{ number_format($saldo->saldo_pendiente, 2) }}
                                    </span>
                                </td>
                                
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a 
                                            href="{{ route('contador.clientes.show', $cli->Codclie) }}" 
                                            class="btn btn-sm btn-outline-info"
                                            title="Ver Estado de Cuenta"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <a 
                                            href="{{ route('contador.clientes.editar', $cli->Codclie) }}" 
                                            class="btn btn-sm btn-outline-warning"
                                            title="Editar Cliente"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                        <h5 class="text-muted">No se encontraron clientes</h5>
                                        <p class="text-muted">Intenta modificar los filtros de búsqueda o agrega un nuevo cliente.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- === PAGINACIÓN === --}}
            <div class="d-flex justify-content-end mt-4">
                {{ $clientes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection