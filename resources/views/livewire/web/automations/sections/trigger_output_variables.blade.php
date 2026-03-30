<div class="space-y-4 pt-4">
    <div class="flex items-center justify-between">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Available Output Variables</label>
        @php
            $vars = $nodeConfig['output_variables'] ?? [];
        @endphp
        <span class="text-[9px] font-black bg-white/5 px-2 py-0.5 rounded text-slate-500">{{ count($vars) }} ITEMS</span>
    </div>

    <div class="space-y-2">
        @foreach($vars as $var)
            <div class="flex items-center justify-between p-3.5 bg-[#101d39]/40 border border-white/5 rounded-2xl group hover:border-primary/20 transition-all">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-sm text-slate-500 group-hover:text-primary transition-colors">settings_ethernet</span>
                    <span class="text-xs font-black text-slate-300 group-hover:text-white transition-colors">{{ $var['key'] }}</span>
                </div>
                <span class="text-[9px] font-black text-slate-600 group-hover:text-slate-400 tracking-tighter">{{ $var['type'] }}</span>
            </div>
        @endforeach
    </div>
</div>
