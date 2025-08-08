<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the HTTP client settings for your application.
    | This includes SSL certificate verification, timeouts, and other
    | HTTP client options.
    |
    */

    'client' => [
        'options' => [
            'verify' => env('HTTP_VERIFY_SSL', true) ? \Composer\CaBundle\CaBundle::getBundledCaBundlePath() : false,
            'timeout' => env('HTTP_TIMEOUT', 30),
            'connect_timeout' => env('HTTP_CONNECT_TIMEOUT', 10),
            'http_errors' => false,
            'allow_redirects' => [
                'max' => 5,
                'strict' => false,
                'referer' => true,
                'protocols' => ['http', 'https'],
            ],
            'headers' => [
                'User-Agent' => 'Spring-Glossy-Cosmetics/1.0',
                'Accept' => 'application/json, text/plain, */*',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate, br',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SSL Configuration
    |--------------------------------------------------------------------------
    |
    | SSL certificate verification settings for external API calls.
    |
    */

    'ssl' => [
        'verify' => env('HTTP_VERIFY_SSL', true),
        'ca_bundle_path' => env('CURL_CA_BUNDLE', \Composer\CaBundle\CaBundle::getBundledCaBundlePath()),
        'cert_path' => env('SSL_CERT_FILE'),
        'key_path' => env('SSL_KEY_FILE'),
        'passphrase' => env('SSL_PASSPHRASE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Retry settings for failed HTTP requests.
    |
    */

    'retry' => [
        'enabled' => env('HTTP_RETRY_ENABLED', true),
        'max_attempts' => env('HTTP_RETRY_MAX_ATTEMPTS', 3),
        'delay' => env('HTTP_RETRY_DELAY', 1000), // milliseconds
        'backoff_multiplier' => env('HTTP_RETRY_BACKOFF_MULTIPLIER', 2),
        'retry_on_status_codes' => [408, 429, 500, 502, 503, 504],
    ],

    /*
    |--------------------------------------------------------------------------
    | Proxy Configuration
    |--------------------------------------------------------------------------
    |
    | Proxy settings for HTTP requests (if needed).
    |
    */

    'proxy' => [
        'enabled' => env('HTTP_PROXY_ENABLED', false),
        'host' => env('HTTP_PROXY_HOST'),
        'port' => env('HTTP_PROXY_PORT'),
        'username' => env('HTTP_PROXY_USERNAME'),
        'password' => env('HTTP_PROXY_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Headers
    |--------------------------------------------------------------------------
    |
    | Default headers to be sent with all HTTP requests.
    |
    */

    'default_headers' => [
        'Content-Type' => 'application/json',
        'X-Requested-With' => 'XMLHttpRequest',
    ],

    /*
    |--------------------------------------------------------------------------
    | Service-Specific Configurations
    |--------------------------------------------------------------------------
    |
    | Configuration for specific external services.
    |
    */

    'services' => [
        'paystack' => [
            'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
            'timeout' => env('PAYSTACK_TIMEOUT', 30),
            'headers' => [
                'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
                'Cache-Control' => 'no-cache',
            ],
        ],
        'aws' => [
            'base_url' => env('AWS_ENDPOINT'),
            'timeout' => env('AWS_TIMEOUT', 30),
            'headers' => [
                'User-Agent' => 'Spring-Glossy-Cosmetics-AWS/1.0',
            ],
        ],
        'resend' => [
            'base_url' => env('RESEND_BASE_URL', 'https://api.resend.com'),
            'timeout' => env('RESEND_TIMEOUT', 30),
            'headers' => [
                'Authorization' => 'Bearer ' . env('RESEND_KEY'),
                'Content-Type' => 'application/json',
            ],
        ],
    ],

];
