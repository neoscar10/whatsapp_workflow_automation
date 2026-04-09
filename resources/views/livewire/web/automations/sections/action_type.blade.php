<div class="space-y-3">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Action Category</label>
    
    @php
        $subtype = $activeNode->subtype ?? 'whatsapp_message';
        $label = match($subtype) {
            'whatsapp_message' => 'WhatsApp Message',
            'call_api' => 'API Call',
            'send_email' => 'Send Email',
            'update_row' => 'Update Data',
            'collect_input' => 'Collect Input',
            default => ucfirst(str_replace('_', ' ', $subtype))
        };
        $icon = match($subtype) {
            'whatsapp_message' => 'chat',
            'call_api' => 'api',
            'send_email' => 'mail',
            'update_row' => 'database',
            'collect_input' => 'input',
            default => 'play_arrow'
        };
    @endphp

    <div class="flex items-center gap-4 p-4 bg-white/5 border border-white/5 rounded-2xl group transition-all hover:bg-white/[0.07]">
        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20 shadow-lg shadow-primary/5">
            <span class="material-symbols-outlined text-primary text-xl transition-transform group-hover:scale-110">{{ $icon }}</span>
        </div>
        <div class="flex-1">
            <p class="text-[11px] font-black text-white uppercase tracking-wider">{{ $label }}</p>
            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-tight">Core Action • Non-editable</p>
        </div>
    </div>
</div>
