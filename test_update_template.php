<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppTemplateService;
use Illuminate\Support\Facades\Http;

try {
    // Basic setup
    $company = Company::firstOrCreate(
        ['primary_email' => 'dev@example.com'],
        ['name' => 'Dev Company', 'slug' => 'dev-company', 'status' => 'active']
    );

    $user = User::firstOrCreate(
        ['email' => 'dev@example.com'],
        ['name' => 'Dev User', 'password' => bcrypt('password'), 'company_id' => $company->id]
    );
    
    $account = WhatsAppAccount::firstOrCreate(
        ['company_id' => $company->id],
        ['access_token' => 'fake_token', 'waba_id' => '123456789', 'business_id' => '987654321', 'connection_status' => 'connected']
    );

    $template = WhatsAppTemplate::firstOrCreate(
        ['company_id' => $company->id, 'remote_template_name' => 'draft_promo'],
        [
            'whatsapp_account_id' => $account->id,
            'remote_template_id' => '555444333',
            'display_title' => 'Draft Promo',
            'category' => 'marketing',
            'language_code' => 'en_US',
            'status' => 'rejected',
            'body_text' => 'Old text',
        ]
    );

    // Mock HTTP
    Http::fake([
        'https://graph.facebook.com/v21.0/555444333' => Http::response([
            'id' => '555444333',
            'status' => 'PENDING',
            'category' => 'MARKETING'
        ], 200)
    ]);

    // Service Call
    $service = app(WhatsAppTemplateService::class);
    
    $data = [
        'category' => 'marketing',
        'body_text' => 'New updated text',
        'language_code' => 'en_US',
        'remote_template_name' => 'draft_promo'
    ];
    
    $service->updateTemplateRecord($template, $account, $data, [], $user->id);

    echo "Update Service executed successfully.\n";

} catch (\Exception $e) {
    echo "ERROR CAUGHT:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
