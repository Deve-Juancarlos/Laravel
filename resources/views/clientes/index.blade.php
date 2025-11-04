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
        /* === KPIS CARDS === */
        .kpi-card { 
            display: flex; 
            align-items: center; 
            background: #fff; 
            border-radius: 12px; 
            padding: 1.5rem; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        .kpi-icon { 
            width: 50px; 
            height: 50px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin-right: 1.25rem; 
            font-size: 1.5rem; 
            color: #fff; 
        }

        .kpi-content .kpi-label { 
            font-size: 0.875rem; 
            font-weight: 600; 
            color: #6c757d; 
            text-transform: uppercase; 
            margin-bottom: 0.25rem; 
        }

        .kpi-content .kpi-value { 
            font-size: 1.5rem; 
            font-weight: 700; 
            color: #343a40; 
        }

        /* === RESUMEN CARD === */
        .resumen-card .card-body {
            padding: 2rem;
        }

        .resumen-item {
            text-align: center;
            padding: 1rem;
        }

        .resumen-item h6 {
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .resumen-item h4 {
            margin-bottom: 0;
            font-size: 1.5rem;
        }

        /* === BOTÓN NUEVO CLIENTE === */
        .btn-nuevo-cliente {
            min-height: 150px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-nuevo-cliente:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,123,255,0.3);
        }

        /* === TABLE IMPROVEMENTS === */
        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
            padding: 0.75rem;
        }

        /* === ACCIONES BUTTONS === */
        .btn-group-actions {
            gap: 0.5rem;
        }

        /* === RESPONSIVE === */
        @media (max-width: 768px) {
            .resumen-card .card-body {
                padding: 1.5rem;
            }
            
            .resumen-item {
                padding: 0.75rem;
            }
            
            .resumen-item h4 {
                font-size: 1.25rem;
            }
            
            .btn-group-actions {
                display: flex;
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
    {{-- === DASHBOARD RESUMEN === --}}
    <div class="row mb-4">
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-body resumen-card">
                    <div class="row">
                        <div class="col">
                            <div class="resumen-item">
                                <h6 class="text-muted">
                                    <i class="fas fa-users me-1"></i>
                                    Total Clientes
                                </h6>
                                <h4 class="fw-bold">
                                    {{ $resumenGeneral['total_clientes'] }}
                                </h4>
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="resumen-item">
                                <h6 class="text-muted">
                                    <i class="fas fa-user-check me-1"></i>
                                    Clientes Activos
                                </h6>
                                <h4 class="fw-bold text-success">
                                    {{ $resumenGeneral['clientes_activos'] }}
                                </h4>
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="resumen-item">
                                <h6 class="text-muted">
                                    <i class="fas fa-wallet me-1"></i>
                                    Total Cartera (Deuda)
                                </h6>
                                <h4 class="fw-bold text-danger">
                                    S/ {{ number_format($resumenGeneral['total_cartera'], 2) }}
                                </h4>
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="resumen-item">
                                <h6 class="text-muted">
                                    <i class="fas fa-crown me-1"></i>
                                    Mayor Comprador
                                </h6>
                                
                                @if($resumenGeneral['mayor_deudor'])
                                    <h4 
                                        class="fw-bold" 
                                        title="{{ $resumenGeneral['mayor_deudor']->Razon }} (S/ {{ number_format($resumenGeneral['mayor_deudor']->SaldoTotal, 0) }})"
                                    >
                                        {{ Str::limit($resumenGeneral['mayor_deudor']->Razon, 15) }}
                                    </h4>
                                @else
                                    <h4 class="fw-bold">N/A</h4>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 d-flex">
            <a 
                href="{{ route('contador.clientes.crear') }}" 
                class="btn btn-primary btn-lg w-100 btn-nuevo-cliente d-flex flex-column justify-content-center align-items-center"
            >
                <i class="fas fa-plus-circle fa-2x mb-2"></i>
                <span class="h5 mb-0">Nuevo Cliente</span>
            </a>
        </div>
    </div>

    {{-- === TABLA DE CLIENTES === --}}
    <div class="card shadow">
        <div class="card-header">
            <h5 class="card-title m-0">
                <i class="fas fa-address-book me-2"></i>
                Lista de Clientes Registrados
            </h5>
        </div>
        
        <div class="card-body">
            {{-- === FILTROS === --}}
            <form method="GET" action="{{ route('contador.clientes.index') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="busqueda" class="form-label">
                            <i class="fas fa-search me-1"></i>
                            Buscar (Razón Social o RUC)
                        </label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="busqueda" 
                            id="busqueda" 
                            value="{{ $filtros['busqueda'] ?? '' }}"
                            placeholder="Escriba el nombre o documento del cliente..."
                        >
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i>
                            <span class="ms-1">Filtrar</span>
                        </button>
                    </div>
                    
                    <div class="col-md-2">
                        <a href="{{ route('contador.clientes.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-eraser me-1"></i>
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>

            {{-- === TABLA === --}}
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <i class="fas fa-building me-1"></i>
                                Razón Social
                            </th>
                            <th>
                                <i class="fas fa-id-card me-1"></i>
                                Documento (RUC/DNI)
                            </th>
                            <th>
                                <i class="fas fa-phone me-1"></i>
                                Contacto
                            </th>
                            <th>
                                <i class="fas fa-user-tie me-1"></i>
                                Vendedor
                            </th>
                            <th class="text-end">
                                <i class="fas fa-money-bill-wave me-1"></i>
                                Deuda Pendiente
                            </th>
                            <th class="text-center">
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
                                    <div class="fw-bold">
                                        {{ $cli->Razon }}
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $cli->Direccion ?? 'Sin dirección' }}
                                    </small>
                                </td>
                                
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-id-badge me-2 text-primary"></i>
                                        <span class="fw-medium">
                                            {{ $cli->Documento }}
                                        </span>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="mb-1">
                                        <i class="fas fa-phone me-1 text-success"></i>
                                        <span class="small">
                                            {{ $cli->Telefono1 ?? $cli->Celular ?? 'Sin teléfono' }}
                                        </span>
                                    </div>
                                    <div>
                                        <i class="fas fa-envelope me-1 text-info"></i>
                                        <small class="text-muted">
                                            {{ $cli->Email ?? 'Sin email' }}
                                        </small>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="fw-medium">
                                        <i class="fas fa-user me-1 text-warning"></i>
                                        {{ $cli->Vendedor ?? 'N/A' }}
                                    </div>
                                </td>
                                
                                <td class="text-end">
                                    <div class="fw-bold {{ $saldo->saldo_pendiente > 0 ? 'text-danger' : 'text-success' }}">
                                        <i class="fas fa-{{ $saldo->saldo_pendiente > 0 ? 'exclamation-triangle' : 'check-circle' }} me-1"></i>
                                        S/ {{ number_format($saldo->saldo_pendiente, 2) }}
                                    </div>
                                </td>
                                
                                <td class="text-center">
                                    <div class="btn-group-actions d-flex justify-content-center">
                                        <a 
                                            href="{{ route('contador.clientes.show', $cli->Codclie) }}" 
                                            class="btn btn-sm btn-info" 
                                            title="Ver Estado de Cuenta"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <a 
                                            href="{{ route('contador.clientes.editar', $cli->Codclie) }}" 
                                            class="btn btn-sm btn-warning" 
                                            title="Editar Cliente"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center p-5">
                                    <div class="text-muted">
                                        <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                        <h5>No se encontraron clientes</h5>
                                        <p>Intenta modificar los filtros de búsqueda.</p>
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
@endsection