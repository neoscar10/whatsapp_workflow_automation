<div class="flex items-center gap-2">
    @if($company)
        @if($canAccessLive)
            @if($isDemo)
                <!-- Demo Mode active (Live Access available) -->
                <div class="flex items-center gap-2 bg-amber-500/10 border border-amber-500/30 rounded-xl p-1 pr-2.5 text-amber-600 dark:text-amber-400 font-bold text-xs shadow-xs">
                    <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-amber-500/20">
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-amber-500"></span>
                        </span>
                        <span class="material-symbols-outlined text-[15px]">science</span>
                        <span>Demo Mode</span>
                    </span>
                    <button 
                        type="button" 
                        wire:click="toggleMode" 
                        class="hover:text-emerald-500 hover:underline flex items-center gap-1 text-[11px] font-bold transition-all ml-1 cursor-pointer"
                        title="Click to switch company to Live Mode"
                    >
                        <span class="material-symbols-outlined text-[14px]">swap_horiz</span>
                        <span>Switch to Live</span>
                    </button>
                </div>
            @else
                <!-- Live Mode active -->
                <div class="flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-1 pr-2.5 text-emerald-600 dark:text-emerald-400 font-bold text-xs shadow-xs">
                    <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-emerald-500/20">
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                        </span>
                        <span class="material-symbols-outlined text-[15px]">verified</span>
                        <span>Live Mode</span>
                    </span>
                    <button 
                        type="button" 
                        wire:click="toggleMode" 
                        class="hover:text-amber-500 hover:underline flex items-center gap-1 text-[11px] font-bold transition-all ml-1 cursor-pointer"
                        title="Click to switch company to Demo Mode"
                    >
                        <span class="material-symbols-outlined text-[14px]">swap_horiz</span>
                        <span>Switch to Demo</span>
                    </button>
                </div>
            @endif
        @else
            <!-- Live Access Disabled by Super Admin -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 font-bold text-xs shadow-xs" title="Account is in Demo Mode (Live Mode access is disabled by admin)">
                <span class="relative flex size-2">
                    <span class="relative inline-flex size-2 rounded-full bg-amber-500"></span>
                </span>
                <span class="material-symbols-outlined text-[16px]">science</span>
                <span>Demo Mode</span>
            </div>
        @endif
    @endif
</div>
