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
            --primary-color: #dc3545;
            --secondary-color: #6c757d;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
        }

        body {
            background-color: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .error-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
            width: 90%;
            border: 1px solid var(--border-color);
        }

        .error-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #fee;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }

        .error-icon i {
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .error-code {
            font-size: 4rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
            line-height: 1;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #212529;
            margin: 1rem 0;
        }

        .error-message {
            font-size: 1rem;
            color: var(--secondary-color);
            line-height: 1.5;
            margin-bottom: 2rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
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

        .help-info {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 1rem;
            margin-top: 2rem;
            border-left: 3px solid var(--primary-color);
        }

        .help-info h6 {
            color: #212529;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .help-info p {
            color: var(--secondary-color);
            margin: 0;
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .contact-link {
            color: var(--primary-color);
            text-decoration: none;
        }

        .contact-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .error-container {
                padding: 2rem;
                margin: 1rem;
            }

            .error-code {
                font-size: 3rem;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 200px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
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
            No tiene permisos para acceder a este recurso. 
            Verifique sus credenciales o contacte al administrador si necesita acceso.
        </p>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home"></i>
                Ir al Inicio
            </a>
            <button onclick="history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Regresar
            </button>
        </div>

        <!-- Help Information -->
        <div class="help-info">
            <h6><i class="fas fa-question-circle me-1"></i>¿Necesita ayuda?</h6>
            <p>
                Si considera que debería tener acceso a este recurso, 
                contacte al equipo de soporte a través de 
                <a href="mailto:soporte@sifano.com" class="contact-link">soporte@sifano.com</a>
            </p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>