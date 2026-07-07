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

    'whatsapp' => [
        'app_id' => env('WHATSAPP_APP_ID'),
        'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
        'simulator' => [
            'enabled' => env('WHATSAPP_SIMULATOR_ENABLED', false),
            'capture_outbound' => env('WHATSAPP_SIMULATOR_CAPTURE_OUTBOUND', true),
            'fake_phone_number_id' => env('WHATSAPP_SIMULATOR_FAKE_PHONE_NUMBER_ID', 'LOCAL_PHONE_NUMBER_ID'),
            'fake_waba_id' => env('WHATSAPP_SIMULATOR_FAKE_WABA_ID', 'SIMULATED_WABA_ID'),
        ],
    ],

];
