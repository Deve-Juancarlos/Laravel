@extends('layouts.app')
@section('title', 'Ventas y Facturación')
@section('page-title', 'Ventas y Facturación')

@section('breadcrumbs')
    <li class="breadcrumb-item">Ventas</li>
    <li class="breadcrumb-item active" aria-current="page">Listado</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/ventas/index.css') }}">
@endpush

@section('content')

{{-- Mensajes de Éxito/Error --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Tarjetas de Estadísticas --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Ventas Hoy</p>
                        <h3 class="mb-0 fw-bold">{{ $facturas->where('Fecha', '>=', now()->startOfDay())->count() }}</h3>
                    </div>
                    <i class="fas fa-file-invoice stat-icon text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card shadow-sm" style="border-left-color: #28a745;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Mes</p>
                        <h3 class="mb-0 fw-bold text-success">S/ {{ number_format($facturas->where('Fecha', '>=', now()->startOfMonth())->sum('Total'), 2) }}</h3>
                    </div>
                    <i class="fas fa-chart-line stat-icon text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card shadow-sm" style="border-left-color: #ffc107;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Facturas</p>
                        <h3 class="mb-0 fw-bold text-warning">{{ $facturas->where('Tipo', 1)->count() }}</h3>
                    </div>
                    <i class="fas fa-receipt stat-icon text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card shadow-sm" style="border-left-color: #17a2b8;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Boletas</p>
                        <h3 class="mb-0 fw-bold text-info">{{ $facturas->where('Tipo', 2)->count() }}</h3>
                    </div>
                    <i class="fas fa-file-alt stat-icon text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Sección de Búsqueda Mejorada --}}
<div class="search-section shadow">
    <form method="GET" action="{{ route('contador.facturas.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-bold mb-2">
                    <i class="fas fa-search me-1"></i> Buscar Documento o Cliente
                </label>
                <input type="text" class="form-control" name="q" 
                       placeholder="Ej: F001-00000123 o Razón Social..." 
                       value="{{ $filtros['q'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold mb-2">
                    <i class="fas fa-filter me-1"></i> Estado
                </label>
                <select name="estado" class="form-select">
                    <option value="activas" @selected(($filtros['estado'] ?? 'activas') == 'activas')>
                        Activas
                    </option>
                    <option value="anuladas" @selected(($filtros['estado'] ?? '') == 'anuladas')>
                        Anuladas
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-light w-100 fw-bold">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('contador.facturas.create') }}" class="btn btn-success w-100 fw-bold">
                    <i class="fas fa-plus me-1"></i> Nueva Venta
                </a>
            </div>
        </div>
    </form>
</div>

{{-- Tabla de Ventas --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0 fw-bold">
                <i class="fas fa-clipboard-list me-2 text-primary"></i>
                Historial de Documentos de Venta
            </h5>
            <span class="badge bg-primary">{{ $facturas->total() }} documentos</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-ventas table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3">
                            <i class="fas fa-file-invoice me-1 text-muted"></i> Documento
                        </th>
                        <th class="py-3">
                            <i class="fas fa-user me-1 text-muted"></i> Cliente
                        </th>
                        <th class="py-3">
                            <i class="fas fa-calendar me-1 text-muted"></i> Emisión
                        </th>
                        <th class="py-3">
                            <i class="fas fa-calendar-check me-1 text-muted"></i> Vencimiento
                        </th>
                        <th class="text-end py-3">
                            <i class="fas fa-dollar-sign me-1 text-muted"></i> Total
                        </th>
                        <th class="text-center py-3">Estado</th>
                        <th class="text-center py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facturas as $factura)
                        <tr>
                            <td class="px-4">
                                <div>
                                    <strong class="d-block text-primary">{{ $factura->Numero }}</strong>
                                    <span class="badge badge-documento {{ $factura->Tipo == 1 ? 'bg-info' : 'bg-secondary' }}">
                                        {{ $factura->Tipo == 1 ? 'FACTURA' : 'BOLETA' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong class="d-block">{{ Str::limit($factura->Cliente, 30) }}</strong>
                                </div>
                            </td>
                            <td>
                                <i class="far fa-calendar-alt me-1 text-muted"></i>
                                {{ \Carbon\Carbon::parse($factura->Fecha)->format('d/m/Y') }}
                            </td>
                            <td>
                                @php
                                    $vencimiento = \Carbon\Carbon::parse($factura->FechaV);
                                    $diasRestantes = now()->diffInDays($vencimiento, false);
                                @endphp
                                <i class="far fa-calendar-check me-1 text-muted"></i>
                                {{ $vencimiento->format('d/m/Y') }}
                                @if($diasRestantes < 0 && !$factura->Eliminado)
                                    <span class="badge bg-danger ms-1">Vencido</span>
                                @elseif($diasRestantes <= 7 && $diasRestantes >= 0 && !$factura->Eliminado)
                                    <span class="badge bg-warning ms-1">Por vencer</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="text-success fs-6">S/ {{ number_format($factura->Total, 2) }}</strong>
                            </td>
                            <td class="text-center">
                                @if($factura->Eliminado)
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="fas fa-ban me-1"></i> ANULADO
                                    </span>
                                @else
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i> ACTIVO
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('contador.facturas.show', ['numero' => $factura->Numero, 'tipo' => $factura->Tipo]) }}" 
                                       class="btn btn-sm btn-outline-primary btn-action" 
                                       title="Ver Documento">
                                        <i class="fas fa-eye me-1"></i> Ver
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">No se encontraron documentos de venta.</p>
                                <a href="{{ route('contador.facturas.create') }}" class="btn btn-primary mt-3">
                                    <i class="fas fa-plus me-1"></i> Crear Primera Venta
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted">
                Mostrando {{ $facturas->firstItem() ?? 0 }} - {{ $facturas->lastItem() ?? 0 }} 
                de {{ $facturas->total() }} documentos
            </div>
            <div>
                {{ $facturas->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

@endsection