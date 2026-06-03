<?php

namespace Modules\CA\Livewire;

use Livewire\Component;

class CADashboard extends Component
{
    public function render()
    {
        return view('ca::livewire.dashboard')
            ->layout('layouts.panel', [
                'title' => 'CA Dashboard',
                'activeNav' => 'ca.dashboard',
            ]);
    }
}
