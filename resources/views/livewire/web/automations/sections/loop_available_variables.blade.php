<div class="space-y-4 pt-4 border-t border-white/5">
    <div class="flex items-center justify-between px-1">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Iteration Variables</label>
        <span class="text-[9px] font-black bg-white/5 px-2 py-0.5 rounded text-slate-500 uppercase tracking-tighter">3 Active</span>
    </div>

    <div class="space-y-2">
        @php
            $alias = $nodeConfig['item_alias_name'] ?? 'item';
            $vars = [
                ['key' => $alias, 'label' => 'Current Item', 'type' => 'OBJECT'],
                ['key' => 'loop_index', 'label' => 'Current Position', 'type' => 'INTEGER'],
                ['key' => 'loop_total', 'label' => 'Total Count', 'type' => 'INTEGER'],
            ];
        @endphp
        @foreach($vars as $var)
            <div class="flex items-center justify-between p-3.5 bg-[#101d39]/40 border border-white/5 rounded-2xl group hover:border-primary/20 transition-all">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-sm text-slate-500 group-hover:text-primary transition-colors">data_object</span>
                    <div class="flex flex-col">
                        <span class="text-xs font-black text-slate-300 group-hover:text-white transition-colors">@{{ {{ $var['key'] }} }}</span>
                        <span class="text-[8px] font-bold text-slate-600 uppercase tracking-tight">{{ $var['label'] }}</span>
                    </div>
                </div>
                <span class="text-[9px] font-black text-slate-600 group-hover:text-slate-500 tracking-tighter">{{ $var['type'] }}</span>
            </div>
        @endforeach
    </div>
</div>
