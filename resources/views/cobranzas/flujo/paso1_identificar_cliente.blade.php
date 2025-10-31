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
              El 'action' debe apuntar a la ruta que PROCESA el paso 1,
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
{{-- Tu script JS original está perfecto. 
     Solo asegúrate de que la ruta 'contador.api.clientes.search' sea correcta. --}}
<script>
$(document).ready(function() {
    
    // 1. Configuración de la Búsqueda AJAX
    $('#search_cliente').on('keyup', function() {
        let query = $(this).val();
        let resultsContainer = $('#search_results_container');

        if (query.length < 3) {
            resultsContainer.empty().hide();
            $('#selected_cliente_summary').hide();
            $('#cliente_id').val('');
            $('#btn_siguiente').prop('disabled', true);
            return;
        }

        resultsContainer.html('<a href="#" class="list-group-item list-group-item-action text-muted">Buscando...</a>').show();

        $.ajax({
            // Esta ruta debe estar definida en web.php y limpiada con route:clear
            url: "{{ route('contador.api.clientes.search') }}", 
            type: 'GET',
            data: { 'query': query },
            success: function(data) {
                resultsContainer.empty();
                if (data.length > 0) {
                    $.each(data, function(index, cliente) {
                        resultsContainer.append(
                            `<a href="#" class="list-group-item list-group-item-action search-result-item" 
                                data-id="${cliente.id}" 
                                data-nombre="${cliente.razon_social}" 
                                data-ruc="${cliente.ruc}" 
                                data-deuda="${cliente.deuda_total}">
                                <strong>${cliente.razon_social}</strong><br>
                                <small class="text-muted">RUC: ${cliente.ruc}</small>
                            </a>`
                        );
                    });
                } else {
                    resultsContainer.html('<a href="#" class="list-group-item list-group-item-action text-danger">No se encontraron clientes.</a>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Añadido para depuración
                console.error("Error en AJAX:", textStatus, errorThrown);
                resultsContainer.html('<a href="#" class="list-group-item list-group-item-action text-danger">Error al buscar. Revise la consola.</a>');
            }
        });
    });

    // 2. Manejador de Clic en un Resultado
    $(document).on('click', '.search-result-item', function(e) {
        e.preventDefault(); 
        let id = $(this).data('id');
        let nombre = $(this).data('nombre');
        let ruc = $(this).data('ruc');
        let deuda = parseFloat($(this).data('deuda')).toFixed(2);

        $('#cliente_id').val(id);
        $('#selected_cliente_nombre').text(nombre);
        $('#selected_cliente_ruc').text('RUC: ' + ruc);
        $('#selected_cliente_deuda').text('S/ ' + deuda.replace(/\d(?=(\d{3})+\.)/g, '$&,')); 
        $('#selected_cliente_summary').show();
        $('#search_results_container').empty().hide();
        $('#search_cliente').val(nombre); 
        $('#btn_siguiente').prop('disabled', false);
    });

    // 3. Validar el formulario
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