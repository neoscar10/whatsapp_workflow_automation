<div class="space-y-4">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Select Trigger Category</label>
    <div class="grid grid-cols-2 gap-2">
        @php
            $categories = [
                ['id' => 'time_based', 'icon' => 'schedule', 'label' => 'Time-based'],
                ['id' => 'event_based', 'icon' => 'dynamic_feed', 'label' => 'Event-based'],
                ['id' => 'behavior', 'icon' => 'person_search', 'label' => 'Behavior'],
                ['id' => 'webhook', 'icon' => 'webhook', 'label' => 'Webhook'],
                ['id' => 'conditional', 'icon' => 'alt_route', 'label' => 'Conditional'],
            ];
        @endphp
        @foreach($categories as $cat)
            <button 
                type="button"
                wire:click="$set('nodeConfig.trigger_category', '{{ $cat['id'] }}')"
                class="flex flex-col items-center justify-center p-4 rounded-2xl border transition-all space-y-2 group"
                :class="config.trigger_category === '{{ $cat['id'] }}' ? 'bg-primary/20 border-primary shadow-[0_0_20px_rgba(37,99,235,0.2)]' : 'bg-[#101d39]/40 border-white/5 hover:border-white/10'"
            >
                <span class="material-symbols-outlined text-xl" :class="config.trigger_category === '{{ $cat['id'] }}' ? 'text-primary' : 'text-slate-500 group-hover:text-slate-300'">{{ $cat['icon'] }}</span>
                <span class="text-[10px] font-black uppercase tracking-widest" :class="config.trigger_category === '{{ $cat['id'] }}' ? 'text-white' : 'text-slate-500 group-hover:text-slate-400'">{{ $cat['label'] }}</span>
            </button>
        @endforeach
    </div>
</div>
