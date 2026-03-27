<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$template = \App\Models\WhatsApp\WhatsAppTemplate::where('remote_template_name', 'black_friday')->first();
if ($template) {
    echo "ID: " . $template->id . "\n";
    echo "ACCOUNT: " . $template->whatsapp_account_id . "\n";
    echo "NAME: " . $template->remote_template_name . "\n";
} else {
    echo "NOT FOUND\n";
}
