@if($showTemplateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="flex h-[870px] w-full max-w-5xl flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900 lg:rounded-2xl">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Select Template</h2>
                <button
                    type="button"
                    wire:click="closeTemplateSendModal"
                    class="text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-slate-200"
                >
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            {{-- Content --}}
            <div class="flex flex-1 overflow-hidden">
                {{-- Left Pane --}}
                <div class="flex w-full flex-col border-r border-slate-200 dark:border-slate-800 md:w-[45%]">
                    <div class="space-y-4 p-4">
                        <div class="group relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-primary">
                                search
                            </span>
                            <input
                                wire:model.live.debounce.300ms="templateSearch"
                                type="text"
                                placeholder="Search templates..."
                                class="w-full rounded-xl border-none bg-slate-100 py-2.5 pl-10 pr-4 text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-primary/50 dark:bg-slate-800 dark:text-slate-100"
                            />
                        </div>

                        <div class="flex gap-2 overflow-x-auto pb-1">
                            <button
                                type="button"
                                wire:click="$set('templateFilter', 'all')"
                                class="rounded-full px-4 py-1.5 text-sm font-medium {{ $templateFilter === 'all' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }}"
                            >
                                All
                            </button>
                            <button
                                type="button"
                                wire:click="$set('templateFilter', 'approved')"
                                class="rounded-full px-4 py-1.5 text-sm font-medium {{ $templateFilter === 'approved' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }}"
                            >
                                Approved
                            </button>
                            <button
                                type="button"
                                wire:click="$set('templateFilter', 'recent')"
                                class="rounded-full px-4 py-1.5 text-sm font-medium {{ $templateFilter === 'recent' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }}"
                            >
                                Recent
                            </button>
                        </div>
                        @if($templateModalError)
                            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/20 dark:text-red-400">
                                {{ $templateModalError }}
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 space-y-2 overflow-y-auto px-4 pb-4">
                        @forelse($availableTemplates as $template)
                            <button
                                type="button"
                                wire:click="selectTemplate({{ $template['id'] }})"
                                class="flex w-full items-center gap-4 rounded-xl border bg-white p-4 text-left transition-all dark:bg-slate-900 {{ (int) $selectedTemplateId === (int) $template['id'] ? 'border-2 border-primary bg-primary/5' : 'border-slate-100 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50' }}"
                            >
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg {{ (int) $selectedTemplateId === (int) $template['id'] ? 'bg-primary/10 text-primary' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                    <span class="material-symbols-outlined">{{ $template['icon'] ?? 'description' }}</span>
                                </div>

                                <div class="flex-1">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $template['name'] }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $template['subtitle'] }}</p>
                                </div>

                                @if((int) $selectedTemplateId === (int) $template['id'])
                                    <span class="material-symbols-outlined text-primary">check_circle</span>
                                @endif
                            </button>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 px-4 py-10 text-center dark:border-slate-700">
                                <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No templates found</p>
                                <p class="mt-1 text-xs text-slate-400">Try another search or filter.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Right Pane --}}
                <div class="hidden flex-1 flex-col overflow-y-auto bg-slate-50 p-8 dark:bg-slate-950 md:flex">
                    @if($selectedTemplatePreview)
                        <div class="mx-auto w-full max-w-md">
                            <div class="mb-6 flex items-center gap-3">
                                <span class="material-symbols-outlined text-slate-400">visibility</span>
                                <span class="text-sm font-bold uppercase tracking-wider text-slate-500">Live Preview</span>
                            </div>

                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">B</div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-900 dark:text-slate-100">Bot Assistant</p>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Always active</p>
                                    </div>
                                </div>

                                <div class="space-y-4 p-6">
                                    <div class="space-y-3">
                                        @foreach($selectedTemplatePreview['preview_paragraphs'] ?? [] as $paragraph)
                                            <p class="text-sm leading-relaxed text-slate-800 dark:text-slate-200">{!! $paragraph !!}</p>
                                        @endforeach
                                    </div>

                                    @if(!empty($selectedTemplatePreview['button_text']))
                                        <div class="pt-2">
                                            <button class="w-full rounded-lg bg-primary/10 py-2.5 text-sm font-semibold text-primary transition-all hover:bg-primary hover:text-white">
                                                {{ $selectedTemplatePreview['button_text'] }}
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                <div class="bg-slate-50 px-4 py-2 text-right text-[10px] text-slate-400 dark:bg-slate-800/30">
                                    {{ $selectedTemplatePreview['time_label'] ?? '10:45 AM' }}
                                </div>
                            </div>

                            <div class="mt-8 space-y-4">
                                <div>
                                    <h4 class="mb-2 text-xs font-bold uppercase text-slate-400">Variables</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($selectedTemplatePreview['variables'] ?? [] as $variable)
                                            <span class="rounded bg-slate-200 px-2 py-1 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                {{ $variable }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400">No variables</span>
                                        @endforelse
                                    </div>
                                </div>

                                <div>
                                    <h4 class="mb-2 text-xs font-bold uppercase text-slate-400">Category</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">
                                        {{ $selectedTemplatePreview['category_label'] ?? 'Uncategorized' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-1 items-center justify-center text-sm text-slate-400">
                            Select a template to preview.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between border-t border-slate-200 bg-white px-6 py-4 dark:border-slate-800 dark:bg-slate-900">
                <p class="hidden text-sm text-slate-500 dark:text-slate-400 sm:block">
                    Selected:
                    <span class="font-semibold text-slate-900 dark:text-slate-100">
                        {{ $selectedTemplatePreview['name'] ?? 'None' }}
                    </span>
                </p>

                <div class="flex w-full items-center gap-3 sm:w-auto">
                    <button
                        type="button"
                        wire:click="closeTemplateSendModal"
                        class="flex-1 rounded-xl border border-slate-200 px-6 py-2.5 font-medium text-slate-600 transition-colors hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800 sm:flex-none"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        wire:click="sendSelectedTemplate"
                        wire:loading.attr="disabled"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary/90 sm:flex-none"
                    >
                        <span wire:loading.remove wire:target="sendSelectedTemplate">Send Template</span>
                        <span wire:loading wire:target="sendSelectedTemplate">Sending...</span>
                        <span class="material-symbols-outlined text-[20px]">send</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
