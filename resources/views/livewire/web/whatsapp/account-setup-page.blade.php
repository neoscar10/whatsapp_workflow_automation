<div class="mx-auto w-full max-w-[1400px] space-y-8 p-10">
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="mb-2 text-3xl font-black tracking-tight text-slate-900 dark:text-white">Connect WhatsApp Account</h1>
            <p class="text-slate-500 dark:text-slate-400">
                Configure your Meta Business credentials to enable API messaging.
            </p>
        </div>

        <div class="flex items-center gap-3 rounded-lg border px-4 py-2 {{ $isConnected ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800/30 dark:bg-emerald-900/20' : ($connectionStatus === 'pending-sync' ? 'border-amber-200 bg-amber-50 dark:border-amber-800/30 dark:bg-amber-900/20' : 'border-red-200 bg-red-50 dark:border-red-800/30 dark:bg-red-900/20') }}">
            <div class="h-2 w-2 rounded-full {{ $isConnected ? 'bg-emerald-500' : ($connectionStatus === 'pending-sync' ? 'animate-pulse bg-amber-500' : 'animate-pulse bg-red-500') }}"></div>
            <span class="text-sm font-semibold {{ $isConnected ? 'text-emerald-600 dark:text-emerald-400' : ($connectionStatus === 'pending-sync' ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                @if($connectionStatus === 'pending-sync')
                    Syncing...
                @else
                    {{ $isConnected ? 'Connected' : 'Not Connected' }}
                @endif
            </span>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($lastSyncError)
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-start gap-3">
             <span class="material-symbols-outlined text-lg">error</span>
             <div>
                 <p class="font-bold">Last Sync Error</p>
                 <p>{{ $lastSyncError }}</p>
             </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-3 border-b border-slate-100 p-6 dark:border-slate-800">
                    <span class="material-symbols-outlined text-primary">api</span>
                    <h3 class="font-bold">API Configuration</h3>
                </div>

                <form wire:submit="save">
                    <div class="space-y-6 p-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Access Token</label>
                            <div class="relative">
                                <input type="{{ $showAccessToken ? 'text' : 'password' }}"
                                       wire:model.defer="access_token"
                                       placeholder="{{ $hasSavedToken ? 'Token already configured. Enter a new token to replace it.' : 'EAAG...' }}"
                                        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 pr-12 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary dark:border-slate-800 dark:bg-slate-800 dark:text-white" />
                                <button type="button"
                                        wire:click="toggleAccessTokenVisibility"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                    <span class="material-symbols-outlined text-xl">
                                        {{ $showAccessToken ? 'visibility_off' : 'visibility' }}
                                    </span>
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-500">
                                Generate a permanent token from your Meta App Dashboard under WhatsApp Setup.
                            </p>
                            @error('access_token')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    WhatsApp Business Account ID (WABA ID)
                                </label>
                                <input type="text"
                                       wire:model.defer="waba_id"
                                       placeholder="10982347523..."
                                        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary dark:border-slate-800 dark:bg-slate-800 dark:text-white" />
                                @error('waba_id')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Business ID
                                </label>
                                <input type="text"
                                       wire:model.defer="business_id"
                                       placeholder="123456789012345"
                                        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary dark:border-slate-800 dark:bg-slate-800 dark:text-white" />
                                @error('business_id')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 p-6 dark:border-slate-800 dark:bg-slate-800/50">
                        <button type="button"
                                wire:click="discardChanges"
                                class="rounded-lg px-6 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-200 dark:text-slate-400 dark:hover:bg-slate-700">
                            Discard
                        </button>

                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary/20 transition-transform hover:bg-primary/90 active:scale-95 disabled:cursor-not-allowed disabled:opacity-70">
                            <span class="material-symbols-outlined text-lg" wire:loading.remove wire:target="save">cable</span>
                            <span wire:loading.remove wire:target="save">Connect Account</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full text-slate-500 shadow-inner {{ $webhookStatus === 'verified' && $webhookSubscriptionStatus === 'subscribed' ? 'bg-emerald-50 text-emerald-500 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800' }}">
                        <span class="material-symbols-outlined">sync_alt</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Webhook Configuration</h4>
                            @if($webhookStatus !== 'not_configured')
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded border {{ $webhookStatus === 'verified' ? 'bg-emerald-50 text-emerald-600 border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400' : 'bg-amber-50 text-amber-600 border-amber-200 dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-400' }}">
                                    {{ str_replace('_', ' ', $webhookStatus) }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500">
                            Configure your callback URL to receive messages in real-time.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    @if($webhookSubscriptionStatus === 'subscribed')
                        <div class="hidden sm:flex flex-col items-end mr-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">App</span>
                            <span class="flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Subscribed
                            </span>
                        </div>
                    @endif
                    <button type="button"
                            wire:click="openWebhookModal"
                            class="whitespace-nowrap rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 shadow-sm active:scale-95">
                        Setup Webhooks
                    </button>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-primary to-blue-700 p-6 text-white">
                <div class="relative z-10">
                    <h4 class="mb-2 font-bold">Need Help?</h4>
                    <p class="mb-4 text-sm leading-relaxed text-blue-100">
                        Follow our step-by-step guide on how to obtain your Meta Business credentials.
                    </p>
                    <a href="javascript:void(0)"
                       class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm font-bold backdrop-blur-sm transition-colors hover:bg-white/20">
                        View Documentation
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                    </a>
                </div>
                <div class="absolute -bottom-8 -right-8 opacity-20">
                    <span class="material-symbols-outlined text-[120px]">help_center</span>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h4 class="mb-4 text-sm font-bold">Meta Status</h4>
                <ul class="space-y-4">
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-lg text-green-500">check_circle</span>
                        <div class="flex-1">
                            <p class="text-xs font-bold">Cloud API</p>
                            <p class="text-[10px] text-slate-500">Operational</p>
                        </div>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-lg {{ $lastSyncedAt ? 'text-green-500' : 'text-slate-300' }}">sync</span>
                        <div class="flex-1">
                            <p class="text-xs font-bold">Last Account Sync</p>
                            <p class="text-[10px] text-slate-500">{{ $lastSyncedAt ? $lastSyncedAt->diffForHumans() : 'Never' }}</p>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="aspect-video rounded-xl border-4 border-white bg-cover bg-center shadow-lg dark:border-slate-800"
                 style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAmfipHyiBUMQGULy6mJ_Qt3eOIg3zkwPL5m5Fvd-F099IXjF5tUm-BXN4xyFCeuH4ax6TZj5OfrWjAqpB0-Oh-gZ5QD-rMK9wLnnT-BS2OB9wToS_FcHGEVtIhc6fJAc1gu31x-4nn9xRZgQ6TY0FaVg_of1nr1DiqO5gQh2TiFke7MKdC0tJLO1gl3mjAqWP3xk7WWxgOmgE8tJNDmztiyd96jAzRgecUoM7QIBMdEr_resrSQJipWjuiBuOgeWPchJiGO1zr91o');">
            </div>
        </div>
    </div>

    @include('partials.panel.whatsapp.webhook-setup-modal')
</div>
