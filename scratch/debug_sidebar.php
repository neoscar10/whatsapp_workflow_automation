<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$reqs = \Modules\CA\Models\CAClientComplianceRequirement::where('ca_client_compliance_id', 13)->get();
echo 'Requirements: ' . count($reqs) . PHP_EOL;

$deadlines = \Modules\CA\Models\CAClientComplianceDeadline::where('ca_client_compliance_id', 13)->get();
echo 'Deadlines: ' . count($deadlines) . PHP_EOL;

$timelines = \Modules\CA\Models\CAComplianceTimeline::where('ca_client_compliance_id', 13)->get();
echo 'Timelines: ' . count($timelines) . PHP_EOL;
