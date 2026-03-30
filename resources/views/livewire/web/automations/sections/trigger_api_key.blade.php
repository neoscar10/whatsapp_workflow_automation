<div class="space-y-4" x-data="{ revealed: false }">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">API Key</label>
    <div class="relative group">
        <div class="absolute inset-y-0 left-4 flex items-center text-slate-600">
            <span class="material-symbols-outlined text-lg">key</span>
        </div>
        <input 
            :type="revealed ? 'text' : 'password'" 
            readonly 
            :value="revealed ? '{{ $nodeConfig['webhook_secret'] ?? '' }}' : '{{ $nodeConfig['api_key_masked'] ?? '••••••••••••••••' }}'" 
            class="w-full bg-[#101d39] border border-white/10 rounded-2xl pl-12 pr-24 py-3.5 text-xs font-mono text-slate-300 focus:ring-2 focus:ring-primary shadow-xl"
        />
        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1">
            <button 
                type="button"
                @click="revealed = !revealed"
                class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-slate-300 transition-colors"
                title="Toggle Visibility"
            >
                <span class="material-symbols-outlined text-lg" x-text="revealed ? 'visibility_off' : 'visibility'"></span>
            </button>
            <button 
                type="button"
                @click="navigator.clipboard.writeText('{{ $nodeConfig['webhook_secret'] ?? '' }}'); $dispatch('notify', {type: 'success', message: 'API Key copied!'})"
                class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-primary transition-colors"
                title="Copy Key"
            >
                <span class="material-symbols-outlined text-lg">content_copy</span>
            </button>
        </div>
    </div>
    <p class="text-[9px] font-bold text-slate-600 uppercase tracking-tight px-1">
        Use this key in the <code class="text-primary/70">X-Automation-Key</code> header to authorize requests.
    </p>
</div>
