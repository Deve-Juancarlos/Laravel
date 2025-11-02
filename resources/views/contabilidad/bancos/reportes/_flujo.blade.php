<div class="row">
    <div class="col-md-6">
        <h6 class="text-success">Top 10 Ingresos</h6>
        <ul class="list-group">
        @forelse($datos['topIngresos'] as $mov)
            <li class="list-group-item d-flex justify-content-between">
                <span>{{ $mov->clase_descripcion }} ({{ $mov->Documento }})</span>
                <strong>S/ {{ number_format($mov->Monto, 2) }}</strong>
            </li>
        @empty
            <li class="list-group-item text-muted">No hay ingresos.</li>
        @endforelse
        </ul>
    </div>
    <div class="col-md-6">
        <h6 class="text-danger">Top 10 Egresos</h6>
        <ul class="list-group">
        @forelse($datos['topEgresos'] as $mov)
            <li class="list-group-item d-flex justify-content-between">
                <span>{{ $mov->clase_descripcion }} ({{ $mov->Documento }})</span>
                <strong>S/ {{ number_format($mov->Monto, 2) }}</strong>
            </li>
        @empty
            <li class="list-group-item text-muted">No hay egresos.</li>
        @endforelse
        </ul>
    </div>
</div>
