<h6 class="text-muted">Resumen por Banco</h6>
<div class="table-responsive">
    <table class="table table-sm table-hover table-striped">
        <thead class="table-light">
            <tr>
                <th>Banco</th>
                <th class="text-end">Ingresos</th>
                <th class="text-end">Egresos</th>
                <th class="text-end">Neto</th>
            </tr>
        </thead>
        <tbody>
        @forelse($datos as $banco)
        <tr>
            <td>{{ $banco['nombre'] }}</td>
            <td class="text-end text-success">S/ {{ number_format($banco['ingresos'], 2) }}</td>
            <td class="text-end text-danger">S/ {{ number_format($banco['egresos'], 2) }}</td>
            <td class="text-end fw-bold">S/ {{ number_format($banco['saldo_neto_periodo'], 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center p-3 text-muted">No hay datos.</td></tr>
        @endforelse
        </tbody>
        <tfoot class="table-dark">
            <tr>
                <td><strong>TOTAL</strong></td>
                <td class="text-end"><strong>S/ {{ number_format($totales['ingresos'], 2) }}</strong></td>
                <td class="text-end"><strong>S/ {{ number_format($totales['egresos'], 2) }}</strong></td>
                <td class="text-end"><strong>S/ {{ number_format($totales['ingresos'] - $totales['egresos'], 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>

