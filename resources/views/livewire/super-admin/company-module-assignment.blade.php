<div class="p-8 space-y-6 flex-1 overflow-y-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Tenant Module Assignments</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage feature-set availability for system tenants.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('superadmin.modules') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-350 text-xs font-bold rounded-lg transition-colors flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Back to Modules</span>
            </a>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex gap-8">
        <!-- Left Pane: Companies List -->
        <div class="w-1/3 space-y-4">
            <div class="relative w-full">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <span class="material-symbols-outlined text-[20px]">search</span>
                </span>
                <input type="text" wire:model.live="search" class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-medium text-slate-900 dark:text-white outline-none focus:ring-4 focus:ring-primary/10 transition-all" placeholder="Search companies..." />
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden max-h-[600px] overflow-y-auto no-scrollbar">
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($companies as $company)
                        <div 
                            wire:click="selectCompany({{ $company->id }})"
                            class="p-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-850 transition-colors flex items-center justify-between gap-4 {{ $selectedCompanyId === $company->id ? 'bg-primary/5 border-l-4 border-primary dark:bg-primary/10' : '' }}"
                        >
                            <div>
                                <h4 class="font-bold text-slate-800 dark:text-white text-xs">{{ $company->name }}</h4>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $company->primary_email }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wide uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                {{ $company->status }}
                            </span>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 font-bold">No companies found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Pane: Assignment Details & Form -->
        <div class="w-2/3">
            @if($selectedCompany)
                <div class="space-y-6">
                    <!-- Company Profile Info -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">{{ $selectedCompany->name }}</h3>
                        <p class="text-xs text-slate-500">Enable/disable modules or configure expiration dates for the CA or other business tools.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <!-- Currently Assigned Modules -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Assigned Modules</h4>
                            <div class="space-y-3">
                                @forelse($assignedModules as $assignment)
                                    <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-900/50 flex items-center justify-between">
                                        <div>
                                            <h5 class="font-bold text-slate-800 dark:text-white text-xs">{{ $assignment->module->name }}</h5>
                                            <p class="text-[10px] text-slate-400 mt-0.5 font-mono">Status: <span class="text-emerald-600 font-bold uppercase">{{ $assignment->status }}</span></p>
                                            @if($assignment->expires_at)
                                                <p class="text-[9px] text-slate-400 font-mono">Expires: {{ $assignment->expires_at->format('Y-m-d') }}</p>
                                            @endif
                                        </div>
                                        <button 
                                            type="button" 
                                            wire:click="removeModule('{{ $assignment->module->slug }}')"
                                            class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/20 dark:hover:bg-rose-950/50 text-[10px] font-bold rounded-lg uppercase tracking-wide transition-colors"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 font-medium py-4 text-center">No custom modules assigned yet.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Assign New Module Form -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Assign Module</h4>
                            <form wire:submit.prevent="assignModule" class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Select Module</label>
                                    <select wire:model="moduleSlug" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-xs font-semibold outline-none text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all">
                                        <option value="">Choose a module...</option>
                                        @foreach($availableModules as $mod)
                                            <option value="{{ $mod->slug }}">{{ $mod->name }} (v{{ $mod->version }})</option>
                                        @endforeach
                                    </select>
                                    @error('moduleSlug') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Expires At (Optional)</label>
                                    <input type="date" wire:model="expiresAt" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-xs font-semibold outline-none text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all" />
                                    @error('expiresAt') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <button type="submit" class="w-full py-3 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-xl transition-colors shadow-sm uppercase tracking-wider">
                                    Assign to Company
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center text-slate-400 dark:text-slate-500 shadow-sm flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-[48px] text-slate-300 dark:text-slate-700 mb-3">corporate_fare</span>
                    <h4 class="font-bold text-slate-900 dark:text-white">No company selected</h4>
                    <p class="text-xs max-w-xs mt-1 leading-relaxed">Choose a company from the left panel directory to start managing its module assignments.</p>
                </div>
            @endif
        </div>
    </div>
</div>
