<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'webhooks/whatsapp/*',
            'webhooks/whatsapp/meta',
            'webhooks/whatsapp/meta/',
            'api/v1/automation/webhooks/*',
            'api/v1/wallet/fund/*/verify',  // Secured by Razorpay HMAC signature instead
            'api/v1/webhooks/razorpay',
            'api/v1/webhooks/cashfree',
            'api/v1/webhooks/payu',
            'payu/callback',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\CheckCompanyDemoStatus::class,
        ]);

        $middleware->alias([
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'module' => \App\Http\Middleware\EnsureModuleEnabled::class,
        ]);

        // Allow Sanctum to authenticate API requests using the browser session cookie
        // (required for Livewire frontend fetch calls to auth:sanctum protected endpoints)
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
