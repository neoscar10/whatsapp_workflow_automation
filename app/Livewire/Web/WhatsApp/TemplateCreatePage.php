<?php

namespace App\Livewire\Web\WhatsApp;

use App\Services\WhatsApp\WhatsAppAccountSetupService;
use App\Services\WhatsApp\WhatsAppTemplateService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Template')]
class TemplateCreatePage extends Component
{
    public string $name = '';
    public string $category = 'marketing';
    public string $language = 'en_US';
    
    public string $headerType = 'none';
    public ?string $headerText = null;
    
    public string $bodyText = '';
    public ?string $footerText = null;

    public array $buttons = [];

    // Example payload properties
    public array $exampleHeaderValues = [];
    public array $exampleBodyValues = [];

    public function mount()
    {
        $accountData = $this->getAccount();
        if (!$accountData || ($accountData['connection_status'] ?? '') !== 'connected') {
            return redirect()->route('whatsapp.setup.account');
        }

        if (session()->has('duplicate_template')) {
            $data = session('duplicate_template');
            $this->name = $data['name'];
            $this->category = $data['category'];
            $this->language = $data['language'];
            $this->headerType = $data['headerType'] ?: 'none';
            $this->headerText = $data['headerText'];
            $this->bodyText = $data['bodyText'];
            $this->footerText = $data['footerText'];
            $this->buttons = $data['buttons'] ?? [];
            $this->updatedBodyText();
        }
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
        $this->buttons = array_values($this->buttons); // Re-index
    }

    // Dynamic variable extraction when body changes
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
            'name' => 'required|string|regex:/^[a-z0-9_]+$/|max:512',
            'category' => 'required|in:marketing,utility,authentication',
            'language' => 'required|string',
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

    protected function messages()
    {
        return [
            'name.regex' => 'The template name can only contain lowercase letters, numbers, and underscores.',
        ];
    }

    public function createTemplate(WhatsAppTemplateService $templateService)
    {
        $this->validate();

        $account = $this->getAccount();
        if (!$account) return;

        // Build data array for service
        $data = [
            'remote_template_name' => $this->name,
            'category' => $this->category,
            'language_code' => $this->language,
            'header_type' => $this->headerType,
            'header_text' => $this->headerType === 'text' ? $this->headerText : null,
            'body_text' => $this->bodyText,
            'footer_text' => $this->footerText,
        ];

        // Format example payloads correctly
        $examplePayload = [];
        
        if ($this->headerType === 'text' && preg_match('/\{\{1\}\}/', $this->headerText ?? '')) {
             $examplePayload['header_text'] = [$this->exampleHeaderValues[0] ?? 'Example'];
        } elseif (in_array($this->headerType, ['image', 'video', 'document'])) {
             // Hardcode dummy handle for creation since we don't have an upload UI yet
             // Meta requires a valid Resumable Upload API handle for media examples. 
             // We will pass a standard dummy string; if Meta rejects, we'd need file upload logic here.
             $examplePayload['header_handle'] = ['dummy_handle_for_review']; 
        }

        if (count($this->exampleBodyValues) > 0) {
            $examplePayload['body_text'] = array_values($this->exampleBodyValues);
        }

        if (!empty($examplePayload)) {
            $data['example_payload'] = $examplePayload;
        }

        $accountModel = \App\Models\WhatsApp\WhatsAppAccount::where('company_id', auth()->user()->company_id)->first();
        if (!$accountModel) {
            $this->addError('api', 'WhatsApp account not found.');
            return;
        }

        try {
            $templateService->createTemplate($accountModel, $data, $this->buttons, auth()->id());
            session()->flash('status', 'Template created successfully and submitted for review.');
            return redirect()->route('whatsapp.templates.index');
        } catch (\Exception $e) {
            Log::error('Component Template Create Error', ['message' => $e->getMessage()]);
            $this->addError('api', 'Failed to create template: ' . $e->getMessage());
        }
    }

    #[Layout('layouts.panel')]
    public function render()
    {
        return view('livewire.web.whatsapp.template-create-page')->layoutData([
            'activeNav' => 'whatsapp-templates',
        ]);
    }
}
