<?php

use App\Livewire\Web\Auth\LoginPage;
use App\Livewire\Web\Auth\RegisterCompanyPage;
use App\Livewire\Web\Company\CompanyProfilePage;
use App\Livewire\Web\Dashboard\DashboardPage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');
Route::get('/register', RegisterCompanyPage::class)->name('company.register');

Route::middleware('guest')->group(function () {
    Route::get('/login', LoginPage::class)->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardPage::class)->name('dashboard');
    Route::get('/company/profile', CompanyProfilePage::class)->name('company.profile');
    Route::get('/chats', \App\Livewire\Web\Chats\ChatInboxPage::class)->name('chats.index');

    // WhatsApp Setup
    Route::group(['prefix' => 'whatsapp/setup'], function () {
        Route::get('/', function () {
            return redirect()->route('whatsapp.setup.phone-numbers');
        });
        Route::get('/phone-numbers', \App\Livewire\Web\WhatsApp\PhoneNumbersPage::class)->name('whatsapp.setup.phone-numbers');
        Route::get('/account', \App\Livewire\Web\WhatsApp\AccountSetupPage::class)->name('whatsapp.setup.account');
    });

    // WhatsApp Templates
    Route::group(['prefix' => 'whatsapp/templates'], function () {
        Route::get('/', \App\Livewire\Web\WhatsApp\TemplatesIndexPage::class)->name('whatsapp.templates.index');
        Route::get('/create', \App\Livewire\Web\WhatsApp\TemplateCreatePage::class)->name('whatsapp.templates.create');
        Route::get('/{id}', \App\Livewire\Web\WhatsApp\TemplateShowPage::class)->name('whatsapp.templates.show');
        Route::get('/{id}/edit', \App\Livewire\Web\WhatsApp\TemplateEditPage::class)->name('whatsapp.templates.edit');
    });
    Route::get('/panel', function () {
        return redirect()->route('dashboard');
    })->name('panel.home');
});

Route::get('/debug-db', function () {
    return [
        'webhook_events_count' => \Illuminate\Support\Facades\DB::table('whatsapp_webhook_events')->count(),
        'conversations_count' => \Illuminate\Support\Facades\DB::table('conversations')->count(),
        'messages_count' => \Illuminate\Support\Facades\DB::table('conversation_messages')->count(),
        'phone_numbers_count' => \App\Models\WhatsApp\WhatsAppPhoneNumber::count(),
        'accounts_count' => \App\Models\WhatsApp\WhatsAppAccount::count(),
        'latest_events' => \Illuminate\Support\Facades\DB::table('whatsapp_webhook_events')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($e) {
                return [
                    'id' => $e->id,
                    'event_type' => $e->event_type,
                    'processing_status' => $e->processing_status,
                    'payload' => json_decode($e->payload, true),
                    'created_at' => $e->created_at,
                ];
            }),
    ];
});

Route::get('/debug-route', function () {
    return 'ok';
});

// Public Webhooks
Route::get('/webhooks/whatsapp/meta', [\App\Http\Controllers\Webhooks\WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.meta.verify');
Route::post('/webhooks/whatsapp/meta', [\App\Http\Controllers\Webhooks\WhatsAppWebhookController::class, 'receive'])->name('webhooks.whatsapp.meta.receive');
Route::get('/webhooks/whatsapp/test-receive', [\App\Http\Controllers\Webhooks\WhatsAppWebhookController::class, 'receive']); // Manual test route

Route::get('/privacy-policy', function () {
    return view('pages.privacy');
})->name('privacy-policy');