@use('Illuminate\Support\Str')
@extends('layouts.app')
@section('title', 'Notas de Crédito')
@section('page-title', 'Notas de Crédito')

@section('breadcrumbs')
    <li class="breadcrumb-item">Ventas</li>
    <li class="breadcrumb-item active" aria-current="page">Notas de Crédito</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <a href="{{ route('contador.notas-credito.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nueva Nota de Crédito
        </a>
    </div>
    <div class="col-md-6 text-end">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-secondary">
                <i class="fas fa-download me-1"></i> Exportar
            </button>
            <button type="button" class="btn btn-outline-secondary">
                <i class="fas fa-filter me-1"></i> Filtros Avanzados
            </button>
        </div>
    </div>
</div>

<div class="card shadow">
    <div class="card-header bg-white">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="card-title m-0">
                    <i class="fas fa-file-invoice text-danger me-2"></i>
                    Historial de Notas de Crédito
                </h5>
            </div>
            <div class="col-md-6">
                <form method="GET" action="{{ route('contador.notas-credito.index') }}">
                    <div class="input-group">
                        <input type="text" 
                               class="form-control" 
                               name="q" 
                               placeholder="Buscar por N° de Nota o Cliente..." 
                               value="{{ $filtros['q'] ?? '' }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(isset($filtros['q']))
                            <a href="{{ route('contador.notas-credito.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 12%;">N° Nota Crédito</th>
                        <th style="width: 10%;">Fecha</th>
                        <th style="width: 25%;">Cliente</th>
                        <th style="width: 12%;">Doc. Afectado</th>
                        <th class="text-center" style="width: 10%;">Tipo</th>
                        <th class="text-end" style="width: 11%;">Monto</th>
                        <th class="text-center" style="width: 10%;">Estado</th>
                        <th class="text-center" style="width: 10%;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notas as $nc)
                        <tr>
                            <td>
                                <strong class="text-primary">{{ $nc->Numero }}</strong>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    {{ \Carbon\Carbon::parse($nc->Fecha)->format('d/m/Y') }}
                                </small>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ Str::limit($nc->ClienteNombre, 30) }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $nc->Documento }}</span>
                            </td>
                            <td class="text-center">
                                @if($nc->TipoNota == 7)
                                    <span class="badge bg-info" title="Devolución de Mercadería">
                                        <i class="fas fa-box-open me-1"></i>DEV
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark" title="Descuento">
                                        <i class="fas fa-percent me-1"></i>DESC
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="text-danger">
                                    S/ {{ number_format($nc->Total, 2) }}
                                </strong>
                            </td>
                            <td class="text-center">
                                @if($nc->Anulado)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-ban me-1"></i>Anulada
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Activa
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('contador.notas-credito.show', $nc->Numero) }}" 
                                       class="btn btn-outline-info" 
                                       title="Ver/Imprimir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$nc->Anulado)
                                        <button type="button" 
                                                class="btn btn-outline-secondary" 
                                                title="Descargar PDF"
                                                onclick="alert('Funcionalidad en desarrollo')">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    <h5>No se encontraron notas de crédito</h5>
                                    <p>Comienza creando tu primera nota de crédito.</p>
                                    <a href="{{ route('contador.notas-credito.create') }}" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus me-1"></i> Crear Primera NC
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($notas->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Mostrando {{ $notas->firstItem() }} - {{ $notas->lastItem() }} de {{ $notas->total() }} registros
            </div>
            <div>
                {{ $notas->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Resumen Rápido --}}
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-invoice fa-2x text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Total NC Mes</h6>
                        <h4 class="mb-0">{{ $notas->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">NC Activas</h6>
                        <h4 class="mb-0">{{ $notas->where('Anulado', 0)->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-dollar-sign fa-2x text-danger"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Monto Total</h6>
                        <h4 class="mb-0">S/ {{ number_format($notas->sum('Total'), 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        cursor: pointer;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
@endpush