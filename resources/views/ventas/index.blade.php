@extends('layouts.app')
@section('title', 'Ventas y Facturación')
@section('page-title', 'Ventas y Facturación')

@section('breadcrumbs')
    <li class="breadcrumb-item">Ventas</li>
    <li class="breadcrumb-item active" aria-current="page">Listado</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <a href="{{ route('contador.facturas.create') }}" class="btn btn-primary mb-3">
            <i class="fas fa-plus me-1"></i> Nueva Venta (Factura/Boleta)
        </a>
    </div>
</div>

<div class="card shadow">
    <div class="card-header">
        <h5 class="card-title m-0">Historial de Documentos de Venta</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('contador.facturas.index') }}" class="mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="q" placeholder="Buscar por N° Documento o Cliente..." value="{{ $filtros['q'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <select name="estado" class="form-select">
                        <option value="activas" @selected(($filtros['estado'] ?? 'activas') == 'activas')>Activas</option>
                        <option value="anuladas" @selected(($filtros['estado'] ?? '') == 'anuladas')>Anuladas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filtrar</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Documento</th>
                        <th>Cliente</th>
                        <th>Fecha Emisión</th>
                        <th>Vencimiento</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facturas as $factura)
                        <tr>
                            <td>
                                <strong>{{ $factura->Numero }}</strong>
                                <br><small class="text-muted">{{ $factura->Tipo == 1 ? 'Factura' : 'Boleta' }}</small>
                            </td>
                            <td>{{ $factura->Cliente }}</td>
                            <td>{{ \Carbon\Carbon::parse($factura->Fecha)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($factura->FechaV)->format('d/m/Y') }}</td>
                            <td class="text-end fw-bold">S/ {{ number_format($factura->Total, 2) }}</td>
                            <td class="text-center">
                                @if($factura->Eliminado)
                                    <span class="badge bg-danger">Anulado</span>
                                @else
                                    <span class="badge bg-success">Activo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('contador.facturas.show', ['numero' => $factura->Numero, 'tipo' => $factura->Tipo]) }}" 
                                   class="btn btn-sm btn-info" 
                                   title="Ver/Imprimir">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center p-4 text-muted">No se encontraron ventas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end">
            {{ $facturas->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection