<aside class="flex w-72 flex-col border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm z-20">
    <div class="flex h-full flex-col gap-8 p-6">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-primary">
                <span class="material-symbols-outlined text-white">cloud_queue</span>
            </div>

            <div class="flex flex-col">
                <h1 class="text-base font-bold leading-none text-slate-900 dark:text-slate-100 uppercase tracking-tight">WA Cloud</h1>
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">SaaS Admin Panel</p>
            </div>
        </div>

        <nav class="flex flex-1 flex-col gap-1.5 overflow-y-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
            <a href="{{ route('dashboard') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'dashboard' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'dashboard' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">dashboard</span>
                <p class="text-sm font-bold">Dashboard</p>
            </a>

            <a href="{{ route('company.profile') }}"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all {{ ($activeNav ?? '') === 'company-profile' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px] {{ ($activeNav ?? '') === 'company-profile' ? 'text-white' : 'text-slate-400 group-hover:text-primary transition-colors' }}">business</span>
                <p class="text-sm font-bold">Company Profile</p>
            </a>

            <a href="javascript:void(0)"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 text-slate-600 transition-all hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800">
                <span class="material-symbols-outlined text-[22px] text-slate-400 group-hover:text-primary transition-colors">group</span>
                <p class="text-sm font-bold">Team Management</p>
            </a>

            <a href="javascript:void(0)"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 text-slate-600 transition-all hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800">
                <span class="material-symbols-outlined text-[22px] text-slate-400 group-hover:text-primary transition-colors">forum</span>
                <p class="text-sm font-bold">Message Logs</p>
            </a>

            <a href="javascript:void(0)"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 text-slate-600 transition-all hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800">
                <span class="material-symbols-outlined text-[22px] text-slate-400 group-hover:text-primary transition-colors">description</span>
                <p class="text-sm font-bold">Templates</p>
            </a>

            <div class="my-6 border-t border-slate-100 dark:border-slate-800"></div>

            <a href="javascript:void(0)"
               class="group flex items-center gap-3 rounded-xl px-4 py-3 text-slate-600 transition-all hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800">
                <span class="material-symbols-outlined text-[22px] text-slate-400 group-hover:text-primary transition-colors">settings</span>
                <p class="text-sm font-bold">Settings</p>
            </a>
        </nav>

        <div class="mt-auto rounded-2xl bg-slate-50 p-5 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
            <p class="mb-3 text-[10px] font-black uppercase tracking-[0.1em] text-slate-400 dark:text-slate-500">
                Storage Usage
            </p>

            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                <div class="h-full bg-primary shadow-[0_0_8px_rgba(36,99,235,0.4)]" style="width: {{ data_get($storage, 'percent', 65) }}%"></div>
            </div>

            <p class="mt-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 flex justify-between items-center">
                <span>{{ data_get($storage, 'label', '6.5GB of 10GB') }}</span>
                <span class="text-primary">{{ data_get($storage, 'percent', 65) }}%</span>
            </p>
        </div>
    </div>
</aside>
