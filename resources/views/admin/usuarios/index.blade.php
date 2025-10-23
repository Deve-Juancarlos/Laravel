@extends('layouts.admin')

@push('styles')
<style>
    /* Stats Section */
    .stats-section-users {
        margin-bottom: 2rem;
    }

    .stats-grid-users {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card-user {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card-user:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }

    .stat-card-user.total {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card-user.admins {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-card-user.regulares {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stat-card-user h6 {
        font-size: 0.875rem;
        font-weight: 500;
        opacity: 0.9;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card-user .value {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    /* Search and Filters */
    .search-filters-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .search-input-user {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem 0.75rem 3rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .search-input-user:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }

    /* Filter Buttons */
    .filter-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .filter-btn-user {
        padding: 0.6rem 1.2rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        color: #475569;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-btn-user:hover {
        border-color: #667eea;
        color: #667eea;
        background: #f8f9ff;
    }

    .filter-btn-user.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: transparent;
    }

    /* Table Container */
    .users-table-container {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .users-table {
        margin-bottom: 0;
    }

    .users-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .users-table thead th {
        border: none;
        padding: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .users-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .users-table tbody tr:hover {
        background-color: #f8fafc;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .users-table tbody td {
        padding: 1rem;
        vertical-align: middle;
        font-size: 0.95rem;
    }

    /* User Avatar */
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        color: white;
        margin-right: 0.75rem;
        text-transform: uppercase;
    }

    .user-info {
        display: flex;
        align-items: center;
    }

    .user-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 1rem;
    }

    /* Role Badges */
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .role-badge.admin {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .role-badge.user {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }

    /* Action Buttons */
    .btn-action-user {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border: 2px solid #667eea;
        color: #667eea;
        background: white;
        text-decoration: none;
    }

    .btn-action-user:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
    }

    /* Current User Badge */
    .current-user-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.9rem;
        border-radius: 20px;
        background-color: #fef3c7;
        color: #92400e;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* Alert Success */
    .alert-success-custom {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border: none;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        color: #065f46;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 4px rgba(5, 150, 105, 0.1);
    }

    .alert-success-custom i {
        font-size: 1.25rem;
    }

    /* Empty State */
    .empty-state-users {
        text-align: center;
        padding: 3rem 1rem;
        color: #64748b;
    }

    .empty-state-users i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .empty-state-users p {
        font-size: 1.1rem;
        margin: 0;
    }

    /* Animation */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .users-table tbody tr {
        animation: slideIn 0.4s ease;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <!-- Stats Section -->
    <div class="stats-section-users">
        <div class="stats-grid-users">
            <div class="stat-card-user total">
                <h6>Total Usuarios</h6>
                <p class="value" id="totalUsers">{{ $usuarios->count() }}</p>
            </div>
            <div class="stat-card-user admins">
                <h6>Administradores</h6>
                <p class="value" id="totalAdmins">0</p>
            </div>
            <div class="stat-card-user regulares">
                <h6>Usuarios Regulares</h6>
                <p class="value" id="totalRegulares">0</p>
            </div>
        </div>
    </div>

    <!-- Success Alert -->
    @if(session('success'))
        <div class="alert-success-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="search-filters-section">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="position-relative">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchUser" class="form-control search-input-user" 
                           placeholder="Buscar usuario por nombre...">
                </div>
            </div>
            <div class="col-md-6">
                <div class="filter-buttons">
                    <button class="filter-btn-user active" data-filter="all">
                        <i class="fas fa-users"></i> Todos
                    </button>
                    <button class="filter-btn-user" data-filter="admin">
                        <i class="fas fa-user-shield"></i> Administradores
                    </button>
                    <button class="filter-btn-user" data-filter="user">
                        <i class="fas fa-user"></i> Usuarios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="users-table-container">
        <div class="table-responsive">
            <table class="table users-table" id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Tipo de Rol</th>
                        <th>ID Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $u)
                        @php
                            $tipo = strtoupper($u->tipousuario) === 'ADMIN' ? 'admin' : 'user';
                            $initials = strtoupper(substr($u->usuario, 0, 2));
                            $colors = ['#667eea', '#f093fb', '#4facfe', '#56ab2f', '#eb3349', '#2193b0'];
                            $colorIndex = ord($u->usuario[0]) % count($colors);
                            $bgColor = $colors[$colorIndex];
                        @endphp
                        <tr data-tipo="{{ $tipo }}">
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar" style="background: {{ $bgColor }}">
                                        {{ $initials }}
                                    </div>
                                    <span class="user-name">{{ $u->usuario }}</span>
                                </div>
                            </td>
                            <td>
                                @if($tipo === 'admin')
                                    <span class="role-badge admin">
                                        <i class="fas fa-user-shield"></i> Administrador
                                    </span>
                                @else
                                    <span class="role-badge user">
                                        <i class="fas fa-user"></i> Usuario
                                    </span>
                                @endif
                            </td>
                            <td>{{ $u->idusuario ?? '—' }}</td>
                            <td>
                                @if($u->usuario !== Auth::user()->usuario)
                                    <a href="{{ route('admin.usuarios.roles', $u->usuario) }}" class="btn-action-user">
                                        <i class="fas fa-user-tag"></i> Cambiar Rol
                                    </a>
                                @else
                                    <span class="current-user-badge">
                                        <i class="fas fa-user-circle"></i> Tu Cuenta
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="no-data-row">
                            <td colspan="4">
                                <div class="empty-state-users">
                                    <i class="fas fa-users-slash"></i>
                                    <p>No hay usuarios registrados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Empty State (Hidden by default) -->
        <div id="emptyStateUsers" class="empty-state-users" style="display: none;">
            <i class="fas fa-search"></i>
            <p>No se encontraron usuarios con los criterios de búsqueda.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchUser');
    const filterButtons = document.querySelectorAll('.filter-btn-user');
    const rows = document.querySelectorAll('#tablaUsuarios tbody tr:not(.no-data-row)');
    const emptyState = document.getElementById('emptyStateUsers');
    const tableBody = document.querySelector('#tablaUsuarios tbody');

    let currentFilter = 'all';

    // Calcular totales iniciales
    updateStats();

    // Search functionality
    searchInput.addEventListener('input', function() {
        filterUsers();
    });

    // Filter buttons
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            filterUsers();
        });
    });

    function filterUsers() {
        const search = searchInput.value.toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
            const userName = row.querySelector('.user-name').textContent.toLowerCase();
            const userType = row.dataset.tipo;

            const matchesSearch = userName.includes(search);
            const matchesFilter = currentFilter === 'all' || userType === currentFilter;

            const isVisible = matchesSearch && matchesFilter;
            row.style.display = isVisible ? '' : 'none';

            if (isVisible) visibleCount++;
        });

        // Update stats
        updateStatsFiltered();

        // Show/hide empty state
        if (visibleCount === 0 && rows.length > 0) {
            tableBody.style.display = 'none';
            emptyState.style.display = 'block';
        } else {
            tableBody.style.display = '';
            emptyState.style.display = 'none';
        }
    }

    function updateStats() {
        let adminCount = 0;
        let userCount = 0;

        rows.forEach(row => {
            const tipo = row.dataset.tipo;
            if (tipo === 'admin') adminCount++;
            if (tipo === 'user') userCount++;
        });

        animateValue(document.getElementById('totalAdmins'), 0, adminCount, 800);
        animateValue(document.getElementById('totalRegulares'), 0, userCount, 800);
    }

    function updateStatsFiltered() {
        let totalVisible = 0;
        let adminCount = 0;
        let userCount = 0;

        rows.forEach(row => {
            if (row.style.display !== 'none') {
                totalVisible++;
                const tipo = row.dataset.tipo;
                if (tipo === 'admin') adminCount++;
                if (tipo === 'user') userCount++;
            }
        });

        document.getElementById('totalUsers').textContent = totalVisible;
        document.getElementById('totalAdmins').textContent = adminCount;
        document.getElementById('totalRegulares').textContent = userCount;
    }

    function animateValue(element, start, end, duration) {
        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            element.textContent = Math.round(current);
        }, 16);
    }
});
</script>
@endpush