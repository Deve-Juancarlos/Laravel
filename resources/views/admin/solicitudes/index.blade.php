@extends('layouts.admin') {{-- O tu layout de Admin --}}

@section('title', 'Solicitudes de Eliminación')

@push('styles')
    <link href="{{ asset('css/admin/solicitudes-eliminacion.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="solicitudes-eliminacion">
    <div class="card shadow-sm">
        <div class="card-header">
            <h4>Solicitudes de Eliminación Pendientes</h4>
            <small class="text-muted">Asientos contables marcados para eliminación por los usuarios.</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Asiento N°</th>
                            <th>Fecha Asiento</th>
                            <th>Glosa</th>
                            <th>Monto</th>
                            <th>Solicitado en</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitudes as $asiento)
                            <tr class="table-warning">
                                <td>
                                    <a href="{{ route('contador.libro-diario.show', $asiento->id) }}" target="_blank">
                                        <strong>{{ $asiento->numero }}</strong>
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($asiento->fecha)->format('d/m/Y') }}</td>
                                <td>{{ $asiento->glosa }}</td>
                                <td>S/ {{ number_format($asiento->total_debe, 2) }}</td>
                                <td>{{ $asiento->updated_at->diffForHumans() }}</td>
                                <td class="text-center">
                                    
                                    <!-- Botón de RECHAZAR (Revierte a ACTIVO) -->
                                    <form action="{{ route('admin.solicitudes.asiento.rechazar', $asiento->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de RECHAZAR esta solicitud? El asiento volverá a estar Activo.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            <i class="fas fa-undo me-1"></i> Rechazar
                                        </button>
                                    </form>

                                    <!-- Botón de APROBAR (Elimina PERMANENTEMENTE) -->
                                    <form action="{{ route('admin.solicitudes.asiento.aprobar', $asiento->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¡ATENCIÓN! ¿Está seguro de APROBAR esta eliminación? El asiento se borrará permanentemente.');">
                                        @csrf
                                        <button type-="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash-alt me-1"></i> Aprobar Eliminación
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center p-4">
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    No hay solicitudes de eliminación pendientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $solicitudes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection