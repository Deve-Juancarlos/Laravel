<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIFANO') }} - @yield('title', 'Sistema de Gestión Farmacéutica')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--light-color);
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .sidebar.collapsed {
            margin-left: -250px;
        }

        .sidebar-brand {
            padding: 1.5rem;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .sidebar-brand img {
            height: 40px;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin: 0.25rem 0;
        }

        .nav-link {
            color: rgba(255,255,255,0.9);
            padding: 0.75rem 1.5rem;
            border-radius: 0;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: white;
            transform: translateX(5px);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 0.75rem;
        }

        .submenu {
            background: rgba(0,0,0,0.2);
            padding-left: 1rem;
        }

        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        .topbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .content-wrapper {
            padding: 2rem;
            min-height: calc(100vh - 120px);
        }

        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            border-radius: 0.75rem 0.75rem 0 0 !important;
            font-weight: 600;
        }

        .btn {
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        .alert {
            border-radius: 0.75rem;
            border: none;
        }

        .table {
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .table thead th {
            background: var(--dark-color);
            color: white;
            border: none;
            font-weight: 600;
        }

        .form-control {
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-dropdown {
            position: relative;
        }

        .user-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            min-width: 200px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .user-dropdown:hover .user-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--dark-color);
            font-size: 1.25rem;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .sidebar-toggle:hover {
            background: var(--light-color);
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
                position: fixed;
                z-index: 1000;
                width: 250px;
            }

            .sidebar.show {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
            }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .spinner-border {
            color: var(--primary-color);
        }

        /* Notificaciones */
        .notification-item {
            border-left: 4px solid var(--primary-color);
            transition: all 0.2s ease;
        }

        .notification-item:hover {
            background: var(--light-color);
            border-left-color: var(--success-color);
        }

        .notification-item.critical {
            border-left-color: var(--danger-color);
        }

        .notification-item.warning {
            border-left-color: var(--warning-color);
        }

        .notification-item.info {
            border-left-color: var(--info-color);
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="sidebar col-md-3 col-lg-2 d-md-block" id="sidebar">
                <div class="position-sticky sidebar-brand">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-pills text-white me-2" style="font-size: 1.5rem;"></i>
                        <div>
                            <h5 class="text-white mb-0 fw-bold">SIFANO</h5>
                            <small class="text-white-50">Sistema Farmacéutico</small>
                        </div>
                    </div>
                </div>

                <ul class="sidebar-nav nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </li>

                    <!-- Facturación -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('facturas*') ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#facturacionMenu">
                            <i class="fas fa-receipt"></i>
                            Facturación
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('facturas*') ? 'show' : '' }}" id="facturacionMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('facturas.index') ? 'active' : '' }}" href="{{ route('facturas.index') }}">
                                        <i class="fas fa-list"></i>
                                        Lista de Facturas
                                    </a>
                                </li>
                                @can('create', App\Models\Factura::class)
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('facturas.create') ? 'active' : '' }}" href="{{ route('facturas.create') }}">
                                        <i class="fas fa-plus"></i>
                                        Nueva Factura
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </li>

                    <!-- Clientes -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('clientes*') ? 'active' : '' }}" href="{{ route('clientes.index') }}">
                            <i class="fas fa-users"></i>
                            Clientes
                        </a>
                    </li>

                    <!-- Productos -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('productos*') ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#productosMenu">
                            <i class="fas fa-box"></i>
                            Productos
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('productos*') ? 'show' : '' }}" id="productosMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('productos.index') ? 'active' : '' }}" href="{{ route('productos.index') }}">
                                        <i class="fas fa-list"></i>
                                        Lista de Productos
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('productos.inventario') ? 'active' : '' }}" href="{{ route('productos.inventario') }}">
                                        <i class="fas fa-boxes"></i>
                                        Inventario
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('productos.vencimientos') ? 'active' : '' }}" href="{{ route('productos.vencimientos') }}">
                                        <i class="fas fa-clock"></i>
                                        Control de Vencimientos
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Reportes -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reportes*') ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#reportesMenu">
                            <i class="fas fa-chart-bar"></i>
                            Reportes
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('reportes*') ? 'show' : '' }}" id="reportesMenu">
                            <ul class="nav flex-column submenu">
                                @can('verReporteFinanciero', App\Models\Reporte::class)
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('reportes.financiero') ? 'active' : '' }}" href="{{ route('reportes.financiero') }}">
                                        <i class="fas fa-dollar-sign"></i>
                                        Reporte Financiero
                                    </a>
                                </li>
                                @endcan
                                @can('verReporteInventario', App\Models\Reporte::class)
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('reportes.inventario') ? 'active' : '' }}" href="{{ route('reportes.inventario') }}">
                                        <i class="fas fa-boxes"></i>
                                        Reporte de Inventario
                                    </a>
                                </li>
                                @endcan
                                @can('verReporteMedicamentosControlados', App\Models\Reporte::class)
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('reportes.medicamentos-controlados') ? 'active' : '' }}" href="{{ route('reportes.medicamentos-controlados') }}">
                                        <i class="fas fa-shield-alt"></i>
                                        Medicamentos Controlados
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </li>

                    <!-- Auditoría -->
                    @can('viewAny', App\Models\Trazabilidad::class)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('trazabilidad*') ? 'active' : '' }}" href="{{ route('trazabilidad.index') }}">
                            <i class="fas fa-history"></i>
                            Auditoría
                        </a>
                    </li>
                    @endcan

                    <!-- Configuración -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('configuracion*') ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#configuracionMenu">
                            <i class="fas fa-cog"></i>
                            Configuración
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('configuracion*') ? 'show' : '' }}" id="configuracionMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('configuracion.usuarios') ? 'active' : '' }}" href="{{ route('configuracion.usuarios') }}">
                                        <i class="fas fa-users"></i>
                                        Usuarios
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('configuracion.parametros') ? 'active' : '' }}" href="{{ route('configuracion.parametros') }}">
                                        <i class="fas fa-sliders-h"></i>
                                        Parámetros
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Top Bar -->
                <div class="topbar d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button class="sidebar-toggle me-3" id="sidebarToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                @yield('breadcrumb')
                            </ol>
                        </nav>
                    </div>

                    <div class="d-flex align-items-center">
                        <!-- Notificaciones -->
                        <div class="me-3">
                            <button class="btn position-relative" id="notificationBtn">
                                <i class="fas fa-bell text-muted"></i>
                                <span class="notification-badge" id="notificationCount" style="display: none;">0</span>
                            </button>
                        </div>

                        <!-- Usuario -->
                        <div class="user-dropdown">
                            <button class="btn d-flex align-items-center" type="button">
                                <div class="text-end me-2">
                                    <div class="fw-bold">{{ auth()->user()->nombre ?? 'Usuario' }}</div>
                                    <small class="text-muted">{{ auth()->user()->rol ?? 'Rol' }}</small>
                                </div>
                                <div class="avatar bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr(auth()->user()->nombre ?? 'U', 0, 1)) }}
                                </div>
                            </button>
                            <div class="user-dropdown-menu">
                                <a class="dropdown-item d-flex align-items-center" href="{{ route('perfil') }}">
                                    <i class="fas fa-user me-2"></i>
                                    Mi Perfil
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="{{ route('configuracion.cambiar-password') }}">
                                    <i class="fas fa-key me-2"></i>
                                    Cambiar Contraseña
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item d-flex align-items-center w-100 text-start">
                                        <i class="fas fa-sign-out-alt me-2"></i>
                                        Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="content-wrapper">
                    <!-- Alertas -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Errores de validación -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Error:</strong> Por favor corrige los siguientes errores:
                            <ul class="mt-2 mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Contenido principal -->
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Configuración CSRF para AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toggle Sidebar
        $('#sidebarToggle').on('click', function() {
            $('#sidebar').toggleClass('show');
            $('.main-content').toggleClass('expanded');
        });

        // Loading overlay
        function showLoading() {
            $('#loadingOverlay').show();
        }

        function hideLoading() {
            $('#loadingOverlay').hide();
        }

        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Inicializar componentes
        $(document).ready(function() {
            // Initialize DataTables
            if ($('.data-table').length) {
                $('.data-table').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    responsive: true,
                    pageLength: 25,
                    order: [[0, 'desc']]
                });
            }

            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'Selecciona una opción...'
            });

            // Form validation
            $('form').on('submit', function() {
                showLoading();
            });

            // Auto-refresh notifications
            loadNotifications();
            setInterval(loadNotifications, 30000); // Refresh every 30 seconds
        });

        // Load notifications
        function loadNotifications() {
            $.get('/notificaciones/recientes')
                .done(function(data) {
                    updateNotificationBadge(data.count);
                    updateNotificationDropdown(data.notifications);
                })
                .fail(function() {
                    console.log('Error loading notifications');
                });
        }

        function updateNotificationBadge(count) {
            const badge = $('#notificationCount');
            if (count > 0) {
                badge.text(count).show();
            } else {
                badge.hide();
            }
        }

        function updateNotificationDropdown(notifications) {
            // Implement notification dropdown update logic
            console.log('Updating notifications:', notifications);
        }

        // Confirm delete
        function confirmDelete(message = '¿Estás seguro de que deseas eliminar este elemento?') {
            return Swal.fire({
                title: 'Confirmar eliminación',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                return result.isConfirmed;
            });
        }
    </script>

    @stack('scripts')
</body>
</html>