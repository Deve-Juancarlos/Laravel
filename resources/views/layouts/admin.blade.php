<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') | SEDIMCORP Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/layout/app.css') }}">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    @stack('styles')
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand text-center py-3">
            <img src="{{ asset('images/Logo.png') }}" 
                 alt="SEDIMCORP Logo" 
                 class="img-fluid mb-2" 
                 style="max-width: 50px;">
            <h4 class="fw-bold text-white mb-0">SEDIMCORP</h4>
            <small class="fw-bold text-white mb-0" style="font-size: 0.85rem;">Panel Administrativo</small>
        </div>

        <nav class="sidebar-nav">
            @include('layouts.partials._sidebar_admin')
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <button class="btn btn-link d-md-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <div class="topbar-search d-none d-md-block">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" placeholder="Buscar reportes, usuarios...">
                </div>
            </div>

            <div class="topbar-right">
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    @if(isset($notification_count) && $notification_count > 0)
                        <span class="notification-badge">{{ $notification_count }}</span>
                    @endif
                </div>

                <div class="user-menu dropdown">
                    <div data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->usuario ?? Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="d-none d-md-block ms-2">
                            <div style="font-size: 0.875rem; font-weight: 600;">
                                {{ Auth::user()->usuario ?? Auth::user()->name ?? 'Administrador' }}
                            </div>
                            <div style="font-size: 0.75rem; color: #666;">
                                {{ Auth::user()->tipousuario ?? 'ADMIN' }}
                            </div>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
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

            
            <div class="page-header-container mb-3">
                
                @yield('header-content')

                
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Inicio</a></li>
                        @yield('breadcrumbs')
                    </ol>
                </nav>
            </div>

            @yield('content')
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
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

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
