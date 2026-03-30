<div class="space-y-6">
    <div class="space-y-3">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Recipient</label>
        <input 
            type="email" 
            wire:model.lazy="nodeConfig.recipient" 
            class="w-full bg-[#101d39] border border-white/10 rounded-xl px-4 py-3.5 text-xs text-white focus:ring-1 focus:ring-primary shadow-lg"
            placeholder="customer@example.com"
        />
    </div>

    <div class="space-y-3">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Subject</label>
        <input 
            type="text" 
            wire:model.lazy="nodeConfig.subject" 
            class="w-full bg-[#101d39] border border-white/10 rounded-xl px-4 py-3.5 text-xs text-white focus:ring-1 focus:ring-primary shadow-lg"
            placeholder="Important Update Regarding..."
        />
    </div>

    <div class="space-y-3">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Body (HTML/Text)</label>
        <textarea 
            wire:model.lazy="nodeConfig.body"
            class="w-full h-40 bg-[#101d39] border border-white/10 rounded-2xl p-4 text-[11px] font-bold text-slate-300 focus:ring-1 focus:ring-primary shadow-lg resize-none"
            placeholder="Hello @{{ name }}, \n\nWe wanted to..."
        ></textarea>
        <div class="flex justify-end px-1">
             <button type="button" class="text-[9px] font-black text-slate-500 hover:text-primary transition-colors uppercase tracking-widest flex items-center gap-2">
                 <span class="material-symbols-outlined text-sm">variable_insert</span>
                 Insert Variable
             </button>
        </div>
    </div>
</div>
