<div class="space-y-6">
    <div class="p-5 rounded-2xl bg-primary/5 border border-primary/20 space-y-4 shadow-xl relative overflow-hidden group">
        <div class="absolute -right-8 -top-8 w-24 h-24 bg-primary/10 rounded-full blur-3xl"></div>
        <div class="flex items-center gap-3 text-primary">
            <span class="material-symbols-outlined text-xl">account_tree</span>
            <h4 class="text-[10px] font-black uppercase tracking-[0.2em]">Parallel Execution</h4>
        </div>
        <p class="text-[10px] font-bold text-slate-500 leading-relaxed uppercase tracking-tight pr-8">
            This node splits the workflow into multiple independent branches that run simultaneously.
        </p>
    </div>

    <div class="space-y-4">
        <div class="space-y-2">
            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Branch Configuration</label>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white/[0.03] border border-white/5 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 group hover:border-primary/30 transition-all cursor-pointer">
                    <span class="text-2xl font-black text-white group-hover:text-primary transition-colors">{{ $nodeConfig['branch_count'] ?? 2 }}</span>
                    <span class="text-[9px] font-black text-slate-600 uppercase tracking-widest">Active Branches</span>
                </div>
                <button type="button" class="bg-white/[0.03] border border-dashed border-white/10 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 hover:bg-primary/5 hover:border-primary/30 group transition-all">
                    <span class="material-symbols-outlined text-slate-500 group-hover:text-primary">add_circle</span>
                    <span class="text-[9px] font-black text-slate-600 group-hover:text-primary uppercase tracking-widest">Add Branch</span>
                </button>
            </div>
        </div>

        <div class="space-y-2 pt-2">
            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Synchronization Mode</label>
            <select wire:model.lazy="nodeConfig.sync_mode" class="w-full bg-[#101d39] border border-white/10 rounded-xl px-4 py-3.5 text-xs text-white focus:ring-1 focus:ring-primary shadow-lg appearance-none">
                <option value="wait_all">Wait for All Branches</option>
                <option value="independent">Continue Independently</option>
                <option value="race">First Success Wins</option>
            </select>
        </div>

        <div class="p-4 rounded-xl bg-white/[0.02] border border-white/5 flex gap-3 items-start">
            <span class="material-symbols-outlined text-amber-500 text-sm">warning</span>
            <p class="text-[9px] font-bold text-slate-600 uppercase tracking-tight leading-relaxed">
                Be careful with recursive loops when using parallel branches. Ensure every branch has a clear termination node.
            </p>
        </div>
    </div>
</div>
