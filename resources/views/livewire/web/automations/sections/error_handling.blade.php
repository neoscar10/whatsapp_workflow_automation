<div class="pt-4 border-t border-white/5" x-data="{ open: false }">
    <button @click="open = !open" type="button" class="flex w-full items-center justify-between text-slate-400 hover:text-white transition-colors group">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-lg opacity-60 group-hover:opacity-100 transition-opacity">report_problem</span>
            <span class="text-sm font-bold text-slate-300 group-hover:text-white transition-colors">Error Handling</span>
        </div>
        <span class="material-symbols-outlined transition-transform duration-200" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" x-collapse x-cloak class="mt-6 space-y-5">
        <div class="p-4 bg-amber-500/5 rounded-2xl border border-amber-500/10 flex gap-3">
            <span class="material-symbols-outlined text-amber-500 text-lg">info</span>
            <p class="text-[10px] font-bold text-amber-400/80 leading-relaxed uppercase tracking-tight">
                Define what happens if this action fails to deliver or errors.
            </p>
        </div>
        <label class="flex items-center justify-between group cursor-pointer">
            <span class="text-[11px] font-black uppercase tracking-tight text-slate-400 group-hover:text-slate-200 transition-colors">Abort on Failure</span>
            <input type="checkbox" wire:model.lazy="nodeConfig.abort_on_failure" class="h-5 w-5 rounded border-white/10 bg-[#101d39] text-primary focus:ring-primary transition-all" />
        </label>
        <div class="space-y-2">
            <label class="block text-[10px] font-black uppercase tracking-tight text-slate-500">Retry Policy</label>
            <select wire:model.lazy="nodeConfig.retry_policy" class="w-full bg-[#101d39] border border-white/5 rounded-xl px-4 py-2.5 text-xs text-white focus:ring-2 focus:ring-primary appearance-none shadow-lg">
                <option value="none">No Retry</option>
                <option value="immediate">Immediate Retry</option>
                <option value="exponential">Exponential Backoff</option>
            </select>
        </div>
    </div>
</div>
