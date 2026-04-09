@if(!empty($nodeConfig['template_id']))
    <div class="space-y-6">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-sm text-primary">link</span>
            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Variable Mapping</label>
        </div>

        @php 
            $template = $availableTemplates->firstWhere('id', $nodeConfig['template_id']);
        @endphp

        @if($template)
            {{-- Body Variables --}}
            @if(!empty($nodeConfig['template_variable_mappings']['body']))
                <div class="space-y-4">
                    <label class="block text-[9px] font-black text-slate-600 uppercase tracking-widest pl-1">Body Placeholders</label>
                    <div class="space-y-3">
                        @foreach($nodeConfig['template_variable_mappings']['body'] as $index => $value)
                            <div class="space-y-2 p-3.5 rounded-2xl bg-white/[0.02] border border-white/5 hover:border-primary/20 transition-all">
                                <div class="flex items-center justify-between px-0.5">
                                    <span class="text-[10px] font-black text-primary uppercase tracking-tighter">Placeholder &#123;&#123; {{ $index }} &#125;&#125;</span>
                                    <span class="text-[9px] font-bold text-slate-600 italic">Body variable</span>
                                </div>
                                <div class="relative group">
                                    <input 
                                        type="text" 
                                        wire:model.lazy="nodeConfig.template_variable_mappings.body.{{ $index }}"
                                        placeholder="{{ $index == 1 ? 'e.g. customer.name' : 'Enter expression...' }}"
                                        class="w-full bg-[#0a1630] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder:text-slate-700 focus:ring-2 focus:ring-primary shadow-inner"
                                    />
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" class="text-slate-500 hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-lg">variable_insert</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Header Variables --}}
            @if(!empty($nodeConfig['template_variable_mappings']['header']))
                <div class="space-y-4 pt-2">
                    <label class="block text-[9px] font-black text-slate-600 uppercase tracking-widest pl-1">Header Placeholders</label>
                    <div class="space-y-3">
                        @foreach($nodeConfig['template_variable_mappings']['header'] as $index => $value)
                            <div class="space-y-2 p-3.5 rounded-2xl bg-white/[0.02] border border-white/5 hover:border-primary/20 transition-all">
                                <div class="flex items-center justify-between px-0.5">
                                    <span class="text-[10px] font-black text-amber-500 uppercase tracking-tighter">Placeholder &#123;&#123; {{ $index }} &#125;&#125;</span>
                                    <span class="text-[9px] font-bold text-slate-600 italic">Header variable</span>
                                </div>
                                <div class="relative group">
                                    <input 
                                        type="text" 
                                        wire:model.lazy="nodeConfig.template_variable_mappings.header.{{ $index }}"
                                        placeholder="Enter expression..."
                                        class="w-full bg-[#0a1630] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder:text-slate-700 focus:ring-2 focus:ring-primary shadow-inner"
                                    />
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" class="text-slate-500 hover:text-amber-500 transition-colors">
                                            <span class="material-symbols-outlined text-lg">variable_insert</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(empty($nodeConfig['template_variable_mappings']['body']) && empty($nodeConfig['template_variable_mappings']['header']))
                <div class="p-6 rounded-2xl bg-emerald-500/5 border border-emerald-500/10 flex flex-col items-center justify-center text-center">
                    <span class="material-symbols-outlined text-emerald-500/50 text-3xl mb-3">check_circle</span>
                    <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Static Template</p>
                    <p class="text-[9px] font-bold text-slate-500 uppercase mt-1">This template has no dynamic variables to map.</p>
                </div>
            @endif
        @endif
    </div>
@endif
