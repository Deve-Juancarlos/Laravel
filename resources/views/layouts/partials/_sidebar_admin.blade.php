<div class="nav-section">Principal</div>
<a class="nav-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
    <i class="fas fa-chart-pie"></i>
    <span>Dashboard</span>
</a>

<div class="nav-section">Reportes de Ventas</div>
<a class="nav-link {{ request()->routeIs('admin.reportes.ventas-periodo') ? 'active' : '' }}" href="{{ route('admin.reportes.ventas-periodo') }}">
    <i class="fas fa-calendar-alt"></i>
    <span>Ventas por Período</span>
</a>

<a class="nav-link {{ request()->routeIs('admin.reportes.ventas-cliente') ? 'active' : '' }}" href="{{ route('admin.reportes.ventas-cliente') }}">
    <i class="fas fa-users"></i>
    <span>Ventas por Cliente</span>
</a>

<a class="nav-link {{ request()->routeIs('admin.reportes.ventas-producto') ? 'active' : '' }}" href="{{ route('admin.reportes.ventas-producto') }}">
    <i class="fas fa-boxes"></i>
    <span>Ventas por Producto</span>
</a>

<a class="nav-link {{ request()->routeIs('admin.reportes.ventas-vendedor') ? 'active' : '' }}" href="{{ route('admin.reportes.ventas-vendedor') }}">
    <i class="fas fa-user-tie"></i>
    <span>Desempeño Vendedores</span>
</a>

<div class="nav-section">Reportes Financieros</div>
<a class="nav-link {{ request()->routeIs('admin.reportes.cuentas-cobrar') ? 'active' : '' }}" href="{{ route('admin.reportes.cuentas-cobrar') }}">
    <i class="fas fa-hand-holding-usd"></i>
    <span>Cuentas por Cobrar</span>
</a>

<a class="nav-link {{ request()->routeIs('admin.cuentas-corrientes.*') ? 'active' : '' }}" href="{{ route('admin.cuentas-corrientes.index') }}">
    <i class="fas fa-exchange-alt"></i>
    <span>Cuentas Corrientes</span>
</a>

<div class="nav-section">Reportes de Inventario</div>
<a class="nav-link {{ request()->routeIs('admin.reportes.inventario-valorado') ? 'active' : '' }}" href="{{ route('admin.reportes.inventario-valorado') }}">
    <i class="fas fa-warehouse"></i>
    <span>Inventario Valorizado</span>{{-- Inventario general + servicio + vistas  --}}
</a>

<a class="nav-link {{ request()->routeIs('admin.reportes.productos-vencer') ? 'active' : '' }}" href="{{ route('admin.reportes.productos-vencer') }}">
    <i class="fas fa-exclamation-triangle"></i>
    <span>Productos por Vencer</span>
</a>

<a class="nav-link {{ request()->routeIs('admin.reportes.rentabilidad-productos') ? 'active' : '' }}" href="{{ route('admin.reportes.rentabilidad-productos') }}">
    <i class="fas fa-chart-line"></i>
    <span>Rentabilidad</span>
</a>

<div class="nav-section">Reportes SUNAT</div>

<a class="nav-link {{ request()->routeIs('admin.reportes.sunat-compras') ? 'active' : '' }}" href="{{ route('admin.reportes.sunat-compras') }}">
    <i class="fas fa-file-invoice"></i>
    <span>Registro de Compras</span>
</a>

<div class="nav-section">Gestión Financiera</div>
<a class="nav-link {{ request()->routeIs('admin.bancos.*') ? 'active' : '' }}" href="{{ route('admin.bancos.index') }}">
    <i class="fas fa-university"></i>
    <span>Bancos y Cuentas</span>
</a>



<div class="nav-section">Administración del Sistema</div>
<a class="nav-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}" href="{{ route('admin.usuarios.index') }}">
    <i class="fas fa-user-shield"></i>
    <span>Usuarios y Permisos</span>
</a>

<a class="nav-link {{ request()->routeIs('admin.solicitudes.asiento.*') ? 'active' : '' }}" href="{{ route('admin.solicitudes.asiento.index') }}">
    <i class="fas fa-tasks"></i>
    <span>Solicitudes Asientos</span>
</a>

<a class="nav-link {{ request()->routeIs('admin.auditoria.*') ? 'active' : '' }}" href="{{ route('admin.auditoria.index') }}">
    <i class="fas fa-history"></i>
    <span>Auditoría del Sistema</span>
</a>

<a class="nav-link {{ request()->routeIs('admin.planillas.*') ? 'active' : '' }}" href="{{ route('admin.planillas.index') }}">
    <i class="fas fa-file-invoice-dollar"></i>
    <span>Planillas Administrativas</span>{{-- Revisar el controlador y crear el servicio y las vistas --}}
</a>





