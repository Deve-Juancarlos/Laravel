<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Cuenta</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; line-height: 1.4; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header h3 { margin: 0; font-size: 16px; color: #555; }
        .client-info { margin-bottom: 20px; }
        .client-info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-size: 12px; }
        .text-end { text-align: right; }
        .total-row { background-color: #f2f2f2; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $empresa['nombre'] ?? 'SEDIMCORP SAC' }}</h1>
            <h3>Estado de Cuenta del Cliente</h3>
        </div>

        <div class="client-info">
            <p><strong>Cliente:</strong> {{ $cliente->Razon }}</p>
            <p><strong>Código:</strong> {{ $cliente->Codclie }}</p>
            <p><strong>Fecha de Emisión:</strong> {{ date('d/m/Y') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Documento</th>
                    <th class->="text-end">Fecha Emisión</th>
                    <th class="text-end">Fecha Venc.</th>
                    <th class="text-end">Días Venc.</th>
                    <th class="text-end">Importe Total</th>
                    <th class="text-end">Saldo Pendiente</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deudaDetallada as $deuda)
                <tr>
                    <td>{{ $deuda->Documento }}</td>
                    <td class="text-end">{{ \Carbon\Carbon::parse($deuda->FechaF)->format('d/m/Y') }}</td>
                    <td class="text-end">{{ \Carbon\Carbon::parse($deuda->FechaV)->format('d/m/Y') }}</td>
                    <td class="text-end" style="{{ $deuda->dias_vencidos > 0 ? 'color: red; font-weight: bold;' : '' }}">
                        {{ $deuda->dias_vencidos }}
                    </td>
                    <td class="text-end">S/ {{ number_format($deuda->Importe, 2) }}</td>
                    <td class="text-end">S/ {{ number_format($deuda->Saldo, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No se encontraron deudas pendientes.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="text-end">SALDO TOTAL PENDIENTE:</td>
                    <td class="text-end">S/ {{ number_format($totalDeuda, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            Este es un documento generado por el sistema.
        </div>
    </div>
</body>
</html>