<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$template = \App\Models\WhatsApp\WhatsAppTemplate::where('remote_template_name', 'hello_world')->first();
if (!$template) {
    echo "Template 'hello_world' not found.\n";
    exit;
}
$output = "TEMPLATE: " . $template->remote_template_name . "\n";
$output .= "BODY: " . $template->body_text . "\n";
$output .= "META: " . json_encode($template->meta_payload, JSON_PRETTY_PRINT) . "\n";

file_put_contents('tmp/hello_world_dump.log', $output);
echo "Dumped to tmp/hello_world_dump.log\n";
