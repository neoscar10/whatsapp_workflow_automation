<div 
    x-data="{ 
        showLeftSidebar: true, 
        showRightSidebar: true,
        showScrollButton: false,
        companyChannel: null,
        activeChannel: null,
        selectedId: @entangle('selectedConversationId'),
        scrollToBottom() {
            const container = this.$refs.messageContainer;
            if (container) {
                container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
            }
        },
        initEcho() {
            const companyId = {{ (int) $this->effectiveCompanyId }};
            
            const setupListeners = () => {
                if (!window.Echo) {
                    console.warn('[WebSockets] window.Echo is not ready yet, retrying...');
                    setTimeout(setupListeners, 500);
                    return;
                }

                console.log('[WebSockets] Initializing Echo for company ' + companyId);

                // Subscribe to company-wide chats channel
                if (!this.companyChannel) {
                    this.companyChannel = window.Echo.private(`company.${companyId}.chats`);
                    this.companyChannel
                        .subscribed(() => console.log('[WebSockets] Subscribed to company.' + companyId + '.chats'))
                        .listen('.chat.inbound.received', (e) => {
                            console.log('[WebSockets] Inbound message received:', e);
                            $wire.$refresh();
                        })
                        .listen('.message.received', (e) => {
                            console.log('[WebSockets] Message received on company channel:', e);
                            $wire.$refresh().then(() => this.scrollToBottom());
                        })
                        .listen('.conversation.updated', (e) => {
                            console.log('[WebSockets] Conversation updated on company channel:', e);
                            $wire.$refresh();
                        });
                }

                // Subscribe to active conversation channel dynamically
                this.$watch('selectedId', (newId, oldId) => {
                    this.subscribeToConversation(companyId, newId, oldId);
                });

                if (this.selectedId) {
                    this.subscribeToConversation(companyId, this.selectedId, null);
                }
            };

            setupListeners();
        },
        subscribeToConversation(companyId, newId, oldId) {
            if (oldId && oldId !== newId) {
                console.log('[WebSockets] Leaving conversation channel: company.' + companyId + '.conversation.' + oldId);
                window.Echo.leave(`company.${companyId}.conversation.${oldId}`);
            }

            if (newId) {
                console.log('[WebSockets] Subscribing to conversation channel: company.' + companyId + '.conversation.' + newId);
                window.Echo.private(`company.${companyId}.conversation.${newId}`)
                    .listen('.message.received', (e) => {
                        console.log('[WebSockets] Message received on active conversation:', e);
                        $wire.$refresh().then(() => this.scrollToBottom());
                    })
                    .listen('.conversation.updated', (e) => {
                        console.log('[WebSockets] Active conversation updated:', e);
                        $wire.$refresh();
                    });
            }
        }
    }" 
    x-init="initEcho()"
    wire:poll.3s="refreshChatDataAfterRealtimeEvent"
    class="flex flex-1 w-full relative overflow-hidden wa-page-root antialiased min-h-[500px]"
>
    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Chat Area --}}
        <main class="flex flex-1 overflow-hidden">
            {{-- Chat List Column --}}
            <section 
                x-show="showLeftSidebar" 
                x-transition:enter="transition-all ease-out duration-300 origin-left"
                x-transition:enter-start="opacity-0 -translate-x-4 w-0"
                x-transition:enter-end="opacity-100 translate-x-0 w-64 md:w-80"
                x-transition:leave="transition-all ease-in duration-200 origin-left"
                x-transition:leave-start="opacity-100 translate-x-0 w-64 md:w-80"
                x-transition:leave-end="opacity-0 -translate-x-4 w-0"
                class="flex w-64 flex-shrink-0 flex-col border-r wa-border-subtle wa-sidebar md:w-80 override-transition"
            >
                <div class="border-b wa-border-subtle p-3.5 wa-header">
                    <div class="mb-3 flex gap-2">
                        @if($hasAvailableChannels && !empty($channelAvailability['channels']))
                            <select 
                                wire:model.live="selectedPhoneNumberId"
                                class="flex-1 rounded-lg border wa-border-subtle wa-page-root py-2 pl-3 pr-8 text-xs font-semibold wa-text-primary transition-colors focus:border-[#00a884] appearance-none outline-none"
                                style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%238696a0%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.7rem top 50%; background-size: 0.65rem auto;"
                            >
                                <option value="">All Phone Numbers</option>
                                @foreach($channelAvailability['channels'] as $channel)
                                    <option value="{{ $channel['id'] }}">{{ $channel['display_name'] }} ({{ $channel['phone_number'] }})</option>
                                @endforeach
                            </select>
                        @else
                            <div class="flex flex-1 items-center justify-center rounded-lg border wa-border-subtle wa-page-root py-2 text-xs font-semibold wa-text-secondary">
                                No Active Numbers
                            </div>
                        @endif

                        <button
                            type="button"
                            class="rounded-lg border wa-border-subtle wa-panel p-2 wa-text-secondary transition-colors hover:wa-text-primary"
                        >
                            <span class="material-symbols-outlined text-sm">filter_list</span>
                        </button>
                    </div>

                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] wa-text-secondary">search</span>
                            <input
                                wire:model.live.debounce.300ms="search"
                                type="text"
                                placeholder="Search or start new chat"
                                class="w-full rounded-lg wa-no-border wa-page-root py-2 pl-10 pr-4 text-xs wa-text-primary placeholder:text-[#8696a0] focus:ring-1 focus:ring-[#00a884] outline-none"
                            />
                        </div>
                        
                        <button
                            type="button"
                            wire:click="openInitiateChatModal"
                            class="flex shrink-0 items-center justify-center rounded-lg wa-btn-primary px-3 text-white transition-colors"
                            title="New Chat"
                        >
                            <span class="material-symbols-outlined text-sm">add_comment</span>
                        </button>
                    </div>
                </div>
                
                <div class="no-scrollbar flex gap-2 border-b wa-border-subtle wa-page-root px-3 py-2.5 shrink-0 overflow-x-auto">
                    <button
                        type="button"
                        wire:click="$set('tab', 'all')"
                        class="whitespace-nowrap rounded-full px-3 py-1 text-xs transition-colors {{ $tab === 'all' ? 'wa-filter-active font-semibold' : 'wa-filter-inactive font-medium' }}"
                    >
                        All
                    </button>
                    <button
                        type="button"
                        wire:click="$set('tab', 'active')"
                        class="whitespace-nowrap rounded-full px-3 py-1 text-xs transition-colors {{ $tab === 'active' ? 'wa-filter-active font-semibold' : 'wa-filter-inactive font-medium' }}"
                    >
                        Active (24h)
                    </button>
                    <button
                        type="button"
                        wire:click="$set('tab', 'inactive')"
                        class="whitespace-nowrap rounded-full px-3 py-1 text-xs transition-colors {{ $tab === 'inactive' ? 'wa-filter-active font-semibold' : 'wa-filter-inactive font-medium' }}"
                    >
                        Inactive
                    </button>
                </div>

                <div class="no-scrollbar flex-1 overflow-y-auto wa-page-root">
                    @forelse($conversationList as $conversation)
                        <button
                            type="button"
                            wire:click="selectConversation({{ $conversation['id'] }})"
                            class="flex w-full cursor-pointer gap-3 border-b wa-border-subtle p-3.5 text-left transition-colors {{ (int) $selectedConversationId === (int) $conversation['id'] ? 'wa-item-active' : 'wa-page-root wa-item-hover' }}"
                        >
                            <div class="relative flex-shrink-0">
                                @if(!empty($conversation['avatar_url']))
                                    <div class="h-12 w-12 rounded-full wa-header bg-cover bg-center" style="background-image: url('{{ $conversation['avatar_url'] }}');"></div>
                                @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full wa-header wa-text-secondary">
                                        <span class="material-symbols-outlined text-2xl">person</span>
                                    </div>
                                @endif

                                @if(!empty($conversation['is_session_active']))
                                    <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-[#111b21] bg-[#00a884]" title="Active Window"></span>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex items-start justify-between">
                                    <h3 class="truncate text-sm font-semibold wa-text-primary">{{ $conversation['name'] }}</h3>
                                    <span class="text-[11px] {{ ($conversation['unread_count'] ?? 0) > 0 ? 'wa-text-accent font-semibold' : 'wa-text-secondary' }}">{{ $conversation['time_label'] }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-xs wa-text-secondary">
                                        {{ $conversation['preview'] }}
                                    </p>

                                    @if(($conversation['unread_count'] ?? 0) > 0)
                                        <span class="flex h-5 min-w-[20px] px-1.5 shrink-0 items-center justify-center rounded-full wa-btn-primary text-[11px] font-bold text-[#111b21]">
                                            {{ $conversation['unread_count'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="p-8 text-center text-xs wa-text-secondary">
                            No conversations match your criteria.
                        </div>
                    @endforelse
                </div>
            </section>

            @if($activeConversation)
                {{-- Column 2: Chat Window --}}
                <section class="flex flex-1 flex-col border-r wa-border-subtle wa-chat-container">
                    <header class="flex h-16 shrink-0 items-center justify-between border-b wa-border-subtle wa-header px-4 md:px-6">
                        <div class="flex items-center gap-3">
                            <button @click="showLeftSidebar = !showLeftSidebar" class="p-2 wa-text-secondary transition-colors hover:wa-text-primary rounded-lg hover:bg-[#2a3942]">
                                <span class="material-symbols-outlined" x-text="showLeftSidebar ? 'keyboard_double_arrow_left' : 'keyboard_double_arrow_right'"></span>
                            </button>
                            <div>
                                <h2 class="leading-none font-bold wa-text-primary text-base">{{ $activeConversation['name'] }}</h2>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="text-xs wa-text-secondary">{{ $activeConversation['phone'] }}</span>
                                    @if(!empty($activeConversation['is_session_active']))
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#00a884]"></span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider wa-text-accent">Active Session</span>
                                    @else
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#8696a0]"></span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider wa-text-secondary">Session Expired</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button @click="showRightSidebar = !showRightSidebar" class="p-2 wa-text-secondary transition-colors hover:wa-text-primary rounded-lg hover:bg-[#2a3942]" title="Toggle Sidebar">
                                <span class="material-symbols-outlined">view_sidebar</span>
                            </button>
                        </div>
                    </header>

                    <div 
                        x-ref="messageContainer"
                        @scroll="showScrollButton = $el.scrollTop < ($el.scrollHeight - $el.clientHeight - 150)"
                        x-init="
                            $nextTick(() => { $el.scrollTop = $el.scrollHeight });
                            const observer = new MutationObserver(() => {
                                if (!showScrollButton) {
                                    $el.scrollTo({ top: $el.scrollHeight, behavior: 'smooth' });
                                }
                            });
                            observer.observe($el, { childList: true, subtree: true });
                        "
                        class="no-scrollbar relative flex flex-1 flex-col gap-3 overflow-y-auto wa-chat-bg p-4 md:p-6"
                    >
                        @foreach($messages as $message)
                            @if($message['message_type'] === 'card')
                                <div class="my-2 flex justify-center">
                                    <div class="w-full max-w-[320px] overflow-hidden rounded-lg wa-header shadow-md wa-text-primary border wa-border-subtle">
                                        <div class="flex items-center gap-2.5 bg-[#005c4b]/30 px-4 py-2.5 border-b wa-border-subtle">
                                            <span class="material-symbols-outlined wa-text-accent text-sm">shopping_bag</span>
                                            <h4 class="text-xs font-bold uppercase tracking-wider wa-text-accent">
                                                {{ $message['card_title'] ?? 'Card' }}
                                            </h4>
                                        </div>
                                        <div class="p-4">
                                            <p class="mb-1 text-sm font-semibold wa-text-primary">{{ $message['card_heading'] ?? '' }}</p>
                                            <p class="mb-4 text-xs wa-text-secondary">{{ $message['card_subtext'] ?? '' }}</p>
                                            @if(!empty($message['card_button_text']))
                                                <button class="w-full rounded-lg wa-btn-primary py-2 text-xs font-bold text-white transition-colors">
                                                    {{ $message['card_button_text'] }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @elseif(in_array($message['message_type'], ['image', 'video', 'audio', 'document']))
                                <div class="flex max-w-[75%] md:max-w-[65%] flex-col {{ $message['direction'] === 'outbound' ? 'self-end items-end' : 'items-start' }}">
                                    <div class="rounded-lg {{ $message['direction'] === 'outbound' ? 'rounded-tr-none wa-bubble-outbound' : 'rounded-tl-none wa-bubble-inbound' }} p-1.5 shadow-sm">
                                        
                                        @if($message['message_type'] === 'image')
                                            <div class="w-72 overflow-hidden rounded-lg wa-page-root aspect-video group relative">
                                                @if(!empty($message['resolved_media_url']))
                                                    <img 
                                                        src="{{ $message['resolved_media_url'] }}" 
                                                        alt="Message image" 
                                                        class="h-full w-full object-cover"
                                                        onerror="this.onerror=null; this.src='https://placehold.co/400x300/111b21/8696a0?text=Image+Unavailable'; this.parentElement.classList.add('opacity-50');"
                                                    >
                                                    <a href="{{ $message['resolved_media_url'] }}" target="_blank" class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <span class="material-symbols-outlined text-white">open_in_new</span>
                                                    </a>
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center wa-page-root">
                                                        <div class="text-center p-4">
                                                            <span class="material-symbols-outlined wa-text-secondary">image_not_supported</span>
                                                            <p class="text-[10px] wa-text-secondary font-bold uppercase mt-1">Image unavailable</p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($message['message_type'] === 'video')
                                            <div class="w-72 overflow-hidden rounded-lg wa-page-root aspect-video relative group flex items-center justify-center">
                                                @if(!empty($message['resolved_media_url']))
                                                    <video class="h-full w-full object-cover opacity-60">
                                                        <source src="{{ $message['resolved_media_url'] }}">
                                                    </video>
                                                    <a href="{{ $message['resolved_media_url'] }}" target="_blank" class="absolute inset-0 flex items-center justify-center transition-transform hover:scale-110">
                                                        <div class="flex h-12 w-12 items-center justify-center rounded-full wa-btn-primary shadow-lg">
                                                            <span class="material-symbols-outlined text-3xl">play_arrow</span>
                                                        </div>
                                                    </a>
                                                @else
                                                    <div class="text-center p-4">
                                                        <span class="material-symbols-outlined wa-text-secondary">videocam_off</span>
                                                        <p class="text-[10px] wa-text-secondary font-bold uppercase mt-1">Video unavailable</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($message['message_type'] === 'audio')
                                            <div class="w-72 p-2.5 wa-page-root/60 rounded-lg flex items-center gap-3 border wa-border-subtle">
                                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#00a884]/20 wa-text-accent">
                                                    <span class="material-symbols-outlined text-lg">audiotrack</span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[10px] font-bold uppercase wa-text-secondary tracking-wider leading-none">Voice Memo</p>
                                                    @if(!empty($message['resolved_media_url']))
                                                        <audio controls class="mt-1.5 h-7 w-full scale-95 -ml-[2%]">
                                                            <source src="{{ $message['resolved_media_url'] }}">
                                                        </audio>
                                                    @else
                                                        <p class="text-[10px] font-bold wa-text-secondary mt-1">AUDIO UNAVAILABLE</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif($message['message_type'] === 'document')
                                            <a href="{{ $message['resolved_media_url'] ?? '#' }}" target="_blank" class="w-72 p-3 wa-page-root/60 rounded-lg flex items-center gap-3 border wa-border-subtle hover:wa-page-root transition-colors group">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-lg wa-header wa-text-accent border wa-border-subtle">
                                                    <span class="material-symbols-outlined text-2xl">description</span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[11px] font-bold wa-text-primary truncate pr-2">{{ $message['media_meta']['filename'] ?? 'document.pdf' }}</p>
                                                    <p class="text-[10px] font-bold wa-text-secondary mt-0.5">{{ strtoupper(isset($message['media_meta']['filename']) ? pathinfo($message['media_meta']['filename'], PATHINFO_EXTENSION) : 'PDF') }}</p>
                                                </div>
                                                @if(!empty($message['resolved_media_url']))
                                                    <span class="material-symbols-outlined wa-text-secondary group-hover:wa-text-accent transition-colors pr-1">download</span>
                                                @endif
                                            </a>
                                        @endif

                                        @if(!empty($message['body']))
                                            <div class="px-2 py-1.5">
                                                <p class="text-sm wa-text-primary leading-relaxed">{{ $message['body'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-1 {{ $message['direction'] === 'outbound' ? 'mr-1 flex items-center gap-1' : 'ml-1' }}">
                                        <span class="text-[10px] wa-text-secondary">{{ $message['time_label'] }}</span>
                                        @if($message['direction'] === 'outbound' && !empty($message['status_icon']))
                                            <span 
                                                class="material-symbols-outlined text-[14px] {{ $message['status_color'] === 'text-primary' || $message['status_icon'] === 'done_all' ? 'wa-text-blue' : 'wa-text-secondary' }}"
                                                @if(!empty($message['failure_message'])) title="{{ $message['failure_message'] }}" @endif
                                            >
                                                {{ $message['status_icon'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="flex max-w-[75%] md:max-w-[65%] flex-col {{ $message['direction'] === 'outbound' ? 'self-end items-end' : 'items-start' }}">
                                    <div class="{{ $message['direction'] === 'outbound' ? 'rounded-lg rounded-tr-none wa-bubble-outbound' : 'rounded-lg rounded-tl-none wa-bubble-inbound' }} px-3.5 py-2 shadow-sm">
                                        @if($message['message_type'] === 'template')
                                            <div class="mb-1 flex items-center gap-1.5 wa-text-accent opacity-90">
                                                <span class="material-symbols-outlined text-xs">auto_awesome</span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider">WhatsApp Template</span>
                                            </div>
                                        @endif
                                        <p class="text-sm leading-relaxed wa-text-primary select-text">{{ $message['body'] }}</p>
                                    </div>

                                    <div class="mt-1 {{ $message['direction'] === 'outbound' ? 'mr-1 flex items-center gap-1' : 'ml-1' }}">
                                        <span class="text-[10px] wa-text-secondary">{{ $message['time_label'] }}</span>
                                        @if($message['direction'] === 'outbound' && !empty($message['status_icon']))
                                            <span 
                                                class="material-symbols-outlined text-[14px] {{ $message['status_color'] === 'text-primary' || $message['status_icon'] === 'done_all' ? 'wa-text-blue' : 'wa-text-secondary' }}"
                                                @if(!empty($message['failure_message'])) title="{{ $message['failure_message'] }}" @endif
                                            >
                                                {{ $message['status_icon'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        {{-- Scroll to bottom button --}}
                        <div 
                            x-show="showScrollButton"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-4"
                            class="sticky bottom-4 right-4 z-20 flex justify-end pr-2"
                        >
                            <button 
                                @click="scrollToBottom()"
                                class="flex h-9 w-9 items-center justify-center rounded-full wa-header wa-text-accent shadow-xl border wa-border-subtle transition-all hover:bg-[#2a3942] active:scale-95"
                            >
                                <span class="material-symbols-outlined text-xl">keyboard_double_arrow_down</span>
                            </button>
                        </div>
                    </div>

                    <div class="border-t wa-border-subtle wa-header p-3 md:p-4">
                        @if($errorMessage)
                            <div class="mb-3 rounded-lg border border-red-500/30 bg-red-950/40 px-3 py-2 text-xs text-red-300">
                                {{ $errorMessage }}
                            </div>
                        @endif

                        @if($successMessage)
                            <div class="mb-3 rounded-lg border border-emerald-500/30 bg-emerald-950/40 px-3 py-2 text-xs text-emerald-300">
                                {{ $successMessage }}
                            </div>
                        @endif

                        {{-- Media Preview Tray --}}
                        @if(!empty($composerMediaMetadata))
                            <div class="mb-3 animate-in fade-in slide-in-from-bottom-2">
                                <div class="relative flex items-center gap-3 rounded-lg border wa-border-subtle wa-page-root p-3 shadow-md">
                                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg wa-header flex items-center justify-center border wa-border-subtle">
                                        @if($composerMediaMetadata['preview_url'])
                                            <img src="{{ $composerMediaMetadata['preview_url'] }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="material-symbols-outlined wa-text-accent text-[28px]">
                                                {{ str_starts_with($composerMediaMetadata['mime'], 'video/') ? 'movie' : (str_starts_with($composerMediaMetadata['mime'], 'audio/') ? 'audiotrack' : 'description') }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-bold wa-text-primary">
                                            {{ $composerMediaMetadata['name'] }}
                                        </p>
                                        <div class="mt-1 flex items-center gap-2">
                                            <span class="rounded wa-header px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider wa-text-secondary">
                                                {{ explode('/', $composerMediaMetadata['mime'])[1] ?? 'File' }}
                                            </span>
                                            <span class="text-[10px] font-medium wa-text-secondary">
                                                {{ number_format($composerMediaMetadata['size'] / 1024, 1) }} KB
                                            </span>
                                        </div>
                                    </div>

                                    <button 
                                        type="button" 
                                        wire:click="removeComposerMedia"
                                        class="flex h-7 w-7 items-center justify-center rounded-full wa-header wa-text-secondary transition-all hover:bg-red-900/30 hover:text-red-400"
                                    >
                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if($activeConversation['is_session_active'])
                            <div class="flex items-center gap-2 rounded-lg wa-composer-pill p-1.5 pl-3 transition-colors">
                                <div x-data="{ showEmoji: false }" class="relative flex items-center">
                                    <button @click="showEmoji = !showEmoji" type="button" class="p-1.5 wa-text-secondary transition-colors hover:wa-text-primary" title="Emoji">
                                        <span class="material-symbols-outlined text-2xl">sentiment_satisfied</span>
                                    </button>
                                    
                                    <div 
                                        x-show="showEmoji" 
                                        @click.away="showEmoji = false" 
                                        class="absolute bottom-full left-0 mb-3 z-50 shadow-2xl rounded-xl overflow-hidden border wa-border-subtle"
                                        style="display: none;"
                                    >
                                        <emoji-picker 
                                            @emoji-click="$wire.set('messageText', ($wire.messageText || '') + $event.detail.unicode); showEmoji = false; $nextTick(() => { document.getElementById('messageInput').focus(); });"
                                        ></emoji-picker>
                                    </div>
                                </div>
                                
                                {{-- Hidden File Input --}}
                                <input 
                                    type="file" 
                                    id="composerMediaInput" 
                                    class="hidden" 
                                    wire:model="composerMedia"
                                    accept="image/*,video/*,audio/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                >
                                <button 
                                    type="button" 
                                    onclick="document.getElementById('composerMediaInput').click()"
                                    class="p-1.5 wa-text-secondary transition-colors hover:wa-text-primary {{ !empty($composerMediaMetadata) ? 'wa-text-accent bg-[#00a884]/10 rounded-lg' : '' }}"
                                    title="Attach File"
                                >
                                    <span class="material-symbols-outlined text-2xl">attach_file</span>
                                </button>

                                <button 
                                    type="button" 
                                    wire:click="openTemplateSendModal"
                                    class="p-1.5 wa-text-secondary transition-colors hover:wa-text-accent"
                                    title="Send Template"
                                >
                                    <span class="material-symbols-outlined text-2xl">auto_awesome</span>
                                </button>

                                <input id="messageInput"
                                    wire:model.defer="messageText"
                                    type="text"
                                    placeholder="{{ !empty($composerMediaMetadata) ? 'Add a caption...' : 'Type a message' }}"
                                    class="flex-1 wa-composer-input px-2 text-sm outline-none shadow-none"
                                    wire:keydown.enter="sendMessage"
                                />

                                <button
                                    type="button"
                                    wire:click="sendMessage"
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full wa-btn-primary shadow-md transition-all active:scale-95"
                                    wire:loading.attr="disabled"
                                    wire:target="sendMessage, composerMedia"
                                >
                                    <span class="material-symbols-outlined text-xl" wire:loading.remove wire:target="sendMessage, composerMedia">send</span>
                                    <div wire:loading wire:target="sendMessage, composerMedia" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                                </button>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center gap-3 rounded-lg border border-dashed wa-border-subtle wa-page-root p-5 text-center">
                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-500/10 text-amber-400">
                                    <span class="material-symbols-outlined text-2xl">history_toggle_off</span>
                                </div>
                                <div class="text-center">
                                    <h4 class="text-xs font-bold wa-text-primary">Messaging Window Closed</h4>
                                    <p class="mt-1 text-xs wa-text-secondary">It's been more than 24 hours since the user last messaged you. Send a pre-approved template to re-engage.</p>
                                </div>
                                <button 
                                    type="button" 
                                    wire:click="openTemplateSendModal"
                                    class="flex items-center gap-2 rounded-lg wa-btn-primary px-5 py-2.5 text-xs font-bold text-white shadow-md transition-all active:scale-95"
                                >
                                    <span class="material-symbols-outlined text-base">auto_awesome</span>
                                    Send WhatsApp Template
                                </button>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Column 3: Contact & Actions --}}
                <section 
                    x-show="showRightSidebar"
                    x-transition:enter="transition-all ease-out duration-300 origin-right"
                    x-transition:enter-start="opacity-0 translate-x-4 w-0"
                    x-transition:enter-end="opacity-100 translate-x-0 w-64 md:w-80"
                    x-transition:leave="transition-all ease-in duration-200 origin-right"
                    x-transition:leave-start="opacity-100 translate-x-0 w-64 md:w-80"
                    x-transition:leave-end="opacity-0 translate-x-4 w-0"
                    class="no-scrollbar w-64 shrink-0 overflow-y-auto border-l wa-border-subtle wa-sidebar md:w-80 flex flex-col override-transition"
                >
                    <div class="border-b wa-border-subtle p-8 text-center wa-page-root">
                        <div class="mb-4 overflow-hidden rounded-full border-4 wa-border-subtle wa-header shadow-lg mx-auto h-24 w-24">
                            @if(!empty($activeConversation['avatar_url']))
                                <img src="{{ $activeConversation['avatar_url'] }}" alt="{{ $activeConversation['name'] }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center wa-header wa-text-secondary">
                                    <span class="material-symbols-outlined text-4xl">person</span>
                                </div>
                            @endif
                        </div>

                        <h2 class="text-lg font-bold wa-text-primary">{{ $activeConversation['name'] }}</h2>
                        <p class="mb-2 text-xs wa-text-secondary">{{ $activeConversation['phone'] }}</p>

                        @if(!empty($activeConversation['location']))
                            <div class="inline-flex items-center gap-1.5 rounded-full wa-header px-3 py-1 text-[10px] font-bold uppercase tracking-wide wa-text-secondary">
                                <span class="material-symbols-outlined text-[14px]">location_on</span>
                                {{ $activeConversation['location'] }}
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col gap-6 p-6">
                        <div>
                            <h4 class="mb-3 text-[10px] font-bold uppercase tracking-widest wa-text-secondary">Team Notes</h4>
                            <div class="flex flex-col gap-3">
                                <textarea
                                    wire:model.defer="noteText"
                                    placeholder="Add a private note for the team..."
                                    class="h-32 w-full resize-none rounded-lg border wa-border-subtle wa-header p-3 text-xs wa-text-primary outline-none focus:border-[#00a884]"
                                ></textarea>

                                <div class="flex justify-end">
                                    <button
                                        type="button"
                                        wire:click="saveNote"
                                        class="flex items-center gap-2 rounded-lg wa-btn-primary px-4 py-2 text-xs font-bold text-white shadow-sm transition-all active:scale-95"
                                    >
                                        <span class="material-symbols-outlined text-[16px]">save</span>
                                        Save Note
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto pt-4">
                            <button
                                type="button"
                                wire:click="closeChat"
                                class="flex w-full items-center justify-center gap-2 rounded-lg wa-btn-secondary px-4 py-3 text-xs font-bold transition-all"
                            >
                                <span class="material-symbols-outlined text-lg">check_circle</span>
                                Close Chat
                            </button>
                        </div>
                    </div>
                </section>
            @else
                {{-- Empty State Main Area --}}
                <section class="relative flex flex-1 items-center justify-center wa-chat-bg p-8">
                    <div class="z-10 w-full max-w-md space-y-5 text-center">
                        <div class="relative inline-flex items-center justify-center rounded-full wa-header p-8 shadow-2xl border wa-border-subtle">
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[#00a884]/10 wa-text-accent">
                                <span class="material-symbols-outlined text-5xl" style="font-variation-settings: 'wght' 300">chat_bubble</span>
                            </div>

                            <div class="absolute -right-1 -top-1 flex h-9 w-9 items-center justify-center rounded-full border wa-border-subtle wa-header wa-text-accent shadow-lg">
                                <span class="material-symbols-outlined text-lg">forum</span>
                            </div>

                            <div class="absolute -left-1 bottom-1 flex h-7 w-7 items-center justify-center rounded-full wa-btn-primary text-[#111b21] shadow-lg">
                                <span class="material-symbols-outlined text-base">edit</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            @if($hasAvailableChannels)
                                <h2 class="text-xl font-bold wa-text-primary">
                                    Select a conversation to start messaging
                                </h2>
                                <p class="text-xs leading-relaxed wa-text-secondary">
                                    Choose a contact from the list on the left to view the chat history or start a new conversation.
                                </p>
                            @else
                                <h2 class="text-xl font-bold wa-text-primary">
                                    No Messaging Channels Configured
                                </h2>
                                <p class="text-xs leading-relaxed wa-text-secondary mb-5">
                                    You haven't set up any active WhatsApp phone numbers yet. Configure your first number to start receiving and sending messages.
                                </p>
                                <div class="flex justify-center pt-2">
                                    <a href="{{ route('whatsapp.setup.phone-numbers') }}" 
                                       wire:navigate
                                       class="inline-flex items-center gap-2 rounded-lg wa-btn-primary px-6 py-2.5 text-xs font-bold text-white shadow-md transition-all active:scale-95">
                                        <span class="material-symbols-outlined text-base">add_call</span>
                                        Configure WhatsApp Number
                                    </a>
                                </div>
                            @endif
                        </div>

                        @if($hasAvailableChannels)
                            <div class="flex items-center justify-center gap-3 pt-2">
                                <div class="flex items-center gap-1.5 rounded-full wa-header border wa-border-subtle px-3 py-1 text-xs font-medium wa-text-secondary">
                                    <span class="material-symbols-outlined text-sm">lock</span>
                                    End-to-end encrypted
                                </div>

                                <div class="flex items-center gap-1.5 rounded-full wa-header border wa-border-subtle px-3 py-1 text-xs font-medium wa-text-secondary">
                                    <span class="material-symbols-outlined text-sm">cloud_done</span>
                                    Cloud Synced
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @endif
        </main>
    </div>

    @include('livewire.web.chats.partials.assign-agent-modal')
    @include('livewire.web.chats.partials.select-template-modal')
    @include('livewire.web.chats.partials.initiate-chat-modal')

    <script>
        function formatChatLocalTimestamps() {
            document.querySelectorAll('.local-time[data-iso]').forEach(el => {
                const iso = el.getAttribute('data-iso');
                if (iso) {
                    const date = new Date(iso);
                    if (!isNaN(date.getTime())) {
                        el.textContent = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
                    }
                }
            });
        }
        document.addEventListener('DOMContentLoaded', formatChatLocalTimestamps);
        document.addEventListener('livewire:navigated', formatChatLocalTimestamps);
        if (window.Livewire) {
            Livewire.hook('morph.updated', formatChatLocalTimestamps);
        }
    </script>
</div>
