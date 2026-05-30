<?php

namespace App\Console\Commands;

use App\Services\Payment\Reconciliation\PendingPaymentReconciliationService;
use App\Services\Payment\Reconciliation\AbandonedPaymentService;
use App\Services\Payment\Reconciliation\StaleProcessingPaymentService;
use App\Events\Payment\ReconciliationRunCompleted;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcilePaymentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:reconcile {--stale-pending=15 : Minutes before a pending transaction is stale} {--stale-processing=30 : Minutes before a processing state is timed out} {--abandon-hours=24 : Hours before a transaction is abandoned}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run comprehensive payment transaction state reconciliation and cleanup.';

    /**
     * Execute the console command.
     */
    public function handle(
        PendingPaymentReconciliationService $pendingService,
        AbandonedPaymentService $abandonedService,
        StaleProcessingPaymentService $staleProcessingService
    ): int {
        $this->info("Starting multi-gateway payment reconciliation pipeline...");
        
        $stalePending = (int) $this->option('stale-pending');
        $staleProcessing = (int) $this->option('stale-processing');
        $abandonHours = (int) $this->option('abandon-hours');

        Log::info("Running payments:reconcile command", [
            'stale_pending' => $stalePending,
            'stale_processing' => $staleProcessing,
            'abandon_hours' => $abandonHours,
        ]);

        // 1. Recover stale processing states back to pending
        $this->info("Analyzing stale processing states...");
        $processingResult = $staleProcessingService->recover($staleProcessing);
        $this->line("-> Processing Stuck Recovered: " . $processingResult['total_recovered']);

        // 2. Query gateway api for status checks on stale pending orders
        $this->info("Querying gateway APIs for stale pending checkouts...");
        $pendingResult = $pendingService->reconcile($stalePending);
        $this->line("-> Pending Resolved: " . $pendingResult['total_resolved']);

        // 3. Mark long-running stale checkouts as abandoned/expired
        $this->info("Processing long-abandoned checkouts...");
        $abandonResult = $abandonedService->clean($abandonHours);
        $this->line("-> Marked Abandoned: " . $abandonResult['total_marked_abandoned']);

        $summary = [
            'stale_processing_recovered' => $processingResult['total_recovered'],
            'stale_pending_resolved' => $pendingResult['total_resolved'],
            'abandoned_cleaned' => $abandonResult['total_marked_abandoned'],
            'timestamp' => now()->toDateTimeString(),
        ];

        // Dispatch reconciliation completed telemetry event
        event(new ReconciliationRunCompleted($summary));

        $this->info("Multi-gateway payment reconciliation pipeline completed successfully!");
        return 0;
    }
}
