<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign\Campaign;
use App\Services\Campaign\CampaignService;
use App\Services\Campaign\CampaignDispatchService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class CampaignIndexPage extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $type = '';
    public $campaignToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'type' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sendNow($id, CampaignService $service, CampaignDispatchService $dispatchService)
    {
        $campaign = $service->findForCompany(Auth::user(), $id);
        
        try {
            $service->update(Auth::user(), $campaign, ['status' => 'queued']);
            $dispatchService->dispatchCampaign($campaign);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Campaign queued for sending.'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function pause($id, CampaignService $service)
    {
        $campaign = $service->findForCompany(Auth::user(), $id);
        try {
            $service->pause(Auth::user(), $campaign);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Campaign paused.']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function resume($id, CampaignService $service, CampaignDispatchService $dispatchService)
    {
        $campaign = $service->findForCompany(Auth::user(), $id);
        try {
            $service->resume(Auth::user(), $campaign);
            $dispatchService->dispatchCampaign($campaign);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Campaign resumed.']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function duplicate($id, CampaignService $service)
    {
        $campaign = $service->findForCompany(Auth::user(), $id);
        $new = $service->duplicate(Auth::user(), $campaign);
        
        $this->dispatch('open-campaign-modal', id: $new->id);
    }

    public function confirmDelete($id)
    {
        $this->campaignToDelete = $id;
    }

    public function cancelDelete()
    {
        $this->campaignToDelete = null;
    }

    public function deleteCampaign(CampaignService $service)
    {
        if ($this->campaignToDelete) {
            $campaign = $service->findForCompany(Auth::user(), $this->campaignToDelete);
            $campaign->delete();
            $this->campaignToDelete = null;
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Campaign deleted successfully.']);
        }
    }

    public function render(CampaignService $service)
    {
        $campaigns = $service->listForCompany(Auth::user(), [
            'search' => $this->search,
            'status' => $this->status,
            'type' => $this->type,
            'per_page' => 10,
        ]);

        $stats = [
            'total' => Campaign::forCompany(Auth::user()->company_id)->count(),
            'draft' => Campaign::forCompany(Auth::user()->company_id)->where('status', 'draft')->count(),
            'sending' => Campaign::forCompany(Auth::user()->company_id)->where('status', 'sending')->count(),
            'completed' => Campaign::forCompany(Auth::user()->company_id)->where('status', 'completed')->count(),
        ];

        return view('livewire.campaigns.campaign-index-page', [
            'campaigns' => $campaigns,
            'stats' => $stats,
        ])->layout('layouts.panel', ['title' => 'Campaigns', 'activeNav' => 'campaigns']);
    }

    #[On('campaign-created')]
    public function onCampaignCreated()
    {
        $this->resetPage();
    }
}
