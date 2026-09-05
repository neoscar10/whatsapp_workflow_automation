<div class="flex flex-col h-full bg-slate-50 dark:bg-slate-900/50 overflow-y-auto" @if($campaign->isSending() || $campaign->status === 'queued') wire:poll.5s @endif>
    <div class="px-8 py-8 space-y-8">
    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-center justify-between shadow-sm animate-in fade-in duration-200">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900"><span class="material-symbols-outlined text-base">close</span></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-800 dark:border-rose-900/40 dark:bg-rose-950/40 dark:text-rose-300 flex items-center justify-between shadow-sm animate-in fade-in duration-200">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">error</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400 hover:text-rose-900"><span class="material-symbols-outlined text-base">close</span></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('campaigns.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-400 transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $campaign->name }}</h1>
                <div class="flex items-center gap-2">
                    @php
                        $statusColors = [
                            'draft' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                            'scheduled' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                            'queued' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                            'sending' => 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-light',
                            'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                            'paused' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                            'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                            'failed' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                        ];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $statusColors[$campaign->status] ?? $statusColors['draft'] }}">
                        {{ $campaign->status }}
                    </span>
                    <span class="text-xs text-slate-400">• Created {{ $campaign->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($campaign->failed_count > 0)
                <button wire:click="retryFailed" 
                        wire:loading.attr="disabled" 
                        wire:target="retryFailed" 
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-bold text-rose-600 transition-all hover:bg-rose-100 dark:border-rose-900/30 dark:bg-rose-900/20 dark:text-rose-400 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="retryFailed" class="material-symbols-outlined text-lg">refresh</span>
                    <span wire:loading wire:target="retryFailed" class="material-symbols-outlined text-lg animate-spin">sync</span>
                    <span>Retry Failed</span>
                </button>
            @endif
            <button wire:click="exportReport" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-slate-900/20 transition-all hover:bg-slate-800 dark:bg-slate-800">
                <span class="material-symbols-outlined text-lg">download</span>
                Export Report
            </button>
        </div>
    </div>

    {{-- Progress Bar --}}
    @if($campaign->isSending() || $campaign->isCompleted() || $campaign->isPaused())
        @php
            $terminal = $campaign->sent_count + $campaign->failed_count + $campaign->skipped_recipient_count;
            $percent = $campaign->recipient_count > 0 ? ($terminal / $campaign->recipient_count) * 100 : 0;
        @endphp
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-2 flex items-center justify-between">
                <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Campaign Progress</span>
                <span class="text-sm font-bold text-primary">{{ round($percent) }}%</span>
            </div>
            <div class="h-3 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                <div class="h-full bg-primary transition-all duration-1000" style="width: {{ $percent }}%"></div>
            </div>
        </div>
    @endif

    {{-- Metrics --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $summary['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 text-blue-500">Sent</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $summary['sent'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 text-indigo-500">Delivered</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $summary['delivered'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 text-emerald-500">Read</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $summary['read'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 text-rose-500">Failed</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $summary['failed'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Skipped</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $summary['skipped'] }}</p>
        </div>
    </div>

    {{-- Recipients Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center gap-4">
            <div class="relative flex-1 min-w-[200px]">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input type="text" wire:model.live="search" placeholder="Search by name or phone..." class="w-full rounded-xl border-slate-200 bg-slate-50 pl-10 pr-4 text-sm dark:border-slate-700 dark:bg-slate-800">
            </div>
            <select wire:model.live="status" class="rounded-xl border-slate-200 bg-slate-50 text-sm dark:border-slate-700 dark:bg-slate-800">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="sent">Sent</option>
                <option value="delivered">Delivered</option>
                <option value="read">Read</option>
                <option value="failed">Failed</option>
                <option value="skipped">Skipped</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 dark:border-slate-800 dark:bg-slate-800/50">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Recipient</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Last Action</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Provider ID / Error</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recipients as $recipient)
                        <tr class="transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800">
                                        <span class="material-symbols-outlined">person</span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $recipient->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-slate-500">{{ $recipient->phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $rStatusColors = [
                                        'pending' => 'text-slate-400',
                                        'sent' => 'text-blue-500',
                                        'delivered' => 'text-indigo-500',
                                        'read' => 'text-emerald-500',
                                        'failed' => 'text-rose-500',
                                        'skipped' => 'text-amber-500',
                                        'cancelled' => 'text-slate-400',
                                    ];
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="flex h-2 w-2 rounded-full {{ str_replace('text', 'bg', $rStatusColors[$recipient->status] ?? 'bg-slate-400') }}"></span>
                                    <span class="text-xs font-bold {{ $rStatusColors[$recipient->status] ?? 'text-slate-400' }}">{{ ucfirst($recipient->status) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-slate-900 dark:text-white">
                                    {{ $recipient->last_attempted_at?->diffForHumans() ?? 'Not attempted' }}
                                </p>
                                <p class="text-[10px] text-slate-400">
                                    Attempts: {{ $recipient->attempts }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                @if($recipient->status === 'failed')
                                    <div class="max-w-[240px] space-y-1">
                                        <p class="text-xs font-black text-rose-600 dark:text-rose-400 truncate">{{ $recipient->meta_error_code ?: 'FAILED' }}</p>
                                        <p class="text-[11px] text-slate-600 dark:text-slate-400 line-clamp-2 leading-tight" title="{{ $recipient->meta_error_message }}">{{ $recipient->meta_error_message }}</p>
                                        <button type="button" wire:click="showErrorDetails({{ $recipient->id }})" class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 hover:text-rose-700 hover:underline dark:text-rose-400">
                                            <span class="material-symbols-outlined text-[13px]">info</span>
                                            View Full Error
                                        </button>
                                    </div>
                                @elseif($recipient->status === 'skipped')
                                    <p class="text-xs text-amber-600 font-medium">{{ $recipient->skip_reason }}</p>
                                @else
                                    <p class="text-[10px] font-mono text-slate-400 truncate max-w-[150px]">{{ $recipient->provider_message_id ?? 'N/A' }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(in_array($recipient->status, ['failed', 'skipped', 'pending']))
                                        <button type="button" 
                                                wire:click="retrySingleRecipient({{ $recipient->id }})" 
                                                wire:loading.attr="disabled"
                                                wire:target="retrySingleRecipient({{ $recipient->id }})"
                                                title="Retry sending to {{ $recipient->name ?? $recipient->phone }}"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                            <span wire:loading.remove wire:target="retrySingleRecipient({{ $recipient->id }})" class="material-symbols-outlined text-lg">refresh</span>
                                            <span wire:loading wire:target="retrySingleRecipient({{ $recipient->id }})" class="material-symbols-outlined text-lg animate-spin">sync</span>
                                        </button>
                                    @endif
                                    @if($recipient->contact_id)
                                        <a href="{{ route('contacts.index', ['search' => $recipient->phone]) }}" class="text-slate-400 hover:text-primary transition-colors" title="View Contact">
                                            <span class="material-symbols-outlined text-xl">account_circle</span>
                                        </a>
                                    @endif
                                    @if($recipient->conversation_id)
                                        <a href="{{ route('chats.index', ['conversation_id' => $recipient->conversation_id]) }}" class="text-slate-400 hover:text-primary transition-colors" title="View Chat">
                                            <span class="material-symbols-outlined text-xl">chat</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <p class="text-slate-500">No recipients found for the selected criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recipients->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-800/50">
                {{ $recipients->links() }}
            </div>
        @endif
    </div>

    {{-- Custom Error Details Modal --}}
    @if($selectedErrorDetails)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-in fade-in duration-200">
            <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-100 dark:border-slate-800 animate-in zoom-in-95 duration-200 space-y-6">
                <div class="flex items-start justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                            <span class="material-symbols-outlined text-2xl">error_med</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">Recipient Error Details</h3>
                            <p class="text-xs text-slate-500">Full dispatch exception trace &amp; Meta API response payload</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeErrorModal" class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                {{-- Contact Snapshot --}}
                <div class="grid grid-cols-2 gap-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 p-4 border border-slate-100 dark:border-slate-800 text-xs">
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400">Recipient Name</p>
                        <p class="font-bold text-slate-900 dark:text-white mt-0.5">{{ $selectedErrorDetails['name'] }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400">Phone Number</p>
                        <p class="font-mono font-bold text-slate-900 dark:text-white mt-0.5">{{ $selectedErrorDetails['phone'] }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400">Attempt Count</p>
                        <p class="font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ $selectedErrorDetails['attempts'] }} attempt(s)</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400">Last Attempted At</p>
                        <p class="font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ $selectedErrorDetails['last_attempted_at'] }}</p>
                    </div>
                </div>

                {{-- Error Code & Full Message --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-rose-500">Error Code</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 font-mono">
                            {{ $selectedErrorDetails['error_code'] }}
                        </span>
                    </div>
                    <div class="rounded-2xl bg-rose-50/50 dark:bg-rose-950/30 p-4 border border-rose-100 dark:border-rose-900/40 max-h-48 overflow-y-auto">
                        <p class="text-xs font-mono text-rose-900 dark:text-rose-200 leading-relaxed whitespace-pre-wrap select-text">{{ $selectedErrorDetails['error_message'] }}</p>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800 pt-4">
                    <button type="button" wire:click="closeErrorModal" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 transition-colors">
                        Close
                    </button>
                    <button type="button" 
                            wire:click="retrySingleRecipient({{ $selectedErrorDetails['id'] }})" 
                            wire:loading.attr="disabled"
                            wire:target="retrySingleRecipient({{ $selectedErrorDetails['id'] }})"
                            class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-600/20 transition-all flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="retrySingleRecipient({{ $selectedErrorDetails['id'] }})" class="material-symbols-outlined text-base">refresh</span>
                        <span wire:loading wire:target="retrySingleRecipient({{ $selectedErrorDetails['id'] }})" class="material-symbols-outlined text-base animate-spin">sync</span>
                        <span>Retry Recipient</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
