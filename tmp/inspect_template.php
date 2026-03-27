<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$template = \App\Models\WhatsApp\WhatsAppTemplate::where('remote_template_name', 'black_friday')->first();

if (!$template) {
    echo "Template 'black_friday' not found.\n";
    exit;
}

echo "--- TEMPLATE DEBUG ---\n";
echo "Name: " . $template->remote_template_name . "\n";
echo "Header Type: " . $template->header_type . "\n";
echo "Header Text: " . $template->header_text . "\n";
echo "Body Text: " . $template->body_text . "\n";
echo "Footer Text: " . $template->footer_text . "\n";
echo "Meta Payload: " . json_encode($template->meta_payload, JSON_PRETTY_PRINT) . "\n";

$matches = [];
preg_match_all('/\{\{([^}]+)\}\}/', $template->body_text, $matches);
echo "Body Vars: " . json_encode(array_unique($matches[1] ?? [])) . "\n";

preg_match_all('/\{\{([^}]+)\}\}/', $template->header_text, $matches);
echo "Header Vars: " . json_encode(array_unique($matches[1] ?? [])) . "\n";
