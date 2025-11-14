<table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; font-size:12px; width:100%;">
  <thead>
    <tr>
      <th colspan="7" style="font-size: 16px; font-weight: bold; text-align: center; background: #e1eefe;">LIBRO DIARIO - {{ config('app.name', 'SEIMCORP') }}</th>
    </tr>
    <tr>
      <th colspan="7" style="font-weight: bold;">Reporte del {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</th>
    </tr>
    <tr>
      <th style="background-color:#D9EAD3;border:1px solid #000;">Fecha</th>
      <th style="background-color:#D9EAD3;border:1px solid #000;">Asiento</th>
      <th style="background-color:#D9EAD3;border:1px solid #000;">Cuenta</th>
      <th style="background-color:#D9EAD3;border:1px solid #000;">Nombre Cuenta</th>
      <th style="background-color:#D9EAD3;border:1px solid #000;">Concepto</th>
      <th style="background-color:#D9EAD3;border:1px solid #000;">Debe</th>
      <th style="background-color:#D9EAD3;border:1px solid #000;">Haber</th>
    </tr>
  </thead>
  <tbody>
    @foreach($asientos as $asiento)
      @foreach($asiento->detalles as $detalle)
        <tr>
          <td>{{ \Carbon\Carbon::parse($asiento->fecha)->format('d/m/Y') }}</td>
          <td>{{ $asiento->numero }}</td>
          <td>{{ $detalle->cuenta_contable }}</td>
          <td>{{ $detalle->cuenta->nombre ?? 'N/A' }}</td>
          <td>{{ $detalle->concepto }}</td>
          <td>{{ $detalle->debe > 0 ? number_format($detalle->debe,2) : '' }}</td>
          <td>{{ $detalle->haber > 0 ? number_format($detalle->haber,2) : '' }}</td>
        </tr>
      @endforeach
      <tr style="background-color: #f3f3f3;">
        <td colspan="4"></td>
        <td style="font-weight:bold;text-align:right;">Total Asiento {{ $asiento->numero }}:</td>
        <td style="font-weight:bold;border-top:1px solid #000;">{{ $asiento->total_debe }}</td>
        <td style="font-weight:bold;border-top:1px solid #000;">{{ $asiento->total_haber }}</td>
      </tr>
      <tr><td colspan="7"></td></tr>
    @endforeach
    <tr><td colspan="7"></td></tr>
    <tr style="background-color: #D9EAD3;">
      <td colspan="4"></td>
      <td style="font-weight:bold;font-size:14px;text-align:right;">TOTAL GENERAL:</td>
      <td style="font-weight:bold;font-size:14px;border-top:2px solid #000;">{{ $totales['total_debe'] }}</td>
      <td style="font-weight:bold;font-size:14px;border-top:2px solid #000;">{{ $totales['total_haber'] }}</td>
    </tr>
  </tbody>
</table>
