<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$clientCompliance = \Modules\CA\Models\CAClientCompliance::with([
            'clientRequirements.complianceRequirement',
            'deadlines',
            'timelines'
        ])->find(13);

$total = $clientCompliance->clientRequirements->count();
$approved = $clientCompliance->clientRequirements->where('status', 'approved')->count();
$inReview = $clientCompliance->clientRequirements->where('status', 'uploaded')->count();
$pending = $clientCompliance->clientRequirements->where('status', 'pending')->count();

echo "Total: $total, Approved: $approved, InReview: $inReview, Pending: $pending\n";

$deadlines = $clientCompliance->deadlines()->where('status', '!=', 'completed')->orderBy('due_date')->take(5)->get();
echo "Upcoming Deadlines: " . count($deadlines) . "\n";
foreach($deadlines as $d) {
    echo " - " . $d->deadline_name . " (due: " . $d->due_date->format('Y-m-d') . ")\n";
}

$timelines = $clientCompliance->timelines->take(5);
echo "Timelines: " . count($timelines) . "\n";

