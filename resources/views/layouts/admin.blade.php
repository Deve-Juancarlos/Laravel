<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com;">
    <title>@yield('title', 'Dashboard') - SEDIM Farmacéutico</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 270px;
            --sidebar-bg: #1a2332;
            --primary: #3498db;
            --danger: #e74c3c;
            --success: #27ae60;
            --warning: #f39c12;
            
            /* Variables de tema (se sobrescriben en dark-theme) */
            --bg-primary: #ffffff;
            --bg-secondary: #f5f7fa;
            --bg-tertiary: #e9ecef;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --text-muted: #adb5bd;
            --border-color: #dee2e6;
            --shadow-sm: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            --shadow-md: 0 0.5rem 1rem rgba(0,0,0,0.15);
            --shadow-lg: 0 1rem 3rem rgba(0,0,0,0.175);
        }

        body.dark-theme {
            --bg-primary: #1a1d23;
            --bg-secondary: #22262e;
            --bg-tertiary: #2a2f38;
            --text-primary: #e9ecef;
            --text-secondary: #adb5bd;
            --text-muted: #6c757d;
            --border-color: #3a3f4b;
            --shadow-sm: 0 0.125rem 0.25rem rgba(0,0,0,0.3);
            --shadow-md: 0 0.5rem 1rem rgba(0,0,0,0.5);
            --shadow-lg: 0 1rem 3rem rgba(0,0,0,0.7);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-secondary);
            color: var(--text-primary);
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, #0f1419 100%);
            color: #e8edf2;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 4px 0 12px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s;
        }

        .sidebar-header {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.02);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo-img {
            width: 45px;
            height: 45px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }

        .brand-text h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, var(--primary) 0%, #2980b9 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-text small {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .user-profile {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.03);
        }

        .profile-content {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .profile-img-wrapper {
            position: relative;
        }

        .profile-img {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            border: 3px solid var(--primary);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .status-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background: var(--success);
            border: 2px solid var(--sidebar-bg);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(0.9); }
        }

        .user-info h6 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 600;
            color: white;
        }

        .role-badge {
            display: inline-block;
            font-size: 0.75rem;
            padding: 2px 10px;
            border-radius: 12px;
            background: rgba(52, 152, 219, 0.2);
            color: var(--primary);
            border: 1px solid rgba(52, 152, 219, 0.3);
            margin-top: 4px;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0.75rem;
        }

        .sidebar-content::-webkit-scrollbar { width: 6px; }
        .sidebar-content::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .sidebar-content::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 3px; }

        .menu-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,0.4);
            padding: 1rem 1rem 0.5rem;
            margin-top: 0.5rem;
        }

        .nav-link {
            color: #e8edf2;
            padding: 0.85rem 1.1rem;
            border-radius: 8px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            transition: all 0.3s;
            position: relative;
        }

        .nav-link:hover {
            background: #2c3e50;
            color: white;
            transform: translateX(4px);
        }

        .nav-link.active {
            background: linear-gradient(90deg, rgba(52, 152, 219, 0.15) 0%, rgba(52, 152, 219, 0.05) 100%);
            color: white;
            border-left: 4px solid var(--primary);
            padding-left: calc(1.1rem - 4px);
        }

        .nav-link i {
            width: 22px;
            text-align: center;
            font-size: 1.1rem;
        }

        .sidebar-footer {
            padding: 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .footer-link {
            color: #e8edf2;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .footer-link:hover {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s;
        }

        .topbar {
            background: var(--bg-primary);
            padding: 0.85rem 1.5rem;
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
            transition: background-color 0.3s;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .mobile-toggle {
            background: none;
            border: none;
            font-size: 1.4rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            display: none;
            color: var(--text-primary);
        }

        .page-title {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            padding: 0.6rem 1rem 0.6rem 2.8rem;
            border-radius: 25px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-primary);
            color: var(--text-primary);
            width: 280px;
            transition: all 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            width: 320px;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .content-wrapper {
            padding: 2rem;
        }

        /* SESSION TIMER */
        .session-timer {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(39, 174, 96, 0.9);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
        }

        .session-timer.warning {
            background: rgba(243, 156, 18, 0.9);
        }

        .session-timer.danger {
            background: rgba(231, 76, 60, 0.9);
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* RESPONSIVE */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .mobile-toggle {
                display: block;
            }
            .search-input {
                width: 200px;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 1.3rem;
            }
            .search-box {
                display: none;
            }
            .content-wrapper {
                padding: 1rem;
            }
        }

        /* ALERTS */
        .alert-custom {
            border-left: 4px solid;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-info {
            background: #e8f4fd;
            border-color: var(--primary);
            color: #1e5a7d;
        }

        .alert-warning {
            background: #fef5e7;
            border-color: var(--warning);
            color: #7d5e1e;
        }

        .alert-danger {
            background: #fadbd8;
            border-color: var(--danger);
            color: #7d1e1e;
        }

        body.dark-theme .alert-info {
            background: rgba(52, 152, 219, 0.15);
            color: #a3d5ff;
        }

        body.dark-theme .alert-warning {
            background: rgba(243, 156, 18, 0.15);
            color: #ffd89b;
        }

        body.dark-theme .alert-danger {
            background: rgba(231, 76, 60, 0.15);
            color: #ff9999;
        }

        /* MODAL DE SESIÓN */
        #sessionWarningModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #sessionWarningModal .modal-content {
            background: var(--bg-primary);
            color: var(--text-primary);
            padding: 1.5rem;
            border-radius: 8px;
            max-width: 400px;
            text-align: center;
            box-shadow: var(--shadow-lg);
        }

        /* CARDS CON TEMA */
        .card {
            background-color: var(--bg-primary);
            border-color: var(--border-color);
            box-shadow: var(--shadow-sm);
            color: var(--text-primary);
        }

        .table {
            color: var(--text-primary);
        }

        .table thead th {
            background-color: var(--bg-tertiary);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: var(--bg-secondary);
        }

        .table-hover tbody tr:hover {
            background-color: var(--bg-tertiary);
        }

        .form-control, .form-select {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            border-color: #86b7fe;
        }

        .modal-content {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }

        .modal-header, .modal-footer {
            border-color: var(--border-color);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <div class="brand-logo">
                    <img src="{{ asset('images/Logo.png') }}" alt="Logo" class="logo-img">
                    <div class="brand-text">
                        <h4>SEDIM</h4>
                        <small>Farmacéutico</small>
                    </div>
                </div>
            </div>
            
            <div class="user-profile">
                <div class="profile-content">
                    <div class="profile-img-wrapper">
                        <img src="{{ asset('images/admin.png') }}" alt="Usuario" class="profile-img">
                        <span class="status-indicator"></span>
                    </div>
                    <div class="user-info">
                        <h6>{{ Auth::user()->usuario ?? 'Administrador' }}</h6>
                        <span class="role-badge">
                            <i class="fas fa-shield-alt"></i> {{ Auth::user()->tipousuario ?? 'ADMIN' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="sidebar-content">
                <nav class="nav-menu">
                    <a href="{{ route('dashboard.admin') }}" class="nav-link {{ request()->routeIs('dashboard.admin') ? 'active' : '' }}">
                        <i class="fas fa-home"></i> 
                        <span>Dashboard</span>
                    </a>

                    <div class="menu-section-title">
                        <i class="fas fa-exclamation-triangle"></i> Operaciones Críticas
                    </div>
                    
                    <a href="{{ route('admin.planillas.index') }}" class="nav-link {{ request()->routeIs('admin.planillas.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i> 
                        <span>Planillas Cobranza</span>
                    </a>
                    
                    <a href="{{ route('admin.cuentas-corrientes.index') }}" class="nav-link {{ request()->routeIs('admin.cuentas-corrientes.*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt"></i> 
                        <span>Cuentas Corrientes</span>
                    </a>

                    <div class="menu-section-title">
                        <i class="fas fa-university"></i> Gestión Financiera
                    </div>
                    
                    <a href="{{ route('admin.bancos.index') }}" class="nav-link {{ request()->routeIs('admin.bancos.*') ? 'active' : '' }}">
                        <i class="fas fa-landmark"></i> 
                        <span>Bancos y Cuentas</span>
                    </a>

                    <div class="menu-section-title">
                        <i class="fas fa-cog"></i> Administración
                    </div>
                    
                    <a href="{{ route('admin.usuarios.index') }}" class="nav-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
                        <i class="fas fa-user-shield"></i> 
                        <span>Usuarios y Permisos</span>
                    </a>

                    <a href="{{ route('admin.auditoria.index') }}" class="nav-link {{ request()->routeIs('admin.auditoria.*') ? 'active' : '' }}">
                        <i class="fas fa-history"></i> 
                        <span>Auditoría del Sistema</span>
                    </a>

                    <div class="menu-section-title">
                        <i class="fas fa-chart-line"></i> Reportes
                    </div>
                    
                    <a href="{{ route('admin.reportes.facturas') }}" class="nav-link {{ request()->routeIs('admin.reportes.facturas') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i> 
                        <span>Facturas</span>
                    </a>
                    
                    <a href="{{ route('admin.reportes.movimientos') }}" class="nav-link {{ request()->routeIs('admin.reportes.movimientos') ? 'active' : '' }}">
                        <i class="fas fa-money-check-alt"></i> 
                        <span>Movimientos Bancarios</span>
                    </a>
                </nav>
            </div>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                    @csrf
                    <a href="#" class="footer-link" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();" aria-label="Cerrar sesión">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </form>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="mobile-toggle" id="mobileToggle" aria-label="Alternar menú lateral">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">
                        @yield('title', 'Dashboard')
                    </h1>
                </div>
                <div class="topbar-right">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" placeholder="Buscar..." class="search-input" id="globalSearch">
                    </div>
                    
                    <!-- ============================================== -->
                    <!-- COMPONENTE DE NOTIFICACIONES Y TEMA (NUEVO)   -->
                    <!-- ============================================== -->
                    @include('components.notificaciones_y_tema')
                    <!-- ============================================== -->
                </div>
            </header>

            <div class="content-wrapper">
                @if(session('success'))
                <div class="alert-custom alert-info">
                    <i class="fas fa-check-circle fa-lg"></i>
                    <div>{{ session('success') }}</div>
                </div>
                @endif

                @if(session('error'))
                <div class="alert-custom alert-danger">
                    <i class="fas fa-exclamation-circle fa-lg"></i>
                    <div>{{ session('error') }}</div>
                </div>
                @endif

                @if(session('warning'))
                <div class="alert-custom alert-warning">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                    <div>{{ session('warning') }}</div>
                </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Session Timer -->
    <div class="session-timer" id="sessionTimer">
        <i class="fas fa-shield-alt"></i>
        <span id="timerDisplay">Sesión: 15:00</span>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // ===== SEGURIDAD Y GESTIÓN DE SESIÓN MEJORADA =====
        const SecurityManager = {
            sessionTimeout: 900,
            currentTime: 900,
            warningTime: 300,
            dangerTime: 60,
            inactivityTimer: null,
            countdownInterval: null,
            warningShown: false,

            init() {
                this.startCountdown();
                this.setupActivityListeners();
                this.setupCSRFToken();
                this.sanitizeInputs();
                this.preventDataLeaks();
                this.setupSecureHeaders();
            },

            startCountdown() {
                const timerEl = document.getElementById('sessionTimer');
                const displayEl = document.getElementById('timerDisplay');
                
                this.countdownInterval = setInterval(() => {
                    this.currentTime--;
                    
                    const minutes = Math.floor(this.currentTime / 60);
                    const seconds = this.currentTime % 60;
                    displayEl.textContent = `Sesión: ${minutes}:${seconds.toString().padStart(2, '0')}`;
                    
                    if (this.currentTime <= this.dangerTime) {
                        timerEl.className = 'session-timer danger';
                        this.showExpirationWarning();
                    } else if (this.currentTime <= this.warningTime) {
                        timerEl.className = 'session-timer warning';
                    } else {
                        timerEl.className = 'session-timer';
                    }
                    
                    if (this.currentTime <= 0) {
                        this.sessionExpired();
                    }
                }, 1000);
            },

            showExpirationWarning() {
                if (this.currentTime === 60 && !this.warningShown) {
                    this.warningShown = true;
                    const modal = document.createElement('div');
                    modal.id = 'sessionWarningModal';
                    modal.innerHTML = `
                        <div class="modal-content">
                            <i class="fas fa-exclamation-triangle" style="color: #f39c12; font-size: 2rem;"></i>
                            <h5 class="mt-3">Sesión por expirar</h5>
                            <p class="text-muted">Tu sesión finalizará en <strong>1 minuto</strong> por inactividad.</p>
                            <button class="btn btn-primary" onclick="SecurityManager.extendSession()">
                                Mantener sesión activa
                            </button>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }
            },

            extendSession() {
                this.resetTimer();
                this.warningShown = false;
                const modal = document.getElementById('sessionWarningModal');
                if (modal) modal.remove();
            },

            resetTimer() {
                this.currentTime = this.sessionTimeout;
                // Remover la línea de session.ping si no existe la ruta
                // fetch('{{ route("session.ping") }}', { ... }).catch(err => {});
            },

            sessionExpired() {
                clearInterval(this.countdownInterval);
                alert('Su sesión ha expirado por inactividad. Será redirigido al inicio de sesión.');
                window.location.href = '{{ route("logout") }}';
            },

            setupActivityListeners() {
                ['mousedown', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
                    document.addEventListener(event, () => {
                        this.resetTimer();
                    }, { passive: true });
                });
            },

            setupCSRFToken() {
                const token = document.querySelector('meta[name="csrf-token"]');
                if (!token) {
                    console.error('⚠️ Token CSRF no encontrado');
                    return;
                }
                if (window.jQuery) {
                    $.ajaxSetup({
                        headers: { 'X-CSRF-TOKEN': token.content }
                    });
                }
            },

            sanitizeInputs() {
                const searchInput = document.getElementById('globalSearch');
                if (!searchInput) return;

                searchInput.addEventListener('input', function(e) {
                    let value = e.target.value;
                    value = value.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
                    value = value.replace(/javascript:/gi, '');
                    value = value.replace(/on\w+\s*=/gi, '');
                    if (value !== e.target.value) {
                        e.target.value = value;
                        console.warn('⚠️ Caracteres potencialmente peligrosos removidos');
                    }
                });

                searchInput.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const text = (e.clipboardData || window.clipboardData).getData('text');
                    const cleaned = text.replace(/<[^>]*>/g, '');
                    e.target.value = cleaned;
                });
            },

            preventDataLeaks() {
                document.addEventListener('copy', function(e) {
                    const selection = window.getSelection().toString();
                    if (selection.length > 100) {
                        e.clipboardData.setData('text/plain', 
                            'Contenido protegido - SEDIM Farmacéutico\n' +
                            'Para copiar información contacte al administrador'
                        );
                        e.preventDefault();
                    }
                });
            },

            setupSecureHeaders() {
                if (window.self !== window.top) {
                    window.top.location = window.self.location;
                }

                const devToolsCheck = /./;
                devToolsCheck.toString = function() {
                    console.warn('⚠️ DevTools detectadas - Actividad monitoreada');
                    return 'devtools';
                };
                console.log('%c', devToolsCheck);
            }
        };

        window.addEventListener('error', function(e) {
            console.error('Error capturado:', e.message, e.filename, e.lineno);

            const alert = document.createElement('div');
            alert.className = 'alert-custom alert-danger';
            alert.innerHTML = `<i class="fas fa-exclamation-circle"></i> Error: ${e.message || 'Algo salió mal'}`;
            document.querySelector('.content-wrapper')?.prepend(alert);
        });

        window.addEventListener('unhandledrejection', function(e) {
            console.error('Promesa rechazada:', e.reason);
        });

        document.addEventListener('DOMContentLoaded', function() {
            SecurityManager.init();

            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.getElementById('mobileToggle');

            mobileToggle?.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });

            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992) {
                    if (!sidebar.contains(event.target) && !mobileToggle?.contains(event.target)) {
                        sidebar.classList.remove('active');
                    }
                }
            });

            document.querySelectorAll('.alert-custom:not(.alert-danger)').forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });

            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.dataset.originalText = originalText;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                        
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }, 3000);
                    }
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>