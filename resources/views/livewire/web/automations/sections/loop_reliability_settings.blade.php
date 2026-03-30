<div class="space-y-4">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Reliability & Retries</label>
    <div class="relative group">
        <div class="absolute inset-y-0 left-4 flex items-center text-slate-500">
            <span class="material-symbols-outlined text-lg">history</span>
        </div>
        <input 
            type="number" 
            wire:model.lazy="nodeConfig.retry_per_iteration" 
            class="w-full bg-[#101d39] border border-white/10 rounded-2xl pl-12 pr-4 py-3.5 text-xs font-black text-slate-300 focus:ring-2 focus:ring-primary shadow-xl"
            placeholder="3"
        />
        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-[9px] font-black text-slate-600 uppercase tracking-widest">Retries / Item</div>
    </div>
    <div class="flex p-4 rounded-2xl bg-white/[0.03] border border-white/5 gap-3">
        <span class="material-symbols-outlined text-slate-500 text-sm shrink-0">verified_user</span>
        <p class="text-[9px] font-bold text-slate-600 leading-relaxed uppercase tracking-tight">
            Failed iterations will be retried up to {{ $nodeConfig['retry_per_iteration'] ?? 3 }} times before triggering the failure handling policy.
        </p>
    </div>
</div>
