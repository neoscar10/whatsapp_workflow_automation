<div class="space-y-6">
    <div class="p-8 rounded-[2.5rem] bg-white/[0.02] border border-dashed border-white/10 flex flex-col items-center justify-center text-center space-y-4 group hover:border-red-500/30 transition-all">
        <div class="w-16 h-16 rounded-full bg-red-500/10 flex items-center justify-center border border-red-500/20 group-hover:bg-red-500/20 transition-all shadow-2xl">
            <span class="material-symbols-outlined text-red-500 text-3xl">stop_circle</span>
        </div>
        <div class="space-y-1">
            <h4 class="text-[11px] font-black text-white uppercase tracking-[0.2em]">Flow Termination</h4>
            <p class="text-[9px] font-bold text-slate-600 uppercase tracking-tighter leading-relaxed">This node marks the final point of the automation run.</p>
        </div>
    </div>

    <div class="space-y-4 pt-2">
        <div class="space-y-2">
            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">End State</label>
            <div class="grid grid-cols-2 gap-3">
                <button 
                    type="button" 
                    wire:click="$set('nodeConfig.end_state', 'success')"
                    class="p-4 rounded-2xl border transition-all flex flex-col items-center gap-2 group {{ ($nodeConfig['end_state'] ?? 'success') === 'success' ? 'bg-emerald-500/10 border-emerald-500/50' : 'bg-white/[0.03] border-white/5 hover:border-white/20' }}"
                >
                    <span class="material-symbols-outlined text-xl {{ ($nodeConfig['end_state'] ?? 'success') === 'success' ? 'text-emerald-500' : 'text-slate-500 group-hover:text-slate-300' }}">check_circle</span>
                    <span class="text-[9px] font-black uppercase tracking-widest {{ ($nodeConfig['end_state'] ?? 'success') === 'success' ? 'text-white' : 'text-slate-600 group-hover:text-slate-400' }}">Success</span>
                </button>
                <button 
                    type="button" 
                    wire:click="$set('nodeConfig.end_state', 'failed')"
                    class="p-4 rounded-2xl border transition-all flex flex-col items-center gap-2 group {{ ($nodeConfig['end_state'] ?? '') === 'failed' ? 'bg-red-500/10 border-red-500/50' : 'bg-white/[0.03] border-white/5 hover:border-white/20' }}"
                >
                    <span class="material-symbols-outlined text-xl {{ ($nodeConfig['end_state'] ?? '') === 'failed' ? 'text-red-500' : 'text-slate-500 group-hover:text-slate-300' }}">error</span>
                    <span class="text-[9px] font-black uppercase tracking-widest {{ ($nodeConfig['end_state'] ?? '') === 'failed' ? 'text-white' : 'text-slate-600 group-hover:text-slate-400' }}">Failed</span>
                </button>
            </div>
        </div>

        <div class="space-y-3 pt-2">
            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Final Note (Optional)</label>
            <textarea 
                wire:model.lazy="nodeConfig.summary"
                class="w-full h-24 bg-[#101d39] border border-white/10 rounded-2xl p-4 text-[11px] font-bold text-slate-300 focus:ring-1 focus:ring-primary shadow-lg resize-none"
                placeholder="Workflow execution completed after..."
            ></textarea>
        </div>
    </div>
</div>
