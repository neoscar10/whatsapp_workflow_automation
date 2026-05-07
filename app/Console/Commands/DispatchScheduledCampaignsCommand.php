<?php

namespace App\Console\Commands;

use App\Services\Campaign\CampaignSchedulerService;
use Illuminate\Console\Command;

class DispatchScheduledCampaignsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:dispatch-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch WhatsApp campaigns that are scheduled and due.';

    /**
     * Execute the console command.
     */
    public function handle(CampaignSchedulerService $scheduler): void
    {
        $this->info('Checking for scheduled campaigns...');
        
        $dispatched = $scheduler->dispatchDueCampaigns();
        
        if ($dispatched > 0) {
            $this->info("Successfully dispatched {$dispatched} campaign(s).");
        } else {
            $this->info('No campaigns due for dispatch.');
        }
    }
}
