<?php

namespace App\Livewire\Web\Panel;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TopbarCompanyModeSwitch extends Component
{
    protected $listeners = ['refreshVerification' => '$refresh'];

    public function toggleMode()
    {
        $user = Auth::user();
        $company = $user?->company;

        if (!$company) {
            return;
        }

        if (!$company->can_access_live) {
            session()->flash('error', 'Live mode access is disabled for your company.');
            return;
        }

        if ($company->status === 'demo') {
            $company->update([
                'status' => 'active',
                'demo_ends_at' => null,
            ]);
            session()->flash('success', 'Switched to Live Mode.');
        } else {
            $company->update([
                'status' => 'demo',
            ]);
            session()->flash('success', 'Switched to Demo Mode.');
        }

        return redirect(request()->header('Referer') ?: route('dashboard'));
    }

    public function render()
    {
        $user = Auth::user();
        $company = $user?->company;

        return view('livewire.web.panel.topbar-company-mode-switch', [
            'company' => $company,
            'canAccessLive' => (bool) ($company?->can_access_live),
            'isDemo' => $company?->status === 'demo',
        ]);
    }
}
