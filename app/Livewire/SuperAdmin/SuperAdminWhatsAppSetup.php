<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Company;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use Livewire\Component;
use Livewire\WithPagination;

class SuperAdminWhatsAppSetup extends Component
{
    use WithPagination;

    public $access_token = '';
    public $waba_id = '';
    public $business_id = '';
    public $connectionStatus = 'not_connected';

    public $showNumberModal = false;
    public $editingNumberId = null;
    public $display_name = '';
    public $phone_number_id = '';
    public $phone_number = '';

    public function mount()
    {
        $company = Company::where('slug', 'system-demo')->firstOrFail();
        $account = WhatsAppAccount::where('company_id', $company->id)->first();

        if ($account) {
            $this->waba_id = $account->waba_id;
            $this->business_id = $account->business_id;
            $this->connectionStatus = $account->connection_status;
        }
    }

    public function saveAccount()
    {
        $this->validate([
            'waba_id' => 'required|string|max:50',
            'business_id' => 'required|string|max:50',
            'access_token' => 'nullable|string|max:4096',
        ]);

        $company = Company::where('slug', 'system-demo')->firstOrFail();
        
        $updateData = [
            'waba_id' => $this->waba_id,
            'business_id' => $this->business_id,
            'connection_status' => 'connected',
        ];

        if (!empty($this->access_token)) {
            $updateData['access_token'] = trim($this->access_token);
        }

        WhatsAppAccount::updateOrCreate(
            ['company_id' => $company->id],
            $updateData
        );

        $this->connectionStatus = 'connected';
        $this->access_token = '';
        session()->flash('success', 'Demo WABA account configured successfully.');
    }

    public function openCreateModal()
    {
        $this->resetNumberForm();
        $this->showNumberModal = true;
    }

    public function openEditModal($id)
    {
        $number = WhatsAppPhoneNumber::findOrFail($id);
        $this->editingNumberId = $number->id;
        $this->display_name = $number->display_name;
        $this->phone_number_id = $number->phone_number_id;
        $this->phone_number = $number->phone_number;
        $this->showNumberModal = true;
    }

    public function closeNumberModal()
    {
        $this->showNumberModal = false;
        $this->resetNumberForm();
    }

    public function resetNumberForm()
    {
        $this->editingNumberId = null;
        $this->display_name = '';
        $this->phone_number_id = '';
        $this->phone_number = '';
    }

    public function saveNumber()
    {
        $this->validate([
            'display_name' => 'required|string|max:100',
            'phone_number_id' => 'required|string|max:50',
            'phone_number' => 'required|string|max:30',
        ]);

        $company = Company::where('slug', 'system-demo')->firstOrFail();
        $account = WhatsAppAccount::where('company_id', $company->id)->first();

        if (!$account) {
            session()->flash('error', 'Please configure the WABA Account credentials first.');
            return;
        }

        WhatsAppPhoneNumber::updateOrCreate(
            [
                'id' => $this->editingNumberId,
            ],
            [
                'company_id' => $company->id,
                'whatsapp_account_id' => $account->id,
                'display_name' => $this->display_name,
                'phone_number_id' => $this->phone_number_id,
                'phone_number' => $this->phone_number,
                'status' => 'active',
            ]
        );

        session()->flash('success', 'Demo phone number saved successfully.');
        $this->closeNumberModal();
    }

    public function toggleNumberStatus($id)
    {
        $number = WhatsAppPhoneNumber::findOrFail($id);
        $number->update([
            'status' => $number->status === 'active' ? 'inactive' : 'active',
        ]);
        session()->flash('success', 'Phone number status updated.');
    }

    public function deleteNumber($id)
    {
        $number = WhatsAppPhoneNumber::findOrFail($id);
        $number->delete();
        session()->flash('success', 'Phone number removed.');
    }

    public function render()
    {
        $company = Company::where('slug', 'system-demo')->firstOrFail();
        $numbers = WhatsAppPhoneNumber::where('company_id', $company->id)->paginate(10);

        return view('livewire.super-admin.whatsapp-setup', [
            'numbers' => $numbers,
        ])->layout('layouts.super-admin', [
            'title' => 'Demo WhatsApp Setup',
            'activeNav' => 'demo-whatsapp-setup',
        ]);
    }
}
