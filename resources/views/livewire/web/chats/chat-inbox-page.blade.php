<div 
    x-data="{ 
        showLeftSidebar: true, 
        showRightSidebar: true,
        showScrollButton: false,
        scrollToBottom() {
            const container = this.$refs.messageContainer;
            if (container) {
                container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
            }
        }
    }" 
    x-init="
        console.log('ChatInbox initialized. Listening for company {{ auth()->user()->company_id }} chats...');
        if (window.Echo) {
            window.Echo.private('company.{{ auth()->user()->company_id }}.chats')
                .subscribed(() => {
                    console.log('Successfully subscribed to company chats channel');
                })
                .listen('.chat.inbound.received', (e) => {
                    console.log('Realtime INBOUND message received:', e);
                    $wire.dispatch('refresh-chat-data', e);
                })
                .listen('.conversation.updated', (e) => {
                    console.log('Realtime CONVERSATION update received:', e);
                    $wire.dispatch('refresh-chat-data', e);
                });
        } else {
            console.error('Laravel Echo is NOT initialized on this page!');
        }
    "
    class="flex flex-1 w-full relative overflow-hidden bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 antialiased min-h-[500px]"
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
                class="flex w-64 flex-shrink-0 flex-col border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 md:w-80 override-transition"
            >
                <div class="border-b border-slate-200 p-4 dark:border-slate-800">
                    <div class="mb-4 flex gap-2">
                        <button
                            type="button"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-primary py-2 text-xs font-bold text-white transition-colors hover:bg-primary/90"
                        >
                            <span class="material-symbols-outlined text-sm">add</span>
                            New Chat
                        </button>

                        <button
                            type="button"
                            class="rounded-lg border border-slate-200 p-2 text-slate-500 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                        >
                            <span class="material-symbols-outlined text-sm">filter_list</span>
                        </button>
                    </div>

                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-slate-400">search</span>
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="text"
                            placeholder="Search conversations..."
                            class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm transition-all focus:border-primary focus:ring-1 focus:ring-primary dark:border-slate-700 dark:bg-slate-900"
                        />
                    </div>
                </div>
                
                <div class="no-scrollbar flex overflow-x-auto border-b border-slate-100 px-4 dark:border-slate-800 shrink-0">
                    <button
                        type="button"
                        wire:click="$set('tab', 'all')"
                        class="whitespace-nowrap px-4 py-3 text-sm transition-colors {{ $tab === 'all' ? 'border-b-2 border-primary font-semibold text-primary' : 'font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300' }}"
                    >
                        All
                    </button>
                    <button
                        type="button"
                        wire:click="$set('tab', 'assigned')"
                        class="whitespace-nowrap px-4 py-3 text-sm transition-colors {{ $tab === 'assigned' ? 'border-b-2 border-primary font-semibold text-primary' : 'font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300' }}"
                    >
                        Assigned
                    </button>
                    <button
                        type="button"
                        wire:click="$set('tab', 'unassigned')"
                        class="whitespace-nowrap px-4 py-3 text-sm transition-colors {{ $tab === 'unassigned' ? 'border-b-2 border-primary font-semibold text-primary' : 'font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300' }}"
                    >
                        Unassigned
                    </button>
                </div>

                <div class="custom-scrollbar flex-1 overflow-y-auto">
                    @forelse($conversationList as $conversation)
                        <button
                            type="button"
                            wire:click="selectConversation({{ $conversation['id'] }})"
                            class="flex w-full cursor-pointer gap-3 border-b border-slate-100 p-4 text-left transition-colors dark:border-slate-800 {{ (int) $selectedConversationId === (int) $conversation['id'] ? 'border-l-4 border-l-primary bg-primary/5 dark:bg-primary/10' : 'hover:bg-slate-50 dark:hover:bg-slate-800' }}"
                        >
                            <div class="relative flex-shrink-0">
                                @if(!empty($conversation['avatar_url']))
                                    <div class="h-12 w-12 rounded-full bg-slate-200 bg-cover bg-center" style="background-image: url('{{ $conversation['avatar_url'] }}');"></div>
                                @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-200 text-slate-400 dark:bg-slate-700">
                                        <span class="material-symbols-outlined">person</span>
                                    </div>
                                @endif

                                @if(!empty($conversation['is_active']))
                                    <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-green-500 dark:border-slate-900"></span>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex items-start justify-between">
                                    <h3 class="truncate text-sm font-semibold">{{ $conversation['name'] }}</h3>
                                    <span class="text-[10px] text-slate-400">{{ $conversation['time_label'] }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <p class="truncate text-xs text-slate-500">
                                        {{ $conversation['preview'] }}
                                    </p>

                                    @if(($conversation['unread_count'] ?? 0) > 0)
                                        <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] text-white">
                                            {{ $conversation['unread_count'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="p-8 text-center text-sm text-slate-500">
                            No conversations match your criteria.
                        </div>
                    @endforelse
                </div>
            </section>

            @if($activeConversation)
                {{-- Column 2: Chat Window --}}
                <section class="flex flex-1 flex-col border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <header class="flex h-16 shrink-0 items-center justify-between border-b border-slate-100 px-6 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <button @click="showLeftSidebar = !showLeftSidebar" class="p-2 text-slate-400 transition-colors hover:text-primary rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                                <span class="material-symbols-outlined" x-text="showLeftSidebar ? 'keyboard_double_arrow_left' : 'keyboard_double_arrow_right'"></span>
                            </button>
                            <div>
                                <h2 class="leading-none font-bold text-slate-900 dark:text-white">{{ $activeConversation['name'] }}</h2>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="text-xs text-slate-500">{{ $activeConversation['phone'] }}</span>
                                    @if(!empty($activeConversation['is_active']))
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-green-600">Active</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button @click="showRightSidebar = !showRightSidebar" class="p-2 text-slate-400 transition-colors hover:text-primary rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" title="Toggle Sidebar">
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
                        class="custom-scrollbar relative flex flex-1 flex-col gap-6 overflow-y-auto bg-slate-100 p-8 dark:bg-slate-950/40"
                    >
                        @foreach($messages as $message)
                            @if($message['message_type'] === 'card')
                                <div class="my-2 flex justify-center">
                                    <div class="w-full max-w-[320px] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                                        <div class="flex items-center gap-3 bg-primary/5 px-4 py-3 dark:bg-primary/10">
                                            <span class="material-symbols-outlined text-primary">shopping_bag</span>
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                                {{ $message['card_title'] ?? 'Card' }}
                                            </h4>
                                        </div>
                                        <div class="p-4">
                                            <p class="mb-1 text-sm font-semibold">{{ $message['card_heading'] ?? '' }}</p>
                                            <p class="mb-4 text-xs text-slate-500">{{ $message['card_subtext'] ?? '' }}</p>
                                            @if(!empty($message['card_button_text']))
                                                <button class="w-full rounded-lg bg-primary py-2 text-xs font-bold text-white transition-colors hover:bg-primary/90">
                                                    {{ $message['card_button_text'] }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @elseif($message['message_type'] === 'image')
                                <div class="flex max-w-[80%] flex-col items-start">
                                    <div class="rounded-2xl rounded-tl-none border border-slate-100 bg-white p-1.5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                                        <div class="w-72 overflow-hidden rounded-xl bg-slate-100 aspect-video">
                                            <img src="{{ $message['media_url'] }}" alt="Message image" class="h-full w-full object-cover">
                                        </div>
                                        @if(!empty($message['body']))
                                            <div class="px-2.5 py-2">
                                                <p class="text-xs text-slate-500">{{ $message['body'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <span class="ml-1 mt-1 text-[10px] text-slate-400">{{ $message['time_label'] }}</span>
                                </div>
                            @else
                                <div class="flex max-w-[80%] flex-col {{ $message['direction'] === 'outbound' ? 'self-end items-end' : 'items-start' }}">
                                    <div class="{{ $message['direction'] === 'outbound' ? 'rounded-2xl rounded-tr-none bg-gradient-to-br from-primary to-blue-700 text-white shadow-md shadow-primary/20' : 'rounded-2xl rounded-tl-none border border-slate-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800' }} px-4 py-3">
                                        @if($message['message_type'] === 'template')
                                            <div class="mb-2 flex items-center gap-2 opacity-80">
                                                <span class="material-symbols-outlined text-xs">auto_awesome</span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider">WhatsApp Template</span>
                                            </div>
                                        @endif
                                        <p class="text-sm leading-relaxed">{{ $message['body'] }}</p>
                                    </div>

                                    <div class="mt-1 {{ $message['direction'] === 'outbound' ? 'mr-1 flex items-center gap-1' : 'ml-1' }}">
                                        <span class="text-[10px] text-slate-400">{{ $message['time_label'] }}</span>
                                        @if($message['direction'] === 'outbound' && !empty($message['status_icon']))
                                            <span 
                                                class="material-symbols-outlined text-[14px] {{ $message['status_color'] ?? 'text-slate-400' }}"
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
                            class="sticky bottom-4 right-4 z-20 flex justify-end pr-4"
                        >
                            <button 
                                @click="scrollToBottom()"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-primary shadow-lg ring-1 ring-slate-200 transition-all hover:bg-slate-50 active:scale-95 dark:bg-slate-800 dark:text-primary dark:ring-slate-700"
                            >
                                <span class="material-symbols-outlined">keyboard_double_arrow_down</span>
                            </button>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                        @if($errorMessage)
                            <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                {{ $errorMessage }}
                            </div>
                        @endif

                        @if($successMessage)
                            <div class="mb-3 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">
                                {{ $successMessage }}
                            </div>
                        @endif

                        <div class="flex items-center gap-3 rounded-xl bg-slate-100 p-2 dark:bg-slate-800">
                            <button type="button" class="p-2 text-slate-500 transition-colors hover:text-primary">
                                <span class="material-symbols-outlined">sentiment_satisfied</span>
                            </button>
                            <button type="button" class="p-2 text-slate-500 transition-colors hover:text-primary">
                                <span class="material-symbols-outlined">attach_file</span>
                            </button>
                            <button 
                                type="button" 
                                wire:click="openTemplateSendModal"
                                class="p-2 text-slate-500 transition-colors hover:text-primary"
                                title="Send Template"
                            >
                                <span class="material-symbols-outlined">auto_awesome</span>
                            </button>

                            <input
                                wire:model.defer="messageText"
                                type="text"
                                placeholder="Type a message..."
                                class="flex-1 border-none bg-transparent px-2 text-sm placeholder:text-slate-500 focus:ring-0"
                                wire:keydown.enter="sendMessage"
                            />

                            <button
                                type="button"
                                wire:click="sendMessage"
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-white shadow-lg shadow-primary/30 transition-all hover:bg-primary/90"
                            >
                                <span class="material-symbols-outlined">send</span>
                            </button>
                        </div>
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
                    class="custom-scrollbar w-64 shrink-0 overflow-y-auto border-l border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 md:w-80 flex flex-col override-transition"
                >
                    <div class="border-b border-slate-200 p-10 text-center dark:border-slate-800">
                        <div class="mb-6 overflow-hidden rounded-full border-4 border-white bg-slate-200 shadow-xl dark:border-slate-800 mx-auto h-28 w-28">
                            @if(!empty($activeConversation['avatar_url']))
                                <img src="{{ $activeConversation['avatar_url'] }}" alt="{{ $activeConversation['name'] }}" class="h-full w-full object-cover">
                            @endif
                        </div>

                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $activeConversation['name'] }}</h2>
                        <p class="mb-3 text-sm text-slate-500">{{ $activeConversation['phone'] }}</p>

                        @if(!empty($activeConversation['location']))
                            <div class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                <span class="material-symbols-outlined text-[14px]">location_on</span>
                                {{ $activeConversation['location'] }}
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col gap-8 p-8">
                        <div>
                            <h4 class="mb-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Management</h4>
                            <div class="flex flex-col gap-3">
                                @if(!empty($sidebarData['assignment']))
                                    <button
                                        type="button"
                                        wire:click="openAssignAgentModal"
                                        class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50 dark:hover:bg-slate-800"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary dark:bg-slate-700">
                                                {{ \Illuminate\Support\Str::substr($sidebarData['assignment']['name'], 0, 1) }}
                                            </div>
                                            <div class="text-left">
                                                <p class="text-[10px] uppercase text-slate-400">Assigned To</p>
                                                <p class="font-semibold leading-tight text-slate-900 dark:text-slate-100">{{ $sidebarData['assignment']['name'] }}</p>
                                            </div>
                                        </div>
                                        <span class="material-symbols-outlined text-[18px] text-slate-400">edit</span>
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="openAssignAgentModal"
                                        class="flex w-full items-center justify-between rounded-xl border border-dashed border-slate-300 bg-white px-4 py-3 text-sm font-medium transition-all hover:border-primary hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary"
                                    >
                                        <span class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[20px]">person_add</span>
                                            Assign Agent
                                        </span>
                                        <span class="material-symbols-outlined text-[18px]">add</span>
                                    </button>
                                @endif

                                <div class="flex flex-wrap gap-2">
                                    @foreach($sidebarData['labels'] ?? [] as $label)
                                        <div class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold {{ $label['class'] ?? 'bg-primary/10 text-primary' }}">
                                            {{ $label['name'] }}
                                            <span class="material-symbols-outlined cursor-pointer text-[14px]">close</span>
                                        </div>
                                    @endforeach

                                    @if(!empty($sidebarData['labels']) && count($sidebarData['labels']) > 0)
                                        <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg border border-dashed border-slate-300 text-slate-400 transition-all hover:border-primary hover:text-primary dark:border-slate-600">
                                            <span class="material-symbols-outlined text-[18px]">add</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="mb-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Team Notes</h4>
                            <div class="flex flex-col gap-3">
                                <textarea
                                    wire:model.defer="noteText"
                                    placeholder="Add a private note for the team..."
                                    class="h-32 w-full resize-none rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs focus:border-primary focus:ring-primary dark:border-slate-800 dark:bg-slate-800/50"
                                ></textarea>

                                <div class="flex justify-end">
                                    <button
                                        type="button"
                                        wire:click="saveNote"
                                        class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white shadow-sm transition-all hover:bg-primary/90 active:scale-95"
                                    >
                                        <span class="material-symbols-outlined text-[18px]">save</span>
                                        Save Note
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto pt-4">
                            <button
                                type="button"
                                wire:click="closeChat"
                                class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-bold text-slate-600 transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50"
                            >
                                <span class="material-symbols-outlined">check_circle</span>
                                Close Chat
                            </button>
                        </div>
                    </div>
                </section>
            @else
                {{-- Empty State Main Area --}}
                <section class="relative flex flex-1 items-center justify-center bg-background-light p-8 dark:bg-background-dark">
                    <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-20 dark:opacity-10">
                        <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-primary/20 blur-3xl"></div>
                        <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-primary/20 blur-3xl"></div>
                    </div>

                    <div class="z-10 w-full max-w-md space-y-6 text-center">
                        <div class="relative inline-flex items-center justify-center rounded-full bg-white p-8 shadow-xl shadow-primary/5 ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                            <div class="flex h-24 w-24 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <span class="material-symbols-outlined text-6xl" style="font-variation-settings: 'wght' 300">chat_bubble</span>
                            </div>

                            <div class="absolute -right-1 -top-1 flex h-10 w-10 items-center justify-center rounded-full border border-slate-100 bg-white text-primary shadow-lg dark:border-slate-700 dark:bg-slate-800">
                                <span class="material-symbols-outlined text-xl">forum</span>
                            </div>

                            <div class="absolute -left-2 bottom-2 flex h-8 w-8 items-center justify-center rounded-full bg-primary text-white shadow-lg">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            @if($hasAvailableChannels)
                                <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Select a conversation to start messaging
                                </h2>
                                <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                                    Choose a contact from the list on the left to view the chat history or start a new conversation.
                                </p>
                            @else
                                <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    No Messaging Channels Configured
                                </h2>
                                <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400 mb-6">
                                    You haven't set up any active WhatsApp phone numbers yet. Configure your first number to start receiving and sending messages.
                                </p>
                                <div class="flex justify-center pt-4">
                                    <a href="{{ route('whatsapp.setup.phone-numbers') }}" 
                                       wire:navigate
                                       class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-sm font-bold text-white shadow-lg transition-all hover:bg-primary/90 active:scale-95">
                                        <span class="material-symbols-outlined text-lg">add_call</span>
                                        Configure WhatsApp Number
                                    </a>
                                </div>
                            @endif
                        </div>

                        @if($hasAvailableChannels)
                            <div class="flex items-center justify-center gap-4 pt-4">
                                <div class="flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-400 dark:bg-slate-800">
                                    <span class="material-symbols-outlined text-sm">lock</span>
                                    End-to-end encrypted
                                </div>

                                <div class="flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-400 dark:bg-slate-800">
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
</div>
