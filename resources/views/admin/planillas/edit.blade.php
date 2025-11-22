@use('Illuminate\Support\Str')
@extends('layouts.admin')

@section('title', 'Editar Planilla')

@push('styles')
<style>
    /* ===== EDIT FORM STYLES ===== */
    .edit-container {
        animation: fadeIn 0.4s ease-in-out;
        max-width: 900px;
        margin: 0 auto;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .edit-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .edit-header {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        padding: 1.75rem;
        color: white;
    }

    .edit-header h4 {
        margin: 0;
        font-weight: 800;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .edit-body {
        padding: 2rem;
    }

    /* FORM GROUPS */
    .form-group-custom {
        margin-bottom: 1.5rem;
    }

    .form-label-custom {
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
    }

    .form-control-custom {
        padding: 0.85rem 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.3s;
        width: 100%;
    }

    .form-control-custom:focus {
        outline: none;
        border-color: #f39c12;
        box-shadow: 0 0 0 4px rgba(243, 156, 18, 0.1);
    }

    .form-control-custom:disabled {
        background: #f8f9fa;
        color: #7f8c8d;
        cursor: not-allowed;
    }

    /* RADIO/SWITCH GROUPS */
    .toggle-group {
        display: flex;
        gap: 1rem;
        margin-top: 0.75rem;
    }

    .toggle-option {
        flex: 1;
        position: relative;
    }

    .toggle-option input[type="radio"] {
        display: none;
    }

    .toggle-label {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.85rem 1.5rem;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 600;
        gap: 0.5rem;
    }

    .toggle-option input[type="radio"]:checked + .toggle-label {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        border-color: #27ae60;
        color: white;
        transform: scale(1.05);
    }

    .toggle-option input[type="radio"]:not(:checked) + .toggle-label:hover {
        border-color: #bdc3c7;
        background: #f8f9fa;
    }

    /* ALERT BOX */
    .alert-custom {
        padding: 1.25rem 1.5rem;
        border-radius: 12px;
        border-left: 4px solid;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .alert-custom.danger {
        background: rgba(231, 76, 60, 0.1);
        border-color: #e74c3c;
        color: #c0392b;
    }

    .alert-custom.warning {
        background: rgba(243, 156, 18, 0.1);
        border-color: #f39c12;
        color: #e67e22;
    }

    .alert-custom i {
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    /* BUTTONS */
    .btn-group-custom {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .btn-custom {
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

    .btn-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    }

    .btn-custom.btn-success {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        color: white;
    }

    .btn-custom.btn-secondary {
        background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
        color: white;
    }

    /* INFO CARDS */
    .info-card {
        background: linear-gradient(135deg, #ecf0f1 0%, #ffffff 100%);
        padding: 1rem 1.25rem;
        border-radius: 10px;
        border-left: 4px solid #3498db;
        margin-bottom: 1.5rem;
    }

    .info-card strong {
        color: #2c3e50;
        font-weight: 700;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .edit-body {
            padding: 1.5rem;
        }

        .btn-group-custom {
            flex-direction: column;
        }

        .btn-custom {
            width: 100%;
            justify-content: center;
        }

        .toggle-group {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="edit-container">
    <div class="edit-card">
        <div class="edit-header">
            <h4>
                <i class="fas fa-edit"></i>
                Editar Planilla de Cobranza
            </h4>
        </div>

        <div class="edit-body">
            @if($errors->any())
                <div class="alert-custom warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Errores de validación:</strong>
                        <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if($planilla->Confirmacion || $planilla->Impreso)
                <div class="alert-custom danger">
                    <i class="fas fa-ban"></i>
                    <div>
                        <strong>No se puede editar:</strong> Esta planilla ya está 
                        {{ $planilla->Confirmacion ? 'confirmada' : '' }}
                        {{ $planilla->Confirmacion && $planilla->Impreso ? ' e ' : '' }}
                        {{ $planilla->Impreso ? 'impresa' : '' }}.
                    </div>
                </div>
                <div class="btn-group-custom">
                    <a href="{{ route('admin.planillas.show', [$planilla->Serie, $planilla->Numero]) }}" 
                       class="btn-custom btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Volver al Detalle
                    </a>
                </div>
            @else
                <!-- INFORMACIÓN DE LA PLANILLA -->
                <div class="info-card">
                    <i class="fas fa-info-circle" style="color: #3498db; margin-right: 0.5rem;"></i>
                    Editando planilla <strong>{{ $planilla->Serie }}-{{ $planilla->Numero }}</strong>
                </div>

                <form action="{{ route('admin.planillas.update', [$planilla->Serie, $planilla->Numero]) }}" 
                      method="POST"
                      id="editForm">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- SERIE -->
                        <div class="col-md-4">
                            <div class="form-group-custom">
                                <label class="form-label-custom">
                                    <i class="fas fa-hashtag" style="color: #e74c3c;"></i>
                                    Serie
                                </label>
                                <input type="text" 
                                       class="form-control-custom" 
                                       value="{{ $planilla->Serie }}" 
                                       disabled>
                            </div>
                        </div>

                        <!-- NÚMERO -->
                        <div class="col-md-4">
                            <div class="form-group-custom">
                                <label class="form-label-custom">
                                    <i class="fas fa-list-ol" style="color: #e74c3c;"></i>
                                    Número
                                </label>
                                <input type="text" 
                                       class="form-control-custom" 
                                       value="{{ $planilla->Numero }}" 
                                       disabled>
                            </div>
                        </div>

                        <!-- VENDEDOR -->
                        <div class="col-md-4">
                            <div class="form-group-custom">
                                <label class="form-label-custom">
                                    <i class="fas fa-user-tie" style="color: #3498db;"></i>
                                    Vendedor
                                </label>
                                <select name="Vendedor" class="form-control-custom" required>
                                    @foreach($vendedores as $v)
                                        <option value="{{ $v->Codemp }}" 
                                                {{ $v->Codemp == $planilla->Vendedor ? 'selected' : '' }}>
                                            {{ $v->Nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- FECHA DE CREACIÓN -->
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">
                                    <i class="far fa-calendar-alt" style="color: #27ae60;"></i>
                                    Fecha de Creación
                                </label>
                                <input type="date" 
                                       name="FechaCrea" 
                                       class="form-control-custom" 
                                       value="{{ \Carbon\Carbon::parse($planilla->FechaCrea)->format('Y-m-d') }}"
                                       required>
                            </div>
                        </div>

                        <!-- FECHA DE INGRESO -->
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">
                                    <i class="far fa-calendar-check" style="color: #27ae60;"></i>
                                    Fecha de Ingreso
                                </label>
                                <input type="date" 
                                       name="FechaIng" 
                                       class="form-control-custom" 
                                       value="{{ \Carbon\Carbon::parse($planilla->FechaIng)->format('Y-m-d') }}"
                                       required>
                            </div>
                        </div>
                    </div>

                    <!-- CONFIRMACIÓN -->
                    <div class="form-group-custom">
                        <label class="form-label-custom">
                            <i class="fas fa-check-circle" style="color: #27ae60;"></i>
                            ¿Confirmar Planilla?
                        </label>
                        <div class="toggle-group">
                            <div class="toggle-option">
                                <input type="radio" 
                                       name="Confirmacion" 
                                       value="1" 
                                       id="confirmSi" 
                                       {{ $planilla->Confirmacion ? 'checked' : '' }}>
                                <label for="confirmSi" class="toggle-label">
                                    <i class="fas fa-check"></i>
                                    Sí, Confirmar
                                </label>
                            </div>
                            <div class="toggle-option">
                                <input type="radio" 
                                       name="Confirmacion" 
                                       value="0" 
                                       id="confirmNo" 
                                       {{ !$planilla->Confirmacion ? 'checked' : '' }}>
                                <label for="confirmNo" class="toggle-label">
                                    <i class="fas fa-times"></i>
                                    No, Mantener Pendiente
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- IMPRESO -->
                    <div class="form-group-custom">
                        <label class="form-label-custom">
                            <i class="fas fa-print" style="color: #3498db;"></i>
                            ¿Marcar como Impresa?
                        </label>
                        <div class="toggle-group">
                            <div class="toggle-option">
                                <input type="radio" 
                                       name="Impreso" 
                                       value="1" 
                                       id="impresoSi" 
                                       {{ $planilla->Impreso ? 'checked' : '' }}>
                                <label for="impresoSi" class="toggle-label">
                                    <i class="fas fa-check"></i>
                                    Sí, Impresa
                                </label>
                            </div>
                            <div class="toggle-option">
                                <input type="radio" 
                                       name="Impreso" 
                                       value="0" 
                                       id="impresoNo" 
                                       {{ !$planilla->Impreso ? 'checked' : '' }}>
                                <label for="impresoNo" class="toggle-label">
                                    <i class="fas fa-times"></i>
                                    No, Sin Imprimir
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- BOTONES -->
                    <div class="btn-group-custom">
                        <button type="submit" class="btn-custom btn-success">
                            <i class="fas fa-save"></i>
                            Guardar Cambios
                        </button>
                        <a href="{{ route('admin.planillas.index') }}" class="btn-custom btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Prevenir doble envío
document.getElementById('editForm')?.addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn.disabled) {
        e.preventDefault();
        return false;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    
  r
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
    }, 3000);
});
</script>
@endpush