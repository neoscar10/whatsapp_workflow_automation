<?php

namespace App\Console\Commands;

use App\Models\CompanyVerificationDocumentVersion;
use App\Models\CompanyVerificationTimeline;
use App\Services\Verification\VerificationWorkflowService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MonitorVerificationExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verification:monitor-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor company verification documents and handle upcoming and occurred expiries.';

    /**
     * Execute the console command.
     */
    public function handle(VerificationWorkflowService $workflowService)
    {
        $today = Carbon::today();

        // 1. Process Expired Documents
        $expiredVersions = CompanyVerificationDocumentVersion::where('status', 'approved')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', $today)
            ->get();

        foreach ($expiredVersions as $version) {
            $doc = $version->document;
            $verification = $doc->verification;

            // Log timeline event
            CompanyVerificationTimeline::create([
                'company_verification_id' => $verification->id,
                'event_type' => 'document_expired',
                'title' => 'Document Expired',
                'description' => "Approved document {$doc->documentType->name} has expired on {$version->expiry_date->format('Y-m-d')}.",
                'metadata' => ['document_name' => $doc->documentType->name, 'expiry_date' => $version->expiry_date->format('Y-m-d')],
            ]);

            // Re-check and update status
            $workflowService->recalculateStatus($verification);
            $this->info("Handled expiry for document: {$doc->documentType->name} (Company ID: {$verification->company_id})");
        }

        // 2. Process Impending Expiries (30, 14, 7 days)
        $thresholds = [30, 14, 7];
        foreach ($thresholds as $days) {
            $targetDate = Carbon::today()->addDays($days);
            
            $expiringVersions = CompanyVerificationDocumentVersion::where('status', 'approved')
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', $targetDate)
                ->get();

            foreach ($expiringVersions as $version) {
                $doc = $version->document;
                $verification = $doc->verification;

                CompanyVerificationTimeline::create([
                    'company_verification_id' => $verification->id,
                    'event_type' => 'document_expiring_soon',
                    'title' => "Document Expiring in {$days} Days",
                    'description' => "Approved document {$doc->documentType->name} will expire on {$version->expiry_date->format('Y-m-d')}.",
                    'metadata' => ['document_name' => $doc->documentType->name, 'days_left' => $days, 'expiry_date' => $version->expiry_date->format('Y-m-d')],
                ]);

                $this->info("Logged warning: {$doc->documentType->name} expires in {$days} days (Company ID: {$verification->company_id})");
            }
        }
    }
}
