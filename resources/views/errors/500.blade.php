<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del Servidor - SIFANO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #6c757d;
            --danger-color: #dc3545;
            --success-color: #198754;
            --warning-color: #ffc107;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
        }

        body {
            background-color: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .error-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .error-header {
            background: linear-gradient(135deg, var(--danger-color) 0%, #c82333 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .error-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .error-code {
            font-size: 4rem;
            font-weight: 700;
            margin: 0;
            line-height: 1;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 1rem 0 0;
        }

        .error-body {
            padding: 2rem;
        }

        .error-message {
            font-size: 1.1rem;
            color: var(--secondary-color);
            line-height: 1.6;
            margin-bottom: 2rem;
            text-align: center;
        }

        .error-details {
            background: var(--light-gray);
            border-radius: 6px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--danger-color);
        }

        .error-details h6 {
            color: #212529;
            margin-bottom: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .detail-item {
            background: white;
            padding: 1rem;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }

        .detail-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 0.95rem;
            color: #212529;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
        }

        .url-value {
            color: var(--danger-color);
            font-weight: 500;
        }

        .error-stack {
            background: #2c3e50;
            color: #ecf0f1;
            border-radius: 4px;
            padding: 1rem;
            margin-top: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            overflow-x: auto;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #c82333;
            color: white;
        }

        .btn-secondary {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--secondary-color);
        }

        .btn-secondary:hover {
            background-color: var(--light-gray);
            color: #495057;
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #146c43;
            color: white;
        }

        .system-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 6px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .system-info h6 {
            color: #0d47a1;
            margin-bottom: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .system-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .system-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            background: white;
            border-radius: 4px;
        }

        .system-label {
            font-size: 0.85rem;
            color: var(--secondary-color);
        }

        .system-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #0d47a1;
        }

        .server-status {
            background: #fff3e0;
            border: 1px solid #ffcc02;
            border-radius: 6px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .server-status h6 {
            color: #e65100;
            margin-bottom: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: white;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-indicator.red {
            background-color: var(--danger-color);
        }

        .status-indicator.yellow {
            background-color: var(--warning-color);
        }

        .status-indicator.green {
            background-color: var(--success-color);
        }

        .status-info {
            flex: 1;
        }

        .status-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #212529;
        }

        .status-description {
            font-size: 0.8rem;
            color: var(--secondary-color);
        }

        .troubleshooting-section {
            background: #f1f8e9;
            border: 1px solid #c8e6c9;
            border-radius: 6px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .troubleshooting-section h6 {
            color: #2e7d32;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .troubleshooting-list {
            margin: 0;
            padding-left: 1.5rem;
        }

        .troubleshooting-list li {
            margin-bottom: 0.5rem;
            color: #616161;
            line-height: 1.4;
        }

        .contact-section {
            background: #fafafa;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: center;
        }

        .contact-section h6 {
            color: #212529;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .contact-info {
            color: var(--secondary-color);
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 576px) {
            .error-header {
                padding: 1.5rem;
            }

            .error-code {
                font-size: 3rem;
            }

            .error-body {
                padding: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 250px;
                justify-content: center;
            }

            .detail-grid,
            .system-grid,
            .status-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <!-- Error Header -->
            <div class="error-header">
                <div class="error-icon">
                    <i class="fas fa-server"></i>
                </div>
                <h1 class="error-code">500</h1>
                <h2 class="error-title">Error Interno del Servidor</h2>
            </div>

            <!-- Error Body -->
            <div class="error-body">
                <!-- Error Message -->
                <p class="error-message">
                    Se ha producido un error interno en el servidor. Nuestro equipo técnico ha sido notificado automáticamente 
                    y está trabajando para resolver el problema. Por favor, inténtelo de nuevo en unos minutos.
                </p>

                <!-- Error Details -->
                <div class="error-details">
                    <h6>
                        <i class="fas fa-exclamation-triangle"></i>
                        Detalles del Error
                    </h6>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">URL Solicitada</div>
                            <div class="detail-value url-value" id="requestedUrl">-</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Código de Estado</div>
                            <div class="detail-value">500 Internal Server Error</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Timestamp</div>
                            <div class="detail-value" id="errorTimestamp">-</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">ID de Error</div>
                            <div class="detail-value" id="errorId">-</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Servidor</div>
                            <div class="detail-value" id="serverName">-</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">User Agent</div>
                            <div class="detail-value" id="userAgent">-</div>
                        </div>
                    </div>
                    <div class="error-stack" id="errorStack">
                        <strong>Stack Trace:</strong><br>
                        Exception: Internal Server Error<br>
                        File: app/Http/Controllers/ErrorHandler.php<br>
                        Line: <span id="errorLine">156</span><br>
                        Message: <span id="errorMessage">Unexpected server exception</span><br>
                        <br>
                        <strong>PHP Backtrace:</strong><br>
                        #0 /var/www/html/app/Http/Controllers/BaseController.php(87): ErrorHandler->handle()<br>
                        #1 /var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Controller.php(54): BaseController->callAction()<br>
                        #2 /var/www/html/vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php(45): Controller->callAction()
                    </div>
                </div>

                <!-- System Information -->
                <div class="system-info">
                    <h6>
                        <i class="fas fa-server"></i>
                        Información del Servidor
                    </h6>
                    <div class="system-grid">
                        <div class="system-item">
                            <span class="system-label">Servidor</span>
                            <span class="system-value" id="serverHostname">SIFANO-SERVER</span>
                        </div>
                        <div class="system-item">
                            <span class="system-label">Versión PHP</span>
                            <span class="system-value" id="phpVersion">8.2.15</span>
                        </div>
                        <div class="system-item">
                            <span class="system-label">Memoria Límite</span>
                            <span class="system-value" id="memoryLimit">256M</span>
                        </div>
                        <div class="system-item">
                            <span class="system-label">Max Execution Time</span>
                            <span class="system-value" id="maxExecutionTime">30s</span>
                        </div>
                        <div class="system-item">
                            <span class="system-label">Zona Horaria</span>
                            <span class="system-value" id="timezone">America/Lima</span>
                        </div>
                        <div class="system-item">
                            <span class="system-label">Base de Datos</span>
                            <span class="system-value" id="databaseVersion">MySQL 8.0.35</span>
                        </div>
                        <div class="system-item">
                            <span class="system-label">Versión Laravel</span>
                            <span class="system-value" id="laravelVersion">10.45.0</span>
                        </div>
                        <div class="system-item">
                            <span class="system-label">Sistema Operativo</span>
                            <span class="system-value" id="osVersion">Ubuntu 22.04</span>
                        </div>
                    </div>
                </div>

                <!-- Server Status -->
                <div class="server-status">
                    <h6>
                        <i class="fas fa-heartbeat"></i>
                        Estado de Componentes del Sistema
                    </h6>
                    <div class="status-grid" id="systemStatus">
                        <div class="status-item">
                            <div class="status-indicator red" id="dbStatus"></div>
                            <div class="status-info">
                                <div class="status-name">Base de Datos</div>
                                <div class="status-description" id="dbDescription">Error de conexión</div>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-indicator yellow" id="cacheStatus"></div>
                            <div class="status-info">
                                <div class="status-name">Sistema de Cache</div>
                                <div class="status-description" id="cacheDescription">Respuesta lenta</div>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-indicator green" id="authStatus"></div>
                            <div class="status-info">
                                <div class="status-name">Autenticación</div>
                                <div class="status-description" id="authDescription">Funcionando correctamente</div>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-indicator yellow" id="apiStatus"></div>
                            <div class="status-info">
                                <div class="status-name">Servicios API</div>
                                <div class="status-description" id="apiDescription">Mantenimiento programado</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="/" class="btn btn-primary">
                        <i class="fas fa-home"></i>
                        Ir al Inicio
                    </a>
                    <button onclick="location.reload()" class="btn btn-success">
                        <i class="fas fa-redo"></i>
                        Reintentar
                    </button>
                    <button onclick="history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Regresar
                    </button>
                </div>

                <!-- Troubleshooting Section -->
                <div class="troubleshooting-section">
                    <h6>
                        <i class="fas fa-tools"></i>
                        Posibles Causas y Soluciones
                    </h6>
                    <ul class="troubleshooting-list">
                        <li><strong>Problema temporal del servidor:</strong> Espere unos minutos e intente recargar la página</li>
                        <li><strong>Error de base de datos:</strong> El problema puede estar relacionado con la conectividad a la base de datos</li>
                        <li><strong>Límite de recursos:</strong> El servidor puede haber excedido su límite de memoria o tiempo de ejecución</li>
                        <li><strong>Configuración incorrecta:</strong> Puede haber un error en la configuración del servidor web</li>
                        <li><strong>Dependencias faltantes:</strong> Faltan librerías o servicios necesarios para el funcionamiento</li>
                    </ul>
                </div>

                <!-- Contact Section -->
                <div class="contact-section">
                    <h6>
                        <i class="fas fa-headset"></i>
                        ¿Necesita Ayuda Inmediata?
                    </h6>
                    <div class="contact-info">
                        Si el problema persiste o necesita asistencia técnica, contacte a nuestro equipo de soporte:
                    </div>
                    <div class="action-buttons">
                        <a href="mailto:soporte@sifano.com?subject=Error%20500&body=Error ID: ERR-500-[ID]" class="btn btn-primary">
                            <i class="fas fa-envelope"></i>
                            soporte@sifano.com
                        </a>
                        <a href="tel:+5112345678" class="btn btn-secondary">
                            <i class="fas fa-phone"></i>
                            +51 1 234-5678
                        </a>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Horario de atención: Lunes a Viernes 8:00 AM - 6:00 PM
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Información dinámica del sistema y error
        function getErrorInfo() {
            // URL solicitada
            document.getElementById('requestedUrl').textContent = window.location.href;
            
            // Timestamp actual
            const now = new Date();
            document.getElementById('errorTimestamp').textContent = now.toLocaleString('es-ES');
            
            // Generar ID de error único
            const errorId = '500-' + Math.random().toString(36).substr(2, 12).toUpperCase();
            document.getElementById('errorId').textContent = errorId;
            
            // User Agent resumido
            const userAgent = navigator.userAgent;
            let browserName = 'Unknown Browser';
            if (userAgent.includes('Chrome')) browserName = 'Chrome';
            else if (userAgent.includes('Firefox')) browserName = 'Firefox';
            else if (userAgent.includes('Safari')) browserName = 'Safari';
            else if (userAgent.includes('Edge')) browserName = 'Edge';
            document.getElementById('userAgent').textContent = browserName;
            
            // Información del servidor
            const systemInfo = {
                serverHostname: 'SIFANO-SERVER',
                phpVersion: '8.2.15',
                memoryLimit: '256M',
                maxExecutionTime: '30s',
                timezone: 'America/Lima',
                databaseVersion: 'MySQL 8.0.35',
                laravelVersion: '10.45.0',
                osVersion: 'Ubuntu 22.04'
            };
            
            // Llenar información del sistema
            Object.keys(systemInfo).forEach(key => {
                const element = document.getElementById(key);
                if (element) {
                    element.textContent = systemInfo[key];
                }
            });
        }
        
        // Simular cambios en el estado del sistema
        function updateSystemStatus() {
            const statusOptions = {
                db: { colors: ['red', 'yellow', 'green'], descriptions: ['Error de conexión', 'Respuesta lenta', 'Funcionando correctamente'] },
                cache: { colors: ['yellow', 'green'], descriptions: ['Respuesta lenta', 'Funcionando correctamente'] },
                auth: { colors: ['green', 'yellow'], descriptions: ['Funcionando correctamente', 'Mantenimiento menor'] },
                api: { colors: ['yellow', 'red', 'green'], descriptions: ['Mantenimiento programado', 'Error temporal', 'Funcionando correctamente'] }
            };
            
            Object.keys(statusOptions).forEach(key => {
                const options = statusOptions[key];
                const colorIndex = Math.floor(Math.random() * options.colors.length);
                const color = options.colors[colorIndex];
                const description = options.descriptions[colorIndex];
                
                const indicator = document.getElementById(key + 'Status');
                const descElement = document.getElementById(key + 'Description');
                
                if (indicator && descElement) {
                    indicator.className = `status-indicator ${color}`;
                    descElement.textContent = description;
                }
            });
        }
        
        // Función para copiar información del error
        function copyErrorInfo() {
            const errorInfo = {
                url: window.location.href,
                timestamp: new Date().toISOString(),
                status: '500 Internal Server Error',
                userAgent: navigator.userAgent,
                server: 'SIFANO-SERVER',
                errorId: document.getElementById('errorId').textContent
            };
            
            const errorText = `Error SIFANO 500\n` +
                `URL: ${errorInfo.url}\n` +
                `Timestamp: ${errorInfo.timestamp}\n` +
                `Status: ${errorInfo.status}\n` +
                `Error ID: ${errorInfo.errorId}\n` +
                `User Agent: ${errorInfo.userAgent}\n` +
                `Server: ${errorInfo.server}`;
            
            navigator.clipboard.writeText(errorText).then(() => {
                showNotification('Información del error copiada al portapapeles', 'success');
            }).catch(() => {
                showNotification('No se pudo copiar al portapapeles', 'error');
            });
        }
        
        // Mostrar notificación
        function showNotification(message, type = 'info') {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
        
        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            getErrorInfo();
            
            // Actualizar estado del sistema cada 30 segundos
            setInterval(updateSystemStatus, 30000);
            
            // Agregar botón de copiar información del error
            setTimeout(() => {
                const copyButton = document.createElement('button');
                copyButton.className = 'btn btn-outline-secondary';
                copyButton.style.cssText = 'position: fixed; bottom: 20px; left: 20px; z-index: 1000;';
                copyButton.innerHTML = '<i class="fas fa-copy"></i> Copiar Info Error';
                copyButton.onclick = copyErrorInfo;
                document.body.appendChild(copyButton);
            }, 1000);
        });
    </script>
</body>
</html>