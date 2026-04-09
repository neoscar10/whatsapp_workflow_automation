<header class="sticky top-0 z-10 flex h-20 shrink-0 items-center justify-between border-b border-slate-200 bg-white/80 backdrop-blur-md px-8 dark:border-slate-800 dark:bg-slate-900/80">
    <div class="flex items-center gap-4">
        <button 
            @click="sidebarOpen = !sidebarOpen" 
            class="flex items-center justify-center size-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-primary/10 hover:text-primary transition-all dark:bg-slate-800"
            title="Toggle Sidebar"
        >
            <span class="material-symbols-outlined transition-transform duration-300" :class="!sidebarOpen ? '' : 'rotate-180'">
                menu_open
            </span>
        </button>

        <div class="mx-2 h-6 w-px bg-slate-200 dark:bg-slate-800"></div>

        <div class="flex flex-1 items-center gap-4">
        <div class="relative w-full max-w-md group">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-slate-400 group-focus-within:text-primary transition-colors">search</span>
            <input
                type="text"
                placeholder="{{ $topbarSearchPlaceholder ?? 'Search across messages, team, or logs...' }}"
                class="w-full h-11 rounded-xl border-none bg-slate-100 pl-12 pr-4 text-sm font-medium placeholder:text-slate-500 focus:ring-4 focus:ring-primary/10 transition-all dark:bg-slate-800"
            />
        </div>
    </div>

    <div class="flex items-center gap-3">
        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl mr-2">
            <button type="button" class="relative rounded-lg p-2 text-slate-500 hover:bg-white hover:shadow-sm dark:text-slate-400 dark:hover:bg-slate-700 transition-all">
                <span class="material-symbols-outlined text-[22px]">notifications</span>
                <span class="absolute right-2.5 top-2.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-slate-900"></span>
            </button>

            <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-white hover:shadow-sm dark:text-slate-400 dark:hover:bg-slate-700 transition-all">
                <span class="material-symbols-outlined text-[22px]">help_outline</span>
            </button>
        </div>

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
