@foreach($nodeConfig['rule_groups'] ?? [] as $groupIndex => $group)
    <div class="space-y-3" x-data="{ 
        getOperatorLabel(op) {
            const labels = {
                'equals': 'Equals',
                'not_equals': 'Does not equal',
                'contains': 'Contains',
                'not_contains': 'Does not contain',
                'starts_with': 'Starts with',
                'ends_with': 'Ends with',
                'greater_than': 'Greater than',
                'less_than': 'Less than',
                'exists': 'Exists',
                'is_set': 'Is set',
                'is_empty': 'Is empty',
                'is_not_empty': 'Is not empty'
            };
            return labels[op] || op;
        }
    }">
        <label class="block text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">Rule Group {{ $groupIndex + 1 }}</label>
        <div class="bg-surface-container-low/50 rounded-2xl p-5 border border-white/5 space-y-5 shadow-2xl">
            @foreach($group['rules'] as $ruleIndex => $rule)
                <div class="space-y-4" x-data="{ 
                    get operator() { return $wire.get('nodeConfig.rule_groups.{{ $groupIndex }}.rules.{{ $ruleIndex }}.operator') },
                    get field() { return $wire.get('nodeConfig.rule_groups.{{ $groupIndex }}.rules.{{ $ruleIndex }}.field') },
                    get value() { return $wire.get('nodeConfig.rule_groups.{{ $groupIndex }}.rules.{{ $ruleIndex }}.value') },
                    isExistenceOperator() {
                        return ['exists', 'is_set', 'is_empty', 'is_not_empty'].includes(this.operator);
                    }
                }">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Rule {{ $ruleIndex + 1 }}</span>
                            <div class="h-1 w-1 rounded-full bg-slate-700"></div>
                            <span class="text-[9px] font-bold text-primary/60 uppercase tracking-tighter italic" x-show="field">
                                Checks <span class="text-white" x-text="field"></span>
                            </span>
                        </div>
                        @if(count($group['rules']) > 1)
                            <button wire:click="removeRule({{ $groupIndex }}, {{ $ruleIndex }})" class="text-red-500/50 hover:text-red-500 transition-colors">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider">Field (Variable Path)</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    list="workflow-variables"
                                    wire:model.lazy="nodeConfig.rule_groups.{{ $groupIndex }}.rules.{{ $ruleIndex }}.field" 
                                    class="w-full bg-[#101d39]/80 border border-white/5 rounded-xl text-sm p-3.5 text-white focus:ring-2 focus:ring-primary shadow-xl"
                                    placeholder="e.g., message_body or trigger.name"
                                />
                                <span class="material-symbols-outlined absolute right-3.5 top-3.5 text-slate-600 pointer-events-none">account_tree</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider">Operator</label>
                            <div class="relative">
                                <select wire:model.lazy="nodeConfig.rule_groups.{{ $groupIndex }}.rules.{{ $ruleIndex }}.operator" class="w-full bg-[#101d39]/80 border border-white/5 rounded-xl text-sm p-3.5 text-white focus:ring-2 focus:ring-primary shadow-xl appearance-none">
                                    <optgroup label="Text Operators" class="bg-[#101d39]">
                                        <option value="contains">Contains</option>
                                        <option value="not_contains">Does not contain</option>
                                        <option value="equals">Equals</option>
                                        <option value="not_equals">Does not equal</option>
                                        <option value="starts_with">Starts with</option>
                                        <option value="ends_with">Ends with</option>
                                    </optgroup>
                                    <optgroup label="Numeric Operators" class="bg-[#101d39]">
                                        <option value="greater_than">Greater than</option>
                                        <option value="less_than">Less than</option>
                                    </optgroup>
                                    <optgroup label="Existence & State" class="bg-[#101d39]">
                                        <option value="exists">Exists (Path is valid)</option>
                                        <option value="is_set">Is set (Not null)</option>
                                        <option value="is_empty">Is empty</option>
                                        <option value="is_not_empty">Is not empty</option>
                                    </optgroup>
                                </select>
                                <span class="material-symbols-outlined absolute right-3.5 top-3.5 text-slate-600 pointer-events-none">unfold_more</span>
                            </div>
                        </div>

                        <div class="space-y-2" x-show="!isExistenceOperator()" x-transition>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider">Comparison Value</label>
                            <input 
                                type="text" 
                                wire:model.lazy="nodeConfig.rule_groups.{{ $groupIndex }}.rules.{{ $ruleIndex }}.value"
                                class="w-full bg-[#101d39]/80 border border-white/5 rounded-xl text-sm p-3.5 text-white focus:ring-2 focus:ring-primary shadow-xl placeholder:text-slate-700"
                                placeholder="e.g., support"
                            />
                        </div>
                    </div>

                    <!-- Rule Insight / Natural Language Preview -->
                    <div class="bg-slate-900/40 border border-white/5 rounded-xl p-3 flex items-start gap-3" x-show="field">
                        <span class="material-symbols-outlined text-primary text-sm mt-0.5">info</span>
                        <div class="text-[11px] leading-relaxed text-slate-400">
                            Logic: If <span class="text-white font-bold" x-text="field"></span> 
                            <span class="text-primary font-bold" x-text="getOperatorLabel(operator).toLowerCase()"></span>
                            <template x-if="!isExistenceOperator() && value">
                                <span>"<span class="text-white font-bold" x-text="value"></span>"</span>
                            </template>
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

<datalist id="workflow-variables">
    <option value="message_body">Message Body</option>
    <option value="sender_phone">Sender Phone Number</option>
    <option value="contact_name">Contact Name</option>
    <option value="trigger.name">Name (Trigger Prefix)</option>
    <option value="trigger.phone_number">Phone (Trigger Prefix)</option>
    <option value="last_action_status">Status of Last Action</option>
</datalist>
