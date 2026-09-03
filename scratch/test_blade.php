<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Blade;

echo "=== TESTING BLADE COMPILATION FOR campaign-form-modal.blade.php ===\n";

try {
    $path = resource_path('views/livewire/campaigns/campaign-form-modal.blade.php');
    $content = file_get_contents($path);
    $compiled = Blade::compileString($content);
    echo "SUCCESS: campaign-form-modal.blade.php compiled cleanly without Blade syntax errors!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
