@extends('layouts.app')

@section('title', 'Verificación - Balance de Comprobación')

@section('sidebar-menu')
{{-- MENÚ PRINCIPAL --}}
<div class="nav-section">Dashboard</div>
<ul>
    <li><a href="{{ route('dashboard.contador') }}" class="nav-link active">
        <i class="fas fa-chart-pie"></i> Panel Principal
    </a></li>
</ul>

{{-- CONTABILIDAD --}}
<div class="nav-section">Contabilidad</div>
<ul>
    <li>
        <a href="{{ route('contador.libro-diario.index') }}" class="nav-link has-submenu">
            <i class="fas fa-book"></i> Libros Contables
        </a>
        <div class="nav-submenu">
            <a href="{{ route('contador.libro-diario.index') }}" class="nav-link"><i class="fas fa-file-alt"></i> Libro Diario</a>
            <a href="{{ route('contador.libro-mayor.index') }}" class="nav-link"><i class="fas fa-book-open"></i> Libro Mayor</a>
            <a href="{{route('contador.balance-comprobacion.index')}}" class="nav-link"><i class="fas fa-balance-scale"></i> Balance Comprobación</a>    
            <a href="{{ route('contador.estado-resultados.index') }}" class="nav-link"><i class="fas fa-chart-bar"></i> Estados Financieros</a>
        </div>
    </li>
    <li>
        <a href="#" class="nav-link has-submenu">
            <i class="fas fa-file-invoice"></i> Registros
        </a>
        <div class="nav-submenu">
            <a href="#" class="nav-link"><i class="fas fa-shopping-cart"></i> Compras</a>
            <a href="#" class="nav-link"><i class="fas fa-cash-register"></i> Ventas</a>
            <a href="#" class="nav-link"><i class="fas fa-university"></i> Bancos</a>
            <a href="#" class="nav-link"><i class="fas fa-money-bill-wave"></i> Caja</a>
        </div>
    </li>
</ul>

{{-- VENTAS Y COBRANZAS --}}
<div class="nav-section">Ventas & Cobranzas</div>
<ul>
    <li><a href="{{ route('contador.reportes.ventas') }}" class="nav-link">
        <i class="fas fa-chart-line"></i> Análisis Ventas
    </a></li>
    <li><a href="{{ route('contador.reportes.compras') }}" class="nav-link">
        <i class="fas fa-wallet"></i> Cartera
    </a></li>
    <li><a href="{{ route('contador.facturas.create') }}" class="nav-link">
        <i class="fas fa-clock"></i> Fact. Pendientes
    </a></li>
    <li><a href="{{ route('contador.facturas.index') }}" class="nav-link">
        <i class="fas fa-exclamation-triangle"></i> Fact. Vencidas
    </a></li>
</ul>

{{-- GESTIÓN --}}
<div class="nav-section">Gestión</div>
<ul>
    <li><a href="{{ route('contador.clientes') }}" class="nav-link">
        <i class="fas fa-users"></i> Clientes
    </a></li>
    <li><a href="{{ route('contador.reportes.medicamentos-controlados') }}" class="nav-link">
        <i class="fas fa-percentage"></i> Márgenes
    </a></li>
    <li><a href="{{ route('contador.reportes.inventario') }}" class="nav-link">
        <i class="fas fa-boxes"></i> Inventario
    </a></li>
</ul>

{{-- REPORTES SUNAT --}}
<div class="nav-section">SUNAT</div>
<ul>
    <li><a href="#" class="nav-link">
        <i class="fas fa-file-invoice-dollar"></i> PLE
    </a></li>
    <li><a href="#" class="nav-link">
        <i class="fas fa-percent"></i> IGV Mensual
    </a></li>
</ul>
@endsection


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
