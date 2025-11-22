@use('Illuminate\Support\Str')
@extends('layouts.app')
@section('title', 'Planillas de Descuento')
@section('page-title', 'Descuento de Letras')

@section('breadcrumbs')
    <li class="breadcrumb-item">Tesorería</li>
    <li class="breadcrumb-item active" aria-current="page">Descuento de Letras</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <a href="{{ route('contador.letras_descuento.create') }}" class="btn btn-primary mb-3">
            <i class="fas fa-plus me-1"></i> Nueva Planilla de Descuento
        </a>
    </div>
</div>

<div class="card shadow">
    <div class="card-header">
        <h5 class="card-title m-0">Planillas Generadas (PllaLetras)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Planilla</th>
                        <th>Fecha</th>
                        <th>Banco</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planillas as $pl)
                        <tr>
                            <td><strong>{{ $pl->Serie }}-{{ $pl->Numero }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($pl->Fecha)->format('d/m/Y') }}</td>
                            <td>{{ $pl->NombreBanco }}</td>
                            <td class="text-center">
                                @if($pl->Procesado)
                                    <span class="badge bg-success">Procesada</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('contador.letras_descuento.show', ['serie' => $pl->Serie, 'numero' => $pl->Numero]) }}" class="btn btn-sm btn-info" title="Ver / Gestionar">
                                    <i class="fas fa-edit"></i> Gestionar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-4 text-muted">No se encontraron planillas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end">
            {{ $planillas->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection