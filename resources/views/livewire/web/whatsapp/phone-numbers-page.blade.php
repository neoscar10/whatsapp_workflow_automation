<div class="mx-auto w-full max-w-[1400px] space-y-8 p-10">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Manage Phone Numbers</h2>
            <p class="mt-1 text-[15px] font-medium text-slate-500 dark:text-slate-400">
                Configure and manage your WhatsApp Cloud API phone numbers and their statuses.
            </p>

            @unless($hasConnectedAccount)
                <div class="mt-4 inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700 dark:border-amber-900/30 dark:bg-amber-900/20 dark:text-amber-300">
                    <span class="material-symbols-outlined text-base">info</span>
                    <span>
                        Connect your WhatsApp account before adding numbers.
                        <a href="{{ route('whatsapp.setup.account') }}" class="font-semibold underline">Go to Account Setup</a>
                    </span>
                </div>
            @endunless
        </div>

        <div class="flex items-center gap-3">
            @if($hasConnectedAccount)
                <button type="button"
                        wire:click="syncFromMeta"
                        wire:loading.attr="disabled"
                        class="flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/5 px-5 py-2.5 text-sm font-bold text-primary transition-all hover:bg-primary/10 active:scale-95 disabled:opacity-50">
                    <span class="material-symbols-outlined text-lg" wire:loading.remove wire:target="syncFromMeta">sync</span>
                    <span class="animate-spin material-symbols-outlined text-lg" wire:loading wire:target="syncFromMeta">sync</span>
                    <span wire:loading.remove wire:target="syncFromMeta">Sync from Meta</span>
                    <span wire:loading wire:target="syncFromMeta">Syncing...</span>
                </button>

                <button type="button"
                        wire:click="openCreateModal"
                        class="flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary/30 transition-all hover:bg-primary/90 active:scale-95">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Add Number
                </button>
            @endif

            <a href="{{ route('whatsapp.setup.account') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                <span class="material-symbols-outlined text-lg">settings_suggest</span>
                Account Setup
            </a>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->has('phone_numbers'))
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first('phone_numbers') }}
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <div class="flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
            <button type="button"
                    wire:click="$set('statusTab', 'all')"
                    class="rounded-lg px-6 py-2 text-sm {{ $statusTab === 'all' ? 'bg-white shadow-sm font-semibold text-slate-900 dark:bg-slate-700 dark:text-white' : 'font-medium text-slate-500 dark:text-slate-400' }}">
                All Numbers
            </button>
            <button type="button"
                    wire:click="$set('statusTab', 'active')"
                    class="rounded-lg px-6 py-2 text-sm {{ $statusTab === 'active' ? 'bg-white shadow-sm font-semibold text-slate-900 dark:bg-slate-700 dark:text-white' : 'font-medium text-slate-500 dark:text-slate-400' }}">
                Active
            </button>
            <button type="button"
                    wire:click="$set('statusTab', 'inactive')"
                    class="rounded-lg px-6 py-2 text-sm {{ $statusTab === 'inactive' ? 'bg-white shadow-sm font-semibold text-slate-900 dark:bg-slate-700 dark:text-white' : 'font-medium text-slate-500 dark:text-slate-400' }}">
                Inactive
            </button>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative group">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-slate-400 group-focus-within:text-primary transition-colors">search</span>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search numbers..."
                    class="w-72 h-12 rounded-xl border border-slate-200 bg-slate-50 pl-12 pr-4 text-sm font-medium text-slate-900 placeholder:text-slate-500 focus:ring-4 focus:ring-primary/10 transition-all dark:border-slate-800 dark:bg-slate-800 dark:text-white"
                />
            </div>

            <button type="button"
                    class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white h-12 px-5 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                <span class="material-symbols-outlined text-[20px]">filter_list</span>
                Filter
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Phone Number</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Verified Name</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status / Quality</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Last Synced</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($numbers as $number)
                        <tr class="transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                            <td class="whitespace-nowrap px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded {{ $number->status === 'active' ? 'bg-primary/5 text-primary' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                        <span class="material-symbols-outlined text-lg">call</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-900 dark:text-white">
                                            {{ $number->phone_number ?: 'Pending sync from Meta' }}
                                        </span>
                                        <span class="text-xs text-slate-400">
                                            ID: {{ $number->phone_number_id }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                             <td class="whitespace-nowrap px-6 py-5 text-slate-600 dark:text-slate-300">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $number->verified_name ?: $number->display_name }}</span>
                                    @if($number->verified_name && $number->verified_name !== $number->display_name)
                                        <span class="text-[10px] text-slate-400">Local: {{ $number->display_name }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-5">
                                <div class="flex flex-col gap-1.5">
                                    @if ($number->status === 'active')
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-800 dark:text-slate-400">
                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            {{ ucfirst($number->status) }}
                                        </span>
                                    @endif

                                    @if($number->quality_rating)
                                        <div class="flex items-center gap-1">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Quality:</span>
                                            <span class="text-[10px] font-bold {{ $number->quality_rating === 'GREEN' ? 'text-emerald-500' : ($number->quality_rating === 'YELLOW' ? 'text-amber-500' : 'text-red-500') }}">
                                                {{ $number->quality_rating }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col">
                                    <span>{{ $number->synced_at ? $number->synced_at->diffForHumans() : 'Never' }}</span>
                                    @if($number->last_sync_error)
                                        <span class="text-[10px] text-red-500 truncate max-w-[150px]" title="{{ $number->last_sync_error }}">Sync Error</span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-5 text-right space-x-3">
                                <button type="button"
                                        wire:click="openEditModal({{ $number->id }})"
                                        class="text-sm font-semibold text-primary hover:text-primary/80">
                                    Edit
                                </button>

                                <button type="button"
                                        wire:click="toggleNumberStatus({{ $number->id }})"
                                        class="text-sm font-semibold transition-colors {{ $number->status === 'active' ? 'text-slate-400 hover:text-red-500' : 'text-primary hover:text-primary/80' }}">
                                    {{ $number->status === 'active' ? 'Disable' : 'Enable' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                No phone numbers found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/30">
            <span class="text-sm text-slate-500 dark:text-slate-400">
                Showing {{ $numbers->firstItem() ?? 0 }}-{{ $numbers->lastItem() ?? 0 }} of {{ $numbers->total() }} numbers
            </span>

            <div>
                {{ $numbers->links() }}
            </div>
        </div>
    </div>

    <div class="mt-8 flex items-start gap-4 rounded-xl border border-primary/20 bg-primary/5 p-6 dark:bg-primary/10">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary/20 text-primary">
            <span class="material-symbols-outlined">help</span>
        </div>

        <div>
            <h3 class="font-bold text-slate-900 dark:text-white">Need help setting up a new number?</h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Check our WhatsApp Cloud API guide to learn how to register and verify new phone numbers for your organization.
            </p>
            <a href="javascript:void(0)" class="mt-3 inline-block text-sm font-bold text-primary hover:underline">
                Read documentation →
            </a>
        </div>
    </div>

    @include('partials.panel.whatsapp.phone-number-modal')
</div>
