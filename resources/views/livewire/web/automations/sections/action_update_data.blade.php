<div class="space-y-6">
    <div class="space-y-2">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Target Source</label>
        <select wire:model.lazy="nodeConfig.target_source" class="w-full bg-[#101d39] border border-white/10 rounded-xl px-4 py-3.5 text-xs text-white focus:ring-1 focus:ring-primary shadow-lg appearance-none">
            <option value="contacts">CRM Contacts</option>
            <option value="orders">Recent Orders</option>
            <option value="custom_table">Custom Database Table</option>
        </select>
    </div>

    <div class="space-y-3 pt-2">
        <div class="flex items-center justify-between">
            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Update Fields</label>
            <button type="button" class="text-[9px] font-black text-primary uppercase tracking-widest hover:text-white transition-colors">Add Field</button>
        </div>
        <div class="space-y-3">
            <div class="p-4 rounded-xl bg-white/[0.02] border border-white/5 space-y-3 group hover:border-white/10 transition-all">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest">Field Name</label>
                        <input type="text" value="status" class="w-full bg-[#0a1630] border border-white/5 rounded-lg px-3 py-2 text-[10px] text-slate-400" readonly />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest">New Value</label>
                        <input type="text" value="replied" class="w-full bg-[#0a1630] border border-white/5 rounded-lg px-3 py-2 text-[10px] text-slate-400" readonly />
                    </div>
                </div>
            </div>
            <div class="p-4 rounded-xl bg-white/[0.02] border border-white/5 space-y-3 group hover:border-white/10 transition-all">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest">Field Name</label>
                        <input type="text" value="last_interaction_at" class="w-full bg-[#0a1630] border border-white/5 rounded-lg px-3 py-2 text-[10px] text-slate-400" readonly />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest">New Value</label>
                        <input type="text" value="@{{ now }}" class="w-full bg-[#0a1630] border border-white/5 rounded-lg px-3 py-2 text-[10px] text-white" readonly />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
