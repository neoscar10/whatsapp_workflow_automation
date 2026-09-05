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
            session()->flash('success', 'All failed campaign recipients have been re-queued for sending.');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Retry jobs dispatched.']);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public ?array $selectedErrorDetails = null;

    public function showErrorDetails(int $recipientId)
    {
        $recipient = \App\Models\Campaign\CampaignRecipient::where('campaign_id', $this->campaignId)
            ->findOrFail($recipientId);

        $this->selectedErrorDetails = [
            'id' => $recipient->id,
            'name' => $recipient->name ?: 'Unknown',
            'phone' => $recipient->phone,
            'status' => $recipient->status,
            'attempts' => $recipient->attempts,
            'last_attempted_at' => $recipient->last_attempted_at?->format('Y-m-d H:i:s') ?? 'N/A',
            'error_code' => $recipient->meta_error_code ?: 'N/A',
            'error_message' => $recipient->meta_error_message ?: 'No explicit error message provided by Meta API.',
            'skip_reason' => $recipient->skip_reason,
        ];
    }

    public function closeErrorModal()
    {
        $this->selectedErrorDetails = null;
    }

    public function retrySingleRecipient(int $recipientId, CampaignDispatchService $dispatchService)
    {
        try {
            $recipient = \App\Models\Campaign\CampaignRecipient::where('campaign_id', $this->campaignId)
                ->findOrFail($recipientId);
            $dispatchService->retryRecipient($recipient);
            $this->closeErrorModal();
            session()->flash('success', 'Recipient ' . ($recipient->name ?: $recipient->phone) . ' has been re-queued for retry.');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Recipient queued for retry.']);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
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
