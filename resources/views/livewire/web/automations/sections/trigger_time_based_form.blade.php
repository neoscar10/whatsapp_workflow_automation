<div class="rounded-3xl p-6 bg-white/[0.02] border border-white/5 space-y-6 shadow-2xl">
    <div class="grid grid-cols-2 gap-4">
        <div class="space-y-2">
            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest">Start Date</label>
            <input 
                type="date" 
                wire:model.lazy="nodeConfig.start_date" 
                class="w-full bg-[#101d39] border border-white/10 rounded-xl px-3 py-2.5 text-xs font-bold text-white focus:ring-2 focus:ring-primary shadow-lg"
            />
        </div>
        <div class="space-y-2">
            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest">Start Time</label>
            <input 
                type="time" 
                wire:model.lazy="nodeConfig.start_time" 
                class="w-full bg-[#101d39] border border-white/10 rounded-xl px-3 py-2.5 text-xs font-bold text-white focus:ring-2 focus:ring-primary shadow-lg"
            />
        </div>
    </div>

    <div class="space-y-3">
        <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest">Repeat Interval</label>
        <div class="grid grid-cols-2 gap-2">
            @foreach(['once', 'daily', 'weekly', 'monthly'] as $interval)
                <button 
                    type="button"
                    wire:click="$set('nodeConfig.repeat_interval', '{{ $interval }}')"
                    class="py-2.5 rounded-xl border text-[10px] font-black uppercase tracking-widest transition-all"
                    :class="config.repeat_interval === '{{ $interval }}' ? 'bg-primary text-white border-primary shadow-lg shadow-primary/20' : 'bg-white/5 text-slate-500 border-white/5 hover:border-white/10'"
                >
                    {{ $interval }}
                </button>
            @endforeach
        </div>
    </div>
</div>
