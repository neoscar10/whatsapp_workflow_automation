@if($showAssignAgentModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        {{-- Backdrop --}}
        <div 
            class="absolute inset-0 bg-slate-900/40 backdrop-blur-md"
            wire:click="closeAssignAgentModal"
        ></div>

        {{-- Modal Content --}}
        <div class="relative w-full max-w-md overflow-hidden rounded-[2.5rem] border border-white/20 bg-white/90 shadow-2xl backdrop-blur-xl dark:border-slate-800/50 dark:bg-[#0B0F1A]/95">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-100/50 px-8 py-6 dark:border-slate-800/50">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Assign Agent</h2>
                    <p class="text-[11px] font-medium text-slate-400 uppercase tracking-tighter">Select a team member</p>
                </div>
                <button
                    type="button"
                    wire:click="closeAssignAgentModal"
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
                        wire:model.live.debounce.300ms="agentSearch"
                        type="text"
                        placeholder="Search team members..."
                        class="w-full rounded-full border-none bg-slate-100/50 py-3.5 pl-12 pr-4 text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-primary/30 dark:bg-slate-800/50 dark:text-white"
                    />
                </div>

                @if($assignAgentError)
                    <div class="mt-4 rounded-2xl bg-red-50 px-4 py-3 text-xs font-bold text-red-600 dark:bg-red-950/20 dark:text-red-400">
                        {{ $assignAgentError }}
                    </div>
                @endif
            </div>

            {{-- Agent List --}}
            <div class="custom-scrollbar max-h-[400px] overflow-y-auto px-8 pb-8 pt-4">
                <div class="space-y-3">
                    @forelse($assignAgents as $agent)
                        <label class="group relative flex cursor-pointer items-center gap-4 rounded-2xl border border-transparent p-4 transition-all hover:bg-white dark:hover:bg-slate-800/50 hover:shadow-xl hover:shadow-slate-200/50 dark:hover:shadow-none {{ (int) $selectedAgentId === (int) $agent['id'] ? 'bg-white dark:bg-slate-800 border-primary/20 shadow-xl' : '' }} {{ !($agent['is_assignable'] ?? true) ? 'opacity-50 grayscale' : '' }}">
                            <div class="relative">
                                @if(!empty($agent['avatar_url']))
                                    <img
                                        src="{{ $agent['avatar_url'] }}"
                                        alt="{{ $agent['name'] }}"
                                        class="h-12 w-12 rounded-2xl object-cover shadow-lg"
                                    />
                                @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary font-black text-sm">
                                        {{ \Illuminate\Support\Str::of($agent['name'])->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}
                                    </div>
                                @endif

                                <span class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full border-2 border-white dark:border-slate-900 {{ $agent['ui_status_color'] ?? 'bg-slate-400' }} shadow-sm"></span>
                            </div>

                            <div class="flex-1">
                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $agent['name'] }}</p>
                                <p class="text-[11px] font-medium text-slate-400">{{ $agent['subtitle'] ?? 'Member' }}</p>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="radio"
                                    wire:model="selectedAgentId"
                                    value="{{ $agent['id'] }}"
                                    name="agent_selection"
                                    class="h-5 w-5 border-2 border-slate-200 bg-transparent text-primary focus:ring-primary/20 focus:ring-offset-0 dark:border-slate-700"
                                    @disabled(!($agent['is_assignable'] ?? true))
                                />
                            </div>
                            
                            @if(!($agent['is_assignable'] ?? true))
                                <div class="absolute inset-0 z-10 cursor-not-allowed" title="{{ $agent['disabled_reason'] ?? 'Unavailable' }}"></div>
                            @endif
                        </label>
                    @empty
                        <div class="py-12 text-center">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-50 dark:bg-slate-800">
                                <span class="material-symbols-outlined text-slate-300 text-3xl">person_search</span>
                            </div>
                            <p class="text-sm font-bold text-slate-600 dark:text-slate-400">No agents found</p>
                            <p class="mt-1 text-xs text-slate-400">Try refining your search</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center gap-3 border-t border-slate-100/50 bg-slate-50/50 p-8 dark:border-slate-800/50 dark:bg-slate-900/50">
                <button
                    type="button"
                    wire:click="closeAssignAgentModal"
                    class="flex-1 rounded-2xl py-4 text-xs font-black uppercase tracking-widest text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-white"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    wire:click="assignChat"
                    wire:loading.attr="disabled"
                    class="flex-[1.5] rounded-2xl bg-primary py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-primary/30 transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
                >
                    <div class="flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="assignChat">Assign Now</span>
                        <span wire:loading wire:target="assignChat">Assigning...</span>
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
@endif
