@extends('layouts.app')
@section('title', 'Gestionar Planilla')
@section('page-title', 'Gestionar Planilla de Descuento')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.letras_descuento.index') }}">Planillas</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $planilla->Serie }}-{{ $planilla->Numero }}</li>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><strong>Planilla:</strong> {{ $planilla->Serie }}-{{ $planilla->Numero }}</div>
                    <div class="col-md-3"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($planilla->Fecha)->format('d/m/Y') }}</div>
                    <div class="col-md-3"><strong>Banco:</strong> {{ $planilla->NombreBanco }}</div>
                    <div class="col-md-3"><strong>Estado:</strong>
                        @if($planilla->Procesado)
                            <span class="badge bg-success">Procesada</span>
                        @else
                            <span class="badge bg-warning text-dark">Pendiente</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Formulario para AÑADIR letras (solo si no está procesada) --}}
@if(!$planilla->Procesado)
<div class="card shadow mb-4" id="divAgregarLetras">
    <div class="card-header">
        <h5 class="card-title m-0">Añadir Letras a la Planilla</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Buscar Letra Pendiente (en CtaCliente)</label>
                <select id="selectLetra" class="form-control"></select>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-success w-100" id="btnAgregarLetra">
                    <i class="fas fa-plus"></i> Añadir a Planilla
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Lista de Letras en la Planilla (PllaDetLetras) --}}
<div class="card shadow mb-4">
    <div class="card-header">
        <h5 class="card-title m-0">Letras Incluidas en esta Planilla</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead class="table-light">
                    <tr>
                        <th>N° Letra</th>
                        <th>Cliente</th>
                        <th>Vencimiento</th>
                        <th class="text-end">Importe</th>
                        @if(!$planilla->Procesado)
                        <th class="text-center">Acción</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="tablaDetalles">
                    @forelse($detalles as $det)
                    <tr id="item-{{$det->Orden}}">
                        <td>{{ $det->NroLetra }}</td>
                        <td>{{ $det->Cliente }}</td>
                        <td>{{ \Carbon\Carbon::parse($det->Vencimiento)->format('d/m/Y') }}</td>
                        <td class="text-end fw-bold">{{ number_format($det->Importe, 2) }}</td>
                        @if(!$planilla->Procesado)
                        <td class="text-center">
                            <button class="btn btn-danger btn-sm btn-quitar" data-id="{{ $det->Orden }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center p-3 text-muted">Aún no hay letras en esta planilla.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-bold fs-5">TOTAL PLANILLA</td>
                        <td class="text-end fw-bold fs-5" id="totalPlanilla">S/ {{ number_format($totalPlanilla, 2) }}</td>
                        @if(!$planilla->Procesado)
                        <td></td>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Formulario para PROCESAR (El GOLAZO Contable) --}}
@if(!$planilla->Procesado)
<div class="card shadow">
    <div class="card-header bg-success text-white">
        <h5 class="card-title m-0">Procesar Descuento y Abono a Banco</h5>
    </div>
    <form action="{{ route('contador.letras_descuento.procesar', ['serie' => $planilla->Serie, 'numero' => $planilla->Numero]) }}" method="POST">
        @csrf
        <div class="card-body">
            <p>Este paso registrará el ingreso del dinero en <strong>{{ $planilla->NombreBanco }}</strong>, marcará las letras como cobradas en <strong>CtaCliente</strong> y generará el <strong>Asiento Contable</strong>.</p>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Monto Total Letras</label>
                    <input type="text" class="form-control" value="S/ {{ number_format($totalPlanilla, 2) }}" disabled>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="interes" class="form-label fw-bold">Intereses y Gastos (S/) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" id="interes" name="interes" value="0.00" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="fecha_abono" class="form-label fw-bold">Fecha de Abono <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_abono" name="fecha_abono" value="{{ now()->format('Y-m-d') }}" required>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-success btn-lg" {{ $detalles->isEmpty() ? 'disabled' : '' }}>
                <i class="fas fa-check-circle me-1"></i> Confirmar y Procesar Descuento
            </button>
        </div>
    </form>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    
    // Solo activar Select2 si el formulario existe
    if ($('#divAgregarLetras').length) {
        
        let letraSeleccionada = null;
        const TOKEN = '{{ csrf_token() }}';
        
        $('#selectLetra').select2({
            placeholder: 'Buscar Letra (Tipo 9) por N° o Cliente...',
            minimumInputLength: 3,
            ajax: {
                url: "{{ route('contador.letras_descuento.api.buscarLetras') }}",
                dataType: 'json',
                delay: 250,
                data: (params) => ({ q: params.term }),
                processResults: (data) => ({
                    results: $.map(data, (letra) => ({
                        id: letra.Documento,
                        text: `${letra.Documento} - ${letra.Razon} (S/ ${letra.Saldo})`,
                        data: letra // Guardamos el objeto completo
                    }))
                })
            }
        }).on('select2:select', function (e) {
            letraSeleccionada = e.params.data.data;
        });

        // Botón Añadir
        $('#btnAgregarLetra').on('click', function() {
            if (!letraSeleccionada) {
                Swal.fire('Error', 'Debe seleccionar una letra.', 'error');
                return;
            }

            const data = {
                Serie: '{{ $planilla->Serie }}',
                Numero: '{{ $planilla->Numero }}',
                CodBanco: '{{ $planilla->CodBanco }}',
                Ruc: letraSeleccionada.Ruc,
                Cliente: letraSeleccionada.Razon,
                NroLetra: letraSeleccionada.Documento,
                Vencimiento: letraSeleccionada.FechaV,
                Importe: letraSeleccionada.Saldo
            };

            $.ajax({
                url: "{{ route('contador.letras_descuento.api.agregarLetra') }}",
                type: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': TOKEN },
                success: (response) => {
                    if (response.success) {
                        location.reload(); // Recargamos para ver el item
                    }
                },
                error: (jqXHR) => Swal.fire('Error', jqXHR.responseJSON.message || 'Error al añadir item', 'error')
            });
        });

        // Botón Quitar
        $('#tablaDetalles').on('click', '.btn-quitar', function() {
            const itemId = $(this).data('id');
            Swal.fire({
                title: '¿Quitar Letra?',
                text: "Esta acción quitará la letra de la planilla.",
                icon: 'warning',
                showCancelButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('contador/letras-descuento/quitar-letra') }}/${itemId}`,
                        type: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': TOKEN },
                        success: (response) => {
                             location.reload(); // Recargamos
                        },
                        error: (jqXHR) => Swal.fire('Error', 'No se pudo quitar la letra', 'error')
                    });
                }
            });
        });
    }
});
</script>
@endpush