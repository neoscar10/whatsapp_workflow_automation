<?php

namespace App\Livewire\Web\Chats;

use App\Services\Chat\ChatConversationActionService;
use App\Services\Chat\ChatInboxService;
use App\Services\Chat\ChatMessageService;
use Exception;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatInboxPage extends Component
{
    public string $search = '';
    public string $tab = 'all';
    public ?int $selectedConversationId = null;
    public int $companyId;
    
    public string $messageText = '';
    public string $noteText = '';
    
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    // Assign Agent Modal State
    public bool $showAssignAgentModal = false;
    public string $agentSearch = '';
    public ?int $selectedAgentId = null;
    public array $assignAgents = [];
    public ?string $assignAgentError = null;

    // Select Template Modal State
    public bool $showTemplateModal = false;
    public string $templateSearch = '';
    public string $templateFilter = 'all';
    public ?int $selectedTemplateId = null;
    public array $availableTemplates = [];
    public ?array $selectedTemplatePreview = null;
    public ?string $templateModalError = null;
    public ?string $templateModalMessage = null;
    public array $templateVariables = [];
    public array $systemVariableOptions = [];

    public array $channelAvailability = [];

    protected $queryString = [
        'selectedConversationId' => ['except' => null, 'as' => 'conversation'],
    ];

    public function mount(ChatInboxService $inboxService)
    {
        // Don't auto-select the first conversation anymore to support empty states.
        $this->selectedConversationId = $this->selectedConversationId ?? null;

        if ($this->selectedConversationId) {
            $this->syncNoteText();
            $this->dispatch('conversation-selected', [
                'conversation_id' => $this->selectedConversationId,
                'company_id' => auth()->user()->company_id,
            ]);
        }

        $this->companyId = auth()->user()->company_id;
    }

    public function updatedSearch()
    {
        $this->resetMessages();
    }

    public function updatedTab()
    {
        $this->resetMessages();
    }

    public function selectConversation(int $id)
    {
        $this->selectedConversationId = $id;
        $this->resetMessages();
        $this->syncNoteText();

        $this->dispatch('conversation-selected', [
            'conversation_id' => $id,
            'company_id' => auth()->user()->company_id,
        ]);
    }

    public function sendMessage(ChatMessageService $messageService)
    {
        $this->resetMessages();
        
        $text = trim($this->messageText);
        if (empty($text)) {
            return;
        }

        if (!$this->selectedConversationId) {
            $this->errorMessage = 'No active conversation selected to send message to.';
            return;
        }

        try {
            $result = $messageService->sendTextMessage(auth()->user(), $this->selectedConversationId, $text);
            if ($result) {
                $this->messageText = '';
            } else {
                $this->errorMessage = 'Failed to send message: conversation not found or access denied.';
            }
        } catch (Exception $e) {
            $this->errorMessage = 'Error sending message: ' . $e->getMessage();
        }
    }

    public function saveNote(ChatConversationActionService $actionService)
    {
        $this->resetMessages();
        
        $text = trim($this->noteText);
        if (empty($text)) {
            $this->errorMessage = 'Note text cannot be empty.';
            return;
        }

        if (!$this->selectedConversationId) {
            $this->errorMessage = 'No active conversation selected.';
            return;
        }

        try {
            $result = $actionService->savePrivateNote(auth()->user(), $this->selectedConversationId, $text);
            if ($result) {
                // Keep the text in the textarea instead of clearing it as per user request
                $this->successMessage = 'Note saved successfully.';
            } else {
                $this->errorMessage = 'Failed to save note.';
            }
        } catch (Exception $e) {
            $this->errorMessage = 'Error saving note: ' . $e->getMessage();
        }
    }

    public function closeChat(ChatConversationActionService $actionService)
    {
        $this->resetMessages();
        
        if (!$this->selectedConversationId) {
            return;
        }

        try {
            $actionService->closeConversation(auth()->user(), $this->selectedConversationId);
            $this->successMessage = 'Chat closed securely.';
        } catch (Exception $e) {
            $this->errorMessage = 'Error closing chat: ' . $e->getMessage();
        }
    }

    public function openAssignAgentModal(\App\Services\Team\ChatAgentDirectoryService $directoryService)
    {
        $this->resetMessages();
        
        if (!$this->selectedConversationId) {
            $this->errorMessage = 'No active conversation selected.';
            return;
        }

        $this->showAssignAgentModal = true;
        $this->agentSearch = '';
        $this->selectedAgentId = null;
        $this->assignAgentError = null;
        
        $this->loadAssignableAgents($directoryService);
    }

    public function closeAssignAgentModal()
    {
        $this->showAssignAgentModal = false;
        $this->agentSearch = '';
        $this->selectedAgentId = null;
        $this->assignAgentError = null;
    }

    public function updatedAgentSearch(\App\Services\Team\ChatAgentDirectoryService $directoryService)
    {
        $this->loadAssignableAgents($directoryService);
    }

    public function loadAssignableAgents(\App\Services\Team\ChatAgentDirectoryService $directoryService)
    {
        $this->assignAgents = $directoryService->getAssignableAgentsForUser(auth()->user(), [
            'search' => $this->agentSearch,
        ]);
    }

    public function assignChat(ChatConversationActionService $actionService)
    {
        $this->assignAgentError = null;

        if (!$this->selectedConversationId) {
            $this->assignAgentError = 'No active conversation selected to assign.';
            return;
        }

        if (!$this->selectedAgentId) {
            $this->assignAgentError = 'Please select an agent to assign.';
            return;
        }

        try {
            $summary = $actionService->assignConversation(auth()->user(), $this->selectedConversationId, $this->selectedAgentId);
            
            // Re-fetch the layout to refresh sidebar. Livewire will re-render cleanly.
            $this->closeAssignAgentModal();
            $this->successMessage = 'Chat assigned securely.';
        } catch (Exception $e) {
            $this->assignAgentError = 'Error assigning chat: ' . $e->getMessage();
        }
    }

    public function openTemplateSendModal(\App\Services\Template\ChatTemplateDirectoryService $directoryService)
    {
        $this->resetMessages();
        
        if (!$this->selectedConversationId) {
            $this->errorMessage = 'No active conversation selected.';
            return;
        }

        $this->showTemplateModal = true;
        $this->templateSearch = '';
        $this->templateFilter = 'all';
        $this->selectedTemplateId = null;
        $this->selectedTemplatePreview = null;
        $this->templateModalError = null;
        
        $this->loadTemplates($directoryService);
        
        $this->systemVariableOptions = app(\App\Services\WhatsApp\WhatsAppTemplateVariableResolver::class)->getAvailableSystemVariables();
        
        // Auto-select first if available
        if (!empty($this->availableTemplates)) {
            $this->selectTemplate($this->availableTemplates[0]['id'], $directoryService);
        }
    }

    public function closeTemplateSendModal()
    {
        $this->showTemplateModal = false;
        $this->resetTemplateModalState();
    }

    public function updatedTemplateSearch(\App\Services\Template\ChatTemplateDirectoryService $directoryService)
    {
        $this->loadTemplates($directoryService);
    }

    public function updatedTemplateFilter(\App\Services\Template\ChatTemplateDirectoryService $directoryService)
    {
        $this->loadTemplates($directoryService);
    }

    public function loadTemplates(\App\Services\Template\ChatTemplateDirectoryService $directoryService)
    {
        $this->availableTemplates = $directoryService->getChatEligibleTemplatesForUser(auth()->user(), [
            'search' => $this->templateSearch,
            'filter' => $this->templateFilter,
        ]);
    }

    public function selectTemplate(int $templateId, \App\Services\Template\ChatTemplateDirectoryService $directoryService)
    {
        $this->selectedTemplateId = $templateId;
        $this->selectedTemplatePreview = $directoryService->getTemplatePreview(auth()->user(), $templateId);
        
        // Initialize variables mapping
        $this->templateVariables = [];
        if (!empty($this->selectedTemplatePreview['variables'])) {
            foreach ($this->selectedTemplatePreview['variables'] as $variable) {
                $this->templateVariables[$variable] = [
                    'type' => 'system',
                    'value' => 'contact_name', // Default to contact name if possible
                ];
            }
        }

        $this->refreshTemplatePreview();
    }

    public function updatedTemplateVariables()
    {
        $this->refreshTemplatePreview();
    }

    protected function refreshTemplatePreview()
    {
        if (!$this->selectedTemplatePreview || empty($this->templateVariables)) {
            return;
        }

        $resolver = app(\App\Services\WhatsApp\WhatsAppTemplateVariableResolver::class);
        $conversation = \App\Models\Chat\Conversation::find($this->selectedConversationId);
        
        $body = $this->selectedTemplatePreview['original_body_text'] ?? implode("\n", $this->selectedTemplatePreview['preview_paragraphs']);
        
        // Save original if not already there
        if (!isset($this->selectedTemplatePreview['original_body_text'])) {
            $this->selectedTemplatePreview['original_body_text'] = $body;
        }

        $resolvedBody = $resolver->resolveAllForPreview($body, $this->templateVariables, $conversation, auth()->user());
        $this->selectedTemplatePreview['preview_paragraphs'] = explode("\n", $resolvedBody);
    }

    public function sendSelectedTemplate(\App\Services\Chat\ChatTemplateSendService $sendService)
    {
        $this->templateModalError = null;

        if (!$this->selectedTemplateId) {
            $this->templateModalError = 'Please select a template to send.';
            return;
        }

        // Build WhatsApp components payload
        $components = [];
        if (!empty($this->templateVariables)) {
            $resolver = app(\App\Services\WhatsApp\WhatsAppTemplateVariableResolver::class);
            $conversation = \App\Models\Chat\Conversation::find($this->selectedConversationId);
            $parameters = [];

            foreach ($this->templateVariables as $variable => $config) {
                $value = $resolver->getValueFromMapping($config, $conversation, auth()->user());
                
                if (empty(trim((string)$value))) {
                    $this->templateModalError = "Please provide a value for Variable {{ $variable }}";
                    return;
                }

                $parameters[] = [
                    'type' => 'text',
                    'text' => $value,
                ];
            }

            $components[] = [
                'type' => 'body',
                'parameters' => $parameters,
            ];
        }

        try {
            $result = $sendService->sendTemplateToConversation(
                auth()->user(), 
                $this->selectedConversationId, 
                $this->selectedTemplateId,
                ['components' => $components]
            );

            if ($result['success']) {
                $this->closeTemplateSendModal();
                $this->successMessage = 'Template sent successfully.';
            }
        } catch (Exception $e) {
            $this->templateModalError = $e->getMessage();
        }
    }

    private function resetTemplateModalState()
    {
        $this->templateSearch = '';
        $this->templateFilter = 'all';
        $this->selectedTemplateId = null;
        $this->selectedTemplatePreview = null;
        $this->availableTemplates = [];
        $this->templateModalError = null;
    }

    #[On('realtime-message-received')]
    public function handleRealtimeMessage($payload)
    {
        // For backward compatibility if other parts of the app use this event
    }

    public function getListeners()
    {
        $userId = auth()->id();
        $listeners = [
            "echo-private:company.{$this->companyId}.chats,.chat.inbound.received" => 'refreshChatDataAfterRealtimeEvent',
            "echo-private:company.{$this->companyId}.chats,.conversation.updated" => 'refreshChatDataAfterRealtimeEvent',
            "refresh-chat-data" => 'refreshChatDataAfterRealtimeEvent',
            "realtime-conversation-updated" => 'handleRealtimeConversationUpdate',
        ];

        if ($this->selectedConversationId) {
            $listeners["echo-private:company.{$this->companyId}.conversation.{$this->selectedConversationId},message.received"] = 'refreshChatDataAfterRealtimeEvent';
            $listeners["echo-private:conversations.{$this->selectedConversationId},WhatsApp\\MessageStatusUpdated"] = 'onMessageStatusUpdated';
        }

        return $listeners;
    }

    public function onMessageStatusUpdated($data)
    {
        // Simply refresh the component to pick up status changes
    }

    public function refreshChatDataAfterRealtimeEvent($payload = null)
    {
        // Triggers a component refresh. Data is re-fetched in render().
    }

    public function handleRealtimeConversationUpdate($payload)
    {
        // Triggers a component refresh to update the sidebar.
    }

    private function syncNoteText()
    {
        if (!$this->selectedConversationId) {
            $this->noteText = '';
            return;
        }

        $conversation = \App\Models\Chat\Conversation::find($this->selectedConversationId);
        $latestNote = $conversation?->notes()->latest()->first();
        $this->noteText = $latestNote?->note ?? '';
    }

    private function resetMessages()
    {
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    public function render(ChatInboxService $inboxService)
    {
        $user = auth()->user();
        
        $data = $inboxService->getInboxDataForUser($user, [
            'search' => $this->search,
            'tab' => $this->tab,
            'selected_conversation_id' => $this->selectedConversationId,
        ]);

        $this->channelAvailability = $data['channel_availability'];

        return view('livewire.web.chats.chat-inbox-page', [
            'conversationList' => $data['conversations'],
            'activeConversation' => $data['activeConversation'],
            'messages' => $data['messages'],
            'sidebarData' => $data['sidebarData'],
            'hasAvailableChannels' => $data['channel_availability']['has_available_channels'],
            'agentInitials' => strtoupper(substr($user->name, 0, 2)),
            'agentName' => $user->name,
            'agentStatusLabel' => 'Online',
        ])->layout('layouts.panel', ['activeNav' => 'chats']);
    }
}
