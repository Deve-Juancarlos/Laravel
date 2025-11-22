@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Planillas de Cobranza')

@push('styles')
    <link href="{{ asset('css/admin/planilla-cobranza.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="planilla-cobranza-container">
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