@extends('layouts.app')

@section('title', "Cuenta {$cuenta} - Libro Mayor")

@section('styles')
<style>
    .container-fluid {
        padding: 0;
    }
    
    .main-content-wrapper {
        margin-left: 0;
        padding: 0;
    }
    
    .breadcrumb-section {
        background: #f8f9fa;
        padding: 1rem 2rem;
        border-bottom: 1px solid #dee2e6;
    }
    
    .breadcrumb {
        margin: 0;
        font-size: 0.9rem;
    }
    
    .breadcrumb a {
        color: #6c757d;
        text-decoration: none;
    }
    
    .breadcrumb a:hover {
        color: #495057;
    }
    
    .breadcrumb .active {
        color: #212529;
        font-weight: 500;
    }
    
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        margin-bottom: 0;
    }
    
    .page-header h1 {
        margin: 0 0 0.5rem 0;
        font-size: 2rem;
        font-weight: 700;
    }
    
    .account-info {
        background: white;
        padding: 1.5rem;
        border-bottom: 1px solid #dee2e6;
    }
    
    .account-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    
    .account-field {
        display: flex;
        flex-direction: column;
    }
    
    .account-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 0.25rem;
        letter-spacing: 0.5px;
    }
    
    .account-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
    }
    
    .account-value.saldo {
        font-size: 1.5rem;
    }
    
    .saldo-deudor {
        color: #dc3545;
    }
    
    .saldo-acreedor {
        color: #28a745;
    }
    
    .content-wrapper {
        background: white;
        padding: 0;
    }
    
    .table-container {
        background: white;
    }
    
    .table-header {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .table-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
    }
    
    .table {
        margin: 0;
        font-size: 0.9rem;
    }
    
    .table th {
        background: #f8f9fa;
        border-top: none;
        font-weight: 600;
        padding: 0.875rem 1rem;
        color: #495057;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    
    .table td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
    }
    
    .table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .saldo-column {
        text-align: right;
        font-weight: 600;
    }
    
    .debe-column {
        color: #dc3545;
    }
    
    .haber-column {
        color: #28a745;
    }
    
    .saldo-acumulado {
        font-weight: 600;
        background-color: #e9ecef !important;
    }
    
    .documento-link {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
    }
    
    .documento-link:hover {
        color: #0056b3;
        text-decoration: underline;
    }
    
    .btn-back {
        background: #6c757d;
        border: none;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .btn-back:hover {
        background: #545b62;
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }
    
    .summary-cards {
        padding: 1.5rem;
        background: #f8f9fa;
    }
    
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .summary-card {
        background: white;
        padding: 1rem;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }
    
    .summary-card.total {
        border-left-color: #28a745;
    }
    
    .summary-card.debe {
        border-left-color: #dc3545;
    }
    
    .summary-card.haber {
        border-left-color: #28a745;
    }
    
    .summary-label {
        font-size: 0.8rem;
        color: #6c757d;
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }
    
    .summary-value {
        font-size: 1.3rem;
        font-weight: 700;
        color: #212529;
    }
    
    @media (max-width: 768px) {
        .account-details {
            grid-template-columns: 1fr;
        }
        .page-header {
            padding: 1.5rem;
        }
        .page-header h1 {
            font-size: 1.5rem;
        }
        .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="main-content-wrapper">
        {{-- Breadcrumb --}}
        <div class="breadcrumb-section">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('contador.libro-mayor.index') }}">Libro Mayor</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $cuenta }}</li>
                </ol>
            </nav>
        </div>

        {{-- Header --}}
        <div class="page-header">
            <h1><i class="fas fa-calculator me-2"></i>Cuenta: {{ $cuenta }}</h1>
            <p>Movimientos detallados - {{ $infoCuenta->cuenta_nombre ?? 'Sin nombre' }}</p>
            <a href="{{ route('contador.libro-mayor.index') }}" class="btn-back">
                <i class="fas fa-arrow-left me-1"></i>Volver al Libro Mayor
            </a>
        </div>

        {{-- Información de la Cuenta --}}
        <div class="account-info">
            <div class="account-details">
                <div class="account-field">
                    <span class="account-label">Cuenta Contable</span>
                    <span class="account-value">{{ $cuenta }}</span>
                </div>
                <div class="account-field">
                    <span class="account-label">Nombre de la Cuenta</span>
                    <span class="account-value">{{ $infoCuenta->cuenta_nombre ?? 'Sin nombre' }}</span>
                </div>
                <div class="account-field">
                    <span class="account-label">Período</span>
                    <span class="account-value">{{ $fechaInicio }} - {{ $fechaFin }}</span>
                </div>
                <div class="account-field">
                    <span class="account-label">Total Movimientos</span>
                    <span class="account-value">{{ number_format($infoCuenta->total_movimientos ?? 0) }}</span>
                </div>
                <div class="account-field">
                    <span class="account-label">Saldo Anterior</span>
                    <span class="account-value saldo {{ $saldoAnterior >= 0 ? 'saldo-acreedor' : 'saldo-deudor' }}">
                        S/ {{ number_format(abs($saldoAnterior), 2) }}
                        <small class="text-muted">({{ $saldoAnterior >= 0 ? 'Acreedor' : 'Deudor' }})</small>
                    </span>
                </div>
                <div class="account-field">
                    <span class="account-label">Saldo Final</span>
                    <span class="account-value saldo {{ ($saldoAnterior + $totalesPeriodo['debe'] - $totalesPeriodo['haber']) >= 0 ? 'saldo-acreedor' : 'saldo-deudor' }}">
                        S/ {{ number_format(abs($totalesPeriodo['saldo_final']), 2) }}
                        <small class="text-muted">({{ ($saldoAnterior + $totalesPeriodo['debe'] - $totalesPeriodo['haber']) >= 0 ? 'Acreedor' : 'Deudor' }})</small>
                    </span>
                </div>
            </div>
        </div>

        {{-- Resumen del Período --}}
        <div class="summary-cards">
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-label">Total Debe</div>
                    <div class="summary-value debe">S/ {{ number_format($totalesPeriodo['debe'], 2) }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Total Haber</div>
                    <div class="summary-value haber">S/ {{ number_format($totalesPeriodo['haber'], 2) }}</div>
                </div>
                <div class="summary-card total">
                    <div class="summary-label">Saldo del Período</div>
                    <div class="summary-value {{ $totalesPeriodo['debe'] - $totalesPeriodo['haber'] >= 0 ? 'saldo-acreedor' : 'saldo-deudor' }}">
                        S/ {{ number_format($totalesPeriodo['debe'] - $totalesPeriodo['haber'], 2) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de Movimientos --}}
        <div class="content-wrapper">
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">
                        <i class="fas fa-list me-2"></i>Movimientos de la Cuenta
                    </h3>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Fecha</th>
                                <th style="width: 80px;">N° Asiento</th>
                                <th>Descripción</th>
                                <th style="width: 120px;">Auxiliar</th>
                                <th class="text-end" style="width: 120px;">Debe (S/)</th>
                                <th class="text-end" style="width: 120px;">Haber (S/)</th>
                                <th class="text-end" style="width: 120px;">Saldo Acum. (S/)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Fila de Saldo Anterior --}}
                            <tr class="saldo-acumulado">
                                <td colspan="5" class="text-end">
                                    <strong>SALDO ANTERIOR</strong>
                                </td>
                                <td></td>
                                <td class="text-end saldo-column">
                                    <strong>{{ number_format($saldoAnterior, 2) }}</strong>
                                </td>
                            </tr>

                            @forelse($movimientos as $movimiento)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="#" class="documento-link" title="Ver asiento completo">
                                            {{ $movimiento->numero }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-500">{{ Str::limit($movimiento->concepto ?? $movimiento->Descripcion, 50) }}</div>
                                        @if($movimiento->auxiliar)
                                            <small class="text-muted">{{ Str::limit($movimiento->auxiliar, 30) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($movimiento->auxiliar ?? '-', 20) }}</td>
                                    <td class="text-end debe-column">
                                        @if($movimiento->debe > 0)
                                            {{ number_format($movimiento->debe, 2) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end haber-column">
                                        @if($movimiento->haber > 0)
                                            {{ number_format($movimiento->haber, 2) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end saldo-column {{ isset($movimiento->saldo_acumulado) && $movimiento->saldo_acumulado >= 0 ? 'saldo-acreedor' : 'saldo-deudor' }}">
                                        {{ number_format($movimiento->saldo_acumulado ?? 0, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        No hay movimientos registrados para esta cuenta en el período
                                    </td>
                                </tr>
                            @endforelse

                            {{-- Fila de Totales --}}
                            @if($movimientos->count() > 0)
                                <tr class="table-warning fw-bold">
                                    <td colspan="4" class="text-end">
                                        <strong>TOTALES DEL PERÍODO</strong>
                                    </td>
                                    <td class="text-end debe-column">
                                        <strong>{{ number_format($totalesPeriodo['debe'], 2) }}</strong>
                                    </td>
                                    <td class="text-end haber-column">
                                        <strong>{{ number_format($totalesPeriodo['haber'], 2) }}</strong>
                                    </td>
                                    <td class="text-end saldo-column">
                                        <strong>{{ number_format($totalesPeriodo['saldo_final'], 2) }}</strong>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Resaltar filas con saldos importantes
    const saldoRows = document.querySelectorAll('.saldo-acumulado');
    saldoRows.forEach(row => {
        row.style.backgroundColor = '#e3f2fd';
        row.style.fontWeight = '600';
    });
    
    // Funcionalidad para ver asientos (si tienes sistema de asientos)
    const asientoLinks = document.querySelectorAll('.documento-link');
    asientoLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const numeroAsiento = this.textContent.trim();
            // Aquí podrías abrir un modal o redirigir al detalle del asiento
            console.log('Ver asiento:', numeroAsiento);
        });
    });
});
</script>
@endsection