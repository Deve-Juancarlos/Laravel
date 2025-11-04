@extends('layouts.admin')

@section('title', 'Planillas de Cobranza')

@push('styles')
<style>
  
    .planillas-container {
        animation: fadeIn 0.4s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

   
    .planillas-header {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        padding: 1.75rem;
        border-radius: 16px 16px 0 0;
        box-shadow: 0 4px 12px rgba(231, 76, 60, 0.2);
    }

    .planillas-header h4 {
        margin: 0;
        color: white;
        font-weight: 800;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .planillas-badge {
        background: rgba(255, 255, 255, 0.25);
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 25px;
        font-weight: 700;
        font-size: 0.95rem;
        backdrop-filter: blur(10px);
    }

   
    .filters-section {
        background: white;
        padding: 1.5rem;
        border-bottom: 2px solid #f5f7fa;
    }

    .filter-group {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-input {
        flex: 1;
        min-width: 250px;
        padding: 0.75rem 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        transition: all 0.3s;
        font-size: 0.95rem;
    }

    .filter-input:focus {
        outline: none;
        border-color: #e74c3c;
        box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.1);
    }

    .filter-select {
        padding: 0.75rem 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        transition: all 0.3s;
        font-size: 0.95rem;
        cursor: pointer;
    }

    .filter-select:focus {
        outline: none;
        border-color: #e74c3c;
        box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.1);
    }

 
    .table-container {
        background: white;
        border-radius: 0 0 16px 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .planillas-table {
        margin: 0;
        width: 100%;
    }

    .planillas-table thead {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    .planillas-table thead th {
        padding: 1.25rem 1rem;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #7f8c8d;
        border: none;
        white-space: nowrap;
    }

    .planillas-table tbody tr {
        transition: all 0.3s;
        border-bottom: 1px solid #ecf0f1;
    }

    .planillas-table tbody tr:hover {
        background: linear-gradient(135deg, #fef5f1 0%, #fff 100%);
        transform: scale(1.005);
        box-shadow: 0 2px 8px rgba(231, 76, 60, 0.1);
    }

    .planillas-table tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
        color: #2c3e50;
        font-size: 0.95rem;
    }

 
    .status-badge {
        padding: 0.45rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
    }

    .status-badge.confirmed {
        background: rgba(39, 174, 96, 0.15);
        color: #27ae60;
    }

    .status-badge.not-confirmed {
        background: rgba(243, 156, 18, 0.15);
        color: #f39c12;
    }

    .status-badge.printed {
        background: rgba(52, 152, 219, 0.15);
        color: #3498db;
    }

    .status-badge.not-printed {
        background: rgba(149, 165, 166, 0.15);
        color: #95a5a6;
    }

    
    .action-btn {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border: none;
        text-decoration: none;
        margin: 0.2rem;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .action-btn.btn-view {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
    }

    .action-btn.btn-edit {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        color: white;
    }

    .action-btn.btn-delete {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
    }

    .modal-custom .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }

    .modal-custom .modal-header {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        padding: 1.5rem;
        border: none;
    }

    .modal-custom .modal-header h5 {
        margin: 0;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .modal-custom .modal-body {
        padding: 2rem;
    }

    .confirm-input {
        padding: 0.85rem 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s;
        width: 100%;
    }

    .confirm-input:focus {
        outline: none;
        border-color: #e74c3c;
        box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.1);
    }

    .alert-box {
        padding: 1rem 1.25rem;
        border-radius: 10px;
        border-left: 4px solid;
        margin: 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .alert-box.danger {
        background: rgba(231, 76, 60, 0.1);
        border-color: #e74c3c;
        color: #c0392b;
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

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .planillas-table {
            font-size: 0.85rem;
        }

        .action-btn {
            padding: 0.4rem 0.75rem;
            font-size: 0.8rem;
        }

        .filter-group {
            flex-direction: column;
        }

        .filter-input {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="planillas-container">
    <!-- HEADER -->
    <div class="planillas-header d-flex justify-content-between align-items-center">
        <h4>
            <i class="fas fa-file-invoice-dollar"></i>
            Planillas de Cobranza
        </h4>
        <div class="planillas-badge">
            {{ $planillas->total() }} {{ $planillas->total() == 1 ? 'planilla' : 'planillas' }}
        </div>
    </div>

    <!-- FILTERS -->
    <div class="filters-section">
        <form action="{{ route('admin.planillas.index') }}" method="GET">
            <div class="filter-group">
                <div style="flex: 2;">
                    <input type="text" 
                           name="search" 
                           class="filter-input" 
                           placeholder="🔍 Buscar por serie, número o vendedor..."
                           value="{{ request('search') }}">
                </div>
                <select name="estado" class="filter-select">
                    <option value="">Todos los estados</option>
                    <option value="confirmada" {{ request('estado') == 'confirmada' ? 'selected' : '' }}>
                        Confirmadas
                    </option>
                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>
                        Pendientes
                    </option>
                </select>
                <button type="submit" class="action-btn btn-view">
                    <i class="fas fa-filter"></i>
                    Filtrar
                </button>
                @if(request()->has('search') || request()->has('estado'))
                    <a href="{{ route('admin.planillas.index') }}" class="action-btn btn-edit">
                        <i class="fas fa-times"></i>
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- TABLE -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="planillas-table table">
                <thead>
                    <tr>
                        <th>Serie</th>
                        <th>Número</th>
                        <th>Vendedor</th>
                        <th>Fecha Creación</th>
                        <th>Confirmada</th>
                        <th>Impresa</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planillas as $p)
                        <tr>
                            <td><strong style="color: #e74c3c; font-size: 1.05rem;">{{ $p->Serie }}</strong></td>
                            <td><strong>{{ $p->Numero }}</strong></td>
                            <td>
                                <i class="fas fa-user-tie" style="color: #3498db; margin-right: 0.5rem;"></i>
                                @php
                                    $vendedor = $vendedores->firstWhere('Codemp', $p->Vendedor);
                                @endphp
                                {{ $vendedor ? $vendedor->Nombre : 'ID: ' . $p->Vendedor }}
                            </td>
                            <td>
                                <i class="far fa-calendar-alt" style="color: #95a5a6; margin-right: 0.5rem;"></i>
                                {{ \Carbon\Carbon::parse($p->FechaCrea)->format('d/m/Y') }}
                            </td>
                            <td>
                                @if($p->Confirmacion)
                                    <span class="status-badge confirmed">
                                        <i class="fas fa-check-circle"></i>
                                        Confirmada
                                    </span>
                                @else
                                    <span class="status-badge not-confirmed">
                                        <i class="fas fa-clock"></i>
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($p->Impreso)
                                    <span class="status-badge printed">
                                        <i class="fas fa-print"></i>
                                        Impresa
                                    </span>
                                @else
                                    <span class="status-badge not-printed">
                                        <i class="fas fa-ban"></i>
                                        No Impresa
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.planillas.show', [$p->Serie, $p->Numero]) }}" 
                                   class="action-btn btn-view"
                                   title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                    Ver
                                </a>
                                @if(!$p->Confirmacion && !$p->Impreso)
                                    <a href="{{ route('admin.planillas.edit', [$p->Serie, $p->Numero]) }}" 
                                       class="action-btn btn-edit"
                                       title="Editar planilla">
                                        <i class="fas fa-edit"></i>
                                        Editar
                                    </a>
                                @endif
                                @if($p->Confirmacion)
                                    <a href="{{ route('contador.anulacion.show', [$p->Serie, $p->Numero]) }}" 
                                       class="action-btn btn-anular" {{-- Usamos el nuevo estilo --}}
                                       title="Anular esta planilla (Recomendado)">
                                        <i class="fas fa-undo-alt"></i> {{-- Icono de reversa --}}
                                        Anular
                                    </a>
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
                                    <div class="empty-state-title">No hay planillas registradas</div>
                                    <div class="empty-state-text">
                                        No se encontraron planillas de cobranza en el sistema
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($planillas->hasPages())
            <div class="pagination-container">
                <div>
                    Mostrando {{ $planillas->firstItem() }} a {{ $planillas->lastItem() }} de {{ $planillas->total() }} resultados
                </div>
                <div>
                    {{ $planillas->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- MODAL DE ELIMINACIÓN -->
<div class="modal fade modal-custom" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Eliminar Planilla
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert-box danger">
                    <i class="fas fa-exclamation-circle fa-lg"></i>
                    <div>
                        <strong>¡Atención!</strong> Esta acción revertirá saldos, notas de crédito y movimientos de caja.
                    </div>
                </div>

                <p style="margin: 1.5rem 0 0.5rem 0; font-weight: 600;">
                    ¿Está seguro de eliminar la planilla <strong style="color: #e74c3c;"><span id="modalSerie"></span>-<span id="modalNumero"></span></strong>?
                </p>

                <p style="margin: 1rem 0 0.5rem 0;">
                    Para confirmar, escriba <strong style="color: #e74c3c;">ELIMINAR</strong>:
                </p>
                <input type="text" 
                       id="confirmText" 
                       class="confirm-input" 
                       placeholder="Escriba ELIMINAR para confirmar..."
                       autocomplete="off">
            </div>
            <div class="modal-footer" style="padding: 1.25rem; background: #f8f9fa;">
                <button type="button" class="action-btn btn-edit" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="button" class="action-btn btn-delete" id="btnConfirmarEliminar" disabled>
                    <i class="fas fa-trash"></i>
                    Confirmar Eliminación
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        // TU SCRIPT EXISTENTE DE ELIMINAR (no lo borres)
        function eliminarPlanilla(serie, numero) {
            // ... todo tu código actual ...
        }

        document.getElementById('confirmText').addEventListener('input', function() {
            const isValid = this.value.trim().toUpperCase() === 'ELIMINAR';
            document.getElementById('btnConfirmarEliminar').disabled = !isValid;
        });

        // ========================================
        // NUEVO: FILTRADO EN TIEMPO REAL
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            const estadoSelect = document.querySelector('select[name="estado"]');
            const tableRows = document.querySelectorAll('.planillas-table tbody tr:not(:has(.empty-state-container))');
            const emptyState = document.querySelector('.empty-state-container');
            const badge = document.querySelector('.planillas-badge');
            
            function filterPlanillas() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const estadoFilter = estadoSelect.value.toLowerCase();
                
                let visibleCount = 0;
                
                tableRows.forEach(row => {
                    const serie = row.cells[0] ? row.cells[0].textContent.toLowerCase() : '';
                    const numero = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
                    const vendedor = row.cells[2] ? row.cells[2].textContent.toLowerCase() : '';
                    
                    // Leer estado desde el badge visible
                    const estadoBadge = row.querySelector('.status-badge.confirmed, .status-badge.not-confirmed');
                    const estadoText = estadoBadge ? estadoBadge.textContent.toLowerCase() : '';
                    const esConfirmada = estadoText.includes('confirmada');
                    const esPendiente = estadoText.includes('pendiente');
                    
                    // Filtro de búsqueda
                    const matchesSearch = !searchTerm || 
                                        serie.includes(searchTerm) || 
                                        numero.includes(searchTerm) || 
                                        vendedor.includes(searchTerm);
                    
                    // Filtro de estado
                    let matchesEstado = true;
                    if (estadoFilter === 'confirmada') {
                        matchesEstado = esConfirmada;
                    } else if (estadoFilter === 'pendiente') {
                        matchesEstado = esPendiente;
                    }
                    
                    const isVisible = matchesSearch && matchesEstado;
                    row.style.display = isVisible ? '' : 'none';
                    
                    if (isVisible) visibleCount++;
                });
                
                // Actualizar contador
                if (badge) {
                    badge.textContent = `${visibleCount} ${visibleCount === 1 ? 'planilla' : 'planillas'}`;
                }
                
                // Mensaje de "no hay resultados"
                if (emptyState) {
                    const emptyRow = emptyState.closest('tr');
                    if (visibleCount === 0 && tableRows.length > 0) {
                        if (emptyRow) emptyRow.style.display = '';
                        const title = emptyState.querySelector('.empty-state-title');
                        const text = emptyState.querySelector('.empty-state-text');
                        if (title) title.textContent = 'No se encontraron resultados';
                        if (text) text.textContent = 'No hay planillas que coincidan con los filtros aplicados';
                    } else {
                        if (emptyRow) emptyRow.style.display = 'none';
                    }
                }
            }
            
            // Event listeners
            if (searchInput) {
                searchInput.addEventListener('input', filterPlanillas);
            }
            
            if (estadoSelect) {
                estadoSelect.addEventListener('change', filterPlanillas);
            }
            
            // Aplicar filtros iniciales
            filterPlanillas();
            
            // Prevenir submit y usar filtrado client-side
            const filterForm = document.querySelector('.filters-section form');
            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    filterPlanillas();
                });
            }
        });
    </script>
@endpush