<div class="space-y-4 pt-4 border-t border-white/5">
    <div class="flex items-center justify-between px-1">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Rule Builder</label>
        <span class="px-2 py-0.5 rounded bg-primary/10 text-primary text-[9px] font-black uppercase tracking-widest border border-primary/20">
            Match {{ strtoupper($nodeConfig['match_mode'] ?? 'all') }}
        </span>
    </div>

    <div class="space-y-3">
        @foreach($nodeConfig['rules'] ?? [] as $index => $rule)
            <div class="p-5 bg-white/[0.03] border border-white/5 rounded-3xl space-y-4 group hover:border-white/10 transition-all relative">
                <button 
                    type="button" 
                    wire:click="removeTriggerRule({{ $index }})"
                    class="absolute -right-2 -top-2 w-6 h-6 bg-red-500/10 border border-red-500/20 text-red-500 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500 hover:text-white"
                >
                    <span class="material-symbols-outlined text-xs">close</span>
                </button>

                <div class="space-y-3">
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest">Field</label>
                        <select wire:model.lazy="nodeConfig.rules.{{ $index }}.field" class="w-full bg-[#101d39] border border-white/10 rounded-xl px-3 py-2.5 text-xs text-white focus:ring-1 focus:ring-primary shadow-lg appearance-none">
                            <option value="user.tags">User Tags</option>
                            <option value="user.segment">User Segment</option>
                            <option value="user.last_seen">Last Seen</option>
                            <option value="user.total_spend">Total Spend</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest">Operator</label>
                            <select wire:model.lazy="nodeConfig.rules.{{ $index }}.operator" class="w-full bg-[#101d39] border border-white/10 rounded-xl px-3 py-2.5 text-xs text-white focus:ring-1 focus:ring-primary shadow-lg appearance-none">
                                <option value="contains">Contains</option>
                                <option value="equals">Equals</option>
                                <option value="greater_than">Greater than</option>
                                <option value="is_set">Is set</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest">Value</label>
                            <input 
                                type="text" 
                                wire:model.lazy="nodeConfig.rules.{{ $index }}.value" 
                                class="w-full bg-[#101d39] border border-white/10 rounded-xl px-3 py-2.5 text-xs text-white focus:ring-1 focus:ring-primary shadow-lg"
                                placeholder="Value..."
                            />
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <button 
            type="button" 
            wire:click="addTriggerRule"
            class="w-full py-4 border border-dashed border-white/10 rounded-3xl text-[10px] font-black text-slate-500 uppercase tracking-widest hover:border-primary/50 hover:bg-primary/5 hover:text-primary transition-all flex items-center justify-center gap-2 group"
        >
            <span class="material-symbols-outlined text-sm transition-transform group-hover:scale-125">add_circle</span>
            Add Another Rule
        </button>
    </div>
</div>
