@if($showAssignAgentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
        <div class="w-full max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 dark:border-slate-800">
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Assign Chat to Agent</h2>
                <button
                    type="button"
                    wire:click="closeAssignAgentModal"
                    class="text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-slate-200"
                >
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            {{-- Search --}}
            <div class="px-6 py-4">
                <label class="relative flex items-center">
                    <div class="pointer-events-none absolute left-3 flex items-center text-slate-400">
                        <span class="material-symbols-outlined text-[20px]">search</span>
                    </div>
                    <input
                        wire:model.live.debounce.300ms="agentSearch"
                        type="text"
                        placeholder="Search agents by name or email..."
                        class="w-full rounded-lg border-none bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 transition-all focus:ring-2 focus:ring-primary/50 dark:bg-slate-800 dark:text-slate-100"
                    />
                </label>

                @if($assignAgentError)
                    <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                        {{ $assignAgentError }}
                    </div>
                @endif
            </div>

            {{-- Agent List --}}
            <div class="custom-scrollbar max-h-[320px] overflow-y-auto px-6 pb-6">
                <div class="space-y-2">
                    @forelse($assignAgents as $agent)
                        <label class="group flex cursor-pointer items-center gap-4 rounded-lg border border-slate-100 p-3 transition-colors hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50 {{ !($agent['is_assignable'] ?? true) ? 'opacity-60' : '' }}">
                            <div class="relative">
                                @if(!empty($agent['avatar_url']))
                                    <img
                                        src="{{ $agent['avatar_url'] }}"
                                        alt="{{ $agent['name'] }}"
                                        class="h-10 w-10 rounded-full object-cover"
                                    />
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-500 dark:bg-slate-700">
                                        {{ \Illuminate\Support\Str::of($agent['name'])->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}
                                    </div>
                                @endif

                                <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white dark:border-slate-900 {{ $agent['ui_status_color'] ?? 'bg-slate-400' }}"></span>
                            </div>

                            <div class="flex-1">
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $agent['name'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $agent['subtitle'] }}</p>
                                @if(!empty($agent['disabled_reason']) && !($agent['is_assignable'] ?? true))
                                    <p class="mt-1 text-[11px] text-amber-600">{{ $agent['disabled_reason'] }}</p>
                                @endif
                            </div>

                            <input
                                type="radio"
                                wire:model="selectedAgentId"
                                value="{{ $agent['id'] }}"
                                name="agent_selection"
                                class="h-5 w-5 border-slate-300 bg-transparent text-primary focus:ring-primary focus:ring-offset-0 dark:border-slate-600"
                                @disabled(!($agent['is_assignable'] ?? true))
                            />
                        </label>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 px-4 py-10 text-center dark:border-slate-700">
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No agents found</p>
                            <p class="mt-1 text-xs text-slate-400">Try another search or review your company team setup.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/30">
                <button
                    type="button"
                    wire:click="closeAssignAgentModal"
                    class="rounded-lg px-5 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-200/50 dark:text-slate-300 dark:hover:bg-slate-700/50"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    wire:click="assignChat"
                    wire:loading.attr="disabled"
                    class="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-white shadow-sm shadow-primary/20 transition-all hover:bg-primary/90 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="assignChat">Assign Chat</span>
                    <span wire:loading wire:target="assignChat">Assigning...</span>
                </button>
            </div>
        </div>
    </div>
@endif
