<div class="p-8 max-w-7xl mx-auto w-full space-y-8">
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

    @if ($isLowBalance)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 flex items-center gap-3 dark:border-amber-900/30 dark:bg-amber-950/20 dark:text-amber-400">
            <span class="material-symbols-outlined text-[20px] text-amber-600 dark:text-amber-500">warning</span>
            <div>
                <p class="font-bold">Low Account Balance Warning</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Your balance is below the warning threshold of ₹{{ number_format($threshold, 2) }}. Please top up your wallet to continue uninterrupted services.</p>
            </div>
        </div>
    @endif

    <!-- Header Section -->
    <section class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[24px] font-bold text-slate-900 dark:text-white tracking-tight">Wallet Overview</h2>
            <p class="text-on-surface-variant dark:text-slate-400 text-[14px]">Securely manage balances, fund operations, and trace transaction ledgers.</p>
        </div>
        @if(!$isDemo)
        <button 
            type="button"
            class="bg-primary text-white px-6 py-2.5 rounded-lg text-[14px] font-semibold flex items-center gap-2 shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all"
            wire:click="$dispatch('open-funding-modal')"
        >
            <span class="material-symbols-outlined text-[20px]" data-icon="add">add</span>
            Fund Wallet
        </button>
        @else
        <button 
            type="button"
            class="bg-slate-300 text-slate-500 dark:bg-slate-800 dark:text-slate-400 px-6 py-2.5 rounded-lg text-[14px] font-semibold flex items-center gap-2 cursor-not-allowed select-none"
            disabled
            title="Real funding is disabled in Demo Mode"
        >
            <span class="material-symbols-outlined text-[20px]">block</span>
            Funding Disabled
        </button>
        @endif
    </section>

    <!-- Metric Grid -->
    <section class="grid grid-cols-1 {{ $isDemo ? 'md:grid-cols-2' : 'md:grid-cols-3' }} gap-6">
        <!-- Card 1 (Current Balance) -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between group hover:border-primary/30 transition-colors">
            <div class="flex justify-between items-start mb-4">
                <span class="text-[12px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    {{ $isDemo ? 'Demo Balance' : 'Current Balance' }}
                </span>
                @if($isDemo)
                    <span class="bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">DEMO MODE</span>
                @else
                    <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">{{ $wallet->status->value }}</span>
                @endif
            </div>
            <div class="space-y-1">
                <div class="text-[32px] font-extrabold text-slate-900 dark:text-white flex items-baseline gap-2">
                    <span class="text-[18px] font-medium text-slate-400 dark:text-slate-500">₹</span>{{ number_format($isDemo ? $demoCredits : $wallet->balance, 2) }}
                </div>
                <div class="text-[12px] font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]" data-icon="currency_exchange">currency_exchange</span>
                    {{ $wallet->currency }} {{ $isDemo ? 'Demo Account' : 'Currency Account' }}
                </div>
            </div>
        </div>

        @if(!$isDemo)
        <!-- Card 2 (Total Funded Credits) -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between group hover:border-primary/30 transition-colors">
            <div class="flex justify-between items-start mb-4">
                <span class="text-[12px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Funded Credits</span>
                <div class="w-8 h-8 rounded-lg bg-primary/5 flex items-center justify-center text-primary dark:bg-primary/20">
                    <span class="material-symbols-outlined text-[20px]" data-icon="payments">payments</span>
                </div>
            </div>
            <div class="space-y-1">
                <div class="text-[32px] font-extrabold text-slate-900 dark:text-white flex items-baseline gap-2">
                    <span class="text-[18px] font-medium text-slate-400 dark:text-slate-500">₹</span>{{ number_format($totalFunded, 2) }}
                </div>
                <p class="text-[12px] text-slate-500 dark:text-slate-400">
                    @if($latestFundingDate)
                        Latest: {{ $latestFundingDate->format('M d, Y') }}
                    @else
                        No successful funding transactions
                    @endif
                </p>
            </div>
        </div>
        @endif

        <!-- Card 3 (Activity - Last 30 Days) -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between group hover:border-primary/30 transition-colors">
            <div class="flex justify-between items-start mb-4">
                <span class="text-[12px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Activity (Last 30 Days)</span>
                <div class="w-8 h-8 rounded-lg bg-primary/5 flex items-center justify-center text-primary dark:bg-primary/20">
                    <span class="material-symbols-outlined text-[20px]" data-icon="analytics">analytics</span>
                </div>
            </div>
            <div class="space-y-1">
                <div class="text-[32px] font-extrabold text-slate-900 dark:text-white">{{ $recentTransactionsCount }}</div>
                <p class="text-[12px] text-slate-500 dark:text-slate-400">Total ledger transaction logs generated</p>
            </div>
        </div>
    </section>

    <!-- Transaction History Section -->
    <section class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col shadow-sm">
        <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50 dark:bg-slate-900/50">
            <div class="flex items-center gap-3">
                <h3 class="text-[16px] font-bold text-slate-900 dark:text-white">Transaction History</h3>
                <span class="bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-bold px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-600">{{ $transactions->total() }} total logs</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative min-w-[240px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]" data-icon="search">search</span>
                    <input 
                        type="text"
                        class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg text-[14px] focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" 
                        placeholder="Search references..." 
                        wire:model.live.debounce.300ms="search"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <select 
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg px-3 py-2 text-[14px] font-medium outline-none focus:ring-2 focus:ring-primary/20"
                        wire:model.live="type"
                    >
                        <option value="">All Flow Types</option>
                        <option value="credit">Credits</option>
                        <option value="debit">Debits</option>
                    </select>
                    <select 
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg px-3 py-2 text-[14px] font-medium outline-none focus:ring-2 focus:ring-primary/20"
                        wire:model.live="status"
                    >
                        <option value="">All Verification States</option>
                        <option value="successful">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="reversed">Reversed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table list / Empty State -->
        @if($transactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="p-4">Reference / Description</th>
                            <th class="p-4">Gateway</th>
                            <th class="p-4">Category</th>
                            <th class="p-4">Type</th>
                            <th class="p-4">Amount</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $txn)
                            <tr class="border-b border-slate-200 dark:border-slate-700/50">
                                <td class="p-4">
                                    <span class="font-mono text-[12px] font-medium text-slate-700 dark:text-slate-300">{{ $txn->reference }}</span>
                                    @if($txn->description)
                                        <div class="text-[12px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $txn->description }}</div>
                                    @endif
                                    @if($txn->provider_reference)
                                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">Ref: {{ $txn->provider_reference }}</div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @php
                                        // Attempt to resolve gateway from metadata if populated
                                        $gatewayName = $txn->metadata['gateway'] ?? ($txn->category->value === 'funding' ? 'razorpay' : 'system');
                                    @endphp
                                    <x-wallet.gateway-badge :gateway="$gatewayName" />
                                </td>
                                <td class="p-4 text-[13px] capitalize text-slate-700 dark:text-slate-300">{{ $txn->category->value }}</td>
                                <td class="p-4 text-[13px]">
                                    @if($txn->type->value === 'credit')
                                        <span class="text-emerald-600 dark:text-emerald-400 font-medium">Credit</span>
                                    @else
                                        <span class="text-rose-600 dark:text-rose-400 font-medium">Debit</span>
                                    @endif
                                </td>
                                <td class="p-4 text-[13px] font-bold">
                                    <span class="{{ $txn->type->value === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $txn->type->value === 'credit' ? '+' : '-' }}₹{{ number_format($txn->amount, 2) }}
                                    </span>
                                </td>
                                <td class="p-4 text-[13px]">
                                    <x-wallet.transaction-status-badge :status="$txn->status" />
                                </td>
                                <td class="p-4 text-[12px] text-slate-400 dark:text-slate-500">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                    {{ $transactions->links() }}
                </div>
            @endif
        @else
            <div class="flex-1 min-h-[400px] flex flex-col items-center justify-center text-center p-8 bg-white dark:bg-slate-800">
                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-900 rounded-full flex items-center justify-center mb-6 border border-slate-100 dark:border-slate-700">
                    <span class="material-symbols-outlined text-slate-400 dark:text-slate-500 text-[48px]" data-icon="history_toggle_off">history_toggle_off</span>
                </div>
                <h4 class="text-slate-900 dark:text-white font-bold text-[18px] mb-2">No ledger transactions found</h4>
                <p class="text-slate-500 dark:text-slate-400 text-[14px] max-w-sm">No ledger transactions matched the criteria. Try adjusting your search or filters to find specific records.</p>
            </div>
        @endif
    </section>
 
    <!-- Payment Inits Section -->
    <section class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col shadow-sm">
        <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
            <h3 class="text-[16px] font-bold text-slate-900 dark:text-white">Recent Payment Attempts</h3>
            <p class="text-[12px] text-slate-500 dark:text-slate-400">Quick funding access & verification module</p>
        </div>
        <div class="p-5 bg-white dark:bg-slate-800">
            <div class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($paymentAttempts as $attempt)
                    <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 first:pt-0 last:pb-0">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black text-slate-900 dark:text-white">₹{{ number_format($attempt->amount, 2) }}</span>
                                <x-wallet.gateway-badge :gateway="$attempt->gateway" />
                            </div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500 font-mono">Order: {{ $attempt->gateway_order_id ?? $attempt->id }}</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Initialized {{ $attempt->created_at->diffForHumans() }}</div>
                            @if($attempt->status->value === 'failed' && isset($attempt->payload['failure_reason']))
                                <div class="text-[11px] text-red-500 font-medium">Reason: {{ $attempt->payload['failure_reason'] }}</div>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <x-wallet.transaction-status-badge :status="$attempt->status" />
                            @if(in_array($attempt->status->value, ['pending', 'processing', 'failed', 'abandoned']))
                                <button 
                                    type="button" 
                                    class="bg-primary text-white px-3 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wider hover:bg-primary/95 transition-all flex items-center gap-1 active:scale-95"
                                    wire:click="$dispatch('retry-payment', { transactionId: '{{ $attempt->id }}' })"
                                >
                                    <span class="material-symbols-outlined text-[12px]" data-icon="refresh">refresh</span>
                                    <span>Retry</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 dark:text-slate-400 text-[13px]">
                        No gateway checkout instances found.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Nest the Fund Wallet Modal Component -->
    <livewire:wallet.fund-wallet-modal />
</div>
