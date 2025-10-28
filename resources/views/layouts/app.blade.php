<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SIFANO') - Distribuidora</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @stack('head')
    <style>      
      body { background: #f8fafc; font-family: 'Segoe UI', Roboto, Arial, sans-serif; }
      .container { max-width:1200px; margin:0 auto; padding: 1rem; }
    </style>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <script src="{{ asset('js/app.js') }}"></script>


    @stack('scripts')
</body>
</html>