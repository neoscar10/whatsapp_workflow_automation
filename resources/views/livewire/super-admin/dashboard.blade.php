<div class="p-8 space-y-8 flex-1 overflow-y-auto">
    <!-- Header Page -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Super Admin Dashboard</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Overall platform usage statistics and metrics.</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Companies Count Card -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Companies</p>
                <div class="flex size-10 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                    <span class="material-symbols-outlined text-[22px]">corporate_fare</span>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['companies_count'] }}</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Total registered clients</p>
            </div>
        </div>

        <!-- Total Users Card -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Users</p>
                <div class="flex size-10 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                    <span class="material-symbols-outlined text-[22px]">group</span>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['users_count'] }}</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Registered accounts</p>
            </div>
        </div>

        <!-- Wallet Transactions Card -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Wallet Transactions</p>
                <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                    <span class="material-symbols-outlined text-[22px]">monetization_on</span>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['wallet_transactions'] }}</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Total system transactions</p>
            </div>
        </div>

        <!-- WhatsApp Connections Card -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">WhatsApp Connections</p>
                <div class="flex size-10 items-center justify-center rounded-xl bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400">
                    <span class="material-symbols-outlined text-[22px]">phone_android</span>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['whatsapp_connections'] }}</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Active phone numbers</p>
            </div>
        </div>

        <!-- Automations Card -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Automations</p>
                <div class="flex size-10 items-center justify-center rounded-xl bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400">
                    <span class="material-symbols-outlined text-[22px]">auto_awesome</span>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['automations'] }}</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Total configured flows</p>
            </div>
        </div>

        <!-- Campaigns Card -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Campaigns</p>
                <div class="flex size-10 items-center justify-center rounded-xl bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400">
                    <span class="material-symbols-outlined text-[22px]">campaign</span>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['campaigns'] }}</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Total run marketing efforts</p>
            </div>
        </div>

        <!-- Messages Sent Card -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Messages Sent</p>
                <div class="flex size-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                    <span class="material-symbols-outlined text-[22px]">outgoing_mail</span>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['messages_sent'] }}</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Outbound platform messages</p>
            </div>
        </div>
    </div>
</div>
