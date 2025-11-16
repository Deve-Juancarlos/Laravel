@extends('layouts.admin')

@section('title', 'Cuentas Corrientes')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/cuentas-corrientes.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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