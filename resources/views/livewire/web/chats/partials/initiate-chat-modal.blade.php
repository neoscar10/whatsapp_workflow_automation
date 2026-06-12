@if($showInitiateChatModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        {{-- Backdrop --}}
        <div 
            class="absolute inset-0 bg-slate-900/40 backdrop-blur-md"
            wire:click="closeInitiateChatModal"
        ></div>

        {{-- Modal Content --}}
        <div class="relative w-full max-w-md overflow-hidden rounded-[2.5rem] border border-white/20 bg-white/90 shadow-2xl backdrop-blur-xl dark:border-slate-800/50 dark:bg-[#0B0F1A]/95">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-100/50 px-8 py-6 dark:border-slate-800/50">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Start a Conversation</h2>
                    <p class="text-[11px] font-medium text-slate-400 uppercase tracking-tighter">Choose a contact to begin messaging</p>
                </div>
                <button
                    type="button"
                    wire:click="closeInitiateChatModal"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition-all hover:bg-slate-200 hover:text-slate-600 dark:bg-slate-800 dark:hover:bg-slate-700 dark:hover:text-white"
                >
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            {{-- Search Area --}}
            <div class="px-8 pt-6 pb-2">
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-primary">search</span>
                    <input
                        wire:model.live.debounce.300ms="contactSearch"
                        type="text"
                        placeholder="Search by name or phone number..."
                        class="w-full rounded-full border-none bg-slate-100/50 py-3.5 pl-12 pr-4 text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-primary/30 dark:bg-slate-800/50 dark:text-white"
                    />
                </div>
            </div>

            {{-- Contacts List --}}
            <div class="no-scrollbar max-h-[350px] overflow-y-auto px-8 pb-8 pt-4">
                <div class="space-y-3">
                    @forelse($contactsForInitiation as $contact)
                        <div 
                            wire:click="selectAndInitiateChat({{ $contact['id'] }})"
                            class="group relative flex cursor-pointer items-center gap-4 rounded-2xl border border-slate-100 dark:border-slate-800/80 p-4 transition-all hover:bg-white dark:hover:bg-slate-800/50 hover:shadow-xl hover:shadow-slate-200/50 dark:hover:shadow-none hover:border-primary/20 {{ (int) $selectedContactId === (int) $contact['id'] ? 'bg-white dark:bg-slate-800 border-primary shadow-xl ring-1 ring-primary' : '' }}"
                        >
                            <div class="relative">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary font-black text-sm">
                                    {{ \Illuminate\Support\Str::of($contact['name'])->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $contact['name'] }}</p>
                                <p class="text-[11px] font-medium text-slate-400">{{ $contact['phone'] }}</p>
                            </div>

                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-400 opacity-0 group-hover:opacity-100 group-hover:bg-primary/10 group-hover:text-primary transition-all dark:bg-slate-800">
                                <span class="material-symbols-outlined text-[18px]">chat</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-50 dark:bg-slate-800">
                                <span class="material-symbols-outlined text-slate-300 text-3xl">person_search</span>
                            </div>
                            <p class="text-sm font-bold text-slate-600 dark:text-slate-400">No contacts found</p>
                            <p class="mt-1 text-xs text-slate-400">Try refining your search</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center gap-3 border-t border-slate-100/50 bg-slate-50/50 p-8 dark:border-slate-800/50 dark:bg-slate-900/50">
                <button
                    type="button"
                    wire:click="closeInitiateChatModal"
                    class="flex-1 rounded-2xl py-4 text-xs font-black uppercase tracking-widest text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-white"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
@endif
