<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class VentaCarritoService
{
    protected $connection = 'sqlsrv';
    protected $sessionKey = 'venta_carrito';

    // Inicia un carrito nuevo
    public function iniciar($cliente)
    {
        $carrito = [
            'cliente' => $cliente,
            'items' => collect(), // Usamos colecciones de Laravel
            'pago' => [
                'tipo_doc' => 1, // 1 = Factura por defecto (de tu tabla Tablas)
                'condicion' => 'contado', // 'contado' o 'credito'
                'fecha_venc' => now()->addDays(30)->format('Y-m-d'),
                'vendedor_id' => $cliente->Vendedor ?? null,
                'moneda' => 1, // 1 = Soles (de tu tabla Tablas)
            ],
            'totales' => [
                'subtotal' => 0,
                'igv' => 0,
                'total' => 0,
            ]
        ];
        Session::put($this->sessionKey, $carrito);
        return $carrito;
    }

    public function get()
    {
        return Session::get($this->sessionKey);
    }

    // Añade un item (producto/lote) al carrito
    public function agregarItem($itemData)
    {
        $carrito = $this->get();
        if (!$carrito) return null;

        if (empty($itemData['codpro']) || empty($itemData['lote']) || !isset($itemData['cantidad']) || !isset($itemData['precio'])) {
            throw new \Exception("Datos del item incompletos.");
        }

        // Validar stock real en BD
        $stockLote = $this->getStockLote($itemData['codpro'], $itemData['lote']);
        if ($stockLote < $itemData['cantidad']) {
            throw new \Exception("Stock insuficiente para el lote {$itemData['lote']}. Solo quedan {$stockLote} unidades.");
        }

        // ID único (producto + lote)
        $itemId = $itemData['codpro'] . '-' . $itemData['lote'];

        if ($carrito['items']->has($itemId)) {
            $carrito['items'][$itemId]['cantidad'] = $itemData['cantidad']; // Actualiza
        } else {
            $carrito['items'][$itemId] = $itemData; // Añade
        }

        $this->actualizarTotales($carrito);
        return $carrito;
    }

    public function eliminarItem($itemId)
    {
        $carrito = $this->get();
        if (!$carrito) return null;
        $carrito['items']->forget($itemId);
        $this->actualizarTotales($carrito);
        return $carrito;
    }

    public function actualizarPago($pagoData)
    {
        $carrito = $this->get();
        if (!$carrito) return null;
        $carrito['pago'] = array_merge($carrito['pago'], $pagoData);
        Session::put($this->sessionKey, $carrito);
        return $carrito;
    }

    // Recalcula los totales
    private function actualizarTotales(&$carrito) // Pasa por referencia
    {
        $subtotal = 0;
        foreach ($carrito['items'] as $item) {
            $subtotal += $item['cantidad'] * $item['precio'];
        }
        
        // Asumiendo IGV 18% (Venta Grabada)
        $igv = $subtotal * 0.18; 
        $total = $subtotal + $igv;

        $carrito['totales']['subtotal'] = round($subtotal, 2);
        $carrito['totales']['igv'] = round($igv, 2);
        $carrito['totales']['total'] = round($total, 2);

        Session::put($this->sessionKey, $carrito);
    }
    
    // Función de ayuda para consultar stock real de un lote
    public function getStockLote($codPro, $lote)
    {
        $saldo = DB::connection($this->connection)->table('Saldos')
            ->where('codpro', $codPro)
            ->where('lote', $lote)
            ->where('saldo', '>', 0)
            ->first();
        return $saldo ? $saldo->saldo : 0;
    }

    public function olvidar()
    {
        Session::forget($this->sessionKey);
    }
}