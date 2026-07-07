<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\CA\Models\CANotification;
use Modules\CA\Models\CADocument;
use Modules\CA\Models\CAClientComplianceRequirement;
use App\Services\WhatsApp\WhatsAppOutboundMessageService;
use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class NotificationsPage extends Component
{
    use WithPagination;

    public $filterStatus = 'pending';
    public $filterType = '';

    public bool $showReassignModal = false;
    public ?int $selectedDocumentId = null;
    public ?int $selectedNotificationId = null;
    public ?int $targetRequirementId = null;

    protected $queryString = [
        'filterStatus' => ['except' => ''],
        'filterType'   => ['except' => ''],
    ];

    public function approveDocument(int $notificationId): void
    {
        $actor = Auth::user();
        $notification = CANotification::where('company_id', $actor->company_id)->findOrFail($notificationId);
        
        $docId = $notification->metadata_json['ca_document_id'] ?? null;
        if (!$docId) return;

        $document = CADocument::where('company_id', $actor->company_id)->find($docId);
        if (!$document) return;

        $requirement = $document->clientComplianceRequirement;

        try {
            // Update Document status
            $document->update([
                'status' => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]);

            // Update Requirement status
            if ($requirement) {
                $requirement->update(['status' => 'completed']);
            }

            // Mark notification as resolved
            $notification->update([
                'status' => 'resolved',
                'read_at' => now(),
            ]);

            session()->flash('success', 'Document approved and requirement marked completed successfully.');
        } catch (Exception $e) {
            Log::error("CA Notification Page: Approve failed: " . $e->getMessage());
            session()->flash('error', 'Approve failed: ' . $e->getMessage());
        }
    }

    public function rejectDocument(int $notificationId, string $reason = 'Incorrect file uploaded'): void
    {
        $actor = Auth::user();
        $notification = CANotification::where('company_id', $actor->company_id)->findOrFail($notificationId);

        $docId = $notification->metadata_json['ca_document_id'] ?? null;
        if (!$docId) return;

        $document = CADocument::where('company_id', $actor->company_id)->find($docId);
        if (!$document) return;

        $requirement = $document->clientComplianceRequirement;

        try {
            // Update Document status
            $document->update(['status' => 'rejected']);

            // Update Requirement status back to rejected
            if ($requirement) {
                $requirement->update(['status' => 'rejected']);
            }

            // Mark notification as resolved
            $notification->update([
                'status' => 'resolved',
                'read_at' => now(),
            ]);

            // Send rejection session notification message to client via WhatsApp
            $client = $document->client;
            if ($client && $client->phone) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $client->phone);
                if (!str_starts_with($cleanPhone, '91') && strlen($cleanPhone) === 10) {
                    $cleanPhone = '91' . $cleanPhone;
                }

                $conversation = Conversation::where('company_id', $actor->company_id)
                    ->where('contact_phone', $cleanPhone)
                    ->first();

                if ($conversation) {
                    $replyText = "✗ Document Rejected: *{$document->document_name}*\nReason: {$reason}. Please re-upload the correct document.";
                    
                    $msg = ConversationMessage::create([
                        'conversation_id'     => $conversation->id,
                        'direction'           => 'outbound',
                        'message_type'        => 'text',
                        'body'                => $replyText,
                        'status'              => 'queued',
                    ]);

                    app(WhatsAppOutboundMessageService::class)->sendConversationMessage($msg);
                }
            }

            session()->flash('success', 'Document rejected and client notified on WhatsApp.');
        } catch (Exception $e) {
            Log::error("CA Notification Page: Reject failed: " . $e->getMessage());
            session()->flash('error', 'Reject failed: ' . $e->getMessage());
        }
    }

    public function openReassignModal(int $notificationId): void
    {
        $actor = Auth::user();
        $notification = CANotification::where('company_id', $actor->company_id)->findOrFail($notificationId);
        
        $this->selectedNotificationId = $notificationId;
        $this->selectedDocumentId = $notification->metadata_json['ca_document_id'] ?? null;
        $this->targetRequirementId = null;
        $this->showReassignModal = true;
    }

    public function reassignDocument(): void
    {
        if (!$this->targetRequirementId) {
            session()->flash('error', 'Please select a target compliance requirement.');
            return;
        }

        $actor = Auth::user();
        $document = CADocument::where('company_id', $actor->company_id)->findOrFail($this->selectedDocumentId);
        $notification = CANotification::where('company_id', $actor->company_id)->findOrFail($this->selectedNotificationId);
        $newReq = CAClientComplianceRequirement::findOrFail($this->targetRequirementId);

        try {
            // Update Document assignments
            $document->update([
                'ca_client_compliance_requirement_id' => $newReq->id,
                'ca_client_compliance_id' => $newReq->ca_client_compliance_id,
                'document_name' => $newReq->name,
            ]);

            // Update Requirement statuses
            $newReq->update(['status' => 'under_review']);

            // Mark notification as resolved
            $notification->update([
                'status' => 'resolved',
                'read_at' => now(),
            ]);

            $this->showReassignModal = false;
            session()->flash('success', "Document successfully reassigned to {$newReq->name}.");
        } catch (Exception $e) {
            Log::error("CA Notification Page: Reassign failed: " . $e->getMessage());
            session()->flash('error', 'Reassign failed: ' . $e->getMessage());
        }
    }

    public function markAsRead(int $notificationId): void
    {
        $actor = Auth::user();
        CANotification::where('company_id', $actor->company_id)
            ->where('id', $notificationId)
            ->update(['read_at' => now(), 'status' => 'resolved']);
    }

    public function render()
    {
        $actor = Auth::user();
        $query = CANotification::where('company_id', $actor->company_id)
            ->with(['client'])
            ->orderByDesc('created_at');

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        $notifications = $query->paginate(10);

        // Fetch pending requirements for reassign list if document is selected
        $pendingRequirements = collect();
        if ($this->selectedDocumentId) {
            $doc = CADocument::find($this->selectedDocumentId);
            if ($doc) {
                $pendingRequirements = CAClientComplianceRequirement::whereHas('clientCompliance', function($q) use ($doc) {
                    $q->where('ca_client_id', $doc->ca_client_id);
                })
                ->whereIn('status', ['pending', 'rejected'])
                ->get();
            }
        }

        return view('ca::livewire.notifications-page', [
            'notifications' => $notifications,
            'pendingRequirements' => $pendingRequirements,
        ])->layout('layouts.panel');
    }
}
