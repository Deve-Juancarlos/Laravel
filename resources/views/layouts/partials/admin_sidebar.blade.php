{{-- ¡Este es el menú que verá solo el Administrador! --}}
<nav class="sidebar">
    <div class="sidebar-header">
        <h3>GERENCIA (ADMIN)</h3>
    </div>

    <ul class="list-unstyled components">
        <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard Gerencial
            </a>
        </li>

        <p>Reportes Clave</p>
        
        <li>
            {{-- ¡Este será nuestro próximo GOLAZO! --}}
            <a href="#"> {{-- {{ route('admin.kardex.index') }} --}}
                <i class="fas fa-boxes"></i> Kardex Valorizado
            </a>
        </li>
        <li>
            <a href="{{ route('contador.estado-resultados.balance-general') }}"> {{-- Reutilizamos la ruta --}}
                <i class="fas fa-balance-scale-right"></i> Balance General
            </a>
        </li>
        <li>
            <a href="#"> {{-- {{ route('admin.estado-resultados.index') }} --}}
                <i class="fas fa-chart-line"></i> Estado de Resultados
            </a>
        </li>
        <li>
            <a href="{{ route('contador.cxc.index') }}"> {{-- Reutilizamos la ruta --}}
                <i class="fas fa-hand-holding-usd"></i> Cuentas por Cobrar
            </a>
        </li>
        <li>
            <a href="#"> {{-- {{ route('admin.cxp.index') }} --}}
                <i class="fas fa-money-bill-wave"></i> Cuentas por Pagar
            </a>
        </li>

        <p>Comunicación</p>
        <li>
            <a href="#"> {{-- Reutilizamos la ruta --}}
                <i class="fas fa-bell"></i> Ver Notificaciones
            </a>
        </li>
    </ul>
</nav>