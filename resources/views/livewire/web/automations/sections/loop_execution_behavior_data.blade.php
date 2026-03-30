<div class="space-y-4">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Processing Mode</label>
    <div class="space-y-3">
        @foreach(['sequential' => ['icon' => 'reorder', 'label' => 'Sequential', 'desc' => 'Process one item at a time (Safer)'], 'parallel' => ['icon' => 'grid_view', 'label' => 'Parallel', 'desc' => 'Process all items simultaneously (Faster)']] as $id => $info)
            <label class="flex items-center gap-4 p-4 rounded-2xl bg-[#101d39]/40 border transition-all cursor-pointer group"
                :class="config.execution_behavior === '{{ $id }}' ? 'border-primary bg-primary/5' : 'border-white/5 hover:border-white/10'">
                <input type="radio" wire:model.lazy="nodeConfig.execution_behavior" value="{{ $id }}" class="hidden">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all"
                    :class="config.execution_behavior === '{{ $id }}' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-white/5 text-slate-500 group-hover:bg-white/10'">
                    <span class="material-symbols-outlined text-xl">{{ $info['icon'] }}</span>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-black uppercase tracking-widest transition-colors"
                        :class="config.execution_behavior === '{{ $id }}' ? 'text-white' : 'text-slate-400 group-hover:text-slate-200'">{{ $info['label'] }}</p>
                    <p class="text-[9px] font-bold text-slate-600 uppercase tracking-tight">{{ $info['desc'] }}</p>
                </div>
                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                    :class="config.execution_behavior === '{{ $id }}' ? 'border-primary' : 'border-white/10'">
                    <div class="w-2.5 h-2.5 rounded-full bg-primary transition-all scale-0"
                        :class="config.execution_behavior === '{{ $id }}' ? 'scale-100' : ''"></div>
                </div>
            </label>
        @endforeach
    </div>
</div>
