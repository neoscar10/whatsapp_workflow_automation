<?php

namespace App\Livewire\Web\WhatsApp;

use App\Services\WhatsApp\WhatsAppAccountSetupService;
use App\Services\WhatsApp\WhatsAppTemplateService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Template Details')]
class TemplateShowPage extends Component
{
    public $template;

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
    }

    public function getAccount()
    {
        return app(WhatsAppAccountSetupService::class)->getSetupDataForUser(auth()->user());
    }

    public function duplicateTemplate()
    {
        // Simple duplication to the create page by passing data through session or query string
        // Since we are copying a complex state, session is safer than query params
        
        $duplicateData = [
            'name' => $this->template->remote_template_name . '_copy',
            'category' => $this->template->category,
            'language' => $this->template->language_code,
            'headerType' => $this->template->header_type,
            'headerText' => $this->template->header_text,
            'bodyText' => $this->template->body_text,
            'footerText' => $this->template->footer_text,
            'buttons' => $this->template->buttons->map(function ($btn) {
                return [
                    'type' => $btn->type,
                    'text' => $btn->text,
                    'url' => $btn->url,
                    'phone_number' => $btn->phone_number,
                    'example_value' => $btn->example_value,
                ];
            })->toArray(),
        ];

        session()->flash('duplicate_template', $duplicateData);
        return redirect()->route('whatsapp.templates.create');
    }

    #[Layout('layouts.panel')]
    public function render()
    {
        return view('livewire.web.whatsapp.template-show-page')->layoutData([
            'activeNav' => 'whatsapp-templates',
        ]);
    }
}
