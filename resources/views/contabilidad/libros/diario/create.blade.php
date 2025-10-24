@extends('layouts.contador')

@section('title', 'Nuevo Asiento Diario - SIFANO')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contabilidad') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('libros-diario') }}">Libro Diario</a></li>
    <li class="breadcrumb-item active">Nuevo Asiento</li>
@endsection

@section('contador-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-plus text-success me-2"></i>
        Nuevo Asiento Diario
    </h1>
    <div>
        <a href="{{ route('libros-diario') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Volver al Listado
        </a>
    </div>
</div>

<form method="POST" action="{{ route('libros-diario.store') }}" id="asientoForm">
    @csrf
    
    <div class="row">
        <!-- Información Principal -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Información del Asiento
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Número de Asiento</label>
                            <input type="text" name="numero_asiento" class="form-control" 
                                   value="{{ $numeroAsiento ?? '' }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha *</label>
                            <input type="date" name="fecha" class="form-control" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Descripción *</label>
                            <input type="text" name="descripcion" class="form-control" 
                                   placeholder="Descripción del asiento" required>
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Asiento</label>
                            <select name="tipo_asiento" class="form-select" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="manual">Manual</option>
                                <option value="automático">Automático</option>
                                <option value="ajuste">Ajuste</option>
                                <option value="cierre">Cierre</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Referencia</label>
                            <input type="text" name="referencia" class="form-control" 
                                   placeholder="Factura, recibo, etc.">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Partidas Contables -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Partidas Contables
                    </h5>
                    <button type="button" class="btn btn-sm btn-success" onclick="agregarPartida()">
                        <i class="fas fa-plus me-1"></i>
                        Agregar Partida
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm" id="partidasTable">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 40%">Cuenta</th>
                                    <th style="width: 30%">Descripción</th>
                                    <th style="width: 15%">Debe</th>
                                    <th style="width: 15%">Haber</th>
                                    <th style="width: 5%">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="partidasBody">
                                <!-- Las partidas se agregarán dinámicamente -->
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="2" class="text-end">Totales:</th>
                                    <th>
                                        <span id="totalDebe" class="fw-bold text-success">S/ 0.00</span>
                                    </th>
                                    <th>
                                        <span id="totalHaber" class="fw-bold text-primary">S/ 0.00</span>
                                    </th>
                                    <th>
                                        <span id="diferencia" class="fw-bold text-warning">S/ 0.00</span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Validación de Balance -->
                    <div id="balanceValidation" class="alert mt-3" style="display: none;">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong id="balanceMessage"></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Resumen del Asiento -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>
                        Resumen
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Debe:</span>
                        <strong class="text-success" id="resumenDebe">S/ 0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Haber:</span>
                        <strong class="text-primary" id="resumenHaber">S/ 0.00</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Diferencia:</span>
                        <strong id="resumenDiferencia" class="text-warning">S/ 0.00</strong>
                    </div>
                </div>
            </div>

            <!-- Cuentas Principales -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Cuentas Frecuentes
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($cuentasFrecuentes ?? [] as $cuenta)
                    <button type="button" class="btn btn-sm btn-outline-primary mb-2 w-100 text-start"
                            onclick="agregarPartidaRapida({{ $cuenta->id }}, '{{ $cuenta->nombre }}', 'Cuenta {{ $cuenta->codigo }}')">
                        {{ $cuenta->codigo }} - {{ Str::limit($cuenta->nombre, 25) }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Acciones -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success" id="guardarBtn" disabled>
                            <i class="fas fa-save me-2"></i>
                            Guardar Asiento
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="limpiarFormulario()">
                            <i class="fas fa-eraser me-2"></i>
                            Limpiar
                        </button>
                        <a href="{{ route('libros-diario') }}" class="btn btn-outline-danger">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Template para nuevas partidas -->
<template id="partidaTemplate">
    <tr>
        <td>
            <select name="cuenta_id[]" class="form-select form-select-sm cuenta-select" required>
                <option value="">Seleccionar cuenta</option>
                @foreach($cuentas ?? [] as $cuenta)
                    <option value="{{ $cuenta->id }}">{{ $cuenta->codigo }} - {{ $cuenta->nombre }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" name="descripcion_partida[]" class="form-control form-control-sm" 
                   placeholder="Descripción" required>
        </td>
        <td>
            <input type="number" name="debe[]" class="form-control form-control-sm debe-input" 
                   step="0.01" min="0" placeholder="0.00" onchange="calcularTotales()">
        </td>
        <td>
            <input type="number" name="haber[]" class="form-control form-control-sm haber-input" 
                   step="0.01" min="0" placeholder="0.00" onchange="calcularTotales()">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarPartida(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@section('scripts')
<script>
    let partidaCounter = 0;

    function agregarPartida() {
        const template = document.getElementById('partidaTemplate');
        const clone = template.content.cloneNode(true);
        const tbody = document.getElementById('partidasBody');
        
        // Inicializar Select2 en el nuevo select
        setTimeout(() => {
            const select = clone.querySelector('.cuenta-select');
            $(select).select2({
                theme: 'bootstrap-5',
                placeholder: 'Buscar cuenta...'
            });
        }, 100);
        
        tbody.appendChild(clone);
        partidaCounter++;
        calcularTotales();
    }

    function agregarPartidaRapida(cuentaId, cuentaNombre, cuentaCodigo) {
        const template = document.getElementById('partidaTemplate');
        const clone = template.content.cloneNode(true);
        const tbody = document.getElementById('partidasBody');
        
        // Seleccionar la cuenta automáticamente
        const select = clone.querySelector('.cuenta-select');
        select.value = cuentaId;
        
        tbody.appendChild(clone);
        partidaCounter++;
        calcularTotales();
        
        // Enfocar en la descripción
        const descInput = tbody.lastElementChild.querySelector('input[name="descripcion_partida[]"]');
        if (descInput) {
            descInput.focus();
        }
    }

    function eliminarPartida(button) {
        const row = button.closest('tr');
        row.remove();
        partidaCounter--;
        calcularTotales();
    }

    function calcularTotales() {
        let totalDebe = 0;
        let totalHaber = 0;
        
        // Calcular totales
        document.querySelectorAll('.debe-input').forEach(input => {
            const value = parseFloat(input.value) || 0;
            totalDebe += value;
        });
        
        document.querySelectorAll('.haber-input').forEach(input => {
            const value = parseFloat(input.value) || 0;
            totalHaber += value;
        });
        
        const diferencia = totalDebe - totalHaber;
        
        // Actualizar visualizaciones
        document.getElementById('totalDebe').textContent = 'S/ ' + totalDebe.toFixed(2);
        document.getElementById('totalHaber').textContent = 'S/ ' + totalHaber.toFixed(2);
        document.getElementById('diferencia').textContent = 'S/ ' + diferencia.toFixed(2);
        
        document.getElementById('resumenDebe').textContent = 'S/ ' + totalDebe.toFixed(2);
        document.getElementById('resumenHaber').textContent = 'S/ ' + totalHaber.toFixed(2);
        document.getElementById('resumenDiferencia').textContent = 'S/ ' + diferencia.toFixed(2);
        
        // Validar balance
        validarBalance(diferencia);
        
        // Habilitar/deshabilitar botón de guardar
        const guardarBtn = document.getElementById('guardarBtn');
        const balanceValid = Math.abs(diferencia) < 0.01 && partidaCounter > 0;
        guardarBtn.disabled = !balanceValid;
    }

    function validarBalance(diferencia) {
        const balanceDiv = document.getElementById('balanceValidation');
        const balanceMessage = document.getElementById('balanceMessage');
        
        if (partidaCounter === 0) {
            balanceDiv.style.display = 'none';
            return;
        }
        
        if (Math.abs(diferencia) < 0.01) {
            balanceDiv.className = 'alert alert-success mt-3';
            balanceMessage.innerHTML = '<i class="fas fa-check-circle me-2"></i>El asiento está balanceado correctamente.';
        } else {
            balanceDiv.className = 'alert alert-danger mt-3';
            balanceMessage.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>El asiento NO está balanceado. La diferencia es de S/ ' + Math.abs(diferencia).toFixed(2);
        }
        
        balanceDiv.style.display = 'block';
    }

    function limpiarFormulario() {
        if (confirm('¿Estás seguro de que deseas limpiar el formulario?')) {
            document.getElementById('partidasBody').innerHTML = '';
            partidaCounter = 0;
            calcularTotales();
            
            // Limpiar otros campos
            document.querySelector('input[name="descripcion"]').value = '';
            document.querySelector('select[name="tipo_asiento"]').value = '';
            document.querySelector('input[name="referencia"]').value = '';
        }
    }

    // Validación del formulario
    document.getElementById('asientoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (partidaCounter === 0) {
            Swal.fire('Error', 'Debe agregar al menos una partida contable', 'error');
            return;
        }
        
        showLoading();
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                Swal.fire('Éxito', 'Asiento creado correctamente', 'success')
                    .then(() => {
                        window.location.href = data.redirect || '{{ route("libros-diario") }}';
                    });
            } else {
                Swal.fire('Error', data.message || 'Error creando el asiento', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            Swal.fire('Error', 'Error de conexión', 'error');
        });
    });

    // Inicializar componentes
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Select2 en todas las cuentas
        $('.cuenta-select').select2({
            theme: 'bootstrap-5',
            placeholder: 'Buscar cuenta...'
        });
        
        // Agregar una partida inicial
        agregarPartida();
        
        // Evitar que se ingresen valores en ambos campos (debe y haber)
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('debe-input')) {
                const row = e.target.closest('tr');
                const haberInput = row.querySelector('.haber-input');
                if (e.target.value && parseFloat(e.target.value) > 0) {
                    haberInput.value = '';
                    haberInput.disabled = true;
                } else {
                    haberInput.disabled = false;
                }
            } else if (e.target.classList.contains('haber-input')) {
                const row = e.target.closest('tr');
                const debeInput = row.querySelector('.debe-input');
                if (e.target.value && parseFloat(e.target.value) > 0) {
                    debeInput.value = '';
                    debeInput.disabled = true;
                } else {
                    debeInput.disabled = false;
                }
            }
        });
    });
</script>
@endsection