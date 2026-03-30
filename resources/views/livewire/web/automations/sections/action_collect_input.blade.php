<div class="space-y-6">
    <div class="space-y-3">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Prompt / Question</label>
        <textarea 
            wire:model.lazy="nodeConfig.prompt"
            class="w-full h-28 bg-[#101d39] border border-white/10 rounded-2xl p-4 text-[11px] font-bold text-slate-300 focus:ring-1 focus:ring-primary shadow-lg resize-none"
            placeholder="e.g., What is your email address?"
        ></textarea>
    </div>

    <div class="space-y-3">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Response Type</label>
        <select wire:model.lazy="nodeConfig.response_type" class="w-full bg-[#101d39] border border-white/10 rounded-xl px-4 py-3.5 text-xs text-white focus:ring-1 focus:ring-primary shadow-lg appearance-none">
            <option value="text">Free Text</option>
            <option value="number">Number</option>
            <option value="email">Email Address</option>
            <option value="date">Date</option>
            <option value="url">URL</option>
            <option value="choice">Multiple Choice</option>
        </select>
    </div>

    <div class="bg-primary/5 border border-primary/20 rounded-2xl p-4 flex items-center justify-between group cursor-default shadow-xl">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-xl">variable_insert</span>
            <div class="space-y-0.5">
                <p class="text-[9px] font-black text-primary uppercase tracking-widest">Store Response In</p>
                <p class="text-[11px] font-mono font-bold text-white tracking-tight">@{{ {{ $nodeConfig['variable_key'] ?? 'collected_input' }} }}</p>
            </div>
        </div>
        <button type="button" class="material-symbols-outlined text-slate-500 hover:text-white transition-colors">edit</button>
    </div>

    <div class="space-y-3 pt-2">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Validation Rules</label>
        <div class="space-y-2">
            <label class="flex items-center gap-3 p-3.5 rounded-xl bg-white/[0.02] border border-white/5 cursor-pointer hover:bg-white/5 transition-colors">
                <input type="checkbox" wire:model.lazy="nodeConfig.is_required" class="w-4 h-4 rounded border-white/20 bg-transparent text-primary focus:ring-primary" checked />
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Required Field</span>
            </label>
            <label class="flex items-center gap-3 p-3.5 rounded-xl bg-white/[0.02] border border-white/5 cursor-pointer hover:bg-white/5 transition-colors">
                <input type="checkbox" wire:model.lazy="nodeConfig.allow_retries" class="w-4 h-4 rounded border-white/20 bg-transparent text-primary focus:ring-primary" checked />
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Allow 2 Retries</span>
            </label>
        </div>
    </div>
</div>
