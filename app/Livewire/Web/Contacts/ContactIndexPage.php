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
    public $showExportModal = false;
    public $exportMode = 'contacts'; // 'contacts' or 'template'
    public $exportFormat = 'xlsx';   // 'xlsx' or 'csv'
    
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

    // Simulator Modal State
    public $showSimulatorModal = false;
    public $simulatorContactId = null;
    public $simulatorContactName = '';
    public $simulatorContactPhone = '';
    public $simulatorMessages = [];
    public $simulatorMessageText = '';
    public $simulatorUploadFile = null;
    public $simulatorErrorMessage = null;

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

    public function openExportModal($mode = 'contacts')
    {
        $this->exportMode = $mode;
        $this->exportFormat = 'xlsx';
        $this->showExportModal = true;
    }

    public function closeExportModal()
    {
        $this->showExportModal = false;
    }

    public function downloadExport()
    {
        $this->showExportModal = false;
        $service = app(ContactExportService::class);
        $format = strtolower($this->exportFormat) === 'csv' ? 'csv' : 'xlsx';

        if ($this->exportMode === 'template') {
            $filename = 'contacts-import-template.' . $format;
            $contentType = $format === 'xlsx' 
                ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
                : 'text/csv; charset=UTF-8';

            return response()->streamDownload(
                $service->getImportTemplate($format),
                $filename,
                ['Content-Type' => $contentType]
            );
        }

        $filename = 'contacts-export-' . now()->format('Y-m-d') . '.' . $format;
        $contentType = $format === 'xlsx' 
            ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
            : 'text/csv; charset=UTF-8';

        return response()->streamDownload(
            $service->exportContacts(auth()->user()->company_id, $format),
            $filename,
            ['Content-Type' => $contentType]
        );
    }

    public function exportContacts()
    {
        return $this->openExportModal('contacts');
    }

    public function downloadImportTemplate()
    {
        return $this->openExportModal('template');
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
        $this->showExportModal = false;
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
                $this->resetPage();
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
            'csvFile' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        $this->isProcessing = true;

        try {
            $service = app(\App\Services\Contact\ContactImportService::class);
            $this->importResults = $service->importFromCsv(auth()->user(), $this->csvFile);
            $this->resetPage();
            
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

    // WhatsApp Inbound Simulator Methods
    public function openSimulatorModal(int $contactId): void
    {
        if (!config('services.whatsapp.simulator.enabled') || !app()->environment('local', 'testing')) {
            abort(403, "WhatsApp Simulator is disabled in this environment.");
        }

        $this->resetSimulatorForm();

        $contact = \App\Models\Contact\Contact::where('company_id', auth()->user()->company_id)->findOrFail($contactId);

        $this->simulatorContactId = $contact->id;
        $this->simulatorContactName = $contact->name ?: $contact->phone;
        $this->simulatorContactPhone = $contact->phone;

        $this->loadSimulatorMessages();
        $this->showSimulatorModal = true;
    }

    public function loadSimulatorMessages(): void
    {
        $this->simulatorErrorMessage = null;
        if (!$this->simulatorContactId) return;

        $contact = \App\Models\Contact\Contact::find($this->simulatorContactId);
        if ($contact) {
            $conversation = \App\Models\Chat\Conversation::where('company_id', auth()->user()->company_id)
                ->where('contact_phone', $contact->phone)
                ->first();

            if ($conversation) {
                $this->simulatorMessages = $conversation->messages()
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(function ($msg) {
                        return [
                            'id'          => $msg->id,
                            'direction'   => $msg->direction,
                            'message_type'=> $msg->message_type,
                            'body'        => $msg->message_type === 'template'
                                                ? ($msg->rendered_body ?: $msg->body)
                                                : $msg->body,
                            'media_url'   => $msg->media_url,
                            'status'      => $msg->status,
                            'created_at'  => $msg->created_at,
                        ];
                    })
                    ->toArray();
            } else {
                $this->simulatorMessages = [];
            }
        }
    }

    public function sendSimulatedMessage(): void
    {
        if (!config('services.whatsapp.simulator.enabled') || !app()->environment('local', 'testing')) {
            abort(403, "WhatsApp Simulator is disabled in this environment.");
        }

        $this->validate([
            'simulatorMessageText' => 'required_without:simulatorUploadFile|nullable|string',
            'simulatorUploadFile' => 'nullable|file|max:10240', // 10MB max
        ]);

        try {
            $simulatorService = app(\App\Services\WhatsApp\Simulation\WhatsAppInboundSimulatorService::class);
            $simulatorService->simulate(
                contactId: $this->simulatorContactId,
                body: $this->simulatorMessageText,
                file: $this->simulatorUploadFile,
                userId: auth()->id()
            );

            $this->simulatorMessageText = '';
            $this->simulatorUploadFile = null;
            $this->loadSimulatorMessages();
            $this->dispatch('notify', ['message' => 'Simulated message delivered successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            $this->simulatorErrorMessage = $e->getMessage();
        }
    }

    public function resetSimulatorForm(): void
    {
        $this->simulatorContactId = null;
        $this->simulatorContactName = '';
        $this->simulatorContactPhone = '';
        $this->simulatorMessages = [];
        $this->simulatorMessageText = '';
        $this->simulatorUploadFile = null;
        $this->simulatorErrorMessage = null;
    }

    public function closeSimulatorModal(): void
    {
        $this->showSimulatorModal = false;
        $this->resetSimulatorForm();
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
