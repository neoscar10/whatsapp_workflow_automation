<div class="flex flex-col h-full bg-slate-50 dark:bg-slate-900/50 overflow-y-auto">
    <div class="max-w-5xl mx-auto w-full px-8 py-8 space-y-8 pb-32">
        {{-- Stepper --}}
        <div class="relative flex items-center justify-between">
            @foreach(['Details', 'Audience', 'Content', 'Review'] as $index => $label)
                @php $currentStep = $index + 1; @endphp
                <div class="flex flex-col items-center gap-2 relative z-10">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 transition-all {{ $step >= $currentStep ? 'border-primary bg-primary text-white shadow-lg shadow-primary/30' : 'border-slate-200 bg-white text-slate-400 dark:border-slate-800 dark:bg-slate-900' }}">
                        @if($step > $currentStep)
                            <span class="material-symbols-outlined text-xl">check</span>
                        @else
                            <span class="text-sm font-bold">{{ $currentStep }}</span>
                        @endif
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider {{ $step >= $currentStep ? 'text-primary' : 'text-slate-400' }}">{{ $label }}</span>
                </div>
                @if($index < 3)
                    <div class="absolute top-5 h-[2px] w-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full bg-primary transition-all duration-500" style="width: {{ (($step - 1) / 3) * 100 }}%"></div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Step 1: Details --}}
        @if($step === 1)
            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900 animate-in fade-in slide-in-from-bottom-4">
                <div class="space-y-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Campaign Details</h2>
                        <p class="text-slate-500 dark:text-slate-400">Basic information about your campaign.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Campaign Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" placeholder="e.g., Summer Sale Blast" class="w-full rounded-xl border-slate-200 bg-slate-50 transition-all focus:border-primary focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @error('name') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300">WhatsApp Phone Number <span class="text-rose-500">*</span></label>
                            <select wire:model="whatsapp_phone_number_id" class="w-full rounded-xl border-slate-200 bg-slate-50 transition-all focus:border-primary focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                @foreach($phoneNumbers as $number)
                                    <option value="{{ $number->id }}">{{ $number->display_phone_number }} ({{ $number->verified_name }})</option>
                                @endforeach
                            </select>
                            @error('whatsapp_phone_number_id') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Description</label>
                            <textarea wire:model="description" rows="3" placeholder="What is this campaign about?" class="w-full rounded-xl border-slate-200 bg-slate-50 transition-all focus:border-primary focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Campaign Type</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" wire:click="$set('type', 'template')" class="flex flex-col items-center gap-2 rounded-2xl border-2 p-4 transition-all {{ $type === 'template' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-100 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-800/50' }}">
                                    <span class="material-symbols-outlined text-3xl">article</span>
                                    <span class="text-xs font-bold uppercase tracking-wider">Template</span>
                                </button>
                                <button type="button" wire:click="$set('type', 'text')" class="flex flex-col items-center gap-2 rounded-2xl border-2 p-4 transition-all {{ $type === 'text' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-100 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-800/50' }}">
                                    <span class="material-symbols-outlined text-3xl">notes</span>
                                    <span class="text-xs font-bold uppercase tracking-wider">Text</span>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Sending Mode</label>
                            <select wire:model.live="send_mode" class="w-full rounded-xl border-slate-200 bg-slate-50 transition-all focus:border-primary focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="draft">Save as Draft</option>
                                <option value="now">Send Now</option>
                                <option value="schedule">Schedule for Later</option>
                            </select>
                        </div>

                        @if($send_mode === 'schedule')
                            <div class="space-y-2 animate-in fade-in slide-in-from-top-2">
                                <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Scheduled Date & Time</label>
                                <input type="datetime-local" wire:model="scheduled_at" class="w-full rounded-xl border-slate-200 bg-slate-50 transition-all focus:border-primary focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                @error('scheduled_at') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 2: Audience --}}
        @if($step === 2)
            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900 animate-in fade-in slide-in-from-bottom-4">
                <div class="space-y-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Select Audience</h2>
                        <p class="text-slate-500 dark:text-slate-400">Choose who will receive this campaign.</p>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        @foreach([
                            ['key' => 'selected_contacts', 'label' => 'Select Contacts', 'icon' => 'person'],
                            ['key' => 'groups', 'label' => 'By Groups', 'icon' => 'group'],
                            ['key' => 'filters', 'label' => 'By Filters', 'icon' => 'filter_alt'],
                            ['key' => 'csv', 'label' => 'Import CSV', 'icon' => 'upload_file'],
                        ] as $opt)
                            <button type="button" wire:click="$set('audience_type', '{{ $opt['key'] }}')" class="flex flex-1 min-w-[140px] flex-col items-center gap-2 rounded-2xl border-2 p-4 transition-all {{ $audience_type === $opt['key'] ? 'border-primary bg-primary/5 text-primary' : 'border-slate-100 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-2xl">{{ $opt['icon'] }}</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider">{{ $opt['label'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="min-h-[200px] rounded-2xl border border-slate-100 bg-slate-50/50 p-6 dark:border-slate-800 dark:bg-slate-800/30">
                        @if($audience_type === 'selected_contacts')
                            <p class="text-sm text-slate-500 italic">Select individual contacts from your database (Feature coming soon - multi-select from table).</p>

                        @elseif($audience_type === 'groups')
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                                @foreach($groups as $group)
                                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 cursor-pointer transition-all hover:border-primary dark:border-slate-700 dark:bg-slate-800">
                                        <input type="checkbox" wire:model="selected_group_ids" value="{{ $group->id }}" class="rounded text-primary focus:ring-primary">
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $group->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif($audience_type === 'filters')
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Contact Source</label>
                                    <select wire:model="audience_filters.source" class="w-full rounded-xl border-slate-200 text-sm">
                                        <option value="">All Sources</option>
                                        <option value="manual">Manual</option>
                                        <option value="imported">Imported</option>
                                        <option value="webhook">Webhook</option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Status</label>
                                    <select wire:model="audience_filters.status" class="w-full rounded-xl border-slate-200 text-sm">
                                        <option value="">All Statuses</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="lead">Lead</option>
                                    </select>
                                </div>
                            </div>
                        @elseif($audience_type === 'csv')
                            <div class="flex flex-col items-center justify-center p-6 text-center">
                                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <span class="material-symbols-outlined text-3xl">cloud_upload</span>
                                </div>
                                <h3 class="mb-1 text-base font-bold text-slate-900 dark:text-white">Upload CSV File</h3>
                                <p class="mb-6 text-sm text-slate-500">Ensure your CSV has a "phone" column. Other columns will be used for personalization.</p>
                                
                                <input type="file" wire:model="csv_file" class="hidden" id="csv_upload">
                                <label for="csv_upload" class="cursor-pointer rounded-xl border-2 border-dashed border-primary/30 px-8 py-4 transition-all hover:bg-primary/5">
                                    <span class="text-sm font-bold text-primary">Select CSV File</span>
                                </label>

                                <button type="button" wire:click="downloadSampleCsv" class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:underline">
                                    <span class="material-symbols-outlined text-sm">download</span>
                                    Download Sample CSV Template
                                </button>
                                @error('csv_file') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror

                                @if($csv_file)
                                    <div class="mt-4 flex flex-col items-center">
                                        <p class="text-xs font-medium text-emerald-600">File selected: {{ $csv_file->getClientOriginalName() }}</p>
                                        <button type="button" wire:click="importCsv" class="mt-4 rounded-xl bg-slate-900 px-6 py-2 text-sm font-bold text-white transition-all hover:bg-slate-800">
                                            Process Import
                                        </button>
                                    </div>
                                @endif

                                @if($import_summary)
                                    <div class="mt-6 w-full rounded-2xl bg-white p-4 shadow-sm dark:bg-slate-900">
                                        <h4 class="mb-2 text-sm font-bold text-slate-900 dark:text-white">Import Summary</h4>
                                        <div class="grid grid-cols-3 gap-2 text-xs">
                                            <div class="p-2 bg-slate-50 rounded-lg">
                                                <p class="text-slate-500">Total</p>
                                                <p class="font-bold text-slate-900">{{ $import_summary['total'] }}</p>
                                            </div>
                                            <div class="p-2 bg-emerald-50 rounded-lg">
                                                <p class="text-emerald-600">Success</p>
                                                <p class="font-bold text-emerald-700">{{ $import_summary['success'] }}</p>
                                            </div>
                                            <div class="p-2 bg-rose-50 rounded-lg">
                                                <p class="text-rose-600">Failed</p>
                                                <p class="font-bold text-rose-700">{{ $import_summary['failed'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 3: Content --}}
        @if($step === 3)
            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900 animate-in fade-in slide-in-from-bottom-4">
                <div class="space-y-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Message Content</h2>
                        <p class="text-slate-500 dark:text-slate-400">Design the message that will be sent to your audience.</p>
                    </div>

                    @if($type === 'template')
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Select Template <span class="text-rose-500">*</span></label>
                                <select wire:model.live="whatsapp_template_id" class="w-full rounded-xl border-slate-200 bg-slate-50 transition-all focus:border-primary focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                    <option value="">-- Choose Approved Template --</option>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->id }}">{{ $tpl->display_title ?? $tpl->remote_template_name }} ({{ $tpl->language_code }})</option>
                                    @endforeach
                                </select>
                                @error('whatsapp_template_id') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>

                            @if($whatsapp_template_id)
                                @php $selectedTemplate = $templates->find($whatsapp_template_id); @endphp
                                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                                    {{-- Variable Mapping --}}
                                    <div class="space-y-6">
                                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Variable Mapping</h3>
                                        
                                        {{-- Body Variables --}}
                                        <div class="space-y-4">
                                            <p class="text-xs font-bold text-slate-500">Body Components</p>
                                            {{-- Simple regex extraction or service logic needed here --}}
                                            {{-- For now, let's assume we allow up to 3 body variables if template has placeholders --}}
                                            @foreach(range(1, 3) as $idx)
                                                <div class="flex items-center gap-3">
                                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-slate-100 text-[10px] font-bold text-slate-500">{{ $idx }}</span>
                                                    <select wire:model="template_variable_mapping.body.{{ $idx }}.source" class="flex-1 rounded-lg border-slate-200 text-xs">
                                                        @foreach($personalizationFields as $field)
                                                            <option value="{{ $field['key'] }}">{{ $field['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    @if(($template_variable_mapping['body'][$idx]['source'] ?? '') === 'static')
                                                        <input type="text" wire:model="template_variable_mapping.body.{{ $idx }}.value" placeholder="Value" class="flex-1 rounded-lg border-slate-200 text-xs">
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Template Preview --}}
                                    <div class="space-y-4">
                                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Preview</h3>
                                        <div class="rounded-2xl bg-slate-50 p-6 dark:bg-slate-800/50">
                                            <div class="max-w-[280px] mx-auto rounded-2xl bg-white p-3 shadow-sm dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                                                @if($selectedTemplate->header_type !== 'none')
                                                    <div class="mb-2 h-32 rounded-xl bg-slate-100 flex items-center justify-center text-slate-300">
                                                        <span class="material-symbols-outlined text-3xl">image</span>
                                                    </div>
                                                @endif
                                                <p class="text-sm text-slate-800 dark:text-slate-200 whitespace-pre-wrap">{{ $selectedTemplate->body_text }}</p>
                                                @if($selectedTemplate->footer_text)
                                                    <p class="mt-2 text-[10px] text-slate-400">{{ $selectedTemplate->footer_text }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="space-y-6">
                            <div class="rounded-xl bg-amber-50 p-4 border border-amber-200 dark:bg-amber-900/20 dark:border-amber-800">
                                <div class="flex gap-3">
                                    <span class="material-symbols-outlined text-amber-600">warning</span>
                                    <p class="text-sm text-amber-800 dark:text-amber-400">
                                        <strong>Recommendation:</strong> Free-form text campaigns are restricted to WhatsApp's 24-hour service window. Use <strong>Approved Templates</strong> for reliable broadcasts.
                                    </p>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Message Body <span class="text-rose-500">*</span></label>
                                <textarea wire:model="message_body" rows="6" placeholder="Type your campaign message here..." class="w-full rounded-xl border-slate-200 bg-slate-50 transition-all focus:border-primary focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                                @error('message_body') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Step 4: Review --}}
        @if($step === 4)
            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900 animate-in fade-in slide-in-from-bottom-4">
                <div class="space-y-8">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Review & Confirm</h2>
                        <p class="text-slate-500 dark:text-slate-400">Verify everything before starting your campaign.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div class="space-y-6">
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-slate-400 uppercase">Campaign Name</p>
                                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $name }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-slate-400 uppercase">Audience</p>
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">groups</span>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                        {{ ucfirst(str_replace('_', ' ', $audience_type)) }}
                                    </p>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-slate-400 uppercase">Send Mode</p>
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">{{ $send_mode === 'schedule' ? 'event' : ($send_mode === 'now' ? 'send' : 'draft') }}</span>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                        {{ $send_mode === 'schedule' ? 'Scheduled for ' . $scheduled_at : ($send_mode === 'now' ? 'Send Immediately' : 'Save as Draft') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-6 dark:bg-slate-800/50">
                             <p class="mb-4 text-xs font-bold text-slate-400 uppercase">Message Snapshot</p>
                             <div class="max-w-[280px] mx-auto rounded-2xl bg-white p-3 shadow-sm dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                                @if($type === 'template')
                                    @php $tpl = $templates->find($whatsapp_template_id); @endphp
                                    <p class="text-xs font-bold text-primary mb-1">Template: {{ $tpl?->remote_template_name }}</p>
                                    <p class="text-sm text-slate-800 dark:text-slate-200">{{ $tpl?->body_text }}</p>
                                @else
                                    <p class="text-sm text-slate-800 dark:text-slate-200">{{ $message_body }}</p>
                                @endif
                             </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-primary/20 bg-primary/5 p-4 flex gap-4 items-center">
                        <span class="material-symbols-outlined text-primary text-3xl">info</span>
                        <div>
                            <p class="text-sm font-bold text-primary">Ready to go?</p>
                            <p class="text-xs text-primary/70">Your campaign will be processed through the background queue. You can track progress in the Reports section.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Controls --}}
        <div class="fixed bottom-0 left-0 right-0 z-50 border-t border-slate-200 bg-white/80 p-4 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/80">
            <div class="max-w-5xl mx-auto flex items-center justify-between">
                <button type="button" wire:click="{{ $step === 1 ? 'window.history.back()' : 'prevStep' }}" class="inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-bold text-slate-600 transition-all hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    {{ $step === 1 ? 'Cancel' : 'Back' }}
                </button>
                
                @if($step < 4)
                    <button type="button" wire:click="nextStep" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-8 py-2.5 text-sm font-bold text-white shadow-lg shadow-slate-900/20 transition-all hover:scale-[1.02] active:scale-[0.98] dark:bg-primary dark:shadow-primary/30">
                        Next Step
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>
                @else
                    <button type="button" wire:click="finish" class="inline-flex items-center gap-2 rounded-xl bg-primary px-10 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary/30 transition-all hover:scale-[1.02] active:scale-[0.98]">
                        {{ $send_mode === 'now' ? 'Start Campaign' : ($send_mode === 'schedule' ? 'Schedule Campaign' : 'Save Draft') }}
                        <span class="material-symbols-outlined text-lg">rocket_launch</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
