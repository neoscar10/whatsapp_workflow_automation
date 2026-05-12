<?php

namespace App\Livewire\Contacts;

use App\Models\Contact\ContactGroup;
use App\Services\Contact\ContactGroupService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class AudienceManagerPage extends Component
{
    use WithPagination;

    public $search = '';

    // Modal State
    public $showStaticGroupModal = false;
    public $showMembershipModal = false;

    // Form Data
    public $selectedId = null;
    public $name = '';
    public $description = '';

    // Membership Data
    public $membershipGroupId = null;
    public $availableSearch = '';
    public $memberSearch = '';
    public $selectedContactIds = [];

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedAvailableSearch()
    {
        $this->resetPage('available-page');
    }

    public function updatedMemberSearch()
    {
        $this->resetPage('member-page');
    }

    // Group Methods
    public function openStaticGroupModal($id = null)
    {
        $this->resetForm();
        if ($id) {
            $group = ContactGroup::where('company_id', Auth::user()->company_id)->findOrFail($id);
            $this->selectedId = $group->id;
            $this->name = $group->name;
            $this->description = $group->description;
        }
        $this->showStaticGroupModal = true;
    }

    public function saveStaticGroup()
    {
        $this->validate(['name' => 'required|string|max:255']);
        
        $service = app(ContactGroupService::class);
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'type' => 'static',
        ];

        if ($this->selectedId) {
            $group = ContactGroup::where('company_id', Auth::user()->company_id)->findOrFail($this->selectedId);
            $service->update(Auth::user(), $group, $data);
            $msg = 'Group updated successfully.';
        } else {
            $service->create(Auth::user(), $data);
            $msg = 'Group created successfully.';
        }

        $this->showStaticGroupModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function deleteGroup($id)
    {
        $group = ContactGroup::where('company_id', Auth::user()->company_id)->findOrFail($id);
        app(ContactGroupService::class)->delete(Auth::user(), $group);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Group deleted.']);
    }

    // Membership Methods
    public function openMembershipModal($id)
    {
        $this->membershipGroupId = $id;
        $this->availableSearch = '';
        $this->memberSearch = '';
        $this->selectedContactIds = [];
        $this->showMembershipModal = true;
        $this->resetPage('available-page');
        $this->resetPage('member-page');
    }

    public function addSelectedContacts()
    {
        if (empty($this->selectedContactIds)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Select at least one contact to add.']);
            return;
        }

        $group = ContactGroup::where('company_id', Auth::user()->company_id)->findOrFail($this->membershipGroupId);
        $result = app(ContactGroupService::class)->addContactsToGroup(Auth::user(), $group, $this->selectedContactIds);

        $this->selectedContactIds = [];
        $this->dispatch('notify', ['type' => 'success', 'message' => "{$result['added_count']} contacts added to group."]);
    }

    public function removeMember($contactId)
    {
        $group = ContactGroup::where('company_id', Auth::user()->company_id)->findOrFail($this->membershipGroupId);
        app(ContactGroupService::class)->removeContactsFromGroup(Auth::user(), $group, [$contactId]);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Member removed from group.']);
    }

    protected function resetForm()
    {
        $this->reset(['selectedId', 'name', 'description']);
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;
        $service = app(ContactGroupService::class);
        
        $data = $service->listForCompany($companyId, [
            'search' => $this->search, 
            'type' => 'static'
        ]);

        $stats = [
            'groups_count' => ContactGroup::where('company_id', $companyId)->where('type', 'static')->count(),
        ];

        // Membership data if modal is open
        $availableContacts = collect();
        $currentMembers = collect();
        $membershipGroup = null;

        if ($this->showMembershipModal && $this->membershipGroupId) {
            $membershipGroup = ContactGroup::where('company_id', $companyId)->findOrFail($this->membershipGroupId);
            $availableContacts = $service->searchAvailableContactsForGroup(Auth::user(), $membershipGroup, [
                'search' => $this->availableSearch,
                'per_page' => 10,
                'page_name' => 'available-page'
            ]);
            $currentMembers = $service->getGroupMembers(Auth::user(), $membershipGroup, [
                'search' => $this->memberSearch,
                'per_page' => 10,
                'page_name' => 'member-page'
            ]);
        }

        return view('livewire.contacts.audience-manager-page', [
            'items' => $data,
            'stats' => $stats,
            'membershipGroup' => $membershipGroup,
            'availableContacts' => $availableContacts,
            'currentMembers' => $currentMembers,
        ])->layout('layouts.panel', ['title' => 'Audience Manager', 'activeNav' => 'contacts.audiences']);
    }
}
