<div class="p-6 lg:p-8 w-full max-w-7xl mx-auto font-['Inter']">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-[24px] font-semibold tracking-tight text-slate-900 dark:text-white">Compliance Automation Library</h1>
            <p class="text-[14px] text-[#424656] dark:text-slate-400 mt-1">Configure default compliance reminders, customize tone/language, and manage schedules.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/30 rounded-xl text-sm text-green-800 dark:text-green-300 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 rounded-xl text-sm text-red-800 dark:text-red-300 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">warning</span>
            {{ session('error') }}
        </div>
    @endif

    <!-- LIST MODE -->
    @if($viewMode === 'list')
        <!-- Tab Control Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-200 dark:border-slate-800 mb-6 gap-4">
            <div class="flex gap-6">
                <button type="button" wire:click="selectTab('mine')"
                    class="pb-3 text-sm font-bold border-b-2 transition-all cursor-pointer {{ $activeTab === 'mine' ? 'border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                    Your Automations
                </button>
                <button type="button" wire:click="selectTab('system')"
                    class="pb-3 text-sm font-bold border-b-2 transition-all cursor-pointer {{ $activeTab === 'system' ? 'border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                    System Automations Catalogue
                </button>
            </div>
            @if($activeTab === 'mine')
                <button type="button" wire:click="openCreateModal"
                    class="mb-2 sm:mb-0 inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all cursor-pointer shadow-sm active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    Create Custom Automation
                </button>
            @endif
        </div>

        @php
            $addedLibraryIds = \Modules\CA\Models\CAClientAutomation::where('company_id', auth()->user()->company_id)
                ->whereNull('client_id')
                ->pluck('automation_library_id')
                ->toArray();
        @endphp

        <!-- Full Width List Layout of Automations -->
        <div class="flex flex-col gap-4 animate-fade-in">
            @if($activeTab === 'mine')
                @forelse($myAutomations as $item)
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <!-- Icon & Name Info -->
                        <div class="flex items-center gap-4 min-w-0 flex-1">
                            <div class="size-12 rounded-2xl flex items-center justify-center flex-shrink-0 text-white font-bold"
                                 style="background-color: {{ $item->automationLibrary->color ?? '#3b82f6' }};">
                                <span class="material-symbols-outlined text-[24px]">{{ $item->automationLibrary->icon ?? 'settings_suggest' }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white truncate" title="{{ $item->metadata_json['custom_name'] ?? $item->automationLibrary->name }}">
                                        {{ $item->metadata_json['custom_name'] ?? $item->automationLibrary->name }}
                                    </h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        {{ $item->frequency }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-1">
                                    {{ $item->automationLibrary->description }}
                                </p>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        <div class="flex items-center gap-3 shrink-0">
                            <button type="button" wire:click="selectAutomation({{ $item->id }})"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all cursor-pointer shadow-sm active:scale-95">
                                Configure Workspace
                                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                            </button>
                            
                            <button type="button" wire:click="deleteCompanyAutomation({{ $item->id }})"
                                onclick="confirm('Are you sure you want to delete this template from your library?') || event.stopImmediatePropagation()"
                                class="text-slate-400 hover:text-red-500 dark:hover:text-red-400 transition-colors cursor-pointer p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl">
                        <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 text-[48px]">folder_open</span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mt-3">No Custom Automations Mapped</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Visit the "System Automations Catalogue" tab to customize and copy templates to your firm's library.</p>
                    </div>
                @endforelse
            @else
                @forelse($systemLibrary as $item)
                    @php
                        $isAlreadyAdded = in_array($item->id, $addedLibraryIds);
                    @endphp
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <!-- Icon & Name Info -->
                        <div class="flex items-center gap-4 min-w-0 flex-1">
                            <div class="size-12 rounded-2xl flex items-center justify-center flex-shrink-0 text-white font-bold"
                                 style="background-color: {{ $item->color ?? '#3b82f6' }};">
                                <span class="material-symbols-outlined text-[24px]">{{ $item->icon ?? 'settings_suggest' }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white truncate" title="{{ $item->name }}">{{ $item->name }}</h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        {{ $item->frequency }}
                                    </span>
                                    @if($isAlreadyAdded)
                                        <span class="inline-flex items-center gap-0.5 px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800/30">
                                            <span class="material-symbols-outlined text-[10px]">check</span> Added
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-1">
                                    {{ $item->description }}
                                </p>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        <div class="flex items-center gap-3 shrink-0">
                            <button type="button" wire:click="selectAutomation({{ $item->id }})"
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all cursor-pointer shadow-sm active:scale-95">
                                Customize & Add to Library
                                <span class="material-symbols-outlined text-[14px]">tune</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl">
                        <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 text-[48px]">folder_open</span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mt-3">No Catalogue Items Available</h3>
                    </div>
                @endforelse
            @endif
        </div>

    <!-- DETAIL MODE (WORKSPACE) -->
    @else
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden animate-fade-in">
            
            <!-- Detail Header Bar -->
            <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-800/20">
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="goBackToList"
                        class="flex items-center justify-center p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                    </button>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-green-500"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                {{ $activeTab === 'mine' ? 'Customized Workspace' : 'System Template Preview' }}
                            </span>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mt-1">
                            {{ $activeTab === 'mine' ? ($selectedAutomation->metadata_json['custom_name'] ?? $selectedAutomation->automationLibrary->name) : $selectedAutomation->name }}
                        </h2>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                        {{ $selectedAutomation->frequency }}
                    </span>
                </div>
            </div>

            <!-- Workspace Details Content Layout -->
            <div class="flex flex-col gap-8 p-6">

                <!-- SECTION 1: REGULAR REMINDER CUSTOMIZER -->
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                    <!-- Left: Inputs -->
                    <div class="xl:col-span-7 flex flex-col gap-6">
                        
                        <!-- Automation Name Input -->
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Automation Name / Alias</label>
                            <input type="text" wire:model="customName" placeholder="E.g., Monthly Bank Statement Automation"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 font-semibold transition-colors">
                        </div>

                        <!-- Template Text Customizer -->
                        <div class="space-y-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Template Message Copy</h3>
                            
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Header Title</label>
                                <input type="text" wire:model.live.debounce.300ms="templateTitle" 
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 font-semibold transition-colors">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Message Body</label>
                                    <!-- Tone Tabs Selector -->
                                    <div class="flex bg-slate-100 dark:bg-slate-800 p-0.5 rounded-xl border border-slate-200/50 dark:border-slate-700/50">
                                        <button type="button" wire:click="selectTone('professional')"
                                            class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all cursor-pointer {{ $selectedTone === 'professional' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                                            👔 Professional
                                        </button>
                                        <button type="button" wire:click="selectTone('friendly')"
                                            class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all cursor-pointer {{ $selectedTone === 'friendly' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                                            🤝 Friendly
                                        </button>
                                        <button type="button" wire:click="selectTone('urgent')"
                                            class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all cursor-pointer {{ $selectedTone === 'urgent' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                                            🚨 Urgent
                                        </button>
                                    </div>
                                </div>
                                <textarea wire:model.live.debounce.300ms="templateBody" rows="12"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 font-mono resize-none transition-colors"></textarea>
                            </div>
                        </div>

                        <!-- AI Regeneration Button -->
                        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 dark:border-slate-800 pt-4">
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                💡 <span class="font-semibold">AI Assistant:</span> Switch tone tabs above to load defaults, or trigger a variation below.
                            </div>
                            @php
                                $libId = $activeTab === 'mine' ? $selectedAutomation->automationLibrary->id : $selectedAutomation->id;
                                $regenCount = session()->get('ai_regen_count_' . $libId, 0);
                            @endphp
                            <div class="flex flex-col items-end">
                                <button type="button" wire:click="regenerateWithAI" 
                                    wire:loading.attr="disabled"
                                    wire:target="regenerateWithAI"
                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm active:scale-95 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" 
                                    {{ $regenCount >= 3 ? 'disabled' : '' }}>
                                    <span class="material-symbols-outlined text-[16px] animate-spin" wire:loading wire:target="regenerateWithAI">autorenew</span>
                                    <span class="material-symbols-outlined text-[16px]" wire:loading.remove wire:target="regenerateWithAI">auto_awesome</span>
                                    <span wire:loading.remove wire:target="regenerateWithAI">Regenerate Message via AI</span>
                                    <span wire:loading wire:target="regenerateWithAI">Regenerating message...</span>
                                </button>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">AI Limit: {{ $regenCount }} / 3 generations</span>
                            </div>
                        </div>

                    </div>

                    <!-- Right: Preview Simulator -->
                    <div class="xl:col-span-5 flex flex-col">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Live Reminder WABA Preview</label>
                        
                        <!-- Device Frame -->
                        <div class="border border-slate-200 dark:border-slate-800 rounded-3xl p-3 bg-slate-100 dark:bg-slate-950 flex-grow flex flex-col min-h-[380px] shadow-inner relative overflow-hidden">
                            <!-- Phone Header -->
                            <div class="flex items-center gap-2.5 px-3 py-2 bg-emerald-800 dark:bg-emerald-900 text-white rounded-2xl shadow-sm mb-4">
                                <div class="size-8 rounded-full bg-emerald-700 flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                    {{ substr($customName ?: ($activeTab === 'mine' ? $selectedAutomation->automationLibrary->name : $selectedAutomation->name), 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-xs font-bold truncate leading-tight">
                                        {{ $customName ?: ($activeTab === 'mine' ? $selectedAutomation->automationLibrary->name : $selectedAutomation->name) }}
                                    </h4>
                                    <span class="text-[9px] text-emerald-200 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span> Online
                                    </span>
                                </div>
                            </div>

                            <!-- Chat Bubble Area -->
                            <div class="flex-grow overflow-y-auto no-scrollbar px-1 flex flex-col justify-end">
                                <div class="bg-white dark:bg-slate-800 text-slate-950 dark:text-slate-100 rounded-2xl rounded-tl-none p-3.5 shadow-sm border border-slate-200/50 dark:border-slate-800/80 max-w-[90%] text-xs leading-relaxed relative">
                                    @if(!empty($templateTitle))
                                        <h5 class="font-bold text-[13px] border-b border-slate-100 dark:border-slate-850 pb-1.5 mb-2 text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[16px] text-emerald-600 dark:text-emerald-400">notifications_active</span>
                                            {{ $templateTitle }}
                                        </h5>
                                    @endif
                                    
                                    <div class="space-y-1 text-[11px] font-sans leading-relaxed text-slate-800 dark:text-slate-200">
                                        @php
                                            $previewBody = $templateBody;
                                            $placeholders = [
                                                'client_name' => 'John Doe',
                                                'firm_name' => 'Apex Advisors',
                                                'document_name' => ($activeTab === 'mine' ? $selectedAutomation->automationLibrary->name : $selectedAutomation->name) . ' Documents',
                                                'due_date' => \Carbon\Carbon::now()->addDays(7)->format('d-M-Y'),
                                                'days_remaining' => '7',
                                            ];
                                            foreach($placeholders as $ph => $val) {
                                                $previewBody = str_replace('{' . '{' . $ph . '}' . '}', "**" . $val . "**", $previewBody);
                                                $previewBody = str_replace('{' . $ph . '}', "**" . $val . "**", $previewBody);
                                                $previewBody = str_replace($ph, "**" . $val . "**", $previewBody);
                                            }
                                            $previewBody = nl2br(e($previewBody));
                                            $previewBody = preg_replace('/\*([^\*]+)\*/', '<strong>$1</strong>', $previewBody);
                                            $previewBody = preg_replace('/\\*\\*([^\\*]+)\\*\\*/', '<strong>$1</strong>', $previewBody);
                                        @endphp
                                        {!! $previewBody !!}
                                    </div>

                                    <div class="flex justify-end items-center gap-1 mt-1.5 text-[9px] text-slate-400 dark:text-slate-500">
                                        <span>{{ \Carbon\Carbon::now()->format('H:i') }}</span>
                                        <span class="material-symbols-outlined text-[14px] text-sky-500">done_all</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: REMINDER TIMING & RULES -->
                <div class="space-y-4 border-t border-slate-100 dark:border-slate-800 pt-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Reminder Timing & Rules</h3>
                        <button type="button" wire:click="addReminderRule"
                            class="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 flex items-center gap-1 cursor-pointer">
                            <span class="material-symbols-outlined text-[14px]">add</span> Add Rule
                        </button>
                    </div>

                    @error('editingRules') <span class="text-xs text-red-500 block font-medium">{{ $message }}</span> @enderror

                    <div class="space-y-3">
                        @forelse($editingRules as $idx => $rule)
                            <div class="flex items-end gap-3 p-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200/50 dark:border-slate-800 rounded-xl" wire:key="rule-{{ $idx }}">
                                <!-- Offset Days (Only show if not 'On Due Date') -->
                                @if(($rule['trigger_type'] ?? '') !== 'on_due')
                                    <div class="w-16 flex flex-col gap-1">
                                        <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Days</span>
                                        <input type="number" min="0" wire:model="editingRules.{{ $idx }}.offset_days"
                                            class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-center font-bold">
                                    </div>
                                @endif
                                
                                <!-- Trigger Type -->
                                <div class="flex-grow flex flex-col gap-1">
                                    <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Trigger Reference</span>
                                    <select wire:model.live="editingRules.{{ $idx }}.trigger_type"
                                        class="w-full px-2.5 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs cursor-pointer">
                                        <option value="before_due">Day(s) Before Due Date</option>
                                        <option value="on_due">On the Due Date</option>
                                        <option value="after_due">Day(s) After Due Date (Overdue Escalation)</option>
                                    </select>
                                </div>

                                <!-- Send Time -->
                                <div class="w-32 flex flex-col gap-1">
                                    <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Send Time</span>
                                    <input type="time" wire:model="editingRules.{{ $idx }}.send_time"
                                        class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs">
                                </div>

                                <!-- Remove button -->
                                <div class="pb-1">
                                    <button type="button" wire:click="removeReminderRule({{ $idx }})"
                                        class="text-slate-400 hover:text-red-500 transition-colors cursor-pointer">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 dark:text-slate-400 italic">No reminder triggers configured yet. Clients won't receive messages until you add rules.</p>
                        @endforelse
                    </div>
                </div>

                <!-- SECTION 3: OVERDUE REMINDER CUSTOMIZER -->
                @if(collect($editingRules)->contains('trigger_type', 'after_due'))
                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 border-t border-slate-100 dark:border-slate-800 pt-8 mt-4 animate-fade-in">
                        <!-- Left: Inputs -->
                        <div class="xl:col-span-7 flex flex-col gap-6">
                            
                            <!-- Overdue Template Text Customizer -->
                            <div class="space-y-4">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-450 dark:text-slate-400">Overdue Reminder Template Message</h3>
                                
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Overdue Header Title</label>
                                    <input type="text" wire:model.live.debounce.300ms="overdueTitle" 
                                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 font-semibold transition-colors">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Overdue Message Body</label>
                                    <textarea wire:model.live.debounce.300ms="overdueBody" rows="12"
                                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 font-mono resize-none transition-colors"></textarea>
                                </div>
                            </div>

                            <!-- Overdue AI Regeneration Button -->
                            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 dark:border-slate-800 pt-4">
                                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    💡 <span class="font-semibold">AI Assistant:</span> Trigger a custom urgent overdue variation copy below.
                                </div>
                                @php
                                    $overdueRegenCount = session()->get('ai_overdue_regen_count_' . $libId, 0);
                                @endphp
                                <div class="flex flex-col items-end">
                                    <button type="button" wire:click="regenerateOverdueWithAI" 
                                        wire:loading.attr="disabled"
                                        wire:target="regenerateOverdueWithAI"
                                        class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm active:scale-95 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" 
                                        {{ $overdueRegenCount >= 3 ? 'disabled' : '' }}>
                                        <span class="material-symbols-outlined text-[16px] animate-spin" wire:loading wire:target="regenerateOverdueWithAI">autorenew</span>
                                        <span class="material-symbols-outlined text-[16px]" wire:loading.remove wire:target="regenerateOverdueWithAI">auto_awesome</span>
                                        <span wire:loading.remove wire:target="regenerateOverdueWithAI">Regenerate Overdue via AI</span>
                                        <span wire:loading wire:target="regenerateOverdueWithAI">Regenerating message...</span>
                                    </button>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">AI Limit: {{ $overdueRegenCount }} / 3 generations</span>
                                </div>
                            </div>

                        </div>

                        <!-- Right: Preview Simulator -->
                        <div class="xl:col-span-5 flex flex-col">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Live Overdue WABA Preview</label>
                            
                            <!-- Device Frame -->
                            <div class="border border-slate-200 dark:border-slate-800 rounded-3xl p-3 bg-slate-100 dark:bg-slate-950 flex-grow flex flex-col min-h-[380px] shadow-inner relative overflow-hidden">
                                <!-- Phone Header -->
                                <div class="flex items-center gap-2.5 px-3 py-2 bg-red-800 dark:bg-red-900 text-white rounded-2xl shadow-sm mb-4">
                                    <div class="size-8 rounded-full bg-red-700 flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                        ⚠️
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-xs font-bold truncate leading-tight">
                                            Overdue: {{ $customName ?: ($activeTab === 'mine' ? $selectedAutomation->automationLibrary->name : $selectedAutomation->name) }}
                                        </h4>
                                        <span class="text-[9px] text-red-200 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span> Overdue Notice
                                        </span>
                                    </div>
                                </div>

                                <!-- Chat Bubble Area -->
                                <div class="flex-grow overflow-y-auto no-scrollbar px-1 flex flex-col justify-end">
                                    <div class="bg-white dark:bg-slate-800 text-slate-950 dark:text-slate-100 rounded-2xl rounded-tl-none p-3.5 shadow-sm border border-slate-200/50 dark:border-slate-800/80 max-w-[90%] text-xs leading-relaxed relative">
                                        @if(!empty($overdueTitle))
                                            <h5 class="font-bold text-[13px] border-b border-red-100 dark:border-slate-850 pb-1.5 mb-2 text-red-850 dark:text-red-400 flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-[16px] text-red-650 dark:text-red-400 font-bold">warning</span>
                                                {{ $overdueTitle }}
                                            </h5>
                                        @endif
                                        
                                        <div class="space-y-1 text-[11px] font-sans leading-relaxed text-slate-800 dark:text-slate-200">
                                            @php
                                                $previewOverdueBody = $overdueBody;
                                                $placeholders = [
                                                    'client_name' => 'John Doe',
                                                    'firm_name' => 'Apex Advisors',
                                                    'document_name' => ($activeTab === 'mine' ? $selectedAutomation->automationLibrary->name : $selectedAutomation->name) . ' Documents',
                                                    'due_date' => \Carbon\Carbon::now()->addDays(-3)->format('d-M-Y'),
                                                    'days_remaining' => '3',
                                                ];
                                                foreach($placeholders as $ph => $val) {
                                                    $previewOverdueBody = str_replace('{' . '{' . $ph . '}' . '}', "**" . $val . "**", $previewOverdueBody);
                                                    $previewOverdueBody = str_replace('{' . $ph . '}', "**" . $val . "**", $previewOverdueBody);
                                                    $previewOverdueBody = str_replace($ph, "**" . $val . "**", $previewOverdueBody);
                                                }
                                                $previewOverdueBody = nl2br(e($previewOverdueBody));
                                                $previewOverdueBody = preg_replace('/\*([^\*]+)\*/', '<strong>$1</strong>', $previewOverdueBody);
                                                $previewOverdueBody = preg_replace('/\\*\\*([^\\*]+)\\*\\*/', '<strong>$1</strong>', $previewOverdueBody);
                                            @endphp
                                            {!! $previewOverdueBody !!}
                                        </div>

                                        <div class="flex justify-end items-center gap-1 mt-1.5 text-[9px] text-slate-400 dark:text-slate-500">
                                            <span>{{ \Carbon\Carbon::now()->format('H:i') }}</span>
                                            <span class="material-symbols-outlined text-[14px] text-sky-500">done_all</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- SECTION 4: TEMPLATE VARIABLES MAPPING -->
                <div class="space-y-4 border-t border-slate-100 dark:border-slate-800 pt-6">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Template Variables Mapping</h3>
                    
                    <div class="space-y-3 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-850">
                        @if(empty($extractedVariables['body']) && empty($extractedVariables['header']))
                            <p class="text-xs text-slate-500 dark:text-slate-400 italic">No custom parameters detected in the template copy.</p>
                        @else
                            <!-- Header variables -->
                            @foreach(($extractedVariables['header'] ?? []) as $var)
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center" wire:key="hvar-{{ $var }}">
                                    <div class="sm:col-span-4 text-xs font-bold text-slate-700 dark:text-slate-300 truncate">
                                        Header: <code class="bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 px-1.5 py-0.5 rounded font-mono">{{ $var }}</code>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <select wire:model.live="variableMappings.header.{{ $var }}.source" 
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs cursor-pointer font-medium">
                                            <option value="system">System Value</option>
                                            <option value="static">Static Value</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-5">
                                        @if(($variableMappings['header'][$var]['source'] ?? 'system') === 'system')
                                            <select wire:model.live="variableMappings.header.{{ $var }}.value" 
                                                class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs cursor-pointer font-medium">
                                                @foreach($this->getAvailableSystemVariables() as $sysVar)
                                                    <option value="{{ $sysVar['key'] }}">{{ $sysVar['label'] }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" wire:model="variableMappings.header.{{ $var }}.value" placeholder="Enter static text"
                                                class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs">
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <!-- Body variables -->
                            @foreach(($extractedVariables['body'] ?? []) as $var)
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center" wire:key="bvar-{{ $var }}">
                                    <div class="sm:col-span-4 text-xs font-bold text-slate-700 dark:text-slate-300 truncate">
                                        Body: <code class="bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 px-1.5 py-0.5 rounded font-mono">{{ $var }}</code>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <select wire:model.live="variableMappings.body.{{ $var }}.source" 
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs cursor-pointer font-medium">
                                            <option value="system">System Value</option>
                                            <option value="static">Static Value</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-5">
                                        @if(($variableMappings['body'][$var]['source'] ?? 'system') === 'system')
                                            <select wire:model.live="variableMappings.body.{{ $var }}.value" 
                                                class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs cursor-pointer font-medium">
                                                @foreach($this->getAvailableSystemVariables() as $sysVar)
                                                    <option value="{{ $sysVar['key'] }}">{{ $sysVar['label'] }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" wire:model="variableMappings.body.{{ $var }}.value" placeholder="Enter static text"
                                                class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs">
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

            </div>

            <!-- Detail Footer Bar -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="goBackToList"
                        class="px-4 py-2 cursor-pointer bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 font-semibold text-sm transition-all shadow-sm">
                        Back to List
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    @if($activeTab === 'system')
                        <button type="button" wire:click="addToYourAutomations"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition-all shadow-md cursor-pointer active:scale-95">
                            <span class="material-symbols-outlined text-[18px]">library_add</span>
                            Save & Add to Library
                        </button>
                    @else
                        <button type="button" wire:click="saveChanges" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition-all shadow-md cursor-pointer active:scale-95 disabled:opacity-50">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Save Changes
                        </button>
                    @endif
                </div>
            </div>

        </div>
    @endif

    <!-- Create Custom Automation Modal -->
    @if($showCreateModal)
        @php
            $selectedLib = $selectedLibraryId ? \Modules\CA\Models\CAAutomationLibrary::find($selectedLibraryId) : null;
            $selectedLibName = $selectedLib ? $selectedLib->name : '';
            $selectedLibFreq = $selectedLib ? $selectedLib->frequency : 'monthly';
        @endphp
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-7xl overflow-hidden border border-slate-200 dark:border-slate-800 flex flex-col" style="height: 90vh;">
                <!-- Detail Header Bar -->
                <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-800/20 shrink-0">
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="$set('showCreateModal', false)"
                            class="flex items-center justify-center p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                        </button>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="size-2 rounded-full bg-blue-500"></span>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                    Create Custom Automation
                                </span>
                            </div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white mt-1">
                                {{ $customName ?: ($selectedLibName ?: 'New Automation') }}
                            </h2>
                        </div>
                    </div>
                    
                    @if($selectedLib)
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                {{ $selectedLibFreq }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Scrollable Body -->
                <div class="flex-1 overflow-y-auto p-6 space-y-8">
                    <!-- Dropdown to select category -->
                    <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-xl border border-slate-200/50 dark:border-slate-800/80">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-450 dark:text-slate-400 mb-2">Select Document Category / Catalogue Reference</label>
                        <select wire:model.live="selectedLibraryId" class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-900 dark:text-white font-semibold">
                            <option value="">-- Choose document category --</option>
                            @foreach(\Modules\CA\Models\CAAutomationLibrary::active()->get() as $lib)
                                <option value="{{ $lib->id }}">{{ $lib->name }} ({{ ucfirst($lib->frequency) }})</option>
                            @endforeach
                        </select>
                        @error('selectedLibraryId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @if($selectedLibraryId && $selectedLib)
                        <!-- Workspace Details Content Layout -->
                        <div class="flex flex-col gap-8">
                            <!-- SECTION 1: REGULAR REMINDER CUSTOMIZER -->
                            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                                <!-- Left: Inputs -->
                                <div class="xl:col-span-7 flex flex-col gap-6">
                                    
                                    <!-- Automation Name Input -->
                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Automation Name / Alias</label>
                                        <input type="text" wire:model="customName" placeholder="E.g., Monthly Bank Statement Automation"
                                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 font-semibold transition-colors">
                                    </div>

                                    <!-- Template Text Customizer -->
                                    <div class="space-y-4">
                                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Template Message Copy</h3>
                                        
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Header Title</label>
                                            <input type="text" wire:model.live.debounce.300ms="templateTitle" 
                                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 font-semibold transition-colors">
                                        </div>

                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Message Body</label>
                                                <!-- Tone Tabs Selector -->
                                                <div class="flex bg-slate-100 dark:bg-slate-800 p-0.5 rounded-xl border border-slate-200/50 dark:border-slate-700/50">
                                                    <button type="button" wire:click="selectTone('professional')"
                                                        class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all cursor-pointer {{ $selectedTone === 'professional' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                                                        👔 Professional
                                                    </button>
                                                    <button type="button" wire:click="selectTone('friendly')"
                                                        class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all cursor-pointer {{ $selectedTone === 'friendly' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                                                        🤝 Friendly
                                                    </button>
                                                    <button type="button" wire:click="selectTone('urgent')"
                                                        class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all cursor-pointer {{ $selectedTone === 'urgent' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                                                        🚨 Urgent
                                                    </button>
                                                </div>
                                            </div>
                                            <textarea wire:model.live.debounce.300ms="templateBody" rows="12"
                                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 font-mono resize-none transition-colors"></textarea>
                                        </div>
                                    </div>

                                    <!-- AI Regeneration Button -->
                                    <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 dark:border-slate-800 pt-4">
                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                            💡 <span class="font-semibold">AI Assistant:</span> Switch tone tabs above to load defaults, or trigger a variation below.
                                        </div>
                                        @php
                                            $regenCount = session()->get('ai_regen_count_' . $selectedLibraryId, 0);
                                        @endphp
                                        <div class="flex flex-col items-end">
                                            <button type="button" wire:click="regenerateWithAI" 
                                                wire:loading.attr="disabled"
                                                wire:target="regenerateWithAI"
                                                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm active:scale-95 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" 
                                                {{ $regenCount >= 3 ? 'disabled' : '' }}>
                                                <span class="material-symbols-outlined text-[16px] animate-spin" wire:loading wire:target="regenerateWithAI">autorenew</span>
                                                <span class="material-symbols-outlined text-[16px]" wire:loading.remove wire:target="regenerateWithAI">auto_awesome</span>
                                                <span wire:loading.remove wire:target="regenerateWithAI">Regenerate Message via AI</span>
                                                <span wire:loading wire:target="regenerateWithAI">Regenerating message...</span>
                                            </button>
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">AI Limit: {{ $regenCount }} / 3 generations</span>
                                        </div>
                                    </div>

                                </div>

                                <!-- Right: Preview Simulator -->
                                <div class="xl:col-span-5 flex flex-col">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Live Reminder WABA Preview</label>
                                    
                                    <!-- Device Frame -->
                                    <div class="border border-slate-200 dark:border-slate-800 rounded-3xl p-3 bg-slate-100 dark:bg-slate-950 flex-grow flex flex-col min-h-[380px] shadow-inner relative overflow-hidden">
                                        <!-- Phone Header -->
                                        <div class="flex items-center gap-2.5 px-3 py-2 bg-emerald-800 dark:bg-emerald-900 text-white rounded-2xl shadow-sm mb-4">
                                            <div class="size-8 rounded-full bg-emerald-700 flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                                {{ substr($customName ?: $selectedLibName, 0, 1) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <h4 class="text-xs font-bold truncate leading-tight">
                                                    {{ $customName ?: $selectedLibName }}
                                                </h4>
                                                <span class="text-[9px] text-emerald-200 flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span> Online
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Chat Bubble Area -->
                                        <div class="flex-grow overflow-y-auto no-scrollbar px-1 flex flex-col justify-end">
                                            <div class="bg-white dark:bg-slate-800 text-slate-950 dark:text-slate-100 rounded-2xl rounded-tl-none p-3.5 shadow-sm border border-slate-200/50 dark:border-slate-800/80 max-w-[90%] text-xs leading-relaxed relative">
                                                @if(!empty($templateTitle))
                                                    <h5 class="font-bold text-[13px] border-b border-slate-100 dark:border-slate-850 pb-1.5 mb-2 text-slate-900 dark:text-white flex items-center gap-1.5">
                                                        <span class="material-symbols-outlined text-[16px] text-emerald-600 dark:text-emerald-400">notifications_active</span>
                                                        {{ $templateTitle }}
                                                    </h5>
                                                @endif
                                                
                                                <div class="space-y-1 text-[11px] font-sans leading-relaxed text-slate-800 dark:text-slate-200">
                                                    @php
                                                        $previewBody = $templateBody;
                                                        $placeholders = [
                                                            'client_name' => 'John Doe',
                                                            'firm_name' => 'Apex Advisors',
                                                            'document_name' => $selectedLibName . ' Documents',
                                                            'due_date' => \Carbon\Carbon::now()->addDays(7)->format('d-M-Y'),
                                                            'days_remaining' => '7',
                                                        ];
                                                        foreach($placeholders as $ph => $val) {
                                                            $previewBody = str_replace('{' . '{' . $ph . '}' . '}', "**" . $val . "**", $previewBody);
                                                            $previewBody = str_replace('{' . $ph . '}', "**" . $val . "**", $previewBody);
                                                            $previewBody = str_replace($ph, "**" . $val . "**", $previewBody);
                                                        }
                                                        $previewBody = nl2br(e($previewBody));
                                                        $previewBody = preg_replace('/\*([^\*]+)\*/', '<strong>$1</strong>', $previewBody);
                                                        $previewBody = preg_replace('/\\*\\*([^\\*]+)\\*\\*/', '<strong>$1</strong>', $previewBody);
                                                    @endphp
                                                    {!! $previewBody !!}
                                                </div>

                                                <div class="flex justify-end items-center gap-1 mt-1.5 text-[9px] text-slate-400 dark:text-slate-500">
                                                    <span>{{ \Carbon\Carbon::now()->format('H:i') }}</span>
                                                    <span class="material-symbols-outlined text-[14px] text-sky-500">done_all</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 2: REMINDER TIMING & RULES -->
                            <div class="space-y-4 border-t border-slate-100 dark:border-slate-800 pt-6">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Reminder Timing & Rules</h3>
                                    <button type="button" wire:click="addReminderRule"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 flex items-center gap-1 cursor-pointer">
                                        <span class="material-symbols-outlined text-[14px]">add</span> Add Rule
                                    </button>
                                </div>

                                @error('editingRules') <span class="text-xs text-red-500 block font-medium">{{ $message }}</span> @enderror

                                <div class="space-y-3">
                                    @forelse($editingRules as $idx => $rule)
                                        <div class="flex items-end gap-3 p-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200/50 dark:border-slate-800 rounded-xl" wire:key="rule-{{ $idx }}">
                                            <!-- Offset Days (Only show if not 'On Due Date') -->
                                            @if(($rule['trigger_type'] ?? '') !== 'on_due')
                                                <div class="w-16 flex flex-col gap-1">
                                                    <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Days</span>
                                                    <input type="number" min="0" wire:model="editingRules.{{ $idx }}.offset_days"
                                                        class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-center font-bold">
                                                </div>
                                            @endif
                                            
                                            <!-- Trigger Type -->
                                            <div class="flex-grow flex flex-col gap-1">
                                                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Trigger Reference</span>
                                                <select wire:model.live="editingRules.{{ $idx }}.trigger_type"
                                                    class="w-full px-2.5 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs cursor-pointer">
                                                    <option value="before_due">Day(s) Before Due Date</option>
                                                    <option value="on_due">On the Due Date</option>
                                                    <option value="after_due">Day(s) After Due Date (Overdue Escalation)</option>
                                                </select>
                                            </div>

                                            <!-- Send Time -->
                                            <div class="w-32 flex flex-col gap-1">
                                                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Send Time</span>
                                                <input type="time" wire:model="editingRules.{{ $idx }}.send_time"
                                                    class="w-full px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs">
                                            </div>

                                            <!-- Remove button -->
                                            <div class="pb-1">
                                                <button type="button" wire:click="removeReminderRule({{ $idx }})"
                                                    class="text-slate-400 hover:text-red-500 transition-colors cursor-pointer">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-500 dark:text-slate-400 italic">No reminder triggers configured yet. Clients won't receive messages until you add rules.</p>
                                    @endforelse
                                </div>
                            </div>

                            <!-- SECTION 3: OVERDUE REMINDER CUSTOMIZER -->
                            @if(collect($editingRules)->contains('trigger_type', 'after_due'))
                                <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 border-t border-slate-100 dark:border-slate-800 pt-8 mt-4 animate-fade-in">
                                    <!-- Left: Inputs -->
                                    <div class="xl:col-span-7 flex flex-col gap-6">
                                        
                                        <!-- Overdue Template Text Customizer -->
                                        <div class="space-y-4">
                                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-450 dark:text-slate-400">Overdue Reminder Template Message</h3>
                                            
                                            <div>
                                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Overdue Header Title</label>
                                                <input type="text" wire:model.live.debounce.300ms="overdueTitle" 
                                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 font-semibold transition-colors">
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Overdue Message Body</label>
                                                <textarea wire:model.live.debounce.300ms="overdueBody" rows="12"
                                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 font-mono resize-none transition-colors"></textarea>
                                            </div>
                                        </div>

                                        <!-- Overdue AI Regeneration Button -->
                                        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 dark:border-slate-800 pt-4">
                                            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                                💡 <span class="font-semibold">AI Assistant:</span> Trigger a custom urgent overdue variation copy below.
                                            </div>
                                            @php
                                                $overdueRegenCount = session()->get('ai_overdue_regen_count_' . $selectedLibraryId, 0);
                                            @endphp
                                            <div class="flex flex-col items-end">
                                                <button type="button" wire:click="regenerateOverdueWithAI" 
                                                    wire:loading.attr="disabled"
                                                    wire:target="regenerateOverdueWithAI"
                                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm active:scale-95 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" 
                                                    {{ $overdueRegenCount >= 3 ? 'disabled' : '' }}>
                                                    <span class="material-symbols-outlined text-[16px] animate-spin" wire:loading wire:target="regenerateOverdueWithAI">autorenew</span>
                                                    <span class="material-symbols-outlined text-[16px]" wire:loading.remove wire:target="regenerateOverdueWithAI">auto_awesome</span>
                                                    <span wire:loading.remove wire:target="regenerateOverdueWithAI">Regenerate Overdue via AI</span>
                                                    <span wire:loading wire:target="regenerateOverdueWithAI">Regenerating message...</span>
                                                </button>
                                                <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">AI Limit: {{ $overdueRegenCount }} / 3 generations</span>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Right: Preview Simulator -->
                                    <div class="xl:col-span-5 flex flex-col">
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Live Overdue WABA Preview</label>
                                        
                                        <!-- Device Frame -->
                                        <div class="border border-slate-200 dark:border-slate-800 rounded-3xl p-3 bg-slate-100 dark:bg-slate-950 flex-grow flex flex-col min-h-[380px] shadow-inner relative overflow-hidden">
                                            <!-- Phone Header -->
                                            <div class="flex items-center gap-2.5 px-3 py-2 bg-red-800 dark:bg-red-900 text-white rounded-2xl shadow-sm mb-4">
                                                <div class="size-8 rounded-full bg-red-700 flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                                    ⚠️
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <h4 class="text-xs font-bold truncate leading-tight">
                                                        Overdue: {{ $customName ?: $selectedLibName }}
                                                    </h4>
                                                    <span class="text-[9px] text-red-200 flex items-center gap-1">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span> Overdue Notice
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Chat Bubble Area -->
                                            <div class="flex-grow overflow-y-auto no-scrollbar px-1 flex flex-col justify-end">
                                                <div class="bg-white dark:bg-slate-800 text-slate-950 dark:text-slate-100 rounded-2xl rounded-tl-none p-3.5 shadow-sm border border-slate-200/50 dark:border-slate-800/80 max-w-[90%] text-xs leading-relaxed relative">
                                                    @if(!empty($overdueTitle))
                                                        <h5 class="font-bold text-[13px] border-b border-red-100 dark:border-slate-850 pb-1.5 mb-2 text-red-850 dark:text-red-400 flex items-center gap-1.5">
                                                            <span class="material-symbols-outlined text-[16px] text-red-650 dark:text-red-400 font-bold">warning</span>
                                                            {{ $overdueTitle }}
                                                        </h5>
                                                    @endif
                                                    
                                                    <div class="space-y-1 text-[11px] font-sans leading-relaxed text-slate-800 dark:text-slate-200">
                                                        @php
                                                            $previewOverdueBody = $overdueBody;
                                                            $placeholders = [
                                                                'client_name' => 'John Doe',
                                                                'firm_name' => 'Apex Advisors',
                                                                'document_name' => $selectedLibName . ' Documents',
                                                                'due_date' => \Carbon\Carbon::now()->addDays(-3)->format('d-M-Y'),
                                                                'days_remaining' => '3',
                                                            ];
                                                            foreach($placeholders as $ph => $val) {
                                                                $previewOverdueBody = str_replace('{{' . $ph . '}}', "**" . $val . "**", $previewOverdueBody);
                                                                $previewOverdueBody = str_replace('{' . $ph . '}', "**" . $val . "**", $previewOverdueBody);
                                                                $previewOverdueBody = str_replace($ph, "**" . $val . "**", $previewOverdueBody);
                                                            }
                                                            $previewOverdueBody = nl2br(e($previewOverdueBody));
                                                            $previewOverdueBody = preg_replace('/\*([^\*]+)\*/', '<strong>$1</strong>', $previewOverdueBody);
                                                            $previewOverdueBody = preg_replace('/\\*\\*([^\\*]+)\\*\\*/', '<strong>$1</strong>', $previewOverdueBody);
                                                        @endphp
                                                        {!! $previewOverdueBody !!}
                                                    </div>

                                                    <div class="flex justify-end items-center gap-1 mt-1.5 text-[9px] text-slate-400 dark:text-slate-500">
                                                        <span>{{ \Carbon\Carbon::now()->format('H:i') }}</span>
                                                        <span class="material-symbols-outlined text-[14px] text-sky-500">done_all</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- SECTION 4: TEMPLATE VARIABLES MAPPING -->
                            <div class="space-y-4 border-t border-slate-100 dark:border-slate-800 pt-6">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Template Variables Mapping</h3>
                                
                                <div class="space-y-3 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-850">
                                    @if(empty($extractedVariables['body']) && empty($extractedVariables['header']))
                                        <p class="text-xs text-slate-500 dark:text-slate-400 italic">No custom parameters detected in the template copy.</p>
                                    @else
                                        <!-- Header variables -->
                                        @foreach(($extractedVariables['header'] ?? []) as $var)
                                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center" wire:key="hvar-{{ $var }}">
                                                <div class="sm:col-span-4 text-xs font-bold text-slate-700 dark:text-slate-300 truncate">
                                                    Header: <code class="bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 px-1.5 py-0.5 rounded font-mono">{{ $var }}</code>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <select wire:model.live="variableMappings.header.{{ $var }}.source" 
                                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs cursor-pointer font-medium">
                                                        <option value="system">System Value</option>
                                                        <option value="static">Static Value</option>
                                                    </select>
                                                </div>
                                                <div class="sm:col-span-5">
                                                    @if(($variableMappings['header'][$var]['source'] ?? 'system') === 'system')
                                                        <select wire:model.live="variableMappings.header.{{ $var }}.value" 
                                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs cursor-pointer font-medium">
                                                            @foreach($this->getAvailableSystemVariables() as $sysVar)
                                                                <option value="{{ $sysVar['key'] }}">{{ $sysVar['label'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <input type="text" wire:model="variableMappings.header.{{ $var }}.value" placeholder="Enter static text"
                                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs">
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach

                                        <!-- Body variables -->
                                        @foreach(($extractedVariables['body'] ?? []) as $var)
                                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center" wire:key="bvar-{{ $var }}">
                                                <div class="sm:col-span-4 text-xs font-bold text-slate-700 dark:text-slate-300 truncate">
                                                    Body: <code class="bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 px-1.5 py-0.5 rounded font-mono">{{ $var }}</code>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <select wire:model.live="variableMappings.body.{{ $var }}.source" 
                                                        class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs cursor-pointer font-medium">
                                                        <option value="system">System Value</option>
                                                        <option value="static">Static Value</option>
                                                    </select>
                                                </div>
                                                <div class="sm:col-span-5">
                                                    @if(($variableMappings['body'][$var]['source'] ?? 'system') === 'system')
                                                        <select wire:model.live="variableMappings.body.{{ $var }}.value" 
                                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs cursor-pointer font-medium">
                                                            @foreach($this->getAvailableSystemVariables() as $sysVar)
                                                                <option value="{{ $sysVar['key'] }}">{{ $sysVar['label'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <input type="text" wire:model="variableMappings.body.{{ $var }}.value" placeholder="Enter static text"
                                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs">
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-12 text-center text-slate-500">
                            <span class="material-symbols-outlined text-[48px] text-slate-300 mb-2">library_add</span>
                            <p class="text-sm font-semibold">Please select a Document Category from the dropdown above to manually configure its custom parameters.</p>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-end gap-3 shrink-0">
                    <button type="button" wire:click="$set('showCreateModal', false)"
                        class="px-4 py-2 cursor-pointer bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 font-semibold text-sm transition-all shadow-sm">
                        Cancel
                    </button>
                    @if($selectedLibraryId && $selectedLib)
                        <button type="button" wire:click="createCustomAutomation"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition-all shadow-md cursor-pointer active:scale-95">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            Create Automation
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
