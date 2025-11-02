@extends('layouts.app')

@section('title', 'Conciliación Bancaria')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos.css') }}" rel="stylesheet">
@endpush

@section('page-title')
    <div>
        <h1><i class="fas fa-tasks me-2"></i>Conciliación Bancaria</h1>
        <p class="text-muted">Verificar saldos de libros contra saldos bancarios.</p>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.bancos.index') }}">Bancos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Conciliación</li>
@endsection

@section('content')
<div class="container-fluid conciliacion-view">

    <!-- Navegación del Módulo de Bancos -->
    <nav class="nav nav-tabs eerr-subnav mb-4">
        <a class="nav-link" href="{{ route('contador.bancos.index') }}">
            <i class="fas fa-home me-1"></i> Dashboard Bancos
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.flujo-diario') }}">
            <i class="fas fa-calendar-day me-1"></i> Flujo Diario
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.diario') }}">
            <i class="fas fa-calendar-alt me-1"></i> Reporte Diario
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.mensual') }}">
            <i class="fas fa-calendar-week me-1"></i> Resumen Mensual
        </a>
        <a class="nav-link active" href="{{ route('contador.bancos.conciliacion') }}">
            <i class="fas fa-tasks me-1"></i> Conciliación
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.transferencias') }}">
            <i class="fas fa-exchange-alt me-1"></i> Transferencias
        </a>
        <a class="nav-link" href="{{ route('contador.bancos.reporte') }}">
            <i class="fas fa-file-invoice me-1"></i> Reportes
        </a>
    </nav>

    <!-- Filtros -->
    <div class="card shadow-sm filters-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.bancos.conciliacion') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="cuenta">Cuenta Bancaria</label>
                        <select name="cuenta" id="cuenta" class="form-select" required onchange="this.form.submit()">
                            <option value="">Seleccione una cuenta...</option>
                            @foreach($listaBancos as $banco)
                                <option value="{{ $banco->Cuenta }}" {{ $cuentaSeleccionada == $banco->Cuenta ? 'selected' : '' }}>
                                    {{ $banco->Banco }} - ({{ $banco->Cuenta }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="fecha_corte">Fecha de Corte</label>
                        <input type="date" name="fecha_corte" id="fecha_corte" class="form-control" value="{{ $fechaCorte }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Generar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($data)
    @php
        $diferencias = $data['diferencias'];
        $chequesPendientes = $data['chequesPendientes'];
        $depositosTransito = $data['depositosTransito'];
        $infoCuenta = $data['infoCuenta'];
    @endphp
    
    <div class="row">
        <!-- Columna Izquierda: Libros -->
        <div class="col-lg-6">
            <div class="card shadow-sm conciliacion-section">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-book-open me-2"></i>Saldo según Libros</h5>
                    <span class="fs-5 fw-bold">S/ {{ number_format($diferencias['saldo_libros'], 2) }}</span>
                </div>
                <div class="card-body">
                    <h6 class="text-danger">(-) Cheques Pendientes ({{ $chequesPendientes->count() }})</h6>
                    <div class="table-responsive mb-3" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            @forelse($chequesPendientes as $cheque)
                            <tr>
                                <td>{{ $cheque->numero_cheque }}</td>
                                <td>{{ \Carbon\Carbon::parse($cheque->fecha_emision)->format('d/m/Y') }}</td>
                                <td class="text-end">S/ {{ number_format($cheque->Monto, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td class="text-muted">No hay cheques pendientes.</td></tr>
                            @endforelse
                        </table>
                    </div>
                    <h6 class="text-success">(+) Depósitos en Tránsito ({{ $depositosTransito->count() }})</h6>
                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            @forelse($depositosTransito as $deposito)
                            <tr>
                                <td>{{ $deposito->Documento }}</td>
                                <td>{{ \Carbon\Carbon::parse($deposito->Fecha)->format('d/m/Y') }}</td>
                                <td class="text-end">S/ {{ number_format($deposito->Monto, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td class="text-muted">No hay depósitos en tránsito.</td></tr>
                            @endforelse
                        </table>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">SALDO CONCILIADO (LIBROS)</h6>
                        <h5 class="mb-0 text-primary fw-bold">S/ {{ number_format($diferencias['saldo_bancario_estimado'], 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Banco -->
        <div class="col-lg-6">
            <form action="{{ route('contador.bancos.conciliacion.store') }}" method="POST">
                @csrf
                <input type="hidden" name="cuenta" value="{{ $cuentaSeleccionada }}">
                <input type="hidden" name="fecha_conciliacion" value="{{ $fechaCorte }}">
                
                <div class="card shadow-sm conciliacion-section">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-university me-2"></i>Saldo según Banco</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="saldo_bancario" class="form-label fw-bold">Ingrese Saldo del Estado de Cuenta Bancario</label>
                            <div class="input-group">
                                <span class="input-group-text">S/</span>
                                <input type="number" step="0.01" class="form-control form-control-lg" id="saldo_bancario" name="saldo_bancario" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                        </div>
                        
                        <hr>
                        
                        @php
                            $saldoEstimado = $diferencias['saldo_bancario_estimado'];
                        @endphp
                        
                        <div id="resultado-conciliacion" class="text-center p-3 rounded">
                            <h6 class="mb-1">DIFERENCIA</h6>
                            <h4 id="diferencia_final" class="fw-bold mb-0">S/ 0.00</h4>
                            <span id="badge_estado" class="badge"></span>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100 mt-3">
                            <i class="fas fa-save me-2"></i> Guardar Conciliación
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @else
        @if(empty($cuentaSeleccionada))
            <div class="alert alert-info text-center">
                <i class="fas fa-arrow-up fa-2x mb-2"></i>
                <h5 class="mb-0">Seleccione una cuenta bancaria y una fecha de corte para comenzar.</h5>
            </div>
        @else
            <div class="alert alert-warning text-center">
                <i class="fas fa-info-circle fa-2x mb-2"></i>
                <h5 class="mb-0">No se encontraron datos de conciliación para la cuenta seleccionada.</h5>
                <p class="mb-0">La cuenta existe pero no se pudo cargar la data de conciliación.</p>
            </div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const saldoBancarioInput = document.getElementById('saldo_bancario');
    const diferenciaFinalEl = document.getElementById('diferencia_final');
    const badgeEstadoEl = document.getElementById('badge_estado');
    const resultadoEl = document.getElementById('resultado-conciliacion');
    
    // Solo si el contenedor de datos existe
    if(saldoBancarioInput && typeof @json($data ?? null) !== 'undefined' && @json($data ?? null) !== null) {
        const saldoLibrosEstimado = {{ $diferencias['saldo_bancario_estimado'] ?? 0 }};

        function calcularDiferencia() {
            let saldoBanco = parseFloat(saldoBancarioInput.value) || 0;
            let diferencia = saldoLibrosEstimado - saldoBanco;

            diferenciaFinalEl.textContent = 'S/ ' + Math.abs(diferencia).toFixed(2);

            if (Math.abs(diferencia) < 0.01) {
                badgeEstadoEl.textContent = 'CUADRADO';
                badgeEstadoEl.className = 'badge bg-success';
                resultadoEl.className = 'text-center p-3 rounded bg-success-soft';
            } else {
                badgeEstadoEl.textContent = 'NO CUADRA';
                badgeEstadoEl.className = 'badge bg-danger';
                resultadoEl.className = 'text-center p-3 rounded bg-danger-soft';
            }
        }

        saldoBancarioInput.addEventListener('input', calcularDiferencia);
        
        // Calcular al cargar por si hay un valor previo
        calcularDiferencia();
    }
});
</script>
@endpush

