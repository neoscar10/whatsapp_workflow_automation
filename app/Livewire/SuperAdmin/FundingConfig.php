<?php

namespace App\Livewire\SuperAdmin;

use App\Models\FundingPackage;
use App\Models\SystemSetting;
use Livewire\Component;

class FundingConfig extends Component
{
    // Settings properties
    public $wallet_threshold = 100.00;
    public $showThresholdModal = false;

    // Demo Mode properties
    public $showDemoBillingModal = false;
    public $demo_text_rate = '0.0000';
    public $demo_template_utility_rate = '0.0000';
    public $demo_template_auth_rate = '0.0000';
    public $demo_template_marketing_rate = '0.0000';
    public $demo_automation_rate = '0.0000';

    // Package modal properties
    public $showModal = false;
    public $editingPackageId = null;
    public $amount = '';
    public $text_rate = '0.1000';
    public $template_utility_rate = '0.3000';
    public $template_auth_rate = '0.1500';
    public $template_marketing_rate = '0.5000';
    public $automation_rate = '0.0500';

    public function mount()
    {
        $this->wallet_threshold = (float) SystemSetting::get('wallet_threshold', 100.00);
        $this->demo_text_rate = number_format((float) SystemSetting::get('demo_text_rate', 0.1000), 4, '.', '');
        $this->demo_template_utility_rate = number_format((float) SystemSetting::get('demo_template_utility_rate', 0.3000), 4, '.', '');
        $this->demo_template_auth_rate = number_format((float) SystemSetting::get('demo_template_auth_rate', 0.1500), 4, '.', '');
        $this->demo_template_marketing_rate = number_format((float) SystemSetting::get('demo_template_marketing_rate', 0.5000), 4, '.', '');
        $this->demo_automation_rate = number_format((float) SystemSetting::get('demo_automation_rate', 0.0500), 4, '.', '');
    }

    public function openThresholdModal()
    {
        $this->wallet_threshold = (float) SystemSetting::get('wallet_threshold', 100.00);
        $this->showThresholdModal = true;
    }

    public function closeThresholdModal()
    {
        $this->showThresholdModal = false;
    }

    public function saveThresholdSettings()
    {
        $this->validate([
            'wallet_threshold' => 'required|numeric|min:0',
        ]);

        SystemSetting::set('wallet_threshold', $this->wallet_threshold);
        $this->showThresholdModal = false;
        session()->flash('success_settings', 'Wallet threshold settings saved successfully.');
    }

    public function openDemoBillingModal()
    {
        $this->demo_text_rate = number_format((float) SystemSetting::get('demo_text_rate', 0.1000), 4, '.', '');
        $this->demo_template_utility_rate = number_format((float) SystemSetting::get('demo_template_utility_rate', 0.3000), 4, '.', '');
        $this->demo_template_auth_rate = number_format((float) SystemSetting::get('demo_template_auth_rate', 0.1500), 4, '.', '');
        $this->demo_template_marketing_rate = number_format((float) SystemSetting::get('demo_template_marketing_rate', 0.5000), 4, '.', '');
        $this->demo_automation_rate = number_format((float) SystemSetting::get('demo_automation_rate', 0.0500), 4, '.', '');
        $this->showDemoBillingModal = true;
    }

    public function closeDemoBillingModal()
    {
        $this->showDemoBillingModal = false;
    }

    public function saveDemoBillingSettings()
    {
        $this->validate([
            'demo_text_rate' => 'required|numeric|min:0',
            'demo_template_utility_rate' => 'required|numeric|min:0',
            'demo_template_auth_rate' => 'required|numeric|min:0',
            'demo_template_marketing_rate' => 'required|numeric|min:0',
            'demo_automation_rate' => 'required|numeric|min:0',
        ]);

        SystemSetting::set('demo_text_rate', $this->demo_text_rate);
        SystemSetting::set('demo_template_utility_rate', $this->demo_template_utility_rate);
        SystemSetting::set('demo_template_auth_rate', $this->demo_template_auth_rate);
        SystemSetting::set('demo_template_marketing_rate', $this->demo_template_marketing_rate);
        SystemSetting::set('demo_automation_rate', $this->demo_automation_rate);

        $this->showDemoBillingModal = false;
        session()->flash('success_settings', 'Demo billing rates saved successfully.');
    }

    public function openCreateModal()
    {
        $this->resetPackageForm();
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $pkg = FundingPackage::findOrFail($id);
        $this->editingPackageId = $pkg->id;
        $this->amount = (float) $pkg->amount;
        $this->text_rate = (float) $pkg->text_rate;
        $this->template_utility_rate = (float) $pkg->template_utility_rate;
        $this->template_auth_rate = (float) $pkg->template_auth_rate;
        $this->template_marketing_rate = (float) $pkg->template_marketing_rate;
        $this->automation_rate = (float) $pkg->automation_rate;

        $this->showModal = true;
    }

    public function togglePackageStatus($id)
    {
        $pkg = FundingPackage::findOrFail($id);
        $pkg->update(['is_active' => !$pkg->is_active]);
        session()->flash('success_packages', 'Package status updated.');
    }

    public function deletePackage($id)
    {
        $pkg = FundingPackage::findOrFail($id);
        $pkg->delete();
        session()->flash('success_packages', 'Package deleted successfully.');
    }

    public function savePackage()
    {
        $this->validate([
            'amount' => 'required|numeric|min:0',
            'text_rate' => 'required|numeric|min:0',
            'template_utility_rate' => 'required|numeric|min:0',
            'template_auth_rate' => 'required|numeric|min:0',
            'template_marketing_rate' => 'required|numeric|min:0',
            'automation_rate' => 'required|numeric|min:0',
        ]);

        $data = [
            'amount' => $this->amount,
            'text_rate' => $this->text_rate,
            'template_utility_rate' => $this->template_utility_rate,
            'template_auth_rate' => $this->template_auth_rate,
            'template_marketing_rate' => $this->template_marketing_rate,
            'automation_rate' => $this->automation_rate,
        ];

        if ($this->editingPackageId) {
            FundingPackage::findOrFail($this->editingPackageId)->update($data);
            session()->flash('success_packages', 'Package updated successfully.');
        } else {
            FundingPackage::create($data);
            session()->flash('success_packages', 'Package created successfully.');
        }

        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetPackageForm();
    }

    private function resetPackageForm()
    {
        $this->editingPackageId = null;
        $this->amount = '';
        $this->text_rate = '0.1000';
        $this->template_utility_rate = '0.3000';
        $this->template_auth_rate = '0.1500';
        $this->template_marketing_rate = '0.5000';
        $this->automation_rate = '0.0500';
        $this->resetErrorBag();
    }

    public function render()
    {
        $packages = FundingPackage::latest()->paginate(10);

        return view('livewire.super-admin.funding-config', [
            'packages' => $packages,
        ])
        ->layout('layouts.super-admin', [
            'title' => 'Funding & Packages Config',
            'activeNav' => 'funding-config',
        ]);
    }
}
