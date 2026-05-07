<?php

namespace App\Console\Commands;

use App\Services\Contact\ContactSyncService;
use Illuminate\Console\Command;

class SyncContactsFromConversationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contacts:sync-from-conversations {--company= : Sync contacts for a specific company ID} {--dry-run : Run without writing to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill contacts table from existing conversations data';

    /**
     * Execute the console command.
     */
    public function handle(ContactSyncService $syncService)
    {
        $companyId = $this->option('company');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info("Running in DRY-RUN mode. No changes will be saved.");
        }

        $this->info("Starting contact sync from conversations...");

        $stats = $syncService->backfillFromConversations($companyId ? (int)$companyId : null, $dryRun);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Scanned Conversations', $stats['scanned']],
                ['Contacts Created', $stats['created']],
                ['Contacts Updated', $stats['updated']],
                ['Conversations Linked', $stats['linked']],
                ['Skipped (Invalid/Empty Phone)', $stats['skipped']],
                ['Errors', count($stats['errors'])],
            ]
        );

        if (!empty($stats['errors'])) {
            if ($this->confirm('Do you want to see the error details?')) {
                foreach ($stats['errors'] as $error) {
                    $this->error($error);
                }
            }
        }

        $this->info("Sync completed successfully!");
    }
}
