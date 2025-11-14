<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid">

        <!-- Botón para mostrar/ocultar el Sidebar -->
        {{-- Nota: Este botón necesitará JavaScript para funcionar y alternar una clase en el body o en el sidebar. --}}
        <button class="btn btn-outline-secondary me-2" id="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Navbar Brand (Opcional) -->
        <a class="navbar-brand d-none d-lg-block" href="{{ route('admin.dashboard') }}">
            Panel de Administración
        </a>

        <!-- Menús a la derecha -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">

                <!-- Menú de Notificaciones ("Campanita") -->
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fs-5"></i>
                        {{-- Badge para el contador de notificaciones --}}
                        <span class="position-absolute top-1 start-10 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6em;">
                            3
                            <span class="visually-hidden">unread messages</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="notificationsDropdown" style="width: 350px;">
                        <li><h6 class="dropdown-header">Notificaciones</h6></li>
                        <li><a class="dropdown-item d-flex align-items-start" href="#">
                            <i class="fas fa-file-alt text-primary mt-1 me-2"></i>
                            <div>
                                <small class="d-block text-muted">14 Nov, 2025</small>
                                Nuevo reporte de ventas generado.
                            </div>
                        </a></li>
                        <li><a class="dropdown-item d-flex align-items-start" href="#">
                            <i class="fas fa-user-plus text-success mt-1 me-2"></i>
                            <div>
                                <small class="d-block text-muted">14 Nov, 2025</small>
                                Se registró un nuevo usuario: "Juan Pérez".
                            </div>
                        </a></li>
                        <li><a class="dropdown-item d-flex align-items-start" href="#">
                            <i class="fas fa-exclamation-triangle text-warning mt-1 me-2"></i>
                            <div>
                                <small class="d-block text-muted">13 Nov, 2025</small>
                                Alerta de inventario bajo para "Producto X".
                            </div>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center text-muted small" href="#">Ver todas las notificaciones</a></li>
                    </ul>
                </li>

                <li class="nav-item ms-2"><div class="vr"></div></li>

                <!-- Menú del Usuario -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{-- Puedes poner un avatar aquí --}}
                        <i class="fas fa-user-circle fs-4 me-2"></i>
                        @auth
                            {{ Auth::user()->name }}
                        @endauth
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i> Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cogs me-2"></i> Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            {{-- El enlace de "Cerrar Sesión" debe ser un formulario POST por seguridad --}}
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</nav>
