<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$wizard = new \Modules\CA\Livewire\ClientOnboardingWizard();
$wizard->business_type_id = 1; // Proprietorship or whatever
$wizard->loadIntelligence();

echo "Loaded intelligence.\n";
echo "Master Deadlines count: " . \Modules\CA\Models\CAComplianceDeadline::count() . "\n";
