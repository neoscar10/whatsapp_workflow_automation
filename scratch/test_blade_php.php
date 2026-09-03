<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Blade;

echo "=== REAL PHP LINTING FOR COMPILER VIEWS ===\n";

$views = [
    'resources/views/livewire/campaigns/campaign-form-modal.blade.php',
    'resources/views/livewire/campaigns/campaign-wizard-page.blade.php',
    'resources/views/livewire/campaigns/campaign-show-page.blade.php',
];

foreach ($views as $v) {
    $fullPath = base_path($v);
    echo "\nChecking {$v}...\n";
    $content = file_get_contents($fullPath);
    $compiled = Blade::compileString($content);
    
    // Save to temp file and run php -l
    $tmpFile = sys_get_temp_dir() . '/test_blade_compile.php';
    file_put_contents($tmpFile, $compiled);
    
    $output = shell_exec("php -l " . escapeshellarg($tmpFile));
    echo $output;
}
