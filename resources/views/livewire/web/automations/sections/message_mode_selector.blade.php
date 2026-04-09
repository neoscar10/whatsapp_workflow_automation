<div class="space-y-3">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Message Type</label>
    <div class="grid grid-cols-2 gap-2 bg-[#101d39] p-1 rounded-xl border border-white/5">
        <button 
            type="button" 
            wire:click="$set('nodeConfig.message_mode', 'text')"
            class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-black uppercase tracking-wider transition-all {{ ($nodeConfig['message_mode'] ?? 'text') === 'text' ? 'bg-primary text-white shadow-lg' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }}"
        >
            <span class="material-symbols-outlined text-sm">subject</span>
            Plain Text
        </button>
        <button 
            type="button" 
            wire:click="$set('nodeConfig.message_mode', 'template')"
            class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-black uppercase tracking-wider transition-all {{ ($nodeConfig['message_mode'] ?? 'text') === 'template' ? 'bg-primary text-white shadow-lg' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }}"
        >
            <span class="material-symbols-outlined text-sm">dashboard_customize</span>
            Template
        </button>
    </div>
</div>
