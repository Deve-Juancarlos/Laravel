@extends('layouts.app')
@section('title', 'Órdenes de Compra')
@section('page-title', 'Gestión de Compras')

@section('breadcrumbs')
    <li class="breadcrumb-item">Compras</li>
    <li class="breadcrumb-item active" aria-current="page">Órdenes de Compra</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <a href="{{ route('contador.compras.create') }}" class="btn btn-primary mb-3">
            <i class="fas fa-plus me-1"></i> Nueva Orden de Compra
        </a>
    </div>
</div>

<div class="card shadow">
    <div class="card-header">
        <h5 class="card-title m-0">Historial de Órdenes de Compra</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('contador.compras.index') }}" class="mb-3">
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="q" class="form-label">Buscar (N° Orden o Proveedor)</label>
                    <input type="text" class="form-control" name="q" id="q" value="{{ $filtros['q'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="PENDIENTE" @selected(($filtros['estado'] ?? '') == 'PENDIENTE')>Pendiente</option>
                        <option value="RECIBIDO" @selected(($filtros['estado'] ?? '') == 'RECIBIDO')>Recibido</option>
                        <option value="ANULADO" @selected(($filtros['estado'] ?? '') == 'ANULADO')>Anulado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 mt-4"><i class="fas fa-filter"></i> Filtrar</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Orden (O/C)</th>
                        <th>Proveedor</th>
                        <th>Fecha Emisión</th>
                        <th>Fecha Entrega</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordenes as $oc)
                        <tr>
                            <td><strong>{{ $oc->Serie }}-{{ $oc->Numero }}</strong></td>
                            <td>{{ $oc->ProveedorNombre }}</td>
                            <td>{{ \Carbon\Carbon::parse($oc->FechaEmision)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($oc->FechaEntrega)->format('d/m/Y') }}</td>
                            <td class="text-end fw-bold">S/ {{ number_format($oc->Total, 2) }}</td>
                            <td class="text-center">
                                @if($oc->Estado == 'PENDIENTE')
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                @elseif($oc->Estado == 'RECIBIDO')
                                    <span class="badge bg-success">Recibido</span>
                                @else
                                    <span class="badge bg-danger">Anulado</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- ¡CORREGIDO! Solo botón de Ver --}}
                                <a href="{{ route('contador.compras.show', $oc->Id) }}" class="btn btn-sm btn-info" title="Ver Detalle">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center p-4 text-muted">No se encontraron órdenes de compra.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end">
            {{ $ordenes->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection