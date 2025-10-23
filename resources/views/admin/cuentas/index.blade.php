@extends('layouts.admin')

@section('title', 'Cuentas Corrientes')

@push('styles')
<style>
    /* ===== CUENTAS CORRIENTES STYLES ===== */
    .cuentas-container {
        animation: fadeIn 0.4s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* HEADER CARD */
    .cuentas-header {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        padding: 1.75rem;
        border-radius: 16px 16px 0 0;
        box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
    }

    .cuentas-header h4 {
        margin: 0;
        color: white;
        font-weight: 800;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .cuentas-badge {
        background: rgba(255, 255, 255, 0.25);
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 25px;
        font-weight: 700;
        font-size: 0.95rem;
        backdrop-filter: blur(10px);
    }

    /* STATISTICS CARDS */
    .stats-section {
        background: white;
        padding: 1.5rem;
        border-bottom: 2px solid #f5f7fa;
    }

    .stats-grid-mini {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }

    .stat-mini-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        padding: 1.25rem;
        border-radius: 12px;
        border-left: 4px solid;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-mini-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .stat-mini-card.total { border-left-color: #3498db; }
    .stat-mini-card.deudor { border-left-color: #e74c3c; }
    .stat-mini-card.acreedor { border-left-color: #27ae60; }

    .stat-mini-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-mini-card.total .stat-mini-icon {
        background: rgba(52, 152, 219, 0.1);
        color: #3498db;
    }

    .stat-mini-card.deudor .stat-mini-icon {
        background: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
    }

    .stat-mini-card.acreedor .stat-mini-icon {
        background: rgba(39, 174, 96, 0.1);
        color: #27ae60;
    }

    .stat-mini-details h6 {
        margin: 0 0 0.25rem 0;
        color: #7f8c8d;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-mini-details .value {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 800;
        color: #2c3e50;
    }

    /* FILTERS */
    .filters-section {
        background: white;
        padding: 1.5rem;
        border-bottom: 2px solid #f5f7fa;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 1rem;
        align-items: center;
    }

    .filter-group {
        position: relative;
    }

    .filter-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #95a5a6;
        font-size: 1rem;
        pointer-events: none;
    }

    .filter-input, .filter-select {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        transition: all 0.3s;
        font-size: 0.95rem;
    }

    .filter-select {
        padding-left: 2.75rem;
        cursor: pointer;
        appearance: none;
        background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2395a5a6' d='M6 9L1 4h10z'/%3E%3C/svg%3E") no-repeat right 1rem center;
        padding-right: 2.5rem;
    }

    .filter-input:focus, .filter-select:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
    }

    .btn-export {
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        color: white;
        border: none;
        font-weight: 700;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
    }

    .btn-export:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
    }

    /* TABLE CONTAINER */
    .table-container {
        background: white;
        border-radius: 0 0 16px 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .cuentas-table {
        margin: 0;
        width: 100%;
    }

    .cuentas-table thead {
        background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
    }

    .cuentas-table thead th {
        padding: 1.25rem 1rem;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: white;
        border: none;
        white-space: nowrap;
    }

    .cuentas-table tbody tr {
        transition: all 0.3s;
        border-bottom: 1px solid #ecf0f1;
    }

    .cuentas-table tbody tr:hover {
        background: linear-gradient(135deg, #f0f8ff 0%, #fff 100%);
        transform: scale(1.002);
        box-shadow: 0 2px 8px rgba(52, 152, 219, 0.1);
    }

    .cuentas-table tbody tr.deudor {
        background: rgba(231, 76, 60, 0.03);
    }

    .cuentas-table tbody tr.deudor:hover {
        background: rgba(231, 76, 60, 0.08);
    }

    .cuentas-table tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    /* DOCUMENT TYPE BADGES */
    .doc-badge {
        padding: 0.4rem 0.9rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
    }

    .doc-badge.factura {
        background: rgba(52, 152, 219, 0.15);
        color: #3498db;
    }

    .doc-badge.boleta {
        background: rgba(155, 89, 182, 0.15);
        color: #9b59b6;
    }

    .doc-badge.letra {
        background: rgba(243, 156, 18, 0.15);
        color: #f39c12;
    }

    .doc-badge.nota-credito {
        background: rgba(39, 174, 96, 0.15);
        color: #27ae60;
    }

    .doc-badge.otros {
        background: rgba(149, 165, 166, 0.15);
        color: #95a5a6;
    }

    /* STATUS BADGES */
    .status-badge {
        padding: 0.45rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .status-badge.deudor {
        background: rgba(231, 76, 60, 0.15);
        color: #e74c3c;
    }

    .status-badge.acreedor {
        background: rgba(39, 174, 96, 0.15);
        color: #27ae60;
    }

    /* AMOUNT STYLING */
    .amount-negative {
        color: #e74c3c;
        font-weight: 800;
        font-size: 1.05rem;
    }

    .amount-positive {
        color: #27ae60;
        font-weight: 700;
        font-size: 1.05rem;
    }

    /* EMPTY STATE */
    .empty-state-container {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state-icon {
        font-size: 5rem;
        color: #ecf0f1;
        margin-bottom: 1.5rem;
    }

    .empty-state-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #7f8c8d;
        margin-bottom: 0.5rem;
    }

    .empty-state-text {
        color: #95a5a6;
        font-size: 1rem;
    }

    /* PAGINATION */
    .pagination-container {
        padding: 1.5rem;
        background: white;
        border-radius: 0 0 16px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* LOADING OVERLAY */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loading-spinner {
        background: white;
        padding: 2rem 3rem;
        border-radius: 16px;
        text-align: center;
    }

    .loading-spinner i {
        font-size: 3rem;
        color: #3498db;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* RESPONSIVE */
    @media (max-width: 1200px) {
        .filters-grid {
            grid-template-columns: 1fr;
        }

        .btn-export {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 768px) {
        .stats-grid-mini {
            grid-template-columns: 1fr;
        }

        .cuentas-table {
            font-size: 0.85rem;
        }

        .cuentas-table thead th,
        .cuentas-table tbody td {
            padding: 0.85rem 0.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="cuentas-container">
    <!-- HEADER -->
    <div class="cuentas-header d-flex justify-content-between align-items-center">
        <h4>
            <i class="fas fa-exchange-alt"></i>
            Cuentas Corrientes por Cliente
        </h4>
        <div class="cuentas-badge">
            {{ $cuentas->total() }} {{ $cuentas->total() == 1 ? 'registro' : 'registros' }}
        </div>
    </div>

    <!-- STATISTICS -->
    <div class="stats-section">
        <div class="stats-grid-mini">
            <div class="stat-mini-card total">
                <div class="stat-mini-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-mini-details">
                    <h6>Total Registros</h6>
                    <p class="value">{{ $cuentas->total() }}</p>
                </div>
            </div>

            <div class="stat-mini-card deudor">
                <div class="stat-mini-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stat-mini-details">
                    <h6>Saldos Negativos</h6>
                    <p class="value" id="countDeudor">0</p>
                </div>
            </div>

            <div class="stat-mini-card acreedor">
                <div class="stat-mini-icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="stat-mini-details">
                    <h6>Saldos Positivos</h6>
                    <p class="value" id="countAcreedor">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTERS -->
    <div class="filters-section">
        <div class="filters-grid">
            <div class="filter-group">
                <i class="fas fa-search filter-icon"></i>
                <input type="text" 
                       id="searchCliente" 
                       class="filter-input" 
                       placeholder="Buscar por código o documento...">
            </div>

            <div class="filter-group">
                <i class="fas fa-dollar-sign filter-icon"></i>
                <select id="filterMoneda" class="filter-select">
                    <option value="">Todas las monedas</option>
                    <option value="1">Soles (S/)</option>
                    <option value="2">Dólares ($)</option>
                </select>
            </div>

            <div class="filter-group">
                <i class="fas fa-balance-scale filter-icon"></i>
                <select id="filterSaldo" class="filter-select">
                    <option value="">Todos los saldos</option>
                    <option value="negativo">Saldos Negativos</option>
                    <option value="positivo">Saldos Positivos</option>
                </select>
            </div>

        <button class="btn-export" onclick="window.location.href='{{ route('admin.cuentas-corrientes.exportar') }}'">
                <i class="fas fa-file-excel"></i>
                Exportar
            </button>

        </div>
    </div>

    <!-- TABLE -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="cuentas-table table" id="tablaCuentas">
                <thead>
                    <tr>
                        <th>Cód. Cliente</th>
                        <th>Documento</th>
                        <th>Tipo</th>
                        <th>Fecha Promesa</th>
                        <th class="text-end">Importe (S/)</th>
                        <th>Fecha Ingreso</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuentas as $c)
                        <tr class="{{ $c->Importe < 0 ? 'deudor' : '' }}" data-importe="{{ $c->Importe }}">
                            <td>
                                <strong style="color: #3498db; font-size: 1.05rem;">
                                    {{ $c->CodClie }}
                                </strong>
                            </td>
                            <td>
                                <span style="font-weight: 600;">{{ $c->Documento }}</span>
                            </td>
                            <td>
                                @php
                                    $tipoInfo = match($c->Tipo) {
                                        1 => ['nombre' => 'Factura', 'clase' => 'factura', 'icono' => 'file-invoice'],
                                        2 => ['nombre' => 'Boleta', 'clase' => 'boleta', 'icono' => 'receipt'],
                                        7 => ['nombre' => 'Letra', 'clase' => 'letra', 'icono' => 'file-signature'],
                                        8 => ['nombre' => 'Nota Crédito', 'clase' => 'nota-credito', 'icono' => 'file-invoice-dollar'],
                                        default => ['nombre' => 'Tipo ' . $c->Tipo, 'clase' => 'otros', 'icono' => 'file'],
                                    };
                                @endphp
                                <span class="doc-badge {{ $tipoInfo['clase'] }}">
                                    <i class="fas fa-{{ $tipoInfo['icono'] }}"></i>
                                    {{ $tipoInfo['nombre'] }}
                                </span>
                            </td>
                            <td>
                                <i class="far fa-calendar-alt" style="color: #95a5a6; margin-right: 0.5rem;"></i>
                                {{ \Carbon\Carbon::parse($c->FechaF)->format('d/m/Y') }}
                            </td>
                            <td class="text-end">
                                <span class="{{ $c->Importe < 0 ? 'amount-negative' : 'amount-positive' }}">
                                    {{ $c->Importe < 0 ? '-' : '+' }} S/ {{ number_format(abs($c->Importe), 2) }}
                                </span>
                            </td>
                            <td>
                                <i class="far fa-clock" style="color: #95a5a6; margin-right: 0.5rem;"></i>
                                {{ \Carbon\Carbon::parse($c->FechaV)->format('d/m/Y') }}
                            </td>
                            <td class="text-center">
                                @if($c->Importe < 0)
                                    <span class="status-badge deudor">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Deudor
                                    </span>
                                @else
                                    <span class="status-badge acreedor">
                                        <i class="fas fa-check-circle"></i>
                                        Acreedor
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state-container">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-inbox"></i>
                                    </div>
                                    <div class="empty-state-title">No hay registros</div>
                                    <div class="empty-state-text">
                                        No se encontraron cuentas corrientes en el sistema
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($cuentas->hasPages())
            <div class="pagination-container">
                <div>
                    Mostrando {{ $cuentas->firstItem() }} a {{ $cuentas->lastItem() }} de {{ $cuentas->total() }} resultados
                </div>
                <div>
                    {{ $cuentas->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- LOADING OVERLAY -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p style="margin-top: 1rem; font-weight: 600; color: #2c3e50;">
            Exportando datos...
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchCliente');
    const filterMoneda = document.getElementById('filterMoneda');
    const filterSaldo = document.getElementById('filterSaldo');
    const rows = document.querySelectorAll('#tablaCuentas tbody tr');

    // Contar saldos iniciales
    updateCounters();

    function filterTable() {
        const search = searchInput.value.toLowerCase();
        const moneda = filterMoneda.value;
        const saldo = filterSaldo.value;

        let visibleRows = 0;
        let deudorCount = 0;
        let acreedorCount = 0;

        rows.forEach(row => {
            if (row.querySelector('.empty-state-container')) {
                row.style.display = 'none';
                return;
            }

            const codCliente = row.cells[0]?.textContent.toLowerCase() || '';
            const documento = row.cells[1]?.textContent.toLowerCase() || '';
            const importeText = row.cells[4]?.textContent || '0';
            const importe = parseFloat(importeText.replace(/[^0-9.-]/g, ''));
            const isNegative = importe < 0;

            const matchesSearch = codCliente.includes(search) || documento.includes(search);
            const matchesSaldo = !saldo || 
                (saldo === 'negativo' && isNegative) || 
                (saldo === 'positivo' && !isNegative);

            const isVisible = matchesSearch && matchesSaldo;
            row.style.display = isVisible ? '' : 'none';

            if (isVisible) {
                visibleRows++;
                if (isNegative) deudorCount++;
                else acreedorCount++;
            }
        });

        // Actualizar contadores
        document.getElementById('countDeudor').textContent = deudorCount;
        document.getElementById('countAcreedor').textContent = acreedorCount;

        // Mostrar mensaje si no hay resultados
        if (visibleRows === 0 && rows.length > 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = `
                <td colspan="7">
                    <div class="empty-state-container">
                        <div class="empty-state-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="empty-state-title">No se encontraron resultados</div>
                        <div class="empty-state-text">
                            Intenta ajustar los filtros de búsqueda
                        </div>
                    </div>
                </td>
            `;
            emptyRow.classList.add('no-results-row');
            
            // Remover filas de "no resultados" anteriores
            document.querySelectorAll('.no-results-row').forEach(r => r.remove());
            
            document.querySelector('#tablaCuentas tbody').appendChild(emptyRow);
        } else {
            document.querySelectorAll('.no-results-row').forEach(r => r.remove());
        }
    }

    function updateCounters() {
        let deudorCount = 0;
        let acreedorCount = 0;

        rows.forEach(row => {
            if (row.querySelector('.empty-state-container')) return;
            
            const importe = parseFloat(row.dataset.importe || '0');
            if (importe < 0) deudorCount++;
            else acreedorCount++;
        });

        document.getElementById('countDeudor').textContent = deudorCount;
        document.getElementById('countAcreedor').textContent = acreedorCount;
    }

    searchInput.addEventListener('input', filterTable);
    filterMoneda.addEventListener('change', filterTable);
    filterSaldo.addEventListener('change', filterTable);
});

function exportarCuentas() {
    const cliente = document.getElementById('searchCliente').value.trim();
    const moneda = document.getElementById('filterMoneda').value;
    const saldo = document.getElementById('filterSaldo').value;

    let url = '/admin/cuentas-corrientes/exportar?';

    const params = [];
    if (cliente) params.push(`cliente=${encodeURIComponent(cliente)}`);
    if (moneda) params.push(`moneda=${encodeURIComponent(moneda)}`);
    if (saldo) params.push(`saldo=${encodeURIComponent(saldo)}`);

    window.location.href = url + params.join('&');
}

// Animación de entrada para los números
const animateValue = (element, start, end, duration) => {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = value;
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
};

// Animar contadores al cargar
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const deudorEl = document.getElementById('countDeudor');
        const acreedorEl = document.getElementById('countAcreedor');
        
        const deudorValue = parseInt(deudorEl.textContent);
        const acreedorValue = parseInt(acreedorEl.textContent);
        
        deudorEl.textContent = '0';
        acreedorEl.textContent = '0';
        
        animateValue(deudorEl, 0, deudorValue, 1000);
        animateValue(acreedorEl, 0, acreedorValue, 1000);
    }, 100);
});
</script>
@endpush