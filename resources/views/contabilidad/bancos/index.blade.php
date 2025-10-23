{{-- resources/views/contabilidad/bancos/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Maestro de Bancos')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🏦 Maestro de Bancos</h2>
        @can('crear-bancos')
            <a href="{{ route('contabilidad.bancos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Cuenta Bancaria
            </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            @if($bancos->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay cuentas bancarias registradas.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Banco</th>
                                <th>N° Cuenta</th>
                                <th>Moneda</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bancos as $banco)
                            <tr>
                                <td>{{ $banco->nombre_banco }}</td>
                                <td>{{ $banco->numero_cuenta }}</td>
                                <td>
                                    @if($banco->moneda_codigo == '1') S/ @else US$ @endif
                                </td>
                                <td>{{ $banco->descripcion ?? '-' }}</td>
                                <td>
                                    @if($banco->estado)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-secondary disabled">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <!-- En muchos sistemas contables, NO se edita ni elimina por integridad -->
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection