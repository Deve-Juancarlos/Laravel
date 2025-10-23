@extends('layouts.admin')

@section('title', 'Dashboard Administrativo')

@push('styles')
<style>
    /* ===== DASHBOARD STYLES ===== */
    .content-wrapper {
        animation: fadeIn 0.4s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* QUICK ACTIONS */
    .quick-actions {
        margin-bottom: 2rem;
    }

    .quick-action-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .quick-action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        transition: width 0.3s;
    }

    .quick-action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }

    .quick-action-card:hover::before {
        width: 100%;
        opacity: 0.05;
    }

    .quick-action-card.danger::before { background: #e74c3c; }
    .quick-action-card.primary::before { background: #3498db; }
    .quick-action-card.warning::before { background: #f39c12; }
    .quick-action-card.success::before { background: #27ae60; }

    .quick-action-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: white;
        position: relative;
        z-index: 1;
    }

    .quick-action-card.danger .quick-action-icon { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }
    .quick-action-card.primary .quick-action-icon { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
    .quick-action-card.warning .quick-action-icon { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
    .quick-action-card.success .quick-action-icon { background: linear-gradient(135deg, #27ae60 0%, #229954 100%); }

    .quick-action-content {
        flex: 1;
        position: relative;
        z-index: 1;
    }

    .quick-action-content h5 {
        margin: 0 0 0.25rem 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .quick-action-content p {
        margin: 0;
        font-size: 0.85rem;
        color: #7f8c8d;
    }

    /* STATS GRID */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        border-left: 5px solid;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.5s;
    }

    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.15);
    }

    .stat-card:hover::after {
        opacity: 1;
    }

    .stat-card.warning { border-left-color: #f39c12; }
    .stat-card.info { border-left-color: #3498db; }
    .stat-card.success { border-left-color: #27ae60; }
    .stat-card.danger { border-left-color: #e74c3c; }

    .stat-card-content {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        position: relative;
        z-index: 1;
    }

    .stat-icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        position: relative;
    }

    .stat-card.warning .stat-icon-wrapper {
        background: linear-gradient(135deg, rgba(243, 156, 18, 0.1) 0%, rgba(243, 156, 18, 0.05) 100%);
        color: #f39c12;
    }

    .stat-card.info .stat-icon-wrapper {
        background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(52, 152, 219, 0.05) 100%);
        color: #3498db;
    }

    .stat-card.success .stat-icon-wrapper {
        background: linear-gradient(135deg, rgba(39, 174, 96, 0.1) 0%, rgba(39, 174, 96, 0.05) 100%);
        color: #27ae60;
    }

    .stat-card.danger .stat-icon-wrapper {
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.1) 0%, rgba(231, 76, 60, 0.05) 100%);
        color: #e74c3c;
    }

    .stat-details {
        flex: 1;
    }

    .stat-details h3 {
        margin: 0 0 0.5rem 0;
        font-size: 2rem;
        font-weight: 800;
        color: #2c3e50;
        line-height: 1;
    }

    .stat-details p {
        margin: 0 0 0.5rem 0;
        font-size: 0.95rem;
        color: #7f8c8d;
        font-weight: 600;
    }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .stat-trend.positive {
        background: rgba(39, 174, 96, 0.1);
        color: #27ae60;
    }

    .stat-trend.negative {
        background: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
    }

    .stat-trend.neutral {
        background: rgba(149, 165, 166, 0.1);
        color: #7f8c8d;
    }

    /* CHARTS GRID */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .chart-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transition: all 0.3s;
    }

    .chart-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f5f7fa;
    }

    .chart-header h4 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .chart-header .badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    /* TABLES GRID */
    .tables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 1.5rem;
    }

    .table-card {
        background: white;
        border-radius: 16px;
        padding: 0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        overflow: hidden;
        transition: all 0.3s;
    }

    .table-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .table-header {
        padding: 1.5rem 1.75rem;
        border-bottom: 2px solid #f5f7fa;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    .table-header h4 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .table-responsive {
        padding: 0;
    }

    .table {
        margin: 0;
    }

    .table thead th {
        background: #f8f9fa;
        color: #7f8c8d;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem 1.25rem;
    }

    .table tbody tr {
        transition: all 0.2s;
        border-bottom: 1px solid #ecf0f1;
    }

    .table tbody tr:hover {
        background: #f8f9fa;
        transform: scale(1.01);
    }

    .table tbody td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    .badge-status {
        padding: 0.4rem 0.9rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .badge-status.pending {
        background: rgba(243, 156, 18, 0.15);
        color: #f39c12;
    }

    .badge-status.completed {
        background: rgba(39, 174, 96, 0.15);
        color: #27ae60;
    }

    .badge-status.cancelled {
        background: rgba(231, 76, 60, 0.15);
        color: #e74c3c;
    }

    /* SECTION HEADER */
    .section-header {
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-title::before {
        content: '';
        width: 4px;
        height: 24px;
        background: linear-gradient(180deg, #3498db 0%, #2980b9 100%);
        border-radius: 2px;
    }

    
    .btn-action {
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
        text-decoration: none;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-action.btn-view {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
    }

    .btn-action.btn-delete {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
    }

    .btn-action.btn-edit {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        color: white;
    }

    /* EMPTY STATE */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
    }

    .empty-state-icon {
        font-size: 4rem;
        color: #ecf0f1;
        margin-bottom: 1rem;
    }

    .empty-state-text {
        color: #95a5a6;
        font-size: 1rem;
        font-weight: 600;
    }

    /* LOADING STATE */
    .loading-skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        border-radius: 8px;
    }

    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* RESPONSIVE */
    @media (max-width: 1200px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .tables-grid {
            grid-template-columns: 1fr;
        }

        .stat-card-content {
            flex-direction: column;
            text-align: center;
        }

        .chart-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
    }

    @media (max-width: 576px) {
        .quick-action-card {
            flex-direction: column;
            text-align: center;
        }

        .stat-details h3 {
            font-size: 1.5rem;
        }

        .table-header {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }

        .table-header .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
    <div class="content-wrapper">
        <!-- ACCESOS RÁPIDOS -->
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-bolt"></i>
                Accesos Rápidos
            </h2>
        </div>

        <div class="quick-actions">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="{{ route('admin.planillas.index') }}" class="quick-action-card danger">
                        <div class="quick-action-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="quick-action-content">
                            <h5>Planillas</h5>
                            <p>Gestionar cobranzas</p>
                        </div>
                        <i class="fas fa-chevron-right" style="color: #e0e0e0;"></i>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="{{ route('admin.bancos.index') }}" class="quick-action-card primary">
                        <div class="quick-action-icon">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <div class="quick-action-content">
                            <h5>Bancos</h5>
                            <p>Administrar cuentas</p>
                        </div>
                        <i class="fas fa-chevron-right" style="color: #e0e0e0;"></i>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="{{ route('admin.usuarios.index') }}" class="quick-action-card warning">
                        <div class="quick-action-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="quick-action-content">
                            <h5>Usuarios</h5>
                            <p>Control de accesos</p>
                        </div>
                        <i class="fas fa-chevron-right" style="color: #e0e0e0;"></i>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="{{ route('admin.cuentas-corrientes.index') }}" class="quick-action-card success">
                        <div class="quick-action-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="quick-action-content">
                            <h5>Cuentas Corrientes</h5>
                            <p>Movimientos y saldos</p>
                        </div>
                        <i class="fas fa-chevron-right" style="color: #e0e0e0;"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- MÉTRICAS CLAVE -->
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-chart-line"></i>
                Métricas Principales
            </h2>
            <span class="badge bg-primary">Actualizado: {{ now()->format('d/m/Y H:i') }}</span>
        </div>

        <div class="stats-grid">
            <!-- Cobranza Pendiente -->
            <div class="stat-card warning">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="stat-details">
                        <h3>S/ {{ number_format($data['cobranzaPendiente'] ?? 0, 2) }}</h3>
                        <p>Cobranza Pendiente</p>
                        <span class="stat-trend negative">
                            <i class="fas fa-file-alt"></i>
                            {{ $data['planillasPendientes'] ?? 0 }} planillas
                        </span>
                    </div>
                </div>
            </div>

            <!-- Saldo Total Clientes -->
            <div class="stat-card info">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3>S/ {{ number_format($data['saldoTotalClientes'] ?? 0, 2) }}</h3>
                        <p>Saldo Total Clientes</p>
                        <span class="stat-trend {{ ($data['saldoTotalClientes'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                            <i class="fas fa-arrow-{{ ($data['saldoTotalClientes'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                            {{ ($data['saldoTotalClientes'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($data['variacionSaldo'] ?? 0, 1) }}%
                        </span>
                    </div>
                </div>
            </div>

            <!-- Ingresos en Caja -->
            <div class="stat-card success">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <div class="stat-details">
                        <h3>S/ {{ number_format($data['cajaHoy'] ?? 0, 2) }}</h3>
                        <p>Ingresos Hoy</p>
                        <span class="stat-trend positive">
                            <i class="fas fa-check-circle"></i>
                            {{ $data['operacionesHoy'] ?? 0 }} operaciones
                        </span>
                    </div>
                </div>
            </div>

            <!-- Alertas Críticas -->
            <div class="stat-card danger">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-details">
                        <h3>{{ $data['alertasCriticas'] ?? 0 }}</h3>
                        <p>Alertas Críticas</p>
                        <span class="stat-trend {{ ($data['alertasCriticas'] ?? 0) > 0 ? 'negative' : 'neutral' }}">
                            <i class="fas fa-bell"></i>
                            {{ ($data['alertasCriticas'] ?? 0) > 0 ? 'Requiere atención' : 'Todo en orden' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICOS -->
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-chart-bar"></i>
                Análisis Visual
            </h2>
        </div>

        <div class="charts-grid">
            <!-- Planillas por Vendedor -->
            <div class="chart-card">
                <div class="chart-header">
                    <h4>Planillas por Vendedor</h4>
                    <span class="badge bg-primary">Últimos 30 días</span>
                </div>
                <div style="height: 280px; position: relative;">
                    <canvas id="planillasVendedorChart"></canvas>
                </div>
            </div>

            <!-- Notas de Crédito -->
            <div class="chart-card">
                <div class="chart-header">
                    <h4>Estado de Notas de Crédito</h4>
                    <span class="badge bg-success">Periodo actual</span>
                </div>
                <div style="height: 280px; position: relative;">
                    <canvas id="notasCreditoChart"></canvas>
                </div>
            </div>
        </div>

        <!-- TABLAS -->
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-table"></i>
                Actividad Reciente
            </h2>
        </div>

        <div class="tables-grid">
            <!-- Planillas Recientes -->
            <div class="table-card">
                <div class="table-header">
                    <h4>
                        <i class="fas fa-file-invoice me-2"></i>
                        Planillas Recientes
                    </h4>
                    <a href="{{ route('admin.planillas.index') }}" class="btn btn-action btn-view">
                        <i class="fas fa-eye"></i>
                        Ver Todas
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Serie</th>
                                <th>Número</th>
                                <th>Vendedor</th>
                                <th>Fecha</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['planillasRecientes'] ?? [] as $p)
                            <tr>
                                <td><strong>{{ $p->Serie }}</strong></td>
                                <td>{{ $p->Numero }}</td>
                                <td>
                                    <i class="fas fa-user-tie me-1" style="color: #3498db;"></i>
                                    {{ $p->Vendedor }}
                                </td>
                                <td>
                                    <i class="far fa-calendar-alt me-1" style="color: #95a5a6;"></i>
                                    {{ \Carbon\Carbon::parse($p->FechaCrea)->format('d/m/Y') }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.planillas.show', [$p->Serie, $p->Numero]) }}" 
                                       class="btn btn-sm btn-action btn-delete"
                                       onclick="return confirm('¿Está seguro de eliminar esta planilla?')">
                                        <i class="fas fa-trash"></i>
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                        <div class="empty-state-text">
                                            No hay planillas recientes
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Últimos Movimientos en Caja -->
            <div class="table-card">
                <div class="table-header">
                    <h4>
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Movimientos en Caja
                    </h4>
                    <a href="{{ route('admin.reportes.movimientos') }}" class="btn btn-action btn-view">
                        <i class="fas fa-eye"></i>
                        Ver Todos
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['movimientosCaja'] ?? [] as $m)
                            <tr>
                                <td><strong>{{ $m->Documento }}</strong></td>
                                <td>
                                    <span class="badge-status {{ $m->Tipo == 5 ? 'completed' : 'pending' }}">
                                        <i class="fas fa-{{ $m->Tipo == 5 ? 'check' : 'clock' }}"></i>
                                        {{ $m->Tipo == 5 ? 'Cobranza' : 'Otro' }}
                                    </span>
                                </td>
                                <td>
                                    <i class="far fa-clock me-1" style="color: #95a5a6;"></i>
                                    {{ \Carbon\Carbon::parse($m->Fecha)->format('d/m/Y H:i') }}
                                </td>
                                <td class="text-end">
                                    <strong style="color: #27ae60; font-size: 1.05rem;">
                                        <i class="fas fa-dollar-sign"></i>
                                        S/ {{ number_format($m->Monto, 2) }}
                                    </strong>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                        <div class="empty-state-text">
                                            No hay movimientos recientes
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
       
        Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        Chart.defaults.color = '#7f8c8d';
        Chart.defaults.plugins.legend.position = 'bottom';
        Chart.defaults.plugins.legend.labels.padding = 20;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;

        
        const planillasData = @json($data['graficoPlanillasVendedor'] ?? ['labels' => [], 'data' => []]);
        if (planillasData.labels?.length) {
            const ctx1 = document.getElementById('planillasVendedorChart');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: planillasData.labels,
                    datasets: [{
                        label: 'Número de Planillas',
                        data: planillasData.data,
                        backgroundColor: 'rgba(231, 76, 60, 0.8)',
                        borderColor: 'rgba(231, 76, 60, 1)',
                        borderWidth: 2,
                        borderRadius: 8,
                        hoverBackgroundColor: 'rgba(192, 57, 43, 0.9)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return 'Planillas: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: { size: 12 }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                font: { size: 12, weight: '600' }
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

      
        const notasData = @json($data['graficoNotasCredito'] ?? ['labels' => [], 'data' => []]);
        if (notasData.labels?.length) {
            const ctx2 = document.getElementById('notasCreditoChart');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: notasData.labels,
                    datasets: [{
                        data: notasData.data,
                        backgroundColor: [
                            'rgba(39, 174, 96, 0.8)',
                            'rgba(243, 156, 18, 0.8)',
                            'rgba(231, 76, 60, 0.8)'
                        ],
                        borderColor: [
                            'rgba(39, 174, 96, 1)',
                            'rgba(243, 156, 18, 1)',
                            'rgba(231, 76, 60, 1)'
                        ],
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: { size: 13, weight: '600' },
                                color: '#2c3e50'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1500,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

       
        const animateValue = (element, start, end, duration) => {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const value = Math.floor(progress * (end - start) + start);
                element.textContent = value.toLocaleString('es-PE');
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        };

        
        document.querySelectorAll('.stat-details h3').forEach(element => {
            const text = element.textContent;
            const match = text.match(/[\d,]+/);
            if (match) {
                const value = parseInt(match[0].replace(/,/g, ''));
                if (!isNaN(value)) {
                    element.textContent = text.replace(match[0], '0');
                    animateValue(element, 0, value, 1500);
                }
            }
        });
    });
</script>
@endpush