<div class="p-8 space-y-6 flex-1 overflow-y-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Wallet Monitoring</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Monitor company wallet balances and inspect ledger transaction records.</p>
        </div>
    </div>

    <!-- Filters/Search -->
    <div class="flex items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
        <div class="relative flex-1 max-w-md group">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-slate-400 group-focus-within:text-primary transition-colors">search</span>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search by company name, owner, or email..."
                class="w-full h-11 rounded-xl border border-slate-200 bg-slate-50 pl-12 pr-4 text-sm font-medium text-slate-900 placeholder:text-slate-500 focus:ring-4 focus:ring-primary/10 transition-all dark:border-slate-800 dark:bg-slate-800 dark:text-white outline-none"
            />
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="p-4">Company Name</th>
                        <th class="p-4">Owner User</th>
                        <th class="p-4">Billing Status</th>
                        <th class="p-4">Wallet Balance</th>
                        <th class="p-4">Demo Balance</th>
                        <th class="p-4">Last Transaction At</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium">
                    @forelse($wallets as $wallet)
                        @php
                            $company = $wallet->user?->company;
                            $isDemo = $company && $company->status === 'demo';
                            $statusColors = [
                                'active' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                'suspended' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
                                'demo' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                            ];
                            $badgeClass = $statusColors[$company?->status ?? 'active'] ?? 'bg-slate-100 text-slate-800';
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold uppercase">
                                        {{ substr($company?->name ?? 'System', 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="text-slate-900 dark:text-slate-100 font-semibold">{{ $company?->name ?? 'Unknown Company' }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5 font-bold">@slug({{ $company?->slug ?? 'n-a' }})</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-slate-600 dark:text-slate-400">
                                {{ $wallet->user?->name ?? 'N/A' }}
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $wallet->user?->email }}</p>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize {{ $badgeClass }}">
                                    {{ $company?->status ?? 'active' }}
                                </span>
                            </td>
                            <td class="p-4 text-slate-900 dark:text-slate-100 font-semibold">
                                <span class="{{ $isDemo ? 'text-slate-400 line-through font-medium' : 'text-slate-900 dark:text-white' }}">
                                    ₹{{ number_format($wallet->balance, 2) }}
                                </span>
                            </td>
                            <td class="p-4">
                                @if($isDemo)
                                    <span class="text-amber-600 dark:text-amber-400 font-bold">
                                        ₹{{ number_format($company->demo_credits ?? 0.00, 2) }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-600 dark:text-slate-400">
                                {{ $wallet->last_transaction_at ? $wallet->last_transaction_at->format('Y-m-d H:i') : 'Never' }}
                            </td>
                            <td class="p-4 text-right">
                                <button wire:click="viewDetails('{{ $wallet->id }}')" class="px-3 py-1.5 text-xs font-bold bg-primary/10 hover:bg-primary/20 text-primary rounded-lg transition-colors inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                                    <span>View Ledger</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 dark:text-slate-500 font-bold">
                                No wallets registered in the database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($wallets->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-850">
                {{ $wallets->links() }}
            </div>
        @endif
    </div>

    <!-- Ledger / Transaction Details Modal -->
    @if($showDetailsModal && $selectedWallet)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-3xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Wallet Ledger - {{ $selectedWallet->user?->company?->name }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Owner: {{ $selectedWallet->user?->name }} ({{ $selectedWallet->user?->email }})</p>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Stats summary cards inside modal -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-800 rounded-xl p-4">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Wallet Balance</span>
                            <span class="text-lg font-bold text-slate-900 dark:text-white mt-1 block">₹{{ number_format($selectedWallet->balance, 2) }}</span>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-800 rounded-xl p-4">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Demo Credits</span>
                            <span class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-1 block">
                                {{ $selectedWallet->user?->company?->status === 'demo' ? '₹' . number_format($selectedWallet->user?->company?->demo_credits ?? 0.00, 2) : 'Inactive' }}
                            </span>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-800 rounded-xl p-4">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Wallet Status</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold capitalize mt-2 bg-emerald-100 text-emerald-800">
                                {{ $selectedWallet->status->value ?? 'active' }}
                            </span>
                        </div>
                    </div>

                    <!-- Ledger Table -->
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    <th class="p-3">Reference / Date</th>
                                    <th class="p-3">Type</th>
                                    <th class="p-3">Category</th>
                                    <th class="p-3">Amount</th>
                                    <th class="p-3">Balance Ledger</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-medium text-slate-700 dark:text-slate-300">
                                @forelse($selectedTransactions as $tx)
                                    <tr>
                                        <td class="p-3">
                                            <p class="font-semibold text-slate-900 dark:text-white">{{ $tx->reference }}</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $tx->created_at->format('Y-m-d H:i') }}</p>
                                            @if($tx->description)
                                                <p class="text-[10px] text-slate-500 italic mt-0.5">{{ $tx->description }}</p>
                                            @endif
                                        </td>
                                        <td class="p-3">
                                            @if($tx->type->value === 'credit')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                    Credit
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                                    Debit
                                                </span>
                                            @endif
                                        </td>
                                        <td class="p-3 capitalize">
                                            {{ strtolower($tx->category->value ?? $tx->category) }}
                                        </td>
                                        <td class="p-3 font-semibold {{ $tx->type->value === 'credit' ? 'text-emerald-600' : 'text-rose-600' }}">
                                            {{ $tx->type->value === 'credit' ? '+' : '-' }}₹{{ number_format($tx->amount, 2) }}
                                        </td>
                                        <td class="p-3 text-slate-500">
                                            <span class="block">Before: ₹{{ number_format($tx->balance_before, 2) }}</span>
                                            <span class="block font-semibold text-slate-700 dark:text-slate-300">After: ₹{{ number_format($tx->balance_after, 2) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-6 text-center text-slate-400 dark:text-slate-500 font-bold">
                                            No transaction records found for this wallet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($selectedTransactions->hasPages())
                        <div class="pt-2">
                            {{ $selectedTransactions->links() }}
                        </div>
                    @endif
                </div>

                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button wire:click="closeModal" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                        Close Ledger
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
