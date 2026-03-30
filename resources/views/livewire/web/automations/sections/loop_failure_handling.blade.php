<div class="space-y-4">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Failure Handling</label>
    <div class="relative">
        <select wire:model.lazy="nodeConfig.failure_handling" class="w-full bg-[#101d39] border border-white/10 rounded-2xl px-4 py-3.5 text-xs font-bold text-white focus:ring-2 focus:ring-primary shadow-xl appearance-none group hover:border-white/10 transition-all">
            <option value="stop_on_error">Stop loop and report error</option>
            <option value="continue_on_error">Continue loop on error</option>
            <option value="retry_current">Retry current step (up to 3 times)</option>
        </select>
        <span class="material-symbols-outlined absolute right-3.5 top-3.5 text-slate-500 pointer-events-none">expand_more</span>
    </div>
</div>
