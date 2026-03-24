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
            ->limit(5)
            ->get(),
        'accounts_list' => \Illuminate\Support\Facades\DB::table('whatsapp_accounts')
            ->get(['id', 'company_id', 'waba_id']),
        'phone_numbers_list' => \Illuminate\Support\Facades\DB::table('whatsapp_phone_numbers')
            ->get(['id', 'whatsapp_account_id', 'phone_number_id', 'phone_number']),
        'user_company_id' => auth()->check() ? auth()->user()->company_id : 'guest',
        'identification_test' => (function() {
            $event = \Illuminate\Support\Facades\DB::table('whatsapp_webhook_events')
                ->where('processing_status', 'pending')
                ->where('event_type', 'whatsapp_business_account')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$event) return 'No pending event found';
            
            $payload = json_decode($event->payload, true);
            $entry = $payload['entry'][0] ?? [];
            $wabaId = $entry['id'] ?? null;
            $value = $entry['changes'][0]['value'] ?? [];
            $pnId = $value['metadata']['phone_number_id'] ?? null;
            
            $service = app(\App\Services\WhatsApp\WhatsAppWebhookEventService::class);
            
            // We need a reflection or just copy the logic since it's protected
            $query = \App\Models\WhatsApp\WhatsAppAccount::query();
            if ($pnId) {
                $query->whereHas('phoneNumbers', function ($q) use ($pnId) {
                    $q->where('phone_number_id', $pnId);
                });
            } elseif ($wabaId) {
                $query->where('waba_id', $wabaId);
            }
            $account = $query->first();
            
            return [
                'extracted_waba_id' => $wabaId,
                'extracted_pn_id' => $pnId,
                'found_account_id' => $account->id ?? 'NOT FOUND',
                'found_account_company' => $account->company_id ?? 'N/A',
            ];
        })(),
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