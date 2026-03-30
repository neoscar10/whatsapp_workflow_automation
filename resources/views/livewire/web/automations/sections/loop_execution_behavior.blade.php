<div class="space-y-4">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Execution Behavior</label>
    <div class="flex p-1 bg-[#101d39]/60 border border-white/5 rounded-2xl shadow-inner">
        @foreach(['sequential' => ['icon' => 'reorder', 'label' => 'Sequential'], 'parallel' => ['icon' => 'grid_view', 'label' => 'Parallel']] as $id => $info)
            <button 
                type="button"
                wire:click="$set('nodeConfig.execution_behavior', '{{ $id }}')"
                class="flex-1 flex items-center justify-center gap-2.5 py-3 rounded-xl transition-all group"
                :class="config.execution_behavior === '{{ $id }}' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-500 hover:text-slate-300'"
            >
                <span class="material-symbols-outlined text-lg" :class="config.execution_behavior === '{{ $id }}' ? 'text-white' : 'text-slate-500 group-hover:text-slate-400'">{{ $info['icon'] }}</span>
                <span class="text-[10px] font-black uppercase tracking-widest">{{ $info['label'] }}</span>
            </button>
        @endforeach
    </div>
</div>
