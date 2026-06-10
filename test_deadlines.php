<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = \Modules\CA\Models\CAClientCompliance::find(2); // Client 2
if ($c) {
    dump("Found client compliance: " . $c->id);
    dump("Compliance ID: " . $c->compliance->id);
    dump("Master Deadlines count: " . $c->compliance->complianceDeadlines->count());
    
    app(\Modules\CA\Services\DeadlineService::class)->generateDeadlines($c);
    
    dump("Client Deadlines count after generation: " . $c->deadlines->count());
} else {
    dump("Not found");
}
