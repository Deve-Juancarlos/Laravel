@extends('layouts.app')

@section('title', 'SIFANO - Área Contable')

@push('styles')
<style>
    /* Estilos específicos para Contador */
    .contador-sidebar {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    .contador-brand {
        background: rgba(255,255,255,0.15);
        border-bottom: 1px solid rgba(255,255,255,0.25);
    }

    .contador-nav .nav-link:hover,
    .contador-nav .nav-link.active {
        background: rgba(255,255,255,0.15);
        border-left-color: #fbbf24;
    }

    .contador-card {
        border-left: 4px solid #059669;
    }

    .financial-metric {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: white;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    .revenue-card {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .expense-card {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .profit-card {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }

    .balance-card {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin: 1rem 0;
    }

    .financial-report-section {
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background: white;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }

    .book-export-btn {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        border: none;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .book-export-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4);
        color: white;
    }

    .tax-alert {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .sunat-status {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .sunat-status.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .sunat-status.sent {
        background: #dcfce7;
        color: #166534;
    }

    .sunat-status.error {
        background: #fee2e2;
        color: #991b1b;
    }

    .accounting-toolbar {
        background: white;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .financial-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .summary-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .icon-green {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
    }

    .icon-orange {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
    }

    .icon-blue {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
    }

    .icon-purple {
        background: rgba(139, 92, 246, 0.1);
        color: #7c3aed;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-calculator text-success me-2"></i>
            Área Contable
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                @yield('breadcrumb')
                <li class="breadcrumb-item active">Contabilidad</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @hasrole('Administrador|Contador')
        <button class="btn btn-outline-success" onclick="exportFinancialReport()">
            <i class="fas fa-download me-2"></i>
            Exportar Reportes
        </button>
        <button class="btn btn-success" onclick="syncWithSunat()">
            <i class="fas fa-sync me-2"></i>
            Sincronizar con SUNAT
        </button>
        @endhasrole
    </div>
</div>

<!-- Alertas Contables -->
<div class="tax-alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-info-circle me-3"></i>
        <div>
            <strong>Recordatorio Tributario:</strong> Los libros electrónicos del mes anterior deben ser enviados a SUNAT antes del día 15.
        </div>
    </div>
</div>

@hasrole('Administrador|Contador')
<!-- Métricas Financieras -->
<div class="financial-summary">
    <div class="summary-card">
        <div class="summary-icon icon-green">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <h6 class="text-muted mb-1">Ingresos del Mes</h6>
        <h4 class="mb-0">S/ {{ number_format($ingresosMes ?? 0, 2) }}</h4>
        <small class="text-success">
            <i class="fas fa-arrow-up me-1"></i>
            +{{ number_format(($crecimientoIngresos ?? 0), 1) }}% vs mes anterior
        </small>
    </div>
    
    <div class="summary-card">
        <div class="summary-icon icon-orange">
            <i class="fas fa-credit-card"></i>
        </div>
        <h6 class="text-muted mb-1">Gastos del Mes</h6>
        <h4 class="mb-0">S/ {{ number_format($gastosMes ?? 0, 2) }}</h4>
        <small class="text-muted">
            <i class="fas fa-minus me-1"></i>
            S/ {{ number_format(($gastosMesAnterior ?? 0), 2) }} mes anterior
        </small>
    </div>
    
    <div class="summary-card">
        <div class="summary-icon icon-blue">
            <i class="fas fa-chart-line"></i>
        </div>
        <h6 class="text-muted mb-1">Utilidad Neta</h6>
        <h4 class="mb-0">S/ {{ number_format(($utilidadNeta ?? 0), 2) }}</h4>
        <small class="{{ ($utilidadNeta ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
            <i class="fas fa-percentage me-1"></i>
            {{ number_format(($margenUtilidad ?? 0), 1) }}% margen
        </small>
    </div>
    
    <div class="summary-card">
        <div class="summary-icon icon-purple">
            <i class="fas fa-balance-scale"></i>
        </div>
        <h6 class="text-muted mb-1">Balance General</h6>
        <h4 class="mb-0">S/ {{ number_format(($balanceGeneral ?? 0), 2) }}</h4>
        <small class="text-muted">
            <i class="fas fa-calendar me-1"></i>
            Actualizado: {{ date('d/m/Y') }}
        </small>
    </div>
</div>
@endhasrole

@yield('contador-content')
@endsection

@section('scripts')
<script>
    // Funcionalidades específicas del contador
    function exportFinancialReport() {
        showLoading();
        
        const reportType = prompt('Tipo de reporte a exportar:\n1. Estado de Resultados\n2. Balance General\n3. Flujo de Caja\n4. Reporte Completo');
        
        if (reportType) {
            window.open(`/reportes/exportar?tipo=${reportType}&formato=excel`, '_blank');
        }
        
        hideLoading();
    }

    function syncWithSunat() {
        Swal.fire({
            title: 'Sincronización con SUNAT',
            text: 'Esto puede tomar varios minutos. ¿Deseas continuar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, sincronizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                
                fetch('/api/sunat/sync', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        Swal.fire(
                            'Éxito',
                            'Sincronización completada exitosamente',
                            'success'
                        );
                    } else {
                        Swal.fire(
                            'Error',
                            'Error en la sincronización: ' + (data.message || 'Error desconocido'),
                            'error'
                        );
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    Swal.fire(
                        'Error',
                        'Error de conexión durante la sincronización',
                        'error'
                    );
                });
            }
        });
    }

    // Gráfico de tendencias financieras
    function initFinancialCharts() {
        // Gráfico de ingresos vs gastos
        const ctx1 = document.getElementById('financialTrendChart');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: @json($mesesLabels ?? []),
                    datasets: [{
                        label: 'Ingresos',
                        data: @json($ingresosData ?? []),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Gastos',
                        data: @json($gastosData ?? []),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // Gráfico de distribución de gastos
        const ctx2 = document.getElementById('expenseDistributionChart');
        if (ctx2) {
            new Chart(ctx2, {  // Aquí debe ser ctx2, no ctx1
                type: 'line',
                data: {
                    labels: @json($mesesLabels ?? []),
                    datasets: [{
                        label: 'Ingresos',
                        data: @json($ingresosData ?? []),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Gastos',
                        data: @json($gastosData ?? []),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    }

    // Auto-refresh financial data
    function refreshFinancialData() {
        fetch('/api/financial/metrics')
            .then(response => response.json())
            .then(data => {
                // Update UI with new data
                console.log('Financial data updated:', data);
            })
            .catch(error => {
                console.error('Error updating financial data:', error);
            });
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        initFinancialCharts();
        
        // Refresh financial data every 5 minutes
        setInterval(refreshFinancialData, 300000);
    });
</script>
@endsection