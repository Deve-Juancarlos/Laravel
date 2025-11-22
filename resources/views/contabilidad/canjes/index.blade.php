@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Canjes de Facturas por Letras')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-exchange-alt"></i> Canjes de Facturas por Letras</h2>
                <a href="{{ route('contador.canjes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Canje
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filtros de Búsqueda
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('contador.canjes.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label>Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-md-3">
                        <label>Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-md-4">
                        <label>Cliente</label>
                        <input type="text" name="cliente" class="form-control" placeholder="Buscar cliente..." value="{{ request('cliente') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Canjes -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Historial de Canjes
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>RUC/DNI</th>
                            <th>Factura Origen</th>
                            <th>Letra Destino</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($canjes as $canje)
                            <tr>
                                <td>{{ $canje->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($canje->fecha_canje)->format('d/m/Y H:i') }}</td>
                                <td>{{ $canje->cliente_nombre }}</td>
                                <td>{{ $canje->cliente_doc }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $canje->factura_origen }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $canje->letra_destino }}</span>
                                </td>
                                <td>{{ $canje->usuario_nombre ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-inbox"></i> No hay canjes registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $canjes->links() }}
            </div>
        </div>
    </div>
</div>

<style>
.badge {
    font-size: 0.85rem;
    padding: 0.4rem 0.6rem;
}
</style>
@endsection