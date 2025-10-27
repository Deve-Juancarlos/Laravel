@extends('layouts.app')

@section('title', 'Exportar Libro Mayor')

@section('styles')
<style>
    .export-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 30px;
        color: white;
        margin-bottom: 20px;
        text-align: center;
    }
    .export-option {
        border: 2px solid #e3e6f0;
        border-radius: 10px;
        padding: 20px;
        transition: all 0.3s ease;
        cursor: pointer;
        height: 100%;
    }
    .export-option:hover {
        border-color: #667eea;
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .export-option.selected {
        border-color: #667eea;
        background-color: #f8f9fc;
    }
    .export-icon {
        font-size: 3em;
        margin-bottom: 15px;
        color: #667eea;
    }
    .btn-export-main {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        padding: 15px 30px;
        border-radius: 10px;
        color: white;
        font-weight: 600;
        font-size: 1.1em;
        transition: transform 0.3s ease;
    }
    .btn-export-main:hover {
        transform: translateY(-2px);
        color: white;
    }
    .preview-table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('contador.libro-mayor.index') }}">Libro Mayor</a></li>
                    <li class="breadcrumb-item active">Exportar</li>
                </ol>
            </nav>
        </div>
    </div>

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
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter"></i> Configurar Período de Exportación
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('contador.libro-mayor.exportar') }}" id="exportForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" 
                                       value="{{ $fechaInicio ?? '' }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control" 
                                       value="{{ $fechaFin ?? '' }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cuenta Específica (Opcional)</label>
                                <input type="text" name="cuenta_contable" class="form-control" 
                                       placeholder="Ej: 101.11">
                            </div>
                        </div>
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
                    Consolidado por cuenta contable con totales de Debe, Haber y Saldos. 
                    Ideal para análisis gerencial y resúmenes ejecutivos.
                </p>
                <ul class="list-unstyled text-start">
                    <li><i class="fas fa-check text-success me-2"></i>Un registro por cuenta</li>
                    <li><i class="fas fa-check text-success me-2"></i>Totales por período</li>
                    <li><i class="fas fa-check text-success me-2"></i>Formato compacto</li>
                    <li><i class="fas fa-check text-success me-2"></i>Excel compatible</li>
                </ul>
                <input type="radio" name="export_type" value="resumen" style="display: none;" id="radio-resumen">
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
                    Incluye cada movimiento individual con asiento, fecha, cuenta y montos. 
                    Para auditoría completa y verificación detallada.
                </p>
                <ul class="list-unstyled text-start">
                    <li><i class="fas fa-check text-success me-2"></i>Todos los movimientos</li>
                    <li><i class="fas fa-check text-success me-2"></i>Información completa</li>
                    <li><i class="fas fa-check text-success me-2"></i>Formato expandido</li>
                    <li><i class="fas fa-check text-success me-2"></i>Auditoría completa</li>
                </ul>
                <input type="radio" name="export_type" value="detallado" style="display: none;" id="radio-detallado">
            </div>
        </div>
    </div>

    <!-- Vista Previa de Datos (si hay datos) -->
    @if(($datos ?? collect())->count() > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-eye"></i> Vista Previa (Primeros 10 registros)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="preview-table">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Nombre</th>
                                    <th class="text-end">Debe</th>
                                    <th class="text-end">Haber</th>
                                    <th class="text-end">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($datos->toArray(), 0, 10) as $dato)
                                <tr>
                                    <td>{{ $dato['cuenta_contable'] ?? '' }}</td>
                                    <td>{{ $dato['cuenta_nombre'] ?? '' }}</td>
                                    <td class="text-end text-success">
                                        S/ {{ number_format($dato['total_debe'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-end text-danger">
                                        S/ {{ number_format($dato['total_haber'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-end">
                                        S/ {{ number_format(($dato['total_debe'] ?? 0) - ($dato['total_haber'] ?? 0), 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($datos->count() > 10)
                        <div class="text-center mt-3">
                            <small class="text-muted">Mostrando 10 de {{ $datos->count() }} registros totales</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

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

    <!-- Información Adicional -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border-left-primary shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Formato de Archivo
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">CSV (Excel Compatible)</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-csv fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Codificación
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">UTF-8</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-language fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-info shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Estándar SUNAT
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">PCGE Compatible</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-certificate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
}

function exportData() {
    if (!selectedExportType) {
        alert('Por favor selecciona un tipo de exportación');
        return;
    }
    
    const form = document.getElementById('exportForm');
    const exportTypeInput = document.createElement('input');
    exportTypeInput.type = 'hidden';
    exportTypeInput.name = 'tipo';
    exportTypeInput.value = selectedExportType;
    form.appendChild(exportTypeInput);
    
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
    }, 3000);
}

// Establecer fechas por defecto (mes actual)
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    const fechaInicioInput = document.querySelector('input[name="fecha_inicio"]');
    const fechaFinInput = document.querySelector('input[name="fecha_fin"]');
    
    if (!fechaInicioInput.value) {
        fechaInicioInput.value = firstDay.toISOString().split('T')[0];
    }
    
    if (!fechaFinInput.value) {
        fechaFinInput.value = today.toISOString().split('T')[0];
    }
});
</script>
@endsection