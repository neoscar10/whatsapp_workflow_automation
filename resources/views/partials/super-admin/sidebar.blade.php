<aside 
    class="relative h-full flex flex-col border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm z-20 no-scrollbar transition-all duration-300 ease-in-out shrink-0 overflow-hidden"
    :class="sidebarOpen ? 'w-72 opacity-100 translate-x-0' : 'w-0 opacity-0 -translate-x-full border-none'"
>
    <div class="flex h-full w-72 flex-col gap-8 p-6 no-scrollbar">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary">
                    <span class="material-symbols-outlined text-white">admin_panel_settings</span>
                </div>

                <div class="flex flex-col">
                    <h1 class="text-base font-bold leading-none text-slate-900 dark:text-slate-100 uppercase tracking-tight">Platform Admin</h1>
                    <p class="text-[10px] font-bold text-primary dark:text-primary uppercase tracking-widest mt-1">Super User Layer</p>
                </div>
            </div>

            <button 
                @click="sidebarOpen = false" 
                class="flex items-center justify-center size-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all dark:bg-slate-800 dark:hover:bg-slate-700 lg:hidden xl:flex"
                title="Collapse Sidebar"
            >
                <span class="material-symbols-outlined text-[20px]">
                    menu_open
                </span>
            </button>
        </div>

        <nav class="flex flex-1 flex-col gap-1.5 overflow-y-auto no-scrollbar">
            <a href="{{ route('superadmin.dashboard') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'dashboard' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'dashboard' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">dashboard</span>
                <p class="text-sm font-bold">Dashboard</p>
            </a>

            <a href="{{ route('superadmin.companies') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'companies' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'companies' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">corporate_fare</span>
                <p class="text-sm font-bold">Companies</p>
            </a>

            <a href="{{ route('superadmin.wallets') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'wallet-monitoring' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'wallet-monitoring' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">monetization_on</span>
                <p class="text-sm font-bold">Wallet Monitoring</p>
            </a>

            <a href="{{ route('superadmin.funding') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'funding-config' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'funding-config' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">price_change</span>
                <p class="text-sm font-bold">Funding Config</p>
            </a>

            <a href="{{ route('superadmin.whatsapp-setup') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'demo-whatsapp-setup' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'demo-whatsapp-setup' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">settings_suggest</span>
                <p class="text-sm font-bold">Demo WhatsApp Setup</p>
            </a>

            <a href="{{ route('superadmin.verification-templates') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'verification-templates' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'verification-templates' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">verified</span>
                <p class="text-sm font-bold">Verification Config</p>
            </a>

            <a href="{{ route('superadmin.verification-queue') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'verification-queue' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'verification-queue' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">checklist_rtl</span>
                <p class="text-sm font-bold">Verification Queue</p>
            </a>

            <a href="#"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'users' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'users' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">group</span>
                <p class="text-sm font-bold">Users</p>
            </a>

            <a href="{{ route('superadmin.modules') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'modules' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'modules' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">view_module</span>
                <p class="text-sm font-bold">Modules</p>
            </a>

            <a href="#"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'deployments' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'deployments' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">cloud_sync</span>
                <p class="text-sm font-bold">Deployments</p>
            </a>

            <a href="#"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'subscriptions' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'subscriptions' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">credit_card</span>
                <p class="text-sm font-bold">Subscriptions</p>
            </a>

            <a href="#"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'system-health' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'system-health' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">monitor_heart</span>
                <p class="text-sm font-bold">System Health</p>
            </a>

            <div class="my-3 border-t border-slate-100 dark:border-slate-800"></div>

            <a href="#"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'settings' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'settings' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">settings</span>
                <p class="text-sm font-bold">Platform Settings</p>
            </a>
        </nav>

        {{-- Logout --}}
        <div class="border-t border-slate-100 dark:border-slate-800 pt-4">
            <div class="flex items-center gap-3 px-4 py-2 mb-2">
                <div class="flex size-8 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                    <span class="material-symbols-outlined text-[18px] text-slate-500 dark:text-slate-400">shield_person</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate">{{ auth()->user()?->name ?? 'Admin' }}</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate">{{ auth()->user()?->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="group flex w-full items-center gap-3 rounded-xl px-4 py-3 text-slate-500 hover:bg-red-50 hover:text-red-600 dark:text-slate-400 dark:hover:bg-red-950/30 dark:hover:text-red-400 transition-all">
                    <span class="material-symbols-outlined text-[22px] text-slate-400 group-hover:text-red-500 transition-colors">logout</span>
                    <p class="text-sm font-bold">Log Out</p>
                </button>
            </form>
        </div>
    </div>
</aside>
