<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPIs Dashboard - SIFANO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --warning-gradient: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --shadow-elegant: 0 20px 40px rgba(0, 0, 0, 0.1);
            --border-radius: 15px;
        }

        body {
            background: var(--primary-gradient);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .kpi-dashboard {
            background: var(--glass-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-elegant);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }

        .dashboard-header {
            background: var(--dark-gradient);
            color: white;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .dashboard-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .dashboard-header h1 {
            position: relative;
            z-index: 2;
            margin: 0;
            font-weight: 700;
            font-size: 2.5rem;
        }

        .dashboard-header p {
            position: relative;
            z-index: 2;
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .dashboard-content {
            padding: 2rem;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .kpi-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .kpi-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .kpi-card.revenue::before {
            background: var(--success-gradient);
        }

        .kpi-card.sales::before {
            background: var(--info-gradient);
        }

        .kpi-card.customers::before {
            background: var(--warning-gradient);
            color: #2c3e50 !important;
        }

        .kpi-card.inventory::before {
            background: var(--danger-gradient);
        }

        .kpi-card.efficiency::before {
            background: var(--dark-gradient);
        }

        .kpi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .kpi-title {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            font-size: 1.1rem;
        }

        .kpi-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .kpi-icon.primary {
            background: var(--primary-gradient);
        }

        .kpi-icon.success {
            background: var(--success-gradient);
        }

        .kpi-icon.warning {
            background: var(--warning-gradient);
            color: #2c3e50 !important;
        }

        .kpi-icon.danger {
            background: var(--danger-gradient);
        }

        .kpi-icon.info {
            background: var(--info-gradient);
            color: #2c3e50 !important;
        }

        .kpi-value {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .kpi-value.success {
            background: var(--success-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .kpi-value.warning {
            background: var(--warning-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .kpi-value.danger {
            background: var(--danger-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .kpi-subtitle {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .kpi-change {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .kpi-change.positive {
            color: #27ae60;
        }

        .kpi-change.negative {
            color: #e74c3c;
        }

        .kpi-change.neutral {
            color: #95a5a6;
        }

        .kpi-progress {
            margin-top: 1rem;
        }

        .progress-bar {
            height: 8px;
            border-radius: 10px;
            background: #f8f9fa;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 1s ease;
            position: relative;
            overflow: hidden;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .chart-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 1rem;
        }

        .chart-title {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            font-size: 1.3rem;
        }

        .chart-controls {
            display: flex;
            gap: 1rem;
        }

        .control-btn {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            border: 1px solid #dee2e6;
            background: white;
            color: #6c757d;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .control-btn:hover, .control-btn.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
        }

        .chart-wrapper {
            position: relative;
            height: 350px;
        }

        .mini-charts {
            display: grid;
            grid-template-rows: 1fr 1fr;
            gap: 1rem;
        }

        .mini-chart {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .mini-chart-title {
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 1rem 0;
            font-size: 1rem;
        }

        .mini-chart-wrapper {
            position: relative;
            height: 120px;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .metric-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .metric-trend {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .real-time-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success-gradient);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(67, 233, 123, 0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .real-time-indicator::before {
            content: '';
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
            100% { opacity: 1; transform: scale(1); }
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .shape {
            position: absolute;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 70%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 15%;
            left: 80%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 1200px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .kpi-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                padding: 1.5rem;
            }

            .dashboard-content {
                padding: 1.5rem;
            }

            .dashboard-header h1 {
                font-size: 2rem;
            }

            .charts-section {
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Indicador de tiempo real -->
    <div class="real-time-indicator">
        <span>En Vivo</span>
        <small style="font-size: 0.8rem; opacity: 0.8;">Actualizando...</small>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <div class="container-fluid py-4">
        <div class="kpi-dashboard position-relative">
            <!-- Elementos flotantes decorativos -->
            <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
            </div>

            <!-- Header -->
            <div class="dashboard-header">
                <h1><i class="fas fa-chart-line me-3"></i>Dashboard KPIs</h1>
                <p>Indicadores clave de rendimiento en tiempo real - Actualizado: <span id="lastUpdate">Ahora</span></p>
            </div>

            <div class="dashboard-content position-relative">
                <!-- KPI Cards -->
                <div class="kpi-grid">
                    <div class="kpi-card revenue">
                        <div class="kpi-header">
                            <h5 class="kpi-title">Ingresos Totales</h5>
                            <div class="kpi-icon success">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="kpi-value success" id="revenueValue">S/ 125,450</div>
                        <div class="kpi-subtitle">Este mes</div>
                        <div class="kpi-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+12.5% vs mes anterior</span>
                        </div>
                        <div class="kpi-progress">
                            <div class="progress">
                                <div class="progress-fill" style="width: 85%; background: var(--success-gradient);"></div>
                            </div>
                            <small class="text-muted mt-1">Meta: S/ 150,000 (85% completado)</small>
                        </div>
                    </div>

                    <div class="kpi-card sales">
                        <div class="kpi-header">
                            <h5 class="kpi-title">Ventas del Día</h5>
                            <div class="kpi-icon info">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="kpi-value" id="salesValue">1,247</div>
                        <div class="kpi-subtitle">Transacciones</div>
                        <div class="kpi-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+8.2% vs ayer</span>
                        </div>
                        <div class="kpi-progress">
                            <div class="progress">
                                <div class="progress-fill" style="width: 78%; background: var(--info-gradient);"></div>
                            </div>
                            <small class="text-muted mt-1">Meta: 1,600 transacciones (78% completado)</small>
                        </div>
                    </div>

                    <div class="kpi-card customers">
                        <div class="kpi-header">
                            <h5 class="kpi-title">Clientes Atendidos</h5>
                            <div class="kpi-icon warning">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="kpi-value warning" id="customersValue">892</div>
                        <div class="kpi-subtitle">Clientes únicos</div>
                        <div class="kpi-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+15.3% vs mes anterior</span>
                        </div>
                        <div class="kpi-progress">
                            <div class="progress">
                                <div class="progress-fill" style="width: 92%; background: var(--warning-gradient);"></div>
                            </div>
                            <small class="text-muted mt-1">Meta: 970 clientes (92% completado)</small>
                        </div>
                    </div>

                    <div class="kpi-card inventory">
                        <div class="kpi-header">
                            <h5 class="kpi-title">Rotación de Inventario</h5>
                            <div class="kpi-icon danger">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                        <div class="kpi-value danger" id="inventoryValue">6.8</div>
                        <div class="kpi-subtitle">Veces por mes</div>
                        <div class="kpi-change negative">
                            <i class="fas fa-arrow-down"></i>
                            <span>-2.1% vs mes anterior</span>
                        </div>
                        <div class="kpi-progress">
                            <div class="progress">
                                <div class="progress-fill" style="width: 68%; background: var(--danger-gradient);"></div>
                            </div>
                            <small class="text-muted mt-1">Meta: 10.0 rotaciones (68% completado)</small>
                        </div>
                    </div>

                    <div class="kpi-card efficiency">
                        <div class="kpi-header">
                            <h5 class="kpi-title">Eficiencia Operativa</h5>
                            <div class="kpi-icon primary">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                        </div>
                        <div class="kpi-value" id="efficiencyValue">94.2%</div>
                        <div class="kpi-subtitle">Índice de eficiencia</div>
                        <div class="kpi-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+3.7% vs mes anterior</span>
                        </div>
                        <div class="kpi-progress">
                            <div class="progress">
                                <div class="progress-fill" style="width: 94%; background: var(--primary-gradient);"></div>
                            </div>
                            <small class="text-muted mt-1">Meta: 100% (94% completado)</small>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-header">
                            <h5 class="kpi-title">Tickets Promedio</h5>
                            <div class="kpi-icon primary">
                                <i class="fas fa-receipt"></i>
                            </div>
                        </div>
                        <div class="kpi-value" id="ticketValue">S/ 87.50</div>
                        <div class="kpi-subtitle">Por transacción</div>
                        <div class="kpi-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+5.4% vs mes anterior</span>
                        </div>
                        <div class="kpi-progress">
                            <div class="progress">
                                <div class="progress-fill" style="width: 88%; background: var(--primary-gradient);"></div>
                            </div>
                            <small class="text-muted mt-1">Meta: S/ 100 (88% completado)</small>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-section">
                    <!-- Revenue Chart -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <h4 class="chart-title">
                                <i class="fas fa-chart-area me-2"></i>
                                Ingresos por Período
                            </h4>
                            <div class="chart-controls">
                                <button class="control-btn active" data-period="7d">7D</button>
                                <button class="control-btn" data-period="30d">30D</button>
                                <button class="control-btn" data-period="90d">90D</button>
                                <button class="control-btn" data-period="1y">1Y</button>
                            </div>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <!-- Mini Charts -->
                    <div class="mini-charts">
                        <div class="mini-chart">
                            <h5 class="mini-chart-title">
                                <i class="fas fa-pie-chart me-2"></i>
                                Ventas por Categoría
                            </h5>
                            <div class="mini-chart-wrapper">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>

                        <div class="mini-chart">
                            <h5 class="mini-chart-title">
                                <i class="fas fa-clock me-2"></i>
                                Ventas por Hora
                            </h5>
                            <div class="mini-chart-wrapper">
                                <canvas id="hourlyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Metrics -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-value text-success">98.5%</div>
                        <div class="metric-label">Disponibilidad Stock</div>
                        <div class="metric-trend text-success">
                            <i class="fas fa-check"></i>
                            <span>Excelente</span>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-value text-warning">2.3 min</div>
                        <div class="metric-label">Tiempo Promedio de Atención</div>
                        <div class="metric-trend text-success">
                            <i class="fas fa-arrow-down"></i>
                            <span>-15 seg</span>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-value text-info">4.8/5</div>
                        <div class="metric-label">Satisfacción del Cliente</div>
                        <div class="metric-trend text-success">
                            <i class="fas fa-star"></i>
                            <span>+0.2 pts</span>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-value text-primary">S/ 12,340</div>
                        <div class="metric-label">Margen de Ganancia</div>
                        <div class="metric-trend text-success">
                            <i class="fas fa-arrow-up"></i>
                            <span>+18.7%</span>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-value text-danger">156</div>
                        <div class="metric-label">Productos en Stock Bajo</div>
                        <div class="metric-trend text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Revisar</span>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-value text-success">89.2%</div>
                        <div class="metric-label">Exactitud de Inventario</div>
                        <div class="metric-trend text-success">
                            <i class="fas fa-check-circle"></i>
                            <span>Óptimo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar gráficos
            initializeCharts();
            
            // Actualizar datos cada 30 segundos
            setInterval(updateRealTimeData, 30000);
            
            // Controles de período
            $('.control-btn').on('click', function() {
                const period = $(this).data('period');
                
                // Remover clase active de todos los controles
                $('.control-btn').removeClass('active');
                
                // Agregar clase active al control seleccionado
                $(this).addClass('active');
                
                // Simular carga y actualización
                updateChartPeriod(period);
            });

            // Animación de entrada
            setTimeout(() => {
                $('.kpi-card').each(function(index) {
                    setTimeout(() => {
                        $(this).addClass('pulse');
                        setTimeout(() => {
                            $(this).removeClass('pulse');
                        }, 1000);
                    }, index * 100);
                });
            }, 500);
        });

        // Función para inicializar gráficos
        function initializeCharts() {
            // Gráfico de ingresos
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                    datasets: [{
                        label: 'Ingresos (S/)',
                        data: [18200, 19500, 16800, 21300, 22400, 18900, 22100],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#667eea',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'S/ ' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    elements: {
                        point: {
                            hoverRadius: 8
                        }
                    }
                }
            });

            // Gráfico de categorías (dona)
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Medicamentos', 'Suplementos', 'Cuidado Personal', 'Otros'],
                    datasets: [{
                        data: [45, 25, 20, 10],
                        backgroundColor: [
                            '#667eea',
                            '#43e97b',
                            '#ffeaa7',
                            '#fa709a'
                        ],
                        borderWidth: 0,
                        cutout: '60%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de ventas por hora
            const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
            new Chart(hourlyCtx, {
                type: 'bar',
                data: {
                    labels: ['8AM', '10AM', '12PM', '2PM', '4PM', '6PM'],
                    datasets: [{
                        label: 'Ventas',
                        data: [45, 78, 95, 112, 89, 67],
                        backgroundColor: [
                            '#43e97b',
                            '#43e97b',
                            '#ffeaa7',
                            '#ffeaa7',
                            '#43e97b',
                            '#43e97b'
                        ],
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Función para actualizar datos en tiempo real
        function updateRealTimeData() {
            // Simular cambios en KPIs
            const revenueEl = document.getElementById('revenueValue');
            const salesEl = document.getElementById('salesValue');
            const customersEl = document.getElementById('customersValue');
            const inventoryEl = document.getElementById('inventoryValue');
            const efficiencyEl = document.getElementById('efficiencyValue');
            const ticketEl = document.getElementById('ticketValue');

            // Pequeños cambios aleatorios para simular datos en tiempo real
            const changes = {
                revenue: Math.random() * 1000,
                sales: Math.random() * 50,
                customers: Math.random() * 20,
                inventory: (Math.random() - 0.5) * 0.2,
                efficiency: (Math.random() - 0.5) * 2,
                ticket: (Math.random() - 0.5) * 5
            };

            // Actualizar valores con animación
            animateValue(revenueEl, parseFloat(revenueEl.textContent.replace(/[^0-9.]/g, '')), 
                        parseFloat(revenueEl.textContent.replace(/[^0-9.]/g, '')) + changes.revenue, 1000, 'S/ ');

            animateValue(salesEl, parseInt(salesEl.textContent), 
                        parseInt(salesEl.textContent) + changes.sales, 1000, '');

            animateValue(customersEl, parseInt(customersEl.textContent), 
                        parseInt(customersEl.textContent) + changes.customers, 1000, '');

            animateValue(inventoryEl, parseFloat(inventoryEl.textContent), 
                        parseFloat(inventoryEl.textContent) + changes.inventory, 1000, '');

            animateValue(efficiencyEl, parseFloat(efficiencyEl.textContent.replace('%', '')), 
                        parseFloat(efficiencyEl.textContent.replace('%', '')) + changes.efficiency, 1000, '%');

            animateValue(ticketEl, parseFloat(ticketEl.textContent.replace(/[^0-9.]/g, '')), 
                        parseFloat(ticketEl.textContent.replace(/[^0-9.]/g, '')) + changes.ticket, 1000, 'S/ ');

            // Actualizar timestamp
            document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();

            // Mostrar notificación sutil
            showUpdateNotification();
        }

        // Función para actualizar período del gráfico
        function updateChartPeriod(period) {
            $('#loadingOverlay').show();
            
            setTimeout(() => {
                $('#loadingOverlay').hide();
                
                // Simular actualización del gráfico con datos diferentes según el período
                updateRevenueChart(period);
            }, 1000);
        }

        // Función para actualizar gráfico de ingresos
        function updateRevenueChart(period) {
            const chart = Chart.getChart('revenueChart');
            
            const periodData = {
                '7d': {
                    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                    data: [18200, 19500, 16800, 21300, 22400, 18900, 22100]
                },
                '30d': {
                    labels: Array.from({length: 30}, (_, i) => `Día ${i + 1}`),
                    data: Array.from({length: 30}, () => Math.floor(Math.random() * 5000) + 15000)
                },
                '90d': {
                    labels: Array.from({length: 12}, (_, i) => `Semana ${i + 1}`),
                    data: Array.from({length: 12}, () => Math.floor(Math.random() * 10000) + 80000)
                },
                '1y': {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    data: [85000, 92000, 88000, 105000, 112000, 108000, 125000, 118000, 135000, 142000, 138000, 150000]
                }
            };

            const data = periodData[period] || periodData['7d'];
            
            chart.data.labels = data.labels;
            chart.data.datasets[0].data = data.data;
            chart.update();

            Swal.fire({
                icon: 'success',
                title: 'Gráfico actualizado',
                text: `Mostrando datos del período: ${period}`,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }

        // Función para animar valores
        function animateValue(element, start, end, duration, prefix = '') {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const value = progress * (end - start) + start;
                element.textContent = prefix + (prefix.includes('S/') ? Math.floor(value).toLocaleString() : value.toFixed(1)) + (prefix.includes('%') ? '%' : '');
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Función para mostrar notificación de actualización
        function showUpdateNotification() {
            // Crear notificación temporal
            const notification = $('<div class="alert alert-info alert-dismissible fade show position-fixed" style="top: 100px; right: 20px; z-index: 1050; width: 300px;">' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '<i class="fas fa-sync-alt me-2"></i>' +
                '<strong>Actualización:</strong> KPIs actualizados en tiempo real' +
                '</div>');
            
            $('body').append(notification);
            
            // Auto-remover después de 3 segundos
            setTimeout(() => {
                notification.alert('close');
            }, 3000);
        }

        // Efectos de hover mejorados
        document.addEventListener('DOMContentLoaded', function() {
            // Efectos de hover en KPI cards
            const kpiCards = document.querySelectorAll('.kpi-card');
            kpiCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    const progressFill = this.querySelector('.progress-fill');
                    if (progressFill) {
                        progressFill.style.width = '100%';
                    }
                });
                
                card.addEventListener('mouseleave', function() {
                    const progressFill = this.querySelector('.progress-fill');
                    if (progressFill) {
                        const originalWidth = progressFill.getAttribute('data-original-width') || '85%';
                        progressFill.style.width = originalWidth;
                    }
                });
            });

            // Guardar anchos originales de las barras de progreso
            document.querySelectorAll('.progress-fill').forEach(fill => {
                fill.setAttribute('data-original-width', fill.style.width);
            });
        });
    </script>
</body>
</html>