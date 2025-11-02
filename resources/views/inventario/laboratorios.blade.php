@extends('layouts.app')
@section('title', 'Laboratorios')
@section('page-title', 'Maestro de Laboratorios')
@section('breadcrumbs')
    <li class="breadcrumb-item">Inventario</li>
    <li class="breadcrumb-item active" aria-current="page">Laboratorios</li>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h5 class="card-title m-0">Laboratorios</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('contador.inventario.laboratorios') }}" class="mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="q" placeholder="Buscar por Nombre..." value="{{ $filtros['q'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filtrar</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('contador.inventario.laboratorios') }}" class="btn btn-secondary w-100">Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Descripción (Laboratorio)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laboratorios as $lab)
                        <tr>
                            <td><code>{{ $lab->CodLab }}</code></td>
                            <td><strong>{{ $lab->Descripcion }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center p-4 text-muted">No se encontraron laboratorios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end">
            {{ $laboratorios->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection