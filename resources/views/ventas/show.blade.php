<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documento de Venta: {{ $factura->Numero }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .invoice-container {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .invoice-header, .invoice-footer { padding: 25px; }
        .invoice-body { padding: 25px; }
        .company-details strong { font-size: 1.5rem; color: #333; }
        .document-details { border: 2px solid #333; text-align: center; padding: 10px; }
        .document-details h4 { margin: 0; font-weight: 700; }
        .document-details h5 { margin: 0; }
        .table-items th { background-color: #f2f2f2; }
        .totals-table td { border-bottom: none; }
        
        /* Estilos para impresión */
        @media print {
            body { background-color: #fff; margin: 0; }
            .invoice-container {
                box-shadow: none;
                border: none;
                margin: 0;
                max-width: 100%;
                border-radius: 0;
            }
            .btn-print { display: none; }
            .breadcrumb { display: none; }
        }
    </style>
</head>
<body>
    
    <div class="container-fluid p-3 breadcrumb">
        <a href="{{ route('contador.facturas.index') }}" class="btn btn-secondary btn-print">
            <i class="fas fa-arrow-left"></i> Volver a Ventas
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-print">
            <i class="fas fa-print"></i> Imprimir
        </button>
        {{-- <a href="#" class="btn btn-danger btn-print">
            <i class="fas fa-file-pdf"></i> Descargar PDF
        </a> --}}
    </div>

    <div class="invoice-container">
        <div class="invoice-header row align-items-center">
            <div class="col-8 company-details">
                <strong>{{ $empresa['nombre'] }}</strong><br>
                {{ $empresa['direccion'] }}<br>
                RUC: {{ $empresa['ruc'] }}
            </div>
            <div class="col-4">
                <div class="document-details">
                    <h4>R.U.C. {{ $empresa['ruc'] }}</h4>
                    <hr class="my-1">
                    <h4>{{ $factura->Tipo == 1 ? 'FACTURA' : 'BOLETA' }} ELECTRÓNICA</h4>
                    <hr class="my-1">
                    <h5 class="fw-bold">{{ $factura->Numero }}</h5>
                </div>
            </div>
        </div>

        <div class="invoice-body border-top border-bottom">
            <div class="row">
                <div class="col-8">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td style="width: 150px;"><strong>Señor(es):</strong></td>
                            <td>{{ $factura->ClienteNombre }}</td>
                        </tr>
                        <tr>
                            <td><strong>Dirección:</strong></td>
                            <td>{{ $factura->ClienteDireccion }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-4">
                     <table class="table table-sm table-borderless">
                        <tr>
                            <td style="width: 100px;"><strong>RUC/DNI:</strong></td>
                            <td>{{ $factura->ClienteRuc }}</td>
                        </tr>
                        <tr>
                            <td><strong>Emisión:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($factura->Fecha)->format('d/m/Y') }}</td>
                        </tr>
                         <tr>
                            <td><strong>Vencimiento:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($factura->FechaV)->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <table class="table table-items table-striped">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Cantidad</th>
                        <th>Unidad</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th class="text-end">V. Unitario</th>
                        <th class="text-end">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detalles as $item)
                    <tr>
                        <td class="text-center">{{ number_format($item->Cantidad, 2) }}</td>
                        <td>UNIDAD</td> {{-- Asumimos UNIDAD, deberías tenerlo en Docdet --}}
                        <td>{{ $item->Codpro }}</td>
                        <td>
                            {{ $item->ProductoNombre }}
                            <br><small class="text-muted">Lote: {{ $item->Lote }} | Venc: {{ \Carbon\Carbon::parse($item->Vencimiento)->format('m/y') }}</small>
                        </td>
                        <td class="text-end">{{ number_format($item->Precio, 2) }}</td>
                        <td class="text-end">{{ number_format($item->Subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row mt-3">
                <div class="col-8">
                    {{-- Aquí iría la lógica para convertir el total a letras --}}
                    <strong>SON:</strong> 
                    <span>{{ strtoupper(App\Helpers\NumberToWords::convert($factura->Total, $factura->Moneda == 1 ? 'SOLES' : 'DÓLARES')) }}</span>
                </div>
                <div class="col-4">
                    <table class="table table-sm table-borderless totals-table">
                        <tr>
                            <td>Op. Gravada:</td>
                            <td class="text-end">S/ {{ number_format($factura->Subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td>IGV (18%):</td>
                            <td class="text-end">S/ {{ number_format($factura->Igv, 2) }}</td>
                        </tr>
                        <tr class="fs-5 fw-bold border-top">
                            <td>Importe Total:</td>
                            <td class="text-end">S/ {{ number_format($factura->Total, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="invoice-footer text-center text-muted">
            <p class="small">Esta es una representación impresa del documento electrónico.</p>
        </div>
    </div>
    
    {{-- (Necesitarás un Helper para convertir números a letras) --}}
    {{-- (Crea app/Helpers/NumberToWords.php) --}}

</body>
</html>