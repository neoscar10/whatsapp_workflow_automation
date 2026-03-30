<div class="space-y-6">
    {{-- Header --}}
    <div class="space-y-2">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">API Endpoint</label>
        <div class="flex gap-2">
            <select wire:model.lazy="nodeConfig.method" class="w-24 bg-[#101d39] border border-white/10 rounded-xl px-3 py-3 text-xs text-white focus:ring-1 focus:ring-primary shadow-lg appearance-none">
                <option value="GET">GET</option>
                <option value="POST">POST</option>
                <option value="PUT">PUT</option>
                <option value="PATCH">PATCH</option>
                <option value="DELETE">DELETE</option>
            </select>
            <input 
                type="text" 
                wire:model.lazy="nodeConfig.url" 
                class="flex-1 bg-[#101d39] border border-white/10 rounded-xl px-4 py-3 text-xs text-white focus:ring-1 focus:ring-primary shadow-lg"
                placeholder="https://api.example.com/endpoint"
            />
        </div>
    </div>

    {{-- Headers Section --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Headers</label>
            <button type="button" class="text-[9px] font-black text-primary uppercase tracking-widest hover:text-white transition-colors">Add Header</button>
        </div>
        <div class="space-y-2">
            @foreach($nodeConfig['headers'] ?? [] as $index => $header)
                <div class="flex gap-2 group">
                    <input type="text" value="{{ $header['key'] }}" class="flex-1 bg-white/[0.03] border border-white/5 rounded-lg px-3 py-2 text-[11px] text-slate-400" readonly />
                    <input type="text" value="{{ $header['value'] }}" class="flex-1 bg-white/[0.03] border border-white/5 rounded-lg px-3 py-2 text-[11px] text-slate-400" readonly />
                </div>
            @endforeach
        </div>
    </div>

    {{-- Body Section --}}
    <div class="space-y-3">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Request Body (JSON)</label>
        <textarea 
            wire:model.lazy="nodeConfig.body"
            class="w-full h-32 bg-[#101d39] border border-white/10 rounded-2xl p-4 text-[11px] font-mono text-slate-300 focus:ring-1 focus:ring-primary shadow-lg resize-none"
            placeholder='{ "key": "value" }'
        ></textarea>
        <div class="flex justify-between items-center px-1">
            <p class="text-[9px] font-bold text-slate-600 uppercase tracking-tight italic">Support liquid tags: @{{ variable }}</p>
            <button type="button" class="text-[9px] font-black text-slate-500 hover:text-primary transition-colors uppercase tracking-widest">Variable Selector</button>
        </div>
    </div>
</div>
