@extends('layouts.app')
@section('title', 'Proveedores')
@section('page-title', 'Maestro de Proveedores')

@section('breadcrumbs')
    <li class="breadcrumb-item">Compras</li>
    <li class="breadcrumb-item active" aria-current="page">Proveedores</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <a href="{{ route('contador.proveedores.crear') }}" class="btn btn-primary mb-3">
            <i class="fas fa-plus me-1"></i> Nuevo Proveedor
        </a>
    </div>
</div>

<div class="card shadow">
    <div class="card-header">
        <h5 class="card-title m-0">Proveedores Registrados</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('contador.proveedores.index') }}" class="mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="q" placeholder="Buscar por RUC o Razón Social..." value="{{ $filtros['q'] ?? '' }}">
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
                        <th>Razón Social</th>
                        <th>RUC</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proveedores as $prov)
                        <tr>
                            <td>
                                <strong>{{ $prov->RazonSocial }}</strong>
                                <br><small class="text-muted">{{ $prov->Direccion ?? 'Sin dirección' }}</small>
                            </td>
                            <td>{{ $prov->Ruc }}</td>
                            <td>
                                {{ $prov->Contacto }}
                                <br><small class="text-muted">{{ $prov->Email }}</small>
                            </td>
                            <td>{{ $prov->Telefono }}</td>
                            <td class="text-center">
                                <a href="#" class="btn btn-sm btn-warning" title="Editar Proveedor">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-4 text-muted">No se encontraron proveedores.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end">
            {{ $proveedores->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection