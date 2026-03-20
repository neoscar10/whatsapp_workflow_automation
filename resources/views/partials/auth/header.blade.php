<header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-slate-200 dark:border-slate-800 px-6 lg:px-10 py-4">
    <a href="{{ route('home') }}" class="flex items-center gap-3 text-slate-900 dark:text-slate-100">
        <div class="flex items-center justify-center size-10 rounded-lg bg-primary text-white">
            <span class="material-symbols-outlined">chat_bubble</span>
        </div>
        <h2 class="text-slate-900 dark:text-slate-100 text-xl font-bold leading-tight tracking-tight">WhatsApp Cloud Panel</h2>
    </a>

    <div class="flex items-center gap-4">
        @if(request()->routeIs('login'))
            <div class="hidden md:flex gap-4">
                <button class="text-slate-600 dark:text-slate-400 text-sm font-medium hover:text-primary transition-colors">Documentation</button>
                <button class="text-slate-600 dark:text-slate-400 text-sm font-medium hover:text-primary transition-colors">Support</button>
            </div>
        @elseif(request()->routeIs('company.register'))
            <span class="hidden text-sm text-slate-500 dark:text-slate-400 md:block">Already have an account?</span>
            <a class="text-sm font-semibold text-primary transition-colors hover:text-primary/80" href="{{ route('login') }}">Sign In</a>
        @endif
    </div>
</header>
