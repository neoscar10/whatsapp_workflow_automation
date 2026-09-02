<div x-data="{ show: @entangle('show') }" x-show="show" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
    {{-- Backdrop --}}
    <div x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    {{-- Modal Content --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4" class="relative w-full max-w-4xl overflow-hidden rounded-[2rem] bg-white shadow-2xl dark:bg-slate-900">
            
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-8 py-6 dark:border-slate-800 dark:bg-slate-800/50">
                <div>
                    <h2 class="text-xl font-black tracking-tight text-slate-900 dark:text-white">
                        {{ $campaignId ? 'Edit Campaign' : 'Create New Campaign' }}
                    </h2>
                    <p class="text-xs font-medium text-slate-500">Step {{ $step }} of 4: {{ ['Details', 'Audience', 'Content', 'Review'][$step - 1] }}</p>
                </div>
                <button @click="show = false" class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm transition-all hover:bg-slate-50 hover:text-slate-600 dark:bg-slate-800 dark:text-slate-500 dark:hover:bg-slate-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            {{-- Stepper --}}
            <div class="bg-white px-8 py-6 dark:bg-slate-900">
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
                            <span class="text-[10px] font-black uppercase tracking-widest {{ $step >= $currentStep ? 'text-primary' : 'text-slate-400' }}">{{ $label }}</span>
                        </div>
                    @endforeach
                    {{-- Progress Line --}}
                    <div class="absolute left-0 top-5 h-[2px] w-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full bg-primary transition-all duration-500" style="width: {{ (($step - 1) / 3) * 100 }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Body Content --}}
            <div class="max-h-[60vh] overflow-y-auto px-8 py-4">
                
                {{-- Step 1: Details --}}
                @if($step === 1)
                    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-wider text-slate-500">Campaign Name <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="name" placeholder="e.g., Summer Sale 2024" class="w-full rounded-2xl border-none bg-slate-50 py-3 px-4 text-sm focus:ring-2 focus:ring-primary/20 dark:bg-slate-800 dark:text-white">
                                @error('name') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-wider text-slate-500">WhatsApp Number <span class="text-rose-500">*</span></label>
                                <select wire:model="whatsapp_phone_number_id" class="w-full rounded-2xl border-none bg-slate-50 py-3 px-4 text-sm focus:ring-2 focus:ring-primary/20 dark:bg-slate-800 dark:text-white">
                                    <option value="">Select Phone Number</option>
                                    @foreach($phoneNumbers as $number)
                                        <option value="{{ $number->id }}">{{ $number->display_phone_number }} ({{ $number->verified_name }})</option>
                                    @endforeach
                                </select>
                                @error('whatsapp_phone_number_id') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <label class="text-xs font-black uppercase tracking-wider text-slate-500">Description</label>
                                <textarea wire:model="description" rows="2" placeholder="Optional context for this campaign..." class="w-full rounded-2xl border-none bg-slate-50 py-3 px-4 text-sm focus:ring-2 focus:ring-primary/20 dark:bg-slate-800 dark:text-white"></textarea>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-wider text-slate-500">Campaign Type</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <button type="button" wire:click="$set('type', 'template')" class="flex flex-col items-center gap-2 rounded-2xl border-2 p-4 transition-all {{ $type === 'template' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-50 bg-slate-50 text-slate-400 dark:border-slate-800 dark:bg-slate-800/50' }}">
                                        <span class="material-symbols-outlined text-2xl">article</span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider">Template</span>
                                    </button>
                                    <button type="button" wire:click="$set('type', 'text')" class="flex flex-col items-center gap-2 rounded-2xl border-2 p-4 transition-all {{ $type === 'text' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-50 bg-slate-50 text-slate-400 dark:border-slate-800 dark:bg-slate-800/50' }}">
                                        <span class="material-symbols-outlined text-2xl">notes</span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider">Text</span>
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-wider text-slate-500">Sending Mode</label>
                                <select wire:model.live="send_mode" class="w-full rounded-2xl border-none bg-slate-50 py-3 px-4 text-sm focus:ring-2 focus:ring-primary/20 dark:bg-slate-800 dark:text-white">
                                    <option value="draft">Save as Draft</option>
                                    <option value="now">Send Immediately</option>
                                    <option value="schedule">Schedule for Later</option>
                                </select>
                                
                                @if($send_mode === 'schedule')
                                    <div class="mt-4 animate-in fade-in slide-in-from-top-2">
                                        <label class="text-xs font-black uppercase tracking-wider text-slate-400">Scheduled Date & Time</label>
                                        <input type="datetime-local" wire:model="scheduled_at" class="mt-1 w-full rounded-2xl border-none bg-white py-3 px-4 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-primary/20 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                                        @error('scheduled_at') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Step 2: Audience --}}
                @if($step === 2)
                    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div class="flex flex-wrap gap-4">
                            @foreach([
                                ['key' => 'selected_contacts', 'label' => 'Contacts', 'icon' => 'person'],
                                ['key' => 'groups', 'label' => 'By Groups', 'icon' => 'group'],
                                ['key' => 'csv', 'label' => 'Import CSV', 'icon' => 'upload_file'],
                                ['key' => 'manual', 'label' => 'Manual Entry', 'icon' => 'edit_note'],
                            ] as $opt)
                                <button type="button" wire:click="$set('audience_type', '{{ $opt['key'] }}')" class="flex flex-1 min-w-[120px] flex-col items-center gap-2 rounded-2xl border-2 p-4 transition-all {{ $audience_type === $opt['key'] ? 'border-primary bg-primary/5 text-primary' : 'border-slate-50 bg-slate-50 text-slate-400 dark:border-slate-800 dark:bg-slate-800/50' }}">
                                    <span class="material-symbols-outlined text-2xl">{{ $opt['icon'] }}</span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider">{{ $opt['label'] }}</span>
                                </button>
                            @endforeach
                        </div>

                        <div class="rounded-3xl border border-slate-100 bg-slate-50/30 p-6 dark:border-slate-800 dark:bg-slate-800/20">
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
                            @endif

                            @if($audience_type === 'selected_contacts')
                                <div class="space-y-4">
                                    {{-- Search & Filter Controls --}}
                                    <div class="flex items-center gap-3">
                                        <div class="relative flex-1">
                                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">person_search</span>
                                            <input type="text" wire:model.live.debounce.300ms="contact_search" placeholder="Search contacts by name or phone..." class="w-full rounded-2xl border-none bg-white py-3 pl-10 pr-4 text-sm ring-1 ring-slate-100 focus:ring-2 focus:ring-primary/20 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                                        </div>
                                        <button type="button" wire:click="$toggle('show_filters')" class="flex h-11 w-11 items-center justify-center rounded-2xl transition-all {{ $show_filters ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'bg-white text-slate-400 ring-1 ring-slate-100 dark:bg-slate-800 dark:ring-slate-700 hover:text-slate-600' }}">
                                            <span class="material-symbols-outlined">filter_list</span>
                                        </button>
                                    </div>

                                    @if($show_filters)
                                        <div class="grid grid-cols-2 gap-4 rounded-2xl bg-white p-4 ring-1 ring-slate-100 dark:bg-slate-800 dark:ring-slate-700 animate-in fade-in slide-in-from-top-2">
                                            <div class="space-y-2">
                                                <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Source</label>
                                                <select wire:model.live="audience_filters.source" class="w-full rounded-xl border-none bg-slate-50 py-2 px-3 text-xs focus:ring-2 focus:ring-primary/20 dark:bg-slate-700 dark:text-white">
                                                    <option value="">All Sources</option>
                                                    <option value="manual">Manual</option>
                                                    <option value="imported">Imported</option>
                                                </select>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Status</label>
                                                <select wire:model.live="audience_filters.status" class="w-full rounded-xl border-none bg-slate-50 py-2 px-3 text-xs focus:ring-2 focus:ring-primary/20 dark:bg-slate-700 dark:text-white">
                                                    <option value="">All Statuses</option>
                                                    <option value="active">Active</option>
                                                    <option value="lead">Lead</option>
                                                </select>
                                            </div>
                                            <div class="col-span-2 flex items-center gap-3 py-2 border-t border-slate-100 dark:border-slate-700 mt-2">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" wire:model.live="send_to_all_filtered" class="sr-only peer">
                                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                                    <span class="ml-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Send to ALL contacts matching these filters</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($search_results))
                                        <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 space-y-3">
                                            <div class="flex items-center justify-between border-b border-slate-50 pb-2 dark:border-slate-800">
                                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Search Results ({{ count($search_results) }})</span>
                                                <button type="button" wire:click="selectAllSearchResults" class="text-[10px] font-black uppercase text-primary hover:underline">Select All</button>
                                            </div>
                                            <div class="max-h-48 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                                                @foreach($search_results as $result)
                                                    <div class="flex items-center gap-3 rounded-xl p-2 transition-all hover:bg-slate-50 dark:hover:bg-slate-800">
                                                        <label class="relative flex items-center cursor-pointer">
                                                            <input type="checkbox" 
                                                                wire:click="toggleContact({{ $result['id'] }})"
                                                                {{ in_array($result['id'], $this->selected_contact_ids) ? 'checked' : '' }}
                                                                class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                                                        </label>
                                                        <div class="flex flex-1 items-center gap-3 cursor-pointer" wire:click="toggleContact({{ $result['id'] }})">
                                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
                                                                <span class="material-symbols-outlined text-sm">person</span>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $result['name'] }}</p>
                                                                <p class="text-[9px] text-slate-500">{{ $result['phone'] }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Selected Contacts List --}}
                                    <div class="space-y-2">
                                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Selected Recipients ({{ count($selected_contact_ids) }})</p>
                                        <div class="flex flex-wrap gap-2">
                                            @forelse($selectedContacts as $contact)
                                                <div class="flex items-center gap-2 rounded-full bg-primary/10 pl-3 pr-1 py-1 text-xs font-bold text-primary">
                                                    <span>{{ $contact->name }}</span>
                                                    <button type="button" wire:click="removeContact({{ $contact->id }})" class="flex h-5 w-5 items-center justify-center rounded-full bg-primary/20 hover:bg-primary/30">
                                                        <span class="material-symbols-outlined text-[14px]">close</span>
                                                    </button>
                                                </div>
                                            @empty
                                                <div class="flex flex-col items-center justify-center py-10 text-center w-full">
                                                    <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-300 dark:bg-slate-800">
                                                        <span class="material-symbols-outlined text-3xl">group_add</span>
                                                    </div>
                                                    <p class="text-sm font-bold text-slate-400">No contacts selected yet.</p>
                                                    <p class="text-[10px] text-slate-400">Search above to add recipients.</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($audience_type === 'groups')
                                <div class="space-y-6">
                                    @php
                                        $staticGroups = $groups->where('type', 'static');
                                        $dynamicSegments = $groups->where('type', 'dynamic');
                                    @endphp

                                    @if($staticGroups->count() > 0)
                                        <div class="space-y-3">
                                            <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Static Lists</h4>
                                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                                @foreach($staticGroups as $group)
                                                    <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all {{ in_array($group->id, $selected_group_ids) ? 'border-primary bg-primary/5' : 'border-white dark:border-slate-800 hover:border-slate-200' }}">
                                                        <input type="checkbox" wire:model.live="selected_group_ids" value="{{ $group->id }}" class="sr-only">
                                                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $group->name }}</span>
                                                        <span class="text-[10px] text-slate-500 mt-1">{{ number_format($group->contacts_count) }} contacts</span>
                                                        @if(in_array($group->id, $selected_group_ids))
                                                            <div class="absolute top-2 right-2 size-5 bg-primary text-white rounded-full flex items-center justify-center">
                                                                <span class="material-symbols-outlined text-sm">check</span>
                                                            </div>
                                                        @endif
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if($dynamicSegments->count() > 0)
                                        <div class="space-y-3">
                                            <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Smart Segments</h4>
                                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                                @foreach($dynamicSegments as $segment)
                                                    <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all {{ in_array($segment->id, $selected_group_ids) ? 'border-emerald-500 bg-emerald-500/5' : 'border-white dark:border-slate-800 hover:border-slate-200' }}">
                                                        <input type="checkbox" wire:model.live="selected_group_ids" value="{{ $segment->id }}" class="sr-only">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="material-symbols-outlined text-[16px] text-emerald-500">bolt</span>
                                                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $segment->name }}</span>
                                                        </div>
                                                        <span class="text-[10px] text-slate-500 mt-1">{{ number_format($segment->resolved_count) }} matched</span>
                                                        @if(in_array($segment->id, $selected_group_ids))
                                                            <div class="absolute top-2 right-2 size-5 bg-emerald-500 text-white rounded-full flex items-center justify-center">
                                                                <span class="material-symbols-outlined text-sm">check</span>
                                                            </div>
                                                        @endif
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if($groups->count() === 0)
                                        <div class="text-center py-10 bg-white dark:bg-slate-800 rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-800">
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">No groups or segments found</p>
                                            <a href="{{ route('contacts.audiences') }}" class="text-xs font-bold text-primary hover:underline mt-2 inline-block">Create one in Audience Manager</a>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($audience_type === 'csv')
                                <div class="flex flex-col items-center justify-center py-6 text-center">
                                    <input type="file" wire:model="csv_file" class="hidden" id="modal_csv_upload">
                                    <label for="modal_csv_upload" class="group cursor-pointer flex flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-200 px-12 py-8 transition-all hover:border-primary hover:bg-primary/5 dark:border-slate-700">
                                        <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-400 group-hover:bg-primary/10 group-hover:text-primary dark:bg-slate-800">
                                            <span class="material-symbols-outlined text-3xl">upload_file</span>
                                        </div>
                                        <span class="text-sm font-black text-slate-700 dark:text-slate-300">Click to upload CSV</span>
                                        <span class="mt-1 text-xs text-slate-400">Max size 10MB</span>
                                    </label>

                                    <button type="button" wire:click="downloadSampleCsv" class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:underline">
                                        <span class="material-symbols-outlined text-sm">download</span>
                                        Download Sample CSV Template
                                    </button>
                                    @error('csv_file') <p class="mt-2 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror

                                    @if($csv_file)
                                        <div class="mt-6 flex flex-col items-center">
                                            <p class="text-xs font-bold text-emerald-600">Selected: {{ $csv_file->getClientOriginalName() }}</p>
                                            <button type="button" wire:click="importCsv" class="mt-4 rounded-xl bg-slate-900 px-8 py-2.5 text-xs font-bold text-white transition-all hover:bg-slate-800">
                                                Process Recipients
                                            </button>
                                        </div>
                                    @endif

                                    @if($import_summary)
                                        <div class="mt-6 w-full rounded-2xl bg-white p-4 shadow-xl ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
                                            <div class="grid grid-cols-3 gap-4">
                                                <div class="text-center">
                                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Total</p>
                                                    <p class="text-lg font-black text-slate-900 dark:text-white">{{ $import_summary['total'] }}</p>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-[10px] font-black uppercase tracking-wider text-emerald-500">Success</p>
                                                    <p class="text-lg font-black text-emerald-600">{{ $import_summary['success'] }}</p>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-[10px] font-black uppercase tracking-wider text-rose-500">Failed</p>
                                                    <p class="text-lg font-black text-rose-600">{{ $import_summary['failed'] }}</p>
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
                                                    <th class="p-3 text-[10px] font-black uppercase text-slate-400">Validation Status</th>
                                                    <th class="p-3 text-[10px] font-black uppercase text-slate-400 text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                                @foreach($validationPreviewData['rows'] as $r)
                                                    @if($validationFilter === 'all' || ($validationFilter === 'passed' && $r['is_valid']) || ($validationFilter === 'failed' && !$r['is_valid']))
                                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                                            @if($editingRecipientId === $r['id'])
                                                                <td class="p-2" colspan="2">
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
                @endif

                {{-- Step 3: Content --}}
                @if($step === 3)
                    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        @if($type === 'template')
                            <div class="space-y-4">
                                <label class="text-xs font-black uppercase tracking-wider text-slate-500">Select Template <span class="text-rose-500">*</span></label>
                                <select wire:model.live="whatsapp_template_id" class="w-full rounded-2xl border-none bg-slate-50 py-3 px-4 text-sm focus:ring-2 focus:ring-primary/20 dark:bg-slate-800 dark:text-white">
                                    <option value="">-- Choose WhatsApp Approved Template --</option>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->id }}">{{ $tpl->display_title ?? $tpl->remote_template_name }} ({{ $tpl->language_code }})</option>
                                    @endforeach
                                </select>
                                @error('whatsapp_template_id') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror

                                @if($whatsapp_template_id)
                                    @php 
                                        $selectedTemplate = $templates->find($whatsapp_template_id); 
                                        $templateVars = app(\App\Services\Campaign\CampaignTemplateVariableService::class)->extractVariables($selectedTemplate);
                                    @endphp
                                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                                        {{-- Variable Mapping --}}
                                        <div class="space-y-6">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Content Configuration</h3>
                                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[8px] font-black text-slate-500 dark:bg-slate-800">DYNAMIC</span>
                                            </div>

                                            <div class="space-y-6">
                                                {{-- Header Section --}}
                                                @if(!empty($templateVars['header']) || $selectedTemplate->header_type !== 'text' && $selectedTemplate->header_type !== 'none')
                                                    <div class="space-y-3">
                                                        <label class="text-[10px] font-bold uppercase text-slate-400">Header ({{ $selectedTemplate->header_type }})</label>
                                                        @if($selectedTemplate->header_type === 'text')
                                                            @foreach($templateVars['header'] as $idx => $var)
                                                                <div class="flex items-center gap-3">
                                                                    <span class="flex h-8 {{ is_numeric($idx) ? 'w-8' : 'min-w-[2rem] px-2' }} shrink-0 items-center justify-center rounded-xl bg-primary/10 text-[9px] font-black text-primary">
                                                                        {{ is_numeric($idx) ? "H$idx" : $idx }}
                                                                    </span>
                                                                    <select wire:model.live="template_variable_mapping.header.{{ $idx }}.source" class="flex-1 rounded-xl border-none bg-slate-50 py-2 px-3 text-xs dark:bg-slate-800">
                                                                        @foreach($personalizationFields as $field)
                                                                            <option value="{{ $field['key'] }}">{{ $field['label'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    @if(($template_variable_mapping['header'][$idx]['source'] ?? 'static') === 'static')
                                                                        <input type="text" wire:model="template_variable_mapping.header.{{ $idx }}.value" placeholder="Enter static value" class="flex-1 rounded-xl border-none bg-white py-2 px-3 text-xs ring-1 ring-slate-100 dark:bg-slate-800 dark:ring-slate-700">
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="rounded-2xl border-2 border-dashed border-slate-200 p-4 text-center dark:border-slate-800">
                                                                <span class="material-symbols-outlined text-slate-300">upload_file</span>
                                                                <p class="text-[10px] font-bold text-slate-400 mt-1">Media headers require a hosted URL or file upload in the next phase.</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif

                                                {{-- Body Section --}}
                                                @if(!empty($templateVars['body']))
                                                    <div class="space-y-3">
                                                        <label class="text-[10px] font-bold uppercase text-slate-400">Body Variables</label>
                                                        @foreach($templateVars['body'] as $idx => $var)
                                                            <div class="flex items-center gap-3">
                                                                <span class="flex h-8 {{ is_numeric($idx) ? 'w-8' : 'min-w-[2rem] px-2' }} shrink-0 items-center justify-center rounded-xl bg-slate-100 text-[9px] font-black text-slate-500 dark:bg-slate-800">
                                                                    {{ is_numeric($idx) ? "\\$idx" : $idx }}
                                                                </span>
                                                                <select wire:model.live="template_variable_mapping.body.{{ $idx }}.source" class="flex-1 rounded-xl border-none bg-slate-50 py-2 px-3 text-xs dark:bg-slate-800">
                                                                    @foreach($personalizationFields as $field)
                                                                        <option value="{{ $field['key'] }}">{{ $field['label'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @if(($template_variable_mapping['body'][$idx]['source'] ?? 'static') === 'static')
                                                                    <input type="text" wire:model="template_variable_mapping.body.{{ $idx }}.value" placeholder="Enter static value" class="flex-1 rounded-xl border-none bg-white py-2 px-3 text-xs ring-1 ring-slate-100 dark:bg-slate-800 dark:ring-slate-700">
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                {{-- Buttons Section --}}
                                                @if(!empty($templateVars['button']))
                                                    <div class="space-y-3">
                                                        <label class="text-[10px] font-bold uppercase text-slate-400">Dynamic Buttons</label>
                                                        @foreach($templateVars['button'] as $btnIdx => $vars)
                                                            @foreach($vars as $varIdx => $var)
                                                                <div class="flex flex-col gap-2 rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/50">
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="material-symbols-outlined text-[14px] text-primary">link</span>
                                                                        <span class="text-[10px] font-black text-slate-700 dark:text-slate-300">{{ $var['button_text'] }}</span>
                                                                    </div>
                                                                    <div class="flex items-center gap-3">
                                                                        <select wire:model.live="template_variable_mapping.button.{{ $btnIdx }}.{{ $varIdx }}.source" class="flex-1 rounded-xl border-none bg-white py-2 px-3 text-xs dark:bg-slate-800 ring-1 ring-slate-100 dark:ring-slate-700">
                                                                            @foreach($personalizationFields as $field)
                                                                                <option value="{{ $field['key'] }}">{{ $field['label'] }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @if(($template_variable_mapping['button'][$btnIdx][$varIdx]['source'] ?? 'static') === 'static')
                                                                            <input type="text" wire:model="template_variable_mapping.button.{{ $btnIdx }}.{{ $varIdx }}.value" placeholder="URL Parameter" class="flex-1 rounded-xl border-none bg-white py-2 px-3 text-xs ring-1 ring-slate-100 dark:bg-slate-800 dark:ring-slate-700">
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Preview --}}
                                        <div class="rounded-3xl bg-slate-50 p-6 dark:bg-slate-800/30">
                                            <div class="mx-auto w-full max-w-[280px] rounded-2xl bg-white p-3 shadow-xl dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                                                @if($selectedTemplate->header_type !== 'none')
                                                    <div class="mb-3 h-32 rounded-xl bg-slate-100 flex items-center justify-center text-slate-300 dark:bg-slate-800">
                                                        @if($selectedTemplate->header_type === 'text')
                                                            <span class="text-[10px] font-black uppercase tracking-widest">{{ $selectedTemplate->header_text }}</span>
                                                        @else
                                                            <span class="material-symbols-outlined text-4xl">
                                                                {{ $selectedTemplate->header_type === 'video' ? 'movie' : ($selectedTemplate->header_type === 'document' ? 'description' : 'image') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                                <div class="text-sm text-slate-800 dark:text-slate-200 whitespace-pre-wrap leading-relaxed">{{ $selectedTemplate->body_text }}</div>
                                                @if($selectedTemplate->footer_text)
                                                    <p class="mt-2 text-[10px] text-slate-400 font-medium">{{ $selectedTemplate->footer_text }}</p>
                                                @endif

                                                @if($selectedTemplate->buttons->count() > 0)
                                                    <div class="mt-4 space-y-2 border-t border-slate-50 pt-3 dark:border-slate-800">
                                                        @foreach($selectedTemplate->buttons as $button)
                                                            <div class="flex items-center justify-center gap-2 rounded-xl bg-slate-50 py-2.5 text-xs font-black text-primary dark:bg-slate-800">
                                                                <span class="material-symbols-outlined text-sm">
                                                                    {{ $button->type === 'PHONE_NUMBER' ? 'call' : ($button->type === 'URL' ? 'open_in_new' : 'reply') }}
                                                                </span>
                                                                {{ $button->text }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="space-y-4">
                                <label class="text-xs font-black uppercase tracking-wider text-slate-500">Message Body <span class="text-rose-500">*</span></label>
                                <textarea wire:model="message_body" rows="6" placeholder="Type your WhatsApp broadcast message here..." class="w-full rounded-2xl border-none bg-slate-50 py-4 px-5 text-sm focus:ring-2 focus:ring-primary/20 dark:bg-slate-800 dark:text-white"></textarea>
                                @error('message_body') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Step 4: Review --}}
                @if($step === 4)
                    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                            <div class="space-y-6">
                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Campaign</p>
                                    <p class="text-lg font-black text-slate-900 dark:text-white">{{ $name }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Audience Strategy</p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary">groups</span>
                                        <p class="text-sm font-black text-slate-700 dark:text-slate-300">
                                            {{ ucfirst(str_replace('_', ' ', $audience_type)) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Dispatch Plan</p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary">{{ $send_mode === 'schedule' ? 'event' : ($send_mode === 'now' ? 'rocket_launch' : 'draft') }}</span>
                                        <p class="text-sm font-black text-slate-700 dark:text-slate-300">
                                            {{ $send_mode === 'schedule' ? 'Scheduled: ' . $scheduled_at : ($send_mode === 'now' ? 'Send Immediately' : 'Save as Draft') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-3xl bg-slate-900 p-6 shadow-2xl">
                                <p class="mb-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Message Preview</p>
                                <div class="mx-auto w-full max-w-[280px] rounded-2xl bg-white p-3 shadow-xl dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                                    @if($type === 'template')
                                        @php $tpl = $templates->find($whatsapp_template_id); @endphp
                                        <p class="mb-1 text-[10px] font-black text-primary uppercase">Template: {{ $tpl?->remote_template_name }}</p>
                                        <p class="text-xs text-slate-800 dark:text-slate-200 leading-relaxed">{{ $tpl?->body_text }}</p>
                                    @else
                                        <p class="text-xs text-slate-800 dark:text-slate-200 leading-relaxed">{{ $message_body }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-primary/5 p-6 ring-1 ring-primary/20 flex gap-4 items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <span class="material-symbols-outlined text-2xl">info</span>
                            </div>
                            <div>
                                <p class="text-sm font-black text-primary">Ready to Launch?</p>
                                <p class="text-xs text-primary/70">By confirming, your campaign will be processed via our background worker. You can monitor performance in the report dashboard.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer Controls --}}
            <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/50 px-8 py-6 dark:border-slate-800 dark:bg-slate-800/50">
                <button type="button" @click="show = false" class="text-xs font-black uppercase tracking-widest text-slate-400 transition-all hover:text-slate-600">
                    Cancel
                </button>
                
                <div class="flex items-center gap-3">
                    @if($step > 1)
                        <button type="button" wire:click="prevStep" class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm ring-1 ring-slate-100 transition-all hover:bg-slate-50 hover:text-slate-600 dark:bg-slate-800 dark:text-slate-500 dark:ring-slate-700">
                            <span class="material-symbols-outlined">arrow_back</span>
                        </button>
                    @endif

                    @if($step < 4)
                        <button type="button" wire:click="nextStep" class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-8 py-3 text-xs font-black uppercase tracking-widest text-white shadow-xl transition-all hover:scale-[1.02] active:scale-[0.98] dark:bg-primary">
                            Next Step
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </button>
                    @else
                        <button type="button" wire:click="finish" class="inline-flex items-center gap-2 rounded-2xl bg-primary px-10 py-3 text-xs font-black uppercase tracking-widest text-white shadow-xl transition-all hover:scale-[1.02] active:scale-[0.98]">
                            {{ $send_mode === 'now' ? 'Launch Campaign' : ($send_mode === 'schedule' ? 'Schedule' : 'Save Draft') }}
                            <span class="material-symbols-outlined text-sm">rocket_launch</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
