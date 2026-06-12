<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\CA\Models\CAClient;
use Modules\CA\Models\CABusinessType;
use Illuminate\Support\Facades\Auth;

class ClientIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $business_type_filter = '';
    public $status_filter = '';
    
    public $businessTypes;

    public $showDeleteModal = false;
    public $clientToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'business_type_filter' => ['except' => ''],
        'status_filter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->businessTypes = CABusinessType::where('status', 'active')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->clientToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->clientToDelete = null;
    }

    public function deleteClient()
    {
        if (!$this->clientToDelete) {
            return;
        }

        $client = CAClient::where('company_id', Auth::user()->company_id)->findOrFail($this->clientToDelete);
        
        $suffix = '_deleted_' . time();
        if ($client->email) {
            $client->email = $client->email . $suffix;
        }
        if ($client->phone) {
            $client->phone = $client->phone . $suffix;
        }
        $client->save();
        $client->delete();

        $this->showDeleteModal = false;
        $this->clientToDelete = null;
        session()->flash('message', 'Client successfully deleted.');
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;

        $clients = CAClient::with(['businessType', 'clientCompliances'])
            ->where('company_id', $companyId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('client_name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->business_type_filter, function ($query) {
                $query->where('ca_business_type_id', $this->business_type_filter);
            })
            ->when($this->status_filter, function ($query) {
                $query->where('status', $this->status_filter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('ca::livewire.client-index', [
            'clients' => $clients,
        ])->layout('layouts.panel');
    }
}
