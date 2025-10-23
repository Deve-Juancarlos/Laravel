<?php

return [

    'defaults' => [
    'guard' => 'web',
    'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'acceso_web',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'acceso_web',
            'hash' => false,
        ],
    ],

    'providers' => [
        'acceso_web' => [
            'driver' => 'eloquent',
            'model' => App\Models\AccesoWeb::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'acceso_web',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],
];