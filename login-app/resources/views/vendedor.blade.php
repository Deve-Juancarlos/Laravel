<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Vendedor - Farmacos del Norte SAC</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/dashboard-vendedor.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo">
                <div class="logo-text">
                    <h3>Farmacos del Norte</h3>
                    <span>Sistema SEDIM</span>
                </div>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard.vendedor') }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('catalogo') ? 'active' : '' }}">
                <a href="{{ route('catalogo.index') }}">
                    <i class="fas fa-pills"></i>
                    <span>Catálogo</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('pedidos') ? 'active' : '' }}">
                <a href="{{ route('pedidos.index') }}">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Mis Pedidos</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('clientes') ? 'active' : '' }}">
                <a href="{{ route('clientes.index') }}">
                    <i class="fas fa-users"></i>
                    <span>Mis Clientes</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('ventas') ? 'active' : '' }}">
                <a href="{{ route('ventas.index') }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Mis Ventas</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('comisiones') ? 'active' : '' }}">
                <a href="{{ route('comisiones.index') }}">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Comisiones</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('metas') ? 'active' : '' }}">
                <a href="{{ route('metas.index') }}">
                    <i class="fas fa-target"></i>
                    <span>Metas</span>
                </a>
            </li>
            <li class="menu-item {{ request()->is('perfil') ? 'active' : '' }}">
                <a href="{{ route('perfil.index') }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Mi Perfil</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <span class="user-name">{{ Auth::user()->usuario }}</span>
                    <span class="user-role">{{ ucfirst(Auth::user()->tipousuario) }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">
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
                <button class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Panel de Ventas</h1>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar productos...">
                </div>
                <div class="quick-actions">
                    <button class="quick-btn" title="Nuevo Pedido">
                        <i class="fas fa-plus"></i>
                        <span>Nuevo Pedido</span>
                    </button>
                </div>
                <div class="notifications">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">{{ count($data['actividadClientes']) }}</span>
                    </button>
                </div>
                <div class="user-menu">
                    <button class="user-btn">
                        <i class="fas fa-user-circle"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card sales">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>S/ {{ number_format($data['ventasMes']) }}</h3>
                        <p>Ventas del Mes</p>
                        <span class="stat-change positive">+15% vs mes anterior</span>
                    </div>
                </div>

                <div class="stat-card orders">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-info">
                        <h3>{{ $data['pedidosCompletados'] }}</h3>
                        <p>Pedidos Completados</p>
                    </div>
                </div>

                <div class="stat-card clients">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>{{ $data['clientesActivos'] }}</h3>
                        <p>Clientes Activos</p>
                    </div>
                </div>

                <div class="stat-card commission">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-info">
                        <h3>S/ {{ number_format($data['comisionesGanadas']) }}</h3>
                        <p>Comisiones Ganadas</p>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Recent Orders -->
                <div class="dashboard-card recent-orders">
                    <div class="card-header">
                        <h3>Pedidos Recientes</h3>
                    </div>
                    <div class="card-content">
                        <table>
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['pedidosRecientes'] as $pedido)
                                    <tr>
                                        <td>{{ $pedido['cliente'] ?? $pedido['farmacia'] }}</td>
                                        <td>S/ {{ number_format($pedido['monto']) }}</td>
                                        <td>{{ $pedido['estado'] }}</td>
                                        <td>{{ $pedido['fecha'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Star Products -->
                <div class="dashboard-card star-products">
                    <div class="card-header">
                        <h3>Productos Estrella</h3>
                    </div>
                    <div class="card-content">
                        <ul>
                            @foreach($data['productosEstrella'] as $producto)
                                <li>
                                    <span class="product-name">{{ $producto['nombre'] }}</span>
                                    <span class="product-sold">Vendidos: {{ $producto['vendidos'] }}</span>
                                    <span class="product-income">Ingresos: S/ {{ number_format($producto['ingresos']) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Client Activity -->
                <div class="dashboard-card client-activity">
                    <div class="card-header">
                        <h3>Actividad de Clientes</h3>
                    </div>
                    <div class="card-content">
                        <ul>
                            @foreach($data['actividadClientes'] as $actividad)
                                <li>
                                    <i class="{{ $actividad['icono'] }}"></i>
                                    <span>{{ $actividad['cliente'] }} {{ $actividad['accion'] }}</span>
                                    <span class="activity-time">{{ $actividad['tiempo'] }}</span>
                                    @if(isset($actividad['monto']))
                                        <span class="activity-amount">S/ {{ number_format($actividad['monto']) }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('js/dashboard-vendedor.js') }}"></script>
</body>
</html>