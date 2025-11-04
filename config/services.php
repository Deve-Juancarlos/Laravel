<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    
    'reniec' => [
        'url' => env('RENIEC_API_URL', 'https://api.reniec.gob.pe'),
        'token' => env('RENIEC_API_TOKEN', null),
    ],

    'pse' => [
        // Esta es la URL de la API de tu proveedor (Nubefact, TCI, Efact, etc.)
        'url' => env('PSE_API_URL', 'https_://api.tu-proveedor.com'),
        
        // Este es tu Token o "llave secreta" para conectarte
        'token' => env('PSE_API_TOKEN', null),
    ],


];
