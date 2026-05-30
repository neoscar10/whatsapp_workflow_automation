<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway driver that will be used
    | for payment operations. Supported options: "razorpay", "cashfree"
    |
    */

    'default' => env('PAYMENT_GATEWAY', 'razorpay'),

    /*
    |--------------------------------------------------------------------------
    | Currency Configurations
    |--------------------------------------------------------------------------
    |
    | The default ISO-4217 currency code used by the system for payment and
    | wallet calculations.
    |
    */

    'currency' => env('PAYMENT_CURRENCY', 'INR'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configurations
    |--------------------------------------------------------------------------
    |
    | Here you can configure credentials and options for the payment gateways
    | supported by the application.
    |
    */

    'gateways' => [

        'razorpay' => [
            'key_id' => env('RAZORPAY_KEY_ID', ''),
            'key_secret' => env('RAZORPAY_KEY_SECRET', ''),
            'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),
        ],

        'cashfree' => [
            'enabled' => env('CASHFREE_ENABLED', true),
            'app_id' => env('CASHFREE_APP_ID', ''),
            'secret_key' => env('CASHFREE_SECRET_KEY', ''),
            'environment' => env('CASHFREE_ENV', 'sandbox'), // sandbox or production
            'webhook_secret' => env('CASHFREE_WEBHOOK_SECRET', ''),
            'timeout' => (int) env('CASHFREE_TIMEOUT', 30),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook & Queue Configurations
    |--------------------------------------------------------------------------
    |
    | Configuration for payment webhook retries, processing queues, and logging.
    |
    */
    'webhook' => [
        'queue' => env('PAYMENT_WEBHOOK_QUEUE', 'default'),
        'tries' => env('PAYMENT_WEBHOOK_TRIES', 3),
        'backoff' => [10, 30, 60],
        'timeout' => env('PAYMENT_WEBHOOK_TIMEOUT', 60),
    ],

];

