<div class="space-y-4">
    <label class="block text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">Outcome Settings</label>
    <div class="space-y-3">
        <div class="flex items-center justify-between p-4 bg-[#101d39]/40 border border-white/5 rounded-2xl shadow-xl group hover:border-white/10 transition-all cursor-pointer" @click="$refs.waitToggle.click()">
            <span class="text-xs font-bold text-slate-300 group-hover:text-white transition-colors">Wait for all conditions</span>
            <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" :class="config.wait_for_all_conditions ? 'bg-primary' : 'bg-slate-700'">
                <input type="checkbox" wire:model.lazy="nodeConfig.wait_for_all_conditions" x-ref="waitToggle" class="sr-only" />
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="config.wait_for_all_conditions ? 'translate-x-6' : 'translate-x-1'"></span>
            </div>
        </div>

        <div class="flex items-center justify-between p-4 bg-[#101d39]/40 border border-white/5 rounded-2xl shadow-xl group hover:border-white/10 transition-all cursor-pointer" @click="$refs.caseToggle.click()">
            <span class="text-xs font-bold text-slate-300 group-hover:text-white transition-colors">Case sensitive</span>
            <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" :class="config.case_sensitive ? 'bg-primary' : 'bg-slate-700'">
                <input type="checkbox" wire:model.lazy="nodeConfig.case_sensitive" x-ref="caseToggle" class="sr-only" />
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="config.case_sensitive ? 'translate-x-6' : 'translate-x-1'"></span>
            </div>
        </div>
    </div>
</div>
