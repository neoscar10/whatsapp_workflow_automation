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
    Route::get('/company/verification', \App\Livewire\Web\Company\BusinessVerificationDashboard::class)->name('company.verification');
    Route::get('/chats', \App\Livewire\Web\Chats\ChatInboxPage::class)->name('chats.index');
    Route::get('/contacts', \App\Livewire\Web\Contacts\ContactIndexPage::class)->name('contacts.index');
    Route::get('/contacts/audiences', \App\Livewire\Contacts\AudienceManagerPage::class)->name('contacts.audiences');

    // Campaigns
    Route::group(['prefix' => 'campaigns'], function () {
        Route::get('/', \App\Livewire\Campaigns\CampaignIndexPage::class)->name('campaigns.index');
        Route::get('/{id}', \App\Livewire\Campaigns\CampaignShowPage::class)->name('campaigns.show');
    });

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

    // Automations
    Route::group(['prefix' => 'automations'], function () {
        Route::get('/', \App\Livewire\Web\Automations\AutomationsIndexPage::class)->name('automations.index');
        Route::get('/create', \App\Livewire\Web\Automations\AutomationBuilder::class)->name('automations.create');
        Route::get('/{id}/edit', \App\Livewire\Web\Automations\AutomationBuilder::class)->name('automations.edit');
        Route::get('/{id}/simulate', \App\Livewire\Web\Automations\AutomationSimulation::class)->name('automations.simulate');
    });
    Route::get('/panel', function () {
        return redirect()->route('dashboard');
    })->name('panel.home');

    // Chat media proxy — streams inbound WhatsApp media on-demand
    Route::get('/chat-media/{messageId}', [\App\Http\Controllers\Chat\ChatMediaProxyController::class, 'show'])->name('chat.media.proxy');

    // Secure Verification Document serving route
    Route::get('/company/verification/document-file/{versionId}', [\App\Http\Controllers\Company\VerificationFileController::class, 'show'])->name('company.verification.file');

    // Wallet Dashboard
    Route::get('/wallet', \App\Livewire\Wallet\WalletDashboard::class)->name('wallet.index');

    // Logout
    Route::post('/logout', function () {
        \Illuminate\Support\Facades\Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});

// Public Webhooks
Route::get('/webhooks/whatsapp/meta', [\App\Http\Controllers\Webhooks\WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.meta.verify');
Route::post('/webhooks/whatsapp/meta', [\App\Http\Controllers\Webhooks\WhatsAppWebhookController::class, 'receive'])->name('webhooks.whatsapp.meta.receive');
Route::post('/api/v1/automation/webhooks/{uuid}', [\App\Http\Controllers\Webhooks\AutomationWebhookController::class, 'handle'])->name('api.automation.webhook');

Route::get('/privacy-policy', function () {
    return view('pages.privacy');
})->name('privacy-policy');

// Super Admin Route Group
Route::middleware(['auth', 'super_admin'])->prefix('super-admin')->group(function () {
    Route::get('/dashboard', \App\Livewire\SuperAdmin\SuperAdminDashboard::class)->name('superadmin.dashboard');
    Route::get('/companies', \App\Livewire\SuperAdmin\CompanyIndex::class)->name('superadmin.companies');
    Route::get('/whatsapp-setup', \App\Livewire\SuperAdmin\SuperAdminWhatsAppSetup::class)->name('superadmin.whatsapp-setup');
    Route::get('/wallets', \App\Livewire\SuperAdmin\WalletIndex::class)->name('superadmin.wallets');
    Route::get('/funding', \App\Livewire\SuperAdmin\FundingConfig::class)->name('superadmin.funding');
    Route::get('/verification-templates', \App\Livewire\SuperAdmin\VerificationTemplateConfig::class)->name('superadmin.verification-templates');
    Route::get('/verification-queue', \App\Livewire\SuperAdmin\VerificationQueue::class)->name('superadmin.verification-queue');
    Route::get('/verification-queue/{id}', \App\Livewire\SuperAdmin\VerificationReviewWorkspace::class)->name('superadmin.verification-review');
    Route::get('/verification-queue/{id}/download-all', [\App\Http\Controllers\Company\VerificationFileController::class, 'downloadAll'])->name('superadmin.verification-review.download-all');
    Route::get('/modules', \App\Livewire\SuperAdmin\ModuleIndex::class)->name('superadmin.modules');
    Route::get('/company-modules', \App\Livewire\SuperAdmin\CompanyModuleAssignment::class)->name('superadmin.company-modules');
});

Route::middleware(['auth'])->get('/super-admin/stop-impersonating', function () {
    if (!session()->has('impersonator_user_id')) {
        return redirect()->route('dashboard');
    }

    $impersonatorId = session()->pull('impersonator_user_id');
    $superAdmin = \App\Models\User::find($impersonatorId);

    if ($superAdmin) {
        \Illuminate\Support\Facades\Auth::login($superAdmin);
        return redirect()->route('superadmin.companies');
    }

    return redirect()->route('login');
})->name('superadmin.stop-impersonating');