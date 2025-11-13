{{-- 
  Esta es la plantilla Blade que usa la clase 'LibroDiarioExport'.
  Es una tabla HTML simple que Excel puede interpretar.
--}}
<table>
    <thead>
        {{-- Fila 1: Título --}}
        <tr>
            <th colspan="8" style="font-size: 16px; font-weight: bold; text-align: center;">
                Libro Diario
            </th>
        </tr>
        {{-- Fila 2: Rango de Fechas --}}
        <tr>
            <th colspan="8" style="text-align: center;">
                Del: {{ $fechaInicio }} Al: {{ $fechaFin }}
            </th>
        </tr>
        {{-- Fila 3: Espacio --}}
        <tr></tr>
        {{-- Fila 4: Cabeceras de Datos --}}
        <tr>
            <th style="font-weight: bold; background-color: #f0f0f0;">Fecha</th>
            <th style="font-weight: bold; background-color: #f0f0f0;">Número</th>
            <th style="font-weight: bold; background-color: #f0f0f0;">Glosa Cabecera</th>
            <th style="font-weight: bold; background-color: #f0f0f0;">Cuenta</th>
            <th style="font-weight: bold; background-color: #f0f0f0;">Nombre Cuenta</th>
            <th style="font-weight: bold; background-color: #f0f0f0;">Concepto Detalle</th>
            <th style="font-weight: bold; background-color: #f0f0f0;">Debe</th>
            <th style="font-weight: bold; background-color: #f0f0f0;">Haber</th>
            <th style="font-weight: bold; background-color: #f0f0f0;">Usuario</th>
        </tr>
    </thead>
    <tbody>
        @forelse($asientos as $asiento)
            {{-- Usamos @forelse por si un asiento no tuviera detalles (raro pero posible) --}}
            @forelse($asiento->detalles as $detalle)
                <tr>
                    {{-- 
                        ¡CORRECCIÓN 1!
                        Nos aseguramos que la fecha no sea null antes de parsearla.
                    --}}
                    <td>{{ $asiento->fecha ? \Carbon\Carbon::parse($asiento->fecha)->format('Y-m-d') : 'SIN FECHA' }}</td>
                    
                    {{-- Si es la primera línea del asiento, mostramos la glosa, si no, la dejamos vacía --}}
                    @if ($loop->first)
                        <td>{{ $asiento->numero }}</td>
                        <td>{{ $asiento->glosa }}</td>
                    @else
                        <td></td>
                        <td></td>
                    @endif

                    <td>{{ $detalle->cuenta_contable }}</td>
                    
                    {{-- 
                        ¡CORRECCIÓN 2!
                        Usamos 'optional()' para evitar error si la cuenta fue borrada
                    --}}
                    <td>{{ optional($detalle->cuenta)->nombre ?? 'CUENTA NO ENCONTRADA' }}</td>
                    <td>{{ $detalle->concepto }}</td>
                    <td>{{ $detalle->debe }}</td>
                    <td>{{ $detalle->haber }}</td>

                    {{-- 
                        ¡¡NUEVA CORRECCIÓN!! (Esta es la que faltaba)
                        Usamos 'optional()' para evitar error si el usuario es null
                    --}}
                    @if ($loop->first)
                        <td>{{ optional($asiento->usuario)->usuario ?? 'SIN USUARIO' }}</td>
                    @else
                        <td></td>
                    @endif
                </tr>
            @empty
                {{-- Si un asiento no tiene detalles (raro), mostramos esto --}}
                <tr>
                    <td>{{ $asiento->fecha ? \Carbon\Carbon::parse($asiento->fecha)->format('Y-m-d') : 'SIN FECHA' }}</td>
                    <td>{{ $asiento->numero }}</td>
                    <td>{{ $asiento->glosa }}</td>
                    <td colspan="6" style="color: red;">(Asiento sin detalles)</td>
                </tr>
            @endforelse
        @empty
            {{-- Si no hay ningún asiento en el filtro --}}
            <tr>
                <td colspan="9" style="text-align: center;">No se encontraron asientos para el rango seleccionado.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="6" style="font-weight: bold; text-align: right;">TOTALES:</th>
            <th style="font-weight: bold; background-color: #f0f0f0;">{{ $totales['total_debe'] }}</th>
            <th style="font-weight: bold; background-color: #f0f0f0;">{{ $totales['total_haber'] }}</th>
            <th></th>
        </tr>
    </tfoot>
</table>