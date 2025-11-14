@extends('layouts.app')

@section('title', 'Verificación - Balance de Comprobación')

{{-- ¡SOLUCIÓN! Se corrige la ruta del CSS --}}
@push('styles')
    <link href="{{ asset('css/contabilidad/balance-comparacion/verificar.css') }}" rel="stylesheet">
@endpush

{{-- ENCABEZADO Y TÍTULO DE PÁGINA ESTANDARIZADO --}}
@section('page-title')
    <div>
        <h1><i class="fas fa-check-circle me-2"></i>Verificación del Balance</h1>
        <p class="text-muted">Análisis de cuadre de asientos y balance general</p>
    </div>
@endsection

{{-- BREADCRUMBS ESTANDARIZADOS --}}
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.balance-comprobacion.index') }}">Balance de Comprobación</a></li>
    <li class="breadcrumb-item active" aria-current="page">Verificación</li>
@endsection


@section('content')
<div class="container-fluid">

    {{--
       OPTIMIZACIÓN 1:
       Se ha eliminado el @section('sidebar-menu').
       El menú de la barra lateral debe estar en 'layouts/app.blade.php'
       para no repetirlo en cada vista (Principio DRY).
    --}}

    {{-- BALANCE (FUNCIONALIDAD PRINCIPAL) --}}
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

    {{-- ASIENTOS DESCUDRADOS (HERRAMIENTA DE DIAGNÓSTICO) --}}
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
                            <td>
                                {{-- Enlace al asiento para corregirlo --}}
                                <a href="{{ route('contador.libro-diario.edit', $a->numero) }}">{{ $a->numero }}</a>
                            </td>
                            <td class="text-end">{{ number_format($a->total_debe, 2) }}</td>
                            <td class="text-end">{{ number_format($a->total_haber, 2) }}</td>
                            <td class="text-end text-danger fw-bold">{{ number_format($a->diferencia, 2) }}</td>
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

</div>
@endsection