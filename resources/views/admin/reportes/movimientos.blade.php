@use('Illuminate\Support\Str')
@extends('layouts.admin')

@push('styles')
<style>
    /* Variables CSS */
    :root {
        --primary-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        --success-gradient: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
        --danger-gradient: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        --info-gradient: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
        --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
        --shadow-lg: 0 8px 15px rgba(0,0,0,0.2);
    }

    /* Stats Section */
    .stats-section-movs {
        margin-bottom: 1.5rem;
    }

    .stats-grid-movs {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card-mov {
        background: var(--primary-gradient);
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        color: white;
        box-shadow: var(--shadow-md);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .stat-card-mov::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .stat-card-mov:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .stat-card-mov.total {
        background: var(--primary-gradient);
    }

    .stat-card-mov.ingresos {
        background: var(--success-gradient);
    }

    .stat-card-mov.egresos {
        background: var(--danger-gradient);
    }

    .stat-card-mov.balance {
        background: var(--info-gradient);
    }

    .stat-card-mov h6 {
        font-size: 0.813rem;
        font-weight: 600;
        opacity: 0.95;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        z-index: 1;
    }

    .stat-card-mov .value {
        font-size: 1.875rem;
        font-weight: 700;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    /* Filters Section */
    .filters-section-movs {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-sm);
    }

    .filter-input-mov,
    .filter-select-mov {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.938rem;
        width: 100%;
        background: white;
    }

    .filter-input-mov:focus,
    .filter-select-mov:focus {
        border-color: #11998e;
        box-shadow: 0 0 0 4px rgba(17, 153, 142, 0.1);
        outline: none;
    }

    .input-group-text {
        background: white;
        border: 2px solid #e2e8f0;
        border-right: none;
        border-radius: 10px 0 0 10px;
    }

    .border-start-0 {
        border-left: none !important;
        border-radius: 0 10px 10px 0 !important;
    }

    .btn-clear-filters {
        background: #f1f5f9;
        color: #64748b;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.938rem;
    }

    .btn-clear-filters:hover {
        background: #e2e8f0;
        color: #334155;
        transform: translateY(-1px);
    }

    /* Table Styles */
    .movimientos-table-container {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .movimientos-table {
        margin-bottom: 0;
        font-size: 0.938rem;
    }

    .movimientos-table thead {
        background: var(--primary-gradient);
        color: white;
    }

    .movimientos-table thead th {
        border: none;
        padding: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.813rem;
        letter-spacing: 0.5px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .movimientos-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .movimientos-table tbody tr:hover {
        background-color: #f8fafc;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .movimientos-table tbody td {
        padding: 1rem;
        vertical-align: middle;
        font-size: 0.938rem;
    }

    /* Origin Badges */
    .origin-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.813rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .origin-badge.caja {
        background: #ede9fe;
        color: #6b21a8;
    }

    .origin-badge.banco {
        background: #d1fae5;
        color: #065f46;
    }

    /* Type Badges */
    .type-badge-mov {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.813rem;
        font-weight: 600;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .type-badge-mov.ingreso {
        background: #d1fae5;
        color: #065f46;
    }

    .type-badge-mov.egreso {
        background: #fee2e2;
        color: #991b1b;
    }

    .type-badge-mov.cobranza {
        background: #dbeafe;
        color: #1e40af;
    }

    .type-badge-mov.otro {
        background: #f1f5f9;
        color: #475569;
    }

    /* Currency Badges */
    .currency-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .currency-badge.pen {
        background: #fef3c7;
        color: #92400e;
    }

    .currency-badge.usd {
        background: #dbeafe;
        color: #1e40af;
    }

    /* Amount Styling */
    .amount-ingreso {
        color: #059669;
        font-weight: 700;
        font-family: 'Courier New', monospace;
    }

    .amount-egreso {
        color: #dc2626;
        font-weight: 700;
        font-family: 'Courier New', monospace;
    }

    /* Export Button */
    .btn-export-movs {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.625rem;
        white-space: nowrap;
        text-decoration: none;
    }

    .btn-export-movs:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(17, 153, 142, 0.4);
        color: white;
    }

    /* Empty State */
    .empty-state-movs {
        text-align: center;
        padding: 4rem 1.5rem;
        color: #64748b;
    }

    .empty-state-movs i {
        font-size: 4rem;
        margin-bottom: 1.5rem;
        opacity: 0.2;
        display: block;
    }

    .empty-state-movs p {
        font-size: 1.125rem;
        margin: 0;
        font-weight: 500;
    }

    /* Result Counter */
    .result-counter {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
    }

    /* Mobile Responsive */
    @media (max-width: 992px) {
        .stats-grid-movs {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.875rem;
        }
    }

    @media (max-width: 768px) {
        .stats-grid-movs {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .stat-card-mov {
            padding: 1rem 1.25rem;
        }

        .stat-card-mov h6 {
            font-size: 0.75rem;
        }

        .stat-card-mov .value {
            font-size: 1.5rem;
        }

        .filters-section-movs {
            padding: 1rem;
        }

        .filter-input-mov,
        .filter-select-mov {
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
        }

        .movimientos-table-container {
            padding: 1rem;
            border-radius: 12px;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -1rem;
            padding: 0 1rem;
        }

        .movimientos-table {
            font-size: 0.813rem;
            min-width: 800px;
        }

        .movimientos-table thead th {
            padding: 0.75rem 0.5rem;
            font-size: 0.75rem;
        }

        .movimientos-table tbody td {
            padding: 0.75rem 0.5rem;
            font-size: 0.813rem;
        }

        .origin-badge,
        .type-badge-mov {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            gap: 0.3rem;
        }

        .currency-badge {
            padding: 0.3rem 0.6rem;
            font-size: 0.688rem;
        }

        .btn-export-movs {
            width: 100%;
            justify-content: center;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }

        .btn-clear-filters {
            width: 100%;
            justify-content: center;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
        }

        .empty-state-movs {
            padding: 2.5rem 1rem;
        }

        .empty-state-movs i {
            font-size: 3rem;
        }

        .empty-state-movs p {
            font-size: 1rem;
        }
    }

    @media (max-width: 576px) {
        .stat-card-mov h6 {
            font-size: 0.688rem;
        }

        .stat-card-mov .value {
            font-size: 1.375rem;
        }

        .stat-card-mov::before {
            width: 70px;
            height: 70px;
        }
    }

    /* Animation */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .movimientos-table tbody tr {
        animation: slideInUp 0.3s ease;
    }

    /* Print Styles */
    @media print {
        .filters-section-movs,
        .btn-export-movs,
        .btn-clear-filters {
            display: none !important;
        }

        .movimientos-table-container {
            box-shadow: none;
        }

        .movimientos-table tbody tr:hover {
            background-color: transparent;
        }
    }
</style>
<style>
    /* Pagination Styles */
    .pagination {
        gap: 0.5rem;
    }

    .pagination .page-link {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        color: #667eea;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .pagination .page-link:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: #667eea;
        transform: translateY(-2px);
    }

    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
    }

    .pagination .page-item.disabled .page-link {
        background: #f1f5f9;
        border-color: #e2e8f0;
        color: #94a3b8;
    }
</style>
@endpush

@section('content')
    <div class="content-wrapper">
        <!-- Stats Section -->
        <div class="stats-section-movs">
            <div class="stats-grid-movs">
                <div class="stat-card-mov total">
                    <h6>Total Movimientos</h6>
                    <p class="value" id="totalMovs">{{ $movimientos->total() }}</p>
                </div>
                <div class="stat-card-mov ingresos">
                    <h6>Total Ingresos</h6>
                    <p class="value" id="totalIngresos">S/ 0.00</p>
                </div>
                <div class="stat-card-mov egresos">
                    <h6>Total Egresos</h6>
                    <p class="value" id="totalEgresos">S/ 0.00</p>
                </div>
                <div class="stat-card-mov balance">
                    <h6>Balance</h6>
                    <p class="value" id="balance">S/ 0.00</p>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section-movs">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="searchMov" class="form-control filter-input-mov border-start-0" 
                            placeholder="Buscar documento...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select id="filterOrigen" class="form-select filter-select-mov">
                        <option value="">Todos los orígenes</option>
                        <option value="caja">Caja</option>
                        <option value="banco">Banco</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filterTipoMov" class="form-select filter-select-mov">
                        <option value="">Todos los tipos</option>
                        <option value="ingreso">Ingreso</option>
                        <option value="egreso">Egreso</option>
                        <option value="cobranza">Cobranza</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" id="filterFechaMov" class="form-control filter-input-mov">
                </div>
                <div class="col-md-1">
                    <select id="filterMonedaMov" class="form-select filter-select-mov">
                        <option value="">Moneda</option>
                        <option value="pen">PEN</option>
                        <option value="usd">USD</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="#" class="btn btn-export-movs w-100">
                        <i class="fas fa-file-excel"></i> Exportar
                    </a>    
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="movimientos-table-container">
            <div class="table-responsive">
                <table class="table movimientos-table" id="tablaMovimientos">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Origen</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Moneda</th>
                            <th>Monto</th>
                            <th>Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $m)
                            @php
                                $origen = (isset($m->Numero) && !isset($m->Cuenta)) ? 'caja' : 'banco';
                                $tipoNum = $m->Tipo ?? 0;
                                $tipoMap = match($tipoNum) {
                                    1 => ['text' => 'Ingreso', 'class' => 'ingreso'],
                                    2 => ['text' => 'Egreso', 'class' => 'egreso'],
                                    5 => ['text' => 'Cobranza', 'class' => 'cobranza'],
                                    default => ['text' => 'Otro', 'class' => 'otro']
                                };
                                $monto = $m->Monto ?? 0;
                                $moneda = ($m->Moneda ?? 1) == 1 ? 'pen' : 'usd';
                            @endphp
                            <tr data-origen="{{ $origen }}"
                                data-tipo="{{ $tipoMap['class'] }}"
                                data-moneda="{{ $moneda }}"
                                data-monto="{{ $monto }}"
                                data-monto-tipo="{{ $tipoMap['class'] }}">
                                <td><strong>{{ $m->Documento ?? $m->Cuenta }}</strong></td>
                                <td>
                                    <span class="origin-badge {{ $origen }}">
                                        @if($origen === 'caja')
                                            <i class="fas fa-cash-register"></i> Caja
                                        @else
                                            <i class="fas fa-university"></i> Banco
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="type-badge-mov {{ $tipoMap['class'] }}">
                                        {{ $tipoMap['text'] }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($m->Fecha ?? $m->fecha)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="currency-badge {{ $moneda }}">
                                        @if($moneda === 'pen')
                                            <i class="fas fa-coins"></i> PEN
                                        @else
                                            <i class="fas fa-dollar-sign"></i> USD
                                        @endif
                                    </span>
                                </td>
                                <td class="{{ $tipoMap['class'] === 'ingreso' || $tipoMap['class'] === 'cobranza' ? 'amount-ingreso' : 'amount-egreso' }}">
                                    {{ $tipoMap['class'] === 'ingreso' || $tipoMap['class'] === 'cobranza' ? '+' : '-' }} S/ {{ number_format($monto, 2) }}
                                </td>
                                <td>{{ $m->Razon ?? $m->Clase ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr class="no-data-row">
                                <td colspan="7">
                                    <div class="empty-state-movs">
                                        <i class="fas fa-inbox"></i>
                                        <p>No hay movimientos registrados.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Empty State (Hidden by default) -->
            <div id="emptyStateMov" class="empty-state-movs" style="display: none;">
                <i class="fas fa-search"></i>
                <p>No se encontraron resultados con los filtros aplicados.</p>
            </div>

            <div class="d-flex justify-content-end mt-3 flex-column align-items-end">
                <div class="text-muted small mb-1">
                    Showing {{ $movimientos->firstItem() }} to {{ $movimientos->lastItem() }} of {{ $movimientos->total() }} results
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        {{-- Previous --}}
                        @if ($movimientos->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <span aria-hidden="true">&laquo;</span> Previous
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $movimientos->previousPageUrl() }}" rel="prev">
                                    <span aria-hidden="true">&laquo;</span> Previous
                                </a>
                            </li>
                        @endif

                        {{-- Números de página --}}
                        @foreach ($movimientos->links()->elements[0] as $page => $url)
                            @if ($page == $movimientos->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        {{-- Next --}}
                        @if ($movimientos->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $movimientos->nextPageUrl() }}" rel="next">
                                    Next <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">
                                    Next <span aria-hidden="true">&raquo;</span>
                                </span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchMov');
        const filterOrigen = document.getElementById('filterOrigen');
        const filterTipo = document.getElementById('filterTipoMov');
        const filterFecha = document.getElementById('filterFechaMov');
        const filterMoneda = document.getElementById('filterMonedaMov');
        const rows = document.querySelectorAll('#tablaMovimientos tbody tr:not(.no-data-row)');
        const emptyState = document.getElementById('emptyStateMov');
        const tableBody = document.querySelector('#tablaMovimientos tbody');

        updateStats();

        function filterTable() {
            const search = searchInput.value.toLowerCase().trim();
            const origen = filterOrigen.value.toLowerCase();
            const tipo = filterTipo.value.toLowerCase();
            const fecha = filterFecha.value;
            const moneda = filterMoneda.value.toLowerCase();

            let visibleCount = 0;
            let totalIngresos = 0;
            let totalEgresos = 0;

            rows.forEach(row => {
                const allText = Array.from(row.cells).map(cell => 
                    cell.textContent.toLowerCase().trim()
                ).join(' ');
                
                const origenData = (row.dataset.origen || '').toLowerCase().trim();
                const tipoData = (row.dataset.tipo || '').toLowerCase().trim();
                const monedaData = (row.dataset.moneda || '').toLowerCase().trim();
                const monto = parseFloat(row.dataset.monto) || 0;
                
                // NUEVA LÓGICA: Leer el signo del monto (+/-)
                const montoCell = row.cells[5];
                const montoText = montoCell ? montoCell.textContent.trim() : '';
                const esIngreso = montoText.startsWith('+');
                const esEgreso = montoText.startsWith('-');

                const fechaCell = row.cells[3] ? row.cells[3].textContent.trim() : '';

                const matchesSearch = !search || allText.includes(search);
                const matchesOrigen = !origen || origenData === origen;
                const matchesTipo = !tipo || tipoData === tipo;
                const matchesMoneda = !moneda || monedaData === moneda;
                
                let matchesFecha = true;
                if (fecha) {
                    const [year, month, day] = fecha.split('-');
                    const fechaFormateada = `${day}/${month}/${year}`;
                    matchesFecha = fechaCell.includes(fechaFormateada);
                }

                const isVisible = matchesSearch && matchesOrigen && matchesTipo && matchesFecha && matchesMoneda;
                row.style.display = isVisible ? '' : 'none';

                if (isVisible) {
                    visibleCount++;
                    if (esIngreso) {
                        totalIngresos += monto;
                    } else if (esEgreso) {
                        totalEgresos += monto;
                    }
                }
            });

            const balance = totalIngresos - totalEgresos;

            const currentTotal = parseInt(document.getElementById('totalMovs').textContent.replace(/[^\d]/g, '')) || 0;
            animateValue(document.getElementById('totalMovs'), currentTotal, visibleCount, 300, false);
            animateValue(document.getElementById('totalIngresos'), 0, totalIngresos, 600, true);
            animateValue(document.getElementById('totalEgresos'), 0, totalEgresos, 600, true);
            animateValue(document.getElementById('balance'), 0, balance, 800, true);

            if (visibleCount === 0 && rows.length > 0) {
                tableBody.style.display = 'none';
                emptyState.style.display = 'block';
            } else {
                tableBody.style.display = '';
                emptyState.style.display = 'none';
            }
        }

        function updateStats() {
            let totalIngresos = 0;
            let totalEgresos = 0;

            rows.forEach(row => {
                const monto = parseFloat(row.dataset.monto) || 0;
                const montoCell = row.cells[5];
                const montoText = montoCell ? montoCell.textContent.trim() : '';
                const esIngreso = montoText.startsWith('+');
                const esEgreso = montoText.startsWith('-');

                if (esIngreso) {
                    totalIngresos += monto;
                } else if (esEgreso) {
                    totalEgresos += monto;
                }
            });

            const balance = totalIngresos - totalEgresos;

            animateValue(document.getElementById('totalIngresos'), 0, totalIngresos, 1000, true);
            animateValue(document.getElementById('totalEgresos'), 0, totalEgresos, 1000, true);
            animateValue(document.getElementById('balance'), 0, balance, 1200, true);
        }

        function animateValue(element, start, end, duration, isCurrency = false) {
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
                
                if (isCurrency) {
                    const formatted = Math.abs(current).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    element.textContent = 'S/ ' + formatted;
                } else {
                    element.textContent = Math.round(current);
                }
            }, 16);
        }

        function clearFilters() {
            searchInput.value = '';
            filterOrigen.value = '';
            filterTipo.value = '';
            filterFecha.value = '';
            filterMoneda.value = '';
            filterTable();
        }

        if (searchInput) searchInput.addEventListener('input', filterTable);
        if (filterOrigen) filterOrigen.addEventListener('change', filterTable);
        if (filterTipo) filterTipo.addEventListener('change', filterTable);
        if (filterFecha) filterFecha.addEventListener('change', filterTable);
        if (filterMoneda) filterMoneda.addEventListener('change', filterTable);

        const clearBtn = document.getElementById('clearFilters');
        if (clearBtn) {
            clearBtn.addEventListener('click', clearFilters);
        }

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                if (searchInput) searchInput.focus();
            }
            if (e.key === 'Escape') {
                clearFilters();
            }
        });
    });
</script>
@endpush