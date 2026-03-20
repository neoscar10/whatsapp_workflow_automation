<div class="mx-auto flex min-h-[calc(100vh-170px)] w-full max-w-[1200px] items-center justify-center p-6 md:p-12">
    <div class="w-full max-w-[480px] rounded-2xl border border-slate-200 bg-white p-8 shadow-2xl shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none md:p-12">
        <div class="mb-10 flex flex-col gap-3 text-center">
            <h1 class="text-4xl font-black leading-tight tracking-tight text-slate-900 dark:text-slate-100">
                Welcome Back
            </h1>
            <p class="text-base font-medium text-slate-500 dark:text-slate-400">
                Log in to manage your WhatsApp instances
            </p>
        </div>

        @if (session()->has('status'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 dark:border-green-900/30 dark:bg-green-900/20 dark:text-green-400">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->has('auth'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900/30 dark:bg-red-900/20 dark:text-red-400">
                {{ $errors->first('auth') }}
            </div>
        @endif

        <form class="space-y-6" wire:submit="login">
            <div class="flex flex-col gap-2">
                <label class="text-sm font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                    Email Address
                </label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-primary">
                        mail
                    </span>
                    <input
                        type="email"
                        wire:model="email"
                        autocomplete="email"
                        placeholder="name@company.com"
                        class="flex h-14 w-full rounded-xl border-2 border-slate-100 bg-slate-50 pl-14 pr-4 text-base font-medium text-slate-900 placeholder:text-slate-400 transition-all focus:border-primary focus:bg-white focus:outline-0 focus:ring-4 focus:ring-primary/10 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white dark:focus:border-primary dark:focus:bg-slate-800"
                    />
                </div>
                @error('email')
                    <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        Password
                    </label>
                    <a href="javascript:void(0)" class="text-xs font-bold text-primary hover:text-primary/80 transition-colors hover:underline">
                        Forgot Password?
                    </a>
                </div>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-primary">
                        lock
                    </span>
                    <input
                        type="{{ $showPassword ? 'text' : 'password' }}"
                        wire:model="password"
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        class="flex h-14 w-full rounded-xl border-2 border-slate-100 bg-slate-50 pl-14 pr-12 text-base font-medium text-slate-900 placeholder:text-slate-400 transition-all focus:border-primary focus:bg-white focus:outline-0 focus:ring-4 focus:ring-primary/10 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white dark:focus:border-primary dark:focus:bg-slate-800"
                    />
                    <button
                        type="button"
                        wire:click="togglePassword"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                    >
                        <span class="material-symbols-outlined text-xl">
                            {{ $showPassword ? 'visibility_off' : 'visibility' }}
                        </span>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <div class="relative flex items-center">
                    <input
                        id="remember"
                        type="checkbox"
                        wire:model="remember"
                        class="size-5 rounded-lg border-2 border-slate-200 text-primary transition-all focus:ring-primary/20 dark:border-slate-700 dark:bg-slate-800"
                    />
                </div>
                <label for="remember" class="cursor-pointer text-sm font-medium text-slate-600 dark:text-slate-400 select-none">
                    Keep me logged in
                </label>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="relative flex h-14 w-full items-center justify-center overflow-hidden rounded-xl bg-primary px-5 text-base font-bold leading-normal tracking-wide text-white shadow-xl shadow-primary/30 transition-all hover:bg-primary/90 hover:scale-[1.01] active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:scale-100"
            >
                <div wire:loading.remove wire:target="login" class="flex items-center gap-2">
                    <span>Sign In to Panel</span>
                    <span class="material-symbols-outlined text-xl">arrow_forward</span>
                </div>
                <div wire:loading wire:target="login" class="flex items-center gap-2">
                    <svg class="h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Authenticating...</span>
                </div>
            </button>
        </form>

        <p class="mt-12 text-center text-sm font-medium text-slate-500 dark:text-slate-400">
            Don't have an account?
            <a href="{{ route('company.register') }}" class="ml-1 font-bold text-primary hover:text-primary/80 transition-colors hover:underline">
                Create an account
            </a>
        </p>
    </div>
</div>
