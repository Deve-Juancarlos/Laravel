<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Datos de Empresa - SEDIMCORP SAC
    |--------------------------------------------------------------------------
    |
    | Estos datos se utilizan para generar XML y PDF para SUNAT
    | Datos reales de la empresa extraídos de las facturas
    |
    */

    'ruc' => env('EMPRESA_RUC', '20123456789'),
    'nombre' => env('EMPRESA_NOMBRE', 'SEDIMCORP SAC'),
    'nombre_comercial' => env('EMPRESA_NOMBRE_COMERCIAL', 'SEDIMCORP'),
    'giro' => env('EMPRESA_GIRO', 'EMPRESA DE DISTRIBUIDORA DE FARMACOS'),
    'direccion' => env('EMPRESA_DIRECCION', 'AV. LOS HEROES 754 OTR. D SAN JUAN DE MIRAFLORES - LIMA - LIMA'),
    'telefono' => env('EMPRESA_TELEFONO', '(01) 555-1234'),
    'email' => env('EMPRESA_EMAIL', 'ventas@sedimcorp.com'),
    'web' => env('EMPRESA_WEB', 'www.sedimcorp.com'),
    'logo' => env('EMPRESA_LOGO', '/images/Logo.png'),

    /*
    |--------------------------------------------------------------------------
    | Configuración de Firma Digital
    |--------------------------------------------------------------------------
    */
    'certificado' => [
        'ruta' => env('CERTIFICADO_RUTA', ''),
        'password' => env('CERTIFICADO_PASSWORD', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de SUNAT
    |--------------------------------------------------------------------------
    */
    'sunat' => [
        'usuario' => env('SUNAT_USUARIO', ''),
        'password' => env('SUNAT_PASSWORD', ''),
        'endpoint' => env('SUNAT_ENDPOINT', 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'),
        'ambiente' => env('SUNAT_AMBIENTE', 'produccion'), // pruebas o produccion
    ],

    /*
    |--------------------------------------------------------------------------
    | Moneda por defecto
    |--------------------------------------------------------------------------
    */
    'moneda' => env('EMPRESA_SOLES', 'PEN'),
];
