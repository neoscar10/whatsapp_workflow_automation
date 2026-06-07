<div class="p-8 space-y-8 flex-1 overflow-y-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Demo WhatsApp Setup</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure system-level demo WABA accounts and test phone numbers.</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Account Setup Form -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-primary">settings_suggest</span>
                WABA Configuration
            </h3>

            <form wire:submit.prevent="saveAccount" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">WhatsApp Business Account ID</label>
                    <input type="text" wire:model="waba_id" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" placeholder="e.g. 1093821038219" />
                    @error('waba_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Business Portfolio ID</label>
                    <input type="text" wire:model="business_id" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" placeholder="e.g. 849302830219" />
                    @error('business_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">System User Access Token</label>
                    <input type="password" wire:model="access_token" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" placeholder="••••••••••••••••" />
                    @error('access_token') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Connection Status</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $connectionStatus === 'connected' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                            {{ ucfirst($connectionStatus) }}
                        </span>
                    </div>
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveAccount" class="w-full py-2.5 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed">
                        <span wire:loading wire:target="saveAccount" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span wire:loading.remove wire:target="saveAccount">Save Credentials</span>
                        <span wire:loading wire:target="saveAccount">Saving...</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Phone Numbers Management -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-primary">phone_android</span>
                        Demo Phone Numbers
                    </h3>
                    <button wire:click="openCreateModal" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span>
                        Add Demo Number
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="p-4">Phone Number / ID</th>
                                <th class="p-4">Display Name</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium">
                            @forelse($numbers as $num)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="p-4">
                                        <p class="text-slate-900 dark:text-slate-100 font-semibold">{{ $num->phone_number }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">ID: {{ $num->phone_number_id }}</p>
                                    </td>
                                    <td class="p-4 text-slate-600 dark:text-slate-400">
                                        {{ $num->display_name }}
                                    </td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize {{ $num->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-850' }}">
                                            {{ $num->status }}
                                        </span>
                                    </td>
                                    <td class="p-4 space-x-3">
                                        <button wire:click="openEditModal({{ $num->id }})" class="text-xs font-bold text-primary hover:underline">
                                            Edit
                                        </button>
                                        <button wire:click="toggleNumberStatus({{ $num->id }})" class="text-xs font-bold text-slate-500 hover:text-slate-700">
                                            {{ $num->status === 'active' ? 'Disable' : 'Enable' }}
                                        </button>
                                        <button wire:click="deleteNumber({{ $num->id }})" class="text-xs font-bold text-rose-600 hover:underline">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-slate-400 dark:text-slate-500 font-bold">
                                        No demo numbers configured. Click "Add Demo Number" to start.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($numbers->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-850">
                    {{ $numbers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Phone Number Form Modal -->
    @if($showNumberModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $editingNumberId ? 'Edit Demo Number' : 'Add Demo Number' }}</h3>
                    <button wire:click="closeNumberModal" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <form wire:submit.prevent="saveNumber">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Display Name</label>
                            <input type="text" wire:model="display_name" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" placeholder="e.g. Test Service" />
                            @error('display_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Phone Number ID</label>
                            <input type="text" wire:model="phone_number_id" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" placeholder="e.g. 10928302830293" />
                            @error('phone_number_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Phone Number</label>
                            <input type="text" wire:model="phone_number" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" placeholder="e.g. 15550283028" />
                            @error('phone_number') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                        <button type="button" wire:click="closeNumberModal" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                            Save Phone Number
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
