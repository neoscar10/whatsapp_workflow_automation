<div class="space-y-4 pt-4 border-t border-white/5">
    <div class="flex items-center justify-between px-1">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Matched Profile Variables</label>
        <span class="text-[9px] font-black bg-white/5 px-2 py-0.5 rounded text-slate-500 uppercase tracking-tighter">3 Items</span>
    </div>

    <div class="space-y-2">
        @php
            $profileVars = [
                ['key' => 'user.email', 'type' => 'STRING'],
                ['key' => 'user.id', 'type' => 'STRING'],
                ['key' => 'user.tags', 'type' => 'ARRAY'],
            ];
        @endphp
        @foreach($profileVars as $var)
            <div class="flex items-center justify-between p-3.5 bg-white/[0.02] border border-white/5 rounded-2xl group hover:border-primary/20 transition-all">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-sm text-slate-600 group-hover:text-primary transition-colors">person</span>
                    <span class="text-xs font-black text-slate-400 group-hover:text-white transition-colors">{{ $var['key'] }}</span>
                </div>
                <span class="text-[9px] font-black text-slate-600 group-hover:text-slate-500 tracking-tighter">{{ $var['type'] }}</span>
            </div>
        @endforeach
    </div>
    
    <div class="p-4 rounded-2xl bg-primary/5 border border-primary/10 mt-4">
        <div class="flex gap-3">
            <span class="material-symbols-outlined text-primary text-sm shrink-0">info</span>
            <p class="text-[10px] font-bold text-slate-400 leading-relaxed uppercase tracking-tight">
                These variables represent the data from the matched profile that started this flow.
            </p>
        </div>
    </div>
</div>
