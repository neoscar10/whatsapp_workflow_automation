<div class="p-4 bg-[#101d39]/40 border border-white/5 rounded-2xl shadow-xl group hover:border-white/10 transition-all cursor-pointer" @click="$refs.waitCondToggle.click()">
    <div class="flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-xs font-bold text-slate-300 group-hover:text-white transition-colors">Condition-based wait</span>
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight mt-0.5">Wait until message received</span>
        </div>
        <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" :class="config.condition_based_wait ? 'bg-primary' : 'bg-slate-700'">
            <input type="checkbox" wire:model.lazy="nodeConfig.condition_based_wait" x-ref="waitCondToggle" class="sr-only" />
            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="config.condition_based_wait ? 'translate-x-6' : 'translate-x-1'"></span>
        </div>
    </div>
</div>
