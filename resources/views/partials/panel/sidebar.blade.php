<aside 
    class="relative h-full flex flex-col border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm z-20 no-scrollbar transition-all duration-300 ease-in-out shrink-0 overflow-hidden"
    :class="sidebarOpen ? 'w-72 opacity-100 translate-x-0' : 'w-0 opacity-0 -translate-x-full border-none'"
>
    <div class="flex h-full w-72 flex-col gap-8 p-6 no-scrollbar">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary">
                    <span class="material-symbols-outlined text-white">cloud_queue</span>
                </div>

                <div class="flex flex-col">
                    <h1 class="text-base font-bold leading-none text-slate-900 dark:text-slate-100 uppercase tracking-tight">WA Cloud</h1>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">SaaS Admin Panel</p>
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
            @php
                $sidebarUser = Auth::user();
                $sidebarIsLowBalance = false;
                $sidebarNeedsVerification = false;
                if ($sidebarUser) {
                    $sidebarWallet = \App\Models\Wallet::where('user_id', $sidebarUser->id)->first();
                    $sidebarCompany = $sidebarUser->company;
                    $sidebarIsDemo = $sidebarCompany && $sidebarCompany->status === 'demo';
                    $sidebarDemoCredits = $sidebarIsDemo ? $sidebarCompany->demo_credits : 0.00;
                    
                    $sidebarThreshold = (float) \App\Models\SystemSetting::get('wallet_threshold', 100.00);
                    $sidebarCurrentBalance = $sidebarIsDemo 
                        ? (float)$sidebarDemoCredits 
                        : ($sidebarWallet ? (float)$sidebarWallet->balance : 0.00);
                    $sidebarIsLowBalance = $sidebarCurrentBalance < $sidebarThreshold;

                    if ($sidebarCompany) {
                        $sidebarNeedsVerification = !$sidebarCompany->isVerified();
                    }
                }
            @endphp

            <a href="{{ route('dashboard') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'dashboard' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'dashboard' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">dashboard</span>
                <p class="text-sm font-bold">Dashboard</p>
            </a>

            <a href="{{ route('chats.index') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'chats' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'chats' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">chat</span>
                <p class="text-sm font-bold">Chats</p>
            </a>

            <a href="{{ route('contacts.index') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'contacts' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'contacts' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">group</span>
                <p class="text-sm font-bold">Contacts</p>
            </a>

            <a href="{{ route('contacts.audiences') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'contacts.audiences' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'contacts.audiences' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">groups_2</span>
                <p class="text-sm font-bold">Audience Manager</p>
            </a>

            <a href="{{ route('campaigns.index') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'campaigns' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'campaigns' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">campaign</span>
                <p class="text-sm font-bold">Campaigns</p>
            </a>

            <a href="{{ route('company.profile') }}"
               class="group flex items-center justify-between rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'company-profile' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'company-profile' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">business</span>
                    <p class="text-sm font-bold">Company Profile</p>
                </div>
                @if($sidebarNeedsVerification)
                    <span class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                    </span>
                @endif
            </a>
            <a href="{{ route('whatsapp.templates.index') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 xl:transition-all {{ ($activeNav ?? '') === 'whatsapp-templates' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'whatsapp-templates' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">description</span>
                <p class="text-sm font-bold">Templates</p>
            </a>

            <a href="{{ route('automations.index') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ request()->routeIs('automations.*') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('automations.*') ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">auto_awesome</span>
                <p class="text-sm font-bold">Automations</p>
            </a>

            <a href="{{ route('whatsapp.setup.phone-numbers') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'whatsapp-setup' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'whatsapp-setup' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">settings_suggest</span>
                <p class="text-sm font-bold">WhatsApp Setup</p>
            </a>

            <a href="{{ route('wallet.index') }}"
               class="group flex items-center justify-between rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'wallet' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'wallet' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">account_balance_wallet</span>
                    <p class="text-sm font-bold">My Wallet</p>
                </div>
                @if($sidebarIsLowBalance)
                    <span class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                    </span>
                @endif
            </a>

            <div class="my-3 border-t border-slate-100 dark:border-slate-800"></div>

            <a href="javascript:void(0)"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 text-slate-400 cursor-not-allowed transition-all">
                <span class="material-symbols-outlined text-[22px]">group</span>
                <p class="text-sm font-bold">Team Management</p>
            </a>

            <a href="javascript:void(0)"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 text-slate-400 cursor-not-allowed transition-all">
                <span class="material-symbols-outlined text-[22px]">forum</span>
                <p class="text-sm font-bold">Message Logs</p>
            </a>

            

            <div class="my-6 border-t border-slate-100 dark:border-slate-800"></div>

            <a href="javascript:void(0)"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 text-slate-400 cursor-not-allowed transition-all">
                <span class="material-symbols-outlined text-[22px]">settings</span>
                <p class="text-sm font-bold">Settings</p>
            </a>
        </nav>

        {{-- Logout --}}
        <div class="border-t border-slate-100 dark:border-slate-800 pt-4">
            <div class="flex items-center gap-3 px-4 py-2 mb-2">
                <div class="flex size-8 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                    <span class="material-symbols-outlined text-[18px] text-slate-500 dark:text-slate-400">person</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate">{{ auth()->user()?->name ?? 'User' }}</p>
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
