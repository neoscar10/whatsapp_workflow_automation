<div class="space-y-4">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Loop Type</label>
    <div class="grid grid-cols-3 gap-2 p-1.5 bg-[#101d39]/60 border border-white/5 rounded-2xl shadow-inner">
        @php
            $types = [
                ['id' => 'fixed', 'icon' => 'repeat', 'label' => 'Fixed'],
                ['id' => 'condition_based', 'icon' => 'rule', 'label' => 'Conditional'],
                ['id' => 'iterate_over_data', 'icon' => 'data_array', 'label' => 'Data'],
            ];
        @endphp
        @foreach($types as $type)
            <button 
                type="button"
                wire:click="$set('nodeConfig.loop_type', '{{ $type['id'] }}')"
                class="flex flex-col items-center justify-center py-4 rounded-xl transition-all group gap-2"
                :class="config.loop_type === '{{ $type['id'] }}' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-500 hover:text-slate-300'"
            >
                <span class="material-symbols-outlined text-lg" :class="config.loop_type === '{{ $type['id'] }}' ? 'text-white' : 'text-slate-500 group-hover:text-slate-400'">{{ $type['icon'] }}</span>
                <span class="text-[9px] font-black uppercase tracking-widest text-center">{{ $type['label'] }}</span>
            </button>
        @endforeach
    </div>
</div>
