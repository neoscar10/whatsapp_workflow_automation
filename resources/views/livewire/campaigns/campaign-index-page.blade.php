<div class="flex flex-col h-full bg-slate-50 dark:bg-slate-900/50 overflow-y-auto">
    <div class="px-8 py-8 space-y-8">
        {{-- Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Campaigns</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Send targeted WhatsApp template campaigns to contacts, groups, and segments.</p>
            </div>
            <button @click="$dispatch('open-campaign-modal')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary/30 transition-all hover:scale-[1.02] active:scale-[0.98]">
                <span class="material-symbols-outlined text-lg">add</span>
                Create Campaign
            </button>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                        <span class="material-symbols-outlined">campaign</span>
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Total</p>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400">
                        <span class="material-symbols-outlined">edit_note</span>
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Drafts</p>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['draft'] }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400">
                        <span class="material-symbols-outlined">send</span>
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Sending</p>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['sending'] }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                        <span class="material-symbols-outlined">check_circle</span>
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Completed</p>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['completed'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center gap-4">
                <div class="relative flex-1 min-w-[240px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
                    <input type="text" wire:model.live="search" placeholder="Search campaign name..." class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm pl-10 pr-4 py-2 focus:ring-2 focus:ring-primary/20 transition-all dark:text-white">
                </div>
                <select wire:model.live="status" class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm px-4 py-2 focus:ring-2 focus:ring-primary/20 transition-all dark:text-white">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="sending">Sending</option>
                    <option value="completed">Completed</option>
                    <option value="paused">Paused</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select wire:model.live="type" class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm px-4 py-2 focus:ring-2 focus:ring-primary/20 transition-all dark:text-white">
                    <option value="">All Types</option>
                    <option value="template">Template</option>
                    <option value="text">Text</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Campaign</th>
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</th>
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Audience</th>
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Stats (S/D/R/F)</th>
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Date</th>
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($campaigns as $campaign)
                            <tr class="transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600 font-bold">
                                            <span class="material-symbols-outlined">{{ $campaign->type === 'template' ? 'article' : 'notes' }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $campaign->name }}</p>
                                            <p class="text-xs text-slate-500">{{ Str::limit($campaign->description, 40) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
                                            'scheduled' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            'queued' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                            'sending' => 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-light',
                                            'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                            'paused' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                            'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                                            'failed' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                                        ];
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $statusColors[$campaign->status] ?? $statusColors['draft'] }}">
                                        {{ $campaign->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ number_format($campaign->recipient_count) }}</p>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ str_replace('_', ' ', $campaign->audience_type) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <span class="text-blue-600 dark:text-blue-400 font-bold" title="Sent">{{ $campaign->sent_count }}</span>
                                        <span class="text-slate-300 dark:text-slate-700">/</span>
                                        <span class="text-indigo-600 dark:text-indigo-400 font-bold" title="Delivered">{{ $campaign->delivered_count }}</span>
                                        <span class="text-slate-300 dark:text-slate-700">/</span>
                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold" title="Read">{{ $campaign->read_count }}</span>
                                        <span class="text-slate-300 dark:text-slate-700">/</span>
                                        <span class="text-rose-600 dark:text-rose-400 font-bold" title="Failed">{{ $campaign->failed_count }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-slate-900 dark:text-white font-medium">{{ $campaign->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-slate-500">{{ $campaign->created_at->format('H:i') }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('campaigns.show', $campaign->id) }}" class="p-2 text-slate-400 hover:text-primary hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-all" title="View Report">
                                            <span class="material-symbols-outlined text-[20px]">bar_chart</span>
                                        </a>
                                        
                                        @if($campaign->isDraft())
                                            <button @click="$dispatch('open-campaign-modal', { id: {{ $campaign->id }} })" class="p-2 text-slate-400 hover:text-amber-500 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-all" title="Edit">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </button>
                                        @endif

                                        <button wire:click="duplicate({{ $campaign->id }})" class="p-2 text-slate-400 hover:text-indigo-500 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-all" title="Duplicate">
                                            <span class="material-symbols-outlined text-[20px]">content_copy</span>
                                        </button>

                                        <button wire:confirm="Are you sure you want to delete this campaign?" wire:click="delete({{ $campaign->id }})" class="p-2 text-slate-400 hover:text-red-500 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-all" title="Delete">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 text-slate-300 dark:bg-slate-800 dark:text-slate-700">
                                            <span class="material-symbols-outlined text-5xl">campaign</span>
                                        </div>
                                        <p class="text-lg font-bold text-slate-900 dark:text-white">No campaigns found</p>
                                        <p class="max-w-xs text-slate-500">Create your first campaign to start broadcasting messages to your contacts.</p>
                                        <button @click="$dispatch('open-campaign-modal')" class="mt-2 inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary/30 transition-all hover:scale-[1.02] active:scale-[0.98]">
                                            <span class="material-symbols-outlined text-lg">add</span>
                                            Create Campaign
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($campaigns->hasPages())
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800">
                    {{ $campaigns->links() }}
                </div>
            @endif
        </div>
    </div>

    <livewire:campaigns.campaign-form-modal />
</div>
