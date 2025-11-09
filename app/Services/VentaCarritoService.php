<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log; // <-- Añadido para Loggear

class VentaCarritoService
{
    protected $connection = 'sqlsrv';
    protected $sessionKey = 'venta_carrito';

    // Inicia un carrito nuevo
    public function iniciar($cliente)
    {
        $carrito = [
            'cliente' => $cliente,
            'items' => collect(),
            'pago' => [
                'tipo_doc' => 1,
                'condicion' => 'contado',
                'fecha_venc' => now()->addDays(30)->format('Y-m-d'),
                'vendedor_id' => $cliente->Vendedor ?? null,
                'moneda' => 1,
            ],
            // ¡CORREGIDO! Inicializa todos los totales
            'totales' => [
                'subtotal_bruto' => 0,
                'descuento_total' => 0,
                'subtotal' => 0, // Subtotal Neto
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

    /**
     * ¡CORREGIDO!
     * Ahora SÍ guarda el 'vencimiento' en todas las operaciones.
     */
    public function agregarItem($itemData)
    {
        $carrito = $this->get();
        if (!$carrito) return null;

        if (empty($itemData['codpro']) || empty($itemData['lote']) || !isset($itemData['cantidad']) || !isset($itemData['precio'])) {
            throw new \Exception("Datos del item incompletos.");
        }
        
        // Validación de Vencimiento (evita el NULL de raíz)
        if (empty($itemData['vencimiento']) || $itemData['vencimiento'] == 'N/A') {
             Log::warning("Vencimiento NULO detectado para {$itemData['codpro']} Lote {$itemData['lote']}. Usando fecha actual.");
             $itemData['vencimiento'] = now()->format('Y-m-d'); // Fallback
        }

        $stockLote = $this->getStockLote($itemData['codpro'], $itemData['lote']);
        if ($stockLote < $itemData['cantidad']) {
            throw new \Exception("Stock insuficiente para el lote {$itemData['lote']}. Solo quedan {$stockLote}");
        }

        $itemId = $itemData['codpro'] . '-' . $itemData['lote'];
        $itemData['descuento'] = (float)($itemData['descuento'] ?? 0);

        // ¡¡¡LÓGICA CORREGIDA!!!
        // La forma más simple y segura es simplemente "sobrescribir" el item.
        // No necesitamos la lógica 'if/else' porque $itemData ya tiene toda la info.
        $carrito['items']->put($itemId, $itemData);

        // (Si quisiéramos mantener la lógica de "get" y "put")
        // if ($carrito['items']->has($itemId)) {
        //     $item = $carrito['items']->get($itemId);
        //     $item['cantidad'] = $itemData['cantidad'];
        //     $item['precio'] = $itemData['precio'];
        //     $item['descuento'] = $itemData['descuento'];
        //     $item['vencimiento'] = $itemData['vencimiento']; // <-- ESTA ERA LA LÍNEA FALTANTE
        //     $carrito['items']->put($itemId, $item);
        // } else {
        //     $carrito['items']->put($itemId, $itemData);
        // }

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

    // Recalcula los totales (Tu código está perfecto aquí)
    private function actualizarTotales(&$carrito)
    {
        $subtotal_bruto = 0;
        $descuento_total = 0;
        $subtotal_neto = 0;

        foreach ($carrito['items'] as $item) {
            $precio_bruto_item = $item['cantidad'] * $item['precio'];
            $descuento_item = $precio_bruto_item * ($item['descuento'] / 100); // Asumimos %
            
            $subtotal_item_neto = $precio_bruto_item - $descuento_item;

            $subtotal_bruto += $precio_bruto_item;
            $descuento_total += $descuento_item;
            $subtotal_neto += $subtotal_item_neto;
        }
        
        $igv = $subtotal_neto * 0.18;
        $total = $subtotal_neto + $igv;

        $carrito['totales']['subtotal_bruto'] = round($subtotal_bruto, 2);
        $carrito['totales']['descuento_total'] = round($descuento_total, 2);
        $carrito['totales']['subtotal'] = round($subtotal_neto, 2); // Subtotal Neto
        $carrito['totales']['igv'] = round($igv, 2);
        $carrito['totales']['total'] = round($total, 2);

        Session::put($this->sessionKey, $carrito);
    }
    
    // Función de ayuda para consultar stock (Tu código está perfecto aquí)
    public function getStockLote($codPro, $lote)
    {
        // Usamos SUM() como en tu código, es más seguro.
        return (float) DB::connection($this->connection)
            ->table('Saldos')
            ->where('codpro', $codPro)
            ->where('lote', $lote)
            ->where('saldo', '>', 0)
            ->sum('saldo'); 
    }

    public function olvidar()
    {
        Session::forget($this->sessionKey);
    }
}