<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libro Mayor - Sistema Farmacéutico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .filtros-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .libro-mayor-table {
            font-size: 0.9rem;
        }
        .saldo-positivo {
            color: #28a745;
            font-weight: bold;
        }
        .saldo-negativo {
            color: #dc3545;
            font-weight: bold;
        }
        .fecha-filtro {
            max-width: 200px;
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
                        <h2><i class="fas fa-book text-primary"></i> Libro Mayor</h2>
                        <p class="text-muted mb-0">Consulta detallada de movimientos por cuenta contable</p>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('contabilidad.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </a>
                        <button type="button" class="btn btn-outline-primary" onclick="exportarExcel()">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </button>
                    </div>
                </div>
                <hr>
            </div>
        </div>

        <!-- Filtros de Búsqueda -->
        <div class="row">
            <div class="col-12">
                <div class="filtros-section">
                    <form method="GET" action="{{ route('contabilidad.libro-mayor') }}" id="filtroForm">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-calculator text-primary"></i> Cuenta Contable
                                </label>
                                <div class="input-group">
                                    <input type="text" 
                                           name="cuenta" 
                                           class="form-control" 
                                           value="{{ $cuenta ?? '1041' }}"
                                           placeholder="Ej: 1041 (Cuentas por Cobrar)"
                                           id="cuentaInput">
                                    <button type="button" class="btn btn-outline-secondary" onclick="buscarCuenta()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Use el código de la cuenta contable</small>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-calendar-alt text-success"></i> Fecha Desde
                                </label>
                                <input type="date" 
                                       name="fecha_desde" 
                                       class="form-control fecha-filtro" 
                                       value="{{ $fechaDesde ?? date('Y-m-01') }}"
                                       id="fechaDesdeInput">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-calendar-alt text-danger"></i> Fecha Hasta
                                </label>
                                <input type="date" 
                                       name="fecha_hasta" 
                                       class="form-control fecha-filtro" 
                                       value="{{ $fechaHasta ?? date('Y-m-d') }}"
                                       id="fechaHastaInput">
                            </div>

                            <div class="col-md-3">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Consultar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                                        <i class="fas fa-eraser"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Resumen de la Consulta -->
        @if(isset($totales) && !empty($totales))
        <div class="row mb-3">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h6 class="text-muted">Saldo Inicial</h6>
                                <h4 class="{{ ($totales['saldo_final'] - $totales['total_debe'] + $totales['total_haber']) >= 0 ? 'text-success' : 'text-danger' }}">
                                    S/ {{ number_format($totales['saldo_final'] - $totales['total_debe'] + $totales['total_haber'], 2) }}
                                </h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted">Total Debe</h6>
                                <h4 class="text-info">S/ {{ number_format($totales['total_debe'], 2) }}</h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted">Total Haber</h6>
                                <h4 class="text-warning">S/ {{ number_format($totales['total_haber'], 2) }}</h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted">Saldo Final</h6>
                                <h4 class="{{ $totales['saldo_final'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    S/ {{ number_format($totales['saldo_final'], 2) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Tabla de Movimientos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-table text-primary"></i> Movimientos de la Cuenta {{ $cuenta ?? '1041' }}</h5>
                            <div>
                                <span class="badge bg-primary">{{ $movimientos->count() ?? 0 }} registros</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(isset($movimientos) && $movimientos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover libro-mayor-table" id="tablaLibroMayor">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="10%">
                                            <i class="fas fa-calendar"></i> Fecha
                                        </th>
                                        <th width="15%">
                                            <i class="fas fa-file-alt"></i> Documento
                                        </th>
                                        <th width="25%">
                                            <i class="fas fa-user"></i> Cliente/Concepto
                                        </th>
                                        <th width="15%">
                                            <i class="fas fa-align-left"></i> Detalle
                                        </th>
                                        <th width="12%" class="text-end">
                                            <i class="fas fa-arrow-down"></i> Debe
                                        </th>
                                        <th width="12%" class="text-end">
                                            <i class="fas fa-arrow-up"></i> Haber
                                        </th>
                                        <th width="11%" class="text-end">
                                            <i class="fas fa-balance-scale"></i> Saldo
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $saldoAcumulado = $totales['saldo_final'] - $totales['total_debe'] + $totales['total_haber'];
                                    @endphp
                                    
                                    @foreach($movimientos as $movimiento)
                                    <tr>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($movimiento->FechaF)->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $movimiento->Documento }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ Str::limit($movimiento->Razon, 25) }}</strong>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($movimiento->Detalle ?? 'Sin detalle', 30) }}</small>
                                        </td>
                                        <td class="text-end">
                                            @if($movimiento->Debe > 0)
                                                <span class="text-info fw-bold">
                                                    S/ {{ number_format($movimiento->Debe, 2) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($movimiento->Haber > 0)
                                                <span class="text-warning fw-bold">
                                                    S/ {{ number_format($movimiento->Haber, 2) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @php
                                                $saldoAcumulado += $movimiento->Debe - $movimiento->Haber;
                                            @endphp
                                            <span class="{{ $saldoAcumulado >= 0 ? 'saldo-positivo' : 'saldo-negativo' }}">
                                                S/ {{ number_format($saldoAcumulado, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="4" class="text-end">TOTALES:</th>
                                        <th class="text-end text-info">
                                            S/ {{ number_format($totales['total_debe'], 2) }}
                                        </th>
                                        <th class="text-end text-warning">
                                            S/ {{ number_format($totales['total_haber'], 2) }}
                                        </th>
                                        <th class="text-end {{ $totales['saldo_final'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            S/ {{ number_format($totales['saldo_final'], 2) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron movimientos</h5>
                            <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Adicionales -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6><i class="fas fa-tools text-primary"></i> Acciones Adicionales</h6>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-outline-primary w-100" onclick="imprimirLibro()">
                                    <i class="fas fa-print"></i> Imprimir
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-outline-success w-100" onclick="generarPDF()">
                                    <i class="fas fa-file-pdf"></i> Generar PDF
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-outline-info w-100" onclick="enviarEmail()">
                                    <i class="fas fa-envelope"></i> Enviar por Email
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-outline-warning w-100" onclick="compararPeriodos()">
                                    <i class="fas fa-chart-line"></i> Comparar Períodos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#tablaLibroMayor').DataTable({
                pageLength: 25,
                order: [[0, 'desc']], // Ordenar por fecha descendente
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                responsive: true
            });

            // Validar fechas
            $('#fechaDesdeInput, #fechaHastaInput').on('change', function() {
                validarFechas();
            });
        });

        function validarFechas() {
            const fechaDesde = new Date($('#fechaDesdeInput').val());
            const fechaHasta = new Date($('#fechaHastaInput').val());

            if (fechaDesde > fechaHasta) {
                alert('La fecha "Desde" no puede ser mayor a la fecha "Hasta"');
                $('#fechaHastaInput').val($('#fechaDesdeInput').val());
            }
        }

        function limpiarFiltros() {
            $('#cuentaInput').val('1041');
            $('#fechaDesdeInput').val(new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0]);
            $('#fechaHastaInput').val(new Date().toISOString().split('T')[0]);
            $('#filtroForm').submit();
        }

        function exportarExcel() {
            const cuenta = $('#cuentaInput').val();
            const fechaDesde = $('#fechaDesdeInput').val();
            const fechaHasta = $('#fechaHastaInput').val();

            const url = `{{ route('contabilidad.libro-mayor.exportar') }}?cuenta=${cuenta}&fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}`;
            window.open(url, '_blank');
        }

        function imprimirLibro() {
            window.print();
        }

        function generarPDF() {
            // Implementar generación de PDF
            alert('Función de PDF en desarrollo');
        }

        function enviarEmail() {
            // Implementar envío por email
            alert('Función de email en desarrollo');
        }

        function compararPeriodos() {
            // Implementar comparación de períodos
            alert('Función de comparación en desarrollo');
        }

        function buscarCuenta() {
            // Mostrar modal con catálogo de cuentas
            alert('Catálogo de cuentas en desarrollo');
        }

        // Validación en tiempo real
        $('#cuentaInput').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Mostrar información de la cuenta seleccionada
        function mostrarInfoCuenta(cuenta) {
            const info = {
                '1041': 'Cuentas por Cobrar Comerciales',
                '1042': 'Cuentas por Cobrar Diversas',
                '1212': 'Mercaderías Manufacturadas',
                '4011': 'IGV - Cuenta Propia'
            };

            if (info[cuenta]) {
                console.log(`Cuenta ${cuenta}: ${info[cuenta]}`);
            }
        }
    </script>

    <style>
        @media print {
            .btn, .card-header, .filtros-section, .form-label, .d-flex {
                display: none !important;
            }
            .container-fluid {
                padding: 0;
            }
            .card {
                border: none;
                box-shadow: none;
            }
            .table {
                font-size: 12px;
            }
        }
    </style>
</body>
</html>