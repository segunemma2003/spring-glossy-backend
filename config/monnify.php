<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monnify Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Monnify payment gateway.
    | You can get these credentials from your Monnify dashboard.
    |
    */

    'public_key' => env('MONNIFY_PUBLIC_KEY'),
    'secret_key' => env('MONNIFY_SECRET_KEY'),
    'merchant_id' => env('MONNIFY_MERCHANT_ID'),
    'contract_code' => env('MONNIFY_CONTRACT_CODE'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Set to 'production' for live transactions or 'sandbox' for testing.
    |
    */
    'environment' => env('MONNIFY_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | API URLs
    |--------------------------------------------------------------------------
    |
    | Base URLs for Monnify API endpoints.
    |
    */
    'base_url' => env('MONNIFY_ENVIRONMENT', 'sandbox') === 'production'
        ? 'https://api.monnify.com'
        : 'https://sandbox-api.monnify.com',

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Webhook settings for payment notifications.
    |
    */
    'webhook_url' => env('MONNIFY_WEBHOOK_URL', '/api/webhooks/monnify'),

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Available payment methods for Monnify.
    |
    */
    'payment_methods' => [
        'CARD',
        'ACCOUNT_TRANSFER',
        'USSD',
        'WALLET',
        'BANK_TRANSFER'
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Default currency for transactions.
    |
    */
    'currency' => 'NGN',

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Request timeout in seconds.
    |
    */
    'timeout' => 30,
];
