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
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --warning-gradient: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --shadow-elegant: 0 20px 40px rgba(0, 0, 0, 0.1);
            --border-radius: 15px;
        }

        body {
            background: var(--primary-gradient);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animación de fondo con partículas */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>') repeat;
            animation: float 15s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-10px) rotate(1deg); }
            66% { transform: translateY(-5px) rotate(-1deg); }
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
            animation: slideIn 1.2s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.8) rotate(-5deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1) rotate(0deg);
            }
        }

        .error-icon {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: var(--warning-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 20px 40px rgba(255, 234, 167, 0.4);
            animation: bounce 2s ease-in-out infinite;
            position: relative;
        }

        .error-icon::before {
            content: '';
            position: absolute;
            top: -15px;
            left: -15px;
            right: -15px;
            bottom: -15px;
            border-radius: 50%;
            background: var(--warning-gradient);
            opacity: 0.2;
            animation: pulse 3s ease-out infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.2;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.4;
            }
            100% {
                transform: scale(1);
                opacity: 0.2;
            }
        }

        .error-icon i {
            font-size: 4rem;
            color: #2c3e50;
            animation: wobble 3s ease-in-out infinite;
        }

        @keyframes wobble {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-3deg); }
            75% { transform: rotate(3deg); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .error-code {
            font-size: 9rem;
            font-weight: 900;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            line-height: 1;
            text-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            animation: glow 3s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                filter: drop-shadow(0 0 5px rgba(102, 126, 234, 0.5));
            }
            to {
                filter: drop-shadow(0 0 25px rgba(102, 126, 234, 0.8));
            }
        }

        .error-title {
            font-size: 2.2rem;
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

        .helpful-suggestions {
            background: rgba(102, 126, 234, 0.1);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin: 2rem 0;
            border-left: 4px solid #667eea;
        }

        .helpful-suggestions h5 {
            color: #667eea;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .suggestion-list {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }

        .suggestion-list li {
            color: #495057;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .suggestion-list li:hover {
            transform: translateX(10px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
        }

        .suggestion-list li i {
            color: #667eea;
            font-size: 1.2rem;
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

        .btn-success-custom {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 5px 15px rgba(67, 233, 123, 0.3);
        }

        .btn-success-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(67, 233, 123, 0.4);
            color: white;
        }

        .btn-outline-custom {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
        }

        .btn-outline-custom:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }

        .search-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .search-section h6 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .search-form {
            display: flex;
            gap: 0.5rem;
            max-width: 400px;
            margin: 0 auto;
        }

        .search-input {
            flex: 1;
            padding: 0.8rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }

        .search-btn {
            padding: 0.8rem 1.5rem;
            background: var(--primary-gradient);
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .popular-links {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid rgba(102, 126, 234, 0.1);
        }

        .popular-links h6 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .popular-link {
            padding: 1rem;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 10px;
            text-decoration: none;
            color: #667eea;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .popular-link:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
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
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            animation: floatElement 10s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            width: 60px;
            height: 60px;
            top: 10%;
            left: 15%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 80px;
            height: 80px;
            top: 60%;
            right: 15%;
            animation-delay: 3s;
        }

        .floating-element:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 15%;
            left: 10%;
            animation-delay: 6s;
        }

        .floating-element:nth-child(4) {
            width: 50px;
            height: 50px;
            top: 30%;
            right: 20%;
            animation-delay: 9s;
        }

        @keyframes floatElement {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg) scale(1);
                opacity: 0.3;
            }
            50% { 
                transform: translateY(-40px) rotate(180deg) scale(1.2);
                opacity: 0.7;
            }
        }

        .error-tips {
            background: var(--info-gradient);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }

        .error-tips h6 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .error-tips ul {
            margin: 0;
            padding-left: 1.5rem;
            color: #495057;
        }

        .error-tips li {
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .error-container {
                padding: 2rem;
                margin: 1rem;
            }

            .error-code {
                font-size: 7rem;
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

            .search-form {
                flex-direction: column;
            }

            .links-grid {
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
        <div class="floating-element"></div>
    </div>

    <div class="container">
        <div class="error-container">
            <!-- Error Icon -->
            <div class="error-icon">
                <i class="fas fa-search"></i>
            </div>

            <!-- Error Code -->
            <h1 class="error-code">404</h1>

            <!-- Error Title -->
            <h2 class="error-title">¡Página No Encontrada!</h2>

            <!-- Error Message -->
            <p class="error-message">
                La página que está buscando no existe, se ha movido o se ha eliminado temporalmente. 
                No se preocupe, le ayudaremos a encontrar lo que necesita.
            </p>

            <!-- Helpful Suggestions -->
            <div class="helpful-suggestions">
                <h5><i class="fas fa-lightbulb me-2"></i>Sugerencias Útiles</h5>
                <ul class="suggestion-list">
                    <li>
                        <i class="fas fa-check-circle"></i>
                        Verifique que la URL esté escrita correctamente
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        Use la barra de navegación para explorar el sitio
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        Regrese a la página principal y busque desde ahí
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        Contacte al soporte si persiste el problema
                    </li>
                </ul>
            </div>

            <!-- Search Section -->
            <div class="search-section">
                <h6><i class="fas fa-search me-2"></i>Buscar en el Sitio</h6>
                <form class="search-form" onsubmit="performSearch(event)">
                    <input type="text" class="search-input" placeholder="Buscar productos, servicios, información..." id="searchInput">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ url('/') }}" class="btn-error btn-primary-custom">
                    <i class="fas fa-home"></i>
                    Ir al Inicio
                </a>
                <button onclick="history.back()" class="btn-error btn-success-custom">
                    <i class="fas fa-arrow-left"></i>
                    Página Anterior
                </button>
                <a href="{{ url('/dashboard') }}" class="btn-error btn-outline-custom">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="{{ url('/contacto') }}" class="btn-error btn-outline-custom">
                    <i class="fas fa-envelope"></i>
                    Contactar Soporte
                </a>
            </div>

            <!-- Popular Links -->
            <div class="popular-links">
                <h6><i class="fas fa-star me-2"></i>Enlaces Populares</h6>
                <div class="links-grid">
                    <a href="{{ url('/ventas') }}" class="popular-link">
                        <i class="fas fa-shopping-cart"></i>
                        Ventas
                    </a>
                    <a href="{{ url('/clientes') }}" class="popular-link">
                        <i class="fas fa-users"></i>
                        Clientes
                    </a>
                    <a href="{{ url('/inventario') }}" class="popular-link">
                        <i class="fas fa-boxes"></i>
                        Inventario
                    </a>
                    <a href="{{ url('/reportes') }}" class="popular-link">
                        <i class="fas fa-chart-bar"></i>
                        Reportes
                    </a>
                    <a href="{{ url('/farmacias') }}" class="popular-link">
                        <i class="fas fa-hospital"></i>
                        Farmacias
                    </a>
                    <a href="{{ url('/configuracion') }}" class="popular-link">
                        <i class="fas fa-cog"></i>
                        Configuración
                    </a>
                </div>
            </div>

            <!-- Error Tips -->
            <div class="error-tips">
                <h6><i class="fas fa-info-circle me-2"></i>¿Por Qué Aparece Este Error?</h6>
                <ul>
                    <li>La URL puede haber cambiado debido a una actualización del sitio</li>
                    <li>El enlace puede estar desactualizado o ser incorrecto</li>
                    <li>El contenido puede haberse movido a una nueva ubicación</li>
                    <li>Puede haber un error temporal en el servidor</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Animación de entrada escalonada
            setTimeout(() => {
                $('.error-icon').addClass('bounce');
            }, 500);

            setTimeout(() => {
                $('.error-code').addClass('glow');
            }, 1000);

            setTimeout(() => {
                $('.suggestion-list li').each(function(index) {
                    setTimeout(() => {
                        $(this).addClass('show');
                    }, index * 200);
                });
            }, 1500);

            setTimeout(() => {
                $('.popular-link').each(function(index) {
                    setTimeout(() => {
                        $(this).addClass('show');
                    }, index * 100);
                });
            }, 2000);

            // Efecto de focus en el input de búsqueda
            $('#searchInput').on('focus', function() {
                $(this).parent().addClass('focused');
            });

            $('#searchInput').on('blur', function() {
                $(this).parent().removeClass('focused');
            });
        });

        // Función para realizar búsqueda
        function performSearch(event) {
            event.preventDefault();
            
            const searchTerm = document.getElementById('searchInput').value.trim();
            
            if (!searchTerm) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Búsqueda vacía',
                    text: 'Por favor ingrese un término de búsqueda',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                return;
            }

            // Simular búsqueda
            Swal.fire({
                icon: 'info',
                title: 'Buscando...',
                html: `<strong>Término:</strong> "${searchTerm}"<br>Analizando en la base de datos...`,
                timer: 2000,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading()
                }
            }).then(() => {
                // Simular resultados de búsqueda
                const mockResults = [
                    'Paracetamol 500mg',
                    'Ibuprofeno 400mg',
                    'Omeprazol 20mg',
                    'Vitaminas',
                    'Cuidado personal',
                    'Suplementos'
                ];
                
                const filteredResults = mockResults.filter(item => 
                    item.toLowerCase().includes(searchTerm.toLowerCase())
                );

                if (filteredResults.length > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Resultados Encontrados',
                        html: `
                            <div class="text-start">
                                <p>Se encontraron ${filteredResults.length} resultados para "${searchTerm}":</p>
                                <ul style="text-align: left;">
                                    ${filteredResults.map(result => `<li>${result}</li>`).join('')}
                                </ul>
                            </div>
                        `,
                        showConfirmButton: true,
                        confirmButtonText: 'Ver Resultados',
                        width: '600px'
                    });
                } else {
                    Swal.fire({
                        icon: 'question',
                        title: 'Sin Resultados',
                        html: `
                            <div class="text-start">
                                <p>No se encontraron resultados para "${searchTerm}".</p>
                                <p><strong>Sugerencias:</strong></p>
                                <ul style="text-align: left;">
                                    <li>Verifique la ortografía</li>
                                    <li>Intente con términos más generales</li>
                                    <li>Use sinónimos</li>
                                </ul>
                            </div>
                        `,
                        showConfirmButton: true,
                        confirmButtonText: 'Entendido',
                        width: '600px'
                    });
                }
            });
        }

        // Función para reportar enlace roto
        function reportBrokenLink() {
            Swal.fire({
                icon: 'warning',
                title: 'Reportar Enlace Roto',
                html: `
                    <div class="text-start">
                        <p><strong>URL problemática:</strong> ${window.location.href}</p>
                        <p><strong>Timestamp:</strong> ${new Date().toLocaleString('es-ES')}</p>
                        <div class="mt-3">
                            <label for="linkDescription" class="form-label">¿Qué esperaba encontrar en esta página?</label>
                            <textarea id="linkDescription" class="form-control" rows="3" placeholder="Describa el contenido que esperaba encontrar..."></textarea>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Enviar Reporte',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#667eea',
                width: '600px',
                preConfirm: () => {
                    const description = document.getElementById('linkDescription').value;
                    if (!description) {
                        Swal.showValidationMessage('Por favor proporcione una descripción');
                        return false;
                    }
                    return { description: description };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reporte Enviado',
                        html: `
                            <div class="text-start">
                                <p>Gracias por reportar este problema.</p>
                                <p><strong>Número de ticket:</strong> 404-${Date.now()}</p>
                                <p>Nuestro equipo investigará y solucionará este enlace roto.</p>
                            </div>
                        `,
                        timer: 4000,
                        showConfirmButton: true,
                        confirmButtonText: 'Gracias'
                    });
                }
            });
        }

        // Efectos de animación adicionales
        document.addEventListener('DOMContentLoaded', function() {
            // Efectos de hover en enlaces populares
            const popularLinks = document.querySelectorAll('.popular-link');
            popularLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.05)';
                });
                
                link.addEventListener('mouseleave', function() {
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
                        setTimeout(typeWriter, 20);
                    }
                };
                
                setTimeout(typeWriter, 1500);
            }

            // Botón flotante para reportar enlace roto
            const reportButton = document.createElement('button');
            reportButton.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Reportar Enlace';
            reportButton.className = 'btn btn-warning btn-lg position-fixed';
            reportButton.style.cssText = `
                bottom: 20px;
                right: 20px;
                border-radius: 50px;
                z-index: 1000;
                box-shadow: 0 10px 30px rgba(255, 234, 167, 0.4);
                animation: slideInRight 1s ease-out 3s both;
            `;
            reportButton.onclick = reportBrokenLink;
            
            document.body.appendChild(reportButton);

            // Agregar animación de slideInRight
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        opacity: 0;
                        transform: translateX(100px);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }
            `;
            document.head.appendChild(style);
        });

        // Función para auto-completar búsqueda
        const popularSearches = [
            'Paracetamol', 'Ibuprofeno', 'Omeprazol', 'Vitaminas',
            'Cuidado personal', 'Medicamentos', 'Suplementos',
            'Insumos médicos', 'Bebés', 'Cosméticos'
        ];

        $('#searchInput').on('input', function() {
            const value = $(this).val().toLowerCase();
            const matches = popularSearches.filter(search => 
                search.toLowerCase().includes(value)
            );
            
            // Aquí se podría mostrar sugerencias auto-completadas
            if (value.length > 2 && matches.length > 0) {
                console.log('Sugerencias:', matches.slice(0, 5));
            }
        });
    </script>
</body>
</html>