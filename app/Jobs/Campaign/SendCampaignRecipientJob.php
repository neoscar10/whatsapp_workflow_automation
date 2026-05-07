<?php

namespace App\Jobs\Campaign;

use App\Models\Campaign\CampaignRecipient;
use App\Services\Campaign\CampaignDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCampaignRecipientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $recipientId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(CampaignDispatchService $dispatchService): void
    {
        $recipient = CampaignRecipient::find($this->recipientId);
        
        if (!$recipient) {
            return;
        }

        $dispatchService->sendRecipient($recipient);
    }
}
