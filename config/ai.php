<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used by the
    | application. You may set this to any of the providers defined below.
    | Supported: "gemini", "openai", etc.
    |
    */
    'default' => env('AI_PROVIDER', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each AI provider you wish to
    | support. Each provider can specify its API key, model, and other
    | custom settings.
    |
    */
    'providers' => [
        
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-1.5-pro'),
        ],

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o'),
        ],

    ],
];
