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
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --warning-gradient: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            --error-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --shadow-elegant: 0 20px 40px rgba(0, 0, 0, 0.1);
            --border-radius: 15px;
        }

        body {
            background: var(--error-gradient);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animación de fondo con circuito */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="circuit" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M10,10 L40,10 M10,25 L40,25 M10,40 L40,40 M10,25 L10,40 M40,10 L40,25 M25,10 L25,40" stroke="rgba(255,255,255,0.1)" stroke-width="0.5" fill="none"/><circle cx="10" cy="10" r="2" fill="rgba(255,107,107,0.3)"/><circle cx="40" cy="25" r="2" fill="rgba(255,107,107,0.3)"/><circle cx="25" cy="40" r="2" fill="rgba(255,107,107,0.3)"/></pattern></defs><rect width="100" height="100" fill="url(%23circuit)"/></svg>') repeat;
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.7; }
        }

        .error-container {
            background: var(--glass-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-elegant);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 4rem;
            text-align: center;
            position: relative;
            z-index: 2;
            max-width: 700px;
            width: 90%;
            animation: slideIn 1.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(60px) scale(0.7) rotate(10deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1) rotate(0deg);
            }
        }

        .error-icon {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: var(--error-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 25px 50px rgba(255, 107, 107, 0.4);
            animation: explode 3s ease-in-out infinite;
            position: relative;
        }

        .error-icon::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            right: -20px;
            bottom: -20px;
            border-radius: 50%;
            background: var(--error-gradient);
            opacity: 0.3;
            animation: shockwave 4s ease-out infinite;
        }

        @keyframes shockwave {
            0% {
                transform: scale(1);
                opacity: 0.3;
            }
            50% {
                transform: scale(1.5);
                opacity: 0.6;
            }
            100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        .error-icon i {
            font-size: 4.5rem;
            color: white;
            animation: glitch 2s ease-in-out infinite;
        }

        @keyframes glitch {
            0%, 100% { transform: translateX(0) rotate(0deg); }
            10% { transform: translateX(-2px) rotate(-1deg); }
            20% { transform: translateX(2px) rotate(1deg); }
            30% { transform: translateX(-2px) rotate(-1deg); }
            40% { transform: translateX(2px) rotate(1deg); }
            50% { transform: translateX(-2px) rotate(-1deg); }
        }

        @keyframes explode {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .error-code {
            font-size: 8rem;
            font-weight: 900;
            background: var(--error-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            line-height: 1;
            text-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
            animation: flicker 1.5s ease-in-out infinite;
        }

        @keyframes flicker {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
            75% { opacity: 0.9; }
        }

        .error-title {
            font-size: 2.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 1rem 0;
        }

        .error-message {
            font-size: 1.3rem;
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .error-details {
            background: rgba(255, 107, 107, 0.1);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin: 2rem 0;
            border-left: 4px solid #ff6b6b;
            text-align: left;
        }

        .error-details h5 {
            color: #e74c3c;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .error-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            color: #495057;
            border-left: 3px solid #ff6b6b;
        }

        .troubleshooting-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .troubleshooting-section h5 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .troubleshooting-steps {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .troubleshooting-steps li {
            color: #495057;
            margin-bottom: 1rem;
            padding: 1rem;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 8px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .troubleshooting-steps li:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateX(5px);
        }

        .step-number {
            width: 30px;
            height: 30px;
            background: var(--primary-gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 3rem;
        }

        .btn-error {
            padding: 1rem 1.5rem;
            border-radius: 50px;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .btn-primary-custom {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-danger-custom {
            background: var(--error-gradient);
            color: white;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-danger-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 107, 107, 0.4);
            color: white;
        }

        .btn-outline-custom {
            background: transparent;
            border: 2px solid #ff6b6b;
            color: #ff6b6b;
        }

        .btn-outline-custom:hover {
            background: var(--error-gradient);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 107, 107, 0.4);
        }

        .system-status {
            background: var(--warning-gradient);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }

        .system-status h6 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-indicators {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 8px;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            animation: blink 2s ease-in-out infinite;
        }

        .status-dot.green {
            background: #28a745;
        }

        .status-dot.red {
            background: #dc3545;
        }

        .status-dot.yellow {
            background: #ffc107;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .contact-support {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .contact-support h6 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .support-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .support-option {
            padding: 1rem;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 10px;
            text-decoration: none;
            color: #667eea;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .support-option:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .support-option i {
            font-size: 1.5rem;
        }

        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .floating-element {
            position: absolute;
            background: rgba(255, 107, 107, 0.2);
            border-radius: 50%;
            animation: floatElement 12s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            width: 70px;
            height: 70px;
            top: 15%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 90px;
            height: 90px;
            top: 70%;
            right: 15%;
            animation-delay: 4s;
        }

        .floating-element:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 75%;
            animation-delay: 8s;
        }

        @keyframes floatElement {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg) scale(1);
                opacity: 0.3;
            }
            50% { 
                transform: translateY(-50px) rotate(180deg) scale(1.3);
                opacity: 0.7;
            }
        }

        @media (max-width: 768px) {
            .error-container {
                padding: 2rem;
                margin: 1rem;
            }

            .error-code {
                font-size: 6rem;
            }

            .error-title {
                font-size: 1.8rem;
            }

            .error-message {
                font-size: 1.1rem;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }

            .btn-error {
                width: 100%;
                justify-content: center;
            }

            .status-indicators {
                grid-template-columns: 1fr;
            }

            .support-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Elementos flotantes decorativos -->
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <div class="container">
        <div class="error-container">
            <!-- Error Icon -->
            <div class="error-icon">
                <i class="fas fa-server"></i>
            </div>

            <!-- Error Code -->
            <h1 class="error-code">500</h1>

            <!-- Error Title -->
            <h2 class="error-title">Error Interno del Servidor</h2>

            <!-- Error Message -->
            <p class="error-message">
                Algo salió mal en nuestros servidores. Nuestro equipo técnico ha sido notificado automáticamente 
                y está trabajando para resolver el problema lo antes posible.
            </p>

            <!-- Error Details -->
            <div class="error-details">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Detalles Técnicos</h5>
                <p>
                    <strong>Código de Error:</strong> HTTP 500 Internal Server Error<br>
                    <strong>Timestamp:</strong> <span id="errorTime"></span><br>
                    <strong>Request ID:</strong> 500-<span id="errorId"></span><br>
                    <strong>Servidor:</strong> SIFANO-PROD-<span id="serverId"></span><br>
                    <strong>URL:</strong> <span id="errorUrl"></span>
                </p>
                <div class="error-info">
                    Exception: Internal Server Error<br>
                    File: app/Http/Controllers/ErrorHandler.php<br>
                    Line: 156<br>
                    Message: Unexpected server exception
                </div>
            </div>

            <!-- Troubleshooting Steps -->
            <div class="troubleshooting-section">
                <h5><i class="fas fa-tools me-2"></i>Pasos de Solución</h5>
                <ul class="troubleshooting-steps">
                    <li>
                        <div class="step-number">1</div>
                        <div>
                            <strong>Espere un momento</strong><br>
                            El problema puede ser temporal. Intente recargar la página en unos minutos.
                        </div>
                    </li>
                    <li>
                        <div class="step-number">2</div>
                        <div>
                            <strong>Verifique la URL</strong><br>
                            Asegúrese de que la dirección web esté escrita correctamente sin espacios adicionales.
                        </div>
                    </li>
                    <li>
                        <div class="step-number">3</div>
                        <div>
                            <strong>Intente una acción diferente</strong><br>
                            Use los botones de navegación para acceder a otras secciones del sistema.
                        </div>
                    </li>
                    <li>
                        <div class="step-number">4</div>
                        <div>
                            <strong>Contacte soporte</strong><br>
                            Si el problema persiste, contacte nuestro equipo de soporte técnico.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- System Status -->
            <div class="system-status">
                <h6>
                    <i class="fas fa-heartbeat"></i>
                    Estado del Sistema
                </h6>
                <div class="status-indicators">
                    <div class="status-item">
                        <div class="status-dot red"></div>
                        <div>
                            <strong>Base de Datos</strong><br>
                            <small>Conectividad degradada</small>
                        </div>
                    </div>
                    <div class="status-item">
                        <div class="status-dot yellow"></div>
                        <div>
                            <strong>API Services</strong><br>
                            <small>Respuesta lenta</small>
                        </div>
                    </div>
                    <div class="status-item">
                        <div class="status-dot green"></div>
                        <div>
                            <strong>Autenticación</strong><br>
                            <small>Funcionando correctamente</small>
                        </div>
                    </div>
                    <div class="status-item">
                        <div class="status-dot yellow"></div>
                        <div>
                            <strong>Cache System</strong><br>
                            <small>En mantenimiento</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ url('/') }}" class="btn-error btn-primary-custom">
                    <i class="fas fa-home"></i>
                    Ir al Inicio
                </a>
                <button onclick="history.back()" class="btn-error btn-outline-custom">
                    <i class="fas fa-arrow-left"></i>
                    Regresar
                </button>
                <button onclick="refreshPage()" class="btn-error btn-danger-custom">
                    <i class="fas fa-sync-alt"></i>
                    Reintentar
                </button>
                <a href="{{ url('/dashboard') }}" class="btn-error btn-outline-custom">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </div>

            <!-- Contact Support -->
            <div class="contact-support">
                <h6><i class="fas fa-headset me-2"></i>Soporte Técnico</h6>
                <div class="support-options">
                    <a href="tel:+5112345678" class="support-option">
                        <i class="fas fa-phone"></i>
                        <span>Llamar Soporte</span>
                    </a>
                    <a href="mailto:soporte@sifano.com" class="support-option">
                        <i class="fas fa-envelope"></i>
                        <span>Email Técnico</span>
                    </a>
                    <button onclick="openChat()" class="support-option">
                        <i class="fas fa-comments"></i>
                        <span>Chat en Vivo</span>
                    </button>
                    <button onclick="submitTicket()" class="support-option">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Crear Ticket</span>
                    </a>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Horario de atención: Lunes a Viernes 8:00 AM - 6:00 PM
                    </small>
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
            // Mostrar información detallada del error
            const now = new Date();
            document.getElementById('errorTime').textContent = now.toLocaleString('es-ES');
            
            // Generar IDs únicos
            const errorId = Math.random().toString(36).substr(2, 9).toUpperCase();
            const serverId = Math.floor(Math.random() * 10) + 1;
            
            document.getElementById('errorId').textContent = errorId;
            document.getElementById('serverId').textContent = serverId;
            document.getElementById('errorUrl').textContent = window.location.href;

            // Animación de entrada escalonada
            setTimeout(() => {
                $('.error-icon').addClass('explode');
            }, 500);

            setTimeout(() => {
                $('.error-code').addClass('flicker');
            }, 1000);

            setTimeout(() => {
                $('.troubleshooting-steps li').each(function(index) {
                    setTimeout(() => {
                        $(this).addClass('show');
                    }, index * 300);
                });
            }, 1500);

            setTimeout(() => {
                $('.status-item').each(function(index) {
                    setTimeout(() => {
                        $(this).addClass('show');
                    }, index * 200);
                });
            }, 2500);

            // Simular estado de sistema que cambia
            setInterval(updateSystemStatus, 10000);
        });

        // Función para actualizar estado del sistema
        function updateSystemStatus() {
            const statuses = ['green', 'yellow', 'red'];
            const statusTexts = [
                { name: 'Base de Datos', status: 'Funcionando correctamente', dot: 'green' },
                { name: 'API Services', status: 'Respuesta lenta', dot: 'yellow' },
                { name: 'Autenticación', status: 'Funcionando correctamente', dot: 'green' },
                { name: 'Cache System', status: 'En mantenimiento', dot: 'yellow' }
            ];

            // Simular cambios aleatorios en el estado
            statusTexts.forEach((item, index) => {
                if (Math.random() > 0.7) {
                    const statusItem = document.querySelectorAll('.status-item')[index];
                    const dot = statusItem.querySelector('.status-dot');
                    const text = statusItem.querySelector('small');
                    
                    // Cambiar estado
                    const newStatus = statuses[Math.floor(Math.random() * statuses.length)];
                    dot.className = `status-dot ${newStatus}`;
                    
                    if (newStatus === 'green') {
                        text.textContent = 'Funcionando correctamente';
                    } else if (newStatus === 'yellow') {
                        text.textContent = 'Atención requerida';
                    } else {
                        text.textContent = 'Problema detectado';
                    }
                }
            });
        }

        // Función para refrescar página
        function refreshPage() {
            Swal.fire({
                icon: 'question',
                title: 'Reintentar',
                text: '¿Desea recargar la página para intentar nuevamente?',
                showCancelButton: true,
                confirmButtonText: 'Sí, recargar',
                cancelButtonText: 'No, esperar',
                confirmButtonColor: '#667eea'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Recargando...',
                        text: 'Por favor espere mientras recargamos la página',
                        timer: 2000,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    }).then(() => {
                        window.location.reload();
                    });
                }
            });
        }

        // Función para abrir chat
        function openChat() {
            Swal.fire({
                icon: 'info',
                title: 'Chat en Vivo',
                html: `
                    <div class="text-start">
                        <p>Conectando con un agente de soporte...</p>
                        <div class="mt-3">
                            <div class="chat-message">
                                <strong>Agente:</strong> Hola, soy el agente de soporte #${Math.floor(Math.random() * 1000)}. ¿En qué puedo ayudarte?
                            </div>
                            <div class="mt-2">
                                <textarea class="form-control" rows="3" placeholder="Escriba su consulta aquí..." id="chatMessage"></textarea>
                            </div>
                        </div>
                    </div>
                `,
                showConfirmButton: true,
                confirmButtonText: 'Enviar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#667eea',
                width: '600px',
                preConfirm: () => {
                    const message = document.getElementById('chatMessage').value;
                    if (!message.trim()) {
                        Swal.showValidationMessage('Por favor escriba su consulta');
                        return false;
                    }
                    return { message: message };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Mensaje Enviado',
                        html: `
                            <div class="text-start">
                                <p>Su mensaje ha sido enviado exitosamente.</p>
                                <p><strong>Ticket ID:</strong> CHAT-${Date.now()}</p>
                                <p>Un agente de soporte le responderá en breve.</p>
                            </div>
                        `,
                        timer: 4000,
                        showConfirmButton: true,
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        }

        // Función para crear ticket
        function submitTicket() {
            Swal.fire({
                icon: 'warning',
                title: 'Crear Ticket de Soporte',
                html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <label for="ticketTitle" class="form-label">Título del problema:</label>
                            <input type="text" id="ticketTitle" class="form-control" placeholder="Ej: Error 500 al acceder a ventas">
                        </div>
                        <div class="mb-3">
                            <label for="ticketCategory" class="form-label">Categoría:</label>
                            <select id="ticketCategory" class="form-select">
                                <option value="">Seleccionar categoría...</option>
                                <option value="error-500">Error del servidor (500)</option>
                                <option value="database">Problema de base de datos</option>
                                <option value="performance">Rendimiento lento</option>
                                <option value="login">Problema de acceso</option>
                                <option value="feature">Funcionalidad específica</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="ticketDescription" class="form-label">Descripción detallada:</label>
                            <textarea id="ticketDescription" class="form-control" rows="4" placeholder="Describa paso a paso lo que estaba haciendo cuando ocurrió el error..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="ticketUrgency" class="form-label">Urgencia:</label>
                            <select id="ticketUrgency" class="form-select">
                                <option value="baja">Baja</option>
                                <option value="media" selected>Media</option>
                                <option value="alta">Alta</option>
                                <option value="critica">Crítica</option>
                            </select>
                        </div>
                        <div class="mt-3 p-3 bg-light rounded">
                            <strong>Información del sistema:</strong><br>
                            <small>
                                URL: ${window.location.href}<br>
                                Error: HTTP 500<br>
                                Navegador: ${navigator.userAgent.split(' ').slice(-1)[0]}<br>
                                Timestamp: ${new Date().toLocaleString('es-ES')}
                            </small>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Crear Ticket',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e74c3c',
                width: '700px',
                preConfirm: () => {
                    const title = document.getElementById('ticketTitle').value;
                    const category = document.getElementById('ticketCategory').value;
                    const description = document.getElementById('ticketDescription').value;
                    const urgency = document.getElementById('ticketUrgency').value;
                    
                    if (!title || !category || !description) {
                        Swal.showValidationMessage('Por favor complete todos los campos obligatorios');
                        return false;
                    }
                    
                    return { title, category, description, urgency };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Ticket Creado',
                        html: `
                            <div class="text-start">
                                <p>Su ticket de soporte ha sido creado exitosamente.</p>
                                <p><strong>Número de ticket:</strong> SIFANO-${Date.now()}</p>
                                <p><strong>Prioridad:</strong> ${result.value.urgency.toUpperCase()}</p>
                                <p>Recibirá actualizaciones por email y podrá rastrear el progreso en su panel de usuario.</p>
                                <p><strong>Tiempo estimado de respuesta:</strong> 2-4 horas hábiles</p>
                            </div>
                        `,
                        timer: 6000,
                        showConfirmButton: true,
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        }

        // Efectos de animación adicionales
        document.addEventListener('DOMContentLoaded', function() {
            // Efectos de hover en botones
            const buttons = document.querySelectorAll('.btn-error');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.02)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Efecto de typing en el mensaje de error
            const errorMessage = document.querySelector('.error-message');
            if (errorMessage) {
                const text = errorMessage.textContent;
                errorMessage.textContent = '';
                
                let i = 0;
                const typeWriter = () => {
                    if (i < text.length) {
                        errorMessage.textContent += text.charAt(i);
                        i++;
                        setTimeout(typeWriter, 25);
                    }
                };
                
                setTimeout(typeWriter, 2000);
            }

            // Auto-reload después de 5 minutos (opcional)
            setTimeout(() => {
                if (confirm('Han pasado 5 minutos. ¿Desea intentar recargar la página?')) {
                    window.location.reload();
                }
            }, 300000);
        });
    </script>
</body>
</html>