<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CompraCarritoService
{
    protected $connection = 'sqlsrv';
    protected $sessionKey = 'compra_carrito';

    // Inicia un carrito nuevo
    public function iniciar($proveedor)
    {
        $carrito = [
            'proveedor' => $proveedor,
            'items' => collect(),
            'pago' => [
                'moneda' => 1, // Soles
                'fecha_entrega' => now()->addDays(3)->format('Y-m-d'),
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

    // Añade un item (producto) al carrito
    public function agregarItem($itemData)
    {
        $carrito = $this->get();
        if (!$carrito) return null;

        if (empty($itemData['codpro']) || !isset($itemData['cantidad']) || !isset($itemData['costo'])) {
            throw new \Exception("Datos del item incompletos.");
        }

        $itemId = $itemData['codpro']; // ID es solo el CodPro

        if ($carrito['items']->has($itemId)) {
            $carrito['items'][$itemId]['cantidad'] = $itemData['cantidad'];
            $carrito['items'][$itemId]['costo'] = $itemData['costo'];
        } else {
            $carrito['items'][$itemId] = $itemData;
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

    private function actualizarTotales(&$carrito)
    {
        $subtotal = 0;
        foreach ($carrito['items'] as $item) {
            $subtotal += $item['cantidad'] * $item['costo'];
        }

        // En una ORDEN DE COMPRA no aplicamos IGV
        $igv = 0;
        $total = $subtotal; // Total igual al subtotal

        $carrito['totales']['subtotal'] = round($subtotal, 2);
        $carrito['totales']['igv'] = round($igv, 2);
        $carrito['totales']['total'] = round($total, 2);

        Session::put($this->sessionKey, $carrito);
    }


    public function olvidar()
    {
        Session::forget($this->sessionKey);
    }
} 