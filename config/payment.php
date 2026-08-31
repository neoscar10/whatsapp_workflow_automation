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

        'payu' => [
            'enabled' => env('PAYU_ENABLED', true),
            'key' => env('PAYU_KEY', 'iXlXRj'),
            'salt' => env('PAYU_SALT', 'BBjqi1WE5JutJvmAdkxMQomMowCW4OAP'),
            'client_id' => env('PAYU_CLIENT_ID', '0da6323d60b0aa8ddea5a18fe79b7374c5de4a756828d4bf0faeb22036421c78'),
            'client_secret' => env('PAYU_CLIENT_SECRET', '0fe032a4c3b14fa7d142597abaf53beaf96135c59f633dafaa8f9f1e09fa05aa'),
            'environment' => env('PAYU_ENV', 'test'), // test or production
            'webhook_secret' => env('PAYU_WEBHOOK_SECRET', ''),
            'timeout' => (int) env('PAYU_TIMEOUT', 30),
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

