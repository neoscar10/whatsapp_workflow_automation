<?php

namespace App\Services\Campaign;

use App\Models\Campaign\Campaign;
use App\Jobs\Campaign\DispatchCampaignJob;
use Illuminate\Support\Facades\Log;

class CampaignSchedulerService
{
    /**
     * Dispatch all campaigns that are scheduled and due.
     */
    public function dispatchDueCampaigns(): int
    {
        $campaigns = Campaign::scheduledDue()->get();
        $count = 0;

        foreach ($campaigns as $campaign) {
            try {
                // Mark as queued immediately to prevent double dispatch
                $campaign->update(['status' => 'queued']);
                
                DispatchCampaignJob::dispatch($campaign->id);
                $count++;
            } catch (\Exception $e) {
                Log::error("Failed to dispatch scheduled campaign #{$campaign->id}: " . $e->getMessage());
                $campaign->update([
                    'status' => 'failed',
                    'failure_reason' => $e->getMessage()
                ]);
            }
        }

        return $count;
    }
}
