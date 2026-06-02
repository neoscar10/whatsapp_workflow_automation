<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Company;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class CompanyIndex extends Component
{
    use WithPagination;

    public $showViewModal = false;
    public $showStatusModal = false;
    public $showImpersonateModal = false;
    public $selectedCompany = null;
    public $editingCompany = null;
    public $impersonateCompany = null;
    public $newStatus = '';
    public $demoCredits = 0.00;
    public $selectedDemoPhoneNumberId = null;
    public $demoDuration = 1;
    public $demoDurationUnit = 'days';

    public function viewCompany($id)
    {
        $this->selectedCompany = Company::with(['users' => function($query) {
            $query->where('is_company_owner', true);
        }, 'demoPhoneNumber'])->withCount('users')->findOrFail($id);

        $this->showViewModal = true;
    }

    public function openStatusModal($id)
    {
        $this->editingCompany = Company::findOrFail($id);
        $this->newStatus = $this->editingCompany->status;
        $this->demoCredits = $this->editingCompany->demo_credits ?? 0.00;
        $this->selectedDemoPhoneNumberId = $this->editingCompany->demo_whatsapp_phone_number_id;
        $this->demoDuration = 1;
        $this->demoDurationUnit = 'days';
        $this->showStatusModal = true;
    }

    public function saveStatus()
    {
        $rules = [
            'newStatus' => 'required|in:active,suspended,demo',
        ];

        if ($this->newStatus === 'demo') {
            $rules['demoCredits'] = 'required|numeric|min:0';
            $rules['selectedDemoPhoneNumberId'] = 'required|exists:whatsapp_phone_numbers,id';
            $rules['demoDuration'] = 'required|integer|min:1';
            $rules['demoDurationUnit'] = 'required|in:days,hours,mins';
        }

        $this->validate($rules);

        $updateData = [
            'status' => $this->newStatus,
        ];

        if ($this->newStatus === 'demo') {
            $updateData['demo_credits'] = $this->demoCredits;
            $updateData['demo_whatsapp_phone_number_id'] = $this->selectedDemoPhoneNumberId;

            $endsAt = now();
            if ($this->demoDurationUnit === 'days') {
                $endsAt->addDays($this->demoDuration);
            } elseif ($this->demoDurationUnit === 'hours') {
                $endsAt->addHours($this->demoDuration);
            } elseif ($this->demoDurationUnit === 'mins') {
                $endsAt->addMinutes($this->demoDuration);
            }
            $updateData['demo_ends_at'] = $endsAt;
        } else {
            $updateData['demo_ends_at'] = null;
            $updateData['demo_whatsapp_phone_number_id'] = null;
        }

        $this->editingCompany->update($updateData);

        session()->flash('success', 'Company status updated successfully.');
        $this->closeModals();
    }

    public function confirmImpersonate($id)
    {
        $this->impersonateCompany = Company::findOrFail($id);
        $this->showImpersonateModal = true;
    }

    public function impersonate()
    {
        $owner = $this->impersonateCompany->users()->where('is_company_owner', true)->first()
            ?? $this->impersonateCompany->users()->first();

        if (!$owner) {
            session()->flash('error', 'This company has no users to impersonate.');
            $this->closeModals();
            return;
        }

        // Store original super admin user ID in session
        session(['impersonator_user_id' => auth()->id()]);

        // Login as the tenant owner
        auth()->login($owner);

        // Redirect to dashboard
        return redirect()->route('dashboard');
    }

    public function closeModals()
    {
        $this->showViewModal = false;
        $this->showStatusModal = false;
        $this->showImpersonateModal = false;
        $this->selectedCompany = null;
        $this->editingCompany = null;
        $this->impersonateCompany = null;
        $this->newStatus = '';
        $this->demoCredits = 0.00;
        $this->selectedDemoPhoneNumberId = null;
        $this->demoDuration = 1;
        $this->demoDurationUnit = 'days';
    }

    public function render()
    {
        $companies = Company::with(['users' => function($query) {
            $query->where('is_company_owner', true);
        }])->withCount('users')->paginate(10);

        $demoCompany = Company::where('slug', 'system-demo')->first();
        $demoPhoneNumbers = $demoCompany
            ? \App\Models\WhatsApp\WhatsAppPhoneNumber::where('company_id', $demoCompany->id)->where('status', 'active')->get()
            : collect();

        return view('livewire.super-admin.company-index', [
            'companies' => $companies,
            'demoPhoneNumbers' => $demoPhoneNumbers,
        ])
            ->layout('layouts.super-admin', [
                'title' => 'Companies Management',
                'activeNav' => 'companies',
            ]);
    }
}
