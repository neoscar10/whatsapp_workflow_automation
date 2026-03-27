@if($showTemplateModal)
    <div class="fixed inset-0 z-[100] p-4">
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-slate-900/50 backdrop-blur-md"
            wire:click="closeTemplateSendModal"
        ></div>

        {{-- Modal --}}
        <div class="relative mx-auto flex h-[85vh] max-h-[85vh] w-full max-w-6xl flex-col overflow-hidden rounded-[2.5rem] border border-white/10 bg-white shadow-2xl dark:border-slate-800/60 dark:bg-[#0B0F1A]">
            {{-- Header --}}
            <div class="shrink-0 border-b border-slate-100 bg-white/80 px-8 py-6 backdrop-blur-xl dark:border-slate-800/50 dark:bg-[#0B0F1A]/80">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Select Template</h2>
                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            Choose a pre-approved message with dynamic data
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="closeTemplateSendModal"
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition-all hover:bg-slate-200 hover:text-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
                    >
                        <span class="material-symbols-outlined text-[22px]">close</span>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="flex min-h-0 flex-1 overflow-hidden">
                {{-- Left Pane --}}
                <div class="flex w-full shrink-0 flex-col border-r border-slate-100 dark:border-slate-800/50 md:w-[34%] xl:w-[30%]">
                    <div class="shrink-0 space-y-6 p-8 pb-5">
                        {{-- Search --}}
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-primary">
                                search
                            </span>
                            <input
                                wire:model.live.debounce.300ms="templateSearch"
                                type="text"
                                placeholder="Search templates..."
                                class="w-full rounded-full border-none bg-slate-100/70 py-4 pl-12 pr-4 text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-primary/30 dark:bg-slate-800/60 dark:text-white"
                            />
                        </div>

                        {{-- Filters --}}
                        <div class="flex gap-2 overflow-x-auto pb-1 no-scrollbar">
                            @foreach(['all' => 'All', 'approved' => 'Approved', 'recent' => 'Recent'] as $key => $label)
                                <button
                                    type="button"
                                    wire:click="$set('templateFilter', '{{ $key }}')"
                                    class="whitespace-nowrap rounded-full px-5 py-2.5 text-[11px] font-black uppercase tracking-widest transition-all {{ $templateFilter === $key ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }}"
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

                    {{-- Template List --}}
                    <div class="custom-scrollbar min-h-0 flex-1 space-y-3 overflow-y-auto px-8 pb-8">
                        @forelse($availableTemplates as $template)
                            <button
                                type="button"
                                wire:click="selectTemplate({{ $template['id'] }})"
                                class="group relative flex w-full items-center gap-4 rounded-2xl border border-transparent p-4 text-left transition-all {{ (int) $selectedTemplateId === (int) $template['id'] ? 'border-primary/20 bg-slate-50 dark:bg-slate-800/50' : 'hover:bg-slate-50 dark:hover:bg-slate-800/30' }}"
                            >
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl shadow-sm transition-all {{ (int) $selectedTemplateId === (int) $template['id'] ? 'scale-105 bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-300' }}">
                                    <span class="material-symbols-outlined text-[24px]">{{ $template['icon'] ?? 'description' }}</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-base font-bold text-slate-900 dark:text-white">{{ $template['name'] }}</p>
                                    <p class="truncate text-sm font-medium text-slate-400">{{ $template['subtitle'] }}</p>
                                </div>

                                @if((int) $selectedTemplateId === (int) $template['id'])
                                    <span class="material-symbols-outlined text-primary">check_circle</span>
                                @endif
                            </button>
                        @empty
                            <div class="flex h-full min-h-[220px] flex-col items-center justify-center py-12 text-center">
                                <span class="material-symbols-outlined mb-4 text-5xl text-slate-200 dark:text-slate-700">search_off</span>
                                <p class="text-sm font-bold text-slate-600 dark:text-slate-400">No templates found</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Right Pane --}}
                <div class="hidden min-h-0 flex-1 overflow-hidden bg-[#F8FAFC] dark:bg-slate-950/30 md:flex">
                    @if($selectedTemplatePreview)
                        @php
                            $hType = $selectedTemplatePreview['header_type'] ?? 'none';
                            $hasMediaHeader = in_array($hType, ['image', 'video', 'document']);
                            $hasVariables = !empty($selectedTemplatePreview['variables']);
                            $hasConfigurationPanel = $hasMediaHeader || $hasVariables;
                        @endphp

                        <div
                            wire:key="template-preview-shell-{{ $selectedTemplateId }}-{{ $hasConfigurationPanel ? 'with-config' : 'without-config' }}"
                            class="grid min-h-0 flex-1 overflow-hidden {{ $hasConfigurationPanel ? 'grid-cols-[minmax(0,1fr)_360px] xl:grid-cols-[minmax(0,1.05fr)_400px]' : 'grid-cols-1' }}"
                        >
                            {{-- Preview Column --}}
                            <div class="min-h-0 overflow-hidden {{ $hasConfigurationPanel ? 'border-r border-slate-100 dark:border-slate-800/50' : '' }} bg-white/50 backdrop-blur-sm dark:bg-slate-900/40">
                                <div class="custom-scrollbar h-full overflow-y-auto p-8 lg:p-10">
                                    <div class="mb-8 flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary/10 text-primary">
                                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                                        </div>
                                        <div>
                                            <span class="text-[11px] font-black uppercase tracking-widest text-slate-500">Live Preview</span>
                                        </div>
                                    </div>

                                    <div class="mx-auto w-full max-w-[380px]">
                                        <div class="message-inbound rounded-[2rem] bg-white p-8 shadow-2xl dark:bg-slate-900">
                                            <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-5 dark:border-slate-800">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-primary dark:bg-slate-800">
                                                    <span class="material-symbols-outlined text-[18px]">smart_toy</span>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Template Preview</p>
                                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">
                                                        {{ $selectedTemplatePreview['name'] }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="space-y-5">
                                                @if(!empty($selectedTemplatePreview['preview_header']))
                                                    <div class="border-b border-slate-100 pb-4 text-[15px] font-black text-slate-800 dark:border-slate-800 dark:text-white">
                                                        {!! $selectedTemplatePreview['preview_header'] !!}
                                                    </div>
                                                @endif

                                                @foreach($selectedTemplatePreview['preview_paragraphs'] ?? [] as $paragraph)
                                                    <p class="text-[15px] leading-relaxed text-slate-700 dark:text-slate-300">
                                                        {!! $paragraph !!}
                                                    </p>
                                                @endforeach
                                            </div>

                                            @if(!empty($selectedTemplatePreview['button_text']))
                                                <div class="mt-8 border-t border-slate-100 pt-6 dark:border-slate-800">
                                                    <button
                                                        type="button"
                                                        class="w-full cursor-default rounded-2xl bg-primary py-4 text-[11px] font-black uppercase tracking-widest text-white shadow-xl shadow-primary/20"
                                                    >
                                                        {{ $selectedTemplatePreview['button_text'] }}
                                                    </button>
                                                </div>
                                            @endif

                                            <div class="mt-4 flex items-center justify-end gap-1.5">
                                                <span class="text-[10px] font-medium tracking-tighter text-slate-400">
                                                    {{ $selectedTemplatePreview['time_label'] ?? now()->format('H:i A') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mt-8 rounded-2xl border border-dashed border-slate-200 p-5 dark:border-slate-800">
                                            <h5 class="mb-3 flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                                <span class="material-symbols-outlined text-sm">info</span>
                                                Template Attributes
                                            </h5>

                                            <div class="flex flex-wrap gap-2">
                                                <span class="rounded-full bg-slate-100 px-3 py-1 text-[9px] font-bold uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                                    {{ $selectedTemplatePreview['category_label'] }}
                                                </span>
                                                <span class="rounded-full bg-slate-100 px-3 py-1 text-[9px] font-bold uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                                    Lang: EN
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Configuration Sidebar --}}
                            @if($hasConfigurationPanel)
                                <div class="min-h-0 overflow-hidden bg-slate-50/40 dark:bg-slate-950/20">
                                    <div class="custom-scrollbar h-full overflow-y-auto p-8">
                                        @if($hasMediaHeader)
                                            <div class="{{ $hasVariables ? 'mb-10' : '' }}">
                                                <div class="mb-6 flex items-center gap-3">
                                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                                                        <span class="material-symbols-outlined text-[20px]">
                                                            {{ $hType === 'image' ? 'image' : ($hType === 'video' ? 'movie' : 'description') }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="text-[11px] font-black uppercase tracking-widest text-slate-500">Header Media</span>
                                                        <p class="text-[10px] text-slate-400">Upload required {{ $hType }}</p>
                                                    </div>
                                                </div>

                                                <div class="space-y-4">
                                                    <div class="flex items-center justify-center w-full">
                                                        <label
                                                            for="templateHeaderMedia"
                                                            class="group flex h-32 w-full cursor-pointer flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-200 bg-white/60 transition-all hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900/30 dark:hover:bg-slate-900/50"
                                                        >
                                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                                <div class="mb-2 rounded-xl bg-white p-2 shadow-sm transition-transform group-hover:scale-110 dark:bg-slate-800">
                                                                    <span class="material-symbols-outlined text-[24px] text-primary">cloud_upload</span>
                                                                </div>
                                                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                                                                    <span class="text-primary">Click to upload</span> {{ $hType }}
                                                                </p>
                                                            </div>

                                                            <input
                                                                wire:model="templateHeaderMedia"
                                                                id="templateHeaderMedia"
                                                                type="file"
                                                                class="hidden"
                                                                @if($hType === 'image') accept="image/*"
                                                                @elseif($hType === 'video') accept="video/*"
                                                                @elseif($hType === 'document') accept=".pdf,.doc,.docx" @endif
                                                            />
                                                        </label>
                                                    </div>

                                                    <div wire:loading wire:target="templateHeaderMedia" class="w-full">
                                                        <div class="rounded-2xl border border-slate-100 bg-white p-3 dark:border-slate-800 dark:bg-slate-900/30">
                                                            <div class="flex items-center gap-2">
                                                                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                                                    <div class="h-full w-full animate-pulse bg-primary"></div>
                                                                </div>
                                                                <span class="shrink-0 text-[9px] font-black uppercase text-slate-500">Processing</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @if($templateHeaderMedia && !$errors->has('templateHeaderMedia'))
                                                        <div class="animate-in slide-in-from-bottom-2 fade-in flex items-center justify-between rounded-3xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                                            <div class="flex items-center gap-4 overflow-hidden">
                                                                @if($hType === 'image' && !is_string($templateHeaderMedia))
                                                                    <img
                                                                        src="{{ $templateHeaderMedia->temporaryUrl() }}"
                                                                        class="h-10 w-10 rounded-xl object-cover ring-2 ring-primary/10"
                                                                        alt="Header media preview"
                                                                    >
                                                                @else
                                                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-primary dark:bg-slate-800">
                                                                        <span class="material-symbols-outlined text-[20px]">
                                                                            {{ $hType === 'video' ? 'movie' : 'description' }}
                                                                        </span>
                                                                    </div>
                                                                @endif

                                                                <div class="min-w-0">
                                                                    <p class="truncate text-[10px] font-black uppercase tracking-tight text-slate-700 dark:text-slate-200">
                                                                        {{ is_string($templateHeaderMedia) ? (str_contains($templateHeaderMedia, '/') ? basename($templateHeaderMedia) : $templateHeaderMedia) : $templateHeaderMedia->getClientOriginalName() }}
                                                                    </p>
                                                                    <p class="text-[9px] font-bold uppercase tracking-widest text-primary/80">Ready to send</p>
                                                                </div>
                                                            </div>

                                                            <button
                                                                type="button"
                                                                wire:click="$set('templateHeaderMedia', null)"
                                                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-50 text-slate-400 transition-all hover:bg-red-50 hover:text-red-500 dark:bg-slate-800/70"
                                                            >
                                                                <span class="material-symbols-outlined text-[18px]">close</span>
                                                            </button>
                                                        </div>
                                                    @endif

                                                    @error('templateHeaderMedia')
                                                        <p class="px-2 text-[10px] font-bold uppercase tracking-widest text-red-500">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endif

                                        @if($hasVariables)
                                            <div class="mb-6 flex items-center gap-3">
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
                                                    @php
                                                        $varKey = "{$variable['component']}:{$variable['name']}";
                                                        $varType = $templateVariables[$varKey]['type'] ?? 'system';
                                                    @endphp

                                                    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900/50">
                                                        <div class="mb-4 flex items-center justify-between gap-2">
                                                            <div class="flex items-center gap-2">
                                                                <span class="rounded bg-primary/10 px-2 py-0.5 text-[10px] font-black uppercase tracking-widest text-primary">
                                                                    #{{ $variable['name'] }}
                                                                </span>
                                                                <h5 class="text-[11px] font-black uppercase tracking-tight text-slate-700 opacity-80 dark:text-slate-300">
                                                                    Placeholder {{ $variable['name'] }}
                                                                </h5>
                                                            </div>

                                                            <span class="rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-widest {{ $variable['component'] === 'header' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' }}">
                                                                {{ $variable['component'] }}
                                                            </span>
                                                        </div>

                                                        <div class="mb-4 space-y-2">
                                                            <button
                                                                type="button"
                                                                wire:click="$set('templateVariables.{{ $varKey }}.type', 'system')"
                                                                class="flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $varType === 'system' ? 'border border-primary/20 bg-primary/20 text-primary' : 'border border-transparent bg-slate-100 text-slate-400 hover:bg-slate-200 dark:bg-slate-800/50' }}"
                                                            >
                                                                <span>System Variable</span>
                                                                @if($varType === 'system')
                                                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                                                @endif
                                                            </button>

                                                            <button
                                                                type="button"
                                                                wire:click="$set('templateVariables.{{ $varKey }}.type', 'manual')"
                                                                class="flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $varType === 'manual' ? 'border border-primary/20 bg-primary/20 text-primary' : 'border border-transparent bg-slate-100 text-slate-400 hover:bg-slate-200 dark:bg-slate-800/50' }}"
                                                            >
                                                                <span>Manual Input</span>
                                                                @if($varType === 'manual')
                                                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                                                @endif
                                                            </button>
                                                        </div>

                                                        @if($varType === 'system')
                                                            <div class="relative group">
                                                                <select
                                                                    wire:model.live="templateVariables.{{ $varKey }}.value"
                                                                    class="w-full appearance-none rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-[11px] font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 dark:border-slate-800 dark:bg-slate-950/50 dark:text-white"
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
                                                                $currentKey = $templateVariables[$varKey]['value'] ?? '';
                                                                $currentOption = collect($systemVariableOptions)->firstWhere('key', $currentKey);
                                                            @endphp

                                                            @if($currentOption)
                                                                <div class="mt-2.5 flex items-center gap-1.5 px-1 opacity-70">
                                                                    <span class="material-symbols-outlined text-[12px] text-primary">description</span>
                                                                    <p class="text-[9px] font-medium leading-tight text-slate-400">
                                                                        {{ $currentOption['description'] }}
                                                                    </p>
                                                                </div>
                                                            @endif
                                                        @else
                                                            <input
                                                                wire:model.live.debounce.250ms="templateVariables.{{ $varKey }}.value"
                                                                type="text"
                                                                placeholder="Type value..."
                                                                class="w-full rounded-xl border-none bg-slate-50/70 px-4 py-3 text-[11px] font-bold text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 dark:bg-slate-950/50 dark:text-white"
                                                            />
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="flex min-h-0 flex-1 items-center justify-center p-12">
                            <div class="text-center">
                                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-[2.5rem] bg-slate-50 dark:bg-slate-900/50">
                                    <span class="material-symbols-outlined text-4xl text-slate-200 dark:text-slate-700">chat_bubble</span>
                                </div>
                                <h3 class="text-xs font-bold uppercase tracking-tight text-slate-400">Select a template</h3>
                                <p class="mt-1 text-[11px] text-slate-400">Choose from the list to begin configuration.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div class="shrink-0 border-t border-slate-100 bg-white/80 px-8 py-6 backdrop-blur-xl dark:border-slate-800/50 dark:bg-[#0B0F1A]/80">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        @if($selectedTemplatePreview)
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined shrink-0 text-primary text-xl">check_circle</span>
                                <span class="truncate text-sm font-bold text-slate-900 dark:text-white">
                                    {{ $selectedTemplatePreview['name'] }} ready to send
                                </span>
                            </div>
                        @else
                            <span class="text-xs font-bold italic text-slate-400">Please select a template</span>
                        @endif
                    </div>

                    <div class="flex w-full items-center gap-3 sm:w-auto">
                        <button
                            type="button"
                            wire:click="closeTemplateSendModal"
                            class="flex-1 rounded-2xl px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-white sm:flex-none"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            wire:click="sendSelectedTemplate"
                            wire:loading.attr="disabled"
                            @disabled(!$selectedTemplateId)
                            class="flex flex-[1.35] items-center justify-center gap-2 rounded-2xl bg-primary px-10 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-primary/30 transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 sm:flex-none"
                        >
                            <span wire:loading.remove wire:target="sendSelectedTemplate">Send Now</span>
                            <span wire:loading wire:target="sendSelectedTemplate">Sending...</span>
                            <span class="material-symbols-outlined text-lg">send</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif 