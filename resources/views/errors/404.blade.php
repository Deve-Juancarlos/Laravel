<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página No Encontrada - SIFANO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #fd7e14;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
            --info-color: #0dcaf0;
        }

        body {
            background-color: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 800px;
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
            background: linear-gradient(135deg, var(--primary-color) 0%, #e55100 100%);
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
            border-left: 4px solid var(--primary-color);
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
            color: var(--primary-color);
            font-weight: 500;
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
            background-color: #e55100;
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

        .btn-info {
            background-color: var(--info-color);
            color: #212529;
        }

        .btn-info:hover {
            background-color: #0da9c5;
            color: #212529;
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

        .help-section {
            background: #fff3e0;
            border: 1px solid #ffcc02;
            border-radius: 6px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .help-section h6 {
            color: #e65100;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .help-list {
            margin: 0;
            padding-left: 1.5rem;
        }

        .help-list li {
            margin-bottom: 0.5rem;
            color: #616161;
            line-height: 1.4;
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
            .system-grid {
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
                    <i class="fas fa-search"></i>
                </div>
                <h1 class="error-code">404</h1>
                <h2 class="error-title">Página No Encontrada</h2>
            </div>

            <!-- Error Body -->
            <div class="error-body">
                <!-- Error Message -->
                <p class="error-message">
                    La página que está buscando no existe o ha sido movida. 
                    Verifique la URL o utilice los enlaces de navegación a continuación.
                </p>

                <!-- Error Details -->
                <div class="error-details">
                    <h6>
                        <i class="fas fa-info-circle"></i>
                        Detalles del Error
                    </h6>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">URL Solicitada</div>
                            <div class="detail-value url-value" id="requestedUrl">-</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Código de Estado</div>
                            <div class="detail-value">404 Not Found</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Método HTTP</div>
                            <div class="detail-value" id="httpMethod">GET</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Timestamp</div>
                            <div class="detail-value" id="errorTimestamp">-</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">ID de Sesión</div>
                            <div class="detail-value" id="sessionId">-</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">User Agent</div>
                            <div class="detail-value" id="userAgent">-</div>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="system-info">
                    <h6>
                        <i class="fas fa-server"></i>
                        Información del Sistema
                    </h6>
                    <div class="system-grid">
                        <div class="system-item">
                            <span class="system-label">Servidor</span>
                            <span class="system-value" id="serverName">SIFANO-SERVER</span>
                        </div>
                        <div class="system-item">
                            <span class="system-label">Versión PHP</span>
                            <span class="system-value" id="phpVersion">8.2.15</span>
                        </div>
                        <div class="system-item">
                            <span class="system-label">Zona Horaria</span>
                            <span class="system-value" id="timezone">America/Lima</span>
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
                            <span class="system-label">Versión del Sistema</span>
                            <span class="system-value" id="appVersion">v2.4.1</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="/" class="btn btn-primary">
                        <i class="fas fa-home"></i>
                        Ir al Inicio
                    </a>
                    <button onclick="history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Página Anterior
                    </a>
                    <button onclick="location.reload()" class="btn btn-info">
                        <i class="fas fa-redo"></i>
                        Reintentar
                    </button>
                </div>

                <!-- Help Section -->
                <div class="help-section">
                    <h6>
                        <i class="fas fa-question-circle"></i>
                        ¿Qué puede haber causado este error?
                    </h6>
                    <ul class="help-list">
                        <li>La URL puede haber cambiado o ser incorrecta</li>
                        <li>La página puede haber sido movida o eliminada</li>
                        <li>Puede haber un problema temporal de conectividad</li>
                        <li>El recurso puede requerir permisos especiales</li>
                        <li>Podría ser un error de configuración del servidor</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Obtener información dinámica del sistema
        function getSystemInfo() {
            // URL solicitada
            document.getElementById('requestedUrl').textContent = window.location.href;
            
            // Método HTTP (simulado, en servidor real sería dinámico)
            document.getElementById('httpMethod').textContent = 'GET';
            
            // Timestamp actual
            const now = new Date();
            document.getElementById('errorTimestamp').textContent = now.toLocaleString('es-ES');
            
            // ID de sesión (simulado, en servidor real sería dinámico)
            document.getElementById('sessionId').textContent = generateSessionId();
            
            // User Agent
            document.getElementById('userAgent').textContent = getShortUserAgent();
            
            // Información del sistema (en servidor real vendría de PHP)
            const systemInfo = {
                serverName: 'SIFANO-SERVER',
                phpVersion: '8.2.15',
                timezone: 'America/Lima',
                memoryLimit: '256M',
                maxExecutionTime: '30s',
                appVersion: 'v2.4.1'
            };
            
            // Llenar información del sistema
            Object.keys(systemInfo).forEach(key => {
                const element = document.getElementById(key);
                if (element) {
                    element.textContent = systemInfo[key];
                }
            });
        }
        
        // Generar ID de sesión simulado
        function generateSessionId() {
            return 'sess_' + Math.random().toString(36).substr(2, 16) + '_' + Date.now().toString(36);
        }
        
        // Obtener User Agent resumido
        function getShortUserAgent() {
            const userAgent = navigator.userAgent;
            if (userAgent.includes('Chrome')) return 'Chrome Browser';
            if (userAgent.includes('Firefox')) return 'Firefox Browser';
            if (userAgent.includes('Safari')) return 'Safari Browser';
            if (userAgent.includes('Edge')) return 'Edge Browser';
            return 'Unknown Browser';
        }
        
        // Función para copiar información del error
        function copyErrorInfo() {
            const errorInfo = {
                url: window.location.href,
                timestamp: new Date().toISOString(),
                status: '404 Not Found',
                userAgent: navigator.userAgent,
                server: 'SIFANO-SERVER'
            };
            
            const errorText = `Error SIFANO 404\n` +
                `URL: ${errorInfo.url}\n` +
                `Timestamp: ${errorInfo.timestamp}\n` +
                `Status: ${errorInfo.status}\n` +
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
            getSystemInfo();
            
            // Agregar botón de copiar información (opcional)
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