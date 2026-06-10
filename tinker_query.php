<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$reqs = \Modules\CA\Models\CAComplianceRequirement::all();

$found = [];
foreach($reqs as $r) {
    $found[] = [
        'name' => $r->name,
        'requirement_type' => $r->requirement_type,
        'input_type' => $r->input_type,
    ];
}

echo json_encode($found, JSON_PRETTY_PRINT);
