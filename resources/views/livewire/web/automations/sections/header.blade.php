<div class="px-6 py-5 border-b border-white/10 flex items-center justify-between bg-[#0a1630]">
    <div class="space-y-1">
        <div class="flex items-center gap-2 text-primary">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-80">{{ $activeNode->type }}</span>
            <div class="w-1 h-1 rounded-full bg-slate-700"></div>
            <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">{{ str_replace('_', ' ', $activeNode->subtype) }}</span>
        </div>
        <h3 class="text-sm font-black text-white uppercase tracking-wider">{{ $activeNode->label }}</h3>
    </div>
    <button wire:click="$set('selectedNodeId', null)" class="w-8 h-8 flex items-center justify-center rounded-xl bg-white/5 border border-white/10 text-slate-500 hover:text-white hover:bg-white/10 transition-all">
        <span class="material-symbols-outlined text-lg">close</span>
    </button>
</div>
