
<div class="nav-section">Principal</div>
<a class="nav-link {{ request()->routeIs('contador.dashboard.contador') ? 'active' : '' }}" href="{{ route('contador.dashboard.contador') }}">
    <i class="fas fa-chart-pie"></i>
    <span>Dashboard</span>
</a>

<div class="nav-section">Contabilidad</div>
<a class="nav-link {{ request()->is('contabilidad/asientos*') ? 'active' : '' }}" href="{{ route('contador.libro-diario.index') }}">
    <i class="fas fa-book"></i>
    <span>Libro Diario</span>
</a>

<a class="nav-link {{ request()->is('contabilidad/asientos*') ? 'active' : '' }}" href="{{ route('contador.libro-mayor.index') }}">
    <i class="fas fa-book"></i>
    <span>Libro Mayor</span>
</a>

<a class="nav-link {{ request()->is('contabilidad/plan-cuentas*') ? 'active' : '' }}" href="{{ route('contador.balance-comprobacion.index') }}">
    <i class="fa-duotone fa-solid fa-scale-balanced"></i>
    <span>Balance Comparacion</span>
</a>

<a class="nav-link {{ request()->is('contabilidad/plan-cuentas*') ? 'active' : '' }}" href="{{ route('contador.plan-cuentas.index') }}">
    <i class="fas fa-sitemap"></i>
    <span>Plan de Cuentas</span>
</a>

<a class="nav-link {{ request()->is('contabilidad/estado-finansieros*') ? 'active' : '' }}" href={{ route('contador.estado-resultados.index') }}>
    <i class="fa-sharp fa-solid fa-coins"></i>
    <span>Estados Financieros</span>
</a>

<a class="nav-link {{ request()->is('contador/flujo/cobranzas*') ? 'active' : '' }}" 
   href="{{ route('contador.flujo.cobranzas.paso1') }}">
    <i class="fa-sharp fa-solid fa-coins"></i>
    <span>Flujos diarios</span>
</a>

<a class="nav-link {{ request()->is('contabilidad/reportes*') ? 'active' : '' }}" href="{{ route('contador.reportes.index') }}">
    <i class="fas fa-file-invoice-dollar"></i>
    <span>Reportes</span>
</a>

<div class="nav-section">Tesorería</div>
<a class="nav-link {{ request()->is('tesoreria/bancos*') ? 'active' : '' }}" href="{{ route('contador.bancos.index') }}">
    <i class="fas fa-university"></i>
    <span>Bancos</span>
</a>
<a class="nav-link {{ request()->is('tesoreria/caja*') ? 'active' : '' }}" href="{{ route('contador.caja.create') }}">
    <i class="fas fa-cash-register"></i>
    <span>Caja Chica</span>
</a>
<a class="nav-link {{ request()->is('tesoreria/cobranzas*') ? 'active' : '' }}" href="{{ route('contador.cxc.index') }}">
    <i class="fas fa-hand-holding-usd"></i>
    <span>Cuentas por Cobrar</span>
</a>

<a class="nav-link {{ request()->is('tesoreria/cobranzas*') ? 'active' : '' }}" href="{{ route('contador.letras_descuento.index') }}">
    <i class="fas fa-hand-holding-usd"></i>
    <span>Letras</span>
</a>

<div class="nav-section">Inventario</div>
<a class="nav-link {{ request()->is('inventario/productos*') ? 'active' : '' }}" href="{{ route('contador.inventario.index') }}">
    <i class="fas fa-boxes"></i>
    <span>Productos</span>
</a>
<a class="nav-link {{ request()->is('inventario/stock*') ? 'active' : '' }}" href="{{ route('contador.test.stock') }}">
    <i class="fas fa-warehouse"></i>
    <span>Stock y Lotes</span>
</a>

<a class="nav-link {{ request()->is('admin/usuarios*') ? 'active' : '' }}" href="{{ route('contador.proveedores.index') }}">
    <i class="fas fa-user-shield"></i>
    <span>Lista de Proveedores</span>
</a>

<a class="nav-link {{ request()->is('ventas/listado*') ? 'active' : '' }}" href="{{ route('contador.compras.index') }}">
    <i class="fas fa-receipt"></i>
    <span>Orden de compra </span>
</a>

<a class="nav-link {{ request()->is('ventas/listado*') ? 'active' : '' }}" href="{{ route('contador.cxp.index') }}">
    <i class="fas fa-receipt"></i>
    <span>Lista de ordenes </span>
</a>

<div class="nav-section">Ventas</div>
<a class="nav-link {{ request()->is('ventas/nueva*') ? 'active' : '' }}" href="{{ route('contador.facturas.index') }}">
    <i class="fas fa-cart-plus"></i>
    <span>Nueva Venta</span>
</a>


<a class="nav-link {{ request()->is('ventas/listado*') ? 'active' : '' }}" href="{{ route('contador.notas-credito.index') }}">
    <i class="fas fa-receipt"></i>
    <span>Descuentos</span>
</a>

<a class="nav-link {{ request()->is('ventas/clientes*') ? 'active' : '' }}" href="{{ route('contador.clientes.index') }}">
    <i class="fas fa-users"></i>
    <span>Clientes</span>
</a>

<div class="nav-section">Administración</div>

<a class="nav-link {{ request()->routeIs('contador.reportes.auditoria.*') ? 'active' : '' }}" 
   href="{{ route('contador.reportes.auditoria.libro_diario') }}">
    <i class="fas fa-history"></i>
    <span>Auditoría</span>
</a>
