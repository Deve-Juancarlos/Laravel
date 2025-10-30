@extends('dashboard.contador')

@section('title', 'Balance de Comprobación')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary mb-0">
            <i class="fas fa-balance-scale me-2"></i> Balance de Comprobación
        </h4>
        <span class="text-muted fst-italic">
            {{ now()->format('d/m/Y H:i') }}
        </span>
    </div>

    {{-- Filtros de búsqueda --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('libros.balance-comprobacion.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" 
                           value="{{ request('fecha_inicio') }}" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" 
                           value="{{ request('fecha_fin') }}" class="form-control" required>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calculator me-2"></i> Generar Balance
                    </button>
                </div>
                @if(isset($resumen) && count($resumen) > 0)
                <div class="col-md-3 d-grid">
                    <div class="btn-group" role="group">
                        <a href="{{ route('libros.balance-comprobacion.export.pdf', request()->all()) }}" class="btn btn-danger">
                            <i class="fas fa-file-pdf me-2"></i> PDF
                        </a>
                        <a href="{{ route('libros.balance-comprobacion.export.excel', request()->all()) }}" class="btn btn-success">
                            <i class="fas fa-file-excel me-2"></i> Excel
                        </a>
                    </div>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Resultados del balance --}}
    @if(isset($resumen) && count($resumen) > 0)
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Resumen por Clases Contables 
                    <small class="text-light fst-italic">(del {{ \Carbon\Carbon::parse(request('fecha_inicio'))->format('d/m/Y') }} 
                    al {{ \Carbon\Carbon::parse(request('fecha_fin'))->format('d/m/Y') }})</small>
                </h5>
            </div>
            <div class="card-body p-4">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Clase Contable</th>
                            <th>Total Debe (S/)</th>
                            <th>Total Haber (S/)</th>
                            <th>Resultado Neto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resumen as $clase => $valores)
                            @php
                                $neto = $valores['total_debe'] - $valores['total_haber'];
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ ucfirst(strtolower($clase)) }}</td>
                                <td class="text-end text-success">{{ number_format($valores['total_debe'], 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($valores['total_haber'], 2) }}</td>
                                <td class="text-end fw-bold {{ $neto >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($neto, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        @php
                            $totalDebe = collect($resumen)->sum('total_debe');
                            $totalHaber = collect($resumen)->sum('total_haber');
                            $diferencia = $totalDebe - $totalHaber;
                        @endphp
                        <tr class="text-center">
                            <td>Total General</td>
                            <td class="text-end">{{ number_format($totalDebe, 2) }}</td>
                            <td class="text-end">{{ number_format($totalHaber, 2) }}</td>
                            <td class="text-end {{ $diferencia == 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($diferencia, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                {{-- Estado del balance --}}
                <div class="mt-4 text-center">
                    @if ($diferencia == 0)
                        <div class="alert alert-success fw-semibold">
                            ✅ El balance está cuadrado correctamente.
                        </div>
                    @else
                        <div class="alert alert-danger fw-semibold">
                            ⚠️ Diferencia detectada de {{ number_format($diferencia, 2) }} — revise los asientos contables.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info text-center mt-5">
            <i class="fas fa-info-circle me-2"></i>
            Ingrese un rango de fechas y presione <strong>Generar Balance</strong>.
        </div>
    @endif
</div>
@endsection
