@extends('layouts.app')

@section('title', "Cuenta {$cuenta} - Libro Mayor")

@section('styles')
<style>
/* --- Reset y layout --- */
.container-fluid { padding: 0; }
.main-content-wrapper { margin-left: 0; padding: 0; }

/* Breadcrumb */
.breadcrumb-section { background: #f8f9fa; padding: 1rem 2rem; border-bottom: 1px solid #dee2e6; }
.breadcrumb { margin: 0; font-size: 0.9rem; }
.breadcrumb a { color: #6c757d; text-decoration: none; }
.breadcrumb a:hover { color: #495057; }
.breadcrumb .active { color: #212529; font-weight: 500; }

/* Header */
.page-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; margin-bottom: 1rem; border-radius: 0 0 12px 12px; }
.page-header h1 { margin: 0 0 0.5rem 0; font-size: 2rem; font-weight: 700; }
.page-header p { margin: 0; opacity: 0.85; }
.btn-back { background: #6c757d; border: none; color: white; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; transition: all 0.3s ease; }
.btn-back:hover { background: #545b62; transform: translateY(-1px); }

/* Resumen de la cuenta */
.account-info { background: white; padding: 1.5rem; margin-bottom: 1rem; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.account-field { display: flex; flex-direction: column; }
.account-label { font-size: 0.8rem; text-transform: uppercase; color: #6c757d; font-weight: 600; margin-bottom: 0.25rem; letter-spacing: 0.5px; }
.account-value { font-size: 1.1rem; font-weight: 600; color: #212529; }
.account-value.saldo { font-size: 1.4rem; }
.saldo-deudor { color: #dc3545; }
.saldo-acreedor { color: #28a745; }

/* Resumen del período */
.summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
.summary-card { background: white; padding: 1rem; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); border-left: 4px solid; display: flex; flex-direction: column; justify-content: center; }
.summary-card.debe { border-left-color: #dc3545; }
.summary-card.haber { border-left-color: #28a745; }
.summary-card.total { border-left-color: #007bff; }
.summary-label { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; margin-bottom: 0.25rem; }
.summary-value { font-size: 1.3rem; font-weight: 700; color: #212529; }

/* Tabla de movimientos */
.table-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
.table-header { background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
.table-title { font-size: 1rem; font-weight: 600; margin: 0; }
.table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.table th, .table td { padding: 0.75rem 1rem; text-align: center; }
.table th { background: #f1f3f5; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; }
.table tbody tr:nth-child(even) { background-color: #f8f9fa; }
.debe-column { color: #dc3545; font-weight: 600; }
.haber-column { color: #28a745; font-weight: 600; }
.saldo-column { font-weight: 600; }
.saldo-acumulado { background-color: #e9ecef !important; font-weight: 600; }

/* Links */
.documento-link { color: #007bff; text-decoration: none; font-weight: 500; }
.documento-link:hover { color: #0056b3; text-decoration: underline; }

/* Responsive */
@media (max-width: 768px) {
    .account-info { grid-template-columns: 1fr; }
    .summary-cards { grid-template-columns: 1fr; }
    .page-header h1 { font-size: 1.5rem; }
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
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('contador.libro-mayor.index') }}">Libro Mayor</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $cuenta }}</li>
                </ol>
            </nav>
        </div>

        {{-- Header --}}
        <div class="page-header">
            <h1><i class="fas fa-calculator me-2"></i>Cuenta: {{ $cuenta }}</h1>
            <p>{{ $infoCuenta->cuenta_nombre ?? 'Sin nombre' }}</p>
            <a href="{{ route('contador.libro-mayor.index') }}" class="btn-back"><i class="fas fa-arrow-left me-1"></i>Volver</a>
        </div>

        {{-- Información de la cuenta --}}
        <div class="account-info">
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
                <span class="account-value">{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</span>
            </div>
            <div class="account-field">
                <span class="account-label">Saldo Anterior</span>
                <span class="account-value saldo {{ $saldoAnterior >= 0 ? 'saldo-acreedor' : 'saldo-deudor' }}">
                    S/ {{ number_format(abs($saldoAnterior),2) }} <small>({{ $saldoAnterior >=0 ? 'Acreedor':'Deudor' }})</small>
                </span>
            </div>
            <div class="account-field">
                <span class="account-label">Saldo Final</span>
                <span class="account-value saldo {{ $totalesPeriodo['saldo_final'] >= 0 ? 'saldo-acreedor' : 'saldo-deudor' }}">
                    S/ {{ number_format(abs($totalesPeriodo['saldo_final']),2) }} <small>({{ $totalesPeriodo['saldo_final'] >=0 ? 'Acreedor':'Deudor' }})</small>
                </span>
            </div>
        </div>

        {{-- Resumen del período --}}
        <div class="summary-cards">
            <div class="summary-card debe">
                <div class="summary-label">Total Debe</div>
                <div class="summary-value">S/ {{ number_format($totalesPeriodo['debe'],2) }}</div>
            </div>
            <div class="summary-card haber">
                <div class="summary-label">Total Haber</div>
                <div class="summary-value">S/ {{ number_format($totalesPeriodo['haber'],2) }}</div>
            </div>
            <div class="summary-card total">
                <div class="summary-label">Saldo del Período</div>
                <div class="summary-value {{ $totalesPeriodo['saldo_final'] >=0 ? 'saldo-acreedor' : 'saldo-deudor' }}">
                    S/ {{ number_format($totalesPeriodo['saldo_final'],2) }}
                </div>
            </div>
        </div>

        {{-- Tabla de movimientos --}}
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title"><i class="fas fa-list me-2"></i>Movimientos</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>N° Asiento</th>
                            <th>Descripción</th>
                            <th class="text-end">Debe (S/)</th>
                            <th class="text-end">Haber (S/)</th>
                            <th class="text-end">Saldo Acumulado (S/)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="saldo-acumulado {{ $saldoAnterior >= 0 ? 'saldo-acreedor' : 'saldo-deudor' }}">
                            <td colspan="5" class="text-end"><strong>Saldo Anterior</strong></td>
                            <td class="text-end">{{ number_format($saldoAnterior,2) }}</td>
                        </tr>

                        @forelse($movimientos as $mov)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
                            <td><a href="#" class="documento-link">{{ $mov->numero }}</a></td>
                            <td>{{ Str::limit($mov->concepto ?? '-',50) }}</td>
                            <td class="text-end debe-column">{{ $mov->debe>0?number_format($mov->debe,2):'-' }}</td>
                            <td class="text-end haber-column">{{ $mov->haber>0?number_format($mov->haber,2):'-' }}</td>
                            <td class="text-end saldo-column {{ $mov->saldo_acumulado>=0?'saldo-acreedor':'saldo-deudor' }}">
                                {{ number_format($mov->saldo_acumulado,2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No hay movimientos para este período
                            </td>
                        </tr>
                        @endforelse

                        @if($movimientos->count()>0)
                        <tr class="table-warning fw-bold">
                            <td colspan="3" class="text-end"><strong>Totales del Período</strong></td>
                            <td class="text-end debe-column">{{ number_format($totalesPeriodo['debe'],2) }}</td>
                            <td class="text-end haber-column">{{ number_format($totalesPeriodo['haber'],2) }}</td>
                            <td class="text-end saldo-column">{{ number_format($totalesPeriodo['saldo_final'],2) }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.documento-link').forEach(link=>{
        link.addEventListener('click', e=>{
            e.preventDefault();
            alert('Funcionalidad de ver asiento: ' + link.textContent.trim());
        });
    });
});
</script>
@endsection
