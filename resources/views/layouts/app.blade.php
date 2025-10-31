<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Título dinámico, con SEIMCORP como marca principal -->
    <title>@yield('title', 'Dashboard') | SEIMCORP</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        /* (Tu CSS personalizado va aquí - es excelente, no lo he modificado) */
        * {
            margin: 0; padding: 0; box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        /* Sidebar */
        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh; width: 260px;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white; overflow-y: auto; z-index: 1000; transition: all 0.3s ease;
        }
        .sidebar-brand {
            padding: 1.5rem; background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center;
        }
        .sidebar-brand h4 {
            margin: 0; font-size: 1.5rem; font-weight: 700;
        }
        .sidebar-brand small {
            font-size: 0.75rem; opacity: 0.8;
        }
        .sidebar-nav {
            padding: 1rem 0;
        }
        .nav-section {
            padding: 0.75rem 1.5rem; font-size: 0.75rem; text-transform: uppercase;
            letter-spacing: 1px; color: rgba(255,255,255,0.5); font-weight: 600;
            margin-top: 1rem;
        }
        .nav-link {
            display: flex; align-items: center; padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.8); text-decoration: none;
            transition: all 0.2s ease; border-left: 3px solid transparent;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.1); color: white; border-left-color: #3498db;
        }
        /* Clase 'active' para el enlace actual */
        .nav-link.active {
            background: rgba(255,255,255,0.15); color: white;
            border-left-color: #3498db; font-weight: 600;
        }
        .nav-link i {
            width: 20px; margin-right: 0.75rem; font-size: 1rem;
        }
        /* Main Content */
        .main-content {
            margin-left: 260px; min-height: 100vh; transition: all 0.3s ease;
        }
        /* Topbar */
        .topbar {
            background: white; padding: 1rem 1.5rem; border-bottom: 1px solid #e0e0e0;
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 999;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .topbar-search {
            flex: 1; max-width: 500px; margin: 0 2rem;
        }
        .topbar-right {
            display: flex; align-items: center; gap: 1rem;
        }
        .notification-bell {
            position: relative; cursor: pointer; font-size: 1.25rem; color: #666;
        }
        .notification-badge {
            position: absolute; top: -5px; right: -5px; background: #dc3545;
            color: white; border-radius: 50%; width: 18px; height: 18px;
            font-size: 0.65rem; display: flex; align-items: center; justify-content: center;
        }
        .user-menu {
            display: flex; align-items: center; gap: 0.5rem; cursor: pointer;
            padding: 0.5rem 1rem; border-radius: 8px; transition: background 0.2s;
        }
        .user-menu:hover {
            background: #f8f9fa;
        }
        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600;
        }
        /* Content Area */
        .content-area {
            padding: 2rem;
        }
        /* Cards */
        .card {
            border: none; border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        .card-header {
            background: white; border-bottom: 1px solid #f0f0f0;
            padding: 1.25rem 1.5rem; font-weight: 600; color: #333;
        }
        /* Breadcrumb */
        .breadcrumb {
            background: transparent; padding: 0; margin: 0.5rem 0 0 0;
            font-size: 0.875rem;
        }
        /* Loading Overlay */
        .loading-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7); z-index: 9999;
            justify-content: center; align-items: center;
        }
        .loading-overlay.active { display: flex; }
        .spinner {
            width: 50px; height: 50px; border: 5px solid rgba(255,255,255,0.3);
            border-top-color: white; border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar-search { display: none; }
        }
    </style>

    <!-- Estilos específicos de la página -->
    @stack('styles')
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <!-- Usamos SEIMCORP y SIFANO -->
            <h4><i class="fas fa-prescription-bottle-alt me-2"></i>SEIMCORP</h4>
            <small>Distribuidora de Farmacos</small>
        </div>

        <nav class="sidebar-nav">
           
            @include('layouts.partials._sidebar')
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <button class="btn btn-link d-md-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <!-- ... (Tu barra de búsqueda está bien) ... -->
            <div class="topbar-search d-none d-md-block">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" placeholder="Buscar productos, clientes...">
                </div>
            </div>

            <div class="topbar-right">
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <!--
                        MEJORA N°2:
                        Hacemos el badge de notificación dinámico.
                        Este $notification_count vendrá de un ViewComposer (configuración avanzada)
                        o lo puedes pasar desde el controlador base.
                    -->
                    @if(isset($notification_count) && $notification_count > 0)
                        <span class="notification-badge">{{ $notification_count }}</span>
                    @endif
                </div>

                <div class="user-menu dropdown">
                    <!-- Tu menú de usuario está perfecto, lo dejamos tal cual -->
                    <div data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            <!-- Tu lógica para el avatar es robusta, la dejamos -->
                            {{ strtoupper(substr(Auth::user()->usuario ?? Auth::user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="d-none d-md-block ms-2">
                            <div style="font-size: 0.875rem; font-weight: 600;">
                                {{ Auth::user()->usuario ?? Auth::user()->name ?? 'Usuario' }}
                            </div>
                            <div style="font-size: 0.75rem; color: #666;">
                                {{ Auth::user()->tipousuario ?? 'Usuario' }}
                            </div>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <!-- Tu formulario de logout es correcto -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="content-area">
            
            <!-- Tu manejo de alertas de sesión es perfecto -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- Título de la Página y Breadcrumbs -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 mb-0 text-dark">@yield('page-title', 'Página')</h2>
                <!-- Breadcrumbs (opcional, puedes llenarlo desde las vistas) -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.contador') }}">Inicio</a></li>
                        @yield('breadcrumbs')
                    </ol>
                </nav>
            </div>

            <!-- Aquí se cargará el contenido principal de la página -->
            @yield('content')
        </div>
    </main>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Tu JS básico está perfecto
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('active');
        }
        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('active');
        }

        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>

    <!-- Scripts específicos de la página -->
    @stack('scripts')
</body>
</html>
