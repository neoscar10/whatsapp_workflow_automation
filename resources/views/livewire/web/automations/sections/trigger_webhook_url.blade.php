<div class="space-y-3" x-data="{ copied: false }">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Webhook URL</label>
    <div class="relative group">
        <div class="w-full bg-[#0a1630] border border-white/10 rounded-2xl px-4 py-4 pr-12 text-[11px] font-mono text-primary break-all shadow-2xl flex items-center min-h-[56px]">
            {{ $nodeConfig['webhook_url'] ?? 'Generating endpoint...' }}
        </div>
        <button 
            type="button"
            @click="navigator.clipboard.writeText('{{ $nodeConfig['webhook_url'] ?? '' }}'); copied = true; setTimeout(() => copied = false, 2000)"
            class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center bg-white/5 border border-white/10 rounded-xl hover:bg-primary/20 hover:border-primary/50 transition-all text-slate-400 hover:text-primary active:scale-90"
            title="Copy URL"
        >
            <span class="material-symbols-outlined text-lg" x-show="!copied">content_copy</span>
            <span class="material-symbols-outlined text-lg text-green-500" x-show="copied" x-cloak>check_circle</span>
        </button>
    </div>
    <div class="flex items-center gap-2 px-1">
        <span class="w-1 h-1 rounded-full bg-green-500 animate-pulse"></span>
        <span class="text-[9px] font-black text-slate-600 uppercase tracking-widest">Active & Listening</span>
    </div>
</div>
