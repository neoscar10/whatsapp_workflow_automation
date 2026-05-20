<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Mobile Broadcasting Auth
    Broadcast::routes(['middleware' => ['auth:sanctum']]);

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('logout-all');
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('chats')->name('chats.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\Chat\ChatController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Api\V1\Chat\ChatController::class, 'store'])->name('store');
            Route::get('/{conversation}', [\App\Http\Controllers\Api\V1\Chat\ChatController::class, 'show'])->name('show');
            Route::post('/{conversation}/close', [\App\Http\Controllers\Api\V1\Chat\ChatController::class, 'close'])->name('close');
            Route::post('/{conversation}/reopen', [\App\Http\Controllers\Api\V1\Chat\ChatController::class, 'reopen'])->name('reopen');
            Route::post('/{conversation}/assign', [\App\Http\Controllers\Api\V1\Chat\ChatController::class, 'assign'])->name('assign');
            Route::post('/{conversation}/read', [\App\Http\Controllers\Api\V1\Chat\ChatMessageController::class, 'markRead'])->name('read');
            
            Route::prefix('{conversation}/messages')->name('messages.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\V1\Chat\ChatMessageController::class, 'index'])->name('index');
                Route::post('/text', [\App\Http\Controllers\Api\V1\Chat\ChatMessageController::class, 'sendText'])->name('text');
                Route::post('/media', [\App\Http\Controllers\Api\V1\Chat\ChatMessageController::class, 'sendMedia'])->name('media');
                Route::post('/template', [\App\Http\Controllers\Api\V1\Chat\ChatMessageController::class, 'sendTemplate'])->name('template');
            });
        });

        Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppTemplateController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppTemplateController::class, 'store'])->name('store');
                Route::post('/sync', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppTemplateController::class, 'sync'])->name('sync');
                Route::get('/{id}', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppTemplateController::class, 'show'])->name('show');
                Route::patch('/{id}', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppTemplateController::class, 'update'])->name('update');
                Route::delete('/{id}', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppTemplateController::class, 'destroy'])->name('destroy');

                // Helpers
                Route::get('/helpers/categories', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppHelperController::class, 'categories'])->name('helpers.categories');
                Route::get('/helpers/languages', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppHelperController::class, 'languages'])->name('helpers.languages');
            });

            Route::prefix('setup')->name('setup.')->group(function () {
                Route::get('/account', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppSetupController::class, 'account'])->name('account');
                Route::patch('/account', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppSetupController::class, 'updateAccount'])->name('account.update');
                
                Route::get('/phone-numbers', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppSetupController::class, 'phoneNumbers'])->name('phone-numbers.index');
                Route::post('/phone-numbers', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppSetupController::class, 'storePhoneNumber'])->name('phone-numbers.store');
                Route::patch('/phone-numbers/{id}', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppSetupController::class, 'updatePhoneNumber'])->name('phone-numbers.update');
                Route::post('/phone-numbers/{id}/toggle-status', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppSetupController::class, 'togglePhoneNumberStatus'])->name('phone-numbers.toggle-status');
            });
        });

        Route::prefix('contacts')->name('contacts.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\Contact\ContactController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Api\V1\Contact\ContactController::class, 'store'])->name('store');
            Route::post('/import', [\App\Http\Controllers\Api\V1\Contact\ContactController::class, 'import'])->name('import');
            Route::get('/import/template', [\App\Http\Controllers\Api\V1\Contact\ContactController::class, 'importTemplate'])->name('import-template');
            Route::get('/export', [\App\Http\Controllers\Api\V1\Contact\ContactController::class, 'export'])->name('export');
            Route::get('/{id}', [\App\Http\Controllers\Api\V1\Contact\ContactController::class, 'show'])->name('show');
            Route::patch('/{id}', [\App\Http\Controllers\Api\V1\Contact\ContactController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Api\V1\Contact\ContactController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/opt-in', [\App\Http\Controllers\Api\V1\Contact\ContactController::class, 'optIn'])->name('opt-in');
            Route::post('/{id}/opt-out', [\App\Http\Controllers\Api\V1\Contact\ContactController::class, 'optOut'])->name('opt-out');
            Route::post('/sync', [\App\Http\Controllers\Api\V1\Contact\ContactController::class, 'sync'])->name('sync');
        });

        Route::prefix('contact-tags')->name('contact-tags.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\Contact\ContactTagController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Api\V1\Contact\ContactTagController::class, 'store'])->name('store');
            Route::patch('/{id}', [\App\Http\Controllers\Api\V1\Contact\ContactTagController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Api\V1\Contact\ContactTagController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('contact-groups')->name('contact-groups.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\Contact\ContactGroupController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Api\V1\Contact\ContactGroupController::class, 'store'])->name('store');
            Route::patch('/{id}', [\App\Http\Controllers\Api\V1\Contact\ContactGroupController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Api\V1\Contact\ContactGroupController::class, 'destroy'])->name('destroy');

            // Membership Management
            Route::get('/{group}/available-contacts', [\App\Http\Controllers\Api\V1\Contact\AudienceGroupMemberController::class, 'availableContacts'])->name('available-contacts');
            Route::get('/{group}/members', [\App\Http\Controllers\Api\V1\Contact\AudienceGroupMemberController::class, 'members'])->name('members');
            Route::post('/{group}/members', [\App\Http\Controllers\Api\V1\Contact\AudienceGroupMemberController::class, 'storeMembers'])->name('members.store');
            Route::delete('/{group}/members', [\App\Http\Controllers\Api\V1\Contact\AudienceGroupMemberController::class, 'destroyMembers'])->name('members.destroy');
        });

        // Campaign Management
        Route::prefix('campaigns')->name('campaigns.')->group(function () {
            // CRUD
            Route::get('/', [\App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'show'])->name('show');
            Route::patch('/{id}', [\App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/content', [\App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'updateContent'])->name('update-content');

            // Audience
            Route::post('/audience/preview', [\App\Http\Controllers\Api\V1\Campaign\CampaignAudienceController::class, 'preview'])->name('audience.preview');
            Route::post('/{id}/audience', [\App\Http\Controllers\Api\V1\Campaign\CampaignAudienceController::class, 'sync'])->name('audience.sync');
            Route::post('/{id}/recipients/import', [\App\Http\Controllers\Api\V1\Campaign\CampaignAudienceController::class, 'import'])->name('recipients.import');

            // Recipients
            Route::get('/{id}/recipients', [\App\Http\Controllers\Api\V1\Campaign\CampaignRecipientController::class, 'index'])->name('recipients.index');
            Route::post('/{id}/recipients/{recipientId}/retry', [\App\Http\Controllers\Api\V1\Campaign\CampaignRecipientController::class, 'retry'])->name('recipients.retry');

            // Actions
            Route::post('/{id}/send', [\App\Http\Controllers\Api\V1\Campaign\CampaignActionController::class, 'send'])->name('send');
            Route::post('/{id}/schedule', [\App\Http\Controllers\Api\V1\Campaign\CampaignActionController::class, 'schedule'])->name('schedule');
            Route::post('/{id}/pause', [\App\Http\Controllers\Api\V1\Campaign\CampaignActionController::class, 'pause'])->name('pause');
            Route::post('/{id}/resume', [\App\Http\Controllers\Api\V1\Campaign\CampaignActionController::class, 'resume'])->name('resume');
            Route::post('/{id}/cancel', [\App\Http\Controllers\Api\V1\Campaign\CampaignActionController::class, 'cancel'])->name('cancel');
            Route::post('/{id}/duplicate', [\App\Http\Controllers\Api\V1\Campaign\CampaignActionController::class, 'duplicate'])->name('duplicate');
            Route::post('/{id}/retry-failed', [\App\Http\Controllers\Api\V1\Campaign\CampaignActionController::class, 'retryFailed'])->name('retry-failed');

            // Reports
            Route::get('/{id}/report/summary', [\App\Http\Controllers\Api\V1\Campaign\CampaignReportController::class, 'summary'])->name('report.summary');
            Route::get('/{id}/report/failures', [\App\Http\Controllers\Api\V1\Campaign\CampaignReportController::class, 'failures'])->name('report.failures');
            Route::get('/{id}/report/export', [\App\Http\Controllers\Api\V1\Campaign\CampaignReportController::class, 'export'])->name('report.export');

            // Helpers
            Route::prefix('helpers')->name('helpers.')->group(function () {
                Route::get('/templates', [\App\Http\Controllers\Api\V1\Campaign\CampaignHelperController::class, 'templates'])->name('templates');
                Route::get('/templates/{templateId}/variables', [\App\Http\Controllers\Api\V1\Campaign\CampaignHelperController::class, 'templateVariables'])->name('template-variables');
                Route::get('/personalization-fields', [\App\Http\Controllers\Api\V1\Campaign\CampaignHelperController::class, 'personalizationFields'])->name('personalization-fields');
            });
        });
    });
});
