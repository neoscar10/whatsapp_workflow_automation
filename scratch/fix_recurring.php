<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$reqs = \Modules\CA\Models\CAClientComplianceRequirement::all();
$count = 0;
foreach ($reqs as $req) {
    $master = \Modules\CA\Models\CAComplianceRequirement::find($req->ca_compliance_requirement_id);
    if ($master && $master->is_recurring) {
        $req->is_recurring = true;
        $req->save();
        $count++;
    }
}
echo "Fixed {$count} recurring requirements.\n";
