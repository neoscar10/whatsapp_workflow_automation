<div class="space-y-4">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Time Delay Input</label>
    <div class="flex gap-3">
        <div class="relative flex-[1.5]">
            <input 
                type="number" 
                wire:model.lazy="nodeConfig.delay_value"
                class="w-full bg-[#101d39] border border-white/10 rounded-2xl px-4 py-3.5 text-sm font-black text-white focus:ring-2 focus:ring-primary shadow-xl appearance-none"
                placeholder="30"
            />
        </div>
        <div class="relative flex-1">
            <select wire:model.lazy="nodeConfig.delay_unit" class="w-full bg-[#101d39] border border-white/10 rounded-2xl px-4 py-3.5 text-xs font-bold text-white focus:ring-2 focus:ring-primary shadow-xl appearance-none">
                <option value="minutes">Minutes</option>
                <option value="hours">Hours</option>
                <option value="days">Days</option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-3.5 text-slate-500 pointer-events-none text-lg">expand_more</span>
        </div>
    </div>
    <p class="text-[10px] font-bold text-slate-600 leading-relaxed uppercase tracking-tight">
        The automation will pause for this duration before continuing to the next node.
    </p>
</div>
