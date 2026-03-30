<div class="space-y-3" x-data="{ editorActive: false }">
    <div class="flex items-center justify-between">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Message Editor</label>
        <button type="button" class="text-[10px] font-black text-primary hover:text-blue-400 flex items-center gap-1.5 uppercase tracking-wider group">
            <span class="material-symbols-outlined text-sm transition-transform group-hover:rotate-12">variable_insert</span>
            Variable Selector
        </button>
    </div>

    <div class="rounded-2xl border border-white/10 overflow-hidden bg-[#0a1630] shadow-2xl transition-all" :class="editorActive ? 'ring-2 ring-primary/50' : ''">
        <div class="flex items-center gap-1 p-2 border-b border-white/5 bg-white/[0.02]">
            <button type="button" class="p-1.5 hover:bg-white/5 rounded-lg text-slate-400 hover:text-white transition-colors"><span class="material-symbols-outlined text-sm">format_bold</span></button>
            <button type="button" class="p-1.5 hover:bg-white/5 rounded-lg text-slate-400 hover:text-white transition-colors"><span class="material-symbols-outlined text-sm">format_italic</span></button>
            <button type="button" class="p-1.5 hover:bg-white/5 rounded-lg text-slate-400 hover:text-white transition-colors"><span class="material-symbols-outlined text-sm">link</span></button>
            <div class="w-[1px] h-4 bg-white/10 mx-1.5"></div>
            <button type="button" class="p-1.5 hover:bg-white/5 rounded-lg text-slate-400 hover:text-white transition-colors"><span class="material-symbols-outlined text-sm">face</span></button>
        </div>

        <textarea 
            wire:model.lazy="nodeConfig.message_body"
            @focus="editorActive = true"
            @blur="editorActive = false"
            class="w-full bg-transparent border-0 px-4 py-4 text-sm text-white placeholder:text-slate-600 focus:ring-0 min-h-[180px] leading-relaxed resize-none"
            placeholder="Type your message here. Use @{{variable}} for dynamic content..."
        ></textarea>
    </div>
</div>
