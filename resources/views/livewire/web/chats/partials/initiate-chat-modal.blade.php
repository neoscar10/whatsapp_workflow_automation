@if($showInitiateChatModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        {{-- Backdrop --}}
        <div 
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"
            wire:click="closeInitiateChatModal"
        ></div>

        {{-- Modal Content --}}
        <div class="relative w-full max-w-md overflow-hidden rounded-2xl border border-[#222d34] bg-[#111b21] shadow-2xl text-[#e9edef]">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-[#222d34] bg-[#202c33] px-6 py-4">
                <div>
                    <h2 class="text-base font-bold text-[#e9edef]">Start a Conversation</h2>
                    <p class="text-[11px] font-medium text-[#8696a0] uppercase tracking-wider">Choose a contact to begin messaging</p>
                </div>
                <button
                    type="button"
                    wire:click="closeInitiateChatModal"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8696a0] transition-all hover:bg-[#2a3942] hover:text-[#e9edef]"
                >
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            {{-- Search Area --}}
            <div class="px-6 pt-4 pb-2 bg-[#111b21]">
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#8696a0] text-lg">search</span>
                    <input
                        wire:model.live.debounce.300ms="contactSearch"
                        type="text"
                        placeholder="Search by name or phone number..."
                        class="w-full rounded-lg border border-[#2a3942] bg-[#202c33] py-2 pl-10 pr-4 text-xs font-medium text-[#e9edef] placeholder:text-[#8696a0] focus:border-[#00a884] focus:ring-1 focus:ring-[#00a884] outline-none"
                    />
                </div>
            </div>

            {{-- Contacts List --}}
            <div class="no-scrollbar max-h-[350px] overflow-y-auto px-6 pb-6 pt-2 bg-[#111b21]">
                <div class="space-y-2">
                    @forelse($contactsForInitiation as $contact)
                        <div 
                            wire:click="selectAndInitiateChat({{ $contact['id'] }})"
                            class="group relative flex cursor-pointer items-center gap-3 rounded-lg border border-[#222d34] bg-[#202c33] p-3 transition-all hover:bg-[#2a3942] {{ (int) $selectedContactId === (int) $contact['id'] ? 'border-[#00a884] bg-[#2a3942]' : '' }}"
                        >
                            <div class="relative">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#00a884]/20 text-[#00a884] font-bold text-xs">
                                    {{ \Illuminate\Support\Str::of($contact['name'])->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-[#e9edef] truncate">{{ $contact['name'] }}</p>
                                <p class="text-[11px] font-medium text-[#8696a0]">{{ $contact['phone'] }}</p>
                            </div>

                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-[#00a884] text-white opacity-0 group-hover:opacity-100 transition-all">
                                <span class="material-symbols-outlined text-sm">chat</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#202c33] text-[#8696a0]">
                                <span class="material-symbols-outlined text-2xl">person_search</span>
                            </div>
                            <p class="text-xs font-bold text-[#8696a0]">No contacts found</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center gap-3 border-t border-[#222d34] bg-[#202c33] p-4">
                <button
                    type="button"
                    wire:click="closeInitiateChatModal"
                    class="w-full rounded-lg py-2 text-xs font-bold text-[#8696a0] transition-all hover:bg-[#2a3942] hover:text-[#e9edef]"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
@endif

