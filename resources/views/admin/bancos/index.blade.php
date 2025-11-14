@extends('layouts.admin')

@push('styles')
<style>
    /* Stats Section */
    .stats-section-bancos {
        margin-bottom: 2rem;
    }

    .stats-grid-bancos {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card-banco {
        background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card-banco:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }

    .stat-card-banco.total {
        background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
    }

    .stat-card-banco.soles {
        background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
    }

    .stat-card-banco.dolares {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card-banco h6 {
        font-size: 0.875rem;
        font-weight: 500;
        opacity: 0.9;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card-banco .value {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    /* Search Section */
    .search-section-bancos {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .search-input-banco {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem 0.75rem 3rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .search-input-banco:focus {
        border-color: #2193b0;
        box-shadow: 0 0 0 3px rgba(33, 147, 176, 0.1);
        outline: none;
    }

    .search-icon-banco {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }

    .filter-select-banco {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .filter-select-banco:focus {
        border-color: #2193b0;
        box-shadow: 0 0 0 3px rgba(33, 147, 176, 0.1);
        outline: none;
    }

    /* Table Container */
    .bancos-table-container {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .bancos-table {
        margin-bottom: 0;
    }

    .bancos-table thead {
        background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
        color: white;
    }

    .bancos-table thead th {
        border: none;
        padding: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .bancos-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .bancos-table tbody tr:hover {
        background-color: #f8fafc;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .bancos-table tbody td {
        padding: 1rem;
        vertical-align: middle;
        font-size: 0.95rem;
    }

    /* Bank Icon */
    .bank-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
        margin-right: 1rem;
    }

    .bank-info {
        display: flex;
        align-items: center;
    }

    .bank-details {
        display: flex;
        flex-direction: column;
    }

    .bank-cuenta {
        font-weight: 700;
        color: #1e293b;
        font-size: 1rem;
    }

    .bank-codigo {
        font-size: 0.85rem;
        color: #64748b;
    }

    /* Currency Badge */
    .currency-badge-banco {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .currency-badge-banco.soles {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }

    .currency-badge-banco.dolares {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
    }

    /* Action Button */
    .btn-action-banco {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border: 2px solid #f59e0b;
        color: #f59e0b;
        background: white;
        text-decoration: none;
    }

    .btn-action-banco:hover {
        background: #f59e0b;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
    }

    /* Empty State */
    .empty-state-bancos {
        text-align: center;
        padding: 3rem 1rem;
        color: #64748b;
    }

    .empty-state-bancos i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .empty-state-bancos p {
        font-size: 1.1rem;
        margin: 0;
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .bancos-table tbody tr {
        animation: fadeIn 0.5s ease;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <!-- Stats Section -->
    <div class="stats-section-bancos">
        <div class="stats-grid-bancos">
            <div class="stat-card-banco total">
                <h6>Total Cuentas Bancarias</h6>
                <p class="value" id="totalBancos">{{ $bancos->count() }}</p>
            </div>
            <div class="stat-card-banco soles">
                <h6>Cuentas en Soles</h6>
                <p class="value" id="totalSoles">0</p>
            </div>
            <div class="stat-card-banco dolares">
                <h6>Cuentas en Dólares</h6>
                <p class="value" id="totalDolares">0</p>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-section-bancos">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="position-relative">
                    <i class="fas fa-search search-icon-banco"></i>
                    <input type="text" id="searchBanco" class="form-control search-input-banco" 
                           placeholder="Buscar por cuenta o nombre del banco...">
                </div>
            </div>
            <div class="col-md-4">
                <select id="filterMoneda" class="form-select filter-select-banco">
                    <option value="">💰 Todas las Monedas</option>
                    <option value="1">🇵🇪 Soles (PEN)</option>
                    <option value="2">🇺🇸 Dólares (USD)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bancos-table-container">
        <div class="table-responsive">
            <table class="table bancos-table" id="tablaBancos">
                <thead>
                    <tr>
                        <th>Cuenta Bancaria</th>
                        <th>Nombre del Banco</th>
                        <th>Moneda</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bancos as $banco)
                        <tr data-moneda="{{ $banco->Moneda }}">
                            <td>
                                <div class="bank-info">
                                    <div class="bank-icon">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div class="bank-details">
                                        <span class="bank-cuenta">{{ $banco->Cuenta }}</span>
                                       @if(property_exists($banco, 'Codigo') && $banco->Codigo)
                                            <span class="bank-codigo">Código: {{ $banco->Codigo }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><strong>{{ $banco->Banco }}</strong></td>
                            <td>
                                @if($banco->Moneda == 1)
                                    <span class="currency-badge-banco soles">
                                        <i class="fas fa-coins"></i> Soles (PEN)
                                    </span>
                                @else
                                    <span class="currency-badge-banco dolares">
                                        <i class="fas fa-dollar-sign"></i> Dólares (USD)
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.bancos.edit', $banco->Cuenta) }}" class="btn-action-banco">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr class="no-data-row">
                            <td colspan="4">
                                <div class="empty-state-bancos">
                                    <i class="fas fa-university"></i>
                                    <p>No hay cuentas bancarias registradas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Empty State (Hidden by default) -->
        <div id="emptyStateBancos" class="empty-state-bancos" style="display: none;">
            <i class="fas fa-search"></i>
            <p>No se encontraron cuentas bancarias con los criterios de búsqueda.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchBanco');
        const filterMoneda = document.getElementById('filterMoneda');
        const rows = document.querySelectorAll('#tablaBancos tbody tr:not(.no-data-row)');
        const emptyState = document.getElementById('emptyStateBancos');
        const tableBody = document.querySelector('#tablaBancos tbody');

        // Calcular totales iniciales
        updateStats();

        function filterBancos() {
            const search = searchInput.value.toLowerCase();
            const monedaFilter = filterMoneda.value; // '1' para soles, '2' para dólares

            let visibleCount = 0;

            rows.forEach(row => {
                const cuenta = row.querySelector('.bank-cuenta').textContent.toLowerCase();
                const nombre = row.cells[1].textContent.toLowerCase();
                
                // Leer la moneda desde el badge visible
                const monedaBadge = row.querySelector('.currency-badge-banco');
                const monedaText = monedaBadge ? monedaBadge.textContent.toLowerCase() : '';
                const esSoles = monedaText.includes('soles') || monedaText.includes('pen');
                const esDolares = monedaText.includes('dólares') || monedaText.includes('dolares') || monedaText.includes('usd');

                const matchesSearch = cuenta.includes(search) || nombre.includes(search);
                
                // Filtro de moneda
                let matchesMoneda = true;
                if (monedaFilter === '1') {
                    matchesMoneda = esSoles;
                } else if (monedaFilter === '2') {
                    matchesMoneda = esDolares;
                }

                const isVisible = matchesSearch && matchesMoneda;
                row.style.display = isVisible ? '' : 'none';

                if (isVisible) visibleCount++;
            });

            // Actualizar estadísticas filtradas
            updateStatsFiltered();

            // Mostrar/ocultar estado vacío
            if (visibleCount === 0 && rows.length > 0) {
                tableBody.style.display = 'none';
                emptyState.style.display = 'block';
            } else {
                tableBody.style.display = '';
                emptyState.style.display = 'none';
            }
        }

        function updateStats() {
            let solesCount = 0;
            let dolaresCount = 0;

            rows.forEach(row => {
                // Leer desde el badge visible en lugar de data-attribute
                const monedaBadge = row.querySelector('.currency-badge-banco');
                const monedaText = monedaBadge ? monedaBadge.textContent.toLowerCase() : '';
                
                if (monedaText.includes('soles') || monedaText.includes('pen')) {
                    solesCount++;
                } else if (monedaText.includes('dólares') || monedaText.includes('dolares') || monedaText.includes('usd')) {
                    dolaresCount++;
                }
            });

            animateValue(document.getElementById('totalSoles'), 0, solesCount, 800);
            animateValue(document.getElementById('totalDolares'), 0, dolaresCount, 800);
        }

        function updateStatsFiltered() {
            let totalVisible = 0;
            let solesCount = 0;
            let dolaresCount = 0;

            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    totalVisible++;
                    
                    const monedaBadge = row.querySelector('.currency-badge-banco');
                    const monedaText = monedaBadge ? monedaBadge.textContent.toLowerCase() : '';
                    
                    if (monedaText.includes('soles') || monedaText.includes('pen')) {
                        solesCount++;
                    } else if (monedaText.includes('dólares') || monedaText.includes('dolares') || monedaText.includes('usd')) {
                        dolaresCount++;
                    }
                }
            });

            document.getElementById('totalBancos').textContent = totalVisible;
            document.getElementById('totalSoles').textContent = solesCount;
            document.getElementById('totalDolares').textContent = dolaresCount;
        }

        function animateValue(element, start, end, duration) {
            if (!element) return;
            
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

        searchInput.addEventListener('input', filterBancos);
        filterMoneda.addEventListener('change', filterBancos);
    });
</script>
@endpush