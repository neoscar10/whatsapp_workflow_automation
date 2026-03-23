@if ($showWebhookModal)
    <div class="relative z-[9999]" aria-labelledby="modal-title" role="dialog" aria-modal="true" wire:keydown.escape.window="closeWebhookModal">
        
        {{-- Background backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
             wire:click="closeWebhookModal"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                {{-- Modal panel --}}
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 dark:border-slate-800 dark:bg-slate-900">
                    
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/30">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100" id="modal-title">
                                Setup Webhooks
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Configure your callback URL to receive messages and status events in real time.
                            </p>
                        </div>

                        <button type="button"
                                wire:click="closeWebhookModal"
                                class="text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-slate-200">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <div class="p-6 space-y-6">
                        @if($webhookSetupError)
                            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                                <div class="flex items-center gap-2 mb-1 font-bold">
                                    <span class="material-symbols-outlined text-[18px]">error</span>
                                    Error
                                </div>
                                {{ $webhookSetupError }}
                            </div>
                        @endif

                        @if($webhookSetupMessage)
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                                <div class="flex items-center gap-2 font-bold">
                                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                    {{ $webhookSetupMessage }}
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-4 rounded-lg bg-slate-50 p-4 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                            <div class="flex-1 space-y-1">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</p>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full {{ $webhookStatus === 'verified' ? 'bg-emerald-500' : 'bg-amber-500' }}"></div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white capitalize">
                                        {{ str_replace('_', ' ', $webhookStatus) }}
                                    </p>
                                </div>
                                @if($webhookVerifiedAt)
                                    <p class="text-[10px] text-slate-500">Verified: {{ $webhookVerifiedAt->format('M j, H:i') }}</p>
                                @endif
                            </div>

                            <div class="flex-1 space-y-1 border-l pl-4 border-slate-200 dark:border-slate-700">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">App Subscription</p>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full {{ $webhookSubscriptionStatus === 'subscribed' ? 'bg-emerald-500' : 'bg-slate-400' }}"></div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white capitalize">
                                        {{ str_replace('_', ' ', $webhookSubscriptionStatus) }}
                                    </p>
                                </div>
                                @if($webhookSubscribedAt)
                                    <p class="text-[10px] text-slate-500">Subscribed: {{ $webhookSubscribedAt->format('M j, H:i') }}</p>
                                @endif
                            </div>
                        </div>

                        @if($webhookLastCheckedAt)
                            <div class="flex items-center justify-between px-1">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">history</span>
                                    Last Checked: {{ $webhookLastCheckedAt->diffForHumans() }}
                                </p>
                                @if($webhookLastError)
                                     <p class="text-[10px] font-bold text-red-500 uppercase flex items-center gap-1" title="{{ $webhookLastError }}">
                                        <span class="material-symbols-outlined text-[14px]">error</span>
                                        Check Failed
                                    </p>
                                @endif
                            </div>
                        @endif

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Callback URL</label>
                                <input type="text"
                                       readonly
                                       value="{{ $webhookCallbackUrl }}"
                                       class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 font-mono text-sm text-slate-600 outline-none dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400" />
                                <p class="mt-1 text-xs text-slate-500">Provide this URL in the Meta App Dashboard under WhatsApp > Configuration.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Verify Token</label>
                                <input type="text"
                                       readonly
                                       value="{{ $webhookVerifyToken }}"
                                       class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 font-mono text-sm text-slate-600 outline-none dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400" />
                                <p class="mt-1 text-xs text-slate-500">This token is used by Meta to safely verify our endpoint.</p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 dark:border-blue-900/30 dark:bg-blue-900/20 dark:text-blue-300">
                            <h4 class="font-bold flex items-center gap-2 mb-2">
                                <span class="material-symbols-outlined text-[18px]">list_alt</span>
                                Setup Steps
                            </h4>
                            <ol class="list-decimal list-inside space-y-1 ml-1">
                                <li>Open your Meta App Dashboard > <strong>WhatsApp</strong> > <strong>Configuration</strong>.</li>
                                <li>Click <strong>Edit</strong> next to Webhook.</li>
                                <li>Paste the <strong>Callback URL</strong> and <strong>Verify Token</strong> above, then click <strong>Verify and save</strong>.</li>
                                <li>Below that in Webhook fields, click <strong>Manage</strong> and subscribe to <code>messages</code>.</li>
                                <li>Return here and click <strong>Subscribe App</strong> below to attach the app to your WABA.</li>
                            </ol>
                        </div>
                    </div>

                    <div class="flex flex-col justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/50 sm:flex-row">
                        <button type="button"
                                wire:click="closeWebhookModal"
                                class="rounded-lg px-5 py-2 relative z-10 font-semibold text-slate-600 transition-colors hover:bg-slate-200 dark:text-slate-400 dark:hover:bg-slate-700">
                            Close
                        </button>

                        <button type="button"
                                wire:click="verifyWebhookHealth"
                                wire:loading.attr="disabled"
                                class="rounded-lg border border-slate-300 bg-white relative z-10 px-5 py-2 font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-70 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 hover:dark:bg-slate-600">
                            <span wire:loading.remove wire:target="verifyWebhookHealth">
                                Refresh Status
                            </span>
                            <span wire:loading wire:target="verifyWebhookHealth">
                                Checking...
                            </span>
                        </button>

                        <button type="button"
                                wire:click="subscribeWebhook"
                                wire:loading.attr="disabled"
                                {{ !$has_connected_account ? 'disabled title="Please connect your WhatsApp account first."' : '' }}
                                class="rounded-lg bg-primary relative z-10 px-6 py-2 font-bold text-white shadow-md transition-all hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-70">
                            <span class="flex items-center gap-2" wire:loading.remove wire:target="subscribeWebhook">
                                <span class="material-symbols-outlined text-[18px]">add_link</span>
                                Subscribe App
                            </span>
                            <span wire:loading wire:target="subscribeWebhook">
                                Subscribing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
