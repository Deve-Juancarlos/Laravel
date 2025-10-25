<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - SIFANO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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

        .notifications-container {
            background: var(--glass-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-elegant);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            max-width: 1200px;
            margin: 2rem auto;
        }

        .notifications-header {
            background: var(--dark-gradient);
            color: white;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .notifications-header::before {
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

        .notifications-header::after {
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

        .notifications-header h1 {
            position: relative;
            z-index: 2;
            margin: 0;
            font-weight: 700;
            font-size: 2.5rem;
        }

        .notifications-header p {
            position: relative;
            z-index: 2;
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .notifications-content {
            padding: 2rem;
        }

        .notifications-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .stat-card.unread::before {
            background: var(--danger-gradient);
        }

        .stat-card.today::before {
            background: var(--warning-gradient);
        }

        .stat-card.urgent::before {
            background: var(--danger-gradient);
        }

        .stat-card.all::before {
            background: var(--info-gradient);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card.unread .stat-number {
            color: #e74c3c;
        }

        .stat-card.today .stat-number {
            color: #f39c12;
        }

        .stat-card.urgent .stat-number {
            color: #e74c3c;
        }

        .stat-card.all .stat-number {
            color: #3498db;
        }

        .stat-label {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 500;
        }

        .notifications-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            background: white;
            border-radius: 50px;
            padding: 0.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .filter-tab {
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            border: none;
            background: transparent;
            color: #6c757d;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .filter-tab.active {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .filter-tab:hover:not(.active) {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn-action {
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            border: 2px solid;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-custom {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-outline-custom {
            background: white;
            border-color: #dee2e6;
            color: #6c757d;
        }

        .btn-outline-custom:hover {
            background: var(--warning-gradient);
            border-color: transparent;
            color: #2c3e50;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 234, 167, 0.3);
        }

        .notifications-list {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .notification-item {
            border-bottom: 1px solid #f8f9fa;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .notification-item:hover {
            background: rgba(102, 126, 234, 0.02);
            transform: translateX(5px);
        }

        .notification-item.unread {
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.02) 0%, rgba(231, 76, 60, 0.01) 100%);
            border-left: 4px solid #e74c3c;
        }

        .notification-item.unread::before {
            content: '';
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: #e74c3c;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; transform: translateY(-50%) scale(1); }
            50% { opacity: 0.5; transform: translateY(-50%) scale(1.2); }
            100% { opacity: 1; transform: translateY(-50%) scale(1); }
        }

        .notification-content {
            padding: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .notification-icon.success {
            background: var(--success-gradient);
        }

        .notification-icon.warning {
            background: var(--warning-gradient);
            color: #2c3e50 !important;
        }

        .notification-icon.danger {
            background: var(--danger-gradient);
        }

        .notification-icon.info {
            background: var(--info-gradient);
            color: #2c3e50 !important;
        }

        .notification-icon.primary {
            background: var(--primary-gradient);
        }

        .notification-body {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
        }

        .notification-message {
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.9rem;
            color: #95a5a6;
        }

        .notification-time {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .notification-priority {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-high {
            background: var(--danger-gradient);
            color: white;
        }

        .priority-medium {
            background: var(--warning-gradient);
            color: #2c3e50;
        }

        .priority-low {
            background: var(--info-gradient);
            color: #2c3e50;
        }

        .notification-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .btn-small {
            padding: 0.4rem 1rem;
            border-radius: 15px;
            border: none;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-mark-read {
            background: var(--success-gradient);
            color: white;
        }

        .btn-mark-read:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 233, 123, 0.3);
        }

        .btn-delete {
            background: var(--danger-gradient);
            color: white;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(250, 112, 154, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h3 {
            margin-bottom: 0.5rem;
            color: #495057;
        }

        .load-more {
            text-align: center;
            padding: 2rem;
            background: white;
        }

        .btn-load-more {
            padding: 1rem 2rem;
            border-radius: 50px;
            border: 2px solid #667eea;
            background: transparent;
            color: #667eea;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-load-more:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
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

        .notification-sound {
            display: none;
        }

        @media (max-width: 768px) {
            .notifications-header {
                padding: 1.5rem;
            }

            .notifications-content {
                padding: 1.5rem;
            }

            .notifications-stats {
                grid-template-columns: 1fr;
            }

            .notifications-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-tabs {
                justify-content: center;
            }

            .action-buttons {
                justify-content: center;
            }

            .notification-content {
                flex-direction: column;
                text-align: center;
            }

            .notification-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Indicador de tiempo real -->
    <div class="real-time-indicator">
        <span>En Vivo</span>
        <small style="font-size: 0.8rem; opacity: 0.8;">Escuchando...</small>
    </div>

    <div class="container-fluid py-4">
        <div class="notifications-container position-relative">
            <!-- Elementos flotantes decorativos -->
            <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
            </div>

            <!-- Header -->
            <div class="notifications-header">
                <h1><i class="fas fa-bell me-3"></i>Notificaciones</h1>
                <p>Sistema de alertas y notificaciones en tiempo real para SIFANO</p>
            </div>

            <div class="notifications-content position-relative">
                <!-- Estadísticas -->
                <div class="notifications-stats">
                    <div class="stat-card unread">
                        <div class="stat-number">7</div>
                        <div class="stat-label">No Leídas</div>
                    </div>
                    <div class="stat-card today">
                        <div class="stat-number">24</div>
                        <div class="stat-label">Hoy</div>
                    </div>
                    <div class="stat-card urgent">
                        <div class="stat-number">3</div>
                        <div class="stat-label">Urgentes</div>
                    </div>
                    <div class="stat-card all">
                        <div class="stat-number">156</div>
                        <div class="stat-label">Total</div>
                    </div>
                </div>

                <!-- Controles -->
                <div class="notifications-controls">
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-filter="all">Todas</button>
                        <button class="filter-tab" data-filter="unread">No Leídas</button>
                        <button class="filter-tab" data-filter="urgent">Urgentes</button>
                        <button class="filter-tab" data-filter="sales">Ventas</button>
                        <button class="filter-tab" data-filter="inventory">Inventario</button>
                    </div>

                    <div class="action-buttons">
                        <button class="btn-action btn-outline-custom" onclick="markAllAsRead()">
                            <i class="fas fa-check-double"></i>Marcar Todas
                        </button>
                        <button class="btn-action btn-primary-custom" onclick="refreshNotifications()">
                            <i class="fas fa-sync-alt"></i>Actualizar
                        </button>
                    </div>
                </div>

                <!-- Lista de notificaciones -->
                <div class="notifications-list" id="notificationsList">
                    <!-- Notificación 1 - Crítica -->
                    <div class="notification-item unread" data-category="inventory" data-priority="high">
                        <div class="notification-content">
                            <div class="notification-icon danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="notification-body">
                                <h5 class="notification-title">Stock Crítico - Paracetamol 500mg</h5>
                                <div class="notification-message">
                                    El producto Paracetamol 500mg tiene solo 5 unidades restantes en stock. 
                                    Se recomienda realizar un pedido urgente para evitar desabastecimiento. 
                                    Último proveedor contacted: 2 horas ago.
                                </div>
                                <div class="notification-meta">
                                    <div class="notification-time">
                                        <i class="fas fa-clock"></i>
                                        <span>Hace 15 minutos</span>
                                    </div>
                                    <span class="notification-priority priority-high">Alta</span>
                                    <span class="notification-category">
                                        <i class="fas fa-tag"></i> Inventario
                                    </span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn-small btn-mark-read" onclick="markAsRead(this)">
                                        <i class="fas fa-check"></i> Marcar Leída
                                    </button>
                                    <button class="btn-small btn-delete" onclick="deleteNotification(this)">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notificación 2 - Venta -->
                    <div class="notification-item unread" data-category="sales" data-priority="medium">
                        <div class="notification-content">
                            <div class="notification-icon success">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="notification-body">
                                <h5 class="notification-title">Nueva Venta Registrada</h5>
                                <div class="notification-message">
                                    Venta exitosa por S/ 450.80 registrada. Cliente: María García López. 
                                    Productos incluidos: Omeprazol 20mg, Vitamina C 1000mg, Termómetro Digital.
                                    Pago realizado con tarjeta de crédito.
                                </div>
                                <div class="notification-meta">
                                    <div class="notification-time">
                                        <i class="fas fa-clock"></i>
                                        <span>Hace 8 minutos</span>
                                    </div>
                                    <span class="notification-priority priority-medium">Media</span>
                                    <span class="notification-category">
                                        <i class="fas fa-tag"></i> Ventas
                                    </span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn-small btn-mark-read" onclick="markAsRead(this)">
                                        <i class="fas fa-check"></i> Marcar Leída
                                    </button>
                                    <button class="btn-small btn-delete" onclick="deleteNotification(this)">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notificación 3 - Sistema -->
                    <div class="notification-item" data-category="system" data-priority="low">
                        <div class="notification-content">
                            <div class="notification-icon info">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="notification-body">
                                <h5 class="notification-title">Respaldo de Base de Datos Completado</h5>
                                <div class="notification-message">
                                    El respaldo automático de la base de datos se ha completado exitosamente. 
                                    Tamaño del respaldo: 2.4 GB. Archivos guardados en servidor local y nube.
                                    Próximo respaldo programado: mañana a las 2:00 AM.
                                </div>
                                <div class="notification-meta">
                                    <div class="notification-time">
                                        <i class="fas fa-clock"></i>
                                        <span>Hace 1 hora</span>
                                    </div>
                                    <span class="notification-priority priority-low">Baja</span>
                                    <span class="notification-category">
                                        <i class="fas fa-tag"></i> Sistema
                                    </span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn-small btn-mark-read" onclick="markAsRead(this)">
                                        <i class="fas fa-check"></i> Marcar Leída
                                    </button>
                                    <button class="btn-small btn-delete" onclick="deleteNotification(this)">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notificación 4 - Cliente -->
                    <div class="notification-item unread" data-category="customers" data-priority="medium">
                        <div class="notification-content">
                            <div class="notification-icon primary">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="notification-body">
                                <h5 class="notification-title">Nuevo Cliente Registrado</h5>
                                <div class="notification-message">
                                    Se ha registrado un nuevo cliente en el sistema. 
                                    Nombre: Carlos Rodríguez Mendoza. 
                                    Documento: 78945612. 
                                    Teléfono: 912345678. 
                                    Email: carlos.rodriguez@email.com
                                </div>
                                <div class="notification-meta">
                                    <div class="notification-time">
                                        <i class="fas fa-clock"></i>
                                        <span>Hace 2 horas</span>
                                    </div>
                                    <span class="notification-priority priority-medium">Media</span>
                                    <span class="notification-category">
                                        <i class="fas fa-tag"></i> Clientes
                                    </span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn-small btn-mark-read" onclick="markAsRead(this)">
                                        <i class="fas fa-check"></i> Marcar Leída
                                    </button>
                                    <button class="btn-small btn-delete" onclick="deleteNotification(this)">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notificación 5 - Temperatura -->
                    <div class="notification-item unread" data-category="alerts" data-priority="high">
                        <div class="notification-content">
                            <div class="notification-icon warning">
                                <i class="fas fa-thermometer-half"></i>
                            </div>
                            <div class="notification-body">
                                <h5 class="notification-title">Alerta de Temperatura - Refrigerador Principal</h5>
                                <div class="notification-message">
                                    La temperatura del refrigerador principal ha superado los 8°C (actualmente 8.5°C). 
                                    Esto puede comprometer la integridad de productos termosensibles. 
                                    Se recomienda revisar inmediatamente el sistema de enfriamiento.
                                </div>
                                <div class="notification-meta">
                                    <div class="notification-time">
                                        <i class="fas fa-clock"></i>
                                        <span>Hace 3 horas</span>
                                    </div>
                                    <span class="notification-priority priority-high">Alta</span>
                                    <span class="notification-category">
                                        <i class="fas fa-tag"></i> Alertas
                                    </span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn-small btn-mark-read" onclick="markAsRead(this)">
                                        <i class="fas fa-check"></i> Marcar Leída
                                    </button>
                                    <button class="btn-small btn-delete" onclick="deleteNotification(this)">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notificación 6 - Mantenimiento -->
                    <div class="notification-item" data-category="system" data-priority="low">
                        <div class="notification-content">
                            <div class="notification-icon info">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div class="notification-body">
                                <h5 class="notification-title">Mantenimiento Programado</h5>
                                <div class="notification-message">
                                    Mantenimiento preventivo programado para mañana de 1:00 AM a 3:00 AM. 
                                    Durante este período el sistema estará en modo mantenimiento. 
                                    Se actualizarán los módulos de inventario y reportes.
                                </div>
                                <div class="notification-meta">
                                    <div class="notification-time">
                                        <i class="fas fa-clock"></i>
                                        <span>Hace 5 horas</span>
                                    </div>
                                    <span class="notification-priority priority-low">Baja</span>
                                    <span class="notification-category">
                                        <i class="fas fa-tag"></i> Sistema
                                    </span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn-small btn-mark-read" onclick="markAsRead(this)">
                                        <i class="fas fa-check"></i> Marcar Leída
                                    </button>
                                    <button class="btn-small btn-delete" onclick="deleteNotification(this)">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notificación 7 - Vencimientos -->
                    <div class="notification-item unread" data-category="inventory" data-priority="high">
                        <div class="notification-content">
                            <div class="notification-icon danger">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <div class="notification-body">
                                <h5 class="notification-title">Productos Próximos a Vencer</h5>
                                <div class="notification-message">
                                    8 productos vencerán en los próximos 30 días. 
                                    Los más críticos: Insulina (15 días), Vacunas (22 días). 
                                    Se recomienda revisar el plan de rotación de inventario y contactar proveedores.
                                </div>
                                <div class="notification-meta">
                                    <div class="notification-time">
                                        <i class="fas fa-clock"></i>
                                        <span>Hace 6 horas</span>
                                    </div>
                                    <span class="notification-priority priority-high">Alta</span>
                                    <span class="notification-category">
                                        <i class="fas fa-tag"></i> Inventario
                                    </span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn-small btn-mark-read" onclick="markAsRead(this)">
                                        <i class="fas fa-check"></i> Marcar Leída
                                    </button>
                                    <button class="btn-small btn-delete" onclick="deleteNotification(this)">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado vacío (se muestra cuando no hay notificaciones) -->
                <div class="empty-state" id="emptyState" style="display: none;">
                    <i class="fas fa-bell-slash"></i>
                    <h3>No hay notificaciones</h3>
                    <p>Todas las notificaciones han sido procesadas. ¡Excelente trabajo!</p>
                </div>

                <!-- Botón cargar más -->
                <div class="load-more" id="loadMoreSection">
                    <button class="btn-load-more" onclick="loadMoreNotifications()">
                        <i class="fas fa-chevron-down me-2"></i>
                        Cargar Más Notificaciones
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Filtros de notificaciones
            $('.filter-tab').on('click', function() {
                const filter = $(this).data('filter');
                
                // Remover clase active de todos los filtros
                $('.filter-tab').removeClass('active');
                
                // Agregar clase active al filtro seleccionado
                $(this).addClass('active');
                
                // Aplicar filtro
                filterNotifications(filter);
            });

            // Simular nuevas notificaciones cada 60 segundos
            setInterval(simulateNewNotification, 60000);
            
            // Animación de entrada
            setTimeout(() => {
                $('.notification-item').each(function(index) {
                    setTimeout(() => {
                        $(this).addClass('pulse');
                        setTimeout(() => {
                            $(this).removeClass('pulse');
                        }, 1000);
                    }, index * 100);
                });
            }, 500);
        });

        // Función para filtrar notificaciones
        function filterNotifications(filter) {
            const notifications = $('.notification-item');
            
            notifications.hide();
            
            switch(filter) {
                case 'all':
                    notifications.fadeIn();
                    break;
                case 'unread':
                    notifications.filter('.unread').fadeIn();
                    break;
                case 'urgent':
                    notifications.filter('[data-priority="high"]').fadeIn();
                    break;
                case 'sales':
                    notifications.filter('[data-category="sales"]').fadeIn();
                    break;
                case 'inventory':
                    notifications.filter('[data-category="inventory"]').fadeIn();
                    break;
            }
            
            // Mostrar estado vacío si no hay resultados
            const visibleNotifications = notifications.filter(':visible');
            if (visibleNotifications.length === 0) {
                $('#emptyState').show();
                $('#loadMoreSection').hide();
            } else {
                $('#emptyState').hide();
                $('#loadMoreSection').show();
            }
            
            // Mostrar notificación
            Swal.fire({
                icon: 'info',
                title: 'Filtro aplicado',
                text: `Mostrando notificaciones: ${filter === 'all' ? 'todas' : filter}`,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }

        // Función para marcar como leída
        function markAsRead(button) {
            const notification = $(button).closest('.notification-item');
            
            // Remover clase unread
            notification.removeClass('unread');
            
            // Remover punto indicador
            notification.css('border-left', 'none');
            
            // Deshabilitar botón
            $(button).prop('disabled', true).html('<i class="fas fa-check"></i> Leída');
            
            // Actualizar contador de no leídas
            updateUnreadCount();
            
            // Mostrar confirmación
            Swal.fire({
                icon: 'success',
                title: 'Notificación marcada',
                text: 'La notificación ha sido marcada como leída',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }

        // Función para eliminar notificación
        function deleteNotification(button) {
            const notification = $(button).closest('.notification-item');
            const title = notification.find('.notification-title').text();
            
            Swal.fire({
                icon: 'warning',
                title: 'Eliminar notificación',
                text: `¿Está seguro de que desea eliminar "${title}"?`,
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e74c3c'
            }).then((result) => {
                if (result.isConfirmed) {
                    notification.fadeOut(() => {
                        notification.remove();
                        
                        // Verificar si quedan notificaciones
                        if ($('.notification-item').length === 0) {
                            $('#emptyState').show();
                            $('#loadMoreSection').hide();
                        }
                    });
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: 'La notificación ha sido eliminada',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            });
        }

        // Función para marcar todas como leídas
        function markAllAsRead() {
            const unreadNotifications = $('.notification-item.unread');
            
            if (unreadNotifications.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin notificaciones',
                    text: 'No hay notificaciones no leídas',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                return;
            }
            
            unreadNotifications.removeClass('unread');
            unreadNotifications.css('border-left', 'none');
            unreadNotifications.find('.btn-mark-read').prop('disabled', true).html('<i class="fas fa-check"></i> Leída');
            
            // Actualizar contador
            updateUnreadCount();
            
            Swal.fire({
                icon: 'success',
                title: 'Todas marcadas',
                text: `${unreadNotifications.length} notificaciones marcadas como leídas`,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }

        // Función para actualizar contador de no leídas
        function updateUnreadCount() {
            const unreadCount = $('.notification-item.unread').length;
            $('.stat-card.unread .stat-number').text(unreadCount);
        }

        // Función para refrescar notificaciones
        function refreshNotifications() {
            // Simular carga
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
                
                Swal.fire({
                    icon: 'success',
                    title: 'Actualizado',
                    text: 'Las notificaciones han sido actualizadas',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }, 1500);
        }

        // Función para cargar más notificaciones
        function loadMoreNotifications() {
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
                
                Swal.fire({
                    icon: 'info',
                    title: 'Cargado',
                    text: 'Se han cargado más notificaciones',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }, 1000);
        }

        // Función para simular nueva notificación
        function simulateNewNotification() {
            const notifications = [
                {
                    title: 'Nueva venta registrada',
                    message: 'Venta por S/ 125.50 registrada exitosamente.',
                    category: 'sales',
                    priority: 'medium',
                    icon: 'shopping-cart',
                    iconClass: 'success'
                },
                {
                    title: 'Stock bajo - Ibuprofeno',
                    message: 'El producto Ibuprofeno 400mg tiene solo 12 unidades restantes.',
                    category: 'inventory',
                    priority: 'high',
                    icon: 'exclamation-triangle',
                    iconClass: 'danger'
                },
                {
                    title: 'Cliente nuevo registrado',
                    message: 'Ana López se ha registrado como nuevo cliente.',
                    category: 'customers',
                    priority: 'low',
                    icon: 'user-plus',
                    iconClass: 'primary'
                }
            ];
            
            const randomNotification = notifications[Math.floor(Math.random() * notifications.length)];
            addNewNotification(randomNotification);
        }

        // Función para agregar nueva notificación
        function addNewNotification(notificationData) {
            const timestamp = new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
            
            const notificationHTML = `
                <div class="notification-item unread" data-category="${notificationData.category}" data-priority="${notificationData.priority}">
                    <div class="notification-content">
                        <div class="notification-icon ${notificationData.iconClass}">
                            <i class="fas fa-${notificationData.icon}"></i>
                        </div>
                        <div class="notification-body">
                            <h5 class="notification-title">${notificationData.title}</h5>
                            <div class="notification-message">
                                ${notificationData.message}
                            </div>
                            <div class="notification-meta">
                                <div class="notification-time">
                                    <i class="fas fa-clock"></i>
                                    <span>Ahora mismo</span>
                                </div>
                                <span class="notification-priority priority-${notificationData.priority === 'high' ? 'high' : notificationData.priority === 'medium' ? 'medium' : 'low'}">
                                    ${notificationData.priority === 'high' ? 'Alta' : notificationData.priority === 'medium' ? 'Media' : 'Baja'}
                                </span>
                                <span class="notification-category">
                                    <i class="fas fa-tag"></i> ${notificationData.category.charAt(0).toUpperCase() + notificationData.category.slice(1)}
                                </span>
                            </div>
                            <div class="notification-actions">
                                <button class="btn-small btn-mark-read" onclick="markAsRead(this)">
                                    <i class="fas fa-check"></i> Marcar Leída
                                </button>
                                <button class="btn-small btn-delete" onclick="deleteNotification(this)">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Agregar al inicio de la lista
            $('#notificationsList').prepend(notificationHTML);
            
            // Actualizar contador
            updateUnreadCount();
            
            // Mostrar toast de nueva notificación
            showNewNotificationToast(notificationData.title);
            
            // Sonido de notificación (opcional)
            playNotificationSound();
        }

        // Función para mostrar toast de nueva notificación
        function showNewNotificationToast(title) {
            const toast = $(`
                <div class="alert alert-success alert-dismissible fade show position-fixed" 
                     style="top: 100px; right: 20px; z-index: 1050; width: 350px; max-width: 90vw;">
                    <i class="fas fa-bell me-2"></i>
                    <strong>Nueva notificación:</strong> ${title}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            
            $('body').append(toast);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                toast.alert('close');
            }, 5000);
        }

        // Función para reproducir sonido de notificación
        function playNotificationSound() {
            // Crear un contexto de audio para reproducir sonido
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            } catch (e) {
                // Fallback si Web Audio API no está disponible
                console.log('Audio notification played');
            }
        }

        // Efectos de animación adicionales
        document.addEventListener('DOMContentLoaded', function() {
            // Efectos de hover en estadísticas
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>
</html>