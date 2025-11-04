<?php
// config/contabilidad.php
return [
    'cuentas' => [

        'ventas' => [
            'factura_por_cobrar' => env('CTA_VENTA_FACTURA_COBRAR', '121201'), 
            'boleta_por_cobrar'  => env('CTA_VENTA_BOLETA_COBRAR', '121202'),
            'igv_por_pagar'      => env('CTA_VENTA_IGV', '401101'),      
            'ingreso_por_venta'  => env('CTA_VENTA_INGRESO', '701101'), 
        ],

        'costos' => [
            'costo_de_venta' => env('CTA_COSTO_VENTA', '691101'),
            'mercaderias'    => env('CTA_COSTO_MERCADERIA', '201101'), 
        ],

        'cobranzas' => [
            'factura_por_cobrar'  => env('CTA_COBRO_FACTURA', '121201'),
            'boleta_por_cobrar'   => env('CTA_COBRO_BOLETA', '121202'),
            'anticipo_clientes'   => env('CTA_COBRO_ANTICIPO', '122101'),
            'banco_default'       => env('CTA_COBRO_BANCO_DEFAULT', '104101'),
            'caja_default'        => env('CTA_COBRO_CAJA_DEFAULT', '101101'),  
        ],

        'anulaciones' => [
            // Asiento de Anulación de Cobranza (12 / 10)
            'cliente_por_cobrar' => env('CTA_ANUL_CLIENTE', '121201'), // Revierte la 12
            'banco'              => env('CTA_ANUL_BANCO', '104101'), // Revierte la 10
            
            
        ],
        
        'compras' => [
            'compras_mercaderia' => env('CTA_COMPRA_MERCADERIA', '601101'), 
            'igv_compras'        => env('CTA_COMPRA_IGV', '401101'),        
            'facturas_por_pagar' => env('CTA_COMPRA_POR_PAGAR', '421201'),  
            'almacen_mercaderia' => env('CTA_COMPRA_ALMACEN', '201101'),   
            'variacion_stock'    => env('CTA_COMPRA_VARIACION', '611101'), 
        ],
        
        // --- ¡NUEVA SECCIÓN PARA NOTAS DE CRÉDITO! ---
        'notas_credito' => [
            // Asiento de NC (DEBE)
            'devolucion_ventas'   => env('CTA_NC_DEVOLUCION', '704101'), // 70.4 Devoluciones sobre Ventas
            'descuento_ventas'    => env('CTA_NC_DESCUENTO', '675101'),  // 67.5 Descuentos Concedidos
            'igv_nc'              => env('CTA_NC_IGV', '401101'),        // 40.1.1 IGV (revierte el débito fiscal)
            
            // Asiento de NC (HABER)
            'cuenta_por_cobrar'   => env('CTA_NC_POR_COBRAR', '121201'), // 12.1.2 Facturas por Cobrar (reduce la deuda)
        ],

    ],
];