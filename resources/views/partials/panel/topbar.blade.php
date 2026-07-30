<header class="sticky top-0 z-10 flex h-20 shrink-0 items-center justify-between border-b border-slate-200 bg-white/80 backdrop-blur-md px-8 dark:border-slate-800 dark:bg-slate-900/80 w-full">
    <div class="flex items-center gap-4 flex-1">
        <button 
            @click="sidebarOpen = !sidebarOpen" 
            x-show="!sidebarOpen"
            class="flex items-center justify-center size-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-primary/10 hover:text-primary transition-all dark:bg-slate-800"
            title="Open Sidebar"
        >
            <span class="material-symbols-outlined">
                menu
            </span>
        </button>

        <div class="mx-2 h-6 w-px bg-slate-200 dark:bg-slate-800"></div>

        <div class="relative w-full max-w-md group">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-slate-400 group-focus-within:text-primary transition-colors">search</span>
            <input
                type="text"
                placeholder="{{ $topbarSearchPlaceholder ?? 'Search across messages, team, or logs...' }}"
                class="w-full h-11 rounded-xl border-none bg-slate-100 pl-12 pr-4 text-sm font-medium placeholder:text-slate-500 focus:ring-4 focus:ring-primary/10 transition-all dark:bg-slate-800"
            />
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 shrink-0">
        {{-- Desktop Notification Permission Toggle --}}
        <button 
            onclick="window.requestNotificationPermission ? window.requestNotificationPermission() : null" 
            type="button"
            class="flex items-center justify-center size-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-primary/10 hover:text-primary transition-all dark:bg-slate-800"
            title="Enable Desktop Push Notifications"
        >
            <span class="material-symbols-outlined text-[20px]">notifications</span>
        </button>

        @if(auth()->user()?->role !== 'super_admin')
            <livewire:web.panel.topbar-wallet-balance />
        @endif


        <div class="mx-1 h-8 w-px bg-slate-200 dark:bg-slate-800"></div>

        <div class="flex items-center gap-4 pl-3">
            <div class="hidden text-right lg:block">
                <p class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-none">
                    {{ data_get($topbarUser, 'name', auth()->user()?->name ?? 'Alex Johnson') }}
                </p>
                <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">
                    {{ data_get($topbarUser, 'role_label', 'Admin Account') }}
                </p>
            </div>

            <div
                class="size-11 rounded-xl border-2 border-primary/10 shadow-sm bg-primary/20 bg-cover bg-center shrink-0"
                style="background-image: url('{{ data_get($topbarUser, 'avatar_url', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAFipTKFXb18IRIerEL6GHqmAN918JWxMjFwdUV3WciqAb33bEr3MxJcO6uHOy7RpvO907V_SCPMStdzSes7MgvUmOhE5YvTs68W_mcRDXmKISvF0KIgVgBcZwSpPCOoa_ArcE2z0RzlWjZLFpF3n5zmEPTPcj-TLoxPR4uuipZWcsJjgCWcGrdP-D202rHObY54ZNl7DDPypg725MvjPVqjxRBmaLHNBq57ipak77x9aZ7uoYgSPE0wo2Ph8TYa44iITUvivWv1uo') }}')"
                aria-label="User profile avatar"
            ></div>
        </div>
    </div>
</header>
