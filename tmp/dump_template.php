<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$template = \App\Models\WhatsApp\WhatsAppTemplate::where('remote_template_name', 'black_friday')->first();
$output = "TEMPLATE: " . $template->remote_template_name . "\n";
$output .= "BODY: " . $template->body_text . "\n";
$output .= "HEADER: " . $template->header_text . "\n";
$output .= "FOOTER: " . $template->footer_text . "\n";
$output .= "META: " . json_encode($template->meta_payload, JSON_PRETTY_PRINT) . "\n";

file_put_contents('tmp/template_dump.log', $output);
echo "Dumped to tmp/template_dump.log\n";
