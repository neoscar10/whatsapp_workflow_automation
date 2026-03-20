<?php

namespace App\Livewire\Web\Company;

use App\Livewire\Web\Auth\LoginPage;
use App\Services\Company\CompanyProfileService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class CompanyProfilePage extends Component
{
    use WithFileUploads;

    public $company_name = '';
    public $contact_email = '';
    public $website_url = '';
    public $description = '';
    public $logo = null;
    public $logo_url = null;

    protected function rules()
    {
        $companyId = Auth::user()->company_id;

        return [
            'company_name' => 'required|string|max:150',
            'contact_email' => "required|email|max:255|unique:companies,primary_email,{$companyId}",
            'website_url' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|max:2048',
        ];
    }

    public function mount(CompanyProfileService $service)
    {
        $this->loadProfile($service);
    }

    public function loadProfile(CompanyProfileService $service)
    {
        $data = $service->getProfileDataForUser(Auth::user());

        if ($data) {
            $this->company_name = $data['company_name'];
            $this->contact_email = $data['contact_email'];
            $this->website_url = $data['website_url'];
            $this->description = $data['description'];
            $this->logo_url = $data['logo_url'];
        }
    }

    public function save(CompanyProfileService $service)
    {
        $this->validate();

        $data = [
            'company_name' => $this->company_name,
            'contact_email' => $this->contact_email,
            'website_url' => $this->website_url,
            'description' => $this->description,
        ];

        $updatedData = $service->updateProfileForUser(Auth::user(), $data, $this->logo);

        $this->logo = null;
        $this->logo_url = $updatedData['logo_url'];

        session()->flash('success', 'Company profile updated successfully.');
    }

    public function discardChanges(CompanyProfileService $service)
    {
        $this->resetValidation();
        $this->logo = null;
        $this->loadProfile($service);
    }

    public function removeLogo(CompanyProfileService $service)
    {
        $updatedData = $service->removeLogoForUser(Auth::user());

        $this->logo = null;
        $this->logo_url = $updatedData['logo_url'];

        session()->flash('success', 'Company logo removed successfully.');
    }

    public function render()
    {
        return view('livewire.web.company.company-profile-page')
            ->layout('layouts.panel', [
                'title' => 'Company Profile - WhatsApp Cloud Panel',
                'activeNav' => 'company-profile',
            ]);
    }
}
