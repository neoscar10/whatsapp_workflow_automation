<?php

namespace App\Livewire\Web\Chats;

use App\Services\Chat\ChatConversationActionService;
use App\Services\Chat\ChatInboxService;
use App\Services\Chat\ChatMessageService;
use Exception;
use Livewire\Attributes\On;
use Livewire\Component;

use Livewire\WithFileUploads;

class ChatInboxPage extends Component
{
    use WithFileUploads;
    public string $search = '';
    public string $tab = 'all';
    public ?int $selectedConversationId = null;
    public int $companyId;
    
    public string $messageText = '';
    public string $noteText = '';
    
    // Media Composer State
    public $composerMedia;
    public array $composerMediaMetadata = [];
    public string $composerCaption = '';

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
    public $templateHeaderMedia;
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

    public function updatedComposerMedia()
    {
        $this->errorMessage = null;
        
        try {
            $this->validate([
                'composerMedia' => 'required|file|max:65536', // 64MB max for WhatsApp
            ]);

            // Capture metadata safely while file is definitely there
            $this->composerMediaMetadata = [
                'name' => $this->composerMedia->getClientOriginalName(),
                'size' => $this->composerMedia->getSize(),
                'mime' => $this->composerMedia->getMimeType(),
                'preview_url' => str_starts_with($this->composerMedia->getMimeType(), 'image/') 
                    ? $this->composerMedia->temporaryUrl() 
                    : null,
            ];

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->composerMedia = null;
            $this->composerMediaMetadata = [];
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->composerMedia = null;
            $this->composerMediaMetadata = [];
            $this->errorMessage = 'Error processing file: ' . $e->getMessage();
        }
    }

    public function removeComposerMedia()
    {
        $this->composerMedia = null;
        $this->composerMediaMetadata = [];
        $this->composerCaption = '';
    }

    public function sendMessage(ChatMessageService $messageService)
    {
        $this->resetMessages();
        
        $text = trim($this->messageText);
        
        // Handle Media Send
        if ($this->composerMedia) {
            if (!$this->selectedConversationId) {
                $this->errorMessage = 'No active conversation selected to send media to.';
                return;
            }

            try {
                $caption = trim($this->messageText) ?: null;
                $result = $messageService->sendMediaMessage(auth()->user(), $this->selectedConversationId, $this->composerMedia, $caption);
                
                if ($result) {
                    $this->messageText = '';
                    $this->composerMedia = null;
                    $this->successMessage = "Media sent successfully.";
                } else {
                    $this->errorMessage = 'Failed to send media.';
                }
            } catch (Exception $e) {
                $this->errorMessage = 'Error sending media: ' . $e->getMessage();
            }
            return;
        }

        // Handle Text Send
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
            foreach ($this->selectedTemplatePreview['variables'] as $varData) {
                $key = "{$varData['component']}:{$varData['name']}";
                $this->templateVariables[$key] = [
                    'component' => $varData['component'],
                    'name' => $varData['name'],
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
        $header = $this->selectedTemplatePreview['original_header_text'] ?? ($this->selectedTemplatePreview['header_text'] ?? '');
        
        // Save originals if not already there
        if (!isset($this->selectedTemplatePreview['original_body_text'])) {
            $this->selectedTemplatePreview['original_body_text'] = $body;
        }
        if (!isset($this->selectedTemplatePreview['original_header_text'])) {
            $this->selectedTemplatePreview['original_header_text'] = $header;
        }

        $this->selectedTemplatePreview['preview_paragraphs'] = explode("\n", $resolver->resolveAllForPreview($body, $this->templateVariables, $conversation, auth()->user()));
        $this->selectedTemplatePreview['preview_header'] = $resolver->resolveAllForPreview($header, $this->templateVariables, $conversation, auth()->user());
    }

    public function sendSelectedTemplate(\App\Services\Chat\ChatTemplateSendService $sendService)
    {
        $this->templateModalError = null;

        if (!$this->selectedTemplateId) {
            $this->templateModalError = 'Please select a template to send.';
            return;
        }

        try {
            $apiComponents = [];
            
            // 1. Handle Media Header if required
            $headerType = $this->selectedTemplatePreview['header_type'] ?? 'none';
            if (in_array($headerType, ['image', 'video', 'document'])) {
                if (!$this->templateHeaderMedia) {
                    $this->templateModalError = "Please upload an " . ucfirst($headerType) . " for the header.";
                    return;
                }

                $mediaId = null;
                if ($this->templateHeaderMedia instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    $mediaService = app(\App\Services\WhatsApp\MetaMediaUploadService::class);
                    $account = \App\Models\WhatsApp\WhatsAppAccount::where('company_id', auth()->user()->company_id)->first();
                    $phoneNumber = \App\Models\WhatsApp\WhatsAppPhoneNumber::where('whatsapp_account_id', $account->id)->first();
                    
                    if (!$phoneNumber) {
                        throw new Exception("WhatsApp Phone Number not found for this account.");
                    }

                    $mediaId = $mediaService->uploadMessageMedia($phoneNumber->phone_number_id, $account->access_token, $this->templateHeaderMedia);
                }

                if ($mediaId) {
                    $apiComponents[] = [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => strtolower($headerType),
                                strtolower($headerType) => ['id' => $mediaId]
                            ]
                        ]
                    ];
                }
            }

            // 2. Resolve Variables (Header Text & Body)
            if (!empty($this->templateVariables)) {
                $resolver = app(\App\Services\WhatsApp\WhatsAppTemplateVariableResolver::class);
                $conversation = \App\Models\Chat\Conversation::find($this->selectedConversationId);
                
                $groupedParams = ['header' => [], 'body' => []];

                foreach ($this->templateVariables as $key => $config) {
                    $value = $resolver->getValueFromMapping($config, $conversation, auth()->user());
                    
                    if (empty(trim((string)$value))) {
                        $this->templateModalError = "Please provide a value for Variable {{ {$config['name']} }}";
                        return;
                    }

                    $componentType = $config['component'] ?? 'body';
                    $groupedParams[$componentType][] = [
                        'type' => 'text',
                        'text' => (string)$value,
                    ];
                }

                // Add Text Header parameters if any
                if (!empty($groupedParams['header'])) {
                    $apiComponents[] = [
                        'type' => 'header',
                        'parameters' => $groupedParams['header'],
                    ];
                }

                // Add Body parameters if any
                if (!empty($groupedParams['body'])) {
                    $apiComponents[] = [
                        'type' => 'body',
                        'parameters' => $groupedParams['body'],
                    ];
                }
            }

            // 3. Dispatch Send
            $options = !empty($apiComponents) ? ['components' => $apiComponents] : [];
            $result = $sendService->sendTemplateToConversation(
                auth()->user(),
                $this->selectedConversationId,
                $this->selectedTemplateId,
                $options
            );

            if ($result['success']) {
                $this->closeTemplateSendModal();
                $this->templateHeaderMedia = null; // Reset media state
                $this->successMessage = "Template sent successfully.";
            } else {
                $this->templateModalError = $result['error'] ?? 'Failed to send template.';
            }
            
        } catch (\Exception $e) {
            Log::error('Template Send Error', ['message' => $e->getMessage()]);
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
