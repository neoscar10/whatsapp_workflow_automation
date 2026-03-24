@if($showTemplateModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        {{-- Backdrop --}}
        <div 
            class="absolute inset-0 bg-slate-900/40 backdrop-blur-md"
            wire:click="closeTemplateSendModal"
        ></div>

        {{-- Modal Content --}}
        <div class="relative flex h-[80vh] w-full max-w-6xl flex-col overflow-hidden rounded-[2.5rem] border border-white/20 bg-white shadow-2xl dark:border-slate-800/50 dark:bg-[#0B0F1A]">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-100 bg-white/80 px-8 py-6 backdrop-blur-xl dark:border-slate-800/50 dark:bg-[#0B0F1A]/80">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Select Template</h2>
                    <p class="text-[11px] font-medium text-slate-400 uppercase tracking-tighter">Choose a pre-approved message</p>
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
                <div class="flex w-full flex-col border-r border-slate-100 dark:border-slate-800/50 md:w-[40%] xl:w-[35%]">
                    <div class="space-y-6 p-8">
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
                            <button
                                type="button"
                                wire:click="$set('templateFilter', 'all')"
                                class="whitespace-nowrap rounded-full px-5 py-2 text-[11px] font-black uppercase tracking-widest transition-all {{ $templateFilter === 'all' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400' }}"
                            >
                                All
                            </button>
                            <button
                                type="button"
                                wire:click="$set('templateFilter', 'approved')"
                                class="whitespace-nowrap rounded-full px-5 py-2 text-[11px] font-black uppercase tracking-widest transition-all {{ $templateFilter === 'approved' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400' }}"
                            >
                                Approved
                            </button>
                            <button
                                type="button"
                                wire:click="$set('templateFilter', 'recent')"
                                class="whitespace-nowrap rounded-full px-5 py-2 text-[11px] font-black uppercase tracking-widest transition-all {{ $templateFilter === 'recent' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400' }}"
                            >
                                Recent
                            </button>
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

                {{-- Right Pane: Preview --}}
                <div class="hidden flex-1 flex-col overflow-hidden bg-[#F8FAFC] dark:bg-slate-950/30 md:flex">
                    @if($selectedTemplatePreview)
                        <div class="flex flex-1 flex-col overflow-y-auto custom-scrollbar p-12">
                            <div class="mx-auto w-full max-w-md">
                                <div class="mb-8 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                                        </div>
                                        <span class="text-[11px] font-black uppercase tracking-widest text-slate-500">Live Preview</span>
                                    </div>
                                    <div class="flex gap-1">
                                        <div class="h-1.5 w-1.5 rounded-full bg-slate-300"></div>
                                        <div class="h-1.5 w-4 rounded-full bg-primary"></div>
                                        <div class="h-1.5 w-1.5 rounded-full bg-slate-300"></div>
                                    </div>
                                </div>

                                {{-- Fake Chat Bubble --}}
                                <div class="relative rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 message-inbound">
                                    {{-- Internal Preview Header --}}
                                    <div class="mb-4 flex items-center gap-3 border-b border-slate-50 pb-4 dark:border-slate-800">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-primary dark:bg-slate-800">
                                            <span class="material-symbols-outlined text-sm">smart_toy</span>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Template Preview</p>
                                            <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $selectedTemplatePreview['name'] }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        @foreach($selectedTemplatePreview['preview_paragraphs'] ?? [] as $paragraph)
                                            <p class="text-[14px] leading-relaxed text-slate-700 dark:text-slate-300">{!! $paragraph !!}</p>
                                        @endforeach
                                    </div>

                                    @if(!empty($selectedTemplatePreview['button_text']))
                                        <div class="mt-6 pt-6 border-t border-slate-50 dark:border-slate-800">
                                            <button class="w-full rounded-2xl bg-primary py-3.5 text-xs font-bold text-white shadow-xl shadow-primary/20">
                                                {{ $selectedTemplatePreview['button_text'] }}
                                            </button>
                                        </div>
                                    @endif

                                    <div class="mt-4 flex items-center justify-end gap-1.5">
                                        <span class="text-[10px] font-medium text-slate-400">{{ $selectedTemplatePreview['time_label'] ?? now()->format('H:i A') }}</span>
                                    </div>
                                </div>

                                {{-- Variables Info --}}
                                @if(!empty($selectedTemplatePreview['variables']))
                                    <div class="mt-12 space-y-4">
                                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Dynamic Variables</h4>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($selectedTemplatePreview['variables'] as $variable)
                                                <div class="flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-[11px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                    <span class="material-symbols-outlined text-sm text-primary">data_object</span>
                                                    {{ $variable }}
                                                </div>
                                            @endforeach
                                        </div>
                                        <p class="text-[10px] font-medium leading-relaxed text-slate-400 italic">
                                            These placeholders are automatically populated with the relevant data before sending.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="flex flex-1 flex-col items-center justify-center p-12 text-center">
                            <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-[2rem] bg-slate-50 dark:bg-slate-900">
                                <span class="material-symbols-outlined text-4xl text-slate-200">chat_bubble</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-400">Select a template to preview</h3>
                            <p class="mt-1 text-xs text-slate-400">Visual feedback will appear here in real-time.</p>
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
                            <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $selectedTemplatePreview['name'] }} selected</span>
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
