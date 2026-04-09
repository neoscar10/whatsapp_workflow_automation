<div class="space-y-3">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Select Template</label>
    <div class="relative">
        <select 
            wire:model.live="nodeConfig.template_id"
            class="w-full bg-[#101d39] border border-white/5 rounded-xl px-4 py-3.5 text-sm font-bold text-white shadow-xl focus:ring-2 focus:ring-primary focus:border-transparent appearance-none cursor-pointer"
        >
            <option value="">Choose an approved template...</option>
            @foreach($availableTemplates as $template)
                <option value="{{ $template->id }}">{{ $template->remote_template_name }} ({{ strtoupper($template->language_code) }})</option>
            @endforeach
        </select>
        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-500">
            <span class="material-symbols-outlined">expand_more</span>
        </div>
    </div>

    @if(!empty($nodeConfig['template_id']))
        @php $selectedTpl = $availableTemplates->firstWhere('id', $nodeConfig['template_id']); @endphp
        @if($selectedTpl)
            <div class="p-4 rounded-2xl bg-white/[0.03] border border-white/5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[9px] font-black uppercase tracking-widest text-primary">{{ $selectedTpl->category }}</span>
                    <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20">
                        <div class="w-1 h-1 rounded-full bg-emerald-500"></div>
                        <span class="text-[8px] font-black uppercase text-emerald-500">Approved</span>
                    </div>
                </div>
                
                @if($selectedTpl->header_text)
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-slate-500 uppercase">Header</label>
                        <p class="text-[11px] text-slate-300 font-medium leading-relaxed">{{ $selectedTpl->header_text }}</p>
                    </div>
                @endif

                <div class="space-y-1">
                    <label class="text-[8px] font-black text-slate-500 uppercase">Body Preview</label>
                    <p class="text-[11px] text-white font-medium leading-relaxed italic">"{{ $selectedTpl->body_text }}"</p>
                </div>

                @if($selectedTpl->footer_text)
                    <div class="space-y-1 pt-1 border-t border-white/5">
                        <p class="text-[10px] text-slate-500 font-medium italic">{{ $selectedTpl->footer_text }}</p>
                    </div>
                @endif
            </div>
        @endif
    @endif
</div>
