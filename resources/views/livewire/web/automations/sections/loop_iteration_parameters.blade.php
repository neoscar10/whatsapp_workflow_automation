<div class="space-y-4 pt-4 border-t border-white/5">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Iteration Parameters</label>
    <div class="p-6 bg-white/[0.03] border border-white/5 rounded-3xl space-y-5 shadow-2xl">
        <div class="space-y-2">
            <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest px-1">Data Source</label>
            <div class="relative">
                <select wire:model.lazy="nodeConfig.data_source" class="w-full bg-[#101d39] border border-white/10 rounded-xl px-4 py-3 text-xs text-white focus:ring-1 focus:ring-primary appearance-none">
                    <option value="user_orders">User Orders (Array)</option>
                    <option value="cart_items">Cart Items (Array)</option>
                    <option value="support_tickets">Support Tickets (Array)</option>
                    <option value="custom_array">Custom Array Variable</option>
                </select>
                <span class="material-symbols-outlined absolute right-3 top-3 text-slate-600 text-sm pointer-events-none">database</span>
            </div>
        </div>

        <div class="space-y-2">
            <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest px-1">Item Alias Name</label>
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.lazy="nodeConfig.item_alias_name" 
                    class="w-full bg-[#101d39] border border-white/10 rounded-xl px-4 py-3 text-xs text-white focus:ring-1 focus:ring-primary shadow-lg"
                    placeholder="e.g. order"
                />
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[9px] font-black text-slate-600 uppercase tracking-widest">Variable</span>
            </div>
            <p class="text-[9px] font-bold text-slate-600 uppercase tracking-tight px-1">
                Each item in the collection will be available as <span class="text-primary italic">@{{ {{ $nodeConfig['item_alias_name'] ?? 'item' }} }}</span>.
            </p>
        </div>
    </div>
</div>
