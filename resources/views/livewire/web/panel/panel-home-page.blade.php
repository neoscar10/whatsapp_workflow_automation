<div class="mx-auto flex min-h-[calc(100vh-170px)] w-full max-w-[1200px] flex-col items-center justify-center p-6 md:p-12">
    <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-10 shadow-xl shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
        <div class="mb-8 flex flex-col items-center gap-4 text-center">
            <div class="flex size-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                <span class="material-symbols-outlined text-4xl">verified_user</span>
            </div>
            <div>
                <h1 class="text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-slate-100">
                    Welcome, {{ $user->name }}!
                </h1>
                <p class="mt-2 text-lg text-slate-500 dark:text-slate-400">
                    Successfully authenticated to <span class="font-bold text-slate-900 dark:text-slate-100">{{ $company->name }}</span>
                </p>
            </div>
        </div>

        <div class="grid gap-6 py-6 border-y border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500">
                    <span class="material-symbols-outlined">business</span>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Company</p>
                    <p class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $company->name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500">
                    <span class="material-symbols-outlined">mail</span>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Email Address</p>
                    <p class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $user->email }}</p>
                </div>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-4">
            <div class="rounded-xl bg-primary/5 p-4 border border-primary/10">
                <p class="text-sm text-primary leading-relaxed text-center">
                    <strong>Dashboard Implementation Pending:</strong> This is a temporary panel placeholder. 
                    The full workspace management and WhatsApp integration dashboard will be implemented in the next phase.
                </p>
            </div>

            <button 
                wire:click="logout"
                class="flex h-12 w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-base font-bold text-slate-700 transition-all hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
            >
                Log Out
            </button>
        </div>
    </div>
</div>
