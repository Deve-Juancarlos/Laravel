@extends('layouts.app')

@section('title', 'Plan de Cuentas')

@push('styles')
    <link href="{{ asset('css/contabilidad/plan-cuentas/index.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-sitemap me-2"></i>Plan de Cuentas</h1>
        <p class="text-muted">Administración de Cuentas Contables (PCGE)</p>
    </div>    
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item active" aria-current="page">Plan de Cuentas</li>
@endsection

@section('content')
<div class="plan-cuentas-view">

    {{-- Alertas --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Filtros --}}
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.plan-cuentas.index') }}">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label" for="search">Buscar</label>
                        <input type="text" id="search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Código o nombre...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="tipo">Tipo de Cuenta</label>
                        <select id="tipo" name="tipo" class="form-select">
                            <option value="">Todos</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo }}" {{ ($filters['tipo'] ?? '') == $tipo ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="activo">Estado</label>
                        <select id="activo" name="activo" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ ($filters['activo'] ?? '') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ ($filters['activo'] ?? '') == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                    </div>
                    <a href="{{ route('contador.plan-cuentas.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Nueva Cuenta
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de Cuentas --}}
    <div class="card shadow-sm table-container">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th class="text-center">Nivel</th>
                            <th>Cuenta Padre</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cuentas as $cuenta)
                        <tr>
                            <td><strong class="text-primary">{{ $cuenta->codigo }}</strong></td>
                            <td>{{ $cuenta->nombre }}</td>
                            <td><span class="badge tipo-{{ strtolower($cuenta->tipo) }}">{{ $cuenta->tipo }}</span></td>
                            <td class="text-center">{{ $cuenta->nivel }}</td>
                            <td>
                                @if($cuenta->cuenta_padre)
                                    {{ $cuenta->cuenta_padre }} <small class="text-muted">({{ $cuenta->nombre_padre ?? 'N/A' }})</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($cuenta->activo)
                                    <span class="badge bg-success-soft text-success">Activo</span>
                                @else
                                    <span class="badge bg-danger-soft text-danger">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('contador.plan-cuentas.edit', $cuenta->codigo) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="confirmDelete('{{ $cuenta->codigo }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <form id="delete-form-{{ $cuenta->codigo }}" action="{{ route('contador.plan-cuentas.destroy', $cuenta->codigo) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center p-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="mb-1">No se encontraron cuentas</h5>
                                <p class="text-muted">No hay cuentas que coincidan con los filtros seleccionados.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginación --}}
        @if($cuentas->hasPages())
            <div class="card-footer pagination-wrapper">
                {{ $cuentas->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(codigo) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar la cuenta ${codigo}? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + codigo).submit();
            }
        });
    } else {
        if (confirm(`¿Estás seguro de eliminar la cuenta ${codigo}?`)) {
            document.getElementById('delete-form-' + codigo).submit();
        }
    }
}
</script>
@endpush
