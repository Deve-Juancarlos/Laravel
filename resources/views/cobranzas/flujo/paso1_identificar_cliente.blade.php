@extends('layouts.app')

@section('title', 'Registrar Cobranza - Paso 1')
@section('page-title', 'Asistente de Registro de Cobranzas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">Cobranzas</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paso 1: Identificar Cliente</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto"> 
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-user-check me-2 text-primary"></i>
                    Paso 1: Identificar al Cliente
                </h5>
            </div>

            {{-- 
              ¡CORRECCIÓN IMPORTANTE!
              El 'action' debe apuntar a la ruta que PROCESA el paso 1 ('handlePaso1'),
              no a la que MUESTRA el paso 2.
            --}}
            <form action="{{ route('contador.flujo.cobranzas.handlePaso1') }}" method="POST" id="formPaso1">
                @csrf

                <div class="card-body">
                    <p class="text-muted">
                        Busca al cliente por su RUC o Razón Social para iniciar el registro del pago.
                    </p>

                    {{-- Este input de búsqueda llama al AJAX --}}
                    <div class="mb-3">
                        <label for="search_cliente" class="form-label fw-bold">Buscar Cliente:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="search_cliente" 
                                   placeholder="Escribe el RUC o Razón Social..."
                                   autocomplete="off">
                        </div>
                        
                        <div id="search_results_container" 
                             class="list-group mt-1" 
                             style="display:none; position: absolute; z-index: 100; width: 95%;">
                            {{-- Los resultados de AJAX se cargan aquí --}}
                        </div>
                    </div>

                    <hr>

                    {{-- El resumen se muestra aquí cuando se hace clic --}}
                    <div id="selected_cliente_summary" class="card bg-light" style="display:none;">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Cliente Seleccionado:</h6>
                            <h4 class="mb-1" id="selected_cliente_nombre">--</h4>
                            <p class="text-muted mb-2" id="selected_cliente_ruc">--</p>
                            
                            <div class="alert alert-danger p-2">
                                <h5 class="text-danger mb-0">
                                    Deuda Total Pendiente:
                                    <strong id="selected_cliente_deuda">S/ 0.00</strong>
                                </h5>
                            </div>
                        </div>
                    </div>

                    {{-- Este es el valor que se envía en el formulario POST --}}
                    <input type="hidden" name="cliente_id" id="cliente_id" value="">

                </div>
                
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary" id="btn_siguiente" disabled>
                        Siguiente <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- ESTE ES EL SCRIPT CORREGIDO Y COMPLETO --}}
<script>
$(document).ready(function() {
    
    // 1. Configuración de la Búsqueda AJAX
    $('#search_cliente').on('keyup', function() {
        let query = $(this).val();
        let resultsContainer = $('#search_results_container');

        if (query.length < 3) { // El controlador requiere 3
            resultsContainer.empty().hide();
            $('#selected_cliente_summary').hide();
            $('#cliente_id').val('');
            $('#btn_siguiente').prop('disabled', true);
            return;
        }

        resultsContainer.html('<a href="#" class="list-group-item list-group-item-action text-muted">Buscando...</a>').show();

        $.ajax({
            url: "{{ route('contador.api.clientes.search') }}", // Esta ruta apunta a ClientesController@search
            type: 'GET',
            data: { 'query': query },
            success: function(response) { // La respuesta es un objeto { clientes: [], fuente: '...' }
                resultsContainer.empty();
                
                let clientes = response.clientes; // Extraemos el array de clientes
                let fuente = response.fuente;

                if (clientes.length > 0) {
                    $.each(clientes, function(index, cliente) {
                        
                        let esNuevo = (fuente === 'reniec');
                        let idCliente, nombreCliente, rucCliente, deudaCliente;

                        if (esNuevo) {
                            // Datos de RENIEC/SUNAT
                            idCliente = 'NUEVO-' + cliente.numero_documento;
                            nombreCliente = (cliente.razon_social || cliente.full_name) + " (RENIEC/SUNAT)";
                            rucCliente = cliente.numero_documento;
                            deudaCliente = 0;
                        } else {
                            // Datos locales (de tu BD)
                            idCliente = cliente.Codclie;
                            nombreCliente = cliente.Razon;
                            rucCliente = cliente.documento; // El servicio usa 'documento'
                            deudaCliente = cliente.deuda_total ?? 0; // Ahora sí viene del controlador
                        }

                        let itemClass = esNuevo ? 'list-group-item-success' : '';
                        let tag = esNuevo ? '<span class="badge bg-success ms-2">NUEVO</span>' : '';
                        let rucLabel = esNuevo ? 'Registrar RUC/DNI:' : 'RUC:';

                        resultsContainer.append(
                            `<a href="#" class="list-group-item list-group-item-action search-result-item ${itemClass}" 
                                data-id="${idCliente}" 
                                data-nombre="${nombreCliente}" 
                                data-ruc="${rucCliente}" 
                                data-deuda="${deudaCliente}"
                                data-es-nuevo="${esNuevo}">
                                <strong>${nombreCliente}</strong> ${tag}<br>
                                <small class="text-muted">${rucLabel} ${rucCliente}</small>
                            </a>`
                        );
                    });
                } else {
                    // Usamos el mensaje del controlador
                    resultsContainer.html(`<a href="#" class="list-group-item list-group-item-action text-danger">${response.mensaje || 'No se encontraron clientes.'}</a>`);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error en AJAX:", textStatus, errorThrown);
                resultsContainer.html('<a href="#" class="list-group-item list-group-item-action text-danger">Error 500. Revise ReniecService.php</a>');
            }
        });
    });

    // 2. Manejador de Clic (Este código ya está bien)
    $(document).on('click', '.search-result-item', function(e) {
        e.preventDefault(); 
        let id = $(this).data('id');
        let nombre = $(this).data('nombre');
        let ruc = $(this).data('ruc');
        let deuda = parseFloat($(this).data('deuda')).toFixed(2);
        let esNuevo = $(this).data('es-nuevo'); 

        $('#cliente_id').val(id);
        $('#selected_cliente_nombre').text(nombre);
        $('#selected_cliente_ruc').text('RUC/DNI: ' + ruc);
        
        if (esNuevo) {
            $('#selected_cliente_summary .alert')
                .removeClass('alert-danger')
                .addClass('alert-success')
                .html('<h5 class="text-success mb-0">Este cliente será registrado.</h5>');
        } else {
             $('#selected_cliente_summary .alert')
                .removeClass('alert-success')
                .addClass('alert-danger')
                .html(`<h5 class="text-danger mb-0">Deuda Total Pendiente: <strong id="selected_cliente_deuda">S/ ${deuda.replace(/\d(?=(\d{3})+\.)/g, '$&,')}</strong></h5>`);
        }
        
        $('#selected_cliente_summary').show();
        $('#search_results_container').empty().hide();
        $('#search_cliente').val(nombre); 
        $('#btn_siguiente').prop('disabled', false);
    });

    // 3. Validar el formulario (Este código ya está bien)
    $('#formPaso1').on('submit', function(e) {
        if ($('#cliente_id').val() == '') {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe seleccionar un cliente antes de continuar.'
            });
        }
    });

});
</script>
@endpush