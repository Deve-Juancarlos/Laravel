@use('Illuminate\Support\Str')
@extends('layouts.app')

@section('title', 'Crear Nuevo Cliente')
@section('page-title', 'Crear Nuevo Cliente')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('contador.clientes.index') }}">Clientes</a></li>
    <li class="breadcrumb-item active" aria-current="page">Crear Cliente</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <form action="{{ route('contador.clientes.store') }}" method="POST" id="formCrearCliente">
            @csrf
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title m-0">
                        <i class="fas fa-user-plus me-2"></i>Información del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    
                    <div class="card bg-light border mb-4">
                        <div class="card-body">
                            <h6 class="text-primary">Búsqueda Rápida (RENIEC/SUNAT)</h6>
                            <p class="text-muted small">Ingrese un DNI o RUC y presione "Buscar" para autocompletar el formulario.</p>
                            <div class="input-group">
                                <input type="text" class="form-control" id="inputDocumento" placeholder="Ingrese DNI (8 dígitos) o RUC (11 dígitos)">
                                <button class="btn btn-success" type="button" id="btnBuscarApi">
                                    <i class="fas fa-search me-1"></i> 
                                    <span id="btnBuscarText">Buscar</span>
                                    <span id="btnBuscarLoader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                                </button>
                            </div>
                            <div id="apiMensaje" class="form-text mt-2"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <label for="Razon" class="form-label fw-bold">Razón Social / Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="Razon" name="Razon" value="{{ old('Razon') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="Documento" class="form-label fw-bold">Documento (RUC/DNI) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="Documento" name="Documento" value="{{ old('Documento') }}" required>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="Direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="Direccion" name="Direccion" value="{{ old('Direccion') }}">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label for="Telefono1" class="form-label">Teléfono / Celular</label>
                            <input type="text" class="form-control" id="Telefono1" name="Telefono1" value="{{ old('Telefono1') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="Email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="Email" name="Email" value="{{ old('Email') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="Vendedor" class="form-label">Vendedor Asignado</label>
                            {{-- ¡CORREGIDO! Ahora usa la variable $vendedores --}}
                            <select class="form-select" id="Vendedor" name="Vendedor">
                                <option value="">(Sin Asignar)</option>
                                @foreach($vendedores as $vendedor)
                                    <option value="{{ $vendedor->Codemp }}" @selected(old('Vendedor') == $vendedor->Codemp)>
                                        {{ $vendedor->Nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="text-muted">Campos Adicionales</h6>
                    <div class="row mt-3">
                         <div class="col-md-4">
                            <label for="TipoClie" class="form-label">Tipo de Cliente</label>
                            <input type="text" class="form-control" id="TipoClie" name="TipoClie" value="{{ old('TipoClie', 1) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="Limite" class="form-label">Límite de Crédito (S/)</label>
                            <input type="number" step="0.01" class="form-control" id="Limite" name="Limite" value="{{ old('Limite', 0) }}">
                        </div>
                    </div>

                </div>
                
                <div class="card-footer text-end">
                    <a href="{{ route('contador.clientes.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar Cliente
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    
    $('#btnBuscarApi').on('click', function() {
        const documento = $('#inputDocumento').val().trim();
        if (documento.length !== 8 && documento.length !== 11) {
            $('#apiMensaje').html('<span class="text-danger">Por favor, ingrese un RUC (11 dígitos) o DNI (8 dígitos) válido.</span>');
            return;
        }

        const $btn = $(this);
        const $btnText = $('#btnBuscarText');
        const $btnLoader = $('#btnBuscarLoader');
        const $apiMensaje = $('#apiMensaje');

        $btn.prop('disabled', true);
        $btnText.hide();
        $btnLoader.show();
        $apiMensaje.html('<span class="text-muted">Consultando API...</span>');

        // Esta ruta la creamos en web.php
        const url = `{{ route('contador.clientes.api.consulta', ['documento' => 'DOCUMENTO_PLACEHOLDER']) }}`.replace('DOCUMENTO_PLACEHOLDER', documento);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Llenar el formulario
                    $('#Documento').val(data.numero_documento);
                    $('#Razon').val(data.razon_social);
                    $('#Direccion').val(data.address);
                    
                    $apiMensaje.html(`<span class="text-success"><strong>Éxito:</strong> Datos cargados de ${data.numero_documento.length === 11 ? 'SUNAT' : 'RENIEC'}.</span>`);
                } else {
                    // Si la API no lo encuentra (404) o ya existe (409)
                    $apiMensaje.html(`<span class="text-warning"><strong>Aviso:</strong> ${response.message}</span>`);
                    $('#Documento').val(documento);
                    $('#Razon').val('').focus(); // Limpiamos y ponemos foco
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Error 500 o problema de red
                $apiMensaje.html('<span class="text-danger"><strong>Error:</strong> No se pudo conectar con el servicio. Puede registrar manualmente.</span>');
                $('#Documento').val(documento);
            },
            complete: function() {
                // Habilitar botón y ocultar loader
                $btn.prop('disabled', false);
                $btnText.show();
                $btnLoader.hide();
            }
        });
    });

});
</script>
@endpush