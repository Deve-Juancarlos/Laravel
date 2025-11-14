<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class VentaCarritoService
{
    protected $connection = 'sqlsrv';
    protected $sessionKey = 'venta_carrito';

    
    public function iniciar($cliente)
    {
        
        if (!$cliente || !isset($cliente->Codclie)) {
            throw new \InvalidArgumentException("Cliente inválido. Debe tener código de cliente.");
        }
        
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
            'totales' => [
                'subtotal_bruto' => 0,
                'descuento_total' => 0,
                'subtotal' => 0,
                'igv' => 0,
                'total' => 0,
            ]
        ];
        
        Session::put($this->sessionKey, $carrito);
        
        Log::info("Carrito iniciado para cliente {$cliente->Codclie}", [
            'cliente' => $cliente->Razon ?? 'Sin nombre',
            'vendedor' => $cliente->Vendedor ?? 'Sin vendedor'
        ]);
        
        return $carrito;
    }

    public function get()
    {
        return Session::get($this->sessionKey);
    }

    
    public function agregarItem($itemData)
    {
        $carrito = $this->get();
        if (!$carrito) {
            throw new \Exception("No hay carrito activo. Inicie una venta primero.");
        }

        // Validaciones
        if (empty($itemData['codpro'])) {
            throw new \InvalidArgumentException("El código del producto es obligatorio.");
        }
        
        if (empty($itemData['lote'])) {
            throw new \InvalidArgumentException("El lote es obligatorio.");
        }
        
        if (!isset($itemData['cantidad']) || $itemData['cantidad'] <= 0) {
            throw new \InvalidArgumentException("La cantidad debe ser mayor a 0.");
        }
        
        if (!isset($itemData['precio']) || $itemData['precio'] < 0) {
            throw new \InvalidArgumentException("El precio no puede ser negativo.");
        }
        
        $itemData['descuento'] = (float)($itemData['descuento'] ?? 0);
        if ($itemData['descuento'] < 0 || $itemData['descuento'] > 100) {
            throw new \InvalidArgumentException("El descuento debe estar entre 0 y 100%.");
        }

        if (empty($itemData['vencimiento']) || $itemData['vencimiento'] == 'N/A') {
            Log::warning("Vencimiento NULO para {$itemData['codpro']} Lote {$itemData['lote']}. Usando fecha actual.");
            $itemData['vencimiento'] = now()->format('Y-m-d');
        }

        $stockLote = $this->getStockLote($itemData['codpro'], $itemData['lote']);
        if ($stockLote < $itemData['cantidad']) {
            throw new \DomainException(
                "Stock insuficiente para {$itemData['nombre']} (Lote: {$itemData['lote']}). " .
                "Disponible: {$stockLote}, Solicitado: {$itemData['cantidad']}"
            );
        }

        $itemId = $itemData['codpro'] . '-' . $itemData['lote'];
        $carrito['items']->put($itemId, $itemData);

        $this->actualizarTotales($carrito);
        return $carrito;
    }

    public function eliminarItem($itemId)
    {
        $carrito = $this->get();
        if (!$carrito) {
            throw new \Exception("No hay carrito activo.");
        }
        
        $carrito['items']->forget($itemId);
        $this->actualizarTotales($carrito);
        
        return $carrito;
    }

    public function actualizarPago($pagoData)
    {
        $carrito = $this->get();
        if (!$carrito) {
            throw new \Exception("No hay carrito activo.");
        }
        
        if (isset($pagoData['tipo_doc']) && !in_array($pagoData['tipo_doc'], [1, 3])) {
            throw new \InvalidArgumentException("Tipo de documento inválido. Debe ser 1 (Factura) o 3 (Boleta).");
        }
        
        if (isset($pagoData['condicion']) && !in_array($pagoData['condicion'], ['contado', 'credito'])) {
            throw new \InvalidArgumentException("Condición de pago inválida. Debe ser 'contado' o 'credito'.");
        }
        
        if (isset($pagoData['condicion']) && $pagoData['condicion'] == 'credito') {
            if (empty($pagoData['fecha_venc'])) {
                throw new \InvalidArgumentException("La fecha de vencimiento es obligatoria para ventas a crédito.");
            }
            
            if (strtotime($pagoData['fecha_venc']) < strtotime('today')) {
                throw new \InvalidArgumentException("La fecha de vencimiento no puede ser anterior a hoy.");
            }
        }
        
        if (isset($pagoData['moneda']) && !in_array($pagoData['moneda'], [1, 2])) {
            throw new \InvalidArgumentException("Moneda inválida. Debe ser 1 (Soles) o 2 (Dólares).");
        }
        
        $carrito['pago'] = array_merge($carrito['pago'], $pagoData);
        Session::put($this->sessionKey, $carrito);
        
        return $carrito;
    }

   
    private function actualizarTotales(&$carrito)
    {
        $subtotal_bruto = 0;
        $descuento_total = 0;
        $subtotal_neto = 0;

        foreach ($carrito['items'] as $item) {
            $precio_bruto_item = $item['cantidad'] * $item['precio'];
            $descuento_item = $precio_bruto_item * ($item['descuento'] / 100);
            $subtotal_item_neto = $precio_bruto_item - $descuento_item;

            $subtotal_bruto += $precio_bruto_item;
            $descuento_total += $descuento_item;
            $subtotal_neto += $subtotal_item_neto;
        }
        
        $igv = $subtotal_neto * 0.18;
        $total = $subtotal_neto + $igv;

        $carrito['totales']['subtotal_bruto'] = round($subtotal_bruto, 2);
        $carrito['totales']['descuento_total'] = round($descuento_total, 2);
        $carrito['totales']['subtotal'] = round($subtotal_neto, 2);
        $carrito['totales']['igv'] = round($igv, 2);
        $carrito['totales']['total'] = round($total, 2);

        Session::put($this->sessionKey, $carrito);
    }
    
    public function getStockLote($codPro, $lote)
    {
        $stock = DB::connection($this->connection)
            ->table('Saldos')
            ->where('codpro', $codPro)
            ->where('lote', $lote)
            ->where('saldo', '>', 0)
            ->sum('saldo');
        
        return (float)($stock ?? 0);
    }

    public function olvidar()
    {
        Session::forget($this->sessionKey);
    }
}
