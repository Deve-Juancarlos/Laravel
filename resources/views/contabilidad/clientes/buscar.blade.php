@extends('layouts.contador')

@section('title', 'Buscar Cliente en RENIEC')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">🔍 Búsqueda de Cliente</h2>
                    <p class="text-muted mb-0">Buscar y registrar clientes desde RENIEC</p>
                </div>
                <div>
                    <a href="{{ route('contabilidad.clientes.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-list me-1"></i>Lista de Clientes
                    </a>
                    <a href="{{ route('contabilidad.clientes.create') }}" class="btn btn-success">
                        <i class="fas fa-user-plus me-1"></i>Registro Manual
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Panel de búsqueda -->
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0 text-center">
                        <i class="fas fa-search me-2"></i>Consultar DNI en RENIEC
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Campo de búsqueda -->
                    <div class="text-center mb-4">
                        <label for="dni" class="form-label h5">
                            <i class="fas fa-id-card me-2"></i>Ingrese DNI
                        </label>
                        <div class="input-group input-group-lg">
                            <input type="text" 
                                   id="dni" 
                                   class="form-control text-center" 
                                   maxlength="8" 
                                   placeholder="12345678"
                                   value="{{ request('dni') }}"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            <button id="btn-consultar" class="btn btn-success" type="button" onclick="consultarDNI()">
                                <i class="fas fa-search me-1"></i>Consultar
                            </button>
                        </div>
                        <small class="form-text text-muted">
                            Escribe el DNI y presiona ENTER o haz clic en "Consultar"
                        </small>
                    </div>

                    <!-- Estado de carga -->
                    <div id="loading" class="text-center p-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 h5">Consultando RENIEC...</p>
                        <p class="text-muted">Esto puede tomar unos segundos</p>
                    </div>

                    <!-- Resultados -->
                    <div id="resultado" style="display: none;">
                        <div class="alert alert-success alert-dismissible fade show">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-user-check fa-2x text-success me-3"></i>
                                <div>
                                    <h5 class="mb-1">Cliente encontrado</h5>
                                    <p class="mb-0">Los datos son oficiales de RENIEC</p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nombre completo:</strong><br>
                                       <span id="nombre-completo" class="h6"></span></p>
                                    <p><strong>DNI:</strong><br>
                                       <span id="dni-resultado" class="text-primary h6"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Edad:</strong><br>
                                       <span id="edad"></span> años</p>
                                    <p><strong>Mayor de edad:</strong><br>
                                       <span id="mayor-edad"></span></p>
                                </div>
                            </div>
                            
                            <div class="border-top pt-3">
                                <p><strong>Dirección completa:</strong><br>
                                   <span id="direccion"></span></p>
                                <p><strong>Ubicación:</strong><br>
                                   <span id="ubigeo"></span> - <span id="departamento"></span></p>
                                <p><strong>Provincia y Distrito:</strong><br>
                                   <span id="provincia"></span> - <span id="distrito"></span></p>
                            </div>

                            <div class="mt-4">
                                <button class="btn btn-success me-2" onclick="usarCliente()">
                                    <i class="fas fa-check-circle me-1"></i>Usar este cliente
                                </button>
                                <button class="btn btn-outline-primary me-2" onclick="consultarOTRO()">
                                    <i class="fas fa-search me-1"></i>Buscar otro
                                </button>
                                <button class="btn btn-info" onclick="verificarExistencia()">
                                    <i class="fas fa-database me-1"></i>Verificar en BD
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Error -->
                    <div id="error" class="alert alert-danger" style="display: none;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <h5 class="mb-1">No se encontró el DNI</h5>
                                <p class="mb-0" id="mensaje-error"></p>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button class="btn btn-warning me-2" onclick="registrarManual()">
                                <i class="fas fa-user-plus me-1"></i>Registrar Manualmente
                            </button>
                            <button class="btn btn-outline-secondary" onclick="consultarOTRO()">
                                <i class="fas fa-redo me-1"></i>Intentar otro DNI
                            </button>
                        </div>
                    </div>

                    <!-- Ya existe en BD -->
                    <div id="existe-bd" class="alert alert-info" style="display: none;">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-info-circle fa-2x text-info me-3"></i>
                            <div>
                                <h5 class="mb-1">Cliente ya registrado</h5>
                                <p class="mb-0">Este DNI ya existe en nuestra base de datos</p>
                            </div>
                        </div>
                        
                        <p><strong>Cliente existente:</strong> <span id="cliente-existente"></span></p>

                        <div class="mt-3">
                            <button class="btn btn-primary me-2" onclick="usarClienteExistente()">
                                <i class="fas fa-check me-1"></i>Usar Cliente Existente
                            </button>
                            <button class="btn btn-outline-secondary" onclick="consultarOTRO()">
                                <i class="fas fa-search me-1"></i>Buscar otro
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de consejos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <i class="fas fa-shield-alt fa-3x text-success mb-2"></i>
                            <h6>Datos Oficiales</h6>
                            <small>Información directa de RENIEC</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <i class="fas fa-clock fa-3x text-info mb-2"></i>
                            <h6>Búsqueda Rápida</h6>
                            <small>Resultados en segundos</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <i class="fas fa-user-check fa-3x text-primary mb-2"></i>
                            <h6>Verificación</h6>
                            <small>Validación automática</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <i class="fas fa-database fa-3x text-warning mb-2"></i>
                            <h6>Registro Único</h6>
                            <small>Sin duplicados</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let datosEncontrados = null;
let clienteExistente = null;

document.addEventListener('DOMContentLoaded', function() {
    // Auto-consultar si viene un DNI en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const dniFromUrl = urlParams.get('dni');
    
    if (dniFromUrl && dniFromUrl.length === 8) {
        document.getElementById('dni').value = dniFromUrl;
        consultarDNI();
    }
});

function consultarDNI() {
    const dni = document.getElementById('dni').value.trim();
    
    if (dni.length !== 8) {
        mostrarError('El DNI debe tener exactamente 8 dígitos.');
        return;
    }

    mostrarCargando();
    ocultarTodos();

    // Llamar a la API RENIEC
    fetch('{{ route("contabilidad.clientes.consultar-reniec") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ dni: dni })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.existe) {
                // Cliente ya existe en la base de datos
                clienteExistente = data.cliente;
                mostrarClienteExistente();
            } else {
                // Cliente no existe, mostrar datos de RENIEC
                datosEncontrados = data.reniec_data;
                mostrarResultados();
            }
        } else {
            mostrarError(data.message || 'DNI no encontrado en RENIEC');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error de conexión. Intente nuevamente.');
    })
    .finally(() => {
        ocultarCargando();
    });
}

function mostrarResultados() {
    if (!datosEncontrados) return;
    
    document.getElementById('nombre-completo').textContent = datosEncontrados.nombre_completo || 'NO DISPONIBLE';
    document.getElementById('dni-resultado').textContent = datosEncontrados.dni || document.getElementById('dni').value;
    document.getElementById('edad').textContent = datosEncontrados.edad || 'No calculable';
    document.getElementById('mayor-edad').innerHTML = datosEncontrados.es_mayor_edad 
        ? '<span class="badge bg-success">Sí</span>' 
        : '<span class="badge bg-warning">No</span>';
    document.getElementById('direccion').textContent = datosEncontrados.direccion_completa || 'NO DISPONIBLE';
    document.getElementById('ubigeo').textContent = datosEncontrados.ubigeo || '000000';
    document.getElementById('departamento').textContent = datosEncontrados.departamento || 'NO DISPONIBLE';
    document.getElementById('provincia').textContent = datosEncontrados.provincia || 'NO DISPONIBLE';
    document.getElementId('distrito').textContent = datosEncontrados.distrito || 'NO DISPONIBLE';
    
    document.getElementById('resultado').style.display = 'block';
}

function mostrarClienteExistente() {
    if (!clienteExistente) return;
    
    document.getElementById('cliente-existente').innerHTML = 
        `<strong>${clienteExistente.nombre_completo}</strong> (${clienteExistente.numero_documento})`;
    document.getElementById('existe-bd').style.display = 'block';
}

function usarCliente() {
    if (!datosEncontrados) return;
    
    // Crear cliente desde RENIEC
    fetch('{{ route("contabilidad.clientes.crear-desde-reniec") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ reniec_data: datosEncontrados })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Cliente creado exitosamente: ${data.cliente.nombre_completo}`);
            // Redireccionar a ver el cliente
            window.location.href = `/contabilidad/clientes/${data.cliente.id}`;
        } else {
            alert('❌ Error al crear el cliente: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error de conexión al crear el cliente');
    });
}

function usarClienteExistente() {
    if (!clienteExistente) return;
    
    alert(`✅ Usando cliente existente: ${clienteExistente.nombre_completo}`);
    window.location.href = `/contabilidad/clientes/${clienteExistente.id}`;
}

function verificarExistencia() {
    const dni = document.getElementById('dni').value.trim();
    
    fetch('{{ route("contabilidad.clientes.consultar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ documento: dni })
    })
    .then(response => response.json())
    .then(data => {
        if (data.encontrado) {
            alert(`ℹ️ Cliente ya existe: ${data.cliente.nombre_completo}`);
            window.location.href = `/contabilidad/clientes/${data.cliente.id}`;
        } else {
            alert('ℹ️ Cliente no existe en nuestra base de datos');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error al verificar en base de datos');
    });
}

function registrarManual() {
    const dni = document.getElementById('dni').value.trim();
    window.location.href = `/contabilidad/clientes/create?dni=${dni}`;
}

function consultarOTRO() {
    document.getElementById('dni').value = '';
    document.getElementById('dni').focus();
    ocultarTodos();
}

function mostrarError(mensaje) {
    document.getElementById('mensaje-error').textContent = mensaje;
    document.getElementById('error').style.display = 'block';
}

function mostrarCargando() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('btn-consultar').disabled = true;
}

function ocultarCargando() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('btn-consultar').disabled = false;
}

function ocultarTodos() {
    document.getElementById('resultado').style.display = 'none';
    document.getElementById('error').style.display = 'none';
    document.getElementById('existe-bd').style.display = 'none';
}

// Permitir búsqueda con Enter
document.getElementById('dni').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        consultarDNI();
    }
});

// Auto-focus al cargar
document.getElementById('dni').focus();
</script>
@endpush