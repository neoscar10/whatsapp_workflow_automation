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
    public array $csv_rows = [];
    public $import_summary = null;
    public array $manual_rows = [
        ['phone' => '', 'name' => '']
    ];

    // Validation Preview & Correction State
    public array $validationPreviewData = [
        'total' => 0,
        'passed_count' => 0,
        'failed_count' => 0,
        'rows' => []
    ];
    public string $validationFilter = 'all';
    public ?int $editingRecipientId = null;
    public string $editingPhone = '';
    public string $editingName = '';

    // Custom Delete Confirmation Modal State
    public ?int $confirmingDeleteRecipientId = null;
    public ?string $confirmingDeleteRecipientPhone = null;
    public ?string $confirmingDeleteRecipientName = null;

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

            if ($campaign->audience_type === 'manual') {
                $manuals = $campaign->recipients()->get()->map(fn($r) => [
                    'phone' => $r->phone,
                    'name' => $r->name ?? '',
                ])->toArray();
                if (!empty($manuals)) {
                    $this->manual_rows = $manuals;
                }
            } elseif ($campaign->audience_type === 'selected_contacts') {
                $this->selected_contact_ids = $campaign->recipients()->whereNotNull('contact_id')->pluck('contact_id')->toArray();
            }
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
            $this->saveStep2();
        } elseif ($this->step === 2) {
            $this->saveStep2();
            $this->loadValidationPreview();
        } elseif ($this->step === 3) {
            $this->loadValidationPreview();
        } elseif ($this->step === 4) {
            $this->validateStep4();
            $this->saveStep4();
            $this->loadValidationPreview();
        }

        $this->step++;
    }

    public function prevStep()
    {
        if ($this->step >= 2) {
            $this->loadValidationPreview();
        }
        $this->step--;
    }

    public function goToStep($targetStep)
    {
        if ($targetStep > $this->step) {
            if ($this->step === 1) {
                $this->validateStep1();
                $this->saveStep1();
            }
            if ($this->campaignId) {
                $this->saveStep2();
            }
        }
        $this->loadValidationPreview();
        $this->step = $targetStep;
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['audience_type', 'selected_contact_ids', 'selected_group_ids', 'type'])) {
            if ($this->campaignId) {
                $this->saveStep2();
            }
        }
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
        }
    }

    public function addManualRow()
    {
        array_unshift($this->manual_rows, ['phone' => '', 'name' => '']);
    }

    public function removeManualRow($index)
    {
        unset($this->manual_rows[$index]);
        $this->manual_rows = array_values($this->manual_rows);
        if ($this->campaignId) {
            $this->saveStep2();
        }
    }

    public function loadValidationPreview()
    {
        if ($this->campaignId) {
            $campaign = Campaign::find($this->campaignId);
            if ($campaign) {
                $campaign->update(['type' => $this->type]);
                $this->validationPreviewData = app(CampaignAudienceService::class)->validateAndPreviewRecipients(Auth::user(), $campaign);
            }
        } else {
            $selection = [
                'audience_type' => $this->audience_type,
                'type' => $this->audience_type,
                'contact_ids' => $this->selected_contact_ids,
                'group_ids' => $this->selected_group_ids,
                'filters' => $this->audience_filters,
                'manual_rows' => $this->manual_rows,
                'csv_rows' => $this->csv_rows,
            ];

            $this->validationPreviewData = app(CampaignAudienceService::class)->validateAndPreviewSelection(
                Auth::user(),
                $selection,
                $this->type
            );
        }
    }

    public function switchCampaignType($newType)
    {
        $this->type = $newType;
        if ($this->campaignId) {
            $campaign = Campaign::find($this->campaignId);
            if ($campaign) {
                $campaign->update(['type' => $newType]);
            }
        }
        $this->loadValidationPreview();
        $this->dispatch('notify', ['type' => 'success', 'message' => "Switched campaign type to " . ucfirst($newType) . "."]);
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
        if ($this->campaignId) {
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
        } else {
            if ($this->audience_type === 'manual') {
                foreach ($this->manual_rows as $idx => $row) {
                    if (($row['id'] ?? ($idx + 1)) == $id) {
                        $this->manual_rows[$idx]['phone'] = $this->editingPhone;
                        $this->manual_rows[$idx]['name'] = $this->editingName;
                        break;
                    }
                }
            } elseif ($this->audience_type === 'imported') {
                foreach ($this->csv_rows as $idx => $row) {
                    if (($row['id'] ?? ($idx + 1)) == $id) {
                        $this->csv_rows[$idx]['phone'] = $this->editingPhone;
                        $this->csv_rows[$idx]['name'] = $this->editingName;
                        break;
                    }
                }
            }
            $this->cancelEditRecipientRow();
            $this->loadValidationPreview();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Recipient updated & re-validated.']);
        }
    }

    public function confirmRemoveRecipientRow($id, $phone, $name = '')
    {
        $this->confirmingDeleteRecipientId = $id;
        $this->confirmingDeleteRecipientPhone = $phone;
        $this->confirmingDeleteRecipientName = $name ?: 'N/A';
    }

    public function cancelRemoveRecipientRow()
    {
        $this->confirmingDeleteRecipientId = null;
        $this->confirmingDeleteRecipientPhone = null;
        $this->confirmingDeleteRecipientName = null;
    }

    public function removeRecipientRow($id)
    {
        if ($this->campaignId) {
            $campaign = Campaign::find($this->campaignId);
            if ($campaign) {
                try {
                    app(CampaignAudienceService::class)->removeRecipientRow(Auth::user(), $campaign, $id);
                    $this->cancelRemoveRecipientRow();
                    $this->loadValidationPreview();
                    $this->dispatch('notify', ['type' => 'success', 'message' => 'Recipient removed from campaign.']);
                } catch (\Exception $e) {
                    $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
                }
            }
        } else {
            if ($this->audience_type === 'manual') {
                foreach ($this->manual_rows as $idx => $row) {
                    if (($row['id'] ?? ($idx + 1)) == $id) {
                        unset($this->manual_rows[$idx]);
                        $this->manual_rows = array_values($this->manual_rows);
                        break;
                    }
                }
            } elseif ($this->audience_type === 'imported') {
                foreach ($this->csv_rows as $idx => $row) {
                    if (($row['id'] ?? ($idx + 1)) == $id) {
                        unset($this->csv_rows[$idx]);
                        $this->csv_rows = array_values($this->csv_rows);
                        break;
                    }
                }
            } else {
                $this->selected_contact_ids = array_filter($this->selected_contact_ids, fn($cId) => $cId != $id);
            }
            $this->cancelRemoveRecipientRow();
            $this->loadValidationPreview();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Recipient removed from campaign.']);
        }
    }

    protected function saveStep2()
    {
        if (!$this->campaignId) return;

        $service = app(CampaignAudienceService::class);
        $campaign = Campaign::findOrFail($this->campaignId);
        
        if ($this->audience_type === 'imported') {
            $campaign->update(['audience_type' => 'imported']);
        } elseif ($this->audience_type === 'manual') {
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
        $fullPath = storage_path('app/' . $path);
        $service = app(CampaignRecipientImportService::class);

        try {
            if ($this->campaignId) {
                $campaign = Campaign::findOrFail($this->campaignId);
                $campaign->recipients()->delete();

                $this->import_summary = $service->importFromCsv(Auth::user(), $campaign, $fullPath);
                $this->audience_type = 'imported';
                $campaign->update(['audience_type' => 'imported']);
            } else {
                $this->csv_rows = $service->parseCsvToRows($fullPath);
                $this->audience_type = 'imported';

                $total = count($this->csv_rows);
                $this->import_summary = [
                    'total' => $total,
                    'success' => $total,
                    'failed' => 0,
                ];
            }

            $this->loadValidationPreview();
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

    protected function validateStep4()
    {
        if ($this->type === 'template') {
            $this->validate(['whatsapp_template_id' => 'required|exists:whatsapp_templates,id']);
        } else {
            $this->validate(['message_body' => 'required|string']);
        }
    }

    protected function saveStep4()
    {
        if (!$this->campaignId) return;

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
        if (!$this->campaignId) {
            $this->validateStep1();
            
            $service = app(CampaignService::class);
            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'type' => $this->type,
                'whatsapp_phone_number_id' => $this->whatsapp_phone_number_id,
                'scheduled_at' => $this->send_mode === 'schedule' ? $this->scheduled_at : null,
            ];

            $campaign = $service->createDraft(Auth::user(), $data);
            $this->campaignId = $campaign->id;
        } else {
            $campaign = Campaign::findOrFail($this->campaignId);
        }

        if ($this->audience_type === 'imported' && !empty($this->csv_rows)) {
            $recipients = [];
            foreach ($this->csv_rows as $row) {
                $rawPhone = trim($row['phone'] ?? '');
                if (empty($rawPhone)) continue;

                $normalized = \App\Support\PhoneNumberNormalizer::normalize($rawPhone);
                $contact = \App\Models\Contact\Contact::forCompany(Auth::user()->company_id)
                    ->where('normalized_phone', $normalized)
                    ->first();

                $isValid = \App\Support\PhoneNumberNormalizer::isValid($rawPhone);
                $isMessageable = $contact ? $contact->isMessageable() : true;
                $skipReason = !$isValid ? 'Invalid phone number format' : (!$isMessageable ? 'Contact opted out' : null);

                $recipients[] = [
                    'campaign_id' => $campaign->id,
                    'company_id' => Auth::user()->company_id,
                    'contact_id' => $contact?->id,
                    'phone' => $rawPhone,
                    'normalized_phone' => $normalized,
                    'name' => $row['name'] ?? $contact?->name,
                    'source' => 'imported',
                    'status' => $skipReason ? 'skipped' : 'pending',
                    'skip_reason' => $skipReason,
                    'personalization_data' => isset($row['personalization_data']) ? json_encode($row['personalization_data']) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($recipients)) {
                $campaign->recipients()->delete();
                \App\Models\Campaign\CampaignRecipient::insert($recipients);
            }
            $campaign->update(['audience_type' => 'imported']);
            app(CampaignService::class)->recalculateStats($campaign);
        } else {
            $this->saveStep2();
        }

        $this->saveStep4();

        if (in_array($this->send_mode, ['now', 'schedule'])) {
            $billingType = 'text';
            if ($this->type === 'template') {
                $template = WhatsAppTemplate::find($this->whatsapp_template_id);
                if ($template) {
                    $category = strtolower($template->category);
                    if (in_array($category, ['utility', 'authentication', 'marketing'])) {
                        $billingType = 'template_' . ($category === 'authentication' ? 'auth' : $category);
                    } else {
                        $billingType = 'template_utility';
                    }
                }
            }

            if (!app(\App\Services\Payment\BillingService::class)->canAffordActivity(Auth::user()->company, $billingType)) {
                $this->dispatch('notify', ['type' => 'error', 'message' => "Insufficient wallet balance to start or schedule this campaign."]);
                return;
            }
        }

        if ($this->send_mode === 'now') {
            app(CampaignService::class)->update(Auth::user(), $campaign, ['status' => 'queued']);
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
