<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - Farmacos del Norte SAC</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/dashboard-admin.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" aria-label="Menú principal">
        <div class="sidebar-header">
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Farmacos del Norte">
                <div class="logo-text">
                    <h3>Farmacos del Norte</h3>
                    <span>Sistema SEDIM</span>
                </div>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard.admin') }}" aria-label="Ir al dashboard" title="Dashboard">
                    <i class="fas fa-home" aria-hidden="true"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('productos*') ? 'active' : '' }}">
                <a href="{{ route('productos.index') }}" aria-label="Ver productos" title="Productos">
                    <i class="fas fa-pills" aria-hidden="true"></i>
                    <span>Productos</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('inventario*') ? 'active' : '' }}">
                <a href="{{ route('inventario.index') }}" aria-label="Ver inventario" title="Inventario">
                    <i class="fas fa-boxes" aria-hidden="true"></i>
                    <span>Inventario</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('ventas*') ? 'active' : '' }}">
                <a href="{{ route('ventas.index') }}" aria-label="Ver ventas" title="Ventas">
                    <i class="fas fa-chart-line" aria-hidden="true"></i>
                    <span>Ventas</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('usuarios*') ? 'active' : '' }}">
                <a href="{{ route('usuarios.index') }}" aria-label="Ver usuarios" title="Usuarios">
                    <i class="fas fa-users" aria-hidden="true"></i>
                    <span>Usuarios</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('farmacias*') ? 'active' : '' }}">
                <a href="{{ route('farmacias.index') }}" aria-label="Ver farmacias" title="Farmacias">
                    <i class="fas fa-building" aria-hidden="true"></i>
                    <span>Farmacias</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('reportes*') ? 'active' : '' }}">
                <a href="{{ route('reportes.index') }}" aria-label="Ver reportes" title="Reportes">
                    <i class="fas fa-file-alt" aria-hidden="true"></i>
                    <span>Reportes</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('configuracion*') ? 'active' : '' }}">
                <a href="{{ route('configuracion.index') }}" aria-label="Ver configuración" title="Configuración">
                    <i class="fas fa-cog" aria-hidden="true"></i>
                    <span>Configuración</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="user-details">
                    <span class="user-name">{{ auth()->user()->Usuario }}</span>
                    <span class="user-role">{{ ucfirst(session('tipousuario')) }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn" aria-label="Cerrar sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="menu-toggle" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Panel de Administración</h1>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar..." aria-label="Buscar">
                </div>
                <div class="notifications">
                    <button class="notification-btn" id="notificationToggle" aria-label="Notificaciones">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">{{ count($data['alertas'] ?? []) }}</span>
                    </button>
                    <div class="notification-dropdown" id="notificationDropdown" style="display: none;">
                        {{-- Las notificaciones se cargarán aquí vía AJAX --}}
                    </div>
                </div>
                <div class="user-menu">
                    <button class="user-btn" aria-label="Menú de usuario">
                        <i class="fas fa-user-circle"></i>
                    </button>
                </div>
                <button class="theme-toggle" id="themeToggle" aria-label="Cambiar tema">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            @error('dashboard')
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </div>
            @enderror

            <!-- Stats Cards -->
            @if(isset($data['totalProductos']))
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-icon">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div class="stat-info">
                            <h3>{{ number_format($data['totalProductos']) }}</h3>
                            <p>Total Productos</p>
                            <span class="stat-change positive">+12% vs mes anterior</span>
                        </div>
                    </div>

                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info">
                            <h3>S/ {{ number_format($data['ventasMes'], 2) }}</h3>
                            <p>Ventas del Mes</p>
                            <span class="stat-change positive">+18% vs mes anterior</span>
                        </div>
                    </div>

                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-info">
                            <h3>{{ $data['farmaciasActivas'] }}</h3>
                            <p>Farmacias Activas</p>
                            <span class="stat-change positive">+5% vs mes anterior</span>
                        </div>
                    </div>

                    <div class="stat-card danger">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>{{ $data['stockBajo'] }}</h3>
                            <p>Stock Bajo</p>
                            <span class="stat-change negative">Requiere atención</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-chart-line"></i>
                    <p>No hay datos disponibles</p>
                </div>
            @endif

            <!-- Charts and Tables -->
            <div class="dashboard-grid">
                <!-- Sales Chart -->
                <div class="dashboard-card" id="salesChartCard">
                    <div class="card-header">
                        <h3>Ventas Mensuales</h3>
                        <div class="card-actions">
                            <button class="btn-action" aria-label="Descargar gráfico">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="chart-container">
                            <div class="loading-spinner">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                            <canvas id="salesChart" style="display: none;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Pedidos Recientes</h3>
                        <a href="{{ route('pedidos.index') }}" class="view-all">Ver todos</a>
                    </div>
                    <div class="card-content">
                        <div class="orders-list">
                            @forelse($data['pedidosRecientes'] ?? [] as $pedido)
                                <div class="order-item">
                                    <div class="order-info">
                                        <span class="order-id">#{{ $pedido['id'] }}</span>
                                        <span class="pharmacy-name">{{ $pedido['farmacia'] }}</span>
                                    </div>
                                    <div class="order-details">
                                        <span class="order-amount">S/ {{ number_format($pedido['monto']) }}</span>
                                        <span class="order-status {{ strtolower(str_replace(' ', '', $pedido['estado'])) }}">
                                            {{ $pedido['estado'] }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="no-orders">
                                    <i class="fas fa-shopping-cart"></i>
                                    <p>No hay pedidos recientes</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="dashboard-card" data-lazy-load="products-chart">
                    <div class="card-header">
                        <h3>Productos Más Vendidos</h3>
                        <a href="{{ route('productos.populares') }}" class="view-all">Ver todos</a>
                    </div>
                    <div class="card-content">
                        <div class="products-list">
                            @forelse($data['productosPopulares'] ?? [] as $producto)
                                <div class="product-item">
                                    <div class="product-info">
                                        <span class="product-name">{{ $producto['nombre'] }}</span>
                                        <span class="product-category">{{ $producto['categoria'] }}</span>
                                    </div>
                                    <div class="product-stats">
                                        <span class="units-sold">{{ number_format($producto['unidades']) }} unidades</span>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: {{ $producto['porcentaje'] ?? 75 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="no-products">
                                    <i class="fas fa-pills"></i>
                                    <p>No hay productos populares</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Alerts -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Alertas del Sistema</h3>
                        <button class="btn-action" aria-label="Configurar alertas">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                    <div class="card-content">
                        <div class="alerts-list">
                            @forelse($data['alertas'] ?? [] as $alerta)
                                <div class="alert-item {{ $alerta['tipo'] }}">
                                    <i class="{{ $alerta['icono'] }}"></i>
                                    <div class="alert-content">
                                        <span class="alert-title">{{ $alerta['titulo'] }}</span>
                                        <span class="alert-desc">{{ $alerta['descripcion'] }}</span>
                                    </div>
                                    <span class="alert-time">{{ $alerta['tiempo'] }}</span>
                                </div>
                            @empty
                                <div class="no-alerts">
                                    <i class="fas fa-check-circle"></i>
                                    <p>No hay alertas nuevas</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('js/dashboard-admin.js') }}"></script>
</body>
</html>