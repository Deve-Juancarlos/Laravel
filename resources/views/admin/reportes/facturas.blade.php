@use('Illuminate\Support\Str')
@extends('layouts.admin')

@push('styles')
<style>
    
    .stats-section-facturas {
        margin-bottom: 2rem;
    }

    .stats-grid-facturas {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card-factura {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card-factura:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }

    .stat-card-factura.total {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card-factura.monto {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-card-factura.activos {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stat-card-factura.anulados {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .stat-card-factura h6 {
        font-size: 0.875rem;
        font-weight: 500;
        opacity: 0.9;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card-factura .value {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
    }

   
    .filters-section-facturas {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .filters-section-facturas .row {
        align-items: center;
    }

    .filter-input-factura,
    .filter-select-factura {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .filter-input-factura:focus,
    .filter-select-factura:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }


    .facturas-table-container {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .facturas-table {
        margin-bottom: 0;
    }

    .facturas-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .facturas-table thead th {
        border: none;
        padding: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .facturas-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .facturas-table tbody tr:hover {
        background-color: #f8fafc;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .facturas-table tbody td {
        padding: 1rem;
        vertical-align: middle;
        font-size: 0.95rem;
    }

    
    .doc-type-badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .doc-type-badge.factura {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .doc-type-badge.boleta {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }

    .doc-type-badge.nota-credito {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
    }

    .doc-type-badge.otro {
        background: #94a3b8;
        color: white;
    }


    .status-badge-factura {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.9rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-badge-factura.activo {
        background-color: #d1fae5;
        color: #065f46;
    }

    .status-badge-factura.anulado {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .status-badge-factura.sin-estado {
        background-color: #f1f5f9;
        color: #475569;
    }

    .status-badge-factura i {
        font-size: 0.7rem;
    }

    
    .btn-export-facturas {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-export-facturas:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        color: white;
    }

    /* Empty State */
    .empty-state-facturas {
        text-align: center;
        padding: 3rem 1rem;
        color: #64748b;
    }

    .empty-state-facturas i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .empty-state-facturas p {
        font-size: 1.1rem;
        margin: 0;
    }

    /* Amount Styling */
    .amount-positive {
        color: #059669;
        font-weight: 600;
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .facturas-table tbody tr {
        animation: fadeInUp 0.5s ease;
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
    <div class="stats-section-facturas">
        <div class="stats-grid-facturas">
            <div class="stat-card-factura total">
                <h6>Total Documentos</h6>
                <p class="value" id="totalDocs">{{ $documentos->total() }}</p>
            </div>
            <div class="stat-card-factura monto">
                <h6>Monto Total</h6>
                <p class="value" id="totalMonto">S/ 0.00</p>
            </div>
            <div class="stat-card-factura activos">
                <h6>Documentos Activos</h6>
                <p class="value" id="totalActivos">0</p>
            </div>
            <div class="stat-card-factura anulados">
                <h6>Documentos Anulados</h6>
                <p class="value" id="totalAnulados">0</p>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section-facturas">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" id="searchDoc" class="form-control filter-input-factura border-start-0" 
                           placeholder="Buscar documento...">
                </div>
            </div>
            <div class="col-md-2">
                <select id="filterTipo" class="form-select filter-select-factura">
                    <option value="">Todos los tipos</option>
                    <option value="factura">Facturas</option>
                    <option value="boleta">Boletas</option>
                    <option value="nota">Notas de Crédito</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" id="filterFecha" class="form-control filter-input-factura">
            </div>
            <div class="col-md-2">
                <select id="filterEstado" class="form-select filter-select-factura">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activo</option>
                    <option value="anulado">Anulado</option>
                </select>
            </div>
            <div class="col-md-2">
                <a href="#" class="btn btn-export-facturas w-100">
                    <i class="fas fa-file-excel"></i> Exportar
                </a>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="facturas-table-container">
        <div class="table-responsive">
            <table class="table facturas-table" id="tablaFacturas">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>SubTotal</th>
                        <th>IGV</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documentos as $d)
                        <tr data-tipo="{{ 
                            match($d->TipoDoc ?? $d->tipo ?? 0) {
                                1 => 'factura',
                                2 => 'boleta',
                                3 => 'nota',
                                default => 'otro'
                            }
                        }}" 
                        data-estado="{{ (isset($d->Anulado) && $d->Anulado) ? 'anulado' : 'activo' }}"
                        data-total="{{ $d->Total ?? $d->monto ?? 0 }}">
                            <td><strong>{{ $d->Numero ?? $d->documento }}</strong></td>
                            <td>
                                @php
                                    $tipoMap = match($d->TipoDoc ?? $d->tipo ?? 0) {
                                        1 => ['text' => 'Factura', 'class' => 'factura'],
                                        2 => ['text' => 'Boleta', 'class' => 'boleta'],
                                        3 => ['text' => 'Nota Crédito', 'class' => 'nota-credito'],
                                        default => ['text' => 'Otro', 'class' => 'otro']
                                    };
                                @endphp
                                <span class="doc-type-badge {{ $tipoMap['class'] }}">
                                    {{ $tipoMap['text'] }}
                                </span>
                            </td>
                            <td>{{ $d->Codclie ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($d->Fecha ?? $d->fecha)->format('d/m/Y') }}</td>
                            <td>S/ {{ number_format($d->Bruto ?? 0, 2) }}</td>
                            <td>S/ {{ number_format($d->Igv ?? 0, 2) }}</td>
                            <td class="amount-positive">S/ {{ number_format($d->Total ?? $d->monto ?? 0, 2) }}</td>
                            <td>
                                @if(isset($d->Anulado) && $d->Anulado)
                                    <span class="status-badge-factura anulado">
                                        <i class="fas fa-times-circle"></i> Anulado
                                    </span>
                                @elseif(isset($d->Estado) && $d->Estado == 1)
                                    <span class="status-badge-factura activo">
                                        <i class="fas fa-check-circle"></i> Activo
                                    </span>
                                @else
                                    <span class="status-badge-factura sin-estado">
                                        <i class="fas fa-question-circle"></i> Sin estado
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="no-data-row">
                            <td colspan="8">
                                <div class="empty-state-facturas">
                                    <i class="fas fa-inbox"></i>
                                    <p>No hay documentos registrados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Empty State (Hidden by default) -->
        <div id="emptyState" class="empty-state-facturas" style="display: none;">
            <i class="fas fa-search"></i>
            <p>No se encontraron resultados con los filtros aplicados.</p>
        </div>

        <div class="d-flex justify-content-end mt-3 flex-column align-items-end">
            <div class="text-muted small mb-1">
                Showing {{ $documentos->firstItem() }} to {{ $documentos->lastItem() }} of {{ $documentos->total() }} results
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0">
                    {{-- Previous --}}
                    @if ($documentos->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">
                                <span aria-hidden="true">&laquo;</span> Previous
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $documentos->previousPageUrl() }}" rel="prev">
                                <span aria-hidden="true">&laquo;</span> Previous
                            </a>
                        </li>
                    @endif

                    {{-- Números de página --}}
                    @foreach ($documentos->links()->elements[0] as $page => $url)
                        @if ($page == $documentos->currentPage())
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
                    @if ($documentos->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $documentos->nextPageUrl() }}" rel="next">
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
    const searchInput = document.getElementById('searchDoc');
    const filterTipo = document.getElementById('filterTipo');
    const filterFecha = document.getElementById('filterFecha');
    const filterEstado = document.getElementById('filterEstado');
    const rows = document.querySelectorAll('#tablaFacturas tbody tr:not(.no-data-row)');
    const emptyState = document.getElementById('emptyState');
    const tableBody = document.querySelector('#tablaFacturas tbody');

    // Calcular totales iniciales
    updateStats();

    function filterTable() {
        const search = searchInput.value.toLowerCase();
        const tipo = filterTipo.value;
        const fecha = filterFecha.value;
        const estado = filterEstado.value;

        let visibleCount = 0;
        let totalMonto = 0;
        let activosCount = 0;
        let anuladosCount = 0;

        rows.forEach(row => {
            const doc = row.cells[0].textContent.toLowerCase();
            const tipoData = row.dataset.tipo;
            const fechaCell = row.cells[3].textContent;
            const estadoData = row.dataset.estado;
            const montoData = parseFloat(row.dataset.total);

            const matchesSearch = doc.includes(search);
            const matchesTipo = !tipo || tipoData === tipo;
            const matchesFecha = !fecha || fechaCell.includes(fecha.split('-').reverse().join('/'));
            const matchesEstado = !estado || estadoData === estado;

            const isVisible = matchesSearch && matchesTipo && matchesFecha && matchesEstado;
            row.style.display = isVisible ? '' : 'none';

            if (isVisible) {
                visibleCount++;
                totalMonto += montoData;
                if (estadoData === 'activo') activosCount++;
                if (estadoData === 'anulado') anuladosCount++;
            }
        });

        // Actualizar estadísticas
        document.getElementById('totalDocs').textContent = visibleCount;
        document.getElementById('totalMonto').textContent = 'S/ ' + totalMonto.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        document.getElementById('totalActivos').textContent = activosCount;
        document.getElementById('totalAnulados').textContent = anuladosCount;

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
        let totalMonto = 0;
        let activosCount = 0;
        let anuladosCount = 0;

        rows.forEach(row => {
            const montoData = parseFloat(row.dataset.total);
            const estadoData = row.dataset.estado;

            totalMonto += montoData;
            if (estadoData === 'activo') activosCount++;
            if (estadoData === 'anulado') anuladosCount++;
        });

        // Animar valores
        animateValue(document.getElementById('totalMonto'), 0, totalMonto, 1000, true);
        animateValue(document.getElementById('totalActivos'), 0, activosCount, 800);
        animateValue(document.getElementById('totalAnulados'), 0, anuladosCount, 800);
    }

    function animateValue(element, start, end, duration, isCurrency = false) {
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
                element.textContent = 'S/ ' + current.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            } else {
                element.textContent = Math.round(current);
            }
        }, 16);
    }

    searchInput.addEventListener('input', filterTable);
    filterTipo.addEventListener('change', filterTable);
    filterFecha.addEventListener('change', filterTable);
    filterEstado.addEventListener('change', filterTable);
});
</script>
@endpush