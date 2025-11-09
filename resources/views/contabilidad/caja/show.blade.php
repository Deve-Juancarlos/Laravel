@extends('layouts.app')

@section('title', 'Detalle Movimiento de Caja')
@section('page-title')
    <div>
        <h1><i class="fas fa-receipt me-2"></i>Detalle Movimiento de Caja</h1>
        <p class="text-muted">Movimiento #{{ $movimiento->Numero }}</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.caja.index') }}">Caja</a></li>
    <li class="breadcrumb-item active" aria-current="page">#{{ $movimiento->Numero }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Movimiento de Caja #{{ $movimiento->Numero }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <p class="text-muted mb-1 small">Tipo</p>
                            @if($movimiento->Tipo == 1)
                                <span class="badge fs-6 bg-success">Ingreso</span>
                            @else
                                <span class="badge fs-6 bg-danger">Egreso</span>
                            @endif
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1 small">Monto</p>
                            <h4 class="fw-bold {{ $movimiento->Tipo == 1 ? 'text-success' : 'text-danger' }} mb-0">
                                S/ {{ number_format($movimiento->Monto, 2) }}
                            </h4>
                        </div>
                    </div>
                    
                    <hr>

                    <p class="text-muted mb-1 small">Fecha</p>
                    <p class="fw-bold">{{ \Carbon\Carbon::parse($movimiento->Fecha)->format('d/m/Y') }}</p>

                    <p class="text-muted mb-1 small">Documento Referencia</p>
                    <p class="fw-bold">{{ $movimiento->Documento ?? 'N/A' }}</p>

                    <p class="text-muted mb-1 small">Contrapartida (Cuenta)</p>
                    <p class="fw-bold">{{ $movimiento->Razon }} - {{ $movimiento->contrapartida_nombre ?? 'Cuenta no encontrada' }}</p>

                    @if($movimiento->Eliminado)
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-exclamation-triangle me-1"></i> Este movimiento fue <strong>ANULADO</strong>.
                        </div>
                    @endif
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('contador.caja.edit', $movimiento->Numero) }}" class="btn btn-secondary {{ $movimiento->Eliminado ? 'disabled' : '' }}">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>
                    
                    {{-- Formulario para Anular --}}
                    <form action="{{ route('contador.caja.destroy', $movimiento->Numero) }}" method="POST" onsubmit="return confirm('¿Está seguro de que desea ANULAR este movimiento? Esta acción es irreversible y anulará el asiento contable.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" {{ $movimiento->Eliminado ? 'disabled' : '' }}>
                            <i class="fas fa-trash me-1"></i> Anular
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Asiento Contable Vinculado</h5>
                </div>
                <div class="card-body">
                    @if($asiento)
                        <div class="row">
                            <div class="col-6">
                                <p class="text-muted mb-1 small">Asiento N°</p>
                                <p class="fw-bold">{{ $asiento->numero }}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-muted mb-1 small">Fecha Asiento</p>
                                <p class="fw-bold">{{ \Carbon\Carbon::parse($asiento->fecha)->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <p class="text-muted mb-1 small">Glosa</p>
                        <p class="fw-bold">{{ $asiento->glosa }}</p>

                        <table class="table table-sm table-bordered mt-3">
                            <thead class="table-light">
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Debe</th>
                                    <th class="text-end">Haber</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detalles as $detalle)
                                <tr>
                                    <td>{{ $detalle->cuenta_contable }}</td>
                                    <td>{{ $detalle->cuenta_nombre }}</td>
                                    <td class="text-end">{{ $detalle->debe > 0 ? number_format($detalle->debe, 2) : '' }}</td>
                                    <td class="text-end">{{ $detalle->haber > 0 ? number_format($detalle->haber, 2) : '' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="text-end fw-bold">Totales</td>
                                    <td class="text-end fw-bold">{{ number_format($asiento->total_debe, 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($asiento->total_haber, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <div class="alert alert-warning">
                            Este movimiento de caja no tiene un asiento contable vinculado.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection