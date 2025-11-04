<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class NotaCreditoService
{
    protected $connection = 'sqlsrv';
    protected $sessionKey = 'nota_credito_carrito';

    /**
     * ¡CORRECTO!
     * Pide 3 argumentos, incluyendo el saldo.
     */
    public function iniciar($facturaOriginal, $cliente, $saldoMaximo) // <-- AÑADIMOS SALDO
    {
        $carrito = [
            'factura_original' => $facturaOriginal,
            'cliente' => $cliente,
            'saldo_maximo' => (float) $saldoMaximo, // <-- GUARDAMOS EL SALDO PENDIENTE
            'items_devueltos' => collect(),
            'motivo' => null,
            'tipo_operacion' => null,
            'totales' => [
                'subtotal' => 0, 'igv' => 0, 'total' => 0,
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
     * ¡CORRECTO!
     * Valida los totales contra el saldo máximo.
     */
    public function actualizarCarrito($tipoOperacion, $motivo, $items, $montoDescuento)
    {
        $carrito = $this->get();
        if (!$carrito) return null;
        
        $subtotalNC = 0;

        if ($tipoOperacion == 'devolucion') {
            $itemsProcesados = 0;
            $carrito['items_devueltos'] = collect(); 

            foreach ($items as $key => $item) {
                $item = (object)$item; 
                $cantidadDevuelta = (float)($item->cantidad ?? 0);
                if ($cantidadDevuelta <= 0) continue;
                $itemsProcesados++;
                
                $precio = (float)$item->precio;
                $subtotalItem = $cantidadDevuelta * $precio;
                $subtotalNC += $subtotalItem;
                
                $carrito['items_devueltos'][$key] = $item; 
            }
            if ($itemsProcesados == 0) throw new \Exception('No se especificó ninguna cantidad a devolver.');
        
        } elseif ($tipoOperacion == 'descuento') {
            // Tu vista 'crear_paso2' (la que me pasaste) pide el TOTAL (con IGV)
            $totalNC = (float)$montoDescuento;
            if ($totalNC <= 0) throw new \Exception('El monto de descuento debe ser mayor a 0.');
            
            // ¡¡VALIDACIÓN CLAVE!!
            if ($totalNC > $carrito['saldo_maximo']) {
                throw new \Exception("El monto total de la NC (S/ {$totalNC}) es mayor al saldo pendiente de la factura (S/ {$carrito['saldo_maximo']}).");
            }

            $subtotalNC = round($totalNC / 1.18, 2); // Calculamos el subtotal
            $carrito['items_devueltos'] = collect();
        }

        // Calcular totales (si fue devolución)
        if ($tipoOperacion == 'devolucion') {
            $igvNC = round($subtotalNC * 0.18, 2);
            $totalNC = round($subtotalNC + $igvNC, 2);

            // ¡¡VALIDACIÓN CLAVE (Devolución)!!
            if ($totalNC > $carrito['saldo_maximo']) {
                throw new \Exception("El monto total de la NC (S/ {$totalNC}) es mayor al saldo pendiente de la factura (S/ {$carrito['saldo_maximo']}).");
            }
        }
        
        // Actualizar carrito en sesión
        $carrito['tipo_operacion'] = $tipoOperacion;
        $carrito['motivo'] = $motivo;
        $carrito['totales'] = [
            'subtotal' => $subtotalNC,
            'igv' => round($subtotalNC * 0.18, 2),
            'total' => $totalNC,
        ];
        Session::put($this->sessionKey, $carrito);
        return $carrito;
    }

    public function olvidar()
    {
        Session::forget($this->sessionKey);
    }
}