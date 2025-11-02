<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ORDEN DE COMPRA - {{ $orden->Serie }}-{{ $orden->Numero }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background-color: #f5f5f5; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .orden-container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            border: 2px solid #34495e;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        /* ENCABEZADO - Datos de MI EMPRESA (quien emite) */
        .orden-header {
            padding: 25px;
            border-bottom: 3px solid #34495e;
            background: linear-gradient(135deg, #2980b9 0%, #2c3e50 100%);
            color: white;
        }
        .mi-empresa-logo {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .mi-empresa-info {
            font-size: 0.85rem;
            line-height: 1.6;
        }
        .document-box {
            border: 3px solid white;
            text-align: center;
            padding: 15px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 10px;
        }
        .document-box h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.2rem;
            color: white;
            letter-spacing: 1px;
        }
        .document-box .doc-number {
            font-size: 1.4rem;
            font-weight: 700;
            color: #f39c12;
            margin-top: 5px;
        }
        
        /* CUERPO */
        .orden-body {
            padding: 25px;
        }
        
        /* SECCIÓN PROVEEDOR - A quien le compro */
        .proveedor-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .proveedor-box h5 {
            color: #856404;
            font-weight: 700;
            margin-bottom: 15px;
            border-bottom: 2px solid #ffc107;
            padding-bottom: 10px;
        }
        .proveedor-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .proveedor-info-item strong {
            display: block;
            color: #856404;
            font-size: 0.85rem;
            margin-bottom: 3px;
        }
        .proveedor-info-item p {
            margin: 0;
            color: #333;
            font-weight: 500;
        }
        
        /* DATOS DE LA ORDEN */
        .orden-datos {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        .dato-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }
        .dato-label {
            font-size: 0.75rem;
            color: #7f8c8d;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .dato-value {
            font-size: 1rem;
            font-weight: 700;
            color: #2c3e50;
        }
        .badge-estado {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
        }
        
        /* TABLA DE PRODUCTOS SOLICITADOS */
        .productos-section {
            border: 2px solid #34495e;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 25px;
        }
        .productos-header {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            color: white;
            padding: 12px 20px;
            font-weight: 700;
        }
        .table-productos {
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        .table-productos thead {
            background-color: #ecf0f1;
            color: #2c3e50;
        }
        .table-productos thead th {
            padding: 12px 10px;
            font-weight: 700;
            border: none;
        }
        .table-productos tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }
        .table-productos tbody tr:last-child td {
            border-bottom: none;
        }
        .product-name {
            font-weight: 600;
            color: #2c3e50;
        }
        .lab-badge {
            display: inline-block;
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        /* SECCIÓN DE TOTALES */
        .totales-section {
            margin-top: 25px;
        }
        .totales-box {
            background: linear-gradient(135deg, #2980b9 0%, #2c3e50 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(41, 128, 185, 0.3);
        }
        .totales-box table {
            width: 100%;
            color: white;
        }
        .totales-box td {
            padding: 8px 0;
            font-size: 0.95rem;
        }
        .totales-box .total-final {
            border-top: 2px solid white;
            font-size: 1.3rem;
            font-weight: 700;
            padding-top: 12px !important;
        }
        
        /* CONDICIONES Y PIE */
        .condiciones-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .condiciones-box h6 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .condiciones-box ul {
            margin: 0;
            padding-left: 20px;
            font-size: 0.85rem;
            color: #495057;
        }
        .firmas-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 40px;
            padding-top: 20px;
        }
        .firma-box {
            text-align: center;
        }
        .firma-linea {
            border-top: 2px solid #333;
            margin-bottom: 8px;
            padding-top: 50px;
        }
        .firma-label {
            font-weight: 700;
            color: #2c3e50;
        }
        
        .orden-footer {
            padding: 20px;
            text-align: center;
            background-color: #ecf0f1;
            border-top: 2px solid #34495e;
        }
        
        .action-buttons {
            padding: 20px;
            background-color: #ecf0f1;
            text-align: center;
        }
        .action-buttons .btn {
            margin: 0 5px;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 25px;
        }
        
        @media print {
            body { 
                background-color: #fff; 
                margin: 0;
            }
            .orden-container {
                box-shadow: none;
                border: 2px solid #34495e;
                margin: 0;
                max-width: 100%;
            }
            .action-buttons, .no-print { 
                display: none; 
            }
        }
    </style>
</head>
<body>
    
    {{-- Botones de Acción --}}
    <div class="action-buttons no-print">
        <a href="{{ route('contador.compras.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-2"></i> Imprimir
        </button>
        <button onclick="enviarProveedor()" class="btn btn-success">
            <i class="fas fa-paper-plane me-2"></i> Enviar a Proveedor
        </button>
    </div>

    <div class="orden-container">
        
        {{-- ENCABEZADO - DATOS DE MI EMPRESA (quien emite la orden) --}}
        <div class="orden-header">
            <div class="row align-items-center">
                <div class="col-7">
                    <div class="mi-empresa-logo">
                        <i class="fas fa-hospital me-2"></i>{{ $empresa['nombre'] }}
                    </div>
                    <div class="mi-empresa-info">
                        <i class="fas fa-industry me-2"></i><strong>{{ $empresa['giro'] }}</strong><br>
                        <i class="fas fa-map-marker-alt me-2"></i>{{ $empresa['direccion'] }}<br>
                        <i class="fas fa-id-card me-2"></i><strong>RUC:</strong> {{ $empresa['ruc'] }}<br>
                        <i class="fas fa-phone me-2"></i>{{ $empresa['telefono'] }} | 
                        <i class="fas fa-envelope me-2"></i>{{ $empresa['email'] }}
                    </div>
                </div>
                <div class="col-5">
                    <div class="document-box">
                        <h3><i class="fas fa-shopping-cart me-2"></i>ORDEN DE COMPRA</h3>
                        <hr class="my-2" style="border-color: white; opacity: 0.5;">
                        <div class="doc-number">{{ $orden->Serie }}-{{ $orden->Numero }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="orden-body">
            
            {{-- DATOS DEL PROVEEDOR (A quien le estoy comprando) --}}
            <div class="proveedor-box">
                <h5>
                    <i class="fas fa-truck me-2"></i>PROVEEDOR SOLICITADO
                    <small class="float-end" style="font-size: 0.85rem; font-weight: 500;">
                        Por favor, enviar su factura y productos a nuestra dirección
                    </small>
                </h5>
                <div class="proveedor-info">
                    <div class="proveedor-info-item">
                        <strong><i class="fas fa-building me-1"></i> RAZÓN SOCIAL:</strong>
                        <p>{{ $proveedor->RazonSocial }}</p>
                    </div>
                    <div class="proveedor-info-item">
                        <strong><i class="fas fa-id-card me-1"></i> R.U.C.:</strong>
                        <p>{{ $proveedor->Ruc }}</p>
                    </div>
                    <div class="proveedor-info-item" style="grid-column: 1 / -1;">
                        <strong><i class="fas fa-map-marker-alt me-1"></i> DIRECCIÓN:</strong>
                        <p>{{ $proveedor->Direccion }}</p>
                    </div>
                    @if(isset($proveedor->Telefono))
                    <div class="proveedor-info-item">
                        <strong><i class="fas fa-phone me-1"></i> TELÉFONO:</strong>
                        <p>{{ $proveedor->Telefono }}</p>
                    </div>
                    @endif
                    @if(isset($proveedor->Email))
                    <div class="proveedor-info-item">
                        <strong><i class="fas fa-envelope me-1"></i> EMAIL:</strong>
                        <p>{{ $proveedor->Email }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- INFORMACIÓN DE LA ORDEN --}}
            <div class="orden-datos">
                <div class="dato-card">
                    <div class="dato-label">
                        <i class="far fa-calendar me-1"></i> Fecha de Emisión
                    </div>
                    <div class="dato-value">
                        {{ \Carbon\Carbon::parse($orden->FechaEmision)->format('d/m/Y') }}
                    </div>
                </div>
                <div class="dato-card">
                    <div class="dato-label">
                        <i class="far fa-calendar-check me-1"></i> Fecha de Entrega Solicitada
                    </div>
                    <div class="dato-value">
                        {{ \Carbon\Carbon::parse($orden->FechaEntrega)->format('d/m/Y') }}
                    </div>
                </div>
                <div class="dato-card">
                    <div class="dato-label">
                        <i class="fas fa-coins me-1"></i> Moneda
                    </div>
                    <div class="dato-value">
                        {{ $orden->Moneda == 1 ? 'SOLES (S/)' : 'DÓLARES ($)' }}
                    </div>
                </div>
            </div>

            <div class="alert alert-info mb-4" role="alert">
                <strong><i class="fas fa-info-circle me-2"></i>Estado de la Orden:</strong>
                <span class="badge badge-estado {{ $orden->Estado == 'PENDIENTE' ? 'bg-warning text-dark' : ($orden->Estado == 'RECIBIDO' ? 'bg-success' : ($orden->Estado == 'PARCIAL' ? 'bg-info' : 'bg-danger')) }}">
                    {{ $orden->Estado }}
                </span>
                @if($orden->Estado == 'PENDIENTE')
                    <span class="ms-2">- Esperando recepción de mercadería y factura del proveedor</span>
                @elseif($orden->Estado == 'RECIBIDO')
                    <span class="ms-2">- Orden completamente recibida y facturada</span>
                @endif
            </div>

            {{-- PRODUCTOS SOLICITADOS --}}
            <div class="productos-section">
                <div class="productos-header">
                    <i class="fas fa-box-open me-2"></i>DETALLE DE PRODUCTOS SOLICITADOS
                </div>
                <table class="table table-productos">
                    <thead>
                        <tr>
                            <th style="width: 80px;"><i class="fas fa-hashtag me-1"></i> ITEM</th>
                            <th style="width: 100px;"><i class="fas fa-barcode me-1"></i> CÓDIGO</th>
                            <th><i class="fas fa-pills me-1"></i> DESCRIPCIÓN DEL PRODUCTO</th>
                            <th style="width: 130px;"><i class="fas fa-flask me-1"></i> LABORATORIO</th>
                            <th class="text-center" style="width: 80px;"><i class="fas fa-cubes me-1"></i> CANT.</th>
                            <th class="text-end" style="width: 100px;"><i class="fas fa-tag me-1"></i> P. UNIT.</th>
                            <th class="text-end" style="width: 110px;"><i class="fas fa-calculator me-1"></i> SUBTOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detalles as $index => $item)
                        <tr>
                            <td class="text-center"><strong>{{ $index + 1 }}</strong></td>
                            <td><code>{{ $item->CodPro }}</code></td>
                            <td>
                                <div class="product-name">{{ $item->ProductoNombre }}</div>
                            </td>
                            <td>
                                <span class="lab-badge">{{ $item->LaboratorioNombre }}</span>
                            </td>
                            <td class="text-center"><strong>{{ number_format($item->Cantidad, 0) }}</strong></td>
                            <td class="text-end">S/ {{ number_format($item->CostoUnitario, 2) }}</td>
                            <td class="text-end"><strong>S/ {{ number_format($item->Subtotal, 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TOTALES Y CONDICIONES --}}
            <div class="row">
                <div class="col-7">
                    <div class="condiciones-box">
                        <h6><i class="fas fa-file-contract me-2"></i>CONDICIONES DE LA ORDEN:</h6>
                        <ul>
                            <li>Los productos deben ser entregados en perfectas condiciones.</li>
                            <li>Los lotes deben tener fecha de vencimiento mínima de 12 meses.</li>
                            <li>La factura debe ser emitida a nombre de <strong>{{ $empresa['nombre'] }}</strong> - RUC {{ $empresa['ruc'] }}</li>
                            <li>Lugar de entrega: {{ $empresa['direccion'] }}</li>
                            <li>Horario de recepción: Lunes a Viernes de 8:00 AM a 5:00 PM</li>
                            @if(isset($orden->Observaciones) && $orden->Observaciones)
                            <li><strong>Observaciones adicionales:</strong> {{ $orden->Observaciones }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="col-5">
                    <div class="totales-box">
                        <table>
                            <tr>
                                <td><i class="fas fa-file-invoice me-1"></i> Subtotal:</td>
                                <td class="text-end"><strong>S/ {{ number_format($orden->Subtotal, 2) }}</strong></td>
                            </tr>                        
                            <tr class="total-final">
                                <td><i class="fas fa-coins me-2"></i> TOTAL DE LA ORDEN:</td>
                                <td class="text-end">S/ {{ number_format($orden->Total, 2) }}</td>
                            </tr>
                        </table>
                        <div class="mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.3); font-size: 0.85rem;">
                            <i class="fas fa-info-circle me-1"></i> <strong>Nota:</strong> El proveedor debe emitir su factura por este monto
                        </div>
                    </div>
                </div>
            </div>

            {{-- FIRMAS --}}
            <div class="firmas-section">
                <div class="firma-box">
                    <div class="firma-linea"></div>
                    <div class="firma-label">SOLICITADO POR</div>
                    <small class="text-muted">{{ $empresa['nombre'] }}</small>
                </div>
                <div class="firma-box">
                    <div class="firma-linea"></div>
                    <div class="firma-label">ACEPTADO POR</div>
                    <small class="text-muted">{{ $proveedor->RazonSocial }}</small>
                </div>
            </div>
        </div>

        <div class="orden-footer">
            <p class="mb-2">
                <strong><i class="fas fa-file-signature me-1"></i> Orden de Compra Generada por {{ $empresa['nombre'] }}</strong>
            </p>
            <p class="text-muted small mb-0">
                <i class="fas fa-calendar-alt me-1"></i> Fecha de emisión: {{ \Carbon\Carbon::parse($orden->FechaEmision)->format('d/m/Y H:i:s') }}<br>
            
            </p>
        </div>
    </div>

    <script>
        function enviarProveedor() {
            alert('Función para enviar orden por correo al proveedor. Implementar con backend.');
        }
    </script>

</body>
</html>