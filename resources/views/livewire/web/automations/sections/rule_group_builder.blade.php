@foreach($nodeConfig['rule_groups'] ?? [] as $groupIndex => $group)
    <div class="space-y-3">
        <label class="block text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">Rule Group {{ $groupIndex + 1 }}</label>
        <div class="bg-surface-container-low/50 rounded-2xl p-5 border border-white/5 space-y-5 shadow-2xl">
            @foreach($group['rules'] as $ruleIndex => $rule)
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Rule {{ $ruleIndex + 1 }}</span>
                        @if(count($group['rules']) > 1)
                            <button wire:click="removeRule({{ $groupIndex }}, {{ $ruleIndex }})" class="text-red-500/50 hover:text-red-500 transition-colors">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider">Field</label>
                            <div class="relative">
                                <select wire:model.lazy="nodeConfig.rule_groups.{{ $groupIndex }}.rules.{{ $ruleIndex }}.field" class="w-full bg-[#101d39]/80 border border-white/5 rounded-xl text-sm p-3.5 text-white focus:ring-2 focus:ring-primary shadow-xl appearance-none">
                                    <option value="message_body">Message Body</option>
                                    <option value="sender_phone">Sender Phone</option>
                                    <option value="contact_name">Contact Name</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-3.5 top-3.5 text-slate-600 pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider">Operator</label>
                            <div class="relative">
                                <select wire:model.lazy="nodeConfig.rule_groups.{{ $groupIndex }}.rules.{{ $ruleIndex }}.operator" class="w-full bg-[#101d39]/80 border border-white/5 rounded-xl text-sm p-3.5 text-white focus:ring-2 focus:ring-primary shadow-xl appearance-none">
                                    <option value="contains">Contains</option>
                                    <option value="not_contains">Does not contain</option>
                                    <option value="equals">Equals</option>
                                    <option value="starts_with">Starts with</option>
                                    <option value="ends_with">Ends with</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-3.5 top-3.5 text-slate-600 pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider">Value</label>
                            <input 
                                type="text" 
                                wire:model.lazy="nodeConfig.rule_groups.{{ $groupIndex }}.rules.{{ $ruleIndex }}.value"
                                class="w-full bg-[#101d39]/80 border border-white/5 rounded-xl text-sm p-3.5 text-white focus:ring-2 focus:ring-primary shadow-xl placeholder:text-slate-700"
                                placeholder="e.g., support"
                            />
                        </div>
                    </div>

                    @if(!$loop->last)
                        <div class="flex items-center gap-4 py-2">
                            <div class="flex-1 h-px bg-white/5"></div>
                            <span class="text-[9px] font-black bg-[#152445] border border-white/10 px-2 py-1 rounded-lg text-slate-500 uppercase tracking-widest shadow-lg">AND</span>
                            <div class="flex-1 h-px bg-white/5"></div>
                        </div>
                    @endif
                </div>
            @endforeach

            <button wire:click="addRule({{ $groupIndex }})" class="w-full py-4 border-2 border-dashed border-white/5 rounded-2xl text-slate-500 hover:border-primary/50 hover:text-primary hover:bg-primary/5 transition-all text-[11px] font-black uppercase tracking-widest flex items-center justify-center gap-3 group">
                <span class="material-symbols-outlined text-lg transition-transform group-hover:scale-125">add</span>
                Add Rule
            </button>
        </div>
    </div>
@endforeach
