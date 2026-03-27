<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$template = \App\Models\WhatsApp\WhatsAppTemplate::where('remote_template_name', 'black_friday')->first();
$account = \App\Models\WhatsApp\WhatsAppAccount::first();

$output = "TEMPLATE: " . $template->remote_template_name . "\n";
$output .= "NAMESPACE (DB): " . ($template->namespace ?? 'NULL') . "\n";
$output .= "WABA ID (DB): " . ($account->waba_id ?? 'NULL') . "\n";
$output .= "PHONE ID (DB): " . ($account->phoneNumbers()->first()?->phone_number_id ?? 'NULL') . "\n";

file_put_contents('tmp/namespace_check.log', $output);
echo "Checked namespace in tmp/namespace_check.log\n";
