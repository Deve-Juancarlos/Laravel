{{-- Vista show.blade.php CORREGIDA para contador.libro-diario.show --}}
@extends('layouts.app') {{-- Usar tu layout --}}

@section('title', 'Asiento ' . $asiento->numero)

@section('content')
<div class="container-fluid p-0">
    {{-- Header con navegación --}}
    <div class="d-flex justify-content-between align-items-center p-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white;">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-clipboard-check"></i> Asiento #{{ $asiento->numero }}
            </h1>
            <p class="mb-0 opacity-75">Detalles del asiento contable</p>
        </div>
        <div>
            <a href="{{ route('contador.libro-diario.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Volver al Libro
            </a>
        </div>
    </div>

    {{-- Navegación entre asientos --}}
    <div class="row mb-4">
        <div class="col-6">
            @if($asientoAnterior)
                <a href="{{ route('contador.libro-diario.show', $asientoAnterior->id) }}" class="btn btn-outline-primary w-100">
                    <i class="fas fa-chevron-left"></i> Asiento Anterior<br>
                    <small>{{ $asientoAnterior->numero }} - {{ \Carbon\Carbon::parse($asientoAnterior->fecha)->format('d/m/Y') }}</small>
                </a>
            @else
                <div class="btn btn-outline-secondary w-100" style="cursor: not-allowed;">
                    <i class="fas fa-chevron-left"></i> Sin Asiento Anterior
                </div>
            @endif
        </div>
        <div class="col-6">
            @if($asientoSiguiente)
                <a href="{{ route('contador.libro-diario.show', $asientoSiguiente->id) }}" class="btn btn-outline-primary w-100">
                    <i class="fas fa-chevron-right"></i> Asiento Siguiente<br>
                    <small>{{ $asientoSiguiente->numero }} - {{ \Carbon\Carbon::parse($asientoSiguiente->fecha)->format('d/m/Y') }}</small>
                </a>
            @else
                <div class="btn btn-outline-secondary w-100" style="cursor: not-allowed;">
                    <i class="fas fa-chevron-right"></i> Sin Siguiente Asiento
                </div>
            @endif
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Información del Asiento --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Número:</strong>
                        </div>
                        <div class="col-6">
                            <span class="badge bg-primary fs-6">{{ $asiento->numero }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Fecha:</strong>
                        </div>
                        <div class="col-6">
                            {{ \Carbon\Carbon::parse($asiento->fecha)->format('d/m/Y') }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Estado:</strong>
                        </div>
                        <div class="col-6">
                            @if($asiento->balanceado)
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Balanceado
                                </span>
                            @else
                                <span class="badge bg-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Sin Balancear
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Estado Sistema:</strong>
                        </div>
                        <div class="col-6">
                            <span class="badge bg-info">{{ $asiento->estado }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user"></i> Información del Usuario
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Usuario:</strong>
                        </div>
                        <div class="col-6">
                            {{ $asiento->usuario_nombre ?? 'Sistema' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Email:</strong>
                        </div>
                        <div class="col-6">
                            {{ $asiento->usuario_email ?? '-' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Creado:</strong>
                        </div>
                        <div class="col-6">
                            {{ \Carbon\Carbon::parse($asiento->created_at)->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Actualizado:</strong>
                        </div>
                        <div class="col-6">
                            {{ \Carbon\Carbon::parse($asiento->updated_at)->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen Financiero --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-chart-pie"></i> Resumen Financiero
            </h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="p-3" style="background: rgba(16, 185, 129, 0.1); border-radius: 8px;">
                        <div class="h4 text-success mb-1">S/ {{ number_format($asiento->total_debe, 2) }}</div>
                        <div class="text-muted">Total Debe</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3" style="background: rgba(239, 68, 68, 0.1); border-radius: 8px;">
                        <div class="h4 text-danger mb-1">S/ {{ number_format($asiento->total_haber, 2) }}</div>
                        <div class="text-muted">Total Haber</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3" style="background: rgba(59, 130, 246, 0.1); border-radius: 8px;">
                        <div class="h4 text-primary mb-1">S/ {{ number_format(abs($asiento->total_debe - $asiento->total_haber), 2) }}</div>
                        <div class="text-muted">Diferencia</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3" style="background: {{ $asiento->balanceado ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)' }}; border-radius: 8px;">
                        <div class="h5 mb-1 {{ $asiento->balanceado ? 'text-success' : 'text-warning' }}">
                            @if($asiento->balanceado)
                                <i class="fas fa-check-circle"></i> CUADRA
                            @else
                                <i class="fas fa-exclamation-triangle"></i> NO CUADRA
                            @endif
                        </div>
                        <div class="text-muted">Balance</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Descripción --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-alt"></i> Descripción
            </h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong>Glosa Principal:</strong>
                <p class="mt-2 p-3 bg-light rounded">{{ $asiento->glosa }}</p>
            </div>
            @if($asiento->observaciones)
            <div>
                <strong>Observaciones:</strong>
                <p class="mt-2 p-3" style="background: #fff3cd; border-left: 3px solid #f59e0b; border-radius: 0 8px 8px 0;">
                    {{ $asiento->observaciones }}
                </p>
            </div>
            @endif
        </div>
    </div>

    {{-- Acciones --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="{{ route('contador.libro-diario.index') }}" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Volver al Libro
                </a>
                <a href="{{ route('contador.libro-diario.edit', $asiento->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar Asiento
                </a>
                <button onclick="exportarAsientoPDF()" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </button>
                <button onclick="exportarAsientoExcel()" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
                @if($asiento->balanceado)
                <button onclick="duplicarAsiento()" class="btn btn-info">
                    <i class="fas fa-copy"></i> Duplicar
                </button>
                @endif
                <button onclick="eliminarAsiento()" class="btn btn-warning">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>

    {{-- Detalles del Asiento --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list-alt"></i> Detalles del Asiento
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">Cuenta</th>
                            <th style="width: 25%;">Nombre Cuenta</th>
                            <th style="width: 30%;">Concepto</th>
                            <th style="width: 15%;">Debe</th>
                            <th style="width: 15%;">Haber</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalDebeDetalle = 0; $totalHaberDetalle = 0; @endphp
                        @foreach($detalles as $detalle)
                        <tr>
                            <td>
                                <strong class="text-primary">{{ $detalle->cuenta_contable }}</strong>
                            </td>
                            <td>{{ $detalle->cuenta_nombre ?? 'Cuenta no encontrada' }}</td>
                            <td>{{ $detalle->concepto }}</td>
                            <td class="text-end">
                                @if($detalle->debe > 0)
                                    <span class="text-success fw-bold">S/ {{ number_format($detalle->debe, 2) }}</span>
                                    @php $totalDebeDetalle += $detalle->debe; @endphp
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($detalle->haber > 0)
                                    <span class="text-danger fw-bold">S/ {{ number_format($detalle->haber, 2) }}</span>
                                    @php $totalHaberDetalle += $detalle->haber; @endphp
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        {{-- Fila de totales --}}
                        <tr class="table-active">
                            <td colspan="3"><strong>TOTALES</strong></td>
                            <td class="text-end">
                                <strong class="text-success">S/ {{ number_format($totalDebeDetalle, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <strong class="text-danger">S/ {{ number_format($totalHaberDetalle, 2) }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Historial de Auditoría --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-history"></i> Historial de Auditoría
            </h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker bg-success"></div>
                    <div class="timeline-content">
                        <h6 class="timeline-title">Asiento Creado</h6>
                        <p class="timeline-text">
                            Por: {{ $asiento->usuario_nombre ?? 'Sistema' }}<br>
                            Fecha: {{ \Carbon\Carbon::parse($asiento->created_at)->format('d/m/Y H:i:s') }}
                        </p>
                        <p class="mb-0">Asiento contable #{{ $asiento->numero }} registrado en el sistema</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker bg-info"></div>
                    <div class="timeline-content">
                        <h6 class="timeline-title">Última Actualización</h6>
                        <p class="timeline-text">
                            Por: {{ $asiento->usuario_nombre ?? 'Sistema' }}<br>
                            Fecha: {{ \Carbon\Carbon::parse($asiento->updated_at)->format('d/m/Y H:i:s') }}
                        </p>
                        <p class="mb-0">Información del asiento actualizada</p>
                    </div>
                </div>

                @if($asiento->balanceado)
                <div class="timeline-item">
                    <div class="timeline-marker bg-success"></div>
                    <div class="timeline-content">
                        <h6 class="timeline-title">Balance Verificado</h6>
                        <p class="timeline-text">
                            Fecha: {{ \Carbon\Carbon::parse($asiento->updated_at)->format('d/m/Y H:i:s') }}
                        </p>
                        <p class="mb-0">Sistema automático verificó el balance del asiento contable</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 30px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-content {
    border-left: 2px solid #e9ecef;
    padding-left: 20px;
}

.timeline-title {
    margin-bottom: 8px;
    color: #495057;
}

.timeline-text {
    color: #6c757d;
    margin-bottom: 8px;
}
</style>

@endsection

@push('scripts')
<script>
    function exportarAsientoPDF() {
        if (confirm('¿Desea exportar este asiento a PDF?')) {
            window.open(`{{ route('contador.libro-diario.exportar') }}?asiento_id={{ $asiento->id }}&formato=pdf`, '_blank');
        }
    }

    function exportarAsientoExcel() {
        if (confirm('¿Desea exportar este asiento a Excel?')) {
            window.open(`{{ route('contador.libro-diario.exportar') }}?asiento_id={{ $asiento->id }}&formato=excel`, '_blank');
        }
    }

    function duplicarAsiento() {
        if (confirm('¿Desea crear un nuevo asiento con los mismos datos?')) {
            window.location.href = `{{ route('contador.libro-diario.create') }}?duplicar={{ $asiento->id }}`;
        }
    }

    function eliminarAsiento() {
        if (confirm('¿Está seguro que desea eliminar este asiento? Esta acción no se puede deshacer.')) {
            fetch(`{{ route('contador.libro-diario.destroy', $asiento->id) }}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `{{ route('contador.libro-diario.index') }}`;
                } else {
                    alert('Error al eliminar el asiento: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar el asiento');
            });
        }
    }
</script>
@endpush