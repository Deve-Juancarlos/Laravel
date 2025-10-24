<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Contador - Sistema Contable Farmacéutico</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem;
            border-radius: 8px;
            margin: 0.25rem;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .stat-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            border-radius: 15px;
            color: white;
        }
        .stat-card.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card.green { background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); }
        .stat-card.orange { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); }
        .stat-card.red { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); }
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .loading-spinner {
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top: 3px solid #fff;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar p-3">
                <div class="text-center mb-4">
                    <i class="fas fa-pills fa-3x text-white mb-3"></i>
                    <h4 class="text-white">Contabilidad</h4>
                </div>
                
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#dashboard" data-bs-toggle="tab">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#planillas" onclick="abrirPlanillas()" data-bs-toggle="tab">
                            <i class="fas fa-file-invoice me-2"></i>Planillas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#facturacion" onclick="abrirFacturacion()" data-bs-toggle="tab">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Facturación
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#reportes" data-bs-toggle="tab">
                            <i class="fas fa-chart-bar me-2"></i>Reportes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#clientes" data-bs-toggle="tab">
                            <i class="fas fa-users me-2"></i>Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#config" data-bs-toggle="tab">
                            <i class="fas fa-cog me-2"></i>Configuración
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Dashboard Contador</h1>
                    <div class="text-muted">
                        <i class="fas fa-calendar me-2"></i>
                        {{ date('d/m/Y H:i') }}
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card blue h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="h2 mb-0" id="totalVentas">{{ number_format($stats['total_ventas'] ?? 0, 2) }}</div>
                                        <div class="text-light">Ventas del Mes</div>
                                    </div>
                                    <i class="fas fa-dollar-sign fa-3x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card green h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="h2 mb-0" id="totalFacturas">{{ $stats['total_facturas'] ?? 0 }}</div>
                                        <div class="text-light">Facturas Emitidas</div>
                                    </div>
                                    <i class="fas fa-file-invoice fa-3x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card orange h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="h2 mb-0" id="totalClientes">{{ $stats['total_clientes'] ?? 0 }}</div>
                                        <div class="text-light">Clientes Activos</div>
                                    </div>
                                    <i class="fas fa-users fa-3x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card red h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="h2 mb-0" id="cuentasPorCobrar">{{ number_format($stats['cuentas_por_cobrar'] ?? 0, 2) }}</div>
                                        <div class="text-light">Cuentas por Cobrar</div>
                                    </div>
                                    <i class="fas fa-exclamation-triangle fa-3x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Ventas Mensuales
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="ventasChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>Productos Más Vendidos
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height: 300px;">
                                    <canvas id="productosChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bolt me-2"></i>Acciones Rápidas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <button class="btn btn-primary w-100" onclick="abrirFacturacion()">
                                            <i class="fas fa-file-invoice-dollar me-2"></i>
                                            Nueva Factura
                                        </button>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <button class="btn btn-success w-100" onclick="abrirPlanillas()">
                                            <i class="fas fa-file-invoice me-2"></i>
                                            Nueva Planilla
                                        </button>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <button class="btn btn-info w-100" onclick="abrirLibroMayor()">
                                            <i class="fas fa-book me-2"></i>
                                            Ver Libro Mayor
                                        </button>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <button class="btn btn-warning w-100" onclick="generarBalance()">
                                            <i class="fas fa-balance-scale me-2"></i>
                                            Balance General
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list me-2"></i>Actividad Reciente
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="actividadReciente">
                                    @if(isset($actividades) && count($actividades) > 0)
                                        @foreach($actividades as $actividad)
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="me-3">
                                                <i class="fas fa-circle text-primary" style="font-size: 8px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="small">{{ $actividad['descripcion'] }}</div>
                                                <div class="text-muted small">{{ $actividad['fecha'] }}</div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                                            <div>No hay actividad reciente</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Alertas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="alertas">
                                    @if(isset($alertas) && count($alertas) > 0)
                                        @foreach($alertas as $alerta)
                                        <div class="alert alert-{{ $alerta['tipo'] }} py-2 mb-2">
                                            <i class="fas fa-{{ $alerta['icono'] }} me-2"></i>
                                            {{ $alerta['mensaje'] }}
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                            <div>No hay alertas</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modals -->
    
    <!-- Modal Libro Mayor -->
    <div class="modal fade" id="modalLibroMayor" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-book me-2"></i>Libro Mayor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="libroMayorContent">
                        <div class="text-center py-4">
                            <div class="loading-spinner mx-auto mb-3"></div>
                            <div>Cargando libro mayor...</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="exportarLibroMayor()">
                        <i class="fas fa-download me-2"></i>Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let ventasChart, productosChart;
        let isLoading = false;

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            loadDashboardData();
            updateStats();
        });

        // Chart initialization
        function initializeCharts() {
            // Ventas Chart
            const ventasCtx = document.getElementById('ventasChart').getContext('2d');
            ventasChart = new Chart(ventasCtx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Ventas 2025',
                        data: [12000, 19000, 15000, 25000, 22000, 30000],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Productos Chart
            const productosCtx = document.getElementById('productosChart').getContext('2d');
            productosChart = new Chart(productosCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Producto A', 'Producto B', 'Producto C', 'Otros'],
                    datasets: [{
                        data: [30, 25, 20, 25],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 205, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Load dashboard data
        async function loadDashboardData() {
            try {
                isLoading = true;
                const response = await fetch('/dashboard/contador/data');
                const data = await response.json();
                
                if (data.success) {
                    updateDashboardData(data.data);
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                showAlert('Error al cargar datos del dashboard', 'error');
            } finally {
                isLoading = false;
            }
        }

        // Update dashboard data
        function updateDashboardData(data) {
            if (data.stats) {
                Object.keys(data.stats).forEach(key => {
                    const element = document.getElementById(key);
                    if (element) {
                        element.textContent = formatNumber(data.stats[key]);
                    }
                });
            }
        }

        // Update statistics
        function updateStats() {
            // Realizar llamadas AJAX para actualizar estadísticas
            fetch('/dashboard/contador/stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStatistics(data.data);
                    }
                })
                .catch(error => {
                    console.error('Error updating stats:', error);
                });
        }

        // Update statistics in UI
        function updateStatistics(stats) {
            if (stats.total_ventas !== undefined) {
                document.getElementById('totalVentas').textContent = formatNumber(stats.total_ventas);
            }
            if (stats.total_facturas !== undefined) {
                document.getElementById('totalFacturas').textContent = stats.total_facturas;
            }
            if (stats.total_clientes !== undefined) {
                document.getElementById('totalClientes').textContent = stats.total_clientes;
            }
            if (stats.cuentas_por_cobrar !== undefined) {
                document.getElementById('cuentasPorCobrar').textContent = formatNumber(stats.cuentas_por_cobrar);
            }
        }

        // Navigation functions
        function abrirPlanillas() {
            window.location.href = '/contabilidad/planillas-cobranza';
        }

        function abrirFacturacion() {
            window.location.href = '/contabilidad/facturacion/nueva';
        }

        function abrirLibroMayor() {
            new bootstrap.Modal(document.getElementById('modalLibroMayor')).show();
            loadLibroMayor();
        }

        function generarBalance() {
            window.location.href = '/contabilidad/reportes/balance-general';
        }

        function analizarCartera() {
            window.location.href = '/contabilidad/analisis/cartera';
        }

        function controlFarmaceutico() {
            window.location.href = '/contabilidad/analisis/control-farmaceutico';
        }

        function verCuenta(cuenta) {
            window.location.href = '/contabilidad/cuentas/' + cuenta;
        }

        // Load Libro Mayor
        function loadLibroMayor() {
            const content = document.getElementById('libroMayorContent');
            content.innerHTML = `
                <div class="text-center py-4">
                    <div class="loading-spinner mx-auto mb-3"></div>
                    <div>Cargando libro mayor...</div>
                </div>
            `;

            fetch('/dashboard/contador/libro-mayor')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        content.innerHTML = generateLibroMayorTable(data.data);
                    } else {
                        content.innerHTML = '<div class="alert alert-danger">Error al cargar libro mayor</div>';
                    }
                })
                .catch(error => {
                    content.innerHTML = '<div class="alert alert-danger">Error de conexión</div>';
                });
        }

        // Generate Libro Mayor Table
        function generateLibroMayorTable(data) {
            let html = '<div class="table-responsive">';
            html += '<table class="table table-striped">';
            html += '<thead><tr><th>Cuenta</th><th>Descripción</th><th>Debe</th><th>Haber</th><th>Saldo</th></tr></thead>';
            html += '<tbody>';
            
            data.forEach(cuenta => {
                html += `<tr>
                    <td>${cuenta.codigo}</td>
                    <td>${cuenta.descripcion}</td>
                    <td class="text-end">${formatNumber(cuenta.debe)}</td>
                    <td class="text-end">${formatNumber(cuenta.haber)}</td>
                    <td class="text-end">${formatNumber(cuenta.saldo)}</td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            return html;
        }

        // Export functions
        function exportarLibroMayor() {
            window.open('/dashboard/contador/libro-mayor/exportar', '_blank');
        }

        // Utility functions
        function formatNumber(number) {
            if (typeof number === 'number') {
                return number.toLocaleString('es-PE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
            return number;
        }

        function showAlert(message, type = 'info') {
            // Implementar sistema de alertas
            console.log(`${type.toUpperCase()}: ${message}`);
        }

        // Auto-refresh dashboard data every 5 minutes
        setInterval(() => {
            if (!isLoading) {
                updateStats();
            }
        }, 300000);
    </script>
</body>
</html>