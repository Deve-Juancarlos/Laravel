@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Exportar Libro Mayor')

@push('styles')
    <link href="{{ asset('css/contabilidad/libro-mayor-exportar.css') }}" rel="stylesheet">
@endpush

{{-- 1. Título de la Página --}}
@section('page-title', 'Exportar Libro Mayor')

{{-- 2. Breadcrumbs --}}
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.dashboard.contador') }}">Contabilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contador.libro-mayor.index') }}">Libro Mayor</a></li>
    <li class="breadcrumb-item active" aria-current="page">Exportar</li>
@endsection

{{-- 3. Contenido Principal --}}
@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="export-card">
                <h1 class="h2 mb-3">
                    <i class="fas fa-file-excel"></i>
                    Exportar Libro Mayor
                </h1>
                <p class="mb-0">Genera reportes en Excel/CSV para análisis externo y respaldo</p>
            </div>
        </div>
    </div>

    <!-- Filtros para Exportación -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter"></i> Configurar Período de Exportación
                    </h6>
                </div>
                <div class="card-body">
                    {{-- Este formulario apunta a la ruta de exportación que ya definimos en el controlador --}}
                    <form method="POST" action="{{ route('contador.libro-mayor.exportar') }}" id="exportForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Fecha Inicio *</label>
                                <input type="date" name="fecha_inicio" class="form-control" 
                                       value="{{ $fechaInicio ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha Fin *</label>
                                <input type="date" name="fecha_fin" class="form-control" 
                                       value="{{ $fechaFin ?? \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cuenta Específica (Opcional)</label>
                                <input type="text" name="cuenta" class="form-control" 
                                       placeholder="Ej: 101 (Dejar en blanco para todas)">
                            </div>
                        </div>
                        
                        {{-- Input oculto para el tipo de exportación --}}
                        <input type="hidden" name="tipo" id="exportTypeInput" value="">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Opciones de Exportación -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h5 class="text-gray-800 mb-3">
                <i class="fas fa-list"></i> Selecciona el Tipo de Exportación
            </h5>
        </div>
        
        <!-- Opción 1: Resumen por Cuentas -->
        <div class="col-md-6 mb-3">
            <div class="export-option" onclick="selectExportType('resumen')" id="option-resumen">
                <div class="export-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h5>Resumen por Cuentas</h5>
                <p class="text-muted">
                    Consolidado por cuenta con totales de Debe, Haber y Saldos. 
                    Ideal para análisis gerencial.
                </p>
                <ul class="list-unstyled text-start">
                    <li><i class="fas fa-check text-success me-2"></i>Un registro por cuenta</li>
                    <li><i class="fas fa-check text-success me-2"></i>Totales por período</li>
                    <li><i class="fas fa-check text-success me-2"></i>Formato compacto</li>
                </ul>
                <input type="radio" name="export_type_radio" value="resumen" style="display: none;" id="radio-resumen">
            </div>
        </div>

        <!-- Opción 2: Detallado por Movimientos -->
        <div class="col-md-6 mb-3">
            <div class="export-option" onclick="selectExportType('detallado')" id="option-detallado">
                <div class="export-icon">
                    <i class="fas fa-list-alt"></i>
                </div>
                <h5>Detallado por Movimientos</h5>
                <p class="text-muted">
                    Incluye cada movimiento individual con asiento, fecha y montos. 
                    Para auditoría completa.
                </p>
                <ul class="list-unstyled text-start">
                    <li><i class="fas fa-check text-success me-2"></i>Todos los movimientos</li>
                    <li><i class="fas fa-check text-success me-2"></i>Información completa</li>
                    <li><i class="fas fa-check text-success me-2"></i>Auditoría completa</li>
                </ul>
                <input type="radio" name="export_type_radio" value="detallado" style="display: none;" id="radio-detallado">
            </div>
        </div>
    </div>

    <!-- Botón de Exportación -->
    <div class="row">
        <div class="col-md-12 text-center">
            <button type="button" class="btn btn-export-main" onclick="exportData()" id="exportButton" disabled>
                <i class="fas fa-download me-2"></i>
                Exportar a Excel/CSV
            </button>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    El archivo se descargará automáticamente al hacer clic
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedExportType = '';

function selectExportType(type) {
    // Remover selección anterior
    document.querySelectorAll('.export-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Seleccionar nueva opción
    document.getElementById('option-' + type).classList.add('selected');
    document.getElementById('radio-' + type).checked = true;
    selectedExportType = type;
    
    // Habilitar botón de exportación
    document.getElementById('exportButton').disabled = false;

    // Actualizar el input oculto del formulario
    document.getElementById('exportTypeInput').value = type;
}

function exportData() {
    if (!selectedExportType) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Por favor selecciona un tipo de exportación (Resumen o Detallado).',
        });
        return;
    }
    
    const form = document.getElementById('exportForm');
    
    // Cambiar texto del botón
    const button = document.getElementById('exportButton');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generando archivo...';
    button.disabled = true;
    
    // Enviar formulario
    form.submit();
    
    // Restaurar botón después de un tiempo
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false; 
    }, 3000); // 3 segundos para que inicie la descarga
}

// Establecer fechas por defecto si no están presentes (el controlador ya nos las pasa)
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    const fechaInicioInput = document.querySelector('input[name="fecha_inicio"]');
    const fechaFinInput = document.querySelector('input[name="fecha_fin"]');
    
    // Si el valor no fue seteado por el controlador, usamos el mes actual
    if (!fechaInicioInput.value) {
        fechaInicioInput.value = firstDay.toISOString().split('T')[0];
    }
    
    if (!fechaFinInput.value) {
        fechaFinInput.value = today.toISOString().split('T')[0];
    }
});
</script>
@endpush
