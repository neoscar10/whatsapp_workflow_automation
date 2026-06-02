<div class="p-8 space-y-8 flex-1 overflow-y-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Funding Configuration</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure global recharge limits, wallet warnings, and pre-defined pricing package slots.</p>
        </div>
    </div>

    <!-- Alert Success Settings -->
    @if (session()->has('success_settings'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success_settings') }}
        </div>
    @endif

    <!-- Dynamic Settings Banner at the Top -->
    @if($showSettings)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4 animate-in slide-in-from-top-4 duration-200">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-primary">admin_panel_settings</span>
                    Wallet Threshold Settings
                </h3>
                <button wire:click="$set('showSettings', false)" class="text-slate-400 hover:text-slate-500">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>
            <form wire:submit.prevent="saveSettings" class="flex flex-col md:flex-row items-end gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Low Wallet Threshold Alert (₹)</label>
                    <input type="number" step="0.01" min="0" wire:model="wallet_threshold" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" />
                    @error('wallet_threshold') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="h-11 px-6 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-xl transition-colors shrink-0">
                    Save Changes
                </button>
            </form>
        </div>
    @endif

    <!-- Package Slots Container (Full Width) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-primary">local_offer</span>
                        Recharge Packages & Rates
                    </h3>
                    <div class="flex items-center gap-2">
                        <button wire:click="$toggle('showSettings')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 text-xs font-bold rounded-lg transition-colors flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">settings</span>
                            <span>Wallet Alert Threshold</span>
                        </button>
                        <button wire:click="openCreateModal" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Add Package Slot
                        </button>
                    </div>
                </div>

                @if (session()->has('success_packages'))
                    <div class="mx-6 mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('success_packages') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="p-4">Package Amt</th>
                                <th class="p-4">Text Msg</th>
                                <th class="p-4">Utility Temp</th>
                                <th class="p-4">Auth/OTP Temp</th>
                                <th class="p-4">Marketing Temp</th>
                                <th class="p-4">Automation</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-medium">
                            @forelse($packages as $pkg)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="p-4 text-slate-900 dark:text-slate-100 font-bold text-sm">
                                        ₹{{ number_format($pkg->amount, 2) }}
                                    </td>
                                    <td class="p-4 text-slate-600 dark:text-slate-400">₹{{ number_format($pkg->text_rate, 3) }}</td>
                                    <td class="p-4 text-slate-600 dark:text-slate-400">₹{{ number_format($pkg->template_utility_rate, 3) }}</td>
                                    <td class="p-4 text-slate-600 dark:text-slate-400">₹{{ number_format($pkg->template_auth_rate, 3) }}</td>
                                    <td class="p-4 text-slate-600 dark:text-slate-400">₹{{ number_format($pkg->template_marketing_rate, 3) }}</td>
                                    <td class="p-4 text-slate-600 dark:text-slate-400">₹{{ number_format($pkg->automation_rate, 3) }}</td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold capitalize {{ $pkg->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $pkg->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="p-4 relative text-right" x-data="{ open: false }">
                                        <div class="flex justify-end">
                                            <button @click="open = !open" class="flex items-center justify-center p-1 rounded-full text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                                <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                            </button>
                                        </div>
                                        <div x-show="open" @click.outside="open = false" x-transition class="absolute right-4 mt-1 w-40 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg z-30 py-1 text-left" style="display: none;">
                                            <button wire:click="openEditModal('{{ $pkg->id }}')" @click="open = false" class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                                <span>Edit</span>
                                            </button>
                                            <button wire:click="togglePackageStatus('{{ $pkg->id }}')" @click="open = false" class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2">
                                                <span class="material-symbols-outlined text-[16px]">{{ $pkg->is_active ? 'block' : 'check_circle' }}</span>
                                                <span>{{ $pkg->is_active ? 'Disable' : 'Enable' }}</span>
                                            </button>
                                            <button wire:click="deletePackage('{{ $pkg->id }}')" @click="open = false" class="w-full text-left px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 flex items-center gap-2">
                                                <span class="material-symbols-outlined text-[16px] text-rose-600">delete</span>
                                                <span>Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-slate-400 dark:text-slate-500 font-bold">
                                        No recharge packages seeded. Click "Add Package Slot" to create one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($packages->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-850">
                    {{ $packages->links() }}
                </div>
            @endif
        </div>

    <!-- Add/Edit Package Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $editingPackageId ? 'Edit Package Slot' : 'Add Package Slot' }}</h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <form wire:submit.prevent="savePackage">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Package Amount (₹)</label>
                            <input type="number" step="0.01" min="0" wire:model="amount" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" placeholder="e.g. 5000" />
                            @error('amount') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Text Msg Rate (₹)</label>
                                <input type="number" step="0.0001" min="0" wire:model="text_rate" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" />
                                @error('text_rate') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Utility Temp Rate (₹)</label>
                                <input type="number" step="0.0001" min="0" wire:model="template_utility_rate" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" />
                                @error('template_utility_rate') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Auth Temp Rate (₹)</label>
                                <input type="number" step="0.0001" min="0" wire:model="template_auth_rate" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" />
                                @error('template_auth_rate') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Marketing Temp Rate (₹)</label>
                                <input type="number" step="0.0001" min="0" wire:model="template_marketing_rate" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" />
                                @error('template_marketing_rate') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Automation Creation Rate (₹)</label>
                            <input type="number" step="0.0001" min="0" wire:model="automation_rate" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" />
                            @error('automation_rate') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                            Save Package
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
