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
                            ['key' => 'manual', 'label' => 'Manual Entry', 'icon' => 'edit_note'],
                        ] as $opt)
                            <button type="button" wire:click="$set('audience_type', '{{ $opt['key'] }}')" class="flex flex-1 min-w-[140px] flex-col items-center gap-2 rounded-2xl border-2 p-4 transition-all {{ $audience_type === $opt['key'] ? 'border-primary bg-primary/5 text-primary' : 'border-slate-100 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-2xl">{{ $opt['icon'] }}</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider">{{ $opt['label'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="min-h-[200px] rounded-2xl border border-slate-100 bg-slate-50/50 p-6 dark:border-slate-800 dark:bg-slate-800/30">
                        @if($audience_type === 'manual')
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">Manual Contact Entry</h4>
                                        <p class="text-xs text-slate-500">Enter phone numbers and optional contact names directly.</p>
                                    </div>
                                    <button type="button" wire:click="addManualRow" class="flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-primary rounded-xl hover:bg-primary/90 transition-all">
                                        <span class="material-symbols-outlined text-[16px]">add</span>
                                        Add Row
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    @foreach($manual_rows as $idx => $row)
                                        <div class="flex items-center gap-3 bg-white dark:bg-slate-900 p-3 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
                                            <span class="text-xs font-bold text-slate-400 w-6 text-center">#{{ $idx + 1 }}</span>
                                            <input type="text" wire:model="manual_rows.{{ $idx }}.phone" placeholder="Phone (e.g. +919876543210)" class="flex-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-xs dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20">
                                            <input type="text" wire:model="manual_rows.{{ $idx }}.name" placeholder="Full Name (Optional)" class="flex-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-xs dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20">
                                            @if(count($manual_rows) > 1)
                                                <button type="button" wire:click="removeManualRow({{ $idx }})" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @elseif($audience_type === 'selected_contacts')
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
                                    <span class="material-symbols-outlined text-3xl">upload_file</span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Upload Recipients CSV</h3>
                                <p class="text-xs text-slate-500 mb-4">Select a CSV file containing contact numbers and custom fields.</p>
                                
                                <input type="file" wire:model="csv_file" class="hidden" id="wizard_csv_upload">
                                <label for="wizard_csv_upload" class="cursor-pointer rounded-xl bg-slate-100 px-6 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-200 transition-colors">
                                    Choose CSV File
                                </label>

                                <button type="button" wire:click="downloadSampleCsv" class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:underline">
                                    <span class="material-symbols-outlined text-sm">download</span>
                                    Download Sample CSV Template
                                </button>
                                
                                @if($csv_file)
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

                    {{-- Audience Preview & Validation Correction Section --}}
                    @if(!empty($validationPreviewData['total']) && $validationPreviewData['total'] > 0)
                        <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900 shadow-sm space-y-4">
                            <div class="flex items-center justify-between flex-wrap gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                                <div>
                                    <h4 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary">fact_check</span>
                                        Audience Validation & Correction Preview
                                    </h4>
                                    <p class="text-xs text-slate-500">Review contacts, inspect pass/fail reasons, and correct errors inline.</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="loadValidationPreview" class="px-3 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                                        Re-Validate All
                                    </button>
                                </div>
                            </div>

                            {{-- Text Campaign 24h Rule Notice --}}
                            @if($type === 'text' && !empty($validationPreviewData['text_session_excluded_count']) && $validationPreviewData['text_session_excluded_count'] > 0)
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/40 dark:bg-amber-900/20 text-amber-800 dark:text-amber-300 flex items-start gap-3 animate-in fade-in slide-in-from-top-2">
                                    <span class="material-symbols-outlined text-amber-600 text-xl mt-0.5">warning</span>
                                    <div class="flex-1 text-xs">
                                        <p class="font-bold text-sm text-amber-900 dark:text-amber-200">WhatsApp 24-Hour Customer Window Rule</p>
                                        <p class="mt-1">
                                            Freeform text campaigns require contacts to have messaged your business in the last 24 hours. 
                                            <strong class="font-bold text-amber-950 dark:text-amber-100">{{ $validationPreviewData['text_session_excluded_count'] }} contact(s)</strong> do not have an active 24h session and will be excluded.
                                        </p>
                                        <div class="mt-3 flex items-center gap-2">
                                            <button type="button" wire:click="switchCampaignType('template')" class="px-3 py-1.5 bg-amber-600 text-white font-bold rounded-xl text-xs hover:bg-amber-700 transition-colors shadow-sm">
                                                Switch to Template Campaign (Reach All Contacts)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Validation Summary Stat Cards --}}
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 text-center">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Total Contacts</p>
                                    <p class="text-xl font-black text-slate-900 dark:text-white">{{ $validationPreviewData['total'] }}</p>
                                </div>
                                <div class="bg-emerald-50 dark:bg-emerald-900/20 p-4 rounded-2xl border border-emerald-100 dark:border-emerald-800 text-center">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Passed (Valid)</p>
                                    <p class="text-xl font-black text-emerald-700 dark:text-emerald-300">{{ $validationPreviewData['passed_count'] }}</p>
                                </div>
                                <div class="bg-rose-50 dark:bg-rose-900/20 p-4 rounded-2xl border border-rose-100 dark:border-rose-800 text-center">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400">Failed (Needs Fix)</p>
                                    <p class="text-xl font-black text-rose-700 dark:text-rose-300">{{ $validationPreviewData['failed_count'] }}</p>
                                </div>
                            </div>

                            {{-- Validation Filter Tabs & Table --}}
                            <div class="space-y-3">
                                <div class="flex gap-2">
                                    <button type="button" wire:click="$set('validationFilter', 'all')" class="px-3 py-1 text-xs font-bold rounded-lg transition-colors {{ $validationFilter === 'all' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                        All ({{ $validationPreviewData['total'] }})
                                    </button>
                                    <button type="button" wire:click="$set('validationFilter', 'passed')" class="px-3 py-1 text-xs font-bold rounded-lg transition-colors {{ $validationFilter === 'passed' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                        Passed ({{ $validationPreviewData['passed_count'] }})
                                    </button>
                                    <button type="button" wire:click="$set('validationFilter', 'failed')" class="px-3 py-1 text-xs font-bold rounded-lg transition-colors {{ $validationFilter === 'failed' ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                        Failed ({{ $validationPreviewData['failed_count'] }})
                                    </button>
                                </div>

                                <div class="max-h-60 overflow-y-auto rounded-2xl border border-slate-100 dark:border-slate-800">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-50 dark:bg-slate-800 sticky top-0">
                                            <tr>
                                                <th class="p-3 text-[10px] font-black uppercase text-slate-400">Phone</th>
                                                <th class="p-3 text-[10px] font-black uppercase text-slate-400">Name</th>
                                                <th class="p-3 text-[10px] font-black uppercase text-slate-400">24h Session</th>
                                                <th class="p-3 text-[10px] font-black uppercase text-slate-400">Validation Status</th>
                                                <th class="p-3 text-[10px] font-black uppercase text-slate-400 text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                            @foreach($validationPreviewData['rows'] as $r)
                                                @if($validationFilter === 'all' || ($validationFilter === 'passed' && $r['is_valid']) || ($validationFilter === 'failed' && !$r['is_valid']))
                                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                                        @if($editingRecipientId === $r['id'])
                                                            <td class="p-2" colspan="3">
                                                                <div class="flex gap-2">
                                                                    <input type="text" wire:model="editingPhone" class="flex-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-2 py-1 text-xs text-slate-900 dark:text-white">
                                                                    <input type="text" wire:model="editingName" placeholder="Name" class="flex-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-2 py-1 text-xs text-slate-900 dark:text-white">
                                                                </div>
                                                            </td>
                                                            <td class="p-2">
                                                                <span class="text-[10px] text-amber-600 font-bold">Editing...</span>
                                                            </td>
                                                            <td class="p-2 text-right">
                                                                <div class="flex items-center justify-end gap-1">
                                                                    <button type="button" wire:click="saveRecipientRow({{ $r['id'] }})" class="px-2 py-1 bg-emerald-600 text-white font-bold rounded-md text-[10px]">
                                                                        Save &amp; Validate
                                                                    </button>
                                                                    <button type="button" wire:click="cancelEditRecipientRow" class="px-2 py-1 bg-slate-200 text-slate-700 font-bold rounded-md text-[10px]">
                                                                        Cancel
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        @else
                                                            <td class="p-3 font-mono font-bold">{{ $r['phone'] }}</td>
                                                            <td class="p-3 font-semibold">{{ $r['name'] ?: 'N/A' }}</td>
                                                            <td class="p-3">
                                                                @if(!empty($r['is_session_active']))
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                                        <span class="size-1.5 rounded-full bg-emerald-500"></span> Active 24h
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-semibold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                                                        No 24h Session
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="p-3">
                                                                @if($r['is_valid'])
                                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                                        Passed
                                                                    </span>
                                                                @else
                                                                    <div class="flex flex-col">
                                                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 w-fit">
                                                                            Failed
                                                                        </span>
                                                                        <span class="text-[9px] text-rose-500 mt-0.5">{{ $r['error_reason'] }}</span>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="p-3 text-right">
                                                                <button type="button" wire:click="editRecipientRow({{ $r['id'] }}, '{{ $r['phone'] }}', '{{ $r['name'] }}')" class="px-2 py-1 text-[10px] font-bold text-primary bg-primary/10 hover:bg-primary/20 rounded-md transition-colors">
                                                                    Edit &amp; Fix
                                                                </button>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
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
