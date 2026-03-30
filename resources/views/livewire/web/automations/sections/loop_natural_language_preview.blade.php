<div class="p-5 rounded-3xl bg-primary/5 border border-primary/10 relative overflow-hidden group shadow-xl">
    <div class="absolute -right-8 -bottom-8 w-24 h-24 bg-primary/5 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-1000"></div>
    <div class="flex gap-4 relative z-10">
        <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center border border-primary/20 rotate-3 group-hover:rotate-0 transition-transform">
            <span class="material-symbols-outlined text-primary text-2xl">chat_bubble</span>
        </div>
        <div class="space-y-1.5 flex-1">
            <h4 class="text-[9px] font-black tracking-[0.25em] uppercase text-primary/70">Natural Language Preview</h4>
            <p class="text-xs font-bold text-slate-300 leading-relaxed italic pr-4">
                "Loop until <span class="text-white">{{ $nodeConfig['condition']['field'] ?? '...' }}</span> 
                <span class="text-primary">{{ $nodeConfig['condition']['operator'] ?? '...' }}</span> 
                <span class="text-white">"{{ $nodeConfig['condition']['value'] ?? '...' }}"</span>"
            </p>
        </div>
    </div>
</div>
