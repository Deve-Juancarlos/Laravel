@extends('dashboard.contador')

@section('title', 'Verificación - Balance de Comprobación')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary mb-0">
            <i class="fas fa-check-circle me-2"></i> Verificación del Balance
        </h4>
        <span class="text-muted fst-italic">{{ now()->format('d/m/Y H:i') }}</span>
    </div>

    {{-- BALANCE --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-gradient bg-primary text-white">
            <h5 class="mb-0">Resumen del Balance de Comprobación</h5>
        </div>
        <div class="card-body">
            <p><strong>Total Debe:</strong> S/ {{ number_format($totalDebe, 2) }}</p>
            <p><strong>Total Haber:</strong> S/ {{ number_format($totalHaber, 2) }}</p>
            <p>
                <strong>Diferencia:</strong>
                <span class="{{ $diferencia == 0 ? 'text-success' : 'text-danger fw-bold' }}">
                    S/ {{ number_format($diferencia, 2) }}
                </span>
            </p>

            @if ($diferencia == 0)
                <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i> El balance está cuadrado.</div>
            @else
                <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i> Hay una diferencia que requiere revisión.</div>
            @endif
        </div>
    </div>

    {{-- ASIENTOS DESCUDRADOS --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <h6 class="mb-0">Asientos Descuadrados</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Asiento</th>
                        <th class="text-end">Debe (S/)</th>
                        <th class="text-end">Haber (S/)</th>
                        <th class="text-end">Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asientosDescuadrados as $a)
                        <tr>
                            <td>{{ $a->numero }}</td>
                            <td class="text-end">{{ number_format($a->total_debe, 2) }}</td>
                            <td class="text-end">{{ number_format($a->total_haber, 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($a->diferencia, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No se encontraron asientos descuadrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ÚLTIMAS FACTURAS --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Últimas Facturas Emitidas</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Número</th>
                        <th>Tipo</th>
                        <th>Código Cliente</th>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Vencimiento</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ultimasFacturas as $factura)
                        <tr>
                            <td>{{ $factura->Numero }}</td>
                            <td>{{ $factura->Tipo }}</td>
                            <td>{{ $factura->CodClie }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($factura->Fecha)->format('d/m/Y') }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($factura->FechaV)->format('d/m/Y') }}</td>
                            <td class="text-end">S/ {{ number_format($factura->Total, 2) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $factura->Estado === 'Emitida' ? 'bg-success' : 'bg-warning' }}">
                                    {{ $factura->Estado }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">No se encontraron facturas recientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
