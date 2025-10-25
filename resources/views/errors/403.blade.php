<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - SIFANO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --warning-gradient: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --shadow-elegant: 0 20px 40px rgba(0, 0, 0, 0.1);
            --border-radius: 15px;
        }

        body {
            background: var(--danger-gradient);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animación de fondo */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>') repeat;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(1deg); }
            66% { transform: translateY(-10px) rotate(-1deg); }
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
            max-width: 600px;
            width: 90%;
            animation: slideIn 1s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .error-icon {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--danger-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 20px 40px rgba(250, 112, 154, 0.3);
            animation: pulse 3s ease-in-out infinite;
            position: relative;
        }

        .error-icon::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border-radius: 50%;
            background: var(--danger-gradient);
            opacity: 0.3;
            animation: ripple 2s ease-out infinite;
        }

        @keyframes ripple {
            0% {
                transform: scale(1);
                opacity: 0.3;
            }
            100% {
                transform: scale(1.3);
                opacity: 0;
            }
        }

        .error-icon i {
            font-size: 3.5rem;
            color: white;
            animation: shake 2s ease-in-out infinite;
        }

        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-5deg); }
            75% { transform: rotate(5deg); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .error-code {
            font-size: 8rem;
            font-weight: 900;
            background: var(--danger-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            line-height: 1;
            text-shadow: 0 5px 15px rgba(250, 112, 154, 0.3);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                filter: drop-shadow(0 0 5px rgba(250, 112, 154, 0.5));
            }
            to {
                filter: drop-shadow(0 0 20px rgba(250, 112, 154, 0.8));
            }
        }

        .error-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 1rem 0;
        }

        .error-message {
            font-size: 1.2rem;
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .error-details {
            background: rgba(250, 112, 154, 0.1);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin: 2rem 0;
            border-left: 4px solid #fa709a;
        }

        .error-details h5 {
            color: #e74c3c;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .error-details p {
            color: #495057;
            margin-bottom: 0;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 3rem;
        }

        .btn-error {
            padding: 1rem 2rem;
            border-radius: 50px;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
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

        .btn-outline-custom {
            background: transparent;
            border: 2px solid #e74c3c;
            color: #e74c3c;
        }

        .btn-outline-custom:hover {
            background: var(--danger-gradient);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(250, 112, 154, 0.4);
        }

        .security-info {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .security-info h6 {
            color: #e74c3c;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .security-info ul {
            text-align: left;
            margin: 0;
            padding-left: 1.5rem;
        }

        .security-info li {
            color: #495057;
            margin-bottom: 0.5rem;
            line-height: 1.5;
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
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: floatElement 8s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 80%;
            animation-delay: 4s;
        }

        .floating-element:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 40%;
            left: 5%;
            animation-delay: 6s;
        }

        @keyframes floatElement {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg);
                opacity: 0.3;
            }
            50% { 
                transform: translateY(-30px) rotate(180deg);
                opacity: 0.6;
            }
        }

        .contact-info {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid rgba(231, 76, 60, 0.1);
        }

        .contact-info h6 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .contact-details {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c757d;
            font-size: 0.95rem;
        }

        .contact-item i {
            color: #e74c3c;
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
                font-size: 1.5rem;
            }

            .error-message {
                font-size: 1rem;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-error {
                width: 100%;
                max-width: 250px;
                justify-content: center;
            }

            .contact-details {
                flex-direction: column;
                gap: 1rem;
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
        <div class="floating-element"></div>
    </div>

    <div class="container">
        <div class="error-container">
            <!-- Error Icon -->
            <div class="error-icon">
                <i class="fas fa-lock"></i>
            </div>

            <!-- Error Code -->
            <h1 class="error-code">403</h1>

            <!-- Error Title -->
            <h2 class="error-title">Acceso Denegado</h2>

            <!-- Error Message -->
            <p class="error-message">
                Lo sentimos, no tiene los permisos necesarios para acceder a este recurso. 
                Su solicitud ha sido bloqueada por el sistema de seguridad.
            </p>

            <!-- Error Details -->
            <div class="error-details">
                <h5><i class="fas fa-info-circle me-2"></i>Detalles del Error</h5>
                <p>
                    <strong>Código de Error:</strong> HTTP 403 Forbidden<br>
                    <strong>Motivo:</strong> Acceso no autorizado<br>
                    <strong>Timestamp:</strong> <span id="errorTime"></span><br>
                    <strong>Request ID:</strong> ERR-403-<span id="errorId"></span>
                </p>
            </div>

            <!-- Security Information -->
            <div class="security-info">
                <h6><i class="fas fa-shield-alt me-2"></i>Información de Seguridad</h6>
                <ul>
                    <li>Este error protege el sistema contra accesos no autorizados</li>
                    <li>Sus credenciales pueden haber expirado o ser incorrectas</li>
                    <li>Su rol actual no tiene permisos para este recurso</li>
                    <li>Contacte al administrador si necesita acceso especial</li>
                </ul>
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
                <a href="{{ url('/login') }}" class="btn-error btn-outline-custom">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </a>
            </div>

            <!-- Contact Information -->
            <div class="contact-info">
                <h6><i class="fas fa-headset me-2"></i>¿Necesita Ayuda?</h6>
                <div class="contact-details">
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+51 1 234-5678</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>soporte@sifano.com</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>Lun-Vie 8:00 AM - 6:00 PM</span>
                    </div>
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
            // Mostrar timestamp actual
            const now = new Date();
            document.getElementById('errorTime').textContent = now.toLocaleString('es-ES');
            
            // Generar ID de error único
            const errorId = Math.random().toString(36).substr(2, 9).toUpperCase();
            document.getElementById('errorId').textContent = errorId;

            // Animación de entrada escalonada
            setTimeout(() => {
                $('.error-icon').addClass('pulse');
            }, 500);

            setTimeout(() => {
                $('.error-code').addClass('glow');
            }, 1000);

            setTimeout(() => {
                $('.action-buttons .btn-error').each(function(index) {
                    setTimeout(() => {
                        $(this).addClass('show');
                    }, index * 200);
                });
            }, 1500);
        });

        // Función para reportar problema
        function reportProblem() {
            Swal.fire({
                icon: 'warning',
                title: 'Reportar Problema',
                html: `
                    <div class="text-start">
                        <p><strong>ID de Error:</strong> ERR-403-${document.getElementById('errorId').textContent}</p>
                        <p><strong>Timestamp:</strong> ${document.getElementById('errorTime').textContent}</p>
                        <p><strong>URL:</strong> ${window.location.href}</p>
                        <div class="mt-3">
                            <label for="problemDescription" class="form-label">Descripción del problema:</label>
                            <textarea id="problemDescription" class="form-control" rows="3" placeholder="Describa lo que estaba tratando de hacer..."></textarea>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Enviar Reporte',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e74c3c',
                width: '600px',
                preConfirm: () => {
                    const description = document.getElementById('problemDescription').value;
                    if (!description) {
                        Swal.showValidationMessage('Por favor describa el problema');
                        return false;
                    }
                    return { description: description };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reporte Enviado',
                        text: 'Hemos recibido su reporte y lo investigaremos a la brevedad.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            });
        }

        // Función para solicitar acceso
        function requestAccess() {
            Swal.fire({
                icon: 'question',
                title: 'Solicitar Acceso',
                html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <label for="accessResource" class="form-label">Recurso solicitado:</label>
                            <input type="text" id="accessResource" class="form-control" value="${window.location.pathname}" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="accessJustification" class="form-label">Justificación:</label>
                            <textarea id="accessJustification" class="form-control" rows="3" placeholder="Explique por qué necesita acceso a este recurso..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="accessUrgency" class="form-label">Urgencia:</label>
                            <select id="accessUrgency" class="form-select">
                                <option value="baja">Baja</option>
                                <option value="media" selected>Media</option>
                                <option value="alta">Alta</option>
                                <option value="critica">Crítica</option>
                            </select>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Enviar Solicitud',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#667eea',
                width: '600px',
                preConfirm: () => {
                    const justification = document.getElementById('accessJustification').value;
                    const urgency = document.getElementById('accessUrgency').value;
                    
                    if (!justification) {
                        Swal.showValidationMessage('Por favor proporcione una justificación');
                        return false;
                    }
                    
                    return { 
                        justification: justification,
                        urgency: urgency,
                        resource: document.getElementById('accessResource').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Solicitud Enviada',
                        html: `
                            <div class="text-start">
                                <p>Su solicitud de acceso ha sido enviada exitosamente.</p>
                                <p><strong>Número de ticket:</strong> SIFANO-${Date.now()}</p>
                                <p>Recibirá una respuesta por email dentro de las próximas 24 horas.</p>
                            </div>
                        `,
                        timer: 5000,
                        showConfirmButton: true,
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        }

        // Agregar eventos a los botones
        document.addEventListener('DOMContentLoaded', function() {
            // Efectos de hover adicionales
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
                        setTimeout(typeWriter, 30);
                    }
                };
                
                setTimeout(typeWriter, 2000);
            }
        });
    </script>
</body>
</html>