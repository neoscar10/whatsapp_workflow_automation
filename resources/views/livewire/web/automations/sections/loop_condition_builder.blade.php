<div class="space-y-4 pt-4 border-t border-white/5">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Loop Condition</label>
    <div class="p-6 bg-white/[0.03] border border-white/5 rounded-3xl space-y-5 shadow-2xl">
        <div class="space-y-2">
            <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest px-1">Field to evaluate</label>
            <select wire:model.lazy="nodeConfig.condition.field" class="w-full bg-[#101d39] border border-white/10 rounded-xl px-4 py-3 text-xs text-white focus:ring-1 focus:ring-primary appearance-none">
                <option value="last_message_text">Last Message Text</option>
                <option value="user.tags">User Tags</option>
                <option value="iteration_count">Iteration Count</option>
                <option value="variable_resolved">Variable Resolved</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest px-1">Operator</label>
                <select wire:model.lazy="nodeConfig.condition.operator" class="w-full bg-[#101d39] border border-white/10 rounded-xl px-4 py-3 text-xs text-white focus:ring-1 focus:ring-primary appearance-none">
                    <option value="contains">Contains</option>
                    <option value="equals">Equals</option>
                    <option value="is_set">Is set</option>
                    <option value="matches_regex">Matches Regex</option>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest px-1">Value</label>
                <input 
                    type="text" 
                    wire:model.lazy="nodeConfig.condition.value" 
                    class="w-full bg-[#101d39] border border-white/10 rounded-xl px-4 py-3 text-xs text-white focus:ring-1 focus:ring-primary shadow-lg"
                    placeholder="Value..."
                />
            </div>
        </div>
    </div>
</div>
