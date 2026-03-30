<div class="space-y-3">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Provider Account</label>
    <div class="relative">
        <select wire:model.lazy="nodeConfig.provider_account_id" class="w-full bg-[#101d39] border border-white/10 rounded-xl px-4 py-3.5 text-sm text-white focus:ring-2 focus:ring-primary shadow-xl appearance-none group hover:border-primary/30 transition-all">
            <option value="">Select an account...</option>
            @foreach($availableAccounts as $account)
                <option value="{{ $account->id }}">{{ $account->phoneNumbers->first()?->display_name ?: $account->business_id }}</option>
            @endforeach
        </select>
        <span class="material-symbols-outlined absolute right-3.5 top-3.5 text-slate-500 pointer-events-none">expand_more</span>
    </div>
</div>
