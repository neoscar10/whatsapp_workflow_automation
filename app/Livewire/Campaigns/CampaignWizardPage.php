<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign\Campaign;
use App\Models\Contact\ContactTag;
use App\Models\Contact\ContactGroup;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Services\Campaign\CampaignService;
use App\Services\Campaign\CampaignAudienceService;
use App\Services\Campaign\CampaignTemplateVariableService;
use App\Services\Campaign\CampaignRecipientImportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class CampaignWizardPage extends Component
{
    use WithFileUploads;

    public $campaignId;
    public $step = 1;

    // Step 1: Details
    public $name = '';
    public $description = '';
    public $type = 'template';
    public $whatsapp_phone_number_id = '';
    public $send_mode = 'draft';
    public $scheduled_at = '';

    // Step 2: Audience
    public $audience_type = 'selected_contacts';
    public $selected_contact_ids = [];
    public $selected_group_ids = [];
    public $audience_filters = [
        'source' => '',
        'status' => '',
        'has_opted_in' => '',
        'group_ids' => [],
    ];
    public $csv_file;
    public $import_summary = null;

    // Step 3: Content
    public $whatsapp_template_id = '';
    public $template_variable_mapping = ['header' => [], 'body' => [], 'button' => []];
    public $message_body = ''; // for text campaigns

    public function mount($id = null)
    {
        if ($id) {
            $campaign = Campaign::forCompany(Auth::user()->company_id)->findOrFail($id);
            $this->campaignId = $campaign->id;
            $this->name = $campaign->name;
            $this->description = $campaign->description;
            $this->type = $campaign->type;
            $this->whatsapp_phone_number_id = $campaign->whatsapp_phone_number_id;
            $this->audience_type = $campaign->audience_type;
            $this->audience_filters = array_merge($this->audience_filters, $campaign->audience_filters ?? []);
            $this->whatsapp_template_id = $campaign->whatsapp_template_id;
            $this->template_variable_mapping = array_merge($this->template_variable_mapping, $campaign->template_variable_mapping ?? []);
            $this->message_body = $campaign->message_body;
            $this->scheduled_at = $campaign->scheduled_at?->format('Y-m-d\TH:i');
        } else {
            // Default phone number
            $this->whatsapp_phone_number_id = WhatsAppPhoneNumber::forCompany(Auth::user()->company_id)->first()?->id;
        }
    }

    public function nextStep()
    {
        if ($this->step === 1) {
            $this->validateStep1();
            $this->saveStep1();
        } elseif ($this->step === 2) {
            $this->saveStep2();
        } elseif ($this->step === 3) {
            $this->validateStep3();
            $this->saveStep3();
        }

        $this->step++;
    }

    public function prevStep()
    {
        $this->step--;
    }

    protected function validateStep1()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:template,text',
            'whatsapp_phone_number_id' => 'required|exists:whatsapp_phone_numbers,id',
            'scheduled_at' => $this->send_mode === 'schedule' ? 'required|after:now' : 'nullable',
        ]);
    }

    protected function saveStep1()
    {
        $service = app(CampaignService::class);
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'whatsapp_phone_number_id' => $this->whatsapp_phone_number_id,
            'scheduled_at' => $this->send_mode === 'schedule' ? $this->scheduled_at : null,
        ];

        if ($this->campaignId) {
            $campaign = $service->findForCompany(Auth::user(), $this->campaignId);
            $service->update(Auth::user(), $campaign, $data);
        } else {
            $campaign = $service->createDraft(Auth::user(), $data);
            $this->campaignId = $campaign->id;
        }
    }

    public function addManualRow()
    {
        $this->manual_rows[] = ['phone' => '', 'name' => ''];
    }

    public function removeManualRow($index)
    {
        unset($this->manual_rows[$index]);
        $this->manual_rows = array_values($this->manual_rows);
    }

    public function loadValidationPreview()
    {
        if (!$this->campaignId) return;

        $campaign = Campaign::find($this->campaignId);
        if ($campaign) {
            $this->validationPreviewData = app(CampaignAudienceService::class)->validateAndPreviewRecipients(Auth::user(), $campaign);
        }
    }

    public function editRecipientRow($id, $phone, $name)
    {
        $this->editingRecipientId = $id;
        $this->editingPhone = $phone;
        $this->editingName = $name;
    }

    public function cancelEditRecipientRow()
    {
        $this->editingRecipientId = null;
        $this->editingPhone = '';
        $this->editingName = '';
    }

    public function saveRecipientRow($id)
    {
        if (!$this->campaignId) return;

        $campaign = Campaign::find($this->campaignId);
        if ($campaign) {
            try {
                app(CampaignAudienceService::class)->correctRecipientRow(Auth::user(), $campaign, $id, [
                    'phone' => $this->editingPhone,
                    'name' => $this->editingName,
                ]);
                $this->cancelEditRecipientRow();
                $this->loadValidationPreview();
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Recipient updated & re-validated.']);
            } catch (\Exception $e) {
                $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    protected function saveStep2()
    {
        $service = app(CampaignAudienceService::class);
        $campaign = Campaign::findOrFail($this->campaignId);
        
        if ($this->audience_type === 'manual') {
            $service->addManualRecipients(Auth::user(), $campaign, $this->manual_rows);
        } else {
            $selection = [
                'type' => $this->audience_type,
                'contact_ids' => $this->selected_contact_ids,
                'group_ids' => $this->selected_group_ids,
                'filters' => $this->audience_filters,
            ];

            $service->syncAudience(Auth::user(), $campaign, $selection);
        }

        $this->loadValidationPreview();
    }

    public function importCsv()
    {
        $this->validate([
            'csv_file' => 'required|mimes:csv,txt|max:10240',
        ]);

        $path = $this->csv_file->store('temp');
        $service = app(CampaignRecipientImportService::class);
        $campaign = Campaign::findOrFail($this->campaignId);

        try {
            $this->import_summary = $service->importFromCsv(Auth::user(), $campaign, storage_path('app/' . $path));
            $this->audience_type = 'imported';
            $this->dispatch('notify', ['type' => 'success', 'message' => 'CSV imported successfully.']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function downloadSampleCsv()
    {
        return response()->streamDownload(
            app(\App\Services\Contact\ContactExportService::class)->getImportTemplate(),
            'campaign-recipients-sample.csv'
        );
    }

    protected function validateStep3()
    {
        if ($this->type === 'template') {
            $this->validate(['whatsapp_template_id' => 'required|exists:whatsapp_templates,id']);
        } else {
            $this->validate(['message_body' => 'required|string']);
        }
    }

    protected function saveStep3()
    {
        $service = app(CampaignService::class);
        $campaign = Campaign::findOrFail($this->campaignId);

        $data = [
            'type' => $this->type,
            'whatsapp_template_id' => $this->whatsapp_template_id,
            'template_variable_mapping' => $this->template_variable_mapping,
            'message_body' => $this->message_body,
        ];

        $service->updateContent(Auth::user(), $campaign, $data);
    }

    public function finish()
    {
        $campaign = Campaign::findOrFail($this->campaignId);
        
        if ($this->send_mode === 'now') {
            app(app(CampaignService::class)->update(Auth::user(), $campaign, ['status' => 'queued']));
            app(\App\Services\Campaign\CampaignDispatchService::class)->dispatchCampaign($campaign);
            $msg = 'Campaign started successfully.';
        } elseif ($this->send_mode === 'schedule') {
            app(CampaignService::class)->schedule(Auth::user(), $campaign, $this->scheduled_at);
            $msg = 'Campaign scheduled successfully.';
        } else {
            $msg = 'Campaign saved as draft.';
        }

        session()->flash('notify', ['type' => 'success', 'message' => $msg]);
        return redirect()->route('campaigns.index');
    }

    public function render()
    {
        $groups = app(\App\Services\Contact\ContactGroupService::class)->listForCompany(Auth::user()->company_id);

        return view('livewire.campaigns.campaign-wizard-page', [
            'phoneNumbers' => WhatsAppPhoneNumber::forCompany(Auth::user()->company_id)->get(),
            'groups' => $groups,
            'templates' => WhatsAppTemplate::forCompany(Auth::user()->company_id)->where('status', 'approved')->get(),
            'personalizationFields' => app(CampaignTemplateVariableService::class)->provideAvailablePersonalizationFields(),
        ])->layout('layouts.panel', ['title' => 'Create Campaign', 'activeNav' => 'campaigns']);
    }
}
