<?php
// Cleanup: keep only the FIRST (oldest) requirement named GST Sales Invoices per client compliance
$groups = \Modules\CA\Models\CAClientComplianceRequirement::where('name', 'GST Sales Invoices')
    ->orderBy('id')
    ->get()
    ->groupBy('ca_client_compliance_id');

$deleted = 0;
foreach ($groups as $complianceId => $reqs) {
    if ($reqs->count() <= 1) continue;
    // Keep the first (oldest), delete the rest
    $toDelete = $reqs->slice(1)->pluck('id');
    \Modules\CA\Models\CAClientComplianceRequirement::whereIn('id', $toDelete)->delete();
    $deleted += $toDelete->count();
    echo "Cleaned {$toDelete->count()} duplicates for client_compliance_id={$complianceId}, kept ID={$reqs->first()->id}\n";
}

echo "Total deleted: {$deleted}\n";
echo "Remaining GST Sales Invoices requirements: " . \Modules\CA\Models\CAClientComplianceRequirement::where('name', 'GST Sales Invoices')->count() . "\n";

// Also reset the one we kept to pending so it shows correctly
\Modules\CA\Models\CAClientComplianceRequirement::where('name', 'GST Sales Invoices')
    ->update(['status' => 'pending', 'next_due_date' => now()->toDateString()]);

echo "Reset remaining requirements to pending.\n";
