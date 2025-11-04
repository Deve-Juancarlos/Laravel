@extends('layouts.app')

@section('title', 'Transferencias Bancarias')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
    <style>
        .transferencias-gradient-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
        }

        .transferencias-nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 0 0 1rem 1rem;
            padding: 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .transferencias-nav .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 0;
            position: relative;
        }

        .transferencias-nav .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .transferencias-nav .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }

        .kpi-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .kpi-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 1rem;
        }

        .kpi-icon.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .kpi-icon.success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .kpi-icon.warning {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        }

        .kpi-icon.info {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
        }

        .kpi-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .kpi-label {
            color: #6c757d;
            font-weight: 500;
            margin-bottom: 0;
        }

        .filters-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 2rem;
        }

        .filters-card .card-body {
            padding: 2rem;
        }

        .filters-card .form-label {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
            transform: translateY(-1px);
        }

        .transferencias-table {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
        }

        .transferencias-table .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: none;
            padding: 1.5rem 2rem;
            border-radius: 1rem 1rem 0 0;
        }

        .transferencias-table .card-header h6 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0;
        }

        .transferencias-table .table {
            margin-bottom: 0;
        }

        .transferencias-table .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 1rem;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .transferencias-table .table tbody td {
            padding: 1rem;
            border-color: #f1f3f4;
            vertical-align: middle;
        }

        .transferencias-table .table tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .badge-estado {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-completa {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .badge-pendiente {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: white;
        }

        .monto-amount {
            font-weight: 700;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .resumen-alert {
            background: linear-gradient(135deg, rgba(23, 162, 184, 0.1) 0%, rgba(111, 66, 193, 0.1) 100%);
            border: 1px solid rgba(23, 162, 184, 0.2);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .resumen-alert .alert-heading {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .resumen-alert p {
            color: #495057;
            margin-bottom: 0;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }

        .pagination-wrapper {
            background: #f8f9fa;
            border-top: none;
            border-radius: 0 0 1rem 1rem;
            padding: 1rem 2rem;
        }

        .pagination .page-link {
            color: #667eea;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
            margin: 0 0.25rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
            transform: translateY(-1px);
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }

        @media (max-width: 768px) {
            .transferencias-gradient-header {
                padding: 1.5rem 0;
            }

            .transferencias-nav .nav-link {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            .kpi-card {
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .kpi-icon {
                width: 60px;
                height: 60px;
                font-size: 20px;
            }

            .kpi-value {
                font-size: 1.5rem;
            }

            .filters-card .card-body {
                padding: 1.5rem;
            }
        }
    </style>
@endpush

@section('page-title')
    <div class="transferencias-gradient-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <i class="fas fa-exchange-alt me-3" style="font-size: 2rem;"></i>
                <div>
                    <h1 class="mb-1">Transferencias Bancarias</h1>
                    <p class="mb-0 opacity-75">Registro de transferencias entre cuentas propias</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Transferencias</li>
@endsection

@section('content')
<div class="container-fluid">

    <!-- Navegación del Módulo de Bancos -->
    <nav class="nav nav-tabs transferencias-nav">
        <a class="nav-link" href="{{ route('contador.bancos.index') }}">
            <i class="fas fa-home me-1"></i> Dashboard Bancos
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.flujo-diario') }}">
            <i class="fas fa-calendar-day me-1"></i> Flujo Diario
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.diario') }}">
            <i class="fas fa-calendar-alt me-1"></i> Reporte Diario
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.mensual') }}">
            <i class="fas fa-calendar-week me-1"></i> Resumen Mensual
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.conciliacion') }}">
            <i class="fas fa-tasks me-1"></i> Conciliación
        </a>
        <a class="nav-link active" href="{{ route('contador.bancos.transferencias') }}">
            <i class="fas fa-exchange-alt me-1"></i> Transferencias
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.reporte') }}">
            <i class="fas fa-file-invoice me-1"></i> Reportes
        </a>
    </nav>

    <!-- KPIs de Transferencias -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon primary">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="kpi-value">{{ $resumenTransferencias['total_transferencias'] }}</div>
                <p class="kpi-label">Total Transferencias</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon success">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="kpi-value">S/ {{ number_format($resumenTransferencias['monto_total'], 2) }}</div>
                <p class="kpi-label">Monto Total</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="kpi-value">{{ $resumenTransferencias['transferencias_completas'] ?? 0 }}</div>
                <p class="kpi-label">Transferencias Completas</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="kpi-value">{{ $resumenTransferencias['transferencias_pendientes'] ?? 0 }}</div>
                <p class="kpi-label">Transferencias Pendientes</p>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm filters-card">
         <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.transferencias') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="fecha_fin">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-gradient w-100">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Resumen -->
    <div class="alert resumen-alert" role="alert">
        <h5 class="alert-heading">
            <i class="fas fa-info-circle me-2"></i>Resumen de Transferencias
        </h5>
        <p>
            Se encontraron <strong>{{ $resumenTransferencias['total_transferencias'] }}</strong> transferencias por un monto total de <strong>S/ {{ number_format($resumenTransferencias['monto_total'], 2) }}</strong>.
        </p>
    </div>

    <!-- Tabla de Transferencias -->
    <div class="card transferencias-table">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Detalle de Transferencias
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><i class="fas fa-calendar me-1"></i>Fecha</th>
                            <th><i class="fas fa-file-alt me-1"></i>Documento</th>
                            <th><i class="fas fa-arrow-right me-1"></i>Cuenta Origen</th>
                            <th><i class="fas fa-arrow-left me-1"></i>Cuenta Destino</th>
                            <th class="text-end"><i class="fas fa-dollar-sign me-1"></i>Monto</th>
                            <th class="text-center"><i class="fas fa-check-circle me-1"></i>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transferencias as $trf)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="date-badge me-2">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                    </div>
                                    <span class="fw-600">{{ \Carbon\Carbon::parse($trf->Fecha)->format('d/m/Y') }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="document-badge">
                                    <i class="fas fa-file-invoice me-1"></i>
                                    {{ $trf->Documento }}
                                </span>
                            </td>
                            <td>
                                <div class="account-info">
                                    <div class="fw-600 text-primary">{{ $trf->banco_origen }}</div>
                                    <small class="text-muted">({{ $trf->cuenta_origen }})</small>
                                </div>
                            </td>
                            <td>
                                <div class="account-info">
                                    <div class="fw-600 text-success">{{ $trf->banco_destino }}</div>
                                    <small class="text-muted">({{ $trf->cuenta_destino }})</small>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="monto-amount">S/ {{ number_format($trf->Monto, 2) }}</span>
                            </td>
                            <td class="text-center">
                                @if($trf->estado_transferencia == 'COMPLETA')
                                    <span class="badge badge-estado badge-completa">
                                        <i class="fas fa-check me-1"></i>Completa
                                    </span>
                                @else
                                    <span class="badge badge-estado badge-pendiente">
                                        <i class="fas fa-clock me-1"></i>Pendiente
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-exchange-alt"></i>
                                <h5>No hay transferencias</h5>
                                <p>No se encontraron transferencias en este período.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transferencias->hasPages())
            <div class="card-footer pagination-wrapper">
                {{ $transferencias->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection