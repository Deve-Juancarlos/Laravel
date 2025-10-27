<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Sistema SEDIM - Contabilidad')</title>
    
    <!-- Bootstrap 5.3.0 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6.4.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #3498db;
            --danger: #e74c3c;
            --success: #27ae60;
            --warning: #f39c12;
            --info: #17a2b8;
            --secondary: #6c757d;
            --dark: #343a40;
            --light: #f8f9fa;
            
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --bg-tertiary: #e9ecef;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --text-muted: #868e96;
            --border-color: #dee2e6;
            --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow-md: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --bg-tertiary: #3a3a3a;
            --text-primary: #ffffff;
            --text-secondary: #cccccc;
            --text-muted: #999999;
            --border-color: #4a4a4a;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        /* Header Styles */
        .header-bar {
            background: linear-gradient(135deg, var(--primary) 0%, #2980b9 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: var(--shadow-md);
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0.5rem 0 0 0;
        }
        
        .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }
        
        .breadcrumb-item a:hover {
            color: white;
        }
        
        .breadcrumb-item.active {
            color: rgba(255, 255, 255, 0.9);
        }
        
        /* Submenu Contable */
        .submenu-contable {
            background: var(--bg-primary);
            border-bottom: 1px solid var(--border-color);
            padding: 0;
        }
        
        .submenu-nav {
            display: flex;
            padding: 0;
            margin: 0;
            list-style: none;
        }
        
        .submenu-item {
            position: relative;
        }
        
        .submenu-link {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .submenu-link:hover,
        .submenu-link.active {
            color: var(--primary);
            background-color: var(--bg-secondary);
            border-bottom-color: var(--primary);
        }
        
        /* Content Area */
        .content-area {
            padding: 2rem;
            max-width: 100%;
            margin: 0 auto;
        }
        
        /* Cards */
        .card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        .card-header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        /* Buttons */
        .btn {
            border-radius: 0.5rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-1px);
        }
        
        /* Tables */
        .table {
            color: var(--text-primary);
        }
        
        .table th {
            background: var(--bg-secondary);
            border-color: var(--border-color);
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .table td {
            border-color: var(--border-color);
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background-color: var(--bg-secondary);
        }
        
        /* Theme Toggle */
        .theme-toggle {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.5rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .content-area {
                padding: 1rem;
            }
            
            .submenu-nav {
                flex-wrap: wrap;
            }
            
            .submenu-link {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body data-theme="light">
    <!-- Header Bar -->
    <header class="header-bar">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="header-title">
                        <i class="fas fa-calculator me-2"></i>
                        Módulo Contabilidad
                    </h1>
                    
                    <!-- Breadcrumbs -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            @if(isset($breadcrumbs) && is_array($breadcrumbs))
                                @foreach($breadcrumbs as $breadcrumb)
                                    @if($loop->last)
                                        <li class="breadcrumb-item active">{{ $breadcrumb }}</li>
                                    @else
                                        <li class="breadcrumb-item">
                                            @if(isset($breadcrumb['url']))
                                                <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['text'] }}</a>
                                            @else
                                                {{ $breadcrumb['text'] ?? $breadcrumb }}
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
                            @else
                                <li class="breadcrumb-item active">Dashboard</li>
                            @endif
                        </ol>
                    </nav>
                </div>
                
                <div class="col-auto">
                    <div class="d-flex align-items-center gap-3">
                        <!-- Theme Toggle -->
                        <button class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </button>
                        
                        <!-- Back to Main System -->
                        <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            Volver al Sistema
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Submenu Contable -->
    <nav class="submenu-contable">
        <div class="container-fluid">
            <ul class="submenu-nav">
                <li class="submenu-item">
                    <a href="{{ route('dashboard.contador') }}" class="submenu-link">
                        <i class="fas fa-chart-line me-2"></i>Dashboard
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="{{ route('contador.libro-diario.index') }}" class="submenu-link">
                        <i class="fas fa-book me-2"></i>Libro Diario
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="{{ route('contador.libros.mayor.index') }}" class="submenu-link">
                        <i class="fas fa-book-open me-2"></i>Libro Mayor
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="{{ route('contador.balance.index') }}" class="submenu-link">
                        <i class="fas fa-balance-scale me-2"></i>Balance
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="{{ route('contador.puc.index') }}" class="submenu-link">
                        <i class="fas fa-list me-2"></i>Plan de Cuentas
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="{{ route('contador.reportes.index') }}" class="submenu-link">
                        <i class="fas fa-chart-bar me-2"></i>Reportes
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="content-area">
        @yield('content')
    </main>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme Management
        function toggleTheme() {
            const body = document.body;
            const themeIcon = document.getElementById('theme-icon');
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            body.setAttribute('data-theme', newTheme);
            themeIcon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            
            // Save theme preference
            localStorage.setItem('theme', newTheme);
        }
        
        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            const body = document.body;
            const themeIcon = document.getElementById('theme-icon');
            
            body.setAttribute('data-theme', savedTheme);
            themeIcon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        });
        
        // Security Manager
        function SecurityManager() {
            let sessionTimeout = 30 * 60 * 1000; // 30 minutos
            let warningTimeout = 25 * 60 * 1000; // 25 minutos
            let timeoutId, warningId;
            
            const resetTimer = () => {
                clearTimeout(timeoutId);
                clearTimeout(warningId);
                
                warningId = setTimeout(() => {
                    if (confirm('Tu sesión está por expirar. ¿Deseas continuar?')) {
                        resetTimer();
                    } else {
                        window.location.href = '/logout';
                    }
                }, warningTimeout);
                
                timeoutId = setTimeout(() => {
                    window.location.href = '/logout';
                }, sessionTimeout);
            };
            
            // Eventos que reinician el timer
            ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
                document.addEventListener(event, resetTimer, true);
            });
            
            resetTimer();
        }
        
        SecurityManager();
    </script>
    
    @stack('scripts')
</body>
</html>