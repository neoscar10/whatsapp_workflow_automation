<div class="p-6 md:p-8 space-y-8 max-w-7xl mx-auto w-full">
    {{-- Header Banner --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-2xl">webhook</span>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Outbound Webhooks</h1>
                <span class="bg-primary/10 text-primary text-xs font-semibold px-2.5 py-0.5 rounded-full">Developer API</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-2xl">
                Forward incoming Meta WhatsApp messages, status updates, and template changes to your custom backend endpoints, CRM, or automation tools (Zapier, Make, n8n) in real-time.
            </p>
        </div>

        <button 
            wire:click="openCreateModal"
            class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-all shadow-md shadow-primary/20 shrink-0"
        >
            <span class="material-symbols-outlined text-lg">add_link</span>
            <span>Add Webhook URL</span>
        </button>
    </div>

    {{-- Flash Notifications --}}
    @if(session()->has('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 rounded-xl text-sm font-medium">
            <span class="material-symbols-outlined text-emerald-500">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="flex items-center gap-3 p-4 bg-rose-50 border border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300 rounded-xl text-sm font-medium">
            <span class="material-symbols-outlined text-rose-500">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Main Webhooks Table Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        @if($webhooks->isEmpty())
            <div class="text-center py-16 px-6">
                <div class="size-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-slate-400 text-3xl">link_off</span>
                </div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-200 mb-1">No Webhook Endpoints Configured</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-6">
                    Connect your external platforms to receive real-time HTTP POST notifications whenever events happen on your WhatsApp business account.
                </p>
                <button 
                    wire:click="openCreateModal"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-white text-xs font-semibold hover:bg-primary/90 transition-all shadow-sm"
                >
                    <span class="material-symbols-outlined text-base">add_link</span>
                    <span>Add Destination Endpoint</span>
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="py-4 px-6">Name & Destination URL</th>
                            <th class="py-4 px-6">Subscribed Events</th>
                            <th class="py-4 px-6">Signing Secret</th>
                            <th class="py-4 px-6">Status</th>
                            <th class="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @foreach($webhooks as $webhook)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="font-bold text-sm text-slate-900 dark:text-slate-100">{{ $webhook->name }}</div>
                                    <div class="flex items-center gap-1.5 text-slate-500 font-mono text-[11px] mt-0.5 max-w-md truncate" title="{{ $webhook->url }}">
                                        <span class="material-symbols-outlined text-xs">open_in_new</span>
                                        <span class="truncate">{{ $webhook->url }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($webhook->events ?? [] as $eventKey)
                                            <span class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-mono px-2 py-0.5 rounded-md border border-slate-200 dark:border-slate-700">
                                                {{ $eventKey }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div x-data="{ showSecret: false }" class="flex items-center gap-2">
                                        <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400" x-text="showSecret ? '{{ $webhook->secret }}' : '••••••••••••••••••••'"></span>
                                        <button @click="showSecret = !showSecret" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                            <span class="material-symbols-outlined text-xs" x-text="showSecret ? 'visibility_off' : 'visibility'"></span>
                                        </button>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <button 
                                        wire:click="toggleActive({{ $webhook->id }})"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold transition-all {{ $webhook->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}"
                                    >
                                        <span class="size-1.5 rounded-full {{ $webhook->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                        <span>{{ $webhook->is_active ? 'Active' : 'Disabled' }}</span>
                                    </button>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- Test Ping Button --}}
                                        <button 
                                            wire:click="sendTestPing({{ $webhook->id }})"
                                            title="Send Test Ping POST payload"
                                            class="p-1.5 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-950/30 dark:text-amber-400 dark:hover:bg-amber-900/50 transition-all"
                                        >
                                            <span class="material-symbols-outlined text-base">send</span>
                                        </button>

                                        {{-- Logs Button --}}
                                        <button 
                                            wire:click="viewLogs({{ $webhook->id }})"
                                            title="View HTTP Delivery Logs"
                                            class="p-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition-all"
                                        >
                                            <span class="material-symbols-outlined text-base">history</span>
                                        </button>

                                        {{-- Edit Button --}}
                                        <button 
                                            wire:click="editWebhook({{ $webhook->id }})"
                                            title="Edit Webhook"
                                            class="p-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition-all"
                                        >
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>

                                        {{-- Delete Button --}}
                                        <button 
                                            wire:click="confirmDeleteWebhook({{ $webhook->id }})"
                                            title="Delete Webhook"
                                            class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-950/30 dark:text-rose-400 dark:hover:bg-rose-900/50 transition-all"
                                        >
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($webhooks->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $webhooks->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if($showCreateModal || $showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-xl overflow-hidden p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">webhook</span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
                            {{ $showCreateModal ? 'Add Outbound Webhook' : 'Edit Webhook Endpoint' }}
                        </h3>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form wire:submit.prevent="{{ $showCreateModal ? 'saveWebhook' : 'updateWebhook' }}" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Webhook Label / Name</label>
                        <input 
                            type="text" 
                            wire:model="name"
                            placeholder="e.g. Production CRM Relay or Zapier Trigger"
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-3 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary focus:outline-none"
                        />
                        @error('name') <span class="text-rose-500 text-[11px] font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Destination Webhook URL (HTTPS)</label>
                        <input 
                            type="url" 
                            wire:model="url"
                            placeholder="https://your-crm.com/api/v1/meta-whatsapp-webhook"
                            class="w-full text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-3 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary focus:outline-none"
                        />
                        @error('url') <span class="text-rose-500 text-[11px] font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Subscribed Event Triggers</label>
                        <div class="space-y-2">
                            @foreach($availableEvents as $eventKey => $eventLabel)
                                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-all cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        value="{{ $eventKey }}" 
                                        wire:model="events"
                                        class="mt-0.5 rounded text-primary focus:ring-primary dark:bg-slate-900 border-slate-300 dark:border-slate-700"
                                    />
                                    <div class="text-xs">
                                        <span class="font-mono font-bold text-slate-900 dark:text-slate-100">{{ $eventKey }}</span>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $eventLabel }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('events') <span class="text-rose-500 text-[11px] font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input 
                            type="checkbox" 
                            id="is_active_check" 
                            wire:model="is_active"
                            class="rounded text-primary focus:ring-primary"
                        />
                        <label for="is_active_check" class="text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                            Enable Webhook Delivery Immediately
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800 pt-4 mt-6">
                        <button 
                            type="button" 
                            wire:click="closeModal"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            class="px-5 py-2 rounded-xl text-xs font-semibold bg-primary text-white hover:bg-primary/90 transition-all shadow-md shadow-primary/20"
                        >
                            {{ $showCreateModal ? 'Create Webhook' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Test Ping Result Modal --}}
    @if($showPingModal && $pingResult)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500 text-xl">send</span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Webhook Test Ping Result</h3>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between p-3 rounded-xl {{ $pingResult['success'] ? 'bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300' : 'bg-rose-50 border border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300' }}">
                        <div class="flex items-center gap-2 font-bold">
                            <span class="material-symbols-outlined text-lg">{{ $pingResult['success'] ? 'task_alt' : 'warning' }}</span>
                            <span>{{ $pingResult['success'] ? 'Test Delivery Successful' : 'Test Delivery Failed' }}</span>
                        </div>
                        <div class="font-mono text-xs font-bold">
                            HTTP {{ $pingResult['status_code'] ?? 'ERR' }} ({{ $pingResult['duration_ms'] }} ms)
                        </div>
                    </div>

                    @if($pingResult['error'])
                        <div class="p-3 bg-rose-50/50 dark:bg-rose-950/30 rounded-xl text-rose-700 dark:text-rose-400 font-mono text-[11px]">
                            <strong>Error Details:</strong> {{ $pingResult['error'] }}
                        </div>
                    @endif

                    @if($pingResult['response_body'])
                        <div>
                            <span class="font-bold text-slate-700 dark:text-slate-300 mb-1 block">Server Response Snippet:</span>
                            <pre class="p-3 bg-slate-900 text-slate-200 font-mono text-[11px] rounded-xl overflow-x-auto max-h-48 leading-relaxed">{{ $pingResult['response_body'] }}</pre>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end pt-2">
                    <button wire:click="closeModal" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delivery Logs Modal --}}
    @if($showLogsModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-4xl overflow-hidden p-6 space-y-4 max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">history</span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">HTTP Delivery Audit Logs</h3>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                    @if($logs->isEmpty())
                        <div class="text-center py-12 text-slate-500 dark:text-slate-400 text-xs">
                            No delivery logs recorded for this webhook yet. Trigger WhatsApp events to view logs.
                        </div>
                    @else
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($logs as $log)
                                <div class="py-3 space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-primary">{{ $log->event_type }}</span>
                                            <span class="text-[11px] text-slate-400">{{ $log->created_at->format('M d, Y H:i:s') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-mono text-slate-400">Attempt #{{ $log->attempt }}</span>
                                            <span class="font-mono text-[11px] font-bold px-2 py-0.5 rounded-md {{ $log->delivered_at ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' }}">
                                                HTTP {{ $log->status_code ?? 'ERR' }}
                                            </span>
                                        </div>
                                    </div>
                                    @if($log->error_message)
                                        <div class="text-[11px] font-mono text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 p-2 rounded-lg">
                                            {{ $log->error_message }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="shrink-0 border-t border-slate-100 dark:border-slate-800 pt-3 flex justify-between items-center">
                    <div class="text-xs">
                        {{ $logs->links() }}
                    </div>
                    <button wire:click="closeModal" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                        Close
                    </button>
                </div>
        </div>
    @endif

    {{-- Custom Delete Confirmation Modal --}}
    @if($confirmingWebhookDeletionId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
            <div class="relative transform border border-slate-200 bg-white shadow-2xl transition-all dark:border-slate-800 dark:bg-slate-900 w-full max-w-md rounded-2xl overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-500 dark:bg-rose-900/30">
                            <span class="material-symbols-outlined text-rose-600">warning</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Delete Webhook Endpoint</h3>
                            <p class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                Are you sure you want to delete this webhook endpoint? This action will permanently remove it and all associated HTTP delivery logs. This cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-3 bg-slate-50 p-6 dark:bg-slate-800/50 sm:flex-row sm:justify-end">
                    <button wire:click="closeModal" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition-all hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        Cancel
                    </button>
                    <button wire:click="deleteWebhook" class="rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-lg shadow-red-600/20 transition-all hover:bg-red-500 active:scale-95">
                        <span wire:loading wire:target="deleteWebhook" class="mr-1.5 inline-block h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
