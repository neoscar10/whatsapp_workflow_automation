<div class="flex items-center justify-center p-6 md:p-12">
    <div class="grid w-full max-w-6xl items-center gap-12 md:grid-cols-2">
        @include('partials.auth.register-marketing-panel')

        <div class="flex justify-center">
            <div class="w-full max-w-[480px] rounded-xl border border-primary/10 bg-white p-8 shadow-xl dark:bg-slate-900">
                <div class="mb-8">
                    <h2 class="leading-tight text-3xl font-black text-slate-900 dark:text-white">Create Company</h2>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Get started with your 14-day free trial.</p>
                </div>

                @if (session()->has('success'))
                    <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                <form class="space-y-6" wire:submit="register">
                    <div class="space-y-2">
                        <label class="ml-1 text-sm font-semibold text-slate-700 dark:text-slate-300">Company Name</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-lg text-slate-400">business</span>
                            <input
                                type="text"
                                wire:model="company_name"
                                placeholder="Acme Corp"
                                class="w-full rounded-xl border border-primary/10 bg-background-light py-3.5 pl-11 pr-4 outline-none transition-all focus:border-transparent focus:ring-2 focus:ring-primary dark:bg-background-dark text-slate-900 dark:text-slate-100 placeholder-slate-400"
                            />
                        </div>
                        @error('company_name')
                            <p class="ml-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="ml-1 text-sm font-semibold text-slate-700 dark:text-slate-300">Email Address</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-lg text-slate-400">mail</span>
                            <input
                                type="email"
                                wire:model="email"
                                placeholder="name@company.com"
                                class="w-full rounded-xl border border-primary/10 bg-background-light py-3.5 pl-11 pr-4 outline-none transition-all focus:border-transparent focus:ring-2 focus:ring-primary dark:bg-background-dark text-slate-900 dark:text-slate-100 placeholder-slate-400"
                            />
                        </div>
                        @error('email')
                            <p class="ml-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="ml-1 text-sm font-semibold text-slate-700 dark:text-slate-300">Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-lg text-slate-400">lock</span>
                            <input
                                type="password"
                                wire:model="password"
                                placeholder="Min. 8 characters"
                                class="w-full rounded-xl border border-primary/10 bg-background-light py-3.5 pl-11 pr-4 outline-none transition-all focus:border-transparent focus:ring-2 focus:ring-primary dark:bg-background-dark text-slate-900 dark:text-slate-100 placeholder-slate-400"
                            />
                        </div>
                        @error('password')
                            <p class="ml-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-start gap-3 py-2">
                        <input
                            id="terms"
                            type="checkbox"
                            wire:model="agree_to_terms"
                            class="mt-1 rounded border-primary/20 text-primary focus:ring-primary"
                        />
                        <label for="terms" class="text-xs leading-normal text-slate-500">
                            By creating an account, you agree to our
                            <a href="javascript:void(0)" class="font-medium text-primary hover:underline">Terms of Service</a>
                            and
                            <a href="javascript:void(0)" class="font-medium text-primary hover:underline">Privacy Policy</a>.
                        </label>
                    </div>
                    @error('agree_to_terms')
                        <p class="-mt-3 ml-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary py-4 font-bold text-white shadow-lg shadow-primary/25 transition-all active:scale-[0.98] hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-70"
                    >
                        <span wire:loading.remove wire:target="register">Create Company</span>
                        <span wire:loading wire:target="register">Creating Company...</span>
                        <span class="material-symbols-outlined" wire:loading.remove wire:target="register">arrow_forward</span>
                    </button>

                    @include('partials.auth.social-register-buttons')
                </form>
            </div>
        </div>
    </div>
</div>
