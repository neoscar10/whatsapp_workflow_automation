<div class="space-y-4">
    @php
        $category = $nodeConfig['trigger_category'] ?? '';
        $availableDefs = $triggerDefinitions->where('category', $category);
    @endphp

    @if($availableDefs->count() > 0)
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Trigger Definition / Preset</label>
        <div class="space-y-3">
            <div class="relative group">
                <select 
                    wire:model.live="nodeConfig.trigger_definition_key" 
                    class="w-full bg-[#101d39] border border-white/10 rounded-2xl px-4 py-3.5 text-sm text-white focus:ring-2 focus:ring-primary shadow-xl appearance-none transition-all hover:border-primary/30"
                >
                    <option value="">Select a {{ str_replace('_', ' ', $category) }} preset...</option>
                    @foreach($availableDefs as $def)
                        <option value="{{ $def->key }}">{{ $def->name }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-3.5 top-3.5 text-slate-500 pointer-events-none group-hover:text-primary transition-colors">expand_more</span>
            </div>
            
            <button 
                type="button" 
                wire:click="openCustomTriggerModal"
                class="w-full py-3.5 bg-white/5 border border-white/10 rounded-2xl text-[10px] font-black text-primary uppercase tracking-[0.15em] hover:bg-primary/5 hover:border-primary/30 transition-all flex items-center justify-center gap-2 group shadow-lg shadow-primary/5"
            >
                <span class="material-symbols-outlined text-sm transition-transform group-hover:rotate-90">add_circle</span>
                Define Custom {{ ucfirst(str_replace('_', ' ', $category)) }}
            </button>
        </div>
    @else
        <div class="p-8 rounded-[2rem] bg-white/[0.02] border border-dashed border-white/10 flex flex-col items-center justify-center text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-white/[0.03] flex items-center justify-center border border-white/5">
                <span class="material-symbols-outlined text-slate-600">tune</span>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-tight">No Presets Available</p>
                <p class="text-[9px] font-bold text-slate-600 uppercase tracking-tighter mt-1">Directly configure this {{ str_replace('_', ' ', $category) }} trigger below</p>
            </div>
        </div>
    @endif
</div>
