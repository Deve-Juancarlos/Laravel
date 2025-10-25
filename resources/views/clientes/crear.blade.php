@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-plus text-primary"></i> Registrar Nuevo Cliente
        </h1>
        <div>
            <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Clientes
            </a>
        </div>
    </div>

    <!-- Formulario -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información del Cliente</h6>
                </div>
                <div class="card-body">
                    <form id="clienteForm" method="POST" action="{{ route('clientes.store') }}">
                        @csrf
                        
                        <!-- Tipo de Cliente -->
                        <div class="form-group">
                            <label>Tipo de Cliente *</label>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="tipoPersona" name="tipo" value="persona" class="custom-control-input" checked onchange="cambiarTipo()">
                                <label class="custom-control-label" for="tipoPersona">Persona Natural</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="tipoEmpresa" name="tipo" value="empresa" class="custom-control-input" onchange="cambiarTipo()">
                                <label class="custom-control-label" for="tipoEmpresa">Persona Jurídica</label>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Código del Cliente -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Código del Cliente</label>
                                    <input type="text" class="form-control" name="codigo" 
                                           value="CLI-{{ date('Y') }}-{{ str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) }}" readonly>
                                </div>
                            </div>

                            <!-- Categoría -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Categoría</label>
                                    <select class="form-control" name="categoria" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="a">Categoría A - VIP</option>
                                        <option value="b">Categoría B</option>
                                        <option value="c">Categoría C</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Estado</label>
                                    <select class="form-control" name="estado" required>
                                        <option value="activo" selected>Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                        <option value="suspendido">Suspendido</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Información Personal -->
                        <div id="infoPersonal">
                            <h6 class="text-primary">Información Personal</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Apellidos *</label>
                                        <input type="text" class="form-control" name="apellidos" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombres *</label>
                                        <input type="text" class="form-control" name="nombres" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>DNI *</label>
                                        <input type="text" class="form-control" name="dni" 
                                               pattern="[0-9]{8}" maxlength="8" required>
                                        <small class="form-text text-muted">8 dígitos</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fecha de Nacimiento</label>
                                        <input type="date" class="form-control" name="fecha_nacimiento">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Género</label>
                                        <select class="form-control" name="genero">
                                            <option value="">Seleccionar...</option>
                                            <option value="masculino">Masculino</option>
                                            <option value="femenino">Femenino</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Estado Civil</label>
                                        <select class="form-control" name="estado_civil">
                                            <option value="">Seleccionar...</option>
                                            <option value="soltero">Soltero(a)</option>
                                            <option value="casado">Casado(a)</option>
                                            <option value="divorciado">Divorciado(a)</option>
                                            <option value="viudo">Viudo(a)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información Empresarial -->
                        <div id="infoEmpresa" style="display: none;">
                            <h6 class="text-primary">Información Empresarial</h6>
                            <div class="form-group">
                                <label>Razón Social *</label>
                                <input type="text" class="form-control" name="razon_social">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>RUC *</label>
                                        <input type="text" class="form-control" name="ruc" 
                                               pattern="[0-9]{11}" maxlength="11">
                                        <small class="form-text text-muted">11 dígitos</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Giro del Negocio</label>
                                        <select class="form-control" name="giro_negocio">
                                            <option value="">Seleccionar...</option>
                                            <option value="farmacia">Farmacia</option>
                                            <option value="hospital">Hospital</option>
                                            <option value="clinica">Clínica</option>
                                            <option value="laboratorio">Laboratorio</option>
                                            <option value="medico">Médico Independiente</option>
                                            <option value="otros">Otros</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dirección -->
                        <h6 class="text-primary mt-4">Dirección</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Departamento</label>
                                    <select class="form-control" name="departamento" id="departamento" onchange="cargarProvincias()">
                                        <option value="">Seleccionar...</option>
                                        <option value="lima">Lima</option>
                                        <option value="arequipa">Arequipa</option>
                                        <option value="cusco">Cusco</option>
                                        <option value="la-libertad">La Libertad</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Provincia</label>
                                    <select class="form-control" name="provincia" id="provincia" onchange="cargarDistritos()">
                                        <option value="">Seleccionar...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Distrito</label>
                                    <select class="form-control" name="distrito" id="distrito">
                                        <option value="">Seleccionar...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Dirección Completa *</label>
                            <input type="text" class="form-control" name="direccion" required>
                        </div>

                        <!-- Contacto -->
                        <h6 class="text-primary mt-4">Información de Contacto</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Teléfono Principal *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">+51</span>
                                        </div>
                                        <input type="text" class="form-control" name="telefono_principal" 
                                               placeholder="999 888 777" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Teléfono Secundario</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">+51</span>
                                        </div>
                                        <input type="text" class="form-control" name="telefono_secundario" 
                                               placeholder="988 777 666">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email Principal *</label>
                                    <input type="email" class="form-control" name="email_principal" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email Secundario</label>
                                    <input type="email" class="form-control" name="email_secundario">
                                </div>
                            </div>
                        </div>

                        <!-- Contacto Comercial -->
                        <h6 class="text-primary mt-4">Contacto Comercial</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre del Contacto</label>
                                    <input type="text" class="form-control" name="contacto_nombre">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cargo</label>
                                    <input type="text" class="form-control" name="contacto_cargo">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Teléfono de Contacto</label>
                                    <input type="text" class="form-control" name="contacto_telefono">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email de Contacto</label>
                                    <input type="email" class="form-control" name="contacto_email">
                                </div>
                            </div>
                        </div>

                        <!-- Términos Comerciales -->
                        <h6 class="text-primary mt-4">Términos Comerciales</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Forma de Pago</label>
                                    <select class="form-control" name="forma_pago">
                                        <option value="contado">Contado</option>
                                        <option value="credito">Crédito</option>
                                        <option value="mixto">Mixto</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Días de Crédito</label>
                                    <input type="number" class="form-control" name="dias_credito" 
                                           min="0" max="365" value="30">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Límite de Crédito</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">S/</span>
                                        </div>
                                        <input type="number" class="form-control" name="limite_credito" 
                                               min="0" step="0.01" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea class="form-control" name="observaciones" rows="3" 
                                      placeholder="Información adicional del cliente..."></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="form-group">
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('clientes.index') }}" class="btn btn-secondary mr-2">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="button" class="btn btn-outline-primary mr-2" onclick="guardarBorrador()">
                                    <i class="fas fa-save"></i> Guardar Borrador
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Registrar Cliente
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-md-4">
            <!-- Información de Ayuda -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información</h6>
                </div>
                <div class="card-body">
                    <h6 class="text-warning">Tipos de Cliente</h6>
                    <ul class="list-unstyled">
                        <li><strong>Persona Natural:</strong> Profesionales independientes</li>
                        <li><strong>Persona Jurídica:</strong> Empresas y organizaciones</li>
                    </ul>
                    
                    <h6 class="text-warning mt-3">Categorías</h6>
                    <ul class="list-unstyled">
                        <li><span class="badge badge-warning">VIP</span> Descuentos especiales</li>
                        <li><span class="badge badge-success">B</span> Cliente regular</li>
                        <li><span class="badge badge-secondary">C</span> Cliente básico</li>
                    </ul>

                    <h6 class="text-warning mt-3">Términos</h6>
                    <p class="small text-muted">
                        Los términos comerciales pueden modificarse posteriormente desde la gestión del cliente.
                    </p>
                </div>
            </div>

            <!-- Validaciones en Tiempo Real -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Validaciones</h6>
                </div>
                <div class="card-body">
                    <div id="validaciones">
                        <div class="alert alert-success">
                            <i class="fas fa-check"></i> Formulario válido
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function cambiarTipo() {
    const tipoPersona = document.getElementById('tipoPersona').checked;
    const infoPersonal = document.getElementById('infoPersonal');
    const infoEmpresa = document.getElementById('infoEmpresa');
    
    if (tipoPersona) {
        infoPersonal.style.display = 'block';
        infoEmpresa.style.display = 'none';
        document.querySelector('input[name="dni"]').required = true;
        document.querySelector('input[name="ruc"]').required = false;
    } else {
        infoPersonal.style.display = 'none';
        infoEmpresa.style.display = 'block';
        document.querySelector('input[name="dni"]').required = false;
        document.querySelector('input[name="ruc"]').required = true;
    }
}

function cargarProvincias() {
    const departamento = document.getElementById('departamento').value;
    const provinciaSelect = document.getElementById('provincia');
    
    // Limpiar provincias
    provinciaSelect.innerHTML = '<option value="">Seleccionar...</option>';
    
    if (departamento) {
        const provincias = {
            'lima': ['Lima', 'Callao', 'Cañete', 'Huarochirí'],
            'arequipa': ['Arequipa', 'Camaná', 'Caravelí', 'Castilla'],
            'cusco': ['Cusco', 'Acomayo', 'Anta', 'Calca'],
            'la-libertad': ['Trujillo', 'Ascope', 'Bolívar', 'Chepén']
        };
        
        provincias[departamento].forEach(provincia => {
            const option = document.createElement('option');
            option.value = provincia.toLowerCase().replace(' ', '-');
            option.textContent = provincia;
            provinciaSelect.appendChild(option);
        });
    }
}

function cargarDistritos() {
    const provincia = document.getElementById('provincia').value;
    const distritoSelect = document.getElementById('distrito');
    
    // Limpiar distritos
    distritoSelect.innerHTML = '<option value="">Seleccionar...</option>';
    
    if (provincia) {
        // Ejemplo de distritos para Lima
        if (provincia === 'lima') {
            const distritos = ['Lima', 'Miraflores', 'San Isidro', 'Barranco', 'Surco'];
            distritos.forEach(distrito => {
                const option = document.createElement('option');
                option.value = distrito.toLowerCase().replace(' ', '-');
                option.textContent = distrito;
                distritoSelect.appendChild(option);
            });
        }
    }
}

function validarFormulario() {
    const validaciones = document.getElementById('validaciones');
    let errores = [];
    let avisos = [];
    
    // Validaciones básicas
    const nombre = document.querySelector('input[name="nombres"]').value;
    const apellido = document.querySelector('input[name="apellidos"]').value;
    const telefono = document.querySelector('input[name="telefono_principal"]').value;
    const email = document.querySelector('input[name="email_principal"]').value;
    const direccion = document.querySelector('input[name="direccion"]').value;
    
    if (!nombre.trim()) errores.push('Los nombres son obligatorios');
    if (!apellido.trim()) errores.push('Los apellidos son obligatorios');
    if (!telefono.trim()) errores.push('El teléfono principal es obligatorio');
    if (!email.trim()) errores.push('El email principal es obligatorio');
    if (!direccion.trim()) errores.push('La dirección es obligatoria');
    
    // Validar formato de email
    if (email && !/\S+@\S+\.\S+/.test(email)) {
        errores.push('El email no tiene un formato válido');
    }
    
    // Validar DNI si es persona natural
    if (document.getElementById('tipoPersona').checked) {
        const dni = document.querySelector('input[name="dni"]').value;
        if (dni && dni.length !== 8) {
            errores.push('El DNI debe tener 8 dígitos');
        }
    }
    
    // Validar RUC si es empresa
    if (document.getElementById('tipoEmpresa').checked) {
        const ruc = document.querySelector('input[name="ruc"]').value;
        if (ruc && ruc.length !== 11) {
            errores.push('El RUC debe tener 11 dígitos');
        }
    }
    
    // Generar HTML de validaciones
    let html = '';
    
    if (errores.length === 0) {
        html = '<div class="alert alert-success"><i class="fas fa-check"></i> Formulario válido</div>';
    } else {
        html = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i><strong>Errores:</strong><ul>';
        errores.forEach(error => {
            html += `<li>${error}</li>`;
        });
        html += '</ul></div>';
    }
    
    validaciones.innerHTML = html;
    
    return errores.length === 0;
}

function guardarBorrador() {
    const form = document.getElementById('clienteForm');
    const formData = new FormData(form);
    formData.append('guardar_borrador', '1');
    
    // Validar antes de guardar
    if (!validarFormulario()) {
        Swal.fire({
            title: 'Errores en el formulario',
            text: 'Por favor, corrija los errores antes de guardar.',
            icon: 'error'
        });
        return;
    }
    
    // Simular guardado
    Swal.fire({
        title: 'Guardando...',
        text: 'Guardando borrador del cliente',
        timer: 1500,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        Swal.fire({
            title: '¡Borrador Guardado!',
            text: 'El borrador del cliente ha sido guardado correctamente.',
            icon: 'success'
        });
    });
}

function procesarFormulario(event) {
    event.preventDefault();
    
    if (!validarFormulario()) {
        Swal.fire({
            title: 'Errores en el formulario',
            text: 'Por favor, corrija los errores antes de registrar.',
            icon: 'error'
        });
        return false;
    }
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Mostrar confirmación
    Swal.fire({
        title: '¿Registrar cliente?',
        text: '¿Está seguro de que desea registrar este cliente?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Simular registro
            Swal.fire({
                title: 'Registrando...',
                text: 'Procesando información del cliente',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                Swal.fire({
                    title: '¡Cliente Registrado!',
                    text: 'El cliente ha sido registrado exitosamente.',
                    icon: 'success'
                }).then(() => {
                    window.location.href = '{{ route("clientes.index") }}';
                });
            });
        }
    });
    
    return false;
}

// Event listeners
document.getElementById('clienteForm').addEventListener('submit', procesarFormulario);

// Validación en tiempo real
document.addEventListener('input', function(e) {
    if (e.target.matches('input[name="dni"], input[name="ruc"], input[name="email_principal"]')) {
        setTimeout(validarFormulario, 100);
    }
});

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    cambiarTipo(); // Establecer estado inicial
    validarFormulario(); // Validación inicial
});
</script>
@endsection