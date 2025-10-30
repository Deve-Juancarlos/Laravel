@extends('layouts.app')

@section('title', 'Balance por Clases - Balance de Comprobación')

@section('styles')
<style>
/* Colores personalizados por clase */
.bg-activo { background: #3b82f6; color: white; }
.bg-pasivo { background: #f59e0b; color: white; }
.bg-patrimonio { background: #06b6d4; color: white; }
.bg-ingresos { background: #10b981; color: white; }
.bg-gastos { background: #ef4444; color: white; }

/* Tarjetas resumen general */
.summary-card {
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    transition: transform 0.2s ease;
}
.summary-card:hover { transform: translateY(-3px); }

/* Tarjetas por clase */
.class-card {
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}
.class-card-header {
    padding: 15px 20px;
    font-weight: 600;
    font-size: 1.1rem;
}
.class-card-body { padding: 0; }

/* Tabla dentro de tarjeta */
.table th, .table td { vertical-align: middle; }
.total-row { font-weight: 700; background: #f1f5f9; }
</style>
@endsection

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
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="text-center mb-5">
        <h1 class="fw-bold"><i class="fas fa-layer-group me-2"></i>Balance por Clases de Cuentas</h1>
        <p class="text-muted mb-1">Organización según Plan Contable General Empresarial (PCGE)</p>
        <small class="text-secondary">Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</small>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contador.balance-comprobacion.clases') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Actualizar Clases
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="row mb-4">
        @php
            $clases = ['ACTIVO'=>'bg-activo','PASIVO'=>'bg-pasivo','PATRIMONIO'=>'bg-patrimonio','INGRESOS'=>'bg-ingresos','GASTOS'=>'bg-gastos'];
        @endphp
        @foreach($clases as $nombre => $color)
            @php
                $total = isset($cuentasPorClase[$nombre]) ? array_sum(array_map(fn($c)=>$c->saldo,$cuentasPorClase[$nombre])) : 0;
            @endphp
            <div class="col-md-2 col-sm-4 col-6">
                <div class="summary-card {{ $color }} text-center">
                    <h6 class="mb-1">{{ $nombre }}</h6>
                    <h4 class="fw-bold mb-0">S/ {{ number_format($total,2) }}</h4>
                </div>
            </div>
        @endforeach
        <div class="col-md-2 col-sm-4 col-6">
            <div class="summary-card border text-center">
                <h6 class="mb-1">ECUACIÓN</h6>
                <small class="text-muted">ACTIVO = PASIVO + PATRIMONIO</small>
            </div>
        </div>
    </div>

    <!-- Tablas por clase -->
    @foreach($clases as $nombre => $color)
        @if(isset($cuentasPorClase[strtoupper($nombre)]) && count($cuentasPorClase[strtoupper($nombre)])>0)
            @php
                $total = array_sum(array_map(fn($c)=>$c->saldo,$cuentasPorClase[strtoupper($nombre)]));
            @endphp
            <div class="class-card">
                <div class="class-card-header {{ $color }}">
                    {{ $nombre }}
                </div>
                <div class="class-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Cuenta</th>
                                    <th class="text-end">Saldo</th>
                                    <th class="text-end">Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cuentasPorClase[strtoupper($nombre)] as $cuenta)
                                    <tr>
                                        <td>{{ $cuenta->cuenta }}</td>
                                        <td class="text-end">S/ {{ number_format($cuenta->saldo,2) }}</td>
                                        <td class="text-end">{{ $total>0 ? number_format(($cuenta->saldo/$total)*100,1) : 0 }}%</td>
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td>TOTAL {{ $nombre }}</td>
                                    <td class="text-end">S/ {{ number_format($total,2) }}</td>
                                    <td class="text-end">100%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <!-- Botones de acción -->
    <div class="d-flex justify-content-center mt-4 gap-2 flex-wrap">
        <a href="{{ route('contador.balance-comprobacion.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver al Balance
        </a>
        <a href="{{ route('contador.balance-comprobacion.verificar') }}" class="btn btn-warning">
            <i class="fas fa-check-circle me-2"></i>Verificar Integridad
        </a>
        <button class="btn btn-success" onclick="exportarClases()">
            <i class="fas fa-download me-2"></i>Exportar Excel
        </button>
    </div>

</div>

<script>
function exportarClases() {
    const params = new URLSearchParams({
        fecha_inicio: '{{ $fechaInicio }}',
        fecha_fin: '{{ $fechaFin }}',
        formato: 'clases'
    });
    window.location.href = `{{ route('contador.balance-comprobacion.exportar') }}?${params}`;
}
</script>
@endsection
