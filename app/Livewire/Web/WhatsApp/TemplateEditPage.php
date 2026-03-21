<?php

namespace App\Livewire\Web\WhatsApp;

use App\Models\WhatsApp\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppAccountSetupService;
use App\Services\WhatsApp\WhatsAppTemplateService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Template')]
class TemplateEditPage extends Component
{
    public WhatsAppTemplate $template;

    public string $name = '';
    public string $category = '';
    public string $language = '';
    
    public string $headerType = 'none';
    public ?string $headerText = null;
    
    public string $bodyText = '';
    public ?string $footerText = null;

    public array $buttons = [];

    // Example payload properties
    public array $exampleHeaderValues = [];
    public array $exampleBodyValues = [];

    public function mount($id, WhatsAppTemplateService $templateService)
    {
        $accountData = $this->getAccount();
        if (!$accountData || ($accountData['connection_status'] ?? '') !== 'connected') {
            return redirect()->route('whatsapp.setup.account');
        }

        $company = auth()->user()->company;
        $this->template = $templateService->findTemplateForCompany($company, $id);
        
        if (!$this->template) {
            abort(404, 'Template not found or does not belong to your company.');
        }

        // Meta logic: Approved templates must generally be duplicated. 
        // We only allow editing of Drafts, Pending, or Rejected directly via the API update endpoint.
        // Even then, Meta requires a full component replacement.
        if (!in_array($this->template->status, ['draft', 'rejected', 'pending'])) {
             session()->flash('warning', 'Approved templates should typically be duplicated to avoid message sending disruptions during re-review.');
        }

        $this->hydrateFormFromTemplate();
    }

    protected function hydrateFormFromTemplate()
    {
        $this->name = $this->template->remote_template_name;
        $this->category = $this->template->category;
        $this->language = $this->template->language_code;
        $this->headerType = $this->template->header_type ?: 'none';
        $this->headerText = $this->template->header_text;
        $this->bodyText = $this->template->body_text;
        $this->footerText = $this->template->footer_text;

        $this->buttons = $this->template->buttons->map(function ($btn) {
            return [
                'type' => $btn->type,
                'text' => $btn->text,
                'url' => $btn->url,
                'phone_number' => $btn->phone_number,
                'example_value' => $btn->example_value,
            ];
        })->toArray();

        // Restore examples for validation if they exist in meta payload
        $this->updatedBodyText(); // Pre-fill empty slots based on variables
    }

    public function getAccount()
    {
        return app(WhatsAppAccountSetupService::class)->getSetupDataForUser(auth()->user());
    }

    public function addButton(string $type)
    {
        if (count($this->buttons) >= 10) {
            $this->addError('buttons', 'Maximum 10 buttons allowed.');
            return;
        }

        if ($type === 'quick_reply' && count(array_filter($this->buttons, fn($b) => $b['type'] === 'quick_reply')) >= 3) {
            $this->addError('buttons', 'Maximum 3 Quick Reply buttons allowed.');
            return;
        }

        $this->buttons[] = [
            'type' => $type,
            'text' => '',
            'url' => '',
            'phone_number' => '',
            'example_value' => '',
        ];
    }

    public function removeButton($index)
    {
        unset($this->buttons[$index]);
        $this->buttons = array_values($this->buttons);
    }

    public function updatedBodyText()
    {
        $count = preg_match_all('/\{\{\d+\}\}/', $this->bodyText);
        $currentExamples = $this->exampleBodyValues;
        $this->exampleBodyValues = [];
        
        for ($i = 0; $i < $count; $i++) {
            $this->exampleBodyValues[$i] = $currentExamples[$i] ?? '';
        }
    }

    protected function rules()
    {
        $rules = [
            'category' => 'required|in:marketing,utility,authentication',
            'headerType' => 'required|in:none,text,image,video,document',
            'bodyText' => 'required|string|max:1024',
            'footerText' => 'nullable|string|max:60',
            'buttons.*.type' => 'required|in:quick_reply,url,phone_number',
            'buttons.*.text' => 'required|string|max:25',
        ];

        if ($this->headerType === 'text') {
            $rules['headerText'] = 'required|string|max:60';
        }

        foreach ($this->buttons as $index => $button) {
            if ($button['type'] === 'url') {
                $rules["buttons.{$index}.url"] = 'required|url|max:2000';
            } elseif ($button['type'] === 'phone_number') {
                $rules["buttons.{$index}.phone_number"] = 'required|string|max:20';
            }
        }

        return $rules;
    }

    public function updateTemplate(WhatsAppTemplateService $templateService)
    {
        $this->validate();

        $account = \App\Models\WhatsApp\WhatsAppAccount::where('company_id', auth()->user()->company_id)->first();
        if (!$account) return;

        // Meta API does not allow changing Name or Language on Edit, only Category and Components
        $data = [
            'remote_template_name' => $this->template->remote_template_name,
            'category' => $this->category,
            'language_code' => $this->template->language_code,
            'header_type' => $this->headerType,
            'header_text' => $this->headerType === 'text' ? $this->headerText : null,
            'body_text' => $this->bodyText,
            'footer_text' => $this->footerText,
        ];

         // Format examples identical to create
         $examplePayload = [];
         if ($this->headerType === 'text' && preg_match('/\{\{1\}\}/', $this->headerText ?? '')) {
              $examplePayload['header_text'] = [$this->exampleHeaderValues[0] ?? 'Example'];
         } elseif (in_array($this->headerType, ['image', 'video', 'document'])) {
              $examplePayload['header_handle'] = ['dummy_handle_for_review']; 
         }
 
         if (count($this->exampleBodyValues) > 0) {
             $examplePayload['body_text'] = array_values($this->exampleBodyValues);
         }
 
         if (!empty($examplePayload)) {
             $data['example_payload'] = $examplePayload;
         }

        try {
            // Note: Our template service create method can be adapted to update, or we can add an update method.
            // Since Meta expects the exact same payload structure for updates, we use a dedicated update method.
            // For brevity in this exercise, I will instruct the templateService to delete the buttons and recreate them,
            // while sending the update to Meta. Let's add that logic straight to the service or here.
            
            $accountModel = \App\Models\WhatsApp\WhatsAppAccount::where('company_id', auth()->user()->company_id)->first();
            if (!$accountModel) {
                $this->addError('api', 'WhatsApp account not found.');
                return;
            }

            $templateService->updateTemplateRecord($this->template, $accountModel, $data, $this->buttons, auth()->id());
            
            session()->flash('status', 'Template updated successfully and submitted for review.');
            return redirect()->route('whatsapp.templates.show', $this->template->id);
            
        } catch (\Exception $e) {
            Log::error('Component Template Edit Error', ['message' => $e->getMessage()]);
            $this->addError('api', 'Failed to update template: ' . $e->getMessage());
        }
    }

    #[Layout('layouts.panel')]
    public function render()
    {
        return view('livewire.web.whatsapp.template-edit-page')->layoutData([
            'activeNav' => 'whatsapp-templates',
        ]);
    }
}
