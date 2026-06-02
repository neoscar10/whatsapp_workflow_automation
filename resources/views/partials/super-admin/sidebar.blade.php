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

            <a href="#"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'users' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'users' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">group</span>
                <p class="text-sm font-bold">Users</p>
            </a>

            <a href="#"
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
    </div>
</aside>
