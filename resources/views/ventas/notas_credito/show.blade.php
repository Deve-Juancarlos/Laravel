<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Crédito: {{ $notaCredito->Numero }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* (Todo tu CSS está perfecto, no se cambia nada) */
        body { background-color: #f5f5f5; font-family: Arial, sans-serif; font-size: 13px; padding: 20px; }
        .invoice-container { max-width: 800px; margin: 0 auto; background: white; padding: 0; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .controls { margin-bottom: 15px; text-align: left; }
        .controls a, .controls button { margin-right: 10px; }
        .header-box { border: 2px solid #000; padding: 15px; margin: 20px; text-align: center; }
        .header-box h5 { margin: 5px 0; font-weight: bold; font-size: 14px; }
        .header-box h4 { margin: 5px 0; font-weight: bold; font-size: 16px; }
        .section-title { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 8px 15px; margin: 20px 20px 0 20px; font-weight: bold; font-size: 13px; }
        .section-content { padding: 15px 20px; border-left: 1px solid #dee2e6; border-right: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6; margin: 0 20px 20px 20px; }
        .section-content p { margin: 3px 0; font-size: 13px; }
        .section-content strong { font-weight: 600; }
        .doc-modify-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; text-align: center; }
        .doc-modify-item { padding: 10px 5px; }
        .doc-modify-item small { display: block; color: #6c757d; font-size: 11px; text-transform: uppercase; margin-bottom: 5px; }
        .doc-modify-item span { display: block; font-weight: 500; font-size: 13px; }
        table.table-detail { width: 100%; font-size: 12px; }
        table.table-detail thead { background-color: #f8f9fa; }
        table.table-detail th { padding: 10px 8px; font-weight: 600; border-bottom: 2px solid #dee2e6; font-size: 12px; }
        table.table-detail td { padding: 10px 8px; border-bottom: 1px solid #dee2e6; }
        table.table-detail tbody tr:last-child td { border-bottom: none; }
        .son-text { margin-top: 15px; padding: 10px 20px; }
        .son-text strong { display: block; font-size: 11px; color: #6c757d; margin-bottom: 5px; }
        .son-text p { font-weight: bold; margin: 0; }
        .saldo-box { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 15px 20px; border-radius: 4px; }
        .saldo-box h6 { color: #155724; font-weight: bold; font-size: 13px; margin-bottom: 10px; }
        .saldo-box p { margin: 5px 0; font-size: 12px; color: #155724; }
        .totals-section { padding: 15px 20px; }
        .totals-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 13px; }
        .totals-row.total-final { border-top: 2px solid #000; margin-top: 10px; padding-top: 10px; font-weight: bold; font-size: 16px; color: #dc3545; }
        @media print {
            body { background: white; padding: 0; }
            .controls { display: none; }
            .invoice-container { box-shadow: none; max-width: 100%; }
        }
    </style>
</head>
<body>
    
    <div class="controls">
        <a href="{{ route('contador.notas-credito.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a NC
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-primary">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>

    <div class="invoice-container">
        
        <div class="header-box">
            <h5>R.U.C. {{ $empresa['ruc'] }}</h5>
            <h4>NOTA DE CRÉDITO</h4>
            <h4>{{ $notaCredito->Numero }}</h4>
        </div>

        <div class="section-title">DATOS DEL ADQUIRIENTE (CLIENTE)</div>
        <div class="section-content">
            <p><strong>Señor(es):</strong></p>
            <p>{{ $cliente->RazonSocial }}</p>
            <p><strong>R.U.C./D.N.I.:</strong></p>
            <p>{{ $cliente->Ruc }}</p>
            <p><strong>Dirección:</strong></p>
            <p>{{ $cliente->Direccion }}</p>
        </div>

        <div class="section-title">DATOS DEL DOCUMENTO QUE SE MODIFICA</div>
        <div class="section-content">
            <div class="doc-modify-grid">
                <div class="doc-modify-item">
                    <small>DOCUMENTO</small>
                    <span>{{ $facturaAfectada->Tipo == 1 ? 'FACTURA' : 'BOLETA' }} ELECTRÓNICA</span>
                </div>
                <div class="doc-modify-item">
                    <small>NÚMERO</small>
                    <span>{{ $facturaAfectada->Numero }}</span>
                </div>
                <div class="doc-modify-item">
                    <small>FECHA EMISIÓN (Fact.)</small>
                    <span>{{ \Carbon\Carbon::parse($facturaAfectada->Fecha)->format('d/m/Y') }}</span>
                </div>
                <div class="doc-modify-item">
                    <small>MOTIVO (Glosa)</small>
                    <span>{{ $notaCredito->Observacion }}</span>
                </div>
            </div>
        </div>

        <div class="section-title">DETALLE DE LA NOTA DE CRÉDITO</div>
        <div class="section-content">
            <table class="table-detail">
                <thead>
                    <tr>
                        <th>CÓDIGO</th>
                        <th>DESCRIPCIÓN</th>
                        <th class="text-center">CANT.</th>
                        <th class="text-end">V. UNIT.</th>
                        <th class="text-end">IMPORTE</th>
                    </tr>
                </thead>
                <tbody>
                    @if($detalles->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center">Descuento Global aplicado a la factura.</td>
                        </tr>
                    @else
                        @foreach($detalles as $item)
                        <tr>
                            <td>{{ $item->Codpro }}</td>
                            <td>
                                {{ $item->ProductoNombre }}
                                <br><small class="text-muted">Lote: {{ $item->Lote }}</small>
                            </td>
                            <td class="text-center">{{ number_format($item->Cantidad, 2) }}</td>
                            <td class="text-end">{{ number_format($item->Precio, 2) }}</td>
                            <td class="text-end">{{ number_format($item->Subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <div class="son-text">
            <strong>SON:</strong>
            <p>{{ $totalEnLetras }}</p>
        </div>

        <div class="saldo-box">
            <h6>Impacto en Cuentas por Cobrar</h6>
            <p>Esta Nota de Crédito se aplicó directamente al saldo de la factura original.</p>
            <p><strong>Factura Afectada:</strong> {{ $facturaAfectada->Numero }}</p>
            <p><strong>Nuevo Saldo Pendiente (Factura):</strong> S/ {{ number_format($saldoFacturaOriginal, 2) }}</p>
        </div>

        <div class="totals-section">
            <div class="totals-row">
                <span>Op. Gravada:</span>
                <span>S/ {{ number_format($notaCredito->Monto, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>IGV (18%):</span>
                <span>S/ {{ number_format($notaCredito->Igv, 2) }}</span>
            </div>
            <div class="totals-row total-final">
                <span>IMPORTE TOTAL (NC):</span>
                <span>S/ {{ number_format($notaCredito->Total, 2) }}</span>
            </div>
        </div>

    </div>

</body>
</html>