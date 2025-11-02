<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $factura->Tipo == 1 ? 'FACTURA' : 'BOLETA' }} ELECTRÓNICA - {{ $factura->Numero }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background-color: #f5f5f5; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .invoice-container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            border: 2px solid #333;
            box-shadow: 0 0 20px rgba(0,0,0,0.15);
        }
        .invoice-header {
            padding: 25px;
            border-bottom: 2px solid #333;
        }
        .company-logo {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .company-info {
            font-size: 0.9rem;
            line-height: 1.6;
        }
        .document-box {
            border: 3px solid #333;
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
        }
        .document-box h4 {
            margin: 0;
            font-weight: 700;
            font-size: 1.1rem;
            color: #333;
        }
        .document-box .ruc-header {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .document-box .doc-number {
            font-size: 1.3rem;
            font-weight: 700;
            color: #e74c3c;
            margin-top: 5px;
        }
        .invoice-body {
            padding: 25px;
        }
        .client-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .client-info table {
            width: 100%;
            font-size: 0.9rem;
        }
        .client-info td {
            padding: 5px 0;
        }
        .client-info td:first-child {
            font-weight: 600;
            width: 150px;
        }
        .table-items {
            font-size: 0.9rem;
        }
        .table-items thead {
            background-color: #34495e;
            color: white;
        }
        .table-items thead th {
            padding: 12px 8px;
            font-weight: 600;
        }
        .table-items tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
        }
        .totals-section {
            margin-top: 30px;
        }
        .amount-words {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
            margin-bottom: 20px;
        }
        .totals-table {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .totals-table table {
            width: 100%;
        }
        .totals-table td {
            padding: 8px;
            font-size: 0.95rem;
        }
        .totals-table .total-row {
            border-top: 2px solid #333;
            font-size: 1.2rem;
            font-weight: 700;
            background-color: #e8f4f8;
        }
        .invoice-footer {
            padding: 20px;
            text-align: center;
            background-color: #f8f9fa;
            border-top: 2px solid #333;
        }
        .action-buttons {
            padding: 20px;
            background-color: #ecf0f1;
            text-align: center;
        }
        
        /* Estilos para impresión */
        @media print {
            body { 
                background-color: #fff; 
                margin: 0;
            }
            .invoice-container {
                box-shadow: none;
                border: 2px solid #333;
                margin: 0;
                max-width: 100%;
            }
            .action-buttons { 
                display: none; 
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    
    {{-- Botones de Acción (No se imprimen) --}}
    <div class="action-buttons no-print">
        <a href="{{ route('contador.facturas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver a Ventas
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-1"></i> Imprimir
        </button>
        <button onclick="descargarPDF()" class="btn btn-danger">
            <i class="fas fa-file-pdf me-1"></i> Descargar PDF
        </button>
    </div>

    <div class="invoice-container">
        
        {{-- ENCABEZADO --}}
        <div class="invoice-header">
            <div class="row align-items-center">
                <div class="col-7">
                    <div class="company-logo">
                        {{ $empresa['nombre'] }}
                    </div>
                    <div class="company-info">
                        <strong>{{ $empresa['giro'] }}</strong><br>
                        <i class="fas fa-map-marker-alt me-1"></i> {{ $empresa['direccion'] }}<br>
                        <i class="fas fa-phone me-1"></i> {{ $empresa['telefono'] }} | 
                        <i class="fas fa-envelope me-1"></i> {{ $empresa['email'] }}
                    </div>
                </div>
                <div class="col-5">
                    <div class="document-box">
                        <div class="ruc-header">R.U.C. {{ $empresa['ruc'] }}</div>
                        <hr class="my-2">
                        <h4>{{ $factura->Tipo == 1 ? 'FACTURA' : 'BOLETA' }} ELECTRÓNICA</h4>
                        <hr class="my-2">
                        <div class="doc-number">{{ $factura->Numero }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DATOS DEL CLIENTE --}}
        <div class="invoice-body">
            <div class="client-info">
                <div class="row">
                    <div class="col-8">
                        <table>
                            <tr>
                                <td><strong>Señor(es):</strong></td>
                                <td>{{ $factura->ClienteNombre }}</td>
                            </tr>
                            <tr>
                                <td><strong>Dirección:</strong></td>
                                <td>{{ $factura->ClienteDireccion }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-4">
                        <table>
                            <tr>
                                <td><strong>{{ strlen($factura->ClienteRuc) == 11 ? 'RUC' : 'DNI' }}:</strong></td>
                                <td>{{ $factura->ClienteRuc }}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha Emisión:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($factura->Fecha)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Vencimiento:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($factura->FechaV)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Condición:</strong></td>
                                <td>{{ $condicionPago }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TABLA DE ITEMS --}}
            <table class="table table-items table-bordered">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 80px;">Cantidad</th>
                        <th class="text-center" style="width: 80px;">Unidad</th>
                        <th style="width: 120px;">Código</th>
                        <th>Descripción</th>
                        <th class="text-end" style="width: 100px;">V. Unitario</th>
                        <th class="text-end" style="width: 100px;">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detalles as $item)
                    <tr>
                        <td class="text-center">{{ number_format($item->Cantidad, 2) }}</td>
                        <td class="text-center">UNIDAD</td>
                        <td>{{ $item->Codpro }}</td>
                        <td>
                            <strong>{{ $item->ProductoNombre }}</strong>
                            <br><small class="text-muted">
                                <i class="fas fa-barcode me-1"></i>Lote: {{ $item->Lote }} | 
                                <i class="far fa-calendar me-1"></i>Venc: {{ \Carbon\Carbon::parse($item->Vencimiento)->format('m/Y') }}
                            </small>
                        </td>
                        <td class="text-end">{{ number_format($item->Precio, 2) }}</td>
                        <td class="text-end"><strong>{{ number_format($item->Subtotal, 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- TOTALES --}}
            <div class="totals-section">
                <div class="row">
                    <div class="col-7">
                        <div class="amount-words">
                            <strong><i class="fas fa-comment-dollar me-1"></i> SON:</strong><br>
                            <span style="text-transform: uppercase;">{{ $totalEnLetras }}</span>
                        </div>
                        
                        <div style="font-size: 0.85rem; color: #7f8c8d;">
                            <strong>Información Adicional:</strong><br>
                            <i class="fas fa-user-tie me-1"></i> <strong>Vendedor:</strong> {{ $factura->VendedorNombre ?? 'N/A' }}<br>
                            <i class="fas fa-coins me-1"></i> <strong>Moneda:</strong> {{ $factura->MonedaNombre ?? 'SOLES' }}<br>
                            <i class="fas fa-credit-card me-1"></i> <strong>Forma de Pago:</strong> {{ $condicionPago }}
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="totals-table">
                            <table>
                                <tr>
                                    <td>Op. Gravada:</td>
                                    <td class="text-end"><strong>S/ {{ number_format($factura->Subtotal, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Op. Exonerada:</td>
                                    <td class="text-end"><strong>S/ 0.00</strong></td>
                                </tr>
                                <tr>
                                    <td>Op. Inafecta:</td>
                                    <td class="text-end"><strong>S/ 0.00</strong></td>
                                </tr>
                                <tr>
                                    <td>IGV (18%):</td>
                                    <td class="text-end"><strong>S/ {{ number_format($factura->Igv, 2) }}</strong></td>
                                </tr>
                                <tr class="total-row">
                                    <td>IMPORTE TOTAL:</td>
                                    <td class="text-end">S/ {{ number_format($factura->Total, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PIE DE PÁGINA --}}
        <div class="invoice-footer">        
            <p class="text-muted small mb-0">
                Generado en el Sistema Integrado de Facturación Electrónica (SUNAT)<br>
                Para consultar el comprobante ingrese a: <strong>{{ $empresa['web'] }}</strong>
            </p>
        </div>
    </div>

    <script>
        function descargarPDF() {
            // Aquí puedes implementar la descarga del PDF
            // Por ahora, usaremos la función de imprimir
            alert('Función de descarga PDF. Por el momento use la opción de imprimir y guarde como PDF.');
            window.print();
        }
    </script>

</body>
</html>