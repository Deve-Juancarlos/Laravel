{{-- Vista show.blade.php CORREGIDA para contador.libro-diario.show --}}
@use('Illuminate\Support\Str')
@extends('layouts.app') {{-- Usar tu layout --}}

@section('title', 'Asiento ' . $asiento->numero)

<!-- 1. Título de la Cabecera -->
@section('page-title')
    Asiento #{{ $asiento->numero }}
@endsection

<!-- 2. Breadcrumbs -->
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.libro-diario.index') }}">Libro Diario</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $asiento->numero }}</li>
@endsection

<!-- 3. Estilos CSS de esta página -->
@push('styles')
    <link href="{{ asset('css/contabilidad/asiento-show.css') }}" rel="stylesheet">
@endpush

<!-- 4. Contenido Principal -->
@section('content')

<div class="container-fluid">
    {{-- Navegación entre asientos --}}
    <div class="row mb-3">
        <div class="col-6">
            @if($asientoAnterior)
                <a href="{{ route('contador.libro-diario.show', $asientoAnterior->id) }}" class="btn btn-outline-secondary w-100 p-3 text-start">
                    <i class="fas fa-chevron-left me-2"></i>
                    <strong>Asiento Anterior</strong><br>
                    <small class="text-muted">{{ $asientoAnterior->numero }} - {{ \Carbon\Carbon::parse($asientoAnterior->fecha)->format('d/m/Y') }}</small>
                </a>
            @else
                <div class="btn btn-outline-secondary w-100 p-3 text-start disabled" style="opacity: 0.6;">
                    <i class="fas fa-chevron-left me-2"></i>
                    <strong>Asiento Anterior</strong><br>
                    <small class="text-muted">Estás en el primer asiento.</small>
                </div>
            @endif
        </div>
        <div class="col-6">
            @if($asientoSiguiente)
                <a href="{{ route('contador.libro-diario.show', $asientoSiguiente->id) }}" class="btn btn-outline-secondary w-100 p-3 text-end">
                    <strong>Asiento Siguiente</strong>
                    <i class="fas fa-chevron-right ms-2"></i><br>
                    <small class="text-muted">{{ $asientoSiguiente->numero }} - {{ \Carbon\Carbon::parse($asientoSiguiente->fecha)->format('d/m/Y') }}</small>
                </a>
            @else
                 <div class="btn btn-outline-secondary w-100 p-3 text-end disabled" style="opacity: 0.6;">
                    <strong>Asiento Siguiente</strong>
                    <i class="fas fa-chevron-right ms-2"></i><br>
                    <small class="text-muted">Estás en el último asiento.</small>
                </div>
            @endif
        </div>
    </div>

    {{-- Alertas (manejadas por el layout principal app.blade.php) --}}
    
    {{-- Información del Asiento --}}
    <div class="row mb-4">
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle text-primary me-2"></i> Información General
                    </h5>
                    <a href="{{ route('contador.libro-diario.edit', $asiento->id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-edit me-1"></i> Editar Cabecera
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Número:</strong>
                        </div>
                        <div class="col-sm-8">
                            <span class="badge bg-primary fs-6">{{ $asiento->numero }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Fecha:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ \Carbon\Carbon::parse($asiento->fecha)->format('d/m/Y') }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Estado:</strong>
                        </div>
                        <div class="col-sm-8">
                            @if($asiento->balanceado)
                                <span class="badge bg-success-light text-success">
                                    <i class="fas fa-check-circle"></i> Balanceado
                                </span>
                            @else
                                <span class="badge bg-warning-light text-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Sin Balancear
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Estado Sistema:</strong>
                        </div>
                        <div class="col-sm-8">
                            <span class="badge bg-info-light text-info">{{ $asiento->estado }}</span>
                        </div>
                    </div>
                     <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Usuario:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $asiento->usuario_nombre ?? 'Sistema' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie text-primary me-2"></i> Resumen Financiero
                    </h5>
                </div>
                <div class="card-body">
                    <div class="kpi-box">
                        <div class="kpi-icon-sm bg-success-light">
                            <i class="fas fa-arrow-up text-success"></i>
                        </div>
                        <div class="kpi-content-sm">
                            <div class="kpi-label-sm">Total Debe</div>
                            <div class="kpi-value-sm text-success">S/ {{ number_format($asiento->total_debe, 2) }}</div>
                        </div>
                    </div>
                    
                    <div class="kpi-box mt-3">
                        <div class="kpi-icon-sm bg-danger-light">
                            <i class="fas fa-arrow-down text-danger"></i>
                        </div>
                        <div class="kpi-content-sm">
                            <div class="kpi-label-sm">Total Haber</div>
                            <div class="kpi-value-sm text-danger">S/ {{ number_format($asiento->total_haber, 2) }}</div>
                        </div>
                    </div>

                    <div class="kpi-box mt-3">
                        <div class="kpi-icon-sm {{ $asiento->balanceado ? 'bg-success-light' : 'bg-warning-light' }}">
                            <i class="fas {{ $asiento->balanceado ? 'fa-check-circle text-success' : 'fa-exclamation-triangle text-warning' }}"></i>
                        </div>
                        <div class="kpi-content-sm">
                            <div class="kpi-label-sm">Balance</div>
                            <div class="kpi-value-sm {{ $asiento->balanceado ? 'text-success' : 'text-warning' }}">
                                S/ {{ number_format($asiento->total_debe - $asiento->total_haber, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Descripción --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-alt text-primary me-2"></i> Descripción
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
                <p class="mt-2 p-3 bg-warning-light rounded border-start border-warning border-4">
                    {{ $asiento->observaciones }}
                </p>
            </div>
            @endif
        </div>
    </div>

    {{-- Detalles del Asiento --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list-alt text-primary me-2"></i> Detalles del Asiento
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
                            <th class="text-end" style="width: 15%;">Debe</th>
                            <th class="text-end" style="width: 15%;">Haber</th>
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
                            <td colspan="3" class="text-end"><strong>TOTALES</strong></td>
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

    {{-- Acciones y Auditoría --}}
    <div class="row">
        <!-- Acciones -->
        <div class="col-lg-5">
             <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-cogs text-primary me-2"></i> Acciones</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('contador.libro-diario.edit', $asiento->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i> Editar Cabecera (Glosa, Fecha)
                        </a>
                        <button onclick="duplicarAsiento()" class="btn btn-info text-white">
                            <i class="fas fa-copy me-2"></i> Duplicar Asiento
                        </button>
                        <button onclick="eliminarAsiento()" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i> Eliminar Asiento
                        </button>
                    </div>
                    <hr>
                    <div class="d-flex gap-2">
                        <button onclick="exportarAsientoPDF()" class="btn btn-outline-danger w-100">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button onclick="exportarAsientoExcel()" class="btn btn-outline-success w-100">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                    <!-- Formulario de eliminación oculto -->
                    <form id="delete-form-{{ $asiento->id }}" action="{{ route('contador.libro-diario.destroy', $asiento->id) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>

        <!-- Historial de Auditoría -->
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history text-primary me-2"></i> Historial de Auditoría
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Asiento Creado</h6>
                                <p class="timeline-text">
                                    Por: <strong>{{ $asiento->usuario_nombre ?? 'Sistema' }}</strong><br>
                                    Fecha: {{ \Carbon\Carbon::parse($asiento->created_at)->format('d/m/Y H:i:s') }}
                                </p>
                            </div>
                        </div>

                        @if($asiento->created_at != $asiento->updated_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Última Actualización</h6>
                                <p class="timeline-text">
                                    Fecha: {{ \Carbon\Carbon::parse($asiento->updated_at)->format('d/m/Y H:i:s') }}
                                </p>
                            </div>
                        </div>
                        @endif

                        @if($asiento->balanceado)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Balance Verificado</h6>
                                <p class="timeline-text mb-0">
                                    Sistema verificó el balance del asiento.
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Usa SweetAlert para confirmar la eliminación
    function eliminarAsiento() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará el asiento #{{ $asiento->numero }} permanentemente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    if(typeof showLoading === 'function') showLoading();
                    document.getElementById('delete-form-{{ $asiento->id }}').submit();
                }
            });
        } else {
            if (confirm('¿Estás seguro de eliminar este asiento? Esta acción no se puede deshacer.')) {
                if(typeof showLoading === 'function') showLoading();
                document.getElementById('delete-form-{{ $asiento->id }}').submit();
            }
        }
    }

    // Funciones de exportación (requieren lógica en el servicio)
    function exportarAsientoPDF() {
        if(typeof showLoading === 'function') showLoading();
        // Esta ruta debe ser manejada por tu LibroDiarioController@exportar
        window.location.href = `{{ route('contador.libro-diario.exportar') }}?asiento_id={{ $asiento->id }}&formato=pdf`;
        // Ocultar loading después de un tiempo por si falla la descarga
        setTimeout(() => { if(typeof hideLoading === 'function') hideLoading(); }, 3000);
    }

    function exportarAsientoExcel() {
        if(typeof showLoading === 'function') showLoading();
        // Esta ruta debe ser manejada por tu LibroDiarioController@exportar
        window.location.href = `{{ route('contador.libro-diario.exportar') }}?asiento_id={{ $asiento->id }}&formato=excel`;
        setTimeout(() => { if(typeof hideLoading === 'function') hideLoading(); }, 3000);
    }

    // Duplicar (requiere lógica en el controlador/servicio)
    function duplicarAsiento() {
        Swal.fire({
            title: 'Duplicar Asiento',
            text: "¿Desea crear un nuevo borrador de asiento basado en este?",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Sí, duplicar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                if(typeof showLoading === 'function') showLoading();
                // Esta ruta debe ser manejada por tu LibroDiarioController@create
                window.location.href = `{{ route('contador.libro-diario.create') }}?duplicar={{ $asiento->id }}`;
            }
        });
    }
</script>
@endpush
