{{-- ==========================================
     VISTA: CREAR/EDITAR MERMAS
     MÓDULO: Control de Mermas - Crear
     DESARROLLADO POR: MiniMax Agent
     FECHA: 2025-10-25
     DESCRIPCIÓN: Formulario detallado para crear y editar mermas farmacéuticas
                  con seguimiento completo, documentación y flujo de aprobación
========================================== --}}

@extends('layouts.app')

@section('title', 'Crear/Editar Merma - Control de Mermas')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-plus-circle text-success"></i>
                        {{ isset($merma) ? 'Editar Merma' : 'Registrar Nueva Merma' }}
                    </h1>
                    <p class="text-muted mb-0">
                        Formulario detallado para registro y gestión de mermas farmacéuticas
                    </p>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ route('farmacia.control-mermas.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="saveDraft()">
                        <i class="fas fa-save"></i> Guardar Borrador
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Estado del Formulario --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0" style="background: linear-gradient(135deg, #17a2b8, #138496); color: white;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h5 class="mb-1">
                                <i class="fas fa-clipboard-list"></i>
                                Estado del Registro
                            </h5>
                            <p class="mb-0" id="formStatus">
                                {{ isset($merma) ? 'Editando merma existente' : 'Nuevo registro de merma' }}
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <span class="badge bg-light text-dark fs-6" id="statusBadge">
                                {{ isset($merma) ? $merma->status : 'Borrador' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="mermaForm" enctype="multipart/form-data">
        {{-- Sección 1: Información Básica --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle"></i> 1. Información Básica del Producto
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label">Producto *</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="productSearch" placeholder="Buscar producto por nombre o código" autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" onclick="showProductModal()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div id="selectedProduct" class="mt-2" style="display: none;">
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong id="productName"></strong><br>
                                        <small id="productDetails"></small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearProduct()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="productId" name="product_id">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">Código de Merma</label>
                        <input type="text" class="form-control" id="mermaCode" name="code" 
                               value="{{ isset($merma) ? $merma->code : 'MERMA-2025-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) }}" readonly>
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">Fecha de Merma *</label>
                        <input type="date" class="form-control" id="mermaDate" name="merma_date" 
                               value="{{ isset($merma) ? $merma->merma_date : date('Y-m-d') }}" required>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Lote del Producto *</label>
                        <input type="text" class="form-control" id="loteNumber" name="lote_number" 
                               placeholder="Número de lote" value="{{ isset($merma) ? $merma->lote_number : '' }}" required>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Fecha de Vencimiento</label>
                        <input type="date" class="form-control" id="expirationDate" name="expiration_date" 
                               value="{{ isset($merma) ? $merma->expiration_date : '' }}">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Fecha de Fabricación</label>
                        <input type="date" class="form-control" id="manufacturingDate" name="manufacturing_date" 
                               value="{{ isset($merma) ? $merma->manufacturing_date : '' }}">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Cantidad Afectada *</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="affectedQuantity" name="affected_quantity" 
                                   min="1" value="{{ isset($merma) ? $merma->affected_quantity : '' }}" required>
                            <span class="input-group-text" id="unitLabel">unidades</span>
                        </div>
                        <small class="text-muted">Cantidad de unidades afectadas por la merma</small>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Unidad de Medida</label>
                        <select class="form-select" id="unitOfMeasure" name="unit_of_measure">
                            <option value="unidades" {{ (!isset($merma) || $merma->unit_of_measure == 'unidades') ? 'selected' : '' }}>Unidades</option>
                            <option value="frascos" {{ (isset($merma) && $merma->unit_of_measure == 'frascos') ? 'selected' : '' }}>Frascos</option>
                            <option value="cajas" {{ (isset($merma) && $merma->unit_of_measure == 'cajas') ? 'selected' : '' }}>Cajas</option>
                            <option value="litros" {{ (isset($merma) && $merma->unit_of_measure == 'litros') ? 'selected' : '' }}>Litros</option>
                            <option value="gramos" {{ (isset($merma) && $merma->unit_of_measure == 'gramos') ? 'selected' : '' }}>Gramos</option>
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Valor Unitario (S/)</label>
                        <input type="number" step="0.01" class="form-control" id="unitValue" name="unit_value" 
                               value="{{ isset($merma) ? $merma->unit_value : '' }}" onchange="calculateTotalValue()">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Valor Total Afectado (S/)</label>
                        <input type="number" step="0.01" class="form-control" id="totalValue" name="total_value" 
                               value="{{ isset($merma) ? $merma->total_value : '' }}" readonly>
                        <small class="text-success" id="valueHint">Se calcula automáticamente</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección 2: Detalles de la Merma --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle"></i> 2. Detalles de la Merma
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label">Causa Principal *</label>
                        <select class="form-select" id="mermaCause" name="merma_cause" required>
                            <option value="">Seleccionar causa principal</option>
                            <option value="vencimiento" {{ (isset($merma) && $merma->merma_cause == 'vencimiento') ? 'selected' : '' }}>
                                Vencimiento
                            </option>
                            <option value="deterioro" {{ (isset($merma) && $merma->merma_cause == 'deterioro') ? 'selected' : '' }}>
                                Deterioro por condiciones inadecuadas
                            </option>
                            <option value="error_disp" {{ (isset($merma) && $merma->merma_cause == 'error_disp') ? 'selected' : '' }}>
                                Error de Dispensación
                            </option>
                            <option value="rotura" {{ (isset($merma) && $merma->merma_cause == 'rotura') ? 'selected' : '' }}>
                                Rotura/Daño Físico
                            </option>
                            <option value="robo" {{ (isset($merma) && $merma->merma_cause == 'robo') ? 'selected' : '' }}>
                                Robo/Pérdida
                            </option>
                            <option value="rechazo_calidad" {{ (isset($merma) && $merma->merma_cause == 'rechazo_calidad') ? 'selected' : '' }}>
                                Rechazo por Control de Calidad
                            </option>
                            <option value="error_almacenamiento" {{ (isset($merma) && $merma->merma_cause == 'error_almacenamiento') ? 'selected' : '' }}>
                                Error de Almacenamiento
                            </option>
                            <option value="transporte" {{ (isset($merma) && $merma->merma_cause == 'transporte') ? 'selected' : '' }}>
                                Daño durante Transporte
                            </option>
                            <option value="otros" {{ (isset($merma) && $merma->merma_cause == 'otros') ? 'selected' : '' }}>
                                Otros
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">Severidad</label>
                        <select class="form-select" id="severity" name="severity">
                            <option value="menor" {{ (!isset($merma) || $merma->severity == 'menor') ? 'selected' : '' }}>Menor</option>
                            <option value="moderada" {{ (isset($merma) && $merma->severity == 'moderada') ? 'selected' : '' }}>Moderada</option>
                            <option value="mayor" {{ (isset($merma) && $merma->severity == 'mayor') ? 'selected' : '' }}>Mayor</option>
                            <option value="crítica" {{ (isset($merma) && $merma->severity == 'crítica') ? 'selected' : '' }}>Crítica</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Descripción Detallada de la Merma *</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Descripción detallada de cómo ocurrió la merma, circunstancias, etc." required>{{ isset($merma) ? $merma->description : '' }}</textarea>
                        <small class="text-muted">Proporcionar todos los detalles relevantes para el análisis</small>
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">Fecha de Detección</label>
                        <input type="datetime-local" class="form-control" id="detectionDate" name="detection_date" 
                               value="{{ isset($merma) ? $merma->detection_date : date('Y-m-d\TH:i') }}">
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">¿Se puede recuperar el producto?</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isRecoverable" name="is_recoverable" 
                                   {{ (isset($merma) && $merma->is_recoverable) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isRecoverable">
                                Sí, el producto puede ser recuperado/procesado
                            </label>
                        </div>
                    </div>

                    <div class="col-12" id="recoveryDetails" style="display: none;">
                        <label class="form-label">Detalles de Recuperación</label>
                        <textarea class="form-control" id="recoveryDetails" name="recovery_details" rows="3" 
                                  placeholder="Descripción de cómo se puede recuperar el producto">{{ isset($merma) ? $merma->recovery_details : '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección 3: Personal y Responsabilidad --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users"></i> 3. Personal y Responsabilidad
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label">Persona que Detecta *</label>
                        <select class="form-select" id="detectedBy" name="detected_by" required>
                            <option value="">Seleccionar persona</option>
                            <option value="luis_valencia" {{ (isset($merma) && $merma->detected_by == 'luis_valencia') ? 'selected' : '' }}>
                                Luis Valencia - Auxiliar de Farmacia
                            </option>
                            <option value="ana_rodriguez" {{ (isset($merma) && $merma->detected_by == 'ana_rodriguez') ? 'selected' : '' }}>
                                Q.F. Ana Rodríguez - Químico Farmacéutico
                            </option>
                            <option value="maria_gonzalez" {{ (isset($merma) && $merma->detected_by == 'maria_gonzalez') ? 'selected' : '' }}>
                                María González - Auxiliar
                            </option>
                            <option value="carlos_mendoza" {{ (isset($merma) && $merma->detected_by == 'carlos_mendoza') ? 'selected' : '' }}>
                                Dr. Carlos Mendoza - Director
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Responsable del Área *</label>
                        <select class="form-select" id="areaResponsible" name="area_responsible" required>
                            <option value="">Seleccionar responsable</option>
                            <option value="ana_rodriguez" {{ (isset($merma) && $merma->area_responsible == 'ana_rodriguez') ? 'selected' : '' }}>
                                Q.F. Ana Rodríguez - Q.F. Principal
                            </option>
                            <option value="luis_valencia" {{ (isset($merma) && $merma->area_responsible == 'luis_valencia') ? 'selected' : '' }}>
                                Luis Valencia - Encargado de Almacén
                            </option>
                            <option value="carlos_mendoza" {{ (isset($merma) && $merma->area_responsible == 'carlos_mendoza') ? 'selected' : '' }}>
                                Dr. Carlos Mendoza - Director de Farmacia
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Supervisor Asignado</label>
                        <select class="form-select" id="supervisor" name="supervisor">
                            <option value="">Sin asignar</option>
                            <option value="ana_rodriguez" {{ (isset($merma) && $merma->supervisor == 'ana_rodriguez') ? 'selected' : '' }}>
                                Q.F. Ana Rodríguez
                            </option>
                            <option value="carlos_mendoza" {{ (isset($merma) && $merma->supervisor == 'carlos_mendoza') ? 'selected' : '' }}>
                                Dr. Carlos Mendoza
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">Ubicación donde Ocurrió *</label>
                        <select class="form-select" id="location" name="location" required>
                            <option value="">Seleccionar ubicación</option>
                            <option value="almacen_principal" {{ (isset($merma) && $merma->location == 'almacen_principal') ? 'selected' : '' }}>
                                Almacén Principal
                            </option>
                            <option value="almacen_secundario" {{ (isset($merma) && $merma->location == 'almacen_secundario') ? 'selected' : '' }}>
                                Almacén Secundario
                            </option>
                            <option value="refrigerador" {{ (isset($merma) && $merma->location == 'refrigerador') ? 'selected' : '' }}>
                                Refrigerador
                            </option>
                            <option value="congelador" {{ (isset($merma) && $merma->location == 'congelador') ? 'selected' : '' }}>
                                Congelador
                            </option>
                            <option value="mostrador" {{ (isset($merma) && $merma->location == 'mostrador') ? 'selected' : '' }}>
                                Mostrador de Ventas
                            </option>
                            <option value="area_preparacion" {{ (isset($merma) && $merma->location == 'area_preparacion') ? 'selected' : '' }}>
                                Área de Preparación
                            </option>
                            <option value="area_recepcion" {{ (isset($merma) && $merma->location == 'area_recepcion') ? 'selected' : '' }}>
                                Área de Recepción
                            </option>
                            <option value="transporte" {{ (isset($merma) && $merma->location == 'transporte') ? 'selected' : '' }}>
                                Durante Transporte
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">Turno de Trabajo</label>
                        <select class="form-select" id="workShift" name="work_shift">
                            <option value="mañana" {{ (!isset($merma) || $merma->work_shift == 'mañana') ? 'selected' : '' }}>Mañana (06:00 - 14:00)</option>
                            <option value="tarde" {{ (isset($merma) && $merma->work_shift == 'tarde') ? 'selected' : '' }}>Tarde (14:00 - 22:00)</option>
                            <option value="noche" {{ (isset($merma) && $merma->work_shift == 'noche') ? 'selected' : '' }}>Noche (22:00 - 06:00)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección 4: Acciones Correctivas --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tools"></i> 4. Acciones Correctivas y Preventivas
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Acciones Inmediatas Tomadas</label>
                        <textarea class="form-control" id="immediateActions" name="immediate_actions" rows="3" 
                                  placeholder="Describir las acciones que se tomaron inmediatamente después de detectar la merma">{{ isset($merma) ? $merma->immediate_actions : '' }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Acciones Correctivas Propuestas</label>
                        <textarea class="form-control" id="correctiveActions" name="corrective_actions" rows="3" 
                                  placeholder="Acciones para corregir las causas que originaron la merma">{{ isset($merma) ? $merma->corrective_actions : '' }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Acciones Preventivas Sugeridas</label>
                        <textarea class="form-control" id="preventiveActions" name="preventive_actions" rows="3" 
                                  placeholder="Acciones para prevenir que vuelva a ocurrir una merma similar">{{ isset($merma) ? $merma->preventive_actions : '' }}</textarea>
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">¿Requiere Capacitación Adicional?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="requiresTraining" name="requires_training" 
                                   {{ (isset($merma) && $merma->requires_training) ? 'checked' : '' }}>
                            <label class="form-check-label" for="requiresTraining">
                                Sí, se requiere capacitación del personal
                            </label>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">¿Requiere Modificación de Procedimientos?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="requiresProcedureChange" name="requires_procedure_change" 
                                   {{ (isset($merma) && $merma->requires_procedure_change) ? 'checked' : '' }}>
                            <label class="form-check-label" for="requiresProcedureChange">
                                Sí, se requiere actualizar procedimientos
                            </label>
                        </div>
                    </div>

                    <div class="col-12" id="trainingDetails" style="display: none;">
                        <label class="form-label">Detalles de Capacitación Requerida</label>
                        <textarea class="form-control" id="trainingDetails" name="training_details" rows="2" 
                                  placeholder="Especificar el tipo de capacitación necesaria">{{ isset($merma) ? $merma->training_details : '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección 5: Documentación y Archivos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-paperclip"></i> 5. Documentación y Evidencias
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Fotografías de la Merma</label>
                        <div class="border border-dashed rounded p-4 text-center" id="photoUploadArea">
                            <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Arrastre las imágenes aquí o haga clic para seleccionar</p>
                            <input type="file" id="photos" name="photos[]" multiple accept="image/*" style="display: none;">
                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('photos').click()">
                                <i class="fas fa-upload"></i> Seleccionar Imágenes
                            </button>
                        </div>
                        <div id="photoPreview" class="row g-2 mt-2"></div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Documentos Relacionados</label>
                        <div class="border border-dashed rounded p-4 text-center" id="documentUploadArea">
                            <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Facturas, certificados de calidad, etc.</p>
                            <input type="file" id="documents" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="display: none;">
                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('documents').click()">
                                <i class="fas fa-upload"></i> Seleccionar Documentos
                            </button>
                        </div>
                        <div id="documentPreview" class="row g-2 mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección 6: Configuración de Aprobación --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="fas fa-check-circle"></i> 6. Configuración de Aprobación
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label">Nivel de Aprobación Requerido</label>
                        <select class="form-select" id="approvalLevel" name="approval_level">
                            <option value="automatic" {{ (!isset($merma) || $merma->approval_level == 'automatic') ? 'selected' : '' }}>
                                Automático (mermas menores)
                            </option>
                            <option value="supervisor" {{ (isset($merma) && $merma->approval_level == 'supervisor') ? 'selected' : '' }}>
                                Supervisor (mermas moderadas)
                            </option>
                            <option value="manager" {{ (isset($merma) && $merma->approval_level == 'manager') ? 'selected' : '' }}>
                                Gerente/Director (mermas mayores)
                            </option>
                            <option value="digemid" {{ (isset($merma) && $merma->approval_level == 'digemid') ? 'selected' : '' }}>
                                Requiere DIGEMID (mermas críticas)
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">¿Notificar automáticamente?</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="autoNotify" name="auto_notify" 
                                   {{ (!isset($merma) || $merma->auto_notify) ? 'checked' : '' }}>
                            <label class="form-check-label" for="autoNotify">
                                Enviar notificaciones automáticas a supervisores
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Observaciones Adicionales</label>
                        <textarea class="form-control" id="additionalNotes" name="additional_notes" rows="2" 
                                  placeholder="Observaciones adicionales para el proceso de aprobación">{{ isset($merma) ? $merma->additional_notes : '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botones de Acción --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                            <i class="fas fa-save"></i> Guardar Borrador
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="previewMerma()">
                            <i class="fas fa-eye"></i> Vista Previa
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" onclick="cancelForm()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> {{ isset($merma) ? 'Actualizar Merma' : 'Enviar para Aprobación' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- ==========================================
     MODALES
========================================== --}}

{{-- Modal de Búsqueda de Productos --}}
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-search"></i> Buscar Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-lg-4">
                        <input type="text" class="form-control" id="productSearchInput" placeholder="Buscar por nombre o código">
                    </div>
                    <div class="col-lg-3">
                        <select class="form-select" id="categoryFilter">
                            <option value="">Todas las categorías</option>
                            <option value="medicamentos">Medicamentos</option>
                            <option value="dispositivos">Dispositivos Médicos</option>
                            <option value="cosméticos">Cosméticos</option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <select class="form-select" id="locationFilter">
                            <option value="">Todas las ubicaciones</option>
                            <option value="almacen">Almacén Principal</option>
                            <option value="refrigerador">Refrigerador</option>
                            <option value="congelador">Congelador</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-primary" onclick="searchProducts()">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="productsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Lote</th>
                                <th>Stock</th>
                                <th>Ubicación</th>
                                <th>Vencimiento</th>
                                <th>Valor Unit.</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr onclick="selectProduct('paracetamol', 'Paracetamol 500mg - Jarabe 60ml', 'L2023-001', '245 unidades', 'Almacén Principal', '15/03/2025', 'S/ 10.00')">
                                <td><code>MED001</code></td>
                                <td>
                                    <div class="fw-bold">Paracetamol 500mg</div>
                                    <small class="text-muted">Jarabe 60ml</small>
                                </td>
                                <td>L2023-001</td>
                                <td>245 unidades</td>
                                <td>Almacén Principal</td>
                                <td><span class="badge bg-danger">15/03/2025</span></td>
                                <td>S/ 10.00</td>
                                <td>
                                    <button class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i> Seleccionar
                                    </button>
                                </td>
                            </tr>
                            
                            <tr onclick="selectProduct('insulina', 'Insulina NPH - Vial 10ml', 'INS2023-045', '89 viales', 'Refrigerador', '22/02/2025', 'S/ 65.00')">
                                <td><code>DIS002</code></td>
                                <td>
                                    <div class="fw-bold">Insulina NPH</div>
                                    <small class="text-muted">Vial 10ml</small>
                                </td>
                                <td>INS2023-045</td>
                                <td>89 viales</td>
                                <td>Refrigerador</td>
                                <td><span class="badge bg-danger">22/02/2025</span></td>
                                <td>S/ 65.00</td>
                                <td>
                                    <button class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i> Seleccionar
                                    </button>
                                </td>
                            </tr>
                            
                            <tr onclick="selectProduct('amoxicilina', 'Amoxicilina 250mg - Cápsulas', 'AMX2024-012', '156 blisters', 'Almacén Principal', '08/01/2025', 'S/ 2.50')">
                                <td><code>MED003</code></td>
                                <td>
                                    <div class="fw-bold">Amoxicilina 250mg</div>
                                    <small class="text-muted">Cápsulas</small>
                                </td>
                                <td>AMX2024-012</td>
                                <td>156 blisters</td>
                                <td>Almacén Principal</td>
                                <td><span class="badge bg-danger">08/01/2025</span></td>
                                <td>S/ 2.50</td>
                                <td>
                                    <button class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i> Seleccionar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Vista Previa --}}
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Vista Previa de la Merma
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    {{-- Contenido dinámico de la vista previa --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="printPreview()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar event listeners
    setupEventListeners();
    
    // Verificar si hay un producto preseleccionado
    const productId = document.getElementById('productId').value;
    if (productId) {
        // Cargar datos del producto existente
        loadExistingProduct(productId);
    }
});

function setupEventListeners() {
    // Calcular valor total automáticamente
    document.getElementById('affectedQuantity').addEventListener('input', calculateTotalValue);
    document.getElementById('unitValue').addEventListener('input', calculateTotalValue);
    
    // Mostrar/ocultar campos condicionales
    document.getElementById('isRecoverable').addEventListener('change', function() {
        const recoveryDetails = document.getElementById('recoveryDetails');
        if (this.checked) {
            recoveryDetails.style.display = 'block';
        } else {
            recoveryDetails.style.display = 'none';
        }
    });
    
    document.getElementById('requiresTraining').addEventListener('change', function() {
        const trainingDetails = document.getElementById('trainingDetails');
        if (this.checked) {
            trainingDetails.style.display = 'block';
        } else {
            trainingDetails.style.display = 'none';
        }
    });
    
    // Configurar drag & drop para archivos
    setupFileUpload();
    
    // Búsqueda de productos en tiempo real
    document.getElementById('productSearch').addEventListener('input', debounce(searchProductsRealTime, 300));
}

// Funciones de Cálculo
function calculateTotalValue() {
    const quantity = parseFloat(document.getElementById('affectedQuantity').value) || 0;
    const unitValue = parseFloat(document.getElementById('unitValue').value) || 0;
    const total = quantity * unitValue;
    
    document.getElementById('totalValue').value = total.toFixed(2);
    
    // Actualizar etiqueta de unidad
    const unitOfMeasure = document.getElementById('unitOfMeasure').value;
    document.getElementById('unitLabel').textContent = unitOfMeasure;
}

// Funciones de Gestión de Productos
function showProductModal() {
    document.getElementById('productModal').classList.add('show');
    document.getElementById('productModal').style.display = 'block';
}

function selectProduct(id, name, lote, stock, location, expiration, unitValue) {
    // Llenar campos del formulario
    document.getElementById('productId').value = id;
    document.getElementById('productSearch').value = name;
    document.getElementById('loteNumber').value = lote;
    document.getElementById('unitValue').value = parseFloat(unitValue.replace('S/ ', ''));
    
    // Mostrar producto seleccionado
    showSelectedProduct(name, lote, stock, location, expiration, unitValue);
    
    // Cerrar modal
    document.getElementById('productModal').classList.remove('show');
    document.getElementById('productModal').style.display = 'none';
    
    // Recalcular valor total
    calculateTotalValue();
}

function showSelectedProduct(name, lote, stock, location, expiration, unitValue) {
    const container = document.getElementById('selectedProduct');
    const productName = document.getElementById('productName');
    const productDetails = document.getElementById('productDetails');
    
    productName.textContent = name;
    productDetails.innerHTML = `
        <strong>Lote:</strong> ${lote} | 
        <strong>Stock:</strong> ${stock} | 
        <strong>Ubicación:</strong> ${location} | 
        <strong>Vencimiento:</strong> ${expiration} | 
        <strong>Valor:</strong> ${unitValue}
    `;
    
    container.style.display = 'block';
}

function clearProduct() {
    document.getElementById('productId').value = '';
    document.getElementById('productSearch').value = '';
    document.getElementById('loteNumber').value = '';
    document.getElementById('selectedProduct').style.display = 'none';
}

function loadExistingProduct(productId) {
    // Simular carga de producto existente
    const productData = {
        'paracetamol': {
            name: 'Paracetamol 500mg - Jarabe 60ml',
            lote: 'L2023-001',
            stock: '245 unidades',
            location: 'Almacén Principal',
            expiration: '15/03/2025',
            unitValue: 10.00
        }
    };
    
    const product = productData[productId];
    if (product) {
        showSelectedProduct(product.name, product.lote, product.stock, product.location, product.expiration, 'S/ ' + product.unitValue);
        document.getElementById('unitValue').value = product.unitValue;
        calculateTotalValue();
    }
}

// Funciones de Búsqueda
function searchProducts() {
    const searchTerm = document.getElementById('productSearchInput').value;
    const category = document.getElementById('categoryFilter').value;
    const location = document.getElementById('locationFilter').value;
    
    console.log('Buscando productos:', { searchTerm, category, location });
    // Aquí se implementaría la búsqueda real
}

function searchProductsRealTime(event) {
    const searchTerm = event.target.value;
    if (searchTerm.length >= 3) {
        console.log('Búsqueda en tiempo real:', searchTerm);
        // Aquí se implementaría la búsqueda en tiempo real
    }
}

// Funciones de Archivos
function setupFileUpload() {
    // Configurar drag & drop para fotos
    const photoArea = document.getElementById('photoUploadArea');
    const photoInput = document.getElementById('photos');
    
    photoArea.addEventListener('click', () => photoInput.click());
    photoArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        photoArea.classList.add('border-primary');
    });
    photoArea.addEventListener('dragleave', () => {
        photoArea.classList.remove('border-primary');
    });
    photoArea.addEventListener('drop', (e) => {
        e.preventDefault();
        photoArea.classList.remove('border-primary');
        handleFileUpload(e.dataTransfer.files, 'photo');
    });
    
    photoInput.addEventListener('change', (e) => {
        handleFileUpload(e.target.files, 'photo');
    });
    
    // Configurar drag & drop para documentos
    const documentArea = document.getElementById('documentUploadArea');
    const documentInput = document.getElementById('documents');
    
    documentArea.addEventListener('click', () => documentInput.click());
    documentArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        documentArea.classList.add('border-primary');
    });
    documentArea.addEventListener('dragleave', () => {
        documentArea.classList.remove('border-primary');
    });
    documentArea.addEventListener('drop', (e) => {
        e.preventDefault();
        documentArea.classList.remove('border-primary');
        handleFileUpload(e.dataTransfer.files, 'document');
    });
    
    documentInput.addEventListener('change', (e) => {
        handleFileUpload(e.target.files, 'document');
    });
}

function handleFileUpload(files, type) {
    const previewContainer = document.getElementById(type + 'Preview');
    previewContainer.innerHTML = '';
    
    Array.from(files).forEach(file => {
        if (file.type.startsWith('image/') && type === 'photo') {
            const reader = new FileReader();
            reader.onload = (e) => {
                const col = document.createElement('div');
                col.className = 'col-lg-3 col-md-4 col-sm-6';
                col.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                        <div class="card-body p-2">
                            <small class="text-muted">${file.name}</small>
                        </div>
                    </div>
                `;
                previewContainer.appendChild(col);
            };
            reader.readAsDataURL(file);
        } else {
            const col = document.createElement('div');
            col.className = 'col-lg-4 col-md-6';
            col.innerHTML = `
                <div class="card border">
                    <div class="card-body p-2">
                        <i class="fas fa-file fa-2x text-primary"></i>
                        <br>
                        <small>${file.name}</small>
                        <br>
                        <span class="badge bg-secondary">${formatFileSize(file.size)}</span>
                    </div>
                </div>
            `;
            previewContainer.appendChild(col);
        }
    });
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Funciones de Formulario
function saveDraft() {
    const formData = new FormData(document.getElementById('mermaForm'));
    formData.append('status', 'draft');
    
    // Simular guardado
    Swal.fire({
        title: 'Guardando borrador...',
        text: 'La merma se guardará como borrador',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Borrador Guardado',
            text: 'La merma ha sido guardada como borrador exitosamente.',
            showConfirmButton: false,
            timer: 2000
        });
        
        document.getElementById('statusBadge').textContent = 'Borrador';
    }, 2000);
}

function previewMerma() {
    const formData = getFormData();
    const previewContent = generatePreviewContent(formData);
    
    document.getElementById('previewContent').innerHTML = previewContent;
    document.getElementById('previewModal').classList.add('show');
    document.getElementById('previewModal').style.display = 'block';
}

function generatePreviewContent(data) {
    return `
        <div class="row g-4">
            <div class="col-12">
                <h4>Vista Previa de Merma</h4>
                <hr>
            </div>
            
            <div class="col-lg-6">
                <h6><i class="fas fa-info-circle text-primary"></i> Información del Producto</h6>
                <table class="table table-sm">
                    <tr><td><strong>Producto:</strong></td><td>${data.productName || 'No seleccionado'}</td></tr>
                    <tr><td><strong>Lote:</strong></td><td>${data.lote || 'N/A'}</td></tr>
                    <tr><td><strong>Cantidad:</strong></td><td>${data.quantity || 'N/A'} ${data.unit || 'unidades'}</td></tr>
                    <tr><td><strong>Valor Total:</strong></td><td>S/ ${data.totalValue || '0.00'}</td></tr>
                </table>
            </div>
            
            <div class="col-lg-6">
                <h6><i class="fas fa-exclamation-triangle text-warning"></i> Detalles de la Merma</h6>
                <table class="table table-sm">
                    <tr><td><strong>Causa:</strong></td><td>${data.cause || 'N/A'}</td></tr>
                    <tr><td><strong>Severidad:</strong></td><td>${data.severity || 'N/A'}</td></tr>
                    <tr><td><strong>Ubicación:</strong></td><td>${data.location || 'N/A'}</td></tr>
                    <tr><td><strong>Responsable:</strong></td><td>${data.responsible || 'N/A'}</td></tr>
                </table>
            </div>
            
            <div class="col-12">
                <h6><i class="fas fa-file-alt text-info"></i> Descripción</h6>
                <div class="border rounded p-3 bg-light">
                    ${data.description || 'Sin descripción'}
                </div>
            </div>
            
            <div class="col-12">
                <h6><i class="fas fa-tools text-success"></i> Acciones Correctivas</h6>
                <div class="row">
                    <div class="col-lg-6">
                        <strong>Inmediatas:</strong>
                        <div class="border rounded p-2 bg-light">
                            ${data.immediateActions || 'No especificadas'}
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <strong>Correctivas:</strong>
                        <div class="border rounded p-2 bg-light">
                            ${data.correctiveActions || 'No especificadas'}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function getFormData() {
    return {
        productName: document.getElementById('productSearch').value,
        lote: document.getElementById('loteNumber').value,
        quantity: document.getElementById('affectedQuantity').value,
        unit: document.getElementById('unitOfMeasure').value,
        totalValue: document.getElementById('totalValue').value,
        cause: document.getElementById('mermaCause').selectedOptions[0]?.text,
        severity: document.getElementById('severity').selectedOptions[0]?.text,
        location: document.getElementById('location').selectedOptions[0]?.text,
        responsible: document.getElementById('detectedBy').selectedOptions[0]?.text,
        description: document.getElementById('description').value,
        immediateActions: document.getElementById('immediateActions').value,
        correctiveActions: document.getElementById('correctiveActions').value
    };
}

function cancelForm() {
    Swal.fire({
        title: 'Cancelar Formulario',
        text: '¿Está seguro de cancelar? Se perderán los cambios no guardados.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'Continuar editando'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ route('farmacia.control-mermas.index') }}";
        }
    });
}

function printPreview() {
    const printContent = document.getElementById('previewContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Vista Previa de Merma</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .table { width: 100%; border-collapse: collapse; }
                    .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    .table th { background-color: #f2f2f2; }
                    .border { border: 1px solid #ddd; }
                    .bg-light { background-color: #f8f9fa; }
                </style>
            </head>
            <body>${printContent}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Función debounce para búsqueda en tiempo real
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Manejo del envío del formulario
document.getElementById('mermaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validaciones básicas
    const requiredFields = ['productId', 'mermaDate', 'loteNumber', 'affectedQuantity', 'mermaCause', 'description', 'detectedBy', 'areaResponsible', 'location'];
    
    for (let field of requiredFields) {
        const element = document.getElementById(field);
        if (!element.value) {
            Swal.fire({
                icon: 'error',
                title: 'Campo Requerido',
                text: `El campo ${field} es obligatorio.`
            });
            element.focus();
            return;
        }
    }
    
    // Validar valor total
    const totalValue = parseFloat(document.getElementById('totalValue').value);
    if (totalValue <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error en Valor',
            text: 'El valor total debe ser mayor a cero.'
        });
        document.getElementById('unitValue').focus();
        return;
    }
    
    // Mostrar confirmación
    const isEdit = document.getElementById('productId').value && document.getElementById('productId').value !== '';
    
    Swal.fire({
        title: isEdit ? 'Actualizar Merma' : 'Enviar Merma',
        text: isEdit ? '¿Está seguro de actualizar esta merma?' : '¿Está seguro de enviar esta merma para aprobación?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: isEdit ? 'Sí, actualizar' : 'Sí, enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            submitForm();
        }
    });
});

function submitForm() {
    const formData = new FormData(document.getElementById('mermaForm'));
    const isEdit = document.getElementById('productId').value && document.getElementById('productId').value !== '';
    
    Swal.fire({
        title: isEdit ? 'Actualizando...' : 'Enviando...',
        text: isEdit ? 'Actualizando los datos de la merma' : 'Enviando merma para aprobación',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Simular envío del formulario
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: isEdit ? 'Merma Actualizada' : 'Merma Enviada',
            text: isEdit ? 'La merma ha sido actualizada exitosamente.' : 'La merma ha sido enviada para aprobación exitosamente.',
            showConfirmButton: false,
            timer: 2000
        }).then(() => {
            window.location.href = "{{ route('farmacia.control-mermas.index') }}";
        });
    }, 2000);
}
</script>
@endsection

@section('styles')
<style>
/* Estilos para el formulario de creación de mermas */
.form-section {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
}

.required-field::after {
    content: " *";
    color: red;
}

.file-upload-area {
    transition: all 0.3s ease;
}

.file-upload-area:hover {
    background-color: #f8f9fa;
    border-color: #007bff;
}

.file-upload-area.dragover {
    background-color: #e3f2fd;
    border-color: #2196f3;
    border-style: dashed;
}

.product-selected {
    animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.form-status-badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
}

.status-borrador {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

.status-enviado {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

.status-aprobado {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
}

.status-rechazado {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

/* Estilos para campos condicionales */
.conditional-field {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Estilos para preview de archivos */
.file-preview-item {
    transition: transform 0.2s ease;
}

.file-preview-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-section {
        border-left: none;
        border-top: 2px solid #007bff;
        padding-left: 0;
        padding-top: 1rem;
    }
    
    .card-title {
        font-size: 1.1rem;
    }
    
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}

/* Animaciones para validaciones */
.invalid-feedback {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Estilos para tabla de productos */
.products-table tbody tr {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.products-table tbody tr:hover {
    background-color: #f8f9fa;
}

/* Estilos para botones de acción */
.btn-action {
    transition: all 0.2s ease;
}

.btn-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endsection