<div class="p-6 bg-[#0a1630] border border-white/5 rounded-2xl space-y-5 shadow-2xl">
    <div class="flex items-center gap-2.5 text-slate-500">
        <span class="material-symbols-outlined text-lg">tune</span>
        <span class="text-[10px] font-black uppercase tracking-[0.2em]">Advanced Settings</span>
    </div>

    <div class="space-y-4">
        <div class="flex items-center justify-between group cursor-pointer" @click="$refs.weekendToggle.click()">
            <span class="text-xs font-bold text-slate-400 group-hover:text-slate-200 transition-colors">Exclude Weekends</span>
            <div class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none" :class="config.exclude_weekends ? 'bg-primary' : 'bg-slate-800'">
                <input type="checkbox" wire:model.lazy="nodeConfig.exclude_weekends" x-ref="weekendToggle" class="sr-only" />
                <span class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform" :class="config.exclude_weekends ? 'translate-x-5' : 'translate-x-1'"></span>
            </div>
        </div>

        <div class="flex items-center justify-between group cursor-pointer" @click="$refs.tzToggle.click()">
            <span class="text-xs font-bold text-slate-400 group-hover:text-slate-200 transition-colors">Timezone Relative</span>
            <div class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none" :class="config.timezone_relative ? 'bg-primary' : 'bg-slate-800'">
                <input type="checkbox" wire:model.lazy="nodeConfig.timezone_relative" x-ref="tzToggle" class="sr-only" />
                <span class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform" :class="config.timezone_relative ? 'translate-x-5' : 'translate-x-1'"></span>
            </div>
        </div>
    </div>
</div>
