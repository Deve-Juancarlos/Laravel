    <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas de Temperatura - SIFANO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --warning-gradient: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --shadow-elegant: 0 20px 40px rgba(0, 0, 0, 0.1);
            --border-radius: 15px;
        }

        body {
            background: var(--primary-gradient);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .temperature-dashboard {
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
        }

        .dashboard-header p {
            position: relative;
            z-index: 2;
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }

        .dashboard-content {
            padding: 2rem;
        }

        .temperature-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .temp-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border-left: 5px solid transparent;
        }

        .temp-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .temp-card.critical {
            border-left-color: #e74c3c;
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.05) 0%, rgba(231, 76, 60, 0.02) 100%);
        }

        .temp-card.warning {
            border-left-color: #f39c12;
            background: linear-gradient(135deg, rgba(243, 156, 18, 0.05) 0%, rgba(243, 156, 18, 0.02) 100%);
        }

        .temp-card.normal {
            border-left-color: #27ae60;
            background: linear-gradient(135deg, rgba(39, 174, 96, 0.05) 0%, rgba(39, 174, 96, 0.02) 100%);
        }

        .temp-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .temp-card-title {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .temp-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .temp-icon.critical {
            background: var(--danger-gradient);
        }

        .temp-icon.warning {
            background: var(--warning-gradient);
            color: #2c3e50 !important;
        }

        .temp-icon.normal {
            background: var(--success-gradient);
        }

        .temperature-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 1rem 0;
            position: relative;
        }

        .temperature-value.critical {
            color: #e74c3c;
        }

        .temperature-value.warning {
            color: #f39c12;
        }

        .temperature-value.normal {
            color: #27ae60;
        }

        .temperature-trend {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .trend-up {
            color: #e74c3c;
        }

        .trend-down {
            color: #3498db;
        }

        .trend-stable {
            color: #95a5a6;
        }

        .alerts-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .alerts-header {
            display: flex;
            align-items: center;
            justify-content: between;
            margin-bottom: 2rem;
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 1rem;
        }

        .alerts-title {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            font-size: 1.5rem;
        }

        .alert-filters {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            border: 2px solid #e9ecef;
            background: white;
            color: #6c757d;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .filter-btn:hover, .filter-btn.active {
            border-color: #667eea;
            background: var(--primary-gradient);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .alert-item {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 5px solid;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .alert-item:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .alert-item.critical {
            border-left-color: #e74c3c;
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.05) 0%, white 100%);
        }

        .alert-item.warning {
            border-left-color: #f39c12;
            background: linear-gradient(135deg, rgba(243, 156, 18, 0.05) 0%, white 100%);
        }

        .alert-item.info {
            border-left-color: #3498db;
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.05) 0%, white 100%);
        }

        .alert-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .alert-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .alert-icon.critical {
            background: var(--danger-gradient);
        }

        .alert-icon.warning {
            background: var(--warning-gradient);
            color: #2c3e50 !important;
        }

        .alert-icon.info {
            background: var(--info-gradient);
            color: #2c3e50 !important;
        }

        .alert-title {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .alert-time {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .alert-content {
            color: #495057;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .alert-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-alert {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            border: none;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-acknowledge {
            background: var(--success-gradient);
            color: white;
        }

        .btn-acknowledge:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 233, 123, 0.3);
        }

        .btn-details {
            background: var(--info-gradient);
            color: #2c3e50;
            font-weight: 600;
        }

        .btn-details:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(168, 237, 234, 0.3);
        }

        .btn-escalate {
            background: var(--danger-gradient);
            color: white;
        }

        .btn-escalate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(250, 112, 154, 0.3);
        }

        .real-time-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success-gradient);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(67, 233, 123, 0.3);
        }

        .real-time-indicator::before {
            content: '';
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .temperature-chart {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .chart-title {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
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
        }

        .control-btn:hover, .control-btn.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
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

        .notification-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 1001;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .notification-toast.show {
            transform: translateX(0);
        }

        @media (max-width: 768px) {
            .temperature-cards {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                padding: 1.5rem;
            }
            
            .dashboard-content {
                padding: 1.5rem;
            }
            
            .alert-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Indicador de tiempo real -->
    <div class="real-time-indicator">
        En Vivo
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Toast de notificación -->
    <div class="notification-toast" id="notificationToast">
        <div class="d-flex align-items-center">
            <i class="fas fa-thermometer-half text-warning me-2"></i>
            <div>
                <strong>Temperatura Crítica</strong><br>
                <small>Refrigerador Principal - 8°C</small>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="temperature-dashboard">
            <!-- Header -->
            <div class="dashboard-header">
                <h1><i class="fas fa-thermometer-half me-3"></i>Alertas de Temperatura</h1>
                <p>Sistema de monitoreo y control de temperatura para productos farmacéuticos</p>
            </div>

            <div class="dashboard-content">
                <!-- Temperature Cards -->
                <div class="temperature-cards">
                    <div class="temp-card critical">
                        <div class="temp-card-header">
                            <h5 class="temp-card-title">Refrigerador Principal</h5>
                            <div class="temp-icon critical">
                                <i class="fas fa-snowflake"></i>
                            </div>
                        </div>
                        <div class="temperature-value critical">
                            8.2°C
                        </div>
                        <div class="temperature-trend trend-up">
                            <i class="fas fa-arrow-up"></i>
                            <span>+1.2°C en los últimos 30 min</span>
                        </div>
                        <small class="text-muted">Rango óptimo: 2-8°C | Estado: <strong class="text-danger">CRÍTICO</strong></small>
                    </div>

                    <div class="temp-card warning">
                        <div class="temp-card-header">
                            <h5 class="temp-card-title">Refrigerador Secundario</h5>
                            <div class="temp-icon warning">
                                <i class="fas fa-thermometer-half"></i>
                            </div>
                        </div>
                        <div class="temperature-value warning">
                            9.5°C
                        </div>
                        <div class="temperature-trend trend-stable">
                            <i class="fas fa-minus"></i>
                            <span>Estable en los últimos 15 min</span>
                        </div>
                        <small class="text-muted">Rango óptimo: 2-8°C | Estado: <strong class="text-warning">ADVERTENCIA</strong></small>
                    </div>

                    <div class="temp-card normal">
                        <div class="temp-card-header">
                            <h5 class="temp-card-title">Congelador</h5>
                            <div class="temp-icon normal">
                                <i class="fas fa-snowflake"></i>
                            </div>
                        </div>
                        <div class="temperature-value normal">
                            -18.1°C
                        </div>
                        <div class="temperature-trend trend-stable">
                            <i class="fas fa-check"></i>
                            <span>Condiciones normales</span>
                        </div>
                        <small class="text-muted">Rango óptimo: -25°C a -15°C | Estado: <strong class="text-success">NORMAL</strong></small>
                    </div>

                    <div class="temp-card normal">
                        <div class="temp-card-header">
                            <h5 class="temp-card-title">Área de Almacenamiento</h5>
                            <div class="temp-icon normal">
                                <i class="fas fa-home"></i>
                            </div>
                        </div>
                        <div class="temperature-value normal">
                            22.8°C
                        </div>
                        <div class="temperature-trend trend-down">
                            <i class="fas fa-arrow-down"></i>
                            <span>-0.5°C en la última hora</span>
                        </div>
                        <small class="text-muted">Rango óptimo: 15-25°C | Estado: <strong class="text-success">NORMAL</strong></small>
                    </div>
                </div>

                <!-- Temperature Chart -->
                <div class="temperature-chart">
                    <div class="chart-header">
                        <h4 class="chart-title">
                            <i class="fas fa-chart-line me-2"></i>
                            Historial de Temperaturas (24h)
                        </h4>
                        <div class="chart-controls">
                            <button class="control-btn active" data-period="1h">1H</button>
                            <button class="control-btn" data-period="6h">6H</button>
                            <button class="control-btn" data-period="24h">24H</button>
                            <button class="control-btn" data-period="7d">7D</button>
                        </div>
                    </div>
                    
                    <!-- Chart placeholder -->
                    <div style="height: 300px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                        <div class="text-center text-muted">
                            <i class="fas fa-chart-line" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>Gráfico de temperatura en tiempo real</p>
                            <small>Los datos se actualizarán automáticamente cada 5 minutos</small>
                        </div>
                        <!-- Animated background -->
                        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(45deg, rgba(102, 126, 234, 0.05) 25%, transparent 25%), linear-gradient(-45deg, rgba(102, 126, 234, 0.05) 25%, transparent 25%); background-size: 20px 20px; animation: move 20s linear infinite;"></div>
                    </div>
                </div>

                <!-- Alerts Section -->
                <div class="alerts-section">
                    <div class="alerts-header">
                        <h3 class="alerts-title">
                            <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                            Alertas Activas
                        </h3>
                        <div class="alert-filters">
                            <button class="filter-btn active" data-filter="all">Todas</button>
                            <button class="filter-btn" data-filter="critical">Críticas</button>
                            <button class="filter-btn" data-filter="warning">Advertencias</button>
                            <button class="filter-btn" data-filter="info">Informativas</button>
                        </div>
                    </div>

                    <!-- Alert Items -->
                    <div class="alert-item critical">
                        <div class="alert-header">
                            <div class="alert-info">
                                <div class="alert-icon critical">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <h5 class="alert-title">Temperatura Crítica - Refrigerador Principal</h5>
                                    <div class="alert-time">Hace 5 minutos</div>
                                </div>
                            </div>
                            <span class="badge bg-danger">CRÍTICO</span>
                        </div>
                        <div class="alert-content">
                            La temperatura del refrigerador principal ha alcanzado 8.2°C, superando el límite superior del rango óptimo (2-8°C). 
                            Esto puede comprometer la integridad de productos sensibles como vacunas, insulina y otros medicamentos termosensibles.
                        </div>
                        <div class="alert-actions">
                            <button class="btn-alert btn-acknowledge" onclick="acknowledgeAlert(1)">
                                <i class="fas fa-check me-1"></i>Reconocer
                            </button>
                            <button class="btn-alert btn-details" onclick="viewDetails(1)">
                                <i class="fas fa-eye me-1"></i>Ver Detalles
                            </button>
                            <button class="btn-alert btn-escalate" onclick="escalateAlert(1)">
                                <i class="fas fa-phone me-1"></i>Escalar
                            </button>
                        </div>
                    </div>

                    <div class="alert-item warning">
                        <div class="alert-header">
                            <div class="alert-info">
                                <div class="alert-icon warning">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div>
                                    <h5 class="alert-title">Temperatura Elevada - Refrigerador Secundario</h5>
                                    <div class="alert-time">Hace 12 minutos</div>
                                </div>
                            </div>
                            <span class="badge bg-warning">ADVERTENCIA</span>
                        </div>
                        <div class="alert-content">
                            El refrigerador secundario registra 9.5°C, ligeramente por encima del rango óptimo. 
                            Se recomienda verificar el estado del sistema de enfriamiento y revisar la puerta.
                        </div>
                        <div class="alert-actions">
                            <button class="btn-alert btn-acknowledge" onclick="acknowledgeAlert(2)">
                                <i class="fas fa-check me-1"></i>Reconocer
                            </button>
                            <button class="btn-alert btn-details" onclick="viewDetails(2)">
                                <i class="fas fa-eye me-1"></i>Ver Detalles
                            </button>
                            <button class="btn-alert btn-escalate" onclick="escalateAlert(2)">
                                <i class="fas fa-phone me-1"></i>Escalar
                            </button>
                        </div>
                    </div>

                    <div class="alert-item info">
                        <div class="alert-header">
                            <div class="alert-info">
                                <div class="alert-icon info">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div>
                                    <h5 class="alert-title">Mantenimiento Programado</h5>
                                    <div class="alert-time">Hace 1 hora</div>
                                </div>
                            </div>
                            <span class="badge bg-info">INFORMATIVO</span>
                        </div>
                        <div class="alert-content">
                            El mantenimiento preventivo del refrigerador principal está programado para mañana a las 8:00 AM. 
                            Se realizará calibración de sensores y limpieza del sistema de ventilación.
                        </div>
                        <div class="alert-actions">
                            <button class="btn-alert btn-acknowledge" onclick="acknowledgeAlert(3)">
                                <i class="fas fa-check me-1"></i>Reconocer
                            </button>
                            <button class="btn-alert btn-details" onclick="viewDetails(3)">
                                <i class="fas fa-eye me-1"></i>Ver Detalles
                            </button>
                        </div>
                    </div>

                    <div class="alert-item warning">
                        <div class="alert-header">
                            <div class="alert-info">
                                <div class="alert-icon warning">
                                    <i class="fas fa-battery-quarter"></i>
                                </div>
                                <div>
                                    <h5 class="alert-title">Batería Baja - Sensor Extremo</h5>
                                    <div class="alert-time">Hace 2 horas</div>
                                </div>
                            </div>
                            <span class="badge bg-warning">ADVERTENCIA</span>
                        </div>
                        <div class="alert-content">
                            El sensor de temperatura del área de almacenamiento reporta batería baja (15%). 
                            Es necesario reemplazar la batería para garantizar el monitoreo continuo.
                        </div>
                        <div class="alert-actions">
                            <button class="btn-alert btn-acknowledge" onclick="acknowledgeAlert(4)">
                                <i class="fas fa-check me-1"></i>Reconocer
                            </button>
                            <button class="btn-alert btn-details" onclick="viewDetails(4)">
                                <i class="fas fa-eye me-1"></i>Ver Detalles
                            </button>
                            <button class="btn-alert btn-escalate" onclick="escalateAlert(4)">
                                <i class="fas fa-tools me-1"></i>Mantenimiento
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.js"></script>

    <style>
        @keyframes move {
            0% { background-position: 0 0; }
            100% { background-position: 20px 20px; }
        }
    </style>

    <script>
        $(document).ready(function() {
            // Simular actualización en tiempo real
            setInterval(updateRealTimeData, 30000); // Cada 30 segundos
            
            // Filtros de alertas
            $('.filter-btn').on('click', function() {
                const filter = $(this).data('filter');
                
                // Remover clase active de todos los filtros
                $('.filter-btn').removeClass('active');
                
                // Agregar clase active al filtro seleccionado
                $(this).addClass('active');
                
                // Aplicar filtro
                filterAlerts(filter);
            });

            // Controles de gráfico
            $('.control-btn').on('click', function() {
                const period = $(this).data('period');
                
                // Remover clase active de todos los controles
                $('.control-btn').removeClass('active');
                
                // Agregar clase active al control seleccionado
                $(this).addClass('active');
                
                // Simular cambio de período
                updateChartPeriod(period);
            });

            // Animación de entrada
            setTimeout(() => {
                $('.temp-card').each(function(index) {
                    setTimeout(() => {
                        $(this).addClass('pulse');
                        setTimeout(() => {
                            $(this).removeClass('pulse');
                        }, 1000);
                    }, index * 200);
                });
            }, 500);
        });

        // Función para actualizar datos en tiempo real
        function updateRealTimeData() {
            // Simular cambios aleatorios en temperatura
            const cards = $('.temp-card');
            cards.each(function() {
                const tempValue = $(this).find('.temperature-value');
                const currentTemp = parseFloat(tempValue.text());
                
                // Pequeña variación aleatoria
                const change = (Math.random() - 0.5) * 0.4; // ±0.2°C
                const newTemp = Math.max(currentTemp + change, -25);
                
                // Actualizar temperatura
                tempValue.text(newTemp.toFixed(1) + '°C');
                
                // Actualizar tendencia
                const trend = $(this).find('.temperature-trend span');
                if (change > 0.1) {
                    trend.html('<i class="fas fa-arrow-up"></i><span>+' + Math.abs(change).toFixed(1) + '°C en los últimos 30 min</span>');
                } else if (change < -0.1) {
                    trend.html('<i class="fas fa-arrow-down"></i><span>-' + Math.abs(change).toFixed(1) + '°C en los últimos 30 min</span>');
                }
            });
        }

        // Función para filtrar alertas
        function filterAlerts(filter) {
            const alerts = $('.alert-item');
            
            alerts.hide();
            
            if (filter === 'all') {
                alerts.fadeIn();
            } else {
                alerts.filter('.' + filter).fadeIn();
            }
            
            // Mostrar notificación
            Swal.fire({
                icon: 'info',
                title: 'Filtro aplicado',
                text: `Mostrando alertas: ${filter === 'all' ? 'todas' : filter}`,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }

        // Función para actualizar período del gráfico
        function updateChartPeriod(period) {
            $('#loadingOverlay').show();
            
            setTimeout(() => {
                $('#loadingOverlay').hide();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Gráfico actualizado',
                    text: `Mostrando datos del período: ${period}`,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }, 1000);
        }

        // Función para reconocer alerta
        function acknowledgeAlert(alertId) {
            const alertItem = $('.alert-item').eq(alertId - 1);
            
            // Marcar como reconocida
            alertItem.addClass('acknowledged');
            alertItem.find('.btn-acknowledge').prop('disabled', true).html('<i class="fas fa-check me-1"></i>Reconocida');
            
            Swal.fire({
                icon: 'success',
                title: 'Alerta reconocida',
                text: 'La alerta ha sido marcada como reconocida',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });

            // Ocultar después de 2 segundos
            setTimeout(() => {
                alertItem.fadeOut();
            }, 2000);
        }

        // Función para ver detalles
        function viewDetails(alertId) {
            const alertTitles = [
                'Temperatura Crítica - Refrigerador Principal',
                'Temperatura Elevada - Refrigerador Secundario',
                'Mantenimiento Programado',
                'Batería Baja - Sensor Extremo'
            ];
            
            Swal.fire({
                icon: 'info',
                title: 'Detalles de la Alerta',
                html: `<strong>${alertTitles[alertId - 1]}</strong><br><br>
                       Timestamp: ${new Date().toLocaleString()}<br>
                       Sensor ID: TEMP-00${alertId}<br>
                       Ubicación: Almacén Principal<br>
                       Severidad: ${alertId === 1 ? 'Alta' : alertId === 2 ? 'Media' : 'Baja'}<br><br>
                       Acciones recomendadas:<br>
                       • Verificar sistema de enfriamiento<br>
                       • Revisar puerta y sellos<br>
                       • Contactar técnico si persiste`,
                showConfirmButton: true,
                confirmButtonText: 'Cerrar',
                width: '600px'
            });
        }

        // Función para escalar alerta
        function escalateAlert(alertId) {
            Swal.fire({
                icon: 'warning',
                title: 'Escalar Alerta',
                text: '¿Desea escalar esta alerta al supervisor de turno?',
                showCancelButton: true,
                confirmButtonText: 'Sí, escalar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e74c3c'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Alerta escalada',
                        text: 'Se ha notificado al supervisor de turno',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            });
        }

        // Función para mostrar notificación toast
        function showNotification(message) {
            $('#notificationToast').addClass('show');
            
            setTimeout(() => {
                $('#notificationToast').removeClass('show');
            }, 5000);
        }

        // Simular notificaciones críticas
        setInterval(() => {
            if (Math.random() > 0.8) { // 20% de probabilidad
                const messages = [
                    { icon: 'fa-exclamation-triangle', text: 'Temperatura crítica en Refrigerador Principal' },
                    { icon: 'fa-battery-quarter', text: 'Batería baja en sensor de temperatura' },
                    { icon: 'fa-tools', text: 'Mantenimiento requerido en sistema de enfriamiento' }
                ];
                
                const randomMessage = messages[Math.floor(Math.random() * messages.length)];
                
                $('#notificationToast').html(`
                    <div class="d-flex align-items-center">
                        <i class="fas ${randomMessage.icon} text-warning me-2"></i>
                        <div>
                            <strong>Alerta del Sistema</strong><br>
                            <small>${randomMessage.text}</small>
                        </div>
                    </div>
                `);
                
                showNotification(randomMessage.text);
            }
        }, 45000); // Cada 45 segundos

        // Efectos de animación adicionales
        document.addEventListener('DOMContentLoaded', function() {
            // Efectos de hover en tarjetas
            const tempCards = document.querySelectorAll('.temp-card');
            tempCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    if (this.classList.contains('critical')) {
                        this.style.borderLeftWidth = '8px';
                    }
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.borderLeftWidth = '5px';
                });
            });

            // Animación de contadores
            const statNumbers = document.querySelectorAll('.temperature-value');
            statNumbers.forEach(element => {
                const finalValue = parseFloat(element.textContent);
                element.textContent = '0°C';
                
                setTimeout(() => {
                    animateValue(element, 0, finalValue, 2000, '°C');
                }, 500);
            });
        });

        // Función para animar valores
        function animateValue(element, start, end, duration, suffix) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                element.textContent = (progress * (end - start) + start).toFixed(1) + suffix;
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }
    </script>
</body>
</html>