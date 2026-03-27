<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$syncService = app(\App\Services\WhatsApp\WhatsAppTemplateService::class);
$accounts = \App\Models\WhatsApp\WhatsAppAccount::all();

foreach ($accounts as $account) {
    echo "Syncing templates for Account: " . $account->id . " (WABA: " . $account->waba_id . ")\n";
    try {
        $result = $syncService->syncTemplatesFromMeta($account);
        echo "Success: " . $result['status'] . "\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
