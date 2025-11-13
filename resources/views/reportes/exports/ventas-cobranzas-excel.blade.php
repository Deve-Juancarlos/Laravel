
<table>
    <thead>
        <tr>
            <th style="font-weight: bold;">Mes</th>
            <th style="font-weight: bold;">Total Ventas (Facturado)</th>
            <th style="font-weight: bold;">Total Cobranzas (Ingreso)</th>
            <th style="font-weight: bold;">Brecha (Diferencia)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($datosTabla as $dato)
            @php
                $brecha = $dato['cobranzas'] - $dato['ventas'];
            @endphp
            <tr>
                <td>{{ $dato['mes'] }}</td>
                <td>{{ $dato['ventas'] }}</td>
                <td>{{ $dato['cobranzas'] }}</td>
                <td>{{ $brecha }}</td>
            </tr>
        @endforeach
        
        {{-- Fila de Totales --}}
        <tr>
            <td style="font-weight: bold;">TOTALES</td>
            <td style="font-weight: bold;">{{ $totales['totalVentas'] }}</td>
            <td style="font-weight: bold;">{{ $totales['totalCobranzas'] }}</td>
            <td style="font-weight: bold;">{{ $totales['totalBrecha'] }}</td>
        </tr>
    </tbody>
</table>