<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Modules\CA\Models\CAClient;
use Illuminate\Support\Facades\Auth;

class ClientShow extends Component
{
    public $client;

    public function mount($clientId)
    {
        $this->client = CAClient::with([
            'businessType', 
            'contact',
            'clientCompliances.compliance.serviceCategory',
            'timelines' => function($q) {
                $q->latest()->limit(10);
            }
        ])
        ->where('company_id', Auth::user()->company_id)
        ->findOrFail($clientId);
    }

    public function render()
    {
        return view('ca::livewire.client-show')->layout('layouts.panel');
    }
}
