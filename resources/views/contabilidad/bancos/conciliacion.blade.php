@extends('layouts.app')

@section('title', 'Conciliación Bancaria')

@push('styles')
    <link href="{{ asset('css/contabilidad/bancos/conciliacion.css') }}" rel="stylesheet">
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
<div class="conciliacion-container">

    <!-- Navegación del Módulo de Bancos con Gradiente Púrpura -->
    <nav class="nav nav-tabs eerr-subnav mb-4 banks-nav-gradient">
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

    <!-- Filtros con Gradiente Púrpura -->
    <div class="card shadow-sm filters-card mb-4 filtros-gradient">
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
                        <button type="submit" class="btn btn-primary w-100 banks-btn-gradient">
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
            <div class="conciliation-card books-card">
                <div class="card-header books-header">
                    <div class="header-content">
                        <h5><i class="fas fa-book-open me-2"></i>Saldo según Libros</h5>
                        <div class="balance-amount">
                            S/ {{ number_format($diferencias['saldo_libros'], 2) }}
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="adjustments-section">
                        <div class="adjustment-item">
                            <h6 class="adjustment-title">
                                <i class="fas fa-minus-circle me-2"></i>Cheques Pendientes
                                <span class="badge count-badge">{{ $chequesPendientes->count() }}</span>
                            </h6>
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-sm modern-table">
                                        @forelse($chequesPendientes as $cheque)
                                        <tr>
                                            <td>
                                                <div class="item-info">
                                                    <span class="item-number">{{ $cheque->numero_cheque }}</span>
                                                    <span class="item-date">{{ \Carbon\Carbon::parse($cheque->fecha_emision)->format('d/m/Y') }}</span>
                                                </div>
                                            </td>
                                            <td class="text-end amount-negative">
                                                S/ {{ number_format($cheque->Monto, 2) }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td class="text-muted text-center py-3">No hay cheques pendientes.</td></tr>
                                        @endforelse
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="adjustment-item">
                            <h6 class="adjustment-title">
                                <i class="fas fa-plus-circle me-2"></i>Depósitos en Tránsito
                                <span class="badge count-badge">{{ $depositosTransito->count() }}</span>
                            </h6>
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-sm modern-table">
                                        @forelse($depositosTransito as $deposito)
                                        <tr>
                                            <td>
                                                <div class="item-info">
                                                    <span class="item-number">{{ $deposito->Documento }}</span>
                                                    <span class="item-date">{{ \Carbon\Carbon::parse($deposito->Fecha)->format('d/m/Y') }}</span>
                                                </div>
                                            </td>
                                            <td class="text-end amount-positive">
                                                S/ {{ number_format($deposito->Monto, 2) }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td class="text-muted text-center py-3">No hay depósitos en tránsito.</td></tr>
                                        @endforelse
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="final-balance">
                        <div class="balance-item">
                            <span class="balance-label">SALDO CONCILIADO (LIBROS)</span>
                            <span class="balance-value">S/ {{ number_format($diferencias['saldo_bancario_estimado'], 2) }}</span>
                        </div>
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
                
                <div class="conciliation-card bank-card">
                    <div class="card-header bank-header">
                        <h5><i class="fas fa-university me-2"></i>Saldo según Banco</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-section">
                            <div class="form-group-enhanced">
                                <label for="saldo_bancario" class="form-label">Ingrese Saldo del Estado de Cuenta Bancario</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" step="0.01" class="form-control form-control-lg" id="saldo_bancario" name="saldo_bancario" required>
                                </div>
                            </div>
                            
                            <div class="form-group-enhanced">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3" placeholder="Ingrese observaciones sobre la conciliación..."></textarea>
                            </div>
                        </div>
                        
                        <div class="result-section">
                            <div id="resultado-conciliacion" class="result-display">
                                <h6 class="result-title">DIFERENCIA</h6>
                                <h4 id="diferencia_final" class="result-amount">S/ 0.00</h4>
                                <span id="badge_estado" class="result-badge"></span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100 save-btn">
                            <i class="fas fa-save me-2"></i> Guardar Conciliación
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @else
        @if(empty($cuentaSeleccionada))
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <h5>Seleccione una cuenta bancaria y una fecha de corte para comenzar.</h5>
            </div>
        @else
            <div class="alert-state">
                <div class="alert-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h5>No se encontraron datos de conciliación para la cuenta seleccionada.</h5>
                <p>La cuenta existe pero no se pudo cargar la data de conciliación.</p>
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
                resultadoEl.className = 'result-display bg-success-soft';
                resultadoEl.style.borderColor = '#28a745';
            } else {
                badgeEstadoEl.textContent = 'NO CUADRA';
                badgeEstadoEl.className = 'badge bg-danger';
                resultadoEl.className = 'result-display bg-danger-soft';
                resultadoEl.style.borderColor = '#dc3545';
            }
        }

        saldoBancarioInput.addEventListener('input', calcularDiferencia);
        
        // Calcular al cargar por si hay un valor previo
        calcularDiferencia();
    }
});
</script>
@endpush