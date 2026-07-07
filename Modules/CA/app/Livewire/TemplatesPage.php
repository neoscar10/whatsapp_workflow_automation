<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppTemplateService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class TemplatesPage extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $category = '';
    public $language = '';

    public bool $showEditModal = false;
    public ?int $editingTemplateId = null;
    public string $editTitle = '';
    public string $editBody = '';
    public string $editHeaderType = 'text';
    public string $editHeaderPlaceholder = '';
    public string $editCategory = 'utility';
    public string $editLanguageCode = 'en_us';

    public ?string $modalError = null;

    protected $queryString = [
        'search'   => ['except' => ''],
        'status'   => ['except' => ''],
        'category' => ['except' => ''],
        'language' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function syncTemplates(): void
    {
        $actor = Auth::user();
        $account = WhatsAppAccount::where('company_id', $actor->company_id)
            ->where('connection_status', 'connected')
            ->first();

        if (!$account) {
            session()->flash('error', 'No connected WhatsApp Business Account found. Connect your WABA first.');
            return;
        }

        try {
            $service = app(WhatsAppTemplateService::class);
            $result = $service->syncTemplatesFromMeta($account);
            session()->flash('success', $result['status'] ?? 'Templates synced successfully.');
        } catch (Exception $e) {
            Log::error('WABA Template Sync Failed: ' . $e->getMessage());
            session()->flash('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    public function editTemplate(int $templateId): void
    {
        $actor = Auth::user();
        $template = WhatsAppTemplate::where('company_id', $actor->company_id)->findOrFail($templateId);

        $this->editingTemplateId = $templateId;
        $this->editTitle         = $template->header_text ?? '';
        $this->editBody          = $template->body_text ?? '';
        $this->editHeaderType    = $template->header_type ?? 'text';
        $this->editCategory      = $template->category ?? 'utility';
        $this->editLanguageCode  = $template->language_code ?? 'en_us';
        
        $this->modalError        = null;
        $this->showEditModal     = true;
    }

    public function saveTemplate(): void
    {
        $actor = Auth::user();
        $template = WhatsAppTemplate::where('company_id', $actor->company_id)->findOrFail($this->editingTemplateId);
        $account = $template->account;

        if (!$account) {
            $this->modalError = 'No WABA account associated with this template.';
            return;
        }

        try {
            $service = app(WhatsAppTemplateService::class);

            $service->updateTemplateRecord(
                $template,
                $account,
                [
                    'category' => $this->editCategory,
                    'language_code' => $this->editLanguageCode,
                    'header_type' => $this->editHeaderType,
                    'header_text' => $this->editTitle,
                    'body_text' => $this->editBody,
                ]
            );

            $this->showEditModal = false;
            session()->flash('success', 'Template update submitted to Meta successfully.');
        } catch (Exception $e) {
            Log::error('Meta Template update failed: ' . $e->getMessage());
            $this->modalError = 'Meta Update Error: ' . $e->getMessage();
        }
    }

    public function deleteTemplate(int $templateId): void
    {
        $actor = Auth::user();
        $template = WhatsAppTemplate::where('company_id', $actor->company_id)->findOrFail($templateId);

        try {
            app(WhatsAppTemplateService::class)->deleteTemplate($template);
            session()->flash('success', 'Template deleted successfully.');
        } catch (Exception $e) {
            Log::error('Template deletion failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete template: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $actor = Auth::user();
        
        $query = WhatsAppTemplate::where('company_id', $actor->company_id)
            ->orderByDesc('created_at');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('remote_template_name', 'like', '%' . $this->search . '%')
                  ->orWhere('display_title', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        if (!empty($this->category)) {
            $query->where('category', $this->category);
        }

        if (!empty($this->language)) {
            $query->where('language_code', $this->language);
        }

        $templates = $query->paginate(12);

        return view('ca::livewire.templates-page', [
            'templates' => $templates,
        ])->layout('layouts.panel');
    }
}
