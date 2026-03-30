<div class="space-y-4">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Select Trigger Type</label>
    <div class="grid grid-cols-2 gap-2 p-1 bg-[#101d39]/60 border border-white/5 rounded-2xl shadow-inner">
        @php
            $types = [
                ['id' => 'conditional', 'icon' => 'alt_route', 'label' => 'Conditional'],
                ['id' => 'time_based', 'icon' => 'schedule', 'label' => 'Scheduled'],
                ['id' => 'webhook_api', 'icon' => 'webhook', 'label' => 'Webhook'],
                ['id' => 'event_based', 'icon' => 'dynamic_feed', 'label' => 'DB Change'],
            ];
        @endphp
        @foreach($types as $type)
            <button 
                type="button"
                wire:click="$set('nodeConfig.trigger_category', '{{ $type['id'] }}')"
                class="flex flex-col items-center justify-center py-4 rounded-xl transition-all group gap-2"
                :class="config.trigger_category === '{{ $type['id'] }}' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-500 hover:text-slate-300'"
            >
                <span class="material-symbols-outlined text-lg" :class="config.trigger_category === '{{ $type['id'] }}' ? 'text-white' : 'text-slate-500 group-hover:text-slate-400'">{{ $type['icon'] }}</span>
                <span class="text-[9px] font-black uppercase tracking-widest text-center">{{ $type['label'] }}</span>
            </button>
        @endforeach
    </div>
</div>
