<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIFANO - Sistema Farmacéutico')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 10px;
        }

        .sidebar-brand {
            padding: 1.5rem;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .sidebar-brand h4 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
        }

        .sidebar-brand small {
            font-size: 0.75rem;
            opacity: 0.8;
            display: block;
            margin-top: 0.25rem;
        }

        .sidebar-nav {
            padding: 0.5rem 0;
        }

        .nav-section {
            padding: 0.75rem 1.25rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.6);
            font-weight: 700;
            margin-top: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .nav-section:first-child {
            margin-top: 0.5rem;
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            font-size: 0.9rem;
            position: relative;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #60a5fa;
            padding-left: 1.5rem;
        }

        .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: #60a5fa;
            font-weight: 600;
        }

        .nav-link i {
            width: 24px;
            margin-right: 0.75rem;
            font-size: 1rem;
            text-align: center;
        }

        /* Submenu */
        .nav-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: rgba(0,0,0,0.2);
        }

        .nav-submenu.active {
            max-height: 500px;
        }

        .nav-submenu .nav-link {
            padding-left: 3.5rem;
            font-size: 0.85rem;
        }

        .nav-link.has-submenu::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 1.25rem;
            transition: transform 0.3s ease;
        }

        .nav-link.has-submenu.active::after {
            transform: rotate(180deg);
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Topbar */
        .topbar {
            background: white;
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .topbar-search {
            flex: 1;
            max-width: 500px;
            margin: 0 2rem;
        }

        .topbar-search .form-control {
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
        }

        .topbar-search .input-group-text {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            z-index: 5;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
            font-size: 1.25rem;
            color: #6b7280;
            transition: color 0.2s;
        }

        .notification-bell:hover {
            color: #1e40af;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border: 2px solid white;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: background 0.2s;
        }

        .user-menu:hover {
            background: #f3f4f6;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: 0 2px 8px rgba(30, 64, 175, 0.3);
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.2;
        }

        .user-role {
            font-size: 0.75rem;
            color: #6b7280;
            line-height: 1.2;
        }

        /* Content Area */
        .content-area {
            padding: 1.5rem 2rem;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0 0 1rem 0;
            font-size: 0.875rem;
        }

        .breadcrumb-item a {
            color: #6b7280;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: #1e40af;
        }

        .breadcrumb-item.active {
            color: #1f2937;
            font-weight: 500;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #f3f4f6;
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: #1f2937;
            border-radius: 12px 12px 0 0 !important;
        }

        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .alert-warning {
            background: #fffbeb;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }

        .alert-info {
            background: #eff6ff;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }

        /* Dropdown Menu */
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 10px;
            padding: 0.5rem;
            margin-top: 0.5rem;
        }

        .dropdown-item {
            border-radius: 6px;
            padding: 0.6rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background: #f3f4f6;
            color: #1e40af;
        }

        .dropdown-item i {
            width: 20px;
        }

        .dropdown-divider {
            margin: 0.5rem 0;
            opacity: 0.1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .topbar-search {
                display: none;
            }

            .content-area {
                padding: 1rem;
            }

            .user-info {
                display: none;
            }
        }
    </style>

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
            <h4><i class="fas fa-prescription-bottle-alt me-2"></i>SIFANO</h4>
            <small>Distribuidora de Fármacos</small>
        </div>

        <nav class="sidebar-nav">
            @yield('sidebar-menu')
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <button class="btn btn-link d-md-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <div class="topbar-search d-none d-md-block position-relative">
                <span class="input-group-text">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control" placeholder="Buscar productos, clientes, facturas...">
            </div>

            <div class="topbar-right">
                <!-- Notificaciones -->
                <div class="notification-bell" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
                <ul class="dropdown-menu dropdown-menu-end" style="width: 320px;">
                    <li class="dropdown-header d-flex justify-content-between align-items-center">
                        <strong>Notificaciones</strong>
                        <span class="badge bg-primary">3 nuevas</span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item py-2" href="#">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            <small>5 facturas vencidas</small>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="#">
                            <i class="fas fa-box text-danger me-2"></i>
                            <small>8 productos con stock bajo</small>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="#">
                            <i class="fas fa-calendar text-info me-2"></i>
                            <small>12 productos por vencer</small>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li class="text-center py-2">
                        <a href="#" class="text-primary text-decoration-none" style="font-size: 0.875rem;">Ver todas</a>
                    </li>
                </ul>

                <!-- Usuario -->
                <div class="user-menu" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        {{ strtoupper(substr(session('usuario_logged') ?? 'U', 0, 1)) }}
                    </div>
                    <div class="user-info d-none d-md-block">
                        <div class="user-name">{{ Auth::user()->usuario ?? 'Usuario' }}</div>
                        <div class="user-role">{{ Auth::user()->tipousuario ?? 'Sistema' }}</div>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size: 0.75rem; color: #6b7280;"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Mi Perfil</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-question-circle me-2"></i>Ayuda</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <!-- Page Content -->
        <div class="content-area">
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

            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

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
        // Toggle Sidebar Mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Cerrar sidebar al hacer click fuera (móvil)
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Submenu toggle
        document.querySelectorAll('.nav-link.has-submenu').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
                const submenu = this.nextElementSibling;
                if (submenu) {
                    submenu.classList.toggle('active');
                }
            });
        });

        // Loading Functions
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('active');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('active');
        }

        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Confirmación de logout
        document.getElementById('logout-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Cerrar sesión?',
                text: "¿Está seguro que desea salir del sistema?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1e40af',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>