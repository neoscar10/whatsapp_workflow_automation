<div class="p-8 space-y-6 flex-1 overflow-y-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Modules Management</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Platform-wide system and application modules registry.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('superadmin.company-modules') }}" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-sm">corporate_fare</span>
                <span>Manage Tenant Assignments</span>
            </a>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center bg-slate-50/50 dark:bg-slate-900/20 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
        <div class="relative w-full sm:w-80">
            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                <span class="material-symbols-outlined text-[20px]">search</span>
            </span>
            <input type="text" wire:model.live="search" class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-medium text-slate-900 dark:text-white outline-none focus:ring-4 focus:ring-primary/10 transition-all" placeholder="Search modules..." />
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="p-4">Module Name</th>
                        <th class="p-4">Slug</th>
                        <th class="p-4">Version</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium">
                    @forelse($modules as $module)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold uppercase">
                                        {{ substr($module->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="text-slate-900 dark:text-slate-100 font-semibold">{{ $module->name }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $module->description }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-slate-600 dark:text-slate-400 font-mono text-xs">
                                {{ $module->slug }}
                            </td>
                            <td class="p-4 text-slate-600 dark:text-slate-400">
                                {{ $module->version }}
                            </td>
                            <td class="p-4">
                                @if($module->is_core)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                                        Core Platform
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300">
                                        App Extension
                                    </span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($module->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        Enabled
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                        Disabled
                                    </span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($module->is_core)
                                    <span class="text-xs text-slate-400 italic">Locked</span>
                                @else
                                    <button 
                                        type="button" 
                                        wire:click="toggleStatus('{{ $module->slug }}')" 
                                        class="px-3 py-1.5 {{ $module->is_active ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }} text-[10px] font-bold rounded-lg uppercase tracking-wide"
                                    >
                                        {{ $module->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 dark:text-slate-500 font-bold">
                                No modules found in the system.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
