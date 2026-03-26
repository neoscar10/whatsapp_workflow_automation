@if($showTemplateModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        {{-- Backdrop --}}
        <div 
            class="absolute inset-0 bg-slate-900/40 backdrop-blur-md"
            wire:click="closeTemplateSendModal"
        ></div>

        {{-- Modal Content --}}
        <div class="relative flex h-[85vh] w-full max-w-6xl flex-col overflow-hidden rounded-[2.5rem] border border-white/20 bg-white shadow-2xl dark:border-slate-800/50 dark:bg-[#0B0F1A]">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-100 bg-white/80 px-8 py-6 backdrop-blur-xl dark:border-slate-800/50 dark:bg-[#0B0F1A]/80">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Select Template</h2>
                    <p class="text-[11px] font-medium text-slate-400 uppercase tracking-tighter">Choose a pre-approved message with dynamic data</p>
                </div>
                <button
                    type="button"
                    wire:click="closeTemplateSendModal"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition-all hover:bg-slate-200 hover:text-slate-600 dark:bg-slate-800 dark:hover:bg-slate-700 dark:hover:text-white"
                >
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            {{-- Content --}}
            <div class="flex flex-1 overflow-hidden">
                {{-- Left Pane: Template List --}}
                <div class="flex w-full flex-col border-r border-slate-100 dark:border-slate-800/50 md:w-[35%] xl:w-[30%] shrink-0">
                    <div class="space-y-6 p-8 pb-4">
                        {{-- Search --}}
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-primary">search</span>
                            <input
                                wire:model.live.debounce.300ms="templateSearch"
                                type="text"
                                placeholder="Search templates..."
                                class="w-full rounded-full border-none bg-slate-100/50 py-3.5 pl-12 pr-4 text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-primary/30 dark:bg-slate-800/50 dark:text-white"
                            />
                        </div>

                        {{-- Filters --}}
                        <div class="flex gap-2 overflow-x-auto pb-1 no-scrollbar">
                            @foreach(['all' => 'All', 'approved' => 'Approved', 'recent' => 'Recent'] as $key => $label)
                                <button
                                    type="button"
                                    wire:click="$set('templateFilter', '{{ $key }}')"
                                    class="whitespace-nowrap rounded-full px-5 py-2 text-[11px] font-black uppercase tracking-widest transition-all {{ $templateFilter === $key ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400' }}"
                                >
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        
                        @if($templateModalError)
                            <div class="rounded-2xl bg-red-50 px-4 py-3 text-xs font-bold text-red-600 dark:bg-red-950/20 dark:text-red-400">
                                {{ $templateModalError }}
                            </div>
                        @endif
                    </div>

                    {{-- List --}}
                    <div class="custom-scrollbar flex-1 space-y-3 overflow-y-auto px-8 pb-8">
                        @forelse($availableTemplates as $template)
                            <button
                                type="button"
                                wire:click="selectTemplate({{ $template['id'] }})"
                                class="group relative flex w-full items-center gap-4 rounded-2xl border border-transparent p-4 text-left transition-all {{ (int) $selectedTemplateId === (int) $template['id'] ? 'bg-slate-50 border-primary/20 dark:bg-slate-800/50' : 'hover:bg-slate-50 dark:hover:bg-slate-800/30' }}"
                            >
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl shadow-sm transition-all {{ (int) $selectedTemplateId === (int) $template['id'] ? 'bg-primary text-white scale-110 shadow-primary/20' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                    <span class="material-symbols-outlined text-[24px]">{{ $template['icon'] ?? 'description' }}</span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $template['name'] }}</p>
                                    <p class="truncate text-[11px] font-medium text-slate-400">{{ $template['subtitle'] }}</p>
                                </div>

                                @if((int) $selectedTemplateId === (int) $template['id'])
                                    <span class="material-symbols-outlined text-primary">check_circle</span>
                                @endif
                            </button>
                        @empty
                            <div class="py-12 text-center">
                                <span class="material-symbols-outlined text-5xl text-slate-200 mb-4">search_off</span>
                                <p class="text-sm font-bold text-slate-600 dark:text-slate-400">No templates found</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Right Pane: Preview & Variables (3-Column Split) --}}
                <div class="hidden flex-1 overflow-hidden bg-[#F8FAFC] dark:bg-slate-950/30 md:flex">
                    @if($selectedTemplatePreview)
                        <div class="flex flex-1 overflow-hidden">
                            {{-- Column 2: Live Preview (WIDER) --}}
                            <div class="flex flex-1 flex-col border-r border-slate-100 bg-white/50 backdrop-blur-sm dark:border-slate-800/50 dark:bg-slate-900/40">
                                <div class="flex flex-1 flex-col overflow-y-auto custom-scrollbar p-10">
                                    <div class="mb-8 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                                            </div>
                                            <span class="text-[11px] font-black uppercase tracking-widest text-slate-500">Live Preview</span>
                                        </div>
                                    </div>

                                    <div class="relative mx-auto w-full max-w-[360px] rounded-3xl bg-white p-8 shadow-2xl dark:bg-slate-900 message-inbound">
                                        {{-- Internal Preview Header --}}
                                        <div class="mb-5 flex items-center gap-3 border-b border-slate-50 pb-5 dark:border-slate-800">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-primary dark:bg-slate-800">
                                                <span class="material-symbols-outlined text-sm">smart_toy</span>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Template Preview</p>
                                                <p class="truncate text-xs font-bold text-slate-900 dark:text-white max-w-[200px]">{{ $selectedTemplatePreview['name'] }}</p>
                                            </div>
                                        </div>

                                        <div class="space-y-5">
                                            @foreach($selectedTemplatePreview['preview_paragraphs'] ?? [] as $paragraph)
                                                <p class="text-[14px] leading-relaxed text-slate-700 dark:text-slate-300">{!! $paragraph !!}</p>
                                            @endforeach
                                        </div>

                                        @if(!empty($selectedTemplatePreview['button_text']))
                                            <div class="mt-8 pt-6 border-t border-slate-50 dark:border-slate-800">
                                                <button class="w-full rounded-2xl bg-primary py-4 text-[11px] font-black text-white shadow-xl shadow-primary/20 cursor-default uppercase tracking-widest">
                                                    {{ $selectedTemplatePreview['button_text'] }}
                                                </button>
                                            </div>
                                        @endif

                                        <div class="mt-4 flex items-center justify-end gap-1.5">
                                            <span class="text-[10px] font-medium text-slate-400 tracking-tighter">{{ $selectedTemplatePreview['time_label'] ?? now()->format('H:i A') }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-10 mx-auto w-full max-w-[360px] rounded-2xl border border-dashed border-slate-200 p-6 dark:border-slate-800">
                                        <h5 class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">
                                            <span class="material-symbols-outlined text-sm">info</span>
                                            Template Attributes
                                        </h5>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-full text-[9px] font-bold text-slate-500 uppercase">{{ $selectedTemplatePreview['category_label'] }}</span>
                                            <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-full text-[9px] font-bold text-slate-500 uppercase">Lang: EN</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Column 3: Variable Management (NARROWER) --}}
                            <div class="flex w-[35%] min-w-[340px] flex-col overflow-hidden shrink-0">
                                @if(!empty($selectedTemplatePreview['variables']))
                                    <div class="flex flex-1 flex-col overflow-y-auto custom-scrollbar p-8">
                                        <div class="mb-8 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-500/10 text-orange-500">
                                                <span class="material-symbols-outlined text-[20px]">data_object</span>
                                            </div>
                                            <div>
                                                <span class="text-[11px] font-black uppercase tracking-widest text-slate-500">Variables</span>
                                                <p class="text-[10px] text-slate-400">Map placeholders to values</p>
                                            </div>
                                        </div>

                                        <div class="space-y-4">
                                            @foreach($selectedTemplatePreview['variables'] as $variable)
                                                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900/50">
                                                    {{-- Header: Placeholder Tag & Title --}}
                                                    <div class="mb-4 flex items-center gap-2">
                                                        <span class="text-[10px] font-black text-primary uppercase bg-primary/10 px-2 py-0.5 rounded tracking-widest">#{{ $variable }}</span>
                                                        <h5 class="text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-tighter opacity-80">Placeholder {{ $variable }}</h5>
                                                    </div>

                                                    {{-- Mapping Selector: On its own row --}}
                                                    <div class="mb-4">
                                                        <div class="flex w-full rounded-2xl bg-slate-100 p-1 dark:bg-slate-800/80">
                                                            <button 
                                                                type="button"
                                                                wire:click="$set('templateVariables.{{ $variable }}.type', 'system')"
                                                                class="flex-1 rounded-xl py-2 text-[10px] font-black uppercase tracking-widest transition-all {{ ($templateVariables[$variable]['type'] ?? 'system') === 'system' ? 'bg-white text-primary shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-400 hover:text-slate-600' }}"
                                                            >
                                                                System Variable
                                                            </button>
                                                            <button 
                                                                type="button"
                                                                wire:click="$set('templateVariables.{{ $variable }}.type', 'manual')"
                                                                class="flex-1 rounded-xl py-2 text-[10px] font-black uppercase tracking-widest transition-all {{ ($templateVariables[$variable]['type'] ?? '') === 'manual' ? 'bg-white text-primary shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-400 hover:text-slate-600' }}"
                                                            >
                                                                Manual Input
                                                            </button>
                                                        </div>
                                                    </div>

                                                    @if(($templateVariables[$variable]['type'] ?? 'system') === 'system')
                                                        <div class="relative group">
                                                            <select 
                                                                wire:model.live="templateVariables.{{ $variable }}.value"
                                                                class="w-full appearance-none rounded-xl border-none bg-slate-50 border border-slate-100 px-4 py-3 text-[11px] font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 dark:bg-slate-950/50 dark:border-slate-800 dark:text-white"
                                                            >
                                                                @foreach($systemVariableOptions as $option)
                                                                    <option value="{{ $option['key'] }}">{{ $option['label'] }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                                                                <span class="material-symbols-outlined text-[18px]">expand_more</span>
                                                            </div>
                                                        </div>
                                                        @php
                                                            $currentKey = $templateVariables[$variable]['value'] ?? '';
                                                            $currentOption = collect($systemVariableOptions)->firstWhere('key', $currentKey);
                                                        @endphp
                                                        @if($currentOption)
                                                            <div class="mt-2.5 flex items-center gap-1.5 px-1 opacity-60">
                                                                <span class="material-symbols-outlined text-[12px] text-primary">description</span>
                                                                <p class="text-[9px] font-medium text-slate-400 leading-tight">{{ $currentOption['description'] }}</p>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <input 
                                                            wire:model.live.debounce.250ms="templateVariables.{{ $variable }}.value"
                                                            type="text"
                                                            placeholder="Type value..."
                                                            class="w-full rounded-xl border-none bg-slate-50/50 px-4 py-3 text-[11px] font-bold text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 dark:bg-slate-950/50 dark:text-white"
                                                        />
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="flex flex-1 flex-col items-center justify-center p-12 text-center">
                                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 dark:bg-slate-900/50 opacity-40">
                                            <span class="material-symbols-outlined text-2xl text-slate-300">auto_awesome_motion</span>
                                        </div>
                                        <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Static Content</h4>
                                        <p class="mt-1 text-[11px] text-slate-400 max-w-[150px]">No dynamic variables required for this template.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="flex flex-1 flex-col items-center justify-center p-12 text-center">
                            <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-[2.5rem] bg-slate-50 dark:bg-slate-900/50">
                                <span class="material-symbols-outlined text-4xl text-slate-200">chat_bubble</span>
                            </div>
                            <h3 class="text-xs font-bold text-slate-400 tracking-tight uppercase">Select a template</h3>
                            <p class="mt-1 text-[11px] text-slate-400">Choose from the list to begin configuration.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between border-t border-slate-100 bg-white/80 px-8 py-6 backdrop-blur-xl dark:border-slate-800/50 dark:bg-[#0B0F1A]/80">
                <div class="hidden sm:block">
                    @if($selectedTemplatePreview)
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary text-xl">check_circle</span>
                            <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $selectedTemplatePreview['name'] }} ready to send</span>
                        </div>
                    @else
                        <span class="text-xs font-bold text-slate-400 italic">Please select a template</span>
                    @endif
                </div>

                <div class="flex w-full items-center gap-3 sm:w-auto">
                    <button
                        type="button"
                        wire:click="closeTemplateSendModal"
                        class="flex-1 rounded-2xl py-4 px-8 text-xs font-black uppercase tracking-widest text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-white sm:flex-none"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        wire:click="sendSelectedTemplate"
                        wire:loading.attr="disabled"
                        @disabled(!$selectedTemplateId)
                        class="flex flex-[1.5] items-center justify-center gap-2 rounded-2xl bg-primary px-10 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-primary/30 transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 sm:flex-none"
                    >
                        <span wire:loading.remove wire:target="sendSelectedTemplate">Send Now</span>
                        <span wire:loading wire:target="sendSelectedTemplate">Sending...</span>
                        <span class="material-symbols-outlined text-lg">send</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
