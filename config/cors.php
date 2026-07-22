<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Production: pin ke domain spesifik, JANGAN gunakan wildcard '*'.
    | Dev: gunakan '*' hanya untuk localhost.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        '*', // Mobile apps (Android/iOS) do not send Origin header; wildcard needed for native clients
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'X-Correlation-ID',
        'X-Request-ID',
        'Accept',
    ],

    'exposed_headers' => [
        'X-Correlation-ID',
        'X-Request-ID',
    ],

    'max_age' => 86400,

    'supports_credentials' => false,

];
