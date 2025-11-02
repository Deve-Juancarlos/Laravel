<div class="table-responsive">
    <table class="table table-sm table-hover table-striped">
        <thead class="table-light">
            <tr>
                <th>Banco</th>
                <th class="text-end">Total Movs.</th>
                <th class="text-end">Conciliados</th>
                <th class="text-end">Pendientes</th>
                <th class="text-end">% Conciliado</th>
                <th class="text-end">Diferencia</th>
            </tr>
        </thead>
        <tbody>
        @forelse($datos['porBancoConc'] as $banco)
        <tr>
            <td>{{ $banco['banco'] }}</td>
            <td class="text-end">{{ $banco['total'] }}</td>
            <td class="text-end">{{ $banco['conciliados'] }}</td>
            <td class="text-end">{{ $banco['pendientes'] }}</td>
            <td class="text-end">{{ number_format($banco['porcentaje'], 2) }}%</td>
            <td class="text-end fw-bold">S/ {{ number_format($banco['diferencia'], 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center p-3 text-muted">No hay datos.</td></tr>
        @endforelse
        </tbody>
        <tfoot class="table-dark">
            <tr>
                <td><strong>TOTAL</strong></td>
                <td class="text-end"><strong>{{ collect($datos['porBancoConc'])->sum('total') }}</strong></td>
                <td class="text-end"><strong>{{ $datos['conciliados'] }}</strong></td>
                <td class="text-end"><strong>{{ $datos['pendientes'] }}</strong></td>
                <td class="text-end"><strong>{{ number_format($datos['porcentaje'], 2) }}%</strong></td>
                <td class="text-end"><strong>S/ {{ number_format(collect($datos['porBancoConc'])->sum('diferencia'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>

