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

<style>
/* Estilos específicos para la Conciliación Bancaria */
.banks-nav-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    padding: 0.5rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

.banks-nav-gradient .nav-link {
    color: rgba(255, 255, 255, 0.8);
    border: none;
    border-radius: 8px;
    margin: 0 2px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.banks-nav-gradient .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    transform: translateY(-2px);
}

.banks-nav-gradient .nav-link.active {
    background: white;
    color: #667eea;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.filtros-gradient {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    border: 1px solid rgba(102, 126, 234, 0.1);
    border-radius: 12px;
}

.banks-btn-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.banks-btn-gradient:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

/* Conciliation Cards */
.conciliation-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    height: 100%;
    overflow: hidden;
}

.conciliation-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
}

.books-card {
    border-left: 4px solid #17a2b8;
}

.bank-card {
    border-left: 4px solid #28a745;
}

.books-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
    border: none;
    padding: 1.5rem;
}

.bank-header {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    color: white;
    border: none;
    padding: 1.5rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-content h5 {
    margin: 0;
    font-weight: 600;
    font-size: 1.2rem;
}

.balance-amount {
    font-size: 1.5rem;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 10px;
}

/* Adjustments Section */
.adjustments-section {
    margin-bottom: 2rem;
}

.adjustment-item {
    margin-bottom: 1.5rem;
}

.adjustment-title {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    color: #495057;
    font-weight: 600;
    font-size: 1rem;
}

.count-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    margin-left: 0.5rem;
    border-radius: 12px;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.table-container {
    max-height: 180px;
    overflow-y: auto;
    border-radius: 8px;
    background: #f8f9fa;
}

.modern-table {
    margin: 0;
    font-size: 0.9rem;
}

.modern-table td {
    padding: 0.75rem;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}

.modern-table tbody tr:last-child td {
    border-bottom: none;
}

.item-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.item-number {
    font-weight: 600;
    color: #2c3e50;
}

.item-date {
    font-size: 0.8rem;
    color: #6c757d;
}

.amount-positive {
    color: #28a745;
    font-weight: 600;
}

.amount-negative {
    color: #dc3545;
    font-weight: 600;
}

/* Final Balance */
.final-balance {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    border: 2px solid #e9ecef;
}

.balance-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.balance-label {
    font-weight: 600;
    color: #495057;
}

.balance-value {
    font-size: 1.3rem;
    font-weight: 700;
    color: #667eea;
}

/* Form Section */
.form-section {
    margin-bottom: 2rem;
}

.form-group-enhanced {
    margin-bottom: 1.5rem;
}

.form-group-enhanced .form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.input-group {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.input-group .form-control {
    border: 1px solid #e9ecef;
    padding: 0.75rem 1rem;
    font-size: 1.1rem;
    border-left: none;
}

.input-group .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.input-group .input-group-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: 1px solid #667eea;
    font-weight: 600;
    padding: 0.75rem 1rem;
}

.form-control {
    border-radius: 8px;
    border: 1px solid #e9ecef;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Result Section */
.result-section {
    margin-bottom: 2rem;
}

.result-display {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.result-title {
    margin: 0 0 0.5rem 0;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
}

.result-amount {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
}

.result-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

/* Save Button */
.save-btn {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    border-radius: 8px;
    padding: 1rem 1.5rem;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.save-btn:hover {
    background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

/* Empty States */
.empty-state, .alert-state {
    background: white;
    border-radius: 15px;
    padding: 3rem;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.empty-icon, .alert-icon {
    font-size: 4rem;
    color: #667eea;
    margin-bottom: 1.5rem;
    opacity: 0.7;
}

.empty-state h5, .alert-state h5 {
    color: #495057;
    font-weight: 600;
}

.alert-state p {
    color: #6c757d;
    margin: 1rem 0 0 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .balance-amount {
        font-size: 1.2rem;
    }
    
    .balance-item {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
    
    .result-amount {
        font-size: 1.5rem;
    }
    
    .input-group {
        flex-direction: column;
    }
    
    .input-group .input-group-text {
        border-radius: 8px 8px 0 0;
        border-right: 1px solid #667eea;
        border-bottom: none;
    }
    
    .input-group .form-control {
        border-radius: 0 0 8px 8px;
        border-left: 1px solid #e9ecef;
    }
}
</style>
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