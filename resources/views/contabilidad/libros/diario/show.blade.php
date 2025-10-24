@extends('layouts.contador')

@section('title', 'Ver Asiento Diario - SIFANO')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('libros-diario') }}">Libro Diario</a></li>
    <li class="breadcrumb-item active">Ver Asiento</li>
@endsection

@section('contador-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-eye text-info me-2"></i>
        Asiento Diario #{{ $asiento->numero_asiento ?? '' }}
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success" onclick="exportAsiento()">
            <i class="fas fa-download me-2"></i>
            Exportar PDF
        </button>
        @can('update', $asiento ?? null)
        <a href="{{ route('libros-diario.edit', $asiento->id ?? 0) }}" class="btn btn-outline-warning">
            <i class="fas fa-edit me-2"></i>
            Editar
        </a>
        @endcan
        <a href="{{ route('libros-diario') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información Principal -->
    <div class="col-lg-8">
        <!-- Encabezado del Asiento -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Información del Asiento
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Número de Asiento</label>
                        <p class="form-control-plaintext">{{ $asiento->numero_asiento ?? '' }}</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Fecha</label>
                        <p class="form-control-plaintext">{{ date('d/m/Y', strtotime($asiento->fecha ?? '')) }}</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tipo</label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-secondary">{{ ucfirst($asiento->tipo_asiento ?? '') }}</span>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Estado</label>
                        <p class="form-control-plaintext">
                            @if(($asiento->total_debe ?? 0) - ($asiento->total_haber ?? 0) == 0)
                                <span class="badge bg-success">Balanceado</span>
                            @else
                                <span class="badge bg-danger">Desbalanceado</span>
                            @endif
                        </p>
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Descripción</label>
                        <p class="form-control-plaintext">{{ $asiento->descripcion ?? '' }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Referencia</label>
                        <p class="form-control-plaintext">{{ $asiento->referencia ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Usuario -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user me-2"></i>
                    Información del Usuario
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Creado por</label>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-3">
                                {{ strtoupper(substr($asiento->usuario_nombre ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="mb-0 fw-bold">{{ $asiento->usuario_nombre ?? 'N/A' }}</p>
                                <small class="text-muted">{{ $asiento->usuario_email ?? '' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha de Creación</label>
                        <p class="form-control-plaintext">{{ date('d/m/Y H:i:s', strtotime($asiento->created_at ?? '')) }}</p>
                    </div>
                </div>
                @if($asiento->updated_at ?? false)
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Última Modificación</label>
                        <p class="form-control-plaintext">{{ date('d/m/Y H:i:s', strtotime($asiento->updated_at)) }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Modificado por</label>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center text-white me-3">
                                {{ strtoupper(substr($asiento->updated_by ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="mb-0 fw-bold">{{ $asiento->updated_by ?? 'N/A' }}</p>
                                <small class="text-muted">Usuario que modificó</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Partidas Contables -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Partidas Contables
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width: 5%">#</th>
                                <th style="width: 35%">Cuenta Contable</th>
                                <th style="width: 35%">Descripción</th>
                                <th class="text-end" style="width: 12%">Debe</th>
                                <th class="text-end" style="width: 13%">Haber</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asiento->partidas ?? [] as $index => $partida)
                            <tr>
                                <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $partida->cuenta->codigo ?? '' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $partida->cuenta->nombre ?? '' }}</small>
                                    </div>
                                </td>
                                <td>{{ $partida->descripcion ?? '' }}</td>
                                <td class="text-end fw-bold text-success">
                                    @if(($partida->debe ?? 0) > 0)
                                        S/ {{ number_format($partida->debe, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    @if(($partida->haber ?? 0) > 0)
                                        S/ {{ number_format($partida->haber, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay partidas registradas
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <th colspan="3" class="text-end">TOTALES:</th>
                                <th class="text-end text-success">
                                    S/ {{ number_format($asiento->total_debe ?? 0, 2) }}
                                </th>
                                <th class="text-end text-primary">
                                    S/ {{ number_format($asiento->total_haber ?? 0, 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Observaciones -->
        @if($asiento->observaciones ?? false)
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-sticky-note me-2"></i>
                    Observaciones
                </h6>
            </div>
            <div class="card-body">
                <p class="form-control-plaintext">{{ $asiento->observaciones }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Panel Lateral -->
    <div class="col-lg-4">
        <!-- Resumen del Balance -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-balance-scale me-2"></i>
                    Balance del Asiento
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    @if(($asiento->total_debe ?? 0) - ($asiento->total_haber ?? 0) == 0)
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-3x mb-2"></i>
                            <h5 class="mb-0">Balanceado</h5>
                            <small class="text-muted">El asiento está correctamente balanceado</small>
                        </div>
                    @else
                        <div class="text-danger">
                            <i class="fas fa-exclamation-triangle fa-3x mb-2"></i>
                            <h5 class="mb-0">Desbalanceado</h5>
                            <small class="text-muted">
                                Diferencia: S/ {{ number_format(abs(($asiento->total_debe ?? 0) - ($asiento->total_haber ?? 0)), 2) }}
                            </small>
                        </div>
                    @endif
                </div>
                
                <hr>
                
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="text-success fw-bold fs-5">S/ {{ number_format($asiento->total_debe ?? 0, 2) }}</div>
                            <small class="text-muted">Total Debe</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="text-primary fw-bold fs-5">S/ {{ number_format($asiento->total_haber ?? 0, 2) }}</div>
                            <small class="text-muted">Total Haber</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Acciones
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="duplicarAsiento()">
                        <i class="fas fa-copy me-2"></i>
                        Duplicar Asiento
                    </button>
                    <button class="btn btn-outline-info" onclick="verEnLibroMayor()">
                        <i class="fas fa-book me-2"></i>
                        Ver en Libro Mayor
                    </button>
                    @can('delete', $asiento ?? null)
                    <button class="btn btn-outline-danger" onclick="eliminarAsiento()">
                        <i class="fas fa-trash me-2"></i>
                        Eliminar Asiento
                    </button>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Historial de Cambios -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Historial
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Asiento Creado</h6>
                            <p class="timeline-text">{{ date('d/m/Y H:i', strtotime($asiento->created_at ?? '')) }}</p>
                            <small class="text-muted">por {{ $asiento->usuario_nombre ?? 'N/A' }}</small>
                        </div>
                    </div>
                    
                    @if($asiento->updated_at ?? false)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Última Modificación</h6>
                            <p class="timeline-text">{{ date('d/m/Y H:i', strtotime($asiento->updated_at)) }}</p>
                            <small class="text-muted">por {{ $asiento->updated_by ?? 'N/A' }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -2rem;
    top: 0.5rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #dee2e6;
    border: 3px solid #fff;
    box-shadow: 0 0 0 1px #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.5rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 3px solid #fff;
}

.timeline-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.timeline-text {
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
}

.avatar-sm {
    width: 2.5rem;
    height: 2.5rem;
    font-size: 0.875rem;
}
</style>
@endsection

@section('scripts')
<script>
    function exportAsiento() {
        showLoading();
        
        const url = `/libros-diario/{{ $asiento->id ?? 0 }}/exportar-pdf`;
        window.open(url, '_blank');
        
        setTimeout(hideLoading, 2000);
    }

    function duplicarAsiento() {
        Swal.fire({
            title: 'Duplicar Asiento',
            text: '¿Deseas crear un nuevo asiento con la misma información?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, duplicar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                
                fetch(`/libros-diario/{{ $asiento->id ?? 0 }}/duplicar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        Swal.fire('Éxito', 'Asiento duplicado correctamente', 'success')
                            .then(() => {
                                window.location.href = data.redirect || `/libros-diario/${data.id}/edit`;
                            });
                    } else {
                        Swal.fire('Error', data.message || 'Error duplicando el asiento', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión', 'error');
                });
            }
        });
    }

    function verEnLibroMayor() {
        const cuentaIds = [];
        @foreach($asiento->partidas ?? [] as $partida)
        cuentaIds.push({{ $partida->cuenta_id ?? 0 }});
        @endforeach
        
        const url = `/libros-mayor?cuenta_ids=${cuentaIds.join(',')}&fecha_desde={{ $asiento->fecha }}&fecha_hasta={{ $asiento->fecha }}`;
        window.open(url, '_blank');
    }

    function eliminarAsiento() {
        Swal.fire({
            title: 'Eliminar Asiento',
            text: '¿Estás seguro de que deseas eliminar este asiento? Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                
                fetch(`/libros-diario/{{ $asiento->id ?? 0 }}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        Swal.fire('Eliminado', 'Asiento eliminado correctamente', 'success')
                            .then(() => {
                                window.location.href = '{{ route("libros-diario") }}';
                            });
                    } else {
                        Swal.fire('Error', data.message || 'Error eliminando el asiento', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión', 'error');
                });
            }
        });
    }

    // Imprimir asiento
    function imprimirAsiento() {
        const printContent = document.querySelector('.card').cloneNode(true);
        const printWindow = window.open('', '_blank');
        
        printWindow.document.write(`
            <html>
                <head>
                    <title>Asiento #{{ $asiento->numero_asiento ?? '' }}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .card { border: 1px solid #ddd; margin-bottom: 20px; }
                        .card-header { background: #f8f9fa; padding: 10px; border-bottom: 1px solid #ddd; }
                        .table { width: 100%; border-collapse: collapse; }
                        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        .table th { background: #f8f9fa; }
                        .text-end { text-align: right; }
                        .text-success { color: #28a745; }
                        .text-primary { color: #007bff; }
                    </style>
                </head>
                <body>
                    <h1>Asiento Contable #{{ $asiento->numero_asiento ?? '' }}</h1>
                    ${printContent.outerHTML}
                </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.print();
    }
</script>
@endsection