<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the HTTP client options for your application.
    | This includes SSL certificate verification, timeouts, and other
    | HTTP client settings.
    |
    */

    'client' => [
        'options' => [
            'verify' => env('HTTP_VERIFY_SSL', true),
            'timeout' => env('HTTP_TIMEOUT', 30),
            'connect_timeout' => env('HTTP_CONNECT_TIMEOUT', 10),
            'http_errors' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SSL Certificate Verification
    |--------------------------------------------------------------------------
    |
    | Configure SSL certificate verification for HTTP requests.
    | When set to true, uses the system CA bundle for verification.
    |
    */

    'ssl' => [
        'verify' => env('HTTP_SSL_VERIFY', true),
        'ca_bundle_path' => env('HTTP_CA_BUNDLE_PATH', null),
    ],

];
