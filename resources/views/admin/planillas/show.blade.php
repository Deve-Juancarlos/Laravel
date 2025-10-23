@extends('layouts.admin')

@section('title', 'Detalle de Planilla')

@push('styles')
<style>
    /* ===== SHOW/DETAIL STYLES ===== */
    .detail-container {
        animation: fadeIn 0.4s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .detail-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }

    .detail-header {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        padding: 1.75rem;
        color: white;
    }

    .detail-header h4 {
        margin: 0;
        font-weight: 800;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .detail-body {
        padding: 2rem;
    }

    /* INFO GRID */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .info-item {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        padding: 1.25rem;
        border-radius: 12px;
        border-left: 4px solid;
        transition: all 0.3s;
    }

    .info-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .info-item.primary { border-left-color: #3498db; }
    .info-item.success { border-left-color: #27ae60; }
    .info-item.warning { border-left-color: #f39c12; }
    .info-item.danger { border-left-color: #e74c3c; }

    .info-label {
        font-size: 0.85rem;
        color: #7f8c8d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* STATUS BADGES */
    .status-badge-large {
        padding: 0.5rem 1.25rem;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-badge-large.confirmed {
        background: rgba(39, 174, 96, 0.15);
        color: #27ae60;
    }

    .status-badge-large.not-confirmed {
        background: rgba(243, 156, 18, 0.15);
        color: #f39c12;
    }

    .status-badge-large.printed {
        background: rgba(52, 152, 219, 0.15);
        color: #3498db;
    }

    .status-badge-large.not-printed {
        background: rgba(149, 165, 166, 0.15);
        color: #95a5a6;
    }

    /* DETAIL TABLE */
    .detail-table-section {
        margin-top: 2rem;
    }

    .section-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 3px solid #3498db;
    }

    .detail-table {
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .detail-table thead {
        background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
        color: white;
    }

    .detail-table thead th {
        padding: 1.25rem 1rem;
        font-weight: 700;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
    }

    .detail-table tbody tr {
        transition: all 0.3s;
        border-bottom: 1px solid #ecf0f1;
    }

    .detail-table tbody tr:hover {
        background: linear-gradient(135deg, #f0f8ff 0%, #fff 100%);
    }

    .detail-table tbody td {
        padding: 1.25rem 1rem;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    /* AMOUNT CELL */
    .amount-cell {
        font-weight: 700;
        font-size: 1.05rem;
        color: #27ae60;
    }

    /* ACTION BUTTONS */
    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid #ecf0f1;
    }

    .btn-action-large {
        padding: 0.85rem 2rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        border: none;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-action-large:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    }

    .btn-action-large.btn-secondary {
        background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
        color: white;
    }

    .btn-action-large.btn-warning {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        color: white;
    }

    .btn-action-large.btn-danger {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
    }

    /* EMPTY STATE */
    .empty-detail-state {
        text-align: center;
        padding: 3rem 2rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 12px;
        margin-top: 1rem;
    }

    .empty-detail-icon {
        font-size: 4rem;
        color: #ecf0f1;
        margin-bottom: 1rem;
    }

    .empty-detail-text {
        color: #7f8c8d;
        font-size: 1.1rem;
        font-weight: 600;
    }

    /* MODAL */
    .modal-custom .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }

    .modal-custom .modal-header {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        color: white;
        padding: 1.5rem;
        border: none;
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

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-action-large {
            width: 100%;
            justify-content: center;
        }

        .detail-body {
            padding: 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="detail-container">
    <div class="detail-card">
        <div class="detail-header">
            <h4>
                <i class="fas fa-file-invoice-dollar"></i>
                Detalle de Planilla de Cobranza
            </h4>
        </div>

        <div class="detail-body">
            <!-- INFORMACIÓN PRINCIPAL -->
            <div class="info-grid">
                <div class="info-item primary">
                    <div class="info-label">
                        <i class="fas fa-hashtag"></i>
                        Serie
                    </div>
                    <div class="info-value">
                        {{ $planilla->Serie }}
                    </div>
                </div>

                <div class="info-item primary">
                    <div class="info-label">
                        <i class="fas fa-list-ol"></i>
                        Número
                    </div>
                    <div class="info-value">
                        {{ $planilla->Numero }}
                    </div>
                </div>

                <div class="info-item success">
                    <div class="info-label">
                        <i class="fas fa-user-tie"></i>
                        Vendedor
                    </div>
                    <div class="info-value">
                        @php
                            $vendedor = $vendedores->firstWhere('Codemp', $planilla->Vendedor);
                        @endphp
                        {{ $vendedor ? $vendedor->Nombre : 'ID: ' . $planilla->Vendedor }}
                    </div>
                </div>

                <div class="info-item warning">
                    <div class="info-label">
                        <i class="far fa-calendar-alt"></i>
                        Fecha Creación
                    </div>
                    <div class="info-value">
                        {{ \Carbon\Carbon::parse($planilla->FechaCrea)->format('d/m/Y') }}
                    </div>
                </div>

                <div class="info-item success">
                    <div class="info-label">
                        <i class="fas fa-check-circle"></i>
                        Confirmada
                    </div>
                    <div class="info-value">
                        @if($planilla->Confirmacion)
                            <span class="status-badge-large confirmed">
                                <i class="fas fa-check"></i>
                                Confirmada
                            </span>
                        @else
                            <span class="status-badge-large not-confirmed">
                                <i class="fas fa-clock"></i>
                                Pendiente
                            </span>
                        @endif
                    </div>
                </div>

                <div class="info-item primary">
                    <div class="info-label">
                        <i class="fas fa-print"></i>
                        Impresa
                    </div>
                    <div class="info-value">
                        @if($planilla->Impreso)
                            <span class="status-badge-large printed">
                                <i class="fas fa-check"></i>
                                Impresa
                            </span>
                        @else
                            <span class="status-badge-large not-printed">
                                <i class="fas fa-ban"></i>
                                No Impresa
                            </span>
                        @endif
                    </div>
                </div>

                <div class="info-item danger">
                    <div class="info-label">
                        <i class="far fa-calendar-check"></i>
                        Fecha Ingreso
                    </div>
                    <div class="info-value">
                        {{ \Carbon\Carbon::parse($planilla->FechaIng)->format('d/m/Y') }}
                    </div>
                </div>
            </div>

            <!-- DETALLE DE COBRANZA -->
            <div class="detail-table-section">
                <h5 class="section-title">
                    <i class="fas fa-list-ul"></i>
                    Detalle de Cobranza
                </h5>

                @if($detalle->isNotEmpty())
                    <div class="table-responsive">
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Tipo</th>
                                    <th class="text-end">Descuento</th>
                                    <th class="text-end">Efectivo</th>
                                    <th class="text-end">Cheque</th>
                                    <th class="text-center">Nota Crédito</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detalle as $d)
                                    <tr>
                                        <td>
                                            <strong style="color: #3498db;">{{ $d->Documento }}</strong>
                                        </td>
                                        <td>
                                            <span style="color: #7f8c8d; font-weight: 600;">
                                                {{ $d->TipoDoc }}
                                            </span>
                                        </td>
                                        <td class="text-end amount-cell" style="color: #e74c3c;">
                                            S/ {{ number_format($d->Descuento ?? 0, 2) }}
                                        </td>
                                        <td class="text-end amount-cell">
                                            S/ {{ number_format($d->efectivo ?? 0, 2) }}
                                        </td>
                                        <td class="text-end amount-cell" style="color: #f39c12;">
                                            S/ {{ number_format($d->cheque ?? 0, 2) }}
                                        </td>
                                        <td class="text-center">
                                            @if($d->NotaCred)
                                                <span class="status-badge-large confirmed">
                                                    <i class="fas fa-file-invoice"></i>
                                                    {{ $d->NotaCred}}
                                                </span>
                                            @else
                                                <span style="color: #bdc3c7;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-detail-state">
                        <div class="empty-detail-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <div class="empty-detail-text">
                            Esta planilla no tiene movimientos registrados
                        </div>
                    </div>
                @endif
            </div>

            <!-- BOTONES DE ACCIÓN -->
            <div class="action-buttons">
                <a href="{{ route('admin.planillas.index') }}" class="btn-action-large btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Volver al Listado
                </a>

                @if(!$planilla->Confirmacion && !$planilla->Impreso)
                    <a href="{{ route('admin.planillas.edit', [$planilla->Serie, $planilla->Numero]) }}" 
                       class="btn-action-large btn-warning">
                        <i class="fas fa-edit"></i>
                        Editar Planilla
                    </a>
                @endif

                @if(auth()->user()->tipousuario === 'ADMIN')
                    <button type="button" 
                            class="btn-action-large btn-danger" 
                            onclick="confirmarEliminacion()">
                        <i class="fas fa-trash"></i>
                        Eliminar Planilla
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN -->
<div class="modal fade modal-custom" id="modalConfirmacion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div style="padding: 1rem; background: rgba(243, 156, 18, 0.1); border-radius: 10px; margin-bottom: 1.5rem;">
                    <i class="fas fa-exclamation-triangle" style="color: #f39c12; font-size: 1.25rem; margin-right: 0.5rem;"></i>
                    <strong style="color: #e67e22;">¡Atención!</strong> Esta acción revertirá saldos, notas de crédito y movimientos de caja.
                </div>

                <p style="font-size: 1.05rem; margin-bottom: 0.5rem;">
                    Para confirmar la eliminación de la planilla 
                    <strong style="color: #e74c3c;">{{ $planilla->Serie }}-{{ $planilla->Numero }}</strong>, 
                    escriba <strong style="color: #e74c3c;">ELIMINAR</strong>:
                </p>

                <input type="text" 
                       id="confirmText" 
                       class="confirm-input" 
                       placeholder="Escriba ELIMINAR para confirmar..."
                       autocomplete="off">

                <p style="margin-top: 1rem; font-size: 0.85rem; color: #7f8c8d;">
                    <i class="fas fa-info-circle"></i> Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer" style="padding: 1.25rem; background: #f8f9fa;">
                <button type="button" class="btn-action-large btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="button" class="btn-action-large btn-danger" id="btnEliminarReal" disabled>
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
function confirmarEliminacion() {
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
    document.getElementById('confirmText').value = '';
    document.getElementById('btnEliminarReal').disabled = true;
    modal.show();
}

document.getElementById('confirmText').addEventListener('input', function() {
    const isValid = this.value.trim().toUpperCase() === 'ELIMINAR';
    document.getElementById('btnEliminarReal').disabled = !isValid;
});

document.getElementById('btnEliminarReal').addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';

    fetch("{{ route('admin.planillas.destroy', ['serie' => $planilla->Serie, 'numero' => $planilla->Numero]) }}", {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Planilla eliminada correctamente con reversión contable.');
            window.location.href = "{{ route('admin.planillas.index') }}";
        } else {
            alert('Error: ' + (data.error || data.message));
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i> Confirmar Eliminación';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la planilla');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Confirmar Eliminación';
    });
});
</script>
@endpush