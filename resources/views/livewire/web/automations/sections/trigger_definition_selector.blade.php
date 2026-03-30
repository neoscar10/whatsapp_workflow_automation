<div class="space-y-4">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Trigger Definition</label>
    <div class="space-y-3">
        <div class="relative">
            <select wire:model.live="nodeConfig.trigger_definition_key" class="w-full bg-[#101d39] border border-white/10 rounded-2xl px-4 py-3.5 text-sm text-white focus:ring-2 focus:ring-primary shadow-xl appearance-none">
                <option value="">Select a preset...</option>
                @foreach($triggerDefinitions->where('category', $nodeConfig['trigger_category'] ?? '') as $def)
                    <option value="{{ $def->key }}">{{ $def->name }}</option>
                @endforeach
            </select>
            <span class="material-symbols-outlined absolute right-3.5 top-3.5 text-slate-500 pointer-events-none">expand_more</span>
        </div>
        
        <button 
            type="button" 
            wire:click="openCustomTriggerModal"
            class="w-full py-3.5 bg-white/5 border border-white/10 rounded-2xl text-[10px] font-black text-primary uppercase tracking-[0.15em] hover:bg-primary/5 hover:border-primary/30 transition-all flex items-center justify-center gap-2 group"
        >
            <span class="material-symbols-outlined text-sm transition-transform group-hover:scale-125">add_circle</span>
            Create New Trigger
        </button>
    </div>
</div>
