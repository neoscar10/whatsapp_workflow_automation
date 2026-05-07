<?php

namespace App\Jobs\Campaign;

use App\Models\Campaign\Campaign;
use App\Services\Campaign\CampaignDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $campaignId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(CampaignDispatchService $dispatchService): void
    {
        $campaign = Campaign::find($this->campaignId);
        
        if (!$campaign) {
            return;
        }

        $dispatchService->dispatchCampaign($campaign);
    }
}
