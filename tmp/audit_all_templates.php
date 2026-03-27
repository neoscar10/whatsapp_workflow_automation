<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$templates = \App\Models\WhatsApp\WhatsAppTemplate::all();
$output = "TOTAL TEMPLATES: " . $templates->count() . "\n\n";

foreach ($templates as $t) {
    $output .= "NAME: " . $t->remote_template_name . "\n";
    $output .= "BODY: " . $t->body_text . "\n";
    
    $matches = [];
    preg_match_all('/\{\{([^}]+)\}\}/', $t->body_text, $matches);
    $vars = array_unique($matches[1] ?? []);
    
    $output .= "VARS: " . json_encode($vars) . "\n";
    $output .= "-----------------------------------\n";
}

file_put_contents('tmp/all_templates_audit.log', $output);
echo "Audited " . $templates->count() . " templates to tmp/all_templates_audit.log\n";
