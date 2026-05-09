<?php

namespace App\Livewire\Web\Contacts;

use App\Services\Contact\ContactService;
use App\Services\Contact\ContactTagService;
use App\Services\Contact\ContactGroupService;
use App\Services\Contact\ContactExportService;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class ContactIndexPage extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $status = '';
    public $source = '';
    public $groupId = '';
    public $filterHasOptedIn = '';
    public $filterDoNotMessage = '';

    public $selectedContacts = [];
    public $selectAll = false;

    // Modal State
    public $showFormModal = false;
    public $showImportModal = false;
    
    // Form Fields
    public $contactId = null;
    public $name = '';
    public $phone = '';
    public $contactStatus = 'active'; // Renamed to avoid collision with $status filter
    public $hasOptedIn = false;
    public $doNotMessage = false;
    public $notes = '';
    public $selectedGroups = [];

    // Import State
    public $csvFile;
    public $importResults = null;
    public $isProcessing = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'source' => ['except' => ''],
        'groupId' => ['except' => ''],
    ];

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedContacts = $this->getContactsProperty()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedContacts = [];
        }
    }

    public function getContactsProperty()
    {
        $service = app(ContactService::class);
        return $service->listForCompany(auth()->user()->company_id, [
            'search' => $this->search,
            'status' => $this->status,
            'source' => $this->source,
            'group_id' => $this->groupId,
            'has_opted_in' => $this->filterHasOptedIn === '' ? null : (bool)$this->filterHasOptedIn,
            'do_not_message' => $this->filterDoNotMessage === '' ? null : (bool)$this->filterDoNotMessage,
        ]);
    }

    public function deleteContact($id)
    {
        $service = app(ContactService::class);
        $contact = $service->findForCompany(auth()->user()->company_id, $id);
        $service->delete(auth()->user(), $contact);
        
        $this->dispatch('notify', ['message' => 'Contact deleted successfully', 'type' => 'success']);
    }

    public function exportContacts()
    {
        return response()->streamDownload(
            app(ContactExportService::class)->exportToCsv(auth()->user()->company_id),
            'contacts-export-' . now()->format('Y-m-d') . '.csv'
        );
    }

    public function downloadImportTemplate()
    {
        return response()->streamDownload(
            app(ContactExportService::class)->getImportTemplate(),
            'contacts-import-template.csv'
        );
    }

    // Modal Methods
    public function openCreateModal()
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        $this->contactId = $id;
        
        $service = app(ContactService::class);
        $contact = $service->findForCompany(auth()->user()->company_id, $id);
        
        $this->name = $contact->name;
        $this->phone = $contact->phone;
        $this->contactStatus = $contact->status;
        $this->hasOptedIn = $contact->has_opted_in;
        $this->doNotMessage = $contact->do_not_message;
        $this->notes = $contact->notes;
        $this->selectedGroups = $contact->groups->pluck('id')->toArray();
        
        $this->showFormModal = true;
    }

    public function openImportModal()
    {
        $this->reset(['csvFile', 'importResults', 'isProcessing']);
        $this->showImportModal = true;
    }

    public function closeModals()
    {
        $this->showFormModal = false;
        $this->showImportModal = false;
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->reset(['contactId', 'name', 'phone', 'contactStatus', 'hasOptedIn', 'doNotMessage', 'notes', 'selectedGroups']);
        $this->resetErrorBag();
    }

    public function saveContact()
    {
        $this->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:30',
            'contactStatus' => 'required|in:active,inactive,blocked,archived',
        ]);

        $service = app(ContactService::class);
        $data = [
            'name' => $this->name,
            'phone' => $this->phone,
            'status' => $this->contactStatus,
            'has_opted_in' => $this->hasOptedIn,
            'do_not_message' => $this->doNotMessage,
            'notes' => $this->notes,
            'group_ids' => $this->selectedGroups,
        ];

        try {
            if ($this->contactId) {
                $contact = $service->findForCompany(auth()->user()->company_id, $this->contactId);
                $service->update(auth()->user(), $contact, $data);
                $message = 'Contact updated successfully';
            } else {
                $service->create(auth()->user(), $data);
                $message = 'Contact created successfully';
            }

            $this->dispatch('notify', ['message' => $message, 'type' => 'success']);
            $this->closeModals();
        } catch (\Exception $e) {
            $this->addError('phone', $e->getMessage());
        }
    }

    public function importContacts()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $this->isProcessing = true;

        try {
            $service = app(\App\Services\Contact\ContactImportService::class);
            $this->importResults = $service->importFromCsv(auth()->user(), $this->csvFile);
            
            $this->dispatch('notify', ['message' => 'Import completed', 'type' => 'success']);
        } catch (\Exception $e) {
            $this->addError('csvFile', $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    #[On('refreshContacts')]
    public function refresh()
    {
        // This method just triggers a re-render
    }

    public function render()
    {
        $service = app(ContactService::class);
        
        return view('livewire.web.contacts.contact-index-page', [
            'contacts' => $this->contacts,
            'groups' => app(ContactGroupService::class)->listForCompany(auth()->user()->company_id),
            'stats' => $service->getCompanyStats(auth()->user()->company_id),
        ])->layout('layouts.panel', ['activeNav' => 'contacts']);
    }
}
