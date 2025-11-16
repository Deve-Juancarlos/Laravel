<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompraService
{
    protected $connection = 'sqlsrv';

    public function registrarCompra($nroFactura, $proveedorId, $fechaEmision, $fechaVencimiento, $items, $totales, $ordenId)
    {
        $compraId = DB::connection($this->connection)->table('CompraCab')->insertGetId([
            'Serie' => substr($nroFactura, 0, 4),
            'Numero' => substr($nroFactura, 5),
            'TipoDoc' => '01',
            'CodProv' => $proveedorId,
            'FechaEmision' => $fechaEmision,
            'FechaVencimiento' => $fechaVencimiento,
            'BaseAfecta' => $totales['subtotal'],
            'Igv' => $totales['igv'],
            'Total' => $totales['total'],
            'Estado' => 'REGISTRADA',
            'OrdenCompraId' => $ordenId,
            'created_at' => now(),
        ]);

        // Registrar detalles
        foreach ($items as $item) {
            DB::connection($this->connection)->table('CompraDet')->insert([
                'CompraId' => $compraId,
                'CodPro' => $item['codpro'],
                'Cantidad' => $item['cantidad'],
                'CostoUnitario' => $item['costo'],
                'Subtotal' => $item['cantidad'] * $item['costo'],
                'Lote' => $item['lote'],
                'Vencimiento' => $item['vencimiento'],
            ]);
        }

        return $compraId;
    }
}

