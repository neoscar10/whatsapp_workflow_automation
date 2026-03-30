<div class="rounded-3xl p-6 bg-white/[0.02] border border-white/5 space-y-4 shadow-2xl">
    <div class="flex items-center gap-3 text-primary mb-2">
        <span class="material-symbols-outlined">webhook</span>
        <h4 class="text-xs font-black uppercase tracking-widest">Webhook Settings</h4>
    </div>
    <p class="text-[10px] font-bold text-slate-500 leading-relaxed uppercase tracking-tight">
        Requests sent to your unique endpoint will trigger this automation.
    </p>
    
    <div class="pt-4 border-t border-white/5">
        <label class="flex items-center justify-between group cursor-pointer" @click="$refs.captureToggle.click()">
            <span class="text-xs font-bold text-slate-400 group-hover:text-slate-200 transition-colors">Capture Raw Payload</span>
            <div class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none" :class="config.capture_payload ? 'bg-primary' : 'bg-slate-800'">
                <input type="checkbox" wire:model.lazy="nodeConfig.capture_payload" x-ref="captureToggle" class="sr-only" />
                <span class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform" :class="config.capture_payload ? 'translate-x-5' : 'translate-x-1'"></span>
            </div>
        </label>
    </div>
</div>
