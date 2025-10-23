<!-- Modal de Búsqueda de Cliente por DNI/RUC -->
<div class="modal fade" id="modalBusquedaCliente" tabindex="-1" aria-labelledby="modalBusquedaClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalBusquedaClienteLabel">
                    <i class="fas fa-search"></i> Búsqueda de Cliente por DNI/RUC
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Formulario de búsqueda -->
                <form id="formBusquedaCliente">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Documento</label>
                            <select name="tipo_documento" class="form-select" id="tipoDocumento">
                                <option value="DNI">DNI (Personas)</option>
                                <option value="RUC">RUC (Empresas)</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Número de Documento</label>
                            <input type="text" 
                                   name="numero_documento" 
                                   class="form-control" 
                                   id="numeroDocumento"
                                   placeholder="Ingrese DNI (8 dígitos) o RUC (11 dígitos)">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100" id="btnBuscar">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Indicador de carga -->
                <div id="loading" class="text-center py-3" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Consultando...</span>
                    </div>
                    <p class="mt-2">Consultando en RENIEC/SUNAT...</p>
                </div>

                <!-- Resultados de RENIEC -->
                <div id="resultadoReniec" class="mt-3" style="display: none;">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Información desde RENIEC/SUNAT</h6>
                        <div id="datosReniec"></div>
                    </div>
                </div>

                <!-- Resultados del Sistema -->
                <div id="resultadoSistema" class="mt-3" style="display: none;">
                    <div class="alert alert-success">
                        <h6><i class="fas fa-database"></i> Cliente encontrado en el sistema</h6>
                        <div id="datosSistema"></div>
                    </div>
                </div>

                <!-- Formulario de creación de cliente -->
                <div id="formularioCrear" class="mt-3" style="display: none;">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6><i class="fas fa-user-plus"></i> Crear Nuevo Cliente</h6>
                        </div>
                        <div class="card-body">
                            <form id="formCrearCliente">
                                <input type="hidden" name="numero_documento" id="crearNumeroDoc">
                                <input type="hidden" name="tipo_documento" id="crearTipoDoc">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Nombres/Razón Social</label>
                                        <input type="text" 
                                               name="nombres" 
                                               class="form-control" 
                                               id="crearNombres" 
                                               required>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Apellido Paterno</label>
                                        <input type="text" 
                                               name="apellido_paterno" 
                                               class="form-control" 
                                               id="crearApellidoPaterno">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Apellido Materno</label>
                                        <input type="text" 
                                               name="apellido_materno" 
                                               class="form-control" 
                                               id="crearApellidoMaterno">
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Dirección</label>
                                        <input type="text" 
                                               name="direccion" 
                                               class="form-control" 
                                               id="crearDireccion">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Teléfono</label>
                                        <input type="text" 
                                               name="telefono" 
                                               class="form-control" 
                                               id="crearTelefono">
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Email</label>
                                        <input type="email" 
                                               name="email" 
                                               class="form-control" 
                                               id="crearEmail">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Tipo Cliente</label>
                                        <select name="tipo_cliente" class="form-select" id="crearTipoCliente">
                                            <option value="1">Minorista</option>
                                            <option value="2">Mayorista</option>
                                            <option value="3">Distribuidor</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Límite de Crédito</label>
                                        <input type="number" 
                                               name="limite_credito" 
                                               class="form-control" 
                                               id="crearLimite" 
                                               value="0" 
                                               min="0" 
                                               step="0.01">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Mensajes de error -->
                <div id="mensajeError" class="mt-3" style="display: none;">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span id="textoError"></span>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="btnCrearCliente" style="display: none;">
                    <i class="fas fa-save"></i> Crear Cliente
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Variables globales
    let datosReniec = null;
    let clienteSistema = null;

    // Event listeners
    $('#formBusquedaCliente').on('submit', function(e) {
        e.preventDefault();
        buscarCliente();
    });

    $('#tipoDocumento').on('change', function() {
        validarDocumento();
    });

    $('#numeroDocumento').on('input', function() {
        validarDocumento();
    });

    $('#btnCrearCliente').on('click', function() {
        crearCliente();
    });

    // Función principal de búsqueda
    function buscarCliente() {
        const tipoDoc = $('#tipoDocumento').val();
        const numeroDoc = $('#numeroDocumento').val().trim();
        
        if (!numeroDoc) {
            mostrarError('Ingrese el número de documento');
            return;
        }

        // Mostrar loading
        $('#loading').show();
        $('#resultadoReniec, #resultadoSistema, #formularioCrear, #mensajeError').hide();

        // Determinar endpoint según tipo
        const endpoint = tipoDoc === 'DNI' ? '/contabilidad/reniec/consultar-dni' : '/contabilidad/reniec/consultar-ruc';
        
        $.ajax({
            url: endpoint,
            method: 'POST',
            data: {
                numero_documento: numeroDoc,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#loading').hide();
                
                if (response.success) {
                    datosReniec = response.data || response.cliente || response.empresa;
                    mostrarDatosReniec(datosReniec, tipoDoc);
                    
                    // Buscar también en sistema local
                    buscarEnSistemaLocal(numeroDoc);
                } else {
                    mostrarError(response.message || 'No se encontró información');
                }
            },
            error: function(xhr) {
                $('#loading').hide();
                let mensaje = 'Error al consultar información';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
                
                mostrarError(mensaje);
            }
        });
    }

    // Buscar en sistema local
    function buscarEnSistemaLocal(numeroDoc) {
        $.ajax({
            url: '/contabilidad/reniec/buscar-cliente-sistema',
            method: 'POST',
            data: {
                numero_documento: numeroDoc,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.cliente_sistema) {
                    clienteSistema = response.cliente_sistema;
                    mostrarDatosSistema(clienteSistema);
                }
            }
        });
    }

    // Mostrar datos de RENIEC
    function mostrarDatosReniec(data, tipo) {
        let html = '';
        
        if (tipo === 'DNI') {
            html = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nombre Completo:</strong><br>
                        <span class="text-success">${data.nombre_completo || (data.nombres + ' ' + data.apellido_paterno + ' ' + data.apellido_materno)}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Dirección:</strong><br>
                        <span>${data.direccion || 'No especificada'}</span>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <strong>Departamento:</strong><br>${data.departamento || '-'}
                    </div>
                    <div class="col-md-4">
                        <strong>Provincia:</strong><br>${data.provincia || '-'}
                    </div>
                    <div class="col-md-4">
                        <strong>Distrito:</strong><br>${data.distrito || '-'}
                    </div>
                </div>
            `;
        } else { // RUC
            html = `
                <div class="row">
                    <div class="col-md-8">
                        <strong>Razón Social:</strong><br>
                        <span class="text-success">${data.razon_social || 'N/A'}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Estado:</strong><br>
                        <span class="badge bg-${data.estado === 'ACTIVO' ? 'success' : 'warning'}">${data.estado || 'N/A'}</span>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Dirección:</strong><br>${data.direccion || 'No especificada'}
                    </div>
                    <div class="col-md-6">
                        <strong>Condición:</strong><br>${data.condicion || 'N/A'}
                    </div>
                </div>
            `;
        }
        
        $('#datosReniec').html(html);
        $('#resultadoReniec').show();
        
        // Llenar formulario de creación
        llenarFormularioCreacion(data, tipo);
    }

    // Mostrar datos del sistema
    function mostrarDatosSistema(cliente) {
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <strong>Cliente:</strong><br>
                    <span class="text-success">${cliente.Razon}</span>
                </div>
                <div class="col-md-6">
                    <strong>Código:</strong><br>
                    <span class="badge bg-info">${cliente.Codclie}</span>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-8">
                    <strong>Dirección:</strong><br>${cliente.Direccion || 'No especificada'}
                </div>
                <div class="col-md-4">
                    <strong>Teléfono:</strong><br>${cliente.Telefono1 || 'No especificado'}
                </div>
            </div>
        `;
        
        $('#datosSistema').html(html);
        $('#resultadoSistema').show();
    }

    // Llenar formulario de creación
    function llenarFormularioCreacion(data, tipo) {
        if (tipo === 'DNI') {
            $('#crearNombres').val(data.nombres || '');
            $('#crearApellidoPaterno').val(data.apellido_paterno || '');
            $('#crearApellidoMaterno').val(data.apellido_materno || '');
        } else {
            $('#crearNombres').val(data.razon_social || '');
            $('#crearApellidoPaterno').val('');
            $('#crearApellidoMaterno').val('');
        }
        
        $('#crearDireccion').val(data.direccion || '');
        $('#crearNumeroDoc').val($('#numeroDocumento').val());
        $('#crearTipoDoc').val(tipo);
        
        $('#formularioCrear').show();
        $('#btnCrearCliente').show();
    }

    // Crear cliente
    function crearCliente() {
        const formData = $('#formCrearCliente').serialize();
        
        $.ajax({
            url: '/contabilidad/reniec/crear-cliente-automatico',
            method: 'POST',
            data: formData + '&_token=' + $('meta[name="csrf-token"]').attr('content'),
            success: function(response) {
                if (response.success) {
                    alert('Cliente creado exitosamente!');
                    $('#modalBusquedaCliente').modal('hide');
                    
                    // Si hay callback, ejecutarlo
                    if (typeof window.onClienteCreado === 'function') {
                        window.onClienteCreado(response.cliente);
                    }
                } else {
                    mostrarError(response.message || 'Error al crear cliente');
                }
            },
            error: function(xhr) {
                let mensaje = 'Error al crear cliente';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
                mostrarError(mensaje);
            }
        });
    }

    // Validar documento
    function validarDocumento() {
        const tipo = $('#tipoDocumento').val();
        const numero = $('#numeroDocumento').val();
        
        const maxLength = tipo === 'DNI' ? 8 : 11;
        $('#numeroDocumento').attr('maxlength', maxLength);
        
        if (numero.length === maxLength) {
            // Validar con regex básica
            const regex = tipo === 'DNI' ? /^\d{8}$/ : /^\d{11}$/;
            if (regex.test(numero)) {
                $('#btnBuscar').prop('disabled', false);
                $('#numeroDocumento').removeClass('is-invalid').addClass('is-valid');
            } else {
                $('#btnBuscar').prop('disabled', true);
                $('#numeroDocumento').removeClass('is-valid').addClass('is-invalid');
            }
        } else {
            $('#btnBuscar').prop('disabled', true);
            $('#numeroDocumento').removeClass('is-valid is-invalid');
        }
    }

    // Mostrar error
    function mostrarError(mensaje) {
        $('#textoError').text(mensaje);
        $('#mensajeError').show();
        setTimeout(function() {
            $('#mensajeError').hide();
        }, 5000);
    }

    // Inicializar validación
    validarDocumento();
});

// Función para abrir modal desde cualquier lugar
function abrirBusquedaCliente(callback) {
    window.onClienteCreado = callback;
    $('#modalBusquedaCliente').modal('show');
    $('#formBusquedaCliente')[0].reset();
    $('#resultadoReniec, #resultadoSistema, #formularioCrear, #mensajeError').hide();
}
</script>