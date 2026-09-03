<?php

namespace App\Console\Commands;

use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignRecipient;
use App\Services\Campaign\CampaignDispatchService;
use App\Services\Campaign\CampaignService;
use Illuminate\Console\Command;

class ProcessStuckCampaignsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:process-stuck {--campaign= : Optional Campaign ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process queued or sending campaign recipients that have stalled or are stuck.';

    /**
     * Execute the console command.
     */
    public function handle(CampaignDispatchService $dispatchService, CampaignService $campaignService): int
    {
        $campaignId = $this->option('campaign');

        $query = CampaignRecipient::whereIn('status', ['queued', 'sending', 'pending'])
            ->where('attempts', 0);

        if ($campaignId) {
            $query->where('campaign_id', $campaignId);
        }

        $stuckRecipients = $query->get();

        if ($stuckRecipients->isEmpty()) {
            $this->info("No stuck campaign recipients found.");
            return Command::SUCCESS;
        }

        $this->info("Found " . $stuckRecipients->count() . " stuck recipient(s). Processing inline...");

        foreach ($stuckRecipients as $recipient) {
            $this->line("Processing Recipient #{$recipient->id} ({$recipient->phone})...");
            try {
                $dispatchService->sendRecipient($recipient);
                $this->info("  -> Done. Status: {$recipient->fresh()->status}");
            } catch (\Exception $e) {
                $this->error("  -> Error: " . $e->getMessage());
            }
        }

        // Recalculate stats for affected campaigns
        $campaignIds = $stuckRecipients->pluck('campaign_id')->unique();
        foreach ($campaignIds as $cId) {
            $campaign = Campaign::find($cId);
            if ($campaign) {
                $campaignService->recalculateStats($campaign);
            }
        }

        $this->info("All stuck campaign recipients processed successfully.");
        return Command::SUCCESS;
    }
}
