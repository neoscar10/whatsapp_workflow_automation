<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign\Campaign;
use App\Services\Campaign\CampaignService;
use App\Services\Campaign\CampaignReportService;
use App\Services\Campaign\CampaignDispatchService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CampaignShowPage extends Component
{
    use WithPagination;

    public $campaignId;
    public $search = '';
    public $status = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function mount($id)
    {
        $this->campaignId = $id;
    }

    public function getCampaignProperty()
    {
        return Campaign::forCompany(Auth::user()->company_id)
            ->with(['whatsappTemplate', 'creator'])
            ->findOrFail($this->campaignId);
    }

    public function retryFailed(CampaignDispatchService $dispatchService)
    {
        $campaign = $this->campaign;
        try {
            $dispatchService->retryFailed($campaign);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Retry jobs dispatched.']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function exportReport(CampaignReportService $reportService)
    {
        return $reportService->exportRecipientsCsv(Auth::user(), $this->campaign, [
            'search' => $this->search,
            'status' => $this->status,
        ]);
    }

    public function render(CampaignReportService $reportService)
    {
        $campaign = $this->campaign;
        
        $recipients = $reportService->listRecipients(Auth::user(), $campaign, [
            'search' => $this->search,
            'status' => $this->status,
            'per_page' => 20,
        ]);

        $summary = $reportService->getSummary(Auth::user(), $campaign);

        return view('livewire.campaigns.campaign-show-page', [
            'campaign' => $campaign,
            'recipients' => $recipients,
            'summary' => $summary,
        ])->layout('layouts.panel', ['title' => 'Campaign Report', 'activeNav' => 'campaigns']);
    }
}
