<div class="space-y-4">
    <div class="flex items-center justify-between px-1">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Max Iteration Limit</label>
        <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-500 text-[9px] font-black uppercase tracking-widest border border-amber-500/20">
            Safety Guard
        </span>
    </div>
    <div class="relative group">
        <input 
            type="number" 
            wire:model.lazy="nodeConfig.max_iteration_limit" 
            class="w-full bg-[#101d39] border border-white/10 rounded-2xl pl-4 pr-20 py-4 text-sm font-black text-white focus:ring-2 focus:ring-primary shadow-xl"
            placeholder="100"
        />
        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-[9px] font-black text-slate-600 uppercase tracking-widest group-hover:text-slate-400 transition-colors">
            Cycles
        </div>
    </div>
    <p class="text-[9px] font-bold text-slate-600 uppercase tracking-tight px-1 italic">
        The loop will automatically terminate if it exceeds this threshold.
    </p>
</div>
