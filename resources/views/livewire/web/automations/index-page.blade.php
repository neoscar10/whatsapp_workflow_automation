<div class="mx-auto w-full max-w-7xl p-8">
    {{-- Header --}}
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Automations</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Manage and create automated workflows</p>
        </div>
        <a href="{{ route('automations.create') }}" class="flex items-center gap-2 rounded-xl bg-primary px-6 py-2.5 font-bold text-white shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:opacity-80">
            <span class="material-symbols-outlined">add</span>
            Create Automation
        </a>
    </div>

    {{-- Filter Tabs --}}
    <div class="mb-8 flex items-center gap-2 rounded-2xl bg-white p-1.5 shadow-sm dark:bg-slate-900 border border-slate-100 dark:border-slate-800 w-fit">
        @foreach(['all' => 'All', 'active' => 'Active', 'draft' => 'Draft', 'paused' => 'Paused'] as $key => $label)
            <button 
                wire:click="setFilter('{{ $key }}')"
                class="px-6 py-2 rounded-xl text-sm font-bold transition-all {{ $filter === $key ? 'bg-primary text-white shadow-md' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}"
            >
                {{ $label }}
                @if(($counts[$key] ?? 0) > 0)
                    <span class="ml-1.5 opacity-70">({{ $counts[$key] }})</span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Content --}}
    @if($automations->isNotEmpty())
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 mb-12">
            @foreach($automations as $automation)
                @php $meta = $automation->status_meta; @endphp
                <div class="group relative flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl hover:border-primary/20 dark:border-slate-800 dark:bg-slate-900">
                    {{-- Card Header --}}
                    <div class="mb-5 flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-slate-400 group-hover:bg-primary/10 group-hover:text-primary transition-colors dark:bg-slate-800/50">
                            <span class="material-symbols-outlined text-2xl">
                                {{ match($automation->trigger_summary) {
                                    'Inbound Message' => 'message',
                                    'Order Created' => 'shopping_cart',
                                    'New Lead' => 'person_add',
                                    default => 'bolt'
                                } }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $meta['class'] }}">
                                {{ $meta['label'] }}
                            </span>
                            <button 
                                wire:click="toggleStatus({{ $automation->id }})"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $automation->is_enabled ? 'bg-primary' : 'bg-slate-200 dark:bg-slate-700' }}"
                            >
                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $automation->is_enabled ? 'translate-x-4' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="mb-6 flex-1">
                        <h3 class="mb-1 text-lg font-bold text-slate-900 dark:text-white line-clamp-1">{{ $automation->name }}</h3>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            <span>{{ $automation->trigger_summary ?: 'Manual Trigger' }}</span>
                        </div>
                    </div>

                    {{-- Card Stats --}}
                    <div class="mb-6 grid grid-cols-2 gap-4 rounded-xl bg-slate-50/80 p-3 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Executions</span>
                            <span class="text-sm font-black text-slate-900 dark:text-white">{{ number_format($automation->total_executions) }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Last Run</span>
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                {{ $automation->last_run_at ? $automation->last_run_at->diffForHumans() : 'Never' }}
                            </span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-800">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('automations.edit', $automation->id) }}" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-50 hover:text-primary dark:hover:bg-slate-800" title="Edit">
                                <span class="material-symbols-outlined text-xl">edit</span>
                            </a>
                            <button wire:click="duplicate({{ $automation->id }})" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-50 hover:text-primary dark:hover:bg-slate-800" title="Duplicate">
                                <span class="material-symbols-outlined text-xl">content_copy</span>
                            </button>
                        </div>
                        <button wire:click="confirmDelete({{ $automation->id }})" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20" title="Delete">
                            <span class="material-symbols-outlined text-xl">delete</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-6">
            {{ $automations->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="mt-12 rounded-3xl border-2 border-dashed border-slate-200 bg-white/50 p-16 flex flex-col items-center justify-center text-center dark:border-slate-800 dark:bg-slate-900/50">
            <div class="group relative mb-6 flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-xl transition-all hover:scale-105 dark:bg-slate-800">
                <div class="absolute inset-0 bg-primary opacity-5 group-hover:opacity-10 transition-opacity"></div>
                <span class="material-symbols-outlined text-5xl text-primary/40 transition-transform duration-500 group-hover:scale-110">settings_suggest</span>
            </div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white mb-2">
                @if($filter === 'all')
                    No automations found
                @else
                    No {{ $filter }} automations found
                @endif
            </h2>
            <p class="mx-auto mb-8 max-w-sm text-sm font-medium text-slate-500 dark:text-slate-400">
                Try adjusting your filters or create a new automation to start engaging your customers automatically.
            </p>
            <a href="{{ route('automations.create') }}" class="rounded-xl border-2 border-primary bg-white px-8 py-3 font-bold text-primary transition-all hover:bg-primary/5 active:scale-95 dark:bg-slate-950">
                Create your first automation
            </a>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($automationToDelete)
        <div class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto px-4 py-6 sm:px-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('automationToDelete', null)"></div>
            
            <div class="relative transform border border-slate-200 bg-white shadow-2xl transition-all dark:border-slate-800 dark:bg-slate-900 sm:w-full sm:max-w-md rounded-2xl">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-500 dark:bg-red-900/30">
                            <span class="material-symbols-outlined">warning</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Delete Automation</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                                Are you sure you want to delete this automation? This action will permanently remove it and all associated execution logs. This cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-3 rounded-b-2xl bg-slate-50 p-6 dark:bg-slate-800/50 sm:flex-row sm:justify-end">
                    <button wire:click="$set('automationToDelete', null)" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition-all hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        Cancel
                    </button>
                    <button wire:click="deleteAutomation" class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-red-600/20 transition-all hover:bg-red-500 active:scale-95">
                        <span wire:loading wire:target="deleteAutomation" class="mr-2 inline-block h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
