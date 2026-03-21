<?php

namespace App\Livewire\Web\WhatsApp;

use App\Models\WhatsApp\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppAccountSetupService;
use App\Services\WhatsApp\WhatsAppTemplateService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('WhatsApp Templates')]
class TemplatesIndexPage extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $statusFilter = '';

    public bool $isSyncing = false;
    public ?string $syncMessage = null;
    public ?string $syncError = null;

    public ?int $templateToDelete = null;

    public function mount()
    {
        // Require connection first
        $accountData = $this->getAccount();
        if (!$accountData || ($accountData['connection_status'] ?? '') !== 'connected') {
            return redirect()->route('whatsapp.setup.account');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function getAccount()
    {
        return app(WhatsAppAccountSetupService::class)->getSetupDataForUser(auth()->user());
    }

    public function syncTemplates(WhatsAppTemplateService $templateService)
    {
        $accountModel = \App\Models\WhatsApp\WhatsAppAccount::where('company_id', auth()->user()->company_id)->first();
        if (!$accountModel) return;

        $this->isSyncing = true;
        $this->syncMessage = null;
        $this->syncError = null;

        try {
            $result = $templateService->syncTemplatesFromMeta($accountModel);
            $this->syncMessage = $result['status'];
            $this->resetPage();
            // Dispatch a highly visible success event if desired
            $this->dispatch('templates-synced');
        } catch (\Exception $e) {
            $this->syncError = "Failed to sync templates: " . $e->getMessage();
        } finally {
            $this->isSyncing = false;
        }
    }

    public function confirmDelete($id)
    {
        $this->templateToDelete = $id;
    }

    public function cancelDelete()
    {
        $this->templateToDelete = null;
    }

    public function deleteTemplate(WhatsAppTemplateService $templateService)
    {
        if (!$this->templateToDelete) return;

        $template = WhatsAppTemplate::where('company_id', auth()->user()->company_id)
            ->find($this->templateToDelete);

        if ($template) {
            try {
                $templateService->deleteTemplate($template);
                $this->syncMessage = "Template deleted successfully.";
            } catch (\Exception $e) {
                Log::error('Template deletion failed in component', ['error' => $e->getMessage()]);
                $this->syncError = "Failed to delete template completely. It may have been removed locally.";
            }
        }

        $this->templateToDelete = null;
        $this->resetPage();
    }

    #[Layout('layouts.panel')]
    public function render(WhatsAppTemplateService $templateService)
    {
        $accountData = $this->getAccount();

        $templates = collect();
        $counts = [
            'all' => 0,
            'approved' => 0,
            'pending' => 0,
            'rejected' => 0,
        ];

        if ($accountData && ($accountData['connection_status'] ?? '') === 'connected') {
            $filters = [
                'search' => $this->search,
                'category' => $this->categoryFilter,
                'status' => $this->statusFilter,
            ];
            
            $company = auth()->user()->company;
            $templates = $templateService->listTemplatesForCompany($company, $filters);

            $baseQuery = \App\Models\WhatsApp\WhatsAppTemplate::where('company_id', $company->id);
            $counts['all'] = (clone $baseQuery)->count();
            $counts['approved'] = (clone $baseQuery)->where('status', 'approved')->count();
            $counts['pending'] = (clone $baseQuery)->whereIn('status', ['pending', 'in_appeal'])->count();
            $counts['rejected'] = (clone $baseQuery)->where('status', 'rejected')->count();
        }

        return view('livewire.web.whatsapp.templates-index-page', [
            'templates' => $templates,
            'counts' => $counts,
        ])->layoutData([
            'activeNav' => 'whatsapp-templates',
        ]);
    }
}
