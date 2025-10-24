<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Planilla - Sistema Farmacéutico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .preview-section {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 1.5rem;
            border: 2px solid #2196f3;
        }
        .cliente-seleccionado {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .step {
            text-align: center;
            padding: 1rem;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
        }
        .step.active {
            background: #007bff;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-plus-circle text-primary"></i> Nueva Planilla de Cobranza</h2>
                        <p class="text-muted mb-0">Crear planilla para seguimiento de cobranzas</p>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('planillas-cobranza.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <hr>
            </div>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="text-center">
                <div class="step active" id="step1">1</div>
                <small>Seleccionar Cliente</small>
            </div>
            <div class="text-center">
                <div class="step" id="step2">2</div>
                <small>Configurar Planilla</small>
            </div>
            <div class="text-center">
                <div class="step" id="step3">3</div>
                <small>Generar</small>
            </div>
        </div>

        <form id="formPlanilla" action="{{ route('planillas-cobranza.generar') }}" method="POST">
            @csrf
            
            <!-- Paso 1: Seleccionar Cliente -->
            <div class="form-section" id="paso1">
                <h4><i class="fas fa-user"></i> 1. Seleccionar Cliente</h4>
                
                <div class="row">
                    <div class="col-md-8">
                        <label class="form-label">Buscar Cliente</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="buscarCliente"
                                   placeholder="Buscar por nombre, DNI o razón social">
                            <button type="button" class="btn btn-outline-secondary" onclick="buscarCliente()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-primary w-100" onclick="mostrarModalBusqueda()">
                            <i class="fas fa-search-plus"></i> Búsqueda Avanzada
                        </button>
                    </div>
                </div>

                <!-- Resultados de búsqueda -->
                <div id="resultadosCliente" class="mt-3" style="display: none;">
                    <h6>Resultados de búsqueda:</h6>
                    <div id="listaClientes" class="list-group"></div>
                </div>

                <!-- Cliente seleccionado -->
                <div id="clienteSeleccionado" class="cliente-seleccionado" style="display: none;">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 id="clienteNombre"></h5>
                            <p class="mb-1"><strong>DNI/RUC:</strong> <span id="clienteDocumento"></span></p>
                            <p class="mb-1"><strong>Dirección:</strong> <span id="clienteDireccion"></span></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-light" onclick="cambiarCliente()">
                                <i class="fas fa-edit"></i> Cambiar
                            </button>
                            <input type="hidden" name="cod_clie" id="clienteId">
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button type="button" class="btn btn-primary" onclick="siguientePaso(2)" id="btnSiguiente1">
                        <i class="fas fa-arrow-right"></i> Siguiente
                    </button>
                </div>
            </div>

            <!-- Paso 2: Configurar Planilla -->
            <div class="form-section" id="paso2" style="display: none;">
                <h4><i class="fas fa-cog"></i> 2. Configurar Planilla</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-user-tie text-primary"></i> Vendedor Asignado
                        </label>
                        <select name="cod_emp" class="form-select" required>
                            <option value="">Seleccionar vendedor</option>
                            @foreach($vendedores as $vendedor)
                            <option value="{{ $vendedor->Codemp }}">{{ $vendedor->Nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-calendar-check text-success"></i> Fecha de Vencimiento
                        </label>
                        <input type="date" 
                               name="fecha_vencimiento" 
                               class="form-control" 
                               value="{{ date('Y-m-d', strtotime('+30 days')) }}"
                               required>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <label class="form-label">
                            <i class="fas fa-sticky-note text-warning"></i> Observaciones (Opcional)
                        </label>
                        <textarea name="observaciones" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Agregar notas o instrucciones adicionales"></textarea>
                    </div>
                </div>

                <!-- Vista previa de configuración -->
                <div class="preview-section mt-3">
                    <h6><i class="fas fa-eye"></i> Vista Previa</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Cliente:</strong><br>
                            <span id="previewCliente">Seleccione un cliente</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Vendedor:</strong><br>
                            <span id="previewVendedor">Seleccione un vendedor</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Vence:</strong><br>
                            <span id="previewVencimiento">{{ date('d/m/Y', strtotime('+30 days')) }}</span>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button type="button" class="btn btn-secondary" onclick="anteriorPaso(1)">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-primary" onclick="siguientePaso(3)" id="btnSiguiente2">
                        <i class="fas fa-arrow-right"></i> Siguiente
                    </button>
                </div>
            </div>

            <!-- Paso 3: Generar Planilla -->
            <div class="form-section" id="paso3" style="display: none;">
                <h4><i class="fas fa-check-circle"></i> 3. Generar Planilla</h4>
                
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Resumen de la Planilla</h6>
                    <div id="resumenPlanilla">
                        <p><strong>Cliente:</strong> <span id="resumenCliente"></span></p>
                        <p><strong>Vendedor:</strong> <span id="resumenVendedor"></span></p>
                        <p><strong>Fecha de Vencimiento:</strong> <span id="resumenVencimiento"></span></p>
                        <p><strong>Observaciones:</strong> <span id="resumenObservaciones">Sin observaciones</span></p>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> ¿Está seguro?</h6>
                    <p>Al generar la planilla, se incluirán automáticamente todas las facturas pendientes del cliente seleccionado.</p>
                </div>

                <div class="text-center mt-3">
                    <button type="button" class="btn btn-secondary" onclick="anteriorPaso(2)">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="submit" class="btn btn-success" id="btnGenerar">
                        <i class="fas fa-check"></i> Generar Planilla
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal de búsqueda avanzada -->
    <div class="modal fade" id="modalBusquedaCliente" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Búsqueda Avanzada de Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Nombre/Razón Social</label>
                            <input type="text" class="form-control" id="filtroNombre" placeholder="Buscar por nombre">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">DNI/RUC</label>
                            <input type="text" class="form-control" id="filtroDocumento" placeholder="Buscar por documento">
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-primary" onclick="busquedaAvanzada()">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                    <div id="resultadosAvanzados" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let clienteData = null;

        function buscarCliente() {
            const termino = $('#buscarCliente').val();
            if (termino.length < 2) {
                alert('Ingrese al menos 2 caracteres');
                return;
            }

            $.ajax({
                url: '/contabilidad/facturacion/buscar-cliente',
                method: 'POST',
                data: {
                    termino: termino,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success && response.clientes.length > 0) {
                        mostrarResultados(response.clientes);
                    } else {
                        alert('No se encontraron clientes');
                    }
                },
                error: function() {
                    alert('Error en la búsqueda');
                }
            });
        }

        function mostrarResultados(clientes) {
            const lista = $('#listaClientes');
            lista.empty();
            
            clientes.forEach(function(cliente) {
                const item = $(`
                    <a href="#" class="list-group-item list-group-item-action" onclick="seleccionarCliente(${JSON.stringify(cliente).replace(/"/g, '&quot;')})">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${cliente.Razon}</h6>
                            <small>${cliente.RucDni || 'Sin documento'}</small>
                        </div>
                        <small>${cliente.Direccion || 'Sin dirección'}</small>
                    </a>
                `);
                lista.append(item);
            });
            
            $('#resultadosCliente').show();
        }

        function seleccionarCliente(cliente) {
            clienteData = cliente;
            
            $('#clienteNombre').text(cliente.Razon);
            $('#clienteDocumento').text(cliente.RucDni || 'Sin documento');
            $('#clienteDireccion').text(cliente.Direccion || 'Sin dirección');
            $('#clienteId').val(cliente.Codclie);
            
            $('#clienteSeleccionado').show();
            $('#resultadosCliente').hide();
            $('#buscarCliente').val('');
            
            // Actualizar vista previa
            actualizarVistaPrevia();
        }

        function cambiarCliente() {
            clienteData = null;
            $('#clienteSeleccionado').hide();
            $('#clienteId').val('');
        }

        function siguientePaso(paso) {
            if (paso === 2 && !clienteData) {
                alert('Debe seleccionar un cliente primero');
                return;
            }
            
            if (paso === 3) {
                const vendedor = $('select[name="cod_emp"]').val();
                if (!vendedor) {
                    alert('Debe seleccionar un vendedor');
                    return;
                }
                
                generarResumen();
            }
            
            // Ocultar paso actual
            document.getElementById('paso' + (paso - 1)).style.display = 'none';
            
            // Mostrar siguiente paso
            document.getElementById('paso' + paso).style.display = 'block';
            
            // Actualizar indicador de pasos
            actualizarIndicadorPasos(paso);
        }

        function anteriorPaso(paso) {
            document.getElementById('paso' + (paso + 1)).style.display = 'none';
            document.getElementById('paso' + paso).style.display = 'block';
            actualizarIndicadorPasos(paso);
        }

        function actualizarIndicadorPasos(pasoActual) {
            for (let i = 1; i <= 3; i++) {
                const step = document.getElementById('step' + i);
                step.classList.remove('active', 'completed');
                
                if (i < pasoActual) {
                    step.classList.add('completed');
                } else if (i === pasoActual) {
                    step.classList.add('active');
                }
            }
        }

        function actualizarVistaPrevia() {
            if (clienteData) {
                $('#previewCliente').text(clienteData.Razon);
                $('#resumenCliente').text(clienteData.Razon);
            }
            
            const vendedor = $('select[name="cod_emp"] option:selected').text();
            if (vendedor !== 'Seleccionar vendedor') {
                $('#previewVendedor').text(vendedor);
                $('#resumenVendedor').text(vendedor);
            }
            
            const vencimiento = $('input[name="fecha_vencimiento"]').val();
            $('#previewVencimiento').text(new Date(vencimiento).toLocaleDateString());
            $('#resumenVencimiento').text(new Date(vencimiento).toLocaleDateString());
        }

        function generarResumen() {
            const observaciones = $('textarea[name="observaciones"]').val();
            $('#resumenObservaciones').text(observaciones || 'Sin observaciones');
        }

        function mostrarModalBusqueda() {
            new bootstrap.Modal(document.getElementById('modalBusquedaCliente')).show();
        }

        function busquedaAvanzada() {
            const nombre = $('#filtroNombre').val();
            const documento = $('#filtroDocumento').val();
            
            // Aquí implementarías la búsqueda avanzada
            alert('Función de búsqueda avanzada en desarrollo');
        }

        // Actualizar vista previa cuando cambie el vendedor
        $('select[name="cod_emp"]').on('change', function() {
            const vendedor = $(this).find('option:selected').text();
            if (vendedor !== 'Seleccionar vendedor') {
                $('#previewVendedor').text(vendedor);
                $('#resumenVendedor').text(vendedor);
            }
        });

        // Actualizar vista previa cuando cambie la fecha
        $('input[name="fecha_vencimiento"]').on('change', function() {
            const fecha = new Date($(this).val());
            $('#previewVencimiento').text(fecha.toLocaleDateString());
            $('#resumenVencimiento').text(fecha.toLocaleDateString());
        });
    </script>
</body>
</html>