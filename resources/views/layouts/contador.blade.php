{{-- resources/views/layouts/contador.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Contador') - Sistema Contable</title>
    
    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- Estilos personalizados del stack --}}
    @stack('styles')
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8f9fa;
            color: #2d3748;
        }

        /* Navbar Principal */
        .navbar-brand-custom {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.3rem;
            color: white !important;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .navbar-brand-custom:hover {
            transform: scale(1.05);
        }

        .brand-logo {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .brand-logo i {
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .brand-text-main {
            font-size: 1.2rem;
            font-weight: 700;
        }

        .brand-text-sub {
            font-size: 0.7rem;
            opacity: 0.9;
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .navbar {
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.15);
            padding: 0.75rem 0;
        }

        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 0 4px;
            padding: 0.5rem 1rem !important;
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: white !important;
            transform: translateY(-1px);
        }

        .nav-link i {
            margin-right: 6px;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            margin-top: 12px;
            padding: 0.5rem;
            animation: dropdownFade 0.3s ease;
        }

        @keyframes dropdownFade {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            padding: 10px 16px;
            transition: all 0.2s ease;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding-left: 24px;
        }

        .dropdown-item i {
            width: 20px;
            margin-right: 8px;
        }

        .dropdown-divider {
            margin: 0.5rem 0;
            opacity: 0.1;
        }

        /* Usuario en navbar */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            color: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            margin-right: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .user-info {
            font-size: 0.9rem;
            line-height: 1.3;
        }

        .user-info .fw-bold {
            font-size: 0.95rem;
        }

        .user-info small {
            opacity: 0.9;
        }

        .dropdown-header {
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        /* Contenido principal */
        .main-content {
            min-height: calc(100vh - 70px);
        }

        /* Efectos hover mejorados */
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: white;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after, .nav-link.active::after {
            width: 70%;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Responsive */
        @media (max-width: 991px) {
            .navbar-nav {
                margin-top: 1rem;
            }
            
            .nav-link {
                padding: 10px 16px !important;
                margin: 4px 0;
            }
            
            .user-info {
                display: none;
            }

            .brand-text-sub {
                display: none;
            }
        }

        /* Notifications badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #f5576c;
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }

        /* Loader */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.3s ease;
        }

        .page-loader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e2e8f0;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    {{-- Loader --}}
    <div class="page-loader hidden" id="pageLoader">
        <div class="loader-spinner"></div>
    </div>

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            {{-- Logo/Brand --}}
            <a class="navbar-brand-custom" href="{{ route('contabilidad.dashboard') }}">
                <div class="brand-logo">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-text-main">ContaSys</span>
                    <span class="brand-text-sub">Sistema Contable Pro</span>
                </div>
            </a>

            {{-- Toggle para móvil --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Menú principal --}}
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    {{-- Dashboard --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contabilidad.dashboard') ? 'active' : '' }}" 
                           href="{{ route('contabilidad.dashboard') }}">
                            <i class="fas fa-home"></i>Dashboard
                        </a>
                    </li>

                    {{-- Clientes --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('contabilidad.clientes.*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-users"></i>Clientes
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('contabilidad.clientes.index') }}">
                                <i class="fas fa-list"></i>Lista de Clientes
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('contabilidad.clientes.create') }}">
                                <i class="fas fa-user-plus"></i>Nuevo Cliente
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('contabilidad.clientes.buscar') }}">
                                <i class="fas fa-search"></i>Consultar RENIEC
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-file-contract"></i>Cuentas Corrientes
                            </a></li>
                        </ul>
                    </li>

                    {{-- Facturación --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('contabilidad.facturas.*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-file-invoice-dollar"></i>Facturación
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('contabilidad.facturas.demo') }}">
                                <i class="fas fa-file-invoice"></i>Nueva Factura
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-receipt"></i>Boletas
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-credit-card"></i>Notas de Crédito
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-list-alt"></i>Registro de Ventas
                            </a></li>
                        </ul>
                    </li>

                    {{-- Planillas y Cobranzas --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('contabilidad.planillas.*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-hand-holding-usd"></i>Cobranzas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('contabilidad.planillas.create') }}">
                                <i class="fas fa-file-alt"></i>Nueva Planilla
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-list"></i>Ver Planillas
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-calendar-check"></i>Por Vencer
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-exclamation-triangle"></i>Vencidas
                            </a></li>
                        </ul>
                    </li>

                    {{-- Bancos --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('contabilidad.bancos.*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-university"></i>Bancos
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('contabilidad.bancos.index') }}">
                                <i class="fas fa-landmark"></i>Cuentas Bancarias
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('contabilidad.bancos.create') }}">
                                <i class="fas fa-plus-circle"></i>Nueva Cuenta
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-exchange-alt"></i>Movimientos
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-balance-scale-right"></i>Conciliación
                            </a></li>
                        </ul>
                    </li>

                    {{-- Reportes --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-bar"></i>Reportes
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-file-pdf"></i>Estados Financieros
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-book"></i>Libros Contables
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-chart-line"></i>Análisis Financiero
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-download"></i>Exportar Todo
                            </a></li>
                        </ul>
                    </li>

                    {{-- Configuración --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cogs"></i>Configuración
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-building"></i>Datos de la Empresa
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-percentage"></i>Tributos y Tasas
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-calendar-alt"></i>Períodos Fiscales
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-list-ol"></i>Plan de Cuentas
                            </a></li>
                        </ul>
                    </li>
                </ul>

                {{-- Búsqueda rápida --}}
                <div class="d-flex align-items-center me-3">
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" placeholder="Buscar..." id="quickSearch">
                    </div>
                </div>

                {{-- Notificaciones --}}
                <ul class="navbar-nav me-2">
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell fa-lg"></i>
                            <span class="notification-badge">3</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="width: 320px;">
                            <li class="dropdown-header">
                                <strong>Notificaciones</strong>
                                <span class="badge bg-primary float-end">3 nuevas</span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-exclamation-circle text-warning"></i>
                                <div class="ms-2">
                                    <strong>Facturas por vencer</strong>
                                    <small class="d-block text-muted">23 facturas vencen hoy</small>
                                </div>
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-check-circle text-success"></i>
                                <div class="ms-2">
                                    <strong>Pago recibido</strong>
                                    <small class="d-block text-muted">Cliente: Juan Pérez - S/ 2,450</small>
                                </div>
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-info-circle text-info"></i>
                                <div class="ms-2">
                                    <strong>Nueva actualización</strong>
                                    <small class="d-block text-muted">Sistema actualizado a v2.1</small>
                                </div>
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center text-primary" href="#">
                                Ver todas las notificaciones
                            </a></li>
                        </ul>
                    </li>
                </ul>

                {{-- Usuario y perfil --}}
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" 
                           id="navbarUserDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="user-avatar">
                                {{ strtoupper(substr(Auth::user()->usuario, 0, 2)) }}
                            </div>
                            <div class="user-info">
                                <div class="fw-bold">{{ Auth::user()->usuario }}</div>
                                <small class="text-light">{{ Auth::user()->tipousuario ?? 'Contador' }}</small>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <div class="dropdown-header">
                                    <strong>{{ Auth::user()->usuario }}</strong>
                                    <br><small class="text-muted">{{ Auth::user()->email ?? 'Sin email' }}</small>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user"></i>Mi Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-shield-alt"></i>Seguridad
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-bell"></i>Preferencias
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}" 
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i>Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </nav>

    {{-- Contenido principal --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- Bootstrap Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Scripts personalizados del stack --}}
    @stack('scripts')

    {{-- Scripts globales --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ocultar loader
        setTimeout(() => {
            document.getElementById('pageLoader').classList.add('hidden');
        }, 300);

        // Marcar elemento activo en el menú
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
                link.classList.add('active');
            }
        });

        // Cerrar dropdowns automáticamente en móvil
        const dropdowns = document.querySelectorAll('.dropdown-toggle');
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('hidden.bs.dropdown', function() {
                if (window.innerWidth < 992) {
                    // Cerrar navbar en móvil después de seleccionar
                    const navbarCollapse = document.getElementById('navbarNav');
                    if (navbarCollapse.classList.contains('show')) {
                        setTimeout(() => {
                            navbarCollapse.classList.remove('show');
                        }, 300);
                    }
                }
            });
        });

        // Animación del navbar en scroll
        let lastScrollTop = 0;
        const navbar = document.querySelector('.navbar');
        
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scroll hacia abajo - ocultar navbar
                navbar.style.transform = 'translateY(-100%)';
            } else {
                // Scroll hacia arriba - mostrar navbar
                navbar.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        });

        // Búsqueda rápida
        const quickSearch = document.getElementById('quickSearch');
        if (quickSearch) {
            quickSearch.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = this.value.trim();
                    if (searchTerm) {
                        // Aquí puedes implementar la lógica de búsqueda
                        console.log('Buscando:', searchTerm);
                        // window.location.href = `/contabilidad/buscar?q=${encodeURIComponent(searchTerm)}`;
                    }
                }
            });

            // Placeholder animado
            const placeholders = [
                'Buscar cliente...',
                'Buscar factura...',
                'Buscar por DNI/RUC...',
                'Buscar planilla...'
            ];
            let currentPlaceholder = 0;

            setInterval(() => {
                quickSearch.placeholder = placeholders[currentPlaceholder];
                currentPlaceholder = (currentPlaceholder + 1) % placeholders.length;
            }, 3000);
        }

        // Efecto de typing para el nombre de usuario (solo primera carga)
        if (!sessionStorage.getItem('welcomeShown')) {
            const userName = '{{ Auth::user()->usuario }}';
            const greetingElement = document.querySelector('.user-info .fw-bold');
            
            if (greetingElement) {
                greetingElement.style.opacity = '0';
                setTimeout(() => {
                    greetingElement.style.transition = 'opacity 0.5s ease';
                    greetingElement.style.opacity = '1';
                }, 500);
            }
            sessionStorage.setItem('welcomeShown', 'true');
        }

        // Notificaciones - marcar como leídas
        const notificationItems = document.querySelectorAll('.dropdown-menu .dropdown-item');
        notificationItems.forEach(item => {
            item.addEventListener('click', function() {
                this.style.opacity = '0.6';
            });
        });

        // Efecto hover en dropdowns
        const dropdownMenus = document.querySelectorAll('.dropdown-menu');
        dropdownMenus.forEach(menu => {
            menu.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 12px 40px rgba(0, 0, 0, 0.18)';
            });
            menu.addEventListener('mouseleave', function() {
                this.style.boxShadow = '0 8px 30px rgba(0, 0, 0, 0.12)';
            });
        });

        // Tooltips de Bootstrap
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Confirmar logout
        const logoutLink = document.querySelector('a[href*="logout"]');
        if (logoutLink) {
            logoutLink.addEventListener('click', function(e) {
                if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                    e.preventDefault();
                }
            });
        }
    });

    // Atajos de teclado
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K para búsqueda rápida
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('quickSearch')?.focus();
        }
        
        // Ctrl/Cmd + B para ir al dashboard
        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
            e.preventDefault();
            window.location.href = '{{ route("contabilidad.dashboard") }}';
        }
    });

    // Función global para mostrar notificaciones toast
    window.showToast = function(message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    };

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }

    // Detectar inactividad (30 minutos)
    let inactivityTimer;
    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            if (confirm('Tu sesión está por expirar por inactividad. ¿Deseas continuar?')) {
                resetInactivityTimer();
            } else {
                document.getElementById('logout-form').submit();
            }
        }, 30 * 60 * 1000); // 30 minutos
    }

    ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, resetInactivityTimer, true);
    });

    resetInactivityTimer();
    </script>
</body>
</html>